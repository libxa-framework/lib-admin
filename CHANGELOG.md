# Changelog

All notable changes to LibAdmin are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

> **Pre-1.0 note.** Composer treats the *minor* number as the compatibility
> boundary below 1.0: `^0.3.0` allows `0.3.9` but not `0.4.0`. Anything that
> removes a route or changes documented behaviour therefore requires a minor
> bump, not a patch.

## [Unreleased]

## [0.6.0] - 2026-08-13

Roles and permissions are enforced. Until now they were furniture.

> **Upgrading: your existing admin accounts hold no roles, so after this
> release they can sign in and do nothing.** Run
> `php libxa admin:sync-permissions` to create the default roles and their
> permissions, then `php libxa admin:assign-role you@example.com superadmin`.
> The sync command detects the situation and prints the exact command,
> naming your accounts. It does not fix it for you: granting superadmin to
> whichever account happens to be first is a privilege escalation performed by
> a migration, and the only thing worse than an admin who cannot do anything
> is one who silently can do everything.

### Security

- **The admin JSON API had no authentication on any route.** Thirteen routes,
  no middleware, so every one answered anybody who asked, from anywhere, with
  no session — including `/admin/api/audit-logs`, which holds the full
  contents of deleted records. What remains of the API is now behind both a
  session check and a permission.

- **`POST /admin/api/login` accepted any email and password without checking
  either**, and replied `{"token": "your-token-here"}`. A client built against
  it believes it has authenticated somebody.

- **Roles and permissions are enforced.** The `roles`, `permissions`,
  `role_user` and `permission_role` tables shipped from the first release and
  nothing ever read them. Access control was authentication-only: any account
  that could log in could create, edit and delete every record of every
  registered resource, and `--role` on `admin:make-user` was decoration.

  Every resource action, the media library and the audit trail now check a
  permission. Two rules the Gate keeps, both cutting against the convenient
  default:

  - **An unknown answer is a denial.** Missing tables or a failed query means
    no, not yes. Failing open so as not to lock anyone out turns a broken
    migration into an unprotected panel, silently.
  - **An admin with no roles can do nothing.** Not "everything, because nobody
    has configured this yet".

  `superadmin` is allowed everything by *name* rather than by holding every
  permission, so a resource added tomorrow is covered without another sync — a
  role that had to hold them all would quietly lose access to each new
  resource.

### Added

- `admin:sync-permissions` writes the permissions the registered resources
  imply, creates the default roles, and grants each role what it is defined as
  having. `--prune` deletes permissions no resource defines any more, along
  with their grants. Re-running only adds: a permission revoked by hand stays
  revoked, because a sync must not quietly restore access someone removed on
  purpose.
- Four default roles — `superadmin`, `admin`, `editor`, `viewer`.
- `AdminResource::$authorize`, `permissionPrefix()` and `permissionFor()`.
  Resources are protected by default; opting out is a decision made in the
  class rather than one made by forgetting.
- Navigation, dashboard Quick Actions and the per-row view/edit/delete buttons
  show only what the account can use. Hiding a link is never the control —
  every route checks for itself — but a sidebar of links that all answer 403
  is a panel nobody can navigate.

### Fixed

- **`admin:assign-role` and `admin:revoke-role` reported success and wrote
  nothing.** Someone granting access to a new colleague, or revoking it from a
  departing one, saw it confirmed and it had not happened. Revoking the last
  superadmin now requires `--force`, since it otherwise leaves a panel with
  nobody who can grant anything and no way back through the UI.
- **`admin:roles` printed a hardcoded list** of four names regardless of what
  was in the database — which was nothing. It reads the table now, with holder
  and permission counts, and `--permissions` to list the grants.
- **`admin:make-user` seeds the default roles** before attaching one, so the
  first account created on a fresh install can actually administer the panel.

### Removed

- **The stubbed JSON API**: `POST /login`, `POST /logout`, `GET /me`, and the
  eight `/resources/*` methods. Every one was a TODO returning a fabricated
  success — `"Resource created successfully"` while creating nothing. They are
  gone rather than fixed: a token-authenticated JSON CRUD API is a feature to
  design, not a hole to patch, and nothing can depend on the behaviour of
  endpoints that never had any. `/admin/api/audit-logs` remains and is real.

## [0.5.0] - 2026-08-13

### Added

- **The admin audit trail now records something.**

  `audit_logs` shipped from the first release, with two API endpoints to read
  it, and nothing ever wrote a row — so the panel could create, edit and
  delete any record in the application and leave nothing behind saying who did
  it, which is the one question an audit trail exists to answer. Because the
  endpoints were stubs returning an empty array, the empty response read as
  "nothing has happened" rather than "this was never built".

  Recorded now: `auth.login`, `auth.login_failed`, `auth.logout`,
  `resource.created`, `resource.updated`, `resource.deleted` — each with the
  acting admin, the resource and id, the IP and user agent, and the values.

  Three things it is careful about:

  - **It never throws.** A trail that can take a request down with it is one
    that gets switched off by the first person it inconveniences.
  - **The old values are read before the write.** Snapshotting afterwards
    records the new values twice and loses the only copy of what was there
    before — and for a delete, the only copy anywhere.
  - **`password`, `remember_token`, `api_token` and `secret` are redacted.** An
    audit row is read by more people than the record it came from; a password
    hash in one is an offline cracking target.

  Failed logins are recorded with the attempted address and no actor. That
  half is the more interesting one: a run of them against a single address is
  what a brute-force attempt looks like from inside the panel.

