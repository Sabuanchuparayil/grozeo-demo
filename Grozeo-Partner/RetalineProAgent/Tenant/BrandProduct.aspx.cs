using Amazon.DynamoDBv2.Model;
using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using RetalineProAgent.Core.BussinessModel.Inventory;
using RetalineProAgent.Core.BussinessModel.Store;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Data.SqlClient;
using System.Linq;
using System.Web;
using System.Web.Script.Services;
using System.Web.Services;
using System.Web.UI;
using System.Web.UI.WebControls;
using static System.Runtime.CompilerServices.RuntimeHelpers;

namespace RetalineProAgent
{
    public partial class BrandProduct : Base.BasePartnerPage
    {
        public delegate void ParentAddProductHandler(int status);
        public delegate void ParentAddBrandHandler(int status);
        public delegate void ParentMessageHandler(string title, string msg, int type);

        public event ParentAddProductHandler ParentAddProductBinding;
        public event ParentAddBrandHandler ParentAddBrandBinding;
        public event ParentAddProductHandler ParentCancelAddProductBinding;

        public event ParentMessageHandler ParentMessageBinding;
        public List<Core.BussinessModel.Inventory.ItemMaster> InventoryMap
        {
            get
            {
                return (List<Core.BussinessModel.Inventory.ItemMaster>)ViewState["INVENTORYMAPPING"];
            }
            set
            {
                ViewState["INVENTORYMAPPING"] = value;
            }
        }

