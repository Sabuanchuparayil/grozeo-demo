using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using RetalineProAgent.Core.BussinessModel.Store;
using RetalineProAgent.Service;
using RetalineProAgent.Core.Services;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class PrivateCatItemsSettings: Base.BasePartnerPage
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
        public int SelectedItemId { get; set; }

        public void ResetInventory(bool resetProductsList = true)
        {

            int storegroupid = this.CurrentUser.APIStoreId;
            var dt = DataServiceMySql.GetDataTable($"SELECT DISTINCT stit_id FROM finascop_stock_branch_inventory bi INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id WHERE b.br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());

            InventoryMap = (from row in dt.AsEnumerable()
                            select new Core.BussinessModel.Inventory.ItemMaster()
                            {
                                Id = (int)row.Field<Int64>("stit_id"),
                                //ErpId = row.Field<string>("ErpId")
                            }).ToList();
        }
        protected void Page_Load(object sender, EventArgs e)
        {

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

        protected void chkProductItem_CheckedChanged(object sender, EventArgs e)
        {
            //CheckBox chkProductItem = (CheckBox)sender;
            //if (chkProductItem == null)
            //    return;
            //string virtualCategoryId = chkProductItem.Attributes["virtualCat_id"];
            //string productId = chkProductItem.Attributes["itemid"];
            //string productType = chkProductItem.Attributes["itemType"];
            //var datatable = DataServiceMySql.GetDataTable($"SELECT vc_id,stpi_id,stit_type,IF(stit_type = 1,'Medicine','Product') AS itemType," +
            //    $"finascop_stock_itemmaster.stit_id AS itemId,stit_SKU AS itemName, stit_brand_name, stit_quantity, least_package_type_name, " +
            //    $"stit_category_name FROM retaline_vc_items WHERE vc_id = {virtualCategoryId} AND stit_id = {productId} AND stit_type = {productType}", UserService.GetAPIConnectionString());
            //if(datatable == null)
            //{
            //     if (chkProductItem.Checked)
            //        {
            //            string insertQry = $"INSERT INTO retaline_vc_items(stit_type, vc_id, stit_id) " +
            //                                $"VALUES(" + virtualCategoryId + " ,'" + productId + "','" + productType + "')";
            //            DataServiceMySql.ExecuteSql(insertQry, Service.UserService.GetAPIConnectionString());
            //            Response.Write("<script>alert('Products added successfully')</script>");
            //            Response.Redirect("~/PrivateCatItems");
            //        }
            //}
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

        protected void SDSBrands_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            if (e.Command.Parameters.Contains("storeId"))
                e.Command.Parameters["storeId"].Value = this.CurrentUser.APIStoreId;//.StoreGroupId;
            if (e.Command.Parameters.Contains("type"))
            {
                // e.Command.Parameters["type"].Value = (rbNotAddedProducts.Checked ? 2 : (rbAddedProducts.Checked ? 1 : 0));
            }
        }

        protected void btnSaveProducts_Click(object sender, EventArgs e)
        {
            CheckBox chkProductItem = (CheckBox)sender;
            if (chkProductItem == null)
                return;
            string virtualCategoryId = chkProductItem.Attributes["virtualCat_id"];
            string productId = chkProductItem.Attributes["itemid"];
            string productType = chkProductItem.Attributes["itemType"];
            var datatable = DataServiceMySql.GetDataTable($"SELECT vc_id,stpi_id,stit_type,IF(stit_type = 1,'Medicine','Product') AS itemType," +
                $"finascop_stock_itemmaster.stit_id AS itemId,stit_SKU AS itemName, stit_brand_name, stit_quantity, least_package_type_name, " +
                $"stit_category_name FROM retaline_vc_items WHERE vc_id = {virtualCategoryId} AND stit_id = {productId} AND stit_type = {productType}", UserService.GetAPIConnectionString());
            if (datatable == null)
            {
                if (chkProductItem.Checked)
                {
                    string insertQry = $"INSERT INTO retaline_vc_items(stit_type, vc_id, stit_id) " +
                                        $"VALUES(" + virtualCategoryId + " ,'" + productId + "','" + productType + "')";
                    DataServiceMySql.ExecuteSql(insertQry, Service.UserService.GetAPIConnectionString());
                    Response.Write("<script>alert('Products added successfully')</script>");
                    Response.Redirect("~/PrivateCatItems");
                }
            }
        }
    }

}


