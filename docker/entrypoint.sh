#!/bin/sh
# Container start-up: prepare persistent folders, configure Apache for Render's $PORT,
# run the idempotent bootstrap (schema + first admin), then start Apache.
set -e

PORT="${PORT:-10000}"
DATA_DIR="${DATA_DIR:-/var/data}"
UPLOADS_PATH="${UPLOADS_PATH:-$DATA_DIR/uploads}"
export DATA_DIR UPLOADS_PATH

mkdir -p "$DATA_DIR" "$UPLOADS_PATH/celebrities" "$UPLOADS_PATH/blog"

# Copy the original celebrity photos onto the disk once (first boot only).
if [ ! -f "$DATA_DIR/.seeded-uploads" ]; then
    cp -rn /opt/vmgt/seed/celebrities/. "$UPLOADS_PATH/celebrities/" 2>/dev/null || true
    touch "$DATA_DIR/.seeded-uploads"
fi

chown -R www-data:www-data "$DATA_DIR" "$UPLOADS_PATH" /opt/vmgt 2>/dev/null || true
export BOOTSTRAP_SQL_DIR="${BOOTSTRAP_SQL_DIR:-/opt/vmgt/sql}"

# Apache: listen on $PORT, serve /uploads from the persistent folder with scripts disabled.
echo "Listen ${PORT}" > /etc/apache2/ports.conf
cat > /etc/apache2/sites-available/000-default.conf <<APACHE
<VirtualHost *:${PORT}>
    DocumentRoot /var/www/html
    <Directory /var/www/html>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    Alias /uploads ${UPLOADS_PATH}
    <Directory ${UPLOADS_PATH}>
        Options -Indexes -ExecCGI
        AllowOverride None
        Require all granted
        php_admin_flag engine off
        <FilesMatch "\.(php|phtml|phar|pl|py|cgi|sh)\$">
            Require all denied
        </FilesMatch>
    </Directory>

    ErrorLog /proc/self/fd/2
    CustomLog /proc/self/fd/1 combined
</VirtualHost>
APACHE

# Bootstrap as the web user so the SQLite file is owned correctly.
runuser -u www-data -- env BOOTSTRAP_SQL_DIR="$BOOTSTRAP_SQL_DIR" APP_ROOT="${APP_ROOT:-/var/www/html}" php /opt/vmgt/bin/bootstrap.php

exec apache2-foreground
