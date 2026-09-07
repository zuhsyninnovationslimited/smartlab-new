#!/usr/bin/env sh
set -eu
cd "$(dirname "$0")/.."
[ -f .env ] || cp .env.example .env
app_key="base64:$(openssl rand -base64 32)"
shared_secret="$(openssl rand -hex 32)"
if sed --version >/dev/null 2>&1; then
  sed -i "s|^APP_KEY=.*|APP_KEY=$app_key|" .env
  sed -i "s|^SMART_LAB_API_KEY=.*|SMART_LAB_API_KEY=$shared_secret|" .env
else
  sed -i '' "s|^APP_KEY=.*|APP_KEY=$app_key|" .env
  sed -i '' "s|^SMART_LAB_API_KEY=.*|SMART_LAB_API_KEY=$shared_secret|" .env
fi
docker compose up --build
