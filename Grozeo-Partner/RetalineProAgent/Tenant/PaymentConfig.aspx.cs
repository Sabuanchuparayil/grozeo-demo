using Newtonsoft.Json;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.PaymentGateway;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Windows.Ink;
using System.Configuration;
using MySqlX.XDevAPI.Relational;
using RetalineProAgent.Core.BussinessModel.Store;

namespace RetalineProAgent.Tenant
{
    public partial class PaymentConfig : Base.BasePartnerPage
    {
        private bool SubAccountEnabled
        {
            get
            {
                return ConfigurationManager.AppSettings.Get("PaymentGatewaySubAccount") == "1";
            }
        }
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                pnlSubAccounts.Visible = SubAccountEnabled;
                pnlNoneSubAccount.Visible = !pnlSubAccounts.Visible;
            }

            if (SubAccountEnabled && !IsPostBack && Session["PaymentGatewaySession"] != null) {
                if (Request.QueryString["type"] == "0") // Refresh
                {
                    Common.ShowCustomAlert(this.Page, "Failure", "The process has been timed out. Please try again later.", true, "/tenant/paymentconfig");
                }
                else if (Request.QueryString["type"] == "1") // Redirect
                {
                    try
                    {
                        dynamic subAc = (dynamic)Session["PaymentGatewaySession"];
                        int logId = 0; try { logId = Convert.ToInt32(subAc.logId); } catch { logId = 0; }
                        if (logId > 0 && !string.IsNullOrEmpty(subAc.accountId))
                        {
                            Session.Remove("PaymentGatewaySession");
                            dynamic result = StripeService.CheckAccountStatus(logId, subAc.accountId);
                            if (result != null && result.success == 1)
                                Common.ShowCustomAlert(this.Page, "Success", "Payment gateway account linked successfully!", true, "/tenant/paymentconfig");
                            else
                                Common.ShowToastifyMessage(this.Page, (result != null && !string.IsNullOrEmpty(result.msg) ? result.msg : "Failure, connection was not successfull. Please check your dashboard."), "danger");
                        }
                        else
                        {
                            Common.ShowToastifyMessage(this.Page, "Invalid operation / redirection!", "danger");
                        }
                    }
                    catch(Exception ex)
                    {
                        this.LogError($" Error on PaymentConfig.aspx.cs - Page_Load - Request.QueryString['type'] = 1, {ex.Message}");
                        Common.ShowToastifyMessage(this.Page, "Previous action failed with connect due to some technical failure.", "danger");
                    }
                }
            
            }
        }

        protected void btnConnect_Click(object sender, EventArgs e)
        {
            try
            {
                var domain = Request.Url.Scheme + "://" + Request.Url.Authority; // Get website's domain
                dynamic subAc = StripeService.Connect_InitializeSubAccount(domain, this.CurrentUser.APIStoreId);
                if (subAc == null || string.IsNullOrEmpty(subAc.url))
                {
                    Common.ShowToastifyMessage(this.Page, "Invalid operation. The system cannot connect to the gateway at this stage.", "danger");
                    return;
                }
                Session["PaymentGatewaySession"] = subAc; // $"STRIPE-CONNECT: store-{CurrentUser.StoreGroupId}";

                Response.Redirect(subAc.url);
            }
            catch(Exception ex)
            {
                Common.ShowToastifyMessage(this.Page, ex.Message, "danger");
            }
        }

        protected void SDSLinkedAccount_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            if (e.Command.Parameters.Contains("storeId"))
                e.Command.Parameters["storeId"].Value = this.CurrentUser.APIStoreId;

        }

        protected void selGatewayAccount_SelectedIndexChanged(object sender, EventArgs e)
        {
            DropDownList dl = (DropDownList)sender;
            if (dl != null && !String.IsNullOrEmpty(dl.Attributes["storeid"]) && !String.IsNullOrEmpty(dl.SelectedValue))
            {
                int storeid = Convert.ToInt32(dl.Attributes["storeid"]);

                string sql = @"START TRANSACTION;

UPDATE store_paymentgateway_connect 
SET branchId = TRIM(BOTH ',' FROM 
    REPLACE(
        CONCAT(',', branchId, ','),
        CONCAT(',', @brid, ','),
        ','
    )
)
WHERE id = @rowId AND storeGroupId=@storeId AND FIND_IN_SET(@brid, branchId) > 0;

UPDATE store_paymentgateway_connect
SET branchId = CASE
    WHEN branchId IS NULL OR branchId = '' THEN @brid
    ELSE CONCAT_WS(',', branchId, @brid)
END
WHERE id = @rowId AND storeGroupId=@storeId;

COMMIT;
";

                List<KeyValuePair<string, object>> bankParams = new List<KeyValuePair<string, object>>();
                bankParams.Add(new KeyValuePair<string, object>("storeId", this.CurrentUser.APIStoreId));
                bankParams.Add(new KeyValuePair<string, object>("brid", storeid));
                bankParams.Add(new KeyValuePair<string, object>("rowId", dl.SelectedValue));
                DataServiceMySql.ExecuteSql(sql, parmeters: bankParams);
                // Activitylog
                String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                string Source = strUrl;
                int storegroupid = this.CurrentUser.APIStoreId; ;
                string User = this.CurrentUser.Email;
                //string[] items = {"selling price" = textSellingPrice.ToString(), "discount selling price"= discount_selling_price.ToString(),"mrp=" mrp.ToString(), "stock" = pStock.ToString(),"Item Id"= itemId.ToString() };

                string tenantId = storegroupid.ToString();
                string brnachId = storeid.ToString();
                string bankid = dl.SelectedValue;
                var items = new[]
                    {
                    new { Key = "Tenant Id", Value = tenantId },
                    new { Key = "Branch Id", Value = brnachId },
                    new { Key = "PG Id", Value = bankid },
                    };
                string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
                gvStores.EditIndex = -1;
                SDSStores.Select(DataSourceSelectArguments.Empty);
                gvStores.DataBind();
            }

        }

        public string GetPaymentGatewayName()
        {
            string strPG = "";
            string strPGConfig = ConfigurationManager.AppSettings.Get("PaymentGateway");
            if (strPGConfig.Contains(".revolut.com"))
                strPG = "Revolut";
            else if (strPGConfig.Contains(".stripe.com"))
                strPG = "Stripe";
            else
                strPG = strPGConfig;

            return strPG;
        }

        protected void btnSavebankdetails_Click(object sender, EventArgs e)
        {
            try
            {
                int status = 1;
                int pgstatus = 2;
                int paymentgateway = ConfigurationManager.AppSettings["PaymentGateway"] == "razorpay" ? 2 : 1;
                string paymentgatewayname = ConfigurationManager.AppSettings["PaymentGateway"];
                List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                prms.Add(new KeyValuePair<string, object>("storeId", this.CurrentUser.APIStoreId));
                prms.Add(new KeyValuePair<string, object>("Name", txtcontactname.Text));
                prms.Add(new KeyValuePair<string, object>("ContactNumber", txtcontactnumber.Text));
                prms.Add(new KeyValuePair<string, object>("contactEmail", txtcontactemail.Text));
                prms.Add(new KeyValuePair<string, object>("BankAccountNumber", txtbankaccountNo.Text));
                prms.Add(new KeyValuePair<string, object>("IFSC", txtifsc.Text));
                prms.Add(new KeyValuePair<string, object>("Accountname", hdnAccountname.Value));
                prms.Add(new KeyValuePair<string, object>("Bankname", hdnBankname.Value));
                prms.Add(new KeyValuePair<string, object>("BankBranch", hdnBranch.Value));
                prms.Add(new KeyValuePair<string, object>("Status", status));
                prms.Add(new KeyValuePair<string, object>("pgstatus", pgstatus));
                prms.Add(new KeyValuePair<string, object>("pgtype", paymentgateway));
                prms.Add(new KeyValuePair<string, object>("pgname", paymentgatewayname));
                prms.Add(new KeyValuePair<string, object>("BeneficiaryType", ddlbeneficiarytype.SelectedItem.Text));
                string subaccountconnect = "insert into store_paymentgateway_connect(`pgType`,`pgName`,`storeGroupId`,`status`,`bankName`,`bankAccountName`,`bankAccountNum`,`BeneficiaryType`) Values(@pgtype,@pgname,@storeId,@pgstatus,@Bankname,@Accountname,@BankAccountNumber,@BeneficiaryType); select LAST_INSERT_ID()";
                var result = DataServiceMySql.ExecuteScalar(subaccountconnect, parmeters: prms);
                int storepgtId = Convert.ToInt32(result);
                prms.Add(new KeyValuePair<string, object>("storepgId", storepgtId));
                string getpaymentsubaccount = "insert into MerchantSubaccount(`name`,`contactNumber`,`contactEmail`,`BankaccountNumber`,`IFSC`,`AccountName`,`BankName`,`BankBranchName`,`StoreGroupId`,Status,StorePgconnectId,BeneficiaryType) values(@Name,@ContactNumber,@contactEmail,@BankAccountNumber,@IFSC,@Accountname,@Bankname,@BankBranch,@storeId,@Status,@storepgId,@BeneficiaryType)";
                DataServiceMySql.ExecuteSql(getpaymentsubaccount, parmeters: prms);
                Common.ShowCustomAlert(this.Page, "Success", "Account Details Saved Successfully", true, "/tenant/paymentconfig");
            }
            catch
            {
                Common.ShowCustomAlert(this.Page, "Failed", "Failed to  save Account Details ", false, "/tenant/paymentconfig");

            }
        }

        

    }
}