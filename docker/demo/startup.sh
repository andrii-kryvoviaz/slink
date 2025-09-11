#!/bin/sh

set -euo pipefail

echo "[$(date)] Demo initialization service starting..."

echo "[$(date)] Waiting for main container to be ready..."
timeout=120
while [ $timeout -gt 0 ]; do
  if [ "$(docker inspect -f '{{.State.Running}}' slink-demo 2>/dev/null)" = "true" ]; then
    if nc -z slink-demo 3000 > /dev/null 2>&1; then
      echo "[$(date)] Container port is accessible, checking health..."
      if docker exec slink-demo curl -f http://slink-demo:8080/api/health > /dev/null 2>&1; then
        echo "[$(date)] Container is healthy!"
        break
      fi
    fi
  fi
  sleep 2
  timeout=$((timeout - 2))
done

if [ $timeout -gt 0 ]; then
  echo "[$(date)] Initializing demo environment..."
  docker exec slink-demo slink slink:demo:init
  echo "[$(date)] Seeding demo data..."
  docker exec slink-demo slink slink:demo:seed
  
  if [ -n "$DEMO_EMAIL" ]; then
    echo "[$(date)] Granting admin role to ${DEMO_EMAIL}..."
    docker exec slink-demo slink user:grant:role --email=$DEMO_EMAIL ROLE_ADMIN
  else
    echo "[$(date)] No admin email configured (DEMO_EMAIL)"
  fi
  
  echo "[$(date)] Demo initialization completed successfully!"
else
  echo "[$(date)] Warning: Could not initialize demo environment - container not ready"
fi
