FROM php:8.2-fpm
WORKDIR /app
COPY . .

# Normalize the Laravel app directory for Render builds.
# When the Docker build context is the repo root, the app is nested under /app/backend.
# When the build context is already the backend folder, composer.json is already at /app.
RUN if [ ! -f composer.json ] && [ -d backend ]; then \
    cp backend/composer.json backend/composer.lock . && \
    cp -r backend/* .; \
  fi

# Fail fast if the Laravel manifest is still missing.
RUN test -f composer.json

# System deps for composer & pgsql
ENV DEBIAN_FRONTEND=noninteractive
RUN set -eux \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        curl \
        unzip \
        libpq-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        zlib1g-dev \
        libxml2-dev \
        pkg-config \
        g++ \
        make \
        autoconf \
    && docker-php-ext-install -j$(nproc) pdo pdo_pgsql mbstring zip bcmath xml intl \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PHP deps
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Expose port (Render will assign $PORT at runtime)
EXPOSE 8000

CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=$PORT"]
