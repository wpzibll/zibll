# Release Procedure

1. Ensure `style.css`, `readme.txt`, and `CHANGELOG.md` contain the target version.
2. Run syntax and standards checks.
3. Create Git tag `v1.0.0`.
4. Build the WordPress upload package with folder slug `zibll`:

```bash
zip -r -X zibll-1.0.0.zip zibll
sha256sum zibll-1.0.0.zip > zibll-1.0.0.zip.sha256
```

5. Publish the zip and checksum on the official release page.
6. Announce the release from the official website.

## Package naming

- WordPress theme folder: `zibll`
- Release zip: `zibll-1.0.0.zip`
- Release tag: `v1.0.0`

## Asset and Third-Party License Audit

This release includes a bundled resource audit. See `THIRD-PARTY-NOTICES.md`, `ASSET-LICENSE-AUDIT.md`, `COPYRIGHT.md`, and `licenses/`. Historical raster assets without sufficient provenance were replaced with generated first-party GPL-compatible placeholders.

