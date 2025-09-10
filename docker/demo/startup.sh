#!/bin/sh

echo "[$(date)] Demo cleanup service starting..."

INTERVAL=${DEMO_RESET_INTERVAL_MINUTES:-120}

echo "[$(date)] Reset interval: ${INTERVAL} minutes"

if [ "$INTERVAL" -le 0 ]; then
    echo "[$(date)] Error: Invalid interval ${INTERVAL}. Must be positive."
    exit 1
fi

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

exec crond -f -l 8 -L /dev/stdout
