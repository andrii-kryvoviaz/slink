#!/bin/sh

echo "[$(date)] Starting cleanup process..."

echo "[$(date)] Stopping slink container..."
docker stop slink-demo || true

echo "[$(date)] Waiting for container to stop..."
timeout=30
while [ $timeout -gt 0 ]; do
  if [ "$(docker inspect -f '{{.State.Running}}' slink-demo 2>/dev/null)" != "true" ]; then
    echo "[$(date)] Container has stopped"
    break
  fi
  sleep 1
  timeout=$((timeout - 1))
done

if [ $timeout -le 0 ]; then
  echo "[$(date)] Warning: Container may still be running, proceeding with cleanup anyway"
fi

echo "[$(date)] Cleaning databases..."
rm -f /cleanup/data/*.db /cleanup/data/*.rdb

echo "[$(date)] Cleaning images..."
rm -rf /cleanup/images/*

echo "[$(date)] Cleaning cache..."
rm -rf /cleanup/cache/*

echo "[$(date)] Cleaning JWT keys..."
rm -f /cleanup/jwt/*.pem

echo "[$(date)] Cleanup completed, starting container..."

echo "[$(date)] Starting slink container..."
docker start slink-demo

echo "[$(date)] Waiting for container to be ready..."
timeout=60
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

if [ $timeout -le 0 ]; then
  echo "[$(date)] Warning: Container may not be healthy after restart"
else
  echo "[$(date)] Initializing demo environment..."
  docker exec slink-demo slink slink:demo:init
  
  if [ -n "$DEMO_EMAIL" ]; then
    echo "[$(date)] Granting admin role to ${DEMO_EMAIL}..."
    docker exec slink-demo slink user:grant:role --email=$DEMO_EMAIL ROLE_ADMIN
  else
    echo "[$(date)] No admin email configured (DEMO_EMAIL)"
  fi
fi

echo "[$(date)] Cleanup process completed!"
echo "[$(date)] Next cleanup will run according to configured interval"
