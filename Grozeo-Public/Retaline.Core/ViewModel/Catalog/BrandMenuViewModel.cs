using Retaline.Core.BusinessModel.Brands;
using Retaline.Core.BusinessModel.Home;
using System.Collections.Generic;

namespace Retaline.Core.ViewModel.Catalog
{
    public class BrandMenuViewModel
    {
        public int MenuIterations { get; set; }
        public List<HomeValue> HomeBrands { get; set; } = new List<HomeValue>();
        public List<Brand> Brands { get; set; }
    }
}
