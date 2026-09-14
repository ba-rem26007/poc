#!/usr/bin/env bash
set -e

echo "========================================="
echo " Setting up 3-Tier TLS Application"
echo "========================================="

echo "[1/3] Installing Backend Dependencies & Preparing Database..."
cd backend
composer install --no-interaction
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db" php bin/console doctrine:schema:create --force || true
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db" php bin/console doctrine:fixtures:load --no-interaction
cd ..

echo "[2/3] Installing Frontend Dependencies & Building..."
cd frontend
npm install
npm run build
cd ..

echo "[3/3] Setup Completed Successfully!"
echo ""
echo "To start services with Docker Compose (TLS):"
echo "  docker compose up -d"
echo ""
echo "URLs:"
echo "  Frontend:     https://localhost"
echo "  Swagger API:  https://localhost/api"
echo "  EasyAdmin:    https://localhost/admin"
