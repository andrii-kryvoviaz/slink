#!/bin/sh

set -euo pipefail

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
