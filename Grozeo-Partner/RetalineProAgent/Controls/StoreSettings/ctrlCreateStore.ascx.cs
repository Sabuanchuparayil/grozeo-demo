using NPOI.SS.Formula.Functions;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.GST;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Text;
using System.Text.RegularExpressions;
using System.Threading.Tasks;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Controls.StoreSettings
{
    public partial class ctrlCreateStore: Base.BasePartnerUserControl
    {
        public bool IsBranchView { get; set; }
        public int BranchId {
            get {
                return (int)(ViewState["BRANCHIDEDIT"]??0);
            }
            set { ViewState["BRANCHIDEDIT"] = value; }
        }
        public int APIBranchId { get; set; }
        public Button ResetBtn
        {
            get { return null; }//btnReset
        }

        public Core.BussinessModel.Store.Store APIStore {
            get {
                return (Core.BussinessModel.Store.Store)ViewState["APISTOREEDIT"];
            }
            set {
                ViewState["APISTOREEDIT"] = value;
            } 
        }
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

        public delegate void ParentCustomHandler(int type);
        public event ParentCustomHandler ParentButtonBinding;
        
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

		private int MyGSTId
        {
            get
            {
                return (int)ViewState["MYGSTID"];
            }
            set
            {
                ViewState["MYGSTID"] = value;
            }
        }
        protected void Page_Load(object sender, EventArgs e)
        {
            plcColPBranchInfo.Visible = this.CurrentUser.StoreGroupId <= 0;

            if (!IsPostBack)
            {
                if (ConfigurationManager.AppSettings["CountryCode"] == "AE")
                {
                    txtPinCode.Attributes.Remove("required");
                }
                else
                {
                    txtPinCode.Attributes.Add("required", "required");
                }
                if (this.CurrentUser.StoreGroupId <= 0)
                {
                    try
                    {
                        string sqlGSTByUserId = "SELECT top 1 * FROM GST where userid=@userid and tenantid is null order by isverified desc";
                        List<KeyValuePair<string, object>> gstPrms = new List<KeyValuePair<string, object>>();
                        gstPrms.Add(new KeyValuePair<string, object>("userid", this.CurrentUser.Id));
                        DataTable tblGST = DataService.GetDataTable(sqlGSTByUserId, parmeters: gstPrms);
                        if (tblGST != null && tblGST.Rows.Count > 0)
                        {
                            MyGSTId = Convert.ToInt32(tblGST.Rows[0]["id"]);

							CurGSTResult = new GSTValidationResult();
							CurGSTResult.GSTIN = tblGST.Rows[0]["gstin"].ToString();
							CurGSTResult.TradeName = tblGST.Rows[0]["organization"].ToString();
							CurGSTResult.Address = tblGST.Rows[0]["address"].ToString();
							CurGSTResult.Email = tblGST.Rows[0]["email"].ToString();
							CurGSTResult.Mobile = tblGST.Rows[0]["mobile"].ToString();
							try { CurGSTResult.RawResponse = tblGST.Rows[0]["gstdata"].ToString(); } catch { }
                            try { CurGSTStatus = (tblGST.Rows[0]["isverified"] == DBNull.Value ? Services.GSTStatus.VerificationSkipped : (Convert.ToBoolean(tblGST.Rows[0]["isverified"]) ? Services.GSTStatus.Verified : Services.GSTStatus.VerificationSkipped)); } catch { }
                        }
                    }
                    catch (Exception ex)
                    {
                        string strException = ex.Message;
                    }

                    if (CurGSTResult != null)
                    {
                        txtDisplayName.Text = CurGSTResult.TradeName;
                    }
                }


            }
            ctrlAddressMap1.ParentLocationClientId = txtLocation.ClientID;
            ctrlAddressMap1.ParentLatClientId = hidLat.ClientID;
            ctrlAddressMap1.ParentLongClientId = hidLong.ClientID;

            ctrlAddressMap1.ParentLocationClientId = hidMapAddr.ClientID; // txtLocation.ClientID;
            ctrlAddressMap1.ParentLatClientId = hidLat.ClientID;
            ctrlAddressMap1.ParentLongClientId = hidLong.ClientID;
            ctrlAddressMap1.ParentPinClientId = txtPinCode.ClientID;
            ctrlAddressMap1.ParentLocationNameClientId = txtAddr1.ClientID;
            //ctrlAddressMap1.ParentAddrClientId = txtAddr2.ClientID;
            ctrlAddressMap1.ParentDistrictClientId = hidDistrict.ClientID;
            ctrlAddressMap1.ParentStateClientId = hidState.ClientID;
            if (!String.IsNullOrEmpty(hidMapAddr.Value))
                txtLocation.Text = hidMapAddr.Value;



            plcColStoreInfo.Visible = !IsBranchView;
        }

        protected void Page_PreRender(object sender, EventArgs e)
        {

            hidDistrict.Value = "";
            hidMapAddr.Value = "";
        }

        /// <summary>
        /// Preload input fields
        /// Preload input fields
        /// </summary>
        public void LoadInput()
        {

            int storegroupid = this.CurrentUser.StoreGroupId;
            if (storegroupid > 0)
            {
                if (!IsBranchView)
                {

                    string strSql = $"SELECT a.Id, a.Name, a.Theme, a.APIUrl, a.CanCheckout, a.CustomColor, a.FavIcoImage, " +
                        $"Stuff((SELECT ',' + (t.HostAddress) FROM Host t WHERE a.Id LIKE t.TenantId FOR Xml Path('')), 1, 1, '') as hosts, " +
                        $"a.LogoImage, a.LogoSmall, a.OnlinePaymentEnabled, a.ShowPWA, a.Status, a.StoreId as StoreGroupId, " +
                        $"s.APICode, s.BusinessType, s.SecondaryBusinessTypes, s.DisplayName, s.CreatedBy, s.CreatedOn, s.DBConnectionString, s.GroupId, s.Id as StoreId, " +
                        $"s.InventoryFile, s.MinMargin, s.Name as StoreName, s.Package, s.SelectSql, s.UpdatedOn, s.UpdatedBy, s.InventoryMapType, " +
                        $" sb.Location, sb.Addr, sb.District, sb.[State], sb.Pin, sb.Lat, sb.Lang, sb.GST, sb.PAN, sb.APIBranchId, sb.BankBranch, sb.BankAddr, sb.BankIFSC, sb.BankName, sb.BankNo, sb.MapLocation " +
                        $" FROM AppTenant a inner join Store s on a.Id=s.Tenantid left join StoreBranch sb on sb.StoreId=a.Id and sb.IsDefault = 1 WHERE a.Id={storegroupid}";//SDSStores.SelectCommand + " where a.Id = " + strEditId;

                    if (!(Page.User.IsInRole("SuperAdmin") || Page.User.IsInRole("RetalineProAgent")))
                        strSql += " AND a.Id in (SELECT m.StoreGroupId FROM User_UserRole_Mapping m INNER JOIN [User] u on u.Id=m.UserId WHERE u.Email like @user)";
                    List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                    prms.Add(new KeyValuePair<string, object>("user", Page.User.Identity.Name));
                    DataTable dt = DataService.GetDataTable(strSql, parmeters: prms);
                    if (dt.Rows.Count > 0)
                    {
                        DataRow dr = dt.Rows[0];
                        txtStoreName.Text = dr["Name"].ToString();
                        txtStoreName.Enabled = false;
                        txtDisplayName.Text = dr["DisplayName"].ToString();
                        txtAddr1.Text = dr["Location"].ToString();
                        txtPinCode.Text = dr["Pin"].ToString();
                        txtAddr2.Text = dr["Addr"].ToString();

                        string strLogo = dr["LogoImage"].ToString();
                        string strLogoSmall = dr["LogoSmall"].ToString();
                        string strTheme = dr["Theme"].ToString();
                        if (!String.IsNullOrEmpty(strLogo))
                        {
                            imgLogo.ImageUrl = strLogo;
                            imgLogo.Visible = true;
                            chkDelImgLogo.Visible = true;
                        }
                        if (!String.IsNullOrEmpty(strLogoSmall))
                        {
                            imgLogoWhite.ImageUrl = strLogoSmall;
                            imgLogoWhite.Visible = true;
                            chkDelImgLogoWhite.Visible = true;
                        }

                        string strCustomColor = dr["CustomColor"].ToString();
                        if (!String.IsNullOrEmpty(strCustomColor))
                            txtColor.Text = strCustomColor;

                        string strState = dr["State"].ToString();
                        if (!String.IsNullOrEmpty(strState))
                            selState.Attributes["DefaultState"] = strState;

                        string strDistrict = dr["District"].ToString();
                        if (!String.IsNullOrEmpty(strDistrict))
                            selDistrict.Attributes["DefaultDistrict"] = strDistrict;

                        string strBusinessType = dr["BusinessType"].ToString();

                        //if (!String.IsNullOrEmpty(strBusinessType) && selBusinessTypes.Items.FindByText(strBusinessType) != null)
                        //    selBusinessTypes.Text = (selBusinessTypes.Items.FindByText(strBusinessType).Value);
                        if (!String.IsNullOrEmpty(strBusinessType))
                            selBusinessTypes.Attributes["DefaultBType"] = strBusinessType;

                        string strSecondaryBusinessTypes = dr["SecondaryBusinessTypes"].ToString();
                        if (!String.IsNullOrEmpty(strSecondaryBusinessTypes))
                        {
                            lstBusinessTypes.Attributes["DefaultBType"] = strSecondaryBusinessTypes;
                            string[] strbtypes = strSecondaryBusinessTypes.Trim().Split(',');
                            if (strbtypes.Length > 0)
                            {
                                foreach (string btype in strbtypes)
                                    if (!String.IsNullOrEmpty(btype.Trim()) && lstBusinessTypes.Items.FindByText(btype.Trim()) != null)
                                        lstBusinessTypes.Items.FindByText(btype.Trim()).Selected = true;
                            }
                        }

                        //string strHost = dr["hosts"].ToString(); ;
                        //string[] strHosts = strHost.Split(',');
                    }
                }

                if (APIStore != null)
                {
                    txtAddr2.Text = APIStore.Address;
                    string strDist = APIStore.District.ToString();
                    hidLong.Value = APIStore.Lng; //dr["Lang"].ToString();
                    hidLat.Value = APIStore.Lat; //dr["Lat"].ToString();
                    ctrlAddressMap1.Lat = hidLat.Value;
                    ctrlAddressMap1.Lng = hidLong.Value;
                    txtAddr1.Text = APIStore.BranchName; //dr["Location"].ToString();
                    txtPinCode.Text = APIStore.Pin;// dr["Pin"].ToString();
                    string strState = APIStore.State.ToString();// dr["State"].ToString();

                    if (selState.Items.Count <= 1)
                        selState.DataBind();

                    selState.ClearSelection();
                    if (!String.IsNullOrEmpty(strState) && selState.Items.FindByValue(strState) != null)
                        selState.SelectedValue = strState; //selState.Items.FindByText(strState).Value;

                    if (selDistrict.Items.Count <= 1)
                        selDistrict.DataBind();
                    selDistrict.ClearSelection();
                    if (!String.IsNullOrEmpty(strDist) && selDistrict.Items.FindByValue(strDist) != null)
                        selDistrict.SelectedValue = strDist; //selDistrict.Items.FindByText(strDist).Value;

                    DataTable dtBranch = DataService.GetDataTable($"SELECT * FROM StoreBranch WHERE {(BranchId > 0 ? "Id=" + BranchId + " and " : "")} StoreId= {storegroupid} {(IsBranchView && APIStore != null ? " and APIBranchId=" + APIStore.BranchId : "")}");
                    if (dtBranch != null && dtBranch.Rows.Count > 0)
                    {
                        DataRow dr = dtBranch.Rows[0];
                        txtIFSC.Text = dr["BankIFSC"].ToString();
                        txtBankAcNo.Text = dr["BankNo"].ToString();
                        txtGSTNo.Text = dr["GST"].ToString();
                        txtLocation.Text = dr["MapLocation"].ToString();
                        txtBPan.Text = dr["PAN"].ToString();
                    }
                }

            }




        }


        protected void btnAdd_Click(object sender, EventArgs e)
        {
            int storegroupid = this.CurrentUser.StoreGroupId;
            if (Page.IsValid)
            {
                //if (String.IsNullOrEmpty(hidLat.Value) || String.IsNullOrEmpty(hidLong.Value))
                //{
                //    lblMessage.Text = "Please select location in map. Click on the button 'Load Map' to search your location.";
                //    return;
                //}
                //if (IsBranchView)
                //{
                //    if (APIBranchId > 0)
                //        EditBusinessInfo();
                //    else
                //        CreateTenant();
                //}
                //else
                //{
                    if (storegroupid < 1)
                        CreateTenant();
                    else
                        EditBusinessInfo();
                //}
            }
        }

        /// <summary>
        /// Create new Tenant and store
        /// </summary>
        /// <returns></returns>
        private void CreateTenant()
        {
            if(this.CurrentUser.StoreGroupId > 0)
            {
                EditBusinessInfo();
                return;
            }

            if (Page.IsValid)
            {
                if (String.IsNullOrEmpty(hidLat.Value) || String.IsNullOrEmpty(hidLong.Value))
                {
                    lblMessage.Text = "Please select location in map. Click on the button 'Load Map' to search your location.";
                    ShowFailure("Missing Store Location", "Please select store location in map. You can click on the button 'Load Map' and search your location.");
                    return;
                }
                if (String.IsNullOrEmpty(txtAddr1.Text))
                {
                    ShowFailure("Missing Store Name", "Please enter your store location name.");
                    return;

                }

                //Service.User curUser = UserService.GetCustomerByUsername(Page.User.Identity.Name);
                int storegroupId = -1; //curUser.APIStoreId;
                int tenantId = -1; // curUser.StoreGroupId;

                List<string> strExcempt = new List<string>();
                var tblBrShort = DataServiceMySql.GetDataTable("SELECT DISTINCT branch_shortname FROM finascop_branch", UserService.GetAPIConnectionString());
                if (tblBrShort != null && tblBrShort.Rows.Count > 0)
                {
                    strExcempt = tblBrShort.AsEnumerable().Select(item => string.Format("{0}", item["branch_shortname"])).ToList();
                }
                string strBrShort = Common.RandomString(4, strExcempt?.ToArray());
                if (String.IsNullOrEmpty(strBrShort))
                {
                    lblMessage.Text = "Sorry, there is a technical error on store code creation. Please try again later or contact support for more details";
                    ShowFailure("Store creation failed", "Sorry, there is a technical error on store code creation. Please try again later or contact support for more details");
                    return;
                }

                GSTValidationResult gstResult = null;
                int gstid = -1; string gst="";

                if (!IsBranchView)
                {
                    if (String.IsNullOrEmpty(selBusinessTypes.Text))
                    {
                        lblMessage.Text = "Missing primary business Type. Primary business type is mandatory. Please select your primary in order to continue.";
                        ShowFailure("Missing input field", "Missing primary business Type. Primary business type is mandatory. Please select your primary in order to continue.");
                        return;
                    }
                    string strBusinessType = selBusinessTypes.SelectedItem.Text, strSecondaryBTypes = "";
                    int primaryBusinessType = -1; 
                    try { primaryBusinessType = Convert.ToInt32(selBusinessTypes.Text); } 
                    catch(Exception ex) {
                        primaryBusinessType = -1;
                        lblMessage.Text = "Primary business type is required";
                        ShowFailure("Missing input field", "Missing primary business Type. There is a problem with the primary business type. Error: "+ex.Message);
                        return;
                    }
                    if (primaryBusinessType <= 0)
                    {
                        lblMessage.Text = "Primary business type is required.";
                        ShowFailure("Missing input field", "Missing primary business Type. There is a problem with the primary business type.");
                        return;
                    }

                    List<int> secondaryBTypeIds = new List<int>();
                    try
                    {
                        foreach (ListItem item in lstBusinessTypes.Items)
                            if (item.Selected)
                            {
                                int secBType = Convert.ToInt32(item.Value);
                                if (secBType == primaryBusinessType)
                                    continue;

                                strSecondaryBTypes += (String.IsNullOrWhiteSpace(strSecondaryBTypes) ? "" : ",") + item.Text;
                                secondaryBTypeIds.Add(secBType);
                            }
                    }
                    catch { }

                    string strStoreUrlTemplate = System.Configuration.ConfigurationManager.AppSettings.Get("newsitedomain");
                    string strDomainPart = strStoreUrlTemplate.Replace("[title]", "");

                    //if (!lblCustomDomain.Text.EndsWith(strDomainPart) || lblCustomDomain.Text == strStoreUrlTemplate)
                    //    lblCustomDomain.Text = strStoreUrlTemplate.Replace("[title]", txtStoreName.Text.Replace(" ", "").Trim().ToLower());
                    


                    string strDomainTemplate = System.Configuration.ConfigurationManager.AppSettings.Get("newsitedomain");
                    if(String.IsNullOrWhiteSpace(txtDisplayName.Text))
                     txtDisplayName.Text = CurGSTResult.TradeName;

                    if (String.IsNullOrWhiteSpace(txtDisplayName.Text))
                        txtDisplayName.Text = "My Store";
                    
                    string strDomainStoreName = txtDisplayName.Text;//txtStoreName.Text.Replace(" ", "").Trim().ToLower();
                    if(strDomainStoreName.Length > 17)
                    {
                        if (strDomainStoreName.Contains(" "))
                        {
                            strDomainStoreName = "";
                            foreach (string strPart in strDomainStoreName.Split(' '))
                            {
                                if (strDomainStoreName.Length > 16)
                                    break;
                                strDomainStoreName += (String.IsNullOrEmpty(strPart.Trim())?"": strPart.Trim());
                            }
                        }
                        else
                        {
                            strDomainStoreName = strDomainStoreName.Substring(0, 16);
                        }
                    }
                    strDomainStoreName = strDomainStoreName.Replace(" ", "").Trim().ToLower();
                    strDomainStoreName = Regex.Replace(strDomainStoreName, "[^a-zA-Z0-9_.]+", "", RegexOptions.Compiled);

                    strDomainTemplate = strDomainTemplate.Replace("-sites.", "-{0}.").Replace("[title]", strDomainStoreName);
                    string strDomain = Common.RandomDomainKey(4, new string[] { }, strDomainTemplate);

                    List<KeyValuePair<string, object>> hostParams = new List<KeyValuePair<string, object>>();
                    hostParams.Add(new KeyValuePair<string, object>("host", strDomain));
                    string strSqlCheck = $"select HostAddress from host where HostAddress like @host";
                    DataTable dtHosts = DataService.GetDataTable(strSqlCheck, parmeters: hostParams);
                    if (dtHosts != null && dtHosts.Rows.Count > 0)
                    {
                        List<string> strDomainExcempt = new List<string>();
                        var tblHosts = DataService.GetDataTable("SELECT DISTINCT HostAddress from host");
                        if (tblHosts != null && tblHosts.Rows.Count > 0)
                            strDomainExcempt = tblHosts.AsEnumerable().Select(item => string.Format("{0}", item["HostAddress"])).ToList();
                        string strNewDomain = Common.RandomDomainKey(4, strDomainExcempt?.ToArray(), strDomainTemplate);
                        if(String.IsNullOrEmpty(strNewDomain))
                            strNewDomain = Common.RandomDomainKey(6, strDomainExcempt?.ToArray(), strDomainTemplate);
                        if (String.IsNullOrEmpty(strNewDomain))
                            strNewDomain = Common.RandomDomainKey(8, strDomainExcempt?.ToArray(), strDomainTemplate);


                        if (String.IsNullOrEmpty(strNewDomain))
                        {
                            lblMessage.Text = "Store name is already existing. Please try another name or contact technical support for more details. ";
                            ShowFailure("Duplicate Store Name", "Store name is already existing. Please try another name or contact technical support for more details. ");
                            return;
                        }
                        strDomain = strNewDomain;
                    }

                    //List<KeyValuePair<string, object>> duplicateStorePrms = new List<KeyValuePair<string, object>>();
                    //duplicateStorePrms.Add(new KeyValuePair<string, object>("storename", txtStoreName.Text));
                    //strSqlCheck = $"select top 1 * from AppTenant where [Name] like @storename";
                    //dtHosts = DataService.GetDataTable(strSqlCheck, parmeters: duplicateStorePrms);
                    //if (dtHosts != null && dtHosts.Rows.Count > 0)
                    //{
                    //    lblMessage.Text = "Store name is already existing. Please try another name or contact technical support for more details.";
                    //    ShowFailure("Store creation failed", "Store name is already existing. Please try another name or contact technical support for more details. ");
                    //    return;
                    //}

                    try
                    {
                        string sqlGSTByUserId = "SELECT top 1 * FROM GST where userid=@userid and tenantid is null order by id desc";
                        List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                        prms.Add(new KeyValuePair<string, object>("userid", this.CurrentUser.Id));
                        DataTable tblGST = DataService.GetDataTable(sqlGSTByUserId, parmeters: prms);
                        if (tblGST != null && tblGST.Rows.Count > 0)
                        {
                            gstid = Convert.ToInt32(tblGST.Rows[0]["id"]);
                            gst = tblGST.Rows[0]["gstin"].ToString();
							gstResult = new GSTValidationResult();
							gstResult.GSTIN = tblGST.Rows[0]["gstin"].ToString();
							gstResult.TradeName = tblGST.Rows[0]["organization"].ToString();
							gstResult.Address = tblGST.Rows[0]["address"].ToString();
							gstResult.Email = tblGST.Rows[0]["email"].ToString();
							gstResult.Mobile = tblGST.Rows[0]["mobile"].ToString();
							try { gstResult.RawResponse = tblGST.Rows[0]["gstdata"].ToString(); } catch { }

                            txtDisplayName.Text = gstResult.TradeName;
                        }
                    }
                    catch(Exception ex) {
                        string strException = ex.Message;
                    }

                    string guid = Guid.NewGuid().ToString();
                    // Call API service.
                    storegroupId = Services.StoreService.CreateStoreGroup(txtDisplayName.Text, primaryBusinessType, secondaryBTypeIds, guid, strDomain);
                    if (storegroupId < 1)
                    {
                        ShowFailure("Store creation failed", "Error Code: 1002 - There is a technical error happened in the back end system. Please try again later or contact support for more details.");
                        return;
                        //throw new Exception("Error. Store creation failed at backoffice.");
                    }
                    int apiid = 1; try { apiid = Convert.ToInt32(System.Configuration.ConfigurationManager.AppSettings.Get("APIID")); } catch { apiid = 1; }
                    List<KeyValuePair<String, Object>> parmeters = new List<KeyValuePair<string, object>>();
                    parmeters.Add(new KeyValuePair<string, object>("Name", txtDisplayName.Text));
                    string strTheme = System.Configuration.ConfigurationManager.AppSettings.Get("ThemeDefault");

                    parmeters.Add(new KeyValuePair<string, object>("Theme", strTheme));//selTheme.Text));
                    parmeters.Add(new KeyValuePair<string, object>("APIUrl", System.Configuration.ConfigurationManager.AppSettings.Get("api.url")));
                    parmeters.Add(new KeyValuePair<string, object>("CanCheckout", true));//chkCheckout.Checked));
                    parmeters.Add(new KeyValuePair<string, object>("OnlinePaymentEnabled", true));//chkOnline.Checked));
                    parmeters.Add(new KeyValuePair<string, object>("StoreGroupId", storegroupId));
                    parmeters.Add(new KeyValuePair<string, object>("Status", (CurGSTStatus == Services.GSTStatus.Verified ? 1 : 2) ));//chkStatus.Checked));
                    parmeters.Add(new KeyValuePair<string, object>("ShowPWA", true));//chkPWA.Checked));
                    parmeters.Add(new KeyValuePair<string, object>("MinMargin", 5));// txtMinMargine.Text));
                    parmeters.Add(new KeyValuePair<string, object>("Package", "basic"));//selPackage.Text));
                    parmeters.Add(new KeyValuePair<string, object>("BusinessType", strBusinessType));
                    parmeters.Add(new KeyValuePair<string, object>("DBConnectionString", ""));//txtConnectionString.Text));
                    parmeters.Add(new KeyValuePair<string, object>("SelectSql", ""));// txtSelectSql.Text));
                    parmeters.Add(new KeyValuePair<string, object>("User", Page.User.Identity.Name));
                    parmeters.Add(new KeyValuePair<string, object>("domain", strDomain));
                    parmeters.Add(new KeyValuePair<string, object>("SecondaryBusinessTypes", strSecondaryBTypes));
                    parmeters.Add(new KeyValuePair<string, object>("DisplayName", txtDisplayName.Text));
                    parmeters.Add(new KeyValuePair<string, object>("ApiId", apiid));
                    parmeters.Add(new KeyValuePair<string, object>("Stage", 6)); //(CurGSTStatus == Services.GSTStatus.Verified ? 1 : 2)));

                    string sql = "CreateStore";
                    int result = (int)DataService.ExecuteScalar(sql, parmeters: parmeters, isSP: true);
                    if (result == -2)
                    {
                        lblMessage.Text = "Error!! Store name already exists.";
                        ShowFailure("Store creation failed", "Failure!! Store name already exists. Please try with a different store name.");
                        return;
                    }
                    
                    if (result <=0)
                    {
                        lblMessage.Text = "Error!! Store creation failed.";
                        ShowFailure("Store creation failed", "Failure!! Sorry, there is a technical challenge in the store creation. Please try again later or contact support for more information.");
                        return;
                    }

                    tenantId = result;
                    try
                    {
                        Finascop.Services.StoreService.StoreGroupCreate(txtDisplayName.Text, this.CurrentUser.Phone, storegroupId, guid);
                    }
                    catch(Exception ex) 
                    { 
                    
                    }
                }



                if (gstResult == null)
                {
                    lblMessage.Text = "Partial success!! Business information is created but store is pending because of missing GST data.";
                    string failurecontent = $"<p class=\"mg-b-5\">Your business information has been created. However store creation is not completed because of missing GSTIIN. You can go to the store settings page and click on the GST link add GST and then complete the remaining store creation in the store settings page.</p>";
                    ShowSuccess("Partially completed!!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Create account is partially completed!</a></h5>" + failurecontent);
                    return;

                }

                try
                {
                    string sqlGST = "UPDATE GST SET tenantid=@tenantid where id=@gstid and userid=@userid and tenantid is null";
                    List <KeyValuePair<string, object>> prmsgst = new List<KeyValuePair<string, object>>();
                    prmsgst.Add(new KeyValuePair<string, object>("gstid", gstid));
                    prmsgst.Add(new KeyValuePair<string, object>("userid", this.CurrentUser.Id));
                    prmsgst.Add(new KeyValuePair<string, object>("tenantid", tenantId));
                    DataService.ExecuteSql(sqlGST, parmeters: prmsgst);
                }
                catch {
                    gstid = -1;
                }

                if (gstid <= 0)
                {
                    lblMessage.Text = "Partial success!! Business information is created but store is pending because of missing GST data.";
                    string failurecontent = $"<p class=\"mg-b-5\">Your business information has been created. However store creation is not completed because of missing GSTIIN. You can go to the store settings page and click on the GST link add GST and then complete the remaining store creation in the store settings page.</p>";
                    ShowSuccess("Partially completed!!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Create account is partially completed!</a></h5>" + failurecontent);
                    return;
                }

                var storebranch = Services.StoreService.CreateStore(txtAddr1.Text, strBrShort, storegroupId, txtAddr2.Text, "", "", selDistrict.SelectedItem.Text, Convert.ToInt32(selState.Text), Convert.ToInt32(selDistrict.Text), txtPinCode.Text, Page.User.Identity.Name, this.CurrentUser.Phone, hidLat.Value, hidLong.Value, this.CurrentUser.FullName, gst);
                int branchId = storebranch;

                if(branchId <= 0)
                {
                    lblMessage.Text = "Partial success!! Business information is created but store settings is pending.";
                    string failurecontent = $"<p class=\"mg-b-5\">Your business information has been created. However store creation is not completed. You can go to the store settings page and click on the view stores list to view the store created or use the add button to complete the remaining store creation.</p>";
                    ShowSuccess("Partially completed!!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Store is partially completed!</a></h5>" + failurecontent);
                    return;

                }

                try
                {
                    List<KeyValuePair<string, object>> brParmeters = new List<KeyValuePair<string, object>>();
                    brParmeters.Add(new KeyValuePair<string, object>("StoreId", tenantId));
                    brParmeters.Add(new KeyValuePair<string, object>("Addr", txtAddr2.Text));
                    brParmeters.Add(new KeyValuePair<string, object>("District", selDistrict.SelectedItem.Text));
                    brParmeters.Add(new KeyValuePair<string, object>("Lang", hidLong.Value));
                    brParmeters.Add(new KeyValuePair<string, object>("Lat", hidLat.Value));
                    brParmeters.Add(new KeyValuePair<string, object>("Location", txtAddr1.Text));
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
                    lblMessage.Text = "Partial success!! Business information is created but store settings is pending.";
                    string failurecontent = $"<p class=\"mg-b-5\">Your business information has been created. However store creation is not completed. You can go to the store settings page and click on the view stores list to view the store created or use the add button to complete the remaining store creation.</p>";
                    ShowSuccess("Partially completed!!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Store is partially completed!</a></h5>" + failurecontent);
                    return;
                }

                var stores = Core.Services.APIService.GetStores(storegroupId);
                if (stores != null && stores.Any(s => s.BranchId == branchId))
                {
                    var store = stores.FirstOrDefault(s => s.BranchId == branchId);
                    APIStore = store;
                    APIBranchId = branchId;
                }

                Reset();
                //if (!IsBranchView)
                //{
                //curUser.StoreGroupId = tenantId;
                //curUser.StoreGroupName = txtStoreName.Text;
                //curUser.TenantStage = 6;
                //this.CurrentUser = curUser;
                //}
                Service.UserService.CachedDefaultUser = null;

                btnAdd.Enabled = false;
                string strcontent = $"<p class=\"mg-b-5\">Store: {txtAddr1.Text}, Address: {txtAddr2.Text}<br/>GSTIN: {gstResult.GSTIN}</p>";
                //ShowSuccess("Store Created Successfully!!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Your store has been created successfully!</a></h5>" + strcontent);
                //Service.UserService.CachedDefaultUser = null;
                Response.Redirect("/InventoryMapping");
            }

        }

        /// <summary>
        /// Edit Tenant / Business Info
        /// </summary>
        /// <returns></returns>
        private void EditBusinessInfo()
        {
            int primaryBusinessType = -1;
            try { primaryBusinessType = Convert.ToInt32(selBusinessTypes.Text); }
            catch (Exception ex)
            {
                primaryBusinessType = -1;
                lblMessage.Text = "Primary business type is required";
                ShowFailure("Missing input field", "Missing primary business Type. There is a problem with the primary business type. Error: " + ex.Message);
                return;
            }
            if (primaryBusinessType <= 0)
            {
                lblMessage.Text = "Primary business type is required.";
                ShowFailure("Missing input field", "Missing primary business Type. There is a problem with the primary business type.");
                return;
            }

            string secondaryBTypes = "";
            if (!IsBranchView)
            {
                string strBusinessTypes = selBusinessTypes.SelectedItem.Text;
                foreach (ListItem item in lstBusinessTypes.Items)
                    if (item.Selected)
                        secondaryBTypes += (String.IsNullOrWhiteSpace(secondaryBTypes) ? "" : ",") + item.Text;

                List<KeyValuePair<String, Object>> parmeters = new List<KeyValuePair<string, object>>();
                string strLogo = Guid.NewGuid().ToString();
                parmeters.Add(new KeyValuePair<string, object>("StoreGroupId", this.CurrentUser.StoreGroupId));
                parmeters.Add(new KeyValuePair<string, object>("BusinessType", strBusinessTypes));
                parmeters.Add(new KeyValuePair<string, object>("User", Page.User.Identity.Name));
                parmeters.Add(new KeyValuePair<string, object>("SecondaryBusinessTypes", secondaryBTypes));
                parmeters.Add(new KeyValuePair<string, object>("Displayname", txtDisplayName.Text));
                string sql = "UPDATE Store SET BusinessType=@BusinessType, Displayname=@Displayname, SecondaryBusinessTypes=@SecondaryBusinessTypes WHERE TenantId=@StoreGroupId;";
                int strresult = DataService.ExecuteSql(sql, parmeters: parmeters);
                List<int> secondaryBTypeIds = new List<int>();
                try
                {
                    foreach (ListItem item in lstBusinessTypes.Items)
                    if (item.Selected)
                    {
                        int secBType = Convert.ToInt32(item.Value);
                        if (secBType == primaryBusinessType)
                            continue;
                        secondaryBTypeIds.Add(secBType);
                    }
                    Services.StoreService.AddBusinessTypes(this.CurrentUser.APIStoreId, primaryBusinessType, secondaryBTypeIds);
                }
                catch { }

            }

            Reset();
            btnAdd.Enabled = false;
            string strcontent = $"<p class=\"mg-b-5\">Store: {txtAddr1.Text}, Address: {txtAddr2.Text}<br/>Business Types: {selBusinessTypes.SelectedItem.Text} {secondaryBTypes}</p>";
            //ShowSuccess("Business Info Edited Successfully!!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Business data has been modified successfully!</a></h5>" + strcontent);
            Response.Redirect("/InventoryMapping");

        }

        private void Reset()
        {
            return;


            txtStoreName.Text = "";
            txtColor.Text = "";
            lblMessage.Text = "";
            //txtCustomDomain.Text = "";
            imgLogo.Visible = false;
            imgLogoWhite.Visible = false;
            chkDelImgLogo.Visible = false;
            chkDelImgLogoWhite.Visible = false;
            selDistrict.Text = "";
            selState.Text = "";
            hidLat.Value = "";
            hidLong.Value = "";
            txtLocation.Text = "";
            txtAddr1.Text = "";
            txtAddr2.Text = "";
            txtPinCode.Text = "";
            txtDisplayName.Text = "";
            txtBankAcNo.Text = "";
            //txtBankBranch.Text = "";
            //txtBankName.Text = "";
            txtBPan.Text = "";
            txtIFSC.Text = "";
            txtGSTNo.Text = "";
            lblCustomDomain.Text = System.Configuration.ConfigurationManager.AppSettings.Get("newsitedomain");
        }

        protected void btnReset_Click(object sender, EventArgs e)
        {
            Reset();
            //pnlAddForm.Visible = false;
            //pnlStoresList.Visible = true;
        }

        protected void ISFCSearch_Click(object sender, EventArgs e)
        {
            if (String.IsNullOrEmpty(txtIFSC.Text))
            {
                ltrIFSCSearch.Text = "Please enter IFSC";
                return;
            }
            var bankInfo = APIService.BankInfoFromIFSC(txtIFSC.Text);
            if (bankInfo == null)
            {
                ltrIFSCSearch.Text = "Invalid IFSC.";
                return;
            }
            ltrIFSCSearch.Text = $"{bankInfo.Bank}, {bankInfo.Branch}, {bankInfo.Address}";

        }

        protected void btnReset_Click1(object sender, EventArgs e)
        {
            //if (btnReset.Text == "Cancel")
            //    ParentButtonBinding(0);
        }

        protected void selBusinessTypes_DataBound(object sender, EventArgs e)
        {
            if (selBusinessTypes.Items.Count > 0)
            {
                string strKey = selBusinessTypes.Attributes["DefaultBType"];
                if (!String.IsNullOrEmpty(strKey) && selBusinessTypes.Items.FindByText(strKey) != null)
                    selBusinessTypes.Text = (selBusinessTypes.Items.FindByText(strKey).Value);
            }
        }

        protected void lstBusinessTypes_DataBound(object sender, EventArgs e)
        {
            if (lstBusinessTypes.Items.Count > 0)
            {
                string strKey = lstBusinessTypes.Attributes["DefaultBType"];
                if (!String.IsNullOrEmpty(strKey))
                {
                    string[] strbtypes = strKey.Trim().Split(',');
                    if (strbtypes.Length > 0)
                    {
                        foreach (string btype in strbtypes)
                            if (!String.IsNullOrEmpty(btype.Trim()) && lstBusinessTypes.Items.FindByText(btype.Trim()) != null)
                                lstBusinessTypes.Items.FindByText(btype.Trim()).Selected = true;
                    }
                    //selBusinessTypes.Text = (selBusinessTypes.Items.FindByText(strKey).Value);
                }
            }
        }

        protected void selState_DataBound(object sender, EventArgs e)
        {
            if (selState.Items.Count > 0)
            {
                string strKey = selState.Attributes["DefaultState"];
                if (!String.IsNullOrEmpty(strKey) && selState.Items.FindByText(strKey) != null)
                    selState.Text = (selState.Items.FindByText(strKey).Value);
            }
        }

        protected void selDistrict_DataBound(object sender, EventArgs e)
        {
            if (selDistrict.Items.Count > 0)
            {
                string strKey = selDistrict.Attributes["DefaultDistrict"];
                if (!String.IsNullOrEmpty(strKey) && selDistrict.Items.FindByText(strKey) != null)
                    selDistrict.Text = (selDistrict.Items.FindByText(strKey).Value);
            }

            selDistrict.Items.Insert(0, new ListItem("Select District", ""));
            if (selDistrict.Items.Count > 1 && !String.IsNullOrEmpty(hidDistrict.Value) && selDistrict.Text != hidDistrict.Value && selDistrict.Items.FindByText(hidDistrict.Value) != null)
                selDistrict.SelectedValue = selDistrict.Items.FindByText(hidDistrict.Value).Value; //selState.Items.FindByText(strState).Value;

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

        protected void selState_SelectedIndexChanged(object sender, EventArgs e)
        {
            selDistrict.DataBind();
        }
    }
}