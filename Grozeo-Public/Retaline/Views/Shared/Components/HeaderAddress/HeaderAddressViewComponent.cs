using Microsoft.AspNetCore.Mvc;
using Retaline.Core.BusinessModel.Catalog;
using Retaline.Core.BusinessModel.UserDetails;

namespace Retaline.Web.Views.Shared.Components.Logo
{
	public class HeaderAddress : ViewComponent
	{
		public IViewComponentResult Invoke(Retaline.Core.BusinessModel.UserDetails.User curUser)
        {
			return View("HeaderAddress",curUser);
		}
	}
}
