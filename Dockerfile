# ============================
# Étape 1 : Build front avec Node.js 20
# ============================
FROM node:20.19.0 AS build
WORKDIR /app

# Copier les fichiers nécessaires pour npm
COPY package*.json vite.config.* ./
RUN npm install

# Copier le reste des fichiers
COPY . .

# Builder le front
RUN npm run build

# ============================
# Étape 2 : Laravel + PHP 8.2 + SQLite
# ============================
FROM php:8.2-cli

# Installer dépendances système + SQLite + Composer
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip sqlite3 libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copier tout le projet Laravel
COPY . .

# Copier les fichiers du front compilé
COPY --from=build /app/public /var/www/html/public

# Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Générer .env et clé d’application
RUN cp .env.example .env && php artisan key:generate

# Créer la base SQLite
RUN mkdir -p database && touch database/database.sqlite

# Exposer le port
EXPOSE 8080

# Lancer migrations + serveur Laravel
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8080
