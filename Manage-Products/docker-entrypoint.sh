#!/bin/sh
set -e

php /var/www/html/scripts/seed_demo_admin.php || echo "Admin seed skipped or failed (non-fatal)."

exec php -S "0.0.0.0:${PORT:-8080}" -t /var/www/html
