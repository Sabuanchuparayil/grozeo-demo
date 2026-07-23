# Grozeo-Bizadmin

Admin panel for Grozeo business operations — central hub for store management, fleet, CRM, finance and multi-tenant administration.

## Overview
Extends Manage-Products with additional admin-level modules including partner management, business associates, outbound calls, omni-channel orders, relationship officers, and advanced reporting.

## Tech Stack
- **Language:** PHP 7.4+ (PHP 8.2 recommended)
- **Database:** MySQL 8.0+
- **Frontend:** ExtJS 4
- **Server:** Apache/Nginx

## Additional Modules vs Manage-Products
```
modules/
├── business_associate/        # BA onboarding and management
├── relationship_officer/      # RO management
├── area_manager/              # Area manager assignments
├── retaline_procurement/      # Procurement management
├── retaline_grn/              # Goods received notes
├── retaline_omni_channel/     # Omni-channel orders
├── outbound_calls/            # Outbound call management
├── partner_masters/           # Partner master data
├── crm_consulting_partner/    # Consulting partner CRM
├── crm_associate_partner/     # Associate partner CRM
├── retaline_stock_request/    # Stock request management
├── retaline_scheduledjobs/    # Scheduled job monitoring
├── support_master/            # Support management
├── support_ticket/            # Ticket handling
└── business_division/         # Business division management
```

## Setup
Same as Manage-Products. Both share identical DB layer and config structure.

## Security Changes (2026-05-14)
- 348+ SQL injection fixes applied
- bcrypt password migration
- Global error handler
- Filter sanitization for all ExtJS grids

## License
Proprietary — Grozeo International
