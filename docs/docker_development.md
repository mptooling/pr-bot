## Docker (Development)

### Installation
- [Application settings configuration](configuring.md)
- [GitHub Webhook registration](register_github_webhook.md)
- [Slack App configuration](slack_bot_configuration.md)
- [Reactions configuration (optional)](reactions_config.md)

Build and run the app locally using the Dockerfile in this repository.

### Build
```sh
docker build -t pr-notificator:latest .
```

### Run (env-file)
```sh
docker run -d --name pr-notificator \
  --env-file .env.prod.local \
  -p 8080:80 \
  pr-notificator:latest
```

### Run (bind-mount env files)
```sh
docker run -d --name pr-notificator \
  -p 8080:80 \
  -v "$PWD/.env.prod:/app/.env.prod:ro" \
  -v "$PWD/.env.prod.local:/app/.env.prod.local:ro" \
  pr-notificator:latest
```

### Troubleshooting
- Logs: `docker logs -f pr-notificator`
- Shell: `docker exec -it pr-notificator sh`


