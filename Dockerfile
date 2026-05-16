FROM richarvey/nginx-php-fpm:latest

# Étape 1 : Copie des fichiers
COPY . /var/www/html

# Étape 2 : Configurations d'environnement
ENV WEBROOT /var/www/html/public
ENV COMPOSER_ALLOW_SUPERUSER 1

# Étape 3 : Installer Node.js et NPM (requis pour compiler les assets)
RUN apk add --no-cache nodejs npm

# Étape 4 : Installer les dépendances PHP et JS, puis compiler
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build
