# === ÉTAPE 1 : Compilation des assets JS/CSS avec Node 22 ===
FROM node:22-alpine AS node-builder
WORKDIR /app
COPY . .
RUN npm install && npm run build

# === ÉTAPE 2 : Configuration du serveur PHP-FPM / Nginx ===
FROM richarvey/nginx-php-fpm:latest

# Désactiver explicitement les scripts d'installation de plugins obsolètes au démarrage
ENV COMPOSER_PLUGINS_AUTOLOAD=0

# Installer les dépendances système pour intl
RUN apk update && apk add --no-cache icu-dev

# Configurer et installer l'extension PHP intl
RUN docker-php-ext-configure intl && docker-php-ext-install intl

# Copier le code de l'application
COPY . /var/www/html

# Récupérer les assets CSS/JS compilés à l'Étape 1
COPY --from=node-builder /app/public/build /var/www/html/public/build

# Configurations d'environnement indispensables
ENV WEBROOT /var/www/html/public
ENV COMPOSER_ALLOW_SUPERUSER 1

# Demander à l'image d'exécuter les migrations au démarrage
ENV RUN_MIGRATIONS true

# Ajuster les droits d'écriture pour Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Installer les dépendances PHP uniquement
RUN composer install --no-dev --optimize-autoloader
