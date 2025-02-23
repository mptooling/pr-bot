# GitHub Webhook Handler

This project is a PHP application that handles GitHub webhooks and sends notifications to Slack.

## Requirements

- PHP ^8.4
- Composer
- Symfony

## Installation

1. Clone the repository:
    ```sh
    git clone <repository-url>
    cd <repository-directory>
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

## TODO
- Logging.
- Extract logic from controller.
- Ignore draft PRs.
- Update slack message on PR closed.
- Update slack message on PR merged.

## License

This project is licensed under the MIT License.
