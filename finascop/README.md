# finascop

Finance data entry and audit microservice for Grozeo accounting operations.

## Overview
Azure Function / Worker Service that handles financial data entry, transaction processing, ledger creation, cost centre entries, and daily sales reporting for the Grozeo platform.

## Tech Stack
- **Language:** C# / .NET
- **Database:** SQL Server (via connection string from environment)
- **Hosting:** Azure Functions / App Service

## Key Services
| File | Description |
|------|-------------|
| `DataEntry.cs` | Core financial transaction entry |
| `TransactionEntry.cs` | Transaction processing |
| `CreateGroupLedger.cs` | Group ledger management |
| `CreateTenantLedger.cs` | Tenant-specific ledger creation |
| `CostCentreEntry.cs` | Cost centre accounting entries |
| `DailySalesReport.cs` | Daily sales aggregation |
| `FinascopAudit.cs` | Audit trail management |
| `DataService.cs` | Database access layer (parameterized queries) |

## Setup
```bash
# Set environment variable (required)
export dbconnection="Server=your-server;Database=your-db;User Id=your-user;Password=your-password;"

# Build
dotnet restore
dotnet build

# Run
dotnet run
```

## Security Notes
- `DataService.cs` reads connection string from `dbconnection` environment variable — never hardcode it
- All SQL queries use parameterized statements via `FillParams()`

## License
Proprietary — Grozeo International
