using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using RetalineProAgent.Services;

namespace RetalineProAgent.Areas.Finance.Controllers;

[Area("Finance")]
[Authorize(Policy = "FinanceUser")]
public class ReportController : Controller
{
    private readonly IDataService _db;
    private readonly ILogger<ReportController> _logger;

    public ReportController(IDataService db, ILogger<ReportController> logger)
    {
        _db = db;
        _logger = logger;
    }

    public async Task<IActionResult> Sales(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> GST(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> TDS(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> TCS(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> ProfitLoss(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> BalanceSheet(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> TrialBalance(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Daybook(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }
}
