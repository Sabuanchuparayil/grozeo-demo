using Microsoft.AspNetCore.Mvc;
using Retaline.Core.BusinessModel.Catalog;
using Retaline.Core.ViewModel.Home;
using System.Collections.Generic;

namespace Retaline.Web.Views.Shared.Components.HomeBrands
{
    public class SuggestedProductsViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke(List<HomePageViewModel> model)
        {
            return View(model);
        }
    }
}
