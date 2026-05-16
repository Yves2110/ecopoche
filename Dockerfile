FROM richarvey/nginx-php-fpm:latest

# Étape 1 : Installer les dépendances système nécessaires pour l'extension intl et Node
RUN apk update && apk add --no-cache \
    icu-dev \
    libstdc++ \
    build-base \
    nodejs \
    npm

# Étape 2 : Configurer et installer l'extension PHP intl de manière stable
RUN docker-php-ext-configure intl && docker-php-ext-install intl

# Étape 3 : Copier l'application
COPY . /var/www/html

# Étape 4 : Configurations d'environnement indispensables
ENV WEBROOT /var/www/html/public
ENV COMPOSER_ALLOW_SUPERUSER 1

# Étape 5 : Demander à l'image d'exécuter les migrations au démarrage
ENV RUN_MIGRATIONS true

# Étape 6 : Donner les droits d'écriture à Laravel sur les dossiers de stockage
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Étape 7 : Installer les dépendances PHP/JS et compiler les assets
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build
