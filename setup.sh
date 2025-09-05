#!/bin/bash

echo "🚀 ECRF System Setup Script"
echo "==========================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

# Check if we're in the right directory
if [ ! -f "composer.json" ]; then
    print_error "Please run this script from the project root directory"
    exit 1
fi

echo "Starting ECRF system setup..."
echo

# 1. Install PHP dependencies
print_status "Installing PHP dependencies..."
if command -v composer &> /dev/null; then
    composer install --no-interaction --optimize-autoloader
else
    print_error "Composer not found. Please install Composer first."
    exit 1
fi

# 2. Install Node.js dependencies
print_status "Installing Node.js dependencies..."
if command -v npm &> /dev/null; then
    npm install
else
    print_error "npm not found. Please install Node.js and npm first."
    exit 1
fi

# 3. Setup environment file
print_status "Setting up environment configuration..."
if [ ! -f ".env" ]; then
    cp .env.example .env
    print_status "Created .env file from .env.example"
else
    print_warning ".env file already exists, skipping..."
fi

# 4. Generate application key
print_status "Generating application key..."
php artisan key:generate --force

# 5. Create database
print_status "Setting up database..."
if [ ! -f "database/database.sqlite" ]; then
    touch database/database.sqlite
    print_status "Created SQLite database file"
else
    print_warning "Database file already exists"
fi

# 6. Run migrations
print_status "Running database migrations..."
php artisan migrate --force

# 7. Seed database
print_status "Seeding database with sample data..."
php artisan db:seed --force

# 8. Create storage link
print_status "Creating storage symbolic link..."
php artisan storage:link

# 9. Set permissions
print_status "Setting up permissions..."
chmod -R 755 storage bootstrap/cache
if [ -d "public/storage" ]; then
    chmod -R 755 public/storage
fi

# 10. Build frontend assets
print_status "Building frontend assets..."
npm run build

# 11. Clear and cache configuration
print_status "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo
echo "🎉 Setup Complete!"
echo "=================="
echo
echo "Your ECRF system is now ready to use!"
echo
echo "📋 Sample Users Created:"
echo "  • Admin: admin@ecrf.com (password: password)"
echo "  • Researcher: researcher@ecrf.com (password: password)"  
echo "  • User: user@ecrf.com (password: password)"
echo
echo "📊 Sample Data:"
echo "  • Clinical Trial Demo Project with 3 forms"
echo "  • Demographics, Medical History, and Laboratory Results forms"
echo "  • Realistic clinical field definitions"
echo
echo "🚀 To start the development server:"
echo "  php artisan serve"
echo
echo "🐳 To run with Docker:"
echo "  docker-compose up -d"
echo "  Then visit: http://localhost:8080"
echo
echo "📖 Features Available:"
echo "  • Drag & drop file upload"
echo "  • Intelligent field mapping with fuzzy matching"
echo "  • Excel/CSV import and export"
echo "  • User authentication and authorization"
echo "  • Modern responsive UI"
echo

# Check if we can start the server
if command -v php &> /dev/null; then
    read -p "Would you like to start the development server now? (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        print_status "Starting development server..."
        echo "Server will be available at: http://localhost:8000"
        echo "Press Ctrl+C to stop the server"
        echo
        php artisan serve
    fi
fi