# Writing a LibAdmin plugin

A plugin is a Composer package that adds to the panel: resources, pages,
widgets, navigation, or markup at a hook. It never edits LibAdmin's own code,
which is what makes it possible to install two of them and have both still work
after an upgrade.

## The shape

```
acme-blog/
  composer.json
  src/
    BlogPlugin.php
    Resources/PostResource.php
    Widgets/PostCountWidget.php
```

```json
{
    "name": "acme/libadmin-blog",
    "require": {
        "php": "^8.3",
        "libxa/lib-admin": "^0.2"
    },
    "autoload": {
        "psr-4": { "Acme\\Blog\\": "src/" }
    },
    "extra": {
        "Libxa": {
            "providers": ["Acme\\Blog\\BlogServiceProvider"]
        }
    }
}
```

## The plugin

```php
use Libxa\Admin\Panel\AdminPanel;
use Libxa\Admin\Plugins\Plugin;

final class BlogPlugin implements Plugin
{
    public function id(): string      { return 'acme/blog'; }
    public function name(): string    { return 'Blog'; }
    public function version(): string { return '1.0.0'; }

    /** @return list<string> */
    public function requires(): array { return []; }

    public function register(AdminPanel $panel): void
    {
        $panel->registerResources([PostResource::class]);
        $panel->registerWidgets([PostCountWidget::class]);
    }

    public function boot(AdminPanel $panel): void
    {
        $panel->registerNavigation([
            ['label' => 'Blog', 'icon' => 'file-text', 'url' => '/admin/resources/posts'],
        ]);
    }
}
```

Register it from your service provider:

```php
public function boot(): void
{
    $this->app->make('admin.plugins')->add(new BlogPlugin());
}
```

## register() and boot() are two different things

This is the part worth reading twice.

**`register()`** runs for every plugin before any `boot()` does. Declare what
you provide. Do not look at the panel: nothing else is guaranteed to have
registered yet, so what you see depends on which package Composer autoloaded
first. A plugin that behaves differently because an unrelated package was
installed is a bug that takes a long time to find.

**`boot()`** runs after every plugin has registered. Now the panel is complete,
so this is where to inspect it, react to another plugin, or decide based on
what exists.

```php
public function boot(AdminPanel $panel): void
{
    // Fine here. Would be a coin toss in register().
    if ($this->plugins->has('acme/media')) {
        $panel->registerRenderHook('post.form.footer', fn () => view('blog::media-picker'));
    }
}
```

## Dependencies are declared, not assumed

```php
public function requires(): array
{
    return ['acme/media'];
}
```

The manager orders `boot()` so a dependency boots first, regardless of the
order plugins were added. If the dependency is not installed, your plugin is
skipped with a message naming it, rather than failing later somewhere that
does not mention the real cause.

A cycle is reported for every plugin in it. There is no correct order for a
cycle, and picking one arbitrarily hides the mistake.

## A broken plugin does not take the panel down

If your plugin throws during `register()` or `boot()`, it is recorded and
skipped. Every other plugin still loads, and the panel still opens.

This is deliberate and worth relying on: an admin panel is what someone opens
when something is already wrong. A third-party package throwing must not be the
reason they cannot look.

```php
$failures = app('admin.plugins')->failures();
// ['acme/broken' => 'Undefined method Foo::bar()']
```

A plugin that fails to `register()` is not booted, because its own state is
unknown at that point and booting it anyway is how a half-registered plugin
corrupts the panel.

## What you can register

| | |
|---|---|
| `registerResources([...])` | CRUD over a model |
| `registerPages([...])` | A custom page |
| `registerWidgets([...])` | Dashboard widgets |
| `registerNavigation([...])` | Sidebar entries |
| `registerRenderHook($name, $content)` | Markup at a named point |

## Ids are vendor-prefixed

`acme/blog`, not `blog`. Two plugins claiming the same id is refused outright,
because otherwise one silently replaces the other and which one wins depends on
load order. Two different authors both shipping a plugin called "blog" is not
unlikely.

## Security

Your plugin runs inside the panel, with whatever access the panel has. Two
things to hold to:

**Resources declare their own fields, and those are the only writable ones.**
LibAdmin builds its write allow-list from `fields()`. If you bypass that and
write from the request directly, you have reintroduced mass assignment for
every column in your table.

**Never put a request value where an identifier goes.** Table and column names
cannot be bound as parameters. If you take a column from the query string, for
sorting or filtering, check it against your resource's own columns first.
`ResourceRegistry::isSafeColumn()` does this.

Both of these are here because LibAdmin got them wrong: the resource name came
from the URL and was used directly as a table name, so any table in the
database could be read, written and deleted through it.

## Uploads

If your plugin accepts files, use the media store rather than writing your own:

```php
app('admin.media')->store($request->file('file'), $actorId);
```

It detects the type from the bytes, requires the extension and the contents to
agree, generates the stored filename, and refuses anything not on the
allow-list. `UploadedFile::getMimeType()` returns what the client said, so
anything trusting it accepts a PHP script declared as a PNG.

## Testing

Test the plugin without a panel:

```php
$panel = new AdminPanel();
$manager = new PluginManager();
$manager->add(new BlogPlugin());
$manager->load($panel);

self::assertSame([], $manager->failures());
self::assertContains(PostResource::class, $panel->getResources());
```

`failures()` being empty is the assertion that matters. Without it, a plugin
that throws during boot passes a test that only checks the panel still works,
because that is exactly what the manager guarantees.
