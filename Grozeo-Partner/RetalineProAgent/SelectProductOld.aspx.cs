using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class SelectProductOld: Base.BasePartnerPage
    {
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
            ltrResult.Text = "";

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
                List<KeyValuePair<string, object>> inventoryParams = new List<KeyValuePair<string, object>>();
                inventoryParams.Add(new KeyValuePair<string, object>("storeId", this.CurrentUser.APIStoreId));
                DataTable dtInventory = DataServiceMySql.GetDataTable("SELECT bi.stit_id FROM finascop_stock_branch_inventory bi INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id WHERE b.br_storeGroup=@storeId group by stit_id", UserService.GetAPIConnectionString(), inventoryParams);
                hidSelectedItems.Value = String.Join(",", dtInventory.AsEnumerable().Select(item => string.Format("{0}", item["stit_id"])).ToArray());
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
            //btnSaveProducts.CssClass = (this.CurrentUser.TenantStage == 7 ? "btn btn-primary disabled btn-block mx-2 wd-sm-auto-force px-4" : "btn btn-primary btn-block mx-2 wd-sm-auto-force px-4");
            hlSaveProductsMoveNext.CssClass = (this.CurrentUser.TenantStage == 6 ? "btn btn-primary disabled btn-block m-0 mx-2 wd-sm-auto-force px-4" : "btn btn-primary btn-block m-0 mx-2 wd-sm-auto-force px-4");

            if (hidShowAddForm.Value == "1")
                ShowAddItem();

        }

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
            SDSProducts.Select(DataSourceSelectArguments.Empty);
            lstProducts.DataBind();

            if (lstProducts.Items.Count > 0)
            {
                List<KeyValuePair<String, Object>> tenantParmeters = new List<KeyValuePair<string, object>>();
                tenantParmeters.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.StoreGroupId));
                DataService.ExecuteSql("UPDATE AppTenant SET Stage = 7 WHERE Stage = 6 AND Id=@tenantId", parmeters: tenantParmeters);
                Service.UserService.CachedDefaultUser = null;
            }

            //if (parent is RepeaterItem)
            //{
            //    int storegroupid = this.CurrentUser.StoreGroupId;
            //    Literal ltrProductItemDesc = (Literal)parent.FindControl("ltrProductItemDesc");
            //    Literal ltrProductItemMrp = (Literal)parent.FindControl("ltrProductItemMrp");

            //    if (chkProductItem != null)
            //    {
            //        DataTable dt = new DataTable();
            //        dt.Columns.Add("Id", typeof(int));

            //        if (chkProductItem.Checked)
            //        {
            //            if (!InventoryMap.Any(i => i.Id == Convert.ToInt32(chkProductItem.Attributes["itemid"])))
            //            {
            //                InventoryMap.Add(new Core.BussinessModel.Inventory.ItemMaster() { Id = Convert.ToInt32(chkProductItem.Attributes["itemid"]) });
            //                dt.Columns.Add("ErpId", typeof(string));
            //                dt.Columns.Add("StoreErpId", typeof(string));
            //                dt.Columns.Add("StoreId", typeof(string));
            //                dt.Columns.Add("Description", typeof(string));
            //                dt.Columns.Add("Qty", typeof(float));
            //                dt.Columns.Add("MRP", typeof(float));
            //                DataRow dr = dt.NewRow();
            //                dr["Id"] = chkProductItem.Attributes["itemid"];
            //                dr["StoreId"] = storegroupid;
            //                dr["Description"] = ltrProductItemDesc.Text;
            //                //dr["MRP"] = ltrProductItemMrp.Text;
            //                dt.Rows.Add(dr);
            //                DataService.InventoryMapingBulkInsert(dt);
            //            }
            //        }
            //        else
            //        {
            //            if (InventoryMap.Any(i => i.Id == Convert.ToInt32(chkProductItem.Attributes["itemid"])))
            //                InventoryMap.Remove(InventoryMap.FirstOrDefault(i => i.Id == Convert.ToInt32(chkProductItem.Attributes["itemid"])));
            //            DataRow dr = dt.NewRow();
            //            dr["Id"] = chkProductItem.Attributes["itemid"];
            //            dt.Rows.Add(dr);
            //            List<KeyValuePair<String, Object>> parmeters = new List<KeyValuePair<string, object>>();
            //            parmeters.Add(new KeyValuePair<string, object>("StoreId", storegroupid));
            //            parmeters.Add(new KeyValuePair<string, object>("IDs", dt));
            //            DataService.ExecuteSP(sp: "DeleteInventoryMapping", parmeters: parmeters);
            //        }

            //        lstProducts.DataBind();
            //        if(RefreshInventoryBinding != null)
            //            RefreshInventoryBinding(sender);
            //    }
            //}
        }

        protected void OBJ_Selecting(object sender, ObjectDataSourceSelectingEventArgs e)
        {
            if (e.InputParameters.Contains("storeId"))
                e.InputParameters["storeId"] = this.CurrentUser.APIStoreId;//.StoreGroupId;
        }

        protected void lbtnAll_Click(object sender, EventArgs e)
        {
            foreach (ListViewDataItem item in lstProducts.Items)
            {
                CheckBox chkProductItem = (CheckBox)item.FindControl("chkProductItem");
                if (chkProductItem == null || String.IsNullOrEmpty(chkProductItem.Attributes["itemid"]))
                    continue;

                //var parent = chkProductItem.Parent;
                //if (parent == null)
                //    return;

                string stit_id = chkProductItem.Attributes["itemid"];
                //string mrp = chkProductItem.Attributes["itemmrp"]; // 
                double mrp = 0; try { mrp = Convert.ToDouble(chkProductItem.Attributes["itemmrp"]); } catch { mrp = 0; }
                //string delSql = $"delete from finascop_stock_branch_inventory where stit_id= @stit_id and branch_id in (SELECT br_ID FROM finascop_branch WHERE br_storeGroup = @tenantId)";
                string insSql = $"INSERT IGNORE INTO finascop_stock_branch_inventory (stit_id, branch_id, item_count, mrp, selling_price) SELECT @stit_id, br_ID, 0, @mrp, @mrp FROM finascop_branch WHERE br_storeGroup = @tenantId";
                List<KeyValuePair<String, Object>> stockParmeters = new List<KeyValuePair<string, object>>();
                stockParmeters.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.APIStoreId));
                stockParmeters.Add(new KeyValuePair<string, object>("stit_id", stit_id));
                //if (chkProductItem.Checked)
                stockParmeters.Add(new KeyValuePair<string, object>("mrp", mrp));

                //if (chkProductItem.Checked)
                DataServiceMySql.ExecuteSql(insSql, UserService.GetAPIConnectionString(), parmeters: stockParmeters);
                //else
                //    DataServiceMySql.ExecuteSql(delSql, UserService.GetAPIConnectionString(), parmeters: stockParmeters);

                SelectedItemId = Convert.ToInt32(stit_id);
            }

            SDSProducts.Select(DataSourceSelectArguments.Empty);
            lstProducts.DataBind();

            if (lstProducts.Items.Count > 0)
            {
                List<KeyValuePair<String, Object>> tenantParmeters = new List<KeyValuePair<string, object>>();
                tenantParmeters.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.StoreGroupId));
                DataService.ExecuteSql("UPDATE AppTenant SET Stage = 7 WHERE Stage = 6 AND Id=@tenantId", parmeters: tenantParmeters);
                Service.UserService.CachedDefaultUser = null;
            }
        }

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

        protected void lstProducts_ItemDataBound(object sender, ListViewItemEventArgs e)
        {

            //Core.BussinessModel.Inventory.Products products = (Core.BussinessModel.Inventory.Products)e.Item.DataItem;
            //if(products != null)
            //{
            //    ltrTotalProducts.Text = products.Total.ToString();
            //    ltrTotalProductsCurrentIndex.Text = products.From.ToString();
            //    ltrTotalProductsListed.Text = products.To.ToString();
            //    lbtnProductPagerLeft.Enabled = (products.To > 20);
            //    lbtnProductPagerRight.Enabled = (products.To < products.Total);
            //}
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
                {
                    pager.SetPageProperties(0, 25, true);
                    //var categories = (List<CategoryData>)ODSCategoriesDirect.Select();
                    //if (categories != null)
                    //{
                    //    var selectedCategory = categories.Where(c => c.ParentCategoryId.ToString() == selDepartment.Text).FirstOrDefault();
                    //    if (selectedCategory != null)
                    //    {
                    //        selCategory.DataSource = selectedCategory.Subcategories;
                    //        selCategory.DataBind();
                    //        selCategory.Items.Insert(0, new ListItem("All Categories", "0"));
                    //    }
                    //}
                }

            }


        }

        protected void DeleteItem_Click(object sender, EventArgs e)
        {
            LinkButton delProductItem = (LinkButton)sender;
            if (delProductItem == null)
                return;

            int storegroupid = this.CurrentUser.APIStoreId;
            string strSql = $"DELETE FROM finascop_stock_branch_inventory WHERE stit_ID={delProductItem.Attributes["itemid"]} AND EXISTS(SELECT * FROM finascop_branch WHERE br_ID = finascop_stock_branch_inventory.branch_id AND br_storeGroup={storegroupid})";
            DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString());

            SDSProducts.Select(DataSourceSelectArguments.Empty);
            lstProducts.DataBind();
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
                pager.SetPageProperties(0, 25, true); //.DataBind();

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
            List<KeyValuePair<string, object>> inventoryParams = new List<KeyValuePair<string, object>>();
            inventoryParams.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.APIStoreId));
            inventoryParams.Add(new KeyValuePair<string, object>("selectedids", hidSelectedItems.Value));

            string delSql = $"delete from finascop_stock_branch_inventory where branch_id in (SELECT br_ID FROM finascop_branch WHERE br_storeGroup = @tenantId) and NOT FIND_IN_SET(stit_id, @selectedids); ";
            List<string> lstInsertSqls = new List<string>();
            int indx = 0;
            foreach (string strId in hidSelectedItems.Value.Split(','))
            {
                inventoryParams.Add(new KeyValuePair<string, object>("stit_id" + indx, strId));
                string insSql = $"INSERT IGNORE INTO finascop_stock_branch_inventory (stit_id, branch_id, item_count, mrp, selling_price) SELECT @stit_id{indx}, br_ID, 0, 0, 0 FROM finascop_branch WHERE br_storeGroup = @tenantId";
                lstInsertSqls.Add(insSql);
                indx++;
            }
            string insertSql = String.Join("; ", lstInsertSqls.ToArray());
            try
            {
                DataServiceMySql.ExecuteSql(delSql + insertSql, UserService.GetAPIConnectionString(), inventoryParams);

                if (lstProducts.Items.Count > 0 && this.CurrentUser.TenantStage >= 6)
                {
                    List<KeyValuePair<String, Object>> tenantParmeters = new List<KeyValuePair<string, object>>();
                    tenantParmeters.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.StoreGroupId));
                    DataService.ExecuteSql("UPDATE AppTenant SET Stage = 1 WHERE Stage = 6 AND Id=@tenantId", parmeters: tenantParmeters);
                    Service.UserService.CachedDefaultUser = null;

                    //Response.Redirect("/itemsforsale");
                }
            }
            catch { }
        }

        protected void SDSRetCat_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        }

        protected void btnSubmit_Click(object sender, EventArgs e)
        {
            List<KeyValuePair<string, object>> hsnParam = new List<KeyValuePair<string, object>>();
            hsnParam.Add(new KeyValuePair<string, object>("hsn", selHSN.SelectedItem.Text));
            DataTable dataTable = DataServiceMySql.GetDataTable($"SELECT hsn_id,hsn_code,gst_percent FROM finascop_hsn WHERE hsn_code=@hsn limit 1", Service.UserService.GetAPIConnectionString(), hsnParam);

            if (dataTable != null && dataTable.Rows.Count > 0)
            {
                DataRow da = dataTable.Rows[0];
                txtGSTVAT.Text = da["gst_percent"].ToString();
            }
            //DataTable dataTbl = DataServiceMySql.GetDataTable($"SELECT unit_id,unit_name FROM mypha_unit WHERE unit_id = '" + selUnit.Text + "'", Service.UserService.GetAPIConnectionString());
            //string unit = null;
            //if (dataTbl != null && dataTbl.Rows.Count > 0)
            //{
            //    DataRow da = dataTbl.Rows[0];
            //    unit = da["unit_name"].ToString(); ;
            //}
            if (String.IsNullOrEmpty(selDelMode.Text))
            {
                ltrResult.Text = "Please select delivery type";
                return;
            }
            int deliveryType = Convert.ToInt32(selDelMode.Text);
            int courierDeliv = (deliveryType >= 1 ? 1 : 0);
            int directDeliv = (deliveryType >= 2 ? 1 : 0);

            //string createdOn = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
            //string updatedOn = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");

            List<KeyValuePair<string, object>> paramDuplicate = new List<KeyValuePair<string, object>>();
            paramDuplicate.Add(new KeyValuePair<string, object>("itemname", txtPrdName.Text));
            string strDuplicateSql = "select itemname_id,item_name from finascop_stock_itemmastername where item_name like @itemname limit 1";
            DataTable dtDuplicateItem = DataServiceMySql.GetDataTable(strDuplicateSql, Service.UserService.GetAPIConnectionString(), parmeters: paramDuplicate);

            if (dtDuplicateItem != null && dtDuplicateItem.Rows.Count > 0)
            {
                ltrResult.Text = "Item name is already existing. Please select the item from the gallery or change your item name.";
                return;
            }

            List<KeyValuePair<string, object>> sqlParams = new List<KeyValuePair<string, object>>();
            //sqlParams.Add(new KeyValuePair<string, object>("productId", privateInventoryId));
            sqlParams.Add(new KeyValuePair<string, object>("productName", txtPrdName.Text));
            //sqlParams.Add(new KeyValuePair<string, object>("createdOn", createdOn));
            //sqlParams.Add(new KeyValuePair<string, object>("updatedOn", updatedOn));
            sqlParams.Add(new KeyValuePair<string, object>("storegroupId", this.CurrentUser.APIStoreId));
            string strSql = $"INSERT INTO finascop_stock_itemmastername(item_name,isItemGroup,status,itemStoreGroup) " +
                $"VALUES(@productName,0,1,@storegroupId); select LAST_INSERT_ID() ";
            var result = DataServiceMySql.ExecuteScalar(strSql, Service.UserService.GetAPIConnectionString(), sqlParams);
            int itemId = Convert.ToInt32(result);

            string sku = selBrd.SelectedItem.Text + " " + txtPrdName.Text + " " + txtVarient.Text;
            List<KeyValuePair<string, object>> sqlItemsParams = new List<KeyValuePair<string, object>>();
            sqlItemsParams.Add(new KeyValuePair<string, object>("itemId", itemId));
            sqlItemsParams.Add(new KeyValuePair<string, object>("sku", sku));
            sqlItemsParams.Add(new KeyValuePair<string, object>("businesstypeid", selRetCat.Text));
            sqlItemsParams.Add(new KeyValuePair<string, object>("categoryid", selCat.Text));
            sqlItemsParams.Add(new KeyValuePair<string, object>("subcategoryid", selSubCat.Text));
            sqlItemsParams.Add(new KeyValuePair<string, object>("brand", selBrd.Text));
            sqlItemsParams.Add(new KeyValuePair<string, object>("variant", txtVarient.Text));
            sqlItemsParams.Add(new KeyValuePair<string, object>("quantity", txtQuantity.Text));
            sqlItemsParams.Add(new KeyValuePair<string, object>("unit", selUnit.Text));
            sqlItemsParams.Add(new KeyValuePair<string, object>("hsncode", selHSN.Text));
            sqlItemsParams.Add(new KeyValuePair<string, object>("gst", txtGSTVAT.Text));
            sqlItemsParams.Add(new KeyValuePair<string, object>("productName", txtPrdName.Text));
            sqlItemsParams.Add(new KeyValuePair<string, object>("hsnCodeSelected", selHSN.SelectedItem.Text));
            sqlItemsParams.Add(new KeyValuePair<string, object>("returndays", selDays.SelectedItem.Text));
            sqlItemsParams.Add(new KeyValuePair<string, object>("foodtype", selFoodType.SelectedValue));
            sqlItemsParams.Add(new KeyValuePair<string, object>("countryid", selCountry.Text));
            sqlItemsParams.Add(new KeyValuePair<string, object>("courierDelivery", courierDeliv));
            sqlItemsParams.Add(new KeyValuePair<string, object>("directDelivery", directDeliv));
            sqlItemsParams.Add(new KeyValuePair<string, object>("shortdescription", TextBox1.Text));
            sqlItemsParams.Add(new KeyValuePair<string, object>("longdescription", summernote.Text));
            //sqlItemsParams.Add(new KeyValuePair<string, object>("createdOn", createdOn));
            //sqlItemsParams.Add(new KeyValuePair<string, object>("updatedOn", updatedOn));
            sqlItemsParams.Add(new KeyValuePair<string, object>("categoryName", selCat.SelectedItem.Text));
            sqlItemsParams.Add(new KeyValuePair<string, object>("brandName", selBrd.SelectedItem.Text));
            sqlItemsParams.Add(new KeyValuePair<string, object>("storegroupId", this.CurrentUser.APIStoreId));
            string strSql1 = $"INSERT INTO finascop_stock_itemmaster(stit_itemId,stit_itemReturnTime,stit_SKU,stit_HSNCode," +
                $"stit_GST,stit_Description,stit_product_variant,pdt_brand,product_category,stit_quantity,stit_long_description,stit_itemName,stit_HSN_code," +
                $"stit_category_name,stit_brand_name,courierDelivery,directDelivery,stit_foodtype,stit_orgin_country,stit_unit,stit_qty,stit_StoreGroup) " +
                $"VALUES(@itemId,@returndays,@sku,@hsncode,@gst,@shortdescription,@variant,@brand,@categoryid,@quantity,@longdescription,@productName,@hsnCodeSelected," +
                $"@categoryName,@brandName,@courierDelivery,@directDelivery,@foodtype,@countryid,@unit,@quantity,@storegroupId); " +
                $" INSERT IGNORE INTO finascop_stock_branch_inventory (stit_id, branch_id, item_count, mrp, selling_price) SELECT  LAST_INSERT_ID(), br_ID, 0, 0, 0 FROM finascop_branch WHERE br_storeGroup = @storegroupId ";
            DataServiceMySql.ExecuteSql(strSql1, Service.UserService.GetAPIConnectionString(), sqlItemsParams);

            SDSProducts.Select(DataSourceSelectArguments.Empty);
            lstProducts.DataBind();
            //ShowSuccess("Item Added Successfully!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Your item added successfully!</a></h5>");

            //if (itemId > 0)
            //{

            //}
            //else
            //{
            //    string strUpdateSql = $"UPDATE privateInventory SET product_name=@productName, business_type_id=@businesstypeid,category_id=@categoryid," +
            //        $"sub_category_id=@subcategoryid, variant=@variant,quantity=@quantity, unit=@unit,hsn_code=@hsncode, gst=@gst,return_days=@returndays," +
            //        $"food_type=@foodtype,country_id=@countryid,courierDelivery=@courierDelivery,directDeliver=@directDelivery,short_description=@shortdescription," +
            //        $"long_description=@longdescription, updatedOn=@updatedOn WHERE id=@productId";
            //    DataServiceMySql.ExecuteScalar(strUpdateSql, Service.UserService.GetAPIConnectionString(), sqlParams);

            //}

            hidShowAddForm.Value = "0";

        }
        private void ShowAddItem()
        {
            Type cstype = this.GetType();
            String csname1 = "AddItemPopupScript";
            ClientScriptManager cs = Page.ClientScript;

            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append("<script type=text/javascript> $('#create_new_product').modal('show'); </");
            cstext1.Append("script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }


    }
}