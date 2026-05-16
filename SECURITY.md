# Security Policy

## Supported versions

| Version | Supported |
| --- | --- |
| `1.0.x` | Yes |

Only wpzibll官方 releases are supported. Modified third-party packages, unofficial mirrors, and redistributed archives are outside the wpzibll官方 support scope.

## Reporting vulnerabilities

Do not disclose exploitable vulnerability details in public GitHub issues, discussions, comments, or social media posts.

Use one of the wpzibll官方 private reporting channels:

1. GitHub private vulnerability reporting, when enabled for the repository.
2. The wpzibll官方 Zibll website contact channel: <https://www.zibll.com>.

Please include:

- Affected Zibll version.
- WordPress, PHP, database, and server environment.
- Reproduction steps.
- Proof of concept, if available.
- Impact assessment.
- Suggested fix, if available.
- Whether the issue is already being exploited or publicly discussed.

## Disclosure process

wpzibll官方 will triage the report, confirm impact, prepare a fix, publish a patched release, and credit reporters when appropriate and permitted.

Security fixes may be committed with limited detail before a patched release is available. Public technical details should wait until users have had reasonable time to update.

## Scope

Security-sensitive areas include, but are not limited to:

- Authentication and registration.
- User permissions and capability checks.
- File upload and media processing.
- Payment, order, withdrawal, balance, points, and membership logic.
- AJAX and REST-style endpoints.
- Remote HTTP requests and webhook callbacks.
- Third-party SDK configuration.
- SQL queries, sanitization, escaping, and nonce verification.

## Secrets policy

Do not submit real production credentials, including merchant keys, OAuth secrets, SMS secrets, API tokens, database passwords, SSH keys, or private certificates. Use clearly fake example values such as `your_app_id`, `your_app_secret`, and `example_api_key`.
