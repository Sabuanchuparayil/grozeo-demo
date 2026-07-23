using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using RetalineProAgent.Services;

namespace RetalineProAgent.Areas.Business.Controllers;

[Area("Business")]
[Authorize(Policy = "TenantAdmin")]
public class CRMController : Controller
{
    private readonly IDataService _db;
    private readonly ILogger<CRMController> _logger;

    public CRMController(IDataService db, ILogger<CRMController> logger)
    {
        _db = db;
        _logger = logger;
    }

    public async Task<IActionResult> Prospects(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Retailers(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Leads(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Contacts(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Followups(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }
}
