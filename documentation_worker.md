# Worker Fiscal y Flujo SRI

## Overview

El worker fiscal procesa los documentos electrónicos (facturas, notas de crédito, etc.) a través de un pipeline de 7 etapas, generando los artifacts requeridos por el SRI (Servicio de Rentas Internas del Ecuador): XML, XML firmado, XML autorizado y RIDE (PDF).

## Modos de operación

El sistema soporta 3 modos SRI, controlados por las variables de entorno `SRI_MODE` y `APP_ENV`:

| Modo | SRI_MODE | APP_ENV | Descripción |
|---|---|---|---|
| Mock | `mock` | `local` | Genera artifacts sintéticos (XML stubs + PDF simple). No toca el SRI. Para desarrollo. |
| Celcer | `celcer` | `staging` | Flujo real contra el SRI de pruebas (CELCER). Genera XML real, firma, envía SOAP, autoriza, genera RIDE completo. |
| Sri | `sri` | `production` | Flujo real contra el SRI de producción. Igual que celcer pero apunta a `cel.sri.gob.ec`. |

### Selección del modo

El modo se resuelve en este orden:
1. Variable `SRI_MODE` (si está seteada)
2. Modo declarado en `core.enterprise_tax_settings.authorization_mode` del tenant
3. Default según `APP_ENV`: `local` → mock, `staging` → celcer, `production` → sri

El `APP_ENV` es un techo: nunca se puede escalar. Por ejemplo, `APP_ENV=local` nunca puede usar celcer o sri, incluso si `SRI_MODE=sri`.

## Pipeline de 7 etapas

Al crear una factura (`POST /api/v3/invoices`), se encolan 7 jobs secuenciales en `integration.processing_jobs`:

```
xml → signature → send → authorization → authorized_xml → pdf → delivery
```

Cada job depende del anterior (cadena de `predecessor_id`). El worker procesa los jobs en orden.

### Etapa 1: xml

Genera el XML SRI v2.1.0 del documento.

- **Mock**: Genera un XML stub simple (`<laboratorio validezFiscal="ninguna">...`)
- **Real**: Genera el XML completo con `SriInvoiceXmlGenerator`:
  - `infoTributaria`: ambiente, tipoEmision, razonSocial, ruc, claveAcceso, codDoc, estab, ptoEmi, secuencial, dirMatriz
  - `infoFactura`: fechaEmision, dirEstablecimiento, obligadoContabilidad, tipoIdentificacionComprador, razonSocialComprador, identificacionComprador, totalSinImpuestos, totalDescuento, totalConImpuestos, propina, importeTotal, moneda, pagos
  - `detalles`: cada línea con codigoPrincipal, descripcion, cantidad, precioUnitario, descuento, precioTotalSinImpuesto, impuestos
  - Valida con `SriXmlValidator` (estructural) + `SriInvoiceXsdValidator` (XSD oficial)
- **Artifact generado**: `kind='xml'` en `integration.document_artifacts`

### Etapa 2: signature

Firma el XML con XAdES-EPES (XML Advanced Electronic Signature).

- **Mock**: Genera un stub de firma
- **Real**: Firma con `SriCredentialSigningService`:
  - Si el tenant usa **OpenBao**: firma via `OpenBaoTransitSigningClient` (HSM remoto, la clave privada nunca sale del custodio)
  - Si el tenant usa **P12**: firma via `LegacyP12XmlSigner` que invoca `sri.jar` (Java)
- **Artifact generado**: `kind='signed_xml'` en `integration.document_artifacts`

### Etapa 3: send

Envía el XML firmado al SRI.

- **Mock**: Simula el envío (no hace nada)
- **Real**: Envía via SOAP al endpoint `validarComprobante` del SRI
  - El SRI retorna un `estado` (`RECIBIDA` o `DEVUELTA`) + mensajes
  - Si `DEVUELTA`, el job falla con el detalle del rechazo

### Etapa 4: authorization

Consulta la autorización del comprobante.

- **Mock**: Simula autorización
- **Real**: Consulta via SOAP al endpoint `autorizacionComprobante` del SRI
  - Retorna `AUTORIZADO` o `NO AUTORIZADO` + número de autorización + fecha
  - Si `NO AUTORIZADO`, el job falla

### Etapa 5: authorized_xml

Genera el XML autorizado (el XML original + el bloque de autorización del SRI).

