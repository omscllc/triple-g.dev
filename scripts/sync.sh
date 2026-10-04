#!/bin/bash

######################################################################
# 
# Script to synchronize files and databases between the production
# server and the local development environment.
#
# Usage: ./scripts/sync.sh
#
# NOTE: Run this script from the project root directory.
#
# WARNING: This script will DROP the local database before importing
#          the remote database. Ensure you have backups if necessary.
#
######################################################################

# Project root
PROJECT_ROOT="${PWD}"
INSTALL_FLAG=".install_completed"

# Load environment variables from .env file
if [ -f "${PROJECT_ROOT}/.env" ]; then
    set -a
    source "${PROJECT_ROOT}/.env"
    set +a
else
    echo "Warning: .env file not found. Using default values."
fi

# Local directory path to sync with remote server
LOCAL_SYNC_DIR="${PROJECT_ROOT}/web/assets/"

# Synchronize directly from a local production checkout when enabled.
SYNC_LOCAL="${SYNC_LOCAL:-false}"
SYNC_PROD_ROOT="${SYNC_PROD_ROOT:-/home/cansmof/prod.montreal2027.ca}"

# Hostname or IP to sync from
SYNC_FROM="${SYNC_HOST:-montreal2027.ca}"

# Remote user for SSH
REMOTE_USER="${SYNC_USER:-www-data}"

# Remote root directory
REMOTE_ROOT="${SYNC_REMOTE_ROOT:-/var/www/montreal2027.ca/}"

# SSH key path and port
SSH_KEY="${SYNC_SSH_KEY:-~/.ssh/montreal2027}"
SSH_PORT="${SYNC_SSH_PORT:-345}"

# Local backup directory
BACKUP_DIR="${SYNC_BACKUP_DIR:-../backups}"

# Backup filename prefix
BU_FILENAME_PREFIX="${BU_FILENAME_PREFIX:-m2027-local}"

# Directory to sync
REMOTE_DIR="${REMOTE_ROOT}/web/assets/"

# Path to PHP
PHP_PATH="${PHP_PATH:-/usr/bin/php}"

# Determine drush command based on environment type
APP_ENV_TYPE="${APP_ENV_TYPE:-docker}"
if [ "$APP_ENV_TYPE" = "docker" ]; then
    echo "Using Docker environment for local commands..."

    # Check if the web container is running
    if ! docker compose ps --services --filter "status=running" | grep -q "^web$"; then
        echo "Docker container 'web' is not running."
        read -p "Do you want to start the containers now? (y/N): " start_containers
        if [[ "$start_containers" =~ ^[Yy]$ ]]; then
            echo "Starting Docker containers..."
            docker compose up -d --wait
        else
            echo "Cannot proceed without containers running. Exiting."
            exit 1
        fi
    fi

    DRUSH="docker compose exec -T web ./vendor/bin/drush"
    COMPOSER="docker compose exec -T web composer"
else
    echo "Using native environment for local commands..."
    DRUSH="./vendor/bin/drush"
    COMPOSER="composer"
fi

if [ ! -f "$INSTALL_FLAG" ]; then
    echo "Initial setup detected. Running composer install..."
    $COMPOSER install
    
    if [ "$APP_ENV_TYPE" = "docker" ]; then
        echo "Creating drush symlink in container..."
        docker compose exec -T web ln -sf /var/www/html/vendor/bin/drush /usr/local/sbin/drush
    fi
fi

# Sync files using rsync
if [ "$SYNC_LOCAL" = "true" ]; then
    if [ ! -d "$SYNC_PROD_ROOT" ]; then
        echo "Local production directory not found: ${SYNC_PROD_ROOT}"
        exit 1
    fi

    echo "Starting local file synchronization of assets from ${SYNC_PROD_ROOT}..."
    rsync -av --delete --exclude 'php/*' --progress "${SYNC_PROD_ROOT%/}/web/assets/" "${LOCAL_SYNC_DIR}"
else
    echo "Starting file synchronization of assets from ${SYNC_FROM}..."
    rsync -avz --delete -e "ssh -i ${SSH_KEY} -p ${SSH_PORT}" --exclude 'php/*' --progress "${REMOTE_USER}@${SYNC_FROM}:${REMOTE_DIR}" "${LOCAL_SYNC_DIR}"
fi

read -p "Syncronize database with production? (Any existing local database will be replaced) (y/N): " confirm
if [[ "$confirm" =~ ^[Nn]$ ]]
then
    echo "Database synchronization aborted."
    exit 0
fi

cd "${PROJECT_ROOT}"

if [ "$SYNC_LOCAL" != "true" ]; then
    echo "Downloading remote database..."
    ssh "${REMOTE_USER}@${SYNC_FROM}" -i "${SSH_KEY}" -p "${SSH_PORT}" "(cd ${REMOTE_ROOT} && ${PHP_PATH} ./vendor/bin/drush.php sql:dump)" > dump.sql
fi

if [ ! -f "$INSTALL_FLAG" ]; then
    echo "Initial installation detected. Skipping database backup, config export, and drop."
else
    echo "Backing up local database..."
    mkdir -p "${BACKUP_DIR}"
    $DRUSH --gzip sql:dump > "${BACKUP_DIR}/${BU_FILENAME_PREFIX}-$(date +%Y-%m-%d_%H:%M).sql"

    echo "Exporting changes..."
    $DRUSH cex

    echo "Dropping local database..."
    $DRUSH sql:drop -y
fi

echo "Importing production database into local database..."
if [ "$APP_ENV_TYPE" = "docker" ]; then
    docker compose exec -T web bash -c "$(${DRUSH} sql:connect) < /var/www/html/dump.sql"
elif [ "$SYNC_LOCAL" = "true" ]; then
    DEST_CONNECT=$(${DRUSH} sql:connect)
    (unset DB_NAME DB_USER DB_PASSWORD DB_HOST DATABASE_URL && \
    cd "$SYNC_PROD_ROOT" && \
    set -a && source .env && set +a && \
    ./vendor/bin/drush sql:dump) | $DEST_CONNECT
else
    $(${DRUSH} sql:connect) < dump.sql
fi

echo "Importing any exported changes..."
$DRUSH deploy

# Clean up
if [ "$SYNC_LOCAL" != "true" ]; then
    echo "Cleaning up temporary files..."
    rm dump.sql
fi

echo "Synchronization complete."

