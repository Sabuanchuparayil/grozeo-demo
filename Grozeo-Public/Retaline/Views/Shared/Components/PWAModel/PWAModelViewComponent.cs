using Microsoft.AspNetCore.Mvc;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Retaline.Web.Views.Shared.Components.PWA
{
    public class PWAModelViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke()
        {
            return View("PWAModel");
        }
    }
}
