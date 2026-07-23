using Retaline.Core.BusinessModel.Catalog;
using System.Collections.Generic;

namespace Retaline.Core.BusinessModel.ProductDetails
{
    public class ProductDetailsModel
    {
        public Product Product { get; set; } = new Product();
        public List<Product> SimilarItems { get; set; } = new List<Product>();
        public CategoryProducts CategoryProducts { get; set; } = new CategoryProducts();
        public Product MasterData { get; set; }
        public int BranchId { get; set; }
        public int BranchTypeId { get; set; }
        public string Name { get; set; }
        public string Variant { get; set; }
        public string CategoryName { get; set; }
        public string ReturnDetails { get; set; }
    }
}
