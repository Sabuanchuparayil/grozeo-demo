# Manage-Products

Multi-tenant product, inventory and order management system for Grozeo retailers.

## Overview
Manages the full retail lifecycle including products, stock, orders, purchasing, finance (Finascop), CRM, and delivery management. Supports pharmacy (MyPha), B2B (Retaline), and general retail operations.

## Tech Stack
- **Language:** PHP 7.4+ (PHP 8.2 recommended)
- **Database:** MySQL 8.0+
- **Frontend:** ExtJS 4 (desktop UI)
- **Server:** Apache/Nginx

## Module Architecture
```
modules/
├── auth/                  # Login, session, password reset
├── user/                  # User management
├── tp_products/           # Third-party product catalog
├── mypha_product/         # Pharmacy products
├── mypha_medicine/        # Medicine master data
├── order_processing/      # Order fulfillment
├── order_cancellation/    # Cancellation handling
├── retaline_sales/        # B2B sales orders
├── retaline_deliveryjobs/ # Delivery job management
├── finascop_stock/        # Inventory & stock
├── finascop_purchase/     # Purchase orders
├── finascop_sale/         # Sales accounting
├── finascop_ledger/       # Financial ledger
├── finascop_approval/     # Approval workflows
├── crm_leads/             # CRM lead management
├── crm_sms/               # SMS campaigns
├── qugeo/                 # Driver/fleet management
└── dashboard/             # Analytics dashboard
```

## Setup
```bash
# 1. Configure database
cp finascop_config/config.example.php finascop_config/config.php
# Edit config.php with your DB credentials

# 2. Set Apache document root to repo root
# 3. Enable mod_rewrite
# 4. Import database schema from /db/ directory
```

## Security Changes (2026-05-14)
- SQL injection: 283+ fixes applied using prepared statements (`getItemSafe`, `getFromSafe`, `executeSafe`, `getMultipleSafe`)
- Password hashing: MD5 → bcrypt with automatic migration on login
- Error handling: Global error handler added (`includes/GrozeoErrorHandler.php`)
- Filter sanitization: ExtJS grid filters now sanitized via `buildSafeFilterQuery()`

## Environment Variables
Set these in your server environment (never in committed files):
```
DB_HOST=your-db-host
DB_USER=your-db-user
DB_PASS=your-db-password
DB_NAME=your-database
```

## License
Proprietary — Grozeo International
