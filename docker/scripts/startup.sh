#!/bin/sh

set -euo pipefail

generate_app_secret_if_missing() {
  local env_file="/services/api/.env"
  
  if [ ! -f "$env_file" ]; then
    echo "[Startup] .env file not found at $env_file"
    return
  fi
  
  if grep -q "^APP_SECRET=" "$env_file"; then
    echo "[Startup] APP_SECRET already configured"
    return
  fi
  
  echo "[Startup] Generating APP_SECRET..."
  local secret=$(openssl rand -hex 32)
  
  if grep -q "^###> App Settings ###$" "$env_file"; then
    sed -i "/^###> App Settings ###$/a APP_SECRET=$secret" "$env_file"
    echo "[Startup] APP_SECRET added to App Settings section"
  else
    echo "APP_SECRET=$secret" >> "$env_file"
    echo "[Startup] APP_SECRET appended to .env file"
  fi
  
  echo "[Startup] APP_SECRET successfully configured"
}

generate_app_secret_if_missing

slink lexik:jwt:generate-keypair --skip-if-exists

if [ -f /services/api/.env ]; then
  export $(grep -v '^#' /services/api/.env | xargs)
fi

if [[ "$DATABASE_URL" == sqlite:* ]]; then
  echo "[Startup] Skipping creation of Projection Database as DATABASE_URL uses SQLite."
else
  slink doctrine:database:create --if-not-exists --no-interaction --env=prod --connection=read_model
fi

if [[ "$ES_DATABASE_URL" == sqlite:* ]]; then
  echo "[Startup] Skipping creation of Event Store Database as ES_DATABASE_URL uses SQLite."
else
  slink doctrine:database:create --if-not-exists --no-interaction --env=prod --connection=event_store
fi

slink doctrine:migrations:migrate --no-interaction --configuration=/services/api/config/migrations/event_store.yaml --em=event_store
slink doctrine:migrations:migrate --no-interaction --em=read_model

if [ "${SKIP_HASH_CALCULATION:-false}" != "true" ]; then
  echo "[Startup] Calculating missing SHA-1 hashes for existing images..."
  timeout 300 slink image:calculate-missing-hashes --no-interaction || echo "[Warning] Hash calculation timed out or failed, but continuing startup..."
else
  echo "[Startup] Skipping hash calculation (SKIP_HASH_CALCULATION=true)"
fi
