#!/bin/sh

set -euo pipefail

setup_jwt_keys() {
  local persistent_jwt_dir="/app/var/data/jwt"
  local config_jwt_dir="/services/api/config/jwt"
  
  mkdir -p "$persistent_jwt_dir"
  mkdir -p "$config_jwt_dir"
  
  if [ ! -f "$persistent_jwt_dir/private.pem" ] || [ ! -f "$persistent_jwt_dir/public.pem" ]; then
    echo "[Startup] Generating new JWT keypair..."
    
    openssl genpkey -algorithm RSA -out "$persistent_jwt_dir/private.pem" -pkeyopt rsa_keygen_bits:4096
    openssl rsa -pubout -in "$persistent_jwt_dir/private.pem" -out "$persistent_jwt_dir/public.pem"
    
    chmod 600 "$persistent_jwt_dir/private.pem"
    chmod 644 "$persistent_jwt_dir/public.pem"
    
    echo "[Startup] JWT keypair generated and stored in persistent storage"
  else
    echo "[Startup] Using existing JWT keypair from persistent storage"
  fi
  
  ln -sf "$persistent_jwt_dir/private.pem" "$config_jwt_dir/private.pem"
  ln -sf "$persistent_jwt_dir/public.pem" "$config_jwt_dir/public.pem"
  
  echo "[Startup] JWT configuration updated"
}

generate_app_secret_from_jwt() {
  local env_file="/services/api/.env"
  local jwt_private_key="/app/var/data/jwt/private.pem"
  
  if [ ! -f "$env_file" ]; then
    echo "[Startup] .env file not found at $env_file"
    return
  fi
  
  if [ ! -f "$jwt_private_key" ]; then
    echo "[Startup] JWT private key not found, cannot generate APP_SECRET"
    return
  fi
  
  echo "[Startup] Deriving APP_SECRET from JWT private key..."
  local secret=$(openssl dgst -sha256 -hex "$jwt_private_key" | awk '{print $2}')
  
  if grep -q "^APP_SECRET=" "$env_file"; then
    sed -i "s|^APP_SECRET=.*|APP_SECRET=$secret|" "$env_file"
    echo "[Startup] APP_SECRET updated in .env file"
  else
    if grep -q "^###> App Settings ###$" "$env_file"; then
      sed -i "/^###> App Settings ###$/a APP_SECRET=$secret" "$env_file"
      echo "[Startup] APP_SECRET added to App Settings section"
    else
      echo "APP_SECRET=$secret" >> "$env_file"
      echo "[Startup] APP_SECRET appended to .env file"
    fi
  fi
  
  echo "[Startup] APP_SECRET successfully configured"
}

setup_jwt_keys
generate_app_secret_from_jwt

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
