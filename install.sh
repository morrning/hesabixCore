#!/bin/bash

# Hesabix Installation Script
# Version: 2.6
# Modernized and optimized for Ubuntu 24.04 LTS
# Last Updated: April 28, 2025

# Exit on any error
set -e

# Colors for better UI
declare -r RED='\e[31m'
declare -r GREEN='\e[32m'
declare -r YELLOW='\e[33m'
declare -r BLUE='\e[34m'
declare -r NC='\e[0m'
declare -r BOLD='\e[1m'
declare -r UNDERLINE='\e[4m'

# ASCII Art
print_logo() {
    echo -e "${BOLD}${BLUE}"
    echo "  _  _   ___   ___     _     ___   ___  __  __"
    echo " | || | | __| / __|   /_\   | _ ) |_ _| \ \/ /"
    echo " | __ | | _|  \__ \  / _ \  | _ \  | |   >  < "
    echo " |_||_| |___| |___/ /_/ \_\ |___/ |___| /_/\_\\"
    echo -e "${NC}"
}

# Print header
print_header() {
    clear
    print_logo
    echo -e "${BOLD}${BLUE}=================================================${NC}"
    echo -e "${BOLD}${BLUE}           Hesabix Installation Script           ${NC}"
    echo -e "${BOLD}${BLUE}=================================================${NC}"
    echo -e "${YELLOW}Hesabix is a powerful open-source accounting software${NC}"
    echo -e "${YELLOW}developed with ❤ by Babak Alizadeh (alizadeh.babak)${NC}"
    echo -e "${YELLOW}License: GNU GPL v3${NC}"
    echo -e "${YELLOW}Website: ${UNDERLINE}https://hesabix.ir${NC}"
    echo -e "${YELLOW}Support us: ${UNDERLINE}https://hesabix.ir/page/sponsors${NC} ❤"
    echo -e "${BOLD}${BLUE}=================================================${NC}\n"
}

# Show the header immediately
print_header

# Configuration
declare -r LOG_FILE="/var/log/hesabix_install.log"
declare -r LOG_LEVEL="DEBUG"  # Options: DEBUG, INFO, WARNING, ERROR
declare -r REQUIRED_DISK_SPACE_MB=2000
declare -r REQUIRED_MEMORY_MB=1024
declare -r NODE_VERSION="20"
declare -r COMPOSER_TIMEOUT=600
declare -r NPM_TIMEOUT=600

# Global variables
declare SEND_TELEMETRY=false
declare domain=""
declare apache_user="www-data"

# Function to log messages in JSON format
log_message() {
    local level="$1"
    local message="$2"
    local timestamp
    timestamp=$(date -u +"%Y-%m-%dT%H:%M:%SZ")
    
    # Create JSON log entry
    local log_entry
    log_entry=$(printf '{"timestamp":"%s","level":"%s","message":"%s"}\n' "$timestamp" "$level" "$message")
    
    # Write to log file
    echo "$log_entry" >> "$LOG_FILE"
    
    # Display to console based on level
    case "$level" in
        "INFO")
            echo -e "${GREEN}[INFO] $message${NC}"
            ;;
        "WARNING")
            echo -e "${YELLOW}[WARNING] $message${NC}"
            ;;
        "ERROR")
            echo -e "${RED}[ERROR] $message${NC}"
            ;;
        "DEBUG")
            [[ "$LOG_LEVEL" == "DEBUG" ]] && echo -e "${BLUE}[DEBUG] $message${NC}"
            ;;
    esac
}

# Function to initialize logging
init_logging() {
    mkdir -p "$(dirname "$LOG_FILE")" || {
        echo -e "${RED}Error: Cannot create log directory${NC}"
        exit 1
    }
    
    touch "$LOG_FILE" || {
        echo -e "${RED}Error: Cannot create log file${NC}"
        exit 1
    }
    
    chmod 644 "$LOG_FILE"
    
    log_message "INFO" "Hesabix installation script started"
    log_message "INFO" "Log file: $LOG_FILE"
    log_message "INFO" "To view logs: tail -f $LOG_FILE"
}

# Function to handle errors
handle_error() {
    local message="$1"
    local exit_code="${2:-1}"
    
    log_message "ERROR" "$message"
    log_message "ERROR" "Installation failed. Check logs: $LOG_FILE"
    exit "$exit_code"
}

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to check internet connectivity
check_internet() {
    log_message "INFO" "Checking internet connectivity..."
    if ! ping -c 1 8.8.8.8 >/dev/null 2>&1; then
        handle_error "No internet connection detected. Please ensure the server has internet access."
    fi
    log_message "INFO" "Internet connectivity verified"
}

# Function to validate system requirements
check_system_requirements() {
    log_message "INFO" "Checking system requirements..."
    
    # Check root privileges
    [[ "$EUID" -ne 0 ]] && handle_error "This script must be run as root"
    
    # Check OS compatibility
    if [[ -f /etc/os-release ]]; then
        . /etc/os-release
        log_message "INFO" "Detected OS: $NAME $VERSION"
        
        [[ ! "$ID" =~ ^(ubuntu|debian)$ ]] && handle_error "Only Ubuntu and Debian are supported"
    else
        handle_error "Cannot determine OS type"
    fi
    
    # Check disk space
    local available_space
    available_space=$(df -m / | awk 'NR==2 {print $4}')
    log_message "DEBUG" "Required disk space: ${REQUIRED_DISK_SPACE_MB}MB"
    log_message "DEBUG" "Available disk space: ${available_space}MB"
    
    [[ "$available_space" -lt "$REQUIRED_DISK_SPACE_MB" ]] && \
        handle_error "Insufficient disk space. Required: ${REQUIRED_DISK_SPACE_MB}MB, Available: ${available_space}MB"
    
    # Check memory
    local total_memory
    total_memory=$(free -m | awk '/^Mem:/{print $2}')
    log_message "DEBUG" "Required memory: ${REQUIRED_MEMORY_MB}MB"
    log_message "DEBUG" "Total memory: ${total_memory}MB"
    
    [[ "$total_memory" -lt "$REQUIRED_MEMORY_MB" ]] && \
        log_message "WARNING" "Low memory detected. Performance may be affected"
    
    log_message "INFO" "System requirements check completed"
}

