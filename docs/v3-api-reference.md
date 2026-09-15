# V3 API Reference

Complete reference of all V3 API endpoints. All endpoints are under the `api/v3` prefix and require `auth.v3.cookie` + `tenant.v3.context` middleware unless otherwise noted.

## Summary

| Group | Prefix | Routes | Documentation |
|---|---|---|---|
| Authentication | `auth` | 17 | [Authentication](#authentication) |
| Branches | `branches` | 7 | [Branches](#branches) |
| Issuance Points | `issuance-points` | 2 | [Issuance Points](#issuance-points) |
| Core - Carrier Establishments | `core/carrier-establishments` | 4 | [Core - Carrier Establishments](#core---carrier-establishments) |
| Core - Carrier Emission Points | `core/carrier-emission-points` | 3 | [Core - Carrier Emission Points](#core---carrier-emission-points) |
| Core - Carrier Affiliations | `core/carrier-affiliations` | 4 | [Core - Carrier Affiliations](#core---carrier-affiliations) |
| Core - Carriers | `core/carriers` | 12 | [docs/carrier.md](carrier.md) |
| Core - Companies | `core/companies` | 4 | [Core - Companies](#core---companies) |
| Core - Economic Activities | `core/economic-activities` | 4 | [Core - Economic Activities](#core---economic-activities) |
| Core - Notifications | `core/notifications` | 1 | [Core - Notifications](#core---notifications) |
| Core - Products | `core/products` | 9 | [Core - Products](#core---products) |
| Core - Product Settings | `core/product-settings` | 2 | [Core - Product Settings](#core---product-settings) |
| Core - SRI IVA | `core/sri-iva-types` | 8 | [Core - SRI IVA](#core---sri-iva) |
| Core - Settings | `core/customer-settings`, `core/payment-method-settings`, `core/additional-info-presets` | 8 | [Core - Settings](#core---settings) |
| Core - Third Parties | `core/third-parties` | 6 | [docs/third-party.md](third-party.md) |
| Core - Vehicles | `core/vehicles` | 4 | [Core - Vehicles](#core---vehicles) |
| Catalog (legacy root) | `products`, `product-settings`, `sri-iva-types`, `economic-activities` | 4 | [Catalog (Legacy Root)](#catalog-legacy-root) |
| Fiscal - Invoices | `invoices` | 15 | [Fiscal - Invoices](#fiscal---invoices) |
| Fiscal - Invoice Drafts | `invoice-drafts` | 8 | [Fiscal - Invoice Drafts](#fiscal---invoice-drafts) |
| Fiscal - SRI Admin | `admin` | 3 | [Fiscal - SRI Admin](#fiscal---sri-admin) |
| Fiscal - Bootstrap | `bootstrap` | 1 | [Fiscal - Bootstrap](#fiscal---bootstrap) |
| Platform - Admin | `platform/admin` | 6 | [Platform - Admin](#platform---admin) |
| Platform - RBAC | `roles`, `users`, `permissions`, `menus` | 16 | [Platform - RBAC](#platform---rbac) |
| Platform - Geography | `countries`, `provinces`, `cities` | 3 | [Platform - Geography](#platform---geography) |
| Platform - SRI Environments | `sri-environments` | 1 | [Platform - SRI Environments](#platform---sri-environments) |
| Platform - Contexts | `contexts` | 1 | [Platform - Contexts](#platform---contexts) |
| Platform - Enterprises | `enterprises` | 16 | [Platform - Enterprises](#platform---enterprises) |

**Total: 168 routes**

---

## Response Envelope

All V3 responses follow the standard envelope (except binary responses and 204 No Content):

```json
{
  "status": true|false,
  "message": "",
  "data": ...
}
```

Error responses follow RFC 7807 (`application/problem+json`):

```json
{
  "type": "https://artra.cloud/problems/{errorCode}",
  "title": "Error message",
  "status": 400,
  "code": "error_code",
  "message": "Error message",
  "fieldErrors": []
}
```

## Middleware

All V3 routes (except `auth/challenges` and `auth/sessions`) require:
- `auth.v3.cookie` — validates the V3 session cookie
- `tenant.v3.context` — sets the PostgreSQL `app.tenant_id` session variable for RLS

---

## Authentication

Prefix: `api/v3/auth`

### POST `auth/challenges`

Request a challenge for passwordless auth. **No auth required** (throttled 10/min).

**Body:**
| Field | Validation |
|---|---|
| `email` | required\|email\|max:255 |
| `password` | required\|string\|max:255 |

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": {
    "user": {},
    "enterprises": [],
    "requires_enterprise": true,
    "session_ready": false
  }
}
```
Sets `Set-Cookie` header.

### POST `auth/sessions`

Create a session from a challenge. **No auth required** (throttled 10/min).

**Body:**
| Field | Validation |
|---|---|
| `enterprise_id` | required\|string\|max:36 |

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": { "session_ready": true, "..." : "AuthenticationSessionDTO" }
}
```
Sets `Set-Cookie` header.

### GET `auth/me`

Get current user session.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": { "AuthenticationSessionDTO" }
}
```

### POST `auth/refresh`

Refresh session token.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": { "session_ready": true, "..." : "AuthenticationSessionDTO" }
}
```
Sets `Set-Cookie` header.

### POST `auth/switch-enterprise`

Switch active enterprise.

**Body:**
| Field | Validation |
|---|---|
| `enterprise_id` | required\|string\|max:36 |

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": { "AuthenticationSessionDTO" }
}
```
Sets `Set-Cookie` header.

### DELETE `auth/session`

Destroy current session.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": null
}
```
Forgets session cookie.

### GET `auth/sessions`

List active sessions for the current user.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": []
}
```

### DELETE `auth/sessions`

Revoke all other sessions for the current user.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": null
}
```

### DELETE `auth/sessions/{id}`

Revoke a specific session by ID.

**Path params:** `id` (integer)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": null
}
```

### POST `auth/verify-password`

Verify password for sensitive operations.

**Body:**
| Field | Validation |
|---|---|
| `password` | required\|string |

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": true
}
```

### POST `auth/change-password`

Change password.

**Body:**
| Field | Validation |
|---|---|
| `current_password` | required\|string |
| `new_password` | required\|string\|min:12\|confirmed |

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": null
}
```

### GET `auth/mfa`

Get MFA status.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": {}
}
```

### POST `auth/mfa`

Enable MFA (begin enrollment).

**Body:**
| Field | Validation |
|---|---|
| `current_password` | required\|string |

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": {}
}
```

### POST `auth/mfa/confirm`

Confirm MFA enrollment.

**Body:**
| Field | Validation |
|---|---|
| `code` | required\|string\|digits:6 |

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": {}
}
```

### GET `auth/preferences`

Get user preferences.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": { "preferences": [] }
}
```

### PATCH `auth/preferences`

Update user preferences.

**Body:**
| Field | Validation |
|---|---|
| `preferences` | required\|array |

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": { "preferences": [] }
}
```

### POST `auth/support/users/{id}/password-reset`

Admin-initiated password reset for a user.

**Path params:** `id` (integer)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": null
}
```

---

## Branches

Prefix: `api/v3/branches`

### GET `branches`

List branches.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Sucursales cargadas.",
  "data": []
}
```

### POST `branches`

Create a branch.

**Body:**
| Field | Validation |
|---|---|
| `name` | nullable\|string\|max:150 |
| `sri_establishment_number` | nullable\|string\|max:3 |
| `branch_code` | nullable\|string\|max:25 |
| `address` | nullable\|string\|max:250 |
| `phone` | nullable\|string\|max:30 |
| `email` | nullable\|email\|max:150 |
| `city_id` | nullable\|integer |
| `issuance_point` | nullable\|array |
| `issuance_point.issuance_point_number` | nullable\|string\|max:3 |
| `issuance_point.name` | nullable\|string\|max:150 |
| `issuance_point.is_active` | nullable\|boolean |
| `issuance_point.is_default` | nullable\|boolean |
| `issuance_point.has_tax_validity` | nullable\|boolean |

**Response (201):**
```json
{
  "status": true,
  "message": "Sucursal creada.",
  "data": {}
}
```

### PATCH `branches/{id}`

Update a branch.

**Path params:** `id` (integer)

**Body:**
| Field | Validation |
|---|---|
| `name` | nullable\|string\|max:150 |
| `branch_code` | nullable\|string\|max:25 |
| `address` | nullable\|string\|max:250 |
| `phone` | nullable\|string\|max:30 |
| `email` | nullable\|email\|max:150 |
| `city_id` | nullable\|integer |
| `is_active` | nullable\|boolean |
| `issuance_point` | nullable\|array |
| `issuance_point.name` | nullable\|string\|max:150 |
| `issuance_point.is_active` | nullable\|boolean |
| `issuance_point.is_default` | nullable\|boolean |
| `issuance_point.has_tax_validity` | nullable\|boolean |

**Response (200):**
```json
{
  "status": true,
  "message": "Sucursal actualizada.",
  "data": {}
}
```
404 if not found: `{ "code": "branch_not_found" }`

### DELETE `branches/{id}`

Delete (deactivate) a branch.

**Path params:** `id` (integer)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Sucursal desactivada.",
  "data": { "deleted": true }
}
```
404 if not found: `{ "code": "branch_not_found" }`

### GET `branches/{branch}/issuance-points`

List issuance points for a branch.

**Path params:** `branch` (integer)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Puntos de emisión cargados.",
  "data": []
}
```

### POST `branches/{branch}/issuance-points`

Create an issuance point for a branch.

**Path params:** `branch` (integer)

**Body:**
| Field | Validation |
|---|---|
| `issuance_point_number` | nullable\|string\|max:3 |
| `name` | nullable\|string\|max:150 |
| `is_active` | nullable\|boolean |
| `is_default` | nullable\|boolean |
| `has_tax_validity` | nullable\|boolean |

**Response (201):**
```json
{
  "status": true,
  "message": "Punto de emisión creado.",
  "data": {}
}
```
404 if branch not found: `{ "code": "branch_not_found" }`

### GET `branches/{branch}/issuance-points/{point}/next-sequential`

Get next sequential number for an issuance point.

**Path params:** `branch` (integer), `point` (integer)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Secuencial consultado.",
  "data": {}
}
```
404 if not found: `{ "code": "issuance_point_not_found" }`

---

## Issuance Points

Prefix: `api/v3/issuance-points`

### PATCH `issuance-points/{id}`

Update an issuance point.

**Path params:** `id` (integer)

**Body:**
| Field | Validation |
|---|---|
| `name` | nullable\|string\|max:150 |
| `issuance_point_number` | nullable\|string\|max:3 |
| `is_active` | nullable\|boolean |
| `is_default` | nullable\|boolean |
| `has_tax_validity` | nullable\|boolean |

**Response (200):**
```json
{
  "status": true,
  "message": "Punto de emisión actualizado.",
  "data": {}
}
```
404 if not found: `{ "code": "issuance_point_not_found" }`

### DELETE `issuance-points/{id}`

Delete (deactivate) an issuance point.

**Path params:** `id` (integer)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Punto de emisión desactivado.",
  "data": { "deleted": true }
}
```
404 if not found: `{ "code": "issuance_point_not_found" }`

---

## Core - Carrier Establishments

Prefix: `api/v3/core/carrier-establishments`

### GET `core/carrier-establishments`

List carrier establishments.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Carrier establishments loaded.",
  "data": []
}
```

### POST `core/carrier-establishments`

Create a carrier establishment.

**Body:**
| Field | Validation |
|---|---|
| `carrier_company_id` | required\|string\|uuid |
| `sri_code` | required\|string\|max:10 |
| `name` | required\|string\|max:255 |
| `address` | nullable\|string\|max:255 |
| `phone` | nullable\|string\|max:30 |
| `email` | nullable\|email\|max:150 |
| `city_id` | nullable\|integer |
| `is_active` | nullable\|boolean |
| `activity_ids` | nullable\|array |
| `activity_ids.*` | string\|max:100 |

**Response (201):**
```json
{
  "status": true,
  "message": "Carrier establishment created.",
  "data": {}
}
```

### GET `core/carrier-establishments/{id}`

Get a carrier establishment.

**Path params:** `id` (UUID)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Carrier establishment loaded.",
  "data": {}
}
```

### GET `core/carrier-establishments/{id}/emission-points`

List emission points for a carrier establishment.

**Path params:** `id` (UUID)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Carrier emission points loaded.",
  "data": []
}
```

---

## Core - Carrier Emission Points

Prefix: `api/v3/core/carrier-emission-points`

### GET `core/carrier-emission-points`

List carrier emission points.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Carrier emission points loaded.",
  "data": []
}
```

### POST `core/carrier-emission-points`

Create a carrier emission point.

**Body:**
| Field | Validation |
|---|---|
| `establishment_id` | required\|string\|uuid |
| `sri_code` | required\|string\|max:10 |
| `name` | nullable\|string\|max:255 |
| `is_active` | nullable\|boolean |

**Response (201):**
```json
{
  "status": true,
  "message": "Carrier emission point created.",
  "data": {}
}
```

### GET `core/carrier-emission-points/{id}`

Get a carrier emission point.

**Path params:** `id` (UUID)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Carrier emission point loaded.",
  "data": {}
}
```

---

## Core - Carrier Affiliations

Prefix: `api/v3/core/carrier-affiliations`

### GET `core/carrier-affiliations`

List carrier affiliations.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Carrier affiliations loaded.",
  "data": []
}
```

### POST `core/carrier-affiliations`

Create a carrier affiliation.

**Body:**
| Field | Validation |
|---|---|
| `third_party_id` | required\|string\|uuid |
| `validity` | nullable\|string |
| `vehicle_assignments` | nullable\|array |
| `vehicle_assignments.*.vehicle_id` | required\|string\|uuid |
| `vehicle_assignments.*.validity` | nullable\|string |

**Response (201):**
```json
{
  "status": true,
  "message": "Carrier affiliation created.",
  "data": {}
}
```

### GET `core/carrier-affiliations/{id}`

Get a carrier affiliation.

**Path params:** `id` (UUID)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Carrier affiliation loaded.",
  "data": {}
}
```

### PATCH `core/carrier-affiliations/{id}`

Update a carrier affiliation.

**Path params:** `id` (UUID)

**Body:**
| Field | Validation |
|---|---|
| `validity` | nullable\|string |
| `vehicle_assignments` | nullable\|array |
| `vehicle_assignments.*.vehicle_id` | required\|string\|uuid |
| `vehicle_assignments.*.validity` | nullable\|string |

**Response (200):**
```json
{
  "status": true,
  "message": "Carrier affiliation updated.",
  "data": {}
}
```

---

## Core - Carriers

Prefix: `api/v3/core/carriers`

Full documentation: [docs/carrier.md](carrier.md)

### GET `core/carriers`

List carriers with optional filters.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Socios transportistas cargados.",
  "data": []
}
```

### POST `core/carriers/onboard`

Onboard a new or existing carrier (idempotent).

**Body:**
| Field | Validation |
|---|---|
| `name` | required\|string\|max:255 |
| `legal_name` | required\|string\|max:255 |
| `trade_name` | nullable\|string\|max:255 |
| `person_type` | sometimes\|nullable\|string\|in:natural,juridical |
| `identification` | required\|string\|max:32\|regex:/^[A-Za-z0-9._-]+$/ |
| `identification_type` | required\|string\|in:04,05,06,07,RUC,CED,PAS,CF |
| `address` | nullable\|string\|max:255 |
| `phone` | nullable\|string\|max:30 |
| `email` | nullable\|email\|max:150 |
| `activity_id` | nullable\|string\|max:100 |
| `activity_valid_from` | nullable\|date_format:Y-m-d |
| `plates` | nullable\|array\|max:100 |
| `plates.*` | string\|max:20 |
| `plate` | nullable\|string\|max:20 |
| `issuer_mode` | sometimes\|nullable\|in:operator,partner |
| `operator_establishment_code` | nullable\|digits:3 |
| `operator_emission_point_code` | nullable\|digits:3 |
| `partner_ruc` | required_if:issuer_mode,partner\|digits:13 |
| `partner_establishment_code` | required_if:issuer_mode,partner\|digits:3 |
| `partner_emission_point_code` | required_if:issuer_mode,partner\|digits:3 |
| `partner_next_sequential` | required_if:issuer_mode,partner\|integer\|between:1,999999999 |
| `partner_key_reference` | required_if:issuer_mode,partner\|string\|max:255 |
| `payment_account` | nullable\|array |

**Response (201):**
```json
{
  "status": true,
  "message": "Socio transportista creado o reutilizado.",
  "data": {}
}
```

### GET `core/carriers/{id}`

Get full carrier detail by ThirdParty UUID.

**Path params:** `id` (UUID)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Ficha del socio transportista cargada.",
  "data": {}
}
```

### PATCH `core/carriers/{id}`

Update carrier profile.

**Path params:** `id` (UUID)

**Body:**
| Field | Validation |
|---|---|
| `name` | sometimes\|string\|max:255 |
| `legal_name` | sometimes\|string\|max:255 |
| `trade_name` | nullable\|string\|max:255 |
| `address` | nullable\|string\|max:255 |
| `phone` | nullable\|string\|max:30 |
| `email` | nullable\|email\|max:150 |
| `is_active` | sometimes\|boolean |
| `activity_id` | nullable\|string\|max:100 |
| `activity_valid_from` | nullable\|date_format:Y-m-d |
| `establishment_code` | nullable\|digits:3 |
| `plates` | nullable\|array\|max:100 |
| `plates.*` | string\|max:20 |
| `plate` | nullable\|string\|max:20 |

**Response (200):**
```json
{
  "status": true,
  "message": "Ficha del socio transportista actualizada.",
  "data": {}
}
```

### PATCH `core/carriers/{id}/payment-account`

Update payment account.

**Path params:** `id` (UUID)

**Body:**
| Field | Validation |
|---|---|
| `account_holder_name` | required\|string\|max:160 |
| `account_holder_identification` | required\|string\|max:32 |
| `financial_institution` | required\|string\|max:120 |
| `account_type` | required\|in:savings,checking,other |
| `account_number` | nullable\|digits_between:4,30 |
| `currency` | nullable\|in:USD |
| `verification_status` | nullable\|in:pending,verified,blocked |
| `is_active` | nullable\|boolean |

**Response (200):**
```json
{
  "status": true,
  "message": "Cuenta de pago actualizada.",
  "data": {}
}
```

### PATCH `core/carriers/{id}/signature`

Update signature metadata.

**Path params:** `id` (UUID)

**Body:**
| Field | Validation |
|---|---|
| `status` | required\|in:missing,active,expired,revoked,invalid |
| `certificate_fingerprint` | nullable\|string\|max:128 |
| `certificate_serial` | nullable\|string\|max:255 |
| `certificate_subject` | nullable\|string\|max:255 |
| `valid_from` | nullable\|date |
| `valid_until` | nullable\|date |
| `key_reference` | nullable\|string\|max:255 |
| `key_version` | nullable\|integer\|min:1 |

**Response (200):**
```json
{
  "status": true,
  "message": "Metadatos de firma actualizados.",
  "data": {}
}
```

### POST `core/carriers/{id}/documents`

Record a received document.

**Path params:** `id` (UUID)

**Body:**
| Field | Validation |
|---|---|
| `document_type` | required\|in:invoice,credit_note,debit_note,other |
| `reference` | required\|string\|max:120 |
| `issue_date` | required\|date_format:Y-m-d |
| `total` | required\|regex:/^(?:0\|[1-9][0-9]*)(?:\.[0-9]{1,2})?$/ |
| `xml_base64` | required\|string\|max:14000000 |
| `operation_id` | nullable\|uuid |
| `original_document_id` | nullable\|uuid |
| `reason` | nullable\|string\|max:500 |
| `fiscal_status` | nullable\|in:draft,received,authorized,imported |
| `access_key` | nullable\|string\|max:100 |
| `authorization_number` | nullable\|string\|max:100 |
| `affects_transport` | nullable\|boolean |

**Response (201):**
```json
{
  "status": true,
  "message": "Documento recibido registrado.",
  "data": {}
}
```

### DELETE `core/carriers/{id}/documents/{documentId}`

Cancel a received document.

**Path params:** `id` (UUID), `documentId` (UUID)

**Body:**
| Field | Validation |
|---|---|
| `reason` | nullable\|string |

**Response (200):**
```json
{
  "status": true,
  "message": "Documento recibido cancelado.",
  "data": {}
}
```

### POST `core/carriers/{id}/allocations`

Create a settlement allocation (idempotent).

**Path params:** `id` (UUID)

**Body:**
| Field | Validation |
|---|---|
| `operation_id` | required\|uuid |
| `received_document_id` | required\|uuid |
| `amount` | required\|regex:/^(?:0\|[1-9][0-9]*)(?:\.[0-9]{1,2})?$/ |

**Response (201):**
```json
{
  "status": true,
  "message": "Conciliación registrada.",
  "data": {}
}
```

### DELETE `core/carriers/{id}/allocations/{allocationId}`

Reverse an allocation.

**Path params:** `id` (UUID), `allocationId` (UUID)

**Body:**
| Field | Validation |
|---|---|
| `reason` | nullable\|string |

**Response (200):**
```json
{
  "status": true,
  "message": "Conciliación revertida.",
  "data": {}
}
```

### POST `core/carriers/{id}/operations/{operationId}/settlement-review/clear`

Clear pending credit note review.

**Path params:** `id` (UUID), `operationId` (UUID)

**Body:**
| Field | Validation |
|---|---|
| `reason` | nullable\|string |

**Response (200):**
```json
{
  "status": true,
  "message": "Regularización confirmada.",
  "data": {}
}
```

### GET `core/carriers/{id}/audit`

Get carrier audit trail.

**Path params:** `id` (UUID)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Auditoría cargada.",
  "data": {}
}
```

---

## Core - Companies

Prefix: `api/v3/core/companies`

### GET `core/companies`

List companies.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Companies loaded.",
  "data": []
}
```

### POST `core/companies`

Create a company.

**Body:**
| Field | Validation |
|---|---|
| `name` | required\|string\|max:255 |
| `ruc` | required\|string\|max:13 |
| `legal_name` | nullable\|string\|max:255 |
| `trade_name` | nullable\|string\|max:255 |
| `matrix_address` | nullable\|string\|max:255 |
| `operations_start_date` | nullable\|date |
| `city_id` | nullable\|integer |
| `phone` | nullable\|string\|max:30 |
| `corporate_email` | nullable\|email\|max:150 |
| `activity_ids` | nullable\|array |
| `activity_ids.*` | string\|max:100 |

**Response (201):**
```json
{
  "status": true,
  "message": "Company created.",
  "data": {}
}
```

### GET `core/companies/{id}`

Get a company.

**Path params:** `id` (integer)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Company loaded.",
  "data": {}
}
```

### PATCH `core/companies/{id}`

Update a company.

**Path params:** `id` (integer)

**Body:**
| Field | Validation |
|---|---|
| `name` | sometimes\|string\|max:255 |
| `ruc` | sometimes\|string\|max:13 |
| `legal_name` | nullable\|string\|max:255 |
| `trade_name` | nullable\|string\|max:255 |
| `matrix_address` | nullable\|string\|max:255 |
| `operations_start_date` | nullable\|date |
| `city_id` | nullable\|integer |
| `phone` | nullable\|string\|max:30 |
| `corporate_email` | nullable\|email\|max:150 |
| `activity_ids` | nullable\|array |
| `activity_ids.*` | string\|max:100 |

**Response (200):**
```json
{
  "status": true,
  "message": "Company updated.",
  "data": {}
}
```

---

## Core - Economic Activities

Prefix: `api/v3/core/economic-activities`

### GET `core/economic-activities`

List economic activities.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Economic activities loaded.",
  "data": []
}
```

### POST `core/economic-activities`

Create an economic activity.

**Body:**
| Field | Validation |
|---|---|
| `id` | nullable\|string\|max:100 |
| `name` | required\|string\|max:255 |
| `catalog_version` | nullable\|string\|in:synthetic-lab-v1,staging-legacy-v1 |

**Response (201):**
```json
{
  "status": true,
  "message": "Economic activity created.",
  "data": {}
}
```

### GET `core/economic-activities/{id}`

Get an economic activity.

**Path params:** `id` (string)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Economic activity loaded.",
  "data": {}
}
```

### PATCH `core/economic-activities/{id}`

Update an economic activity.

**Path params:** `id` (string)

**Body:**
| Field | Validation |
|---|---|
| `name` | sometimes\|string\|max:255 |
| `catalog_version` | nullable\|string\|in:synthetic-lab-v1,staging-legacy-v1 |

**Response (200):**
```json
{
  "status": true,
  "message": "Economic activity updated.",
  "data": {}
}
```

---

## Core - Notifications

Prefix: `api/v3/core/notifications`

### POST `core/notifications/test-email`

Send a test email.

**Body:**
| Field | Validation |
|---|---|
| `to` | required\|email\|max:150 |
| `subject` | required\|string\|max:255 |
| `body` | required\|string |
| `is_html` | nullable\|boolean |

**Response (200):**
```json
{
  "status": true,
  "message": "Test email sent.",
  "data": { "sent": true }
}
```

---

## Core - Products

Prefix: `api/v3/core/products`

### GET `core/products`

List products.

**Query params:**
| Field | Validation |
|---|---|
| `search` | nullable\|string\|max:255 |
| `q` | nullable\|string\|max:255 |
| `per_page` | nullable\|integer\|min:1\|max:500 |
| `is_active` | nullable\|string\|in:0,1,all |

**Response (200):**
```json
{
  "status": true,
  "message": "Productos cargados.",
  "data": []
}
```

### POST `core/products`

Create a product.

**Body:**
| Field | Validation |
|---|---|
| `name` | required\|string\|max:200 |
| `reference_price` | required\|numeric\|min:0\|decimal:0,6 |
| `activity_id` | required\|string\|max:100 |
| `barcode` | nullable\|string\|max:100 |
| `auxiliary_code` | nullable\|string\|max:50 |
| `other_code` | nullable\|string\|max:50 |
| `description` | nullable\|string |
| `type` | nullable\|string\|in:product,service |
| `is_active` | nullable\|boolean |
| `sri_iva_type_ids` | nullable\|array |
| `sri_iva_type_ids.*` | integer |

**Response (201):**
```json
{
  "status": true,
  "message": "Producto creado.",
  "data": {}
}
```

### GET `core/products/duplicates`

List duplicate products (availability check).

**Body:** none (uses query params)

**Response (200):**
```json
{
  "status": true,
  "message": "Disponibilidad del producto verificada.",
  "data": {}
}
```

### PATCH `core/products/{id}`

Update a product.

**Path params:** `id` (integer)

**Body:**
| Field | Validation |
|---|---|
| `name` | sometimes\|string\|max:200 |
| `reference_price` | sometimes\|numeric\|min:0\|decimal:0,6 |
| `activity_id` | sometimes\|string\|max:100 |
| `barcode` | nullable\|string\|max:100 |
| `auxiliary_code` | nullable\|string\|max:50 |
| `other_code` | nullable\|string\|max:50 |
| `description` | nullable\|string |
| `type` | sometimes\|string\|in:product,service |
| `is_active` | sometimes\|boolean |
| `sri_iva_type_ids` | nullable\|array |
| `sri_iva_type_ids.*` | integer |

**Response (200):**
```json
{
  "status": true,
  "message": "Producto actualizado.",
  "data": {}
}
```
404 if not found: `{ "code": "product_not_found" }`

### DELETE `core/products/{id}`

Delete (deactivate) a product.

**Path params:** `id` (integer)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Producto desactivado.",
  "data": {}
}
```
404 if not found: `{ "code": "product_not_found" }`

### PATCH `core/products/{id}/status`

Change product status.

**Path params:** `id` (integer)

**Body:**
| Field | Validation |
|---|---|
| `is_active` | nullable\|boolean (default: true) |

**Response (200):**
```json
{
  "status": true,
  "message": "Estado del producto actualizado.",
  "data": {}
}
```
404 if not found: `{ "code": "product_not_found" }`

### POST `core/products/{product}/taxes`

Add a product tax.

**Path params:** `product` (integer)

**Body:**
| Field | Validation |
|---|---|
| `sri_iva_type_id` | required\|integer |
| `tax_name` | nullable\|string\|max:100 |
| `percentage` | nullable\|numeric\|min:0\|max:100\|decimal:0,6 |
| `sri_code` | nullable\|string\|max:25 |
| `sri_iva_percentage_id` | nullable\|integer |

**Response (201):**
```json
{
  "status": true,
  "message": "Impuesto del producto guardado.",
  "data": {}
}
```

### DELETE `core/products/{product}/taxes/{tax}`

Remove a product tax.

**Path params:** `product` (integer), `tax` (integer)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Impuesto del producto eliminado.",
  "data": { "deleted": true }
}
```
404 if not found: `{ "code": "product_tax_not_found" }`

---

## Core - Product Settings

Prefix: `api/v3/core/product-settings`

### GET `core/product-settings`

List product settings.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Configuración de productos cargada.",
  "data": {}
}
```

### POST `core/product-settings`

Create/update product settings.

**Body:**
| Field | Validation |
|---|---|
| `allow_duplicate_names` | required\|boolean |
| `require_barcode` | required\|boolean |
| `require_auxiliary_code` | required\|boolean |
| `auxiliary_code_prefix` | nullable\|string\|max:20\|regex:/^[A-Za-z0-9._-]*$/ |
| `default_product_type` | required\|in:product,service |
| `default_iva_type_id` | nullable\|integer |
| `require_description` | required\|boolean |

**Response (200):**
```json
{
  "status": true,
  "message": "Configuración de productos guardada.",
  "data": {}
}
```

---

## Core - SRI IVA

Prefix: `api/v3/core/sri-iva-types`, `api/v3/core/sri-iva-percentages`

### GET `core/sri-iva-types`

List SRI IVA types.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Tipos de IVA cargados.",
  "data": []
}
```

### POST `core/sri-iva-types`

Create an SRI IVA type.

**Body:**
| Field | Validation |
|---|---|
| `name` | required\|string\|max:100 |
| `percentage` | required\|numeric\|between:0,100 |
| `sri_code` | required\|string\|max:10 |

**Response (201):**
```json
{
  "status": true,
  "message": "Tipo de IVA creado.",
  "data": {}
}
```

### PATCH `core/sri-iva-types/{id}`

Update an SRI IVA type.

**Path params:** `id` (integer)

**Body:**
| Field | Validation |
|---|---|
| `name` | sometimes\|required\|string\|max:100 |
| `percentage` | sometimes\|required\|numeric\|between:0,100 |
| `sri_code` | sometimes\|required\|string\|max:10 |

**Response (200):**
```json
{
  "status": true,
  "message": "Tipo de IVA actualizado.",
  "data": {}
}
```

### DELETE `core/sri-iva-types/{id}`

Delete an SRI IVA type.

**Path params:** `id` (integer)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Tipo de IVA desactivado.",
  "data": null
}
```

### GET `core/sri-iva-types/{type}/percentages`

List IVA percentages for a type.

**Path params:** `type` (integer)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Porcentajes de IVA cargados.",
  "data": []
}
```

### POST `core/sri-iva-types/{type}/percentages`

Create an IVA percentage.

**Path params:** `type` (integer)

**Body:**
| Field | Validation |
|---|---|
| `percentage` | required\|numeric\|between:0,100 |
| `start_date` | required\|date |
| `end_date` | nullable\|date\|after_or_equal:start_date |
| `code` | nullable\|string\|max:20 |

**Response (201):**
```json
{
  "status": true,
  "message": "Porcentaje de IVA creado.",
  "data": {}
}
```

### PATCH `core/sri-iva-percentages/{id}`

Update an IVA percentage.

**Path params:** `id` (integer)

**Body:**
| Field | Validation |
|---|---|
| `percentage` | sometimes\|required\|numeric\|between:0,100 |
| `start_date` | sometimes\|required\|date |
| `end_date` | nullable\|date\|after_or_equal:start_date |
| `code` | sometimes\|required\|string\|max:20 |

**Response (200):**
```json
{
  "status": true,
  "message": "Porcentaje de IVA actualizado.",
  "data": {}
}
```

### DELETE `core/sri-iva-percentages/{id}`

Delete an IVA percentage.

**Path params:** `id` (integer)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Porcentaje de IVA desactivado.",
  "data": null
}
```

---

## Core - Settings

Prefix: `api/v3/core/customer-settings`, `api/v3/core/payment-method-settings`, `api/v3/core/additional-info-presets`

### PATCH `core/customer-settings`

Update customer settings.

**Body:**
| Field | Validation |
|---|---|
| `allow_multiple_plates` | nullable\|boolean |

**Response (200):**
```json
{
  "status": true,
  "message": "Configuración de clientes guardada.",
  "data": {}
}
```

### GET `core/payment-method-settings`

List payment method settings.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Métodos de pago cargados.",
  "data": []
}
```

### PATCH `core/payment-method-settings/{code}`

Update payment method settings.

**Path params:** `code` (string)

**Body:**
| Field | Validation |
|---|---|
| `alias` | nullable\|string\|max:100 |
| `is_active` | nullable\|boolean |

**Response (200):**
```json
{
  "status": true,
  "message": "Método de pago actualizado.",
  "data": {}
}
```
404 if not found: `{ "code": "payment_method_not_found" }`

### GET `core/additional-info-presets`

List additional info presets.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Datos adicionales cargados.",
  "data": []
}
```

### GET `core/additional-info-presets/available`

List available presets.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Datos adicionales disponibles.",
  "data": []
}
```

### POST `core/additional-info-presets`

Create a preset.

**Body:**
| Field | Validation |
|---|---|
| `code` | nullable\|string\|max:50 |
| `name` | nullable\|string\|max:150 |
| `default_value` | nullable\|string\|max:500 |
| `auto_apply` | nullable\|boolean |
| `value_editable` | nullable\|boolean |
| `is_required` | nullable\|boolean |
| `is_active` | nullable\|boolean |
| `sort_order` | nullable\|integer\|min:0 |
| `access_rules` | nullable\|array |

**Response (201):**
```json
{
  "status": true,
  "message": "Dato adicional creado.",
  "data": {}
}
```

### PATCH `core/additional-info-presets/{id}`

Update a preset.

**Path params:** `id` (integer)

**Body:**
| Field | Validation |
|---|---|
| `code` | nullable\|string\|max:50 |
| `name` | nullable\|string\|max:150 |
| `default_value` | nullable\|string\|max:500 |
| `auto_apply` | nullable\|boolean |
| `value_editable` | nullable\|boolean |
| `is_required` | nullable\|boolean |
| `is_active` | nullable\|boolean |
| `sort_order` | nullable\|integer\|min:0 |
| `access_rules` | nullable\|array |

**Response (200):**
```json
{
  "status": true,
  "message": "Dato adicional actualizado.",
  "data": {}
}
```
404 if not found: `{ "code": "additional_info_preset_not_found" }`

### DELETE `core/additional-info-presets/{id}`

Delete a preset.

**Path params:** `id` (integer)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Dato adicional desactivado.",
  "data": { "deleted": true }
}
```
404 if not found: `{ "code": "additional_info_preset_not_found" }`

---

## Core - Third Parties

Prefix: `api/v3/core/third-parties`

See [docs/third-party.md](third-party.md) for full documentation.

### POST `core/third-parties`

Create a third party.

**Body:**
| Field | Validation |
|---|---|
| `name` | required\|string\|max:255 |
| `identification` | required\|string\|max:32\|regex:/^[A-Z0-9._-]+$/ |
| `identification_type` | required\|string\|in:04,05,06,07 |
| `must_invoice` | nullable\|boolean |
| `person_type` | nullable\|string\|in:natural,juridical |
| `legacy_id` | nullable\|integer |
| `address` | nullable\|string\|max:255 |
| `phone` | nullable\|string\|max:30 |
| `email` | nullable\|email\|max:150 |
| `customer_type_id` | nullable\|integer |
| `is_active` | nullable\|boolean |
| `role` | sometimes\|string\|in:customer,carrier |
| `roles` | sometimes\|array\|min:1 |
| `roles.*` | string\|in:customer,carrier,supplier,member\|distinct |
| `custom_fields` | sometimes\|array\|max:100 |

**Response (201):**
```json
{
  "status": true,
  "message": "Tercero creado.",
  "data": {}
}
```

### GET `core/third-parties/field-definitions`

List field definitions.

**Query params:**
| Field | Validation |
|---|---|
| `scope` | nullable\|string\|in:customer,carrier,both |
| `active_only` | nullable\|boolean |

**Response (200):**
```json
{
  "status": true,
  "message": "Campos configurables cargados.",
  "data": []
}
```

### POST `core/third-parties/field-definitions`

Create a field definition.

**Body:**
| Field | Validation |
|---|---|
| `code` | required\|string\|max:81\|regex:/^[a-z][a-z0-9_]{1,80}$/ |
| `label` | required\|string\|max:160 |
| `scope` | sometimes\|in:customer,carrier,both |
| `data_type` | sometimes\|in:text,number,date,boolean,select |
| `validation` | sometimes\|array |
| `validation.options` | sometimes\|array\|min:1\|max:100 |
| `validation.options.*` | string\|max:160 |
| `validation.min_length` | sometimes\|integer\|min:0\|max:10000 |
| `validation.max_length` | sometimes\|integer\|min:0\|max:10000 |
| `validation.min` | sometimes\|numeric |
| `validation.max` | sometimes\|numeric |
| `sort_order` | sometimes\|integer\|min:0\|max:100000 |
| `is_required` | sometimes\|boolean |
| `is_active` | sometimes\|boolean |

**Response (201):**
```json
{
  "status": true,
  "message": "Campo configurable creado.",
  "data": {}
}
```

### PATCH `core/third-parties/field-definitions/{definition}`

Update a field definition.

**Path params:** `definition` (UUID)

**Body:**
| Field | Validation |
|---|---|
| `code` | sometimes\|required\|string\|max:81\|regex:/^[a-z][a-z0-9_]{1,80}$/ |
| `label` | sometimes\|required\|string\|max:160 |
| `scope` | sometimes\|in:customer,carrier,both |
| `data_type` | sometimes\|in:text,number,date,boolean,select |
| `validation` | sometimes\|array |
| `validation.options` | sometimes\|array\|min:1\|max:100 |
| `validation.options.*` | string\|max:160 |
| `validation.min_length` | sometimes\|integer\|min:0\|max:10000 |
| `validation.max_length` | sometimes\|integer\|min:0\|max:10000 |
| `validation.min` | sometimes\|numeric |
| `validation.max` | sometimes\|numeric |
| `sort_order` | sometimes\|integer\|min:0\|max:100000 |
| `is_required` | sometimes\|boolean |
| `is_active` | sometimes\|boolean |

**Response (200):**
```json
{
  "status": true,
  "message": "Campo configurable actualizado.",
  "data": {}
}
```
404 if not found: `{ "code": "third_party_field_not_found" }`

### DELETE `core/third-parties/field-definitions/{definition}`

Deactivate a field definition.

**Path params:** `definition` (UUID)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Campo configurable desactivado.",
  "data": {}
}
```
404 if not found: `{ "code": "third_party_field_not_found" }`

