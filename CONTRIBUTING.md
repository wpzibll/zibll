# Contributing

Zibll accepts community contributions through issues and pull requests in the official repository.

## Before opening an issue

- Search existing issues and release notes.
- Confirm you are using an official Zibll release.
- Provide WordPress, PHP, database, server, browser, and Zibll version information.
- Include reproducible steps, screenshots, logs, or a minimal test case when possible.
- Do not post secrets, private user data, or exploitable vulnerability details publicly.

## Before opening a pull request

- Keep the change focused and reviewable.
- Explain the problem, the solution, and the affected modules.
- Update documentation when behavior or configuration changes.
- Preserve GPL-compatible licensing for all new code and assets.
- Do not include production credentials, private keys, tokens, cookies, database dumps, or user data.
- Run PHP syntax checks for changed PHP files.

## Coding expectations

- Follow WordPress coding practices where practical.
- Sanitize input, escape output, and validate data boundaries.
- Verify nonces and user capabilities for state-changing actions.
- Prefer WordPress APIs for HTTP requests, filesystem access, options, users, posts, taxonomies, and database operations.
- Use `$wpdb->prepare()` for dynamic SQL.
- Keep payment, upload, order, login, and permission changes especially small and auditable.
- Document third-party resources in `THIRD-PARTY-NOTICES.md` when adding dependencies or assets.

## Dependency policy

The official release package keeps `vendor/` so ordinary WordPress users can upload and install the theme directly. Dependency updates should be explicit, reviewable, and compatible with the licenses documented in this repository.

## Licensing

By contributing, you agree that your contribution may be distributed under `GPL-2.0-or-later` as part of Zibll.
