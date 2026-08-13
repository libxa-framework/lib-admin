# Changelog

All notable changes to LibAdmin are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

> **Pre-1.0 note.** Composer treats the *minor* number as the compatibility
> boundary below 1.0: `^0.3.0` allows `0.3.9` but not `0.4.0`. Anything that
> removes a route or changes documented behaviour therefore requires a minor
> bump, not a patch.

## [Unreleased]

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
