## Installation

1. Clone the repository:
    ```sh
    git clone git@github.com:mptooling/pr-bot.git
    cd pr-bot
    ```

2. Install dependencies:
    ```sh
    composer install
    ```

3. Set up environment variables:

   3.1. Create `.env.dev.local` local file for secret env variables.
   ```bash
       touch .env.dev.local 
   ```

   3.2. Configure general env variables. Read [reactions doc](reactions_config.md).
   
   ```
   3.3. Add secret env variables:
   ```text
       GITHUB_WEBHOOK_SECRET=dummy # Set your GitHub webhook secret
       SLACK_BOT_TOKEN=xoxb-bot-secret-token # Set your GitHub webhook secret
   ```


## Local usage

Run the Symfony server:
 ```sh
    symfony server:start
 ```

Tip: Use [ngrok](https://ngrok.com/) to expose your local server to the internet.
