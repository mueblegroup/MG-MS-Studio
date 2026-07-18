# Mueble Studio production deployment

This directory contains reusable server configuration for Laravel Scheduler and the database queue worker.

## Included files

- `supervisor/mueble-lms-worker.conf.example` — persistent Laravel queue worker.
- `cron/mueble-lms` — Laravel Scheduler cron template.
- `install-services.sh` — installs Supervisor/cron and renders the templates.
- `deploy.sh` — repeatable application deployment script.

## 1. Required production environment

Set the production `.env` values before installing services:

```env
APP_ENV=production
APP_DEBUG=false
QUEUE_CONNECTION=database
```

Make sure the application key, database, mail, Stripe and webhook settings are configured.

## 2. Install scheduler and queue services once

From the project directory:

```bash
chmod +x deployment/install-services.sh deployment/deploy.sh

sudo PROJECT_PATH=/var/www/Studio-Management-System-Laravel \
     APP_USER=wildlonewolf \
     PHP_BIN=/usr/bin/php \
     ./deployment/install-services.sh
```

Change `APP_USER`, `PROJECT_PATH`, and `PHP_BIN` for the production server.

The installer creates:

- `/etc/supervisor/conf.d/mueble-lms-worker.conf`
- `/etc/cron.d/mueble-lms`

It also enables Supervisor and cron at boot.

Verify:

```bash
sudo supervisorctl status mueble-lms-worker:*
php artisan schedule:list
```

## 3. Deploy updates

The deployment script intentionally refuses to continue when the server working tree contains local changes. Production code should not be edited directly.

```bash
PROJECT_PATH=/var/www/Studio-Management-System-Laravel \
BRANCH=main \
PHP_BIN=/usr/bin/php \
./deployment/deploy.sh
```

The script performs:

1. Maintenance mode.
2. Fetch and reset to `origin/main`.
3. Production Composer install.
4. Database migrations.
5. Cache clearing.
6. Clean npm install and Vite build.
7. Config, route and Blade caching.
8. Queue restart.
9. Application restore.

## 4. Logs and health checks

```bash
sudo supervisorctl status mueble-lms-worker:*
sudo tail -100 /var/log/mueble-lms-worker.log
tail -100 storage/logs/laravel.log
php artisan queue:failed
php artisan schedule:list
```

## 5. Existing subscription reconciliation

After deploying automatic Stripe end-date cancellation for the first time:

```bash
php artisan subscriptions:sync-class-end-dates
```

This command is a one-time reconciliation for subscriptions created before the cancellation feature existed.
