using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using RetalineProAgent.Services;

namespace RetalineProAgent.Areas.Marketing.Controllers;

[Area("Marketing")]
[Authorize(Policy = "MarketingUser")]
public class CampaignsController : Controller
{
    private readonly IDataService _db;
    private readonly ILogger<CampaignsController> _logger;

    public CampaignsController(IDataService db, ILogger<CampaignsController> logger)
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

    public async Task<IActionResult> Analytics(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }
}
