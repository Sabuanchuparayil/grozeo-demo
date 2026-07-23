namespace Retaline.Core.ViewModel.Cart
{
    public class CartItemViewModel
    {
        public int ProductId { get; set; }
        public int GroupId { get; set; }
        public string ProductName { get; set; }
        public string ProductImage { get; set; }
        public string CartOrderQty { get; set; }
    }
}
