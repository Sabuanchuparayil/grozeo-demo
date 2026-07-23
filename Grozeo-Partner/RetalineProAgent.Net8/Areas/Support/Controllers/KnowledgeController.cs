using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using RetalineProAgent.Services;

namespace RetalineProAgent.Areas.Support.Controllers;

[Area("Support")]
[Authorize(Policy = "TenantAdmin")]
public class KnowledgeController : Controller
{
    private readonly IDataService _db;
    private readonly ILogger<KnowledgeController> _logger;

    public KnowledgeController(IDataService db, ILogger<KnowledgeController> logger)
    {
        _db = db;
        _logger = logger;
    }

    public async Task<IActionResult> Index(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Articles(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> FAQ(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }
}
