# API V3 — Documentación

Base URL: `http://127.0.0.1:8000/api/v3`

Autenticación: cookie de sesión (enviada automáticamente por el navegador/curl con `-b cookies.txt`).

## Índice

| # | Sección | Endpoints |
|---|---------|-----------|
| 1 | [Auth](#auth) | `POST /auth/challenges`, `POST /auth/session`, `DELETE /auth/session`, `GET /auth/me` |
| 2 | [Carrier Establishments](#carrier-establishments) | `GET/POST /core/carrier-establishments`, `GET/PATCH /core/carrier-establishments/{id}` |
| 3 | [Carrier Emission Points](#carrier-emission-points) | `GET/POST /core/carrier-emission-points`, `GET/PATCH /core/carrier-emission-points/{id}` |
| 4 | [Carrier Affiliations](#carrier-affiliations) | `GET/POST /core/carrier-affiliations`, `GET/PATCH /core/carrier-affiliations/{id}` |
| 5 | [Companies](#companies) | `GET/POST /core/companies`, `GET/PATCH /core/companies/{id}` |
| 6 | [Establishments](#establishments) | `GET/POST /core/establishments`, `GET/PATCH /core/establishments/{id}`, `GET /core/establishments/{id}/emission-points` |
| 7 | [Emission Points](#emission-points) | `GET/POST /core/emission-points`, `GET/PATCH /core/emission-points/{id}` |
| 8 | [Vehicles](#vehicles) | `GET/POST /core/vehicles`, `GET/PATCH /core/vehicles/{id}` |
| 9 | [Economic Activities](#economic-activities) | `GET/POST /core/economic-activities`, `GET/PATCH /core/economic-activities/{id}` |
| 10 | [Notifications](#notifications) | `POST /core/notifications/test-email` |
| 11 | [Notas](#notas) | Notas técnicas generales |

---

## Auth

### POST /auth/challenges

Inicia sesión y crea una sesión cookie.

**Request body:**
```json
{
  "email": "admin@billing-v3.local",
  "password": "Admin123!"
}
```

**Response 200:**
```json
{
  "status": true,
  "message": "Sesión creada.",
  "data": {
    "user": {
      "id": "uuid",
      "legacy_id": 1,
      "name": "Administrador V3",
      "email": "admin@billing-v3.local",
      "first_name": "Administrador",
      "last_name": "V3",
      "platform_admin": true,
      "active": true
    },
    "enterprise": {
      "id": "uuid",
      "legacy_id": 1,
      "name": "Empresa V3 Demo",
      "ruc": "1790000000001"
    },
    "expires_at": "2026-09-14 16:35:53",
    "requires_enterprise": false,
    "session_ready": true
  }
}
```

---

### GET /auth/me

Devuelve la sesión activa del usuario autenticado.

**Request body:** ninguno

**Response 200:**
```json
{
  "status": true,
  "message": "Sesión activa.",
  "data": {
    "user": { "id": "uuid", "name": "...", "email": "..." },
    "enterprise": { "id": "uuid", "name": "...", "ruc": "..." },
    "expires_at": "2026-09-14 16:35:53+00"
  }
}
```

**Response 401:** sesión no válida.

---

### POST /auth/switch-enterprise

Cambia la empresa activa de la sesión.

**Request body:**
```json
{
  "enterprise_id": "uuid-de-la-empresa"
}
```

**Response 200:**
```json
{
  "status": true,
  "message": "Empresa activa cambiada.",
  "data": {
    "user": { "id": "uuid", "name": "..." },
    "enterprise": { "id": "uuid", "name": "..." },
    "expires_at": "2026-09-14 16:35:53"
  }
}
```

---

### DELETE /auth/session

Cierra la sesión actual.

**Request body:** ninguno

**Response 200:**
```json
{
  "status": true,
  "message": "Sesión cerrada.",
  "data": null
}
```

---

## Carrier Establishments

### GET /core/carrier-establishments

Lista todos los establecimientos carrier del tenant.

**Request body:** ninguno

**Response 200:**
```json
{
  "status": true,
  "message": "Carrier establishments loaded.",
  "data": [
    {
      "id": "uuid",
      "tenant_id": "uuid",
      "carrier_company_id": "uuid",
      "carrier_company": {
        "id": "uuid",
        "legal_name": "Transportes Demo Cia. S.A.",
        "trade_name": "TransDemo",
        "is_active": true,
        "third_party_name": "Transportes Demo Cia.",
        "third_party_identification": "1790000000002"
      },
      "sri_code": "001",
      "name": "Matriz Carrier",
      "address": "Av. Carrier N1",
      "phone": null,
      "email": null,
      "city_id": null,
      "is_active": true,
      "activities": [
        { "activity_id": "A1234B", "name": "Comercio", "is_primary": false }
      ]
    }
  ]
}
```

---

### POST /core/carrier-establishments

Crea un establecimiento carrier.

**Request body:**
```json
{
  "carrier_company_id": "uuid (required)",
  "sri_code": "004 (required, exactamente 3 dígitos)",
  "name": "Est Final Test (required, max 255)",
  "address": "Av Final (nullable, max 255)",
  "phone": "099111 (nullable, max 30)",
  "email": "final@demo.local (nullable, email, max 150)",
  "city_id": null,
  "is_active": true,
  "activity_ids": ["A1234B", "C5678D"]
}
```

**Response 201:**
```json
{
  "status": true,
  "message": "Carrier establishment created.",
  "data": {
    "id": "uuid",
    "tenant_id": "uuid",
    "carrier_company_id": "uuid",
    "carrier_company": { "id": "uuid", "legal_name": "...", "trade_name": "...", "is_active": true, "third_party_name": "...", "third_party_identification": "..." },
    "sri_code": "004",
    "name": "Est Final Test",
    "address": "Av Final",
    "phone": "099111",
    "email": "final@demo.local",
    "city_id": null,
    "is_active": true,
    "activities": [
      { "activity_id": "A1234B", "name": "Comercio", "is_primary": false }
    ]
  }
}
```

**Response 422:** validation error (campos faltantes o inválidos).

**Response 500:** check constraint violation si `sri_code` no cumple `^[0-9]{3}$`.

---

### GET /core/carrier-establishments/{id}

Muestra un establecimiento carrier por ID.

**URL param:** `id` (UUID)

**Response 200:** igual que un elemento del array de GET list.

**Response 404:** not found.

---

### GET /core/carrier-establishments/{id}/emission-points

Lista los puntos de emisión de un establecimiento carrier.

**URL param:** `id` (UUID del establishment)

**Response 200:**
```json
{
  "status": true,
  "message": "Carrier emission points loaded.",
  "data": [
    {
      "id": "uuid",
      "tenant_id": "uuid",
      "establishment_id": "uuid",
      "establishment": {
        "id": "uuid",
        "name": "Est Final Test",
        "sri_code": "004",
        "carrier_company_id": "uuid",
        "carrier_company_name": "Transportes Demo Cia. S.A."
      },
      "sri_code": "001",
      "name": "EP Final Test",
      "next_sequential": 1,
      "is_active": true
    }
  ]
}
```

**Response 404:** establishment no encontrado.

---

## Carrier Emission Points

### GET /core/carrier-emission-points

Lista todos los puntos de emisión carrier del tenant.

**Request body:** ninguno

**Response 200:** igual que el sub-recurso emission-points pero con todos los EPs del tenant.

---

### POST /core/carrier-emission-points

Crea un punto de emisión carrier.

**Request body:**
```json
{
  "establishment_id": "uuid (required)",
  "sri_code": "001 (required, max 10)",
  "name": "EP Final Test (nullable, max 255)",
  "is_active": true
}
```

**Response 201:**
```json
{
  "status": true,
  "message": "Carrier emission point created.",
  "data": {
    "id": "uuid",
    "tenant_id": "uuid",
    "establishment_id": "uuid",
    "establishment": {
      "id": "uuid",
      "name": "Est Final Test",
      "sri_code": "004",
      "carrier_company_id": "uuid",
      "carrier_company_name": "Transportes Demo Cia. S.A."
    },
    "sri_code": "001",
    "name": "EP Final Test",
    "next_sequential": 1,
    "is_active": true
  }
}
```

**Response 422:** validation error.

---

### GET /core/carrier-emission-points/{id}

Muestra un punto de emisión carrier por ID.

**URL param:** `id` (UUID)

**Response 200:** igual que un elemento del array de GET list.

**Response 404:** not found.

---

## Carrier Affiliations

### GET /core/carrier-affiliations

Lista todas las afiliaciones carrier del tenant con sus vehicle assignments.

**Request body:** ninguno

**Response 200:**
```json
{
  "status": true,
  "message": "Carrier affiliations loaded.",
  "data": [
    {
      "id": "uuid",
      "tenant_id": "uuid",
      "third_party_id": "uuid",
      "validity": "[2028-01-01,2028-12-31)",
      "vehicle_assignments": [
        {
          "vehicle_id": "uuid",
          "validity": "[2028-01-01,2028-12-31)"
        }
      ]
    }
  ]
}
```

> **Nota:** `validity` se devuelve en formato PostgreSQL daterange `[start,end)`. El frontend puede enviar `start/end` y el backend lo normaliza.

---

### POST /core/carrier-affiliations

Crea una afiliación carrier con vehicle assignments opcionales.

**Request body:**
```json
{
  "third_party_id": "uuid (required)",
  "validity": "2028-01-01/2028-12-31 (nullable, formato start/end)",
  "vehicle_assignments": [
    {
      "vehicle_id": "uuid (required)",
      "validity": "2028-01-01/2028-12-31 (nullable, formato start/end)"
    }
  ]
}
```

**Response 201:**
```json
{
  "status": true,
  "message": "Carrier affiliation created.",
  "data": {
    "id": "uuid",
    "tenant_id": "uuid",
    "third_party_id": "uuid",
    "validity": "[2028-01-01,2028-12-31)",
    "vehicle_assignments": [
      {
        "vehicle_id": "uuid",
        "validity": "[2028-01-01,2028-12-31)"
      }
    ]
  }
}
```

**Response 422:** validation error (third_party_id faltante, uuid inválido, etc.).

**Response 500:** exclusion constraint violation si el vehículo ya tiene una asignación con validez solapada.

---

### GET /core/carrier-affiliations/{id}

Muestra una afiliación carrier por ID con sus vehicle assignments.

**URL param:** `id` (UUID)

**Response 200:**
```json
{
  "status": true,
  "message": "Carrier affiliation loaded.",
  "data": {
    "id": "uuid",
    "tenant_id": "uuid",
    "third_party_id": "uuid",
    "validity": "[2028-01-01,2028-12-31)",
    "vehicle_assignments": [
      { "vehicle_id": "uuid", "validity": "[2028-01-01,2028-12-31)" }
    ]
  }
}
```

**Response 404:** not found.

---

### PATCH /core/carrier-affiliations/{id}

Actualiza una afiliación carrier. Puede actualizar `validity`, `vehicle_assignments`, o ambos. Los `vehicle_assignments` se reemplazan completamente (borra los existentes y crea los nuevos).

**URL param:** `id` (UUID)

**Request body (cualquier combinación):**
```json
{
  "validity": "2028-03-01/2028-09-30 (nullable)",
  "vehicle_assignments": [
    {
      "vehicle_id": "uuid (required)",
      "validity": "2028-03-01/2028-09-30 (nullable)"
    }
  ]
}
```

**Response 200:**
```json
{
  "status": true,
  "message": "Carrier affiliation updated.",
  "data": {
    "id": "uuid",
    "tenant_id": "uuid",
    "third_party_id": "uuid",
    "validity": "[2028-03-01,2028-09-30)",
    "vehicle_assignments": [
      { "vehicle_id": "uuid", "validity": "[2028-03-01,2028-09-30)" }
    ]
  }
}
```

**Response 404:** not found.

**Response 500:** exclusion constraint si hay solapamiento de vehicle assignments.

---

## Companies

### GET /core/companies

Lista las empresas del tenant (una por tenant).

**Request body:** ninguno

**Response 200:**
```json
{
  "status": true,
  "message": "Companies loaded.",
  "data": [
    {
      "id": "182db7da-9941-4864-83d4-d498416c31b9",
      "tenant_id": "0c7e54d5-f331-4eaf-adf6-afe726b27f16",
      "name": "Transportes Demo S.A.",
      "ruc": "1790000000001",
      "legal_name": "Transportes Demo Sociedad Anonima",
      "trade_name": "TransDemo",
      "matrix_address": "Av. Amazonas N34-451",
      "operations_start_date": "2015-03-12",
      "city_id": null,
      "phone": "0998887776",
      "corporate_email": "info@transdemo.com",
      "activity_ids": ["A1234B"]
    }
  ]
}
```

---

### POST /core/companies

Crea una empresa (una por tenant — si ya existe, devuelve 500 por unique constraint).

**Request body:**
```json
{
  "name": "Mi Empresa (required, string, max 255)",
  "ruc": "1790000000001 (required, string, max 13)",
  "legal_name": "Mi Empresa S.A. (nullable, string, max 255)",
  "trade_name": "Demo (nullable, string, max 255)",
  "matrix_address": "Av. Amazonas N1 (nullable, string, max 255)",
  "operations_start_date": "2020-01-01 (nullable, date)",
  "city_id": null,
  "phone": "022111111 (nullable, string, max 30)",
  "corporate_email": "info@demo.local (nullable, email, max 150)",
  "activity_ids": ["A1234B"]
}
```

**Response 201:**
```json
{
  "status": true,
  "message": "Company created.",
  "data": {
    "id": "uuid",
    "tenant_id": "uuid",
    "name": "Mi Empresa",
    "ruc": "1790000000001",
    "legal_name": "Mi Empresa S.A.",
    "trade_name": "Demo",
    "matrix_address": "Av. Amazonas N1",
    "operations_start_date": "2020-01-01",
    "city_id": null,
    "phone": "022111111",
    "corporate_email": "info@demo.local",
    "activity_ids": ["A1234B"]
  }
}
```

**Response 422:** validation error (campos faltantes o inválidos).

**Response 500:** unique constraint violation si ya existe una empresa para el tenant.

---

### GET /core/companies/{id}

Muestra una empresa por ID.

**URL param:** `id` (UUID)

**Response 200:**
```json
{
  "status": true,
  "message": "Company loaded.",
  "data": {
    "id": "182db7da-9941-4864-83d4-d498416c31b9",
    "tenant_id": "0c7e54d5-f331-4eaf-adf6-afe726b27f16",
    "name": "Transportes Demo S.A.",
    "ruc": "1790000000001",
    "legal_name": "Transportes Demo Sociedad Anonima",
    "trade_name": "TransDemo",
    "matrix_address": "Av. Amazonas N34-451",
    "operations_start_date": "2015-03-12",
    "city_id": null,
    "phone": "0998887776",
    "corporate_email": "info@transdemo.com",
    "activity_ids": ["A1234B"]
  }
}
```

**Response 404:**
```json
{
  "status": false,
  "message": "Company not found.",
  "data": null
}
```

---

### PATCH /core/companies/{id}

Actualiza una empresa. Acepta cualquier combinación de campos. Si se envía `activity_ids`, se sincronizan las actividades (reemplaza las existentes, manteniendo historial con daterange).

**URL param:** `id` (UUID)

**Request body (cualquier combinación):**
```json
{
  "name": "Nuevo Nombre (sometimes, string, max 255)",
  "ruc": "1790000000001 (sometimes, string, max 13)",
  "legal_name": "Nueva Razon Social (nullable, string, max 255)",
  "trade_name": "Nuevo Trade (nullable, string, max 255)",
  "matrix_address": "Nueva Direccion (nullable, string, max 255)",
  "operations_start_date": "2020-01-01 (nullable, date)",
  "city_id": null,
  "phone": "099999999 (nullable, string, max 30)",
  "corporate_email": "nuevo@demo.local (nullable, email, max 150)",
  "activity_ids": ["A1234B", "C5678D"]
}
```

**Response 200:**
```json
{
  "status": true,
  "message": "Company updated.",
  "data": {
    "id": "182db7da-9941-4864-83d4-d498416c31b9",
    "tenant_id": "0c7e54d5-f331-4eaf-adf6-afe726b27f16",
    "name": "Transportes Demo S.A.",
    "ruc": "1790000000001",
    "legal_name": "Transportes Demo Sociedad Anonima",
    "trade_name": "TransDemo",
    "matrix_address": "Av. Amazonas N34-451",
    "operations_start_date": "2015-03-12",
    "city_id": null,
    "phone": "0998887776",
    "corporate_email": "info@transdemo.com",
    "activity_ids": ["A1234B"]
  }
}
```

**Response 404:**
```json
{
  "status": false,
  "message": "Company not found.",
  "data": null
}
```

**Response 422:** validation error (ej: ruc con más de 13 caracteres).

---

## Establishments

### GET /core/establishments

Lista los establecimientos del tenant.

**Request body:** ninguno

**Response 200:**
```json
{
  "status": true,
  "message": "Establishments loaded.",
  "data": [
    {
      "id": "6d2b6e8c-6bae-4677-836b-715fe23abb5a",
      "tenant_id": "0c7e54d5-f331-4eaf-adf6-afe726b27f16",
      "company_id": "182db7da-9941-4864-83d4-d498416c31b9",
      "sri_code": "001",
      "name": "Updated Est",
      "legacy_id": "1",
      "branch_code": null,
      "address": "Av. Amazonas N1",
      "phone": "0991112223",
      "email": "matriz@demo.local",
      "city_id": null,
      "is_active": true,
      "activity_ids": []
    }
  ]
}
```

---

### POST /core/establishments

Crea un establecimiento. Si se envía `activity_ids`, se sincronizan las actividades (reemplaza las existentes, manteniendo historial con daterange).

**Request body:**
```json
{
  "company_id": "uuid (nullable, string, uuid)",
  "sri_code": "002 (required, string, max 10)",
  "name": "Sucursal Test (required, string, max 255)",
  "legacy_id": "1 (nullable, string, max 255)",
  "branch_code": "002 (nullable, string, max 50)",
  "address": "Av. Test N2 (nullable, string, max 255)",
  "phone": "022333444 (nullable, string, max 30)",
  "email": "sucursal@demo.local (nullable, email, max 150)",
  "city_id": null,
  "is_active": true,
  "activity_ids": ["A1234B", "C5678D"]
}
```

**Response 201:**
```json
{
  "status": true,
  "message": "Establishment created.",
  "data": {
    "id": "01a0a0a7-8972-731c-8c1b-f8de209f6764",
    "tenant_id": "0c7e54d5-f331-4eaf-adf6-afe726b27f16",
    "company_id": "182db7da-9941-4864-83d4-d498416c31b9",
    "sri_code": "002",
    "name": "Sucursal Test",
    "legacy_id": null,
    "branch_code": "002",
    "address": "Av. Test N2",
    "phone": "022333444",
    "email": "sucursal@demo.local",
    "city_id": null,
    "is_active": true,
    "activity_ids": ["A1234B", "C5678D"]
  }
}
```

**Response 422:** validation error (campos faltantes o inválidos).

---

### GET /core/establishments/{id}

Muestra un establecimiento por ID.

**URL param:** `id` (UUID)

**Response 200:**
```json
{
  "status": true,
  "message": "Establishment loaded.",
  "data": {
    "id": "01a0a0a7-8972-731c-8c1b-f8de209f6764",
    "tenant_id": "0c7e54d5-f331-4eaf-adf6-afe726b27f16",
    "company_id": "182db7da-9941-4864-83d4-d498416c31b9",
    "sri_code": "002",
    "name": "Sucursal Actualizada",
    "legacy_id": "2",
    "branch_code": "002",
    "address": "Av. Updated N2",
    "phone": "0999998888",
    "email": "updated@demo.local",
    "city_id": null,
    "is_active": true,
    "activity_ids": ["A1234B"]
  }
}
```

**Response 404:**
```json
{
  "status": false,
  "message": "Establishment not found.",
  "data": null
}
```

---

### PATCH /core/establishments/{id}

Actualiza un establecimiento. Acepta cualquier combinación de campos. Si se envía `activity_ids`, se sincronizan las actividades.

**URL param:** `id` (UUID)

**Request body (cualquier combinación):**
```json
{
  "sri_code": "002 (sometimes, string, max 10)",
  "name": "Sucursal Actualizada (sometimes, string, max 255)",
  "branch_code": "002 (nullable, string, max 50)",
  "address": "Av. Updated N2 (nullable, string, max 255)",
  "phone": "0991112222 (nullable, string, max 30)",
  "email": "updated@demo.local (nullable, email, max 150)",
  "city_id": null,
  "is_active": true,
  "activity_ids": ["A1234B"]
}
```

**Response 200:**
```json
{
  "status": true,
  "message": "Establishment updated.",
  "data": {
    "id": "01a0a0a7-8972-731c-8c1b-f8de209f6764",
    "tenant_id": "0c7e54d5-f331-4eaf-adf6-afe726b27f16",
    "company_id": "182db7da-9941-4864-83d4-d498416c31b9",
    "sri_code": "002",
    "name": "Sucursal Actualizada",
    "legacy_id": "2",
    "branch_code": "002",
    "address": "Av. Updated N2",
    "phone": "0999998888",
    "email": "updated@demo.local",
    "city_id": null,
    "is_active": true,
    "activity_ids": ["A1234B"]
  }
}
```

**Response 404:**
```json
{
  "status": false,
  "message": "Establishment not found.",
  "data": null
}
```

**Response 422:** validation error.

---

### GET /core/establishments/{id}/emission-points

Lista los puntos de emisión pertenecientes a un establecimiento.

**URL param:** `id` (UUID del establishment)

**Response 200:**
```json
{
  "status": true,
  "message": "Emission points loaded.",
  "data": [
    {
      "id": "01a0a0a7-b73d-7061-9aa7-cd1e9029b475",
      "tenant_id": "0c7e54d5-f331-4eaf-adf6-afe726b27f16",
      "establishment_id": "01a0a0a7-8972-731c-8c1b-f8de209f6764",
      "sri_code": "001",
      "name": "Punto Actualizado",
      "legacy_id": "2",
      "is_active": false,
      "is_default": false,
      "has_tax_validity": true
    }
  ]
}
```

**Response 200 (sin emission points):**
```json
{
  "status": true,
  "message": "Emission points loaded.",
  "data": []
}
```

---

## Emission Points

### GET /core/emission-points

Lista todos los puntos de emisión del tenant.

**Request body:** ninguno

**Response 200:**
```json
{
  "status": true,
  "message": "Emission points loaded.",
  "data": [
    {
      "id": "89b2f5fa-b12c-477a-b3d4-c01fe62cefe9",
      "tenant_id": "0c7e54d5-f331-4eaf-adf6-afe726b27f16",
      "establishment_id": "6d2b6e8c-6bae-4677-836b-715fe23abb5a",
      "sri_code": "001",
      "name": "Updated EP",
      "legacy_id": "1",
      "is_active": false,
      "is_default": true,
      "has_tax_validity": true
    }
  ]
}
```

---

### POST /core/emission-points

Crea un punto de emisión.

**Request body:**
```json
{
  "establishment_id": "uuid (required, string, uuid)",
  "sri_code": "001 (required, string, max 10)",
  "name": "Punto Emision Test (nullable, string, max 255)",
  "legacy_id": "1 (nullable, string, max 255)",
  "is_active": true,
  "is_default": true,
  "has_tax_validity": true
}
```

**Response 201:**
```json
{
  "status": true,
  "message": "Emission point created.",
  "data": {
    "id": "01a0a0a7-b73d-7061-9aa7-cd1e9029b475",
    "tenant_id": "0c7e54d5-f331-4eaf-adf6-afe726b27f16",
    "establishment_id": "01a0a0a7-8972-731c-8c1b-f8de209f6764",
    "sri_code": "001",
    "name": "Punto Emision Test",
    "legacy_id": null,
    "is_active": true,
    "is_default": true,
    "has_tax_validity": true
  }
}
```

**Response 422:** validation error (ej: `The establishment id field is required.`, `The sri code field is required.`).

---

### GET /core/emission-points/{id}

Muestra un punto de emisión por ID.

**URL param:** `id` (UUID)

**Response 200:**
```json
{
  "status": true,
  "message": "Emission point loaded.",
  "data": {
    "id": "01a0a0a7-b73d-7061-9aa7-cd1e9029b475",
    "tenant_id": "0c7e54d5-f331-4eaf-adf6-afe726b27f16",
    "establishment_id": "01a0a0a7-8972-731c-8c1b-f8de209f6764",
    "sri_code": "001",
    "name": "Punto Actualizado",
    "legacy_id": "2",
    "is_active": false,
    "is_default": false,
    "has_tax_validity": true
  }
}
```

**Response 404:**
```json
{
  "status": false,
  "message": "Emission point not found.",
  "data": null
}
```

---

### PATCH /core/emission-points/{id}

Actualiza un punto de emisión. Acepta cualquier combinación de campos.

**URL param:** `id` (UUID)

**Request body (cualquier combinación):**
```json
{
  "sri_code": "001 (sometimes, string, max 10)",
  "name": "Punto Actualizado (sometimes, string, max 255)",
  "is_active": false,
  "is_default": false,
  "has_tax_validity": true
}
```

**Response 200:**
```json
{
  "status": true,
  "message": "Emission point updated.",
  "data": {
    "id": "01a0a0a7-b73d-7061-9aa7-cd1e9029b475",
    "tenant_id": "0c7e54d5-f331-4eaf-adf6-afe726b27f16",
    "establishment_id": "01a0a0a7-8972-731c-8c1b-f8de209f6764",
    "sri_code": "001",
    "name": "Punto Actualizado",
    "legacy_id": "2",
    "is_active": false,
    "is_default": false,
    "has_tax_validity": true
  }
}
```

**Response 404:**
```json
{
  "status": false,
  "message": "Emission point not found.",
  "data": null
}
```

**Response 422:** validation error.

---

## Vehicles

### GET /core/vehicles

Lista los vehículos del tenant.

**Request body:** ninguno

**Response 200:**
```json
{
  "status": true,
  "message": "Vehicles loaded.",
  "data": [
    {
      "id": "01a0a058-d37d-7276-8973-2d390c20e626",
      "tenant_id": "0c7e54d5-f331-4eaf-adf6-afe726b27f16",
      "plate": "NEW001",
      "legacy_id": "3"
    },
    {
      "id": "8967b09b-4857-4efb-8995-c614e9f94b84",
      "tenant_id": "0c7e54d5-f331-4eaf-adf6-afe726b27f16",
      "plate": "XYZ789",
      "legacy_id": "1"
    }
  ]
}
```

---

### POST /core/vehicles

Crea un vehículo (placa única por tenant).

**Request body:**
```json
{
  "plate": "ABC123 (required, string, max 20)",
  "legacy_id": "V001 (nullable, string, max 255)"
}
```

**Response 201:**
```json
{
  "status": true,
  "message": "Vehicle created.",
  "data": {
    "id": "01a0a0ae-9d35-72c9-a3bf-e71e5f434ef3",
    "tenant_id": "0c7e54d5-f331-4eaf-adf6-afe726b27f16",
    "plate": "ABC123",
    "legacy_id": null
  }
}
```

**Response 422:** validation error (ej: `The plate field is required.`).

**Response 500:** unique constraint violation si la placa ya existe para el tenant (`vehicles_tenant_id_plate_key`).

---

### GET /core/vehicles/{id}

Muestra un vehículo por ID.

**URL param:** `id` (UUID)

**Response 200:**
```json
{
  "status": true,
  "message": "Vehicle loaded.",
  "data": {
    "id": "01a0a0ae-9d35-72c9-a3bf-e71e5f434ef3",
    "tenant_id": "0c7e54d5-f331-4eaf-adf6-afe726b27f16",
    "plate": "ABC123",
    "legacy_id": "4"
  }
}
```

**Response 404:**
```json
{
  "status": false,
  "message": "Vehicle not found.",
  "data": null
}
```

---

### PATCH /core/vehicles/{id}

Actualiza un vehículo.

**URL param:** `id` (UUID)

**Request body (cualquier combinación):**
```json
{
  "plate": "NEW123 (sometimes, string, max 20)",
  "legacy_id": "2 (nullable, string, max 255)"
}
```

**Response 200:**
```json
{
  "status": true,
  "message": "Vehicle updated.",
  "data": {
    "id": "01a0a0ae-9d35-72c9-a3bf-e71e5f434ef3",
    "tenant_id": "0c7e54d5-f331-4eaf-adf6-afe726b27f16",
    "plate": "ABC123",
    "legacy_id": "4"
  }
}
```

**Response 404:**
```json
{
  "status": false,
  "message": "Vehicle not found.",
  "data": null
}
```

**Response 500:** unique constraint si la placa ya existe para el tenant.

> **Bug conocido:** `legacy_id` no se persiste en PATCH. El mapper `VehicleMapper::toDatabaseArray()` no incluye `legacy_id` en el array que envía a la BD.

---

## Economic Activities

### GET /core/economic-activities

Lista las actividades económicas del catálogo global (no tenant-scoped).

**Request body:** ninguno

**Response 200:**
```json
{
  "status": true,
  "message": "Economic activities loaded.",
  "data": [
    {
      "id": "C5678D",
      "name": "Nueva Actividad",
      "catalog_version": "synthetic-lab-v1"
    },
    {
      "id": "A1234B",
      "name": "Updated Activity",
      "catalog_version": "synthetic-lab-v1"
    }
  ]
}
```

---

### POST /core/economic-activities

Crea una actividad económica.

**Request body:**
```json
{
  "id": "TEST001 (nullable, string, max 100, si se omite se autogenera)",
  "name": "Test Activity (required, string, max 255)",
  "catalog_version": "synthetic-lab-v1 (nullable, in: synthetic-lab-v1, staging-legacy-v1)"
}
```

**Response 201:**
```json
{
  "status": true,
  "message": "Economic activity created.",
  "data": {
    "id": "TEST001",
    "name": "Test Activity",
    "catalog_version": "synthetic-lab-v1"
  }
}
```

**Response 422:** validation error (ej: `The name field is required.`, `The selected catalog version is invalid.`).

**Response 500:** unique constraint si el `id` ya existe (`economic_activities_pkey`).

---

### GET /core/economic-activities/{id}

Muestra una actividad económica por ID (string alfanumérico, no UUID).

**URL param:** `id` (string alfanumérico: `[0-9a-zA-Z_-]+`)

**Response 200:**
```json
{
  "status": true,
  "message": "Economic activity loaded.",
  "data": {
    "id": "TEST001",
    "name": "Updated Activity",
    "catalog_version": "synthetic-lab-v1"
  }
}
```

**Response 404:**
```json
{
  "status": false,
  "message": "Economic activity not found.",
  "data": null
}
```

---

### PATCH /core/economic-activities/{id}

Actualiza una actividad económica.

**URL param:** `id` (string alfanumérico: `[0-9a-zA-Z_-]+`)

**Request body (cualquier combinación):**
```json
{
  "name": "Updated Activity (sometimes, string, max 255)",
  "catalog_version": "synthetic-lab-v1 (nullable, in: synthetic-lab-v1, staging-legacy-v1)"
}
```

**Response 200:**
```json
{
  "status": true,
  "message": "Economic activity updated.",
  "data": {
    "id": "TEST001",
    "name": "Updated Activity",
    "catalog_version": "synthetic-lab-v1"
  }
}
```

**Response 404:**
```json
{
  "status": false,
  "message": "Economic activity not found.",
  "data": null
}
```

> **Bug conocido:** Cambiar `catalog_version` de `synthetic-lab-v1` a `staging-legacy-v1` falla con HTTP 500 (`economic_activities_catalog_version_check` check constraint). La validación de la request permite ambos valores, pero la BD solo permite `synthetic-lab-v1` en updates.

---

## Notifications

### POST /core/notifications/test-email

Envía un email de prueba.

**Request body:**
```json
{
  "to": "test@demo.local (required, email, max 150)",
  "subject": "Test (required, max 255)",
  "body": "Hello (required, string)",
  "is_html": false
}
```

**Response 200:**
```json
{
  "status": true,
  "message": "Test email sent.",
  "data": { "sent": true }
}
```

**Response 422:** validation error.

---

## Notas

- **Formato de fechas (daterange):** El frontend envía `start/end` (ej: `2026-01-01/2026-12-31`). El backend lo normaliza a formato PostgreSQL `[2026-01-01,2026-12-31)` y lo devuelve en ese formato.
- **sri_code de carrier establishments:** Debe ser exactamente 3 dígitos (`^[0-9]{3}$`).
- **Vehicle assignments:** Un vehículo no puede tener dos asignaciones con validez solapada (PostgreSQL EXCLUDE constraint).
- **PATCH de carrier-affiliations:** Los `vehicle_assignments` se reemplazan completamente — los existentes se borran y se crean los nuevos.
- **Tenant isolation:** Todas las APIs de `/core/*` requieren sesión autenticada y tenant context (RLS activo).