- **`GET /admin/api/audit-logs` and `/admin/api/audit-logs/{id}`** read the
  table for real, with filtering by `event`, `resource_type` and
  `admin_user_id`, pagination capped at 100 per page, and `old_values` /
  `new_values` decoded rather than handed back as JSON inside JSON.

## [0.4.0] - 2026-08-13

Everything here came out of installing the panel into a real application and
using it, rather than reading the code. The panel could not be signed into,
and once that was fixed, nothing could be saved.

Requires `libxa/framework` ^0.11.1, which contains the router and kernel fixes
this release depends on.

### Fixed

- **No form in the panel carried a CSRF token.** Login, create, edit, delete
  and logout all posted without one, so with CSRF middleware enabled — the
  default — every write answered 419. The panel could not be logged into at
  all. A test now asserts that each shipped view has one `@csrf` per `<form>`,
  because the failure mode is a bare 419 that names neither the form nor what
  is missing.

- **`admin:make-resource` generated a class that fataled on load.** It emitted
  `protected static string $model` while `AdminResource` declares `?string`,
  and PHP requires a redeclared typed property to match exactly. The command
  reported success and the file looked correct; the panel then died on boot
  with a message about property types that mentioned neither the command nor
  the generated file.

- **`admin:make-user` ignored its password argument.** It always prompted, so
  the documented non-interactive form hung forever; with `--no-interaction`
  the prompt returned `null` and `password_hash()` threw a `TypeError` rather
  than saying what was missing. It now takes the password as its third
  argument, enforces a minimum length, reports a duplicate email as a
  duplicate email rather than a driver constraint violation, and actually
  attaches `--role` — or says plainly that it did not, instead of claiming a
  role it never granted.

- **The resource table rendered every column as plain text.** `BadgeColumn`,
  `BooleanColumn` and `ImageColumn` all came out as raw values, and their view
  partials were dead code that nothing included. The table now dispatches to
  each column's own partial.

- **`formatUsing()` was stored and never applied.** So `dateTime()`, which is
  built on it, did nothing: timestamps rendered exactly as the database stored
  them. Formatters now run, and receive the whole record as a second argument
  so a column can be derived from more than its own value.

- **The detail page dumped every database column.** It cast the record to an
  array and printed every key, so a column deliberately left out of
  `columns()` was still shown in full one click away. The allow-list now holds
  on the detail page as well as the table.

- **The detail page 500'd on any non-scalar value.** Array and JSON columns
  went straight to `addslashes()`, which is a `TypeError`. They are rendered
  as JSON now, and the copy button uses `json_encode` rather than
  `addslashes`, which does not escape everything that can end a JS literal.

- **Records created through the panel had empty timestamps.** The query
  builder does not maintain them and `store()` passed only the writable fields
  through, so anything sorting on `created_at` — including the default resource
  sort — treated panel-created rows as the oldest in the table. `created_at`
  and `updated_at` are now set when the table has them.

- **A constraint violation rendered the driver's error at 500.** Saving a
  duplicate showed a message naming the table and column. Unique, foreign-key
  and not-null failures now return a sentence an operator can act on, and the
  create and edit forms render it — they previously ignored flashed errors
  entirely, so a failed save redirected back to a form that looked untouched.

- **Dashboard Quick Actions were four hardcoded links**, one pointing at
  `/admin/resources/users` whether or not such a resource existed and three
  pointing at `#`. They are built from the registered resources now.

### Removed

- **The settings routes.** `SettingsController::index()` rendered
  `admin::settings`, a view that was never written, so `/admin/settings`
  answered 500; `update()` returned "Settings updated successfully" without
  writing anything anywhere. A route that reports success for work it did not
  do is worse than a missing feature, because people build on it. The
  controller is kept, unrouted and throwing, so that wiring a route to it
  fails immediately rather than quietly discarding input.

## [0.3.0] - 2026-08-12

### Added

- Plugin system, so contributors can extend the panel without forking it.
- `MediaStore`: real uploads with an extension/MIME allow-list, `finfo`
  detection rather than the client-supplied type, and randomised stored names.

### Fixed

- `ResourceRegistry`: the `{resource}` URL segment was used directly as a
  table name, so `/admin/resources/<table>` read, wrote and deleted any table
  in the database, `admin_users` included — and two methods interpolated it
  into raw SQL. Slugs now resolve against the registered resources only.
- `fields()` is the write allow-list. It previously asked the database which
  columns existed and accepted all of them, which makes every column
  mass-assignable by design.
