using Microsoft.AspNetCore.Mvc;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Retaline.Web.Views.Shared.Components.ContactModel
{
    public class ContactModelViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke(Retaline.Core.BusinessModel.UserDetails.User odouser)
        {
            return View("ContactModel", odouser);
        }
    }
}