### GET `core/third-parties/availability`

Check identification availability.

**Query params:**
| Field | Validation |
|---|---|
| `identification` | nullable\|string\|max:50 |
| `identification_type` | nullable\|string\|in:04,05,06,07 |
| `exclude_id` | nullable\|string\|max:100 |

**Response (200):**
```json
{
  "status": true,
  "message": "Disponibilidad de identificación verificada.",
  "data": {}
}
```

---

## Core - Vehicles

Prefix: `api/v3/core/vehicles`

### GET `core/vehicles`

List vehicles.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Vehicles loaded.",
  "data": []
}
```

### POST `core/vehicles`

Create a vehicle.

**Body:**
| Field | Validation |
|---|---|
| `plate` | required\|string\|max:20 |
| `legacy_id` | nullable\|string\|max:255 |

**Response (201):**
```json
{
  "status": true,
  "message": "Vehicle created.",
  "data": {}
}
```

### GET `core/vehicles/{id}`

Get a vehicle.

**Path params:** `id` (integer)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Vehicle loaded.",
  "data": {}
}
```

### PATCH `core/vehicles/{id}`

Update a vehicle.

**Path params:** `id` (integer)

**Body:**
| Field | Validation |
|---|---|
| `plate` | sometimes\|string\|max:20 |
| `legacy_id` | nullable\|string\|max:255 |

