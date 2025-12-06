#!/bin/sh

set -euo pipefail

setup_jwt_keys() {
  local persistent_keys_dir="/app/var/data/keys"
  local config_jwt_dir="/services/api/config/jwt"
  local env_file="/services/api/.env"
  
  mkdir -p "$persistent_keys_dir"
  mkdir -p "$config_jwt_dir"
  
  if [ ! -f "$persistent_keys_dir/private.pem" ] || [ ! -f "$persistent_keys_dir/public.pem" ]; then
    echo "[Startup] Generating new JWT keypair..."
    
    local passphrase=$(openssl rand -hex 32)
    echo "$passphrase" > "$persistent_keys_dir/passphrase"
    chmod 600 "$persistent_keys_dir/passphrase"
    
    openssl genpkey -algorithm RSA -out "$persistent_keys_dir/private.pem" -pkeyopt rsa_keygen_bits:4096 -aes256 -pass pass:"$passphrase" 2>/dev/null
    openssl rsa -pubout -in "$persistent_keys_dir/private.pem" -out "$persistent_keys_dir/public.pem" -passin pass:"$passphrase" 2>/dev/null
    
    chmod 600 "$persistent_keys_dir/private.pem"
    chmod 644 "$persistent_keys_dir/public.pem"
    
    echo "[Startup] JWT keypair generated"
  fi
  
  ln -sf "$persistent_keys_dir/private.pem" "$config_jwt_dir/private.pem"
  ln -sf "$persistent_keys_dir/public.pem" "$config_jwt_dir/public.pem"
  
  if [ -f "$env_file" ]; then
    local passphrase=$(cat "$persistent_keys_dir/passphrase")
    sed -i "s|^JWT_PASSPHRASE=.*|JWT_PASSPHRASE=$passphrase|" "$env_file"
  fi
}

setup_mercure_keys() {
  local persistent_keys_dir="/app/var/data/keys"
  local api_env_file="/services/api/.env"
  
  if [ ! -f "$persistent_keys_dir/mercure_secret" ]; then
    openssl rand -hex 32 > "$persistent_keys_dir/mercure_secret"
    chmod 600 "$persistent_keys_dir/mercure_secret"
    echo "[Startup] Generated Mercure JWT secret"
  fi
  
  local mercure_secret=$(cat "$persistent_keys_dir/mercure_secret")
  export MERCURE_JWT_SECRET="$mercure_secret"
  
  local mercure_hub_url="http://localhost:3333/.well-known/mercure"
  
  export MERCURE_HUB_URL="$mercure_hub_url"
  
  if [ -f "$api_env_file" ]; then
    if grep -q "^MERCURE_JWT_SECRET=" "$api_env_file"; then
      sed -i "s|^MERCURE_JWT_SECRET=.*|MERCURE_JWT_SECRET=$mercure_secret|" "$api_env_file"
    else
      echo "MERCURE_JWT_SECRET=$mercure_secret" >> "$api_env_file"
    fi
    
    if grep -q "^MERCURE_HUB_URL=" "$api_env_file"; then
      sed -i "s|^MERCURE_HUB_URL=.*|MERCURE_HUB_URL=$mercure_hub_url|" "$api_env_file"
    else
      echo "MERCURE_HUB_URL=$mercure_hub_url" >> "$api_env_file"
    fi
  fi
  
  echo "[Startup] Mercure configured with JWT secret and URLs"
}

generate_app_secret_from_jwt() {
  local env_file="/services/api/.env"
  local jwt_private_key="/app/var/data/keys/private.pem"
  
  if [ ! -f "$env_file" ]; then
    echo "[Startup] .env file not found at $env_file"
    return
  fi
  
  if [ ! -f "$jwt_private_key" ]; then
    echo "[Startup] JWT private key not found, cannot generate APP_SECRET"
    return
  fi
  
  local secret=$(openssl dgst -sha256 -hex "$jwt_private_key" | awk '{print $2}')
  
  if grep -q "^APP_SECRET=" "$env_file"; then
    sed -i "s|^APP_SECRET=.*|APP_SECRET=$secret|" "$env_file"
  else
    if grep -q "^###> App Settings ###$" "$env_file"; then
      sed -i "/^###> App Settings ###$/a APP_SECRET=$secret" "$env_file"
    else
      echo "APP_SECRET=$secret" >> "$env_file"
    fi
  fi
}

setup_jwt_keys
setup_mercure_keys
generate_app_secret_from_jwt

if [ -f /services/api/.env ]; then
  export $(grep -v '^#' /services/api/.env | xargs)
fi

if [[ "$DATABASE_URL" != sqlite:* ]]; then
  slink doctrine:database:create --if-not-exists --no-interaction --env=prod --connection=read_model
fi

if [[ "$ES_DATABASE_URL" != sqlite:* ]]; then
  slink doctrine:database:create --if-not-exists --no-interaction --env=prod --connection=event_store
fi

slink doctrine:migrations:migrate --no-interaction --configuration=/services/api/config/migrations/event_store.yaml --em=event_store
slink doctrine:migrations:migrate --no-interaction --em=read_model

if [ "${SKIP_HASH_CALCULATION:-false}" != "true" ]; then
  timeout 300 slink image:calculate-missing-hashes --no-interaction || echo "[Warning] Hash calculation timed out or failed"
fi
