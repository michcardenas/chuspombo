# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 8 application for property rental management that integrates with external APIs (Smoobu, Guesty) for apartment booking and payment processing. The application handles property listings, reservations, and payments through multiple payment providers (Stripe, PayPal).

## Development Commands

### Laravel & PHP Commands
```bash
# Install dependencies
composer install

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Seed the database
php artisan db:seed

# Run tests
php artisan test
./vendor/bin/phpunit

# Start development server
php artisan serve

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Frontend & Asset Commands
```bash
# Install npm dependencies
npm install

# Build assets for development
npm run dev

# Watch files for changes
npm run watch

# Build for production
npm run prod
```

## Architecture Overview

### Core Services
- **SmoobuClient** (`app/Services/SmoobuClient.php`): Integrates with Smoobu API for apartment and booking data
- **GuestyService** (`app/Services/GuestyService.php`): Handles Guesty API integration for property management
- **GuestyTokenService** (`app/Services/GuestyTokenService.php`): Manages Guesty API token refresh
- **StripePaymentService** (`app/Services/StripePaymentService.php`): Stripe payment processing
- **PayPalService** (`app/Services/PayPalService.php`): PayPal payment integration

### Key Controllers
- **PropertiesController**: Main property listing and booking logic
- **CheckoutController**: Handles checkout flow and payment processing
- **ReservationController**: Manages booking creation and reservation flow
- **Admin/DashboardController**: Admin panel for property management
- **Admin/PaginaController**: CMS functionality for page content management
- **Admin/SmoobuApartmentController**: Property management and metadata
- **StripeController & PayPalController**: Payment webhook handlers

### Models
- **SmoobuApartmentMeta**: Property metadata and custom fields
- **SmoobuApartmentImage**: Property image management
- **PropertyPayment**: Payment transaction records
- **PaypalCheckoutSession**: PayPal session tracking
- **Pagina/PaginaMeta**: CMS page content and SEO metadata
- **ContactPage/AboutPage**: Specialized page content models
- **GuestyToken**: API token management for Guesty integration

### Route Structure
- `/propiedades`: Property listings and details
- `/admin`: Administrative interface (requires authentication)
- `/checkout`: Booking and payment flow
- `/book/{propertyId}`: Reservation creation
- Payment endpoints for Stripe and PayPal webhooks

## External API Integrations

This application integrates with:
- **Smoobu API**: Property management and availability
- **Guesty API**: Alternative property management system
- **Stripe API**: Credit card payment processing
- **PayPal API**: PayPal payment processing

## Console Commands

### Custom Artisan Commands
```bash
# Refresh Guesty API token (automated via scheduler)
php artisan guesty:refresh-token
```

## Database Considerations

The application uses Laravel migrations for schema management. Key tables include properties metadata, payments, CMS pages, and API tokens. Always run migrations after pulling changes.

## Environment Setup

Copy `.env.example` to `.env` and configure:
- Database credentials
- Smoobu/Guesty API keys and endpoints
- Stripe/PayPal API credentials
- Application URL and environment settings