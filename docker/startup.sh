#!/bin/sh

# Create Projection Database
/app/bin/console doctrine:database:create --if-not-exists --no-interaction --env=prod --connection=read_model

# Create Event Store Database
/app/bin/console doctrine:database:create --if-not-exists --no-interaction --env=prod --connection=event_store

# Apply migrations
/app/bin/console doctrine:migrations:migrate --no-interaction --configuration=/app/config/migrations/event_store.yaml --em=event_store
/app/bin/console doctrine:migrations:migrate --no-interaction --em=read_model

# Generate JWT keys
/app/bin/console lexik:jwt:generate-keypair --skip-if-exists