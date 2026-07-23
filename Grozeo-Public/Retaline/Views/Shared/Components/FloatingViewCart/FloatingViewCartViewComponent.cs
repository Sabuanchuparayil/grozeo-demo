using Microsoft.AspNetCore.Mvc;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Retaline.Web.Views.Shared.Components.FloatingViewCart
{
    public class FloatingViewCartViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke()
        {
            return View("FloatingViewCart");
        }
    }
}
