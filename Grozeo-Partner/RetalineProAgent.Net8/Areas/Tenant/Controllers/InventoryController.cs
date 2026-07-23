using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using RetalineProAgent.Services;

namespace RetalineProAgent.Areas.Tenant.Controllers;

[Area("Tenant")]
[Authorize(Policy = "TenantAdmin")]
public class InventoryController : Controller
{
    private readonly IDataService _db;
    private readonly ILogger<InventoryController> _logger;

    public InventoryController(IDataService db, ILogger<InventoryController> logger)
    {
        _db = db;
        _logger = logger;
    }

    public async Task<IActionResult> Index(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> StockIn(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> StockOut(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Transfer(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Barcode(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }
}