**Response (200):**
```json
{
  "status": true,
  "message": "Vehicle updated.",
  "data": {}
}
```

---

## Catalog (Legacy Root)

Prefix: `api/v3/` (root, no `core/` prefix)

These endpoints are retained for legacy V3 frontend compatibility. They reuse the Core application controllers and tenant context.

### GET `products`

List products (same as `core/products`).

**Query params:**
| Field | Validation |
|---|---|
| `search` | nullable\|string\|max:255 |
| `q` | nullable\|string\|max:255 |
| `per_page` | nullable\|integer\|min:1\|max:500 |
| `is_active` | nullable\|string\|in:0,1,all |

**Response (200):**
```json
{
  "status": true,
  "message": "Productos cargados.",
  "data": []
}
```

### GET `product-settings`

List product settings (same as `core/product-settings`).

**Response (200):**
```json
{
  "status": true,
  "message": "Configuración de productos cargada.",
  "data": {}
}
```

### GET `sri-iva-types`

List SRI IVA types (same as `core/sri-iva-types`).

**Response (200):**
```json
{
  "status": true,
  "message": "Tipos de IVA cargados.",
  "data": []
}
```

### GET `economic-activities`

List economic activities (same as `core/economic-activities`).

**Response (200):**
```json
{
  "status": true,
  "message": "Economic activities loaded.",
  "data": []
}
```

