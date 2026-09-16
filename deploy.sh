#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────────────────────
# deploy.sh — Deploy script for billing_platformb_backend V3
#
# Usage:
#   ./deploy.sh           Normal deploy (git pull + rebuild + verify)
#   ./deploy.sh --fresh   Reset database + reseed from scratch
#
# This script does NOT touch artra_app or production-nginx.
# It only updates the billing_*_v3 containers.
# ─────────────────────────────────────────────────────────────────────────────
set -euo pipefail

SSH_TARGET="deploy@157.230.149.22"
REMOTE_DIR="/opt/access-pro/billing-platform/releases/backend-v3"
COMPOSE_FILES="-f docker-compose.yml -f docker-compose.production.yml"
FRESH_MODE=false

# Parse arguments
for arg in "$@"; do
    case "$arg" in
        --fresh)
            FRESH_MODE=true
            ;;
        *)
            echo "Unknown argument: $arg"
            echo "Usage: $0 [--fresh]"
            exit 1
            ;;
    esac
done

echo "=== Billing Platform V3 Deploy ==="
echo "Target: $SSH_TARGET:$REMOTE_DIR"
echo "Fresh mode: $FRESH_MODE"
echo ""

# Step 1: Ensure repo exists on server (clone if needed)
echo "[1/6] Ensuring repo exists on server..."
ssh "$SSH_TARGET" "if [ ! -d '$REMOTE_DIR' ]; then
    echo '  Cloning repo...'
    mkdir -p '$(dirname $REMOTE_DIR)'
    git clone git@github.com:kevintoasa42-oss/billing_platformb_backend.git '$REMOTE_DIR'
else
    echo '  Repo already exists.'
fi"

# Step 2: Git pull
echo "[2/6] Pulling latest changes..."
ssh "$SSH_TARGET" "cd $REMOTE_DIR && git pull origin main"

# Step 3: Ensure .env exists
echo "[3/6] Checking .env..."
ssh "$SSH_TARGET" "cd $REMOTE_DIR && if [ ! -f .env ]; then
    echo '  ERROR: .env not found. Create it from .env.docker with proper secrets.'
    exit 1
fi"

# Step 4: Fresh mode — reset database
if [ "$FRESH_MODE" = true ]; then
    echo "[4/6] Fresh mode: resetting database..."
    ssh "$SSH_TARGET" "cd $REMOTE_DIR && docker compose $COMPOSE_FILES down -v"
    echo "  Volumes removed. Database will be recreated on next up."
else
    echo "[4/6] Normal mode: keeping database."
fi

# Step 5: Build and start containers
echo "[5/6] Building and starting containers..."
# Build images with --network=host (server DNS requires it)
ssh "$SSH_TARGET" "cd $REMOTE_DIR && DOCKER_BUILDKIT=0 docker build --network=host -t backend-v3-app -f Dockerfile . && DOCKER_BUILDKIT=0 docker build --network=host -t backend-v3-web -f docker/nginx.Dockerfile . && DOCKER_BUILDKIT=0 docker build --network=host -t backend-v3-db_v3 -f docker/postgres.Dockerfile ."
# Start containers (without --build, using pre-built images)
ssh "$SSH_TARGET" "cd $REMOTE_DIR && docker compose $COMPOSE_FILES up -d"

# Step 5b: Run migrations and seeders (entrypoint is overridden in production)
echo "[5b/6] Running V3 migrations..."
ssh "$SSH_TARGET" "docker exec billing_app_v3 php artisan v3:migrate 2>&1 | tail -5"
echo "[5b/6] Running V3 seeders..."
ssh "$SSH_TARGET" "docker exec billing_app_v3 php artisan v3:seed 2>&1 | tail -5"

# Step 6: Wait for entrypoint to finish and verify
echo "[6/6] Waiting for entrypoint to finish (migrations + seeders)..."
sleep 10

echo "  Checking container status..."
ssh "$SSH_TARGET" "cd $REMOTE_DIR && docker compose $COMPOSE_FILES ps"

echo ""
echo "  Checking V3 routes..."
ROUTE_COUNT=$(ssh "$SSH_TARGET" "docker exec billing_app_v3 php artisan route:list 2>/dev/null | grep -c v3 || echo 0")
echo "  V3 routes: $ROUTE_COUNT (expected: 176)"

echo ""
echo "  Checking migrations..."
ssh "$SSH_TARGET" "docker exec billing_app_v3 php artisan v3:migrate:status 2>&1 | tail -5"

echo ""
echo "  Testing API endpoint (expect 401)..."
HTTP_CODE=$(ssh "$SSH_TARGET" "docker exec production-nginx curl -s -o /dev/null -w '%{http_code}' http://billing_web_v3/api/v3/core/third-parties 2>/dev/null || echo 'failed'")
echo "  Internal HTTP status: $HTTP_CODE (expected: 401)"

echo ""
echo "=== Deploy complete ==="
echo ""
echo "Recent logs:"
ssh "$SSH_TARGET" "docker logs billing_app_v3 --tail 20 2>&1"
