using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using RetalineProAgent.Services;

namespace RetalineProAgent.Areas.Fleet.Controllers;

[Area("Fleet")]
[Authorize(Policy = "TenantAdmin")]
public class VehicleController : Controller
{
    private readonly IDataService _db;
    private readonly ILogger<VehicleController> _logger;

    public VehicleController(IDataService db, ILogger<VehicleController> logger)
    {
        _db = db;
        _logger = logger;
    }

    public async Task<IActionResult> Index(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Create(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Edit(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> History(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }
}