---

## Fiscal - Invoices

Prefix: `api/v3/invoices`

### GET `invoices`

List invoices.

**Body:** none

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "document_number": "001-001-000000001",
      "document_type": "invoice",
      "issued_at": "2026-09-15T12:00:00Z",
      "fiscal_status": "authorized",
      "sri_status": "authorized",
      "authorization_number": "...",
      "authorization_date": "...",
      "not_valid_for_sri": false,
      "fiscal_status_summary": "...",
      "establishment_code": "001",
      "emission_point_code": "001",
      "sequential": 1,
      "subtotal": "100.00",
      "tax": "15.00",
      "discount": "0.00",
      "total": "115.00",
      "recipient": {},
      "issuer": {},
      "details": [],
      "payments": [],
      "additional_info": [],
      "cancellation_capabilities": []
    }
  ],
  "meta": { "total": 1, "per_page": 15, "current_page": 1, "last_page": 1 }
}
```

### POST `invoices`

Create an invoice.

**Body:**
| Field | Validation |
|---|---|
| `emission_point_id` | required\|string |
| `establishment_code` | required\|string |
| `emission_point_code` | required\|string |
| `recipient` | required\|array |
| `issuer` | required\|array |
| `lines` | required\|array |
| `lines.*.product_id` | nullable\|string |
| `lines.*.description_snapshot` | nullable\|string |
| `lines.*.quantity` | required\|numeric |
| `lines.*.unit_price` | required\|numeric |
| `lines.*.subtotal` | required\|numeric |
| `lines.*.tax` | nullable\|numeric |
| `lines.*.discount` | nullable\|numeric |
| `lines.*.total` | nullable\|numeric |
| `payments` | nullable\|array |
| `payments.*.payment_method_code` | required\|string |
| `payments.*.total` | required\|numeric |
| `subtotal` | required\|numeric |
| `tax` | required\|numeric |
| `total` | required\|numeric |
| `discount` | nullable\|numeric |
| `due_at` | nullable\|string |
| `idempotency_key` | nullable\|string |

**Response (201):** Full Invoice object (same shape as GET `invoices/{id}`)

### GET `invoices/export`

Export invoices as CSV.

**Body:** none

**Response (200):** `text/csv` stream with `Content-Disposition: attachment; filename="invoices.csv"`

### GET `invoices/payment-methods`

List payment methods.

**Body:** none

**Response (200):**
```json
[
  { "code": "01", "name": "Sin utilización del sistema financiero" },
  { "code": "19", "name": "Tarjeta de crédito" }
]
```

### GET `invoices/readiness`

Check invoice readiness.

**Body:** none

**Response (200):**
```json
{
  "ready": true,
  "mode": "mock",
  "blockers": [],
  "sequential": null
}
```

### GET `invoices/summary`

Get invoice summary.

**Body:** none

**Response (200):**
```json
{
  "simulated": 0,
  "authorized": 0,
  "voided": 0,
  "rejected": 0,
  "authorized_total": "0.00",
  "simulated_total": "0.00"
}
```

### GET `invoices/{id}`

Get an invoice.

**Path params:** `id` (integer)

**Body:** none

**Response (200):** Full Invoice object. 404 if not found: `{ "code": "invoice_not_found" }`

### DELETE `invoices/{id}`

Void an invoice.

**Path params:** `id` (integer)

**Body:**
| Field | Validation |
|---|---|
| `reason_code` | nullable\|string |
| `reason_note` | nullable\|string |

**Response (200):**
```json
{ "deleted": true }
```
404 if not found: `{ "code": "invoice_not_found" }`

### GET `invoices/{id}/cancellation-workflows`

List cancellation workflows for an invoice.

**Path params:** `id` (integer)

**Body:** none

**Response (200):**
```json
{
  "capabilities": [],
  "reasons": []
}
```
404 if not found: `{ "code": "invoice_not_found" }`

### POST `invoices/{id}/cancellation-workflows/verify`

Verify a cancellation workflow.

**Path params:** `id` (integer)

**Body:** none

**Response (200):**
```json
{ "verified": true }
```
404 if not found: `{ "code": "invoice_not_found" }`

### POST `invoices/{id}/sri-submissions`

Authorize (submit to SRI) an invoice.

**Path params:** `id` (integer)

**Body:** none

**Response (200):** Full Invoice object. 404 if not found: `{ "code": "invoice_not_found" }`

### GET `invoices/{id}/xml`

Get XML artifact.

**Path params:** `id` (integer)

**Body:** none

**Response (200):** `application/xml` binary. 404 if not found: `{ "code": "artifact_not_found" }`

### GET `invoices/{id}/signed-xml`

Get signed XML artifact.

**Path params:** `id` (integer)

**Body:** none

**Response (200):** `application/xml` binary. 404 if not found: `{ "code": "artifact_not_found" }`

### GET `invoices/{id}/ride`

Get RIDE PDF.

**Path params:** `id` (integer)

**Body:** none

**Response (200):** `application/pdf` binary. 404 if not found: `{ "code": "artifact_not_found" }`

### GET `invoices/{id}/sri-status`

Get SRI status for an invoice.

**Path params:** `id` (integer)

**Body:** none

**Response (200):**
```json
{
  "sri_status": "authorized",
  "fiscal_status": "authorized",
  "authorization_number": "..."
}
```
404 if not found: `{ "code": "invoice_not_found" }`

---

## Fiscal - Invoice Drafts

Prefix: `api/v3/invoice-drafts`

### GET `invoice-drafts`

List invoice drafts for the current user.

**Body:** none

**Response (200):**
```json
{
  "data": [
    {
      "id": "uuid",
      "public_id": "uuid",
      "revision": 1,
      "payload": {},
      "summary": {},
      "expires_at": "2026-09-15T12:00:00Z",
      "updated_at": "2026-09-15T12:00:00Z"
    }
  ]
}
```

### POST `invoice-drafts`

Save (create or update) an invoice draft.

**Body:**
| Field | Validation |
|---|---|
| `payload` | required\|array |
| `revision` | nullable\|integer |
| `public_id` | nullable\|string |

**Response (200):**
```json
{
  "id": "uuid",
  "public_id": "uuid",
  "revision": 1,
  "payload": {},
  "summary": {},
  "expires_at": "2026-09-15T12:00:00Z",
  "updated_at": "2026-09-15T12:00:00Z"
}
```

### GET `invoice-drafts/current`

Get current draft for the authenticated user.

**Body:** none

**Response (200):** Draft object (same as POST response) or **204 No Content** if none exists.

### PUT `invoice-drafts/current`

Save (update) the current draft.

**Body:**
| Field | Validation |
|---|---|
| `payload` | required\|array |
| `revision` | nullable\|integer |
| `public_id` | nullable\|string |

**Response (200):** Draft object (same as POST response).

### DELETE `invoice-drafts/current`

Delete the current draft.

**Body:** none

**Response:** **204 No Content**

### PUT `invoice-drafts/{publicId}`

Save (update) a draft by public ID.

**Path params:** `publicId` (string)

**Body:**
| Field | Validation |
|---|---|
| `payload` | required\|array |
| `revision` | nullable\|integer |
| `public_id` | nullable\|string |

**Response (200):** Draft object (same as POST response).

### DELETE `invoice-drafts/{publicId}`

Delete a draft by public ID.

**Path params:** `publicId` (string)

**Query params:** `revision` (integer, optional)

**Body:** none

**Response:** **204 No Content**

### OPTIONS `invoice-drafts/{publicId}`

CORS preflight for draft operations.

**Path params:** `publicId` (string)

**Body:** none

**Response:** **204 No Content**

---

## Fiscal - SRI Admin

Prefix: `api/v3/admin`

### PATCH `admin/fiscal-sri-mode`

Update SRI mode (mock/celcer/sri).

**Body:**
| Field | Validation |
|---|---|
| `mode` | required\|string (allowed: `mock`, `celcer`, `sri`; not validated by FormRequest) |

**Response (200):**
```json
{
  "status": true,
  "message": "Modo fiscal actualizado.",
  "data": { "mode": "mock" }
}
```
422 if invalid mode.

### GET `admin/fiscal-dispatch-control`

Get dispatch control state.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Control fiscal cargado.",
  "data": {
    "environment": "pruebas",
    "state": "running",
    "scope": "tenant",
    "version": 1,
    "reason_code": null,
    "reason_detail": null,
    "paused_at": null,
    "updated_at": "2026-09-15T12:00:00Z"
  }
}
```

