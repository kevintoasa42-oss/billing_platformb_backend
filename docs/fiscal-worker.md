# Worker Fiscal y Flujo SRI

## Overview

El worker fiscal procesa los documentos electronicos (facturas, notas de credito, etc.) a traves de un pipeline de 7 etapas, generando los artifacts requeridos por el SRI (Servicio de Rentas Internas del Ecuador): XML, XML firmado, XML autorizado y RIDE (PDF).

## Modos de operacion

El sistema soporta 3 modos SRI, controlados por las variables de entorno `SRI_MODE` y `APP_ENV`:

| Modo | SRI_MODE | APP_ENV | Descripcion |
|---|---|---|---|
| Mock | `mock` | `local` | Genera artifacts sinteticos (XML stubs + PDF simple). No toca el SRI. Para desarrollo. |
| Celcer | `celcer` | `staging` | Flujo real contra el SRI de pruebas (CELCER). Genera XML real, firma, envia SOAP, autoriza, genera RIDE completo. |
| Sri | `sri` | `production` | Flujo real contra el SRI de produccion. Igual que celcer pero apunta a `cel.sri.gob.ec`. |

### Seleccion del modo

El modo se resuelve en este orden:
1. Variable `SRI_MODE` (si esta seteada)
2. Modo declarado en `core.enterprise_tax_settings.authorization_mode` del tenant
3. Default segun `APP_ENV`: `local` -> mock, `staging` -> celcer, `production` -> sri

El `APP_ENV` es un techo: nunca se puede escalar. Por ejemplo, `APP_ENV=local` nunca puede usar celcer o sri, incluso si `SRI_MODE=sri`.

## Pipeline de 7 etapas

Al crear una factura (`POST /api/v3/invoices`), se encolan 7 jobs secuenciales en `integration.processing_jobs`:

```
xml -> signature -> send -> authorization -> authorized_xml -> pdf -> delivery
```

Cada job depende del anterior (cadena de `predecessor_id`). El worker procesa los jobs en orden.

### Etapa 1: xml

Genera el XML SRI v2.1.0 del documento.

- **Mock**: Genera un XML stub simple (`<laboratorio validezFiscal="ninguna">...`)
- **Real**: Genera el XML completo con `SriInvoiceXmlGenerator`:
  - `infoTributaria`: ambiente, tipoEmision, razonSocial, ruc, claveAcceso, codDoc, estab, ptoEmi, secuencial, dirMatriz
  - `infoFactura`: fechaEmision, dirEstablecimiento, obligadoContabilidad, tipoIdentificacionComprador, razonSocialComprador, identificacionComprador, totalSinImpuestos, totalDescuento, totalConImpuestos, propina, importeTotal, moneda, pagos
  - `detalles`: cada linea con codigoPrincipal, descripcion, cantidad, precioUnitario, descuento, precioTotalSinImpuesto, impuestos
  - Valida con `SriXmlValidator` (estructural) + `SriInvoiceXsdValidator` (XSD oficial)
- **Artifact generado**: `kind='xml'` en `integration.document_artifacts`

### Etapa 2: signature

Firma el XML con XAdES-EPES (XML Advanced Electronic Signature).

- **Mock**: Genera el mismo XML stub (simula firma)
- **Real**: Firma con `SriCredentialSigningService`:
  - Si `key_backend='openbao_transit'`: firma via OpenBao Transit (HSM remoto)
  - Si `key_backend='legacy_p12'`: firma via `sri.jar` (Java) con el P12 de la empresa
  - Construye la firma XAdES-EPES con `SriXmlSigner`:
    - `ds:Signature` con `ds:SignedInfo`, `ds:SignatureValue`, `ds:KeyInfo`
    - `xades:QualifyingProperties` con `xades:SignedProperties`
    - Referencias: comprobante, SignedProperties, KeyInfo
    - Canonicalizacion C14N, digest SHA1, signature RSA-SHA1
  - Verifica la firma con `SriXmlSignatureVerifier`
- **Artifact generado**: `kind='signed_xml'` en `integration.document_artifacts`

### Etapa 3: send

Envia el XML firmado al SRI via SOAP.

- **Mock**: No hace nada (simula envio)
- **Real**: Llama a `SriSoapClient::receptionXml()`:
  - WSDL: `https://celcer.sri.gob.ec/.../RecepcionComprobantesOffline?wsdl` (pruebas)
  - Operacion SOAP: `validarComprobante`
  - Si el SRI responde `RECIBIDA`: actualiza `fiscal_status='received'`, `legacy_sri_status='RECIBIDA'`
  - Si el SRI rechaza: actualiza `fiscal_status='rejected'`, registra mensajes de error
- **No genera artifact**

### Etapa 4: authorization

Consulta la autorizacion al SRI via SOAP.

