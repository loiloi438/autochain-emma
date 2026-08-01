FROM php:8.2-fpm
WORKDIR /app

# Build context is the repo root, so copy the backend app explicitly.
COPY backend/. ./

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