### PATCH `admin/fiscal-dispatch-control`

Update dispatch control.

**Body:**
| Field | Validation |
|---|---|
| `state` | required\|string (not validated by FormRequest) |
| `reason_code` | nullable\|string (not validated by FormRequest) |
| `reason_detail` | nullable\|string (not validated by FormRequest) |

**Response (200):** Same shape as GET response. 409 on conflict.

---

## Fiscal - Bootstrap

Prefix: `api/v3/bootstrap`

### GET `bootstrap/nueva-factura`

Get invoice editor context.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "Contexto consolidado cargado.",
  "data": {
    "company": { "id": 1, "legal_name": "...", "tradename": "...", "ruc": "..." },
    "branches": [],
    "issuance_points": [],
    "sequences": []
  }
}
```
401 if unauthorized: `{ "code": "unauthorized" }`

---

## Platform - Admin

Prefix: `api/v3/platform/admin`

### GET `platform/admin/overview`

Get platform overview.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": {
    "metrics": {},
    "tenant_statuses": [],
    "tenants": [],
    "contracts": [],
    "support_tickets": [],
    "users": [],
    "activity": [],
    "invoices": []
  }
}
```

### GET `platform/admin/tenants`

List tenants.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": []
}
```

### GET `platform/admin/contracts`

List contracts.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": []
}
```

