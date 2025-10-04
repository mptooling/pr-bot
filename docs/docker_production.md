## Docker (Production)

### Installation
- [Application settings configuration](configuring.md)
- [GitHub Webhook registration](register_github_webhook.md)
- [Slack App configuration](slack_bot_configuration.md)
- [Reactions configuration (optional)](reactions_config.md)

Use the published image `luckyj/pr-bit:latest` (or a version like `luckyj/pr-notificator:0.1.0`). Building locally is not required.

### Run with the default configuration and own credentials
```sh
docker run -d --name pr-notificator \
  --env-file .env.prod.local \
  -p 8080:80 \
  luckyj/pr-bit:latest
```

### Run with the custom configuration and credentials
```sh
docker run -d --name pr-notificator \
  -p 8080:80 \
  -v "$PWD/.env.prod:/app/.env.prod:ro" \
  -v "$PWD/.env.prod.local:/app/.env.prod.local:ro" \
  luckyj/pr-bit:latest
```

### Env file rules
- `.env.*.local`: sensitive, machine-specific (secrets). Do NOT commit.
- `.env.*`: general defaults, safe to commit.
- Load order (prod): `.env` → `.env.prod` → `.env.prod.local` (unless compiled env exists).

### Troubleshooting
- Logs: `docker logs -f pr-notificator`
- Shell: `docker exec -it pr-notificator sh`

Reference: [dunglas/symfony-docker](https://github.com/dunglas/symfony-docker)


