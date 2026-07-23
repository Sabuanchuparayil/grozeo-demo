using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using RetalineProAgent.Services;

namespace RetalineProAgent.Areas.Finance.Controllers;

[Area("Finance")]
[Authorize(Policy = "FinanceUser")]
public class VoucherController : Controller
{
    private readonly IDataService _db;
    private readonly ILogger<VoucherController> _logger;

    public VoucherController(IDataService db, ILogger<VoucherController> logger)
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

    public async Task<IActionResult> Details(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> AutoPosting(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }
}
