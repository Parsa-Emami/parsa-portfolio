#!/usr/bin/env bash
set -Eeuo pipefail

APP_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PREVIOUS_FILE="$APP_PATH/storage/framework/previous-deploy-sha"

cd "$APP_PATH"

if [[ ! -f "$PREVIOUS_FILE" ]]; then
    echo "No previous deployment SHA was recorded."
    exit 1
fi

TARGET_SHA="$(cat "$PREVIOUS_FILE")"
CURRENT_SHA="$(git rev-parse HEAD)"

if ! git cat-file -e "$TARGET_SHA^{commit}" 2>/dev/null; then
    echo "Recorded rollback commit does not exist locally: $TARGET_SHA"
    exit 1
fi

echo "$CURRENT_SHA" > storage/framework/previous-deploy-sha
php artisan down --retry=15

git reset --hard "$TARGET_SHA"

composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction \
    --no-progress

if command -v npm >/dev/null 2>&1; then
    npm ci --no-audit --no-fund
    npm run build
fi

php artisan optimize:clear
php artisan optimize
php artisan queue:restart
php artisan up

echo "Rolled back from $CURRENT_SHA to $TARGET_SHA"
echo "Database migrations are not rolled back automatically."
