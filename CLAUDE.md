# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

KocinaSpeed is a modern recipe sharing platform built with Symfony 7.1. It allows users to discover quick and tasty recipes with a complete rating and review system. The application includes both a public-facing website and an admin interface using EasyAdmin.

## Development Commands

### Database Setup and Management
```bash
# Create database and run migrations
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction

# Validate database schema
php bin/console doctrine:schema:validate
```

### Custom Application Commands
```bash
# Create an admin user
php bin/console app:create-admin-user

# Optimize all images in the project
php bin/console app:optimize-images

# Custom cache warmup with critical data
php bin/console app:cache:warmup
```

### Development Environment Setup
```bash
# Run the setup script for complete environment configuration
./scripts/setup-dev-environment.sh

# Standard Symfony commands
php bin/console cache:clear
php bin/console assets:install public
php bin/console importmap:install
```

### Testing
```bash
# Run PHPUnit tests (if configured)
php bin/phpunit

# Server for development
symfony server:start
# OR
php -S localhost:8000 -t public
```

## Architecture Overview

### Core Entities and Relationships
- **Recipe**: Main entity with slug-based URLs, JSON ingredients, rating system, and multiple images
- **User**: Authentication with roles (ROLE_USER, ROLE_ADMIN), password reset functionality
- **Review**: User reviews with 1-5 star ratings, approval system, and image attachments
- **RecipeImage/ReviewImage**: File upload handling with optimization
- **ContactMessage**: Contact form submissions management

### Key Controllers Structure
- **RecipeController**: Homepage (`/`), recipe listing (`/recettes/{category}`), search (`/recherche`), and recipe details (`/recette/{slug}`)
- **ReviewController**: Handle recipe reviews and ratings
- **Admin Controllers**: EasyAdmin CRUD interfaces for all entities
- **SecurityController**: User authentication and authorization
- **ContactController**: Contact form handling
- **PasswordResetController**: Secure password recovery system

### Services Architecture
- **CacheService**: Performance optimization with cached data
- **ImageOptimizerService**: Automatic image optimization for uploads
- **DatabaseOptimizationService**: Database performance improvements

### File Upload Handling
- Recipe images stored in: `public/uploads/recipes/`
- Review images stored in: `public/uploads/reviews/`
- Automatic image optimization and backup system
- Default images provided for missing uploads

## Configuration

### Environment Variables (.env.local)
```env
DATABASE_URL="mysql://username:password@127.0.0.1:3306/kocinaspeed?serverVersion=8.0"
MAILER_DSN=smtp://support@kocinaspeed.fr:password@smtp.server.com:465
```

### Services Configuration
Key parameters in `config/services.yaml`:
- `recipe_images_directory`: Recipe image upload path
- `review_images_directory`: Review image upload path

## Key Features Implementation

### Search Functionality
- Real-time recipe search via `/recherche?query={term}`
- Database indexes on name, category, and combined fields for performance

### Rating System
- 1-5 star ratings with automatic average calculation
- Review approval system for content moderation

### Image Management
- Multiple images per recipe with automatic optimization
- Lazy loading implementation for performance
- Backup system for image files

### Admin Interface
- EasyAdmin 4 integration for complete CRUD operations
- Custom dashboard with statistics
- File upload handling in admin forms

### Security
- Symfony Security component with form login
- Password hashing and secure reset tokens
- CSRF protection on forms
- Role-based access control

### Email System
- Async email sending via Symfony Messenger
- Email templates for notifications and password reset
- SMTP configuration for production

## Database Indexes and Performance
The application includes strategic database indexes:
- Recipe slug lookup optimization
- Category-based filtering
- Search functionality optimization
- Created date ordering

## Development Workflow
1. The application uses XAMPP for local development
2. Images are automatically optimized on upload
3. Cache warming improves initial page load performance
4. Database migrations handle schema changes
5. Custom commands provide maintenance utilities