#!/bin/sh
set -e

timeout 30 php /var/www/html/scripts/seed_demo_admin.php || echo "Admin seed skipped or failed (non-fatal)."

exec php -S "0.0.0.0:${PORT:-8081}" -t /var/www/html
