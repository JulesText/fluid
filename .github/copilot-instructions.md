# Copilot instructions for Fluid

## Setup and validation

- This is a framework-free PHP application served by Apache/PHP with MySQL or MariaDB. Copy
  `config_private.sample.php` to the ignored `config_private.php` and supply the database, login, mail, and OpenAI
  settings. The schema is represented by `db.sql`; `install.php` contains the legacy browser-based installer and
  upgrade paths.
- Install development tools with `composer install` and `npm ci`. There is no build step.
- PHP formatting/linting:
  - Check all PHP: `composer check:php` or `vendor/bin/phpcs --standard=phpcs.xml.dist`
  - Fix all PHP: `composer format:php`
  - Check PHP 7.4 through 8.3 compatibility: `composer check:php-compat`
  - Non-blocking 120-column review: `composer review:php-lines`
  - Check one PHP file: `php -l item.php && vendor/bin/phpcs --standard=phpcs.xml.dist item.php`
- Frontend formatting:
  - Check all first-party JS/CSS and `tabs_*.php`: `npm run check:frontend`
  - Format them: `npm run format:frontend`
  - Check one file: `npx prettier --check gtdfuncs.js`
- No application test suite is configured. `dt/package.json` contains only a placeholder `npm test` that exits with
  an error; do not treat it as a test command.

## Architecture

- Every top-level PHP page or endpoint is directly web-addressable; there is no router, controller layer, template
  engine, autoloader, or namespace structure.
- The normal page bootstrap is `header.php` -> `headerDB.inc.php`. This initializes the session and request globals
  through `ses.php`, loads `config.php`/`config_private.php`, selects the database adapter, connects, and loads
  `gtdfuncs.php`. Full HTML pages then include `headerHtml.inc.php` and finish with `footer.php`; `footer.php` also
  renders session messages and the menu.
- Read/display pages commonly have a paired mutation endpoint: for example `item.php`/`processItems.php`,
  list pages/`processLists.php`, and note pages/`processNote.php`. Mutations usually set `$_SESSION['message']` and
  redirect through `nextScreen()`.
- Database access is primarily label-based: `query($label, $config, $values, $sort)` in `gtdfuncs.php` delegates to
  `getsql()` and `sqlparts()` in `mysql.inc.php`, where the SQL templates live. Default `ORDER BY` fragments live in
  `$sort` in `config.php`. Keep a query label, its values, and its sort entry synchronized.
- The core GTD item is split across `items`, `itemattributes`, and `itemstatus`. Parent/child relationships are stored
  in `lookup`, and next-action relationships in `nextactions`. Type codes are centralized by `getTypes()` and
  `getParentType()` in `gtdfuncs.php`: `m`, `v`, `o`, `g`, `p`, `a`, `i`, `s`, `r`, and `w`.
- Lists and checklists are a parallel model (`list`/`listitems`, `checklist`/`checklistitems`, plus instance tables)
  sharing helpers in `lists.inc.php` and processing in `processLists.php`.
- Live in-place editing is legacy jQuery: PHP emits handlers via `ajaxUpd()`, `matrixAjax.js` posts generic
  table/column identifiers to `matrixSave.php`, and `matrixQuery.php` verifies saved values. Matrix-specific
  calculations fan out through the `matrixSave*.php` endpoints.
- The FI chat feature is a separate subsystem. `fi.php`, `fi_response.php`, and `fi_summary.php` use helpers in
  `fi_require.php`, direct PDO access to `chat_history`, and OpenAI settings from `config.php`/`config_private.php`.

## Repository-specific conventions

- `request.inc.php` is the compatibility boundary for request globals. It validates required inputs per endpoint and
  pre-populates every known `$_GET`, `$_POST`, and `$_REQUEST` key with `null`. When adding an endpoint parameter,
  update its required-key map when appropriate and add the key to `$knownKeys`.
- Follow the query-label vocabulary documented in `mysql.inc.php`: `select*` returns an ID-addressed row, `get*`
  returns collections, while `new*`, `update*`, `delete*`, `complete*`, and `remove*` describe mutations.
- Preserve the configured table prefix (`$config['prefix']`) in SQL and use the existing `query()`/`safeIntoDB()`
  path unless working in one of the established direct-PDO subsystems.
- The application charset is deliberately `ISO8859-15`; helpers such as `makeClean()` use `$config['charset']`.
  Do not silently convert page output or stored text to UTF-8 without an application-wide migration.
- Pure PHP include files generally omit the closing `?>` to avoid accidental response whitespace.
- PHP follows PSR-12 except that scope indentation and files containing side effects are explicitly allowed.
  JavaScript/CSS/JSON use two spaces, double quotes, semicolons, and a 120-column Prettier width. Vendored and
  minified frontend files are excluded by `.prettierignore`.
- `config_private.php`, runtime state files, backups, logs, dependency directories, and installation-specific media
  are ignored. Never move credentials or host-specific values into tracked `config.php`.
- Match tracked filename casing exactly in new includes. Several legacy matrix files have inconsistent historical
  casing, which can work on macOS but fail on a case-sensitive deployment filesystem.
