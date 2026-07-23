using NPOI.SS.Formula.Functions;
//using RetalineProAgent.Core.BussinessModel.GST;
using RetalineProAgent.Core.BussinessModel.Inventory;
using RetalineProAgent.Core.BussinessModel.Store;
using RetalineProAgent.Core.BussinessModel.VAT;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Core.Services.Cache;
using RetalineProAgent.Core.Services.GST;
using RetalineProAgent.Service;
using RetalineProAgent.Service.Store;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Text;
using System.Text.RegularExpressions;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class GST_Add: Base.BasePartnerPage
    {
        string strGSTLabel = (System.Configuration.ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT");
        private GSTValidationResult CurGSTResult
        {
            get
            {
                return (GSTValidationResult)ViewState["CUGSTVALRESULT"];
            }
            set
            {
                ViewState["CUGSTVALRESULT"] = value;
            }
        }


        private string CurViewType
        {
            get
            {
                return (string)ViewState["CURVIEWTYPE"];
            }
            set
            {
                ViewState["CURVIEWTYPE"] = value;
            }
        }

        private int? GSTRecordId
        {
            get
            {
                return (int?)ViewState["GSTTABLEID"];
            }
            set
            {
                ViewState["GSTTABLEID"] = value;
            }
        }

        protected void Page_Load(object sender, EventArgs e)
        {
            lblResult.Text = lblOTPResult.Text = "";
            if(!IsPostBack)
            {
                SetGSTPlaceholder();
                ltrAutoFocusObj.Text = txtGST.ClientID;

                if(!String.IsNullOrEmpty(Request.QueryString["action"]) && !String.IsNullOrEmpty(Request.QueryString["id"]))
                {
                    List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                    prms.Add(new KeyValuePair<string, object>("storegroupId", this.CurrentUser.StoreGroupId));
                    prms.Add(new KeyValuePair<string, object>("gstid", Request.QueryString["id"]));
                    DataTable dtGST = DataService.GetDataTable("Select top 1 g.* from GST g where g.id=@gstid and g.TenantId=@storegroupId and isnull(g.isverified, -1) <= 0 and not exists(select * from GST where isverified=1 and gstin=g.gstin and exists(select * from GSTLog where gstin= g.gstin and is_test_gst = 0))", parmeters: prms);
                    if (dtGST == null || dtGST.Rows.Count <= 0)
                    {
                        //lblResult.Text = "Invalid GSTIN";
                        ShowFailure( $"{strGSTLabel} Verification failed", $"Invalid {strGSTLabel} or the record was expired!! You can add new {strGSTLabel} and verify it in order to activate.");
                        return;
                    }

					GSTValidationResult gstResult = new GSTValidationResult();
					gstResult.GSTIN = dtGST.Rows[0]["gstin"].ToString();
					gstResult.TradeName = dtGST.Rows[0]["organization"].ToString();
					gstResult.Address = dtGST.Rows[0]["address"].ToString();
					gstResult.Email = dtGST.Rows[0]["email"].ToString();
					gstResult.Mobile = dtGST.Rows[0]["mobile"].ToString();
					try { gstResult.RawResponse = dtGST.Rows[0]["gstdata"].ToString(); } catch { }

                    int gstrecid = Convert.ToInt32(dtGST.Rows[0]["id"]);
                    GSTRecordId = gstrecid;
                    CurGSTResult = gstResult;

                    String strMobile = gstResult.Mobile;
                    string gstEmail = gstResult.Email;

                    CurViewType = "2";
                    ltrAutoFocusObj.Text = go1.ClientID;
                    ltrGSTMaskedMobile.Text = String.Format("{0}XXXXXX{1}", strMobile.Substring(0, 2), strMobile.Substring(8));
                    ltrGSTMaskedEmail.Text = String.Format("{0}XX..{1}.{2}XX", gstEmail.Substring(0, 2), gstEmail.Substring(gstEmail.LastIndexOf('@') - 1, 3), gstEmail.Substring(gstEmail.LastIndexOf('.') + 1), 1);

                }
                else if(UserService.CachedDefaultUser.TenantStatus == 2)
                {
                    List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                    prms.Add(new KeyValuePair<string, object>("storegroupId", this.CurrentUser.StoreGroupId));
                    DataTable dtGST = DataService.GetDataTable("Select top 1 g.* from GST g where g.TenantId=@storegroupId and isnull(g.isverified, -1) <= 0 and not exists(select * from GST where isverified=1 and gstin=g.gstin and exists(select * from GSTLog where gstin= g.gstin and is_test_gst = 0))", parmeters: prms);
                    if(dtGST != null && dtGST.Rows.Count > 0)
                    {
                        try {
                            txtGSTNum.Value = txtGST.Text = dtGST.Rows[0]["gstin"].ToString();
                            txtGSTNum.Attributes.Add("placeholder", txtGST.Text);

							GSTValidationResult gstResult = new GSTValidationResult();
							gstResult.GSTIN = dtGST.Rows[0]["gstin"].ToString();
							gstResult.TradeName = dtGST.Rows[0]["organization"].ToString();
							gstResult.Address = dtGST.Rows[0]["address"].ToString();
							gstResult.Email = dtGST.Rows[0]["email"].ToString();
							gstResult.Mobile = dtGST.Rows[0]["mobile"].ToString();
							try { gstResult.RawResponse = dtGST.Rows[0]["gstdata"].ToString(); } catch { }

                            int gstrecid = Convert.ToInt32(dtGST.Rows[0]["id"]);
                            GSTRecordId = gstrecid;
                            CurGSTResult = gstResult;

                            String strMobile = gstResult.Mobile;
                            string gstEmail = gstResult.Email;
                            //CurViewType = "3";
                            Core.Services.APIService.GetOtp(strMobile, gst: txtGST.Text, templateid: 20);

                            //var tblOtp = DataServiceMySql.GetDataTable($"SELECT * FROM retaline_customer_signup_verifiLog WHERE veri_mobile = {strMobile} order by veri_id desc LIMIT 1", UserService.GetAPIConnectionString());
                            //if (tblOtp != null && tblOtp.Rows.Count > 0)
                            //{
                            //    string strBody = $"<br/>Please use the OTP <span style='color: red; font-weight:bold; font-size: 13'>{tblOtp.Rows[0]["veri_sms_code"]}</span> to complete your GST registration.<br/><br/>Thank you<br/>{UserService.CachedDefaultUser.StoreGroupName}";
                            //    Core.Services.APIService.SendEmail(gstEmail, "GST Verification OTP", strBody, "", true);
                            //}

                            CurViewType = "2";
                            ltrAutoFocusObj.Text = go1.ClientID;
                            ltrGSTMaskedMobile.Text = String.Format("{0}XXXXXX{1}", strMobile.Substring(0, 2), strMobile.Substring(8));
                            ltrGSTMaskedEmail.Text = String.Format("{0}XX..{1}.{2}XX", gstEmail.Substring(0, 2), gstEmail.Substring(gstEmail.LastIndexOf('@') - 1, 3), gstEmail.Substring(gstEmail.LastIndexOf('.') + 1), 1);


                        }
                        catch { }
                    }
                }
            }
        }

        protected void Page_PreRender(object sender, EventArgs e)
        {
            go1.Value = go2.Value = go3.Value = go4.Value = "";

            plcAddGST.Visible = (String.IsNullOrEmpty(CurViewType) || CurViewType == "1");
            plcGSTOTP.Visible = (CurViewType == "2");
            plcSignupGSTSuccess.Visible = (CurViewType == "3");

            try
            {
                if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
                    txtGST.Attributes["placeholder"] = "Enter VAT";
            }
            catch { }

            // 
        }

        private void SetGSTPlaceholder()
        {
            if (ConfigurationManager.AppSettings.Get("VATType") == "2")
            {
                txtGST.Attributes["placeholder"] = "Enter GSTIN";
            }
            else
            {
                txtGST.Attributes["placeholder"] = "Enter VAT";
            }
        }

        protected async void btnAddGST_Click(object sender, EventArgs e)
        {
            string vattype = ConfigurationManager.AppSettings.Get("VATType");
            txtGSTNum.Value = txtGST.Text;

            if (vattype == "1")
            {
                Core.BussinessModel.VAT.VATData vatData = null;

                var vatResult = (new Service.Store.VATService()).ValidateVAT(txtGSTNum.Value);
                if (vatResult != null)
                    vatData = vatResult.VatData;
                if (vatData == null || !vatResult.Success || vatData == null || String.IsNullOrEmpty(vatData.company_name))
                {
                    lblResult.Text = (vatResult == null || vatResult.VatData == null || String.IsNullOrEmpty(vatResult.VatData.vat_number) ? "Invalid VAT number or VAT master data access is not available" : vatResult.VatData.company_name);
                    ShowFailure(strGSTLabel + " Verification failed", $"Invalid {strGSTLabel}!! Please enter valid {strGSTLabel} and try again.");
                    return;
                }

                string sqlAddition = "";

                List<KeyValuePair<string, object>> gstPrms = new List<KeyValuePair<string, object>>();
                gstPrms.Add(new KeyValuePair<string, object>("address", vatData.company_address));
                gstPrms.Add(new KeyValuePair<string, object>("vdata", System.Text.Json.JsonSerializer.Serialize(vatData)));
                gstPrms.Add(new KeyValuePair<string, object>("gstin", vatData.vat_number));
                gstPrms.Add(new KeyValuePair<string, object>("organization", vatData.company_name));
                gstPrms.Add(new KeyValuePair<string, object>("email", ""));
                gstPrms.Add(new KeyValuePair<string, object>("mobile", ""));
                gstPrms.Add(new KeyValuePair<string, object>("tenantid", this.CurrentUser.StoreGroupId));

                gstPrms.Add(new KeyValuePair<string, object>("isverified", 1));
                gstPrms.Add(new KeyValuePair<string, object>("userid", this.CurrentUser.Id));
                gstPrms.Add(new KeyValuePair<string, object>("CreatedBy", string.IsNullOrEmpty(this.CurrentUser.Email) ? this.CurrentUser.Phone : this.CurrentUser.Email));
                string sqlInsertGST = $"INSERT INTO GST(address, email, gstdata, gstin, isverified, mobile, organization, userid, tenantid,Createdby) VALUES(@address, @email, @vdata, @gstin, @isverified, @mobile, @organization, @userid, @tenantid,@CreatedBy); select scope_identity(); {sqlAddition}" ;
                DataTable dtGSTId = DataService.GetDataTable(sqlInsertGST, parmeters: gstPrms);
                //if (dtGSTId != null && dtGSTId.Rows.Count > 0)
                //    gstid = Convert.ToInt32(dtGSTId.Rows[0][0]);

                // Remove Redis cache entry
                var cacheService = new RedisCacheService();
                string cachekey = $"Retl.AppTenant.pendingtasks.count.{this.CurrentUser.APIStoreId}";
                await cacheService.RemoveAsync(cachekey);

                // Activitylog
                String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                string Source = strUrl;
                int storegroupid = this.CurrentUser.APIStoreId; ;
                string User = this.CurrentUser.Email;
                string Address = vatData.company_address;
                string v_data = System.Text.Json.JsonSerializer.Serialize(vatData);
                string Gstin = vatData.vat_number;
                string Organization = vatData.company_name;
                var items = new[]
                    {
                    new { Key = "Address", Value = vatData.company_address },
                    new { Key = "Gst data", Value = v_data },
                    new { Key = "Gstin", Value = Gstin },
                    new { Key = "Organization", Value = Organization },
                    new { Key = "Tenantid", Value = storegroupid.ToString() },
                };
                string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);

                Service.UserService.CachedDefaultUser = null;
                string strcontent = $"<p class=\"mg-b-5\">{strGSTLabel} has been verified successfully and added to your list of VAT accounts. The {strGSTLabel} can be linked to store/branch in the store settings page.</p>";
                ShowSuccess(strGSTLabel + " Verification Success!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">{strGSTLabel} account has been validated and added successfully!</a></h5>" + strcontent);

                return;
            }

            VatResult gstinResult = (new Service.Store.VATService()).ValidateGST(txtGSTNum.Value);
            if (gstinResult == null || !gstinResult.Success )
            {
                lblResult.Text = "Invalid " + strGSTLabel + " Account";
                ShowFailure(strGSTLabel + " Verification failed", $"Invalid {strGSTLabel} Account!! Please enter valid {strGSTLabel} and try again.");
                return;
            }

            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("gst", txtGST.Text));
            prms.Add(new KeyValuePair<string, object>("tenantid", this.CurrentUser.StoreGroupId));
            GSTValidationResult gstResult = null;
			bool isTestGST = false;

            try
            {
                var tblTestGST = DataService.GetDataTable("SELECT * FROM GSTLog WHERE gstin = @gst ORDER BY id desc", parmeters: prms);
                if (tblTestGST != null && tblTestGST.Rows.Count > 0)
                {
                    gstResult = new GSTValidationResult();
					gstResult.GSTIN = tblTestGST.Rows[0]["gstin"].ToString();
                    gstResult.TradeName = tblTestGST.Rows[0]["organization"].ToString();
                    gstResult.Address = tblTestGST.Rows[0]["address"].ToString();
                    gstResult.Email = tblTestGST.Rows[0]["email"].ToString();
                    gstResult.Mobile = tblTestGST.Rows[0]["mobile"].ToString();
                    try { gstResult.RawResponse = tblTestGST.Rows[0]["gstdata"].ToString(); } catch { }
                    try { isTestGST = Convert.ToBoolean(tblTestGST.Rows[0]["is_test_gst"]); } catch { isTestGST = false; }
                }
            }
            catch (Exception exGST)
            {
				gstResult = null;
            }
            if (gstResult == null || !isTestGST)
            {
                var tblDuplicateGST = DataService.GetDataTable("select * from gst where tenantid =@tenantid and gstin=@gst", parmeters: prms);
                if(tblDuplicateGST != null && tblDuplicateGST.Rows.Count > 0)
                {
                    Common.ShowCustomAlert(this.Page, "Duplicate", "Entered GST is already linked. Use a different one.", false, "/Tenant/Store/GST-Add");
                    return;
                }
            }

            if (gstResult == null || string.IsNullOrEmpty(gstResult.GSTIN))
            {
				gstResult = (new GSTValidatorService()).ValidateGST(txtGST.Text);

				if (gstResult != null)
                {
                    try
                    {
                        List<KeyValuePair<string, object>> prmsInsertGSTLog = new List<KeyValuePair<string, object>>();
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("gstin", gstResult.GSTIN));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("email", gstResult.Email));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("mobile", gstResult.Mobile));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("organization", gstResult.TradeName));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("address", gstResult.Address));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("is_test_gst", 0));
                        prmsInsertGSTLog.Add(new KeyValuePair<string, object>("gst_data", gstResult.RawResponse.ToString()));
                        DataService.ExecuteSql("INSERT INTO GSTLog (gstin, email, mobile, organization, address, is_test_gst, gstdata) VALUES(@gstin, @email, @mobile, @organization, @address, @is_test_gst, @gst_data)", parmeters: prmsInsertGSTLog);
                    }
                    catch { }
                    // Activitylog
                    String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                    String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                    string Source = strUrl;
                    int storegroupid = this.CurrentUser.APIStoreId; ;
                    string User = this.CurrentUser.Email;

                    string gstin = gstResult.GSTIN;
                    string Email = gstResult.Email;
                    string Mobile = gstResult.Mobile;
                    string Organization = gstResult.TradeName;
                    string Address = gstResult.Address;
                    string gst_result = gstResult.RawResponse;
                    var items = new[]
                    {
                    new { Key = "Gstin", Value = gstin },
                    new { Key = "Email", Value = Email },
                    new { Key = "Mobile", Value = Mobile },
                    new { Key = "Organization", Value = Organization },
                    new { Key = "Address", Value = Address },
                    new { Key = "gstdata", Value = gst_result },
                    new { Key = "Tenantid", Value = storegroupid.ToString() },
                    };
                    string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                    var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
                }

            }

            if (gstResult == null || string.IsNullOrEmpty(gstResult.GSTIN))
            {
                lblResult.Text = $"Invalid {strGSTLabel} number or {strGSTLabel} master data access is not available";
                ShowFailure($"{strGSTLabel} Verification failed", $"Invalid {strGSTLabel} number or {strGSTLabel} master data access is not available.");
                return;
            }
            else if (string.IsNullOrEmpty(gstResult.Mobile))
            {
                lblResult.Text = $"No verification data is available with the {strGSTLabel} master data. Please update your {strGSTLabel} master data with valid contact number to get verified, or try with a different {strGSTLabel} number. Please contact technical support for more details.";
                ShowFailure($"{strGSTLabel} Verification failed", $"No verification data is available with the {strGSTLabel} master data. Please update your {strGSTLabel} master data with valid contact number to get verified, or try with a different {strGSTLabel} number. Please contact technical support for more details.");
                return;

            }

            CurGSTResult = gstResult;

            String strMobile = gstResult.Mobile;
            string gstEmail = gstResult.Email;
            //CurViewType = "3";
            Core.Services.APIService.GetOtp(strMobile, gst: txtGST.Text, templateid: 20);

            var tblOtp = DataServiceMySql.GetDataTable($"SELECT * FROM retaline_customer_signup_verifiLog WHERE veri_mobile = {strMobile} order by veri_id desc LIMIT 1", UserService.GetAPIConnectionString());
            if (tblOtp != null && tblOtp.Rows.Count > 0)
            {
                String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                List<KeyValuePair<string, string>> replacements = new List<KeyValuePair<string, string>>();
                replacements.Add(new KeyValuePair<string, string>("[URLPART]", strUrl.TrimEnd(new char[] { '/', '\\' })));
                replacements.Add(new KeyValuePair<string, string>("[OTP CONTENT]", tblOtp.Rows[0]["veri_sms_code"].ToString()));
                string strBody = EmailService.CreateEmailbody(EmailType.GSTVerification, replacements);
                //string strBody = $"<br/>Please use the OTP <span style='color: red; font-weight:bold; font-size: 13'>{tblOtp.Rows[0]["veri_sms_code"]}</span> to complete your {strGSTLabel} registration.<br/><br/>Thank you<br/>{UserService.CachedDefaultUser.StoreGroupName}";
                Core.Services.APIService.SendEmail(gstEmail, strGSTLabel + " Verification OTP", strBody, "", true);
            }

            CurViewType = "2";
            ltrAutoFocusObj.Text = go1.ClientID;
            ltrGSTMaskedMobile.Text = String.Format("{0}XXXXXX{1}", strMobile.Substring(0, 2), strMobile.Substring(8));
            ltrGSTMaskedEmail.Text = String.Format("{0}XX..{1}.{2}XX", gstEmail.Substring(0, 2), gstEmail.Substring(gstEmail.LastIndexOf('@') - 1, 3), gstEmail.Substring(gstEmail.LastIndexOf('.') + 1), 1);


        }

        protected void btnGSTOTPVerify_Click(object sender, EventArgs e)
        {
            string strOtp = $"{go1.Value}{go2.Value}{go3.Value}{go4.Value}";
            GSTValidationResult gstResult = CurGSTResult;
            var gstVerificationResult = Core.Services.APIService.VerifyOtp(gstResult.Mobile, strOtp);

            if (gstResult != null && gstVerificationResult.Data != null && gstVerificationResult.Data.IsVerified && gstResult != null)
            {
                if (GSTRecordId != null && this.CurrentUser.TenantStatus == 2)
                {
                    List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                    prms.Add(new KeyValuePair<string, object>("gstid", GSTRecordId));
                    prms.Add(new KeyValuePair<string, object>("tenantid", this.CurrentUser.StoreGroupId));
                    string sqlInsertGST = "update gst set isverified=1 where id=@gstid and tenantid=@tenantid; ";
                    sqlInsertGST += " UPDATE StoreBranch SET GSTId= @gstid WHERE Id=(select top 1 Id from StoreBranch where StoreId = @tenantid and isnull(GSTId, -1) <= 0 order by id); UPDATE AppTenant set Status= 1 where Status = 2 and Id = @tenantid; ";
                    DataService.ExecuteSql(sqlInsertGST, parmeters: prms);
                }
                else
                {
                    bool canCheckout = true;
                    try { canCheckout = (ConfigurationManager.AppSettings.Get("CanCheckout") == "1" ? true : false); } catch { canCheckout = false; }

                    List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                    prms.Add(new KeyValuePair<string, object>("gstin", gstResult.GSTIN));
                    prms.Add(new KeyValuePair<string, object>("organization", gstResult.TradeName));
                    prms.Add(new KeyValuePair<string, object>("address", gstResult.Address));
                    prms.Add(new KeyValuePair<string, object>("email", gstResult.Email));
                    prms.Add(new KeyValuePair<string, object>("mobile", gstResult.Mobile));
                    prms.Add(new KeyValuePair<string, object>("gdata", gstResult.RawResponse));
                    prms.Add(new KeyValuePair<string, object>("tenantid", this.CurrentUser.StoreGroupId));
                    prms.Add(new KeyValuePair<string, object>("canCheckout", canCheckout));
                    prms.Add(new KeyValuePair<string, object>("CreatedBy", string.IsNullOrEmpty(this.CurrentUser.Email) ? this.CurrentUser.Phone : this.CurrentUser.Email));

                    string sqlInsertGST = "INSERT INTO gst(gstin, organization, address, email, mobile, gstdata, tenantid, isverified,Createdby) VALUES(@gstin, @organization, @address, @email, @mobile, @gdata, @tenantid, 1,@CreatedBy); ";
                   
                    if (this.CurrentUser.TenantStatus == 2)
                    {
                        sqlInsertGST += " UPDATE StoreBranch SET GSTId= scope_identity() WHERE Id=(select top 1 Id from StoreBranch where StoreId = @tenantid and isnull(GSTId, -1) <= 0 order by id); UPDATE AppTenant set Status= 1, canCheckout= @canCheckout where Status = 2 and Id = @tenantid; ";
                        
                    }
                    if (this.CurrentUser.TenantType == 2)
                    {
                        sqlInsertGST += " update AppTenant set TenantType=1 where id=@tenantid ; ";
                        Service.UserService.CachedDefaultUser = null;
                    }
                     DataService.ExecuteSql(sqlInsertGST, parmeters: prms);
                    // Activitylog
                    String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                    String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                    string Source = strUrl;
                    int storegroupid = this.CurrentUser.APIStoreId; ;
                    string User = this.CurrentUser.Email;

                    string gstin = gstResult.GSTIN;
                    string Email = gstResult.Email;
                    string Mobile = gstResult.Mobile;
                    string Organization = gstResult.TradeName;
                    string Address = gstResult.Address;
                    string gresult = gstResult.RawResponse;
                    var items = new[]
                    {
                    new { Key = "Gstin", Value = gstin },
                    new { Key = "Email", Value = Email },
                    new { Key = "Mobile", Value = Mobile },
                    new { Key = "Organization", Value = Organization },
                    new { Key = "Address", Value = Address },
                    new { Key = "gstdata", Value = gresult },
                    new { Key = "Tenantid", Value = storegroupid.ToString() },
                    new { Key = "Can Checkout", Value = canCheckout.ToString() },
                    };
                    string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                    var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);

                }

                if (this.CurrentUser.TenantStatus == 2)
                    Service.UserService.CachedDefaultUser = null;

                    CurViewType = "3";
                string strcontent = $"<p class=\"mg-b-5\">{strGSTLabel} account has been verified successfully and added to your list of GSTs. The {strGSTLabel} can be linked to store/branch in the store settings page.</p>";
                ShowSuccess(strGSTLabel+" Verification Success!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">{strGSTLabel} account has been validated and added successfully!</a></h5>" + strcontent);

            }
            else
            {
                lblOTPResult.Text = "Invalid OTP or verification failed!!";
                ShowFailure(strGSTLabel+" Verification failed", "Invalid OTP or verification failed!! Please correct your input and try again.");

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

        protected void lbtnResentOTP_Click(object sender, EventArgs e)
        {
            var gstResult = CurGSTResult;
            if(gstResult == null)
            {
                ShowFailure("Resent "+ strGSTLabel + " OTP", $"Invalid {strGSTLabel}!! The {strGSTLabel} is not valid or expired. Please try again with add {strGSTLabel}.");
                CurViewType = "1";
                return;
            }
            String strMobile = gstResult.Mobile;
            string gstEmail = gstResult.Email;
            //CurViewType = "3";
            Core.Services.APIService.GetOtp(strMobile, gst: txtGST.Text, templateid: 20);

            var tblOtp = DataServiceMySql.GetDataTable($"SELECT * FROM retaline_customer_signup_verifiLog WHERE veri_mobile = {strMobile} order by veri_id desc LIMIT 1", UserService.GetAPIConnectionString());
            if (tblOtp != null && tblOtp.Rows.Count > 0)
            {
                String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                List<KeyValuePair<string, string>> replacements = new List<KeyValuePair<string, string>>();
                replacements.Add(new KeyValuePair<string, string>("[URLPART]", strUrl.TrimEnd(new char[] { '/', '\\' })));
                replacements.Add(new KeyValuePair<string, string>("[OTP CONTENT]", tblOtp.Rows[0]["veri_sms_code"].ToString()));
                string strBody = EmailService.CreateEmailbody(EmailType.GSTVerification, replacements);
                //string strBody = $"<br/>Please use the OTP <span style='color: red; font-weight:bold; font-size: 13'>{tblOtp.Rows[0]["veri_sms_code"]}</span> to complete your {strGSTLabel} registration.<br/><br/>Thank you<br/>{UserService.CachedDefaultUser.StoreGroupName}";
                Core.Services.APIService.SendEmail(gstEmail, strGSTLabel+" Verification OTP", strBody, "", true);
            }

            CurViewType = "2";
            ltrAutoFocusObj.Text = go1.ClientID;
            ltrGSTMaskedMobile.Text = String.Format("{0}XXXXXX{1}", strMobile.Substring(0, 2), strMobile.Substring(8));
            ltrGSTMaskedEmail.Text = String.Format("{0}XX..{1}.{2}XX", gstEmail.Substring(0, 2), gstEmail.Substring(gstEmail.LastIndexOf('@') - 1, 3), gstEmail.Substring(gstEmail.LastIndexOf('.') + 1), 1);

        }
    }
}