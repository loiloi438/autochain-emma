FROM php:8.3-cli
WORKDIR /app
COPY . .

# If the build context is the repo root, the Laravel app lives in /app/backend.
# If the build context is already the backend folder, composer.json will already be in /app.
RUN if [ ! -f composer.json ] && [ -f backend/composer.json ]; then \
    cp backend/composer.json backend/composer.lock . && \
    cp -r backend/* .; \
  fi

# System deps for composer & pgsql
RUN apt-get update \
    && apt-get install -y unzip libpq-dev git curl libzip-dev libicu-dev libonig-dev zlib1g-dev libxml2-dev \
    && docker-php-ext-configure zip --with-libzip \
    && docker-php-ext-install pdo pdo_pgsql mbstring zip bcmath xml intl

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PHP deps
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Expose port (Render will assign $PORT at runtime)
EXPOSE 8000

CMD ["sh", "-lc", "php artisan serve --host=0.0.0.0 --port=$PORT"]
