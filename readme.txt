=== Zibll ===
Contributors: Qinver
Tags: blog, community, forum, news, e-commerce, custom-logo, custom-menu, featured-images, footer-widgets, sticky-post, threaded-comments, translation-ready, two-columns, right-sidebar
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Zibll is the official open source WordPress theme maintained and released by Qinver / Zibll.

== Description ==

Zibll provides responsive templates, publishing UI components, user account features, optional community/forum features, optional shop/order/payment templates, and configurable integrations for site administrators.

Starting from version 1.0.0, Zibll is distributed by the official maintainer under GPLv2 or later and maintained through public source code review and community collaboration. Official documentation and usage guides remain available from zibll.com.

Payment, SMS, OAuth, logistics, content-audit, IP/map lookup, and search-related code is provided for site-owner configurable integrations. No real merchant key, application secret, API token, or private key is bundled in this package.

For full project information, security notes, dependency notes, third-party notices, contribution rules, and release guidance, see README.md in the theme root.

== Installation ==

1. Upload the theme ZIP from Appearance > Themes > Add New > Upload Theme.
2. Activate the theme.
3. Configure theme options, login settings, payment gateways, and optional third-party services as needed.
4. Do not commit production secrets, merchant private keys, API tokens, logs, database dumps, or local configuration files to a public repository.

== External Services ==

Optional modules can connect to third-party services when the site administrator enables and configures them. These may include payment gateways, SMS providers, social login providers, logistics APIs, map/IP lookup providers, content-audit providers, and search services.

== Security ==

This release has received a static pre-release security review. See SECURITY-AUDIT.md and SECURITY.md.

Static review does not replace production penetration testing, dependency vulnerability scanning, payment callback testing, or business-permission review.

== Copyright and License ==

Zibll theme code is licensed under GPLv2 or later. Bundled third-party resources are documented in LICENSE, license.txt, NOTICE, COPYRIGHT.md, THIRD-PARTY-NOTICES.md, ASSET-LICENSE-AUDIT.md, and licenses/.

== Changelog ==

= 1.0.0 =
* Initial official open source release by Qinver / Zibll.
* Unified the public package version to 1.0.0.
* Added official open source documentation, community files, and security policy.
* Preserved vendor dependencies for direct WordPress installation.
* Added nonce verification to chunked upload merge action.
* Replaced the file-size detection fallback with a PHP-native implementation.
* Regenerated the checksum manifest.
