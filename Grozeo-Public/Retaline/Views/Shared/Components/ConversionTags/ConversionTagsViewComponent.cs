using Microsoft.AspNetCore.Mvc;

namespace Retaline.Web.Views.Shared.Components.ConversionTags
{
    public class ConversionTagsViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke()
        {
            return View("ConversionTags");
        }
    }
}
