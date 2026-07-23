using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Text.RegularExpressions;
using RetalineProAgent.Core.BussinessModel;
using RetalineProAgent.UI.Login;
using RetalineProAgent.Core.Services.GST;

namespace RetalineProAgent.Controls.SignupControl
{
	public partial class SignupCreateStore : Base.BasePartnerUserControl
	{
		public LoginType CurrentSignupType{get; set;}

		//public delegate void ParentViewTypeHandler(int type);
		//public event ParentViewTypeHandler ParentButtonBinding;

		//public delegate Core.BussinessModel.GST.GSTData ParentGetGSTHandler();
		//public event ParentGetGSTHandler ParentGetGSTBinding;

		public GSTValidationResult CurGSTResult { get; set; }

		//public delegate Core.BussinessModel.VAT.VATData ParentGetVATHandler();
		//public event ParentGetVATHandler ParentGetVATBinding;
		public Core.BussinessModel.VAT.VATData CurVATData
		{
			get;
			set;
		}
        //public delegate Core.BussinessModel.Adhar.AdharInfo ParentGetAADHARHandler();
        //public event ParentGetAADHARHandler ParentGetAADHARBinding;
        public Core.BussinessModel.VAT.TRNData CurTRNData
        {
            get;
            set;
        }
        //public delegate Core.BussinessModel.Adhar.AdharInfo ParentGetAADHARHandler();
        //public event ParentGetAADHARHandler ParentGetAADHARBinding;
        public Core.BussinessModel.Adhar.AdharInfo CurAdharData
		{
			get;
			set;
		}

		//public delegate int ParentGetCurVATTypeHandler();
		//public event ParentGetCurVATTypeHandler ParentGetCurVATTypeBinding;
		public int CurVATType
		{
			get;
			set;
		}

		//public delegate string ParentGetCurMobileHandler();
		//public event ParentGetCurMobileHandler ParentGetCurMobileBinding;
		/// <summary>
		/// The signup mobile number that was verified with OTP, stored in Viewstate.
		/// </summary>
		public string CurMobileNumberVerified
		{
			get;
			set;
		}

		//public delegate string ParentGetUserMobileHandler();
		//public event ParentGetUserMobileHandler ParentGetUserMobileBinding;
		public string UserMobileNumber
		{
			get;
			set;
		}
		//public delegate string ParentGetUserEmailHandler();
		//public event ParentGetUserEmailHandler ParentGetUserEmailBinding;
		public string UserEmail
		{
			get;
			set;
		}

		//public delegate string ParentGetCurInvitationCodeHandler();
		//public event ParentGetCurInvitationCodeHandler ParentGetCurInvitationCodeBinding;
		/// <summary>
		/// Current invitation code if used.
		/// </summary>
		public string CurInvitationCode
		{
			get;
			set;
		}

		/// <summary>
		/// GST/VAT
		/// </summary>
		//public delegate Service.Store.VATType ParentGetCurTaxTypeHandler();
		//public event ParentGetCurTaxTypeHandler ParentGetCurTaxTypeBinding;
		public Service.Store.VATType CurTaxType
		{
			get;
			set;
		}

		//public delegate Services.GSTStatus ParentGetCurGSTStatusHandler();
		//public event ParentGetCurGSTStatusHandler ParentGetCurGSTStatusBinding;
		public Services.GSTStatus CurGSTStatus
		{
			get;
			set;
		}


		public Literal ltrResult {  get; set; }

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

		public string OrganizationName
		{
			get { return ltrGstOrganization.Text; }
			set { ltrGstOrganization.Text = value; }
		}
		public string OrganizationAddress
		{
			get { return ltrGstAddress.Text; }
			set { ltrGstAddress.Text = value; }
		}

