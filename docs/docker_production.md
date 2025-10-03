## Running in Docker (Production)

This guide shows how to build and run the app with a lightweight FrankenPHP (Caddy) image on Alpine, targeting Symfony 7+ / PHP 8.4.

### Prerequisites
- Docker Engine and Docker Compose v2.10+
- Built application image (see below)

### Build the image
```sh
docker build -t pr-bot:latest .
```

### Pass sensitive configuration
In production, prefer environment variables or a compiled env (`.env.local.php`). If you need to pass secrets from a file, use `.env.prod.local` and provide it at runtime:

```sh
# Example: run with variables from .env.prod.local
docker run -d --name pr-bot \
  --env-file .env.prod.local \
  -p 8080:80 \
  pr-bot:latest
```

Notes:
- Ensure `APP_ENV=prod` and `APP_DEBUG=0` are set in your environment. They are also set in the image by default.
- Do not commit `.env.prod.local` to version control.

### Compile environment for best performance (optional but recommended)
Bake the environment into the image (faster boot, no `.env*` parsing):

1. Prepare your env files in the project root (`.env`, `.env.prod`, `.env.prod.local`).
2. Add the following step to the Dockerfile (if not already present):
   ```dockerfile
   # Copy envs and compile to .env.local.php for fast prod boot
   COPY .env ./
   COPY .env.prod ./
   COPY .env.prod.local ./
   RUN composer dump-env prod
   ```
3. Rebuild and run:
   ```sh
   docker build -t pr-bot:latest .
   docker run -d --name pr-bot -p 8080:80 pr-bot:latest
   ```

### File permissions & volumes
- The image runs as the `www-data` user. Cache and logs are warmed during build so `var/` is owned by `www-data`.
- Avoid bind-mounting `var/` in production. If you must use a volume, make sure it’s writable by UID/GID 33.

### Health check
After the container starts, verify the app is reachable:
```sh
curl -i http://localhost:8080/
```

### Troubleshooting
- Inspect logs: `docker logs -f pr-bot`
- Exec into the running container:
  ```sh
  docker exec -it pr-bot sh
  ```

### Reference
- Based on the FrankenPHP/Symfony template approach: [dunglas/symfony-docker](https://github.com/dunglas/symfony-docker)


