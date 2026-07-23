using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using RetalineProAgent.Services;

namespace RetalineProAgent.Areas.Tenant.Controllers;

[Area("Tenant")]
[Authorize(Policy = "TenantAdmin")]
public class DashboardController : Controller
{
    private readonly IDataService _db;
    private readonly ILogger<DashboardController> _logger;

    public DashboardController(IDataService db, ILogger<DashboardController> logger)
    {
        _db = db;
        _logger = logger;
    }

    public async Task<IActionResult> Index(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Analytics(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> StoreCompletion(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }
}
