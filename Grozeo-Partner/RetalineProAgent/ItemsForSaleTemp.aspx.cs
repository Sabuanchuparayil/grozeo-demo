using RetalineProAgent.Core.BussinessModel.Store;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class ItemsForSaleTemp: Base.BasePartnerPage
    {
        List<Store> _myBranches = null;
        List<Store> MyBranches 
        {
            get {
                
                if(_myBranches == null)
                {
                    _myBranches=Core.Services.APIService.GetStores(Convert.ToInt32(APIStoreGroupId));
                }
                return _myBranches;
            }
            set { _myBranches = value; }
        }

        private string _apiStoreGroupId = "";
        private string APIStoreGroupId
        {
            get
            {
                if (String.IsNullOrEmpty(_apiStoreGroupId))
                {
                    int _apiStoreId = this.CurrentUser.APIStoreId;
                    _apiStoreGroupId = _apiStoreId.ToString();

                    if (_apiStoreId < 1)
                    {
                        _apiStoreGroupId = "";
                        var dt = DataService.GetDataTable("SELECT * FROM AppTenant WHERE Id=" + this.CurrentUser.StoreGroupId);
                        if (dt != null && dt.Rows.Count > 0)
                        {
                            string strStoregroupid = dt.Rows[0]["StoreId"].ToString();
                            if (!String.IsNullOrEmpty(strStoregroupid))
                                _apiStoreGroupId = strStoregroupid;
                        }

                    }
                }
                return _apiStoreGroupId;
            }
        }
        protected void Page_Load(object sender, EventArgs e)
        {
            lblResult.Text = "";
        }

        protected void Page_PreRender(object sender, EventArgs e)
        {
            if(selBranches.Items.Count < 1)
            {
                selBranches.DataBind();
            }
        }
        protected void SDSInventory_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["@storeId"].Value = this.CurrentUser.StoreGroupId;
            e.Command.Parameters["@user"].Value = Page.User.Identity.Name;
            if (selBranches.Items.Count < 1)
                selBranches.DataBind();

            if(selBranches.Items.Count ==2)
                e.Command.Parameters["@BranchId"].Value = selBranches.Items[1].Value;
            else if (selBranches.Items.Count > 0 && !String.IsNullOrEmpty(selBranches.Text))
                e.Command.Parameters["@BranchId"].Value = selBranches.Text;

        }

        protected void SDSInventory_Selected(object sender, SqlDataSourceStatusEventArgs e)
        {
            //plcPrice.Visible = e.AffectedRows > 0;
            //ltrStrPriceDefaultText.Visible = plcPrice.Visible;
            //ctrlInventorySetup1.SelectedItemsCount = 
            //ltrTotalItemsSelected.Text= 
            //lblSelectedCount.Text = e.AffectedRows.ToString();

            // paging controls
            int startRowOnPage = (gvProducts.PageIndex * gvProducts.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvProducts.Rows.Count - 1;
            int totalRows = e.AffectedRows;

            ltrPagingCurStart.Text = startRowOnPage.ToString();
            ltrPagingCurTotal.Text = lastRowOnPage.ToString();
            ltrPagingTotal.Text = totalRows.ToString();
            //count2.Text = "Showing " + startRowOnPage.ToString() +
            //              " - " + lastRowOnPage + " of " + totalRows;
            
        }

        protected void ODSStore_Selecting(object sender, ObjectDataSourceSelectingEventArgs e)
        {
            e.InputParameters["storegroupid"] = APIStoreGroupId;
        }

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvProducts.PageIndex > 0)
                gvProducts.PageIndex = gvProducts.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvProducts.PageIndex < gvProducts.PageCount - 1)
                gvProducts.PageIndex = gvProducts.PageIndex + 1;
        }

        protected void gvProducts_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvProducts.PageIndex * gvProducts.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvProducts.Rows.Count - 1;
            ltrPagingCurTotal.Text = lastRowOnPage.ToString();

            //var dv = (DataView)SDSInventory.Select(DataSourceSelectArguments.Empty);
            //var drs = dv.ToTable().Select("MRP is null or MRP < 1");
            //if (drs != null && drs.Length > 0)
            //{
            //    ltrComment.Text = $"{drs.Length} out of {dv.Count} records are missing MRP or Quantity. Please enter value to these records also. Otherwise these items will not be published to site.";
            //}

        }
        protected void chkProductItem_CheckedChanged(object sender, EventArgs e)
        {
            CheckBox chkProductItem = (CheckBox)sender;
            if (chkProductItem == null)
                return;

            int storegroupid = this.CurrentUser.StoreGroupId;
            DataTable dt = new DataTable();
            dt.Columns.Add("Id", typeof(int));

            DataRow dr = dt.NewRow();
            dr["Id"] = chkProductItem.Attributes["itemid"];
            dt.Rows.Add(dr);
            List<KeyValuePair<String, Object>> parmeters = new List<KeyValuePair<string, object>>();
            parmeters.Add(new KeyValuePair<string, object>("StoreId", storegroupid));
            parmeters.Add(new KeyValuePair<string, object>("IDs", dt));
            DataService.ExecuteSP(sp: "DeleteInventoryMapping", parmeters: parmeters);

            SDSInventory.Select(DataSourceSelectArguments.Empty);
            gvProducts.DataBind();
            //ctrlInventorySetup1.ResetInventory();
        }

        protected void btnStockSaveChanges_Click(object sender, EventArgs e)
        {

            List<Store> myBranches = MyBranches.Where(b => plcSelectBranchModel.Visible == false || selBranches.Text == b.BranchId.ToString()).ToList();
            if (myBranches == null || myBranches.Count < 1)
            {
                lblResult.Text = "Failure! Your store location is not active.";
                lblResult.ForeColor = System.Drawing.Color.Red;
                return;
            }
            lblResult.Text = "";

            Store myBranch = null;
            if (myBranches.Count == 1)
            {
                myBranch = myBranches[0];
            }
            else
            {
                if (selBranches.Items.Count > 1 && myBranches.Any(b => b.BranchId.ToString() == selBranches.Text))
                    myBranch = myBranches.FirstOrDefault(b => b.BranchId.ToString() == selBranches.Text);
                else
                    myBranch = myBranches[0];
            }

            DataTable dt = new DataTable();
            dt.Columns.Add("Id", typeof(int));
            dt.Columns.Add("ErpId", typeof(string));
            dt.Columns.Add("StoreErpId", typeof(string));
            dt.Columns.Add("StoreId", typeof(int));
            dt.Columns.Add("Description", typeof(string));
            dt.Columns.Add("Qty", typeof(float));
            dt.Columns.Add("MRP", typeof(float));
            dt.Columns.Add("SellingPrice", typeof(float));
            dt.Columns.Add("Margin", typeof(float));

            DataView viewgvData = (DataView)SDSInventory.Select(DataSourceSelectArguments.Empty);
            if (viewgvData == null || viewgvData.Count < 1)
                return;

            DataTable dtgvData = viewgvData.ToTable();

            foreach (GridViewRow gr in gvProducts.Rows)
            {
                TextBox txtMrp = (TextBox)gr.FindControl("txtMRP");
                if (String.IsNullOrEmpty(txtMrp.Text))
                    continue;

                //TextBox txtSellingPrice = (TextBox)gr.FindControl("txtSellingPrice");
                TextBox txtPStock = (TextBox)gr.FindControl("txtPStock");
                //TextBox txtPCustomMargin = (TextBox)gr.FindControl("txtPCustomMargine");
                int gvid = (int)gvProducts.DataKeys[gr.RowIndex].Values[0];

                DataRow[] dtresult = dtgvData.Select("Id = " + gvid);

                double mrp = Convert.ToInt32(txtMrp.Text);
                double pStock = Convert.ToInt32(txtPStock.Text);
                double pCustomMargin = 5; // Convert.ToInt32(txtPCustomMargin.Text);
                if (dtresult == null || dtresult.Length < 1 || dtresult[0]["Margin"] is DBNull)
                    continue;

                pCustomMargin = (double)dtresult[0]["Margin"];
                if (pCustomMargin < 5)
                    pCustomMargin = 5;

                double sellingPrice = mrp - ((mrp * pCustomMargin)/ 100);

                DataRow dr = dt.NewRow();
                dr["Id"] = gvProducts.DataKeys[gr.RowIndex].Values[0];
                dr["SellingPrice"] = sellingPrice;
                dr["Qty"] = pStock;
                dr["MRP"] = mrp;
                dr["Margin"] = pCustomMargin;
                dt.Rows.Add(dr);
            }
            if (dt.Rows.Count > 0)
            {
                int storegroupid = this.CurrentUser.StoreGroupId;
                List<KeyValuePair<String, Object>> parmeters = new List<KeyValuePair<string, object>>();
                parmeters.Add(new KeyValuePair<string, object>("storeId", storegroupid));
                parmeters.Add(new KeyValuePair<string, object>("BranchId", myBranch.BranchId));
                parmeters.Add(new KeyValuePair<string, object>("tblInventory", dt));
                //DataService.ExecuteSP(sp: "BulkUpdateInventoryMapping", parmeters: parmeters);
                DataService.ExecuteSP(sp: "UpdateCurrentStock", parmeters: parmeters);
                // UpdateCurrentStock
            }
        }

        protected void btnStockPublishItems_Click(object sender, EventArgs e)
        {
            List<Store> myBranches = MyBranches.Where(b => plcSelectBranchModel.Visible = false || selBranches.Text == "-1" || selBranches.Text == b.BranchId.ToString()).ToList();
            if (myBranches == null || myBranches.Count < 1)
            {
                lblResult.Text = "Failure! Your store location is not active.";
                lblResult.ForeColor = System.Drawing.Color.Red;
                return;
            }
            lblResult.Text = "";

            Store myBranch = null;
            if (myBranches.Count == 1)
            {
                myBranch = myBranches[0];
            }
            else
            {
                if (selBranches.Items.Count > 1 && myBranches.Any(b => b.BranchId.ToString() == selBranches.Text))
                    myBranch = myBranches.FirstOrDefault(b => b.BranchId.ToString() == selBranches.Text);
                else
                    myBranch = myBranches[0];
            }


            // UploadInventory
            List<InventoryAPI> inventory = new List<InventoryAPI>();

            string sql = $"SELECT * FROM BranchCurrentStock WHERE BranchId={myBranch.BranchId} and StoreId="+ this.CurrentUser.StoreGroupId;
            DataTable dt = DataService.GetDataTable(sql);
            int missingMRP=0, missingQty=0;
            foreach(DataRow dr in dt.Rows)
            {
                string erpId = dr["InventoryId"].ToString();
                if(string.IsNullOrEmpty(erpId))
                    continue;

                if(dr["Qty"] is DBNull)
                {
                    missingQty++;
                    continue;
                }

                double qty = (double)dr["Qty"];
                if(qty <= 0)
                {
                    missingQty++;
                    continue;
                }
                if (dr["MRP"] is DBNull)
                {
                    missingMRP++;
                    continue;
                }
                double mrp = (double)dr["MRP"];
                if (mrp <= 0)
                {
                    missingMRP++;
                    continue;
                }
                double margin = 0; 
                if (dr["Margin"] is DBNull)
                    margin = (double)dr["Margin"];

                if (margin < 5)
                    margin = 5;

                double sellingPrice = mrp - ((mrp * margin) / 100);
                InventoryAPI stock = new InventoryAPI();
                stock.ErpId = erpId;
                stock.SellingPrice = sellingPrice;
                stock.Qty = qty;
                stock.MRP = mrp;
                inventory.Add(stock);
            }

            //foreach (GridViewRow gr in gvProducts.Rows)
            //{
            //    TextBox txtMrp = (TextBox)gr.FindControl("txtMRP");
            //    TextBox txtSellingPrice = (TextBox)gr.FindControl("txtSellingPrice");
            //    TextBox txtPStock = (TextBox)gr.FindControl("txtPStock");
            //    TextBox txtPCustomMargin = (TextBox)gr.FindControl("txtPCustomMargine");
            //    int mrp = Convert.ToInt32(txtMrp.Text);
            //    int sellingPrice = Convert.ToInt32(txtSellingPrice.Text);
            //    int pStock = Convert.ToInt32(txtPStock.Text);
            //    int pCustomMargin = Convert.ToInt32(txtPCustomMargin.Text);
            //    if (pCustomMargin < 5)
            //        pCustomMargin = 5;

            //    InventoryAPI stock = new InventoryAPI();
            //    stock.ErpId = gvProducts.DataKeys[gr.RowIndex].Values[0].ToString();
            //    stock.SellingPrice = sellingPrice;
            //    stock.Qty = pStock;
            //    stock.MRP = mrp;
            //    inventory.Add(stock);
            //}

            if (inventory.Count > 0)
            {
                Core.Services.APIService.UploadInventory(myBranch.APIKey, inventory);
                lblResult.Text += $" Published {inventory.Count} items to {myBranch.BranchName}.";
                //foreach (var item in myBranches)
                //{

                //}
                lblResult.ForeColor = System.Drawing.Color.Green;

            }
            else
            {
                lblResult.Text = "No item published.";
                lblResult.ForeColor = System.Drawing.Color.Red;
            }
            if (missingMRP > 0)
                lblResult.Text += $" Items missing MRP: {missingMRP}.";
            if (missingQty > 0)
                lblResult.Text += $" Items missing Quantity: {missingQty}.";

        }

        protected void ODSStore_Selected(object sender, ObjectDataSourceStatusEventArgs e)
        {
            MyBranches = (List<Store>)e.ReturnValue;
            if (MyBranches != null)
            {
                ltrBranchName.Visible = MyBranches.Count == 1;
                ltrBranchName.Text = MyBranches[0].BranchName;
                plcSelectBranchModel.Visible = MyBranches.Count > 1;

            }
                //if( MyBranches.Count > 1)
                //{

                //    plcSelectBranchModel.Visible = true;
                //    btnStockPublishItems.Attributes.Add("data-toggle", "modal");
                //    btnStockPublishItems.Attributes.Add("target", "#modal-select-branch");
                //    btnStockPublishItems.Visible = false;
                //    plcMultipleBranchButton.Visible = true;
                //}
        }

        protected void gvProducts_RowDataBound(object sender, GridViewRowEventArgs e)
        {
            try
            {
                if (DataBinder.Eval(e.Row.DataItem, "MRP") == null)
                    return;

                double? mrp = (double?)DataBinder.Eval(e.Row.DataItem, "MRP");
                if (DataBinder.Eval(e.Row.DataItem, "Margin") == null)
                    return;

                double? margin = (double?)DataBinder.Eval(e.Row.DataItem, "Margin");
                Label lblPCustomMarginVal = (Label)e.Row.FindControl("lblPCustomMarginVal");
                Label lblSellingPrice = (Label)e.Row.FindControl("lblSellingPrice");

                if (mrp != null && margin != null && mrp > 0 && margin > 0)
                {
                    lblPCustomMarginVal.Text = ((mrp * margin) / 100).ToString();
                    lblSellingPrice.Text = (mrp - ((mrp * margin) / 100)).ToString();
                }
            }
            catch { }
        }

        protected void selBranches_SelectedIndexChanged(object sender, EventArgs e)
        {
            gvProducts.DataBind();
        }
    }
}