### GET `platform/admin/invoices`

List platform invoices.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": []
}
```

### GET `platform/admin/support/tickets`

List support tickets.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": []
}
```

### GET `platform/admin/users`

List platform users.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": []
}
```

---

## Platform - RBAC

Prefix: `api/v3/roles`, `api/v3/users`, `api/v3/permissions`, `api/v3/menus`

### GET `users`

List users.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": []
}
```

### POST `users`

Create a user.

**Body:**
| Field | Validation |
|---|---|
| `email` | required\|email\|max:255 |
| `first_name` | nullable\|string\|max:120 |
| `last_name` | nullable\|string\|max:120 |
| `name` | nullable\|string\|max:255 |
| `password` | nullable\|string\|min:12 |
| `is_platform_admin` | nullable\|boolean |
| `is_active` | nullable\|boolean |

**Response (201):**
```json
{
  "status": true,
  "message": "...",
  "data": {}
}
```

### PATCH `users/{id}`

Update a user.

**Path params:** `id` (integer)

**Body:**
| Field | Validation |
|---|---|
| `email` | sometimes\|email\|max:255 |
| `first_name` | sometimes\|nullable\|string\|max:120 |
| `last_name` | sometimes\|nullable\|string\|max:120 |
| `password` | sometimes\|nullable\|string\|min:12 |
| `is_platform_admin` | sometimes\|boolean |
| `is_active` | sometimes\|boolean |

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": {}
}
```

### DELETE `users/{id}`

Delete a user.

**Path params:** `id` (integer)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": { "deleted": true }
}
```

