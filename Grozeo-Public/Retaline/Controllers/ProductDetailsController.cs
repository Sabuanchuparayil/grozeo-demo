using Microsoft.AspNetCore.Mvc;
using Retaline.Core.BusinessModel.Cart;
using Retaline.Core.BusinessModel.Catalog;
using Retaline.Core.BusinessModel.ProductDetails;
using Retaline.Core.Services.ProductDetails;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Net.NetworkInformation;
using System.Threading.Tasks;

namespace Retaline.Web.Controllers
{
    public class ProductDetailsController : Controller
    {
        private readonly IProductDetailsService _productDetailsService;

        public ProductDetailsController(IProductDetailsService productDetailsService)
        {
            _productDetailsService = productDetailsService;
        }

        [Route("catalog/{productid}/{productname?}")]
        //[Route("pd/{brid}/{brtypeid}/{productid}/{gid}/{*productname}")]
        //[Route("pd/{brid}/{brtypeid}/{productid}/{gid}/")]
        [Route("pd/{productid}/{brid}/{brtypeid}/{*productname}")]
        [Route("pd/{productid}/{productname?}")]
        //[Route("pd/{productid}/{gid}/{productname}")]
        public async Task<IActionResult> Index(int productid, int brid = -1, int brtypeid = 1, string productname = "", int gid = 0, string keys = "")
        {
            ProductDetailsModel productModel = await _productDetailsService.GetProductDetails(productid, brid, brtypeid, productname, gid, keys);
            return View(productModel);
        }

        [Route("similarproducts/{productid}/{brid}/{brtypeid}/{gid}/{catid}")]
        public async Task<IActionResult> SimilarProducts(int productid, int brid = -1, int brtypeid = 1, int catid = -1, int gid = 0, string keys = "")
        {
            ProductDetailsModel productModel = await _productDetailsService.GetSimilarAndOtherProducts(productid, brid, brtypeid, catid, gid, keys);
            return View(productModel);
        }

        /// <summary>
        /// Groped products list.
        /// Separate the list in to variants and size (quantity). Srike out the size if no variant of the current item has the particular size.
        /// </summary>
        /// <param name="productid">stit_id</param>
        /// <param name="variantGroupId">group id</param>
        /// <returns>json object </returns>
        [Route("productvariants/{productid}/{variantGroupId}")]
        public async Task<IActionResult> ProductVariants(int productid, int variantGroupId)
        {
            // intem in the selected group.
            List<Core.BusinessModel.Catalog.Product> productVariants = await _productDetailsService.GetProductVariants(productid, variantGroupId);
            // current item
            Product curProduct = productVariants.Where(p => p != null).OrderByDescending(p => p.StitId == productid && p.VariantGroupId == variantGroupId ? 2 : 0).FirstOrDefault();

            // group the items list in to size (quantity) group. The product quantity and unit combined will be the group key.
            var sizelist = productVariants.Where(p => p != null).Select(p=> p).
                GroupBy(j => (new { size = j.Quantity, unit = j.ProductUnit.Name }), i => i);//.Select(k => k.FirstOrDefault());

            // group the items list in to variants group. The product name and variant name combined will be the group key.
            var variantlist = productVariants.Where(p => p != null).
                GroupBy(j => (new { pname = j.ProductName, variant = j.ProductVariant }), i => i);//.Select(k => k.FirstOrDefault());

            // variant data object for json response. isCurrent used for sorting to keep the current item selected when taking the first item from list.
            var otherGroupData = variantlist.Select(k => k.Select(i =>
                new {
                    id = i.StitId, name = i.SKU, groupid = i.GroupId, brid = i.BranchId, isCurrent = (i.StitId == productid), brtype = i.BranchTypeId,
                    order = (i.Quantity == curProduct.Quantity && i.ProductUnit.Name == curProduct.ProductUnit.Name ? 2 : 1),
                    imgurl = (string.IsNullOrEmpty(i.MainImage?.FirstOrDefault()?.ThumbUrl) ?
                      i.AdditionalImages?.FirstOrDefault()?.ThumbUrl
                        : i.MainImage?.FirstOrDefault()?.ThumbUrl),
                    prodUrl = string.Format("/pd/{0}/{1}/{2}/{3}", i.StitId, i.BranchId, i.BranchTypeId, 
                        Retaline.Web.Service.Common.EncodeUrl(String.IsNullOrEmpty(i.SKU) ? i.PackageName + "-" + i.Quantity : i.SKU))
                }).OrderByDescending(i => i.order)
                .OrderByDescending(i => i.isCurrent).FirstOrDefault());

            // remove current product if no other variants available.
            if (otherGroupData.Count() == 1 && otherGroupData.FirstOrDefault().id == productid)
                otherGroupData = null;

            // size data object for json response.
            var sizeVariantData = sizelist.Select(k => k.Select(i =>
                new {
                    id = i.StitId, name = i.SKU,
                    // get all the stitids from the variants list and check any of the id exists in the current size list.
                    strikeOut = !((k.Any(si => variantlist.Where(vk => vk.Any(vi => vi.StitId == productid))
                                    .SelectMany(vj => vj.Select(vl => vl.StitId)).Any(vj => si.StitId == vj)))),
                    isCurrent = (i.StitId == productid),
                    order = (i.ProductName == curProduct.ProductName && i.ProductVariant == curProduct.ProductVariant ?2 :1), //(p.size == curProduct.Quantity && p.unit == curProduct.ProductUnit.Name)
                    groupid = i.GroupId, brid = i.BranchId, brtype = i.BranchTypeId,
                    unit = String.Format("{0} {1}", i.Quantity, i.ProductUnit.Name),
                    prodUrl = string.Format("/pd/{0}/{1}/{2}/{3}", i.StitId, i.BranchId, i.BranchTypeId, 
                        Retaline.Web.Service.Common.EncodeUrl(String.IsNullOrEmpty(i.SKU) ? i.PackageName + "-" + i.Quantity : i.SKU))
                }).OrderByDescending(i=> i.order)
                .OrderByDescending(i=> i.isCurrent).FirstOrDefault());

            // remove current product size if no other options available.
            if (sizeVariantData.Count() == 1 && sizeVariantData.FirstOrDefault().id == productid)
                sizeVariantData = null;

            return Json(new { status = 1, data = otherGroupData, sizedata = sizeVariantData });
        }
    }
}
