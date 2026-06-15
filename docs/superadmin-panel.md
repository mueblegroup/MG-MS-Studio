# Superadmin Panel

The superadmin panel is available at:

```text
/superadmin/dashboard
```

A user must have this role value in the `users.role` column:

```text
superadmin
```

Example production-safe SQL after choosing the correct account:

```sql
UPDATE users SET role = 'superadmin' WHERE email = 'owner@example.com';
```

What the panel currently shows:

- Total studios
- Active, trial and inactive studio counts
- Total users
- Role distribution for superadmins, admins, teachers and students
- Paid revenue summary
- Pending order count
- Recent studios table

Notes:

- No large binary assets were added.
- The panel uses the existing Laravel Blade backend layout and Tailwind utility styling.
- The route is protected by `auth` and `role:superadmin` middleware.
