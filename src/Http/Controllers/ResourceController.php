<?php

declare(strict_types=1);

namespace Libxa\Admin\Http\Controllers;

use Libxa\Admin\Audit\AdminAudit;
use Libxa\Admin\Auth\AdminGuard;
use Libxa\Admin\Panel\ResourceRegistry;
use Libxa\Admin\Resources\AdminResource;
use Libxa\Atlas\DB;
use Libxa\Http\Request;
use Libxa\Http\Response;

/**
 * CRUD over the resources the panel has been given.
 *
 * Every method starts by resolving the URL slug against the registered
 * resources, and refuses anything that does not resolve. That is not
 * defensive tidying: before it, the `{resource}` segment was used directly as
 * a table name, so `/admin/resources/<table>` would read, write and delete any
 * table in the database, `admin_users` included. Two of the methods also
 * interpolated it into raw SQL.
 *
 * The two rules that follow from that, and that every method here keeps:
 *
 *   1. The table comes from the resolved resource's model, never from the URL.
 *   2. The fields that may be written come from the resource's own field list,
 *      never from whatever the form happened to post.
 */
class ResourceController
{
    private AdminAudit $audit;

    public function __construct(
        protected AdminGuard $auth,
    ) {
        $this->audit = new AdminAudit($auth);
    }

    public function index(string $resource): Response
    {
        $class = $this->resourceOr404($resource);

        if (! $class instanceof AdminResource) {
            return $class;
        }

        $model = $class::getModel();
        $items = $model !== null ? $model::all() : [];

        $headerWidgets = array_map(static fn ($w) => new $w(), $class->getHeaderWidgets());
        $footerWidgets = array_map(static fn ($w) => new $w(), $class->getFooterWidgets());
        $columnDefs = array_map(static fn ($col) => $col->toArray(), $class->columns());

        return view('admin::resources.index', [
            'resource' => $resource,
            'user' => $this->auth->user(),
            'items' => $items,
            'headerWidgets' => $headerWidgets,
            'footerWidgets' => $footerWidgets,
            'columnDefs' => $columnDefs,
        ]);
    }

    public function create(string $resource): Response
    {
        $class = $this->resourceOr404($resource);

        if (! $class instanceof AdminResource) {
            return $class;
        }

        return view('admin::resources.create', [
            'resource' => $resource,
            'user' => $this->auth->user(),
            'fields' => array_map(static fn ($f) => $f->viewData(), $class->fields()),
        ]);
    }

    public function store(Request $request, string $resource): Response
    {
        $class = $this->resourceOr404($resource);

        if (! $class instanceof AdminResource) {
            return $class;
        }

        $table = ResourceRegistry::tableFor($resource);

        if ($table === null) {
            return $this->notFound();
        }

        $data = $this->writable($class, $request);

        if ($data === []) {
            return back()->with('errors', ['form' => ['Nothing to save.']]);
        }

        try {
            $id = DB::table($table)->insert($this->withTimestamps($data, $table, creating: true));
        } catch (\PDOException $e) {
            return back()->with('errors', ['form' => [$this->constraintMessage($e)]]);
        }

        $this->audit->record('resource.created', $resource, $id, null, $data);

        return redirect('/admin/resources/' . $resource);
    }

    public function show(string $resource, string $id): Response
    {
        $class = $this->resourceOr404($resource);

        if (! $class instanceof AdminResource) {
            return $class;
        }

        $model = $class::getModel();

        return view('admin::resources.show', [
            'resource' => $resource,
            'id' => $id,
            'user' => $this->auth->user(),
            'item' => $model !== null ? $model::find($id) : null,

            // The detail page used to cast the record to an array and print
            // every key it found, so a column a resource deliberately left out
            // of columns() was still shown in full one click away. The
            // allow-list has to hold on every page that renders a record, not
            // just the table.
            'columnDefs' => array_map(static fn ($col) => $col->toArray(), $class->columns()),
        ]);
    }

    public function edit(string $resource, string $id): Response
    {
        $class = $this->resourceOr404($resource);

        if (! $class instanceof AdminResource) {
            return $class;
        }

        $model = $class::getModel();
        $item = $model !== null ? $model::find($id) : null;

        $fields = [];

        if ($item !== null) {
            $class->item = $item;
            $fields = array_map(static fn ($f) => $f->viewData(), $class->fields());
        }

        return view('admin::resources.edit', [
            'resource' => $resource,
            'id' => $id,
            'user' => $this->auth->user(),
            'item' => $item,
            'fields' => $fields,
        ]);
    }

