# ThirdParty Module (V3 DDD)

Bounded context for managing third parties (customers, carriers, suppliers, members) under the V3 DDD architecture.

## Overview

The ThirdParty module manages the `core.third_parties` table and its related tables (`core.third_party_roles`, `core.third_party_activities`, `core.third_party_field_definitions`, `core.third_party_field_values`). It provides CRUD operations, role-based filtering, canonical identification normalization, and configurable custom fields per tenant.

## Architecture

```
app/Context/V3/Modules/Core/ThirdParty/
├── Domain/
│   ├── Models/
│   │   ├── ThirdParty.php
│   │   ├── ThirdPartyRole.php
│   │   └── ThirdPartyActivity.php
│   ├── ValueObjects/
│   │   └── CanonicalIdentification.php
│   ├── Mappers/
│   │   ├── ThirdPartyMapperInterface.php
│   │   ├── ThirdPartyRoleMapperInterface.php
│   │   └── ThirdPartyActivityMapperInterface.php
│   └── Repository/
│       ├── ThirdPartyRepositoryInterface.php
│       └── ThirdPartyFieldRepositoryInterface.php
├── Application/
│   ├── DTOs/
│   │   ├── ThirdPartyCreateDTO.php
│   │   └── ThirdPartyUpdateDTO.php
│   ├── UseCases/
│   │   └── ThirdPartyUseCase.php
│   └── Http/
│       ├── Controllers/
│       │   └── ThirdPartyController.php
│       └── Requests/
│           ├── ThirdPartyCreateRequest.php
│           ├── ThirdPartyUpdateRequest.php
│           ├── ThirdPartyFieldDefinitionRequest.php
│           └── ThirdPartyFieldValuesRequest.php
├── Infrastructure/
│   ├── Laravel/
│   │   └── Eloquent/
│   │       └── Models/
│   │           ├── ThirdPartyModel.php
│   │           ├── ThirdPartyRoleModel.php
│   │           └── ThirdPartyActivityModel.php
│   ├── Mappers/
│   │   ├── ThirdPartyMapper.php
│   │   ├── ThirdPartyRoleMapper.php
│   │   └── ThirdPartyActivityMapper.php
│   └── Postgres/
│       ├── ThirdPartyRepository.php
│       └── ThirdPartyFieldRepository.php
└── ThirdPartyServiceProvider.php
```

## Database Tables

All tables are in the `master_v3` database, schema `core`, with PostgreSQL RLS (Row-Level Security) enforced via `auth.tenant_id()`.

### `core.third_parties`
- Primary key: `(tenant_id, id)` — UUID
- Unique: `(tenant_id, identification_type, canonical_identification)` — canonical form is `upper(regexp_replace(trim(identification), '\s+', '', 'g'))`
- Check: `identification_type IN ('04', '05', '06', '07')` (RUC, Cédula, Pasaporte, Consumidor Final)
- Check: `person_type IS NULL OR person_type IN ('natural', 'juridical')`

### `core.third_party_roles`
- Primary key: `(tenant_id, id)` — UUID
- Unique: `(tenant_id, third_party_id, role)`
- Check: `role IN ('customer', 'supplier', 'member', 'carrier')`

### `core.third_party_activities`
- Primary key: `(tenant_id, id)` — UUID
- Exclude constraint: prevents overlapping validity ranges for the same `(tenant_id, third_party_id, establishment_code, activity_id)`
- FK: `activity_id` → `core.economic_activities(id)`

### `core.third_party_field_definitions`
- Primary key: `(tenant_id, id)` — UUID
- Unique: `(tenant_id, code)`
- Check: `code ~ '^[a-z][a-z0-9_]{1,80}$'`
- Check: `scope IN ('customer', 'carrier', 'both')`
- Check: `data_type IN ('text', 'number', 'date', 'boolean', 'select')`

### `core.third_party_field_values`
- Primary key: `(tenant_id, id)` — UUID
- Unique: `(tenant_id, third_party_id, definition_id)`
- `value` column is `jsonb`

## Canonical Identification

The `CanonicalIdentification` value object normalizes Ecuadorian fiscal identities:

