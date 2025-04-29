#!/bin/bash

# Colors for better display
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Required values
REQUIRED_DISK_SPACE_MB=2000
REQUIRED_MEMORY_MB=1024

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

# Check root privileges
if [ "$EUID" -ne 0 ]; then
    print_error "This script must be run as root"
    exit 1
fi

# Check Docker installation
if ! command -v docker &> /dev/null; then
    print_error "Docker is not installed. Please install Docker first."
    exit 1
fi

# Check Docker Compose installation
if ! command -v docker-compose &> /dev/null; then
    print_error "Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

# Check disk space
available_space=$(df -m / | awk 'NR==2 {print $4}')
if [ "$available_space" -lt "$REQUIRED_DISK_SPACE_MB" ]; then
    print_error "Insufficient disk space. Required: ${REQUIRED_DISK_SPACE_MB}MB, Available: ${available_space}MB"
    exit 1
fi

# Check memory
total_memory=$(free -m | awk '/^Mem:/{print $2}')
if [ "$total_memory" -lt "$REQUIRED_MEMORY_MB" ]; then
    print_warning "Low memory detected. Performance may be affected."
fi

# Check internet connectivity
print_message "Checking internet connectivity..."
if ! ping -c 1 8.8.8.8 >/dev/null 2>&1; then
    print_error "No internet connection detected. Please ensure the server has internet access."
    exit 1
fi

print_message "System requirements check completed successfully." 