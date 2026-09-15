#!/usr/bin/env bash
set -e

echo "=========================================="
echo " Deploying 3-Tier TLS App to poc.d1dev.fr"
echo "=========================================="

COMPOSE_FILE="docker-compose.yml"

# Detect if Traefik network exists on host server
if docker network ls | grep -q "traefik_net"; then
    echo "[+] Traefik network detected (traefik_net)! Using docker-compose.traefik.yml..."
    COMPOSE_FILE="docker-compose.traefik.yml"
elif docker network ls | grep -q "web"; then
    echo "[+] Traefik web network detected (web)! Using docker-compose.traefik.yml..."
    COMPOSE_FILE="docker-compose.traefik.yml"
fi

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
echo "[3/5] Building & starting Docker services using $COMPOSE_FILE..."
docker compose -f $COMPOSE_FILE build --no-cache
docker compose -f $COMPOSE_FILE up -d

# 4. Install PHP dependencies & database fixtures
echo "[4/5] Running Composer & Database Fixtures inside backend container..."
docker compose -f $COMPOSE_FILE exec -T backend composer install --no-dev --optimize-autoloader
docker compose -f $COMPOSE_FILE exec -T backend php bin/console doctrine:schema:update --force || true
docker compose -f $COMPOSE_FILE exec -T backend php bin/console doctrine:fixtures:load --no-interaction || true

echo "=========================================="
echo " Deployment Complete!"
echo " Application live at: https://poc.d1dev.fr"
echo " EasyAdmin Dashboard: https://poc.d1dev.fr/admin"
echo " API Platform Swagger: https://poc.d1dev.fr/api"
echo "=========================================="
