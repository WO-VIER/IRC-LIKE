FROM php:8.2-cli

# Installer dépendances système + Node + extensions PHP SQLite
RUN apt-get update && apt-get install -y \
    curl gnupg2 libzip-dev zip unzip sqlite3 libsqlite3-dev \
    nano openssh-client \
    && docker-php-ext-install pdo pdo_sqlite \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copier projet dans l'image
COPY . .

# Installer dépendances Laravel + Vite
RUN composer install --no-dev --optimize-autoloader
RUN npm install
RUN npm run build

# Configurer Laravel
RUN cp .env.example .env && php artisan key:generate

# Base SQLite pour migrations
RUN mkdir -p database && touch database/database.sqlite

EXPOSE 8080

# Démarrer Laravel
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8080
