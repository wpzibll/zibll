# Security Audit Report

审计对象：Zibll WordPress Theme `1.0.0` 官方开源发布包  
审计类型：开源发布前静态安全审计  
审计日期：2026-05-14

## 审计结论

本次静态审计没有发现内置真实私钥、生产密钥或明显后门代码。主题可以作为 GitHub 开源发布的基础包，但不应宣称已经完成生产级安全认证。

本次已直接修复 2 项问题：

1. `action/media.php`：`zib_ajax_user_split_upload_merge()` 增加 `zib_ajax_verify_nonce('user_upload')`，避免登录态下被 CSRF 触发分片合并动作。
2. `inc/class/file-class.php`：移除文件大小检测函数中的 `exec/stat` 系统命令执行路径，改为 PHP 原生 `filesize()` 回退。

本次已清理 1 个无用文件：

- `zibpay/assets/img/Thumbs.db`

## 检查范围

检查范围包括：

- 主题根目录 PHP 模板文件。
- `action/` AJAX 处理代码。
- `inc/` 主题核心、后台配置、用户、社区、消息、附件、搜索等模块。
- `zibpay/` 支付、订单、会员、余额、提现、商品相关模块。
- `oauth/` 第三方登录相关模块。
- `vendor/` 第三方依赖目录的存在性与发布风险。
- 根目录开源合规文件、校验清单和说明文档。

## 已执行检查

### PHP 语法检查

对非 `vendor/` 目录下 PHP 文件执行语法检查，结果通过。

### 敏感信息检查

检索了以下类型内容：

- AWS 风格密钥。
- `secret_key`、`access_key`、`app_secret`、`private_key`、`api_key` 等字段。
- PEM/私钥文件。
- 口令、token、生产凭据模式。

结果：未发现明显真实密钥或私钥。代码中存在大量第三方服务配置字段名称，属于后台配置项或变量命名，不等同于泄露。

### 高风险函数检查

重点检查了：

- `eval` / `assert`
- `exec` / `shell_exec` / `system` / `passthru` / `proc_open` / `popen`
- `unserialize`
- `base64_decode`
- `file_put_contents`
- `curl_exec`
- `wp_remote_get` / `wp_remote_post`
- `$wpdb->query` / `$wpdb->get_results` / `$wpdb->get_var`

处理结果：

- 未发现主题自有业务代码中直接使用 `eval` 执行用户输入的情况。
- 已移除 `inc/class/file-class.php` 中可被安全工具标红的 `exec` 路径。
- `curl_exec`、`wp_remote_*` 主要集中在第三方 SDK、支付、OAuth、物流、内容审核、搜索服务等模块，属于功能性远程请求，但仍需在生产环境限制可配置 URL 与回调来源。
- `file_put_contents` 主要用于日志、缓存、二维码和支付回调调试文件，建议生产环境关闭调试写入。
- `unserialize` 主要出现在配置框架字段声明中，后续应避免对不可信外部输入直接反序列化。

### AJAX 与权限检查

重点查看了公开 AJAX 与登录态 AJAX：

- `wp_ajax_nopriv_*` 是否主要用于公开读取、登录注册、验证码、评论、搜索等场景。
- 登录态写操作是否存在 nonce 或权限判断。
- 上传、删除、修改用户资料、支付订单等敏感动作是否进行身份校验。

已修复：

- `user_split_upload_merge` 原先只要求登录，缺少合并动作自身的 nonce 校验；已补充。

仍建议继续人工复核：

- 所有 `wp_ajax_nopriv_*` 写操作，尤其是评论、投稿、注册、找回密码、链接提交、支付订单查询等接口，应逐一确认速率限制、验证码、nonce、IP 限制或业务权限。
- 所有后台 AJAX 应确认 `current_user_can()` 权限粒度，而不仅依赖是否登录。

### 文件上传检查

检查重点：

- 上传动作是否校验登录态。
- 上传动作是否校验 nonce。
- 文件类型是否经 WordPress 检测。
- 分片上传是否存在越权合并或超限绕过。

本次修复：

- 分片合并增加 nonce 校验。

仍建议：

- 合并前增加 `$split_chunks_count` 上限，避免异常请求造成资源消耗。
- 对 `$file_name` 使用 `sanitize_file_name()` 后再参与路径和上传逻辑。
- 合并文件建议使用 `wb` 而不是 `ab`，避免异常重复请求追加旧内容。
- 对视频、图片、普通文件分别设置更严格的 MIME 白名单。

### 支付与订单检查

支付模块涉及：

- 支付发起。
- 支付回调。
- 订单关闭。
- 会员购买。
- 余额、积分、提现。
- 虚拟卡密导出。

审计意见：

- 支付回调必须以服务端签名验签结果为唯一依据，不能信任前端返回页。
- 回调日志不应在生产环境写入公开可访问目录。
- 订单状态修改必须保证幂等性，防止重复回调造成重复发货、重复加余额或重复发会员。
- 后台资产、订单、提现、卡密导出等接口必须严格限制管理员权限。

### SQL 与数据访问检查

发现多处 `$wpdb` 查询和拼接查询。静态快速审计未逐条确认所有查询是否完全参数化。

建议：

- 新增和重构代码统一使用 `$wpdb->prepare()`。
- 对排序字段、筛选字段、分页参数使用白名单，不只做字符串清洗。
- 对后台导出类接口增加权限判断和导出审计。

