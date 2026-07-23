using System.Collections.Generic;

namespace Retaline.Core.ViewModel.Cart
{
    public class CartViewModel
    {
        public int CartId { get; set; }
        public double TotalDiscount { get; set; } = 0;
        public double ActualPrice { get; set; } = 0;
        public double SalesPrice { get; set; } = 0;
        public List<CartItemViewModel> CartItems { get; set; } = new List<CartItemViewModel>();
        public int TotalItems { get; set; } = 0;
        public int RemainingItems { get; set; } = 0;
    }
}
