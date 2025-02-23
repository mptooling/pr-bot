# GitHub Webhook Handler

This project is a PHP application that handles GitHub webhooks and sends notifications to Slack.

## Requirements

- PHP ^8.4
- Composer
- Symfony

Bot Requirements:
- Channel permissions: invite bot to the channel so it can react on messages.
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
- Remove message if PR switched to draft.
- Wait until CI is green before posting message.

## License

This project is licensed under the MIT License.
