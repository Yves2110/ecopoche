# Déploiement EcoPoche (Infomaniak / mutualisé)

## Avant mise en ligne

1. Sauvegarde MySQL (export phpMyAdmin).
2. Ne jamais exécuter `migrate:fresh` ni `db:seed` en production.
3. Vérifier `.env` : `APP_DEBUG=false`, `CRON_TOKEN` long et unique, secrets hors git.

## Mise à jour code

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Cron (tâches planifiées + file d'attente)

Configurer un appel HTTP **chaque minute** vers :

```
https://votre-domaine.com/cron/VOTRE_CRON_TOKEN
```

Cela exécute `schedule:run` et traite les jobs en attente (`queue:work --stop-when-empty`).

Dans `.env` production :

```
QUEUE_CONNECTION=database
```

## Tests locaux

```bash
php artisan test
```

Utiliser uniquement `php artisan test` (base `ecopoche_test`).
