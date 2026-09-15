#!/usr/bin/env bash
set -e

echo "=========================================="
echo " Deploying 3-Tier TLS App to poc.d1dev.fr"
echo "=========================================="

# 1. Pull latest changes
if [ -d ".git" ]; then
    echo "[1/5] Pulling latest code from git..."
    git pull origin main || true
fi

# 2. Check / Create JWT Keypair if missing
if [ ! -f "backend/config/jwt/private.pem" ]; then
    echo "[2/5] Generating JWT RSA keypair..."
    mkdir -p backend/config/jwt
    openssl genpkey -out backend/config/jwt/private.pem -algorithm RSA -pkeyopt rsa_keygen_bits:4096
    openssl pkey -in backend/config/jwt/private.pem -out backend/config/jwt/public.pem -pubout
    chmod 644 backend/config/jwt/private.pem backend/config/jwt/public.pem
fi

# 3. Build & start Docker containers
echo "[3/5] Building & starting Docker services..."
docker compose build --no-cache
docker compose up -d

# 4. Install PHP dependencies & database migrations
echo "[4/5] Running Composer & Database Fixtures inside backend container..."
docker compose exec -T backend composer install --no-dev --optimize-autoloader
docker compose exec -T backend php bin/console doctrine:schema:update --force || true
docker compose exec -T backend php bin/console doctrine:fixtures:load --no-interaction || true

echo "=========================================="
echo " Deployment Complete!"
echo " Application live at: https://poc.d1dev.fr"
echo " EasyAdmin Dashboard: https://poc.d1dev.fr/admin"
echo " API Platform Swagger: https://poc.d1dev.fr/api"
echo "=========================================="