### GET `roles`

List roles.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": []
}
```

### POST `roles`

Create a role.

**Body:** Free-form JSON (`SavePlatformConfigurationRequest` accepts `*: nullable`)

**Response (201):**
```json
{
  "status": true,
  "message": "...",
  "data": {}
}
```

### GET `roles/{id}`

Get a role.

**Path params:** `id` (integer)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": {}
}
```

### PATCH `roles/{id}`

Update a role.

**Path params:** `id` (integer)

**Body:** Free-form JSON (`SavePlatformConfigurationRequest` accepts `*: nullable`)

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": {}
}
```

### DELETE `roles/{id}`

Delete a role.

**Path params:** `id` (integer)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": { "deleted": true }
}
```

### POST `roles/{id}/menus`

Assign menus to a role.

**Path params:** `id` (integer)

**Body:** Free-form JSON (`SavePlatformConfigurationRequest` accepts `*: nullable`)

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": {}
}
```

### GET `permissions`

List permissions.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": []
}
```

### GET `menus`

List menus.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": []
}
```

### POST `menus`

Create a menu.

**Body:** Free-form JSON (`SavePlatformConfigurationRequest` accepts `*: nullable`)

**Response (201):**
```json
{
  "status": true,
  "message": "...",
  "data": {}
}
```

### GET `menus/tree`

Get menu tree.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": []
}
```

