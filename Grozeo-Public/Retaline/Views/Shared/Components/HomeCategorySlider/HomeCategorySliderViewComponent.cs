using Microsoft.AspNetCore.Mvc;
using Retaline.Core.BusinessModel.Home;
using System.Collections.Generic;


namespace Retaline.Web.Views.Shared.Components.HomeBanner
{
    public class HomeCategorySliderViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke(List<HomeValue> homeValues)
        {
            return View("HomeCategorySlider", homeValues);
        }
    }
}
