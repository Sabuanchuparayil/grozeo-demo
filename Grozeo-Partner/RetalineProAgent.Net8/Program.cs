using Microsoft.AspNetCore.Authentication.Cookies;
using Microsoft.AspNetCore.HttpOverrides;
using RetalineProAgent.Services;

var builder = WebApplication.CreateBuilder(args);

builder.Services.Configure<ForwardedHeadersOptions>(options =>
{
    options.ForwardedHeaders = ForwardedHeaders.XForwardedFor | ForwardedHeaders.XForwardedProto;
    options.KnownNetworks.Clear();
    options.KnownProxies.Clear();
});

// Auth cookie and session idle timeout are aligned (8 h) so an authenticated user
// does not retain a valid cookie while server-side session state has expired.
var authIdleTimeout = TimeSpan.FromHours(8);

builder.Services.AddAuthentication(CookieAuthenticationDefaults.AuthenticationScheme)
    .AddCookie(options =>
    {
        options.LoginPath     = "/Account/Account/Login";
        options.LogoutPath    = "/Account/Account/Logout";
        options.AccessDeniedPath = "/Account/Account/AccessDenied";
        options.ExpireTimeSpan   = authIdleTimeout;
        options.SlidingExpiration = true;
        options.Cookie.HttpOnly   = true;
        // SameAsRequest allows cookies behind a reverse proxy that terminates TLS
        // when ForwardedHeaders is configured; set Always once HTTPS is guaranteed end-to-end.
        options.Cookie.SecurePolicy = CookieSecurePolicy.SameAsRequest;
        options.Cookie.SameSite   = SameSiteMode.Strict;
        options.Cookie.Name       = "GrozeoPartner.Auth";
    });

builder.Services.AddAuthorization(options =>
{
    options.AddPolicy("SuperAdmin",   p => p.RequireRole("SuperAdmin"));
    options.AddPolicy("TenantAdmin",  p => p.RequireRole("TenantAdmin", "SuperAdmin"));
    options.AddPolicy("FinanceUser",  p => p.RequireRole("Finance", "SuperAdmin"));
    options.AddPolicy("SupportAgent", p => p.RequireRole("Support", "SuperAdmin"));
    options.AddPolicy("MarketingUser", p => p.RequireRole("Marketing", "SuperAdmin"));
});

builder.Services.AddControllersWithViews();
builder.Services.AddSession(options =>
{
    options.IdleTimeout = authIdleTimeout;
    options.Cookie.HttpOnly = true;
    options.Cookie.IsEssential = true;
    options.Cookie.SecurePolicy = CookieSecurePolicy.SameAsRequest;
});

var port = Environment.GetEnvironmentVariable("PORT") ?? "8082";
builder.WebHost.UseUrls($"http://0.0.0.0:{port}");

builder.Services.AddHttpContextAccessor();
builder.Services.AddHttpClient();
builder.Services.AddScoped<IDataService,    DataService>();
builder.Services.AddScoped<IUserService,    UserService>();
builder.Services.AddScoped<ISettlementService, SettlementService>();
builder.Services.AddScoped<IReportService,  ReportService>();
builder.Services.AddScoped<IProductService, ProductService>();
builder.Services.AddScoped<IOrderService,   OrderService>();
builder.Services.AddScoped<IFinanceService, FinanceService>();
builder.Services.AddScoped<IExcelExportService, ExcelExportService>();
builder.Services.AddAntiforgery(opts => opts.HeaderName = "X-CSRF-TOKEN");

var app = builder.Build();

app.UseForwardedHeaders();

if (!app.Environment.IsDevelopment())
{
    app.UseExceptionHandler("/Home/Error");
    app.UseHsts();
    app.UseHttpsRedirection();
}
app.UseStaticFiles();
app.Use(async (ctx, next) =>
{
    ctx.Response.Headers.Append("X-Content-Type-Options", "nosniff");
    ctx.Response.Headers.Append("X-Frame-Options",        "SAMEORIGIN");
    ctx.Response.Headers.Append("X-XSS-Protection",       "1; mode=block");
    if (!app.Environment.IsDevelopment() && ctx.Request.IsHttps)
    {
        ctx.Response.Headers.Append("Strict-Transport-Security", "max-age=31536000; includeSubDomains");
    }
    ctx.Response.Headers.Remove("Server");
    await next();
});

app.UseRouting();
app.UseSession();
app.UseAuthentication();
app.UseAuthorization();

app.MapControllerRoute("areas",   "{area:exists}/{controller=Dashboard}/{action=Index}/{id?}");
app.MapControllerRoute("default", "{controller=Home}/{action=Index}/{id?}");

app.Run();