- **Mock**: No hace nada (simula autorizacion)
- **Real**: Llama a `SriSoapClient::authorize()`:
  - WSDL: `https://celcer.sri.gob.ec/.../AutorizacionComprobantesOffline?wsdl` (pruebas)
  - Operacion SOAP: `autorizacionComprobante` con la `claveAcceso`
  - Espera configurable: `SRI_WAIT_SECONDS` (default 5s)
  - Si el SRI responde `AUTORIZADO`:
    - Actualiza `fiscal_status='authorized'`
    - Actualiza `legacy_sri_status='AUTORIZADO'`
    - Actualiza `authorization_number` y `authorization_date`
    - El SRI devuelve el XML autorizado (con la firma del SRI)
  - Si el SRI responde `NO_AUTORIZADO`: actualiza `fiscal_status='rejected'`, registra mensajes
- **No genera artifact** (el XML autorizado se guarda en la etapa 5)

### Etapa 5: authorized_xml

Almacena el XML autorizado devuelto por el SRI.

- **Mock**: Genera el mismo XML stub
- **Real**: Almacena el `comprobante` devuelto por el SRI en la etapa 4
- **Artifact generado**: `kind='authorized_xml'` en `integration.document_artifacts`

### Etapa 6: pdf

Genera el RIDE (Representacion Impresa de Documento Electronico).

- **Mock**: Genera un PDF simple con Dompdf ("Documento de laboratorio...")
- **Real**: Genera el RIDE completo con `InvoiceRideRenderer`:
  - Usa Dompdf + vista Blade `sri.invoice-ride`
  - Incluye: datos de la empresa (nombre, RUC, direccion), ambiente, tipo emision, clave de acceso, numero de autorizacion, fecha de emision, estado, datos del cliente, detalles (productos), subtotal, descuento, impuestos, total, formas de pago, informacion adicional, transporter (si aplica), logo de marca
  - Usa los snapshots fiscales (issuer_snapshot, recipient_snapshot, fiscal_snapshots) para que el RIDE refleje el estado del documento al momento de la emision, no el estado actual
- **Artifact generado**: `kind='pdf'` en `integration.document_artifacts`

### Etapa 7: delivery

Marca el documento como entregado.

- **Mock**: Actualiza `fiscal_status='simulated'`, `delivery_status='simulated'`
- **Real**: Actualiza `delivery_status='completed'`
- **No genera artifact**

## Clave de acceso SRI

La clave de acceso es un string de 49 digitos generado por `SriAccessKeyGenerator`:

```
fechaEmision(8) + tipoComprobante(2) + RUC(13) + ambiente(1) +
establecimiento(3) + puntoEmision(3) + secuencial(9) +
codigoNumerico(8) + tipoEmision(1) + digitoVerificador(1)
```

- `fechaEmision`: DDMMAAAA
- `tipoComprobante`: `01` = factura, `04` = nota de credito, `03` = liquidacion, `05` = nota de debito, `06` = guia de remision, `07` = retencion
- `ambiente`: `1` = pruebas, `2` = produccion
- `codigoNumerico`: 8 digitos aleatorios
- `tipoEmision`: `1` = normal
- `digitoVerificador`: calculado con algoritmo modulo 11

Se genera al crear la factura y se almacena en `fiscal.documents.access_key`.

## Firma electronica

La firma electronica se configura por empresa en `core.enterprise_electronic_signatures`:

| Campo | Descripcion |
|---|---|
| `has_electronic_signature` | Si la empresa tiene firma activa |
| `sri_mode` | Modo SRI de la empresa (mock/celcer/sri) |
| `file_name` | Path al archivo P12 en storage |
| `password` | Password del P12 (encriptado) |
| `expiration_date` | Fecha de expiracion del certificado |
| `key_backend` | `legacy_p12` o `openbao_transit` |
| `certificate_pem` | Certificado X509 en formato PEM |
| `fingerprint_sha256` | Huella digital del certificado |
| `valid_from` / `valid_until` | Vigencia del certificado |
| `validation_status` | `pending`, `valid`, `invalid` |

### Backends de firma

1. **OpenBao Transit** (recomendado): Las claves privadas nunca salen del HSM. OpenBao firma los datos y devuelve la firma. Requiere un servicio OpenBao corriendo.
2. **Legacy P12**: Usa `sri.jar` (Java) para firmar con el archivo P12. Requiere Java instalado.

## Como correr el worker

### Desarrollo (modo mock)

```bash
# .env
SRI_MODE=mock
APP_ENV=local

# Terminal 1: servidor
php artisan serve

# Terminal 2: worker
php artisan v3:fiscal:worker
```

### Staging (modo celcer con OpenBao)

```bash
# .env
SRI_MODE=celcer
APP_ENV=staging
OPENBAO_ADDR=http://localhost:8200

# Docker solo para OpenBao
docker run -d -p 8200:8200 openbao/openbao server -dev

# Terminal 1: servidor
php artisan serve

# Terminal 2: worker
php artisan v3:fiscal:worker
```

### One-shot (drenar jobs pendientes)

```bash
# Crear factura
curl -X POST .../api/v3/invoices ...

# Drenar jobs manualmente
php artisan v3:fiscal:dispatch-pending --once

# Obtener artifacts
curl .../api/v3/invoices/{id}/xml
curl .../api/v3/invoices/{id}/signed-xml
curl .../api/v3/invoices/{id}/ride
```

