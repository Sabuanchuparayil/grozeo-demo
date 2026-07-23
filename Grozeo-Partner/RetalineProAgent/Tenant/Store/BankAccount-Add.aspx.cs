using RetalineProAgent.Core.BussinessModel.Finance;
using RetalineProAgent.Core.BussinessModel.Inventory;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Core.Services.Cache;
using RetalineProAgent.Service;
using System;
using System.Collections;
using System.Collections.Generic;
using System.Configuration;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class BankAccount_Add : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            lblResult.Text = "";
            txtIFSC.Attributes.Add("placeholder", (ConfigurationManager.AppSettings.Get("CountryCode") == "UK" ? "Enter Sort code" : "Enter IFSC"));
            txtAccountNumber.Attributes.Add("placeholder", (ConfigurationManager.AppSettings.Get("CountryCode") == "AE" ? "Enter IBAN" : "Enter account number"));
            if (!IsPostBack)
            {
                if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
                {
                    txtIFSC.CssClass = "form-control sixDigitCode";
                    txtIFSC.MaxLength = 8;
                }
                else
                {
                    txtIFSC.CssClass = "form-control text-uppercase";
                    txtIFSC.MaxLength = 11;
                }
                plcifc.Visible = bankdetalis.Visible = !(ConfigurationManager.AppSettings.Get("CountryCode") == "AE");
                rfvAccountNumber.ErrorMessage = ConfigurationManager.AppSettings["CountryCode"] == "AE"? "IBAN is required" : "Account number is required";
            }
        }

        protected async void btnAddBank_Click(object sender, EventArgs e)
        {
            if (String.IsNullOrEmpty(txtAccountNumber.Text))
            {
                ShowFailure("Verification failed", "Account number is a required field. Please enter Account number");
                lblResult.Text = "Please enter IFSC";
                return;
            }

            if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK" || ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
            {
                if (String.IsNullOrEmpty(txtIFSC.Text))
                {
                    ShowFailure("Verification failed", $"{(ConfigurationManager.AppSettings.Get("CountryCode") == "UK" ? "Sort" : "IFSC")} is a required field. Please enter {(ConfigurationManager.AppSettings.Get("CountryCode") == "UK" ? "Sort" : "IFSC")}");
                    lblResult.Text = $"Please enter {(ConfigurationManager.AppSettings.Get("CountryCode") == "UK" ? "Sort" : "IFSC")}";
                    return;
                }
            }
            Core.BussinessModel.Store.BankAccount bankInfo = null;
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("accountNumber", txtAccountNumber.Text));
            prms.Add(new KeyValuePair<string, object>("ifc", txtIFSC.Text));
            prms.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.StoreGroupId));

            var dtAccount = DataService.GetDataTable("select  top 1 *, (case when TenantId = @storegroupid then 1 else 0 end) as existsSameTenant from BankAccount where AccountNumber = @accountNumber and SWIFT = @ifc order by existsSameTenant desc", "", prms);
            if (dtAccount != null && dtAccount.Rows.Count > 0)
            {
                if (Convert.ToInt32(dtAccount.Rows[0]["existsSameTenant"]) == 1)
                {
                    //lblResult.Text = "Account is already existing. Please add another account";
                    ShowFailure("Verification failed", "Account is already existing. Please add another account");
                    return;
                }
                var dr = dtAccount.Rows[0];
                // BankName, AccountNumber, AccountName, Branch, BankAddress, SWIFT
                bankInfo = new Core.BussinessModel.Store.BankAccount { name = dr["AccountName"].ToString(), valid = true, status = "ACTIVE", ifsc = new Core.BussinessModel.Store.IFSC { bank = dr["BankName"].ToString(), branch = dr["Branch"].ToString(), address = dr["BankAddress"].ToString() } };
            }
            string bankname = "", accouname = "", branch = "", bankaddress = "";
            if (bankInfo == null)
            {
                if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
                {
                    var bankInfoUK = APIService.VerifyBankAccountUK(txtAccountNumber.Text, txtIFSC.Text, "addBankAccount");
                    if (bankInfoUK != null && bankInfoUK.valid)
                    {
                        bankname = bankInfoUK.bankname;
                        bankaddress = bankInfoUK.addressline1
                            + (String.IsNullOrEmpty(bankInfoUK.addressline2) ? "" : ", " + bankInfoUK.addressline2)
                            + (String.IsNullOrEmpty(bankInfoUK.addressline3) ? "" : ", " + bankInfoUK.addressline3)
                            + (String.IsNullOrEmpty(bankInfoUK.addressline4) ? "" : ", " + bankInfoUK.addressline4);
                        branch = bankInfoUK.branchname;

                    }
                    if (bankInfoUK == null || !bankInfoUK.valid)
                    {
                        ShowFailure("Verification failed", "The account is invalid or not active. Please correct your input or contact your bank to confirm the account status.");
                        //lblResult.Text = "Account verification failed. Please check your input of try with another data";
                        return;
                    }

                }
                else if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
                {
                    bankInfo = APIService.VerifyBankAccount(txtAccountNumber.Text, txtIFSC.Text);
                    if (bankInfo != null && bankInfo.valid && bankInfo.status == "ACTIVE" && !String.IsNullOrEmpty(bankInfo.name))
                    {
                        bankname = bankInfo.ifsc.bank;
                        bankaddress = String.Format("{0} {1} {2}", bankInfo.ifsc.address, bankInfo.ifsc.city, bankInfo.ifsc.state);
                        accouname = bankInfo.name;
                        branch = bankInfo.ifsc.branch;

                    }
                    if (bankInfo == null || !bankInfo.valid || bankInfo.status != "ACTIVE" || String.IsNullOrEmpty(bankInfo.name))
                    {
                        ShowFailure("Verification failed", "The account is invalid or not active. Please correct your input or contact your bank to confirm the account status.");
                        //lblResult.Text = "Account verification failed. Please check your input of try with another data";
                        return;
                    }

                }
                else
                {
                    bankname = "";
                    bankaddress = "";
                    branch = "";
                }
            }
            else
            {
                bankname = bankInfo.ifsc.bank;
                bankaddress = bankInfo.ifsc.address; //String.Format("{0} {1} {2}", bankInfo.ifsc.address, bankInfo.ifsc.city, bankInfo.ifsc.state);
                accouname = bankInfo.name;
                branch = bankInfo.ifsc.branch;
            }
            txtaacountnumber.Text= ConfigurationManager.AppSettings["CountryCode"] == "AE"? "<b>IBAN :</b>" :"<b>Account Number:</b>";

            // Remove Redis cache entry
            var cacheService = new RedisCacheService();
            string cachekey = $"Retl.AppTenant.pendingtasks.count.{this.CurrentUser.APIStoreId}";
            await cacheService.RemoveAsync(cachekey);

            string strAlertSCript = "$('#modalBank').modal('show');";
            strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";
            System.Type cstype = this.GetType();
            String csname1 = "ShowConfirmPopup";
            ClientScriptManager cs = this.ClientScript;
            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
            cstext1.Append("script>");
            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());

            txtaccountname.Text = accouname;
            lblaccountnumber.Text = txtAccountNumber.Text;
            lblbankaddress.Text = bankaddress;
            lbbranch.Text = branch;
            lbBank.Text = bankname;

        }
        public void AddBankAccount(string bankname, string accouname, string branch, string bankaddress, string Isverified)
        {
            string sql = "INSERT INTO BankAccount(TenantId, BankName, AccountNumber, AccountName, Branch, BankAddress, SWIFT, Verified,Createdby) VALUES(@storegroupid, @bankName, @accountNumber, @accountName, @branch, @bankAddress, @ifc, @IsVerified,@CreatedBy)";
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("accountNumber", txtAccountNumber.Text));
            prms.Add(new KeyValuePair<string, object>("ifc", txtIFSC.Text));
            prms.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.StoreGroupId));
            prms.Add(new KeyValuePair<string, object>("bankName", bankname)); // bankInfo.ifsc.bank));
            prms.Add(new KeyValuePair<string, object>("accountName", accouname)); // bankInfo.name));
            prms.Add(new KeyValuePair<string, object>("branch", branch)); // bankInfo.ifsc.branch));
            prms.Add(new KeyValuePair<string, object>("bankAddress", bankaddress)); //String.Format("{0} {1} {2}", bankInfo.ifsc.address, bankInfo.ifsc.city, bankInfo.ifsc.state)));
            prms.Add(new KeyValuePair<string, object>("IsVerified", Isverified));
            prms.Add(new KeyValuePair<string, object>("CreatedBy", string.IsNullOrEmpty(this.CurrentUser.Email) ? this.CurrentUser.Phone : this.CurrentUser.Email));
            DataService.ExecuteSql(sql, parmeters: prms);
            // Activitylog
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            string Source = strUrl;
            int storegroupid = this.CurrentUser.APIStoreId; ;
            string User = this.CurrentUser.Email;
            string bankName = bankname;
            string accountName = accouname;
            string bankbranch = branch;
            string bankaddressnew = bankaddress;
            var items = new[]
                {
                    new { Key = "BanK Name", Value = bankname },
                new { Key = "Account Name", Value = accouname },
                    new { Key = "Bank Branch", Value = branch },
                    new { Key = "Bank Address", Value = bankaddress },
                };
            string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
            var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
            string Accountnumberlabel = ConfigurationManager.AppSettings["CountryCode"] == "AE" ? "IBAN" : "Account Number";
            //lblResult.Text = $"{accouname}, {bankname}, {bankaddress}";
            string strcontent = $"<p class=\"mg-b-5\">{Accountnumberlabel}: {txtAccountNumber.Text}{(String.IsNullOrEmpty(accouname) ? "" : ", <br>Account Name: " + accouname)} {(String.IsNullOrEmpty(bankname) ? "" : ",<br/>Bank: " + bankname)}{(String.IsNullOrEmpty(branch) ? "" : ", <br>Branch:" + branch)}{(String.IsNullOrEmpty(bankaddress) ? "" : ",<br/>Address:" + bankaddress)}</p>";
            ShowSuccess("Bank Verification Success!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Your bank account has been validated and added successfully!</a></h5>" + strcontent);
        }

        private void ShowMessage(string title, string content)
        {
            Type cstype = this.GetType();
            String csname1 = "PopupScript";
            ClientScriptManager cs = Page.ClientScript;
            ltrPopupTitle.Text = title;
            ltrModelBodyContent.Text = content;

            if (!cs.IsStartupScriptRegistered(cstype, csname1))
            {
                StringBuilder cstext1 = new StringBuilder();
                cstext1.Append("<script type=text/javascript> $('#modaldemo1').modal('show'); </");
                cstext1.Append("script>");

                cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
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

            //    cs.RegisterClientScriptBlock(cstype, csname1, @"<script type='text/javascript'>$('#modaldemo4').on('hidden.bs.modal', function (e) {
            //      window.location.href='/bankaccount';
            //});</script>");
        }


        private void ShowFailure(string title, string content)
        {
            ltrErrorPopupTitle.Text = title;
            ltrErrorPopupText.Text = content;
            Type cstype = this.GetType();
            String csname1 = "PopupScript";
            ClientScriptManager cs = Page.ClientScript;


            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append("<script type=text/javascript> $('#modaldemo5').modal('show'); </");
            cstext1.Append("script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());

        }

        protected void btnSaveChanges_Click(object sender, EventArgs e)
        {

        }

        protected void btnshowpopup_Click(object sender, EventArgs e)
        {
            if (ConfigurationManager.AppSettings.Get("CountryCode") == "AE")
            {
                AddBankAccount(lbBank.Text, txtaccountname.Text, lbbranch.Text, lblbankaddress.Text, "0");
            }
            else
            {
                AddBankAccount(lbBank.Text, txtaccountname.Text, lbbranch.Text, lblbankaddress.Text, "1");

            }
        }
    }
}