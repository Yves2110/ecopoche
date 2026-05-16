# === ÉTAPE 1 : Compilation des assets JS/CSS avec Node 22 ===
FROM node:22-alpine AS node-builder
WORKDIR /app

# Copie des fichiers de dépendances et de configuration
# Assure-toi que tailwind.config.js existe bien à la racine de ton dépôt
COPY package*.json vite.config.js tailwind.config.js postcss.config.js ./
# Copie aussi les dossiers de ressources nécessaires à Vite
COPY resources/ ./resources/

RUN npm ci && npm run build


# === ÉTAPE 2 : Configuration du serveur PHP-FPM / Nginx ===
FROM richarvey/nginx-php-fpm:latest

# Installation de l'extension intl pour PHP (adaptée à l'image Alpine)
# On détecte dynamiquement la version de PHP installée pour utiliser le bon paquet
RUN apk add --no-cache icu-dev \
    && apk add --no-cache $(php -r 'echo "php" . PHP_MAJOR_VERSION . PHP_MINOR_VERSION . "-intl";')

# Copie du code applicatif
COPY . /var/www/html

# Récupération des assets compilés depuis l'étape 1
COPY --from=node-builder /app/public/build /var/www/html/public/build

# Variables d'environnement essentielles
ENV WEBROOT /var/www/html/public
ENV COMPOSER_ALLOW_SUPERUSER 1

# Exécution des migrations automatique au démarrage (désactiver si non souhaité)
ENV RUN_MIGRATIONS true

# Installation des dépendances PHP (en premier pour que le cache soit prêt)
RUN composer install --no-dev --optimize-autoloader

# Droits d'écriture pour Laravel (après composer pour éviter des problèmes)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
