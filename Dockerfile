# ============================
# Étape unique : PHP 8.2 + Node 20
# ============================
FROM php:8.2-cli

# Installer dépendances système + SQLite + npm + Node
RUN apt-get update && apt-get install -y \
    curl gnupg2 libzip-dev zip unzip sqlite3 libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copier tout le projet
COPY . .

# Installer dépendances PHP et Node
RUN composer install --no-dev --optimize-autoloader
RUN npm install

# Générer .env et clé
RUN cp .env.example .env && php artisan key:generate

# Builder les assets front
RUN npm run build

# Créer la base SQLite
RUN mkdir -p database && touch database/database.sqlite

# Exposer le port
EXPOSE 8080

# Lancer migrations + serveur
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8080
