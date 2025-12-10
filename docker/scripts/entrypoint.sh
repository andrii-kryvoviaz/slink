#!/bin/sh

set -euo pipefail

/usr/local/bin/startup.sh

if [ -n "${ADMIN_EMAIL:-}" ]; then
  slink slink:admin:init --no-interaction
fi

slink slink:about
exec /usr/bin/supervisord -n -c /etc/supervisord.conf
