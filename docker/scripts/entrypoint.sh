#!/bin/sh

set -euo pipefail

slink slink:about

/usr/local/bin/startup.sh
exec /usr/bin/supervisord -n -c /etc/supervisord.conf
