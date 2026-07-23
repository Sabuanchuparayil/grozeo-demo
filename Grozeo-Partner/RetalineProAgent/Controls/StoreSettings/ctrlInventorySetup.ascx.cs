using RetalineProAgent.Core.BussinessModel.Catalog;
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

namespace RetalineProAgent.Controls.StoreSettings
{
    public partial class ctrlInventorySetup: Base.BasePartnerUserControl
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

            txtGSTVAT.Attributes.Add("readonly", "readonly");
            txtManufacturer.Attributes.Add("readonly", "readonly");

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
                DataTable dtInventory = DataServiceMySql.GetDataTable("SELECT bi.stit_id, mrp, mrpid FROM finascop_stock_branch_inventory bi INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id WHERE b.br_storeGroup=@storeId group by stit_id", UserService.GetAPIConnectionString(), inventoryParams);
                hidSelectedItems.Value = String.Join(",", dtInventory.AsEnumerable().Select(item => string.Format("{0}", item["stit_id"])).ToArray());
                hidSelectedItemsWithPrice.Value = String.Join(",", dtInventory.AsEnumerable().Select(item => string.Format("{0}|{1}|{2}", item["stit_id"], item["mrp"], item["mrpid"])).ToArray());
            }
        }
        public bool IsSelected(string stitId, string mrpid="")
        {
            if (!String.IsNullOrEmpty(hidSelectedItems.Value))
            {
                try
                {
                    if (!String.IsNullOrEmpty(mrpid) && mrpid.Trim() != "0")
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
                //ltrPagingCurStart.Text = lastRowOnPage.ToString();
                //ltrPagingCurTotal.Text = "" + (pager.StartRowIndex + (pager.TotalRowCount > pager.PageSize ? pager.PageSize : pager.TotalRowCount));
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

                //ltrPagingCurStart.Text = startRowOnPage.ToString();
                //ltrPagingCurTotal.Text = lastRowOnPage.ToString();

            }
            int totalRows = e.AffectedRows;
            //ltrPagingTotal.Text = totalRows.ToString();
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
            double percent = 0, _qty = 0;
            List<KeyValuePair<string, object>> inventoryParams = new List<KeyValuePair<string, object>>();
            inventoryParams.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.APIStoreId));
            inventoryParams.Add(new KeyValuePair<string, object>("selectedids", hidSelectedItems.Value));
            inventoryParams.Add(new KeyValuePair<string, object>("qty", _qty));
            inventoryParams.Add(new KeyValuePair<string, object>("percent", percent));

            string delSql = $"delete from finascop_stock_branch_inventory where branch_id in (SELECT br_ID FROM finascop_branch WHERE br_storeGroup = @tenantId) and NOT FIND_IN_SET(stit_id, @selectedids); ";
            List<string> lstInsertSqls = new List<string>();
            int indx = 0;
            //foreach (string strId in hidSelectedItems.Value.Split(','))
            //{
            //    inventoryParams.Add(new KeyValuePair<string, object>("stit_id" + indx, strId));
            //    string insSql = $"INSERT IGNORE INTO finascop_stock_branch_inventory (stit_id, branch_id, item_count, mrp, selling_price) SELECT @stit_id{indx}, br_ID, 0, 0, 0 FROM finascop_branch WHERE br_storeGroup = @tenantId";
            //    lstInsertSqls.Add(insSql);
            //    indx++;
            //}
            foreach (string strId in hidSelectedItemsWithPrice.Value.Split(','))
            {
                try
                {
                    string strstitid = strId.Split('|')[0];
                    string strmrp = strId.Split('|')[1];
                    int mrpid = 0; try { if (strId.Split('|').Length > 1 && !String.IsNullOrEmpty(strId.Split('|')[2])) mrpid = Convert.ToInt32(strId.Split('|')[2]); } catch { mrpid = 0; }
                    int stitid = -1; try { stitid = Convert.ToInt32(strstitid); } catch { }
                    double mrp = 0; try { mrp = Convert.ToDouble(strmrp); } catch { mrp = 0; }
                    if (stitid <= 0 || mrp <= 0)
                        continue;
                    double sellingPrice = mrp;
                    if (percent > 0)
                    {
                        sellingPrice = mrp - (mrp * percent / 100);
                    }
                    inventoryParams.Add(new KeyValuePair<string, object>("stit_id" + indx, stitid));
                    inventoryParams.Add(new KeyValuePair<string, object>("mrp" + indx, mrp));
                    inventoryParams.Add(new KeyValuePair<string, object>("sellingprice" + indx, sellingPrice));
                    inventoryParams.Add(new KeyValuePair<string, object>("mrpid" + indx, mrpid));
                    string insSql = $"INSERT IGNORE INTO finascop_stock_branch_inventory (stit_id, branch_id, item_count, mrp, selling_price, fpod_customerRateHmDel, fpod_customerRateCouDel, fpod_customerRatePikup, fpod_leastSKUmrp, fpod_poLandingCostleastSKU, mrpid) SELECT @stit_id{indx}, br_ID, @qty, @mrp{indx}, @sellingprice{indx}, @sellingprice{indx}, @sellingprice{indx}, @sellingprice{indx}, @sellingprice{indx}, @sellingprice{indx}, @mrpid{indx} FROM finascop_branch WHERE br_storeGroup = @tenantId";
                    //string insSql = $"INSERT IGNORE INTO finascop_stock_branch_inventory (stit_id, branch_id, item_count, mrp, selling_price) SELECT @stit_id{indx}, br_ID, 0, 0, 0 FROM finascop_branch WHERE br_storeGroup = @tenantId";
                    lstInsertSqls.Add(insSql);
                    indx++;
                }
                catch { }
            }

            string insertSql = String.Join("; ", lstInsertSqls.ToArray());
            try
            {
                DataServiceMySql.ExecuteSql(delSql + insertSql, UserService.GetAPIConnectionString(), inventoryParams);

                inventoryParams = new List<KeyValuePair<string, object>>();
                inventoryParams.Add(new KeyValuePair<string, object>("storeId", this.CurrentUser.APIStoreId));
                DataTable dtInventory = DataServiceMySql.GetDataTable("SELECT bi.stit_id, mrp, mrpid FROM finascop_stock_branch_inventory bi INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id WHERE b.br_storeGroup=@storeId group by stit_id", UserService.GetAPIConnectionString(), inventoryParams);
                hidSelectedItems.Value = String.Join(",", dtInventory.AsEnumerable().Select(item => string.Format("{0}", item["stit_id"])).ToArray());
                hidSelectedItemsWithPrice.Value = String.Join(",", dtInventory.AsEnumerable().Select(item => string.Format("{0}|{1}|{2}", item["stit_id"], item["mrp"], item["mrpid"])).ToArray());

                if (lstProducts.Items.Count > 0 && this.CurrentUser.TenantStage >= 6)
                {
                    List<KeyValuePair<String, Object>> tenantParmeters = new List<KeyValuePair<string, object>>();
                    tenantParmeters.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.StoreGroupId));
                    DataService.ExecuteSql("UPDATE AppTenant SET Stage = 7 WHERE Stage = 6 AND Id=@tenantId", parmeters: tenantParmeters);
                    Service.UserService.CachedDefaultUser = null;

                    Response.Redirect("/itemsforsale");
                }
                else
                {
                    SDSProducts.Select(DataSourceSelectArguments.Empty);
                    lstProducts.DataBind();
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

            //Check duplicate product
            //List<KeyValuePair<string, object>> paramDuplicate = new List<KeyValuePair<string, object>>();
            //paramDuplicate.Add(new KeyValuePair<string, object>("itemname", txtPrdName.Text));
            //string strDuplicateSql = "SELECT itemname_id,item_name FROM finascop_stock_itemmastername WHERE item_name LIKE @itemname limit 1";
            //DataTable dtDuplicateItem = DataServiceMySql.GetDataTable(strDuplicateSql, Service.UserService.GetAPIConnectionString(), parmeters: paramDuplicate);

            //if (dtDuplicateItem != null && dtDuplicateItem.Rows.Count > 0)
            //{
            //    ltrResult.Text = "Item name is already existing. Please select the item from the gallery or change your item name.";
            //    return;
            //}
            //else
            //{
            //    //Insert new product
            //List<KeyValuePair<string, object>> sqlParams = new List<KeyValuePair<string, object>>();
            ////sqlParams.Add(new KeyValuePair<string, object>("productId", privateInventoryId));
            //sqlParams.Add(new KeyValuePair<string, object>("productName", txtPrdName.Text));
            ////sqlParams.Add(new KeyValuePair<string, object>("createdOn", createdOn));
            ////sqlParams.Add(new KeyValuePair<string, object>("updatedOn", updatedOn));
            //sqlParams.Add(new KeyValuePair<string, object>("storegroupId", this.CurrentUser.APIStoreId));
            //string strSql = $"INSERT INTO finascop_stock_itemmastername(item_name,isItemGroup,status,itemStoreGroup) " +
            //    $"VALUES(@productName,0,1,@storegroupId); select LAST_INSERT_ID() ";
            //var result = DataServiceMySql.ExecuteScalar(strSql, Service.UserService.GetAPIConnectionString(), sqlParams);
            //int itemnameId = Convert.ToInt32(result);//Take new product Id
            ////List<KeyValuePair<string, object>> sqlId = new List<KeyValuePair<string, object>>();
            ////    sqlId.Add(new KeyValuePair<string, object>("itemId", itemnameId));
            //    string dtUniqueItem = "SELECT fsi_uid,fsi_item_id,fsi_item_name,fsi_count,fsi_brand_id,fsi_brand_name," +
            //        "fsi_category_id,fsi_categry_name,fsi_variant,fsi_def_itemmaster_id,isMedicine FROM finascop_stock_uniqueitem WHERE fsi_item_id=@itemnameId AND isMedicine=0";
            //    DataTable uniqueItemdt = DataServiceMySql.GetDataTable(dtUniqueItem, Service.UserService.GetAPIConnectionString());
            //string uniqueItemId = null;
            //string itemCount = null;
            //string itemIds = null;
            //if (uniqueItemdt.Rows.Count > 0)
            //{

            //    uniqueItemId = uniqueItemdt.Rows[0]["fsi_uid"].ToString();
            //    itemIds = uniqueItemdt.Rows[0]["fsi_item_id"].ToString();
            //    itemCount = uniqueItemdt.Rows[0]["fsi_count"].ToString();
            //}
            //int itemId = Convert.ToInt32(uniqueItemId);
            //int itmIds = Convert.ToInt32(itemIds);
            //int itemCnt = Convert.ToInt32(itemCount);

            string dtProductMaster = "SELECT itemname_id,item_name,isPrivate FROM finascop_stock_itemmastername WHERE isPrivate=1";
                DataTable productMasterId = DataServiceMySql.GetDataTable(dtProductMaster, Service.UserService.GetAPIConnectionString());
            string prdMasterId = null;
            string prdName = null;
            if (productMasterId.Rows.Count > 0)
            {
                prdMasterId = productMasterId.Rows[0]["itemname_id"].ToString();
                prdName = productMasterId.Rows[0]["item_name"].ToString();
            }
            int prdMastId = Convert.ToInt32(prdMasterId);
            string productMasterName = prdName;
            //List<KeyValuePair<string, object>> sqlProductId = new List<KeyValuePair<string, object>>();
            ////sqlItemId.Add(new KeyValuePair<string, object>("itemId", itemnameId));
            //sqlProductId.Add(new KeyValuePair<string, object>("prdMastId", prdMastId));
            //sqlProductId.Add(new KeyValuePair<string, object>("isMedicine", 0));
            //string dtUniqueItem = "SELECT fsi_uid,fsi_item_id,fsi_item_name,fsi_count,fsi_brand_id,fsi_brand_name," +
            //        "fsi_category_id,fsi_categry_name,fsi_variant,fsi_def_itemmaster_id,isMedicine FROM finascop_stock_uniqueitem WHERE fsi_item_id=@prdMastId AND isMedicine=0";
            //    DataTable uniqueItemdt = DataServiceMySql.GetDataTable(dtUniqueItem, Service.UserService.GetAPIConnectionString(), sqlProductId);
            //if (uniqueItemdt != null && uniqueItemdt.Rows.Count > 0)
            //    {
            //List<KeyValuePair<string, object>> sqlItemId = new List<KeyValuePair<string, object>>();
            ////sqlItemId.Add(new KeyValuePair<string, object>("itemId", itemnameId));
            //sqlItemId.Add(new KeyValuePair<string, object>("itemnameId", prdMastId));
            //sqlItemId.Add(new KeyValuePair<string, object>("isMedicine", 0));
            //string strUpdate = $"UPDATE finascop_stock_uniqueitem SET fsi_count=fsi_count+1 WHERE fsi_item_id=@itemnameId AND isMedicine=@isMedicine";
            //DataServiceMySql.ExecuteSql(strUpdate, UserService.GetAPIConnectionString(), sqlItemId);
            //}
            //else
            //{
            List<KeyValuePair<string, object>> sqlPrdParams = new List<KeyValuePair<string, object>>();
            sqlPrdParams.Add(new KeyValuePair<string, object>("itemId", prdMastId));
            sqlPrdParams.Add(new KeyValuePair<string, object>("brand", selBrd.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("variant", txtVarient.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("isMedicine", 0));
            sqlPrdParams.Add(new KeyValuePair<string, object>("productName", productMasterName));
            sqlPrdParams.Add(new KeyValuePair<string, object>("count", 0));
            sqlPrdParams.Add(new KeyValuePair<string, object>("brandName", selBrd.SelectedItem.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("categoryid", selCat.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("categoryName", selCat.SelectedItem.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("sku", txtPrdName.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("businesstypeid", selRetCat.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("subcategoryid", selSubCat.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("quantity", txtQuantity.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("unit", selUnit.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("hsncode", selHSN.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("gst", txtGSTVAT.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("hsnCodeSelected", selHSN.SelectedItem.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("returndays", selDays.SelectedItem.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("foodtype", selFoodType.SelectedValue));
            sqlPrdParams.Add(new KeyValuePair<string, object>("countryid", selCountry.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("courierDelivery", courierDeliv));
            sqlPrdParams.Add(new KeyValuePair<string, object>("directDelivery", directDeliv));
            sqlPrdParams.Add(new KeyValuePair<string, object>("shortdescription", TextBox1.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("longdescription", summernote.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.APIStoreId));
            
            DataServiceMySql.ExecuteSP("addPrivateProduct", UserService.GetAPIConnectionString(), sqlPrdParams);

            SDSProducts.Select(DataSourceSelectArguments.Empty);
            lstProducts.DataBind();
               
            hidShowAddForm.Value = "0";
        }

        protected void btnSave_Click(object sender, EventArgs e)
        {
            List<KeyValuePair<string, object>> sqlBrandParams = new List<KeyValuePair<string, object>>();
            sqlBrandParams.Add(new KeyValuePair<string, object>("brandName", txtBrand.Text));
            sqlBrandParams.Add(new KeyValuePair<string, object>("manufacturer", 1));
            sqlBrandParams.Add(new KeyValuePair<string, object>("storegroupId", this.CurrentUser.APIStoreId));
            string dtBrand = "SELECT brand_id,brand_name FROM mypha_productbrands WHERE brand_name LIKE @brandName limit 1";
            DataTable newBrand = DataServiceMySql.GetDataTable(dtBrand, Service.UserService.GetAPIConnectionString(), sqlBrandParams);
            if (newBrand != null && newBrand.Rows.Count > 0)
            {
                ltrResult.Text = "Brand is already existing. Please add new brand.";
                    return;
            }
            else
            {
                List<KeyValuePair<string, object>> sqlBrandPrms = new List<KeyValuePair<string, object>>();
                sqlBrandPrms.Add(new KeyValuePair<string, object>("brandName", txtBrand.Text));
                sqlBrandPrms.Add(new KeyValuePair<string, object>("manufacturer", 1));
                sqlBrandPrms.Add(new KeyValuePair<string, object>("storegroupId", this.CurrentUser.APIStoreId));
                string strSql = $"INSERT INTO mypha_productbrands(brand_name,manufacture_id,storegroup_id) " +
                    $"VALUES(@brandName,@manufacturer,@storegroupId)";
                DataServiceMySql.ExecuteScalar(strSql, Service.UserService.GetAPIConnectionString(), sqlBrandPrms);
            }
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

        protected void chkStatus_CheckedChanged(object sender, EventArgs e)
        {
            //CheckBox chbtn = (CheckBox)sender;
            ////Label lbl = (Label)chbtn.Parent.FindControl("lblRdDefaultBrnach1");

            ////string strbrid = lbl.Attributes["brid"];

            //if (chbtn != null && !String.IsNullOrEmpty(chbtn.Attributes["itemId"]))
            //{
            //    int itemId = Convert.ToInt32(chbtn.Attributes["itemId"]);
            //    int returnStatus = (chbtn.Checked ? 1 : 0);
            //    foreach (TextBox gr in lstProducts.Controls)
            //    {
            //        TextBox txtQty = (TextBox)gr.FindControl("txtReturnDays");

            //        List<KeyValuePair<string, object>> itemparams = new List<KeyValuePair<string, object>>();
            //        itemparams.Add(new KeyValuePair<string, object>("itemId", itemId));
            //        itemparams.Add(new KeyValuePair<string, object>("returnStatus", returnStatus));
            //        itemparams.Add(new KeyValuePair<string, object>("returnDays", txtQty));
            //        itemparams.Add(new KeyValuePair<string, object>("storeGroupId", this.CurrentUser.APIStoreId));
            //        string strSql = "UPDATE finascop_stock_itemmaster SET stit_custInitiate=@returnStatus , stit_itemReturnTime=@returnDays WHERE stit_ID=@itemId and br_storeGroup=@storeGroupId";
            //        DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), itemparams);
            //    }
            //}

            //lstProducts.DataBind();
        }



    }
}