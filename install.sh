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
declare -r MIN_PHP_VERSION="8.2"
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
    local missing_extensions=()
    local installed_versions
    installed_versions=($(get_installed_php_versions))
    
    for version in "${installed_versions[@]}"; do
        log_message "INFO" "Checking required extensions for PHP $version..."
        for ext in raphf http dom xml gd curl simplexml xmlwriter zip; do
            if ! php"$version" -m | grep -q "$ext"; then
                missing_extensions+=("php${version}-${ext}")
                log_message "WARNING" "Extension $ext is missing for PHP $version"
            fi
        done
    done
    
    if [[ ${#missing_extensions[@]} -gt 0 ]]; then
        echo -e "\nWarning: The following extensions are missing:"
        printf '%s\n' "${missing_extensions[@]}"
        echo -e "\nDo you want to install them now?"
        read -p "Install missing extensions? (y/n) [y]: " response
        
        if [[ "$response" =~ ^[Yy]$ ]] || [[ -z "$response" ]]; then
            log_message "INFO" "Installing missing extensions..."
            
            # First try to install from default repositories
            if ! apt-get install -y "${missing_extensions[@]}"; then
                log_message "WARNING" "Failed to install from default repositories. Trying custom repositories..."
                
                # Add custom repository
                LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php || log_message "WARNING" "PPA already exists"
                update_packages
                
                # Try installing again
                apt-get install -y "${missing_extensions[@]}" || {
                    log_message "ERROR" "Failed to install missing extensions even with custom repository"
                    return 1
                }
            fi
            
            # Enable extensions after installation
            for version in "${installed_versions[@]}"; do
                for ext in raphf http dom xml gd curl simplexml xmlwriter zip; do
                    if ! php"$version" -m | grep -q "$ext"; then
                        log_message "INFO" "Enabling $ext for PHP $version..."
                        phpenmod -v "$version" "$ext" || log_message "WARNING" "Failed to enable $ext for CLI in version $version"
                        phpenmod -v "$version" -s apache2 "$ext" || log_message "WARNING" "Failed to enable $ext for Apache in version $version"
                    fi
                done
            done
            
            # Verify extensions are now installed
            local still_missing=()
            for version in "${installed_versions[@]}"; do
                for ext in raphf http dom xml gd curl simplexml xmlwriter zip; do
                    if ! php"$version" -m | grep -q "$ext"; then
                        still_missing+=("php${version}-${ext}")
                    fi
                done
            done
            
            if [[ ${#still_missing[@]} -gt 0 ]]; then
                echo -e "\nWarning: The following extensions are still missing after installation:"
                printf '%s\n' "${still_missing[@]}"
                return 1
            fi
            
            # Restart Apache
            systemctl restart apache2 || log_message "WARNING" "Failed to restart Apache"
        else
            log_message "WARNING" "User chose not to install missing extensions"
            return 1
        fi
    fi
    
    return 0
}

# Function to verify CLI extensions
verify_cli_extensions() {
    local version="$1"
    log_message "INFO" "Verifying CLI extensions for PHP $version..."
    
    # Get the list of enabled extensions from CLI
    local enabled_extensions
    enabled_extensions=$(php"$version" -m)
    
    # Check each required extension
    for ext in raphf http dom xml gd curl simplexml xmlwriter zip intl mbstring mysql bcmath; do
        if ! echo "$enabled_extensions" | grep -q "^$ext$"; then
            log_message "ERROR" "Extension $ext is not enabled in CLI for PHP $version"
            log_message "INFO" "Attempting to enable $ext for CLI..."
            
            # Try to enable the extension
            phpenmod -v "$version" "$ext" || {
                log_message "ERROR" "Failed to enable $ext for CLI in version $version"
                return 1
            }
            
            # Verify again after enabling
            enabled_extensions=$(php"$version" -m)
            if ! echo "$enabled_extensions" | grep -q "^$ext$"; then
                log_message "ERROR" "Extension $ext is still not enabled in CLI for PHP $version"
                return 1
            fi
        fi
    done
    
    return 0
}

# Function to install PHP and extensions
install_php() {
    log_message "INFO" "Checking PHP installation..."
    
    # Check if php-cli is installed
    if ! command_exists php; then
        log_message "INFO" "Installing php-cli..."
        apt-get install -y php-cli || handle_error "Failed to install php-cli"
    fi
    
    # Get current PHP version
    local current_version
    current_version=$(get_php_version)
    log_message "DEBUG" "Current PHP version: $current_version"
    
    # Check if PHP version meets minimum requirement
    if [[ "$(echo "$current_version >= $MIN_PHP_VERSION" | bc -l)" -ne 1 ]]; then
        log_message "INFO" "Installing PHP $MIN_PHP_VERSION or higher..."
        
        # First try to install from default Ubuntu repositories
        log_message "INFO" "Attempting to install PHP from default Ubuntu repositories..."
        apt-get install -y software-properties-common
        
        local php_packages=(
            "php${MIN_PHP_VERSION}"
            "php${MIN_PHP_VERSION}-cli"
            "libapache2-mod-php${MIN_PHP_VERSION}"
            "php${MIN_PHP_VERSION}-intl"
            "php${MIN_PHP_VERSION}-mbstring"
            "php${MIN_PHP_VERSION}-zip"
            "php${MIN_PHP_VERSION}-gd"
            "php${MIN_PHP_VERSION}-mysql"
            "php${MIN_PHP_VERSION}-curl"
            "php${MIN_PHP_VERSION}-xml"
            "php${MIN_PHP_VERSION}-bcmath"
            "php${MIN_PHP_VERSION}-raphf"
            "php${MIN_PHP_VERSION}-http"
            "php${MIN_PHP_VERSION}-dom"
            "php${MIN_PHP_VERSION}-simplexml"
            "php${MIN_PHP_VERSION}-xmlwriter"
        )
        
        # Try installing from default repositories
        if ! apt-get install -y "${php_packages[@]}"; then
            log_message "WARNING" "Failed to install PHP from default repositories. Trying custom repositories..."
            
            # Add custom repository if default installation fails
            LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php || log_message "WARNING" "PPA already exists"
            update_packages
            
            # Try installing again with custom repository
        apt-get install -y "${php_packages[@]}" || handle_error "Failed to install PHP packages"
        fi
    fi
    
    # Get all installed PHP versions
    local installed_versions
    installed_versions=($(get_installed_php_versions))
    log_message "INFO" "Found installed PHP versions: ${installed_versions[*]}"
    
    # Install and configure extensions for all PHP versions
    for version in "${installed_versions[@]}"; do
        log_message "INFO" "Configuring PHP $version..."
        
        # Install all required extensions for this version
        local required_extensions=(
            "php${version}-raphf"
            "php${version}-http"
            "php${version}-dom"
            "php${version}-xml"
            "php${version}-gd"
            "php${version}-curl"
            "php${version}-simplexml"
            "php${version}-xmlwriter"
            "php${version}-zip"
            "php${version}-intl"
            "php${version}-mbstring"
            "php${version}-mysql"
            "php${version}-bcmath"
        )
        
        # Install extensions
        log_message "INFO" "Installing required extensions for PHP $version..."
        apt-get install -y "${required_extensions[@]}" || {
            log_message "WARNING" "Failed to install some extensions for PHP $version. Trying custom repository..."
            
            # Add custom repository if needed
            LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php || log_message "WARNING" "PPA already exists"
            update_packages
            
            # Try installing again
            apt-get install -y "${required_extensions[@]}" || handle_error "Failed to install PHP extensions for version $version"
        }
        
        # Enable all extensions
        for ext in raphf http dom xml gd curl simplexml xmlwriter zip intl mbstring mysql bcmath; do
            log_message "INFO" "Enabling $ext for PHP $version..."
            phpenmod -v "$version" "$ext" || log_message "WARNING" "Failed to enable $ext for CLI in version $version"
            phpenmod -v "$version" -s apache2 "$ext" || log_message "WARNING" "Failed to enable $ext for Apache in version $version"
    done
    
    # Create PHP configuration for both CLI and Apache
    for sapi in cli apache2; do
            if [[ -d "/etc/php/${version}/${sapi}/conf.d" ]]; then
                cat > "/etc/php/${version}/${sapi}/conf.d/99-hesabix.ini" << EOF
extension=raphf.so
extension=http.so
extension=dom.so
extension=xml.so
extension=gd.so
extension=curl.so
extension=simplexml.so
extension=xmlwriter.so
extension=zip.so
extension=intl.so
extension=mbstring.so
extension=mysql.so
extension=bcmath.so
EOF
            fi
        done
        
        # Enable PHP module for Apache
        if [[ -d "/etc/php/${version}/apache2" ]]; then
            a2enmod "php${version}" || handle_error "Failed to enable PHP module for version $version"
        fi
        
        # Verify extensions are loaded
        log_message "INFO" "Verifying extensions for PHP $version..."
        for ext in raphf http dom xml gd curl simplexml xmlwriter zip intl mbstring mysql bcmath; do
            if ! php"$version" -m | grep -q "$ext"; then
                log_message "ERROR" "Extension $ext is not loaded for PHP $version"
                handle_error "Failed to load extension $ext for PHP $version"
            fi
        done
        
        # Verify CLI extensions specifically
        if ! verify_cli_extensions "$version"; then
            log_message "ERROR" "Failed to verify CLI extensions for PHP $version"
            handle_error "CLI extensions verification failed for PHP $version"
        fi
    done
    
    # Restart Apache if any PHP version was configured
    if [[ ${#installed_versions[@]} -gt 0 ]]; then
    systemctl restart apache2 || handle_error "Failed to restart Apache"
    fi
    
    log_message "INFO" "PHP installation and configuration completed for all versions"
}

# Function to install MySQL
install_mysql() {
    log_message "INFO" "Checking MySQL installation..."
    
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
    
    log_message "INFO" "Setting up SSL for $domain..."
    
    check_internet
    if ! command_exists certbot; then
        log_message "INFO" "Installing Certbot..."
        apt-get install -y certbot python3-certbot-apache || handle_error "Failed to install Certbot"
    fi
    
    certbot --apache -d "$domain" -d "www.$domain" --non-interactive --agree-tos --email "admin@$domain" || {
        log_message "WARNING" "Failed to setup SSL automatically. Please configure SSL manually."
    }
    
    log_message "INFO" "SSL setup completed"
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
    
    log_message "INFO" "Setting up domain: $domain"
    
    # Create domain directory
    mkdir -p "$domain_path" || handle_error "Failed to create domain directory"
    chown -R "$apache_user:$apache_user" "$domain_path"
    chmod -R 755 "$domain_path"
    
    # Enable Apache rewrite module
    a2enmod rewrite || handle_error "Failed to enable Apache rewrite module"
    
    # Create Apache virtual host
    local config_file="/etc/apache2/sites-available/$domain.conf"
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
    local db_name="hesabix_$(echo "$domain" | tr '.-' '_')"
    local db_user="hesabix_user"
    local db_password
    db_password=$(openssl rand -base64 12)
    local domain_path="/var/www/html/$domain"
    
    log_message "INFO" "Setting up database: $db_name"
    
    # Verify MySQL is running
    if ! systemctl is-active --quiet mysql; then
        handle_error "MySQL service is not running"
    fi
    
    # Create database
    mysql -e "CREATE DATABASE IF NOT EXISTS \`$db_name\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" || \
        handle_error "Failed to create database"
    
    # Create user
    mysql -e "CREATE USER IF NOT EXISTS '$db_user'@'localhost' IDENTIFIED BY '$db_password';" || \
        handle_error "Failed to create database user"
    
    # Grant privileges
    mysql -e "GRANT ALL PRIVILEGES ON \`$db_name\`.* TO '$db_user'@'localhost';" || \
        handle_error "Failed to grant database privileges"
    mysql -e "FLUSH PRIVILEGES;" || handle_error "Failed to flush privileges"
    
    # Update environment configuration
    local env_file="$domain_path/hesabixCore/.env.local.php"
    cat > "$env_file" << EOF
<?php
return [
    'APP_ENV' => 'prod',
    'APP_SECRET' => '$(openssl rand -hex 16)',
    'DATABASE_URL' => 'mysql://$db_user:$db_password@127.0.0.1:3306/$db_name?serverVersion=8.0.32&charset=utf8mb4',
    'MESSENGER_TRANSPORT_DSN' => 'doctrine://default?auto_setup=0',
    'MAILER_DSN' => 'null://null',
    'CORS_ALLOW_ORIGIN' => '*',
    'LOCK_DSN' => 'flock',
];
EOF
    
    # Set proper permissions
    chown "$apache_user:$apache_user" "$env_file"
    chmod 644 "$env_file"
    
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
    
    # Initialize git repository
    log_message "INFO" "Initializing git repository in $domain_path..."
    git init || handle_error "Failed to initialize git repository"
    
    # Add remote repository
    git remote add origin https://github.com/morrning/hesabixCore.git || \
        handle_error "Failed to add remote repository"
    
    # Fetch and checkout the repository
    log_message "INFO" "Fetching repository contents..."
    git fetch origin || handle_error "Failed to fetch repository"
    git checkout -b main origin/main || handle_error "Failed to checkout repository"
    
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
    
    # Build web UI
    log_message "INFO" "Building web UI..."
    timeout "$NPM_TIMEOUT" npm run build-only || handle_error "Failed to build web UI"
    
    log_message "INFO" "Web UI setup completed"
}

# Function to set Apache ownership
set_apache_ownership() {
    local domain="$1"
    local domain_path="/var/www/html/$domain"
    
    log_message "INFO" "Setting Apache ownership..."
    
    chown -R "$apache_user:$apache_user" "$domain_path" || \
        handle_error "Failed to set Apache ownership"
    
    find "$domain_path" -type d -exec chmod 755 {} \;
    find "$domain_path" -type f -exec chmod 644 {} \;
    
    log_message "INFO" "Apache ownership set"
}

# Function to show installation summary
show_installation_summary() {
    local domain="$1"
    local domain_path="/var/www/html/$domain"
    
    log_message "INFO" "Showing installation summary..."
    
    echo -e "\n${BOLD}${BLUE}=================================================${NC}"
    echo -e "${BOLD}${BLUE}           Hesabix Installation Summary           ${NC}"
    echo -e "${BOLD}${BLUE}=================================================${NC}"
    echo -e "\n${YELLOW}Installation Details:${NC}"
    echo -e "Domain: $domain"
    echo -e "Installation Path: $domain_path"
    echo -e "Web Server: Apache"
    echo -e "PHP Version: $(php -v | head -n 1 | awk '{print $2}')"
    echo -e "Node.js Version: $(node -v)"
    
    echo -e "\n${YELLOW}Next Steps:${NC}"
    echo -e "1. Configure domain DNS to point to this server"
    echo -e "2. Access the web interface: https://$domain"
    echo -e "3. Register the first user (system administrator)"
    
    echo -e "\n${YELLOW}Support:${NC}"
    echo -e "• Developer: Babak Alizadeh (alizadeh.babak)"
    echo -e "• License: GNU GPL v3"
    echo -e "• Website: ${UNDERLINE}https://hesabix.ir${NC}"
    echo -e "• Support us: ${UNDERLINE}https://hesabix.ir/page/sponsors${NC} ❤"
    
    echo -e "\n${GREEN}Installation completed successfully!${NC}"
    echo -e "${BOLD}${BLUE}=================================================${NC}"
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

# Main execution
main() {
    # Show header first
    print_header
    
    # Wait for user to see the header
    sleep 2
    
    # Then start logging
    init_logging
    check_system_requirements
    install_tools
    display_telemetry_consent
    update_packages
    install_php
    install_mysql
    install_nodejs
    install_apache
    install_composer
    
    domain=$(get_domain)
    setup_domain "$domain"
    install_software "$domain"
    setup_database "$domain"
    setup_web_ui "$domain"
    set_apache_ownership "$domain"
    setup_ssl "$domain"
    show_installation_summary "$domain"
}

# Execute main with error handling
main || handle_error "Installation failed"