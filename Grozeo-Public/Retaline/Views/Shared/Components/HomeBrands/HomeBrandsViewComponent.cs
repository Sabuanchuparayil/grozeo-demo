using Microsoft.AspNetCore.Mvc;
using Retaline.Core.Services.Catalog;

namespace Retaline.Web.Views.Shared.Components.HomeBrands
{
    public class HomeBrandsViewComponent : ViewComponent
    {
        private readonly ICatalogService _catalogService;

        public HomeBrandsViewComponent(ICatalogService catalogService)
        {
            _catalogService = catalogService;
        }

        public IViewComponentResult Invoke()
        {
            var brandDetails = _catalogService.GetBrandsForFooterMenu();
            return View(brandDetails.Result);
        }
    }
}
