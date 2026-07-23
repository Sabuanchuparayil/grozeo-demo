# Grozeo-Partner

ASP.NET partner portal for Grozeo merchant onboarding, store management, and business operations.

## Overview
Web application enabling merchants to manage their Grozeo store, inventory, orders, delivery, finance, CRM and marketing from a single dashboard. Multi-section portal with Tenant, Business, Finance, Operation, Sales, Marketing and Fleet modules.

## Tech Stack
- **Framework:** ASP.NET Framework 4.7.2 (upgrade to .NET 8 recommended)
- **Language:** C#
- **Database:** SQL Server (Azure) + MySQL
- **Auth:** Forms Authentication + Custom Role Provider

## Portal Sections
| Section | Path | Description |
|---------|------|-------------|
| Tenant | `/Tenant/` | Store settings, inventory, orders, delivery rules |
| Business | `/Business/` | CRM, leads, area managers, delivery staff |
| Finance | `/Finance/` | Accounting, settlements, GST reports, vouchers |
| Operation | `/Operation/` | Packing delays, delivery monitoring |
| Sales | `/Sales/` | Sales reporting |
| Marketing | `/Marketing/` | Leads and campaigns |
| Fleet | `/Fleet/` | Vehicle management |
| Admin | `/Manage/` | Super-admin panel |

## Setup
```bash
# 1. Set environment variables on your server/Azure App Service:
IMPORT_DB_HOST=your-mysql-host
IMPORT_DB_USER=your-mysql-user
IMPORT_DB_PASS=your-mysql-password
AZURE_DB_PASSWORD=your-azure-sql-password
MYSQL_DB_PASSWORD=your-mysql-password

# 2. Update connection strings in Web.config to reference env vars

# 3. Build with Visual Studio 2022 or MSBuild
msbuild RetalineProAgent.csproj /p:Configuration=Release

# 4. Deploy to IIS or Azure App Service
```

## Security Notes
- `Command.aspx` executes raw SQL — restrict to super-admin IP whitelist or remove in production
- All DB passwords moved to environment variables
- `compilation debug="false"` set
- `customErrors mode="RemoteOnly"` set

## Security Changes (2026-05-14)
- Hardcoded credentials removed from MasterDataImport.aspx.cs
- Table name injection validation added
- Web.config credentials redacted → use environment variables
- Security response headers added to Web.config

## License
Proprietary — Grozeo International
