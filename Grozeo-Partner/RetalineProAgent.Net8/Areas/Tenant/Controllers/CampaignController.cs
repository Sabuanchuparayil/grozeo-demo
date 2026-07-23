using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using RetalineProAgent.Services;

namespace RetalineProAgent.Areas.Tenant.Controllers;

[Area("Tenant")]
[Authorize(Policy = "TenantAdmin")]
public class CampaignController : Controller
{
    private readonly IDataService _db;
    private readonly ILogger<CampaignController> _logger;

    public CampaignController(IDataService db, ILogger<CampaignController> logger)
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

    public async Task<IActionResult> Coupon(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Sponsored(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }
}
