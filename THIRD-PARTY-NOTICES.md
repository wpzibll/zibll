# Third-Party Notices

This file documents bundled third-party code, fonts, and other assets for the official Zibll 1.0.0 source release.

Outbound theme license: **GPL-2.0-or-later**. First-party PHP, JS, CSS, SVG, PNG, JPG, GIF, POT, PO/MO, and generated placeholder assets in this package are released under the same license unless a file or section below states a different upstream license.

## License compatibility policy

- WordPress.org theme uploads require every file in the zip — code, data, images, fonts, icons, libraries, and other resources — to be GPL-compatible.
- MIT, BSD-2/3-Clause, GPL, LGPL, MPL-2.0-compatible files, and Apache-2.0 components are treated as acceptable for this GPL-2.0-or-later distribution. Apache-2.0 components are compatible through the GPLv3-or-later path of the outbound license.
- SIL OFL font files are bundled as font software under their own font license and are documented separately.
- Unverified raster assets from the historical package were replaced with generated first-party placeholder assets in this release.

## PHP dependencies

| Component | Path | Observed version | License | Status |
| --- | --- | ---: | --- | --- |
| Zibll first-party PHP | `*.php`, `action/`, `inc/`, `oauth/`, `pages/`, `template/`, `zibpay/` except noted below | 1.0.0 | GPL-2.0-or-later | First-party |
| Codestar Framework | `inc/codestar-framework/` | bundled | GPL-2.0 | GPL-compatible |
| Zibll CSF custom layer | `inc/csf-framework/` custom files | 1.0.0 | GPL-2.0-or-later | First-party |
| Guzzle | `vendor/guzzlehttp/*` | bundled | MIT | GPL-compatible |
| PSR HTTP interfaces | `vendor/psr/*` | bundled | MIT | GPL-compatible |
| ralouphie/getallheaders | `vendor/ralouphie/getallheaders` | bundled | MIT | GPL-compatible |
| Symfony deprecation contracts | `vendor/symfony/deprecation-contracts` | bundled | MIT | GPL-compatible |
| Tencent Cloud SDK components | `vendor/tencentcloud/*` | bundled | Apache-2.0 | GPLv3-compatible path |
| qcloudsms/qcloudsms_php | `vendor/qcloudsms/qcloudsms_php` | bundled | MIT | GPL-compatible |
| Yurun Pay SDK | `vendor/yurunsoft/pay-sdk` | bundled | MIT | GPL-compatible |
| Yurun HTTP | `vendor/yurunsoft/yurun-http` | bundled | MIT | GPL-compatible |
| Yurun OAuth Login | `vendor/yurunsoft/yurun-oauth-login` | bundled | MIT | GPL-compatible |
| Xunhu Pay SDK | `zibpay/sdk/xhpay` | bundled | MIT | GPL-compatible |
| QR code class | `inc/class/qrcode.class.php`, `js/libs/jquery.qrcode.min.js` | bundled | MIT-compatible upstream QR implementation | GPL-compatible |

## JavaScript and CSS resources

