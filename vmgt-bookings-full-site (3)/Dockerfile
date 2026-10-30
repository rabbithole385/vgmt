FROM php:8.3-apache

# pdo_sqlite, fileinfo, iconv, mbstring ship with the official image; add MySQL support
# so the same image also works against an external MySQL database.
RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite headers \
    && echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername

COPY . /var/www/html/

# Keep setup files out of the web root; leave only the public app in /var/www/html.
RUN mkdir -p /opt/vmgt/seed \
    && mv /var/www/html/bin /opt/vmgt/bin \
    && mv /var/www/html/sql /opt/vmgt/sql \
    && mv /var/www/html/docker /opt/vmgt/docker \
    && mv /var/www/html/uploads/celebrities /opt/vmgt/seed/celebrities \
    && rm -rf /var/www/html/install /var/www/html/uploads /var/www/html/storage \
              /var/www/html/Dockerfile /var/www/html/render.yaml /var/www/html/.dockerignore \
              /var/www/html/README.md \
    && cp /opt/vmgt/docker/php.ini "$PHP_INI_DIR/conf.d/zz-vmgt.ini" \
    && chmod +x /opt/vmgt/docker/entrypoint.sh \
    && chown -R www-data:www-data /var/www/html

ENV APP_ROOT=/var/www/html \
    DB_DRIVER=sqlite \
    DATA_DIR=/var/data \
    TZ=Africa/Lagos

EXPOSE 10000
CMD ["/opt/vmgt/docker/entrypoint.sh"]
