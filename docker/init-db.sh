#!/bin/bash

# Colors for better display
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Function to display messages
print_message() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

# Function to display errors
print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to display warnings
print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# Wait for MySQL to be ready
print_message "Waiting for MySQL to be ready..."
while ! docker-compose exec -T db mysqladmin ping -h localhost -u root -proot_password --silent; do
    sleep 1
done

# Import initial database structure
print_message "Importing initial database structure..."
if [ -f "hesabixBackup/databasefiles/hesabix-db-default.sql" ]; then
    docker-compose exec -T db mysql -u root -proot_password hesabix_db < hesabixBackup/databasefiles/hesabix-db-default.sql
    if [ $? -eq 0 ]; then
        print_message "Initial database structure imported successfully."
    else
        print_error "Failed to import initial database structure."
        exit 1
    fi
else
    print_error "Initial database file not found at hesabixBackup/databasefiles/hesabix-db-default.sql"
    exit 1
fi

# Update database schema
print_message "Updating database schema..."
docker-compose exec -T web php /var/www/html/hesabixCore/bin/console doctrine:schema:update --force

if [ $? -eq 0 ]; then
    print_message "Database schema updated successfully."
else
    print_error "Failed to update database schema."
    exit 1
fi

print_message "Database initialization completed successfully." 