        public delegate void ParentCustomHandler(object sender);
        public event ParentCustomHandler RefreshInventoryBinding;
        public int SelectedItemId { get; set; }
        public void ResetInventory(bool resetProductsList = true)
        {

            int storegroupid = this.CurrentUser.APIStoreId;

            //var dt = DataService.GetDataTable($"SELECT * FROM InventoryMapping WHERE StoreId={storegroupid}");

            var dt = DataServiceMySql.GetDataTable($"SELECT DISTINCT stit_id FROM finascop_stock_branch_inventory bi INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id WHERE b.br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());

            InventoryMap = (from row in dt.AsEnumerable()
                            select new Core.BussinessModel.Inventory.ItemMaster()
                            {
                                Id = (int)row.Field<Int64>("stit_id"),
                                //ErpId = row.Field<string>("ErpId")
                            }).ToList();
            //if(resetProductsList)
            //    lstProducts.DataBind();
        }
        protected void Page_Load(object sender, EventArgs e)
        {
            int brandId = -1, catId = -1;

            if (!String.IsNullOrEmpty(Request.QueryString["brandId"]))
                try { brandId = Convert.ToInt32(Request.QueryString["brandId"]); } catch { brandId = 0; }


            if (!String.IsNullOrEmpty(Request.QueryString["catId"]))
                try { catId = Convert.ToInt32(Request.QueryString["catId"]); } catch { catId = 0; }

            if (!IsPostBack)
            {
                if (SDSBrandProduct != null && SDSBrandProduct.Select(DataSourceSelectArguments.Empty).GetEnumerator().MoveNext())
                {
                    ScriptManager.RegisterStartupScript(this, this.GetType(), "showModalAddProduct", "$('#modalAddProduct').modal('hide');", true);
                }
                else
                {
                    ScriptManager.RegisterStartupScript(this, this.GetType(), "showModalAddProduct", "$('#modalAddProduct').modal('show');", true);
                }
                APIService.ClearCachedData();
                ResetInventory(false);
                List<KeyValuePair<string, object>> inventoryParams = new List<KeyValuePair<string, object>>();
                inventoryParams.Add(new KeyValuePair<string, object>("storeId", this.CurrentUser.APIStoreId));
                DataTable dtInventory = DataServiceMySql.GetDataTable("SELECT bi.stit_id, mrp, mrpid FROM finascop_stock_branch_inventory bi INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id WHERE b.br_storeGroup=@storeId group by stit_id", UserService.GetAPIConnectionString(), inventoryParams);
                hidSelectedItems.Value = String.Join(",", dtInventory.AsEnumerable().Select(item => string.Format("{0}", item["stit_id"])).ToArray());
                hidSelectedItemsWithPrice.Value = String.Join(",", dtInventory.AsEnumerable().Select(item => string.Format("{0}|{1}|{2}", item["stit_id"], item["mrp"], item["mrpid"])).ToArray());
                btnAddPrdt.Visible = (brandId > 0);

                if (brandId > 0)
                {
                    DataTable dt = DataServiceMySql.GetDataTable($"SELECT brand_name FROM mypha_productbrands WHERE brand_id = {brandId}", Service.UserService.GetAPIConnectionString());
                    if (dt != null && dt.Rows.Count > 0)
                    {
                        DataRow da = dt.Rows[0];
                        string brandName = da["brand_name"].ToString();
                        ltrProductName.Text = brandName;
                        modalMessage.InnerText = $"The selected brand '{brandName}' has no products to add. If you wish to create a new product under this brand, please click proceed or to choose a different brand, click Cancel.";
                    }
                }
                else if (catId > 0)
                {
                    DataTable dt = DataServiceMySql.GetDataTable($"SELECT sub_category FROM mypha_productsubcategory WHERE sub_category_id={catId}", Service.UserService.GetAPIConnectionString());
                    if (dt != null && dt.Rows.Count > 0)
                    {
                        DataRow da = dt.Rows[0];
                        string subCategoryName = da["sub_category"].ToString();
                        ltrProductName.Text = subCategoryName;
                        modalMessage.InnerText = $"The selected subcategory '{subCategoryName}' has no products to add. Click on Cancel to select another subcategory";
                    }
                }
            }

            if (brandId > 0)
             {
                DataTable dt = DataServiceMySql.GetDataTable($"SELECT brand_id, brand_name FROM mypha_productbrands WHERE brand_id = {brandId}", Service.UserService.GetAPIConnectionString());
                if (dt == null || dt.Rows.Count <= 0)
                {
                    Common.ShowCustomAlert(this.Page, "Loading failed", "Invalid selection or the record is not existing", false, "/Tenant/BrandProduct");
                    return;
                }

                DataRow da = dt.Rows[0];
                ltrProductName.Text = da["brand_name"].ToString();
             }

           else if (catId > 0)
            {
                DataTable dt = DataServiceMySql.GetDataTable($"SELECT sub_category_id,sub_category FROM mypha_productsubcategory where sub_category_id={catId}", Service.UserService.GetAPIConnectionString());
                if (dt == null || dt.Rows.Count <= 0)
                {
                    Common.ShowCustomAlert(this.Page, "Loading failed", "Invalid selection or the record is not existing", false, "/Tenant/BrandProduct");
                    return;
                }

                DataRow da = dt.Rows[0];
                ltrProductName.Text = da["sub_category"].ToString();
            }

        }

        protected void Page_PreRender(object sender, EventArgs e)
        {

        }
        protected void selCat_SelectedIndexChanged(object sender, EventArgs e)
        {
            gvBrandProduct.PageIndex = 0;
        }

        protected void gvBrandProduct_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvBrandProduct.PageIndex * gvBrandProduct.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvBrandProduct.Rows.Count - 1;
            //ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSBrandProduct.Select(DataSourceSelectArguments.Empty);
        }

        protected void SDSBrandProduct_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storeId"].Value = this.CurrentUser.APIStoreId;//.StoreGroupId;

            //string catId = Request.QueryString["catId"];
            //e.Command.Parameters["catId"].Value = catId;

            //string brandId = Request.QueryString["brandId"];
            //e.Command.Parameters["brandId"].Value = brandId;

            string catId = Request.QueryString["catId"];
            string brandId = Request.QueryString["brandId"];

            if (!string.IsNullOrEmpty(catId))
            {
                e.Command.Parameters["catId"].Value = catId;
                lnkBrand.Visible = false;

            }
            else
            {
               
               e.Command.Parameters["catId"].Value = 0; 
            }

            
            if (!string.IsNullOrEmpty(brandId))
            {
                e.Command.Parameters["brandId"].Value = brandId;
            }
            else
            {
                
                e.Command.Parameters["brandId"].Value = 0; 
            }
            if (e.Command.Parameters.Contains("type"))
            {
                //e.Command.Parameters["type"].Value = (rbNotAddedProducts.Checked ? 2 : (rbAddedProducts.Checked ? 1 : 0));
            }
            if (e.Command.Parameters.Contains("isNoneGST"))
                e.Command.Parameters["isNoneGST"].Value = CurrentUser.TenantType == 2 ? 1 : 0;
        }

