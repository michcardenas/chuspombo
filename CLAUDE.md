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
- **SmoobuClient** (`app/Services/SmoobuClient.php`): Integrates with Smoobu API for apartment and booking data. Provides methods for fetching apartments, availability, reservations, and prices. Uses HTTP client with API key authentication.
- **GuestyService** (`app/Services/GuestyService.php`): Stub for Guesty API integration (not currently implemented)
- **GuestyTokenService** (`app/Services/GuestyTokenService.php`): Stub for managing Guesty API token refresh (not currently implemented)
- **StripePaymentService** (`app/Services/StripePaymentService.php`): Stripe payment processing and session management
- **PayPalService** (`app/Services/PayPalService.php`): PayPal payment integration with order creation and capture

### Key Controllers
- **PropertiesController**: Main property listing and booking logic. Handles availability checks, price calculations, quote creation, and integration with Smoobu API for property data.
- **CheckoutController**: Modern checkout flow with availability checking, quote generation, and payment processing. Provides AJAX endpoints for real-time availability and pricing.
- **ReservationController**: Manages booking creation and reservation flow
- **Admin/DashboardController**: Admin panel entry point for property management
- **Admin/PaginaController**: CMS functionality for managing page content (about, contact, properties pages)
- **Admin/SmoobuApartmentController**: Property metadata editing interface - manages custom titles, descriptions, images, and settings that override or supplement Smoobu API data
- **Admin/PagoController**: Payment management and viewing in admin panel
- **StripeController & PayPalController**: Payment processing and webhook handlers for respective payment providers

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
- `/propiedades/{id}`: Individual property details page
- `/propiedades/{id}/reservar`: Reservation form and booking flow
- `/admin`: Administrative interface (requires authentication via `/login`)
- `/admin/apartments/{id}/edit`: Edit property metadata and images
- `/admin/pagina/*`: CMS page content management
- `/admin/pagos`: Payment transaction management
- `/checkout/*`: Modern checkout flow with AJAX endpoints for availability and pricing
- `/book/{propertyId}`: Alternative reservation creation endpoint
- `/stripe/*` and `/paypal/*`: Payment processing, success/error handlers, and webhooks

## External API Integrations

This application integrates with:
- **Smoobu API**: Primary property management system for fetching apartments, checking availability, retrieving rates/prices, and managing reservations. Configuration in `config/services.php` requires `base_url` and `key`.
- **Guesty API**: Alternative property management system (stub implementation exists but not currently active)
- **Stripe API**: Credit card payment processing via Stripe Checkout sessions
- **PayPal API**: PayPal payment processing using PayPal Checkout SDK

## Console Commands

### Custom Artisan Commands
```bash
# Refresh Guesty API token (command file exists but not implemented)
php artisan guesty:refresh-token
```

Note: The `RefreshGuestyToken` command exists in `app/Console/Commands/` but is currently empty.

## Key Architecture Patterns

### Property Data Flow
1. **Base data** comes from Smoobu API (`SmoobuClient`) - apartment names, IDs, availability
2. **Local overrides** stored in `SmoobuApartmentMeta` table - custom titles, descriptions, prices
3. **Images** managed separately in `SmoobuApartmentImage` table with sort ordering
4. Properties are cached for 300 seconds (5 minutes) to reduce API calls

### Availability Logic
- Availability checks use date ranges excluding the checkout day (checkout day is available for new check-ins)
- The system tries the Smoobu `/availability` endpoint first, falls back to `/reservations` if needed
- Blocked dates are calculated by checking for overlapping reservations

### Payment Flow
1. User selects dates and property → availability check
2. Price calculation via Smoobu API or local override
3. Quote/session creation with checkout details stored
4. Redirect to Stripe or PayPal for payment
5. Webhook/callback handles payment confirmation
6. Payment record stored in `PropertyPayment` table

## Database Considerations

The application uses Laravel migrations for schema management. Key tables include:
- `smoobu_apartment_meta`: Custom property data overriding/supplementing API
- `smoobu_apartment_images`: Property images with ordering
- `property_payments`: Payment transaction records
- `paypal_checkout_sessions`: PayPal session tracking
- `paginas` and `pagina_meta`: CMS content
- `guesty_tokens`: API token storage (for future use)

Always run migrations after pulling changes.

## Environment Setup

Copy `.env.example` to `.env` and configure:
- Database credentials
- Smoobu API: `SMOOBU_API_URL` and `SMOOBU_API_KEY` (configured in `config/services.php`)
- Guesty API credentials (if implementing)
- Stripe: `STRIPE_KEY` and `STRIPE_SECRET`
- PayPal: `PAYPAL_MODE`, `PAYPAL_CLIENT_ID`, `PAYPAL_SECRET` (sandbox for development, live for production)
- Application URL and environment settings

## Important Notes

- **Route Issue**: There's a reference to non-existent `BookingController` in routes/web.php:46 that will cause errors when listing routes
- **Windows Development**: This project appears to be developed on Windows (XAMPP path structure)
- **Laravel Mix**: Uses Laravel Mix for frontend asset compilation (not Vite)
- **Authentication**: Admin routes use Laravel's built-in `auth` middleware
- **API Caching**: Smoobu API responses are cached to reduce external API calls - clear cache when testing API changes