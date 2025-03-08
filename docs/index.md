## Features
- Listen to GitHub webhooks.
- Posts message to Slack channel when PR is created.
- Updates the previous message when PR is closed or merged and adds a reaction to increasing visibility.
- Removes slack message if PR becomes draft.
- Customizable Slack mentions and emojis.

## Requirements

- PHP ^8.4
- Composer
- Symfony

Bot Requirements:
- Channel permissions: invite the bot to the post messages.
- Bot permissions: `chan:join`, `chat:write` and `chat:write.public`, `incomming-webhook`.
- For the reactions(emoji) feature, make sure the following permissions `reaction:read`, `reaction:write` are granted.


## Installation

- [Development Environment](installation_development_env.md)
- [Production Environment](installation_production_env.md)

## Configuration
### Message routing configuration
[Application settings configuration](configuring.md)

### GitHub Webhook registration
[GitHub Webhook registration](register_github_webhook.md)

### Slack App configuration
[Slack App configuration](slack_bot_configuration.md)

## Testing
```sh
php bin/phpunit
```

## Linters
Run code static analysis, code style linters and tests with built-in composer scripts:
```sh
  composer cs-check
```