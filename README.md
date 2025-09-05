# ECRF System - Electronic Case Report Form Management Platform

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11-red.svg" alt="Laravel 11">
  <img src="https://img.shields.io/badge/Vue.js-3-green.svg" alt="Vue.js 3">
  <img src="https://img.shields.io/badge/Inertia.js-2-purple.svg" alt="Inertia.js 2">
  <img src="https://img.shields.io/badge/License-MIT-blue.svg" alt="MIT License">
</p>

A modern, production-ready Electronic Case Report Form (ECRF) management system built with Laravel and Vue.js. This system provides a complete solution for clinical research data management with intelligent field mapping, drag-and-drop file uploads, and automated data import/export capabilities.

## ✨ Features

### 🔐 Authentication & Security
- **User Authentication**: Complete login/logout system with sample users
- **Role-based Access**: Secure access controls for different user types
- **CSRF Protection**: Built-in security measures
- **Input Validation**: Comprehensive data validation throughout

### 📁 File Management
- **Drag & Drop Upload**: Modern file upload interface with visual feedback
- **Multi-format Support**: JSON project files, Excel (.xlsx, .xls), and CSV files
- **File Validation**: Automatic file type and content validation
- **Progress Tracking**: Real-time upload progress and status feedback

### 🧠 Intelligent Field Mapping
- **Fuzzy Matching Algorithm**: Advanced string similarity matching using multiple algorithms:
  - Levenshtein distance calculation
  - Jaro-Winkler similarity
  - Word-based matching
  - Exact and partial string matching
- **Auto-suggestions**: Automatic field mapping recommendations with confidence scores
- **Visual Mapping Interface**: Intuitive drag-and-drop field mapping wizard
- **Validation Feedback**: Real-time mapping validation and error reporting

### 📊 Data Import/Export
- **Excel Import**: Sophisticated Excel/CSV data import with column mapping
- **Data Validation**: Import validation with detailed error reporting
- **Export Generation**: Multiple export formats (Excel, structured data)
- **Batch Processing**: Efficient handling of large datasets

### 🎨 Modern User Interface
- **Responsive Design**: Mobile-first responsive interface using Tailwind CSS
- **Dark/Light Themes**: Modern color schemes and visual hierarchy
- **Interactive Components**: Rich UI components with smooth animations
- **Accessibility**: WCAG compliant interface design

### 🏥 Clinical Research Ready
- **Form Management**: Complete form and field definition system
- **Project Organization**: Multi-project support with versioning
- **Field Types**: Support for various clinical data types
- **Conditional Logic**: Advanced form logic and dependencies

## 🚀 Quick Start

### Option 1: Automated Setup (Recommended)

```bash
# Clone the repository
git clone https://github.com/HannesTamm1/ecrf-app.git
cd ecrf-app

# Run the automated setup script
chmod +x setup.sh
./setup.sh
```

The setup script will:
- Install all dependencies (PHP and Node.js)
- Set up the database with sample data
- Build frontend assets
- Configure the application
- Optionally start the development server

### Option 2: Manual Setup

#### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- SQLite (default) or MySQL/PostgreSQL

#### Installation Steps

1. **Clone and Install Dependencies**
```bash
git clone https://github.com/HannesTamm1/ecrf-app.git
cd ecrf-app
composer install
npm install
```

2. **Environment Setup**
```bash
cp .env.example .env
php artisan key:generate
```

3. **Database Setup**
```bash
# For SQLite (default)
touch database/database.sqlite
php artisan migrate
php artisan db:seed

# For MySQL/PostgreSQL, update .env file first, then:
# php artisan migrate
# php artisan db:seed
```

4. **Build Assets and Start**
```bash
npm run build
php artisan serve
```

Visit `http://localhost:8000` to access the application.

### Option 3: Docker Deployment

```bash
# Build and start containers
docker-compose up -d

# Run initial setup inside container
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
```

Visit `http://localhost:8080` to access the application.

## 👥 Demo Users

The system comes with pre-configured demo users for testing:

| Role | Email | Password | Description |
|------|-------|----------|-------------|
| Admin | admin@ecrf.com | password | Full system access |
| Researcher | researcher@ecrf.com | password | Research coordinator access |
| User | user@ecrf.com | password | Data entry user access |

## 📋 Sample Data

The system includes a comprehensive demo project:

### Clinical Trial Demo Project
- **Demographics Form**: Patient demographic information
  - Patient ID, Age, Gender, Date of Birth
  - Weight, Height, Ethnicity
  
- **Medical History Form**: Patient medical background
  - Diabetes and Hypertension history
  - Current medications and allergies
  - Previous surgeries
  
- **Laboratory Results Form**: Lab test data
  - Hemoglobin, White Blood Cell Count
  - Platelet Count, Glucose, Creatinine
  - Test dates and values

## 🔧 System Architecture

### Backend (Laravel)
- **Models**: Project, Form, FormField, ImportedRecord, User
- **Controllers**: Authentication, Upload, Import, Export, Mapping
- **Services**: File processing, data validation, field matching
- **Database**: SQLite (default), MySQL/PostgreSQL support

