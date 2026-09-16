# Deploy Guide — Billing Platform Backend V3

This document describes how the Billing Platform Backend V3 is deployed to production at `157.230.149.22`.

## Architecture

The V3 backend runs as a Docker Compose stack with 5 containers, deployed alongside the existing ArtraFiscal backend (`artra_app`). Both backends run in parallel without interfering with each other.

### Containers

| Container | Image | Purpose |
|---|---|---|
| `billing_db_v3` | `postgres:16-alpine` | PostgreSQL 16, database `master_v3` |
| `billing_redis_v3` | `redis:7-alpine` | Cache for auth challenges |
| `billing_app_v3` | Custom (PHP 8.4 FPM) | Laravel V3 application |
| `billing_web_v3` | `nginx:alpine` | Nginx reverse proxy to PHP-FPM |
| `billing_openbao_v3` | `openbao/openbao:2.5.4` | Key custody (dev mode) |

### Network Flow

```
Internet
  → https://api-montex-staging.artra.cloud/billing/api/v3/...
  → production-nginx (tickets_back_proxy network)
  → billing_web_v3 (nginx, port 80)
  → billing_app_v3 (PHP-FPM, port 9000)
  → billing_db_v3 (PostgreSQL, port 5432)
```

The `production-nginx` strips the `/billing` prefix via `rewrite ^/billing/(.*)$ /$1 break;` before forwarding to `billing_web_v3`, so the backend receives `/api/v3/` normally.

### Coexistence with ArtraFiscal

| Route | Backend | Status |
|---|---|---|
| `/api/v1/` | `artra_web` → `artra_app` | Unchanged |
| `/api/v2/` | `artra_web` → `artra_app` | Unchanged |
| `/api/v3/` | `artra_web` → `artra_app` | Unchanged |
| `/billing/api/v3/` | `billing_web_v3` → `billing_app_v3` | New |

The `artra_app` backend is never modified or stopped.

## Database

The V3 backend uses a single PostgreSQL database: `master_v3`. This database contains all V3 schemas:

- `auth` — users, sessions, login attempts, preferences
- `core` — tenants, companies, establishments, third parties, products, vehicles, economic activities
- `fiscal` — invoices, sequences, document payments, dispatch controls
- `integration` — processing jobs, audit events
- `platform` — tenants catalog, audit events

The `enterprises` database (used by V1) is NOT needed for V3.

### Access via SSH Tunnel

The database is not exposed publicly. To connect from your local machine:

```bash
ssh -L 5434:127.0.0.1:5434 deploy@157.230.149.22
```

Then connect your IDE (Navicat, DBeaver, etc.) to:

- Host: `localhost`
- Port: `5434`
- Database: `master_v3`
- Username: `billing_user`
- Password: (see `.env` on the server)

## Environment Variables

Key environment variables (defined in `.env` on the server, never committed):

| Variable | Description | Example |
|---|---|---|
| `APP_KEY` | Laravel encryption key | `base64:...` |
| `APP_URL` | Public URL | `https://api-montex-staging.artra.cloud` |
| `MASTER_V3_DB_PASSWORD` | PostgreSQL password | (generated) |
| `SRI_PROVIDER_RUC` | SRI provider RUC (13 digits) | `1752331700001` |
| `SRI_MOCK` | SRI mock mode | `true` |
| `CORS_ALLOWED_ORIGINS` | Allowed CORS origins | `*` |

See `.env.docker` for the full template.

## Deploy Commands

### First-time Deploy

```bash
# 1. Clone repo on server
ssh deploy@157.230.149.22
git clone git@github.com:kevintoasa42-oss/billing_platformb_backend.git \
    /opt/access-pro/billing-platform/releases/backend-v3

# 2. Create .env from template
cd /opt/access-pro/billing-platform/releases/backend-v3
cp .env.docker .env
# Edit .env with proper secrets:
#   - Generate APP_KEY: php artisan key:generate --show
#   - Generate DB_PASSWORD: openssl rand -hex 24
#   - Set SRI_PROVIDER_RUC=1752331700001
#   - Set SRI_MOCK=true

# 3. Build and start
docker compose -f docker-compose.yml -f docker-compose.production.yml up --build -d

# 4. Wait for entrypoint to finish (migrations + seeders)
docker logs billing_app_v3 -f
```

### Subsequent Deploys

From your local machine (WSL):

```bash
# Normal deploy (keeps database)
./deploy.sh

# Fresh deploy (resets database + reseed)
./deploy.sh --fresh
```

The `deploy.sh` script:
1. SSHs to the server
2. Runs `git pull` on the repo
3. Rebuilds containers with `docker compose up --build -d`
4. Waits for the entrypoint to finish
5. Verifies V3 routes (176 expected)
6. Checks migration status
7. Tests the API endpoint (expects 401)
8. Shows recent logs

## Verification

After deploy, verify:

```bash
# Container status
ssh deploy@157.230.149.22 "cd /opt/access-pro/billing-platform/releases/backend-v3 && docker compose -f docker-compose.yml -f docker-compose.production.yml ps"

# V3 routes (should be 176)
ssh deploy@157.230.149.22 "docker exec billing_app_v3 php artisan route:list | grep -c v3"

# Migration status
ssh deploy@157.230.149.22 "docker exec billing_app_v3 php artisan v3:migrate:status"

# API test (should return 401)
curl -s -o /dev/null -w "%{http_code}" https://api-montex-staging.artra.cloud/billing/api/v3/core/third-parties

# Old backend still works (should return 401)
curl -s -o /dev/null -w "%{http_code}" https://api-montex-staging.artra.cloud/api/v3/core/third-parties
```

## Rollback

If the new backend fails:

```bash
# 1. Restore production-nginx config
ssh deploy@157.230.149.22 "cp /opt/access-pro/tickets_back/nginx/conf.d/default.prod.conf.bak.pre-billing-v3 /opt/access-pro/tickets_back/nginx/conf.d/default.prod.conf.bak"
ssh deploy@157.230.149.22 "docker exec production-nginx nginx -s reload"

# 2. Stop the V3 containers (optional)
ssh deploy@157.230.149.22 "cd /opt/access-pro/billing-platform/releases/backend-v3 && docker compose -f docker-compose.yml -f docker-compose.production.yml down"
```

The `artra_app` backend is never affected by rollback.

## Files

| File | Description |
|---|---|
| `Dockerfile` | PHP 8.4 FPM Alpine image with extensions + Java + Composer |
| `docker/entrypoint-app.sh` | Entrypoint: waits for PG, runs v3:migrate + v3:seed, starts PHP-FPM |
| `docker/nginx.conf` | Nginx reverse proxy config |
| `docker/nginx.Dockerfile` | Hardened Nginx image |
| `docker/postgres.Dockerfile` | Hardened PostgreSQL 16 image |
| `docker/init-multiple-dbs.sh` | Creates `master_v3` database on first run |
| `docker/php-security.ini` | PHP security settings |
| `docker-compose.yml` | Full stack: db, redis, app, web, openbao |
| `docker-compose.production.yml` | Production overrides: hardening, resource limits |
| `.env.docker` | Environment template |
| `deploy.sh` | Deploy script for subsequent updates |
