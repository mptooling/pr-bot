## Docker (Development)

### Installation
- [Application settings configuration](configuring.md)
- [GitHub Webhook registration](register_github_webhook.md)
- [Slack App configuration](slack_bot_configuration.md)
- [Reactions configuration (optional)](reactions_config.md)

Build and run the app locally using the Dockerfile in this repository.

### Build
```sh
docker build -t pr-bot:latest .
```

### Run (env-file)
```sh
docker run -d --name pr-bot \
  --env-file .env.prod.local \
  -p 8080:80 \
  pr-bot:latest
```

### Run (bind-mount env files)
```sh
docker run -d --name pr-bot \
  -p 8080:80 \
  -v "$PWD/.env.prod:/app/.env.prod:ro" \
  -v "$PWD/.env.prod.local:/app/.env.prod.local:ro" \
  pr-bot:latest
```

### Troubleshooting
- Logs: `docker logs -f pr-bot`
- Shell: `docker exec -it pr-bot sh`


