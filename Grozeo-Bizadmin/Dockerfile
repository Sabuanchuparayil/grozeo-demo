FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd mysqli pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

COPY . /var/www/html/

# Use Railway config
RUN cp /var/www/html/includes/config.railway.php /var/www/html/includes/config.php

RUN mkdir -p /var/www/html/cache && chmod 777 /var/www/html/cache

WORKDIR /var/www/html

RUN chmod +x /var/www/html/docker-entrypoint.sh

CMD ["/var/www/html/docker-entrypoint.sh"]
