#!/bin/sh

# Create Projection Database
/app/bin/console doctrine:database:create --if-not-exists --no-interaction --env=prod --connection=read_model

# Create Event Store Database
/app/bin/console doctrine:database:create --if-not-exists --no-interaction --env=prod --connection=event_store

# Apply migrations
/app/bin/console doctrine:migrations:migrate --no-interaction