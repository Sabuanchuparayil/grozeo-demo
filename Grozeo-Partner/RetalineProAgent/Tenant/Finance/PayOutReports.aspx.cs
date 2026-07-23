using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Tenant.Finance
{
    public partial class PayOutReports : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            txtFromDate.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
            txtToDate.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
            
        }

        protected void SDSPayoutReport_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
        }

        protected void lbtnaction_Click(object sender, EventArgs e)
        {
            try
            {
                LinkButton lbtn = (LinkButton)sender;
                hidValueHeadOrderId.Value = (lbtn.Attributes["recid"]);
                string Id = hidValueHeadOrderId.Value;
                List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
                sqldaId.Add(new KeyValuePair<string, object>("id", Id));
                string settlement = "SELECT t.settlement_id,t.status_id,ms.created_at,t.ifsc_code,t.account_number,fb.store_group_name,settlement_date,ms.updated_at,payout_amount, SUM(o.sale_proceeds) AS amounttotal,SUM(o.expenses) AS expenses FROM finance_transaction t INNER JOIN `finance_transaction_log` tl ON t.id=tl.ft_id INNER JOIN merchant_settlements ms ON ms.id=tl.ms_id INNER JOIN finascop_branch_group fb ON fb.store_group_id=t.storegroup_id INNER JOIN merchant_settlements_order o ON ms.ref_id=o.ms_ref_id WHERE t.id=@id";
                var dt = DataServiceMySql.GetDataTable(settlement, parmeters: sqldaId);
                if (dt != null && dt.Rows.Count > 0)
                {
                    var settlementdetails = dt.Rows[0];
                    lbstoregroup.Text = settlementdetails["store_group_name"].ToString();
                    lbsettlementid.Text = settlementdetails["settlement_id"].ToString(); ;
                    lbsettlementdate.Text = ((DateTime)settlementdetails["settlement_date"]).ToString("dd MMM yyyy");
                    lbinitiateddate.Text = ((DateTime)settlementdetails["created_at"]).ToString("dd MMM yyyy");
                    string account = settlementdetails["account_number"]?.ToString();
                    string ifsc = settlementdetails["ifsc_code"]?.ToString();
                    lbbankaccount.Text = string.IsNullOrWhiteSpace(account) ? ifsc ?? "" : string.IsNullOrWhiteSpace(ifsc) ? account : $"{account}/{ifsc}";
                    lbamount.Text = decimal.TryParse(settlementdetails["payout_amount"]?.ToString(), out var amt) ? amt.ToString("N2") : "0.00";
                    bool isPending = settlementdetails["status_id"]?.ToString() == "1";
                   
                }

                //popup Action
                string strAlertSCript = "$('#Pupaction').modal('show');";
                strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";
                System.Type cstype = this.GetType();
                String csname1 = "ShowConfirmPopup";
                ClientScriptManager cs = this.ClientScript;
                StringBuilder cstext1 = new StringBuilder();
                cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
                cstext1.Append("script>");
                cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
            }
            catch (Exception ex)
            {
                Common.ShowCustomAlert(this.Page, "Technical Error", "An unexpected error occurred while processing your request. Please try again later", false, "/Finance/SettlementReports");
            }
        }

        protected void lvsettlement_DataBound(object sender, EventArgs e)
        {
            try
            {
                //to get the sum values
                List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
                sqldaId.Add(new KeyValuePair<string, object>("id", hidValueHeadOrderId.Value));
                string settlementdetails = "SELECT t.settlement_id,fb.store_group_name,settlement_date,ms.updated_at,payout_amount, SUM(o.sale_proceeds) AS amounttotal,SUM(o.expenses) AS expenses FROM finance_transaction t INNER JOIN `finance_transaction_log` tl ON t.id=tl.ft_id INNER JOIN merchant_settlements ms ON ms.id=tl.ms_id INNER JOIN finascop_branch_group fb ON fb.store_group_id=t.storegroup_id INNER JOIN merchant_settlements_order o ON ms.ref_id=o.ms_ref_id WHERE t.id=@id";
                var dtsettle = DataServiceMySql.GetDataTable(settlementdetails, parmeters: sqldaId);
                if (dtsettle != null && dtsettle.Rows.Count > 0)
                {
                    var settlementdetailsamount = dtsettle.Rows[0];
                    Literal ltrtotalamount = (Literal)lvsettlement.FindControl("ltttotalamount");
                    Literal ltrtotaldeduction = (Literal)lvsettlement.FindControl("ltrdeduction");
                    Literal ltrsettlementamount = (Literal)lvsettlement.FindControl("ltrsettleamount");
                    if (ltrtotalamount != null && ltrtotalamount != null && ltrsettlementamount != null)
                    {
                        ltrtotalamount.Text = settlementdetailsamount["amounttotal"].ToString();
                        ltrtotaldeduction.Text = settlementdetailsamount["expenses"].ToString();
                        ltrsettlementamount.Text = settlementdetailsamount["payout_amount"].ToString();
                    }
                }
            }
            catch (Exception ex)
            {
                Common.ShowCustomAlert(this.Page, "Technical Error", "An unexpected error occurred while processing your request. Please try again later", false, "/Finance/SettlementReports");
            }

        }
    }
}