# Laravel 6 → 10 Upgrade Guide for Grozeo-Bizapi & Grozeo-Scheduler

## Prerequisites
- PHP 8.2+ installed on your server
- Composer 2.x
- MySQL 8.0+
- Redis 6+

---

## Step 1: Backup Everything First

```bash
git checkout -b upgrade/laravel10
mysqldump -u root -p your_database > backup_before_upgrade.sql
cp -r . ../backup_laravel6
```

---

## Step 2: Update composer.json

Replace your existing `composer.json` with the provided `composer.json` in this package.

Key changes:
- `"php": "^8.2"` (was `^7.2`)
- `"laravel/framework": "^10.0"` (was `^6.2`)
- `"tymon/jwt-auth": "^2.0"` (was `1.0.0-rc.5`)
- Removed: `fideloper/proxy` (now built into Laravel 10 as `TrustProxies`)
- Removed: `binarytorch/larecipe`, `laravel/nexmo-notification-channel`
- Updated: `aws/aws-sdk-php` to `^3.281`
- Added: `predis/predis ^2.0` for Redis

```bash
composer update
```

---

## Step 3: Update Bootstrap Files

### bootstrap/app.php
Laravel 10 uses the same bootstrap structure as Laravel 6, no changes needed.

### app/Http/Kernel.php
Remove `CheckForMaintenanceMode` — it's been renamed:

```php
// REMOVE:
\App\Http\Middleware\CheckForMaintenanceMode::class,

// ADD:
\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
```

---

## Step 4: Fix Breaking Changes in App Code

### 4.1 Str and Arr helpers (now require facade import)
```php
// BEFORE (Laravel 6 — global helpers):
str_slug($string)
array_get($array, 'key')

// AFTER (Laravel 10):
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
Str::slug($string)
Arr::get($array, 'key')
```

Run this to find all usages:
```bash
grep -rn "str_slug\|str_plural\|str_singular\|array_get\|array_set\|array_pluck" app/ --include="*.php"
```

### 4.2 Model::all() return type
In Laravel 10, `Model::all()` returns `Illuminate\Database\Eloquent\Collection`.
If you're treating it as an array, wrap with `->toArray()`:
```php
// BEFORE:
$items = Model::all();
foreach ($items as $item) { ... } // still works

// If you need an array explicitly:
$items = Model::all()->toArray();
```

### 4.3 Route caching
After upgrade, clear all caches:
```bash
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan cache:clear
php artisan optimize:clear
```

### 4.4 JWT Auth upgrade (rc.5 → v2.0)
JWT 2.0 has changed config key names:

```bash
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret
```

Update `config/jwt.php` if you have custom settings — the config structure changed.

In your AuthController, update the guard usage:
```php
// BEFORE (Laravel 6 / JWT rc.5):
$token = auth()->attempt($credentials);

// AFTER (Laravel 10 / JWT 2.0) — same API, no changes needed
// But ensure your User model implements: Tymon\JWTAuth\Contracts\JWTSubject
```

### 4.5 Middleware registration
In Laravel 10, middleware aliases are registered differently in Kernel.php:
```php
// In app/Http/Kernel.php, rename:
protected $routeMiddleware = [ ... ];
// TO:
protected $middlewareAliases = [ ... ];
```

### 4.6 Eloquent Model casting
```php
// BEFORE (Laravel 6):
protected $casts = ['is_active' => 'boolean'];

// AFTER (Laravel 10) — same syntax works, but new casted() method available:
// No breaking change needed here
```

### 4.7 Exception Handler (app/Exceptions/Handler.php)
```php
// BEFORE (Laravel 6):
public function render($request, Exception $exception) { ... }

// AFTER (Laravel 10):
use Throwable; // not just Exception
public function render($request, Throwable $exception) { ... }
```

---

## Step 5: Update Config Files

### config/auth.php
No changes needed for existing JWT setup.

### config/cors.php
```php
// Laravel 10 includes built-in CORS — you can remove spatie/laravel-cors
// and update config/cors.php to use the native config:
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [env('FRONTEND_URL', 'https://grozeo.in')],
    'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
    'supports_credentials' => true,
];
```

---

## Step 6: Fix PHP 8.2 Compatibility Issues

### 6.1 Dynamic properties deprecated
PHP 8.2 deprecates dynamic properties on classes without `#[AllowDynamicProperties]`.

Run this to find affected classes:
```bash
grep -rn '\$this->[a-z]' app/Http/Controllers/ --include="*.php" | grep -v '__construct\|protected\|public\|private' | head -20
```

Fix by declaring properties explicitly or adding the attribute:
```php
// Option A: Declare the property
class MyController {
    protected string $someProperty;
}

// Option B: Allow dynamic (temporary fix):
#[\AllowDynamicProperties]
class MyController { ... }
```

### 6.2 Nullable types
PHP 8.2 enforces nullable types more strictly:
```php
// BEFORE:
function myFunction(string $param = null) { ... }

// AFTER:
function myFunction(?string $param = null) { ... }
```

### 6.3 str_contains / str_starts_with / str_ends_with
These are now native in PHP 8.0+ — no Str:: facade needed:
```php
// Both work, native is preferred:
str_contains($haystack, $needle);
Str::contains($haystack, $needle);
```

---

## Step 7: Run Tests

```bash
php artisan test
# or
./vendor/bin/phpunit
```

---

## Step 8: Deploy

```bash
# On Railway:
# 1. Set PHP version in Procfile or nixpacks.toml
echo "web: heroku-php-apache2 public/" > Procfile

# Or create nixpacks.toml:
cat > nixpacks.toml << 'EOF'
[phases.setup]
nixPkgs = ["php82", "php82Extensions.pdo_mysql", "php82Extensions.redis", "composer"]

[start]
cmd = "php artisan serve --host=0.0.0.0 --port=$PORT"
EOF

# 2. Push to Railway
railway up
```

---

## Estimated Time
- Bizapi: 2-3 days (many controllers, needs testing)
- Scheduler: 1-2 days (fewer HTTP controllers, more background jobs)

## Testing Checklist
- [ ] Login / JWT token generation works
- [ ] Product listing returns correct data
- [ ] Cart add/remove works
- [ ] Checkout flow completes
- [ ] Payment webhook receives correctly
- [ ] Scheduler cron jobs fire on schedule
- [ ] Queue workers process jobs
- [ ] Finance posting job runs without errors
