using Retaline.Core.BusinessModel.Home;
using System.Collections.Generic;

namespace Retaline.Core.ViewModel.Home
{
    public class HomePageViewModel
    {
        public string Type { get; set; }

        public List<HomeValue> Content { get; set; } = new List<HomeValue>();
        public List<Retaline.Core.BusinessModel.Catalog.Product> Products { get; set; } = new List<BusinessModel.Catalog.Product>();

    }
}
