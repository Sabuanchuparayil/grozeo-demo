using Microsoft.AspNetCore.Mvc;

namespace Retaline.Web.Views.Shared.Components.Logo
{
	public class BusinessCategories : ViewComponent
	{
		public IViewComponentResult Invoke()
		{
			return View("BusinessCategories");
		}
	}
}
