# Asset License Audit

Release: Zibll 1.0.0

This audit records the GPL-compatibility cleanup applied to the official source zip.

## Result

All bundled files in this release are either:

- first-party Zibll work licensed as GPL-2.0-or-later;
- third-party open-source code documented in `THIRD-PARTY-NOTICES.md`;
- third-party font files documented under their own open font licenses;
- generated first-party replacement assets created during the 1.0.0 GPL cleanup.

## Assets replaced because historical provenance was not sufficiently documented

The following asset groups were replaced with generated first-party GPL-compatible placeholders:

- `img/smilies/*.gif`
- `img/captcha/*.jpg`
- `screenshot.jpg`
- `img/share_img.jpg`
- `img/slide.jpg`
- `img/slider-bg.jpg`
- `img/topic.jpg`
- `img/user_t.jpg`
- `img/mail-bg.png`
- `img/qrcode.png`
- `zibpay/assets/img/pay-qrcode.png`
- `zibpay/assets/img/alipay-sys*.png`
- `zibpay/assets/img/wechat-sys*.png`

## First-party asset grant

The official maintainer releases all first-party assets in this package under GPL-2.0-or-later, including custom SVG medals, placeholder images, default icons, CSS, JavaScript, templates, PHP, and language files.

## Review notes

- `zibpay/assets/js/highcharts.js` is retained as a compatibility filename, but the file content is Apache ECharts, not Highcharts. Future releases should rename the file and update enqueue references.
- Apache-2.0 components are compatible through the GPLv3-or-later option of the outbound `GPL-2.0-or-later` license.
- Font Awesome and Julius Sans One remain under SIL OFL 1.1 for font software; related CSS/code is MIT or first-party GPL as documented.
