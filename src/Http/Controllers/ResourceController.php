<?php

declare(strict_types=1);

namespace Libxa\Admin\Http\Controllers;

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
    public function __construct(
        protected AdminGuard $auth,
    ) {
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

        DB::table($table)->insert($data);

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

        DB::table($table)->where('id', $id)->updateRecord($data);

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

        DB::table($table)->where('id', $id)->delete();

        return redirect('/admin/resources/' . $resource);
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Guards
    // ─────────────────────────────────────────────────────────────────────

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
