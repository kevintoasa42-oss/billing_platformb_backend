# Carrier Module (V3 Core)

Canonical carrier profile management for the Ecuadorian transport billing platform.

## Overview

The Carrier module provides the canonical ThirdParty + Carrier experience:
onboarding, profile management, received documents, settlement allocations,
and audit trails. It preserves the reference API contract exactly while
adapting the internals to the V3 DDD architecture.

## Architecture

```
app/Context/V3/Modules/Core/Carrier/
├── Application/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── CarrierController.php          # Thin HTTP coordinator
│   │   └── Requests/
│   │       ├── CarrierOnboardingRequest.php
│   │       ├── CarrierUpdateRequest.php
│   │       ├── CarrierPaymentAccountRequest.php
│   │       ├── CarrierSignatureRequest.php
│   │       ├── CarrierReceivedDocumentRequest.php
│   │       └── CarrierAllocationRequest.php
│   └── UseCases/
│       └── CarrierOnboardingUseCase.php       # Application orchestration
├── Domain/
│   ├── Repository/
│   │   └── CarrierProfileRepositoryInterface.php  # Port
│   └── Services/
│       └── SettlementPolicy.php              # Pure settlement math
├── Infrastructure/
│   └── Postgres/
│       └── CarrierProfileRepository.php      # PostgreSQL adapter
└── CarrierServiceProvider.php                # DI bindings
```

### Layer responsibilities

- **Controller**: validates HTTP input via form requests, extracts tenant
  and actor context, delegates to the use case, wraps responses in the
  standard envelope.
- **Use case**: validates idempotency keys, normalizes canonical
  identification, translates domain exceptions to HTTP exceptions.
- **Repository interface (port)**: defines the contract for carrier
  persistence without coupling to any storage engine.
- **Settlement policy**: pure domain service that evaluates settlement
  metrics (integer-cent based, no floating point).
- **Repository implementation**: PostgreSQL adapter using
  `DB::connection('master_v3')` with raw SQL. Sets tenant context via
  `set_config('app.tenant_id', ...)` inside a transaction so RLS policies
  apply to every statement.

## Database tables

All tables live in the `master_v3` database under the `core` and `fiscal`
schemas:

| Schema | Table | Purpose |
|---|---|---|
| core | carrier_companies | Carrier company profile per ThirdParty |
| core | carrier_onboarding_requests | Idempotency key + response cache |
| core | carrier_activities | Economic activities per carrier |
| core | carrier_establishments | SRI establishments per carrier |
| core | carrier_emission_points | SRI emission points per establishment |
| core | carrier_issuer_profiles | Operator/partner issuer mode config |
| core | carrier_issuer_settings | Default issuer mode per tenant |
| core | carrier_payment_accounts | Encrypted payment account data |
| core | carrier_signatures | Signature metadata (no private keys) |
| core | carrier_affiliations | Carrier affiliation validity periods |
| core | carrier_vehicle_assignments | Vehicle-to-carrier assignments |
| core | third_parties | Canonical ThirdParty identities |
| core | third_party_roles | Role tags (carrier, customer, etc.) |
| core | third_party_activities | SRI activities per ThirdParty |
| core | transport_operations | Transport operations per carrier |
| core | vehicles | Vehicle catalog (tenant-scoped plates) |
| core | economic_activities | Global economic activity catalog |
| fiscal | received_documents | Documents received from carrier partners |
| fiscal | settlement_allocations | Conciliation allocations |
| fiscal | documents | Customer invoices |
| fiscal | credit_notes | Credit notes linked to invoices |
| platform | audit_events | Audit trail for all carrier actions |

## Tenant isolation

Every repository method runs inside `DB::connection('master_v3')->transaction()`
with `set_config('app.tenant_id', $tenantId, true)` set at the transaction
level. PostgreSQL row-level security policies enforce that queries can only
access rows belonging to the current tenant.

## Idempotency

