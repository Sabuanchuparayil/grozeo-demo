using RetalineProAgent.Controls.StoreSettings;
using RetalineProAgent.Controls;
using RetalineProAgent.Core.BussinessModel.Store;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Core.Services.Cache;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Runtime.CompilerServices;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using static System.Windows.Forms.VisualStyles.VisualStyleElement.TreeView;

namespace RetalineProAgent
{
    public partial class ManageBusinessSettings : Base.BasePartnerPage
    {
        private bool IsCheckoutEnabled
        {
            get
            {
                if (ViewState["CHECKOUTENABLED"] != null)
                    return Convert.ToBoolean(ViewState["CHECKOUTENABLED"]);

                return false;
            }
            set { ViewState["CHECKOUTENABLED"] = value; }
        }
        private int? TenantId
            {
                get
                {
                    return (int?)ViewState["TENANTID"];
                }
                set
                {
                    ViewState["TENANTID"] = value;
                }
            }
            private int? StoreId
            {
                get
                {
                    return (int?)ViewState["STOREID"];
                }
                set
                {
                    ViewState["STOREID"] = value;
                }
            }
            public string GSTLabel
            {
                get
                {
                    return (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT");
                }
            }

            public string CodeLabel
            {
                get
                {
                    return (ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "PIN Code" : "Post Code");
                }
            }
            public List<Store> _myStores = null;
            public DataTable dtMystores = null;

            public DataTable TblMyStores
            {
                get
                {
                    if (dtMystores == null)
                    {
                        var dv = (DataView)SDSBranches.Select(DataSourceSelectArguments.Empty); //DataService.GetDataTable($"SELECT * FROM StoreBranch WHERE StoreId={this.CurrentUser.StoreGroupId}");
                        dtMystores = dv.ToTable();
                    }
                    return dtMystores;
                }
            }
            private int EditStoreId
            {
                get
                {
                    return (int)ViewState["EDITBRID"];
                }
                set
                {
                    ViewState["EDITBRID"] = value;
                }
            }
            private int EditAPIStoreId
            {
                get
                {
                    return (int)ViewState["EDITAPIBRID"];
                }
                set
                {
                    ViewState["EDITAPIBRID"] = value;
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
        
        public string GSTIN(int storeid)
            {
                DataTable dt = TblMyStores;
                if (dt != null && dt.Rows.Count > 0)
                {
                    var rows = dt.Select("Id= " + storeid);
                    if (rows != null && rows.Count() > 0)
                        return rows[0]["gstin"].ToString();
                }
                return "Empty";
            }
            public string BankAccount(int storeid)
            {
                DataTable dt = TblMyStores;
                if (dt != null && dt.Rows.Count > 0)
                {
                    var rows = dt.Select("Id= " + storeid);
                    if (rows != null && rows.Count() > 0)
                        return String.Format("{0} {1}", rows[0]["BankName"], rows[0]["Branch"]);
                }
                return "Empty";
            }

        protected void Page_Load(object sender, EventArgs e)
        {
            // Event Binding
            PopupUpgradeConsent1.ParentButtonBinding += new Controls.PopupUpgradeConsent.ParentCustomHandler(UpdateEvent);
            ((Tenant.TenantMaster)Master).MasterEventBinding += new Tenant.TenantMaster.MasterCustomHandler(UpgradeMasterEvent);
            ((Tenant.TenantMaster)Master).ShowServerButtonUpgrade = true;

            // Address map client binding
            ctrlAddressMap1.ParentLocationClientId = hidMapAddr.ClientID; // txtLocation.ClientID;
            ctrlAddressMap1.ParentLatClientId = hidLat.ClientID;
            ctrlAddressMap1.ParentLongClientId = hidLong.ClientID;
            ctrlAddressMap1.ParentPinClientId = txtPinCode.ClientID;
            ctrlAddressMap1.ParentAddr2ClientId = txtAddr3.ClientID;
            ctrlAddressMap1.ParentAddr3ClientId = txtAddr4.ClientID;

            //if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
            //{
            //    if (!String.IsNullOrEmpty(hidMapAddr.Value))
            //        txtAddr2.Text = hidMapAddr.Value;
            //}
            if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
            {
                ctrlAddressMap1.ParentLocationNameClientId = txtAddr2.ClientID;
            }

            ctrlAddressMap1.ParentAddrClientId = txtAddr2.ClientID;
            ctrlAddressMap1.ParentDistrictClientId = hidDistrict.ClientID;
            ctrlAddressMap1.ParentStateClientId = hidState.ClientID;
            if (!String.IsNullOrEmpty(hidMapAddr.Value))
                txtLocation.Text = hidMapAddr.Value;
            rfvstate.ErrorMessage = RetalineProAgent.Service.Common.StateLabel + " is required";
            rfvdistrict.ErrorMessage = RetalineProAgent.Service.Common.DistrictLabel + " is required";

            // Logic for the page load
            if (!IsPostBack)
            {
                PopupUpgradeConsent1.ParentButtonBinding += new Controls.PopupUpgradeConsent.ParentCustomHandler(UpdateEvent);
                var master = (Tenant.TenantMaster)Master;
                master.MasterEventBinding += new Tenant.TenantMaster.MasterCustomHandler(UpgradeMasterEvent);
                master.ShowServerButtonUpgrade = true;
                master.TitleContent = "Upgrade Your Package to Add More Stores";
                master.BodyContent1 = "Your current package doesn't support adding additional stores. To unlock this feature, you'll need to upgrade your subscription. " +
                        "By upgrading, you'll gain access to advanced features and the ability to expand your business seamlessly.";
                master.BodyContent2 = "Click the Upgrade button below to visit the subscriptions page, where you can explore and select a package that fits your needs. Once upgraded, you can return here and continue adding your new store without any interruptions.";

                rqdpincode.Enabled = ConfigurationManager.AppSettings["CountryCode"] != "AE";
                EditStoreId = -1;
                EditAPIStoreId = -1;

                // Initial setup
                LoadInput();
                rqdpincode.Enabled = ConfigurationManager.AppSettings["CountryCode"] != "AE";
                EditStoreId = -1;
                EditAPIStoreId = -1;

                // Country-specific logic
                //if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
                //{
                //    if (!String.IsNullOrEmpty(hidMapAddr.Value))
                //        txtAddr2.Text = hidMapAddr.Value;
                //}
                if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
                {
                    ctrlAddressMap1.ParentLocationNameClientId = txtAddr1.ClientID;
                }

                // Show sponsored content logic
                lstSelectedTypes.DataBind();
                if (this.CurrentUser.PackageId > 1)
                {
                    showSponsored.Visible = true;
                }
                else
                {
                    showSponsored.Visible = false;
                }
            }
        }

        public void LoadInput()
        {

            int storegroupid = this.CurrentUser.StoreGroupId;
            string strbranchId = "";
            if (storegroupid > 0)
            {

                string strSql = $"SELECT a.Id, a.Name, a.CanCheckout, a.OnlinePaymentEnabled, st.APIBranchId, a.PODEnabled, a.ShowPWA, a.Status, a.StoreId as StoreGroupId, " +
                    $"Stuff((SELECT ',' + (t.HostAddress) FROM Host t WHERE a.Id LIKE t.TenantId FOR Xml Path('')), 1, 1, '') as hosts, " +
                    $"s.APICode, s.BusinessType, s.SecondaryBusinessTypes, s.DisplayName, s.CreatedBy, s.CreatedOn, s.GroupId, s.Id as StoreId, " +
                    $"s.InventoryFile, s.MinMargin, s.Name as StoreName, s.Package, s.SelectSql, s.UpdatedOn, s.UpdatedBy, s.InventoryMapType, " +
                    $" s.StoreAddress, s.StoreEmail, s.StorePhone, s.StoreContactName, st.Addr, st.District, st.State, s.SM_FB, s.SM_Twiter, s.SM_Insta, s.SM_WP, s.SM_Other " +
                    $" FROM AppTenant a inner join Store s on a.Id=s.Tenantid inner join StoreBranch st on s.Tenantid=st.StoreId WHERE a.Id={storegroupid}";

                if (!(Page.User.IsInRole("SuperAdmin") || Page.User.IsInRole("RetalineProAgent")))
                    strSql += " AND a.Id in (SELECT m.StoreGroupId FROM User_UserRole_Mapping m INNER JOIN [User] u on u.Id=m.UserId WHERE u.Email like @user)";
                List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                prms.Add(new KeyValuePair<string, object>("user", Page.User.Identity.Name));
                DataTable dt = DataService.GetDataTable(strSql, parmeters: prms);
                if (dt.Rows.Count > 0)
                {
                    DataRow dr = dt.Rows[0];
                    strbranchId = dr["APIBranchId"].ToString();
                    txtContactName.Text = dr["StoreContactName"].ToString();
                    txtContactEmail.Text = dr["StoreEmail"].ToString();
                    txtContactPhone.Text = dr["StorePhone"].ToString();
                    txtAddr.Text = dr["Addr"].ToString() + ',' + ' ' + dr["District"].ToString() + ',' + ' ' + dr["State"].ToString();
                    //txtStoreName.Text = dr["Name"].ToString();
                    //txtStoreName.Enabled = false;
                    txtDisplayName.Text = dr["DisplayName"].ToString();

                    bool checkoutEnabled = false, onlinePaymentEnabled = false, PODEnabled = false;
                    try { checkoutEnabled = Convert.ToBoolean(dr["CanCheckout"]); } catch { checkoutEnabled = false; }
                    try { onlinePaymentEnabled = Convert.ToBoolean(dr["OnlinePaymentEnabled"]); } catch { onlinePaymentEnabled = false; }
                    try { PODEnabled = Convert.ToBoolean(dr["PODEnabled"]); } catch { PODEnabled = false; }

                    rbCheckoutEnabled.Checked = checkoutEnabled;
                    rbPayOnlineEnabled.Checked = rbCheckoutEnabled.Checked && onlinePaymentEnabled;
                    rbPODEnabled.Checked = rbCheckoutEnabled.Checked && PODEnabled;
                    IsCheckoutEnabled = checkoutEnabled;

                    rbCheckoutDisabled.Checked = !rbCheckoutEnabled.Checked;
                    rbPayOnlineDisabled.Checked = !rbPayOnlineEnabled.Checked;
                    rbPODDisabled.Checked = !rbPODEnabled.Checked;

                    var merchantDatas = Services.StoreService.MerchantPendingActions(0, this.CurrentUser.APIStoreId);
                    bool hasPendingAction = false;
                    if (merchantDatas != null) { hasPendingAction = merchantDatas.PendingActions?.Count > 0; }
                    if (hasPendingAction && !rbCheckoutEnabled.Checked)
                    {
                        rbCheckoutEnabled.Enabled = false;
                        rbCheckoutDisabled.Enabled = false;
                    }

                    if (!rbCheckoutEnabled.Checked)
                    {
                        rbPayOnlineEnabled.Enabled = false;
                        rbPayOnlineDisabled.Enabled = false;
                        rbPODEnabled.Enabled = false;
                        rbPODDisabled.Enabled = false;
                    }

                    List<KeyValuePair<string, object>> storeparams = new List<KeyValuePair<string, object>>();
                    storeparams.Add(new KeyValuePair<string, object>("storeId", this.CurrentUser.APIStoreId));

                    var dtStores = DataServiceMySql.GetDataTable("SELECT COUNT(br_Name) FROM finascop_branch WHERE br_storeGroup = @storeId", UserService.GetAPIConnectionString(), storeparams);

                    if (dtStores != null && dtStores.Rows.Count > 0)
                    {

                        DataRow drs = dtStores.Rows[0];
                        string strCountqry = drs["COUNT(br_Name)"].ToString();
                        if (Convert.ToInt32(strCountqry) > 1)
                        {
                            lblStoreCount.Visible = true;
                            lblStoreCount.Text = "view all " + strCountqry + " stores";
                        }
                    }
                          
                }
                string roName = "", roId = "", areaId = "", cpId = "", referrerType = "", cpName = "", areaName = "";
                int relationshipofficerId = 0, consultingpartnerId = 0, roAreaId = 0;
                List<KeyValuePair<string, object>> dataparams = new List<KeyValuePair<string, object>>();
                dataparams.Add(new KeyValuePair<string, object>("storeId", this.CurrentUser.APIStoreId));
                dataparams.Add(new KeyValuePair<string, object>("branchId", strbranchId));

                if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
                {
                    rbPODEnabled.Checked = rbPODDisabled.Checked = rbPODEnabled.Enabled = rbPODDisabled.Enabled = false;
                }


            }
        }

        protected void Page_PreRender(object sender, EventArgs e)
        {
                //go1.Value = go2.Value = go3.Value = go4.Value = "";

                plcStoreList.Visible = (String.IsNullOrEmpty(CurViewType) || CurViewType == "1");
                if (Page.User.IsInRole("SuperAdmin") || Page.User.IsInRole("RetalineProAgent") || Page.User.IsInRole("Agent") || Page.User.IsInRole("StoreAdmin"))
                    plcStoreSettings.Visible = (CurViewType == "2");
                else
                    plcStoreSettings.Visible = btnAddStore.Visible = false;

                if (selDistrict.Items.Count > 1 && !String.IsNullOrEmpty(hidDistrict.Value) && selDistrict.Text != hidDistrict.Value && selDistrict.Items.FindByText(hidDistrict.Value) != null)
                    selDistrict.SelectedValue = selDistrict.Items.FindByText(hidDistrict.Value).Value; //selState.Items.FindByText(strState).Value;

                hidDistrict.Value = "";
                hidMapAddr.Value = "";

               
                PopupUpgradeConsent1.Visible = this.CurrentUser.PackageId >= 2 && rptBranches.Items.Count >= 3;
                if (PopupUpgradeConsent1.Visible)
                {
                    btnAddStore.OnClientClick = "$('#modalupgradeconsent').modal('show'); return false;";
                }
                if (this.CurrentUser.PackageId > 1)
                {
                    List<KeyValuePair<string, object>> storeparams = new List<KeyValuePair<string, object>>();
                    storeparams.Add(new KeyValuePair<string, object>("storegroup", this.CurrentUser.APIStoreId));
                    var dtStoreGroup = DataServiceMySql.GetDataTable($"SELECT store_group_id, showSponsered FROM finascop_branch_group WHERE store_group_id = @storegroup", Service.UserService.GetAPIConnectionString(), storeparams);
                    string sponsoredStore = "";
                    if (dtStoreGroup != null && dtStoreGroup.Rows.Count > 0)
                    {
                        sponsoredStore = dtStoreGroup.Rows[0]["showSponsered"].ToString();
                    }
                    if (Convert.ToInt32(sponsoredStore) == 1)
                    {
                        rdEnabled.Checked = true;
                        rdDisabled.Checked = false;
                    }
                    else
                    {
                        rdEnabled.Checked = false;
                        rdDisabled.Checked = true;
                    }
                }
        }

            protected void ODSStore_Selecting(object sender, ObjectDataSourceSelectingEventArgs e)
            {
                e.InputParameters["storegroupid"] = this.CurrentUser.StoreGroupId;
                e.InputParameters["apistoregroupid"] = this.CurrentUser.APIStoreId;
                           
            }
        protected void rptBranches_ItemDataBound(object sender, RepeaterItemEventArgs e)
        {
            if (e.Item.ItemIndex > 2)
            {
                e.Item.Visible = false;
            }
            else
            {
                if (e.Item.ItemType == ListItemType.Item || e.Item.ItemType == ListItemType.AlternatingItem)
                {
                    RadioButton rdDefaultBrnach = (RadioButton)e.Item.FindControl("rdDefaultBrnach1");
                    Repeater rptTiming = (Repeater)e.Item.FindControl("rptTiming");
                    if (rptTiming != null)
                    {
                        rptTiming.DataSource = (StoreTime[])DataBinder.Eval(e.Item.DataItem, "OnOffTime");
                        rptTiming.DataBind();

                        Literal ltrNoTime = (Literal)e.Item.FindControl("ltrNoTiming");
                        if (ltrNoTime != null)
                            ltrNoTime.Visible = (rptTiming.Items.Count <= 0);
                    }
                }
            }
        }

            protected void chkStatus_CheckedChanged(object sender, EventArgs e)
            {
                CheckBox chbtn = (CheckBox)sender;
               
                if (chbtn != null && !String.IsNullOrEmpty(chbtn.Attributes["brid"]))
                {
                    int brid = Convert.ToInt32(chbtn.Attributes["brid"]);
                    int onlineStaus = (chbtn.Checked ? 1 : 0);
                    string strSql = "UPDATE finascop_branch SET br_SalesOnline=" + onlineStaus + " WHERE br_ID=" + brid + " and br_storeGroup=" + this.CurrentUser.APIStoreId + "";
                    DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString());
                }

                rptBranches.DataBind();
            }
            protected void SDSStore_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
            {
                if (e.Command.Parameters.Contains("@storeId"))
                    e.Command.Parameters["@storeId"].Value = this.CurrentUser.StoreGroupId;

            }

            protected void selState_DataBound(object sender, EventArgs e)
            {
                if (selState.Items.Count > 0)
                {
                    string strKey = selState.Attributes["DefaultState"];
                    if (!String.IsNullOrEmpty(strKey) && selState.Items.FindByText(strKey) != null)
                        selState.Text = (selState.Items.FindByText(strKey).Value);
                }
                selState.Items.Insert(0, new ListItem($"Select {RetalineProAgent.Service.Common.StateLabel}", ""));
            }
            protected void selDistrict_DataBound(object sender, EventArgs e)
            {
                if (selDistrict.Items.Count > 0)
                {
                    string strKey = selDistrict.Attributes["DefaultDistrict"];
                    if (!String.IsNullOrEmpty(strKey) && selDistrict.Items.FindByText(strKey) != null)
                        selDistrict.Text = (selDistrict.Items.FindByText(strKey).Value);
                }
                selDistrict.Items.Insert(0, new ListItem($"Select {RetalineProAgent.Service.Common.DistrictLabel}", ""));
                if (selDistrict.Items.Count > 1 && !String.IsNullOrEmpty(hidDistrict.Value) && selDistrict.Text != hidDistrict.Value && selDistrict.Items.FindByText(hidDistrict.Value) != null)
                    selDistrict.SelectedValue = selDistrict.Items.FindByText(hidDistrict.Value).Value; //selState.Items.FindByText(strState).Value;
            }

            protected void btnAdd_Click(object sender, EventArgs e)
            {
                Button btn = (Button)sender;

                int storegroupid = this.CurrentUser.StoreGroupId;
                //if (Page.IsValid)
                //{
                if (String.IsNullOrEmpty(hidLat.Value) || String.IsNullOrEmpty(hidLong.Value))
                {
                    lblMessage.Text = "Please select location in map. Click on the button 'Load Map' to search your location.";
                    ShowFailure("Validation failed", "Please select location in map. Click on the button 'Load Map' to search your location.");
                    return;
                }

                CreateStore();

                
            }
            private void UpgradeMasterEvent(int type)
            {
                onOffTime.Visible = false;
                Type cstype = this.GetType();
                ClientScriptManager cs = Page.ClientScript;
                StringBuilder cstext1 = new StringBuilder();

                if (rptBranches.Items.Count > 0 && this.CurrentUser.PackageId < 2)
                {
                    cstext1.Append("<script type=text/javascript> $('#modalupgrade').modal('show'); </");
                    cstext1.Append("script>");
                    cs.RegisterStartupScript(cstype, "Upgrade", cstext1.ToString());

                    return;
                }

                CurViewType = "2";
                EditStoreId = -1;
                btnEdit.Visible = false;
                btnAdd.Visible = true;
                ltrAddTitle.Text = "Add new store";

                String csname1 = "PopupMap";
                cstext1.Append("<script type=text/javascript> $('#ADDRESS').modal('show'); </");
                cstext1.Append("script>");

                cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
            }
            private void UpdateEvent(int type)
            {

                CurViewType = "2";
                EditStoreId = -1;
                btnEdit.Visible = false;
                btnAdd.Visible = true;
                ltrAddTitle.Text = "Add new store";

                Type cstype = this.GetType();
                ClientScriptManager cs = Page.ClientScript;
                StringBuilder cstext1 = new StringBuilder();
                String csname1 = "PopupMap";
                cstext1.Append("<script type=text/javascript> $('#ADDRESS').modal('show'); </");
                cstext1.Append("script>");

                cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());

            }
            protected void btnAddStore_Click(object sender, EventArgs e)
            {
                onOffTime.Visible = false;
                Type cstype = this.GetType();
                ClientScriptManager cs = Page.ClientScript;
                StringBuilder cstext1 = new StringBuilder();

                if (rptBranches.Items.Count > 0 && this.CurrentUser.PackageId < 2)
                {
                    cstext1.Append("<script type=text/javascript> $('#modalupgrade').modal('show'); </");
                    cstext1.Append("script>");
                    cs.RegisterStartupScript(cstype, "Upgrade", cstext1.ToString());

                    return;
                }

                CurViewType = "2";
                EditStoreId = -1;
                btnEdit.Visible = false;
                btnAdd.Visible = true;
                ltrAddTitle.Text = "Add new store";

                String csname1 = "PopupMap";
                cstext1.Append("<script type=text/javascript> $('#ADDRESS').modal('show'); </");
                cstext1.Append("script>");

                cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());

            }

            protected void lbtnEditStore_Click(object sender, EventArgs e)
            {
                onOffTime.Visible = true;
                LinkButton lbtn = (LinkButton)sender;
                if (lbtn != null && !String.IsNullOrEmpty(lbtn.Attributes["brid"]))
                {
                    int storeid = Convert.ToInt32(lbtn.Attributes["brid"]);
                    if (storeid <= 0)
                        return;

                    CurViewType = "2";
                    EditStoreId = storeid;
                    btnEdit.Visible = true;
                    btnAdd.Visible = false;
                    LoadInput(EditStoreId);

                    SDSOnOffTime.SelectParameters["brid"].DefaultValue = EditAPIStoreId.ToString();
                    SDSOnOffTime.Select(DataSourceSelectArguments.Empty);
                    gvOnOffTime.DataBind();

                }

            }
            protected void lblDelivRule_Click(object sender, EventArgs e)
            {
                LinkButton lbtn = (LinkButton)sender;
                if (lbtn != null && !String.IsNullOrEmpty(lbtn.Attributes["brid"]))
                {
                    int storeid = Convert.ToInt32(lbtn.Attributes["brid"]);
                    if (storeid <= 0)
                        return;
                    EditStoreId = storeid;
                    CurViewType = "3";
                }

            }

            private void ClearInput()
            {
                txtAddr2.Text = "";
                hidLong.Value = "";
                hidLat.Value = "";
                ctrlAddressMap1.Lat = "";
                ctrlAddressMap1.Lng = "";
                txtAddr1.Text = "";
                txtPinCode.Text = "";

                if (selState.Items.Count <= 1)
                    selState.DataBind();
                selState.ClearSelection();

                if (selDistrict.Items.Count <= 1)
                    selDistrict.DataBind();
                selDistrict.ClearSelection();
            }
            private void LoadInput(int branchId)
            {

                int storegroupid = this.CurrentUser.StoreGroupId;
                if (branchId > 0)
                {
                    DataTable dtBranch = DataService.GetDataTable($"SELECT * FROM StoreBranch br WHERE br.Id= {branchId} and br.StoreId = {storegroupid}");
                    if (dtBranch != null && dtBranch.Rows.Count > 0)
                    {
                        DataRow dr = dtBranch.Rows[0];
                        // EditAPIStoreId
                        // APIBranchId
                        try { EditAPIStoreId = Convert.ToInt32(dr["APIBranchId"]); } catch { EditAPIStoreId = -1; }
                        //txtAddr2.Text = dr["Addr"].ToString();
                        string strDist = dr["District"].ToString();
                        hidLong.Value = dr["Lang"].ToString();
                        hidLat.Value = dr["Lat"].ToString();
                        ctrlAddressMap1.Lat = hidLat.Value;
                        ctrlAddressMap1.Lng = hidLong.Value;
                        txtAddr1.Text = dr["Location"].ToString();
                        txtPinCode.Text = dr["Pin"].ToString();
                        string strState = dr["State"].ToString();

                        ltrAddTitle.Text = "Edit Store - " + txtAddr1.Text;

                        if (selState.Items.Count <= 1)
                            selState.DataBind();

                        selState.ClearSelection();
                        if (!String.IsNullOrEmpty(strState) && selState.Items.FindByText(strState) != null)
                            selState.SelectedValue = selState.Items.FindByText(strState).Value;

                        //if (selDistrict.Items.Count <= 1)
                        selDistrict.DataBind();
                        selDistrict.ClearSelection();
                        if (!String.IsNullOrEmpty(strDist) && selDistrict.Items.FindByText(strDist) != null)
                            selDistrict.SelectedValue = selDistrict.Items.FindByText(strDist).Value;
                                              
                    }
                    var selectparams = new List<KeyValuePair<string, object>>();
                    selectparams.Add(new KeyValuePair<string, object>("branchid", EditAPIStoreId));
                    selectparams.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));
                    DataTable dt = DataServiceMySql.GetDataTable($"SELECT br_ID, br_Email, br_Phone, br_Incharge,br_directDelivery,br_courierDelivery, br_Address, br_Address2, br_Address3 FROM finascop_branch WHERE br_ID=@branchid AND br_storeGroup=@storegroupid", UserService.GetAPIConnectionString(), selectparams);
                    if (dt != null && dt.Rows.Count > 0)
                    {
                        DataRow dr = dt.Rows[0];
                        txtCnName.Text = dr["br_Incharge"].ToString();
                        txtCnNo.Text = dr["br_Phone"].ToString();
                        txtCnEmail.Text = dr["br_Email"].ToString();
                        cbexpressdelivery.Checked = dr["br_directDelivery"].ToString() == "1";
                        cbcourierdelivery.Checked = dr["br_courierDelivery"].ToString() == "1";

                        txtAddr2.Text = dr["br_Address"].ToString();
                        txtAddr3.Text = dr["br_Address2"].ToString();
                        txtAddr4.Text = dr["br_Address3"].ToString();

                }

            }
                GetRODetails(EditAPIStoreId);
            }

            private void CreateStore()
            {
                if (rptBranches.Items.Count > 0 && this.CurrentUser.PackageId < 2)
                {
                    Type cstype = this.GetType();
                    ClientScriptManager cs = Page.ClientScript;
                    StringBuilder cstext1 = new StringBuilder();
                    cstext1.Append("<script type=text/javascript> $('#modalupgrade').modal('show'); </");
                    cstext1.Append("script>");
                    cs.RegisterStartupScript(cstype, "Upgrade", cstext1.ToString());

                    return;
                }

                CurViewType = "2";
                EditStoreId = -1;


                //if (Page.IsValid)
                //{
                Service.User curUser = this.CurrentUser;// .GetCustomerByUsername(Page.User.Identity.Name);
                //int storegroupId = curUser.APIStoreId;
                //int tenantId = curUser.StoreGroupId;
                int gstId = -1, bankId = -1; string gst = "";
                string vattype = ConfigurationManager.AppSettings.Get("VATType");
                string strVATText = (System.Configuration.ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT");

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
                               
                int tradeRestrictionType = (ConfigurationManager.AppSettings.Get("VATType") == "2" ? 1 : 0);
                int taxtype = (tradeRestrictionType == 1 ? 1 : 0);
               
                string storecontactperson = txtCnName.Text;
                string storephone = txtCnNo.Text;
                string storeEmail = "";
                if (txtCnEmail.Text == "")
                {
                    storeEmail = curUser.Email;
                }
                else
                {
                    storeEmail = txtCnEmail.Text;
                }
                int expressdelivery = cbexpressdelivery.Checked ? 1 : 0;
                int courierdelivery = cbcourierdelivery.Checked ? 1 : 0;
                try
                {
                    var storebranch = Services.StoreService.CreateStore(txtAddr1.Text, strBrShort, curUser.APIStoreId, txtAddr2.Text, txtAddr3.Text, txtAddr4.Text, selDistrict.SelectedItem.Text,
                        Convert.ToInt32(selState.Text), Convert.ToInt32(selDistrict.Text), txtPinCode.Text, storeEmail, storephone,
                        hidLat.Value, hidLong.Value, storecontactperson, gst, tradeRestriction: tradeRestrictionType, taxType: taxtype, directDelivery: expressdelivery, courierDelivery: courierdelivery);
                    int branchId = storebranch;
                    hdbranchId.Value = branchId.ToString();
                    UpdateRelationshipOfficerOrConsultingPartner(branchId.ToString(), this.CurrentUser.APIStoreId, hdAreaId.Value, hdnRoId.Value, hdnId.Value);


                    try
                    {
                        List<KeyValuePair<string, object>> brParmeters = new List<KeyValuePair<string, object>>();
                        brParmeters.Add(new KeyValuePair<string, object>("StoreId", curUser.StoreGroupId));
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
                        if (gstId > 0)
                            brParmeters.Add(new KeyValuePair<string, object>("gstid", gstId));
                        else
                            brParmeters.Add(new KeyValuePair<string, object>("gstid", DBNull.Value));

                        //brParmeters.Add(new KeyValuePair<string, object>("bankid", bankId));
                        if (bankId > 0)
                            brParmeters.Add(new KeyValuePair<string, object>("bankid", gstId));
                        else
                            brParmeters.Add(new KeyValuePair<string, object>("bankid", DBNull.Value));
                        DataService.ExecuteSP("CreateStoreBranch", parmeters: brParmeters);
                        //UpdateRelationshipOfficerOrConsultingPartner(branchId, curUser.StoreGroupId, roArea, roId, cpid);
                        // Activitylog
                        String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                        String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                        string Source = strUrl;
                        int storegroupid = this.CurrentUser.APIStoreId;
                        string User = this.CurrentUser.Email;
                        string StoreId = (curUser.StoreGroupId).ToString();
                        string Addr = txtAddr2.Text;
                        string District = selDistrict.SelectedItem.Text;
                        string Lang = hidLong.Value;
                        string Lat = hidLat.Value;
                        string Location = txtAddr1.Text;
                        string Pin = txtPinCode.Text;
                        string State = selState.SelectedItem.Text;
                        string MapLocation = txtLocation.Text;
                        string APIBranchId = branchId.ToString();
                        string IsDefaultBranch = "true";
                        var items = new[]
                            {
                              new { Key = "StoreId", Value = StoreId },
                              new { Key = "Address", Value = Addr },
                              new { Key = "District", Value = District },
                              new { Key = "Lang", Value = Lang },
                              new { Key = "Lat", Value = Lat },
                              new { Key = "Location", Value = Location },
                              new { Key = "Pin", Value = Pin },
                              new { Key = "State", Value = State },
                              new { Key = "MapLocation", Value = MapLocation },
                              new { Key = "APIBranchId", Value = APIBranchId },
                              new { Key = "IsDefaultBranch", Value = IsDefaultBranch },

                           };
                        string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                        var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
                        string strcontent = $"<p class=\"mg-b-5\">Store: {txtAddr1.Text}, Address: {txtAddr2.Text}<br/></p>";

                        if (this.CurrentUser.TenantType == 2)
                        {
                            bool canCheckout = true;
                            try { canCheckout = (ConfigurationManager.AppSettings.Get("CanCheckout") == "1" ? true : false); } catch { canCheckout = false; }
                            string sqlChangeType = "update AppTenant set TenantType=1, CanCheckout=@CanCheckout where id=@StoreId";
                            List<KeyValuePair<string, object>> typechangeparams = new List<KeyValuePair<string, object>>();
                            typechangeparams.Add(new KeyValuePair<string, object>("StoreId", curUser.StoreGroupId));
                            typechangeparams.Add(new KeyValuePair<string, object>("CanCheckout", canCheckout));
                            DataService.ExecuteSql(sqlChangeType, "", typechangeparams);
                            Service.UserService.CachedDefaultUser = null;
                                               
                        }

                        ShowSuccess("Store Created Successfully!!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Your store has been created successfully!</a></h5>" + strcontent);
                    }
                    catch (Exception ex2)
                    {
                        ShowFailure("Store creation failed", "Sorry, there is a technical error happened. Store creation is partially executed.<br/>Error: " + ex2.Message);
                        return;
                    }
                }
                catch (Exception ex)
                {
                    ShowFailure("Store creation failed", "Sorry, there is a technical error happened. Please contact support.<br/>Error: " + ex.Message);
                }
            }
        protected async void btnEditStore_Click(object sender, EventArgs e)
        {
            string strCheckoutMessage = "";
            bool canchekout = rbCheckoutEnabled.Checked;

            if (!IsCheckoutEnabled && canchekout)
            {
                var merchantDatas = Services.StoreService.MerchantPendingActions(0, this.CurrentUser.APIStoreId);
                bool hasPendingAction = false;
                if (merchantDatas != null) { try { hasPendingAction = merchantDatas.PendingActions?.Count > 0; } catch { hasPendingAction = false; } }
                if (hasPendingAction)
                {
                    canchekout = false;
                    strCheckoutMessage = "You cannot enable checkout because of it is linked to pending tasks awaiting completion. Kindly utilize the navigation button located at the top of the screen to ensure all pending tasks are addressed before proceeding.";
                }
            }
            if (canchekout == false)
            {
                diablesponesedproduct(this.CurrentUser.APIStoreId);
            }
            bool canPayOnline = canchekout && rbPayOnlineEnabled.Checked, canPOD = canchekout && rbPODEnabled.Checked;
            if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
            {
                rbPODEnabled.Checked = rbPODDisabled.Checked = rbPODEnabled.Enabled = rbPODDisabled.Enabled = false;
                canPOD = false;
            }

            if (canchekout && !canPayOnline && !canPOD)
            {
                canPayOnline = true;
                if (IsCheckoutEnabled)
                    strCheckoutMessage = "Both 'Pay online' and 'Pay on Delivery' cannot be disabled. Either disable checkout or the 'Pay Online' will be enabled by default";
            }

            List<KeyValuePair<String, Object>> parmeters = new List<KeyValuePair<string, object>>();
            string strLogo = Guid.NewGuid().ToString();
            parmeters.Add(new KeyValuePair<string, object>("StoreGroupId", this.CurrentUser.StoreGroupId));
            parmeters.Add(new KeyValuePair<string, object>("User", Page.User.Identity.Name));
            //parmeters.Add(new KeyValuePair<string, object>("SecondaryBusinessTypes", secondaryBTypes));
            parmeters.Add(new KeyValuePair<string, object>("Displayname", txtDisplayName.Text));
            parmeters.Add(new KeyValuePair<string, object>("contactname", txtContactName.Text));
            parmeters.Add(new KeyValuePair<string, object>("addr", txtAddr.Text));
            parmeters.Add(new KeyValuePair<string, object>("storeemail", txtContactEmail.Text));
            parmeters.Add(new KeyValuePair<string, object>("storephone", txtContactPhone.Text));

            parmeters.Add(new KeyValuePair<string, object>("cancheckout", canchekout));
            parmeters.Add(new KeyValuePair<string, object>("canPayOnline", canPayOnline));
            parmeters.Add(new KeyValuePair<string, object>("canPOD", canPOD));

            parmeters.Add(new KeyValuePair<string, object>("ApiStoreGroupId", this.CurrentUser.APIStoreId));

            //string sql = "UPDATE AppTenant SET [Name] = @Displayname WHERE Id= @StoreGroupId; UPDATE Store SET Displayname=@Displayname, SecondaryBusinessTypes=SecondaryBusinessTypes + ', ' + @SecondaryBusinessTypes, StoreContactName=@contactname, StoreAddress=@addr, StoreEmail=@storeemail, StorePhone=@storephone, UpdatedOn = getutcdate(), UpdatedBy=@User WHERE TenantId=@StoreGroupId; Update [User] set StoreGroupName = @Displayname where StoreGroupId= @StoreGroupId;";
            string sql = "UPDATE AppTenant SET [Name] = @Displayname, CanCheckout = @cancheckout, OnlinePaymentEnabled = @canPayOnline, PODEnabled = @canPOD WHERE Id= @StoreGroupId; " +
                "UPDATE Store SET Displayname=@Displayname, StoreContactName=@contactname, StoreAddress=@addr, StoreEmail=@storeemail, StorePhone=@storephone, " +
                "UpdatedOn = getutcdate(), UpdatedBy=@User  WHERE TenantId=@StoreGroupId; " +
                "Update [User] set StoreGroupName = @Displayname where StoreGroupId= @StoreGroupId;";
            int strresult = DataService.ExecuteSql(sql, parmeters: parmeters);



            // Activitylog
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            string Source = strUrl;
            int storegroupid = this.CurrentUser.APIStoreId;
            string Users = this.CurrentUser.Email;
            string user = Page.User.Identity.Name;
            string Displayname = txtDisplayName.Text;
            string contactname = txtContactName.Text;
            string Address = txtAddr.Text;
            string storeemail = txtContactEmail.Text;
            string storephone = txtContactPhone.Text;
            string cancheckout = canchekout.ToString();
            string canPayOnlines = canPayOnline.ToString();
            string canpod = canPOD.ToString();

            var items = new[]
                {
                             new { Key = "Users", Value =user  },
                             new { Key = "Display Name", Value = Displayname },
                             new { Key = "Contact Name", Value = contactname },
                             new { Key = "Address", Value = Address },
                             new { Key = "Store Email", Value = storeemail },
                             new { Key = "Store Phone", Value = storephone },
                             new { Key = "CanCheckOut", Value = cancheckout },
                             new { Key = "CanPayOnlines", Value = canPayOnlines },
                             new { Key = "CanPod", Value = canpod },
                             };
            string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
            var strresults = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);

            var cacheService = new RedisCacheService();
            string cachekey = $"Retl.AppTenant.host." + this.CurrentUser.PublicSiteUrl.ToLower();
            await cacheService.RemoveAsync(cachekey);
            
            string currentdatetime = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
            List<KeyValuePair<string, object>> storegrpparams = new List<KeyValuePair<string, object>>();
            if ((showSponsored.Visible == true && rdEnabled.Checked == true) || (showSponsored.Visible == true && rdDisabled.Checked == true))
            {
                int sponsoredPrd = 0;
                if (rdEnabled.Checked == true)
                {
                    sponsoredPrd = 1;
                }
                else
                {
                    sponsoredPrd = 0;
                }

                storegrpparams.Add(new KeyValuePair<string, object>("showSponsered", sponsoredPrd));
            }
            else
            {
                storegrpparams.Add(new KeyValuePair<string, object>("showSponsered", -1));
            }
            storegrpparams.Add(new KeyValuePair<string, object>("storeGroupId", this.CurrentUser.APIStoreId));
            storegrpparams.Add(new KeyValuePair<string, object>("store_group_name", txtDisplayName.Text));
            storegrpparams.Add(new KeyValuePair<string, object>("contactNumber", txtContactPhone.Text));
            storegrpparams.Add(new KeyValuePair<string, object>("currentdatetime", currentdatetime));

            string strUpdateSql = $"UPDATE finascop_branch_group SET showSponsered = CASE  WHEN @showSponsered > 0 THEN @showSponsered ELSE showSponsered END,store_group_name = @store_group_name,contactNumber = @contactNumber, updated_on = @currentdatetime WHERE store_group_id=@storeGroupId";
            int rowsupdated = DataServiceMySql.ExecuteSql(strUpdateSql, UserService.GetAPIConnectionString(), storegrpparams);


            Service.UserService.CachedDefaultUser = null;

            string strcontent = $"<p class=\"mg-b-5\">Store: {txtDisplayName.Text}, Contact Address: {txtAddr.Text}. {strCheckoutMessage}</p>"; // <br/>Business Types: {selBusinessTypes.SelectedItem.Text} {secondaryBTypes}
            if (!String.IsNullOrEmpty(strCheckoutMessage))
                strcontent += $"<p class=\"mg-b-5\"><strong>{strCheckoutMessage}</strong></p>";

            ShowSuccess("Business Info Edited Successfully!!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Business data has been updated successfully!</a></h5>" + strcontent);
        }
        private void EditStore(int storeId)
            {
                string vattype = ConfigurationManager.AppSettings.Get("VATType");
                string strVATText = (System.Configuration.ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT");
                int gstId = -1, bankId = -1;
                GetRODetails(storeId);
                Service.User curUser = this.CurrentUser;// .GetCustomerByUsername(Page.User.Identity.Name);
                
                List<KeyValuePair<string, object>> storeParams = new List<KeyValuePair<string, object>>();
                storeParams.Add(new KeyValuePair<string, object>("storegroupid", curUser.StoreGroupId));
                storeParams.Add(new KeyValuePair<string, object>("id", storeId));
                DataTable dtStore = DataService.GetDataTable("select * from StoreBranch where id=@id and StoreId=@storegroupid", parmeters: storeParams);
                if (dtStore == null || dtStore.Rows.Count <= 0 || dtStore.Rows[0]["APIBranchId"] == null || Convert.ToInt32(dtStore.Rows[0]["APIBranchId"]) <= 0)
                {
                    lblMessage.Text = "Process Failed. Invalid Store. Operation failed because of the selected store is invalid.";
                    ShowFailure("Store creation failed", "Invalid Store. Operation failed because of the selected store is invalid");
                    return;
                }
                
                // Limit branch for intra state trading only? (0: no restriction, 1: Intra state only)
                int tradeRestrictionType = (ConfigurationManager.AppSettings.Get("VATType") == "2" ? 1 : 0);
                int taxtype = (tradeRestrictionType == 1 ? 1 : 0);
                
                string storecontactperson = txtCnName.Text;
                string storephone = txtCnNo.Text;
                string storeEmail = "";
                if (txtCnEmail.Text == "")
                {
                    storeEmail = curUser.Email;
                }
                else
                {
                    storeEmail = txtCnEmail.Text;
                }
                    int expressdelivery = cbexpressdelivery.Checked ? 1 : 0;
                    int courierdelivery = cbcourierdelivery.Checked ? 1 : 0;
                try
                {
                    
                    try
                    {
                        List<KeyValuePair<string, object>> brParmeters = new List<KeyValuePair<string, object>>();
                        brParmeters.Add(new KeyValuePair<string, object>("BranchId", storeId));
                        brParmeters.Add(new KeyValuePair<string, object>("StoreId", curUser.StoreGroupId));
                        brParmeters.Add(new KeyValuePair<string, object>("Addr", txtAddr2.Text));
                        brParmeters.Add(new KeyValuePair<string, object>("District", selDistrict.SelectedItem.Text));
                        brParmeters.Add(new KeyValuePair<string, object>("Lang", hidLong.Value));
                        brParmeters.Add(new KeyValuePair<string, object>("Lat", hidLat.Value));
                        brParmeters.Add(new KeyValuePair<string, object>("Location", txtAddr1.Text));
                        brParmeters.Add(new KeyValuePair<string, object>("Pin", txtPinCode.Text));
                        brParmeters.Add(new KeyValuePair<string, object>("State", selState.SelectedItem.Text));
                        brParmeters.Add(new KeyValuePair<string, object>("MapLocation", txtLocation.Text));
                        brParmeters.Add(new KeyValuePair<string, object>("APIBranchId", -1));
                        brParmeters.Add(new KeyValuePair<string, object>("IsDefaultBranch", true));
                        if (gstId > 0)
                            brParmeters.Add(new KeyValuePair<string, object>("gstid", gstId));
                        else
                            brParmeters.Add(new KeyValuePair<string, object>("gstid", DBNull.Value));

                        //brParmeters.Add(new KeyValuePair<string, object>("bankid", bankId));
                        //brParmeters.Add(new KeyValuePair<string, object>("gstid", gstId));
                        if (bankId > 0)
                            brParmeters.Add(new KeyValuePair<string, object>("bankid", gstId));
                        else
                            brParmeters.Add(new KeyValuePair<string, object>("bankid", DBNull.Value));
                        //brParmeters.Add(new KeyValuePair<string, object>("bankid", bankId));
                        brParmeters.Add(new KeyValuePair<string, object>("PAN", ""));

                        DataService.ExecuteSP("CreateStoreBranch", parmeters: brParmeters);
                        Services.StoreService.UpdateStore(Convert.ToInt32(dtStore.Rows[0]["APIBranchId"]), txtAddr1.Text, curUser.APIStoreId, txtAddr2.Text, txtAddr3.Text, txtAddr4.Text, selDistrict.SelectedItem.Text, Convert.ToInt32(selState.Text), Convert.ToInt32(selDistrict.Text), txtPinCode.Text, storeEmail, storephone, storecontactperson, hidLat.Value, hidLong.Value, tradeRestrictionType, directDelivery: expressdelivery, courierDelivery: courierdelivery);
                        // Activitylog
                        String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                        String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                        string Source = strUrl;
                        int storegroupid = this.CurrentUser.APIStoreId;
                        string User = this.CurrentUser.Email;
                        string StoreId = (curUser.StoreGroupId).ToString();
                        string Addr = txtAddr2.Text;
                        string District = selDistrict.SelectedItem.Text;
                        string Lang = hidLong.Value;
                        string Lat = hidLat.Value;
                        string Location = txtAddr1.Text;
                        string Pin = txtPinCode.Text;
                        string State = selState.SelectedItem.Text;
                        string MapLocation = txtLocation.Text;
                        string APIBranchId = "-1";
                        string gstid = gstId.ToString();
                        string bankid = bankId.ToString();
                        string IsDefaultBranch = "true";
                        var items = new[]
                            {
                              new { Key = "StoreId", Value = StoreId },
                              new { Key = "Address", Value = Addr },
                              new { Key = "District", Value = District },
                              new { Key = "Lang", Value = Lang },
                              new { Key = "Lat", Value = Lat },
                              new { Key = "Location", Value = Location },
                              new { Key = "Pin", Value = Pin },
                              new { Key = "State", Value = State },
                              new { Key = "MapLocation", Value = MapLocation },
                              new { Key = "APIBranchId", Value = APIBranchId },
                              new { Key = "IsDefaultBranch", Value = IsDefaultBranch },
                              new { Key = "gstid", Value = gstid },
                              new { Key = "bankid", Value = bankid },

                           };
                        string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                        var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);



                        string strcontent = $"<p class=\"mg-b-5\">Store: {txtAddr1.Text}, Address: {txtAddr2.Text}<br/></p>";
                        ShowSuccess("Store Edited Successfully!!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Store has been updated successfully!</a></h5>" + strcontent);


                    }
                    catch (Exception ex2)
                    {
                        ShowFailure("Store creation failed", "Sorry, there is a technical error happened. Store creation is partially executed.<br/>Error: " + ex2.Message);
                        return;
                    }
                }
                catch (Exception ex)
                {
                    ShowFailure("Store creation failed", "Sorry, there is a technical error happened. Please contact support.<br/>Error: " + ex.Message);
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

            protected void btnEdit_Click(object sender, EventArgs e)
            {
                if (String.IsNullOrEmpty(hidLat.Value) || String.IsNullOrEmpty(hidLong.Value))
                {
                    lblMessage.Text = "Please select location in map. Click on the button 'Load Map' to search your location.";
                    return;
                }
                //if (String.IsNullOrEmpty(selGST.Text))
                //{
                //    lblMessage.Text = "GSTIN is mandatory. Please select GSTIN or you can add new GSTIN in the add GST page";
                //    return;
                //}
                if (EditStoreId > 0)
                    EditStore(EditStoreId);

            }

            protected void SDSOnOffTime_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
            {
                if (e.Command.Parameters.Contains("storegroupid"))
                    e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;

                if (e.Command.Parameters.Contains("brid"))
                    e.Command.Parameters["brid"].Value = EditAPIStoreId > 0 ? EditAPIStoreId : -1;

            }

            protected void btnAddTime_Click(object sender, EventArgs e)
            {
                if (EditAPIStoreId <= 0)
                {
                    ShowFailure("Failure", "Invalid Store");
                    return;
                }
                if (String.IsNullOrEmpty(txtTimeFrom.Text) || String.IsNullOrEmpty(txtTimeTo.Text))
                {
                    ShowFailure("Failure", "Invalid Input");
                    return;
                }
                var timeFrom = DateTime.Parse(txtTimeFrom.Text);
                var timeTo = DateTime.Parse(txtTimeTo.Text);

                if (timeFrom > timeTo)
                {
                    ShowFailure("Failure", "Time To should be greater than time from.");
                    return;
                }

                string sql = "INSERT INTO branch_timings(branch_id, br_open_time, br_close_time, createdBy) SELECT br_ID, @opentime, @closetime, 1 FROM finascop_branch WHERE br_ID=@brid AND br_StoreGroup=@StoreId";
                try
                {
                    string strTimeFrom = DateTime.Parse(txtTimeFrom.Text).ToString("HH:mm:ss");
                    string strTimeTo = DateTime.Parse(txtTimeTo.Text).ToString("HH:mm:ss");
                    List<KeyValuePair<string, object>> brParmeters = new List<KeyValuePair<string, object>>();
                    brParmeters.Add(new KeyValuePair<string, object>("brid", EditAPIStoreId));
                    brParmeters.Add(new KeyValuePair<string, object>("StoreId", this.CurrentUser.APIStoreId));
                    brParmeters.Add(new KeyValuePair<string, object>("opentime", strTimeFrom));
                    brParmeters.Add(new KeyValuePair<string, object>("closetime", strTimeTo));
                    int result = DataServiceMySql.ExecuteSql(sql, UserService.GetAPIConnectionString(), brParmeters);
                    if (result > 0)
                    {
                        SDSOnOffTime.Select(DataSourceSelectArguments.Empty);
                        gvOnOffTime.DataBind();
                        Common.ShowToastifyMessage(this.Page, "On / Off Time Added !");
                        //ShowSuccess("Success!!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">On / Off Time Added !</a></h5>");
                    }
                    else
                    {
                        ShowFailure("Failure", "Operation failed. Invalid store or you don't have permission to execute.");
                    }
                }
                catch (Exception ex2)
                {
                    ShowFailure("Failure", "Sorry, a technical error has occured. Error: " + ex2.Message);
                    return;
                }


            }

            protected void SDSOnOffTime_Deleting(object sender, SqlDataSourceCommandEventArgs e)
            {
                if (e.Command.Parameters.Contains("storegroupid"))
                    e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;

            }
            protected void SDSExpDeliv_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
            {
                e.Command.Parameters["branchid"].Value = (EditStoreId > 0 ? EditStoreId : 0);
            }

            protected void SDSDeliModes_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
            {
                if (e.Command.Parameters.Contains("storeId"))
                    e.Command.Parameters["storeId"].Value = this.CurrentUser.APIStoreId;
                if (e.Command.Parameters.Contains("branchid"))
                    e.Command.Parameters["branchid"].Value = (EditStoreId > 0 ? EditStoreId : 0);
            }

            protected void selDelivRule_SelectedIndexChanged(object sender, EventArgs e)
            {
                DropDownList dl = (DropDownList)sender;
                int deliRule = 0; try { deliRule = Convert.ToInt32(dl.Text); } catch { deliRule = 0; }
                if (dl == null || !"selExpDelivRule,selScheduleRule,selCourDelivRule".Split(',').Contains(dl.ID) || deliRule <= 0)
                {
                    Common.ShowToastifyMessage(this.Page, "Invalid operation", "danger");
                    return;
                }
                string sqlUpdateField = "";

                List<KeyValuePair<string, object>> sqlParams = new List<KeyValuePair<string, object>>();
                if (dl.ID == "selExpDelivRule")
                {
                    sqlUpdateField = "br_rdrIdExpress=@expressId";
                    sqlParams.Add(new KeyValuePair<string, object>("expressId", deliRule));
                }
                else if (dl.ID == "selCourDelivRule")
                {
                    sqlUpdateField = "br_rdrIdCourier=@courierId";
                    sqlParams.Add(new KeyValuePair<string, object>("courierId", deliRule));
                }
                else if (dl.ID == "selScheduleRule")
                {
                    sqlUpdateField = "br_rdrIdSlotted=@slottedId";
                    sqlParams.Add(new KeyValuePair<string, object>("slottedId", deliRule));
                }
                sqlParams.Add(new KeyValuePair<string, object>("branchId", (EditStoreId > 0 ? EditStoreId : 0)));
                sqlParams.Add(new KeyValuePair<string, object>("storeId", this.CurrentUser.APIStoreId));
                if (!String.IsNullOrEmpty(sqlUpdateField))
                {
                    string strUpdateSql = $"UPDATE finascop_branch SET {sqlUpdateField} WHERE br_ID=@branchId and br_storeGroup=@storeId";
                    int updateresult = DataServiceMySql.ExecuteSql(strUpdateSql, Service.UserService.GetAPIConnectionString(), sqlParams);
                    if (updateresult > 0)
                    {
                        Common.ShowToastifyMessage(this.Page, "Successfully updated delivery rule!");
                        //SDSDeliModes.Select(DataSourceSelectArguments.Empty);
                        //rptDeliModes.DataBind();
                        //ShowSuccess("Success", "Delivery rule updated.");
                        return;
                    }
                }
                else
                {
                    Common.ShowToastifyMessage(this.Page, "Invalid selection or there is a technical error happened!", "danger");
                    return;
                }

                Common.ShowToastifyMessage(this.Page, "Update failed", "info");
            }

            protected void rptDeliModes_ItemDataBound(object sender, RepeaterItemEventArgs e)
            {
                if (e.Item.ItemType != ListItemType.Item && e.Item.ItemType != ListItemType.AlternatingItem)
                    return;

                int deliMode = 0; try { deliMode = Convert.ToInt32(DataBinder.Eval(e.Item.DataItem, "rdr_deliveryMode")); } catch { }
                int deliid = 0; try { deliid = Convert.ToInt32(DataBinder.Eval(e.Item.DataItem, "rdr_id")); } catch { }
                if (deliMode == 2)
                {
                    DropDownList dlExpress = (DropDownList)e.Item.FindControl("selExpDelivRule");
                    if (dlExpress != null && dlExpress.Items.FindByValue(deliid.ToString()) != null)
                        dlExpress.SelectedValue = deliid.ToString();
                }
                else if (deliMode == 1)
                {
                    DropDownList dl = (DropDownList)e.Item.FindControl("selCourDelivRule");
                    if (dl != null && dl.Items.FindByValue(deliid.ToString()) != null)
                        dl.SelectedValue = deliid.ToString();
                }
                else if (deliMode == 3)
                {
                    DropDownList dl = (DropDownList)e.Item.FindControl("selScheduleRule");
                    if (dl != null && dl.Items.FindByValue(deliid.ToString()) != null)
                        dl.SelectedValue = deliid.ToString();
                }
            }

            protected void btnHiddenConfirm_Click(object sender, EventArgs e)
            {
                string roId = hdnRoId.Value;
                string cpid = hdnId.Value;
                string branchid = hdbranchId.Value;
                string roArea = hdnRoArea.Value;
                int storeGroupId = this.CurrentUser.APIStoreId;


                UpdateRelationshipOfficerOrConsultingPartner(branchid, storeGroupId, roArea, roId, cpid);
            }
            private void UpdateRelationshipOfficerOrConsultingPartner(string branchId, int storeGroupId, string areaId, string rosId, string cpId)
            {
                try
                {
                    string roId = hdnRoId.Value;
                    string cpid = hdnId.Value;
                    string branchid = hdbranchId.Value;
                    string roArea = hdnRoArea.Value;
                    int relationshipofficerId = 0;
                    int consultingpartnerId = 0;
                    List<KeyValuePair<string, object>> rocpparams = new List<KeyValuePair<string, object>>();
                    rocpparams.Add(new KeyValuePair<string, object>("branchId", branchid));
                    rocpparams.Add(new KeyValuePair<string, object>("storegroupId", this.CurrentUser.APIStoreId));
                    rocpparams.Add(new KeyValuePair<string, object>("areaId", roArea));
                    if (!string.IsNullOrEmpty(roId))
                    {
                        relationshipofficerId = Convert.ToInt32(roId);
                        if (relationshipofficerId > 0)
                        {
                            rocpparams.Add(new KeyValuePair<string, object>("roId", relationshipofficerId));
                            rocpparams.Add(new KeyValuePair<string, object>("referrerType", 0));
                            rocpparams.Add(new KeyValuePair<string, object>("referedBy", 0));
                            string strUpdateSql = $"UPDATE finascop_branch SET areaId=@areaId, roId=@roId, referrerType=@referrerType, referedBy=@referedBy WHERE br_ID=@branchId AND br_storeGroup=@storegroupId";
                            DataServiceMySql.ExecuteSql(strUpdateSql, Service.UserService.GetAPIConnectionString(), rocpparams);
                            Common.ShowCustomAlert(this.Page, "Success", "Relationship officer added / changed successfully!!", true, "/Tenant/ManageBusinessSettings");
                        }
                    }
                    else if (!string.IsNullOrEmpty(cpid))
                    {
                        consultingpartnerId = Convert.ToInt32(cpid);
                        if (consultingpartnerId > 0)
                        {
                            rocpparams.Add(new KeyValuePair<string, object>("roId", 0));
                            rocpparams.Add(new KeyValuePair<string, object>("referrerType", 7));
                            rocpparams.Add(new KeyValuePair<string, object>("referedBy", consultingpartnerId));
                            string strUpdatebranch = $"UPDATE finascop_branch SET roId=@roId, referrerType=@referrerType, referedBy=@referedBy WHERE br_ID=@branchId AND br_storeGroup=@storegroupId";
                            DataServiceMySql.ExecuteSql(strUpdatebranch, Service.UserService.GetAPIConnectionString(), rocpparams);
                            //Common.ShowCustomAlert(this.Page,"Success", "Consulting Partner added / changed successfully!!", true, "/Tenant/Branches");
                        }
                    }
                }
                catch
                {
                    ShowFailure("Error", "Failure with error: Invalid!");
                    return;
                }
            }
            protected void lnkGo_Click(object sender, EventArgs e)
            {
                try
                {
                    bool isSuccess = false;
                    string number = txtMobile.Text;
                    string mobileNumber = (ConfigurationManager.AppSettings.Get("PhoneCountryCode")) + number;
                    if (!string.IsNullOrEmpty(mobileNumber))
                    {
                        List<KeyValuePair<string, object>> roparams = new List<KeyValuePair<string, object>>()
                        {
                           new KeyValuePair<string, object> ("mobile", mobileNumber)

                        };
                        hdnmobile.Value = mobileNumber;
                        var dtRO = DataServiceMySql.GetDataTable($@"SELECT ro.id, ro.roArea, ro.roName, ro.roMobile, ro.rost_id, ae.areaLocation, IF(TYPE = 1, 'Relationship Officer', 'NIL') AS rotype,CONCAT(ro.roName, ' - ', IF(ro.TYPE = 1, 'Relationship Officer', 'NIL'), '\n',ae.areaName, ' Area, ', (SELECT dst_Name FROM finascop_district fd WHERE ro.rodst_Id = fd.dst_Id), ', ', (SELECT st_name FROM finascop_state fs WHERE ro.rost_id = fs.st_ID), ' State') AS rodetails, ae.areaName FROM relationship_officer ro INNER JOIN area_entries ae ON ro.roArea = ae.id WHERE ro.type = 1 AND roMobile = @mobile", UserService.GetAPIConnectionString(), roparams);

                        if (dtRO != null && dtRO.Rows.Count > 0)
                        {
                            DataRow da = dtRO.Rows[0];
                            lblRODetails.Visible = true;
                            btnConnect.Visible = true;

                            string roDetails = da["rodetails"].ToString();
                            roDetails = roDetails.Replace("\n", "<br>");
                            hdnRoId.Value = da["id"].ToString();
                            hdnRoArea.Value = da["roArea"].ToString();
                            lblRODetails.Text = roDetails;
                            isSuccess = true;
                            ScriptManager.RegisterStartupScript(this, GetType(), "showModalScript", "$('#hsnsearch').modal('show');", true);
                        }
                        else
                        {
                            var dtBA = DataServiceMySql.GetDataTable($@"
                                SELECT id, baName, IF(userType = 7, 'Consulting Partner', 'NIL') AS batype, baAddress, CONCAT(baName, ' - ', IF(userType = 7, 'Consulting Partner', 'NIL'), '\n', baAddress) AS cpdetails FROM business_associate WHERE userType = 7 AND baMobileNo = @mobile", UserService.GetAPIConnectionString(), roparams);

                            if (dtBA != null && dtBA.Rows.Count > 0)
                            {
                                DataRow dr = dtBA.Rows[0];
                                lblRODetails.Visible = true;
                                btnConnect.Visible = true;

                                string cpDetails = dr["cpdetails"].ToString();
                                cpDetails = cpDetails.Replace("\n", "<br>");
                                hdnId.Value = dr["id"].ToString();
                                lblRODetails.Text = cpDetails;
                                isSuccess = true;
                                ScriptManager.RegisterStartupScript(this, GetType(), "showModalScript", "$('#hsnsearch').modal('show');", true);
                            }
                            else
                            {
                                lblRODetails.Visible = false;
                                btnConnect.Visible = false;
                                ScriptManager.RegisterStartupScript(this, GetType(), "showModalScript", "$('#hsnsearch').modal('show');", true);
                                ShowFailure("Error", "Failure with error: Provided mobile number is not available.");
                                return;
                            }
                        }
                    }
                    //lstSelectedTypes.DataBind();
                }
                catch
                {
                    ShowFailure("Error", "Failure with error: Invalid!");
                    return;
                }
            }

            protected void btnConnect_Click(object sender, EventArgs e)
            {
                try
                {
                    string roId = hdnRoId.Value, cpid = hdnId.Value, branchId = hdbranchId.Value, roArea = hdnRoArea.Value, areaId = "";
                    int storeGroupId = this.CurrentUser.APIStoreId;
                    if (!string.IsNullOrEmpty(branchId))
                    {
                        List<KeyValuePair<string, object>> rocpparams = new List<KeyValuePair<string, object>>();
                        rocpparams.Add(new KeyValuePair<string, object>("branchId", branchId));
                        rocpparams.Add(new KeyValuePair<string, object>("storegroupId", storeGroupId));
                        var dtbranch = DataServiceMySql.GetDataTable($@"SELECT areaId, roId, referrerType, referedBy FROM finascop_branch WHERE br_ID = @branchId AND br_storeGroup = @storegroupId", UserService.GetAPIConnectionString(), rocpparams);
                        if (dtbranch != null && dtbranch.Rows.Count > 0)
                        {
                            DataRow dr = dtbranch.Rows[0];
                            areaId = dr["areaId"].ToString();
                            hdAreaId.Value = areaId;
                            if (areaId == roArea)
                            {
                                string script = "if (confirm('Do you want to add / change RO?')) { __doPostBack('" + btnHiddenConfirm.UniqueID + "', ''); }";
                                ClientScript.RegisterStartupScript(this.GetType(), "confirmChangeRO", script, true);
                            }
                            else if (areaId != roArea)
                            {
                                string script = "if (confirm('The RO\\'s area does not match the store area. Do you still want to proceed?')) { __doPostBack('" + btnHiddenConfirm.UniqueID + "', ''); }";
                                ClientScript.RegisterStartupScript(this.GetType(), "confirmAreaMismatch", script, true);
                            }
                        }
                    }
                    else
                    {
                        if (!string.IsNullOrEmpty(hdnmobile.Value))
                        {
                            List<KeyValuePair<string, object>> roparams = new List<KeyValuePair<string, object>>()
                        {
                           new KeyValuePair<string, object> ("mobile",  hdnmobile.Value)
                        };

                            var dtRO = DataServiceMySql.GetDataTable($@"SELECT ro.id,ae.id as areaId, ro.roArea, ro.roName, ro.roMobile, ro.rost_id, ae.areaLocation, IF(TYPE = 1, 'Relationship Officer', 'NIL') AS rotype,CONCAT(areaName, ', ', (SELECT dst_Name FROM finascop_district fd WHERE ro.rodst_Id = fd.dst_Id), ', ', (SELECT st_name FROM finascop_state fs WHERE ro.rost_id = fs.st_ID)) AS grozeoarea, ro.roName, CONCAT(roName,' ' , '-', ' ', roMobile) AS roDetails FROM relationship_officer ro INNER JOIN area_entries ae ON ro.roArea = ae.id WHERE ro.type = 1 AND roMobile = @mobile", UserService.GetAPIConnectionString(), roparams);
                            if (dtRO != null && dtRO.Rows.Count > 0)
                            {
                                DataRow da = dtRO.Rows[0];
                                lblRODetails.Visible = true;
                                btnConnect.Visible = true;
                                string areaName = da["grozeoarea"].ToString();
                                string roName = da["roDetails"].ToString();
                                txtGrozeoArea.Text = areaName;
                                txtRO.Text = roName ?? string.Empty;
                                hdnRoId.Value = da["id"].ToString();
                                hdnRoArea.Value = da["roArea"].ToString();
                                hdAreaId.Value = da["areaId"].ToString();


                            }
                            else
                            {
                                var dtBA = DataServiceMySql.GetDataTable($@"SELECT baName, baMobileNo, baAddress, CONCAT(baName,' ' , '-', ' ', baMobileNo) AS cpdetails FROM business_associate WHERE userType = 7 AND baMobileNo = @mobile", UserService.GetAPIConnectionString(), roparams);

                                if (dtBA != null && dtBA.Rows.Count > 0)
                                {
                                    string cpName = "";
                                    DataRow dr = dtBA.Rows[0];
                                    lblRODetails.Visible = true;
                                    btnConnect.Visible = true;
                                    string areaName = dr["cpdetails"].ToString();
                                    string roName = dr["baName"].ToString();
                                    txtGrozeoArea.Text = areaName;
                                    txtRO.Text = cpName ?? string.Empty;
                                    string cpDetails = dr["cpdetails"].ToString();
                                    cpDetails = cpDetails.Replace("\n", "<br>");
                                    lblRODetails.Text = cpDetails;
                                }
                                else
                                {
                                    lblRODetails.Visible = false;
                                    btnConnect.Visible = false;
                                    ShowFailure("Error", "Failure with error: Provided mobile number is not available.");
                                    return;
                                }
                            }
                        }
                    }

                }
                catch
                {
                    ShowFailure("Error", "Failure with error: Invalid!");
                    return;
                }
            }
            public void GetRODetails(int branchId)
            {
                string roName = "", roId = "", areaId = "", cpId = "", referrerType = "", cpName = "", areaName = "";
                int relationshipofficerId = 0, consultingpartnerId = 0, roAreaId = 0;
                List<KeyValuePair<string, object>> dataparams = new List<KeyValuePair<string, object>>
            {
                new KeyValuePair<string, object>("storeId", this.CurrentUser.APIStoreId),
                new KeyValuePair<string, object>("branchId", branchId)
            };
                // Assign the branchId to the hidden field
                hdbranchId.Value = Convert.ToString(branchId);
                var dtBranches = DataServiceMySql.GetDataTable($"SELECT areaId, ae.areaName, CONCAT(areaName, ', ', (SELECT dst_Name FROM finascop_district fd WHERE ro.rodst_Id = fd.dst_Id), ', ', (SELECT st_name FROM finascop_state fs WHERE ro.rost_id = fs.st_ID)) AS grozeoarea, roId, referrerType, referedBy FROM finascop_branch fb INNER JOIN area_entries ae ON fb.areaId = ae.id INNER JOIN relationship_officer ro ON fb.areaId = ro.roArea WHERE br_ID = @branchId AND br_storeGroup = @storeId", UserService.GetAPIConnectionString(), dataparams);
                if (dtBranches != null && dtBranches.Rows.Count > 0)
                {
                    DataRow dr = dtBranches.Rows[0];
                    roId = dr["roId"].ToString();
                    areaId = dr["areaId"].ToString();
                    relationshipofficerId = Convert.ToInt32(roId);
                    roAreaId = Convert.ToInt32(areaId);
                    cpId = dr["referedBy"].ToString();
                    consultingpartnerId = Convert.ToInt32(cpId);
                    referrerType = dr["referrerType"].ToString();
                    areaName = dr["grozeoarea"].ToString();
                }
                if (roAreaId > 0 && relationshipofficerId > 0)
                {
                    dataparams.Add(new KeyValuePair<string, object>("rocpId", relationshipofficerId));
                    var dtRoDetails = DataServiceMySql.GetDataTable($"SELECT fb.areaId, ae.areaName,CONCAT(areaName, ', ', (SELECT dst_Name FROM finascop_district fd WHERE ro.rodst_Id = fd.dst_Id), ', ', (SELECT st_name FROM finascop_state fs WHERE ro.rost_id = fs.st_ID)) AS grozeoarea, ro.roName, CONCAT(roName,' ' , '-', ' ', roMobile) AS roDetails FROM finascop_branch fb INNER JOIN area_entries ae ON fb.areaId = ae.id INNER JOIN relationship_officer ro ON fb.areaId = ro.roArea WHERE br_ID = @branchId AND br_storeGroup = @storeId AND ro.id = @rocpId", UserService.GetAPIConnectionString(), dataparams);
                    if (dtRoDetails != null && dtRoDetails.Rows.Count > 0)
                    {
                        DataRow dr = dtRoDetails.Rows[0];
                        areaName = dr["grozeoarea"].ToString();
                        roName = dr["roDetails"].ToString();
                        txtGrozeoArea.Text = areaName;
                        txtRO.Text = roName ?? string.Empty;
                    }
                    else if (roAreaId > 0 && consultingpartnerId > 0)
                    {
                        dataparams.Add(new KeyValuePair<string, object>("userType", referrerType));
                        dataparams.Add(new KeyValuePair<string, object>("cpId", consultingpartnerId));
                        var dtBA = DataServiceMySql.GetDataTable($"SELECT baName, baMobileNo, baAddress, CONCAT(baName,' ' , '-', ' ', baMobileNo) AS cpdetails FROM business_associate WHERE userType = @userType AND id = @cpId", UserService.GetAPIConnectionString(), dataparams);
                        if (dtBA != null && dtBA.Rows.Count > 0)
                        {
                            DataRow dz = dtBA.Rows[0];
                            cpName = dz["cpdetails"].ToString();
                            txtGrozeoArea.Text = areaName;
                            txtRO.Text = cpName ?? string.Empty;
                        }

                    }

                }

            }

        protected void SDSSelectedBusinessTypes_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["@storeid"].Value = this.CurrentUser.APIStoreId;
        }

        protected void lstSelectedTypes_DataBound(object sender, EventArgs e)
        {
            foreach (ListItem item in lstSelectedTypes.Items)
            {
                item.Selected = true;
            }

        }
        private static void diablesponesedproduct(int storegroupid)
        {
            try
            {
                List<KeyValuePair<string, object>> updateparam = new List<KeyValuePair<string, object>>();
                updateparam.Add(new KeyValuePair<string, object>("storegroupid", storegroupid));
                string strUpdateSql = "UPDATE  finascop_stock_branch_inventory SET issponsered=0 WHERE branch_id in (SELECT br_id FROM `finascop_branch` WHERE  br_storeGroup=@storegroupid)";
                DataServiceMySql.ExecuteSql(strUpdateSql, UserService.GetAPIConnectionString(), updateparam);
            }
            catch (Exception ex)
            {

            }

        }
    }

}



      