- **Mock**: Genera un stub
- **Real**: Inserta el bloque `autorizacion` del SRI dentro del XML
- **Artifact generado**: `kind='authorized_xml'` en `integration.document_artifacts`

### Etapa 6: pdf

Genera el RIDE (Representación Impresa de Documento Electrónico) en PDF.

- **Mock**: Genera un PDF simple con Dompdf ("Documento de laboratorio")
- **Real**: Genera el RIDE completo con `InvoiceRideRenderer` + vista Blade `sri.invoice-ride`:
  - Datos del emisor (logo, nombre, RUC, dirección)
  - Datos del cliente
  - Tabla de items con impuestos
  - Totales y formas de pago
  - Clave de acceso con código de barras
  - Número de autorización y fecha
- **Artifact generado**: `kind='pdf'` en `integration.document_artifacts` (como `bytea`)

### Etapa 7: delivery

Marca el documento como entregado y actualiza el estado fiscal.

- Actualiza `fiscal.documents.fiscal_status` → `'simulated'` (mock) o `'authorized'` (real)
- Actualiza `fiscal.documents.delivery_status` → `'simulated'` (mock) o `'sent'` (real)
- Registra un evento en `fiscal.document_events`

## Clave de acceso SRI (49 dígitos)

La clave de acceso es un identificador único de 49 dígitos generado por `SriAccessKeyGenerator`:

```
DDMMYYYY + TipoComprobante(2) + RUC(13) + Ambiente(1) + Serie(6) + Secuencial(9) + CódigoNumérico(8) + TipoEmisión(1) + DígitoVerificador(1)
```

Ejemplo: `140920260117912345670011001001000000001000000011`

| Posición | Campo | Dígitos |
|---|---|---|
| 1-8 | Fecha emisión (ddmmyyyy) | 8 |
| 9-10 | Tipo comprobante (01=factura) | 2 |
| 11-23 | RUC del emisor | 13 |
| 24 | Ambiente (1=pruebas, 2=producción) | 1 |
| 25-30 | Establecimiento + Punto emisión | 6 |
| 31-39 | Secuencial | 9 |
| 40-47 | Código numérico | 8 |
| 48 | Tipo emisión (1=normal) | 1 |
| 49 | Dígito verificador (módulo 11) | 1 |

## Firma electrónica

### Backends soportados

| Backend | Tabla | Descripción |
|---|---|---|
| OpenBao Transit | `core.enterprise_electronic_signatures` | HSM remoto. La clave privada nunca sale del custodio. Solo llega la firma RSA. |
| P12 (legacy) | `core.enterprise_electronic_signatures` | Archivo .p12 local. Invoca `sri.jar` con Java. |

### Campos de la firma electrónica

| Campo | Descripción |
|---|---|
| `certificate_pem` | Certificado X.509 en PEM |
| `serial_number` | Serial del certificado |
| `issuer_name` | Emisor (ej: FIRMASEGURA S.A.S.) |
| `subject_name` | Titular (ej: KEVIN XAVIER TOASA ANRANGO) |
| `valid_from` / `valid_to` | Vigencia del certificado |
| `signature_backend` | `p12` o `openbao` |
| `openbao_key_reference` | Referencia Transit (ej: `enterprise-7-signature-v1`) |
| `openbao_key_version` | Versión de la clave en OpenBao |
| `credential_version` | Contador de cargas del certificado |
| `is_active` | Si la firma está activa |

### OpenBao

OpenBao es un custodio autohospedado de claves (fork de Vault). El flujo:

1. La empresa sube su certificado P12
2. El sistema importa la clave privada a OpenBao Transit con referencia `enterprise-{id}-signature-v1`
3. OpenBao retorna solo la clave pública + versión
4. Para firmar, el sistema envía el hash del XML a OpenBao → recibe la firma RSA
5. La clave privada NUNCA sale de OpenBao

Configuración:
```bash
OPENBAO_ADDR=https://openbao.internal:8200
OPENBAO_TOKEN=xxx  # AppRole token con permisos de firma
OPENBAO_TRANSIT_MOUNT=transit
```

## Cómo correr el worker

### Desarrollo (mock)

```bash
# Terminal 1: servidor Laravel
php artisan serve

# Terminal 2: worker (daemon)
php artisan v3:fiscal:worker --tenant=<tenant_id>

# O one-shot (procesa todos los pendientes y sale)
php artisan v3:fiscal:dispatch-pending
```