Onboarding and allocation endpoints require an `Idempotency-Key` header
(8-100 chars, `[A-Za-z0-9_-]`). The key is hashed with the canonical JSON
of the request body and stored in `core.carrier_onboarding_requests` or
`fiscal.settlement_allocations.idempotency_key`. Repeated calls with the
same key and body return the stored response; mismatched bodies raise a
domain exception.

## Historical issuer snapshots

When a received document creates a transport operation, the carrier's
issuer configuration is copied into `transport_operations.issuer_snapshot`
as JSONB. This snapshot is never rebuilt from mutable carrier settings when
the operation is read later, ensuring historical fiscal data remains
audit-safe even if the carrier's issuer profile changes.

## Payment account security

- Account numbers are encrypted with `Crypt::encryptString()` before
  storage in `carrier_payment_accounts.account_number_ciphertext`.
- Only the last 4 digits are stored in plaintext
  (`account_number_last4`).
- API responses return a masked account (`•••• 1234`).
- The full account number is never included in any API response.

## Signature metadata

The signature endpoint stores only public metadata (fingerprint, serial,
subject, validity dates, key reference). Private keys and certificate
contents are rejected explicitly and must be managed in the secure key
custody (OpenBao transit).

## Settlement policy

`SettlementPolicy::evaluate()` computes settlement metrics using
integer-cent arithmetic to avoid floating-point errors:

- `customer_net = max(0, customer_gross - customer_credits)`
- `partner_net = max(0, partner_invoices - partner_credits)`
- `pending = max(0, customer_net - allocated)`
- `overbilled = max(0, allocated - customer_net)`
- `blocked = review_pending || overbilled > 0 || partner_net > customer_net`
- `status`: `blocked` | `settled` | `partially_settled` | `open`

## Endpoints

All routes are prefixed with `api/v3/core/carriers` and require
`auth.v3.cookie` + `tenant.v3.context` middleware.

| Method | Path | Controller | Description |
|---|---|---|---|
| GET | `/` | `index` | List carriers with optional filters |
| POST | `/onboard` | `store` | Onboard a new or existing carrier (idempotent) |
| GET | `/{id}` | `show` | Get full carrier detail by ThirdParty UUID |
| PATCH | `/{id}` | `update` | Update carrier profile |
| PATCH | `/{id}/payment-account` | `paymentAccount` | Update payment account |
| PATCH | `/{id}/signature` | `signature` | Update signature metadata |
| POST | `/{id}/documents` | `document` | Record a received document |
| DELETE | `/{id}/documents/{documentId}` | `cancelDocument` | Cancel a received document |
| POST | `/{id}/allocations` | `allocate` | Create a settlement allocation (idempotent) |
| DELETE | `/{id}/allocations/{allocationId}` | `reverseAllocation` | Reverse an allocation |
| POST | `/{id}/operations/{operationId}/settlement-review/clear` | `clearReview` | Clear pending credit note review |
| GET | `/{id}/audit` | `audit` | Get carrier audit trail |

### Query parameters for `GET /`

| Param | Type | Default | Description |
|---|---|---|---|
| `status` | `active\|inactive\|all` | `active` | Filter by active state |
| `search` / `q` | string | - | Search name, identification, trade name, plate |
| `per_page` | int (1-100) | 100 | Result limit |

### Idempotency headers

| Header | Required for | Format |
|---|---|---|
| `Idempotency-Key` | `POST /onboard`, `POST /{id}/allocations` | 8-100 chars `[A-Za-z0-9_-]` |

## Response envelope

All responses use the standard V3 envelope:

```json
{
  "status": true,
  "message": "...",
  "data": ...
}
```

## Error codes

| HTTP | Cause |
|---|---|
| 400 | Invalid Idempotency-Key format |
| 404 | Carrier or document not found |
| 422 | Domain validation failure (identification, money, state) |
| 500 | Unexpected persistence failure |
