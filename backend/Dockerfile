FROM php:8.2-fpm
WORKDIR /app

# Render build context is the backend folder itself.
COPY . .

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
RUN if [ -f composer.json ]; then \
        composer install --no-dev --optimize-autoloader --no-interaction --no-progress; \
    elif [ -f backend/composer.json ]; then \
        cd backend && composer install --no-dev --optimize-autoloader --no-interaction --no-progress; \
    else \
        echo "composer.json not found in build context" >&2; \
        exit 1; \
    fi

# Expose port (Render will assign $PORT at runtime)
EXPOSE 8000

CMD ["sh", "-c", "if [ -f artisan ]; then php artisan serve --host=0.0.0.0 --port=$PORT; else cd backend && php artisan serve --host=0.0.0.0 --port=$PORT; fi"]