		protected void Page_Load(object sender, EventArgs e)
		{
			txtLoginEmail.Text = UserEmail;

			string countryCode = ConfigurationManager.AppSettings.Get("CountryCode");
			string maxLength = countryCode == "IN" ? "10" : countryCode == "UK" ? "13" : countryCode == "AE" ? "9" : "12";
			txtContactPhone.Attributes["maxlength"] = maxLength;
            rqdpostcod.Enabled = ConfigurationManager.AppSettings["CountryCode"] != "AE";
            if (ConfigurationManager.AppSettings.Get("IsPinNumeric") == "1")
				txtPinCode.TextMode = TextBoxMode.Number;
			else
				txtPinCode.TextMode = TextBoxMode.SingleLine;

			ctrlAddressMap1.ParentLocationClientId = hidMapAddr.ClientID; // txtLocation.ClientID;
			ctrlAddressMap1.ParentLatClientId = hidLat.ClientID;
			ctrlAddressMap1.ParentLongClientId = hidLong.ClientID;
			ctrlAddressMap1.ParentPinClientId = txtPinCode.ClientID;
			ctrlAddressMap1.ParentLocationNameClientId = txtLocation.ClientID; //txtAddr1.ClientID;
																			   //ctrlAddressMap1.ParentAddrClientId = txtAddr2.ClientID;
			ctrlAddressMap1.ParentDistrictClientId = hidDistrict.ClientID;
			ctrlAddressMap1.ParentStateClientId = hidState.ClientID;

			if (!String.IsNullOrEmpty(hidLat.Value))
				ctrlAddressMap1.Lat = hidLat.Value;
			if (!String.IsNullOrEmpty(hidLong.Value))
				ctrlAddressMap1.Lng = hidLong.Value;
            rfvreferral.Enabled = ConfigurationManager.AppSettings["SignupRestrictByInvite"] == "1";


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
			var gstResult = CurGSTResult;
			string GstState = "";
			try
			{
				if (CurVATType == 2 && gstResult != null && !string.IsNullOrEmpty(gstResult.State))
				{
					try
					{
						GstState = gstResult.State;
					}
					catch { }
					if (String.IsNullOrEmpty(GstState))
						return;

					string selectedState = selState.SelectedItem.Text.ToLower();
					string selectedGststateName = GstState.ToLower();
					if (gstResult.TaxPayerType != "REGULAR" && selectedState != selectedGststateName)
					{
						Common.ShowCustomAlert(this.Page, "Failure", "Your Selected Store Location is Outside The State Associated With Your Enrollment Number", false);
						selState.SelectedValue = "";
						return;
					}

				}
			}
			catch { }

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

			string strUserEmail = UserEmail; // CurrentSignupType == LoginType.Email ? UserEmail : txtLoginEmail.Value;
			string strUserMobile = CurrentSignupType == LoginType.Mobile ? UserMobileNumber : txtContactPhone.Text;
			if (string.IsNullOrEmpty(strUserEmail))
			{
				txtLoginEmail.Attributes.Add("class", String.Format("{0} error", txtLoginEmail.Attributes["class"]));
				ltrResult.Text = "Invalid email account. Please try with valid email id.";
				return;
			}
			if (string.IsNullOrEmpty(strUserMobile))
			{
				txtContactPhone.Attributes.Add("class", String.Format("{0} error", txtContactPhone.Attributes["class"]));
				ltrResult.Text = "Invalid mobile number. Please try with valid email id.";
				return;
			}

			// Validate email id used to avoid duplication.
			List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
			prms.Add(new KeyValuePair<string, object>("email", strUserEmail));
			//int count = (int)DataService.ExecuteScalar("select count(*) from [User] where Email like @email", parmeters: prms);
			//if (count > 0)
			//{
			//	txtLoginEmail.Attributes.Add("class", String.Format("{0} error", txtLoginEmail.Attributes["class"]));
			//	ltrResult.Text = "Email id is already in use. Please try with another email id. If you own this email id, please login with your account or use the reset password if you forgot it.";
			//	return;
			//}
			prms.Add(new KeyValuePair<string, object>("phone", strUserMobile));
			int count = (int)DataService.ExecuteScalar("select count(*) from [User] where [Mobile] like @phone", parmeters: prms);
			if (count > 0)
			{
				txtContactPhone.Attributes.Add("class", String.Format("{0} error", txtContactPhone.Attributes["class"]));
				ltrResult.Text = "Phone number is already in use. Please try with another number. If you own this number, please login with your account or use the reset password if you forgot it.";
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
			try { primaryBusinessType = Convert.ToInt32(selBusinessTypes.Text); } catch (Exception ex) { primaryBusinessType = -1; this.LogError(ex.Message); }
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
			catch (Exception ex) { this.LogError(ex.Message); }
			if (primaryBusinessType <= 0 && secondaryBTypeIds.Count < 1)
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
				prms.Add(new KeyValuePair<string, object>("VerifyKey", guid));
				prms.Add(new KeyValuePair<string, object>("Password", strPsw));
				prms.Add(new KeyValuePair<string, object>("verifycode", rnd.ToString()));
				prms.Add(new KeyValuePair<string, object>("FullName", txtContactPerson.Value));
				prms.Add(new KeyValuePair<string, object>("EmailVerified", CurrentSignupType == LoginType.Email ? 1 : 0));
				prms.Add(new KeyValuePair<string, object>("MobileVerified", CurrentSignupType == LoginType.Mobile ? 1 : 0));
				prms.Add(new KeyValuePair<string, object>("verifiedVAT", (CurGSTStatus == Services.GSTStatus.Verified ? 1 : 0)));
				string sqlInsert = "INSERT INTO [dbo].[User]([Email],[Mobile],[Password],[PasswordSalt],[PasswordType],[Status],[FullName], hasVerifiedEmail, hasVerifiedMobile, StoreGroupId, hasVerifiedVAT, VerifyKey) VALUES(@email,@phone,@Password, @verifycode, 4, 1, @FullName, @EmailVerified, @MobileVerified, -1, @verifiedVAT, @VerifyKey); select scope_identity()";
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
			catch (Exception ex)
			{
				this.LogError(ex.Message);
				ltrResult.Text = "User creation failure!! There is a technical error on user creation. Please try again later with another number or contact support for more details"; //+ex.Message;
				btnSubmitAccount.Visible = false;
				hlGoHome.Visible = true;
				return;
			}

			User user = null;
			var loginResult = UserService.ValidateCustomer(UserEmail, strPsw, out user);
			if (user == null || user.Id <= 0)
			{
				ltrResult.Text = "There is a technical error on creating user account. Please trying again later or contact support.";
				return;
			}

			int gstid = -1; string gst = "";
			// Generate dynamic url for the new store.
			string strStoreDomain = CreateStoreDomain();
			if (String.IsNullOrEmpty(strStoreDomain))
				return;

			if (dtUserid != null && dtUserid.Rows.Count > 0 && ((CurVATType == 2 && CurGSTResult != null) || (CurVATType != 2 && CurVATData != null)|| (CurVATType != 2 && CurTRNData != null)))
			{
				try
				{
					int userid = Convert.ToInt32(dtUserid.Rows[0][0]);

					var _gstresult = CurGSTResult;
					List<KeyValuePair<string, object>> gstPrms = new List<KeyValuePair<string, object>>();
					if (CurVATType != 2)
					{
						gstPrms.Add(new KeyValuePair<string, object>("address", !string.IsNullOrEmpty(CurVATData?.company_address) ? CurVATData.company_address:CurTRNData?.legal_name));
						gstPrms.Add(new KeyValuePair<string, object>("gstdata", CurVATData!=null ? System.Text.Json.JsonSerializer.Serialize(CurVATData) : System.Text.Json.JsonSerializer.Serialize(CurTRNData)));
						gstPrms.Add(new KeyValuePair<string, object>("gstin", !string.IsNullOrEmpty(CurVATData?.vat_number) ? CurVATData.vat_number:CurTRNData.trn_number));
						gstPrms.Add(new KeyValuePair<string, object>("organization", !string.IsNullOrEmpty(CurVATData?.company_name) ? CurVATData.company_name :CurTRNData.legal_name));
						gstPrms.Add(new KeyValuePair<string, object>("email", ""));
						gstPrms.Add(new KeyValuePair<string, object>("mobile", ""));
						gst = !string.IsNullOrEmpty(CurVATData?.vat_number) ? CurVATData.vat_number : CurTRNData.trn_number;
                    }
					else
					{
						gstPrms.Add(new KeyValuePair<string, object>("address", _gstresult.Address));
						gstPrms.Add(new KeyValuePair<string, object>("email", _gstresult.Email));
						gstPrms.Add(new KeyValuePair<string, object>("gstdata", _gstresult.RawResponse));
						gstPrms.Add(new KeyValuePair<string, object>("gstin", _gstresult.GSTIN));
						gstPrms.Add(new KeyValuePair<string, object>("mobile", _gstresult.Mobile));
						gstPrms.Add(new KeyValuePair<string, object>("organization", _gstresult.TradeName));
						gst = _gstresult.GSTIN;
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
					string Address = (ConfigurationManager.AppSettings.Get("CountryCode") != "IN") ?  (CurVATData?.company_address ?? CurTRNData?.legal_name) : _gstresult.Address;
					string Gstdata = _gstresult.RawResponse;
					string Gstin = (ConfigurationManager.AppSettings.Get("CountryCode") != "IN") ? (CurVATData?.vat_number ?? CurTRNData?.trn_number) : _gstresult.GSTIN;
					string Organization = (ConfigurationManager.AppSettings.Get("CountryCode") != "IN") ? (CurVATData?.company_name ?? CurTRNData?.legal_name)  : _gstresult.TradeName;
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
				catch (Exception ex) { this.LogError(ex.Message); }
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
				catch (Exception ex) { this.LogError(ex.Message); }
			}

			// Notification.
			prms = new List<KeyValuePair<string, object>>();
			prms.Add(new KeyValuePair<string, object>("verify_key", guid));
			prms.Add(new KeyValuePair<string, object>("verify_code", rnd));
			prms.Add(new KeyValuePair<string, object>("verify_type", 2));
			prms.Add(new KeyValuePair<string, object>("verify_status", "pending"));
			prms.Add(new KeyValuePair<string, object>("details", strActivationUrl));
			prms.Add(new KeyValuePair<string, object>("mobil", UserMobileNumber));
			prms.Add(new KeyValuePair<string, object>("email", UserEmail));
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

			Activitylog.SignupUpdatelogAsync(UserMobileNumber);
			// Send welcome email.
			try
			{
				bool hasSendNotification = Core.Services.APIService.SendEmail(UserEmail, "Welcome to Grozeo - Your Merchant Account is Now Active!", strBody, txtContactPerson.Value, true).Result;
				if (!hasSendNotification)
					this.LogError(string.Format("Merchant onboard, welcome email sending failed. Store Url: {0}, email: {1}, user id: {2}" + strStoreDomain, user.Email, user.Id));
			}
			catch (Exception ex) { this.LogError(ex.Message); }

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
				if (CurVATType == 2 && CurGSTResult != null)
					txtStoreName.Text = CurGSTResult.TradeName;
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
				int maxbusinessTypeRestricted = 0; try { maxbusinessTypeRestricted = Convert.ToInt32(ConfigurationManager.AppSettings.Get("MaxBusinessTypeRestricted") ?? "0"); } catch { maxbusinessTypeRestricted = 0; }
				int btIndex = 0;
				foreach (ListItem item in lstBusinessTypes.Items)
					if (item.Selected)
					{
						if (maxbusinessTypeRestricted > 0 && btIndex >= maxbusinessTypeRestricted)
							break;

						btIndex++;
						int secBType = Convert.ToInt32(item.Value);
						if (btIndex == 1)
						{
							strBusinessType = item.Text;
							primaryBusinessType = secBType;
							continue;
						}

						strSecondaryBTypes += (String.IsNullOrWhiteSpace(strSecondaryBTypes) ? "" : ",") + item.Text;
						secondaryBTypeIds.Add(secBType);

					}
			}
			catch (Exception ex) { this.LogError(ex.Message); }

			string guid = Guid.NewGuid().ToString();
			// Create Storegroup using API service.
			string referralcode = CurInvitationCode;
			storegroupId = Services.StoreService.CreateStoreGroup(txtStoreName.Text, primaryBusinessType, secondaryBTypeIds, guid, strDomain, referralcode);
			if (storegroupId < 1)
			{
				ltrResult.Text = "Store creation failed. Error Code: 1002 - There is a technical error happened in the back end system. Please try again later or contact support for more details.";
				return false;
				//throw new Exception("Error. Store creation failed at backoffice.");
			}

			if (!String.IsNullOrWhiteSpace(CurInvitationCode))
			{
				try
				{

					List<KeyValuePair<string, object>> prospectparams = new List<KeyValuePair<string, object>>();
					prospectparams.Add(new KeyValuePair<string, object>("storegroup", storegroupId));
					prospectparams.Add(new KeyValuePair<string, object>("code", CurInvitationCode));
					string prospectSql = $"UPDATE finascop_crm_prospect SET storeGroupId=@storegroup WHERE crpr_mode <> 5 and invitationCode=@code and ifnull(storeGroupId, 0) < 1";
					DataServiceMySql.ExecuteScalar(prospectSql, UserService.GetAPIConnectionString(), prospectparams);

				}
				catch (Exception ex) { this.LogError(ex.Message); }
			}
			int apiid = 1; try { apiid = Convert.ToInt32(System.Configuration.ConfigurationManager.AppSettings.Get("APIID")); } catch (Exception ex) { apiid = 1; this.LogError(ex.Message); }
			bool canCheckout = true, onlinePayment = true, showPwa = true;
			try { canCheckout = (ConfigurationManager.AppSettings.Get("CanCheckout") == "1" ? true : false); } catch { canCheckout = false; }
			try { onlinePayment = (ConfigurationManager.AppSettings.Get("OnlinePaymentEnabled") == "1" ? true : false); } catch { onlinePayment = false; }
			try { showPwa = (ConfigurationManager.AppSettings.Get("ShowPWA") == "1" ? true : false); } catch { showPwa = false; }

			int merchantType = (CurTaxType == Service.Store.VATType.VAT || CurTaxType == Service.Store.VATType.GST || CurTaxType == Service.Store.VATType.TRN || CurTaxType == Service.Store.VATType.TestGST || CurTaxType == Service.Store.VATType.TestVAT ? 1 : 2);

			if (canCheckout && merchantType == 1 && CurGSTStatus == Services.GSTStatus.VerificationSkipped && System.Configuration.ConfigurationManager.AppSettings.Get("StoreDisableNoneVAT") == "1")
				canCheckout = false;

			string strMatomoId = "";
			try
			{
				strMatomoId = Services.StoreService.MatomoCreateSite(txtStoreName.Text, strDomain);
			}
			catch (Exception ex) { this.LogError(ex.Message); }

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
			parmeters.Add(new KeyValuePair<string, object>("addr", String.Format("{0}, {1}, {2}", (ConfigurationManager.AppSettings.Get("CountryCode") == "UK" ? txtAddr1UK.Text : txtAddr2.Text), selDistrict.SelectedItem.Text, selState.Text)));
			parmeters.Add(new KeyValuePair<string, object>("phone", txtContactPhone.Text));
			parmeters.Add(new KeyValuePair<string, object>("email", UserEmail));
			parmeters.Add(new KeyValuePair<string, object>("contactname", txtContactPerson.Value));
			parmeters.Add(new KeyValuePair<string, object>("analyticsId", strMatomoId));
			if (CurTaxType == Service.Store.VATType.Adhar)
			{
				parmeters.Add(new KeyValuePair<string, object>("VerificationType", 3));
				if (CurAdharData != null && CurAdharData.id > 0)
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
			try
			{
				Finascop.Services.StoreService.StoreGroupCreate(txtStoreName.Text, user.Phone, storegroupId, guid);
			}
			catch (Exception ex)
			{
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
			try
			{
				if (ConfigurationManager.AppSettings.Get("VATType") == "2" && tradeRestrictionType == 0)
				{
					taxtype = 0;
					if (!(CurGSTResult == null || string.IsNullOrEmpty(CurGSTResult.GSTIN)))
					{
						if (CurGSTResult.TaxPayerType != "REGULAR")
							tradeRestrictionType = 1;

						switch (CurGSTResult.TaxPayerType)
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
			}
			catch (Exception ex) { this.LogError(ex.Message); }
			string branchName = txtStoreName.Text + '-' + selDistrict.SelectedItem.Text;
			var storebranch = Services.StoreService.CreateStore(branchName, strBrShort, storegroupId, (ConfigurationManager.AppSettings.Get("CountryCode") == "UK" ? txtAddr1UK.Text : txtAddr2.Text), txtAddr3.Text, txtAddr4.Text,
				selDistrict.SelectedItem.Text, Convert.ToInt32(selState.Text), Convert.ToInt32(selDistrict.Text), txtPinCode.Text, user.Email, user.Phone, hidLat.Value,
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
				catch (Exception ex)
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


		protected void CustomValidatorChkAcceptTerms_ServerValidate(object source, ServerValidateEventArgs args)
		{
			args.IsValid = chkAcceptTerms.Checked;
		}


	}
}