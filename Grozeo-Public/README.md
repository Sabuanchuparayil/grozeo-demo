# Grozeo-Public

ASP.NET Core multi-tenant storefront — the customer-facing website for Grozeo and white-label retail brands.

## Overview
Multi-tenant e-commerce storefront supporting multiple themes (Essence, Bonitos, Gears, Yummy, Marketplace, BigStore, Creamily). Each tenant is identified by hostname and configured via appsettings.json.

## Tech Stack
- **Framework:** ASP.NET Core (.NET 6+)
- **Language:** C#
- **Pattern:** MVC + View Components
- **Database:** SQL Server (Azure)
- **Cache:** Redis (optional)

## Supported Themes
`Essence`, `EssenceOriginal`, `Bonitos`, `Gears`, `Yummy`, `Marketplace`, `BigStore`, `Creamily`, `DiegoFelipe`, `Jewel`

## Key Routes
| Route | Controller | Description |
|-------|-----------|-------------|
| `/` | HomeController | Homepage with banners and products |
| `/catalog/*` | CatalogController | Category and product listings |
| `/product/*` | ProductDetailsController | Product detail pages |
| `/cart` | CartController | Shopping cart |
| `/checkout` | CheckoutController | Checkout flow |
| `/my-account/*` | MyAccountController | Orders, profile, wishlist |
| `/auth/*` | AuthenticationController | Login/OTP/Social auth |
| `/address/*` | AddressController | Address management |

## Setup
```bash
# 1. Copy config template
cp Retaline/appsettings.example.json Retaline/appsettings.json
# Fill in connection string, API keys, Revolut token, etc.

# 2. Build
cd Retaline
dotnet restore
dotnet build

# 3. Run
dotnet run
```

## Multi-tenancy
Tenants are configured in `appsettings.json` under `Multitenancy.Tenants`.
Each tenant has its own hostname, theme, API URL, store ID and payment gateway.

## Security Changes (2026-05-14)
- All API keys, DB password, Google API key, Recaptcha keys moved to environment variables
- Hardcoded JWT token removed from config
- All `http://` API URLs changed to `https://`
- `appsettings.example.json` added as safe config template

## License
Proprietary — Grozeo International
