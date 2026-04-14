FROM composer:2.7 AS composer-builder

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install --no-dev --no-scripts --prefer-dist --optimize-autoloader



FROM node:18-alpine AS node-builder  

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci

COPY . .

RUN npm run build 



FROM php:8.3-fpm-alpine AS production

RUN apk add --no-cache \
    linux-headers \
    $PHPIZE_DEPS \
    icu-dev \
    libzip-dev \
    mysql-client \
    oniguruma-dev \
    redis \
    supervisor \
    unzip \
    zip

RUN apk add --no-cache \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    mariadb-connector-c-dev \
    mysql-client

RUN docker-php-ext-install bcmath intl mbstring pdo_mysql zip opcache

RUN pecl install redis && docker-php-ext-enable redis

#opcache for faster performance
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini  
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

#creating a non-root user to run the application
RUN addgroup -g 1000 -S www && \
    adduser -u 1000 -D -S -G www www

WORKDIR /var/www/html

#copying application files, stages. changing ownership to  www user created above 
COPY --chown=www:www . .
COPY --chown=www:www --from=composer-builder /app/vendor ./vendor
COPY --chown=www:www --from=node-builder /app/public/build ./public/build

# ... (after copying files)

# Ensure Laravel storage structure exists and is writable
RUN mkdir -p /var/www/html/storage/framework/cache/data \
             /var/www/html/storage/framework/app/cache \
             /var/www/html/storage/framework/sessions \
             /var/www/html/storage/framework/views \
             /var/www/html/storage/logs \
             /var/www/html/bootstrap/cache && \
    chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# ... (then keep the rest of your log/pid fixes from before)

#changing ownership for web server user and group to access the application files
#extra safety check for chnging ownership 
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache && \
    chown -R www:www /var/www/html && \
    chmod -R 775 /var/www/html/storage && \
    chmod -R 775 /var/www/html/bootstrap/cache

#copy supervisor configuration for queue worker, php-fpm.
#allowing your single container to act like two machines (a Web Server and a Background Worker)
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

#also copying php-fpm configuration for running the application
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

COPY --chown=www:www docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER root

# 1. Create PHP-FPM and Supervisor log folders
# 2. Create the slow.log file so PHP-FPM starts correctly
# 3. Fix permissions for /var/run so Supervisor can write its PID file
RUN mkdir -p /var/log/supervisor /var/log/php-fpm && \
    touch /var/log/php-fpm/slow.log && \
    chown -R www:www /var/log/supervisor /var/log/php-fpm /var/run && \
    chmod -R 775 /var/run

#switching to non-root user from here to the rest of the code
USER www
EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

# Add this to your production stage in the Dockerfile
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer
