using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class SelectProduct: Base.BasePartnerPage
    {
        //public List<Core.BussinessModel.Inventory.ItemMaster> InventoryMap
        //{
        //    get
        //    {
        //        return (List<Core.BussinessModel.Inventory.ItemMaster>)ViewState["INVENTORYMAPPING"];
        //    }
        //    set
        //    {
        //        ViewState["INVENTORYMAPPING"] = value;
        //    }
        //}

        public void ResetInventory(bool resetProductsList = true)
        {

            //int storegroupid = this.CurrentUser.APIStoreId;
            //var dt = DataServiceMySql.GetDataTable($"SELECT DISTINCT stit_id FROM finascop_stock_branch_inventory bi INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id WHERE b.br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());

            //InventoryMap = (from row in dt.AsEnumerable()
            //                select new Core.BussinessModel.Inventory.ItemMaster()
            //                {
            //                    Id = (int)row.Field<Int64>("stit_id"),
            //                    //ErpId = row.Field<string>("ErpId")
            //                }).ToList();

            List<KeyValuePair<string, object>> inventoryParams = new List<KeyValuePair<string, object>>();
            inventoryParams.Add(new KeyValuePair<string, object>("storeId", this.CurrentUser.APIStoreId));
            DataTable dtInventory = DataServiceMySql.GetDataTable("SELECT bi.stit_id, mrp FROM finascop_stock_branch_inventory bi INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id WHERE b.br_storeGroup=@storeId group by stit_id", UserService.GetAPIConnectionString(), inventoryParams);
            hidSelectedItems.Value = String.Join(",", dtInventory.AsEnumerable().Select(item => string.Format("{0}", item["stit_id"])).ToArray());
            hidSelectedItemsWithPrice.Value = String.Join(",", dtInventory.AsEnumerable().Select(item => string.Format("{0}|{1}", item["stit_id"], item["mrp"])).ToArray());
            hidItemsInDB.Value = hidSelectedItems.Value;

        }
        protected void Page_Load(object sender, EventArgs e)
        {
            ltrAddBrandResult.Text= ""; // ltrResult.Text = 
            DataTable dataTable = DataServiceMySql.GetDataTable($"SELECT hsn_id,hsn_code,gst_percent FROM finascop_hsn WHERE hsn_code='" + selHSN.SelectedItem.Text + "'", Service.UserService.GetAPIConnectionString());
            if (dataTable != null && dataTable.Rows.Count > 0)
            {
                DataRow da = dataTable.Rows[0];
                txtGSTVAT.Text = da["gst_percent"].ToString(); ;
            }


            if (!IsPostBack)
            {
                APIService.ClearCachedData();
                ResetInventory(false);
            }
        }
        public bool IsSelected(string stitId)
        {
            if (!String.IsNullOrEmpty(hidSelectedItems.Value))
            {
                try
                {
                    return hidSelectedItems.Value.Split(',').Contains(stitId);
                }
                catch { }
            }
            return false;
        }
        protected void Page_PreRender(object sender, EventArgs e)
        {
            //hlNextSelectItems.Text = "Back";
            hlNextSelectItems.CssClass = "btn btn-primary btn-drk-green m-0 mx-2 wd-sm-auto-force px-4";
            if (!IsPostBack)
            {
                var user = this.CurrentUser;
                if (user.TenantType == 1 && user.TenantStage == 6)
                {
                    if (lstProducts.Items.Count <= 0)
                        lstProducts.DataBind();

                    //if (lstProducts.Items.Count > 0)
                    //{
                    //    hidCurTab.Value = "1";
                    //    hlNextSelectItems.Text = "Next";
                    //    hlNextSelectItems.CssClass = "btn btn-primary btn-drk-green disabled m-0 mx-2 wd-sm-auto-force px-4";
                    //}
                    //else
                    //{
                    //    hidCurTab.Value = "2";
                    //}
                }
            }
            plcMyProducts.Visible = (String.IsNullOrEmpty(hidCurTab.Value) || hidCurTab.Value == "0");
            plcSelectProduct.Visible = hidCurTab.Value == "1";
            plcAddProduct.Visible = hidCurTab.Value == "2";
            //plcSponsoredProducts.Visible = hidCurTab.Value == "3";

            bool hasProducts = (!String.IsNullOrEmpty(hidSelectedItems.Value) && hidSelectedItems.Value.Split(',').Length > 0);
            btnSaveSelectedItems.CssClass = (!hasProducts ? "btn btn-primary btn-drk-green disabled btn-block mx-2 wd-sm-auto-force px-4" : "btn btn-primary btn-drk-green btn-block mx-2 wd-sm-auto-force px-4");
            //lbtnConfirmSponsored.CssClass = (this.CurrentUser.TenantStage == 6 ? "btn btn-primary btn-drk-green disabled mx-2 wd-sm-auto-force px-4" : "btn btn-primary btn-drk-green mx-2 wd-sm-auto-force px-4");

            if(lstSelectedProducts.Items.Count <= 0)
                lstSelectedProducts.DataBind();

            //btnSaveSelectedProducts.CssClass = (lstSelectedProducts.Items.Count <=0 ? "btn btn-primary btn-drk-green disabled btn-block mx-2 wd-sm-auto-force px-4" : "btn btn-primary btn-drk-green btn-block mx-2 wd-sm-auto-force px-4");
            hlNextSelectedProduct.CssClass = (lstSelectedProducts.Items.Count <= 0 ? "btn btn-primary btn-drk-green disabled mx-2 wd-sm-auto-force px-4" : "btn btn-primary btn-drk-green mx-2 wd-sm-auto-force px-4");
            //lbtnConfirmSponsored.OnClientClick = $"window.open('https://{this.CurrentUser.PublicSiteUrl }', '_blank', 'toolbar=0,location=0,menubar=0');";

            //common_quantity.Checked = false;
            //common_selling.Checked = false;
            txtSelectProductQuantity.Text = "1";
            txtSelectProductPercentage.Text = "0";

            //plcSelectProducts.Visible = hidSelectView.Value == "1";
            //plcSelectedProducts.Visible = hidSelectView.Value == "2";
            //tbcAddProduct.Visible = hidSelectView.Value == "1";

            //btnSaveSelectedItems.CssClass = (this.CurrentUser.TenantStage == 6 ? "btn btn-primary btn-drk-green disabled btn-block mx-2 wd-sm-auto-force px-4" : "btn btn-primary btn-drk-green btn-block mx-2 wd-sm-auto-force px-4");
            //btnSaveProducts.CssClass = (this.CurrentUser.TenantStage == 7 ? "btn btn-primary disabled btn-block mx-2 wd-sm-auto-force px-4" : "btn btn-primary btn-block mx-2 wd-sm-auto-force px-4");
            //hlSaveProductsMoveNext.CssClass = (this.CurrentUser.TenantStage == 6 ? "btn btn-primary disabled btn-block m-0 mx-2 wd-sm-auto-force px-4" : "btn btn-primary btn-block m-0 mx-2 wd-sm-auto-force px-4");
            if (Session["SHOWPOSTCODER"] != null && (int)Session["SHOWPOSTCODER"] == 1)
                plsHeaderPostcoder.Visible = true;

        }

        protected void OBJ_Selecting(object sender, ObjectDataSourceSelectingEventArgs e)
        {
            if (e.InputParameters.Contains("storeId"))
                e.InputParameters["storeId"] = this.CurrentUser.APIStoreId;
        }

        //protected void lbtnAll_Click(object sender, EventArgs e)
        //{
        //    foreach (ListViewDataItem item in lstProducts.Items)
        //    {
        //        CheckBox chkProductItem = (CheckBox)item.FindControl("chkProductItem");
        //        if (chkProductItem == null || String.IsNullOrEmpty(chkProductItem.Attributes["itemid"]))
        //            continue;

        //        string stit_id = chkProductItem.Attributes["itemid"];
        //        double mrp = 0; try { mrp = Convert.ToDouble(chkProductItem.Attributes["itemmrp"]); } catch { mrp = 0; }
        //        string insSql = $"INSERT IGNORE INTO finascop_stock_branch_inventory (stit_id, branch_id, item_count, mrp, selling_price) SELECT @stit_id, br_ID, 0, @mrp, @mrp FROM finascop_branch WHERE br_storeGroup = @tenantId";
        //        List<KeyValuePair<String, Object>> stockParmeters = new List<KeyValuePair<string, object>>();
        //        stockParmeters.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.APIStoreId));
        //        stockParmeters.Add(new KeyValuePair<string, object>("stit_id", stit_id));
        //        stockParmeters.Add(new KeyValuePair<string, object>("mrp", mrp));

        //        DataServiceMySql.ExecuteSql(insSql, UserService.GetAPIConnectionString(), parmeters: stockParmeters);
        //    }

        //    SDSProducts.Select(DataSourceSelectArguments.Empty);
        //    lstProducts.DataBind();

        //    if (lstProducts.Items.Count > 0)
        //    {
        //        List<KeyValuePair<String, Object>> tenantParmeters = new List<KeyValuePair<string, object>>();
        //        tenantParmeters.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.StoreGroupId));
        //        DataService.ExecuteSql("UPDATE AppTenant SET Stage = 7 WHERE Stage = 6 AND Id=@tenantId", parmeters: tenantParmeters);
        //        Service.UserService.CachedDefaultUser = null;
        //    }
        //}

        protected void lstProducts_DataBound(object sender, EventArgs e)
        {
            DataPager pager = (DataPager)lstProducts.FindControl("DataPager1");
            if (pager != null)
            {
                int startRowOnPage = (pager.StartRowIndex) + 1;
                int lastRowOnPage = startRowOnPage; //startRowOnPage + lstProducts.Items.Count - 1;
                //Literal ltrPagingCurStart = ltrPagingCurStart;
                //Literal ltrPagingCurTotal = ltrPagingCurTotal;
                ltrPagingCurStart.Text = lastRowOnPage.ToString();
                ltrPagingCurTotal.Text = "" + (pager.StartRowIndex + (pager.TotalRowCount > pager.PageSize ? pager.PageSize : pager.TotalRowCount));
            }
        }

        protected void SDSBrands_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            if (e.Command.Parameters.Contains("storeId"))
                e.Command.Parameters["storeId"].Value = this.CurrentUser.APIStoreId;//.StoreGroupId;
            if (e.Command.Parameters.Contains("type"))
            {
                e.Command.Parameters["type"].Value = (rbNotAddedProducts.Checked ? 2 : (rbAddedProducts.Checked ? 1 : 0));
            }
            //if (e.Command.Parameters["@brsearch"] != null && !String.IsNullOrEmpty(e.Command.Parameters["@brsearch"].Value.ToString()))
            //{
            //    e.Command.Parameters["@brsearch"].Value = String.Format("%{0}%", e.Command.Parameters["@brsearch"]);
            //}
        }

        protected void SDSProducts_Selected(object sender, SqlDataSourceStatusEventArgs e)
        {
            DataPager pager = (DataPager)lstProducts.FindControl("DataPager1");
            if (pager != null)
            {
                //pager.SetPageProperties(0, 25, true); //.DataBind();
                // paging controls
                int startRowOnPage = (e.AffectedRows > pager.StartRowIndex ? pager.StartRowIndex + 1 : e.AffectedRows);
                int lastRowOnPage = (e.AffectedRows > pager.MaximumRows ? pager.MaximumRows : e.AffectedRows); // startRowOnPage + lstProducts.Items.Count - 1;

                //Literal ltrPagingCurStart = (Literal)lstProducts.FindControl("ltrPagingCurStart");
                //Literal ltrPagingCurTotal = (Literal)lstProducts.FindControl("ltrPagingCurTotal");

                ltrPagingCurStart.Text = startRowOnPage.ToString();
                ltrPagingCurTotal.Text = lastRowOnPage.ToString();

            }
            int totalRows = e.AffectedRows;
            ltrPagingTotal.Text = totalRows.ToString();
            //ltrTitleCount.Text = ltrPagingTotal.Text;

        }

        protected void selDepartment_SelectedIndexChanged(object sender, EventArgs e)
        {
            if (selDepartment.SelectedIndex > 0)
            {
                DataPager pager = (DataPager)lstProducts.FindControl("DataPager1");
                if (pager != null)
                    pager.SetPageProperties(0, 25, true);
            }
        }

        protected void rbProducts_CheckedChanged(object sender, EventArgs e)
        {
            SDSProducts.Select(DataSourceSelectArguments.Empty);
            lstProducts.DataBind();
            DataPager pager = (DataPager)lstProducts.FindControl("DataPager1");
            if (pager != null)
                pager.SetPageProperties(0, 25, true); //.DataBind();
        }

        protected void Reload_Products(object sender, EventArgs e)
        {
            DataPager pager = (DataPager)lstProducts.FindControl("DataPager1");
            if (pager != null)
                pager.SetPageProperties(0, 25, true);
        }

        protected void selCategory_DataBound(object sender, EventArgs e)
        {
            selCategory.Items.Insert(0, new ListItem("All Categories", "0"));
        }

        protected void selBrand_DataBound(object sender, EventArgs e)
        {
            selBrand.Items.Insert(0, new ListItem("All Brands", "0"));
        }

        protected void btnSaveProducts_Click(object sender, EventArgs e)
        {
            double percent = 0, _qty = 1;
            if (!String.IsNullOrEmpty(txtSelectProductPercentage.Text))
                try { percent = Convert.ToDouble(txtSelectProductPercentage.Text); } catch { percent = 0; }

            if (!String.IsNullOrEmpty(txtSelectProductQuantity.Text))
                try { _qty = Convert.ToDouble(txtSelectProductQuantity.Text); } catch { _qty = 1; }
            if (_qty <= 0)
                _qty = 1;





            List<KeyValuePair<string, object>> inventoryParams = new List<KeyValuePair<string, object>>();
            inventoryParams.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.APIStoreId));
            inventoryParams.Add(new KeyValuePair<string, object>("selectedids", hidSelectedItems.Value));
            inventoryParams.Add(new KeyValuePair<string, object>("qty", _qty));
            inventoryParams.Add(new KeyValuePair<string, object>("percent", percent));

            string delSql = $"delete from finascop_stock_branch_inventory where branch_id in (SELECT br_ID FROM finascop_branch WHERE br_storeGroup = @tenantId) and NOT FIND_IN_SET(stit_id, @selectedids); ";
            List<string> lstInsertSqls = new List<string>();
            int indx = 0;
            //foreach (string strId in hidSelectedItems.Value.Split(','))
            foreach (string strId in hidSelectedItemsWithPrice.Value.Split(','))
            {
                try
                {
                    string strstitid = strId.Split('|')[0];
                    string strmrp = strId.Split('|')[1];
                    int stitid = -1; try { stitid = Convert.ToInt32(strstitid); } catch { }
                    double mrp = 0; try { mrp = Convert.ToDouble(strmrp); } catch { mrp = 0; }
                    if (stitid <= 0 || mrp <= 0)
                        continue;
                    double sellingPrice = mrp;
                    if(percent > 0)
                    {
                        sellingPrice = mrp - (mrp * percent / 100);
                    }
                    inventoryParams.Add(new KeyValuePair<string, object>("stit_id" + indx, stitid));
                    inventoryParams.Add(new KeyValuePair<string, object>("mrp" + indx, mrp));
                    inventoryParams.Add(new KeyValuePair<string, object>("sellingprice" + indx, sellingPrice));
                    string insSql = $"INSERT IGNORE INTO finascop_stock_branch_inventory (stit_id, branch_id, item_count, mrp, selling_price, fpod_customerRateHmDel, fpod_customerRateCouDel, fpod_customerRatePikup, fpod_leastSKUmrp, fpod_poLandingCostleastSKU) SELECT @stit_id{indx}, br_ID, @qty, @mrp{indx}, @sellingprice{indx}, @sellingprice{indx}, @sellingprice{indx}, @sellingprice{indx}, @sellingprice{indx}, @sellingprice{indx} FROM finascop_branch WHERE br_storeGroup = @tenantId";
                    lstInsertSqls.Add(insSql);
                    indx++;
                }
                catch { }
            }
            string insertSql = String.Join("; ", lstInsertSqls.ToArray());
            try
            {
                DataServiceMySql.ExecuteSql(delSql + insertSql, UserService.GetAPIConnectionString(), inventoryParams);

                if (lstProducts.Items.Count > 0 && this.CurrentUser.TenantStage >= 6)
                {
                    List<KeyValuePair<String, Object>> tenantParmeters = new List<KeyValuePair<string, object>>();
                    tenantParmeters.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.StoreGroupId));
                    DataService.ExecuteSql("UPDATE AppTenant SET Stage = 7 WHERE Stage = 6 AND Id=@tenantId", parmeters: tenantParmeters);
                    Service.UserService.CachedDefaultUser = null;
                    //Response.Redirect("/itemsforsale");
                }
                ResetInventory(true);
                SDSProducts.Select(DataSourceSelectArguments.Empty);
                lstProducts.DataBind();

            }
            catch { }

            hidCurTab.Value = "0";
            selPopupBrands.SelectedIndex = 0;
            ShowToastifyMessage("Products added to your store successfully!");
            SDSSelectedProducts.Select(DataSourceSelectArguments.Empty);
            lstSelectedProducts.DataBind();

            //if (common_selling.Checked && common_quantity.Checked)
            //{
            //    //hidCurTab.Value = "3";
            //}
            //else
            //{
            //    hidSelectView.Value = "2";
            //}

        }

        protected void SDSRetCat_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        }

        protected void lbtnConfirmSponsored_Click(object sender, EventArgs e)
        {
            List<KeyValuePair<String, Object>> tenantParmeters = new List<KeyValuePair<string, object>>();
            tenantParmeters.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.StoreGroupId));
            //DataService.ExecuteSql("UPDATE AppTenant SET Stage = 9 WHERE Stage = 8 AND Id=@tenantId", parmeters: tenantParmeters);
            DataService.ExecuteSql("UPDATE AppTenant SET Stage = 1 WHERE Id=@tenantId", parmeters: tenantParmeters);
            Service.UserService.CachedDefaultUser = null;
            Session["SHOWPUBLICNAVHELP"] = true;
            Response.Redirect("/");
        }

        protected void btnCancelSaveProduct_Click(object sender, EventArgs e)
        {
            hidCurTab.Value = "1";
        }

        protected void btnAddProduct_Click(object sender, EventArgs e)
        {
            if (!Page.IsValid)
            {
                Common.ShowToastifyMessage(Page, "Failure. Please ensure all required input values are provided", "danger");
                return;
            }
            if (String.IsNullOrEmpty(selDelMode.Text))
            {
                Common.ShowToastifyMessage(Page, "Failure. Please select delivery type", "danger");
                //ltrResult.Text = "Please select delivery type";
                return;
            }

            List<KeyValuePair<string, object>> hsnParam = new List<KeyValuePair<string, object>>();
            hsnParam.Add(new KeyValuePair<string, object>("hsn", selHSN.SelectedItem.Text));
            DataTable dataTable = DataServiceMySql.GetDataTable($"SELECT hsn_id,hsn_code,gst_percent FROM finascop_hsn WHERE hsn_code=@hsn limit 1", Service.UserService.GetAPIConnectionString(), hsnParam);

            if (dataTable != null && dataTable.Rows.Count > 0)
            {
                DataRow da = dataTable.Rows[0];
                txtGSTVAT.Text = da["gst_percent"].ToString();
            }
            int deliveryType = Convert.ToInt32(selDelMode.Text);
            int courierDeliv = (deliveryType >= 1 ? 1 : 0);
            int directDeliv = (deliveryType >= 2 ? 1 : 0);

            int spotReturn = new int();
            if (chkSpotReturn.Checked)
            {
                spotReturn = 1;
            }
            string checkSpotReturn = Convert.ToString(spotReturn);

            int returnDays = 0;
            if(txtReturn.Text == "")
            {
                returnDays = 0;
            }
            else
            {
                returnDays = Convert.ToInt32(txtReturn.Text);
            }

            List<KeyValuePair<string, object>> sqlPrdParams = new List<KeyValuePair<string, object>>();
            sqlPrdParams.Add(new KeyValuePair<string, object>("sku", txtPrdName.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("businesstypeid", selRetCat.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("categoryid", selCat.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("subcategoryid", selSubCat.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("brand", selBrd.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("variant", txtVarient.Text));
            //sqlPrdParams.Add(new KeyValuePair<string, object>("isMedicine", 0));
            //sqlPrdParams.Add(new KeyValuePair<string, object>("count", 0));
            sqlPrdParams.Add(new KeyValuePair<string, object>("quantity", txtQuantity.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("unit", selUnit.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("hsncode", selHSN.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("gst", txtGSTVAT.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("productName", txtPrdName.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("hsnCodeSelected", selHSN.SelectedItem.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("returndays", returnDays));
            sqlPrdParams.Add(new KeyValuePair<string, object>("foodtype", selFoodType.SelectedValue));
            sqlPrdParams.Add(new KeyValuePair<string, object>("countryid", selCountry.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("courierDelivery", courierDeliv));
            sqlPrdParams.Add(new KeyValuePair<string, object>("directDelivery", directDeliv));
            sqlPrdParams.Add(new KeyValuePair<string, object>("shortdescription", txtShortDescription.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("longdescription", summernote.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("categoryName", selCat.SelectedItem.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("brandName", selBrd.SelectedItem.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.APIStoreId));
            sqlPrdParams.Add(new KeyValuePair<string, object>("spotReturn", checkSpotReturn));
            sqlPrdParams.Add(new KeyValuePair<string, object>("barcode", txtBarcode.Text));
            double mrp=0, stock=0, sellingprice=0, discount=0;
            try { mrp = Convert.ToDouble(mrprrp.Text); } catch { mrp = 0; }
            try { stock = Convert.ToDouble(newquantity.Text); } catch { stock = 0; }
            try { discount = Convert.ToDouble(newPercentage.Text); } catch { discount = 0; }
            sellingprice = mrp;
            try
            {
                if (mrp > 0 && discount > 0)
                    sellingprice = mrp - ((mrp * discount) / 100);
            }
            catch { }
            sqlPrdParams.Add(new KeyValuePair<string, object>("itemmrp", mrp));
            sqlPrdParams.Add(new KeyValuePair<string, object>("stock", stock));
            sqlPrdParams.Add(new KeyValuePair<string, object>("sellingprice", sellingprice));
            try { 
            DataTable dtResult = DataServiceMySql.GetDataTable("addPrivateProduct", UserService.GetAPIConnectionString(), sqlPrdParams, true);
                try
                {
                    if (dtResult != null && dtResult.Rows.Count > 0)
                    {
                        int stitid = Convert.ToInt32(dtResult.Rows[0]["stit_id"]);
                        UploadImages(stitid);
                    }
                }
                catch { }
            SDSProducts.Select(DataSourceSelectArguments.Empty);
            lstProducts.DataBind();
            SDSSelectedProducts.Select(DataSourceSelectArguments.Empty);
            lstSelectedProducts.DataBind();

            hidCurTab.Value = "0";
            Common.ShowToastifyMessage(Page, "Product added to your list of items");
            }
            catch (Exception ex)
            {
                if (ex.Message.EndsWith("for key 'NewIndex1'"))
                {
                    //ParentMessageBinding("Validation failed", "Product name is already existing. You can find the product from the select product from gallery listing to add stock. If you still want to continue, please enter a new name.", 2);
                    Common.ShowToastifyMessage(this.Page, "Product name is already existing. You can find the product from the select product from gallery listing to add stock. If you still want to continue, please enter a new name.", "danger");
                    //lblProductNameResult.Text = "Duplicate name";
                }
                else
                {
                    //ParentMessageBinding("Operation failed", "Error: " + ex.Message, 2);
                    Common.ShowToastifyMessage(this.Page, "Error: " + "Product name is already existing", "danger");
                }
            }
        }

        private void UploadImages(int productid)
        {
            if (productid < 0)
                return;

            List<KeyValuePair<string, object>> sqlInsertParams = new List<KeyValuePair<string, object>>();
            sqlInsertParams.Add(new KeyValuePair<string, object>("prodid", productid));
            sqlInsertParams.Add(new KeyValuePair<string, object>("imgFolder", "products/"));
            string insertSql = ""; bool selectedDefault = false;
            for (int i = 1; i <= 5; i++)
            {
                FileUpload fileUpload = (FileUpload)this.FindControl("imgUpload" + i.ToString());
                if (fileUpload != null && fileUpload.HasFile)
                {
                    string strFile = FileService.UploadImage(fileUpload.PostedFile.InputStream, fileUpload.FileName);
                    if (!string.IsNullOrEmpty(strFile))
                    {
                        sqlInsertParams.Add(new KeyValuePair<string, object>("imgUrl" + i.ToString(), strFile));
                        int imageType = (i > 1 ? 0 : 1);
                        sqlInsertParams.Add(new KeyValuePair<string, object>("imgType" + i.ToString(), imageType));
                        if (!selectedDefault) { imageType = 0; selectedDefault = true; }
                        insertSql += @" INSERT INTO finascop_stock_item_images(product_id, image_url, image_folder, image_type, created_at)
                                            VALUES(@prodid, @imgUrl" + i.ToString() + ", @imgFolder, @imgType" + i.ToString() + ", NOW()); ";

                    }

                }

            }

            if (!String.IsNullOrEmpty(insertSql))
                DataServiceMySql.ExecuteSql(insertSql, UserService.GetAPIConnectionString(), sqlInsertParams);
        }

        //protected void btnNextSelectItems_Click(object sender, EventArgs e)
        //{
        //    double percent = 0, _qty =0;
        //    if (common_selling.Checked && !String.IsNullOrEmpty(Percentage.Text))
        //        try { percent = Convert.ToDouble(Percentage.Text); } catch { percent = 0; }

        //    if (common_quantity.Checked && !String.IsNullOrEmpty(quantity.Text))
        //        try { _qty = Convert.ToDouble(quantity.Text); } catch { _qty = 0; }

        //    if(percent > 0 || _qty > 0)
        //    {
        //        List<KeyValuePair<String, Object>> inventoryUpdateParams = new List<KeyValuePair<string, object>>();
        //        inventoryUpdateParams.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));
        //        string strSql = "";
        //        if(percent > 0) {
        //            strSql = " selling_price = (case when ifnull(selling_price, 0) <=0 and ifnull(mrp, 0) > 0 then (mrp - (mrp * @percent / 100)) else selling_price end) ";
        //            inventoryUpdateParams.Add(new KeyValuePair<string, object>("percent", percent));
        //        }
        //        if (_qty > 0)
        //        {
        //            strSql += (String.IsNullOrEmpty(strSql)? "" : ", ") + " item_count = @count ";
        //            inventoryUpdateParams.Add(new KeyValuePair<string, object>("count", _qty));
        //        }

        //        string sql = $"UPDATE finascop_stock_branch_inventory SET {strSql} WHERE branch_id in (select br_ID from finascop_branch where br_storeGroup = @storegroupid)";
        //        DataServiceMySql.ExecuteSql(sql, UserService.GetAPIConnectionString(), parmeters: inventoryUpdateParams);
        //    }

        //    if (common_selling.Checked && common_quantity.Checked)
        //    {
        //        hidCurTab.Value = "3";
        //    }
        //    else
        //    {
        //        SDSSelectedProducts.Select(DataSourceSelectArguments.Empty);
        //        lstSelectedProducts.DataBind();
        //        hidSelectView.Value = "2";
        //    }
        //}

        //protected void btnNextSelectedProduct_Click(object sender, EventArgs e)
        //{
        //    hidCurTab.Value = "3";
        //}

        protected void btnSaveSelectedProducts_Click(object sender, EventArgs e)
        {
            List<KeyValuePair<string, object>> inventoryParams = new List<KeyValuePair<string, object>>();
            inventoryParams.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.APIStoreId));

            List<string> lstUpdateSqls = new List<string>();
            int indx = 0;

            foreach (var item in lstSelectedProducts.Items)
            {
                try
                {
                    LinkButton lb = (LinkButton)item.FindControl("lbDelItem");
                    if (lb == null || String.IsNullOrEmpty(lb.Attributes["itemid"]))
                        continue;
                    TextBox txtQty = (TextBox)item.FindControl("txtSelectedProductQty");
                    TextBox txtSellingPrice = (TextBox)item.FindControl("txtSelectedProductSellingPrice");
                    double selliPrice = Convert.ToDouble(txtSellingPrice.Text);
                    Label lblMrp = (Label)item.FindControl("lblMrp");
                    double labelMrp = Convert.ToDouble(lblMrp.Text);
                    double qty = 1, sellingPrice = 0; try { qty = Convert.ToDouble(txtQty.Text); } catch { qty = 0; }
                    try { sellingPrice = Convert.ToDouble(txtSellingPrice.Text); } catch { sellingPrice = 0; }
                    if (selliPrice > labelMrp)
                    {
                        Common.ShowToastifyMessage(this.Page, "Selling price should be less than MRP.", "danger");

                    }
                    else
                    {
                        if (qty <= 0 && sellingPrice <= 0)
                            continue;

                        inventoryParams.Add(new KeyValuePair<string, object>("stitid" + indx, lb.Attributes["itemid"]));
                        inventoryParams.Add(new KeyValuePair<string, object>("qty" + indx, qty));
                        inventoryParams.Add(new KeyValuePair<string, object>("sellingprice" + indx, sellingPrice));
                        string insSql = $" UPDATE finascop_stock_branch_inventory SET item_count = @qty{indx}, selling_price = (case when @sellingprice{indx} >0 then @sellingprice{indx} else mrp end) WHERE stit_id = @stitid{indx} and branch_id in (select br_ID FROM finascop_branch WHERE br_storeGroup = @tenantId)";
                        lstUpdateSqls.Add(insSql);
                        indx++;
                    }

                    
                }
                catch { }
            }
            string updateSql = String.Join("; ", lstUpdateSqls.ToArray());
            try
            {
                DataServiceMySql.ExecuteSql(updateSql, UserService.GetAPIConnectionString(), inventoryParams);

                if (lstSelectedProducts.Items.Count > 0 && this.CurrentUser.TenantStage >= 6)
                {
                    List<KeyValuePair<String, Object>> tenantParmeters = new List<KeyValuePair<string, object>>();
                    tenantParmeters.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.StoreGroupId));
                    DataService.ExecuteSql("UPDATE AppTenant SET Stage = 7 WHERE Stage = 6 AND Id=@tenantId", parmeters: tenantParmeters);
                    Service.UserService.CachedDefaultUser = null;
                    //Response.Redirect("/itemsforsale");
                }
                //ResetInventory(true);
                SDSSelectedProducts.Select(DataSourceSelectArguments.Empty);
                lstSelectedProducts.DataBind();

                ShowToastifyMessage("Inventory updated successfully!");

            }
            catch { }

        }

        private void ShowToastifyMessage(string msg, bool isSuccess=true)
        {
            string strToastifySCript = @"Toastify({
                      text: '"+msg+@"',
                      duration: 3000,
                      stopOnFocus: true,
                      className: 'success',
                    }).showToast();";


            Type cstype = this.GetType();
            String csname1 = "AddItemPopupScript";
            ClientScriptManager cs = Page.ClientScript;

            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strToastifySCript} </");
            cstext1.Append("script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }

        protected void DeleteItem_Click(object sender, EventArgs e)
        {
            LinkButton delProductItem = (LinkButton)sender;
            if (delProductItem == null || String.IsNullOrEmpty(delProductItem.Attributes["itemid"]))
                return;

            List<KeyValuePair<string, object>> paramDelItem = new List<KeyValuePair<string, object>>();
            paramDelItem.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));
            paramDelItem.Add(new KeyValuePair<string, object>("itemId", delProductItem.Attributes["itemid"]));

            int storegroupid = this.CurrentUser.APIStoreId;
            string strSql = $"DELETE FROM finascop_stock_branch_inventory WHERE stit_ID= @itemId AND EXISTS(SELECT * FROM finascop_branch WHERE br_ID = finascop_stock_branch_inventory.branch_id AND br_storeGroup= @storegroupid)";
            DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), paramDelItem);
            ResetInventory();

            SDSProducts.Select(DataSourceSelectArguments.Empty);
            lstProducts.DataBind();

            SDSSelectedProducts.Select(DataSourceSelectArguments.Empty);
            lstSelectedProducts.DataBind();
            hidCurTab.Value = "0";
        }

        protected void btnSelectProduct_Click(object sender, EventArgs e)
        {
            hidCurTab.Value = "1";

            //selBrand.Text = "0";
            //selDepartment.Text = "0";
            //selCategory.Text = "0";
            //txtSelectProductName.Text = "";
        }

        protected void btnSelectAddProduct_Click(object sender, EventArgs e)
        {
            hidCurTab.Value = "2";

        }
        protected void btnBrandGalleryNext_Click(object sender, EventArgs e)
        {
            hidCurTab.Value = "0";

        }

        protected void btnAddBrand_Click(object sender, EventArgs e)
        {
            if(String.IsNullOrEmpty(txtBrand.Text))
            {
                ltrAddBrandResult.Text = "Please enter brand name and manufacturer.";
                ShowAddBrandPopup();

                return;
            }

            var brandParams = new List<KeyValuePair<string, object>>();
            brandParams.Add(new KeyValuePair<string, object>("brandname", txtBrand.Text));
            brandParams.Add(new KeyValuePair<string, object>("manufacture", txtManufacturer.Text));
            brandParams.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.APIStoreId));

            //string strSql = $"INSERT INTO mypha_productbrands (brand_name, manufacture_id) SELECT * FROM (SELECT @brandname, ifnull((SELECT manufacture_id FROM `mypha_productmanufacture` WHERE LOWER(TRIM(REPLACE(manufacture_name, ' ', ''))) LIKE LOWER(TRIM(REPLACE(@manufacture, ' ', '')))), 1))tmp WHERE " +
            //    $"NOT EXISTS( SELECT * FROM mypha_productbrands WHERE brand_name = @brandname);";

            int count = 0;
            try
            {
                DataTable dtBrand = DataServiceMySql.GetDataTable("addPrivateBrand", UserService.GetAPIConnectionString(), brandParams, true);
                if(dtBrand != null && dtBrand.Rows.Count > 0)
                {
                    int brandid = Convert.ToInt32(dtBrand.Rows[0][0]);
                    int isnew = Convert.ToInt32(dtBrand.Rows[0][1]);
                    hidSelectedBrand.Value = brandid.ToString();
                    if (brandid > 0)
                    {
                        SDSBrand.Select(DataSourceSelectArguments.Empty);
                        selBrand.DataBind();
                        selBrd.DataBind();
                        var bd = selBrd.Items.FindByValue(brandid.ToString());
                        //selBrand.ClearSelection();
                        if (bd != null)
                        {
                            selBrd.SelectedValue = bd.Value;
                            //selBrd.Items.FindByValue(brandid.ToString()).Selected = true;
                        }
                        count = 1;
                        Common.ShowToastifyMessage(Page, (isnew > 0 ? "Brand created successfully!" : "Brand name is already existing. It is selected in the brand select box to continue" ), (isnew > 0 ? "success" : "info"));
                    }
                }
            }
            catch { count = 0; }
            if(count == 0)
            {
                Common.ShowToastifyMessage(Page, "The brand name already exists or there is a technical problem on creating brand.", "danger");

                //ltrAddBrandResult.Text = "There is a technical problem on creating brand.";
                //ShowAddBrandPopup();
            }

        }

        private void ShowAddBrandPopup()
        {
            Type cstype = this.GetType();
            String csname1 = "ShowBrandPopupScript";
            ClientScriptManager cs = Page.ClientScript;

            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> $('#addbrand').modal('show'); </");
            cstext1.Append("script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }

        protected void selBrd_DataBound(object sender, EventArgs e)
        {
            selBrd.Items.Insert(0, new ListItem("Select Brand", ""));
        }

        protected void selPopupBrands_SelectedIndexChanged(object sender, EventArgs e)
        {
            hidCurTab.Value = "0";
            if (!String.IsNullOrEmpty(selPopupBrands.Text))
            {
                hidCurTab.Value = "1";
                ltrSelectedBrand.Text = selPopupBrands.SelectedItem.Text;
            }

        }

        protected void SDSSelectedProducts_Selected(object sender, SqlDataSourceStatusEventArgs e)
        {
            ltrMyProductsTotal.Text = e.AffectedRows.ToString();
            ltrMyProductsTotal2.Text = e.AffectedRows.ToString();
        }
    }
}