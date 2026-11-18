#!/bin/sh
set -eu

# ViceCodes cPanel Terminal Update Helper
# Usage:
#   sh bin/update.sh          # Pull latest GitHub code, run safe migrations, clear cache
#   sh bin/update.sh --seed   # Also apply idempotent seed updates (keeps live settings)

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT_DIR"

echo "[ViceCodes Update] Working directory: $ROOT_DIR"

if [ ! -f ".env" ]; then
  echo "[WARNING] .env file not found in $ROOT_DIR!"
  echo "          Copy .env.production.example to .env and enter your cPanel DB credentials."
fi

if [ -d ".git" ]; then
  echo "[1/4] Pulling latest commits from origin/main..."
  git pull --ff-only origin main
else
  echo "[1/4] Not a git checkout; skipping git pull."
fi

echo "[2/4] Ensuring runtime directories and permissions..."
mkdir -p storage/cache storage/logs/rate_limits public/og-cache public/uploads/editor
chmod -R 755 storage public/og-cache public/uploads

PHP_BIN="$(command -v php || true)"
for candidate in /opt/cpanel/ea-php83/root/usr/bin/php /opt/cpanel/ea-php82/root/usr/bin/php /usr/local/bin/php; do
  if [ -x "$candidate" ]; then
    PHP_BIN="$candidate"
    break
  fi
done

if [ -n "$PHP_BIN" ] && [ -f ".env" ]; then
  echo "[3/4] Running database migrations via $PHP_BIN..."
  if [ "${1:-}" = "--seed" ]; then
    "$PHP_BIN" bin/migrate.php --seed
  else
    "$PHP_BIN" bin/migrate.php
  fi
else
  echo "[3/4] Skipping php bin/migrate.php (missing PHP CLI or .env)."
fi

echo "[4/4] Flushing cached HTML and JSON responses..."
rm -f storage/cache/*.html storage/cache/*.json || true

echo "[DONE] ViceCodes is updated and live!"
