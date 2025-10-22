#!/bin/sh

set -euo pipefail

echo "[Entrypoint] Running startup initialization..."
/usr/local/bin/startup.sh

slink slink:about

echo "[Entrypoint] Starting supervisord..."
exec /usr/bin/supervisord -n -c /etc/supervisord.conf
