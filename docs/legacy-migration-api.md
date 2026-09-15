# Legacy Migration API

This API is intended for controlled imports from legacy studio systems into an already-resolved ClassM8 studio tenant. It is not a checkout API and does not contact Stripe or HitPay.

## Authentication

Use a tenant API token with only the abilities required for the migration:

- `migration:read` — inspect mapping/reconciliation records
- `migration:write` — import legacy-only records
- Normal resource abilities such as `plans:create` or `classcards:create` when n8n uses the existing ClassM8 API for resources that already have safe write endpoints

Revoke the migration token when the migration is complete.

For Étude, use a stable source name such as `etude_legacy` for every request.

## Idempotency

Every migration write requires:

```json
{
  "source_system": "etude_legacy",
  "source_id": "103"
}
```

The tuple `(studio_id, source_system, entity_type, source_id)` is unique. Replaying the same source row returns its existing `migration_record` instead of creating a duplicate.

An optional SHA-256 `checksum` can be stored for reconciliation.

## Endpoints

### Mapping ledger

- `GET /api/v1/migration/records`
- `POST /api/v1/migration/records`

Use `POST /records` to register the old-to-new ID mapping for resources created through existing normal API endpoints, such as plans, plan sessions, classes and class cards.

Example:

```json
{
  "source_system": "etude_legacy",
  "entity_type": "plan",
  "source_id": "12",
  "target_type": "App\\Models\\Plan",
  "target_id": 44,
  "metadata": {
    "legacy_name": "Ballet Level 1"
  }
}
```

### Users

`POST /api/v1/migration/users`

```json
{
  "source_system": "etude_legacy",
  "source_id": "student:103",
  "name": "Student Name",
  "email": "student@example.com",
  "role": "student",
  "phone_number": "+6590000000",
  "legacy_password_hash": "$2y$10$....................................................."
}
```

For trusted legacy migrations, ClassM8 can preserve an existing PHP bcrypt password hash through `legacy_password_hash`. The value must be a valid 60-character `$2y$` bcrypt hash with a supported cost factor. It is written directly to the password column so Laravel does not hash the bcrypt string a second time. The hash is never returned by this endpoint.

`password` and `legacy_password_hash` are mutually exclusive. If `legacy_password_hash` is accepted, the response returns `legacy_password_preserved: true` and `password_setup_required: false`, allowing the user to keep the same password used in the legacy system. If both password fields are omitted, ClassM8 creates a random migration password and returns `password_setup_required: true`.

Only use this option for bcrypt hashes from a trusted source database. Revoke the migration token after the import.

### User plan memberships

`POST /api/v1/migration/user-plans`

```json
{
  "source_system": "etude_legacy",
  "source_id": "booking:88",
  "user_id": 501,
  "plan_id": 44,
  "starts_on": "2026-01-01",
  "ends_on": "2026-06-30",
  "is_active": false
}
```

### Class-card purchases/balances

`POST /api/v1/migration/classcard-purchases`

```json
{
  "source_system": "etude_legacy",
  "source_id": "class_card_booking:50",
  "user_id": 501,
  "class_card_id": 7,
  "purchased_at": "2026-05-01T10:00:00+08:00",
  "expires_at": "2026-11-01T23:59:59+08:00",
  "classes_remaining": 8,
  "status": "active"
}
```

### Historical attendance

`POST /api/v1/migration/attendance`

```json
{
  "source_system": "etude_legacy",
  "source_id": "plan_attendance:365",
  "user_id": 501,
  "plan_session_id": 900,
  "attended_at": "2026-06-12T18:00:00+08:00",
  "status": "attended"
}
```

The referenced user and plan session must belong to the resolved studio tenant.

### Historical orders

`POST /api/v1/migration/orders`

```json
{
  "source_system": "etude_legacy",
  "source_id": "booking:113",
  "user_id": 501,
  "currency": "SGD",
  "subtotal": 120.00,
  "total": 120.00,
  "status": "paid",
  "provider_reference": "legacy-booking-113",
  "paid_at": "2026-05-01T10:00:00+08:00",
  "items": [
    {
      "type": "plan",
      "id": 44,
      "quantity": 1,
      "unit_price": 120.00,
      "currency": "SGD"
    }
  ]
}
```

Imported orders use `payment_provider=legacy` and `billing_reason=legacy_migration`. Order model events are suppressed during import, so historical records do not generate live payment-due or payment-success notifications.

### Historical payments

`POST /api/v1/migration/payments`

```json
{
  "source_system": "etude_legacy",
  "source_id": "payment:99",
  "user_id": 501,
  "order_id": 700,
  "amount": 120.00,
  "currency": "SGD",
  "status": "paid",
  "reference": "old-payment-id",
  "paid_at": "2026-05-01T10:00:00+08:00",
  "payload": {
    "legacy_type": "plan"
  }
}
```

Imported payments always use `method=legacy` and `provider=legacy`. No payment gateway service or fulfillment flow is called.

## Recommended Étude n8n order

1. Teachers and students through `/migration/users`, preserving compatible `$2y$` bcrypt hashes with `legacy_password_hash`.
2. Plans through the normal `/api/v1/plans` endpoint, then register each old/new mapping in `/migration/records`.
3. Exact historical plan sessions through normal plan-session endpoints, then register mappings.
4. Class card definition through the normal class-card endpoint, then register its mapping.
5. User-plan memberships and class-card purchase balances through migration endpoints.
6. Historical attendance.
7. Historical orders/bookings.
8. Historical payments linked to the migrated orders where possible.
9. Compare source counts against `GET /api/v1/migration/records` and normal ClassM8 resource counts.
10. Revoke the migration API token.
