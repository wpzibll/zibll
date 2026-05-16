# Zibll

Zibll（子比主题）是由 **wpzibll官方**维护并发布的 WordPress 主题项目。

自 `1.0.0` 起，Zibll 由 wpzibll官方以 `GPL-2.0-or-later` 许可证发布，源代码在 GitHub 公开维护。此前商业版用户仍可继续依据原购买协议使用其已获得的版本；开源版从 `1.0.0` 开始独立维护，并采用公开源码、社区审查、Issue / Pull Request 协作的维护方式。

wpzibll官方站点与教程仍以 <https://www.zibll.com> 为准。GitHub 仓库用于源码发布、问题反馈、代码审查和社区贡献。

## 基本信息

| 项目 | 内容 |
| --- | --- |
| Theme slug | `zibll` |
| 当前版本 | `1.0.0` |
| 许可证 | `GPL-2.0-or-later` |
| WordPress 最低版本 | `5.0` |
| 已测试 WordPress 版本 | `6.8` |
| PHP 最低版本 | `7.0` |
| Text domain | `zibll` |
| zibll官方站点 | <https://www.zibll.com> |
| wpzibll官方维护方 | wpzibll官方 |

## wpzibll官方开源发布说明

Zibll `1.0.0` 是 wpzibll官方发布的首个开源版本，基于 wpzibll官方维护的 Zibll `8.8.1` 代码基线整理发布。自 `1.0.0` 起，项目采用 GPL-2.0-or-later 授权、公开源码、社区审查和 GitHub 协作维护方式。

历史商业版中的域名授权、安装数量限制和专有在线更新机制不适用于 `1.0.0` 及后续开源版本。开源版的更新以 GitHub Release、wpzibll官方站点公告和公开源码仓库为准。

主题中的支付、短信、登录、物流、内容审核、搜索等接口代码属于站点功能模块，仅在站点管理员主动配置第三方服务后生效。本仓库不会内置任何真实商户密钥、应用密钥、短信密钥、OAuth 密钥、API token 或服务器私钥。


## 功能概览

- WordPress 主题模板：文章页、页面、分类页、标签页、归档页、搜索页、作者页、404 页等。
- 响应式前端样式，适配桌面端和移动端。
- 用户系统：登录、注册、找回密码、用户资料、头像、封面、用户中心。
- 内容功能：文章发布、草稿、评论、附件、搜索、海报分享、文档导航等。
- 社区/论坛功能：板块、话题、帖子、版主、关注、权限控制等可选模块。
- 付费与订单功能：内容付费、会员、余额、积分、订单、提现、优惠券、商品等可选模块。
- 第三方服务集成：OAuth 登录、短信、支付、物流查询、内容审核、地理位置/IP 查询、搜索服务等。
- 后台配置系统：主题选项、元框、用户字段、备份与恢复等。

## 安装方式

### 方式一：WordPress 后台上传

1. 下载 GitHub Release 或 wpzibll官方站点发布的主题 ZIP 包。
2. 登录 WordPress 后台。
3. 进入 **外观 → 主题 → 添加新主题 → 上传主题**。
4. 上传 ZIP 包并启用主题。
5. 进入主题设置，根据站点需求启用或关闭相关模块。

### 方式二：手动安装

1. 将 `zibll` 目录上传到 WordPress 的 `wp-content/themes/` 目录。
2. 确认目录结构为：`wp-content/themes/zibll/style.css`。
3. 在 WordPress 后台启用主题。

## 运行环境

建议生产环境使用：

- WordPress 6.x
- PHP 7.4 或 8.x
- MySQL 5.7+ 或 MariaDB 10.3+
- HTTPS
- 已正确配置的文件上传目录权限

最低兼容声明保留为 PHP 7.0，但现代生产环境不建议继续使用已停止维护的 PHP 旧版本。

## Composer 与第三方依赖

仓库包含 `composer.json`，声明了主题使用的 PHP 依赖。当前 wpzibll官方发布包**保留 `vendor/` 目录**，便于普通 WordPress 用户直接上传安装；开发者也可以根据需要重新执行依赖安装和审计。

主要依赖包括：

- `yurunsoft/pay-sdk`
- `yurunsoft/yurun-oauth-login`
- `qcloudsms/qcloudsms_php`
- `guzzlehttp/*`
- `symfony/*`
- `psr/*`

第三方组件、字体、图片、框架和 SDK 的来源及许可信息请阅读：

- `THIRD-PARTY-NOTICES.md`
- `ASSET-LICENSE-AUDIT.md`
- `NOTICE`

## 许可证

Zibll `1.0.0` 及后续开源版本按 `GPL-2.0-or-later` 发布。完整许可证文本见：

- `LICENSE`
- `COPYRIGHT.md`

第三方组件可能使用各自的开源许可证。使用、分发或修改本项目时，应同时遵守本项目许可证和第三方组件许可证。

## 安全

请勿在公开 Issue 中披露可利用漏洞细节。安全问题请按 `SECURITY.md` 中的方式报告。

提交代码或配置示例时，请勿包含真实生产密钥、商户密钥、OAuth secret、短信 secret、服务器私钥、数据库密码或其他敏感信息。

## 贡献

欢迎通过 Issue 和 Pull Request 参与维护。提交前请阅读：

- `CONTRIBUTING.md`
- `CODE_OF_CONDUCT.md`
- `.github/PULL_REQUEST_TEMPLATE.md`

基本要求：

- 保持变更聚焦、可审查。
- 新增代码和资源必须与 GPL-2.0-or-later 兼容。
- 涉及安全、上传、支付、订单、权限、远程请求的变更必须说明安全影响。
- 不提交真实密钥、生产配置和用户隐私数据。

## 发布校验

发布包包含 `MANIFEST.sha256`。可在主题目录中执行：

```bash
sha256sum -c MANIFEST.sha256
```

用于确认发布文件未被意外修改。`MANIFEST.sha256` 自身不参与清单校验。

## 支持与文档

- wpzibll官方站点：<https://www.zibll.com>
- 使用教程：以 wpzibll官方站点发布内容为准
- 源码协作：以 GitHub Issue / Pull Request 为准
- 安全报告：按 `SECURITY.md` 处理

## 免责声明

本项目按 GPL-2.0-or-later 发布，不承诺适用于所有站点环境。站点管理员应根据自身业务场景，对支付、订单、上传、用户注册、短信、OAuth、内容审核等功能进行配置、测试和安全复核。