### Staging (celcer)

```bash
# En .env:
APP_ENV=staging
SRI_MODE=celcer

# El worker procesa automáticamente con el flujo real
php artisan v3:fiscal:worker --tenant=<tenant_id>
```

### Producción (sri)

```bash
# En .env:
APP_ENV=production
SRI_MODE=sri
OPENBAO_ADDR=https://openbao.internal:8200
OPENBAO_TOKEN=xxx

# El worker procesa automáticamente
php artisan v3:fiscal:worker --tenant=<tenant_id>
```

## Endpoints de artifacts

| Endpoint | Método | Artifact | Descripción |
|---|---|---|---|
| `/api/v3/invoices/{id}/xml` | GET | `kind='xml'` | XML generado (sin firmar) |
| `/api/v3/invoices/{id}/signed-xml` | GET | `kind='signed_xml'` | XML firmado |
| `/api/v3/invoices/{id}/authorized-xml` | GET | `kind='authorized_xml'` | XML autorizado por SRI |
| `/api/v3/invoices/{id}/ride` | GET | `kind='pdf'` | PDF/RIDE |

Todos retornan el contenido del artifact almacenado en `integration.document_artifacts`. Si el artifact no existe (worker aún no procesó el job), retorna 404.

## Diagrama del flujo

```
POST /api/v3/invoices
    │
    ▼
┌─────────────────────┐
│ InvoiceRepository   │── Crea documento en fiscal.documents
│                     │── Genera access_key (49 dígitos)
│                     │── Envia a DispatchInvoiceJobsUseCase
└─────────────────────┘
    │
    ▼
┌─────────────────────┐
│ integration.        │── 7 jobs: xml → signature → send → authorization
│ processing_jobs     │    → authorized_xml → pdf → delivery
└─────────────────────┘
    │
    ▼ (worker polls)
┌─────────────────────┐
│ ProcessFiscalJobs   │── Drain por tenant
│ UseCase             │── claimNext() → pipeline->execute() → complete()
└─────────────────────┘
    │
    ├─ Mock: LabFiscalPipeline (stubs)
    │
    └─ Real: CelcerFiscalPipeline
         │
         ├─ xml → SriInvoiceXmlGenerator → XSD validate → store artifact
         ├─ signature → SriCredentialSigningService → OpenBao o P12 → store artifact
         ├─ send → SriSoapClient → validarComprobante → check RECIBIDA
         ├─ authorization → SriSoapClient → autorizacionComprobante → check AUTORIZADO
         ├─ authorized_xml → insert autorizacion block → store artifact
         ├─ pdf → InvoiceRideRenderer → Dompdf → store artifact (bytea)
         └─ delivery → update fiscal_status → record event
    │
    ▼
┌─────────────────────┐
│ integration.        │── xml, signed_xml, authorized_xml, pdf
│ document_artifacts  │── GET /invoices/{id}/xml, /signed-xml, /ride
└─────────────────────┘
```

## Tablas involucradas

| Tabla | Schema | Función |
|---|---|---|
| `documents` | `fiscal` | Documento fiscal (factura, nota crédito, etc.) |
| `document_lines` | `fiscal` | Líneas de detalle |
| `document_payments` | `fiscal` | Formas de pago |
| `document_events` | `fiscal` | Eventos inmutables (auditoría) |
| `processing_jobs` | `integration` | Cola de jobs del worker |
| `document_artifacts` | `integration` | Artifacts generados (XML, PDF) |
| `tenant_data_routes` | `platform` | Enrutamiento de datos por tenant |
| `sri_environments` | `core` | Catálogo de ambientes SRI |
| `enterprise_tax_settings` | `core` | Configuración fiscal por empresa |
| `enterprise_electronic_signatures` | `core` | Firma electrónica por empresa |

## Seguridad

- **RLS**: Todas las tablas tienen Row-Level Security con `auth.tenant_id()`. El worker setea el contexto con `set_config('app.tenant_id', ?, false)` antes de cada query.
- **Firma electrónica**: La clave privada nunca sale del custodio (OpenBao) o del archivo P12 local.
- **IAM**: Los permisos se verifican en el middleware de autenticación, no en el worker.
- **Artifacts**: Los PDFs se guardan como `bytea` (binary), no como texto.
