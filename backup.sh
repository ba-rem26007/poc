#!/usr/bin/env bash
set -e

BACKUP_DIR="./backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="${BACKUP_DIR}/postgres_backup_${TIMESTAMP}.sql.gz"

mkdir -p "${BACKUP_DIR}"

echo "=========================================="
echo " Starting PostgreSQL Database Backup"
echo "=========================================="

docker compose exec -T db pg_dump -U symfony app_db | gzip > "${BACKUP_FILE}"

echo "[+] Backup successfully created at: ${BACKUP_FILE}"

# Cleanup backups older than 7 days
echo "[+] Cleaning up backups older than 7 days..."
find "${BACKUP_DIR}" -type f -name "*.sql.gz" -mtime +7 -delete

echo "=========================================="
echo " Backup Completed Successfully!"
echo "=========================================="
