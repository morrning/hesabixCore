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

# Check system requirements
print_message "Checking system requirements..."
chmod +x docker/check-requirements.sh
./docker/check-requirements.sh

if [ $? -ne 0 ]; then
    print_error "System requirements check failed."
    exit 1
fi

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    print_error "Docker is not installed. Please install Docker first."
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    print_error "Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

# Copy .env.example to .env if it doesn't exist
if [ ! -f .env ]; then
    print_message "Copying .env.example to .env"
    cp .env.example .env
    print_warning "Please configure values in the .env file."
    exit 0
fi

# Create SSL directory if it doesn't exist
if [ ! -d "ssl" ]; then
    print_message "Creating SSL directory..."
    mkdir -p ssl
fi

# Build and start containers
print_message "Building and starting containers..."
docker-compose up -d

# Check container status
if [ $? -eq 0 ]; then
    print_message "Containers started successfully."
else
    print_error "Error starting containers."
    exit 1
fi

# Initialize database
print_message "Initializing database..."
chmod +x docker/init-db.sh
./docker/init-db.sh

if [ $? -eq 0 ]; then
    print_message "Database initialized successfully."
else
    print_error "Error initializing database."
    exit 1
fi

# Setup SSL if domain is provided
if [ -n "$DOMAIN" ] && [ "$DOMAIN" != "localhost" ]; then
    print_message "Setting up SSL for $DOMAIN..."
    docker-compose run --rm certbot certonly --webroot --webroot-path /var/www/html -d $DOMAIN -d www.$DOMAIN --email admin@$DOMAIN --agree-tos --non-interactive
    
    if [ $? -eq 0 ]; then
        print_message "SSL setup completed successfully."
    else
        print_warning "SSL setup failed. Continuing without SSL."
    fi
fi

print_message "Installation completed successfully."
print_message "Website: http://localhost"
if [ -n "$DOMAIN" ] && [ "$DOMAIN" != "localhost" ]; then
    print_message "Secure Website: https://$DOMAIN"
fi
print_message "phpMyAdmin: http://localhost:8080"

# Display logs
print_message "Displaying logs (press Ctrl+C to exit)..."
docker-compose logs -f 