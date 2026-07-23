namespace Retaline.Core.ViewModel.Product
{
    public class ProductViewModel
    {
        public int Id { get; set; }
        public int GroupId { get; set; }
        public int Quantity { get; set; }
        public int ProductId { get; set; }
        public string ProductName { get; set; }
        public string ProductImage { get; set; }
        public int BranchId { get; set; }
        public int? BranchTypeId { get; set; }
        public double? ActualPrice { get; set; }
        public double? SellingPrice { get; set; }
        public string Varient { get; set; }
        public string CategoryName { get; set; }
        public string Source { get; set; }
    }
}