### Frontend (Vue.js + Inertia.js)
- **Components**: Reusable UI components with Tailwind CSS
- **Pages**: Upload, Import Wizard, Export Generator, Authentication
- **State Management**: Reactive data handling with Vue 3 Composition API
- **File Handling**: Drag-and-drop with progress tracking

### Key Technologies
- **Laravel 11**: PHP framework for robust backend API
- **Vue.js 3**: Modern JavaScript framework with Composition API
- **Inertia.js**: SPA-like experience without API complexity
- **Tailwind CSS**: Utility-first CSS framework
- **PhpSpreadsheet**: Excel file processing
- **SQLite/MySQL**: Database storage options

## 📖 User Guide

### 1. Uploading Project Files

1. **Login** with one of the demo accounts
2. **Navigate** to the Upload page
3. **Drag and drop** or click to select JSON project files
4. **Monitor** upload progress and validation results
5. **Review** project metadata and form structure

### 2. Data Import Process

1. **Select Target Form** from your uploaded projects
2. **Upload Excel/CSV** data file using drag-and-drop
3. **Review Column Detection** and data structure
4. **Map Fields** using the intelligent mapping wizard:
   - Use auto-suggestions for quick mapping
   - Manually adjust mappings as needed
   - Review confidence scores for suggestions
5. **Validate Mapping** to check for errors
6. **Import Data** with progress tracking

### 3. Field Mapping Features

The system provides intelligent field mapping with:
- **Automatic Suggestions**: Based on field names and labels
- **Confidence Scoring**: Percentage-based matching confidence
- **Multiple Algorithms**: Various string similarity methods
- **Manual Override**: Full control over field assignments
- **Real-time Validation**: Immediate feedback on mapping quality

### 4. Data Export

1. **Choose Export Type**: Various format options
2. **Select Data Range**: Specify which data to export
3. **Generate Export**: Download processed files
4. **Review Results**: Validation and export summary

## 🛠️ Development

### Project Structure
```
ecrf-app/
├── app/
│   ├── Http/Controllers/     # API and web controllers
│   ├── Models/              # Eloquent models
│   └── Services/            # Business logic services
├── database/
│   ├── migrations/          # Database schema
│   └── seeders/            # Sample data
├── resources/
│   ├── js/
│   │   └── Pages/          # Vue.js pages
│   └── views/              # Blade templates
├── routes/                 # Application routes
└── docker/                # Docker configuration
```

### Available Commands

```bash
# Development
npm run dev              # Start Vite dev server
php artisan serve       # Start Laravel dev server

# Building
npm run build           # Build production assets
php artisan optimize    # Optimize Laravel for production

# Database
php artisan migrate     # Run migrations
php artisan db:seed     # Seed sample data
php artisan migrate:fresh --seed  # Fresh install with data

# Testing
php artisan test        # Run PHP tests
npm run test           # Run JavaScript tests

# Docker
docker-compose up -d    # Start in detached mode
docker-compose logs     # View container logs
docker-compose down     # Stop containers
```

### Adding New Forms

1. **Create Migration**: Add form and field definitions
2. **Update Seeder**: Include sample data
3. **Frontend**: Update form selection and field mapping
4. **Validation**: Add business rules and validation logic

### Extending Field Types

1. **Backend**: Update FormField model and validation
2. **Frontend**: Add UI components for new field types
3. **Mapping**: Extend fuzzy matching for new field patterns
4. **Export**: Update export generators for new data types

## 🔒 Security Features

- **CSRF Protection**: All forms protected against cross-site attacks
- **Input Validation**: Comprehensive server-side validation
- **File Type Validation**: Strict file type and content checking
- **SQL Injection Prevention**: Eloquent ORM with prepared statements
- **XSS Protection**: Output escaping and sanitization
- **Authentication**: Secure session-based authentication

## 📈 Performance Optimizations

- **Asset Compilation**: Optimized CSS and JavaScript bundles
- **Database Queries**: Efficient queries with eager loading
- **Caching**: Route, config, and view caching for production
- **File Processing**: Streaming file processing for large datasets
- **Lazy Loading**: Component lazy loading for better performance

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature-name`
3. Make your changes and test thoroughly
4. Commit your changes: `git commit -am 'Add feature'`
5. Push to the branch: `git push origin feature-name`
6. Submit a pull request

### Development Guidelines

- Follow PSR-12 coding standards for PHP
- Use Vue.js 3 Composition API patterns
- Write comprehensive tests for new features
- Update documentation for significant changes
- Ensure responsive design compatibility

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

For support and questions:

1. **Documentation**: Check this README and inline code comments
2. **Issues**: Open a GitHub issue for bugs or feature requests
3. **Discussions**: Use GitHub Discussions for general questions

## 🙏 Acknowledgments

- Laravel community for the excellent framework
- Vue.js team for the reactive frontend framework
- Tailwind CSS for the utility-first CSS framework
- PhpSpreadsheet for Excel file processing capabilities
- Clinical research community for domain expertise

---

**Built with ❤️ for the clinical research community**