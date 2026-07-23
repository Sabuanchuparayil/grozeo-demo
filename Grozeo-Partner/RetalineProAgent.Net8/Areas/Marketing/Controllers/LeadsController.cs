using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using RetalineProAgent.Services;

namespace RetalineProAgent.Areas.Marketing.Controllers;

[Area("Marketing")]
[Authorize(Policy = "MarketingUser")]
public class LeadsController : Controller
{
    private readonly IDataService _db;
    private readonly ILogger<LeadsController> _logger;

    public LeadsController(IDataService db, ILogger<LeadsController> logger)
    {
        _db = db;
        _logger = logger;
    }

    public async Task<IActionResult> Index(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Import(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Assign(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }
}
