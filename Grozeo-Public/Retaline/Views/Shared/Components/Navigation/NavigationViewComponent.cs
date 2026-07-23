using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Configuration;
using Retaline.Core.Services.Catalog;
using Retaline.Core.ViewModel.Home;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Retaline.Web.Views.Shared.Components.Navigation
{
    public class NavigationViewComponent : ViewComponent
    {
        private readonly ICatalogService _catalogService;
        public NavigationViewComponent(ICatalogService catalogService)
        {
            _catalogService = catalogService;
        }
        public IViewComponentResult Invoke() //(HomeDetailsViewModel details)
        {
            var catalog = _catalogService.GetCatalog().Result;
            
            return View("Navigation", catalog);
        }
    }
}
