#!/bin/bash

set -euo pipefail

LOG_FILE="/var/log/pg_restore_test.log"
DATE=$(date +'%F_%T')

echo "=== PG Restore Test started at $DATE ===" >> "$LOG_FILE"

# Config : apps à tester et bucket S3
APPS=("outline" "nocodb")
S3_BUCKET="d4g-backups-prod"
TMP_DIR="/tmp/pg_restore_test_$DATE"
mkdir -p "$TMP_DIR"

# Fonction pour nettoyer les conteneurs créés
cleanup() {
    echo "Cleaning up..." >> "$LOG_FILE"
    docker rm -f pg-restore-test-container || true
    rm -rf "$TMP_DIR"
}
trap cleanup EXIT

# Télécharger les dumps depuis S3
for APP in "${APPS[@]}"; do
    echo "Downloading latest dump for $APP from s3://$S3_BUCKET/$APP/" >> "$LOG_FILE"
    
    # Récupère le dernier dump (.dump) dans le dossier S3 spécifique à l'app
    # Assure-toi que AWS CLI est configuré et les permissions sont OK
    LATEST_DUMP=$(aws s3 ls "s3://$S3_BUCKET/$APP/" --recursive | sort | tail -n 1 | awk '{print $4}')
    
    if [ -z "$LATEST_DUMP" ]; then
        echo "❌ No dump found for $APP in bucket $S3_BUCKET" >> "$LOG_FILE"
        continue
    fi
    
    echo "Found dump: $LATEST_DUMP" >> "$LOG_FILE"
    
    aws s3 cp "s3://$S3_BUCKET/$LATEST_DUMP" "$TMP_DIR/$APP.dump"
done

# Lancer un conteneur PostgreSQL temporaire
echo "Starting temporary PostgreSQL container..." >> "$LOG_FILE"
docker run -d --name pg-restore-test-container -e POSTGRES_PASSWORD=pgtest -p 5433:5432 postgres:14

# Attendre que PostgreSQL soit prêt
until docker exec pg-restore-test-container pg_isready -U postgres > /dev/null 2>&1; do
    echo "Waiting for PostgreSQL to be ready..." >> "$LOG_FILE"
    sleep 2
done

echo "PostgreSQL ready!" >> "$LOG_FILE"

# Pour chaque dump, créer une DB, restaurer, vérifier
for APP in "${APPS[@]}"; do
    DUMP_FILE="$TMP_DIR/$APP.dump"
    if [ ! -f "$DUMP_FILE" ]; then
        echo "Skipping $APP, dump not downloaded." >> "$LOG_FILE"
        continue
    fi

    DB_NAME="test_${APP}_$(date +'%Y%m%d_%H%M%S')"
    echo "Creating database $DB_NAME" >> "$LOG_FILE"

    docker exec pg-restore-test-container psql -U postgres -c "CREATE DATABASE $DB_NAME;"
    if [ $? -ne 0 ]; then
        echo "❌ Failed to create database $DB_NAME" >> "$LOG_FILE"
        continue
    fi

    echo "Restoring dump for $APP into $DB_NAME..." >> "$LOG_FILE"
    if docker exec -i pg-restore-test-container pg_restore --exit-on-error --verbose -U postgres -d "$DB_NAME" < "$DUMP_FILE"; then
        echo "✅ Success: $APP restored into $DB_NAME" >> "$LOG_FILE"
    else
        echo "❌ Failure: $APP failed to restore" >> "$LOG_FILE"
        # Ici tu peux ajouter un appel curl/mail si tu veux une alerte
    fi

    echo "Dropping database $DB_NAME" >> "$LOG_FILE"
    docker exec pg-restore-test-container psql -U postgres -c "DROP DATABASE $DB_NAME;"
done

echo "Stopping and removing PostgreSQL container..." >> "$LOG_FILE"
docker rm -f pg-restore-test-container

echo "=== PG Restore Test finished at $(date +'%F_%T') ===" >> "$LOG_FILE"
