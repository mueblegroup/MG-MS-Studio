# Superadmin Panel

The superadmin panel is the owner-level control area for the full Mueble LMS platform.

It is available at:

```text
/superadmin/dashboard
```

A superadmin user must have this role value in the `users.role` column:

```text
superadmin
```

A superadmin is intentionally not tied to one studio. For the owner account, keep `studio_id` as `NULL` when possible:

```sql
UPDATE users
SET role = 'superadmin', studio_id = NULL
WHERE email = 'owner@example.com';
```

## What superadmin manages

- All studios across the platform
- Each studio's platform subscription plan
- Studio subscription status: active, trial, inactive or suspended
- Trial and subscription expiry dates
- Platform subscription pricing plans
- Owner-level SaaS subscription revenue from studios

## What superadmin does not manage here

- Student/class payments inside a studio
- Normal studio admin tasks such as classes, teachers, students and studio settings

Those remain inside the existing `admin` dashboard and continue to be scoped by `studio_id`.

## Platform subscription data

This update adds dedicated owner-level tables:

```text
platform_subscription_plans
platform_subscription_payments
```

`platform_subscription_payments` is intentionally separate from the existing `payments` table. The superadmin revenue cards should only count studio SaaS subscription payments, not student/class payments from each studio.

## After deployment

Run:

```bash
php artisan migrate
php artisan optimize:clear
php artisan route:clear
php artisan config:clear
```

No large binary assets were added. The panel uses the existing Laravel Blade backend layout and Tailwind utility styling.