### PATCH `menus/{id}`

Update a menu.

**Path params:** `id` (integer)

**Body:** Free-form JSON (`SavePlatformConfigurationRequest` accepts `*: nullable`)

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": {}
}
```

### DELETE `menus/{id}`

Delete a menu.

**Path params:** `id` (integer)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": { "deleted": true }
}
```

---

## Platform - Geography

Prefix: `api/v3/countries`, `api/v3/provinces`, `api/v3/cities`

### GET `countries`

List countries.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": []
}
```

### GET `provinces`

List provinces (filtered by `?country_code`).

**Query params:** `country_code` (string, optional)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": []
}
```

### GET `cities`

List cities (filtered by `?country_code` and `?province_id`).

**Query params:** `country_code` (string, optional), `province_id` (integer, optional)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": []
}
```

---

## Platform - SRI Environments

Prefix: `api/v3/sri-environments`

### GET `sri-environments`

List SRI environments (pruebas/produccion).

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": []
}
```

---

## Platform - Contexts

Prefix: `api/v3/contexts`

### GET `contexts/{context}`

Get context configuration.

**Path params:** `context` (string)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": {
    "context": "...",
    "enterprise_uuid": "...",
    "menus": [],
    "permissions": []
  }
}
```

---

## Platform - Enterprises

Prefix: `api/v3/enterprises`

### GET `enterprises`

List enterprises.

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": []
}
```

### POST `enterprises`

Create an enterprise.

**Body:**
| Field | Validation |
|---|---|
| `name` | required\|string\|max:255 |
| `ruc` | required\|string\|max:32 |
| `synthetic` | nullable\|boolean |

**Response (201):**
```json
{
  "status": true,
  "message": "...",
  "data": {}
}
```

### GET `enterprises/{id}`

Get an enterprise.

**Path params:** `id` (integer)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": {}
}
```

### PATCH `enterprises/{id}`

Update an enterprise.

**Path params:** `id` (integer)

**Body:**
| Field | Validation |
|---|---|
| `name` | sometimes\|string\|max:255 |
| `ruc` | sometimes\|string\|max:32 |
| `synthetic` | sometimes\|boolean |

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": {}
}
```

### GET `enterprises/{enterprise}/users`

List enterprise users.

**Path params:** `enterprise` (integer)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": []
}
```

### POST `enterprises/{enterprise}/users`

Create an enterprise user assignment.

**Path params:** `enterprise` (integer)

**Body:**
| Field | Validation |
|---|---|
| `user_id` | sometimes\|integer\|min:1 |
| `is_active` | sometimes\|boolean |
| `capabilities` | sometimes\|array |
| `capabilities.*` | string\|max:120 |

**Response (201):**
```json
{
  "status": true,
  "message": "...",
  "data": {}
}
```

### PATCH `enterprises/{enterprise}/users/{user}`

Update an enterprise user assignment.

**Path params:** `enterprise` (integer), `user` (integer)

**Body:**
| Field | Validation |
|---|---|
| `user_id` | sometimes\|integer\|min:1 |
| `is_active` | sometimes\|boolean |
| `capabilities` | sometimes\|array |
| `capabilities.*` | string\|max:120 |

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": { "updated": true }
}
```

### DELETE `enterprises/{enterprise}/users/{user}`

Delete an enterprise user assignment.

**Path params:** `enterprise` (integer), `user` (integer)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": { "deleted": true }
}
```

### GET `enterprises/{enterprise}/tax-settings`

Get enterprise tax settings.

**Path params:** `enterprise` (integer)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": {}
}
```

### POST `enterprises/{enterprise}/tax-settings`

Save enterprise tax settings.

**Path params:** `enterprise` (integer)

**Body:** Free-form JSON (`SavePlatformConfigurationRequest` accepts `*: nullable`)

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": {}
}
```

### GET `enterprises/{enterprise}/electronic-signature`

Get electronic signature.

**Path params:** `enterprise` (integer)

**Body:** none

**Response:** 404 `PlatformAdministrationException` (not configured in current implementation)

### POST `enterprises/{enterprise}/electronic-signature`

Create electronic signature.

**Path params:** `enterprise` (integer)

**Body:** none

**Response:** 409 `PlatformAdministrationException` (not applicable in current implementation)

### DELETE `enterprises/{enterprise}/electronic-signature`

Delete electronic signature.

**Path params:** `enterprise` (integer)

**Body:** none

**Response:** 409 `PlatformAdministrationException` (not applicable in current implementation)

### GET `enterprises/{enterprise}/sri-certification`

Get SRI certification status.

**Path params:** `enterprise` (integer)

**Body:** none

**Response (200):**
```json
{
  "status": true,
  "message": "...",
  "data": {}
}
```

### POST `enterprises/{enterprise}/sri-certification`

Start SRI certification.

**Path params:** `enterprise` (integer)

**Body:** none

**Response:** 409 `PlatformAdministrationException` (not applicable in current implementation)

### GET `enterprises/{enterprise}/sri-certification/{runId}`

Get SRI certification run.

**Path params:** `enterprise` (integer), `runId` (string)

**Body:** none

**Response:** 409 `PlatformAdministrationException` (not applicable in current implementation)

---

## Related Documentation

- [ThirdParty Module](third-party.md)
- [Carrier Module](carrier.md)
- [Fiscal Worker + SRI](fiscal-worker.md)
- [Worker Documentation](../documentation_worker.md)
