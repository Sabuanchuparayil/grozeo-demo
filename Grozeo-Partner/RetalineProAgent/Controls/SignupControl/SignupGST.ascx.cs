using RetalineProAgent.Core.BussinessModel.Store;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.GST;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Linq;
using System.Text.RegularExpressions;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Controls.SignupControl
{
	public partial class SignupGST : Base.BasePartnerUserControl
	{

		public delegate void ParentViewTypeHandlerVAT(StoreSignupViewtype viewtype, string orgName = "", string orgAddress="");
		public event ParentViewTypeHandlerVAT ParentButtonBinding;

		public Literal ltrResult { get; set; }

		/// <summary>
		/// Display view (the type of view based on various user action)
		/// </summary>
		public GSTSignupViewtype CurViewType
		{
			get
			{
				if (ViewState["CURVIEWTYPE"] == null)
					return GSTSignupViewtype.GST;

				return (GSTSignupViewtype)ViewState["CURVIEWTYPE"];
			}
			set
			{
				ViewState["CURVIEWTYPE"] = value;
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
		/// Current GST status
		/// </summary>
		public Services.GSTStatus CurGSTStatus
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

		//public Core.BussinessModel.GST.GSTData CurGSTData
		//{
		//	get
		//	{
		//		return (Core.BussinessModel.GST.GSTData)ViewState["MYGSTDATA"];
		//	}
		//	set
		//	{
		//		ViewState["MYGSTDATA"] = value;
		//	}
		//}

		public GSTValidationResult CurGSTResult
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

		public Core.BussinessModel.VAT.VATData CurVATData
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
		public Core.BussinessModel.VAT.TRNData CurTRNData
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
		public Core.BussinessModel.PAN.PANInfo CurPANData
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
		public Core.BussinessModel.Adhar.AdharInfo CurAdharData
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


		protected void Page_Load(object sender, EventArgs e)
		{
			btnSkipGSTValidation.Text = "Skip " + Service.Store.VATService.VATLabel + " Validation";
			btnChangeGSTNumber.Text = "Change " + Service.Store.VATService.VATLabel + " Number";
			lbtnNoGST.Text = "I don't have " + Service.Store.VATService.VATLabel + " Number";
			lbAffiliateContinueWithVAT.Text = "Continue with " + Service.Store.VATService.VATLabel;



			plcSignupGSTShowVerification.Visible = CurVATType == 2;
			plcSignupGSTSkipVerification.Visible = CurVATType != 2;
			lbtnChangeGST.Text = "Change " + Service.Store.VATService.VATLabel;
			lbtnNoGST.Text = "I don't have " + Service.Store.VATService.VATLabel;
			lbContinueWithGST.Text = "Continue with " + Service.Store.VATService.VATLabel;
			txtGSTNumber.Attributes.Add("placeholder", $"Enter {Service.Store.VATService.VATLabel} #");

		}

		protected void Page_PreRender(object sender, EventArgs e)
		{
			plcSignupGST.Visible = (CurViewType == GSTSignupViewtype.GST || CurViewType == GSTSignupViewtype.GSTOTP); //(CurViewType == "2" || CurViewType == "3");
			plcSignupGSTOTP.Visible = CurViewType == GSTSignupViewtype.GSTOTP; //(CurViewType == "3");
			plcSignupNoGST.Visible = CurViewType == GSTSignupViewtype.NoGST; // (CurViewType == "6");

			pGSTINRequest.Visible = CurVATType == 2 && !plcSignupGSTOTP.Visible;
			pGSTINOTP.Visible = plcSignupGSTOTP.Visible;

		}


		/// <summary>
		/// Change GST click.
		/// </summary>
		/// <param name="sender"></param>
		/// <param name="e"></param>
		protected void lbtnChangeGST_Click(object sender, EventArgs e)
		{
			CurViewType = GSTSignupViewtype.GST; //"2";
			txtGSTNumber.Value = "";

			btnSubmitGSTNumber.CssClass = "btn btn-primary btn-block btn-drk-green mx-w-140 ml-2";
			txtGSTNumber.Attributes.Add("class", String.Format("{0}", txtGSTNumber.Attributes["class"].Replace(" disabled", "")));


		}


		/// <summary>
		/// Continue with GST click, from the Skip GST alert.
		/// </summary>
		/// <param name="sender"></param>
		/// <param name="e"></param>
		protected void lbContinueWithGST_Click(object sender, EventArgs e)
		{
			CurViewType = GSTSignupViewtype.GST; //"2";
			btnSubmitGSTNumber.CssClass = "btn btn-primary btn-block btn-drk-green mx-w-140 ml-2";
			txtGSTNumber.Attributes.Add("class", "form-control gstnumber");
			txtGSTNumber.Value = "";
			gstOTP.Value = "";
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
			if (vattype == "1")
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

				CurViewType = GSTSignupViewtype.GSTOTP; //"3";
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

				CurViewType = GSTSignupViewtype.GSTOTP; //"3";
				btnSubmitGSTNumber.CssClass = "btn btn-primary btn-block btn-drk-green mx-w-140 ml-2 disabled";
				txtGSTNumber.Attributes.Add("class", String.Format("{0} disabled", txtGSTNumber.Attributes["class"]));
				CurTaxType = vatResult.VatType;

				// End of execution if TRN.
				return;
			}

			// Get GST validated.
			GSTValidationResult gstResult = null;
			var gstinResult = (new Service.Store.VATService()).ValidateGST(txtGSTNumber.Value);
			if (gstinResult != null)
				gstResult = gstinResult.GstData;
			if (gstinResult == null || !gstinResult.Success || gstResult == null || string.IsNullOrEmpty(gstResult.GSTIN))
			{
				ltrResult.Text = (gstinResult == null || String.IsNullOrEmpty(gstinResult.Description) ? $"Invalid {Service.Store.VATService.VATLabel} number or {Service.Store.VATService.VATLabel} master data access is not available" : gstinResult.Description);
				return;
			}
			else if (String.IsNullOrEmpty(gstResult.Mobile))
			{
				ltrResult.Text = $"No verification data is available with the {Service.Store.VATService.VATLabel} master data. Please update your {Service.Store.VATService.VATLabel} master data with valid contact number to get verified, or try with a different {Service.Store.VATService.VATLabel} number. Please contact technical support for more details.";
				return;

			}

			CurGSTResult = gstResult;
			// Get the GSTN registered mobile number from API result
			String strMobile = gstResult.Mobile;
			// Get the GSTN registered email id from API result
			string gstEmail = gstResult.Email;

			// Send OTP to the GSTN registered number
			Core.Services.APIService.GetOtp(strMobile, gst: txtGSTNumber.Value, templateid: 20);

			var tblOtp = DataServiceMySql.GetDataTable($"SELECT * FROM retaline_customer_signup_verifiLog WHERE veri_mobile = {strMobile} order by veri_id desc LIMIT 1", UserService.GetAPIConnectionString());
			if (tblOtp != null && tblOtp.Rows.Count > 0)
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

			CurViewType = GSTSignupViewtype.GSTOTP; //"3";
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
		/// OTP Verify on the number send to the GSTN registered mobile number
		/// </summary>
		/// <param name="sender"></param>
		/// <param name="e"></param>
		protected void btnGSTOTPVerify_Click(object sender, EventArgs e)
		{
			string strOtp = gstOTP.Value;
			GSTValidationResult gstResult = CurGSTResult;

			if (sender != btnSkipVerificationConfim)
			{
				var gstVResult = Core.Services.APIService.VerifyOtp(gstResult.Mobile, strOtp);

				if (gstVResult == null || gstVResult.Data == null || !gstVResult.Data.IsVerified)
				{
					ltrResult.Text = "Invalid OTP or verification failed!!";
					return;
				}
			}

			CurGSTStatus = Services.GSTStatus.Verified;
			string orgName = (CurVATType == 2 && gstResult != null) ? gstResult.TradeName : (CurVATData != null ? CurVATData.company_name : CurTRNData.legal_name);
			string orgAddress = "";
			if (CurVATType == 2)
			{
				ltrGSTMaskedMobile.Text = gstResult.Mobile;
				ltrGSTMaskedEmail.Text = gstResult.Email;
                orgAddress = gstResult.Address;
			}
			else
			{
				orgAddress = (CurVATData != null ? CurVATData.company_address : "");
			}

			//CurViewType = "4";
			ParentButtonBinding(StoreSignupViewtype.StoreSignup, orgName, orgAddress);
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
				CurViewType = GSTSignupViewtype.NoGST; //"6";
				btnAdharSubmit.Visible = true;
				txtAdharNum.Text = "";
				txtPAN.Value = "";
				return;
			}

			CurGSTResult = null;
			//CurViewType = "4";

			//ltrGstOrganization.Text = "New Merchant";
			string strOrgName = "New Merchant";
			//ltrGstAddress.Text = $"{Service.Store.VATService.VATLabel} linkage was skipped. {Service.Store.VATService.VATLabel} Priority will be provided to registered merchants When listing on Grozeo.";
			string orgAddress = $"{Service.Store.VATService.VATLabel} linkage was skipped. {Service.Store.VATService.VATLabel} Priority will be provided to registered merchants When listing on Grozeo.";
			ParentButtonBinding(StoreSignupViewtype.StoreSignup, strOrgName, orgAddress);
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
				ltrResult.Text = adharResult?.Description ?? "Invalid Aadhaar number or Aadhaar data access is not available";
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
			try 
			{ 
				//ltrGstOrganization.Text = adharResult.AdharInfo.full_name;
				ParentButtonBinding(StoreSignupViewtype.StoreSignup, adharResult.AdharInfo.full_name);
			} catch { }
			CurTaxType = Service.Store.VATType.Adhar;

			//CurViewType = "4";

		}
		/// <summary>
		/// Skip PAN verification click.
		/// </summary>
		/// <param name="sender"></param>
		/// <param name="e"></param>
		protected void btnNoPANAffiliate_Click(object sender, EventArgs e)
		{
			//ltrGstOrganization.Text = "Virtual Store";
			string strOrgName = "Virtual Store";
			string strOrgAddr = "Set up your virtual store. Only promotional items will be available in your store and there is no order management since there is no own product for sale.";
			//ltrGstAddress.Text = "Set up your virtual store. Only promotional items will be available in your store and there is no order management since there is no own product for sale.";
			ParentButtonBinding(StoreSignupViewtype.StoreSignup, strOrgName, strOrgAddr);

			//CurViewType = "4";
			CurTaxType = Service.Store.VATType.NoVAT;
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
			string strOrgName = "", strOrgAddress = "";
			try { 
				//ltrGstOrganization.Text = CurPANData.result.result.name; 
				strOrgName = CurPANData.result.result.name; 
			} catch { }
			//ltrGstAddress.Text = "Set up your affiliate store. Only promotional items will be available in your store and there is no order management since there is no own product for sale.";
			strOrgAddress = "Set up your affiliate store. Only promotional items will be available in your store and there is no order management since there is no own product for sale.";
			ParentButtonBinding(StoreSignupViewtype.StoreSignup, strOrgName, strOrgAddress);

			//CurViewType = "4";

		}

		protected void BackToLogin_Click(object sender, EventArgs e)
		{
			ParentButtonBinding(StoreSignupViewtype.LoginWithPhone);
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
			//CurViewType = "4";
			string orgName = "", orgAddr = "";
			GSTValidationResult gstResult = CurGSTResult;
			if (gstResult == null)
			{
				orgName = "New Merchant";
				orgAddr = $"{Service.Store.VATService.VATLabel} linkage was skipped. {Service.Store.VATService.VATLabel} Priority will be provided to registered merchants When listing on Grozeo.";
			}
			else
			{
				orgName = gstResult.TradeName;
				orgAddr = gstResult.Address;
			}
			ParentButtonBinding(StoreSignupViewtype.StoreSignup, orgName, orgAddr);
		}

	}

	public enum GSTSignupViewtype
	{
		GST = 1,
		GSTOTP = 2,
		NoGST = 3
	
	}
}