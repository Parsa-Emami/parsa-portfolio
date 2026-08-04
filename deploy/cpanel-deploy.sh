#!/usr/bin/env bash
set -Eeuo pipefail

APP_PATH="${1:-$PWD}"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"

cd "$APP_PATH"

"$PHP_BIN" artisan down --retry=15
trap '"$PHP_BIN" artisan up || true' EXIT

"$PHP_BIN" artisan portfolio:backup --database-only

"$COMPOSER_BIN" install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction \
    --no-progress

if command -v npm >/dev/null 2>&1; then
    npm ci --no-audit --no-fund
    npm run build
elif [[ ! -f public/build/manifest.json ]]; then
    echo "Upload a locally built public/build directory before deploying."
    exit 1
fi

"$PHP_BIN" artisan migrate --force
"$PHP_BIN" artisan storage:link || true
"$PHP_BIN" artisan optimize:clear
"$PHP_BIN" artisan optimize
"$PHP_BIN" artisan queue:restart

mkdir -p storage/framework
cat > storage/framework/deployment.json <<JSON
{
  "commit": "$(git rev-parse HEAD 2>/dev/null || echo unknown)",
  "branch": "$(git branch --show-current 2>/dev/null || echo cpanel)",
  "deployed_at": "$(date -u +"%Y-%m-%dT%H:%M:%SZ")"
}
JSON

"$PHP_BIN" artisan portfolio:production-check --strict
"$PHP_BIN" artisan up

trap - EXIT
echo "cPanel deployment completed."
