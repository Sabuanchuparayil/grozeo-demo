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
    public partial class ScheduledDelivery: Base.BasePartnerPage
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

                FilterType = 12; hidFilterType.Value = "12";
                ltrTitle.Text = "Scheduled Delivery";
            }

            if (gvPendingOrders.HeaderRow != null)
                gvPendingOrders.HeaderRow.TableSection = TableRowSection.TableHeader;
        }
        protected void Page_PreRender(object sender, EventArgs e)
        {
            lbtScheduleDelivery.CssClass = String.Format("nav-link btn btn-block btn-outline-primary btn-sm p-1 px-2 {0}", (FilterType == 12 ? "active" : ""));
            //lbtnViewAll.CssClass = String.Format("nav-link btn btn-block btn-outline-primary btn-sm p-1 px-2 {0}", (FilterType <= 0 ? "active" : ""));
            //lbtnPending.CssClass = String.Format("nav-link btn btn-block btn-outline-primary btn-sm p-1 px-2 {0}", (FilterType == 1 ? "active" : ""));
            //lbtnPacked.CssClass = String.Format("nav-link btn btn-block btn-outline-primary btn-sm p-1 px-2 {0}", (FilterType == 2 ? "active" : ""));
            //lbtnShipped.CssClass = String.Format("nav-link btn btn-block btn-outline-primary btn-sm p-1 px-2 {0}", (FilterType == 3 ? "active" : ""));
            //lbtnDelivered.CssClass = String.Format("nav-link btn btn-block btn-outline-primary btn-sm p-1 px-2 {0}", (FilterType == 4 ? "active" : ""));
            //lbtnIncompleteOrds.CssClass = String.Format("nav-link btn btn-block btn-outline-primary btn-sm p-1 px-2 {0}", (FilterType == 10 ? "active" : ""));

            //lbtnPendingPacking.CssClass = String.Format("{0}", (FilterType == 5 ? "nav-link btn btn-block btn-outline-primary btn-sm p-1 px-2 active" : "dropdown-item "));
            //lbtnPaymentFailed.CssClass = String.Format("{0}", (FilterType == 6 ? "nav-link btn btn-block btn-outline-primary btn-sm p-1 px-2 active" : "dropdown-item "));
            //lbtnPickupFailed.CssClass = String.Format("{0}", (FilterType == 7 ? "nav-link btn btn-block btn-outline-primary btn-sm p-1 px-2 active" : "dropdown-item "));
            //lbtnDeliveryFailed.CssClass = String.Format("{0}", (FilterType == 8 ? "nav-link btn btn-block btn-outline-primary btn-sm p-1 px-2 active" : "dropdown-item "));
            //lbtnCancelled.CssClass = String.Format("{0}", (FilterType == 9 ? "nav-link btn btn-block btn-outline-primary btn-sm p-1 px-2 active" : "dropdown-item "));



        }
        //protected void SDSOnlineOrders_Selected(object sender, SqlDataSourceStatusEventArgs e)
        //{
        //    e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        //    int startRowOnPage = (gvOnlineOrders.PageIndex * gvOnlineOrders.PageSize) + 1;
        //    int lastRowOnPage = startRowOnPage + gvOnlineOrders.Rows.Count - 1;
        //    int totalRows = e.AffectedRows;

        //    ltrPageCurStart.Text = startRowOnPage.ToString();
        //    ltrPageCurTotal.Text = lastRowOnPage.ToString();
        //    ltrPageTotal.Text = totalRows.ToString();
        //}

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
            ltrPageCurTotal.Text = lastRowOnPage.ToString();
            ltrPageCurStart.Text = (gvPendingOrders.Rows.Count > 0 ? startRowOnPage : 0).ToString();
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
            ltrPageTotal.Text = e.AffectedRows.ToString();
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

        protected void SDSPendingOrders_Selecting1(object sender, SqlDataSourceSelectingEventArgs e)
        {

        }
    }
}