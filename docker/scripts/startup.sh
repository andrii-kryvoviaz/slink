#!/bin/sh

set -euo pipefail

# Generate JWT keys
slink lexik:jwt:generate-keypair --skip-if-exists

if [ -f /services/api/.env ]; then
  export $(grep -v '^#' /services/api/.env | xargs)
fi

# Check if DATABASE_URL or ES_DATABASE_URL is set to SQLite
if [[ "$DATABASE_URL" == sqlite:* ]]; then
  echo "[Startup] Skipping creation of Projection Database as DATABASE_URL uses SQLite."
else
  # Create Projection Database
  slink doctrine:database:create --if-not-exists --no-interaction --env=prod --connection=read_model
fi

if [[ "$ES_DATABASE_URL" == sqlite:* ]]; then
  echo "[Startup] Skipping creation of Event Store Database as ES_DATABASE_URL uses SQLite."
else
  # Create Event Store Database
  slink doctrine:database:create --if-not-exists --no-interaction --env=prod --connection=event_store
fi

# Apply migrations
slink doctrine:migrations:migrate --no-interaction --configuration=/apps/api/config/migrations/event_store.yaml --em=event_store
slink doctrine:migrations:migrate --no-interaction --em=read_model