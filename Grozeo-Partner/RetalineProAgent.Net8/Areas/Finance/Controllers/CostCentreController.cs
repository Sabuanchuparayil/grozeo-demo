using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using RetalineProAgent.Services;

namespace RetalineProAgent.Areas.Finance.Controllers;

[Area("Finance")]
[Authorize(Policy = "FinanceUser")]
public class CostCentreController : Controller
{
    private readonly IDataService _db;
    private readonly ILogger<CostCentreController> _logger;

    public CostCentreController(IDataService db, ILogger<CostCentreController> logger)
    {
        _db = db;
        _logger = logger;
    }

    public async Task<IActionResult> Index(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Entry(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Allocation(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Reports(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }
}
