using Retaline.Core.BusinessModel.Catalog;
using Retaline.Core.BusinessModel.ProductDetails;
using Retaline.Core.Services.Catalog;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Retaline.Core.Services.ProductDetails
{
    public class ProductDetailsService : IProductDetailsService
    {
        private readonly IProductService _productService;

        public ProductDetailsService(IProductService productService)
        {
            _productService = productService;
        }


        public async Task<ProductDetailsModel> GetProductDetails(int productid, int brid, int brtypeid, string productname, int gid, string keys)
        {
            ProductDetailsModel productDetails = new();
            List<int> keysList = new List<int>();
            if (!string.IsNullOrEmpty(keys))
            {
                string[] keysArray = keys.Split(',');
                keysList = keysArray.Select(int.Parse).ToList();
            }
            if (keysList.Count < 1)
                keysList.Add(productid);

            Product product = await _productService.GetProductDetails(productid, gid, keysList.ToArray(), brid, brtypeid);
            if (product == null)
            {
                // Handle null product case
                return productDetails;
            }

            productDetails.MasterData=product;
            productDetails.Product = product; //.Item.Where(i => i.StitId == productid && i.StitFsiUId == gid).FirstOrDefault();
            productDetails.BranchId = brid;
            if (brid <= 0 && productDetails.Product != null && productDetails.Product.BranchId.HasValue)
                productDetails.BranchId = productDetails.Product.BranchId.Value;
            productDetails.BranchTypeId = (productDetails.BranchTypeId <= 0 && brtypeid > 0 ? brtypeid : productDetails.BranchTypeId); //brtypeid;
            productDetails.ReturnDetails = product.ReturnDetails;
            productDetails.SimilarItems = new List<Product>(); //product.SimilarItems != null ?
                                          //product.SimilarItems.Where(i => i.Item.Where(ii => ii.SellingPrice > 0 || ii.SellingPrice2>0).ToList().Count > 0).ToList() :
                                          //new List<ItemMaster>();
            //try {
            //    if(product.Item != null && product.Item.Count > 1)
            //    {
            //        var similarOtherProducts = product.Item.Where(p=> p != productDetails.Product).ToList();
            //        if(similarOtherProducts != null && similarOtherProducts.Count > 0) {
            //            if (productDetails.SimilarItems == null)
            //                productDetails.SimilarItems = similarOtherProducts;
            //            else 
            //                productDetails.SimilarItems.InsertRange(0, similarOtherProducts);
            //        }
            //    }
            //} catch { }
            try
            {
                for (int i = 0; i < productDetails.SimilarItems.Count; i++)
                {
                    productDetails.SimilarItems[i].Item = productDetails.SimilarItems[i].Item.Where(i => i.SellingPrice > 0 || i.SellingPrice2 > 0).ToList();
                }
            }
            catch { }
            try { 
              if (product.CategoryId != null)
            {
                productDetails.CategoryProducts = await _productService.GetProductsByCategoy(product.CategoryId, 1);
            }
            else
            {
                productDetails.CategoryProducts = new CategoryProducts();
            }
            }
            catch
            {
                productDetails.CategoryProducts = new CategoryProducts(); // Handle error or set a default value
            }

            if (productDetails == null)
                productDetails.CategoryProducts = new CategoryProducts();
            return productDetails;
        }

        public async Task<ProductDetailsModel> GetSimilarAndOtherProducts(int productid, int brid, int brtypeid, int catid, int gid, string keys)
        {
            List<int> keysList = new List<int>();
            if (!string.IsNullOrEmpty(keys))
            {
                string[] keysArray = keys.Split(',');
                keysList = keysArray.Select(int.Parse).ToList();
            }
            if (keysList.Count < 1)
                keysList.Add(productid);

            ProductDetailsModel productDetails = new();

            try
            {
                var similarproducts = await _productService.GetSimilarProducts(productid, gid, keysList.ToArray(), brid, brtypeid);
                if (similarproducts != null && similarproducts.Count > 0)
                {
                    List<Product> lstSimilarProducts = similarproducts.Where(ii => ii.SellingPrice > 0 || ii.SellingPrice2 > 0).ToList();//.SelectMany(p => p.Item.Where(ii => ii.SellingPrice > 0 || ii.SellingPrice2 > 0)).ToList();
                    if (lstSimilarProducts != null && lstSimilarProducts.Count > 0)
                        productDetails.SimilarItems = lstSimilarProducts;
                }
            }
            catch { }

            try
            {
                var catproducts = await _productService.GetLikeProducts(productid, gid, keysList.ToArray(), brid, brtypeid);
                productDetails.CategoryProducts = new CategoryProducts { Products = catproducts };
            }
            catch { }
            try
            {
                if (productDetails.CategoryProducts == null && catid > 0)
                    productDetails.CategoryProducts = await _productService.GetProductsByCategoy(catid, 1);
            }
            catch { }

            if (productDetails == null)
                productDetails = new ProductDetailsModel(); //.CategoryProducts = new CategoryProducts();
            return productDetails;

        }

        public async Task<List<Product>> GetProductVariants(int productid, int gid, int brid=-1, int brtypeid=1)
        {
            try
            {
                var productVariants = await _productService.GetProductVarants(productid, gid, brid, brtypeid);
                return productVariants;
            }
            catch { }
            return default;
        }

    }
}
