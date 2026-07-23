using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using RetalineProAgent.Services;

namespace RetalineProAgent.Areas.Support.Controllers;

[Area("Support")]
[Authorize(Policy = "TenantAdmin")]
public class TicketController : Controller
{
    private readonly IDataService _db;
    private readonly ILogger<TicketController> _logger;

    public TicketController(IDataService db, ILogger<TicketController> logger)
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

    public async Task<IActionResult> Details(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Resolve(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Escalate(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }
}
