[![PHP](https://img.shields.io/badge/PHP-8.4-4F5B93?logo=php)](https://img.shields.io/badge/PHP-8.4-4F5B93?logo=php)
[![CI](https://github.com/mptooling/pr-bot/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/mptooling/pr-bot/actions/workflows/ci.yml)
![PHPStan Level 8](https://img.shields.io/badge/PHPStan-Level%208-blue?logo=php)

# GitHub Webhook Handler

> [!IMPORTANT]
> 📦 **This project is archived** — but the idea lives on! Development has moved to
> [**notifycat**](https://github.com/mptooling/notifycat), a Go rewrite of the same concept:
> PR webhooks in, tidy Slack updates out — just with more features and active maintenance.
> If pr-bot was useful to you, notifycat will feel right at home. Thanks for stopping by! 🐱

This project is a PHP application that handles GitHub webhooks and sends notifications to Slack.
The main feature is updating the existing slack messages instead of creating new ones which
drastically reduces the number of messages in the channel.

For more information please read the [documentation](docs/index.md).

## License

This project is licensed under the MIT License.