        //protected void SDSBrands_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        //{
        //    //if (e.Command.Parameters.Contains("storeId"))
        //    e.Command.Parameters["storeId"].Value = this.CurrentUser.APIStoreId;//.StoreGroupId;
        //    string brandId = Request.QueryString["brandId"];
        //    e.Command.Parameters["brandId"].Value = brandId;
        //    string catId = Request.QueryString["catId"];
        //    e.Command.Parameters["catId"].Value = catId;
        //    if (e.Command.Parameters.Contains("type"))
        //    {
        //        //e.Command.Parameters["type"].Value = (rbNotAddedProducts.Checked ? 2 : (rbAddedProducts.Checked ? 1 : 0));
        //    }
        //    if (e.Command.Parameters.Contains("isNoneGST"))
        //        e.Command.Parameters["isNoneGST"].Value = CurrentUser.TenantType == 2 ? 1 : 0;

        //    //if (Request.QueryString["flgBrnd"]=="1")
        //    //{
        //    //    SDSBrandProduct.SelectCommand=""

        //    //        SDSBrandProduct.SelectParameters


        //    //}
               
            
        //}

        protected void chkProductItem_CheckedChanged(object sender, EventArgs e)
        {
            CheckBox chkProductItem = (CheckBox)sender;
            if (chkProductItem == null)
                return;

            //var parent = chkProductItem.Parent;
            //if (parent == null)
            //    return;

            string stit_id = chkProductItem.Attributes["itemid"];
            string mrp = chkProductItem.Attributes["itemmrp"]; // 
            string delSql = $"delete from finascop_stock_branch_inventory where stit_id= @stit_id and branch_id in (SELECT br_ID FROM finascop_branch WHERE br_storeGroup = @tenantId)";
            string insSql = $"INSERT IGNORE INTO finascop_stock_branch_inventory (stit_id, branch_id, item_count, mrp, selling_price) SELECT @stit_id, br_ID, 0, @mrp, @mrp FROM finascop_branch WHERE br_storeGroup = @tenantId";
            List<KeyValuePair<String, Object>> stockParmeters = new List<KeyValuePair<string, object>>();
            stockParmeters.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.APIStoreId));
            stockParmeters.Add(new KeyValuePair<string, object>("stit_id", stit_id));
            if (chkProductItem.Checked)
                stockParmeters.Add(new KeyValuePair<string, object>("mrp", mrp));

            if (chkProductItem.Checked)
                DataServiceMySql.ExecuteSql(insSql, UserService.GetAPIConnectionString(), parmeters: stockParmeters);
            else
                DataServiceMySql.ExecuteSql(delSql, UserService.GetAPIConnectionString(), parmeters: stockParmeters);

            SelectedItemId = Convert.ToInt32(stit_id);
            SDSBrandProduct.Select(DataSourceSelectArguments.Empty);
            gvBrandProduct.DataBind();
            if (gvBrandProduct.Rows.Count > 0)
            {
                List<KeyValuePair<String, Object>> tenantParmeters = new List<KeyValuePair<string, object>>();
                tenantParmeters.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.StoreGroupId));
                DataService.ExecuteSql("UPDATE AppTenant SET Stage = 7 WHERE Stage = 6 AND Id=@tenantId", parmeters: tenantParmeters);
                Service.UserService.CachedDefaultUser = null;
            }

        }

        public bool IsSelected(string stitId, string mrpid = "")
        {
            if (!String.IsNullOrEmpty(hidSelectedItems.Value))
            {
                try
                {
                    if (!String.IsNullOrEmpty(mrpid) && mrpid.Trim() != "")
                    {
                        bool isIn = hidSelectedItemsWithPrice.Value.Split(',').Any(v => v.StartsWith(stitId + "|") && (v.EndsWith("|" + mrpid) || v.EndsWith("|0")));
                        return isIn;
                    }
                    return hidSelectedItems.Value.Split(',').Contains(stitId);

                }
                catch { }
            }
            return false;
        }

        protected void btnAddBrand_Click(object sender, EventArgs e)
        {
            if (String.IsNullOrEmpty(txtNewBrand.Text))
            {
                ltrAddBrandResult.Text = "Please enter brand name and manufacturer.";
                //ShowAddBrandPopup();
                if (ParentAddBrandBinding != null)
                    ParentAddBrandBinding(0);
                else
                    Common.ShowToastifyMessage(this.Page, "Please enter brand name.");
                return;
            }

            DataTable dtManufacturer = DataServiceMySql.GetDataTable($"SELECT manufacture_id, manufacture_name FROM mypha_productmanufacture WHERE manufacture_name='Multiple'", Service.UserService.GetAPIConnectionString());
            int manufactureId = 0;
            if (dtManufacturer != null && dtManufacturer.Rows.Count > 0)
            {
                DataRow dr = dtManufacturer.Rows[0];
                manufactureId = Convert.ToInt32(dr["manufacture_id"]);

            }

            var brandParams = new List<KeyValuePair<string, object>>();
            brandParams.Add(new KeyValuePair<string, object>("brandname", txtNewBrand.Text));
            brandParams.Add(new KeyValuePair<string, object>("manufacture", 1));
            brandParams.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.APIStoreId));

            //string strSql = $"INSERT INTO mypha_productbrands (brand_name, manufacture_id) SELECT * FROM (SELECT @brandname, ifnull((SELECT manufacture_id FROM `mypha_productmanufacture` WHERE LOWER(TRIM(REPLACE(manufacture_name, ' ', ''))) LIKE LOWER(TRIM(REPLACE(@manufacture, ' ', '')))), 1))tmp WHERE " +
            //    $"NOT EXISTS( SELECT * FROM mypha_productbrands WHERE brand_name = @brandname);";

            int count = 0;
            try
            {
                DataTable dtBrand = DataServiceMySql.GetDataTable("addPrivateBrand", UserService.GetAPIConnectionString(), brandParams, true);
                if (dtBrand != null && dtBrand.Rows.Count > 0)
                {
                    int brandid = Convert.ToInt32(dtBrand.Rows[0][0]);
                    int isnew = Convert.ToInt32(dtBrand.Rows[0][1]);
                    //hidSelectedBrand.Value = brandid.ToString();
                    if (brandid > 0)
                    {
                        string brandName = txtNewBrand.Text;
                        int manufacturerId = manufactureId;
                        int storegroupId = this.CurrentUser.APIStoreId;
                        var result = Core.Services.APIService.ProductBrand(brandName, manufacturerId, storegroupId);


                        List<KeyValuePair<string, object>> brandparams = new List<KeyValuePair<string, object>>();
                        brandparams.Add(new KeyValuePair<string, object>("brandId", brandid));
                        brandparams.Add(new KeyValuePair<string, object>("mappingId", result.brand_id));
                        string strUpdateSql = $"UPDATE mypha_productbrands SET mapping_id=@mappingId WHERE brand_id=@brandId";
                        DataServiceMySql.ExecuteSql(strUpdateSql, Service.UserService.GetAPIConnectionString(), brandparams);

                        if (isnew == 0)
                        {
                            Common.ShowToastifyMessage(Page, (isnew > 0 ? "Brand created successfully!" : "Brand name is already existing. It is selected in the brand select box to continue"), (isnew > 0 ? "success" : "info"));
                        }
                        else
                        {
                            count = 1;
                            string brandId = Convert.ToString(brandid);
                            Response.Redirect($"PrivateInventory.aspx?brandId={brandId}");
                        }
                    }
                }
                if (ParentAddBrandBinding != null)
                    ParentAddBrandBinding(1);
            }
            catch { count = 0; }
            if (count == 0)
            {
                Common.ShowToastifyMessage(Page, "The brand name already exists or there is a technical problem on creating brand.", "danger");
                if (ParentAddBrandBinding != null)
                    ParentAddBrandBinding(-1);

            }
        }

        protected void addProduct_Click(object sender, EventArgs e)
        {
            Button btn = (Button)sender;
            string addaction = hdnAction.Value;
            double percent = 0, _qty = 0;

            if (string.IsNullOrEmpty(addaction) || !(new[] { "Add", "Delete" }).Contains(addaction))
            {
                Common.ShowToastifyMessage(this.Page, "Invalid operation", "danger");
                return;
            }

            if (addaction == "Add")
            {
                try
                {
                    if (btn == null || string.IsNullOrEmpty(hdnItemId.Value))
                        return;

                    int itemId = SafeToInt(hdnItemId.Value);
                    double itemMrp = SafeToDouble(txtMRP.Text);
                    double mrpid = 0;

                    List<KeyValuePair<string, object>> inventoryParams = new List<KeyValuePair<string, object>>();
                    inventoryParams.Add(new KeyValuePair<string, object>("tenantId", CurrentUser.APIStoreId));
                    inventoryParams.Add(new KeyValuePair<string, object>("selectedids", Convert.ToString(btn.Attributes["itemid"])));
                    inventoryParams.Add(new KeyValuePair<string, object>("qty", _qty));
                    inventoryParams.Add(new KeyValuePair<string, object>("percent", percent));
                    inventoryParams.Add(new KeyValuePair<string, object>("stit_id", itemId));
                    inventoryParams.Add(new KeyValuePair<string, object>("mrp", string.IsNullOrWhiteSpace(txtMRP.Text) ? "0.00" : txtMRP.Text));
                    inventoryParams.Add(new KeyValuePair<string, object>("sellingprice", percent > 0 ? itemMrp - (itemMrp * percent / 100) : itemMrp));
                    inventoryParams.Add(new KeyValuePair<string, object>("mrpid", mrpid));

                    // Step 1: Add HSN parameters
                    inventoryParams.Add(new KeyValuePair<string, object>("hsn", string.IsNullOrWhiteSpace(txtHSN.Text) ? "0" : txtHSN.Text));
                    inventoryParams.Add(new KeyValuePair<string, object>("gst", string.IsNullOrWhiteSpace(txtGST.Text) ? "0" : txtGST.Text));
                    inventoryParams.Add(new KeyValuePair<string, object>("cess", string.IsNullOrWhiteSpace(txtCess.Text) ? "0" : txtCess.Text));


                    // Step 2: Insert inventory
                    string insSql = @"INSERT IGNORE INTO finascop_stock_branch_inventory (stit_id, branch_id, item_count, mrp, selling_price, fpod_customerRateHmDel, fpod_customerRateCouDel, fpod_customerRatePikup, fpod_leastSKUmrp, fpod_poLandingCostleastSKU, mrpid, hsnCode, taxValue, cessValue) SELECT @stit_id, br_ID, @qty, @mrp, @sellingprice, @sellingprice, @sellingprice, @sellingprice, @sellingprice, @sellingprice, @mrpid, @hsn, @gst, @cess FROM finascop_branch WHERE br_storeGroup = @tenantId";
                    DataServiceMySql.ExecuteSql(insSql, UserService.GetAPIConnectionString(), inventoryParams);

                    // Step 3: Product Code Check
                    bool isCodeChanged = !string.Equals(txtProductCode.Text.Trim(), hdnOriginalProductCode.Value.Trim(), StringComparison.OrdinalIgnoreCase);

                    if (isCodeChanged && !string.IsNullOrWhiteSpace(txtProductCode.Text))
                    {
                        inventoryParams.Add(new KeyValuePair<string, object>("code", txtProductCode.Text.Trim()));
                        DataTable dtCode = DataServiceMySql.GetDataTable(
                            "SELECT COUNT(*) AS cnt FROM finascop_stock_itemmaster_product_codes WHERE fsipc_code = @code",
                            UserService.GetAPIConnectionString(), inventoryParams);

                        if (dtCode != null && dtCode.Rows.Count > 0 && Convert.ToInt32(dtCode.Rows[0]["cnt"]) > 0)
                        {
                            Common.ShowToastifyMessage(this.Page, "Duplicate code. The code is already used for another product.", "danger");
                            return;
                        }
                    }

                    // Step 4: Insert or update product code / HSN / GST / Cess
                    inventoryParams.Add(new KeyValuePair<string, object>("itemId", itemId));
                    inventoryParams.Add(new KeyValuePair<string, object>("codeType", 0));
                    inventoryParams.Add(new KeyValuePair<string, object>("company", 0));
                    inventoryParams.Add(new KeyValuePair<string, object>("individual", 0));
                    inventoryParams.Add(new KeyValuePair<string, object>("branch", 0));
                    inventoryParams.Add(new KeyValuePair<string, object>("code", string.IsNullOrWhiteSpace(txtProductCode.Text) ? (object)DBNull.Value : txtProductCode.Text));

                    string sql = @"INSERT INTO finascop_stock_itemmaster_product_codes (fsipc_stit_id, fsipc_code, fsipc_codeType, fsipc_isCompany, fsipc_storeGroup, fsipc_store, fsipc_isIndividual) VALUES (@itemId, @code, @codeType, @company, @tenantId, @branch, @individual) ON DUPLICATE KEY UPDATE fsipc_code = VALUES(fsipc_code), fsipc_codeType = VALUES(fsipc_codeType), fsipc_isCompany = VALUES(fsipc_isCompany), fsipc_isIndividual = VALUES(fsipc_isIndividual);";

                    DataServiceMySql.ExecuteSql(sql, UserService.GetAPIConnectionString(), inventoryParams);

                    // Step 5: Tenant stage update
                    if (gvBrandProduct.Rows.Count > 0 && CurrentUser.TenantStage >= 6)
                    {
                        List<KeyValuePair<string, object>> tenantParmeters = new List<KeyValuePair<string, object>>();
                        tenantParmeters.Add(new KeyValuePair<string, object>("tenantId", CurrentUser.StoreGroupId));

                        DataService.ExecuteSql("UPDATE AppTenant SET Stage = 7 WHERE Stage = 6 AND Id=@tenantId", parmeters: tenantParmeters);
                        Service.UserService.CachedDefaultUser = null;
                        Response.Redirect("/Tenant/StockPrice");
                    }

                    UpdateHiddenSelectedItems();
                    Common.ShowToastifyMessage(Page, "Product Added Successfully!!");
                    btn.Attributes.Add("action", "Delete");
                }
                catch
                {
                    Common.ShowToastifyMessage(Page, "An error occurred while adding the product.", "danger");
                }
                RedirectToBrandProduct();
            }
            else if (addaction == "Delete")
            {
                try
                {
                    if (btn == null || string.IsNullOrEmpty(hdnItemId.Value))
                        return;

                    int itemId = SafeToInt(hdnItemId.Value);
                    int storegroupid = CurrentUser.APIStoreId;

                    List<KeyValuePair<string, object>> deleteParams = new List<KeyValuePair<string, object>>();
                    deleteParams.Add(new KeyValuePair<string, object>("itemId", itemId));
                    deleteParams.Add(new KeyValuePair<string, object>("storegroupid", storegroupid));

                    string strSql = $@"DELETE FROM finascop_stock_branch_inventory WHERE stit_ID=@itemId AND EXISTS(
                      SELECT * FROM finascop_branch WHERE br_ID = finascop_stock_branch_inventory.branch_id AND br_storeGroup=@storegroupid)";
                    DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), deleteParams);

                    // Delete from product codes
                    string deleteProductCodesSql = @"DELETE FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id = @itemId AND fsipc_storeGroup IN (SELECT br_ID FROM finascop_branch WHERE br_storeGroup = @storegroupid)";
                    DataServiceMySql.ExecuteSql(deleteProductCodesSql, UserService.GetAPIConnectionString(), deleteParams);

                    // Log deletion
                    Core.Services.APIService.InventoryLog(itemId, 0, 0, 0, "Deleted Product", "Product deleted");
                    Common.ShowToastifyMessage(Page, "Product Deleted Successfully!!");
                    btn.Attributes.Add("action", "Delete");
                }
                catch
                {
                    Common.ShowToastifyMessage(Page, "An error occurred while deleting the product.", "danger");
                }
            }

            RedirectToBrandProduct();

            // Keep same page & refresh grid
            if (int.TryParse(Request.QueryString["brandId"], out int bId))
            {
                BindBrandProductGrid(bId);
            }
        }

        private void RedirectToBrandProduct()
        {
            string brandId = Request.QueryString["brandId"];
            Response.Redirect($"BrandProduct.aspx?brandId={brandId}");
        }

        // Helpers
        private int SafeToInt(string value)
        {
            int.TryParse(value, out int result);
            return result;
        }

        private double SafeToDouble(string value)
        {
            double.TryParse(value, out double result);
            return result;
        }


        private void UpdateHiddenSelectedItems()
        {
            List<KeyValuePair<string, object>> inventoryParams = new List<KeyValuePair<string, object>>
            {
                new KeyValuePair<string, object>("storeId", this.CurrentUser.APIStoreId)
            };

            DataTable dtInventory = DataServiceMySql.GetDataTable("SELECT bi.stit_id, mrp, mrpid FROM finascop_stock_branch_inventory bi INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id WHERE b.br_storeGroup=@storeId GROUP BY stit_id", 
                UserService.GetAPIConnectionString(), inventoryParams);

            hidSelectedItems.Value = String.Join(",", dtInventory.AsEnumerable().Select(item => $"{item["stit_id"]}"));

            hidSelectedItemsWithPrice.Value = String.Join(",", dtInventory.AsEnumerable().Select(item => $"{item["stit_id"]}|{item["mrp"]}|{item["mrpid"]}"));
        }

        private void BindBrandProductGrid(int brandId)
        {
            SDSBrandProduct.SelectParameters["brandId"].DefaultValue = brandId.ToString();
            gvBrandProduct.DataBind();
        }

        protected void lnkBrand_Click(object sender, EventArgs e)
        {
            string brandId = Request.QueryString["brandId"];
            Response.Redirect($"PrivateInventory.aspx?brandId={brandId}");
        }


        protected void SDSCat_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
            string brandId = Request.QueryString["brandId"];
            e.Command.Parameters["brandId"].Value = brandId;
            if (e.Command.Parameters.Contains("isNoneGST"))
                e.Command.Parameters["isNoneGST"].Value = CurrentUser.TenantType == 2 ? 1 : 0;
        }

        protected void SDSSubCat_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        }

        //protected void SDSBrand_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        //{
        //    if (e.Command.Parameters.Contains("storeId"))
        //        e.Command.Parameters["storeId"].Value = this.CurrentUser.APIStoreId;//.StoreGroupId; 
        //}

        protected void lnkBrands_Click(object sender, EventArgs e)
        {
            string Id = selBrd.SelectedValue;
            Response.Redirect($"BrandProduct.aspx?brandId={Id}");
        }

        protected void lnkSubcategory_Click(object sender, EventArgs e)
        {
            string catId = selSubCategory.SelectedValue;
            Response.Redirect($"BrandProduct.aspx?catId={catId}");

        }

        protected void gvBrandProduct_RowDataBound(object sender, GridViewRowEventArgs e)
        {
            try
            {
                if (e.Row.RowType == DataControlRowType.Header)
                {
                    BoundField myBoundField = (BoundField)((DataControlFieldCell)e.Row.Cells[2]).ContainingField;

                    if (myBoundField != null)
                    {
                        if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
                        {
                            myBoundField.HeaderText = "MRP";
                        }
                        else
                        {
                            myBoundField.HeaderText = "RRP";
                        }
                    }
                }
            }
            catch
            {

            }

            if (e.Row.RowType == DataControlRowType.DataRow)
            {
                try
                {
                    if (IsSelected(DataBinder.Eval(e.Row.DataItem, "stit_Id").ToString(), DataBinder.Eval(e.Row.DataItem, "mrpid").ToString()))
                        e.Row.CssClass = "already_added";
                }
                catch (Exception ex)
                {

                }
            }
        }

        protected void btnAddPrdt_Click(object sender, EventArgs e)
        {
            string brandId = Request.QueryString["brandId"];
            Response.Redirect($"PrivateInventory.aspx?brandId={brandId}");
        }

        protected void selCategory_SelectedIndexChanged(object sender, EventArgs e)
        {
            gvBrandProduct.PageIndex = 0;
        }

        protected void SDSSubcategory_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storeId"].Value = this.CurrentUser.APIStoreId;
        }

        //protected void btnLoadCess_Click(object sender, EventArgs e)
        //{
        //    var dv = (DataView)SDSCESS.Select(DataSourceSelectArguments.Empty);

        //    if (dv.Count > 0)
        //    {
        //        txtCess.Text = dv[0]["hsnCess"].ToString();
        //    }
        //    else
        //    {
        //        txtCess.Text = ""; 
        //    }
        //}

        //protected void btnPrdCode_Click(object sender, EventArgs e)
        //{
        //    var dv = (DataView)SDSPrdCode.Select(DataSourceSelectArguments.Empty);

        //    if (dv.Count > 0)
        //    {
        //        txtProductCode.Text = dv[0]["fsipc_code"].ToString();
        //    }
        //    else
        //    {
        //        txtProductCode.Text = "";
        //    }
        //    ScriptManager.RegisterStartupScript(this, this.GetType(), "ShowModalScript", "$('#productModal').modal('show');", true);
        //}

        protected void btnLoadPopupData_Click(object sender, EventArgs e)
        {
            // Load Cess
            //var dvCess = (DataView)SDSCESS.Select(DataSourceSelectArguments.Empty);
            //txtCess.Text = dvCess.Count > 0 ? dvCess[0]["hsnCess"].ToString() : "";
            txtCess.Text = hdnCess.Value;

            // Load Product Code
            var dvPrd = (DataView)SDSPrdCode.Select(DataSourceSelectArguments.Empty);
            txtProductCode.Text = dvPrd.Count > 0 ? dvPrd[0]["fsipc_code"].ToString() : "";
            hdnOriginalProductCode.Value = txtProductCode.Text;
            titleName.InnerText = hdnSKU.Value;
            // Keep popup open
            ScriptManager.RegisterStartupScript(
                this,
                this.GetType(),
                "ShowModalScript",
                "$('#productModal').modal('show');",
                true
            );
        }

        //[WebMethod]
        //[ScriptMethod(ResponseFormat = ResponseFormat.Json)]
        //public static string GetCessByHSNId(int gst, int hsncodeid)
        //{
        //    List<KeyValuePair<string, object>> cessParams = new List<KeyValuePair<string, object>>();
        //    cessParams.Add(new KeyValuePair<string, object>("hsnId", hsncodeid));
        //    cessParams.Add(new KeyValuePair<string, object>("gst", gst));
        //    string query = "SELECT hsnCess FROM hsn_value WHERE hsnId=@hsnId AND hsnGst=@gst";
        //    var table = DataServiceMySql.GetDataTable(query, UserService.GetAPIConnectionString(), cessParams);

        //    if (table.Rows.Count > 0 && table.Rows[0]["hsnCess"] != DBNull.Value)
        //        return table.Rows[0]["hsnCess"].ToString();

        //    return table.Rows[0]["hsnCess"].ToString();
        //}


        //[WebMethod]
        //[ScriptMethod(ResponseFormat = ResponseFormat.Json)]
        //public static object GetHSNCessAndProductCode(int gst, int hsncodeid, int stitId)
        //{
        //    // Get Cess
        //    string cessQuery = "SELECT hsnCess FROM hsn_value WHERE hsnId=@hsnId AND hsnGst=@gst";
        //    var cessTable = DataServiceMySql.GetDataTable(cessQuery, UserService.GetAPIConnectionString(),
        //        new List<KeyValuePair<string, object>> {
        //    new KeyValuePair<string, object>("hsnId", hsncodeid),
        //    new KeyValuePair<string, object>("gst", gst)
        //        });
        //    string cess = cessTable.Rows.Count > 0 ? cessTable.Rows[0]["hsnCess"].ToString() : "10";

        //    // Get Product Code
        //    string productQuery = "SELECT fsipc_code FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id=@stitId LIMIT 1";
        //    var productTable = DataServiceMySql.GetDataTable(productQuery, UserService.GetAPIConnectionString(),
        //        new List<KeyValuePair<string, object>> {
        //    new KeyValuePair<string, object>("stitId", stitId)
        //        });
        //    string productCode = productTable.Rows.Count > 0 ? productTable.Rows[0]["fsipc_code"].ToString() : string.Empty;

        //    // Return both in one JSON object
        //    return new
        //    {
        //        Cess = cess,
        //        ProductCode = productCode
        //    };
        //}


    }
}


