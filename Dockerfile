FROM php:8.3-apache

# pdo_sqlite, fileinfo, iconv, mbstring ship with the official image; add MySQL support
# so the same image also works against an external MySQL database.
RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite headers \
    && echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername

COPY . /var/www/html/

# Keep setup files in /opt/vmgt while also keeping sql schema accessible.
RUN mkdir -p /opt/vmgt/seed \
    && cp -r /var/www/html/bin /opt/vmgt/bin \
    && cp -r /var/www/html/sql /opt/vmgt/sql \
    && cp -r /var/www/html/docker /opt/vmgt/docker \
    && cp -r /var/www/html/uploads/celebrities /opt/vmgt/seed/celebrities \
    && rm -rf /var/www/html/install /var/www/html/uploads /var/www/html/storage \
              /var/www/html/Dockerfile /var/www/html/render.yaml /var/www/html/.dockerignore \
              /var/www/html/README.md \
    && cp /opt/vmgt/docker/php.ini "$PHP_INI_DIR/conf.d/zz-vmgt.ini" \
    && chmod +x /opt/vmgt/docker/entrypoint.sh \
    && chown -R www-data:www-data /var/www/html /opt/vmgt

ENV APP_ROOT=/var/www/html \
    BOOTSTRAP_SQL_DIR=/opt/vmgt/sql \
    DB_DRIVER=sqlite \
    DATA_DIR=/var/data \
    TZ=Africa/Lagos

EXPOSE 10000
CMD ["/opt/vmgt/docker/entrypoint.sh"]