| Component | Path | Observed version | License | Status |
| --- | --- | ---: | --- | --- |
| Zibll first-party JS/CSS | `js/`, `css/`, `inc/functions/*/assets/`, `zibpay/assets/` custom files except listed below | 1.0.0 | GPL-2.0-or-later | First-party |
| jQuery | `js/libs/jquery.min.js` | 1.9.1 | MIT | GPL-compatible |
| Bootstrap | `js/libs/bootstrap*.js`, `css/bootstrap*.css` | 3.4.1 | MIT | GPL-compatible |
| Animate.css | `css/animate*.css` | bundled | MIT-compatible upstream version used by package | GPL-compatible |
| Font Awesome CSS/Sass | `css/font-awesome.min.css`, icon CSS references | 4.7.0 | MIT | GPL-compatible |
| Swiper | `js/libs/swiper*.js`, `css/swiper*.css` | bundled | MIT | GPL-compatible |
| clipboard copy helper / clipboard.js derivative | `js/libs/clipboard*.js` | bundled/customized | MIT / GPL-2.0-or-later custom wrapper | GPL-compatible |
| lazysizes | `js/libs/lazysizes.min.js` | 5.2.0 | MIT | GPL-compatible |
| spark-md5 | `js/libs/spark-md5*.js` | bundled | WTFPL OR MIT; MIT selected | GPL-compatible |
| SortableJS | `js/libs/Sortable.min.js` | 1.10.2 | MIT | GPL-compatible |
| hls.js | `js/libs/hls.min.js` | bundled | Apache-2.0 | GPLv3-compatible path |
| flv.js | `js/libs/flv.min.js` | bundled | Apache-2.0 | GPLv3-compatible path |
| dash.js | `js/libs/dash.all.min.js` | 3.2.1 observed | BSD-3-Clause | GPL-compatible |
| DPlayer / DPlayer-Lite derivative | `js/libs/DPlayer*.js` | bundled/customized | MIT / GPL-2.0-or-later modifications | GPL-compatible |
| html5shiv | `js/libs/html5.min.js` | bundled | MIT or GPL-2.0 | GPL-compatible |
| InstantClick | `js/libs/instantclick.min.js` | 3.1.0 | MIT | GPL-compatible |
| jQuery Cookie | `js/libs/jquery.cookie.min.js` | bundled | MIT or GPL | GPL-compatible |
| jQuery QRCode | `js/libs/jquery.qrcode.min.js` | bundled | MIT | GPL-compatible |
| petite-vue | `js/libs/petite-vue.iife.js` | bundled | MIT | GPL-compatible |
| translate.js | `js/libs/translate*.js` | 4.0.0.20260210 observed | MIT | GPL-compatible |
| EnlighterJS | `js/enlighter/*` | 3.0.0 | MPL-2.0 | GPL-compatible Larger Work path |
| CodeMirror | `inc/csf-framework/assets/libs/codemirror/*` | 5.58.2 observed | MIT | GPL-compatible |
| Apache ECharts | `zibpay/assets/js/echarts-c.min.js`, `zibpay/assets/js/highcharts.js` | bundled | Apache-2.0 | GPLv3-compatible path; `highcharts.js` filename is a compatibility alias and does not bundle Highcharts |
| Vue.js | `zibpay/assets/js/vue.global.min.js` | bundled | MIT | GPL-compatible |
| Vue Router | `zibpay/assets/js/vue-router.global.min.js` | bundled | MIT | GPL-compatible |
| Vue ECharts | `zibpay/assets/js/vue-echarts.min.js` | bundled | MIT | GPL-compatible |
| Element Plus | `zibpay/assets/js/element-plus*.js`, `zibpay/assets/css/element-plus.min.css` | bundled | MIT | GPL-compatible |
| ECharts theme "Westeros" | `zibpay/assets/js/westeros*.js` | bundled | Apache-2.0 / first-party adaptation | GPLv3-compatible path |

## Font assets

| Asset | Path | License | Status |
| --- | --- | --- | --- |
| Font Awesome webfont | `fonts/fontawesome-webfont.*` | SIL OFL 1.1 for font files; MIT for code/CSS | Documented upstream font license |
| Julius Sans One | `fonts/img-code.ttf` | SIL OFL 1.1 | Documented upstream font license |
| Zibll SVG/icon assets | `img/*.svg`, `img/medal/*.svg`, `zibpay/assets/img/*.svg`, first-party SVGs | GPL-2.0-or-later | First-party |

## Image and generated asset inventory

| Asset group | Path | License | Notes |
| --- | --- | --- | --- |
| Generated GPL placeholder screenshots/demo images | `screenshot.jpg`, `img/share_img.jpg`, `img/slide.jpg`, `img/slider-bg.jpg`, `img/topic.jpg`, `img/user_t.jpg`, `img/mail-bg.png`, `img/qrcode.png`, `zibpay/assets/img/*sys*.png`, `zibpay/assets/img/pay-qrcode.png` | GPL-2.0-or-later | Replaced in 1.0.0 GPL cleanup |
| Generated GPL captcha backgrounds | `img/captcha/*.jpg` | GPL-2.0-or-later | Replaced in 1.0.0 GPL cleanup |
| Generated GPL smilies | `img/smilies/*.gif` | GPL-2.0-or-later | Replaced in 1.0.0 GPL cleanup |
| Zibll first-party UI images and SVGs | `img/`, `inc/csf-framework/assets/images/`, `zibpay/assets/img/` except third-party framework files | GPL-2.0-or-later | First-party official assets |
| Codestar/WordPress admin framework images | `inc/codestar-framework/assets/images/`, framework UI images | GPL-compatible via Codestar/WordPress ecosystem | Framework assets |

## Bundled license texts

Supplementary license texts are stored in `licenses/`:

- `licenses/MIT.txt`
- `licenses/BSD-3-Clause.txt`
- `licenses/Apache-2.0.txt`
- `licenses/MPL-2.0.txt`
- `licenses/OFL-1.1.txt`
- `licenses/WTFPL-or-MIT.txt`

## Maintainer rule for future releases

Do not add external images, fonts, JS, CSS, PHP libraries, videos, screenshots, maps, icons, emojis, or demo content unless the resource is one of:

1. first-party Zibll work released under GPL-2.0-or-later;
2. under a documented GPL-compatible open-source license;
3. removed from the release zip and downloaded only after explicit administrator opt-in where WordPress.org policy allows it.
