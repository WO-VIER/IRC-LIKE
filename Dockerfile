# Étape 1 : build front
FROM node:18 AS build
WORKDIR /app
COPY . .
RUN npm install
RUN npm run build

# Étape 2 : PHP + SQLite
FROM php:8.2-fpm

# Installe dépendances système et extensions PHP
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip sqlite3 libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite

# Installe Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copie le projet Laravel
COPY . .

# Copie le build front
COPY --from=build /app/public /var/www/html/public

# Installe dépendances Laravel
RUN composer install --no-dev --optimize-autoloader
RUN cp .env.example .env
RUN php artisan key:generate
RUN mkdir -p database && touch database/database.sqlite
RUN chown -R www-data:www-data /var/www/html

EXPOSE 8080

# Lancer les migrations et servir l'app
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8080