# Function to update package lists
update_packages() {
    log_message "INFO" "Updating package lists..."
    apt-get update -y || handle_error "Failed to update package lists"
}

# Function to install required tools
install_tools() {
    local tools=("curl" "coreutils" "git" "unzip")
    
    # Update package lists first
    update_packages
    
    for tool in "${tools[@]}"; do
        if ! command_exists "$tool"; then
            log_message "INFO" "Installing $tool..."
            apt-get install -y "$tool" || handle_error "Failed to install $tool"
            log_message "INFO" "$tool installed successfully"
        else
            log_message "INFO" "$tool is already installed"
        fi
    done
}

# Function to get installed PHP version
get_php_version() {
    local version
    version=$(php -v | head -n 1 | awk '{print $2}' | cut -d'.' -f1,2)
    echo "$version"
}

# Function to get all installed PHP versions
get_installed_php_versions() {
    local versions=()
    for version_dir in /etc/php/*; do
        if [[ -d "$version_dir" && "$version_dir" =~ /etc/php/[0-9]+\.[0-9]+$ ]]; then
            versions+=("$(basename "$version_dir")")
        fi
    done
    echo "${versions[@]}"
}

# Function to check if all required extensions are installed for all PHP versions
check_required_extensions() {
    local missing_packages=()
    local installed_versions
    installed_versions=($(get_installed_php_versions))
    
    for version in "${installed_versions[@]}"; do
        log_message "INFO" "Checking required packages for PHP $version..."
        for pkg in php${version}-raphf php${version}-http php${version}-dom php${version}-xml php${version}-gd php${version}-curl php${version}-simplexml php${version}-xmlwriter php${version}-zip; do
            if ! dpkg -l | grep -q "^ii  $pkg "; then
                missing_packages+=("$pkg")
                log_message "WARNING" "Package $pkg is missing"
            fi
        done
    done
    
    if [[ ${#missing_packages[@]} -gt 0 ]]; then
        echo -e "\nWarning: The following packages are missing:"
        printf '%s\n' "${missing_packages[@]}"
        echo -e "\nThese packages will need to be installed manually after the installation is complete."
        return 0
    fi
    
    return 0
}

# Function to verify CLI extensions
verify_cli_extensions() {
    log_message "INFO" "Verifying CLI extensions for PHP..."
    
    # Get the list of enabled extensions from CLI
    local enabled_extensions
    enabled_extensions=$(php -m)
    
    # Check each required extension
    for ext in iconv raphf http dom xml gd curl simplexml xmlwriter zip intl mbstring mysql bcmath; do
        if ! echo "$enabled_extensions" | grep -q "^$ext$"; then
            log_message "ERROR" "Extension $ext is not enabled in CLI"
            log_message "INFO" "Attempting to enable $ext for CLI..."
            
            # Try to enable the extension
            phpenmod "$ext" || {
                log_message "ERROR" "Failed to enable $ext for CLI"
                return 1
            }
            
            # Verify again after enabling
            enabled_extensions=$(php -m)
            if ! echo "$enabled_extensions" | grep -q "^$ext$"; then
                log_message "ERROR" "Extension $ext is still not enabled in CLI"
                return 1
            fi
        fi
    done
    
    return 0
}

# Function to install PHP and extensions
install_php() {
    log_message "INFO" "Checking PHP installation..."
    
    # Update package lists first
    update_packages
    
    # Remove any existing PHP packages first
    apt-get remove --purge php* -y
    apt-get autoremove -y
    apt-get clean
    
    # Install PHP and required extensions
    log_message "INFO" "Installing PHP and required extensions..."
    local php_packages=(
        "php"
        "php-cli"
        "libapache2-mod-php"
        "php-intl"
        "php-mbstring"
        "php-zip"
        "php-gd"
        "php-mysql"
        "php-curl"
        "php-xml"
        "php-bcmath"
        "php-raphf"
        "php-http"
        "php-dom"
        "php-simplexml"
        "php-xmlwriter"
        "php-iconv"
    )
    
    # Install each package individually to handle dependencies
    for pkg in "${php_packages[@]}"; do
        log_message "INFO" "Installing $pkg..."
        if ! apt-get install -y "$pkg"; then
            log_message "WARNING" "Failed to install $pkg, continuing with other packages..."
        fi
    done
    
    # Install specific PHP 8.3 packages
    for pkg in php8.3-dom php8.3-simplexml php8.3-xmlwriter; do
        if ! apt-get install -y "$pkg"; then
            log_message "WARNING" "Failed to install $pkg, continuing with other packages..."
        fi
    done
    
    # Enable PHP module for Apache
    a2enmod php8.3 || a2enmod php8.2 || a2enmod php8.1 || a2enmod php8.0 || a2enmod php7.4 || log_message "WARNING" "Failed to enable PHP module"
    
    # Restart Apache
    systemctl restart apache2 || log_message "WARNING" "Failed to restart Apache"
    
    log_message "INFO" "PHP installation and configuration completed"
}

# Function to install MySQL
install_mysql() {
    log_message "INFO" "Checking MySQL installation..."
    
    # Update package lists first
    update_packages
    
    if command_exists mysql; then
        local mysql_version
        mysql_version=$(mysql -V | grep -oP '\d+\.\d+\.\d+' | head -1)
        log_message "INFO" "MySQL/MariaDB version $mysql_version is installed"
    else
        log_message "INFO" "Installing MySQL..."
        apt-get install -y mysql-server || handle_error "Failed to install MySQL"
        systemctl enable mysql || handle_error "Failed to enable MySQL"
        systemctl start mysql || handle_error "Failed to start MySQL"
        log_message "INFO" "MySQL installed successfully"
    fi
    
    # Verify MySQL service
    if ! systemctl is-active --quiet mysql; then
        handle_error "MySQL service is not running"
    fi
}

# Function to install Node.js
install_nodejs() {
    log_message "INFO" "Checking Node.js installation..."
    
    # Update package lists first
    update_packages
    
    if command_exists node; then
        local node_version
        node_version=$(node -v | cut -d'v' -f2 | cut -d'.' -f1)
        if [[ "$node_version" -ge "$NODE_VERSION" ]]; then
            log_message "INFO" "Node.js version $(node -v) is installed"
            return
        fi
    fi
    
    log_message "INFO" "Installing Node.js $NODE_VERSION..."
    check_internet
    curl -fsSL https://deb.nodesource.com/setup_${NODE_VERSION}.x | bash - || handle_error "Failed to setup Node.js repository"
    apt-get install -y nodejs || handle_error "Failed to install Node.js"
    
    # Verify npm installation
    if ! command_exists npm; then
        log_message "INFO" "Installing npm..."
        apt-get install -y npm || handle_error "Failed to install npm"
    fi
    
    log_message "INFO" "Node.js and npm installed successfully"
}

# Function to install Apache
install_apache() {
    log_message "INFO" "Checking Apache installation..."
    
    # Update package lists first
    update_packages
    
    if command_exists apache2; then
        log_message "INFO" "Apache is installed"
    else
        log_message "INFO" "Installing Apache..."
        apt-get install -y apache2 || handle_error "Failed to install Apache"
        systemctl enable apache2 || handle_error "Failed to enable Apache"
        systemctl start apache2 || handle_error "Failed to start Apache"
        log_message "INFO" "Apache installed successfully"
    fi
    
    # Verify Apache service
    if ! systemctl is-active --quiet apache2; then
        handle_error "Apache service is not running"
    fi
}

# Function to install Composer
install_composer() {
    log_message "INFO" "Checking Composer installation..."
    
    if command_exists composer; then
        log_message "INFO" "Composer is already installed"
        return
    fi
    
    log_message "INFO" "Installing Composer..."
    check_internet
    local composer_setup="/tmp/composer-setup.php"
    
    # Set environment variables for Composer
    export COMPOSER_ALLOW_SUPERUSER=1
    export COMPOSER_HOME=/root/.config/composer
    export COMPOSER_NO_INTERACTION=1
    
    php -r "copy('https://getcomposer.org/installer', '$composer_setup');" || handle_error "Failed to download Composer installer"
    php "$composer_setup" --install-dir=/usr/local/bin --filename=composer || handle_error "Failed to install Composer"
    rm -f "$composer_setup"
    
    # Configure Composer to work with root user
    mkdir -p /root/.config/composer
    cat > /root/.config/composer/config.json << EOF
{
    "config": {
        "allow-plugins": {
            "php-http/discovery": true,
            "symfony/flex": true,
            "symfony/runtime": true
        },
        "sort-packages": true,
        "optimize-autoloader": true,
        "preferred-install": "dist",
        "no-interaction": true
    }
}
EOF
    
    command_exists composer || handle_error "Composer installation verification failed"
    log_message "INFO" "Composer installed successfully"
}

# Function to setup SSL with Let's Encrypt
setup_ssl() {
    local domain="$1"
    local max_attempts=3
    local attempt=1
    local success=false
    
    log_message "INFO" "Setting up SSL for $domain..."
    
    # Check if domain is accessible
    log_message "INFO" "Checking if domain $domain is accessible..."
    if ! host "$domain" >/dev/null 2>&1; then
        log_message "WARNING" "Domain $domain is not accessible. Please make sure DNS is properly configured."
        echo -e "\n${YELLOW}SSL setup skipped. Please ensure:${NC}"
        echo -e "1. DNS records for $domain are properly configured"
        echo -e "2. Domain is pointing to this server's IP address"
        echo -e "3. Port 80 is accessible from the internet"
        echo -e "\n${YELLOW}You can run SSL setup later using:${NC}"
        echo -e "${GREEN}sudo certbot --apache -d $domain -d www.$domain${NC}"
        return 1
    fi
    
    # Check if certbot is installed
    if ! command_exists certbot; then
        log_message "INFO" "Installing Certbot..."
        apt-get install -y certbot python3-certbot-apache || {
            log_message "ERROR" "Failed to install Certbot"
            return 1
        }
    fi
    
    # Check if Apache is running
    if ! systemctl is-active --quiet apache2; then
        log_message "ERROR" "Apache is not running. Please start Apache first."
        return 1
    }
    
    # Try to setup SSL with multiple attempts
    while [[ $attempt -le $max_attempts ]] && [[ $success == false ]]; do
        log_message "INFO" "Attempt $attempt of $max_attempts to setup SSL..."
        
        # Stop Apache temporarily to free port 80
        systemctl stop apache2 || {
            log_message "WARNING" "Failed to stop Apache, continuing anyway..."
        }
        
        # Run certbot
        if certbot --apache -d "$domain" -d "www.$domain" --non-interactive --agree-tos --email "admin@$domain" --force-renewal; then
            success=true
            log_message "INFO" "SSL setup completed successfully"
        else
            log_message "WARNING" "Attempt $attempt failed to setup SSL"
            attempt=$((attempt + 1))
            sleep 5
        fi
        
        # Start Apache again
        systemctl start apache2 || {
            log_message "WARNING" "Failed to start Apache"
        }
    done
    
    if [[ $success == true ]]; then
        # Verify SSL installation
        if curl -s "https://$domain" >/dev/null 2>&1; then
            log_message "INFO" "SSL verification successful"
            echo -e "\n${GREEN}SSL setup completed successfully!${NC}"
            echo -e "You can access your site securely at: ${UNDERLINE}https://$domain${NC}"
        else
            log_message "WARNING" "SSL verification failed"
            echo -e "\n${YELLOW}SSL setup completed but verification failed.${NC}"
            echo -e "Please check your SSL configuration manually."
        fi
    else
        log_message "ERROR" "Failed to setup SSL after $max_attempts attempts"
        echo -e "\n${RED}SSL setup failed.${NC}"
        echo -e "${YELLOW}Possible reasons:${NC}"
        echo -e "1. Domain DNS is not properly configured"
        echo -e "2. Port 80 is blocked by firewall"
        echo -e "3. Let's Encrypt rate limit exceeded"
        echo -e "\n${YELLOW}You can try setting up SSL manually using:${NC}"
        echo -e "${GREEN}sudo certbot --apache -d $domain -d www.$domain${NC}"
        echo -e "\n${YELLOW}Or check the logs:${NC}"
        echo -e "${GREEN}sudo certbot certificates${NC}"
        echo -e "${GREEN}sudo certbot --apache -d $domain -d www.$domain --dry-run${NC}"
        return 1
    fi
}

# Function to validate domain
validate_domain() {
    local domain="$1"
    if [[ "$domain" =~ ^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$ ]]; then
        return 0
    fi
    handle_error "Invalid domain format: $domain. Must be like example.com"
}

# Function to get domain input
get_domain() {
    local domain
    while true; do
        read -p "Please enter the domain for Hesabix installation (e.g., example.com): " domain
        if validate_domain "$domain"; then
            echo "$domain"
            break
        fi
    done
}

# Function to setup domain
setup_domain() {
    local domain="$1"
    local domain_path="/var/www/html/$domain/public_html"
    local config_file="/etc/apache2/sites-available/$domain.conf"
    local sessions_path="/var/www/html/$domain/hesabixCore/var/sessions"
    
    log_message "INFO" "Setting up domain: $domain"
    
    # Remove existing virtual host if it exists
    if [[ -f "$config_file" ]]; then
        log_message "INFO" "Removing existing virtual host configuration..."
        a2dissite "$domain.conf" >/dev/null 2>&1
        rm -f "$config_file"
    fi
    
    # Check for existing domain directory
    if [[ -d "/var/www/html/$domain" ]]; then
        echo -e "\n${YELLOW}Warning: The directory /var/www/html/$domain already exists.${NC}"
        echo -e "${YELLOW}All its contents will be deleted.${NC}"
        read -p "Do you want to continue? (y/n) [n]: " response
        
        if [[ ! "$response" =~ ^[Yy]$ ]]; then
            log_message "ERROR" "User chose not to delete existing directory"
            handle_error "Installation aborted by user"
        fi
        
        log_message "INFO" "Removing existing domain directory..."
        rm -rf "/var/www/html/$domain"
    fi
    
    # Create domain directory
    mkdir -p "$domain_path" || handle_error "Failed to create domain directory"
    chown -R "$apache_user:$apache_user" "$domain_path"
    chmod -R 755 "$domain_path"
    
    # Create sessions directory
    log_message "INFO" "Creating sessions directory..."
    mkdir -p "$sessions_path" || handle_error "Failed to create sessions directory"
    chown -R "$apache_user:$apache_user" "$sessions_path"
    chmod -R 777 "$sessions_path"
    
    # Enable Apache rewrite module
    a2enmod rewrite || handle_error "Failed to enable Apache rewrite module"
    
    # Create Apache virtual host
    cat > "$config_file" << EOF
<VirtualHost *:80>
    ServerName $domain
    ServerAlias www.$domain
    DocumentRoot $domain_path
    
    <Directory $domain_path>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/$domain-error.log
    CustomLog \${APACHE_LOG_DIR}/$domain-access.log combined
</VirtualHost>
EOF

    apache2ctl configtest || handle_error "Apache configuration test failed"
    a2ensite "$domain.conf" || handle_error "Failed to enable Apache site"
    systemctl restart apache2 || handle_error "Failed to restart Apache"
    
    log_message "INFO" "Domain setup completed"
}

# Function to setup database
setup_database() {
    local domain="$1"
    local base_db_name="hesabix_$(echo "$domain" | tr '.-' '_')"
    local db_name="$base_db_name"
    local db_user="hesabix_user"
    local db_password
    db_password=$(openssl rand -base64 12)
    local domain_path="/var/www/html/$domain"
    local counter=1
    
    log_message "INFO" "Setting up database..."
    
    # Verify MySQL is running
    if ! systemctl is-active --quiet mysql; then
        handle_error "MySQL service is not running"
    fi
    
    # Check if database exists and create new name if needed
    while mysql -e "SHOW DATABASES LIKE '$db_name'" | grep -q "$db_name"; do
        db_name="${base_db_name}_${counter}"
        counter=$((counter + 1))
    done
    
    if [[ "$db_name" != "$base_db_name" ]]; then
        log_message "WARNING" "Database $base_db_name already exists, using $db_name instead"
    fi
    
    # Drop existing user if exists
    mysql -e "DROP USER IF EXISTS '$db_user'@'localhost';" || \
        log_message "WARNING" "Failed to drop existing user"
    
    # Create database
    mysql -e "CREATE DATABASE IF NOT EXISTS \`$db_name\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" || \
        handle_error "Failed to create database"
    
    # Create user with proper permissions
    mysql -e "CREATE USER '$db_user'@'localhost' IDENTIFIED BY '$db_password';" || \
        handle_error "Failed to create database user"
    
    # Grant all privileges and make sure they are applied
    mysql -e "GRANT ALL PRIVILEGES ON \`$db_name\`.* TO '$db_user'@'localhost';" || \
        handle_error "Failed to grant database privileges"
    mysql -e "FLUSH PRIVILEGES;" || handle_error "Failed to flush privileges"
    
    # Verify user can connect
    if ! mysql -u"$db_user" -p"$db_password" -e "SELECT 1;" >/dev/null 2>&1; then
        handle_error "Failed to verify database user access"
    fi
    
    # Import default database structure
    log_message "INFO" "Importing default database structure..."
    if [[ -f "$domain_path/hesabixBackup/databasefiles/hesabix-db-default.sql" ]]; then
        mysql -u"$db_user" -p"$db_password" "$db_name" < "$domain_path/hesabixBackup/databasefiles/hesabix-db-default.sql" || \
            log_message "WARNING" "Failed to import default database structure"
    else
        log_message "WARNING" "Default database structure file not found"
    fi
    
    # Update environment configuration
    local env_file="$domain_path/hesabixCore/.env.local.php"
    cat > "$env_file" << EOF
<?php

// This file was generated by running "composer dump-env dev"

return array (
  'APP_ENV' => 'prod',
  'SYMFONY_DOTENV_PATH' => './.env',
  'APP_SECRET' => '$(openssl rand -hex 16)',
  'DATABASE_URL' => 'mysql://$db_user:$db_password@127.0.0.1:3306/$db_name?serverVersion=8.0.32&charset=utf8mb4',
  'MESSENGER_TRANSPORT_DSN' => 'doctrine://default?auto_setup=0',
  'MAILER_DSN' => 'null://null',
  'CORS_ALLOW_ORIGIN' => '*',
  'LOCK_DSN' => 'flock',
);
EOF
    
    # Set proper permissions
    chown "$apache_user:$apache_user" "$env_file"
    chmod 644 "$env_file"
    
    # Update database schema
    log_message "INFO" "Updating database schema..."
    cd "$domain_path/hesabixCore" || handle_error "Failed to change to hesabixCore directory"
    
    # Set environment variables for doctrine command
    export DATABASE_URL="mysql://$db_user:$db_password@127.0.0.1:3306/$db_name?serverVersion=8.0.32&charset=utf8mb4"
    
    php bin/console doctrine:schema:update --force || \
        log_message "WARNING" "Failed to update database schema"
    
    log_message "INFO" "Database setup completed"
}

# Function to install software
install_software() {
    local domain="$1"
    local domain_path="/var/www/html/$domain"
    local current_version
    current_version=$(get_php_version)
    
    log_message "INFO" "Installing software for domain: $domain"
    
    # Ensure domain directory exists
    if [[ ! -d "$domain_path" ]]; then
        handle_error "Domain directory $domain_path does not exist"
    fi
    
    cd "$domain_path" || handle_error "Failed to change to domain directory: $domain_path"
    
    # Check internet connectivity
    check_internet
    
    # Check required extensions before proceeding
    if ! check_required_extensions; then
        echo -e "\nWarning: Some required extensions are missing or could not be installed."
        echo -e "Do you want to continue with the installation anyway?"
        echo -e "Note: This might cause issues with some features."
        read -p "Continue? (y/n) [n]: " response
        
        if [[ ! "$response" =~ ^[Yy]$ ]]; then
            log_message "ERROR" "User chose not to continue with missing extensions"
            handle_error "Installation aborted by user"
        fi
        
        # If user continues, add --ignore-platform-req flags
        local composer_flags="--ignore-platform-req=ext-simplexml --ignore-platform-req=ext-xmlwriter --ignore-platform-req=ext-zip"
    else
        local composer_flags=""
    fi
    
    # Initialize git repository if not already initialized
    if [[ ! -d ".git" ]]; then
        log_message "INFO" "Initializing git repository in $domain_path..."
        git init || handle_error "Failed to initialize git repository"
    fi
    
    # Check if remote origin exists
    if ! git remote get-url origin >/dev/null 2>&1; then
        # Add remote repository if it doesn't exist
        git remote add origin https://github.com/morrning/hesabixCore.git || \
            handle_error "Failed to add remote repository"
    else
        # Update remote URL if it exists
        git remote set-url origin https://github.com/morrning/hesabixCore.git || \
            handle_error "Failed to update remote repository"
    fi
    
    # Fetch and checkout the repository
    log_message "INFO" "Fetching repository contents..."
    git fetch origin || handle_error "Failed to fetch repository"
    
    # Check if master branch exists
    if git show-ref --verify --quiet refs/heads/master; then
        # Switch to master branch
        git checkout master || handle_error "Failed to checkout master branch"
        # Pull latest changes
        git pull origin master || handle_error "Failed to pull latest changes"
    else
        # Create and checkout master branch
        git checkout -b master origin/master || handle_error "Failed to checkout repository"
    fi
    
    # Verify repository path
    if [[ ! -d "$domain_path" ]]; then
        handle_error "Repository directory $domain_path was not created"
    fi
    
    cd "$domain_path" || handle_error "Failed to change to domain directory: $domain_path"
    
    # Verify composer.json exists in hesabixCore directory
    if [[ ! -f "hesabixCore/composer.json" ]]; then
        handle_error "composer.json file not found in $domain_path/hesabixCore"
    fi
    
    # Configure Composer
    export COMPOSER_ALLOW_SUPERUSER=1
    export COMPOSER_HOME=/root/.config/composer
    export COMPOSER_NO_INTERACTION=1
    
    # Install dependencies
    log_message "INFO" "Installing Composer dependencies..."
    cd "hesabixCore" || handle_error "Failed to change to hesabixCore directory"
    
    # Try to install dependencies
    if ! timeout "$COMPOSER_TIMEOUT" composer install --no-interaction --optimize-autoloader $composer_flags; then
        log_message "ERROR" "Failed to install Composer dependencies"
        handle_error "Failed to install Composer dependencies"
    fi
    
    # Generate environment
    log_message "INFO" "Generating environment file..."
    timeout 300 composer dump-env prod --no-interaction || \
        handle_error "Failed to generate environment file"
    
    # Verify environment file
    if [[ ! -f ".env.local.php" ]]; then
        handle_error "Environment file (.env.local.php) was not generated"
    fi
    
    log_message "INFO" "Software installation completed"
}

# Function to setup web UI
setup_web_ui() {
    local domain="$1"
    local domain_path="/var/www/html/$domain"
    local webui_path="$domain_path/webUI"
    
    log_message "INFO" "Setting up web UI..."
    
    # Check if webUI directory exists
    if [[ ! -d "$webui_path" ]]; then
        handle_error "Web UI directory ($webui_path) does not exist"
    fi
    
    cd "$webui_path" || handle_error "Failed to change to webUI directory"
    
    # Install dependencies
    log_message "INFO" "Installing web UI dependencies..."
    timeout "$NPM_TIMEOUT" npm install || handle_error "Failed to install web UI dependencies"
    
    # Set proper permissions for webUI directory
    log_message "INFO" "Setting proper permissions for webUI directory..."
    chown -R "$apache_user:$apache_user" "$webui_path"
    chmod -R 755 "$webui_path"
    
    # Set execute permissions for node_modules/.bin
    if [[ -d "$webui_path/node_modules/.bin" ]]; then
        chmod -R +x "$webui_path/node_modules/.bin"
    fi
    
    # Set proper permissions for node_modules
    log_message "INFO" "Setting proper permissions for node_modules directory..."
    chmod -R 755 "$webui_path/node_modules"
    find "$webui_path/node_modules" -type f -exec chmod 644 {} \;
    find "$webui_path/node_modules" -type d -exec chmod 755 {} \;
    find "$webui_path/node_modules/.bin" -type f -exec chmod 755 {} \;
    
    # Build web UI
    log_message "INFO" "Building web UI..."
    timeout "$NPM_TIMEOUT" npm run build-only || handle_error "Failed to build web UI"
    
    log_message "INFO" "Web UI setup completed"
}

# Function to set Apache ownership
set_apache_ownership() {
    local domain="$1"
    local domain_path="/var/www/html/$domain"
    local webui_path="$domain_path/webUI"
    
    log_message "INFO" "Setting Apache ownership..."
    
    chown -R "$apache_user:$apache_user" "$domain_path" || \
        handle_error "Failed to set Apache ownership"
    
    # Set permissions for all directories and files
    find "$domain_path" -type d -exec chmod 755 {} \;
    find "$domain_path" -type f -exec chmod 644 {} \;
    
    # Set special permissions for webUI node_modules
    if [[ -d "$webui_path/node_modules" ]]; then
        log_message "INFO" "Setting special permissions for webUI node_modules..."
        chmod -R 755 "$webui_path/node_modules"
        find "$webui_path/node_modules" -type f -exec chmod 644 {} \;
        find "$webui_path/node_modules" -type d -exec chmod 755 {} \;
        find "$webui_path/node_modules/.bin" -type f -exec chmod 755 {} \;
    fi
    
    log_message "INFO" "Apache ownership set"
}

# Function to display GPL license
display_gpl_license() {
    echo -e "\n${BOLD}${BLUE}=================================================${NC}"
    echo -e "${BOLD}${BLUE}           GNU General Public License v3           ${NC}"
    echo -e "${BOLD}${BLUE}=================================================${NC}"
    echo -e "${YELLOW}This program is free software: you can redistribute it and/or modify${NC}"
    echo -e "${YELLOW}it under the terms of the GNU General Public License as published by${NC}"
    echo -e "${YELLOW}the Free Software Foundation, either version 3 of the License, or${NC}"
    echo -e "${YELLOW}(at your option) any later version.${NC}"
    echo -e "\n${YELLOW}This program is distributed in the hope that it will be useful,${NC}"
    echo -e "${YELLOW}but WITHOUT ANY WARRANTY; without even the implied warranty of${NC}"
    echo -e "${YELLOW}MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.${NC}"
    echo -e "${YELLOW}See the GNU General Public License for more details.${NC}"
    echo -e "${BOLD}${BLUE}=================================================${NC}\n"
    
    read -p "Do you accept the terms of the GNU General Public License v3? (y/n) [y]: " response
    if [[ ! "$response" =~ ^[Yy]$ ]] && [[ -n "$response" ]]; then
        handle_error "License terms not accepted"
    fi
}

# Function to confirm installation
confirm_installation() {
    echo -e "\n${BOLD}${BLUE}=================================================${NC}"
    echo -e "${BOLD}${BLUE}           Installation Confirmation           ${NC}"
    echo -e "${BOLD}${BLUE}=================================================${NC}"
    echo -e "${YELLOW}This script will install:${NC}"
    echo -e "• PHP and required extensions"
    echo -e "• MySQL/MariaDB"
    echo -e "• Apache"
    echo -e "• Node.js"
    echo -e "• Composer"
    echo -e "• phpMyAdmin"
    echo -e "• Hesabix Core"
    echo -e "• Hesabix Web UI"
    echo -e "\n${YELLOW}The installation will require approximately 2GB of disk space.${NC}"
    echo -e "${BOLD}${BLUE}=================================================${NC}\n"
    
    read -p "Do you want to continue with the installation? (y/n) [y]: " response
    if [[ ! "$response" =~ ^[Yy]$ ]] && [[ -n "$response" ]]; then
        handle_error "Installation cancelled by user"
    fi
}

# Function to install phpMyAdmin
install_phpmyadmin() {
    log_message "INFO" "Installing phpMyAdmin..."
    
    # Update package lists first
    update_packages
    
    # Install phpMyAdmin
    apt-get install -y phpmyadmin || handle_error "Failed to install phpMyAdmin"
    
    # Configure phpMyAdmin
    log_message "INFO" "Configuring phpMyAdmin..."
    
    # Create Apache configuration
    cat > /etc/apache2/conf-available/phpmyadmin.conf << EOF
Alias /phpmyadmin /usr/share/phpmyadmin
<Directory /usr/share/phpmyadmin>
    Options FollowSymLinks
    DirectoryIndex index.php
    AllowOverride All
    Require all granted
</Directory>
EOF
    
    # Enable configuration
    a2enconf phpmyadmin || handle_error "Failed to enable phpMyAdmin configuration"
    
    # Restart Apache
    systemctl restart apache2 || handle_error "Failed to restart Apache"
    
    log_message "INFO" "phpMyAdmin installation completed"
}

# Function to show installation summary
show_installation_summary() {
    local domain="$1"
    local domain_path="/var/www/html/$domain"
    local missing_packages=()
    local db_name="hesabix_$(echo "$domain" | tr '.-' '_')"
    local db_user="hesabix_user"
    local db_password
    local env_file="$domain_path/hesabixCore/.env.local.php"
    
    # Get database password from env file
    if [[ -f "$env_file" ]]; then
        db_password=$(php -r "include '$env_file'; echo \$env['DATABASE_URL']; echo PHP_EOL;" | grep -oP '(?<=://[^:]+:)[^@]+(?=@)')
    fi
    
    log_message "INFO" "Showing installation summary..."
    
    # Check for missing packages
    for version in $(get_installed_php_versions); do
        for pkg in php${version}-raphf php${version}-http php${version}-dom php${version}-xml php${version}-gd php${version}-curl php${version}-simplexml php${version}-xmlwriter php${version}-zip; do
            if ! dpkg -l | grep -q "^ii  $pkg "; then
                missing_packages+=("$pkg")
            fi
        done
    done
    
    echo -e "\n${BOLD}${BLUE}=================================================${NC}"
    echo -e "${BOLD}${BLUE}           Hesabix Installation Summary           ${NC}"
    echo -e "${BOLD}${BLUE}=================================================${NC}"
    echo -e "\n${YELLOW}Installation Details:${NC}"
    echo -e "Domain: $domain"
    echo -e "Installation Path: $domain_path"
    echo -e "Web Server: Apache"
    echo -e "PHP Version: $(php -v | head -n 1 | awk '{print $2}')"
    echo -e "Node.js Version: $(node -v)"
    echo -e "phpMyAdmin URL: https://$domain/phpmyadmin"
    
    echo -e "\n${YELLOW}Database Information:${NC}"
    echo -e "Database Name: $db_name"
    echo -e "Database User: $db_user"
    if [[ -n "$db_password" ]]; then
        echo -e "Database Password: $db_password"
    else
        echo -e "${RED}Database Password: Not found in .env.local.php${NC}"
        echo -e "\n${YELLOW}To get database information, you can:${NC}"
        echo -e "1. Check the file: $env_file"
        echo -e "2. Run this command to extract password:"
        echo -e "   ${GREEN}php -r \"include '$env_file'; echo \$env['DATABASE_URL']; echo PHP_EOL;\" | grep -oP '(?<=://[^:]+:)[^@]+(?=@)'${NC}"
        echo -e "3. Or check MySQL directly:"
        echo -e "   ${GREEN}mysql -u root -e \"SELECT User, Host FROM mysql.user WHERE User='$db_user';\"${NC}"
    fi
    echo -e "Database Host: localhost"
    echo -e "Database Port: 3306"
    
    if [[ ${#missing_packages[@]} -gt 0 ]]; then
        echo -e "\n${RED}Warning: The following packages were not installed:${NC}"
        printf '%s\n' "${missing_packages[@]}"
        echo -e "\n${YELLOW}Please install these packages manually using:${NC}"
        echo -e "sudo apt-get install ${missing_packages[*]}"
    fi
    
    echo -e "\n${YELLOW}Next Steps:${NC}"
    echo -e "1. Configure domain DNS to point to this server"
    echo -e "2. Access the web interface: https://$domain"
    echo -e "3. Access phpMyAdmin: https://$domain/phpmyadmin"
    echo -e "4. Register the first user (system administrator)"
    
    echo -e "\n${YELLOW}Support:${NC}"
    echo -e "• Developer: Babak Alizadeh (alizadeh.babak)"
    echo -e "• License: GNU GPL v3"
    echo -e "• Website: ${UNDERLINE}https://hesabix.ir${NC}"
    echo -e "• Support us: ${UNDERLINE}https://hesabix.ir/page/sponsors${NC} ❤"
    
    echo -e "\n${GREEN}Installation completed successfully!${NC}"
    echo -e "${BOLD}${BLUE}=================================================${NC}"
    
    # Restart Apache at the end of installation
    log_message "INFO" "Restarting Apache..."
    systemctl restart apache2 || log_message "WARNING" "Failed to restart Apache"
}

# Function to display telemetry consent
display_telemetry_consent() {
    echo -e "\n${RED}================================================="
    echo -e "${RED}           Anonymous Data Collection           "
    echo -e "${RED}================================================="
    echo -e "${BLUE}To improve Hesabix, we would like to collect anonymous data:"
    echo -e "${BLUE}• System information (OS, PHP, MySQL versions)"
    echo -e "${BLUE}• Installation path and domain"
    echo -e "${BLUE}• Installation date"
    
    read -p "Do you agree? (y/n) [n]: " response
    [[ "$response" =~ ^[Yy]$ ]] && SEND_TELEMETRY=true
}

# Function to handle rollback
rollback() {
    local domain="$1"
    local domain_path="/var/www/html/$domain"
    
    log_message "ERROR" "Starting rollback process..."
    echo -e "\n${RED}Starting rollback process...${NC}"
    
    # Log rollback steps in main log file
    {
        echo "----------------------------------------"
        echo "ROLLBACK STARTED AT: $(date)"
        echo "Domain: $domain"
        echo "Domain path: $domain_path"
        echo "----------------------------------------"
        echo "Note: System packages installed via apt are not removed"
        echo "----------------------------------------"
    } >> "$LOG_FILE"
    
    # Remove domain directory if it exists
    if [[ -d "$domain_path" ]]; then
        log_message "INFO" "Removing domain directory..."
        rm -rf "$domain_path"
        echo "Removed domain directory: $domain_path" >> "$LOG_FILE"
    fi
    
    # Remove Apache virtual host
    local config_file="/etc/apache2/sites-available/$domain.conf"
    if [[ -f "$config_file" ]]; then
        log_message "INFO" "Removing Apache virtual host..."
        a2dissite "$domain.conf" >/dev/null 2>&1
        rm -f "$config_file"
        echo "Removed Apache virtual host: $config_file" >> "$LOG_FILE"
    fi
    
    # Remove SSL certificates if they exist
    if certbot certificates | grep -q "$domain"; then
        log_message "INFO" "Removing SSL certificates..."
        certbot delete --cert-name "$domain" --non-interactive
        echo "Removed SSL certificates for: $domain" >> "$LOG_FILE"
    fi
    
    # Remove database if it exists
    local db_name="hesabix_$(echo "$domain" | tr '.-' '_')"
    if mysql -e "SHOW DATABASES LIKE '$db_name'" | grep -q "$db_name"; then
        log_message "INFO" "Removing database..."
        mysql -e "DROP DATABASE IF EXISTS \`$db_name\`;"
        echo "Removed database: $db_name" >> "$LOG_FILE"
    fi
    
    # Remove database user if it exists
    local db_user="hesabix_user"
    if mysql -e "SELECT User FROM mysql.user WHERE User='$db_user'" | grep -q "$db_user"; then
        log_message "INFO" "Removing database user..."
        mysql -e "DROP USER IF EXISTS '$db_user'@'localhost';"
        echo "Removed database user: $db_user" >> "$LOG_FILE"
    fi
    
    # Restart Apache
    systemctl restart apache2
    
    # Log completion in main log file
    {
        echo "----------------------------------------"
        echo "ROLLBACK COMPLETED AT: $(date)"
        echo "----------------------------------------"
    } >> "$LOG_FILE"
    
    log_message "INFO" "Rollback completed"
    echo -e "\n${YELLOW}Rollback completed.${NC}"
    echo -e "${YELLOW}Check the installation log file for details: ${UNDERLINE}$LOG_FILE${NC}"
    echo -e "\n${YELLOW}Note: System packages installed via apt were not removed.${NC}"
    echo -e "${YELLOW}You can safely run the installation script again.${NC}"
}

# Main execution
main() {
    # Show header first
    print_header
    
    # Wait for user to see the header
    sleep 2
    
    # Then start logging
    init_logging
    
    # Get domain first for rollback purposes
    domain=$(get_domain)
    
    # Create a trap for rollback
    trap 'rollback "$domain"; exit 1' ERR
    
    check_system_requirements
    install_tools
    display_gpl_license
    confirm_installation
    display_telemetry_consent
    update_packages
    install_php
    install_mysql
    install_nodejs
    install_apache
    install_composer
    install_phpmyadmin
    
    setup_domain "$domain"
    install_software "$domain"
    setup_database "$domain"
    setup_web_ui "$domain"
    set_apache_ownership "$domain"
    setup_ssl "$domain"
    show_installation_summary "$domain"
    
    # Remove the trap at the end of successful installation
    trap - ERR
}

# Execute main with error handling
main || handle_error "Installation failed"