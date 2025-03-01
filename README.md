[![CI](https://github.com/mptooling/pr-bot/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/mptooling/pr-bot/actions/workflows/ci.yml)

# GitHub Webhook Handler

This project is a PHP application that handles GitHub webhooks and sends notifications to Slack.

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
- If reactions enabled, permissions `reaction:read`, `reaction:write` also must be added to the bot.

## Installation

### Development/Local
1. Clone the repository:
    ```sh
    git clone git@github.com:mptooling/pr-bot.git
    cd cd pr-bot
    ```

2. Install dependencies:
    ```sh
    composer install
    ```

3. Set up environment variables:
    - Copy `.env.dev` to `.env.dev.local` and configure the necessary environment variables.

4. Add real env variables:
    - GITHUB_WEBHOOK_SECRET=yoursecret
    - SLACK_BOT_TOKEN=xoxb-slackbottoken

## Usage

Run the Symfony server:
    ```sh
    symfony server:start
    ```
Add GitHub and Slack data to the database:
    ```sh
    php bin/console github-slack-mapping:write owner/remository SLACK_CHANNEL_ID '<@USERID>,<!subteam^GROUPID>'
    ```

## Testing
```sh
php bin/phpunit
```

## Linters
Run code static analysis, code style linters and tests with built-in composer scripts:
```sh
  composer cs-check
```

## TODO
- Wait until CI is green before posting the message.

## License

This project is licensed under the MIT License.
