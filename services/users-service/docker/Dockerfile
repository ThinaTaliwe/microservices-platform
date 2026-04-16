FROM php:8.3-cli

# System deps + PHP extensions needed by Laravel + Postgres
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Default dev command (simple)
CMD ["sh", "-lc", "php -v && php -S 0.0.0.0:8000 -t public"]
