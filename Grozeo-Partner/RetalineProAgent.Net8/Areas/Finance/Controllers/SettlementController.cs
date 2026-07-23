using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using RetalineProAgent.Services;

namespace RetalineProAgent.Areas.Finance.Controllers;

[Area("Finance")]
[Authorize(Policy = "FinanceUser")]
public class SettlementController : Controller
{
    private readonly IDataService _db;
    private readonly ILogger<SettlementController> _logger;

    public SettlementController(IDataService db, ILogger<SettlementController> logger)
    {
        _db = db;
        _logger = logger;
    }

    public async Task<IActionResult> Index(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Apply(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Details(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Download(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Passbook(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }
}
