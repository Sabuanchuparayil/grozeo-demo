using NPOI.OpenXmlFormats.Wordprocessing;
using RetalineProAgent.Core.BussinessModel.Finance;
using RetalineProAgent.Core.BussinessModel.VAT;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Net.Mail;
using System.Reflection;
using System.Text;
using System.Text.RegularExpressions;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{

    public partial class signup_Deleted : Base.BasePartnerPage
    {
        //protected void Page_Load(object sender, EventArgs e)
        //{
        //    lblResult.Text = "";
        //    string logoUrl = ConfigurationManager.AppSettings.Get("logoUrl");
        //    if (!String.IsNullOrEmpty(logoUrl))
        //        imgLogo.Src = logoUrl;
        //}

        private Core.BussinessModel.GST.GSTData CurGSTData { 
            get {
                return (Core.BussinessModel.GST.GSTData)ViewState["MYGSTDATA"];
            } 
            set {
                ViewState["MYGSTDATA"] = value; 
            } 
        }
        private Core.BussinessModel.VAT.VATData CurVATData
        {
            get
            {
                return (Core.BussinessModel.VAT.VATData)ViewState["MYVATDATA"];
            }
            set
            {
                ViewState["MYVATDATA"] = value;
            }
        }
        private Core.BussinessModel.VAT.TRNData CurTRNData
        {
            get
            {
                return (Core.BussinessModel.VAT.TRNData)ViewState["MYTRNDATA"];
            }
            set
            {
                ViewState["MYTRNDATA"] = value;
            }
        }

        private Core.BussinessModel.PAN.PANInfo CurPANData
        {
            get
            {
                return (Core.BussinessModel.PAN.PANInfo)ViewState["MYPANDATA"];
            }
            set
            {
                ViewState["MYPANDATA"] = value;
            }
        }

        /// <summary>
        /// GST/VAT
        /// </summary>
        public Service.Store.VATType CurTaxType
        {
            get
            {
                if (ViewState["CURTAXTYPE"] == null)
                    ViewState["CURTAXTYPE"] = (String.IsNullOrEmpty(ConfigurationManager.AppSettings.Get("VATType")) ? 0 : Convert.ToInt32(ConfigurationManager.AppSettings.Get("VATType")));

                return (Service.Store.VATType)ViewState["CURTAXTYPE"];
            }
            set
            {
                ViewState["CURTAXTYPE"] = value;
            }
        }
        /// <summary>
        /// GST/VAT
        /// </summary>
        public int CurVATType
        {
            get
            {
                if (ViewState["CURVATTYPE"] == null)
                    ViewState["CURVATTYPE"] = (String.IsNullOrEmpty(ConfigurationManager.AppSettings.Get("VATType")) ? 0 : Convert.ToInt32(ConfigurationManager.AppSettings.Get("VATType")));

                return (int)ViewState["CURVATTYPE"];
            }
            set
            {
                ViewState["CURVATTYPE"] = value;
            }
        }
        /// <summary>
        /// Display view (the type of view based on various user action)
        /// </summary>
        public string CurViewType
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
        /// <summary>
        /// Current Verification key
        /// </summary>
        private string VerifyKey
        {
            get
            {
                return (string)ViewState["CURVERIFIKEY"];
            }
            set
            {
                ViewState["CURVERIFIKEY"] = value;
            }
        }
        /// <summary>
        /// Current GST status
        /// </summary>
        private Services.GSTStatus CurGSTStatus
        {
            get
            {
                if (ViewState["CURVERIGSTSTATUS"] == null)
                    return Services.GSTStatus.NoGST;

                return (Services.GSTStatus)ViewState["CURVERIGSTSTATUS"];
            }
            set
            {
                ViewState["CURVERIGSTSTATUS"] = value;
            }

        }

        private KeyValuePair<int, string> CurValidationKey
        {
            get
            {
                if (ViewState["CURVIEWVALIDATIONKEY"] == null)
                    return default;

                return (KeyValuePair<int, string>)ViewState["CURVIEWVALIDATIONKEY"];
            }
            set
            {
                ViewState["CURVIEWVALIDATIONKEY"] = value;
            }

        }
        /// <summary>
        /// The signup mobile number that was verified with OTP, stored in Viewstate.
        /// </summary>
        private string CurMobileNumberVerified
        {
            get
            {
                return (string)ViewState["CURVERIFIEDMOBILE"];
            }
            set
            {
                ViewState["CURVERIFIEDMOBILE"] = value;
            }
        }

        /// <summary>
        /// Current invitation code if used.
        /// </summary>
        public string CurInvitationCode
        {
            get
            {
                if (ViewState["CURVIEWINVTCODE"] == null)
                    return "";

                return ViewState["CURVIEWINVTCODE"].ToString();
            }
            set
            {
                ViewState["CURVIEWINVTCODE"] = value;
            }

        }

        /// <summary>
        /// Adhar client id (for OTP verification)
        /// </summary>
        public string AdharClientID
        {
            get
            {
                return (string)ViewState["ADHARCLIENTID"];
            }
            set
            {
                ViewState["ADHARCLIENTID"] = value;
            }
        }

        /// <summary>
        /// Adhar data
        /// </summary>
        private Core.BussinessModel.Adhar.AdharInfo CurAdharData
        {
            get
            {
                return (Core.BussinessModel.Adhar.AdharInfo)ViewState["MYADHARDATA"];
            }
            set
            {
                ViewState["MYADHARDATA"] = value;
            }
        }

        /// <summary>
        /// Page loads
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                LinkButton2.Text = LinkButton1.Text = lbContinueWithGST.Text = lbAffiliateContinueWithVAT.Text = "Continue with "+ Service.Store.VATService.VATLabel;
                btnSkipGSTValidation.Text = "Skip "+ Service.Store.VATService.VATLabel + " Validation";
                btnChangeGSTNumber.Text = "Change "+ Service.Store.VATService.VATLabel + " Number";
                lbtnNoGST.Text = "I don't have "+ Service.Store.VATService.VATLabel + " Number";

                lbAffiliateContinueWithVAT.Text = "Continue with " + Service.Store.VATService.VATLabel;
                int restrictsignup = 1;
                if (!String.IsNullOrEmpty(ConfigurationManager.AppSettings.Get("SignupRestrictByInvite")))
                {
                    try {
                        restrictsignup = Convert.ToInt32(ConfigurationManager.AppSettings.Get("SignupRestrictByInvite"));
                    } catch { }
                }
                plcWithInvitationCode.Visible = true;
                plcWithoutInvitationCode.Visible = !plcWithInvitationCode.Visible;
                rqdpostcod.Enabled= ConfigurationManager.AppSettings["CountryCode"] != "AE";
                // ** Restrict signup with invtiation code. -- Temporary removed
                //if (restrictsignup > 0)
                //{
                //    plcWithInvitationCode.Visible = !String.IsNullOrEmpty(Request.QueryString["refcode"]);
                //    plcWithoutInvitationCode.Visible = !plcWithInvitationCode.Visible;
                //}
                //else
                //{
                //    plcWithInvitationCode.Visible = true;
                //    plcWithoutInvitationCode.Visible = !plcWithInvitationCode.Visible;
                //}
                rfvreferral.Enabled = ConfigurationManager.AppSettings["SignupRestrictByInvite"] == "1";
                if (!String.IsNullOrEmpty(Request.QueryString["refcode"]))
                {
                    List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                    prms.Add(new KeyValuePair<string, object>("code", Request.QueryString["refcode"]));
                    var dtResult = RetalineProAgent.Core.Services.DataServiceMySql.GetDataTable("SELECT * FROM finascop_crm_prospect WHERE (crpr_mode = 5 OR IFNULL(storeGroupId, 0) < 1) and invitationCode=@code AND DATE_ADD(NOW(), INTERVAL -30 MINUTE) < TIMESTAMP(IFNULL(crpr_ExpiredOn, crpr_CreatedOn))", parmeters: prms);
                    if (dtResult != null && dtResult.Rows.Count > 0)
                    {
                        CurInvitationCode = Request.QueryString["refcode"];
                        txtInvitationCode.Text = Request.QueryString["refcode"];                        
                        if (!String.IsNullOrEmpty(CurInvitationCode))
                        {
                            txtRaferralcode.Text = CurInvitationCode;
                            txtRaferralcode.ReadOnly = true;
                            txtRaferralcode.Enabled=false;
                        }
                        
                    }
                    else if(restrictsignup > 0)
                    {
                        Common.ShowCustomAlert(this.Page, "Failure", "Invalid request or the invitation link was expired", false);
                        return;
                    }
                    else
                    {
                        Common.ShowToastifyMessage(this.Page, "Invalid request or the invitation link was expired", "danger");
                    }

                }

                if (!String.IsNullOrEmpty(Request.QueryString["withpostcode"]) && Request.QueryString["withpostcode"] == "1")
                    Session.Add("SHOWPOSTCODER", 1);

            }


            string countryCode = ConfigurationManager.AppSettings.Get("CountryCode");
            string maxLength = countryCode == "IN" ? "10" : countryCode == "UK" ? "13" :countryCode == "AE" ? "9" : "12";
            txtContactPhone.Attributes["maxlength"] = maxLength;
            txtSignupMobileNumber.Attributes["maxlength"] = maxLength;

            btnSkipGSTValidation.Text = $"Skip {Service.Store.VATService.VATLabel} Validation";
            btnChangeGSTNumber.Text = $"Change {Service.Store.VATService.VATLabel} Number";

            if (ConfigurationManager.AppSettings.Get("IsPinNumeric") == "1")
                txtPinCode.TextMode = TextBoxMode.Number;
            else
                txtPinCode.TextMode = TextBoxMode.SingleLine;

            ltrResult.Text = "";
            ctrlAddressMap1.ParentLocationClientId = hidMapAddr.ClientID; // txtLocation.ClientID;
            ctrlAddressMap1.ParentLatClientId = hidLat.ClientID;
            ctrlAddressMap1.ParentLongClientId = hidLong.ClientID;
            ctrlAddressMap1.ParentPinClientId = txtPinCode.ClientID;
            ctrlAddressMap1.ParentLocationNameClientId = txtLocation.ClientID; //txtAddr1.ClientID;
            //ctrlAddressMap1.ParentAddrClientId = txtAddr2.ClientID;
            ctrlAddressMap1.ParentDistrictClientId = hidDistrict.ClientID;
            ctrlAddressMap1.ParentStateClientId = hidState.ClientID;

            plcSignupGSTShowVerification.Visible = CurVATType == 2;
            plcSignupGSTSkipVerification.Visible = CurVATType != 2;
            lbtnChangeGST.Text = "Change " + Service.Store.VATService.VATLabel;
            lbtnNoGST.Text = "I don't have " + Service.Store.VATService.VATLabel;
            lbContinueWithGST.Text = "Continue with " + Service.Store.VATService.VATLabel;
            txtGSTNumber.Attributes.Add("placeholder", $"Enter {Service.Store.VATService.VATLabel} #");
            // pGSTText.InnerHtml = (CurVATType == 2 ? pGSTText.InnerHtml : Service.Store.VATService.VATLabel+" Priority will be given to registered merchants when listing on Grozeo.");
            if (!String.IsNullOrEmpty(hidLat.Value))
                ctrlAddressMap1.Lat = hidLat.Value;
            if (!String.IsNullOrEmpty(hidLong.Value))
                ctrlAddressMap1.Lng = hidLong.Value;

            if (!IsPostBack)
            {

                if (User.Identity.IsAuthenticated)
                    FormsAuthenticationService.SignOut();

                pnlResetPswSuccess.Visible = false;
                pnlResetPswInvalidkey.Visible = false;
                pnlResetPswView.Visible = true;

                if (!String.IsNullOrEmpty(Request.QueryString["verify"]))
                {
                    VerifyKey = Request.QueryString["verify"];
                    CurViewType = "8";
                    string sql = $"select top 1 * from VerifyLog where verify_key = @key and expire_time >= getutcdate() order by id desc";
                    List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                    prms.Add(new KeyValuePair<string, object>("key", VerifyKey));
                    var tblUserLog = DataService.GetDataTable(sql, parmeters: prms);
                    if (tblUserLog == null || tblUserLog.Rows.Count < 1)
                    {
                        pnlResetPswInvalidkey.Visible = true;
                        pnlResetPswView.Visible = false;
                    }

                }
                txtSignupMobileNumber.Focus();
            }

        }

        /// <summary>
        /// End of pare render.
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        protected void Page_PreRender(object sender, EventArgs e)
        {
            plcSignupMobile.Visible = (String.IsNullOrEmpty(CurViewType) || CurViewType == "1");
            plcSignupGST.Visible = (CurViewType == "2" || CurViewType == "3");
            plcSignupGSTOTP.Visible= (CurViewType == "3");
            plcSignupGSTSuccess.Visible = (CurViewType == "4");
            plcSignupNoGST.Visible = (CurViewType == "6");
            plcSetPassword.Visible = (CurViewType == "8");

            //plcSignupComplete.Visible = (CurViewType == "5");
            //plcSignupRegister.Visible = (CurViewType == "7");
            //plcGSTVerificationSkiped.Visible = ltrGSTVerificationSkiped.Visible = CurGSTStatus != Services.GSTStatus.Verified;
            //ltrGSTVerifiedSuccessfully.Visible = !ltrGSTVerificationSkiped.Visible;

            pGSTINRequest.Visible = CurVATType == 2 && !plcSignupGSTOTP.Visible;
            pGSTINOTP.Visible = plcSignupGSTOTP.Visible;

            plsHeaderPostcoder.Visible = false;
            if (Session["SHOWPOSTCODER"] != null && (int)Session["SHOWPOSTCODER"] == 1)
                plsHeaderPostcoder.Visible = true;

            // Set focus to the relevant input box.
            SetFocusInput();
        }

        /// <summary>
        /// Set focus to the input based on the current View
        /// </summary>
        private void SetFocusInput()
        {
            switch (CurViewType)
            {
                case "2":
                    btnSubmitGSTNumber.Visible = true;
                    txtGSTNumber.Focus();
                    break; 
                case "3":
                    btnSubmitGSTNumber.Visible = false;
                    gstOTP.Focus();
                    break;
                case "4":
                    txtStoreName.Focus();
                    break;
                case "6":
                    txtAdharNum.Focus();
                    break;
                case "8":
                    txtSetPassword1.Focus();
                    break;
                default:
                    break;
            }
        }

        /// <summary>
        /// Verify mobile OTP
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        protected void btnVerifyOTP_Click(object sender, EventArgs e)
        {
            string strOtp = txtOTP.Value;
            Core.BussinessModel.UserDetails.UserDetailsFromApi userResult = null; 
            if(!String.IsNullOrEmpty(txtOTP.Value))
                userResult = Core.Services.APIService.VerifyOtp(txtSignupMobileNumber.Value, strOtp);

            if (userResult != null && userResult.Data != null && userResult.Data.IsVerified)
            {
                Activitylog.SignuplogAsync(0, 1, 1, txtSignupMobileNumber.Value,"1");
                CurMobileNumberVerified = txtSignupMobileNumber.Value;
                CurViewType = "2";
                txtGSTNumber.Value = "";
                gstOTP.Value = "";
            }
            else
            {
                ltrResult.Text = "Invalid OTP or verification failed!!";
                txtOTP.Focus();
                Type cstype = this.GetType();
                String csname1 = "PopupScript";
                ClientScriptManager cs = Page.ClientScript;
                StringBuilder cstext1 = new StringBuilder();
                cstext1.Append("<script type=text/javascript> $('.otp_toggle').show(); $('#txtPhone').prop('disabled', true); $('.otpsent').show(); </");
                cstext1.Append("script>");

                cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());

            }
        }

        /// <summary>
        /// GSTN/VAT OTP Verification
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        protected void lbtnGSTSendOtp_Click(object sender, EventArgs e)
        {
            string vattype = ConfigurationManager.AppSettings.Get("VATType");
            gstOTP.Value = "";
            // 1= VAT, 2 = GST, 10 = TRN
            if(vattype == "1")
            {
                Core.BussinessModel.VAT.VATData vatData = null;

                var vatResult = (new Service.Store.VATService()).ValidateVAT(txtGSTNumber.Value);
                if (vatResult != null)
                    vatData = vatResult.VatData;
                if (vatData == null || !vatResult.Success || vatData == null || String.IsNullOrEmpty(vatData.company_name))
                {
                    ltrResult.Text = (vatResult == null || vatResult.VatData == null || String.IsNullOrEmpty(vatResult.VatData.vat_number) ? $"Invalid {Service.Store.VATService.VATLabel} number or {Service.Store.VATService.VATLabel} master data access is not available" : vatResult.VatData.company_name);
                    return;
                }

                CurVATData = vatData;
                ltrVATResult.Text = $"<br/>Organization: {CurVATData.company_name}, <br/>Address: {CurVATData.company_address}";

                CurViewType = "3";
                btnSubmitGSTNumber.CssClass = "btn btn-primary btn-block btn-drk-green mx-w-140 ml-2 disabled";
                txtGSTNumber.Attributes.Add("class", String.Format("{0} disabled", txtGSTNumber.Attributes["class"]));
                CurTaxType = vatResult.VatType;

                // End of execution if VAT.
                return;
            }
            else if (vattype == "10")
            {
                Core.BussinessModel.VAT.TRNData trnData = null;

                var vatResult = Service.Store.VATService.ValidateTRN(txtGSTNumber.Value);
                if (vatResult != null)
                    trnData = vatResult.TRNData;
                if (trnData == null || !vatResult.Success || trnData == null || String.IsNullOrEmpty(trnData.legal_name))
                {
                    ltrResult.Text = (vatResult == null || vatResult.TRNData == null || String.IsNullOrEmpty(vatResult.TRNData.trn_number) ? $"Invalid TRN or TRN master data access is not available" : vatResult.TRNData.legal_name);
                    return;
                }

                CurTRNData = trnData;
                ltrVATResult.Text = $"<br/>Organization: {trnData.legal_name}"; //, <br/>Address: {CurVATData.company_address}";

                CurViewType = "3";
                btnSubmitGSTNumber.CssClass = "btn btn-primary btn-block btn-drk-green mx-w-140 ml-2 disabled";
                txtGSTNumber.Attributes.Add("class", String.Format("{0} disabled", txtGSTNumber.Attributes["class"]));
                CurTaxType = vatResult.VatType;

                // End of execution if TRN.
                return;
            }

            // Get GST validated.
            Core.BussinessModel.GST.GSTData gstData = null;
            var gstinResult = (new Service.Store.VATService()).ValidateGST(txtGSTNumber.Value);
            if (gstinResult != null)
                gstData = gstinResult.GstData;
            if (gstinResult == null || !gstinResult.Success || gstData == null || gstData.result == null || gstData.result.result== null || gstData.result.result.gstnDetailed == null)
            {
                ltrResult.Text = (gstinResult == null || String.IsNullOrEmpty(gstinResult.Description)? $"Invalid {Service.Store.VATService.VATLabel} number or {Service.Store.VATService.VATLabel} master data access is not available" : gstinResult.Description);
                return;
            }
            else if (gstData.result.result.gstnDetailed.principalPlaceAddress == null || String.IsNullOrEmpty(gstData.result.result.gstnDetailed.principalPlaceAddress.mobile))
            {
                ltrResult.Text = $"No verification data is available with the {Service.Store.VATService.VATLabel} master data. Please update your {Service.Store.VATService.VATLabel} master data with valid contact number to get verified, or try with a different {Service.Store.VATService.VATLabel} number. Please contact technical support for more details.";
                return;

            }

            CurGSTData = gstData;
            // Get the GSTN registered mobile number from API result
            String strMobile = (gstData.result.result.gstnDetailed.principalPlaceAddress != null && gstData.result.result.gstnDetailed.additionalPlaceAddress.Length > 0
                && !String.IsNullOrEmpty(gstData.result.result.gstnDetailed.additionalPlaceAddress[gstData.result.result.gstnDetailed.additionalPlaceAddress.Length-1].mobile)?
                gstData.result.result.gstnDetailed.additionalPlaceAddress[gstData.result.result.gstnDetailed.additionalPlaceAddress.Length - 1].mobile 
                : gstData.result.result.gstnDetailed.principalPlaceAddress.mobile);
            // Get the GSTN registered email id from API result
            string gstEmail = (gstData.result.result.gstnDetailed.principalPlaceAddress != null && gstData.result.result.gstnDetailed.additionalPlaceAddress.Length > 0
                && !String.IsNullOrEmpty(gstData.result.result.gstnDetailed.additionalPlaceAddress[gstData.result.result.gstnDetailed.additionalPlaceAddress.Length - 1].emailId) ?
                gstData.result.result.gstnDetailed.additionalPlaceAddress[gstData.result.result.gstnDetailed.additionalPlaceAddress.Length - 1].emailId
                : gstData.result.result.gstnDetailed.principalPlaceAddress.emailId);

            // Send OTP to the GSTN registered number
            Core.Services.APIService.GetOtp(strMobile, gst: txtGSTNumber.Value, templateid: 20);

            var tblOtp = DataServiceMySql.GetDataTable($"SELECT * FROM retaline_customer_signup_verifiLog WHERE veri_mobile = {strMobile} order by veri_id desc LIMIT 1", UserService.GetAPIConnectionString());
            if(tblOtp != null && tblOtp.Rows.Count > 0)
            {
                String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                List<KeyValuePair<string, string>> replacements = new List<KeyValuePair<string, string>>();
                replacements.Add(new KeyValuePair<string, string>("[OTP CONTENT]", tblOtp.Rows[0]["veri_sms_code"].ToString()));
                replacements.Add(new KeyValuePair<string, string>("[URLPART]", strUrl.TrimEnd(new char[] { '/', '\\' })));
                string strBody = EmailService.CreateEmailbody(EmailType.GSTVerification, replacements);

                // Send OTP to Email also
                Core.Services.APIService.SendEmail(gstEmail, $"{Service.Store.VATService.VATLabel} Verification OTP", strBody, "", true);
            }

            CurViewType = "3";
            btnSubmitGSTNumber.CssClass = "btn btn-primary btn-block btn-drk-green mx-w-140 ml-2 disabled";
            txtGSTNumber.Attributes.Add("class", String.Format("{0} disabled", txtGSTNumber.Attributes["class"]));
            CurTaxType = gstinResult.VatType;

            //string input = gstEmail;
            string maskEmailPattern = @"(?<=[\w]{1})[\w-\._\+%]*(?=[\w]{1}@)";
            string maskedEmail = Regex.Replace(gstEmail, maskEmailPattern, m => new string('*', m.Length));

            // Show masked email and phone number from GSTN result.
            ltrGSTMaskedMobile.Text = String.Format("{0}XXXXXX{1}", strMobile.Substring(0, 2), strMobile.Substring(8));
            ltrGSTMaskedEmail.Text = maskedEmail; //String.Format("{0}XX..{1}.{2}XX", gstEmail.Substring(0, 2), gstEmail.Substring(gstEmail.LastIndexOf('@')-1, 3), gstEmail.Substring(gstEmail.LastIndexOf('.') + 1), 1);
        }

        /// <summary>
        /// No GST clicked.
        /// Show the view for no GST view according to the disable store flag on no gst.
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        protected void lbtnNoGST_Click(object sender, EventArgs e)
        {
            CurGSTStatus = Services.GSTStatus.NoGST;
            Common.ShowCustomAlert(this.Page, "Notification", $"If you proceed without {Service.Store.VATService.VATLabel}, you can only sell {Service.Store.VATService.VATLabel} exempted products.", false);

            if (ConfigurationManager.AppSettings.Get("StoreDisableNoneVAT") == "1")
            {
                CurViewType = "6";
                btnAdharSubmit.Visible = true;
                txtAdharNum.Text = "";
                txtPAN.Value = "";
                return;
            }

            CurGSTData = null;
            CurViewType = "4";
            ltrGstOrganization.Text = "New Merchant";
            ltrGstAddress.Text = $"{Service.Store.VATService.VATLabel} linkage was skipped. {Service.Store.VATService.VATLabel} Priority will be provided to registered merchants When listing on Grozeo.";
        }

        /// <summary>
        /// OTP Verify on the number send to the GSTN registered mobile number
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        protected void btnGSTOTPVerify_Click(object sender, EventArgs e)
        {
            string strOtp = gstOTP.Value;
            Core.BussinessModel.GST.GSTData gstData = CurGSTData;

            if (sender != btnSkipVerificationConfim)
            {
                var gstResult = Core.Services.APIService.VerifyOtp(gstData.result.result.gstnDetailed.principalPlaceAddress.mobile, strOtp);

                if (gstResult == null || gstResult.Data == null || !gstResult.Data.IsVerified)
                {
                    ltrResult.Text = "Invalid OTP or verification failed!!";
                    return;
                }
            }

            CurGSTStatus = Services.GSTStatus.Verified;
            //ltrGSTGSTIN.Text = (CurVATType == 2? gstData.result.essentials.gstin : CurVATData.vat_number);
            ltrGstOrganization.Text = (CurVATType == 2 ? gstData.result.result.gstnDetailed.legalNameOfBusiness : CurVATData.company_name);

            if (CurVATType == 2)
            {
                ltrGSTMaskedMobile.Text = (gstData.result.result.gstnDetailed.principalPlaceAddress != null && gstData.result.result.gstnDetailed.additionalPlaceAddress.Length > 0
                && !String.IsNullOrEmpty(gstData.result.result.gstnDetailed.additionalPlaceAddress[gstData.result.result.gstnDetailed.additionalPlaceAddress.Length - 1].mobile) ?
                gstData.result.result.gstnDetailed.additionalPlaceAddress[gstData.result.result.gstnDetailed.additionalPlaceAddress.Length - 1].mobile
                : gstData.result.result.gstnDetailed.principalPlaceAddress.mobile);

                ltrGSTMaskedEmail.Text = (gstData.result.result.gstnDetailed.principalPlaceAddress != null && gstData.result.result.gstnDetailed.additionalPlaceAddress.Length > 0
                && !String.IsNullOrEmpty(gstData.result.result.gstnDetailed.additionalPlaceAddress[gstData.result.result.gstnDetailed.additionalPlaceAddress.Length - 1].emailId) ?
                gstData.result.result.gstnDetailed.additionalPlaceAddress[gstData.result.result.gstnDetailed.additionalPlaceAddress.Length - 1].emailId
                : gstData.result.result.gstnDetailed.principalPlaceAddress.emailId);

                // Show GSTN received data to the user for location and address.
                // **** Removed in the new Design ***
                //try
                //{
                //    ltrGstDistrict.Text = gstData.result.result.gstnDetailed.additionalPlaceAddress[gstData.result.result.gstnDetailed.additionalPlaceAddress.Length - 1].splitAddress.district[0];
                //    if (String.IsNullOrEmpty(ltrGstDistrict.Text))
                //        ltrGstDistrict.Text = ltrGstDistrict.Text = gstData.result.result.gstnDetailed.principalPlaceAddress.splitAddress.district[0];
                //}
                //catch { try { ltrGstDistrict.Text = gstData.result.result.gstnDetailed.principalPlaceAddress.splitAddress.district[0]; } catch { } }
                //try
                //{
                //    ltrGstState.Text = gstData.result.result.gstnDetailed.additionalPlaceAddress[gstData.result.result.gstnDetailed.additionalPlaceAddress.Length - 1].splitAddress.state[0][0];
                //    if (String.IsNullOrEmpty(ltrGstState.Text))
                //        ltrGstState.Text = gstData.result.result.gstnDetailed.principalPlaceAddress.splitAddress.state[0][0];
                //}
                //catch { try { ltrGstState.Text = gstData.result.result.gstnDetailed.principalPlaceAddress.splitAddress.state[0][0]; } catch { } }

                //try
                //{
                //    ltrGstPin.Text = gstData.result.result.gstnDetailed.additionalPlaceAddress[gstData.result.result.gstnDetailed.additionalPlaceAddress.Length - 1].splitAddress.pincode;
                //    if (String.IsNullOrEmpty(ltrGstPin.Text))
                //        ltrGstPin.Text = gstData.result.result.gstnDetailed.principalPlaceAddress.splitAddress.pincode;
                //}
                //catch { try { ltrGstPin.Text = gstData.result.result.gstnDetailed.principalPlaceAddress.splitAddress.pincode; } catch { } }

                //ltrGstCorpType.Text = gstData.result.result.gstnDetailed.constitutionOfBusiness; //.taxPayerType;
                                                                                                 //ltrGstBusinessType.Text = String.Join(",", gstData.result.result.gstnDetailed.natureOfBusinessActivities);
                ltrGstAddress.Text = (gstData.result.result.gstnDetailed.principalPlaceAddress != null && gstData.result.result.gstnDetailed.additionalPlaceAddress.Length > 0
                && !String.IsNullOrEmpty(gstData.result.result.gstnDetailed.additionalPlaceAddress[gstData.result.result.gstnDetailed.additionalPlaceAddress.Length - 1].address) ?
                gstData.result.result.gstnDetailed.additionalPlaceAddress[gstData.result.result.gstnDetailed.additionalPlaceAddress.Length - 1].address
                : gstData.result.result.gstnDetailed.principalPlaceAddress.address);
            }
            else
            {
                ltrGstAddress.Text = (CurVATData != null ? CurVATData.company_address : "");
            }

            CurViewType = "4";
        }

        /// <summary>
        /// Final Signup Form Submit.
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        protected void btnCompleteGSTSignup_Click(object sender, EventArgs e)
        {
            // Validate email
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("email", txtLoginEmail.Value));
            int count = (int)DataService.ExecuteScalar("select count(*) from [User] where Email like @email", parmeters: prms);
            if (count > 0)
            {
                ltrResult.Text = "Email id is already in use. Please try with another email id. If you own this email id, please login with your account or use the reset password if you forgot it.";
                return;
            }

            // Store unique id.
            string guid = Guid.NewGuid().ToString();
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            string strActivationUrl = $"{strUrl}signup?verify={guid}";

            prms.Add(new KeyValuePair<string, object>("phone", CurMobileNumberVerified));
            prms.Add(new KeyValuePair<string, object>("VerifyKey", guid));
            prms.Add(new KeyValuePair<string, object>("Password", "NA"));
            prms.Add(new KeyValuePair<string, object>("FullName", txtContactPerson.Value));
            prms.Add(new KeyValuePair<string, object>("verifiedVAT", (CurGSTStatus == Services.GSTStatus.Verified ? 1 : 0)));
            string sqlInsert = "INSERT INTO [dbo].[User]([Email],[Mobile],[Password],[PasswordSalt],[PasswordType],[Status],[FullName], hasVerifiedMobile, StoreGroupId, hasVerifiedVAT, VerifyKey) VALUES(@email,@phone,@Password, null, 4, 1, @FullName, 1, -1, @verifiedVAT, @VerifyKey); select scope_identity()";
           // Create user with activation pending.
            var dtUserid = DataService.GetDataTable(sqlInsert, parmeters: prms);

            if(dtUserid != null && dtUserid.Rows.Count > 0 && CurGSTData != null)
            {
                try
                {
                    int userid = Convert.ToInt32(dtUserid.Rows[0][0]);
                    // Save GST for the new store.
                    var _gstdata = CurGSTData;
                    List<KeyValuePair<string, object>> gstPrms = new List<KeyValuePair<string, object>>();
                    gstPrms.Add(new KeyValuePair<string, object>("address", _gstdata.result.result.gstnDetailed.principalPlaceAddress.address));
                    gstPrms.Add(new KeyValuePair<string, object>("email", _gstdata.result.result.gstnDetailed.principalPlaceAddress.emailId));
                    gstPrms.Add(new KeyValuePair<string, object>("gstdata", System.Text.Json.JsonSerializer.Serialize(_gstdata)));
                    gstPrms.Add(new KeyValuePair<string, object>("gstin", _gstdata.result.essentials.gstin));
                    gstPrms.Add(new KeyValuePair<string, object>("isverified", (CurGSTStatus == Services.GSTStatus.Verified? 1 : 0)));
                    gstPrms.Add(new KeyValuePair<string, object>("mobile", _gstdata.result.result.gstnDetailed.principalPlaceAddress.mobile));
                    gstPrms.Add(new KeyValuePair<string, object>("organization", _gstdata.result.result.gstnDetailed.legalNameOfBusiness));
                    gstPrms.Add(new KeyValuePair<string, object>("userid", userid));
                    gstPrms.Add(new KeyValuePair<string, object>("CreatedBy", string.IsNullOrEmpty(this.CurrentUser.Email) ? this.CurrentUser.Phone : this.CurrentUser.Email));
                    string sqlInsertGST = "INSERT INTO GST(address, email, gstdata, gstin, isverified, mobile, organization, userid,Createdby) VALUES(@address, @email, @gstdata, @gstin, @isverified, @mobile, @organization, @userid,@CreatedBy)";                   
                    DataService.ExecuteSql(sqlInsertGST, parmeters: gstPrms);
                    // Activitylog
                    String strUrls = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                    string Source = strUrls;
                    int storegroupid = this.CurrentUser.APIStoreId; ;
                    string User = this.CurrentUser.Email;
                    string Address = (ConfigurationManager.AppSettings.Get("CountryCode") == "UK") ? CurVATData.company_address : _gstdata.result.result.gstnDetailed.principalPlaceAddress.address;
                    string Gstdata = System.Text.Json.JsonSerializer.Serialize(_gstdata);
                    string Gstin = (ConfigurationManager.AppSettings.Get("CountryCode") == "UK") ? CurVATData.vat_number : _gstdata.result.essentials.gstin;
                    string Organization = (ConfigurationManager.AppSettings.Get("CountryCode") == "UK") ? CurVATData.company_name : _gstdata.result.result.gstnDetailed.legalNameOfBusiness;
                    var items = new[]
                        {
                    new { Key = "Address", Value = Address },
                    new { Key = "Gst data", Value = Gstdata },
                    new { Key = "Gstin", Value = Gstin },
                    new { Key = "Organization", Value = Organization },
                    new { Key = "Tenantid", Value = storegroupid.ToString() },
                };
                    string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                    var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
                }
                catch(Exception ex) { this.LogError(ex.Message); }
            }
            int rnd = new Random().Next(1001, 9999);

            CurValidationKey = new KeyValuePair<int, string>(rnd, guid);

            prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("verify_key", guid));
            prms.Add(new KeyValuePair<string, object>("verify_code", ""));
            prms.Add(new KeyValuePair<string, object>("verify_type", 2));
            prms.Add(new KeyValuePair<string, object>("verify_status", "pending"));
            prms.Add(new KeyValuePair<string, object>("details", strActivationUrl));
            prms.Add(new KeyValuePair<string, object>("mobil", txtSignupMobileNumber.Value));
            prms.Add(new KeyValuePair<string, object>("email", txtLoginEmail.Value));
            string strBody = $"<p style='color: green'><strong>WELCOME TO GROZEO FAMILY.</strong></p>" +
                "<p><strong>YOU HAVE SUCCESSFULLY COMPLETED YOUR REGISTRATION.</strong></p><br/>" +
                $"<p>Verification Code: <b>{rnd}</b><br/><br/>"+
                "<p>Please click on the link or use the url provide below to activate your store. Grozeo help you phygitise your store and will support you compete with your fellow online merchants. We are just a call/ click away. Our AI enabled state-of-the-art support system will extend maximum support to you and your customers without bothering you. " +
                $"<br><br>{strActivationUrl}<br><br>Enjoy your new freedom to a skeumorphic retail ecosystem - Grozeo</p>";
            prms.Add(new KeyValuePair<string, object>("data", strBody));
            // Save log data
            DataService.ExecuteSql("INSERT INTO VerifyLog(verify_key, verify_code, verify_type, expire_time, verify_status, details, mobile, email, [data]) VALUES(@verify_key, @verify_code, @verify_type, DATEADD(HOUR, 1, GETUTCDATE()), @verify_status, @details, @mobil, @email, @data)", parmeters: prms);


            // Send activation email.
            APIService.SendEmail(txtLoginEmail.Value, "Welcome to Our Online Store! Merchant Registration Confirmation", strBody, txtContactPerson.Value, true);

            CurViewType = "5";
        }

        /// <summary>
        /// Reset account password from email generated by forgot password.
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        protected void btnSetPassword_Click(object sender, EventArgs e)
        {
            if (String.IsNullOrEmpty(VerifyKey))//(String.IsNullOrEmpty(Request.QueryString["verify"]))
            {
                ltrResult.Text = "Invalid key!!";
                CurViewType = "1";
                return;
            }

            if (String.IsNullOrEmpty(txtSetPassword1.Text))
            {
                ltrResult.Text = "Invalid password 1";
                return;
            }
            else if (String.IsNullOrEmpty(txtSetPassword2.Text))
            {
                ltrResult.Text = "Invalid Password 2";
                return;
            }
            else if(txtSetPassword1.Text != txtSetPassword2.Text)
            {
                ltrResult.Text = "Password 1 and Password 2 are not matching";
                return;
            }

            string verifyKey = VerifyKey;//  Request.QueryString["verify"];
            // Validate key
            string sql = $"select top 1 * from VerifyLog where verify_key = @key and expire_time >= getutcdate() order by id desc";
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("key", verifyKey));
            var tblUserLog = DataService.GetDataTable(sql, parmeters: prms);
            if(tblUserLog == null || tblUserLog.Rows.Count < 1)
            {
                pnlResetPswInvalidkey.Visible = true;
                pnlResetPswView.Visible = false;
                return;
            }

            string strmobile = tblUserLog.Rows[0]["mobile"].ToString();
            string strEmail = tblUserLog.Rows[0]["email"].ToString();
            sql = "select top 1 * from [User] where email = @email and mobile = @mobile";
            prms.Add(new KeyValuePair<string, object>("email", strEmail));
            prms.Add(new KeyValuePair<string, object>("mobile", strmobile));
            var tblUser = DataService.GetDataTable(sql, parmeters: prms);
            // Validate user.
            if (tblUser == null || tblUser.Rows.Count < 1)
            {
                pnlResetPswInvalidkey.Visible = true;
                pnlResetPswView.Visible = false;
                return;
            }
            // Generate new password
            string saltKey = EncryptionService.CreateSaltKey(5);
            string strEncPsw = EncryptionService.CreatePasswordHash(txtSetPassword1.Text, saltKey);

            // Set new password
            sql = "UPDATE [User] SET [Password] = @psw, [PasswordSalt] = @saltKey, [PasswordType] = 2, hasVerifiedEmail=1  WHERE id= @id; UPDATE VerifyLog SET expire_time=NULL, verify_status='verified' where verify_key = @key";
            prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("psw", strEncPsw));
            prms.Add(new KeyValuePair<string, object>("saltKey", saltKey));
            prms.Add(new KeyValuePair<string, object>("id", (int)tblUser.Rows[0]["id"]));
            prms.Add(new KeyValuePair<string, object>("key", verifyKey));
            DataService.ExecuteSql(sql, parmeters: prms);

            pnlResetPswSuccess.Visible = true;
            pnlResetPswView.Visible = false;

            // Authenticate user.
            User user = null;
            var loginResult = UserService.ValidateCustomer(tblUser.Rows[0]["email"].ToString() , txtSetPassword1.Text, out user);
            switch (loginResult)
            {
                case CustomerLoginResults.Successful:
                    {
                        FormsAuthenticationService.SignIn(user, true);
                        Service.UserService.CachedDefaultUser = user;
                        ltrlSetPswContinue.Text = "Please continue to next step for creating your store.";
                        hlSetPswNavigate.NavigateUrl = "/";
                        hlSetPswNavigate.Text = "Continue";

                        if(CurGSTStatus == Services.GSTStatus.VerificationSkipped && !String.IsNullOrEmpty(txtGSTNumber.Value))
                        {
                            try
                            {
                                string sqlInsertVerifyLog = "INSERT INTO VerifyLog(verify_key, verify_code, verify_type, verify_status, UserId) VALUES(@verify_key, @verify_code, @verify_type, @verify_status, @UserId);";
                                List<KeyValuePair<string, object>> prmsInsertVerifyLog = new List<KeyValuePair<string, object>>();
                                prmsInsertVerifyLog.Add(new KeyValuePair<string, object>("verify_key", txtGSTNumber.Value));
                                prmsInsertVerifyLog.Add(new KeyValuePair<string, object>("verify_code", -1));
                                prmsInsertVerifyLog.Add(new KeyValuePair<string, object>("verify_type", 3));
                                prmsInsertVerifyLog.Add(new KeyValuePair<string, object>("verify_status", $"{(ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT")} Verification Pending"));
                                prmsInsertVerifyLog.Add(new KeyValuePair<string, object>("UserId", user.Id));
                                DataService.ExecuteSql(sqlInsertVerifyLog, parmeters: prmsInsertVerifyLog);
                            }
                            catch(Exception ex) { this.LogError(ex.Message); }

                        }
                        Response.Redirect("/");
                        break;
                    }
                default:
                    ltrlSetPswContinue.Text = "Please login with your credentials.";
                    hlSetPswNavigate.NavigateUrl = "/login";
                    hlSetPswNavigate.Text = "Login";
                    break;
            }

        }

        /// <summary>
        /// Change GST click.
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        protected void lbtnChangeGST_Click(object sender, EventArgs e)
        {
            CurViewType = "2";
            txtGSTNumber.Value = "";

            btnSubmitGSTNumber.CssClass = "btn btn-primary btn-block btn-drk-green mx-w-140 ml-2";
            txtGSTNumber.Attributes.Add("class", String.Format("{0}", txtGSTNumber.Attributes["class"].Replace(" disabled", "")));


        }

        /// <summary>
        /// Skip GST Verification click
        /// Show alternate verification view or the create store form, based on the VAT type
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        protected void lbtnSkipGSTVerification_Click(object sender, EventArgs e)
        {
            CurGSTStatus = Services.GSTStatus.VerificationSkipped;
            CurViewType = "4";
            Core.BussinessModel.GST.GSTData gstData = CurGSTData;
            if (gstData == null)
            {
                ltrGstOrganization.Text = "New Merchant";
                ltrGstAddress.Text = $"{Service.Store.VATService.VATLabel} linkage was skipped. {Service.Store.VATService.VATLabel} Priority will be provided to registered merchants When listing on Grozeo.";
            }
            else
            {
                ltrGstOrganization.Text = gstData.result.result.gstnDetailed.legalNameOfBusiness;
                // Show GSTN location details received from API.
                // *** Removed in the new UI ***
                //try
                //{
                //    ltrGstDistrict.Text = gstData.result.result.gstnDetailed.additionalPlaceAddress[gstData.result.result.gstnDetailed.additionalPlaceAddress.Length - 1].splitAddress.district[0];
                //    if (String.IsNullOrEmpty(ltrGstDistrict.Text))
                //        ltrGstDistrict.Text = ltrGstDistrict.Text = gstData.result.result.gstnDetailed.principalPlaceAddress.splitAddress.district[0];
                //}
                //catch { try { ltrGstDistrict.Text = gstData.result.result.gstnDetailed.principalPlaceAddress.splitAddress.district[0]; } catch { } }
                //try
                //{
                //    ltrGstState.Text = gstData.result.result.gstnDetailed.additionalPlaceAddress[gstData.result.result.gstnDetailed.additionalPlaceAddress.Length - 1].splitAddress.state[0][0];
                //    if (String.IsNullOrEmpty(ltrGstState.Text))
                //        ltrGstState.Text = gstData.result.result.gstnDetailed.principalPlaceAddress.splitAddress.state[0][0];
                //}
                //catch { try { ltrGstState.Text = gstData.result.result.gstnDetailed.principalPlaceAddress.splitAddress.state[0][0]; } catch { } }
                //try
                //{
                //    ltrGstPin.Text = gstData.result.result.gstnDetailed.additionalPlaceAddress[gstData.result.result.gstnDetailed.additionalPlaceAddress.Length - 1].splitAddress.pincode;
                //    if (String.IsNullOrEmpty(ltrGstPin.Text))
                //        ltrGstPin.Text = gstData.result.result.gstnDetailed.principalPlaceAddress.splitAddress.pincode;
                //}
                //catch { try { ltrGstPin.Text = gstData.result.result.gstnDetailed.principalPlaceAddress.splitAddress.pincode; } catch { } }
                //ltrGstCorpType.Text = gstData.result.result.gstnDetailed.constitutionOfBusiness; //.taxPayerType;
                //ltrGstBusinessType.Text = String.Join(",", gstData.result.result.gstnDetailed.natureOfBusinessActivities);

                ltrGstAddress.Text = (gstData.result.result.gstnDetailed.principalPlaceAddress != null && gstData.result.result.gstnDetailed.additionalPlaceAddress.Length > 0
                && !String.IsNullOrEmpty(gstData.result.result.gstnDetailed.additionalPlaceAddress[gstData.result.result.gstnDetailed.additionalPlaceAddress.Length - 1].address) ?
                gstData.result.result.gstnDetailed.additionalPlaceAddress[gstData.result.result.gstnDetailed.additionalPlaceAddress.Length - 1].address
                : gstData.result.result.gstnDetailed.principalPlaceAddress.address);
            }

        }

        /// <summary>
        /// Continue with GST click, from the Skip GST alert.
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        protected void lbContinueWithGST_Click(object sender, EventArgs e)
        {
            CurViewType = "2";
            btnSubmitGSTNumber.CssClass = "btn btn-primary btn-block btn-drk-green mx-w-140 ml-2";
            txtGSTNumber.Attributes.Add("class", "form-control gstnumber");
            txtGSTNumber.Value = "";
            gstOTP.Value = "";
        }

        /// <summary>
        /// On State select box data bound.
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        protected void selState_DataBound(object sender, EventArgs e)
        {
            if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
                selState.Items.Insert(0, new ListItem("Select Country", ""));
            else if (ConfigurationManager.AppSettings.Get("CountryCode") == "AE")
                selState.Items.Insert(0, new ListItem("Select Emirate", ""));
            else
                selState.Items.Insert(0, new ListItem("Select State", ""));

            if (selState.Items.Count > 1)
            {
                string strKey = selState.Attributes["DefaultState"];
                if (!String.IsNullOrEmpty(strKey) && selState.Items.FindByText(strKey) != null)
                    selState.Text = (selState.Items.FindByText(strKey).Value);
            }
        }

        /// <summary>
        /// On district select box data bound
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        protected void selDistrict_DataBound(object sender, EventArgs e)
        {
            if (selDistrict.Items.Count > 0)
            {
                string strKey = selDistrict.Attributes["DefaultDistrict"];
                if (!String.IsNullOrEmpty(strKey) && selDistrict.Items.FindByText(strKey) != null)
                    selDistrict.Text = (selDistrict.Items.FindByText(strKey).Value);
            }

            if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
                selDistrict.Items.Insert(0, new ListItem("Select County", ""));
            else if (ConfigurationManager.AppSettings.Get("CountryCode") == "AE")
                selDistrict.Items.Insert(0, new ListItem("Select Area", ""));
            else
                selDistrict.Items.Insert(0, new ListItem("Select District", ""));

            if (selDistrict.Items.Count > 1 && !String.IsNullOrEmpty(hidDistrict.Value) && selDistrict.Text != hidDistrict.Value && selDistrict.Items.FindByText(hidDistrict.Value) != null)
                selDistrict.SelectedValue = selDistrict.Items.FindByText(hidDistrict.Value).Value; //selState.Items.FindByText(strState).Value;

        }

        /// <summary>
        /// On state select box selection changed.
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        protected void selState_SelectedIndexChanged(object sender, EventArgs e)
        {
            var _gstdata = CurGSTData;
            string GstState = "";
            if (_gstdata != null)
            {
                 GstState = _gstdata.result.result.gstnDetailed.principalPlaceAddress.splitAddress.state[0][0];
                string selectedState = selState.SelectedItem.Text.ToLower();
                string selectedGststateName = GstState.ToLower();
                if (_gstdata.result.result.gstnDetailed.taxPayerType != "REGULAR" && selectedState != selectedGststateName)
                {
                    Common.ShowCustomAlert(this.Page, "Failure", "Your Selected Store Location is Outside The State Associated With Your Enrollment Number", false);
                    selState.SelectedValue = "";
                    return;
                }

            }
           
            selDistrict.DataBind();
        }

        /// <summary>
        /// Create Store Form Submit
        /// The final create store action. Store data along with user will be created if the validations are succeeded.
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        protected void btnSubmitAccount_Click(object sender, EventArgs e)
        {
            // Verify location selection
            if (String.IsNullOrEmpty(hidLat.Value) || String.IsNullOrEmpty(hidLong.Value))
            {
                txtLocation.CssClass = txtLocation.CssClass + " error";
                ltrResult.Text = "Please select location in map. Click on the location input box to 'Load Map' and search your location.";
                return;
            }
            // Avoid blank store name
            if (String.IsNullOrEmpty(txtStoreName.Text))
            { 
                txtStoreName.CssClass = txtStoreName.CssClass + " error";
                ltrResult.Text = "Missing Store Name. Please enter your store location name.";
                return;
            }
            if (!chkAcceptTerms.Checked)
            {
                chkAcceptTerms.CssClass = txtStoreName.CssClass + " error";
                ltrResult.Text = "Please mark the checkbox to indicate your acceptance of the terms and conditions once you have verified.";
                return;

            }
            // Validate email id used to avoid duplication.
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("email", txtLoginEmail.Value));
            int count = (int)DataService.ExecuteScalar("select count(*) from [User] where Email like @email", parmeters: prms);
            if (count > 0)
            {
                txtLoginEmail.Attributes.Add("class", String.Format( "{0} error", txtLoginEmail.Attributes["class"]));
                ltrResult.Text = "Email id is already in use. Please try with another email id. If you own this email id, please login with your account or use the reset password if you forgot it.";
                return;
            }
            // This is not required in the new UI since the two business types merged into one. Still validate to ensure atleast one selection.
            if (String.IsNullOrEmpty(selBusinessTypes.Text))
            {
                selBusinessTypes.CssClass = selBusinessTypes.CssClass + " error";
                ltrResult.Text = "Missing primary business Type. Please select your primary business type.";
                return;
            }

            string strBusinessType = selBusinessTypes.SelectedItem.Text;
            int primaryBusinessType = -1;
            try { primaryBusinessType = Convert.ToInt32(selBusinessTypes.Text); }catch (Exception ex){ primaryBusinessType = -1; this.LogError(ex.Message); }
            if (primaryBusinessType <= 0)
            {
                selBusinessTypes.CssClass = selBusinessTypes.CssClass + " error";
                ltrResult.Text = "Please select business category.";
                return;
            }

            primaryBusinessType = -1;
            List<int> secondaryBTypeIds = new List<int>();
            try
            {
                foreach (ListItem item in lstBusinessTypes.Items)
                {
                    if (item.Selected)
                    {
                        int secBType = Convert.ToInt32(item.Value);
                        if (primaryBusinessType <= 0)
                        {
                            primaryBusinessType = secBType;
                            continue;
                        }

                        if (secBType == primaryBusinessType)
                            continue;

                        secondaryBTypeIds.Add(secBType);
                    }
                }
            }
            catch(Exception ex) { this.LogError(ex.Message); }
            if(primaryBusinessType <= 0 && secondaryBTypeIds.Count < 1)
            {
                lstBusinessTypes.CssClass = selBusinessTypes.CssClass + " error";
                ltrResult.Text = "Please select retail category.";
                return;
            }

            // Build branch short name using predefined code.
            List<string> strExcempt = new List<string>();
            var tblBrShort = DataServiceMySql.GetDataTable("SELECT DISTINCT branch_shortname FROM finascop_branch", UserService.GetAPIConnectionString());
            if (tblBrShort != null && tblBrShort.Rows.Count > 0)
            {
                strExcempt = tblBrShort.AsEnumerable().Select(item => string.Format("{0}", item["branch_shortname"])).ToList();
            }
            string strBrShort = Common.RandomString(4, strExcempt?.ToArray());
            if (String.IsNullOrEmpty(strBrShort))
            {
                ltrResult.Text = "Sorry, there is a technical error on store code creation. Please try again later or contact support for more details";
                return;
            }

            // The Guid used for identifying the store in finance postings as well.
            string guid = Guid.NewGuid().ToString();
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            string strActivationUrl = $"{strUrl}signup?verify={guid}";
            // Default password.
            string strPsw = Guid.NewGuid().ToString().Split('-').LastOrDefault();
            int rnd = new Random().Next(1001, 9999);

            // Create store user (new user account).
            DataTable dtUserid = new DataTable();
            try
            {
                prms.Add(new KeyValuePair<string, object>("phone", CurMobileNumberVerified));
                prms.Add(new KeyValuePair<string, object>("VerifyKey", guid));
                prms.Add(new KeyValuePair<string, object>("Password", strPsw));
                prms.Add(new KeyValuePair<string, object>("verifycode", rnd.ToString()));
                prms.Add(new KeyValuePair<string, object>("FullName", txtContactPerson.Value));
                prms.Add(new KeyValuePair<string, object>("verifiedVAT", (CurGSTStatus == Services.GSTStatus.Verified ? 1 : 0)));
                string sqlInsert = "INSERT INTO [dbo].[User]([Email],[Mobile],[Password],[PasswordSalt],[PasswordType],[Status],[FullName], hasVerifiedMobile, StoreGroupId, hasVerifiedVAT, VerifyKey) VALUES(@email,@phone,@Password, @verifycode, 4, 1, @FullName, 1, -1, @verifiedVAT, @VerifyKey); select scope_identity()";
                // Create user with activation pending.
                dtUserid = DataService.GetDataTable(sqlInsert, parmeters: prms);
                if (dtUserid == null || dtUserid.Rows.Count <= 0)
                {
                    ltrResult.Text = "User creation failure!! There is a technical error on user creation. Please try again later or contact support for more details";
                    btnSubmitAccount.Visible = false;
                    hlGoHome.Visible = true;
                    return;
                }
            }
            catch(Exception ex) {
                this.LogError(ex.Message);
                ltrResult.Text = "User creation failure!! There is a technical error on user creation. Please try again later with another number or contact support for more details"; //+ex.Message;
                btnSubmitAccount.Visible = false;
                hlGoHome.Visible = true;
                return;
            }

            User user = null;
            var loginResult = UserService.ValidateCustomer(txtLoginEmail.Value, strPsw, out user);
            if(user == null || user.Id <= 0)
            {
                ltrResult.Text = "There is a technical error on creating user account. Please trying again later or contact support.";
                return;
            }

            int gstid = -1; string gst = "";
            // Generate dynamic url for the new store.
            string strStoreDomain = CreateStoreDomain();
            if (String.IsNullOrEmpty(strStoreDomain))
                return;

            if (dtUserid != null && dtUserid.Rows.Count > 0 && ( (CurVATType == 2 && CurGSTData != null) || (CurVATType != 2 && CurVATData != null) ))
            {
                try
                {
                    int userid = Convert.ToInt32(dtUserid.Rows[0][0]);

                    var _gstdata = CurGSTData;
                    List<KeyValuePair<string, object>> gstPrms = new List<KeyValuePair<string, object>>();
                    if(CurVATType != 2)
                    {
                        gstPrms.Add(new KeyValuePair<string, object>("address", CurVATData.company_address));
                        gstPrms.Add(new KeyValuePair<string, object>("gstdata", System.Text.Json.JsonSerializer.Serialize(CurVATData)));
                        gstPrms.Add(new KeyValuePair<string, object>("gstin", CurVATData.vat_number));
                        gstPrms.Add(new KeyValuePair<string, object>("organization", CurVATData.company_name));
                        gstPrms.Add(new KeyValuePair<string, object>("email", ""));
                        gstPrms.Add(new KeyValuePair<string, object>("mobile", ""));
                        gst = CurVATData.vat_number;
                    }
                    else
                    {
                        gstPrms.Add(new KeyValuePair<string, object>("address", _gstdata.result.result.gstnDetailed.principalPlaceAddress.address));
                        gstPrms.Add(new KeyValuePair<string, object>("email", _gstdata.result.result.gstnDetailed.principalPlaceAddress.emailId));
                        gstPrms.Add(new KeyValuePair<string, object>("gstdata", System.Text.Json.JsonSerializer.Serialize(_gstdata)));
                        gstPrms.Add(new KeyValuePair<string, object>("gstin", _gstdata.result.essentials.gstin));
                        gstPrms.Add(new KeyValuePair<string, object>("mobile", _gstdata.result.result.gstnDetailed.principalPlaceAddress.mobile));
                        gstPrms.Add(new KeyValuePair<string, object>("organization", _gstdata.result.result.gstnDetailed.legalNameOfBusiness));
                        gst = _gstdata.result.essentials.gstin;
                    }

                    gstPrms.Add(new KeyValuePair<string, object>("isverified", (CurGSTStatus == Services.GSTStatus.Verified ? 1 : 0)));
                    gstPrms.Add(new KeyValuePair<string, object>("userid", userid));
                    gstPrms.Add(new KeyValuePair<string, object>("CreatedBy", string.IsNullOrEmpty(this.CurrentUser.Email) ? user.Phone : this.CurrentUser.Email));
                    string sqlInsertGST = "INSERT INTO GST(address, email, gstdata, gstin, isverified, mobile, organization, userid,Createdby) VALUES(@address, @email, @gstdata, @gstin, @isverified, @mobile, @organization, @userid,@CreatedBy); select scope_identity()";
                    DataTable dtGSTId = DataService.GetDataTable(sqlInsertGST, parmeters: gstPrms);
                    // Activitylog
                    String strUrls = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                    string Source = strUrls;
                    int storegroupid = this.CurrentUser.APIStoreId; ;
                    string User = this.CurrentUser.Email;
                    string Address = (ConfigurationManager.AppSettings.Get("CountryCode") == "UK") ? CurVATData.company_address:_gstdata.result.result.gstnDetailed.principalPlaceAddress.address;
                    string Gstdata = System.Text.Json.JsonSerializer.Serialize(CurGSTData);
                    string Gstin = (ConfigurationManager.AppSettings.Get("CountryCode") == "UK") ? CurVATData.vat_number:_gstdata.result.essentials.gstin;
                    string Organization = (ConfigurationManager.AppSettings.Get("CountryCode") == "UK") ? CurVATData.company_name:_gstdata.result.result.gstnDetailed.legalNameOfBusiness;
                    var items = new[]
                        {
                    new { Key = "Address", Value = Address },
                    new { Key = "Gst data", Value = Gstdata },
                    new { Key = "Gstin", Value = Gstin },
                    new { Key = "Organization", Value = Organization },
                    new { Key = "Tenantid", Value = storegroupid.ToString() },
                };
                    string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                    var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
                    if (dtGSTId != null && dtGSTId.Rows.Count > 0)
                        gstid = Convert.ToInt32(dtGSTId.Rows[0][0]);
                }
                catch(Exception ex) { this.LogError(ex.Message); }
            }

            CurValidationKey = new KeyValuePair<int, string>(rnd, guid);

            // Sing in user and create store.
            FormsAuthenticationService.SignIn(user, true);
            Service.UserService.CachedDefaultUser = user;

            if (!String.IsNullOrEmpty(strStoreDomain))
            {
                CreateStore(strBusinessType, primaryBusinessType, strBrShort, strStoreDomain, gstid, gst, user);
                try
                {
                    // CRM notification on new store.
                    string strLeadUrl = ConfigurationManager.AppSettings.Get("CrmNewStore");
                    if (!String.IsNullOrEmpty(strLeadUrl))
                    {
                        List<KeyValuePair<string, string>> data = new List<KeyValuePair<string, string>>();
                        data.Add(new KeyValuePair<string, string>("name", txtStoreName.Text));
                        data.Add(new KeyValuePair<string, string>("email", user.Email));
                        data.Add(new KeyValuePair<string, string>("phone", user.Phone));
                        data.Add(new KeyValuePair<string, string>("location", selDistrict.SelectedItem.Text));
                        data.Add(new KeyValuePair<string, string>("address", String.Format("{0}, {1}, {2}", (ConfigurationManager.AppSettings.Get("CountryCode") == "UK" ? txtAddr1UK.Text : txtAddr2.Text), selDistrict.SelectedItem.Text, selState.Text)));
                        data.Add(new KeyValuePair<string, string>("url", strStoreDomain));
                        APIService.SubmitForm(strLeadUrl, data);
                    }
                }
                catch(Exception ex) { this.LogError(ex.Message); }
            }

            // Notification.
            prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("verify_key", guid));
            prms.Add(new KeyValuePair<string, object>("verify_code", rnd));
            prms.Add(new KeyValuePair<string, object>("verify_type", 2));
            prms.Add(new KeyValuePair<string, object>("verify_status", "pending"));
            prms.Add(new KeyValuePair<string, object>("details", strActivationUrl));
            prms.Add(new KeyValuePair<string, object>("mobil", txtSignupMobileNumber.Value));
            prms.Add(new KeyValuePair<string, object>("email", txtLoginEmail.Value));
            prms.Add(new KeyValuePair<string, object>("data", ""));

            List<KeyValuePair<string, string>> replacements = new List<KeyValuePair<string, string>>();
            replacements.Add(new KeyValuePair<string, string>("[URLPART]", strUrl.TrimEnd(new char[] { '/', '\\' })));
            replacements.Add(new KeyValuePair<string, string>("[name]", txtStoreName.Text));
            replacements.Add(new KeyValuePair<string, string>("[storeurl]", strStoreDomain));
            replacements.Add(new KeyValuePair<string, string>("[generalurl]", strUrl));
            replacements.Add(new KeyValuePair<string, string>("[OTP CONTENT]", rnd.ToString()));
            string strBody = EmailService.CreateEmailbody(EmailType.StoreCreated, replacements);
            // Save log data
            DataService.ExecuteSql("INSERT INTO VerifyLog(verify_key, verify_code, verify_type, expire_time, verify_status, details, mobile, email, [data]) VALUES(@verify_key, @verify_code, @verify_type, DATEADD(HOUR, 1, GETUTCDATE()), @verify_status, @details, @mobil, @email, @data)", parmeters: prms);

            Activitylog.SignupUpdatelogAsync(txtSignupMobileNumber.Value);
            // Send welcome email.
            try { 
                bool hasSendNotification = Core.Services.APIService.SendEmail(txtLoginEmail.Value, "Welcome to Grozeo - Your Merchant Account is Now Active!", strBody, txtContactPerson.Value, true).Result; 
                if(!hasSendNotification)
                    this.LogError(string.Format( "Merchant onboard, welcome email sending failed. Store Url: {0}, email: {1}, user id: {2}" + strStoreDomain, user.Email, user.Id));
            } 
            catch(Exception ex) { this.LogError(ex.Message); }

            // Send activation code
            //replacements = new List<KeyValuePair<string, string>>();
            //replacements.Add(new KeyValuePair<string, string>("[URLPART]", strUrl.TrimEnd(new char[] { '/', '\\' })));
            //replacements.Add(new KeyValuePair<string, string>("[OTP CONTENT]", rnd.ToString()));
            //replacements.Add(new KeyValuePair<string, string>("[user fullName]", user.FullName));
            //strBody = EmailService.CreateEmailbody(EmailType.VerifyEmail, replacements);
            // Send activation email.
            // Core.Services.APIService.SendEmail(user.Email, "Your Authorization Code for Grozeo Merchant Account", strBody, user.FullName, true);


            Session["SHOWPUBLICNAVHELP"] = true;
            Session["ShowThankyouMessage"] = true;
            //Response.Redirect("/SelectProduct");
            Response.Redirect("/tenant");

        }

        /// <summary>
        /// Generate dynamic url for new store.
        /// </summary>
        /// <returns>Dynamic Url</returns>
        private string CreateStoreDomain()
        {
            if (String.IsNullOrWhiteSpace(txtStoreName.Text))
            {
                if(CurVATType == 2 && CurGSTData != null)
                    txtStoreName.Text = (String.IsNullOrEmpty(CurGSTData.result.result.gstnDetailed.tradeNameOfBusiness)
                            || CurGSTData.result.result.gstnDetailed.legalNameOfBusiness == "NA" ? CurGSTData.result.result.gstnDetailed.legalNameOfBusiness : CurGSTData.result.result.gstnDetailed.tradeNameOfBusiness);
                else if (CurVATType != 2 && CurVATData != null && !String.IsNullOrEmpty(CurVATData.company_name))
                    txtStoreName.Text = CurVATData.company_name;

            }

            string strStoreUrlTemplate = System.Configuration.ConfigurationManager.AppSettings.Get("newsitedomain");
            string strDomainPart = strStoreUrlTemplate.Replace("[title]", "");

            string strDomainTemplate = System.Configuration.ConfigurationManager.AppSettings.Get("newsitedomain");

            if (String.IsNullOrWhiteSpace(txtStoreName.Text))
                txtStoreName.Text = "My Store";

            string strDomainStoreName = txtStoreName.Text;//txtStoreName.Text.Replace(" ", "").Trim().ToLower();
            if (strDomainStoreName.Length > 17)
            {
                if (strDomainStoreName.Contains(" "))
                {
                    strDomainStoreName = "";
                    foreach (string strPart in txtStoreName.Text.Split(' '))
                    {
                        if (strDomainStoreName.Length > 16)
                            break;
                        strDomainStoreName += (String.IsNullOrEmpty(strPart.Trim()) ? "" : strPart.Trim());
                    }
                }
                else
                {
                    strDomainStoreName = strDomainStoreName.Substring(0, 16);
                }
            }
            if (String.IsNullOrEmpty(strDomainStoreName))
                strDomainStoreName = txtStoreName.Text.Replace(" ", "").ToLower(); // (String.IsNullOrEmpty(txtAddr1.Text) ? "mystore" : txtAddr1.Text.Replace(" ", "").ToLower());
            if (String.IsNullOrEmpty(strDomainStoreName))
                strDomainStoreName = "mystore";

            strDomainStoreName = strDomainStoreName.Replace(" ", "").Trim().ToLower();
            strDomainStoreName = Regex.Replace(strDomainStoreName, "[^a-zA-Z0-9]+", "", RegexOptions.Compiled);

            strDomainTemplate = strDomainTemplate.Replace("-sites.", "-{0}.").Replace("[title]", strDomainStoreName);
            List<string> strDomainExcempt = new List<string>();
            List<KeyValuePair<string, object>> hostsParams = new List<KeyValuePair<string, object>>();
            hostsParams.Add(new KeyValuePair<string, object>("host", strDomainStoreName));
            var tblHosts = DataService.GetDataTable("SELECT DISTINCT HostAddress from host WHERE HostAddress LIKE '%'+ @host + '%'", parmeters: hostsParams);
            if (tblHosts != null && tblHosts.Rows.Count > 0)
                strDomainExcempt = tblHosts.AsEnumerable().Select(item => string.Format("{0}", item["HostAddress"])).ToList();

            string strDomain = Common.GenStoreDomain(2, strDomainExcempt?.ToArray(), strDomainTemplate);
            if (String.IsNullOrEmpty(strDomain))
            {
                // Recursive call upto 3 time to find unique dymaic domain. Limit upto 3 in order to avoid infinite call.
                string strNewDomain = Common.GenStoreDomain(3, strDomainExcempt?.ToArray(), strDomainTemplate);
                if (String.IsNullOrEmpty(strNewDomain))
                    strNewDomain = Common.GenStoreDomain(4, strDomainExcempt?.ToArray(), strDomainTemplate);
                if (String.IsNullOrEmpty(strNewDomain))
                    strNewDomain = Common.GenStoreDomain(5, strDomainExcempt?.ToArray(), strDomainTemplate);

                if (String.IsNullOrEmpty(strNewDomain))
                {
                    ltrResult.Text = "Store name is already existing. Please try another name or contact technical support for more details. ";
                    return "";
                }
                strDomain = strNewDomain;
            }

            return strDomain;
        }

        /// <summary>
        /// Create Store in database
        /// </summary>
        /// <param name="strBusinessType"></param>
        /// <param name="primaryBusinessType"></param>
        /// <param name="strBrShort"></param>
        /// <param name="strDomain"></param>
        /// <param name="gstid"></param>
        /// <param name="gst"></param>
        /// <param name="user"></param>
        /// <returns></returns>
        private bool CreateStore(string strBusinessType, int primaryBusinessType, string strBrShort, string strDomain, int gstid, string gst, User user)
        {
            int storegroupId = -1, tenantId = -1;
            string strSecondaryBTypes = "";

            List<int> secondaryBTypeIds = new List<int>();
            try
            {
                int maxbusinessTypeRestricted = 0; try { maxbusinessTypeRestricted= Convert.ToInt32(ConfigurationManager.AppSettings.Get("MaxBusinessTypeRestricted")??"0"); } catch { maxbusinessTypeRestricted = 0; }
                int btIndex = 0;
                foreach (ListItem item in lstBusinessTypes.Items)
                    if (item.Selected)
                    {
                        if (maxbusinessTypeRestricted >0 && btIndex >= maxbusinessTypeRestricted)
                            break;

                        btIndex++;
                        int secBType = Convert.ToInt32(item.Value);
                        if(btIndex == 1)
                        {
                            strBusinessType = item.Text;
                            primaryBusinessType = secBType;
                            continue;
                        }
                        
                        strSecondaryBTypes += (String.IsNullOrWhiteSpace(strSecondaryBTypes) ? "" : ",") + item.Text;
                        secondaryBTypeIds.Add(secBType);
                        
                    }
            }
            catch(Exception ex) { this.LogError(ex.Message); }

            string guid = Guid.NewGuid().ToString();
            // Create Storegroup using API service.
            string referralcode = CurInvitationCode != null ? CurInvitationCode : txtInvitationCode.Text;
            storegroupId = Services.StoreService.CreateStoreGroup(txtStoreName.Text, primaryBusinessType, secondaryBTypeIds, guid, strDomain, referralcode);
            if (storegroupId < 1)
            {
                ltrResult.Text = "Store creation failed. Error Code: 1002 - There is a technical error happened in the back end system. Please try again later or contact support for more details.";
                return false;
                //throw new Exception("Error. Store creation failed at backoffice.");
            }
            if (!String.IsNullOrWhiteSpace(txtInvitationCode.Text))
                CurInvitationCode = txtInvitationCode.Text;

            if (!String.IsNullOrWhiteSpace(CurInvitationCode))
            {
                try {

                    List<KeyValuePair<string, object>> prospectparams = new List<KeyValuePair<string, object>>();
                    prospectparams.Add(new KeyValuePair<string, object>("storegroup", storegroupId));
                    prospectparams.Add(new KeyValuePair<string, object>("code", CurInvitationCode));
                    string prospectSql = $"UPDATE finascop_crm_prospect SET storeGroupId=@storegroup WHERE crpr_mode <> 5 and invitationCode=@code and ifnull(storeGroupId, 0) < 1";
                    DataServiceMySql.ExecuteScalar(prospectSql, UserService.GetAPIConnectionString(), prospectparams);

                } catch(Exception ex) { this.LogError(ex.Message); }
            }
            int apiid = 1; try { apiid = Convert.ToInt32(System.Configuration.ConfigurationManager.AppSettings.Get("APIID")); } catch(Exception ex) { apiid = 1; this.LogError(ex.Message); }
            bool canCheckout = true, onlinePayment = true, showPwa = true;
            try { canCheckout = (ConfigurationManager.AppSettings.Get("CanCheckout") == "1" ? true : false); } catch { canCheckout = false; }
            try { onlinePayment = (ConfigurationManager.AppSettings.Get("OnlinePaymentEnabled") == "1" ? true : false); } catch { onlinePayment = false; }
            try { showPwa = (ConfigurationManager.AppSettings.Get("ShowPWA") == "1" ? true : false); } catch { showPwa = false; }

            int merchantType = (CurTaxType == Service.Store.VATType.VAT || CurTaxType == Service.Store.VATType.GST || CurTaxType == Service.Store.VATType.TRN || CurTaxType == Service.Store.VATType.TestGST || CurTaxType == Service.Store.VATType.TestVAT ? 1 : 2);

            if (canCheckout && merchantType == 1 && CurGSTStatus == Services.GSTStatus.VerificationSkipped && System.Configuration.ConfigurationManager.AppSettings.Get("StoreDisableNoneVAT") == "1")
                canCheckout = false;

            string strMatomoId = "";
            try {
                strMatomoId = Services.StoreService.MatomoCreateSite(txtStoreName.Text, strDomain);
            } catch(Exception ex) { this.LogError(ex.Message); }

            List<KeyValuePair<String, Object>> parmeters = new List<KeyValuePair<string, object>>();
            parmeters.Add(new KeyValuePair<string, object>("Name", txtStoreName.Text));
            string strTheme = System.Configuration.ConfigurationManager.AppSettings.Get("ThemeDefault");

            parmeters.Add(new KeyValuePair<string, object>("Theme", strTheme));
            parmeters.Add(new KeyValuePair<string, object>("APIUrl", System.Configuration.ConfigurationManager.AppSettings.Get("api.url")));
            parmeters.Add(new KeyValuePair<string, object>("CanCheckout", canCheckout));
            parmeters.Add(new KeyValuePair<string, object>("OnlinePaymentEnabled", onlinePayment));
            parmeters.Add(new KeyValuePair<string, object>("StoreGroupId", storegroupId));
            parmeters.Add(new KeyValuePair<string, object>("Status", (merchantType != 1 || CurGSTStatus == Services.GSTStatus.Verified ? 1 : 2))); //(CurGSTStatus == Services.GSTStatus.Verified?1:2)));
            parmeters.Add(new KeyValuePair<string, object>("ShowPWA", showPwa));
            parmeters.Add(new KeyValuePair<string, object>("MinMargin", 5));
            parmeters.Add(new KeyValuePair<string, object>("Package", "basic"));
            parmeters.Add(new KeyValuePair<string, object>("BusinessType", strBusinessType));
            parmeters.Add(new KeyValuePair<string, object>("DBConnectionString", ""));
            parmeters.Add(new KeyValuePair<string, object>("SelectSql", ""));
            parmeters.Add(new KeyValuePair<string, object>("User", user.Email));
            parmeters.Add(new KeyValuePair<string, object>("domain", strDomain));
            parmeters.Add(new KeyValuePair<string, object>("SecondaryBusinessTypes", strSecondaryBTypes));
            parmeters.Add(new KeyValuePair<string, object>("DisplayName", txtStoreName.Text));
            parmeters.Add(new KeyValuePair<string, object>("ApiId", apiid));
            parmeters.Add(new KeyValuePair<string, object>("Stage", 1));
            parmeters.Add(new KeyValuePair<string, object>("tenantType", merchantType));
            parmeters.Add(new KeyValuePair<string, object>("addr", String.Format("{0}, {1}, {2}", (ConfigurationManager.AppSettings.Get("CountryCode") == "UK" ? txtAddr1UK.Text : txtAddr2.Text) , selDistrict.SelectedItem.Text, selState.Text)));
            parmeters.Add(new KeyValuePair<string, object>("phone", txtContactPhone.Text));
            parmeters.Add(new KeyValuePair<string, object>("email", txtLoginEmail.Value));
            parmeters.Add(new KeyValuePair<string, object>("contactname", txtContactPerson.Value));
            parmeters.Add(new KeyValuePair<string, object>("analyticsId", strMatomoId));
            if(CurTaxType == Service.Store.VATType.Adhar)
            {
                parmeters.Add(new KeyValuePair<string, object>("VerificationType", 3));
                if(CurAdharData != null && CurAdharData.id > 0)
                    parmeters.Add(new KeyValuePair<string, object>("AdharId", CurAdharData.id));

            }

            string sql = "CreateStore";
            int result = (int)DataService.ExecuteScalar(sql, parmeters: parmeters, isSP: true);
            if (result <= 0)
            {
                ltrResult.Text = "Error!! Store creation failed.";
                return false;
            }

            tenantId = result;

            // Call finascop ledger create
            try{ Finascop.Services.StoreService.StoreGroupCreate(txtStoreName.Text, user.Phone, storegroupId, guid); 
            }catch (Exception ex) {
                this.LogError(ex.Message);
                List<KeyValuePair<string, object>> logparams = new List<KeyValuePair<string, object>>();
                logparams.Add(new KeyValuePair<string, object>("entityId", tenantId));
                logparams.Add(new KeyValuePair<string, object>("type", "StoreGroup Creation - signup"));
                logparams.Add(new KeyValuePair<string, object>("status", 2));
                logparams.Add(new KeyValuePair<string, object>("comments", "StoreGroup Creation caused an Exception"));
                string insertQry = $"INSERT INTO finascop_log(entity_id, type, status, comments) " +
                                    $"VALUES(@entityId, @type, @status, @comments); select LAST_INSERT_ID()";
                var sqlResult = DataServiceMySql.ExecuteScalar(insertQry, UserService.GetAPIConnectionString(), logparams);
                int lastId = Convert.ToInt32(sqlResult);
            }

            // No branch should be created if type is PAN (affiliated merchant).
            // *** Skip this part since BR changed to allow store from None GST/VAT also with restriction mode.
            //if (CurTaxType == Service.Store.VATType.PAN || CurTaxType == Service.Store.VATType.Adhar)
            //    return true;

            int taxtype = (merchantType == 1 ? 1 : 0);
            // Restriction mode - Limit branch for intra state trading only? (0: no restriction, 1: Intra state only)
            int tradeRestrictionType = (ConfigurationManager.AppSettings.Get("VATType") == "2" && CurGSTStatus != Services.GSTStatus.Verified ? 1 : 0);
            try {
                if (ConfigurationManager.AppSettings.Get("VATType") == "2" && tradeRestrictionType == 0)
                {
                    taxtype = 0;
                    if (!(CurGSTData == null || CurGSTData.result == null || CurGSTData.result.result == null
                        || CurGSTData.result.result.gstnDetailed == null))
                    {
                        if (CurGSTData.result.result.gstnDetailed.taxPayerType != "REGULAR")
                            tradeRestrictionType = 1;

                        switch (CurGSTData.result.result.gstnDetailed.taxPayerType)
                        {
                            case "REGULAR":
                                taxtype = 1;
                                break;
                            case "COMPOSITION":
                                taxtype = 2;
                                break;
                            case "UNREGISTERED APPLICANT":
                                taxtype = 3;
                                break;                                
                        }
                    }
                }
            } catch(Exception ex) { this.LogError(ex.Message); }
            string branchName = txtStoreName.Text + '-' + selDistrict.SelectedItem.Text;
            var storebranch = Services.StoreService.CreateStore(branchName, strBrShort, storegroupId, (ConfigurationManager.AppSettings.Get("CountryCode") == "UK" ? txtAddr1UK.Text : txtAddr2.Text),
                txtStoreName.Text, Convert.ToInt32(selState.Text), Convert.ToInt32(selDistrict.Text), txtPinCode.Text, user.Email, user.Phone, hidLat.Value, 
                hidLong.Value, user.FullName, gst, tradeRestriction: tradeRestrictionType, taxType: taxtype);
            int branchId = storebranch;

            if (branchId <= 0)
            {
                ltrResult.Text = "Partial success!! Business information is created but store setting is pending.";
                return true;
            }
            try
            {
                List<KeyValuePair<string, object>> brParmeters = new List<KeyValuePair<string, object>>();
                brParmeters.Add(new KeyValuePair<string, object>("StoreId", tenantId));
                brParmeters.Add(new KeyValuePair<string, object>("Addr", (ConfigurationManager.AppSettings.Get("CountryCode") == "UK" ? txtAddr1UK.Text : txtAddr2.Text)));
                brParmeters.Add(new KeyValuePair<string, object>("District", selDistrict.SelectedItem.Text));
                brParmeters.Add(new KeyValuePair<string, object>("Lang", hidLong.Value));
                brParmeters.Add(new KeyValuePair<string, object>("Lat", hidLat.Value));
                brParmeters.Add(new KeyValuePair<string, object>("Location", txtStoreName.Text));
                brParmeters.Add(new KeyValuePair<string, object>("Pin", txtPinCode.Text));
                brParmeters.Add(new KeyValuePair<string, object>("State", selState.SelectedItem.Text));
                brParmeters.Add(new KeyValuePair<string, object>("MapLocation", txtLocation.Text));
                brParmeters.Add(new KeyValuePair<string, object>("APIBranchId", branchId));
                brParmeters.Add(new KeyValuePair<string, object>("IsDefaultBranch", true));
                brParmeters.Add(new KeyValuePair<string, object>("gstid", gstid));
                //brParmeters.Add(new KeyValuePair<string, object>("bankid", -1));
                DataService.ExecuteSP("CreateStoreBranch", parmeters: brParmeters);
            }
            catch (Exception ex2)
            {
                this.LogError(ex2.Message);
                ltrResult.Text = "Partial success!! Business information is created but store settings is pending.";
                return true;
            }
            Service.UserService.CachedDefaultUser = null;
            if (gstid > 0)
            {
                try
                {
                    string sqlGST = "UPDATE GST SET tenantid=@tenantid where id=@gstid and userid=@userid and tenantid is null";
                    List<KeyValuePair<string, object>> prmsgst = new List<KeyValuePair<string, object>>();
                    prmsgst.Add(new KeyValuePair<string, object>("gstid", gstid));
                    prmsgst.Add(new KeyValuePair<string, object>("userid", user.Id));
                    prmsgst.Add(new KeyValuePair<string, object>("tenantid", tenantId));
                    DataService.ExecuteSql(sqlGST, parmeters: prmsgst);
                }
                catch(Exception ex)
                {
                    gstid = -1;
                    this.LogError(ex.Message);
                }
            }
            else
            {
                ltrResult.Text = $"Partial success!! Business information is created but store is pending because of missing {Service.Store.VATService.VATLabel} data.";
            }

            return true;
        }

        /// <summary>
        /// PAN Verification (None GST account)
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        protected void btnPANSubmit_Click(object sender, EventArgs e)
        {
            Core.BussinessModel.PAN.PANInfo panData = null;

            var panResult = (new Service.Store.VATService()).ValidatePAN(txtPAN.Value);
            if (panResult != null)
                panData = panResult.PanData;
            if (panData == null || !panResult.Success || panData.result == null || panData.result.essentials == null || String.IsNullOrEmpty(panData.result.essentials.number))
            {
                ltrResult.Text = (panResult == null || panResult.PanData == null || String.IsNullOrEmpty(panResult.Description) ? "Invalid PAN number or PAN master data access is not available" : panResult.Description);
                return;
            }

            CurPANData = panData;
            ltrPANResult.Text = $"Name: {CurPANData.result.result.title} {CurPANData.result.result.name}";

            //CurViewType = "3";
            plcPANConfirm.Visible = true;

            btnPANSubmit.CssClass = "btn btn-primary btn-block btn-drk-green mx-w-140 ml-2 disabled";
            txtPAN.Attributes.Add("class", String.Format("{0} disabled", txtGSTNumber.Attributes["class"]));
            CurTaxType = panResult.VatType;

            return;
        }
        /// <summary>
        /// PAN completion.
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        protected void btnPANConfirm_Click(object sender, EventArgs e)
        {
            try { ltrGstOrganization.Text = CurPANData.result.result.name; } catch { }
            ltrGstAddress.Text = "Set up your affiliate store. Only promotional items will be available in your store and there is no order management since there is no own product for sale.";

            CurViewType = "4";

        }

        /// <summary>
        /// Skip PAN verification click.
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        protected void btnNoPANAffiliate_Click(object sender, EventArgs e)
        {
            try { ltrGstOrganization.Text = "Virtual Store"; } catch { }
            ltrGstAddress.Text = "Set up your virtual store. Only promotional items will be available in your store and there is no order management since there is no own product for sale.";

            CurViewType = "4";
            CurTaxType = Service.Store.VATType.NoVAT;
        }

        /// <summary>
        /// Invitation code request form, shows if signup restricted by invitation code only.
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        protected void lbtnRequestInvitationCode_Click(object sender, EventArgs e)
        {
            plcWithInvitationCode.Visible = false;
            plcWithoutInvitationCode.Visible = !plcWithInvitationCode.Visible;

        }
        /// <summary>
        /// Show invitation code input if choosen to insert.
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        protected void lbtnHaveInvitationCode_Click(object sender, EventArgs e)
        {
            plcWithInvitationCode.Visible = true;
            plcWithoutInvitationCode.Visible = !plcWithInvitationCode.Visible;

        }
        /// <summary>
        /// Adhar validation
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        protected void btnAdharSubmit_Click(object sender, EventArgs e)
        {

            var adharResult = (new Service.Store.VATService()).ValidateAdhar(txtAdharNum.Text);
            if (adharResult == null || adharResult.AdharData == null || !adharResult.Success || !adharResult.AdharData.valid_aadhaar || String.IsNullOrEmpty(adharResult.AdharData.client_id))
            {
                ltrResult.Text = adharResult?.Description??"Invalid Aadhaar number or Aadhaar data access is not available";
                return;
            }

            AdharClientID = adharResult.AdharData.client_id;
            plcAdharVerify.Visible = true;
            //btnAdharSubmit.CssClass = btnAdharSubmit.CssClass + " disabled";
            btnAdharSubmit.Visible = false;
            txtAdharNum.CssClass += " disabled";
        }
        /// <summary>
        /// Adhar Validate OTP.
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        protected void btnAdharOTPSubmit_Click(object sender, EventArgs e)
        {
            var adharResult = (new Service.Store.VATService()).VerifyAdhar(txtAdharNum.Text, AdharClientID, txtAdharOTP.Value);
            if (adharResult == null || !adharResult.Success || adharResult.AdharInfo == null || String.IsNullOrEmpty(adharResult.AdharInfo.aadhaar_number))
            {
                ltrResult.Text = "Verification failed. Invalid input.";
                return;
            }

            CurAdharData = adharResult.AdharInfo;
            try { ltrGstOrganization.Text = adharResult.AdharInfo.full_name; } catch { }
            CurTaxType = Service.Store.VATType.Adhar;

            CurViewType = "4";

        }

        protected void CustomValidatorChkAcceptTerms_ServerValidate(object source, ServerValidateEventArgs args)
        {
            args.IsValid = chkAcceptTerms.Checked;
        }
    }
}