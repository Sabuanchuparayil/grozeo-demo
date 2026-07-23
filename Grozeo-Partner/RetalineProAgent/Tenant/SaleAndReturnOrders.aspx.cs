using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using RestSharp;
using RetalineProAgent.Core.BussinessModel.Finance;
using RetalineProAgent.Core.BussinessModel.Store;
using RetalineProAgent.Core.Services;
//using RetalineProAgent.Core.BussinessModel.OnlineOrders;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Data.SqlTypes;
using System.Dynamic;
using System.IO;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class SaleAndReturnOrders: Base.BasePartnerPage
    {
        public int FilterType
        {
            get
            {
                if (ViewState["ORDFILTERTYPE"] == null)
                    return 0;
                else
                    return (int)ViewState["ORDFILTERTYPE"];
            }
            set
            {
                ViewState["ORDFILTERTYPE"] = value;
            }
        }

        protected void btnFilterType_Click(object sender, EventArgs e)
        {
            LinkButton btn = (LinkButton)sender;
            if (btn != null && !String.IsNullOrEmpty(btn.Attributes["typeid"]))
            {
                int btypeid = Convert.ToInt32(btn.Attributes["typeid"]);
                FilterType = btypeid;
                hidFilterType.Value = btypeid.ToString();
                ltrTitle.Text = btn.Text;
            }
        }
        protected void Page_Load(object sender, EventArgs e)
        {
            int storegroupid = this.CurrentUser.APIStoreId;
            var dtBranches = DataServiceMySql.GetDataTable($"SELECT br_ID,br_name FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
            if (dtBranches != null && dtBranches.Rows.Count > 0)
            {
                DataRow dr = dtBranches.Rows[0];
                string branchName = dr["br_name"].ToString();

                var btStoreGrp = DataServiceMySql.GetDataTable($"SELECT COUNT(br_storeGroup) AS cnt FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
                if (btStoreGrp != null && btStoreGrp.Rows.Count > 0)
                {
                    DataRow dc = btStoreGrp.Rows[0];
                    string storeGroup = dc["cnt"].ToString();
                    if (Convert.ToInt32(storeGroup) == 1)
                    {
                        branchname.Visible = true;
                        branchname.Value = dr["br_name"].ToString();
                    }
                    else
                    {
                        branchname.Visible = false;
                    }
                }


            }
            if (!IsPostBack && String.IsNullOrEmpty(hidFilterType.Value))
            {
                FilterType = 1; hidFilterType.Value = "1";
                ltrTitle.Text = "Sales & Orders";
                txtDateFrom.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
                txtDateTo.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));

            }

                if (gvPendingOrders.HeaderRow != null)
                gvPendingOrders.HeaderRow.TableSection = TableRowSection.TableHeader;
        }

        protected void selBranches_SelectedIndexChanged(object sender, EventArgs e)
        {
            gvPendingOrders.PageIndex = 0;
            gvPendingOrders.DataBind();
            //ltrBranchName.Text = (selBranches.SelectedIndex >= 0 ? selBranches.SelectedItem.Text : "");
            int branchId = Convert.ToInt32(selBranch.SelectedValue);
            Session["SelectedBranchId"] = branchId;
        }

        protected void selBranches_DataBound(object sender, EventArgs e)
        {
            //MyBranches = (List<Store>)e.ReturnValue;
            //plcSelectBranchModel.Visible = selBranch.Items.Count > 2;
            if (selBranch.Items.Count > 1)
            {
                plcSelectBranchModel.Visible = selBranch.Items.Count > 2;

            }
           
        }

        protected void SDSBranches_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
            if (Page.User.IsInRole("BranchManager"))
            {
                int brid = UserService.UserRoleBranchId;
                e.Command.Parameters["branchid"].Value = brid;
            }
           
        }

        


        protected void Page_PreRender(object sender, EventArgs e)
        {
            lbtnPending.CssClass = String.Format("btn btn-block btn-outline-primary {0}", (FilterType == 1 ? "active" : ""));
            lbtnViewAll.CssClass = String.Format("btn btn-block btn-outline-primary {0}", (FilterType == 2 ? "active" : ""));

            if (selBranch.Items.Count < 1)
            {
                selBranch.DataBind();
            }
        }

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvPendingOrders.PageIndex > 0)
                gvPendingOrders.PageIndex = gvPendingOrders.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvPendingOrders.PageIndex < gvPendingOrders.PageCount - 1)
                gvPendingOrders.PageIndex = gvPendingOrders.PageIndex + 1;
        }

        protected void gvPendingOrders_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvPendingOrders.PageIndex * gvPendingOrders.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvPendingOrders.Rows.Count - 1;
            //ltrPageCurTotal.Text = lastRowOnPage.ToString();
            //ltrPageCurStart.Text = (gvPendingOrders.Rows.Count > 0 ? startRowOnPage : 0).ToString();
            var dv = (DataView)SDSPendingOrders.Select(DataSourceSelectArguments.Empty);
        }

        protected void SDSPendingOrders_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
            hidFilterType.Value = FilterType.ToString();
            e.Command.Parameters["filterType"].Value = FilterType;
            if (Page.User.IsInRole("BranchManager"))
            {
                int brid = UserService.UserRoleBranchId;
                e.Command.Parameters["branchid"].Value = brid;
            }
        }
        protected void SDS_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
            if (Page.User.IsInRole("BranchManager") && e.Command.Parameters.Contains("branchid"))
            {
                int brid = UserService.UserRoleBranchId;
                e.Command.Parameters["branchid"].Value = brid;
            }

        }

        protected void SDSPendingOrders_Selected(object sender, SqlDataSourceStatusEventArgs e)
        {
            //ltrPageTotal.Text = e.AffectedRows.ToString();
        }

        //private void ExportGridToExcel()
        //{
        //    DataView dv = (DataView)SDSPendingOrders.Select(DataSourceSelectArguments.Empty);
        //    DataTable dt = dv.ToTable();
        //    IWorkbook wb = new XSSFWorkbook();
        //    ISheet sheet = wb.CreateSheet("Data1");
        //    ICreationHelper cH = wb.GetCreationHelper();
        //    int rows = 0;
        //    IRow rowH = sheet.CreateRow(rows++);

        //    foreach (DataControlField dc in gvForExportOnly.Columns)
        //    {
        //        ICell cell = rowH.CreateCell(rowH.Cells.Count);
        //        cell.SetCellValue(cH.CreateRichTextString(dc.HeaderText));
        //    }

        //    foreach (DataRow dr in dt.Rows)
        //    {
        //        IRow row = sheet.CreateRow(rows++);
        //        for (int j = 0; j < gvForExportOnly.Columns.Count; j++)
        //        {
        //            ICell cell = row.CreateCell(j);
        //            cell.SetCellValue(cH.CreateRichTextString(dr[gvForExportOnly.Columns[j].SortExpression].ToString()));
        //        }
        //    }

        //    Response.Clear();
        //    Response.Buffer = true;
        //    Response.Charset = "";
        //    Response.ContentType = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
        //    Response.AddHeader("content-disposition", "attachment;filename=Orders.xlsx");
        //    wb.Write(Response.OutputStream);

        //    Response.Flush();
        //    Response.End();

        //}

        //protected void lbtnDownloadExcel_Click(object sender, EventArgs e)
        //{
        //    ExportGridToExcel();
        //}

        protected void btnDownloadWithDateRange_Click(object sender, EventArgs e)
        {
            DateTime fromDate, toDate;
            if (DateTime.TryParseExact(txtDateFromModal.Text, "yyyy-MM-dd", null, System.Globalization.DateTimeStyles.None, out fromDate) &&
                DateTime.TryParseExact(txtDateToModal.Text, "yyyy-MM-dd", null, System.Globalization.DateTimeStyles.None, out toDate))
            {
                // Ensure the date range does not exceed 31 days
                if ((toDate - fromDate).Days > 31)
                { 
                    Common.ShowToastifyMessage(this.Page, "'Please select a date range of 31 days or less.", "danger");
                    return;
                }

                DataView dv = (DataView)SDSPendingOrders.Select(DataSourceSelectArguments.Empty);
                DataTable dt = dv.ToTable();
                var filteredRows = dt.AsEnumerable()
                    .Where(row =>
                    {
                        string createdAtString = row.Field<string>("created_at");
                        if (DateTime.TryParse(createdAtString, out DateTime createdAt))
                        {
                            return createdAt.Date >= fromDate.Date && createdAt.Date <= toDate.Date;
                        }
                        else
                        {
                            return false;
                        }
                    });

                if (!filteredRows.Any())
                {
                    Common.ShowToastifyMessage(this.Page, "'No data available for the selected date range.", "danger");
                    return;
                }

                DataTable filteredTable = filteredRows.CopyToDataTable();
                ExportDataToExcel(filteredTable);
            }
            else
            {
                Common.ShowToastifyMessage(this.Page, "'Invalid date format. Please enter dates in yyyy-MM-dd format.", "danger");
            }
        }

        private void ExportDataToExcel(DataTable dt)
        {
            IWorkbook workbook = new XSSFWorkbook();
            ISheet sheet = workbook.CreateSheet("Orders");
            ICreationHelper creationHelper = workbook.GetCreationHelper();
            IRow headerRow = sheet.CreateRow(0);
            int columnIndex = 0;
            foreach (DataControlField column in gvForExportOnly.Columns)
            {
                NPOI.SS.UserModel.ICell headerCell = headerRow.CreateCell(columnIndex++);
                headerCell.SetCellValue(creationHelper.CreateRichTextString(column.HeaderText));
            }
            int rowIndex = 1;
            foreach (DataRow dataRow in dt.Rows)
            {
                IRow excelRow = sheet.CreateRow(rowIndex++);
                columnIndex = 0;
                foreach (DataControlField column in gvForExportOnly.Columns)
                {
                    string dataField = ((BoundField)column).DataField; 
                    if (dataRow.Table.Columns.Contains(dataField))
                    {
                        NPOI.SS.UserModel.ICell dataCell = excelRow.CreateCell(columnIndex++);
                        string cellValue = dataRow[dataField]?.ToString() ?? string.Empty;
                        dataCell.SetCellValue(creationHelper.CreateRichTextString(cellValue));
                    }
                }
            }
            
            Response.Clear();
            Response.Buffer = true;
            Response.Charset = "";
            Response.ContentType = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
            Response.AddHeader("content-disposition", "attachment;filename=Orders.xlsx");
            workbook.Write(Response.OutputStream);
            Response.Flush();
            Response.End();
        }

        protected void btnRevoke_Click(object sender, EventArgs e)
        {
            Button btnRevoke = (Button)sender;

            string orderId = Convert.ToString(btnRevoke.Attributes["transferOrderId"]);
            List<KeyValuePair<string, object>> sqlId = new List<KeyValuePair<string, object>>();
            sqlId.Add(new KeyValuePair<string, object>("toId", orderId));
            var boyId = "SELECT id,boy_id,order_pk_id FROM retaline_godown_boy_orders_request WHERE order_pk_id=@toId";
            DataTable orderBoyId = DataServiceMySql.GetDataTable(boyId, Service.UserService.GetAPIConnectionString(), sqlId);
            if (btnRevoke == null || String.IsNullOrEmpty(btnRevoke.Attributes["orderpickerid"]))
            {
                // show error
                return;
            }
            string boy = null;
            string boyPkId = null;
            if (orderBoyId.Rows.Count > 0)
            {

                boy = orderBoyId.Rows[0]["boy_id"].ToString();
                boyPkId = orderBoyId.Rows[0]["order_pk_id"].ToString();

            }
            int orderNum = Convert.ToInt32(boy);
            int orderPIckerId = Convert.ToInt32(boyPkId);
            int storegroupid = this.CurrentUser.APIStoreId;

            //string orderPIckerId = Convert.ToString(btnRevoke.Attributes["orderpickerid"]);
            ////string orderNum = Request.QueryString["ordernum"];

            ////string orderId = Convert.ToString(Request.QueryString["toid"]);
            //string orderNumber = Convert.ToString(btnRevoke.Attributes["customerorderId"]);
            //int orderNum = Convert.ToInt32(orderNumber);

            string result = Core.Services.APIService.Revoke(orderNum, orderPIckerId);

            // show result as status.
            int status = Convert.ToInt32(result);

            Page.ClientScript.RegisterClientScriptBlock(typeof(string), "Revoked",
                @"<script language='javascript'>$(document).ready(function () {showSuccess('Order Revoked Successfully.'); window.location.href='/Tenant/PendingOrders'; }); </script>");

        }

        protected void gvPendingOrders_RowDataBound(object sender, GridViewRowEventArgs e)
        {
            //  data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo"
            e.Row.Attributes.Add("data-toggle", "collapse");
            e.Row.Attributes.Add("data-target", String.Format("#collapse{0}", e.Row.DataItemIndex));
            e.Row.Attributes.Add("aria-expanded", "false");
            e.Row.Attributes.Add("aria-controls", String.Format("collapse{0}", e.Row.DataItemIndex));

        }

        protected void lbtnWishlist_Click(object sender, EventArgs e)
        {

            if (selBranch.SelectedValue == "-1")
            {
                int storegroupid = this.CurrentUser.APIStoreId;
                var getBranches = DataServiceMySql.GetDataTable($"SELECT br_ID,br_name FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
                if (getBranches != null && getBranches.Rows.Count > 0)
                {
                    DataRow dr = getBranches.Rows[0];
                    int branchId = Convert.ToInt32(dr["br_ID"].ToString());
                    Response.Redirect("/Tenant/CustomerWishlist?BranchId=" + branchId);
                }


            }
            else
            {
                int branchId = Convert.ToInt32(selBranch.SelectedValue);
                Response.Redirect("/Tenant/CustomerWishlist?BranchId=" + branchId);
            }

        }

        protected void gvPendingOrders_RowCommand(object sender, GridViewCommandEventArgs e)
        {
            if (e.CommandName == "Update")
            {
                string[] args = e.CommandArgument.ToString().Split('|');
                string orderId = args[0];
                int newRuleType = Convert.ToInt32(args[1]);

                SDSPendingOrders.UpdateParameters["order_id"].DefaultValue = orderId;
                SDSPendingOrders.UpdateParameters["delivery_rule_type"].DefaultValue = newRuleType.ToString();
                SDSPendingOrders.Update();

                gvPendingOrders.DataBind();
            }
        }       
        
    }
}

