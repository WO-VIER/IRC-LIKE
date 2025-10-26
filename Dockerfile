FROM php:8.2-cli

# Install necessary extensions
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy application files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose the port Laravel will run on
EXPOSE 8080

# App key (if not provided by DO)
RUN php artisan key:generate --force || true

# Migrate database if exists (won’t break if not)
RUN mkdir -p /app/database && \
    touch /app/database/database.sqlite && \
    php artisan migrate --force || true

# Run the server
CMD php artisan serve --host=0.0.0.0 --port=8080
