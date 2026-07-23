using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using RetalineProAgent.Services;

namespace RetalineProAgent.Areas.Admin.Controllers;

[Area("Admin")]
[Authorize(Policy = "SuperAdmin")]
public class SystemController : Controller
{
    private readonly IDataService _db;
    private readonly ILogger<SystemController> _logger;

    public SystemController(IDataService db, ILogger<SystemController> logger)
    {
        _db = db;
        _logger = logger;
    }

    public async Task<IActionResult> Jobs(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Logs(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Settings(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }
}
