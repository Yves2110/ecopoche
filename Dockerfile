FROM richarvey/nginx-php-fpm:latest

# Étape 1 : Forcer l'utilisation des dépôts "v3.20" d'Alpine pour obtenir Node.js 20+ de manière native
RUN echo "https://dl-cdn.alpinelinux.org/alpine/v3.20/main" > /etc/apk/repositories && \
    echo "https://dl-cdn.alpinelinux.org/alpine/v3.20/community" >> /etc/apk/repositories

# Étape 2 : Mettre à jour et installer Node.js 20 et NPM (parfaitement compatibles avec le système)
RUN apk update && apk add --no-cache nodejs npm

# Étape 3 : Copie des fichiers de ton application
COPY . /var/www/html

# Étape 4 : Configurations d'environnement indispensables
ENV WEBROOT /var/www/html/public
ENV COMPOSER_ALLOW_SUPERUSER 1

# Étape 5 : Installer les dépendances et compiler les assets CSS/JS
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build
