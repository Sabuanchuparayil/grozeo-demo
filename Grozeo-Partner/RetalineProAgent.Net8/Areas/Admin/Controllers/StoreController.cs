using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using RetalineProAgent.Services;

namespace RetalineProAgent.Areas.Admin.Controllers;

[Area("Admin")]
[Authorize(Policy = "SuperAdmin")]
public class StoreController : Controller
{
    private readonly IDataService _db;
    private readonly ILogger<StoreController> _logger;

    public StoreController(IDataService db, ILogger<StoreController> logger)
    {
        _db = db;
        _logger = logger;
    }

    public async Task<IActionResult> Index(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Domains(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Config(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }
}
