# Mueble Studio production deployment

This directory contains the supported production deployment and background-service configuration for the Laravel Studio Management System.

The application is multi-tenant and may be served from:

- the central SaaS/client portal domain;
- studio subdomains under `SAAS_ROOT_DOMAIN`;
- verified/custom studio domains.

Production deployments must keep the Laravel queue worker and scheduler running because subscription billing reconciliation, background order fulfillment, notifications, and other maintenance tasks depend on them.

## Included files

- `supervisor/mueble-lms-worker.conf.example` — persistent Laravel database queue worker.
- `cron/mueble-lms` — Laravel Scheduler cron template.
- `install-services.sh` — installs Supervisor/cron and renders the templates.
- `deploy.sh` — repeatable application deployment script.

## 1. Required production environment

At minimum, production must use:

```env
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=Asia/Kuala_Lumpur

QUEUE_CONNECTION=database
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true

LOG_LEVEL=warning
```

Also configure the real values for:

```env
APP_KEY=
APP_URL=https://your-central-domain.example

SAAS_ROOT_DOMAIN=studio.example.com
SAAS_CENTRAL_DOMAINS=your-central-domain.example
SESSION_DOMAIN=.example.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="${APP_NAME}"

STRIPE_PUBLISHABLE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=
```

Do not reuse staging credentials in production.

### Session domains and custom studio domains

`SESSION_DOMAIN` should normally be the shared parent domain for the central portal and SaaS studio subdomains, for example `.example.com`.

Verified/custom studio domains use a host-only session cookie automatically and therefore do not need to be children of `SESSION_DOMAIN`.

### Tenant payment gateways

Stripe and HitPay credentials configured inside a studio are tenant-specific. Tenant payment webhooks must point to that studio's own host so the application can resolve the correct studio before validating the webhook signature.

Examples:

```text
https://dance-a.studio.example.com/webhooks/stripe
https://dance-a.studio.example.com/webhooks/hitpay
```

or on a custom domain:

```text
https://portal.clientstudio.com/webhooks/stripe
https://portal.clientstudio.com/webhooks/hitpay
```

The platform subscription Stripe webhook remains a central-domain webhook.

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

The scheduler cron must point to this repository, not another Laravel application on the same server.

A typical cron entry is:

```cron
* * * * * cd /var/www/Studio-Management-System-Laravel && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

## 3. Deploy updates

The deployment script intentionally refuses to continue when the server working tree contains local changes. Production code should not be edited directly.

```bash
PROJECT_PATH=/var/www/Studio-Management-System-Laravel \
BRANCH=main \
PHP_BIN=/usr/bin/php \
./deployment/deploy.sh
```

The deployment script performs:

1. maintenance mode;
2. fetch and hard reset to `origin/main`;
3. production Composer install;
4. forced database migrations;
5. Laravel cache clearing;
6. clean npm install and Vite production build;
7. config, route and Blade caching;
8. queue restart;
9. application restore.

For deployments that include frontend/CSS/JS changes, do not skip the Vite build.

## 4. Recommended manual production verification

After deployment, run:

```bash
php artisan migrate:status
php artisan route:list
php artisan schedule:list
php artisan queue:failed
php artisan test
```

Run PHP syntax checks across application code:

```bash
find app routes bootstrap config -name '*.php' -print0 | xargs -0 -n1 php -l
```

Verify the queue worker:

```bash
sudo supervisorctl status mueble-lms-worker:*
sudo tail -100 /var/log/mueble-lms-worker.log
```

Verify Laravel logs:

```bash
tail -100 storage/logs/laravel.log
```

## 5. Subscription reconciliation

The current Stripe class-subscription end-date reconciliation command is:

```bash
php artisan subscriptions:sync-stripe-end-dates
```

Use it after first deploying automatic Stripe final-class cancellation and when reconciling existing Stripe class subscriptions.

The old command below is obsolete and must not be used:

```text
subscriptions:sync-class-end-dates
```

The production scheduler should also expose the current subscription reconciliation commands in `php artisan schedule:list`, including the Stripe and HitPay recurring synchronization tasks configured by the application.

## 6. Production payment smoke test

Before accepting customer traffic, test at least one real or provider-approved low-value transaction for every enabled production gateway.

Verify the full path:

1. student checkout creates the pending order/payment;
2. the provider checkout opens with the correct tenant credentials;
3. webhook signature validation succeeds on the studio host;
4. the payment becomes `paid`;
5. the order is fulfilled once only;
6. the class, class card, plan, or subscription appears for the student;
7. the queue has no failed fulfillment jobs;
8. admin and student payment history/receipt pages show the transaction.

For recurring subscription classes, also verify the next billing date and final class cancellation schedule in the payment provider.

## 7. Custom-domain smoke test

For every production custom domain configuration, verify:

```text
HTTPS certificate valid
login works
logout works
session remains valid across normal page navigation
admin/teacher/student tenant isolation works
Stripe/HitPay webhook URL uses the custom host when configured there
```

Custom domains must not depend on the shared SaaS session-cookie domain.

## 8. API production notes

Studio API access uses Laravel Sanctum tokens and tenant-aware API middleware.

New API tokens are bound to the intended studio. If an older owner token is ambiguous because the owner controls more than one studio, revoke it and create a new token from the intended studio workspace.

Never expose plaintext API tokens after creation and revoke tokens that are no longer required.

## 9. Secrets and security

Production rules:

- `APP_DEBUG=false`.
- Never commit `.env` or provider secrets.
- Never expose `phpinfo()` or diagnostic PHP files under `public/`.
- Use HTTPS for central, tenant-subdomain, and custom-domain traffic.
- Keep `APP_KEY` stable after encrypted data exists; changing it will make application-encrypted secrets unreadable.
- Studio payment gateway credentials and saved SMTP passwords are treated as secrets and must not be copied into logs, tickets, or screenshots.
- Back up the database before migrations or major billing changes.

## 10. Rollback preparation

Before a production release, record the current commit and create a database backup.

```bash
git rev-parse HEAD
```

If a deployment must be rolled back, restore a compatible application commit and, when a migration changed persistent data/schema incompatibly, restore or explicitly reverse the database change. Never blindly run migration rollback on a live billing database without reviewing the migration first.
