#!/bin/sh

set -euo pipefail

/usr/local/bin/startup.sh

slink slink:about
exec /usr/bin/supervisord -n -c /etc/supervisord.conf
