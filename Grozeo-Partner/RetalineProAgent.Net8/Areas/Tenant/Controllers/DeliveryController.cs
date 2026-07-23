using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using RetalineProAgent.Services;

namespace RetalineProAgent.Areas.Tenant.Controllers;

[Area("Tenant")]
[Authorize(Policy = "TenantAdmin")]
public class DeliveryController : Controller
{
    private readonly IDataService _db;
    private readonly ILogger<DeliveryController> _logger;

    public DeliveryController(IDataService db, ILogger<DeliveryController> logger)
    {
        _db = db;
        _logger = logger;
    }

    public async Task<IActionResult> Index(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Slots(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Rules(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Staff(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> JobConfirm(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }
}
