[![CI](https://github.com/mptooling/pr-bot/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/mptooling/pr-bot/actions/workflows/ci.yml)

# GitHub Webhook Handler

This project is a PHP application that handles GitHub webhooks and sends notifications to Slack.

## Features
- Listen to GitHub webhooks.
- Posts message to slack channel when PR is created.
- Updates the previous message when PR is closed or merged and adds a reaction to increasing visibility.
- Removes slack message if PR becomes draft.
- Customizable Slack mentions and emojis.

## Requirements

- PHP ^8.4
- Composer
- Symfony

Bot Requirements:
- Channel permissions: invite the bot to the channel to react to messages.
- Bot permissions: `chan:join`, `chat:write` and `chat:write.public`, `incomming-webhook`, `reaction:read`, `reaction:write`.

## Installation

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
    - `GITHUB_WEBHOOK_SECRET`: The Github signature that signs requests.
    - `SLACK_BOT_TOKEN`: The Slack bot token to send messages.
    - `SLACK_CHANNEL`: The Slack channel to send notifications to.

## Usage

Run the Symfony server:
    ```sh
    symfony server:start
    ```

## Testing
```sh
php bin/phpunit
```

## TODO
- Wait until CI is green before posting the message.

## License

This project is licensed under the MIT License.
