using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using RetalineProAgent.Services;

namespace RetalineProAgent.Areas.Operation.Controllers;

[Area("Operation")]
[Authorize(Policy = "TenantAdmin")]
public class MonitoringController : Controller
{
    private readonly IDataService _db;
    private readonly ILogger<MonitoringController> _logger;

    public MonitoringController(IDataService db, ILogger<MonitoringController> logger)
    {
        _db = db;
        _logger = logger;
    }

    public async Task<IActionResult> PackingDelays(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> DeliveryDelays(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> LiveOrders(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }
}
