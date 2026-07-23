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
    public partial class AssignOrderPicker: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
        }
        
        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvOrderPicker.PageIndex > 0)
                gvOrderPicker.PageIndex = gvOrderPicker.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvOrderPicker.PageIndex < gvOrderPicker.PageCount - 1)
                gvOrderPicker.PageIndex = gvOrderPicker.PageIndex + 1;
        }

        protected void gvOrderPicker_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvOrderPicker.PageIndex * gvOrderPicker.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvOrderPicker.Rows.Count - 1;
            //ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSOrderPickers.Select(DataSourceSelectArguments.Empty);
        }

        protected void SDSOrderPickers_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
        }

        protected void chkStatus_CheckedChanged(object sender, EventArgs e)
        {

            CheckBox chbtn = (CheckBox)sender;
            //int status = chbtn.Checked ? 1 : 0;
            int storegroupid = this.CurrentUser.APIStoreId;
            var dtBranches = DataServiceMySql.GetDataTable($"SELECT br_ID FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
            foreach (DataRow dr in dtBranches.Rows)
            {
                string brId = dr["br_ID"].ToString();
                var strid = DataServiceMySql.GetDataTable($"SELECT id FROM retaline_godown_boy WHERE branch_id = {brId}", UserService.GetAPIConnectionString());
                foreach (DataRow da in strid.Rows)
                {
                    string id = da["id"].ToString();
                    //int id = 0;
                    if (chbtn.Checked)
                    {
                        string strSq1Updatel = $"UPDATE retaline_godown_boy SET status=1 WHERE id = '" + id + "'";
                        DataServiceMySql.ExecuteSql(strSq1Updatel, UserService.GetAPIConnectionString());
                    }
                    else
                    {
                        string strSq1Update2 = $"UPDATE retaline_godown_boy SET status=0 WHERE id = '" + id + "'";
                        DataServiceMySql.ExecuteSql(strSq1Update2, UserService.GetAPIConnectionString());
                    }
                }
            }
        }

        protected void btnAdd_Click(object sender, EventArgs e)
        {
            Button btnAssign = (Button)sender;
            if (btnAssign == null || String.IsNullOrEmpty(btnAssign.Attributes["orderpickerid"]))
            {
                // show error
                return;
            }
            int branchid = Convert.ToInt32(btnAssign.Attributes["branchid"]);
            int storegroupid = this.CurrentUser.APIStoreId;

            string orderPIckerId = Convert.ToString(btnAssign.Attributes["orderpickerid"]);
            //string orderNum = Request.QueryString["ordernum"];

            string transferOrderId = Convert.ToString(Request.QueryString["toid"]);
            //string fstr = Convert.ToString(Request.QueryString["orderid"]);
            int orderId = Convert.ToInt32(Request.QueryString["orderid"]);

           
            List<KeyValuePair<string, object>> boyparams = new List<KeyValuePair<string, object>>();
            boyparams.Add(new KeyValuePair<string, object>("orderPIckerId", orderPIckerId));
            boyparams.Add(new KeyValuePair<string, object>("storeGroupId", this.CurrentUser.APIStoreId));
            DataTable tblBoyInfo = DataServiceMySql.GetDataTable($"SELECT COUNT(*) AS cnt FROM finascop_stock_transfer_order INNER JOIN finascop_branch fb ON fsto_source = fb.br_ID WHERE  fsto_status IN(2, 4) AND fsto_polled_boy = @orderPIckerId AND fb.br_storeGroup = @storeGroupId",
                    UserService.GetAPIConnectionString(), boyparams);
            DataRow dr = tblBoyInfo.Rows[0];

            if (Convert.ToInt32(dr["cnt"]) == 0)
            {
                string result = Core.Services.APIService.AssignOrderPicker(transferOrderId, orderId, orderPIckerId, branchid, storegroupid);

                // show result as status.
                int status = Convert.ToInt32(result);
                ShowSuccess("Assigned Successfully!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Order picker has been assigned successfully!</a></h5>");
            }
            else
            {
                Common.ShowToastifyMessage(this.Page, "Sorry, Boy already polled.", "danger");
            }
            
        }

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
    }

}


