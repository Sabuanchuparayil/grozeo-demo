using Microsoft.Identity.Client;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Finance
{
    public partial class Settlementaplications : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {

        }

        protected void btnview_Click(object sender, EventArgs e)
        {
            LinkButton btn = (LinkButton)sender;
            string id = btn.CommandArgument;
            hdngetpaymentgatewayid.Value = id;
            if (id.StartsWith("OID:"))
            {
                string orderId = id.Replace("OID:", "");
            }
            else if (id.StartsWith("ID:"))
            {
               string accountid = id.Replace("ID:", "");
                if (id != null)
                {
                    try
                    {
                        List<KeyValuePair<string, object>> rms = new List<KeyValuePair<string, object>>();
                        rms.Add(new KeyValuePair<string, object>("id", accountid));
                        var getdetails = DataServiceMySql.GetDataTable("select * from MerchantSubaccount ms where id=@id", Service.UserService.GetAPIConnectionString(), rms);

                        if (getdetails != null)
                        {

                            var dr = getdetails.Rows[0];
                            txtcontactname.ReadOnly = (txtcontactname.Text = dr["name"].ToString()) != null;
                            txtcontactnumber.ReadOnly = (txtcontactnumber.Text = dr["contactNumber"].ToString()) != null;
                            txtcontactemail.ReadOnly = (txtcontactemail.Text = dr["contactEmail"].ToString()) != null;
                            txtbankaccountNo.ReadOnly = (txtbankaccountNo.Text = dr["BankaccountNumber"].ToString()) != null;
                            txtifsc.ReadOnly = (txtifsc.Text = dr["IFSC"].ToString()) != null;
                            txtAccountname.ReadOnly = (txtAccountname.Text = dr["AccountName"].ToString()) != null;
                            txtbankname.ReadOnly = (txtbankname.Text = dr["BankName"].ToString()) != null;
                            txtbranch.ReadOnly = (txtbranch.Text = dr["BankBranchName"].ToString()) != null;
                            txtpaymentgatewayaccountId.Text = dr["PaymentgatewayId"] != DBNull.Value ? dr["PaymentgatewayId"].ToString() : string.Empty;
                            txtbeneficiarytype.ReadOnly = (txtbeneficiarytype.Text = dr["BeneficiaryType"].ToString()) != null;
                            //popup Action
                            string strAlertSCript = "$('#razorpayaccountDetails').modal('show');";
                            strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";
                            System.Type cstype = this.GetType();
                            String csname1 = "ShowConfirmPopup";
                            ClientScriptManager cs = this.ClientScript;
                            StringBuilder cstext1 = new StringBuilder();
                            cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
                            cstext1.Append("script>");
                            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
                        }


                    }
                    catch
                    {

                    }

                }

            }
            
        }

        protected void btnSavebankdetails_Click(object sender, EventArgs e)
        {
            string pgid = hdngetpaymentgatewayid.Value;
            string pgaccountid = txtpaymentgatewayaccountId.Text;

            // Ensure pgaccountid is not null/empty
            if (!string.IsNullOrEmpty(pgaccountid))
            {
                try
                {
                    // Set status values based on checkbox state
                    int pgstatus = rbActivated.Checked ? 3 : 2;
                    int status = rbActivated.Checked ? 1 : 3;
                    // Prepare SQL parameters
                    List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>
                    {
                        new KeyValuePair<string, object>("id", pgid),
                        new KeyValuePair<string, object>("pgstatus", pgstatus),
                        new KeyValuePair<string, object>("pgaccountid", pgaccountid),
                        new KeyValuePair<string, object>("status", status)
                    };

                    // Update both tables using INNER JOIN
                    string updatepg = "Update MerchantSubaccount ms inner join store_paymentgateway_connect sc on sc.id=ms.StorepgconnectId SET ms.PaymentgatewayId=@pgaccountid,sc.accountId=@pgaccountid,ms.status=@pgstatus,sc.status=@status where ms.id=@id";
                    // Execute SQL update
                    var data = DataServiceMySql.ExecuteSql(updatepg, Service.UserService.GetAPIConnectionString(), prms);

                    // Show success alert
                    Common.ShowCustomAlert(this.Page, "Successful", "Payment details saved successfully.", true, "/Finance/Settlementaplications");
                }
                catch
                {
                    // Show error alert if something fails
                    Common.ShowCustomAlert(this.Page, "Technical Error", "An unexpected error occurred while processing your request. Please try again later.", false, "/Finance/Settlementaplications");
                }
            }

        }

        protected void btnSaveReason_Click(object sender, EventArgs e)
        {
            try
            {
                string pgid = hdngetpaymentgatewayid.Value;
                if (!string.IsNullOrEmpty(pgid))
                {
                    List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>
                    {
                            new KeyValuePair<string, object>("id", pgid),
                            new KeyValuePair<string, object>("pgstatus", 4),
                            new KeyValuePair<string, object>("comment",txtReason.Text),
                            new KeyValuePair<string, object>("status", 4)
                    };
                    string updatepg = "Update MerchantSubaccount ms inner join store_paymentgateway_connect sc on sc.id=ms.StorepgconnectId SET ms.Remark=@comment,ms.status=@pgstatus,sc.status=@status where ms.id=@id";
                    // Execute SQL update
                    var data = DataServiceMySql.ExecuteSql(updatepg, Service.UserService.GetAPIConnectionString(), prms);
                    Common.ShowCustomAlert(this.Page, "Successful", "Data saved successfully.", true, "/Finance/Settlementaplications");

                }
            }
            catch
            {
                Common.ShowCustomAlert(this.Page, "Technical Error", "An unexpected error occurred while processing your request. Please try again later.", false, "/Finance/Settlementaplications");

            }

        }
    }
}

