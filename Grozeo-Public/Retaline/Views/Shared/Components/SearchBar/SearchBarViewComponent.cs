using Microsoft.AspNetCore.Mvc;

namespace Retaline.Web.Views.Shared.Components.Logo
{
	public class SearchBar : ViewComponent
	{
		public IViewComponentResult Invoke()
		{
			return View();
		}
	}
}
