using Microsoft.AspNetCore.Mvc;

namespace Retaline.Web.Views.Shared.Components.Logo
{
	public class MoreMenu : ViewComponent
	{
		public IViewComponentResult Invoke(string name)
		{
			return View("MoreMenu",name);
		}
	}
}
