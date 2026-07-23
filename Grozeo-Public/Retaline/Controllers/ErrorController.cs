using Microsoft.AspNetCore.Mvc;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Retaline.Web.Controllers
{
    public class ErrorController : Controller
    {
        public IActionResult Index()
        {
            return View(0);
        }

        [Route("Error/{statusCode}")]
        public IActionResult HttpStatusCodeHandler(int statusCode)
        {
            return View("Index", statusCode);
        }
    }
}
