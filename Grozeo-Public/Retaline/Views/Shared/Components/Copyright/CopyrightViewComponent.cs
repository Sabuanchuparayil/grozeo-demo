using Microsoft.AspNetCore.Mvc;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Retaline.Web.Views.Shared.Components.Copyright
{
    public class CopyrightViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke(bool isInMenu)
        {
            return View("Copyright", isInMenu);
        }
    }
}
