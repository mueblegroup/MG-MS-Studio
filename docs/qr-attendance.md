# Session QR attendance

Admins and the assigned teacher can open **Show attendance QR** from a class or
plan session's attendance page. Select **Open QR check-in** to display the code.
Students scan with their phone camera, log in to that studio, and tap
**Confirm attendance**. Password login and two-factor authentication preserve
the original check-in URL. The QR screen refreshes the checked-in list every
10 seconds. Manual attendance remains available for corrections and students
without phones.

Check-in opens 15 minutes before the scheduled start and closes exactly at the
scheduled end, using the studio's configured timezone and the server clock.
Changing the schedule or cancelling the class invalidates its QR. **Replace QR
code** also invalidates previously shared links and open confirmation pages.
The code is fixed during a session unless staff replace it; a forwarded code
can still be used by an eligible student, so QR check-in is not proof of physical
presence.

## Eligibility and security

- Staff access requires studio membership (or studio ownership for an admin),
  and teachers must be assigned to the class/plan.
- The signed URL is bound to the portal host, session type, session ID, current
  schedule, random QR version, and expiry. Both GET and POST validate it.
- Only the authenticated student can check in; request-supplied student IDs and
  attendance statuses are ignored. GET never records attendance.
- Students need an active assignment or plan enrolment covering the session date.
  Purchased class assignments require a paid, fulfilled order for that session.
  Subscription sessions always require that session's paid, fulfilled order.
  Staff-granted individual-class assignments and active plan enrolments remain
  valid entitlements. Cancelling future subscription billing does not invalidate
  a session already paid for and assigned.
- Confirmation uses CSRF protection, rate limits, a transaction, row locks, and
  existing attendance uniqueness constraints. Repeated scans retain the original
  check-in time. Students cannot replace staff-recorded no-shows.
- QR generation/replacement and successful check-ins are recorded in audit logs.
  QR images are generated locally in the browser; no third-party QR API is used.
- Class-card deductions stay in the existing staff workflow. This feature does
  not spend card credits or allow a card alone to enrol a student in a session.

## Deployment

Run the new migration before serving the updated code:

```sh
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

The repository includes updated production assets. When rebuilding assets, run
`npm ci && npm run build`. No new PHP package is required.

## Verification

Run `php artisan test --filter=QrAttendanceTest`. The tests exercise authentication,
2FA return, tenant/staff isolation, CSRF, read-only scans, identity spoofing,
duplicate submissions, payment/enrolment restrictions, exact timezone cutoff,
QR replacement, rescheduling, and staff corrections. The SQLite test setup runs
the real migrations, substituting a portable column change for the existing
MySQL-only class-type ENUM alteration. Database row-lock concurrency should also
be smoke-tested against the deployment's MySQL/MariaDB engine.

Implementation verification: 20 tests / 187 assertions passed on PHP 8.3 with
SQLite. The existing Composer lock contains PHP 8.4-only packages; verification
used an isolated PHP 8.3-compatible dependency resolution without changing the
repository Composer files. Production assets and Laravel route/view caching
also passed.
