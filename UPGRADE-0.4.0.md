## Upgrade to 0.4.0 (from 0.3.x)

This guide describes the minimal steps to upgrade from 0.3.x to 0.4.0.

Notes:
- Plan a short maintenance window; database schema may change.
- Ensure your environment variables are up to date before starting.

### 1) Pull the changes (or checkout the tag)
```sh
# Update main
git checkout main
git pull --ff-only

# Or checkout the release tag directly
git fetch --tags
git checkout 0.4.0
```

### 2) Install Composer dependencies
```sh
composer install --no-dev --optimize-autoloader --no-interaction
```

### 3) Run Doctrine migrations
```sh
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
```

### 4) Clear and warm the cache (prod)
```sh
APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear --no-warmup
APP_ENV=prod APP_DEBUG=0 php bin/console cache:warmup
```

### 5) Restart webserver
```

```

### 5) Verify
Check the webhook endpoint responds (it should reject unauthenticated calls):
```sh
curl -i -X POST \
  -H 'Content-Type: application/json' \
  http://<your-host>:<port>/webhook/github \
  -d '{}'
```

Expected: `401 Unauthorized` with body like `Unauthenticated`.

