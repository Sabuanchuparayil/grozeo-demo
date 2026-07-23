using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using RetalineProAgent.Services;

namespace RetalineProAgent.Areas.Tenant.Controllers;

[Area("Tenant")]
[Authorize(Policy = "TenantAdmin")]
public class OrderController : Controller
{
    private readonly IDataService _db;
    private readonly ILogger<OrderController> _logger;

    public OrderController(IDataService db, ILogger<OrderController> logger)
    {
        _db = db;
        _logger = logger;
    }

    public async Task<IActionResult> Index(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Details(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Cancel(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Hold(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Returns(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> BOD(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }
}
