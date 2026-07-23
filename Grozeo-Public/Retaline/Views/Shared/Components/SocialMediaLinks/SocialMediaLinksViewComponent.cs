using Microsoft.AspNetCore.Mvc;
using Retaline.Core.Services.Catalog;

namespace Retaline.Web.Views.Shared.Components.HomeBrands
{
    public class SocialMediaLinksViewComponent : ViewComponent
    {

        public SocialMediaLinksViewComponent()
        {
        }

        public IViewComponentResult Invoke()
        {
            return View();
        }
    }
}
