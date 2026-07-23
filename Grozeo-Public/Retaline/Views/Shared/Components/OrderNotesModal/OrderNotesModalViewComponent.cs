using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Configuration;
using Retaline.Core.BusinessModel.Home;
using System.Collections.Generic;


namespace Retaline.Web.Views.Shared.Components.OrderNotesModal
{
    public class OrderNotesModalViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke()
        {
            return View();
        }
    }
}

