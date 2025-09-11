#!/bin/sh

set -euo pipefail

echo "[$(date)] Demo cleanup service starting..."

INTERVAL=${DEMO_RESET_INTERVAL_MINUTES:-120}

echo "[$(date)] Reset interval: ${INTERVAL} minutes"

if [ "$INTERVAL" -le 0 ]; then
    echo "[$(date)] Error: Invalid interval ${INTERVAL}. Must be positive."
    exit 1
fi

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
  
  if [ -n "$DEMO_EMAIL" ]; then
    echo "[$(date)] Granting admin role to ${DEMO_EMAIL}..."
    docker exec slink-demo slink user:grant:role --email=$DEMO_EMAIL ROLE_ADMIN
  else
    echo "[$(date)] No admin email configured (DEMO_EMAIL)"
  fi
else
  echo "[$(date)] Warning: Could not initialize demo environment - container not ready"
fi

echo "[$(date)] Configuring cleanup schedule..."

if [ "$INTERVAL" -lt 60 ]; then
    CRON_EXPR="*/${INTERVAL} * * * *"
    echo "[$(date)] Configuring cron: every ${INTERVAL} minutes"
elif [ $((INTERVAL % 60)) -eq 0 ]; then
    HOURS=$((INTERVAL / 60))
    if [ "$HOURS" -le 23 ]; then
        CRON_EXPR="0 */${HOURS} * * *"
        echo "[$(date)] Configuring cron: every ${HOURS} hours"
    elif [ "$HOURS" -eq 24 ]; then
        CRON_EXPR="0 2 * * *"
        echo "[$(date)] Configuring cron: daily at 2:00 AM"
    else
        DAYS=$((HOURS / 24))
        if [ "$DAYS" -le 7 ] && [ $((HOURS % 24)) -eq 0 ]; then
            CRON_EXPR="0 2 */${DAYS} * *"
            echo "[$(date)] Configuring cron: every ${DAYS} days at 2:00 AM"
        else
            echo "[$(date)] Warning: Interval ${INTERVAL} minutes too large. Using daily."
            CRON_EXPR="0 2 * * *"
            echo "[$(date)] Configuring cron: daily at 2:00 AM (fallback)"
        fi
    fi
else
    echo "[$(date)] Warning: ${INTERVAL} minutes not evenly divisible by 60. Using minute-based cron."
    CRON_EXPR="*/${INTERVAL} * * * *"
    echo "[$(date)] Configuring cron: every ${INTERVAL} minutes"
fi

echo "${CRON_EXPR} cleanup" | crontab -

echo "[$(date)] Cron job configured with expression: ${CRON_EXPR}"
echo "[$(date)] Available commands: cleanup, startup"
echo "[$(date)] Starting cron daemon..."

if ! crond -f -l 8 -L /dev/stdout 2>/dev/null; then
    echo "[$(date)] Standard crond failed, trying with -S option..."
    exec crond -f -S -l 8 -L /dev/stdout
fi
