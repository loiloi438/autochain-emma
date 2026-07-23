FROM php:8.3-cli
WORKDIR /app
COPY . .

# System deps for composer & pgsql
RUN apt-get update \
    && apt-get install -y unzip libpq-dev git curl \
    && docker-php-ext-install pdo pdo_pgsql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PHP deps
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Expose port (Render will assign $PORT at runtime)
EXPOSE 8000

CMD ["php", "artisan", "serve", "--host", "0.0.0.0", "--port", "${PORT}"]
