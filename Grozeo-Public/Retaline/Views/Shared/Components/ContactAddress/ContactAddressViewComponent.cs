using Microsoft.AspNetCore.Mvc;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Retaline.Web.Views.Shared.Components.ContactAddress
{
    public class ContactAddressViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke(bool isFooter = false)
        {
            return View("ContactAddress", isFooter);
        }
    }
}
