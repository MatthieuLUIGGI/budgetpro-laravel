# Étape 1 : Build des assets frontend
FROM node:18 AS build-frontend

WORKDIR /app
COPY package*.json vite.config.js ./
COPY resources ./resources
COPY public ./public

RUN npm install && npm run build

# Étape 2 : Environnement PHP/Laravel
FROM php:8.2-fpm

# Installer extensions PHP nécessaires à Laravel et PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo pdo_pgsql

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copier les fichiers Laravel
COPY . .

# Copier les assets buildés
COPY --from=build-frontend /app/public/build ./public/build

# Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Exposer le port
EXPOSE 8000

# Commande de démarrage
CMD php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan serve --host=0.0.0.0 --port=8000
