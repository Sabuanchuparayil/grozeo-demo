using Microsoft.AspNetCore.Mvc;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Retaline.Web.Views.Shared.Components.Logo
{
    public class LogoViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke(bool isPwa=false)
        {
            return View("Logo", isPwa);
        }
    }
}