### XSS 与输出转义检查

主题模板和 AJAX 返回中存在大量 HTML 拼接，这是 WordPress 主题常见形态，但也是 XSS 高发区域。

建议：

- URL 输出使用 `esc_url()`。
- HTML 属性使用 `esc_attr()`。
- 普通文本使用 `esc_html()`。
- 富文本内容只允许通过 `wp_kses_post()` 或明确白名单输出。
- JSON 嵌入 HTML 属性时继续使用 `esc_attr(json_encode(...))` 或 `wp_json_encode()`。

### CSRF 检查

主题已有统一 `zib_ajax_verify_nonce()` 和大量 `wp_nonce_field()`。本次发现并修复了一个合并上传动作遗漏。

建议继续复核：

- 所有状态改变操作必须有 nonce。
- 管理员后台危险操作同时需要 nonce 与 `current_user_can()`。
- GET 型删除、恢复、清空、导出动作应尽量改为 POST。

### SSRF 与远程请求检查

主题包含远程下载、OAuth、支付、物流、地图/IP、内容审核、搜索服务等远程请求。

建议：

- 不允许普通用户直接控制远程请求 URL。
- 管理员可配置 URL 也应限制协议为 `https/http`，禁止 `file://`、`gopher://` 等危险协议。
- 对内网地址、回环地址、链路本地地址做拦截，降低 SSRF 风险。
- 设置合理 timeout，避免阻塞 PHP-FPM。

### 开源合规检查

根目录已包含：

- `LICENSE`
- `license.txt`
- `COPYRIGHT.md`
- `NOTICE`
- `THIRD-PARTY-NOTICES.md`
- `ASSET-LICENSE-AUDIT.md`
- `PROVENANCE.md`
- `SECURITY.md`
- `CONTRIBUTING.md`
- `CODE_OF_CONDUCT.md`

建议发布时保留上述文件，不要删除。

## 删除/保留策略

已删除：

- `zibpay/assets/img/Thumbs.db`：Windows 缩略图缓存文件，无发布价值。

暂不删除：

- `.github/`：GitHub issue、PR、CI 配置。
- `.editorconfig`、`.gitignore`、`.distignore`、`phpcs.xml.dist`：开发与发布辅助文件。
- `vendor/`：当前 WordPress 直接上传包依赖其运行；如改为源码仓库模式，可另行讨论是否移除并改用 Composer 安装。
- `inc/codestar-framework/` 与 `inc/csf-framework/`：代码中均存在加载或资源引用，未确认完全冗余前不应删除。
- `js/libs/swiper.min.js.map`：调试映射文件，可选择性删除；本次未删除，避免影响前端调试和源码追踪。
- `inc/csf-framework/assets/images/audit_test.jpg`：后台内容审核测试图片，代码中有引用，未删除。

## 风险分级

### 已修复

| 风险 | 文件 | 说明 |
| --- | --- | --- |
| 中 | `action/media.php` | 分片合并接口缺少 nonce，已补充。 |
| 低-中 | `inc/class/file-class.php` | 文件大小检测存在系统命令执行路径，已移除。 |
| 低 | `zibpay/assets/img/Thumbs.db` | 无用系统缓存文件，已删除。 |

### 待进一步复核

| 风险 | 模块 | 建议 |
| --- | --- | --- |
| 中 | 支付回调 | 逐接口确认签名验签、幂等、日志路径、状态机。 |
| 中 | AJAX 写操作 | 逐接口确认 nonce、权限、验证码、频率限制。 |
| 中 | 文件上传 | 强化 MIME 白名单、文件名清洗、分片数量上限。 |
| 中 | SQL 查询 | 对动态查询统一 `$wpdb->prepare()` 和白名单。 |
| 中 | 远程请求 | 限制协议、内网地址、timeout，降低 SSRF 风险。 |
| 低-中 | HTML 拼接 | 对模板输出继续补齐转义。 |

## 本次变更文件

- `README.md`
- `SECURITY-AUDIT.md`
- `action/media.php`
- `inc/class/file-class.php`
- `MANIFEST.sha256`
- 删除：`zibpay/assets/img/Thumbs.db`

## 发布建议

可以发布到 GitHub。README 中应明确：

1. 这是 Zibll 官方开源发布版本。
2. 未内置真实密钥。
3. 支付和第三方服务由管理员自行配置。
4. 静态审计不等于生产安全认证。
5. 安全问题通过 `SECURITY.md` 披露。



## 2026-05-14 Official Release Wording Cleanup

Applied for the official open source release package:

- Unified runtime package version constants to `1.0.0` via `THEME_VERSION` and `ZIBLL_VERSION`.
- Replaced historical version-maintenance tasks with a concise public version-record hook for the official open source release.
- Standardized the persistent theme version option as `zibll_version`.
- Updated admin option badges and release labels to the public `1.0.0` version.
- Updated `update_log.md` to the official `1.0.0` release-note format and retained official tutorial/documentation links.
- Confirmed the optional WordPress update-check control is administrator-configured and defaults to off.

Notes:

- User-facing commerce features such as paid content, VIP purchase, points purchase, coupon discounts, and order flows remain available as site-owner configurable product features.
- Official Zibll documentation links were intentionally retained.