    public function update(Request $request, string $resource, string $id): Response
    {
        $class = $this->resourceOr404($resource);

        if (! $class instanceof AdminResource) {
            return $class;
        }

        $table = ResourceRegistry::tableFor($resource);

        if ($table === null) {
            return $this->notFound();
        }

        $data = $this->writable($class, $request);

        if ($data === []) {
            return redirect('/admin/resources/' . $resource);
        }

        // Read before the write. Snapshotting afterwards records the new
        // values twice and loses the only copy of what was there before.
        $before = $this->audit->snapshot($table, $id);

        try {
            DB::table($table)->where('id', $id)->updateRecord($this->withTimestamps($data, $table, creating: false));
        } catch (\PDOException $e) {
            return back()->with('errors', ['form' => [$this->constraintMessage($e)]]);
        }

        $this->audit->record('resource.updated', $resource, $id, $before, $data);

        return redirect('/admin/resources/' . $resource);
    }

    public function destroy(string $resource, string $id): Response
    {
        $class = $this->resourceOr404($resource);

        if (! $class instanceof AdminResource) {
            return $class;
        }

        $table = ResourceRegistry::tableFor($resource);

        if ($table === null) {
            return $this->notFound();
        }

        // The deleted row is the whole point of auditing a delete: after this
        // runs there is nothing left anywhere to say what was removed.
        $before = $this->audit->snapshot($table, $id);

        DB::table($table)->where('id', $id)->delete();

        $this->audit->record('resource.deleted', $resource, $id, $before, null);

        return redirect('/admin/resources/' . $resource);
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Guards
    // ─────────────────────────────────────────────────────────────────────

    /**
     * A message for a database constraint failure, safe to show an operator.
     *
     * Saving a duplicate used to escape as an unhandled PDOException, so the
     * panel answered 500 and rendered the driver's message — which names the
     * table and column — straight onto the page. The common cases get a
     * sentence someone can act on; anything else stays generic rather than
     * echoing the driver.
     */
    private function constraintMessage(\PDOException $e): string
    {
        $message = $e->getMessage();

        return match (true) {
            str_contains($message, 'UNIQUE constraint') || str_contains($message, 'Duplicate entry')
                => 'That value is already taken. Records here must be unique.',
            str_contains($message, 'FOREIGN KEY') || str_contains($message, 'foreign key')
                => 'That change refers to a record that does not exist, or is still in use elsewhere.',
            str_contains($message, 'NOT NULL') || str_contains($message, 'cannot be null')
                => 'A required value is missing.',
            default => 'The record could not be saved.',
        };
    }

    /**
     * The resource for a slug, or a 404 response to return instead.
     *
     * Returning either an instance or a Response keeps the check to two lines
     * at the top of every action, which matters: an action that forgets it is
     * an action that reads and writes an arbitrary table again.
     */
    private function resourceOr404(string $slug): AdminResource|Response
    {
        $class = ResourceRegistry::resolve($slug);

        if ($class === null) {
            return $this->notFound();
        }

        return new $class();
    }

    /**
     * Add created_at/updated_at to a write, when the table has them.
     *
     * Records made through the panel had empty timestamps: the query builder
     * does not maintain them, and store() passed only the writable fields
     * straight through. Anything sorting or reporting on created_at — the
     * resource default sort included — then silently treated panel-created
     * rows as the oldest in the table.
     *
     * The columns are checked rather than assumed, since a resource may point
     * at a table that has neither.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function withTimestamps(array $data, string $table, bool $creating): array
    {
        $schema = DB::schema();
        $now = date('Y-m-d H:i:s');

        if ($creating && $schema->hasColumn($table, 'created_at')) {
            $data['created_at'] = $now;
        }

        if ($schema->hasColumn($table, 'updated_at')) {
            $data['updated_at'] = $now;
        }

        return $data;
    }

    private function notFound(): Response
    {
        // Deliberately says nothing about whether a table of that name exists.
        // "No such resource" and "not registered" are different answers, and
        // the difference is a way to enumerate the schema.
        return (new Response(status: 404, content: 'Not found.'));
    }

    /**
     * The submitted values a resource actually allows to be written.
     *
     * Taken from the resource's declared fields rather than from the request,
     * so a form that posts `is_admin` or `role_id` writes neither unless the
     * resource says those are editable. The previous version asked the
     * database which columns existed and accepted all of them, which makes
     * every column mass-assignable by design.
     *
     * @return array<string, mixed>
     */
    private function writable(AdminResource $resource, Request $request): array
    {
        $allowed = [];

        foreach ($resource->fields() as $field) {
            $name = method_exists($field, 'getName') ? $field->getName() : null;

            if (is_string($name) && ResourceRegistry::isSafeColumn($name)) {
                $allowed[] = $name;
            }
        }

        if ($allowed === []) {
            return [];
        }

        $data = array_intersect_key($request->all(), array_flip($allowed));

        // A password field arrives in plain text and must never be stored that
        // way. An empty one means "leave it alone" rather than "set it to the
        // hash of an empty string", which would lock the account with a
        // password nobody knows.
        if (array_key_exists('password', $data)) {
            $password = (string) $data['password'];

            if ($password === '') {
                unset($data['password']);
            } else {
                $data['password'] = password_hash($password, PASSWORD_BCRYPT);
            }
        }

        return $data;
    }
}
