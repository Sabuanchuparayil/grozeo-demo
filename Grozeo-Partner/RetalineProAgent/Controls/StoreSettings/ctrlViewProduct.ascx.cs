using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Configuration;

namespace RetalineProAgent.Controls.StoreSettings
{
    public partial class ctrlViewProduct : Base.BasePartnerUserControl
    {
        public delegate void ParentAddProductHandler(int status);
        public delegate void ParentAddBrandHandler(int status);
        public delegate void ParentMessageHandler(string title, string msg, int type);

        public event ParentAddProductHandler ParentAddProductBinding;
        public event ParentAddBrandHandler ParentAddBrandBinding;
        public event ParentAddProductHandler ParentCancelAddProductBinding;

        public event ParentMessageHandler ParentMessageBinding;

        

        protected void Page_Load(object sender, EventArgs e)
        {

            lblProductNameResult.Text = "";


            if (!IsPostBack)
            {
                    LoadProductInfo();
            }
            
            int brandId = -1;
            if (!String.IsNullOrEmpty(Request.QueryString["brandId"]))
                try { brandId = Convert.ToInt32(Request.QueryString["brandId"]); } catch { brandId = 0; }

            if (brandId > 0)
            {
                DataTable dt = DataServiceMySql.GetDataTable($"SELECT brand_id, brand_name FROM mypha_productbrands WHERE brand_id = {brandId}", Service.UserService.GetAPIConnectionString());
                if (dt == null || dt.Rows.Count <= 0)
                {
                    Common.ShowCustomAlert(this.Page, "Loading failed", "Invalid selection or the record is not existing", false, "/Tenant/BrandProduct");
                    return;
                }

                DataRow da = dt.Rows[0];
                txtSelectedBrand.Text = da["brand_name"].ToString();
            }

            List<KeyValuePair<string, object>> subPrdParams = new List<KeyValuePair<string, object>>();
            subPrdParams.Add(new KeyValuePair<string, object>("subcatId", selSubCat.Text));
            subPrdParams.Add(new KeyValuePair<string, object>("perishable", 1));
            DataTable dtSubPrd = DataServiceMySql.GetDataTable($"SELECT COUNT(*) AS cnt FROM mypha_productsubcategory WHERE isPerishable=@perishable AND sub_category_id=@subcatId", Service.UserService.GetAPIConnectionString(), subPrdParams);
            int subPrdCount = 0;
            if (dtSubPrd != null && dtSubPrd.Rows.Count > 0)
            {
                DataRow da = dtSubPrd.Rows[0];
                subPrdCount = Convert.ToInt32(da["cnt"]);
            }
            if (subPrdCount > 0)
            {
                selDelMode.Items.FindByValue("1").Enabled = false;
                selDelMode.Items.FindByValue("3").Enabled = false;
                selDelMode.SelectedItem.Text = "Direct";
                selDelMode.Enabled = false;
            }
            else
            {
                selDelMode.Items.FindByValue("1").Enabled = true;
                selDelMode.Items.FindByValue("3").Enabled = true;
                selDelMode.Enabled = false;
                selDelMode.Enabled = false;
            }
        }

        

        private void LoadProductInfo()
        {
            int productId = -1;
            if (!String.IsNullOrEmpty(Request.QueryString["id"]))
                try { productId = Convert.ToInt32(Request.QueryString["id"]); } catch { productId = 0; }
            string selectSql = @"SELECT i.stit_itemId, i.stit_itemBarcode, i.stit_GST, i .taxValueId, i.stit_custInitiate, i.stit_itemReturnTime,i.stit_SKU,i.stit_HSNCode, i.stit_GST,i.stit_Description,i.stit_product_variant,
i.pdt_brand,i.product_category,i.stit_quantity,i.stit_long_description,i.stit_itemName,i.stit_HSN_code,i.stit_hsnId, i.stit_category_name,i.stit_brand_name,i.courierDelivery,
i.directDelivery,i.stit_foodtype,i.stit_orgin_country,i.stit_unit,i.stit_qty,i.stit_StoreGroup, i.stit_fsiuid, i.stit_MRP
,pc.parent_category, c.category_id, sc.sub_category_id, pc.parent_category_businessType
FROM finascop_stock_itemmaster i INNER JOIN mypha_productsubcategory sc ON sc.sub_category_id = i.product_category
INNER JOIN mypha_productcategory c ON c.category_id = sc.main_category INNER JOIN mypha_productparent_category pc ON pc.parent_category_id=c.parent_category
WHERE stit_Id=@stitid LIMIT 1";

                List<KeyValuePair<string, object>> selectParams = new List<KeyValuePair<string, object>>();
                selectParams.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));
                selectParams.Add(new KeyValuePair<string, object>("stitid", productId));
                DataTable dataTable = DataServiceMySql.GetDataTable(selectSql, Service.UserService.GetAPIConnectionString(), selectParams);

                if (dataTable == null || dataTable.Rows.Count <= 0)
                {
                    ParentMessageBinding("Invalid Product", "The product is invalid or you do not have access to the product.", 3);
                    return;
                }

                DataRow da = dataTable.Rows[0];

