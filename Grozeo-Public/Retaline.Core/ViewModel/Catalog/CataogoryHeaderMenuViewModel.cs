using Retaline.Core.BusinessModel.Catalog;
using System.Collections.Generic;

namespace Retaline.Core.ViewModel.Catalog
{
    public class CataogoryHeaderMenuViewModel
    {
        public int MenuIterations { get; set; }
        public List<CategoryData> Categories { get; set; } = new List<CategoryData>();

    }
}
