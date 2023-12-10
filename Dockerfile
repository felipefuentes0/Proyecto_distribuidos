FROM php:8.2-fpm
WORKDIR /var/www
COPY . /var/www
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader
EXPOSE 8000

ENV NAME juegazo
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]