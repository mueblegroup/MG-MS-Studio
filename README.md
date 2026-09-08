<p align="center"><a href="https://mueblegroup.com" target="_blank"><img src="https://mueblegroup.com/wp-content/uploads/2024/02/MUEBLE-LOGO-drak.png" width="400" alt="Mueble Logo"></a></p>

# Studio Management System (Laravel)

A multi-tenant **Studio / LMS Management System** built with Laravel for dance studios, gyms, training centers, academies, and similar institutes.

The application supports a central client portal, tenant studio subdomains, custom studio domains, role-based administration, class scheduling, subscriptions, attendance, payments, tenant-specific payment gateways, API access, and production queue/scheduler workflows.

---

## Features

### Multi-tenant studio platform

- Central SaaS/client portal
- Studio subdomains
- Verified/custom studio domains
- Tenant-aware authentication and authorization
- Admin, Teacher, Student, and Superadmin roles
- Owner-controlled studio timezone
- Tenant-specific settings and branding

### Classes and scheduling

- One-time classes
- Recurring classes
- Subscription classes
- Plans and plan sessions
- Class cards / lesson credits
- Student assignment and booking flows
- Teacher schedules
- Student schedules
- Attendance tracking
- Class reschedule/cancellation history

### Payments and subscriptions

- Stripe integration
- HitPay integration
- Tenant-specific gateway credentials
- Signed webhook validation
- Order and payment records
- Automatic fulfillment after successful payment
- Recurring subscription class billing
- Payment retry flows
- Subscription grace periods
- Student/admin payment history and receipts

### Communication and account features

- In-app notifications
- Tenant-specific SMTP configuration
- Two-factor authentication support
- Student self-registration controls
- Profile completion flows

### Developer and API features

- Laravel Sanctum API tokens
- Studio-bound API access
- API ability/permission controls
- API request logging
- Tenant-aware model scopes
- Database migrations
- Queue-based order fulfillment
- Laravel Scheduler integration
- Production deployment scripts

---

## Technology stack

- **Backend:** Laravel 12
- **PHP:** 8.2+
- **Database:** MySQL / MariaDB
- **Frontend:** Blade + Tailwind CSS + Vite
- **API auth:** Laravel Sanctum
- **Payments:** Stripe + HitPay
- **PDF:** DomPDF
- **Authentication integrations:** Laravel Socialite, Apple, Microsoft
- **Server:** Linux with Nginx or Apache
- **Background processing:** Laravel Queue + Supervisor
- **Scheduled jobs:** Laravel Scheduler + cron
- **Version control:** Git + GitHub

---

## Installation

### Requirements

- PHP 8.2 or newer
- Composer
- MySQL / MariaDB
- Node.js and npm
- Nginx or Apache for production

### 1. Clone the repository

```bash
git clone https://github.com/mueblegroup/MG-MS-Studio.git
cd MG-MS-Studio
```

### 2. Install dependencies

```bash
composer install
npm install
npm run build
```

### 3. Environment setup

```bash
cp .env.example .env
php artisan key:generate
```

Review all `.env` values before continuing, especially:

```env
APP_URL=
APP_TIMEZONE=Asia/Kuala_Lumpur
SAAS_ROOT_DOMAIN=
SAAS_CENTRAL_DOMAINS=
SESSION_DOMAIN=

DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

QUEUE_CONNECTION=database
SESSION_DRIVER=database
```

### 4. Database setup

```bash
php artisan migrate
```

### 5. Storage and permissions

```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
```

Use ownership appropriate for the web/PHP-FPM user on the target server.

### 6. Local development

```bash
php artisan serve
```

Production servers should use Nginx/Apache and should not expose `php artisan serve` publicly.

---

## Production deployment

Production deployment instructions, Supervisor configuration, Scheduler setup, payment smoke tests, custom-domain checks, reconciliation commands, and rollback guidance are maintained in:

```text
deployment/README.md
```

The provided deployment script performs maintenance mode, Git synchronization, production Composer install, migrations, Vite build, Laravel cache generation, and queue restart.

Typical deployment invocation:

```bash
PROJECT_PATH=/var/www/Studio-Management-System-Laravel \
BRANCH=main \
PHP_BIN=/usr/bin/php \
./deployment/deploy.sh
```

---

## Production environment minimums

A production environment should include at least:

```env
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=Asia/Kuala_Lumpur
QUEUE_CONNECTION=database
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
LOG_LEVEL=warning
```

Configure real production values for database, mail, domains, Stripe, HitPay, and any other provider credentials.

Do not reuse staging secrets in production.

---

## Queue and scheduler

The application contains background flows that should not run with `QUEUE_CONNECTION=sync` in production.

Use the included Supervisor template for the database queue worker and run the Laravel Scheduler every minute.

Example scheduler cron:

```cron
* * * * * cd /var/www/Studio-Management-System-Laravel && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

Verify services with:

```bash
sudo supervisorctl status mueble-lms-worker:*
php artisan schedule:list
php artisan queue:failed
```

---

## Subscription reconciliation

The current Stripe class-subscription end-date reconciliation command is:

```bash
php artisan subscriptions:sync-stripe-end-dates
```

The former `subscriptions:sync-class-end-dates` command is obsolete.

Always inspect `php artisan schedule:list` on the deployed version to confirm the currently registered Stripe and HitPay synchronization commands.

---

## Multi-tenant security notes

Tenant separation is enforced through studio context middleware, scoped models, role checks, and explicit tenant-aware validation in sensitive flows.

Important operational rules:

- Use the intended studio host for tenant Stripe/HitPay webhook URLs.
- Keep the central platform Stripe webhook on the central domain.
- Custom domains use host-only session cookies automatically where needed.
- API tokens created for studio use are bound to the intended studio.
- Revoke ambiguous/legacy API tokens when an owner controls multiple studios.
- Never bypass tenant middleware in new admin/API routes without an explicit reason.

---

## Secrets and sensitive configuration

- `.env` files must never be committed.
- Payment gateway credentials must not be logged.
- Studio SMTP passwords are treated as secrets.
- Never expose `phpinfo()` or diagnostic PHP files from `public/`.
- Keep `APP_KEY` stable after encrypted application data exists.
- Use HTTPS for central, tenant-subdomain, and custom-domain traffic.

---

## Production verification

After deploying a production release, run:

```bash
php artisan migrate:status
php artisan route:list
php artisan schedule:list
php artisan queue:failed
php artisan test
```

Optional PHP syntax verification:

```bash
find app routes bootstrap config -name '*.php' -print0 | xargs -0 -n1 php -l
```

Before enabling live payments, perform a complete Stripe/HitPay smoke test and verify order fulfillment, webhook processing, subscription billing state, queue health, and student/admin payment history.

---

## Important development rules

- All schema changes must use migrations.
- Do not edit production code directly.
- Do not commit generated secrets.
- Use tenant-aware Eloquent models/validation for records containing `studio_id`.
- Be careful with plain `exists:*` validation because it bypasses Eloquent global scopes.
- New route-model-bound tenant resources must be protected by tenant context before bindings execute.
- Test payment webhook idempotency when changing order or subscription logic.

---

## License

This project is proprietary and owned by Mueble Group.

Unauthorized distribution or resale is not permitted.

## Maintained by

### Mueble Group

Web Development · LMS Solutions · Digital Services
