#!/usr/bin/env bash
set -Eeuo pipefail

BRANCH="${1:-main}"
EXPECTED_SHA="${2:-}"
BUILD_NUMBER="${3:-}"
APP_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
LOCK_FILE="$APP_PATH/storage/framework/deploy.lock"
PREVIOUS_SHA=""

cd "$APP_PATH"

mkdir -p storage/framework storage/logs storage/app/backups

if command -v flock >/dev/null 2>&1; then
    exec 9>"$LOCK_FILE"
    flock -n 9 || {
        echo "Another deployment is already running."
        exit 1
    }
fi

if [[ ! -f artisan || ! -d .git ]]; then
    echo "Deployment path is not a Laravel Git repository: $APP_PATH"
    exit 1
fi

PREVIOUS_SHA="$(git rev-parse HEAD)"
echo "$PREVIOUS_SHA" > storage/framework/previous-deploy-sha

rollback_code() {
    local exit_code=$?
    echo "Deployment failed. Restoring code to $PREVIOUS_SHA"

    git reset --hard "$PREVIOUS_SHA" || true
    composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress || true

    if command -v npm >/dev/null 2>&1; then
        npm ci --no-audit --no-fund || true
        npm run build || true
    fi

    php artisan optimize:clear || true
    php artisan optimize || true
    php artisan up || true

    exit "$exit_code"
}

trap rollback_code ERR

echo "Creating pre-deploy database backup..."
php artisan portfolio:backup --database-only || {
    echo "Pre-deploy backup failed; deployment aborted."
    exit 1
}

git fetch origin --prune

TARGET_SHA="$(git rev-parse "origin/$BRANCH")"

if [[ -n "$EXPECTED_SHA" && "$TARGET_SHA" != "$EXPECTED_SHA" ]]; then
    echo "Expected $EXPECTED_SHA but origin/$BRANCH points to $TARGET_SHA"
    exit 1
fi

if ! git merge-base --is-ancestor "$PREVIOUS_SHA" "$TARGET_SHA"; then
    echo "Non fast-forward deployment refused."
    exit 1
fi

MAINTENANCE_SECRET="$(php -r 'echo bin2hex(random_bytes(16));')"
php artisan down --retry=15 --secret="$MAINTENANCE_SECRET"

git merge --ff-only "$TARGET_SHA"

composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction \
    --no-progress

if command -v npm >/dev/null 2>&1; then
    npm ci --no-audit --no-fund
    npm run build
elif [[ ! -f public/build/manifest.json ]]; then
    echo "Node.js is unavailable and no built Vite manifest exists."
    exit 1
fi

php artisan migrate --force
php artisan storage:link || true
php artisan optimize:clear
php artisan optimize
php artisan queue:restart

cat > storage/framework/deployment.json <<JSON
{
  "commit": "$(git rev-parse HEAD)",
  "branch": "$BRANCH",
  "build": "$BUILD_NUMBER",
  "deployed_at": "$(date -u +"%Y-%m-%dT%H:%M:%SZ")"
}
JSON

php artisan portfolio:production-check --strict

php artisan up
trap - ERR

echo "Deployment completed: $(git rev-parse HEAD)"
