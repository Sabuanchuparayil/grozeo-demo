using Microsoft.AspNetCore.Mvc;
using RetalineProAgent.Models;
using System.Diagnostics;

namespace RetalineProAgent.Controllers;

public class HomeController : Controller
{
    public IActionResult Index() =>
        RedirectToAction("Login", "Account", new { area = "Account" });

    [ResponseCache(Duration = 0, Location = ResponseCacheLocation.None, NoStore = true)]
    public IActionResult Error() =>
        View(new ErrorViewModel
        {
            RequestId = Activity.Current?.Id ?? HttpContext.TraceIdentifier
        });
}
