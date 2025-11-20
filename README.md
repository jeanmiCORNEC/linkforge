# LinkForge – Guide déploiement (prod-ready)

## Description rapide
SaaS de gestion de liens trackés (campagnes/sources), redirections rapides avec forwarding des paramètres, analytics (clics, devices, pays), exports CSV/raw, quotas Free/Pro.

## Stack & prérequis
- PHP 8.2+ / Composer
- Node 18+ / npm
- MySQL/MariaDB
- Accès cron pour `php artisan schedule:run`

## Installation locale (dev)
```bash
cp .env.example .env
composer install
npm install
php artisan key:generate
php artisan migrate --seed
npm run dev   # ou npm run build pour prod
php artisan serve
```

## Variables d’environnement clés
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://app.linkforge.io` (domaine final)
- `FORCE_HTTPS=true` (activer après SSL)
- `MAXMIND_LICENSE_KEY=` (GeoLite2 City)
- `CLICK_MONITOR_LOOKBACK_MINUTES=60` (alerte “plus de clics récents”)
- DB_* (connexion MySQL)
- `LOG_CHANNEL=stack`, `LOG_LEVEL=info`

## Domaine local conseillé
- APP_URL: `http://linkforge.test`
- Ajoutez dans `/etc/hosts` (ou équivalent) : `127.0.0.1 linkforge.test`
- Utilisez ce domaine pour tester les liens courts `/l/{code}` (évite 127.0.0.1 dans les CSV/exports).

## Géolocalisation (GeoLite2 City)
1. Créez une licence MaxMind et renseignez `MAXMIND_LICENSE_KEY` dans `.env`.
2. Installez/actualisez la base :
```bash
php artisan geo:maxmind-update
```
3. Le scheduler déclenche la mise à jour mensuelle (voir section cron).

## Scheduler / Cron
Ajoutez ce cron (exécuté chaque minute) :
```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```
Il déclenche notamment :
- `geo:maxmind-update` (mensuel, 03:00 UTC)
- `clicks:monitor` (horaire) : alerte/log si aucun clic récent.

## Déploiement production (pas à pas)
1) Pull/checkout du code, `composer install --optimize-autoloader`  
2) `npm install && npm run build`  
3) `php artisan key:generate` (si clé absente)  
4) Configurer `.env` (voir variables clés)  
5) `php artisan migrate --force`  
6) `php artisan geo:maxmind-update` (ou fournir un `.mmdb` local)  
7) Mettre en place le cron `schedule:run`  
8) Activer `FORCE_HTTPS=true` une fois le certificat OK  
9) Vérifier que les liens courts `/l/{code}` pointent bien sur `APP_URL` (pas de 127.0.0.1)  
10) Vérifier les logs et l’alerte `clicks:monitor` (LOOKBACK ajusté si besoin).

## Commandes utiles
- `php artisan clicks:monitor --since=60` : vérifie qu’il y a eu des clics récents.
- `php artisan geo:maxmind-update` : met à jour GeoLite2 City.
- `php artisan demo:seed --clicks=200` : génère un jeu de données de démonstration (user démo plan pro, campagnes/sources/liens + clics).

## Notes
- Les redirections sont best-effort : même si la géoloc ou l’insert du clic échoue, l’utilisateur est redirigé.
- Les short codes sont uniques et générés automatiquement à la création des tracked links.
