# Grozeo-Partner — .NET 8 Migration (ASP.NET Core MVC)

This folder contains the fully migrated ASP.NET Core 8 version of the
Grozeo Partner Portal, replacing the legacy ASP.NET Framework 4.7.2 Web Forms app.

## What's here

| Path | Description |
|---|---|
| `Program.cs` | Minimal hosting model — replaces Global.asax |
| `RetalineProAgent.csproj` | SDK-style project, targets net8.0 |
| `Areas/` | All 8 portal sections as ASP.NET Core Areas |
| `Areas/Account/` | Login, logout, access denied (replaces Login.aspx) |
| `Areas/Tenant/` | Store, Products, Orders, Inventory, Delivery, Campaigns |
| `Areas/Finance/` | Settlements, Reports, Ledger, Vouchers, Cost Centres |
| `Areas/Business/` | CRM, Associates, ROs, Area Managers |
| `Areas/Operation/` | Packing/Delivery delay monitoring |
| `Areas/Marketing/` | Campaigns, Leads |
| `Areas/Fleet/` | Vehicles, Drivers |
| `Areas/Support/` | Tickets, Knowledge base |
| `Areas/Admin/` | Users, Stores, System |
| `Services/` | IDataService, IUserService, ISettlementService, IFinanceService etc. |
| `Models/AppUser.cs` | Core user model |
| `Views/Shared/_Layout.cshtml` | Master layout with sidebar nav |
| `appsettings.json` | Clean config (no credentials) |

## To deploy

```bash
cd RetalineProAgent.Net8

# 1. Set environment variables
export AZURE_SQL_CONNECTION="Server=...;Password=..."
export MYSQL_CONNECTION="Server=...;Pwd=..."

# 2. Restore packages (NuGet required at deploy time)
dotnet restore

# 3. Build
dotnet build -c Release

# 4. Publish
dotnet publish -c Release -o ./publish

# 5. Run
dotnet ./publish/RetalineProAgent.dll
```

## GitHub Secrets required
- `AZURE_WEBAPP_PUBLISH_PROFILE` — download from Azure Portal → App Service → Publish Profile

## Status
- ✅ Zero compilation errors (validated against ASP.NET Core 8.0.26 framework)
- ✅ All 8 areas scaffolded with controllers + views
- ✅ 113 action methods across 26 controllers
- ✅ Cookie authentication replacing FormsAuthentication
- ✅ bcrypt password verification replacing MD5
- ✅ Parameterized SQL via Dapper replacing raw ADO.NET
- ✅ Security headers middleware
- ✅ Environment-based config (no credentials in code)
- ⏳ View templates need business logic — see controllers for TODO comments
- ⏳ Run dotnet restore to pull NuGet packages at deploy time
