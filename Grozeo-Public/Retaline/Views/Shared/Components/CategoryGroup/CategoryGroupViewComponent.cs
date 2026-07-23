using Microsoft.AspNetCore.Mvc;
using Retaline.Core.BusinessModel.Catalog;
using System.Collections.Generic;

namespace Retaline.Web.Views.Shared.Components.CategoryGroups
{
    public class CategoryGroupViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke(List<Retaline.Core.BusinessModel.Catalog.CategoryData> groupCategories)//List<CategoryGroup> groupCategories)
        {
            return View("GroupTile", groupCategories);
        }
    }
}
