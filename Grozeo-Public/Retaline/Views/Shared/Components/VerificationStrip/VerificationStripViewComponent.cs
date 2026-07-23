using Microsoft.AspNetCore.Mvc;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Retaline.Web.Views.Shared.Components.VerificationStrip
{
    public class VerificationStripViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke()
        {
            return View("VerificationStrip");
        }
    }
}
