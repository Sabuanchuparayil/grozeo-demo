# Grozeo-Partner: ASP.NET Framework 4.7.2 → .NET 8 Migration Guide

## Overview

This is a **rewrite migration**, not an in-place upgrade. ASP.NET Web Forms
(the technology Grozeo-Partner uses: `.aspx`, `.aspx.cs`, `Global.asax`) does
not run on .NET 8. The target is **ASP.NET Core 8 MVC with Razor Views**.

Estimated effort: 4–6 weeks with one senior .NET developer.

---

## What Changes

| Old (.NET Framework 4.7.2) | New (.NET 8) |
|---|---|
| Web Forms (.aspx + code-behind) | Razor Pages or MVC Controllers + Views |
| Global.asax | Program.cs (minimal hosting) |
| Web.config | appsettings.json + environment variables |
| System.Web.HttpContext | Microsoft.AspNetCore.Http.HttpContext |
| HttpSessionState | ISession (distributed via Redis) |
| FormsAuthentication | Cookie authentication middleware |
| SqlConnection (raw ADO.NET) | EF Core 8 + parameterized queries |
| NPOI for Excel | ClosedXML or EPPlus |
| Response.Write | Controller action returns |
| Page_Load events | Controller action methods |

---

## Step 1: Setup New Project

```bash
dotnet new mvc -n RetalineProAgent --framework net8.0
cd RetalineProAgent
```

Copy the provided `RetalineProAgent.csproj`, `Program.cs`, and `appsettings.json`
into the project root.

---

## Step 2: Migrate Global.asax → Program.cs

The provided `Program.cs` replaces `Global.asax` and `Global.asax.cs`.

Key mappings:
- `Application_Start` logic → builder.Services configuration sections
- `Session_Start` → Session middleware in `UseSession()`
- `Application_Error` → `UseExceptionHandler()`
- Custom routes from `RouteConfig.cs` → `app.MapControllerRoute()`

---

## Step 3: Migrate Web.config → appsettings.json + Environment Variables

**Never put real values in appsettings.json.** Use environment variables.

```bash
# Set on Azure App Service:
az webapp config appsettings set --name grozeo-partner \
  --resource-group grozeo-rg \
  --settings \
    ConnectionStrings__DefaultConnection="Server=...;Password=$(AZURE_DB_PASSWORD)" \
    ConnectionStrings__MySqlConnection="Server=...;Pwd=$(MYSQL_PASSWORD)" \
    IMPORT_DB_HOST="your-mysql-host" \
    IMPORT_DB_USER="your-mysql-user" \
    IMPORT_DB_PASS="new-secure-password"
```

---

## Step 4: Migrate Each Section (Priority Order)

### 4.1 Authentication (Login.aspx → AccountController)

```csharp
// OLD: FormsAuthentication.SetAuthCookie(username, rememberMe);
// NEW:
var claims = new List<Claim>
{
    new Claim(ClaimTypes.Name, user.UserName),
    new Claim(ClaimTypes.Role, user.RoleName),
    new Claim("UserId", user.UserId.ToString()),
    new Claim("BranchId", user.BranchId.ToString())
};
var identity = new ClaimsIdentity(claims, CookieAuthenticationDefaults.AuthenticationScheme);
await HttpContext.SignInAsync(
    CookieAuthenticationDefaults.AuthenticationScheme,
    new ClaimsPrincipal(identity),
    new AuthenticationProperties { IsPersistent = rememberMe }
);
```

### 4.2 Each .aspx Page → Controller + View

Convert each page systematically. Example for Tenant/Orders:

**OLD (OrderDetails.aspx.cs):**
```csharp
protected void Page_Load(object sender, EventArgs e)
{
    if (!IsPostBack)
    {
        var orderId = Request.QueryString["orderId"];
        var orders = DataService.GetDataTable($"SELECT * FROM orders WHERE id={orderId}");
        // bind to GridView
    }
}
```

**NEW (Tenant/OrderController.cs):**
```csharp
[Authorize(Policy = "TenantAdmin")]
public class OrderController : Controller
{
    private readonly IDataService _db;
    public OrderController(IDataService db) => _db = db;

    public async Task<IActionResult> Details(int orderId)
    {
        // Parameterized query via EF Core or Dapper
        var order = await _db.GetOrderByIdAsync(orderId);
        if (order == null) return NotFound();
        return View(order);
    }
}
```

