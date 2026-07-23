using Microsoft.AspNetCore.Mvc;
//using Retaline.Core.BusinessModel.Home;
using Retaline.Core.BusinessModel.Catalog;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Retaline.Web.Views.Shared.Components.HomeBrands
{
    public class ItemMasterViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke(List<Product> item)
        {
            return View(item);
        }
    }
}
