using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using RetalineProAgent.Services;

namespace RetalineProAgent.Areas.Tenant.Controllers;

[Area("Tenant")]
[Authorize(Policy = "TenantAdmin")]
public class ProductController : Controller
{
    private readonly IDataService _db;
    private readonly ILogger<ProductController> _logger;

    public ProductController(IDataService db, ILogger<ProductController> logger)
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

    public async Task<IActionResult> Delete(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> BulkUpload(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }

    public async Task<IActionResult> Pricing(int? id)
    {
        // TODO: Load data via injected service
        return View();
    }
}