### 4.3 Session Migration

```csharp
// OLD:
Session["admin"] = userObject;
var user = (AdminUser)Session["admin"];

// NEW (via ISession with JSON serialization):
HttpContext.Session.SetString("admin", JsonSerializer.Serialize(userObject));
var user = JsonSerializer.Deserialize<AdminUser>(HttpContext.Session.GetString("admin") ?? "{}");

// Or better — use Claims from the auth cookie (already stored at login):
var userId = User.FindFirst("UserId")?.Value;
var branchId = User.FindFirst("BranchId")?.Value;
```

### 4.4 DataService Migration

The `RetalineProAgent.Core/Services/DataService.cs` already uses parameterized
queries with `FillParams()`. It only needs the connection string source updated:

```csharp
// OLD:
private static string ConnectionString =>
    ConfigurationManager.ConnectionStrings["localConnection"].ConnectionString;

// NEW:
private readonly IConfiguration _config;
public DataService(IConfiguration config) => _config = config;
private string ConnectionString =>
    _config.GetConnectionString("DefaultConnection")
    ?? throw new InvalidOperationException("DefaultConnection not configured");
```

### 4.5 MasterDataImport Migration (SECURITY FIX INCLUDED)

The hardcoded credentials fix from the security audit is already applied.
In .NET 8, read from environment:

```csharp
// Already fixed in security audit — just update the config reading:
var dbHost = Environment.GetEnvironmentVariable("IMPORT_DB_HOST")
    ?? _config["Database:ImportHost"]
    ?? throw new InvalidOperationException("IMPORT_DB_HOST not set");
```

### 4.6 Excel Export (NPOI → ClosedXML)

```csharp
// OLD (NPOI):
var workbook = new XSSFWorkbook();
var sheet = workbook.CreateSheet("Data");
var row = sheet.CreateRow(0);
row.CreateCell(0).SetCellValue("Header");

// NEW (ClosedXML):
using var workbook = new XLWorkbook();
var sheet = workbook.Worksheets.Add("Data");
sheet.Cell(1, 1).Value = "Header";
// Populate data...
using var stream = new MemoryStream();
workbook.SaveAs(stream);
return File(stream.ToArray(), "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", "export.xlsx");
```

---

## Step 5: Area Structure

Preserve the portal sections as ASP.NET Core Areas:

```
Areas/
├── Tenant/
│   ├── Controllers/
│   ├── Views/
│   └── TenantAreaRegistration.cs
├── Finance/
├── Business/
├── Operation/
├── Sales/
├── Marketing/
├── Fleet/
├── Support/
└── Admin/
```

```csharp
// Each area controller gets the [Area] attribute:
[Area("Tenant")]
[Authorize(Policy = "TenantAdmin")]
public class OrderController : Controller { ... }
```

---

## Step 6: Deploy to Azure App Service (.NET 8 Linux)

```bash
# Publish
dotnet publish -c Release -o ./publish

# Deploy via Azure CLI
az webapp deploy --resource-group grozeo-rg \
  --name grozeo-partner \
  --src-path ./publish \
  --type zip
```

```yaml
# Or GitHub Actions CI/CD:
- name: Deploy to Azure
  uses: azure/webapps-deploy@v2
  with:
    app-name: grozeo-partner
    publish-profile: ${{ secrets.AZURE_WEBAPP_PUBLISH_PROFILE }}
    package: ./publish
```

---

## Priority Order for Migration

1. **Authentication + Login** (foundation for everything else)
2. **Tenant/Orders** (highest daily usage)
3. **Finance/Settlements** (business-critical)
4. **Tenant/Inventory** (core merchant workflow)
5. **Business/CRM** (sales team usage)
6. **All remaining sections** (can run in parallel once foundation is solid)

---

## Testing Checklist

- [ ] Login and session persistence works
- [ ] All page redirects and authorization policies correct
- [ ] DataService parameterized queries return correct data
- [ ] Excel exports download correctly
- [ ] Settlement reports calculate correctly
- [ ] Credential environment variables read correctly (no hardcoded values)
- [ ] Security headers present in responses
- [ ] HTTPS enforced (HSTS header present)