## Endpoints de artifacts

| Endpoint | Artifact kind | Content-Type | Descripcion |
|---|---|---|---|
| `GET /invoices/{id}/xml` | `xml` | `application/xml` | XML SRI sin firmar |
| `GET /invoices/{id}/signed-xml` | `signed_xml` | `application/xml` | XML firmado con XAdES |
| `GET /invoices/{id}/ride` | `pdf` | `application/pdf` | RIDE (PDF) |

Si el worker aun no ha procesado el job correspondiente, retorna 404.

## Diagrama del flujo

```
POST /invoices
    |
    v
Crear documento en fiscal.documents
    |  + access_key (49 digitos)
    |  + writer_epoch
    |
    v
Encolar 7 jobs en integration.processing_jobs
    |
    |  xml -> signature -> send -> authorization -> authorized_xml -> pdf -> delivery
    |
    v
Worker (php artisan v3:fiscal:worker)
    |
    +-- [mock] LabFiscalPipeline
    |   +-- xml: XML stub
    |   +-- signature: XML stub
    |   +-- send: (nada)
    |   +-- authorization: (nada)
    |   +-- authorized_xml: XML stub
    |   +-- pdf: PDF simple con Dompdf
    |   +-- delivery: fiscal_status='simulated'
    |
    +-- [celcer/sri] CelcerFiscalPipeline
        +-- xml: SriInvoiceXmlGenerator + SriXmlValidator + SriInvoiceXsdValidator
        +-- signature: SriCredentialSigningService (OpenBao o P12) + SriXmlSigner
        +-- send: SriSoapClient::receptionXml -> SRI CELCER
        +-- authorization: SriSoapClient::authorize -> SRI CELCER
        +-- authorized_xml: XML autorizado del SRI
        +-- pdf: InvoiceRideRenderer (Dompdf + Blade)
        +-- delivery: fiscal_status='authorized'
              |
              v
        integration.document_artifacts
              |
              v
        GET /invoices/{id}/xml         -> artifact kind='xml'
        GET /invoices/{id}/signed-xml  -> artifact kind='signed_xml'
        GET /invoices/{id}/ride         -> artifact kind='pdf'
```

## Tablas involucradas

| Tabla | Schema | Funcion |
|---|---|---|
| `fiscal.documents` | fiscal | Documentos fiscales (facturas, notas, etc.) |
| `fiscal.document_lines` | fiscal | Lineas de los documentos |
| `fiscal.document_payments` | fiscal | Pagos de los documentos |
| `fiscal.document_events` | fiscal | Eventos del documento (audit trail) |
| `fiscal.fiscal_snapshots` | fiscal | Snapshots fiscales inmutables |
| `fiscal.operations` | fiscal | Operaciones (auditoria) |
| `integration.processing_jobs` | integration | Jobs del pipeline (7 por documento) |
| `integration.fiscal_outbox` | integration | Outbox fiscal |
| `integration.consumer_inbox` | integration | Inbox de consumidores (idempotencia) |
| `integration.processing_attempts` | integration | Intentos de procesamiento |
| `integration.dead_letter_jobs` | integration | Jobs muertos (no recuperables) |
| `integration.document_artifacts` | integration | Artifacts generados (XML, PDF, etc.) |
| `platform.tenant_data_routes` | platform | Ruta del tenant (consolidated, frozen, writer_epoch) |
| `core.enterprise_electronic_signatures` | core | Firma electronica de la empresa |
| `core.enterprise_tax_settings` | core | Configuracion fiscal de la empresa |
| `core.sri_environments` | core | Catalogo de ambientes SRI |

## Seguridad

- **RLS**: Todas las tablas tienen Row Level Security por `tenant_id`
- **Firma electronica**: Las claves privadas nunca se almacenan en la base de datos. OpenBao las custodia. El P12 legacy se almacena en `storage/app/private/` con permisos restringidos.
- **Password del P12**: Se encripta con `encrypt()` de Laravel antes de almacenarlo
- **Sin IAM**: Solo autenticacion de sesion V3 + RLS. Sin capabilities, sin roles.

## Estructura de modulos DDD

```
app/Context/V3/Modules/Fiscal/
+-- Invoice/          (CRUD de facturas - existente)
+-- Worker/           (pipeline del worker - NUEVO)
+-- Sri/              (servicios SRI - NUEVO)
```

### Separacion de responsabilidades

- **Invoice**: CRUD, controllers, DTOs, form requests, repositories de invoices y drafts
- **Worker**: encolar jobs, procesar jobs, pipelines (mock y real), repositorios de processing jobs
- **Sri**: generador XML, validador XSD, firmador XAdES, cliente SOAP, renderer RIDE, adaptador OpenBao

El modulo Worker depende del modulo Sri (via `CelcerFiscalPipeline`).
El modulo Sri no conoce al Worker ni al Invoice.
El modulo Invoice llama al Worker (`DispatchInvoiceJobsUseCase`) despues de crear el documento.
