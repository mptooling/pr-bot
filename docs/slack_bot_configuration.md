# How to Create a Slack Bot

## Step 1: Create a Slack App
1. Navigate to [Slack API: Your Apps](https://api.slack.com/apps).
2. Click **Create an App**.
3. Choose **From scratch**.
4. Enter a name for your bot and select a workspace.
5. Click **Create App**.

## Step 2: Configure Bot Permissions
To enable your bot to perform actions, grant the necessary OAuth permissions.

1. Go to the **OAuth & Permissions** section in your app settings.
2. Under **Scopes**, add the following **Bot Token Scopes**:
    - `channels:join` – Allows the bot to join public channels.
    - `chat:write` – Enables the bot to send messages to users and channels.
    - `chat:write.public` – Allows the bot to send messages to public channels.
    - `incoming-webhook` – Enables webhooks to send messages to Slack.
3. If your bot will handle reactions (emoji interactions), add these scopes:
    - `reactions:read` – Allows reading emoji reactions in channels.
    - `reactions:write` – Enables adding emoji reactions to messages.

For more details, refer to Slack's official [OAuth & Permissions documentation](https://api.slack.com/authentication/oauth-v2).

## Step 3: Install the App to Your Workspace
1. Navigate to the **OAuth & Permissions** section.
2. Click **Install to Workspace**.
3. Review the requested permissions and authorize the app.
4. After installation, copy the **Bot User OAuth Token** that must be used as a value of the `SLACK_BOT_TOKEN` environment variable..

## Step 4: Invite the Bot to Channels
To allow the bot to post messages in channels, invite it to the desired channels. Use slack channel integration to invite the bot to the channel.