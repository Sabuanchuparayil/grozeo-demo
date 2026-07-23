using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Configuration;
using Retaline.Core.BusinessModel.Home;
using System.Collections.Generic;


namespace Retaline.Web.Views.Shared.Components.AgeVerificationModal
{
    public class AgeVerificationModalViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke()
        {
            return View();
        }
    }
}

