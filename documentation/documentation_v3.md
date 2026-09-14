# API V3 — Documentación

Base URL: `http://127.0.0.1:8000/api/v3`

Autenticación: cookie de sesión (enviada automáticamente por el navegador/curl con `-b cookies.txt`).

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

**Response 200:**
```json
{
  "status": true,
  "message": "Companies loaded.",
  "data": [
    {
      "id": "uuid",
      "tenant_id": "uuid",
      "name": "Mi Empresa Demo",
      "ruc": "1790000000001",
      "legal_name": "Mi Empresa Demo S.A.",
      "trade_name": "Demo",
      "matrix_address": "Av. Amazonas N1",
      "operations_start_date": "2020-01-01",
      "city_id": null,
      "phone": "022111111",
      "corporate_email": null,
      "activity_ids": []
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
  "name": "Mi Empresa (required, max 255)",
  "ruc": "1790000000001 (required, max 13)",
  "legal_name": "Mi Empresa S.A. (nullable, max 255)",
  "trade_name": "Demo (nullable, max 255)",
  "matrix_address": "Av. Amazonas N1 (nullable, max 255)",
  "operations_start_date": "2020-01-01 (nullable, date)",
  "city_id": null,
  "phone": "022111111 (nullable, max 30)",
  "corporate_email": "info@demo.local (nullable, email, max 150)",
  "activity_ids": ["A1234B"]
}
```

**Response 201:** igual al elemento del array de GET list.

**Response 422:** validation error.

---

### GET /core/companies/{id}

Muestra una empresa por ID.

**Response 200:** igual al elemento del array de GET list.

**Response 404:** not found.

---

### PATCH /core/companies/{id}

Actualiza una empresa.

**Request body (cualquier combinación de campos):**
```json
{
  "name": "Nuevo Nombre (sometimes, max 255)",
  "ruc": "1790000000001 (sometimes, max 13)",
  "phone": "099999999 (nullable, max 30)",
  "legal_name": "...",
  "trade_name": "...",
  "matrix_address": "...",
  "operations_start_date": "2020-01-01",
  "city_id": null,
  "corporate_email": "...",
  "activity_ids": ["A1234B"]
}
```

**Response 200:** empresa actualizada.

**Response 404:** not found.

---

## Establishments

### GET /core/establishments

Lista los establecimientos del tenant.

