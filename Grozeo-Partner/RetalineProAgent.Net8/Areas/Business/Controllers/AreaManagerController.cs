using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using RetalineProAgent.Services;

namespace RetalineProAgent.Areas.Business.Controllers;

[Area("Business")]
[Authorize(Policy = "TenantAdmin")]
public class AreaManagerController : Controller
{
    private readonly IDataService _db;
    private readonly ILogger<AreaManagerController> _logger;

    public AreaManagerController(IDataService db, ILogger<AreaManagerController> logger)
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

    public async Task<IActionResult> Assign(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }
}
