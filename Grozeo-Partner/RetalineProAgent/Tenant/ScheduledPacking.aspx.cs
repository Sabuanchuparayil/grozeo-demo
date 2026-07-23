using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using RetalineProAgent.Core.Services;
//using RetalineProAgent.Core.BussinessModel.OnlineOrders;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.IO;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class ScheduledPacking: Base.BasePartnerPage
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
                ltrTitle.Text = btn.Text + " Orders";
            }
        }
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack && String.IsNullOrEmpty(hidFilterType.Value))
            {

                FilterType = 11; hidFilterType.Value = "11";
                ltrTitle.Text = "Pending Orders";
            }

            if (gvPendingOrders.HeaderRow != null)
                gvPendingOrders.HeaderRow.TableSection = TableRowSection.TableHeader;
        }
        protected void Page_PreRender(object sender, EventArgs e)
        {
            lbtSchedulePacking.CssClass = String.Format("nav-link btn btn-block btn-outline-primary btn-sm p-1 px-2 {0}", (FilterType == 11 ? "active" : ""));
            //lbtScheduleDelivery.CssClass = String.Format("nav-link btn btn-block btn-outline-primary btn-sm p-1 px-2 {0}", (FilterType == 12 ? "active" : ""));

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

        }

        protected void SDSPendingOrders_Selected(object sender, SqlDataSourceStatusEventArgs e)
        {
            //ltrPageTotal.Text = e.AffectedRows.ToString();
        }

        private void ExportGridToExcel()
        {
            DataView dv = (DataView)SDSPendingOrders.Select(DataSourceSelectArguments.Empty);
            DataTable dt = dv.ToTable();
            IWorkbook wb = new XSSFWorkbook();
            ISheet sheet = wb.CreateSheet("Data1");
            ICreationHelper cH = wb.GetCreationHelper();
            int rows = 0;
            IRow rowH = sheet.CreateRow(rows++);

            foreach (DataControlField dc in gvForExportOnly.Columns)
            {
                ICell cell = rowH.CreateCell(rowH.Cells.Count);
                cell.SetCellValue(cH.CreateRichTextString(dc.HeaderText));
            }

            foreach (DataRow dr in dt.Rows)
            {
                IRow row = sheet.CreateRow(rows++);
                for (int j = 0; j < gvForExportOnly.Columns.Count; j++)
                {
                    ICell cell = row.CreateCell(j);
                    cell.SetCellValue(cH.CreateRichTextString(dr[gvForExportOnly.Columns[j].SortExpression].ToString()));
                }
            }

            Response.Clear();
            Response.Buffer = true;
            Response.Charset = "";
            Response.ContentType = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
            Response.AddHeader("content-disposition", "attachment;filename=Orders.xlsx");
            wb.Write(Response.OutputStream);

            Response.Flush();
            Response.End();

        }

        protected void lbtnDownloadExcel_Click(object sender, EventArgs e)
        {
            ExportGridToExcel();
        }

        //protected void btnRevoke_Click(object sender, EventArgs e)
        //{
        //    Button btnRevoke = (Button)sender;

        //    string orderId = Convert.ToString(btnRevoke.Attributes["transferOrderId"]);
        //    List<KeyValuePair<string, object>> sqlId = new List<KeyValuePair<string, object>>();
        //        sqlId.Add(new KeyValuePair<string, object>("toId", orderId));
        //    var boyId = "SELECT id,boy_id,order_pk_id FROM retaline_godown_boy_orders_request WHERE order_pk_id=@toId";
        //    DataTable orderBoyId = DataServiceMySql.GetDataTable(boyId, Service.UserService.GetAPIConnectionString(), sqlId);
        //    if (btnRevoke == null || String.IsNullOrEmpty(btnRevoke.Attributes["transferOrderId"]))
        //    {
        //        // show error
        //        return;
        //    }
        //    string boy = null;
        //    string boyPkId = null;
        //    if (orderBoyId.Rows.Count > 0)
        //    {

        //        boy = orderBoyId.Rows[0]["boy_id"].ToString();
        //        boyPkId = orderBoyId.Rows[0]["order_pk_id"].ToString();

        //    }
        //    int orderNum = Convert.ToInt32(boy);
        //    int orderPIckerId = Convert.ToInt32(boyPkId);
        //    int storegroupid = this.CurrentUser.APIStoreId;
        //    string result = Core.Services.APIService.Revoke(orderNum, orderPIckerId);

        //    // show result as status.
        //    string status = result;
        //    ShowSuccess("Revoked Successfully!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Order has been revoked successfully!</a></h5>");

        //}

        private void ShowSuccess(string title, string content)
        {
            ltrErrorPopupTitle.Text = title;
            ltrErrorPopupText.Text = content;
            Type cstype = this.GetType();
            String csname1 = "PopupScript";
            ClientScriptManager cs = Page.ClientScript;
            ltrSuccessTitle.Text = title;
            ltrSuccessContent.Text = content;

            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append("<script type=text/javascript> $('#modaldemo4').modal('show'); </");
            cstext1.Append("script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }

        protected void gvPendingOrders_RowDataBound(object sender, GridViewRowEventArgs e)
        {
            //  data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo"
            e.Row.Attributes.Add("data-toggle", "collapse");
            e.Row.Attributes.Add("data-target", String.Format("#collapse{0}", e.Row.RowIndex));
            e.Row.Attributes.Add("aria-expanded", "false");
            e.Row.Attributes.Add("aria-controls", String.Format("collapse{0}", e.Row.RowIndex));

        }

        protected void btnMoveToPacking_Click(object sender, EventArgs e)
        {
            Button btnAssign = (Button)sender;
            string transferOrderId = Convert.ToString(btnAssign.Attributes["transferOrderId"]);
            string status = Convert.ToString(btnAssign.Attributes["statusId"]);
            string type = Convert.ToString(btnAssign.Attributes["orderType"]);
            int orderType = Convert.ToInt32(type);
            string requestId = Convert.ToString(btnAssign.Attributes["request"]);
            int statusId = Convert.ToInt32(status);

            if ((statusId == 11) && (orderType == 1))
            {
                List<KeyValuePair<string, object>> orderparams = new List<KeyValuePair<string, object>>();
                orderparams.Add(new KeyValuePair<string, object>("transOrdId", transferOrderId));
                orderparams.Add(new KeyValuePair<string, object>("status", 6));
                orderparams.Add(new KeyValuePair<string, object>("updatedBy", 1));
                string updateQry = "UPDATE finascop_stock_transfer_order SET fsto_status=@status, fsto_updateby=@updatedBy WHERE fsto_id=@transOrdId";
                DataServiceMySql.ExecuteSql(updateQry, Service.UserService.GetAPIConnectionString(), orderparams);

                List<KeyValuePair<string, object>> custorderparams = new List<KeyValuePair<string, object>>();
                custorderparams.Add(new KeyValuePair<string, object>("orderId", requestId));
                custorderparams.Add(new KeyValuePair<string, object>("status", 7));
                string updtQry = "UPDATE retaline_customer_order SET status_id=@status WHERE order_id=@orderId";
                DataServiceMySql.ExecuteSql(updtQry, Service.UserService.GetAPIConnectionString(), custorderparams);

                Common.ShowCustomAlert(this.Page, "Data updated!", "Data updated successfully!", true, "/Tenant/PendingOrders");
            }
            else
            {
                Common.ShowToastifyMessage(this.Page, "Error occured while saving data.", "danger");
            }

        }

        protected void SDSPendingOrders_Selecting1(object sender, SqlDataSourceSelectingEventArgs e)
        {

        }

        
    }
}