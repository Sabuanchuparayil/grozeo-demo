using Retaline.Core.BusinessModel.Catalog;
using Retaline.Core.BusinessModel.Home;
using Retaline.Core.ViewModel.Cart;

namespace Retaline.Core.ViewModel.Home
{
    public class HeaderAndFooterViewModel
    {
        public HomeRoot HomeRoot { get; set; } = new HomeRoot();
        public CatalogRoot Catalog { get; set; } = new CatalogRoot();
        public int CartCount { get; set; }
        public CartViewModel Cart { get; set; } = new CartViewModel();
    }
}