| Input type | Normalized code |
|---|---|
| `RUC` | `04` |
| `CED`, `CI`, `CÉDULA` | `05` |
| `PAS`, `PASSPORT` | `06` |
| `CF`, `CONSUMIDOR FINAL` | `07` |

If no type is provided, the type is inferred from the length: 13 characters → `04` (RUC), otherwise → `05` (Cédula).

The identification value is normalized to `UPPERCASE` with all whitespace removed.

## API Endpoints

All endpoints are under `api/v3/core/third-parties` and require `auth.v3.cookie` + `tenant.v3.context` middleware.

### Third Party Creation

| Method | Path | Description |
|---|---|---|
| POST | `/third-parties` | Create a third party |

### Identification Availability

| Method | Path | Description |
|---|---|---|
| GET | `/third-parties/availability` | Check if an identification is available |

Query params: `identification` (or `identification_number`), `identification_type` (optional), `exclude_id` (optional, for updates).

### Custom Field Definitions

| Method | Path | Description |
|---|---|---|
| GET | `/third-parties/field-definitions` | List field definitions (optionally filtered by `?scope=`) |
| POST | `/third-parties/field-definitions` | Create a field definition |
| PATCH | `/third-parties/field-definitions/{definition}` | Update a field definition |
| DELETE | `/third-parties/field-definitions/{definition}` | Deactivate a field definition |

## Request/Response Examples

### Create a Third Party

**Request:**
```json
POST /api/v3/core/third-parties
{
  "name": "Juan Pérez",
  "identification": "1712345678",
  "identification_type": "05",
  "email": "juan@example.com",
  "phone": "+593 99 123 4567",
  "address": "Av. Amazonas N34-451",
  "role": "customer"
}
```

**Response (201):**
```json
{
  "status": true,
  "message": "Tercero creado.",
  "data": {
    "id": "a1b2c3d4-e5f6-7890-1234-567890abcdef",
    "tenant_id": "...",
    "name": "Juan Pérez",
    "identification": "1712345678",
    "identification_type": "05",
    "must_invoice": true,
    "is_active": true,
    "roles": ["customer"],
    "activities": [],
    "custom_fields": []
  }
}
```

### Create a Field Definition

**Request:**
```json
POST /api/v3/core/third-parties/field-definitions
{
  "code": "credit_limit",
  "label": "Límite de Crédito",
  "scope": "customer",
  "data_type": "number",
  "validation": { "min": 0, "max": 100000 },
  "is_required": false,
  "sort_order": 10
}
```

**Response (201):**
```json
{
  "status": true,
  "message": "Campo configurable creado.",
  "data": {
    "id": "uuid",
    "code": "credit_limit",
    "label": "Límite de Crédito",
    "scope": "customer",
    "data_type": "number",
    "validation": { "min": 0, "max": 100000 },
    "is_required": false,
    "sort_order": 10,
    "is_active": true
  }
}
```

### Check Identification Availability

**Request:**
```
GET /api/v3/core/third-parties/availability?identification=1712345678&identification_type=05
```

**Response (200):**
```json
{
  "status": true,
  "message": "Disponibilidad de identificación verificada.",
  "data": {
    "available": true,
    "identification": "1712345678",
    "identification_type": "05"
  }
}
```

## Response Envelope

All responses follow the standard V3 API envelope:

```json
{
  "status": true|false,
  "message": "",
  "data": ...
}
```

Error responses include a `data.code` field:
- `third_party_not_found` — 404
- `customer_not_found` — 404
- `third_party_field_not_found` — 404
- `third_party_fields_invalid` — 422
- `invalid_identification` — 422

## Security

- **RLS**: All tables have forced row-level security via `auth.tenant_id()` which reads `current_setting('app.tenant_id', true)`.
- **Tenant context**: Set by the `tenant.v3.context` middleware before any query.
- **Audit**: Field definition and value changes are recorded in `platform.audit_events`.

## Dependencies

- `CarrierCompanyRepositoryInterface` — for resolving carrier company IDs
- `CarrierAffiliationRepositoryInterface` — for resolving vehicle plates by third party ID
