# Grozeo-Scheduler

Background job scheduler for Grozeo — handles finance automation, courier integrations, order assignment, and merchant settlements.

## Overview
A Laravel-based queue and cron system that runs critical background processes. This service must run continuously in production alongside the main API.

## Tech Stack
- **Framework:** Laravel 6.x (upgrade to 10/11 recommended)
- **Language:** PHP 7.4+ (PHP 8.2 recommended)
- **Database:** MySQL 8.0+
- **Queue:** Redis

## Scheduled Jobs
| Job | Schedule | Description |
|-----|----------|-------------|
| AssignOrder | Every minute | Auto-assigns orders to delivery boys |
| RemoveBlockedItems | Every minute | Clears blocked cart items |
| OrderStatusUpdate | Every minute | Syncs order status changes |
| BranchStatusUpdate | Every 3 min | Updates branch availability |
| MerchantSettlements | Daily 00:02 | Processes merchant payouts |
| FinanceTransaction | Daily 13:02 | Posts finance entries |
| ConsignmentTrackingUpdate | Every 2 hrs | Updates shipment tracking |
| CreateShippingConsignment | Every minute | Creates Shiprocket/Shipyaari consignments |
| CreateExpressConsignment | Every minute | Creates express delivery consignments |
| PartnerDeliveryStartedCheck | Every 30 min | Checks partner delivery status |
| PartnerDeliveryCompletedCheck | Every 30 min | Marks completed partner deliveries |

## Finance Module
Located in `app/Finance/India/` and `app/Finance/UK/` — processes order value heads for auto-posting financial entries.

## Setup
```bash
composer install
cp .env.example .env
# Fill in DB, Redis, payment gateway, SMS keys
php artisan key:generate
php artisan migrate

# Start queue worker (production: use supervisor)
php artisan queue:work --queue=default --tries=3

# Start scheduler (add to cron)
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

## Supervisor Config (Production)
```ini
[program:grozeo-scheduler]
command=php /var/www/scheduler/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
numprocs=2
```

## Security Changes (2026-05-14)
- `eval()` removed from FinanceIndia.php and FinanceUK.php → replaced with `SafeMath::evaluate()`
- SecurityHeaders middleware added
- Production env config hardened (APP_DEBUG=false, Redis sessions)

## License
Proprietary — Grozeo International
