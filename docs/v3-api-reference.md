# V3 API Reference

Complete reference of all V3 API endpoints. All endpoints are under the `api/v3` prefix and require `auth.v3.cookie` + `tenant.v3.context` middleware unless otherwise noted.

## Summary

| Group | Prefix | Routes | Documentation |
|---|---|---|---|
| Authentication | `auth` | 17 | [Authentication](#authentication) |
| Core - Branches | `core/branches` | 7 | [Core - Branches](#core---branches) |
| Core - Carrier Establishments | `core/carrier-establishments` | 4 | [Core - Carrier](#core---carrier) |
| Core - Carrier Emission Points | `core/carrier-emission-points` | 3 | [Core - Carrier](#core---carrier) |
| Core - Carrier Affiliations | `core/carrier-affiliations` | 4 | [Core - Carrier](#core---carrier) |
| Core - Companies | `core/companies` | 4 | [Core - Companies](#core---companies) |
| Core - Economic Activities | `core/economic-activities` | 3 | [Core - Economic Activities](#core---economic-activities) |
| Core - Notifications | `core/notifications` | 1 | [Core - Notifications](#core---notifications) |
| Core - Products | `core/products` | 9 | [Core - Products](#core---products) |
| Core - Product Settings | `core/product-settings` | 2 | [Core - Products](#core---products) |
| Core - SRI IVA | `core/sri-iva-types` | 8 | [Core - SRI IVA](#core---sri-iva) |
| Core - Settings | `core/customer-settings`, `core/payment-method-settings`, `core/additional-info-presets` | 8 | [Core - Settings](#core---settings) |
| Core - Third Parties | `core/third-parties` | 15 | [docs/third-party.md](third-party.md) |
| Core - Vehicles | `core/vehicles` | 3 | [Core - Vehicles](#core---vehicles) |
| Fiscal - Invoices | `invoices` | 15 | [Fiscal - Invoices](#fiscal---invoices) |
| Fiscal - Invoice Drafts | `invoice-drafts` | 8 | [Fiscal - Invoice Drafts](#fiscal---invoice-drafts) |
| Fiscal - SRI Admin | `admin` | 3 | [Fiscal - SRI Admin](#fiscal---sri-admin) |
| Fiscal - Bootstrap | `bootstrap` | 1 | [Fiscal - Bootstrap](#fiscal---bootstrap) |
| Platform - Admin | `platform/admin` | 6 | [Platform - Admin](#platform---admin) |
| Platform - RBAC | `roles`, `users`, `permissions`, `menus` | 16 | [Platform - RBAC](#platform---rbac) |
| Platform - Geography | `countries`, `provinces`, `cities` | 3 | [Platform - Geography](#platform---geography) |
| Platform - SRI Environments | `sri-environments` | 1 | [Platform - SRI](#platform---sri) |
| Platform - Contexts | `contexts` | 1 | [Platform - Contexts](#platform---contexts) |
| Platform - Enterprises | `enterprises` | 16 | [Platform - Enterprises](#platform---enterprises) |

**Total: 162 routes**

---

## Authentication

Prefix: `api/v3/auth`

| Method | Path | Description | Auth |
|---|---|---|---|
| POST | `auth/challenges` | Request a challenge for passwordless auth | No (throttled) |
| POST | `auth/sessions` | Create a session from a challenge | No (throttled) |
| GET | `auth/me` | Get current user | Yes |
| POST | `auth/refresh` | Refresh session token | Yes |
| POST | `auth/switch-enterprise` | Switch active enterprise | Yes |
| DELETE | `auth/session` | Destroy current session | Yes |
| POST | `auth/change-password` | Change password | Yes |
| POST | `auth/verify-password` | Verify password for sensitive ops | Yes |
| GET | `auth/sessions` | List active sessions | Yes |
| DELETE | `auth/sessions` | Revoke all other sessions | Yes |
| DELETE | `auth/sessions/{id}` | Revoke a specific session | Yes |
| GET | `auth/mfa` | Get MFA status | Yes |
| POST | `auth/mfa` | Enable MFA | Yes |
| POST | `auth/mfa/confirm` | Confirm MFA enrollment | Yes |
| GET | `auth/preferences` | Get user preferences | Yes |
| PATCH | `auth/preferences` | Update user preferences | Yes |
| POST | `auth/support/users/{id}/password-reset` | Admin password reset | Yes |

---

## Core - Branches

Prefix: `api/v3/core/branches`

| Method | Path | Description |
|---|---|---|
| GET | `core/branches` | List branches |
| POST | `core/branches` | Create a branch |
| PATCH | `core/branches/{id}` | Update a branch |
| DELETE | `core/branches/{id}` | Delete a branch |
| GET | `core/branches/{branch}/issuance-points` | List issuance points for a branch |
| POST | `core/branches/{branch}/issuance-points` | Create an issuance point |
| GET | `core/branches/{branch}/issuance-points/{point}/next-sequential` | Get next sequential number |

---

## Core - Carrier

Prefix: `api/v3/core/carrier-*`

| Method | Path | Description |
|---|---|---|
| GET | `core/carrier-establishments` | List carrier establishments |
| POST | `core/carrier-establishments` | Create a carrier establishment |
| GET | `core/carrier-establishments/{id}` | Get a carrier establishment |
| PATCH | `core/carrier-establishments/{id}` | Update a carrier establishment |
| GET | `core/carrier-establishments/{id}/emission-points` | List emission points |
| GET | `core/carrier-emission-points` | List carrier emission points |
| POST | `core/carrier-emission-points` | Create a carrier emission point |
| GET | `core/carrier-emission-points/{id}` | Get a carrier emission point |
| GET | `core/carrier-affiliations` | List carrier affiliations |
| POST | `core/carrier-affiliations` | Create a carrier affiliation |
| GET | `core/carrier-affiliations/{id}` | Get a carrier affiliation |
| PATCH | `core/carrier-affiliations/{id}` | Update a carrier affiliation |

---

## Core - Companies

Prefix: `api/v3/core/companies`

| Method | Path | Description |
|---|---|---|
| GET | `core/companies` | List companies |
| POST | `core/companies` | Create a company |
| GET | `core/companies/{id}` | Get a company |
| PATCH | `core/companies/{id}` | Update a company |

---

## Core - Economic Activities

Prefix: `api/v3/core/economic-activities`

| Method | Path | Description |
|---|---|---|
| GET | `core/economic-activities` | List economic activities |
| POST | `core/economic-activities` | Create an economic activity |
| GET | `core/economic-activities/{id}` | Get an economic activity |
| PATCH | `core/economic-activities/{id}` | Update an economic activity |

---

## Core - Notifications

Prefix: `api/v3/core/notifications`

| Method | Path | Description |
|---|---|---|
| POST | `core/notifications/test-email` | Send a test email |

---

## Core - Products

Prefix: `api/v3/core/products`

| Method | Path | Description |
|---|---|---|
| GET | `core/products` | List products |
| POST | `core/products` | Create a product |
| GET | `core/products/duplicates` | List duplicate products |
| GET | `core/products/{id}` | Get a product |
| PATCH | `core/products/{id}` | Update a product |
| DELETE | `core/products/{id}` | Delete a product |
| PATCH | `core/products/{id}/status` | Change product status |
| POST | `core/products/{product}/taxes` | Add a product tax |
| DELETE | `core/products/{product}/taxes/{tax}` | Remove a product tax |
| GET | `core/product-settings` | List product settings |
| POST | `core/product-settings` | Create product settings |

---

## Core - SRI IVA

Prefix: `api/v3/core/sri-iva-types`

| Method | Path | Description |
|---|---|---|
| GET | `core/sri-iva-types` | List SRI IVA types |
| POST | `core/sri-iva-types` | Create an SRI IVA type |
| GET | `core/sri-iva-types/{id}` | Get an SRI IVA type |
| PATCH | `core/sri-iva-types/{id}` | Update an SRI IVA type |
| DELETE | `core/sri-iva-types/{id}` | Delete an SRI IVA type |
| GET | `core/sri-iva-types/{type}/percentages` | List IVA percentages for a type |
| POST | `core/sri-iva-types/{type}/percentages` | Create an IVA percentage |
| PATCH | `core/sri-iva-percentages/{id}` | Update an IVA percentage |
| DELETE | `core/sri-iva-percentages/{id}` | Delete an IVA percentage |

---

## Core - Settings

Prefix: `api/v3/core/*-settings`, `api/v3/core/additional-info-presets`

| Method | Path | Description |
|---|---|---|
| PATCH | `core/customer-settings` | Update customer settings |
| GET | `core/payment-method-settings` | List payment method settings |
| PATCH | `core/payment-method-settings/{code}` | Update payment method settings |
| GET | `core/additional-info-presets` | List additional info presets |
| GET | `core/additional-info-presets/available` | List available presets |
| POST | `core/additional-info-presets` | Create a preset |
| PATCH | `core/additional-info-presets/{id}` | Update a preset |
| DELETE | `core/additional-info-presets/{id}` | Delete a preset |

---

## Core - Third Parties

Prefix: `api/v3/core/third-parties`

See [docs/third-party.md](third-party.md) for full documentation.

| Method | Path | Description |
|---|---|---|
| GET | `core/third-parties` | List all third parties |
| POST | `core/third-parties` | Create a third party |
| GET | `core/third-parties/{id}` | Get a third party |
| PATCH | `core/third-parties/{id}` | Update a third party |
| GET | `core/third-parties/roles` | List available roles |
| GET | `core/third-parties/customers` | List customers |
| GET | `core/third-parties/carriers` | List carriers |
| GET | `core/third-parties/availability` | Check identification availability |
| GET | `core/third-parties/{id}/customer` | Get a customer by third party ID |
| GET | `core/third-parties/field-definitions` | List field definitions |
| POST | `core/third-parties/field-definitions` | Create a field definition |
| PATCH | `core/third-parties/field-definitions/{definition}` | Update a field definition |
| DELETE | `core/third-parties/field-definitions/{definition}` | Deactivate a field definition |
| GET | `core/third-parties/{id}/fields` | Get custom field values |
| PATCH | `core/third-parties/{id}/fields` | Replace custom field values |

---

## Core - Vehicles

Prefix: `api/v3/core/vehicles`

| Method | Path | Description |
|---|---|---|
| GET | `core/vehicles` | List vehicles |
| POST | `core/vehicles` | Create a vehicle |
| GET | `core/vehicles/{id}` | Get a vehicle |
| PATCH | `core/vehicles/{id}` | Update a vehicle |

---

## Fiscal - Invoices

Prefix: `api/v3/invoices`

| Method | Path | Description |
|---|---|---|
| GET | `invoices` | List invoices |
| POST | `invoices` | Create an invoice |
| GET | `invoices/export` | Export invoices |
| GET | `invoices/payment-methods` | List payment methods |
| GET | `invoices/readiness` | Check invoice readiness |
| GET | `invoices/summary` | Get invoice summary |
| GET | `invoices/{id}` | Get an invoice |
| PATCH | `invoices/{id}` | Update an invoice |
| GET | `invoices/{id}/cancellation-workflows` | List cancellation workflows |
| POST | `invoices/{id}/cancellation-workflows` | Create a cancellation workflow |
| POST | `invoices/{id}/cancellation-workflows/verify` | Verify a cancellation workflow |
| GET | `invoices/{id}/ride` | Get RIDE PDF |
| GET | `invoices/{id}/signed-xml` | Get signed XML |
| GET | `invoices/{id}/sri-status` | Get SRI status |
| GET | `invoices/{id}/sri-submissions` | List SRI submissions |
| GET | `invoices/{id}/xml` | Get XML |

---

## Fiscal - Invoice Drafts

Prefix: `api/v3/invoice-drafts`

| Method | Path | Description |
|---|---|---|
| GET | `invoice-drafts` | List invoice drafts |
| POST | `invoice-drafts` | Create an invoice draft |
| GET | `invoice-drafts/current` | Get current draft |
| PATCH | `invoice-drafts/current` | Update current draft |
| DELETE | `invoice-drafts/current` | Delete current draft |
| GET | `invoice-drafts/{publicId}` | Get a draft by public ID |
| PATCH | `invoice-drafts/{publicId}` | Update a draft |
| DELETE | `invoice-drafts/{publicId}` | Delete a draft |

---

## Fiscal - SRI Admin

Prefix: `api/v3/admin`

| Method | Path | Description |
|---|---|---|
| PATCH | `admin/fiscal-sri-mode` | Update SRI mode (mock/celcer/sri) |
| GET | `admin/fiscal-dispatch-control` | Get dispatch control state |
| PATCH | `admin/fiscal-dispatch-control` | Update dispatch control |

---

## Fiscal - Bootstrap

Prefix: `api/v3/bootstrap`

| Method | Path | Description |
|---|---|---|
| GET | `bootstrap/nueva-factura` | Get invoice editor context |

---

## Platform - Admin

Prefix: `api/v3/platform/admin`

| Method | Path | Description |
|---|---|---|
| GET | `platform/admin/contracts` | List contracts |
| GET | `platform/admin/invoices` | List platform invoices |
| GET | `platform/admin/overview` | Get platform overview |
| GET | `platform/admin/support/tickets` | List support tickets |
| GET | `platform/admin/tenants` | List tenants |
| GET | `platform/admin/users` | List platform users |

---

## Platform - RBAC

Prefix: `api/v3/roles`, `api/v3/users`, `api/v3/permissions`, `api/v3/menus`

| Method | Path | Description |
|---|---|---|
| GET | `roles` | List roles |
| POST | `roles` | Create a role |
| GET | `roles/{id}` | Get a role |
| PATCH | `roles/{id}` | Update a role |
| DELETE | `roles/{id}` | Delete a role |
| POST | `roles/{id}/menus` | Assign menus to a role |
| GET | `users` | List users |
| POST | `users` | Create a user |
| GET | `users/{id}` | Get a user |
| PATCH | `users/{id}` | Update a user |
| DELETE | `users/{id}` | Delete a user |
| GET | `permissions` | List permissions |
| GET | `menus` | List menus |
| POST | `menus` | Create a menu |
| GET | `menus/tree` | Get menu tree |
| PATCH | `menus/{id}` | Update a menu |
| DELETE | `menus/{id}` | Delete a menu |

---

## Platform - Geography

Prefix: `api/v3/countries`, `api/v3/provinces`, `api/v3/cities`

| Method | Path | Description |
|---|---|---|
| GET | `countries` | List countries |
| GET | `provinces` | List provinces |
| GET | `cities` | List cities |

---

## Platform - SRI

Prefix: `api/v3/sri-environments`

| Method | Path | Description |
|---|---|---|
| GET | `sri-environments` | List SRI environments (pruebas/produccion) |

---

## Platform - Contexts

Prefix: `api/v3/contexts`

| Method | Path | Description |
|---|---|---|
| GET | `contexts/{context}` | Get context configuration |

---

## Platform - Enterprises

Prefix: `api/v3/enterprises`

| Method | Path | Description |
|---|---|---|
| GET | `enterprises` | List enterprises |
| POST | `enterprises` | Create an enterprise |
| GET | `enterprises/{id}` | Get an enterprise |
| GET | `enterprises/{enterprise}/users` | List enterprise users |
| POST | `enterprises/{enterprise}/users` | Create an enterprise user |
| PATCH | `enterprises/{enterprise}/users/{user}` | Update an enterprise user |
| DELETE | `enterprises/{enterprise}/users/{user}` | Delete an enterprise user |
| GET | `enterprises/{enterprise}/tax-settings` | Get enterprise tax settings |
| PATCH | `enterprises/{enterprise}/tax-settings` | Update enterprise tax settings |
| GET | `enterprises/{enterprise}/electronic-signature` | Get electronic signature |
| POST | `enterprises/{enterprise}/electronic-signature` | Create electronic signature |
| DELETE | `enterprises/{enterprise}/electronic-signature` | Delete electronic signature |
| GET | `enterprises/{enterprise}/sri-certification` | Get SRI certification status |
| POST | `enterprises/{enterprise}/sri-certification` | Start SRI certification |
| GET | `enterprises/{enterprise}/sri-certification/{runId}` | Get SRI certification run |

---

## Response Envelope

All V3 responses follow the standard envelope:

```json
{
  "status": true|false,
  "message": "",
  "data": ...
}
```

## Middleware

All V3 routes (except `auth/challenges` and `auth/sessions`) require:
- `auth.v3.cookie` — validates the V3 session cookie
- `tenant.v3.context` — sets the PostgreSQL `app.tenant_id` session variable for RLS

## Related Documentation

- [ThirdParty Module](third-party.md)
- [Fiscal Worker + SRI](fiscal-worker.md)
- [Worker Documentation](../documentation_worker.md)
