using NPOI.SS.Formula.Functions;
using RetalineProAgent.Core.BussinessModel.Finance;
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
    public partial class CCViewJobs: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            txtDeliDate.Text = DateTime.Now.ToString("yyyy-MM-dd");
            String today = DateTime.Now.ToString("yyyy-MM-dd");

            txtDeliDate.Attributes["max"] = today;
            if (gvCCJobs.Rows.Count > 0)
            {
                btnDeliverOrders.Visible = true;
                txtDeliDate.Visible = true;
                txtCashInHand.Visible = true;
            }
            else
            {
                btnDeliverOrders.Visible = false;
                txtDeliDate.Visible = false;
                txtCashInHand.Visible = false;
            }
        }




        protected void gvCCJobs_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvCCJobs.PageIndex * gvCCJobs.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvCCJobs.Rows.Count - 1;
            //var dv = (DataView)SDSDeliveryBoy.Select(DataSourceSelectArguments.Empty);
        }

        protected void SDSCCJobs_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        }

        protected void ccDeliverOrders(object sender, EventArgs e)
        {
            List<int> lstQrIds = new List<int>();

            //var quorIdList = new List<Dictionary<string, object>>();
            DateTime collectionDate = Convert.ToDateTime(txtDeliDate.Text);
            var storegroupid = this.CurrentUser.APIStoreId;
            Boolean hasjobs = true;
            foreach (GridViewRow gr in gvCCJobs.Rows)
            {
                CheckBox chk = (CheckBox)gr.FindControl("ckCCJobs");
                if (chk == null || !chk.Checked)
                {
                    hasjobs = false;
                    continue;                   
                    
                }
                int rowid = 0; try
                {
                    hasjobs = true;
                    rowid = Convert.ToInt32(chk.Attributes["quor_id"]);
                    lstQrIds.Add(rowid);
                    //quorIdList.Add(new Dictionary<string, object> {
                    //        {"quor_id", rowid }
                    //    });
                }
                catch { rowid = 0; }
                if (rowid <= 0)
                {
                    hasjobs = false;
                    continue;
                }
                    

            }
            
            string result = Core.Services.APIService.DeliverCODJobs(storegroupid, lstQrIds.ToArray(), collectionDate);

            string message = result;
            if(result == "Delivered")
            {
                Common.ShowCustomAlert(this.Page, "Completed Successfully!", "Your job(s) are completed successfully", true, "/Tenant/ReceiveCash");
            }
            else
            {
                if (hasjobs == false)
                {
                    Common.ShowToastifyMessage(this.Page, "Please select job(s) and continue.", "danger");
                }
                else
                {
                    Common.ShowCustomAlert(this.Page, "Failure", "Execution failure . Please contact support for more details.", false, "/Tenant/ReceiveCash");
                }         
                 
                return;
            }
            




        }
        private void calculateAmount()
        {
            double total = 0;
            foreach (GridViewRow gr in gvCCJobs.Rows)
            {
                CheckBox chk = (CheckBox)gr.FindControl("ckCCJobs");
                Literal ltr = (Literal)gr.FindControl("ltrAmount");
                if (chk != null && ltr != null && chk.Checked && !string.IsNullOrEmpty(ltr.Text))
                {
                    double amt = Convert.ToDouble(ltr.Text);
                    total = total + amt;


                }
            }

            txtCashInHand.Text = total.ToString();
            // Set the total amount to a hidden field or label
            lblTotalAmount.Text = total.ToString();
            string driverId = Request.QueryString["d_ID"];
            string strDriverName = "";
            if (!String.IsNullOrEmpty(driverId))
            {
                string sql = $"SELECT d_Name FROM qugeo_driver WHERE d_ID = '{driverId}'";
                var tblItems = DataServiceMySql.GetDataTable(sql, UserService.GetAPIConnectionString());
                if (tblItems != null && tblItems.Rows.Count > 0)
                {
                    var ta = tblItems.Rows[0];
                    strDriverName = ta["d_Name"].ToString();
                }
            }
            lblDriverName.Text = strDriverName;
        }

        protected void ckCCJobs_CheckedChanged(object sender, EventArgs e)
        {
            calculateAmount();

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