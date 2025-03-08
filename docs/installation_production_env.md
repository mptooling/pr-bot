## Production app installation
### TL;DR

In this section I recommend set up based on my personal preferences and experience.
You can adjust it to your needs.

### General steps
1. Install php-fmp v8.4.
2. Install nginx.
3. Install composer.
4. Setup env variables for the application.
5. Install dependencies.
6. Configure message routing.
7. Dump env configuration.

### Nginx and php-fmp configuration

Please refer to the [official Symfony documentation](https://symfony.com/doc/current/setup/web_server_configuration.html) 
for the most up-to-date configuration.

### Install composer

Please refer to the [official Composer documentation](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos)

### Setup env variables

Configure general env variables in the `.env.prod` file:
```text
### Slack Reactions Settings 
  SLACK_REACTIONS_ENABLED=true # Set to true to enable reactions. Make sure bot has permissions.
  SLACK_REACTION_NEW_PR=rocket # Emoji for new PR. Works only if SLACK_REACTIONS_ENABLED=true
  SLACK_REACTION_MERGED_PR=white_check_mark # Emoji for merged PR. Works only if SLACK_REACTIONS_ENABLED=true
  SLACK_REACTION_CLOSED_PR=no_entry_sign # Emoji for closed PR. Works only if SLACK_REACTIONS_ENABLED=true
###
```

Create `.env.prod.local` local file for secret env variables.
```bash
   touch .env.prod.local 
```
Add secret env variables:
```text
   GITHUB_WEBHOOK_SECRET=dummy # Set your GitHub webhook secret
   SLACK_BOT_TOKEN=xoxb-bot-secret-token # Set your GitHub webhook secret
```

### Install dependencies
```bash
composer install --no-dev --optimize-autoloader
```

### Configure message routing

Please, read [configuring application notifications](configuring.md) for more information.

### Dump env configuration
```bash
php composer dump-env prod
```

This will create a `.env.local.php` file with the configuration for the production environment.

For more info, please read [official documentation](https://symfony.com/doc/current/configuration.html#configuring-environment-variables-in-production).