**Response 200:**
```json
{
  "status": true,
  "message": "Establishments loaded.",
  "data": [
    {
      "id": "uuid",
      "tenant_id": "uuid",
      "company_id": "uuid",
      "sri_code": "001",
      "name": "Matriz",
      "legacy_id": "1",
      "branch_code": null,
      "address": "Av. Amazonas N1",
      "phone": "022222222",
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

Crea un establecimiento.

**Request body:**
```json
{
  "company_id": "uuid (nullable)",
  "sri_code": "001 (required, max 10)",
  "name": "Matriz (required, max 255)",
  "legacy_id": "1 (nullable, max 255)",
  "branch_code": "001 (nullable, max 50)",
  "address": "Av. Amazonas N1 (nullable, max 255)",
  "phone": "022222222 (nullable, max 30)",
  "email": "matriz@demo.local (nullable, email, max 150)",
  "city_id": null,
  "is_active": true,
  "activity_ids": ["A1234B"]
}
```

**Response 201:** igual al elemento del array de GET list.

---

### GET /core/establishments/{id}

Muestra un establecimiento por ID.

**Response 200:** igual al elemento del array de GET list.

**Response 404:** not found.

---

### PATCH /core/establishments/{id}

Actualiza un establecimiento.

**Request body (cualquier combinación):**
```json
{
  "sri_code": "001 (sometimes, max 10)",
  "name": "Matriz Actualizada (sometimes, max 255)",
  "branch_code": "001 (nullable, max 50)",
  "address": "...",
  "phone": "...",
  "email": "...",
  "city_id": null,
  "is_active": true,
  "activity_ids": ["A1234B"]
}
```

**Response 200:** establecimiento actualizado.

---

### GET /core/establishments/{id}/emission-points

Lista los puntos de emisión de un establecimiento.

**Response 200:**
```json
{
  "status": true,
  "message": "Emission points loaded.",
  "data": [
    {
      "id": "uuid",
      "tenant_id": "uuid",
      "establishment_id": "uuid",
      "sri_code": "001",
      "name": "Punto 1",
      "legacy_id": "1",
      "is_active": true,
      "is_default": true,
      "has_tax_validity": true
    }
  ]
}
```

---

## Emission Points

### GET /core/emission-points

Lista los puntos de emisión del tenant.

**Response 200:** igual al sub-recurso establishment emission-points.

---

### POST /core/emission-points

Crea un punto de emisión.

**Request body:**
```json
{
  "establishment_id": "uuid (required)",
  "sri_code": "001 (required, max 10)",
  "name": "Punto 1 (nullable, max 255)",
  "legacy_id": "1 (nullable, max 255)",
  "is_active": true,
  "is_default": true,
  "has_tax_validity": true
}
```

**Response 201:** punto de emisión creado.

---

### GET /core/emission-points/{id}

Muestra un punto de emisión por ID.

**Response 200:** igual al elemento del array de GET list.

**Response 404:** not found.

---

### PATCH /core/emission-points/{id}

Actualiza un punto de emisión.

**Request body (cualquier combinación):**
```json
{
  "sri_code": "001 (sometimes, max 10)",
  "name": "Punto 1 Actualizado (sometimes, max 255)",
  "is_active": false,
  "is_default": true,
  "has_tax_validity": true
}
```

**Response 200:** punto de emisión actualizado.

---

## Vehicles

### GET /core/vehicles

Lista los vehículos del tenant.

**Response 200:**
```json
{
  "status": true,
  "message": "Vehicles loaded.",
  "data": [
    {
      "id": "uuid",
      "tenant_id": "uuid",
      "plate": "ABC123",
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
  "plate": "ABC123 (required, max 20)",
  "legacy_id": "1 (nullable, max 255)"
}
```

**Response 201:**
```json
{
  "status": true,
  "message": "Vehicle created.",
  "data": {
    "id": "uuid",
    "tenant_id": "uuid",
    "plate": "ABC123",
    "legacy_id": null
  }
}
```

**Response 422:** validation error o placa duplicada.

---

### GET /core/vehicles/{id}

Muestra un vehículo por ID.

**Response 200:** igual al elemento del array de GET list.

**Response 404:** not found.

---

### PATCH /core/vehicles/{id}

Actualiza un vehículo.

**Request body:**
```json
{
  "plate": "NEW123 (sometimes, max 20)",
  "legacy_id": "2 (nullable, max 255)"
}
```

**Response 200:** vehículo actualizado.

---

## Economic Activities

### GET /core/economic-activities

Lista las actividades económicas del catálogo global.

**Response 200:**
```json
{
  "status": true,
  "message": "Economic activities loaded.",
  "data": [
    {
      "id": "A1234B",
      "name": "Comercio al por mayor y menor",
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
  "id": "C5678D (nullable, max 100, si se omite se autogenera)",
  "name": "Nueva Actividad (required, max 255)",
  "catalog_version": "synthetic-lab-v1 (nullable, in: synthetic-lab-v1, staging-legacy-v1)"
}
```

**Response 201:**
```json
{
  "status": true,
  "message": "Economic activity created.",
  "data": {
    "id": "C5678D",
    "name": "Nueva Actividad",
    "catalog_version": "synthetic-lab-v1"
  }
}
```

---

### GET /core/economic-activities/{id}

Muestra una actividad económica por ID (string, no UUID).

**URL param:** `id` (string alfanumérico)

**Response 200:** igual al elemento del array de GET list.

**Response 404:** not found.

---

### PATCH /core/economic-activities/{id}

Actualiza una actividad económica.

**Request body:**
```json
{
  "name": "Updated Activity (sometimes, max 255)",
  "catalog_version": "synthetic-lab-v1 (nullable)"
}
```

**Response 200:** actividad actualizada.

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
