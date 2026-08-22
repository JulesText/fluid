# TODO

## Codebase consistency and simplification

- [ ] Replace polymorphic `query()` results (`false`, `0`, affected-row integers, or arrays) with consistent
  `queryRows()`, `queryRow()`, and `execute()` APIs.
- [ ] Standardize database access instead of mixing the named MySQLi query layer with direct PDO queries.
- [ ] Replace direct `$_GET`, `$_POST`, and `$_REQUEST` access with typed request helpers and centralized validation.
- [ ] Replace executable includes such as `matrixSaveCL.php` with reusable functions.
- [ ] Centralize duplicated list and checklist field mapping, defaults, normalization, and validation.
- [ ] Reduce reliance on global `$config`, `$values`, and `$sort` state.
- [ ] Normalize filename casing and include paths for case-sensitive deployment filesystems.
- [ ] Separate request processing, database operations, HTML rendering, and inline JavaScript.
- [ ] Add characterization and smoke tests before restructuring behavior-heavy areas.

Estimated Copilot AI-credit scope:

- Targeted consistency cleanup: **80–150 AIC**
- Thorough incremental refactor with tests: **200–350 AIC**
- Full architecture rewrite: **500+ AIC**

Preferred approach: complete the **200–350 AIC incremental refactor** in independently testable stages.
