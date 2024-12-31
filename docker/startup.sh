#!/bin/sh

set -euo pipefail

if [ -f /app/.env ]; then
  export $(grep -v '^#' /app/.env | xargs)
fi

# Check if DATABASE_URL or ES_DATABASE_URL is set to SQLite
if [[ "$DATABASE_URL" == sqlite:* ]]; then
  echo "[Startup] Skipping creation of Projection Database as DATABASE_URL uses SQLite."
else
  # Create Projection Database
  /app/bin/console doctrine:database:create --if-not-exists --no-interaction --env=prod --connection=read_model
fi

if [[ "$ES_DATABASE_URL" == sqlite:* ]]; then
  echo "[Startup] Skipping creation of Event Store Database as ES_DATABASE_URL uses SQLite."
else
  # Create Event Store Database
  /app/bin/console doctrine:database:create --if-not-exists --no-interaction --env=prod --connection=event_store
fi

# Apply migrations
/app/bin/console doctrine:migrations:migrate --no-interaction --configuration=/app/config/migrations/event_store.yaml --em=event_store
/app/bin/console doctrine:migrations:migrate --no-interaction --em=read_model

# Generate JWT keys
/app/bin/console lexik:jwt:generate-keypair --skip-if-exists