                txtPrdName.Text = da["stit_itemName"].ToString();
                txtVarient.Text = da["stit_product_variant"].ToString();
                txtQuantity.Text = da["stit_qty"].ToString();
                //selType.Text = da["stit_GST"].ToString();
                txtShortDescription.Text = da["stit_Description"].ToString();
                txtReturn.Text = da["stit_itemReturnTime"].ToString();
                string isReturn = da["stit_custInitiate"].ToString();
                chkSpotReturn.Checked = (isReturn == "1");
                summernote.Text = da["stit_long_description"].ToString();
                //txtBarcode.Text = da["stit_itemBarcode"].ToString();
                txtSelectedBrand.Text = da["stit_brand_name"].ToString();
                txtProductWebName.Text = da["stit_SKU"].ToString();

                if (selRetCat.Items.Count <= 1)
                    selRetCat.DataBind();
                if (selRetCat.Items.FindByValue(da["parent_category_businessType"].ToString()) != null)
                    selRetCat.Text = da["parent_category_businessType"].ToString();

                if (selCat.Items.Count <= 1)
                    selCat.DataBind();
                if (selCat.Items.FindByValue(da["category_id"].ToString()) != null)
                    selCat.Text = da["category_id"].ToString();

                if (selSubCat.Items.Count <= 1)
                    selSubCat.DataBind();
                if (selSubCat.Items.FindByValue(da["sub_category_id"].ToString()) != null)
                    selSubCat.Text = da["sub_category_id"].ToString();

                //if (selBrd.Items.Count <= 1)
                //    selBrd.DataBind();
                //if (selBrd.Items.FindByValue(da["pdt_brand"].ToString()) != null)
                //    selBrd.Text = da["pdt_brand"].ToString();

                if (selFoodType.Items.FindByValue(da["stit_foodtype"].ToString()) != null)
                    selFoodType.Text = da["stit_foodtype"].ToString();

                if (selUnit.Items.Count <= 1)
                    selUnit.DataBind();
                if (selUnit.Items.FindByValue(da["stit_unit"].ToString()) != null)
                    selUnit.Text = da["stit_unit"].ToString();

                if (selHSN.Items.Count <= 1)
                    selHSN.DataBind();
                if (selHSN.Items.FindByValue(da["stit_hsnId"].ToString()) != null)
                    selHSN.Text = da["stit_hsnId"].ToString();

                if (selType.Items.Count <= 1)
                    selType.DataBind();
                if (selType.Items.FindByValue(da["taxValueId"].ToString()) != null)
                    selType.Text = da["taxValueId"].ToString();

            //if (selDays.Items.FindByValue(da["stit_itemReturnTime"].ToString()) != null)
            //    selDays.Text = da["stit_itemReturnTime"].ToString();

            if (selCountry.Items.Count <= 1)
                    selCountry.DataBind();
                if (selCountry.Items.FindByValue(da["stit_orgin_country"].ToString()) != null)
                    selCountry.Text = da["stit_orgin_country"].ToString();
                // courierDelivery,i.directDelivery
                if (da["courierDelivery"].ToString() == "1" && da["directDelivery"].ToString() == "1")
                    selDelMode.Text = "3";
                else if (da["directDelivery"].ToString() == "1")
                    selDelMode.Text = "2";
                else if (da["courierDelivery"].ToString() == "1")
                    selDelMode.Text = "1";
                else
                    selDelMode.Text = "";

            string strBarcodeSql = @"SELECT fsipc_id, fsipc_stit_id, fsipc_code FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id =  @stitid";
            DataTable dtBarcode = DataServiceMySql.GetDataTable(strBarcodeSql, Service.UserService.GetAPIConnectionString(), selectParams);
            if (dtBarcode != null && dtBarcode.Rows.Count > 0)
            {
                DataRow dz = dtBarcode.Rows[0];
                txtBarcode.Text = dz["fsipc_code"].ToString();
            }

            string strImageSql = @"select id, image_url, image_folder from finascop_stock_item_images where product_id = @stitid order by image_type desc, id";
                DataTable dtImages = DataServiceMySql.GetDataTable(strImageSql, Service.UserService.GetAPIConnectionString(), selectParams);
                if (dtImages != null && dtImages.Rows.Count > 0)
                {
                    string imageUrl = ConfigurationManager.AppSettings.Get("ImageLocation"); //"https://odomedsdev.s3.ap-southeast-1.amazonaws.com/products/";
                    productImg1.ImageUrl = String.Format("{0}{1}", imageUrl, dtImages.Rows[0]["image_url"]);
                    productImg1.Attributes.Add("imgid", dtImages.Rows[0]["id"].ToString());
                    if (dtImages.Rows.Count > 1)
                    {
                        productImg2.ImageUrl = String.Format("{0}{1}", imageUrl, dtImages.Rows[1]["image_url"]);
                        productImg2.Attributes.Add("imgid", dtImages.Rows[1]["id"].ToString());
                    }
                    if (dtImages.Rows.Count > 2)
                    {
                        productImg3.ImageUrl = String.Format("{0}{1}", imageUrl, dtImages.Rows[2]["image_url"]);
                        productImg3.Attributes.Add("imgid", dtImages.Rows[2]["id"].ToString());
                    }
                    if (dtImages.Rows.Count > 3)
                    {
                        productImg4.ImageUrl = String.Format("{0}{1}", imageUrl, dtImages.Rows[3]["image_url"]);
                        productImg4.Attributes.Add("imgid", dtImages.Rows[3]["id"].ToString());
                    }
                    if (dtImages.Rows.Count > 4)
                    {
                        productImg5.ImageUrl = String.Format("{0}{1}", imageUrl, dtImages.Rows[4]["image_url"]);
                        productImg5.Attributes.Add("imgid", dtImages.Rows[4]["id"].ToString());
                    }
                }
        }
        
    }
}