FROM richarvey/nginx-php-fpm:latest

# Étape 1 : Installer les outils système indispensables pour les extensions natives (comme Tailwind)
RUN apk add --no-cache libstdc++ build-base

# Étape 2 : Injecter Node.js 22 et NPM directement depuis l'image officielle Node Alpine
COPY --from=node:22-alpine /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-alpine /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -sf /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

# Étape 3 : Copie des fichiers de ton application
COPY . /var/www/html

# Étape 4 : Configurations d'environnement indispensables
ENV WEBROOT /var/www/html/public
ENV COMPOSER_ALLOW_SUPERUSER 1

# Étape 5 : Installer les dépendances PHP et compiler proprement tes assets CSS/JS
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build
