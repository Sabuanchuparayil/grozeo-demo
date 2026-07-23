using Microsoft.AspNetCore.Mvc;
using Retaline.Core.BusinessModel.UserDetails;
using System.Collections.Generic;

namespace Retaline.Web.Views.Shared.Components.CustomerAddress
{
    public class CustomerAddressViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke(List<Address> details)
        {
            return View("Address", details);
        }
    }
}
