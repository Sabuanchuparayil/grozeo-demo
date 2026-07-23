# Grozeo-Bizapi

Customer-facing REST API powering the Grozeo mobile app and storefront web application.

## Overview
Laravel-based API serving 211 endpoints across authentication, product catalog, cart, checkout, orders, payments, delivery tracking, and driver management.

## Tech Stack
- **Framework:** Laravel 6.x (upgrade to 10/11 recommended)
- **Language:** PHP 7.4+ (PHP 8.2 recommended)
- **Auth:** JWT (tymon/jwt-auth)
- **Database:** MySQL 8.0+
- **Cache/Queue:** Redis

## API Route Groups
| Prefix | Auth | Description |
|--------|------|-------------|
| `/api/signup/*` | Public | Registration and OTP verification |
| `/api/login` | Public | Authentication |
| `/api/home/*` | Public | Home screen content |
| `/api/products/*` | Public | Product catalog |
| `/api/category/*` | Public | Category browsing |
| `/api/search/*` | Public | Product search |
| `/api/feedback/*` | JWT | Customer feedback |
| `/api/customer/*` | JWT | Profile management |
| `/api/cart/*` | JWT | Cart operations |
| `/api/checkout/*` | JWT | Checkout flow |
| `/api/orders/*` | JWT | Order history |
| `/api/wishlist/*` | JWT | Wishlist management |
| `/api/driver/*` | Driver JWT | Driver app endpoints |
| `/api/back-office/*` | Back-office JWT | Admin operations |

## Payment Gateways
- Razorpay (India)
- Stripe (UK/International)
- Revolut
- CCAvenue

## Courier Integrations
- Shiprocket
- Shipyaari

## Setup
```bash
composer install
cp .env.example .env
# Fill in DB, Redis, JWT secret, payment keys, SMS provider
php artisan key:generate
php artisan jwt:secret
php artisan migrate
php artisan serve
```

## Authentication Flow
```
POST /api/signup/mobile      → Request OTP
POST /api/signup/verify      → Verify OTP → returns JWT
POST /api/login              → Email/password → returns JWT
Authorization: Bearer {jwt}  → All protected routes
```

## Security Changes (2026-05-14)
- SecurityHeaders middleware (X-Frame-Options, HSTS, X-Content-Type-Options)
- SafeMath helper added
- Production env hardened
- APP_DEBUG=false enforced

## License
Proprietary — Grozeo International
