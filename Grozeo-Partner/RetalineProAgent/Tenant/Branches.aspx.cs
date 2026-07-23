using Antlr.Runtime.Misc;
using RetalineProAgent.Core.BussinessModel.Finance;
//using RetalineProAgent.Core.BussinessModel.GST;
using RetalineProAgent.Core.BussinessModel.Inventory;
using RetalineProAgent.Core.BussinessModel.Store;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Core.Services.Cache;
using RetalineProAgent.Service;
using RetalineProAgent.Service.Store;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.HtmlControls;
using System.Web.UI.WebControls;
using static System.Windows.Forms.VisualStyles.VisualStyleElement.TreeView;

namespace RetalineProAgent
{
    public partial class Branches: Base.BasePartnerPage
    {
        private int? TenantId { 
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
        //public List<Store> MyStores {
        //    get
        //    {
        //        if(_myStores == null)
        //        {
        //            _myStores = new List<Store>();
        //            //var dv = (DataView)SDSBranches.Select(DataSourceSelectArguments.Empty); //DataService.GetDataTable($"SELECT * FROM StoreBranch WHERE StoreId={this.CurrentUser.StoreGroupId}");
        //            DataTable dt = TblMyStores;
        //            if(dt != null && dt.Rows.Count > 0)
        //            {
        //                foreach(DataRow dr in dt.Rows)
        //                    _myStores.Add(new Store() { DBBranchid = (int)dr["Id"], BranchId = (int)dr["APIBranchId"] });
        //            }
        //        }
        //        return _myStores;
        //    }
        //}
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

        //public int DBStoreId(int apiStoreId)
        //{
        //    var store = MyStores.FirstOrDefault(s => s.DBBranchid == apiStoreId);
        //    if(store != null)
        //        return store.DBBranchid;

        //    return -1;
        //}
        public string GSTIN(int storeid)
        {
            DataTable dt = TblMyStores;
            if(dt != null && dt.Rows.Count > 0)
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

            ctrlAddressMap1.ParentAddr2ClientId = txtAddr3.ClientID;
            ctrlAddressMap1.ParentAddr3ClientId = txtAddr4.ClientID;

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

                string type = Request.QueryString["type"];

                if (!string.IsNullOrEmpty(type))
                {
                    ViewState["PageType"] = type;  // Store for use later

                    switch (type)
                    {
                        case "ManageBranch":
                            lnkBack.NavigateUrl = "/Navigations/SettingsMenu";
                            break;

                        case "DeliveryRates":
                            lnkBack.NavigateUrl = "/Navigations/Delivery";
                            break;

                        default:
                            lnkBack.NavigateUrl = "/Tenant/Default";
                            break;
                    }
                }
                else
                {
                    ViewState["PageType"] = "ManageBranch"; 
                    lnkBack.NavigateUrl = "/Tenant/Default";
                }
            }

            //if (ConfigurationManager.AppSettings.Get("VATType") == "2")
            //    selGST.Attributes.Add("required", "required");

            //lblMessage.Text = "";

            ctrlAddressMap1.ParentLocationClientId = hidMapAddr.ClientID; // txtLocation.ClientID;
            ctrlAddressMap1.ParentLatClientId = hidLat.ClientID;
            ctrlAddressMap1.ParentLongClientId = hidLong.ClientID;
            ctrlAddressMap1.ParentPinClientId = txtPinCode.ClientID;
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
            rfvdistrict.ErrorMessage= RetalineProAgent.Service.Common.DistrictLabel + " is required";

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

            plcDelivRuleSetting.Visible = (CurViewType == "3");


            if (!IsPostBack)
            {
                if (rptBranches.Items.Count < 1)
                    rptBranches.DataBind();
                if (rptBranches.Items.Count > 0 && this.CurrentUser.PackageId < 2)
                    btnAddStore.OnClientClick = "$('#modalupgrade').modal('show'); return false;";
            }
            PopupUpgradeConsent1.Visible = this.CurrentUser.PackageId >= 2 && rptBranches.Items.Count >= 3;
            if (PopupUpgradeConsent1.Visible)
            {
                btnAddStore.OnClientClick = "$('#modalupgradeconsent').modal('show'); return false;";
            }
        }

        protected void ODSStore_Selecting(object sender, ObjectDataSourceSelectingEventArgs e)
        {
            e.InputParameters["storegroupid"] = this.CurrentUser.StoreGroupId;
            e.InputParameters["apistoregroupid"] = this.CurrentUser.APIStoreId;

            //var dt = DataService.GetDataTable("SELECT * FROM AppTenant WHERE Id=" + this.CurrentUser.StoreGroupId);
            //if (dt != null && dt.Rows.Count > 0)
            //{
            //    string strStoregroupid = dt.Rows[0]["StoreId"].ToString();
            //    if (!String.IsNullOrEmpty(strStoregroupid))
            //        e.InputParameters["storegroupid"] = strStoregroupid;//this.CurrentUser.StoreGroupId;
            //}
        }

        protected void Unnamed_RowDataBound(object sender, GridViewRowEventArgs e)
        {
            
        }

        //protected void rptBranches_ItemDataBound(object sender, RepeaterItemEventArgs e)
        //{
        //    if (e.Item.ItemType == ListItemType.Item || e.Item.ItemType == ListItemType.AlternatingItem)
        //    {
        //        RadioButton rdDefaultBrnach = (RadioButton)e.Item.FindControl("rdDefaultBrnach1");
        //        Repeater rptTiming = (Repeater)e.Item.FindControl("rptTiming");
        //        if(rptTiming != null)
        //        {
        //            rptTiming.DataSource = (StoreTime[])DataBinder.Eval(e.Item.DataItem, "OnOffTime");
        //            rptTiming.DataBind();

        //            Literal ltrNoTime = (Literal)e.Item.FindControl("ltrNoTiming");
        //            if (ltrNoTime != null)
        //                ltrNoTime.Visible = (rptTiming.Items.Count <= 0);
        //        }

        //        var setDelivRule = (HyperLink)e.Item.FindControl("setDelivRule");
        //        var setStoreTiming = (LinkButton)e.Item.FindControl("setStoreTiming");

        //        if (setDelivRule != null && setStoreTiming != null)
        //        {
        //            // default both hidden
        //            setDelivRule.Visible = false;
        //            setStoreTiming.Visible = false;

        //            if (Request.UrlReferrer != null)
        //            {
        //                string referrer = Request.UrlReferrer.AbsolutePath.ToLower();

        //                if (referrer.Contains("/navigations/delivery"))
        //                {
        //                    setDelivRule.Visible = true;   // show Delivery Rule
        //                }
        //                else if (referrer.Contains("/navigations/settingsmenu"))
        //                {
        //                    setStoreTiming.Visible = true; // show Store Timing
        //                    dvStoreTiming.Visible = true;
        //                    onOffTime.Visible = true;
        //                }
        //            }
        //        }
        //    }
        //}

        //protected void rptBranches_ItemDataBound(object sender, RepeaterItemEventArgs e)
        //{
        //    if (e.Item.ItemType == ListItemType.Item || e.Item.ItemType == ListItemType.AlternatingItem)
        //    {
        //        RadioButton rdDefaultBrnach = (RadioButton)e.Item.FindControl("rdDefaultBrnach1");
        //        Repeater rptTiming = (Repeater)e.Item.FindControl("rptTiming");
        //        if (rptTiming != null)
        //        {
        //            rptTiming.DataSource = (StoreTime[])DataBinder.Eval(e.Item.DataItem, "OnOffTime");
        //            rptTiming.DataBind();

        //            Literal ltrNoTime = (Literal)e.Item.FindControl("ltrNoTiming");
        //            if (ltrNoTime != null)
        //                ltrNoTime.Visible = (rptTiming.Items.Count <= 0);
        //        }

        //        var setDelivRule = (HyperLink)e.Item.FindControl("setDelivRule");
        //        var setStoreTiming = (LinkButton)e.Item.FindControl("setStoreTiming");

        //        // get the <td> cells
        //        var tdToggle = (HtmlTableCell)e.Item.FindControl("tdToggle");
        //        var tdEdit = (HtmlTableCell)e.Item.FindControl("tdEdit");
        //        var thStatus = (HtmlTableCell)e.Item.FindControl("thStatus");
        //        var thEdit = (HtmlTableCell)e.Item.FindControl("thEdit");

        //        if (setDelivRule != null && setStoreTiming != null)
        //        {
        //            // default both hidden
        //            setDelivRule.Visible = false;
        //            setStoreTiming.Visible = false;

        //            if (Request.UrlReferrer != null)
        //            {
        //                string referrer = Request.UrlReferrer.AbsolutePath.ToLower();

        //                if (referrer.Contains("/navigations/delivery"))
        //                {
        //                    setDelivRule.Visible = true;   // show Delivery Rule
        //                    if (tdToggle != null) tdToggle.Visible = false; // hide toggle column
        //                    if (tdEdit != null) tdEdit.Visible = false;     // hide edit column
        //                }
        //                else if (referrer.Contains("/navigations/settingsmenu"))
        //                {
        //                    setStoreTiming.Visible = true; // show Store Timing
        //                    dvStoreTiming.Visible = true;
        //                    onOffTime.Visible = true;
        //                }
        //            }
        //        }
        //    }
        //}

        protected void rptBranches_ItemDataBound(object sender, RepeaterItemEventArgs e)
        {
            string type = ViewState["PageType"] != null ? ViewState["PageType"].ToString() : "ManageBranch";

            // HEADER
            if (e.Item.ItemType == ListItemType.Header)
            {
                var thStatus = (HtmlTableCell)e.Item.FindControl("thStatus");
                var thEdit = (HtmlTableCell)e.Item.FindControl("thEdit");
                var thAction = (HtmlTableCell)e.Item.FindControl("thAction");

                if (type == "DeliveryRates")
                {
                    if (thStatus != null) thStatus.Visible = false;
                    if (thEdit != null) thEdit.Visible = false;
                    if (thAction != null) thAction.Visible = true;
                }
            }

            // ROWS
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

                var setDelivRule = (HyperLink)e.Item.FindControl("setDelivRule");
                var setStoreTiming = (LinkButton)e.Item.FindControl("setStoreTiming");

                var tdToggle = (HtmlTableCell)e.Item.FindControl("tdToggle");
                var tdEdit = (HtmlTableCell)e.Item.FindControl("tdEdit");

                if (setDelivRule != null && setStoreTiming != null)
                {
                    setDelivRule.Visible = false;
                    setStoreTiming.Visible = false;

                    if (type == "DeliveryRates")
                    {
                        setDelivRule.Visible = true;
                        if (tdToggle != null) tdToggle.Visible = false;
                        if (tdEdit != null) tdEdit.Visible = false;
                        lblPageTitle.Text = "Delivery Rate";
                        btnAddStore.Visible = false;
                    }
                    else if (type == "ManageBranch")
                    {
                        setStoreTiming.Visible = true;
                        dvStoreTiming.Visible = true;
                        onOffTime.Visible = true;
                        lblPageTitle.Text = "Manage Stores";
                    }
                }
            }
        }



        protected void setStoreTiming_Click(object sender, EventArgs e)
        {
            plcStoreTiming.Visible = true;
            LinkButton lbtn = (LinkButton)sender;
            if (lbtn != null && !String.IsNullOrEmpty(lbtn.Attributes["brid"]))
            {
                int storeid = Convert.ToInt32(lbtn.Attributes["brid"]);
                if (storeid <= 0)
                    return;

                CurViewType = "4";
                EditStoreId = storeid;
                btnEdit.Visible = true;
                btnAdd.Visible = false;
                LoadInput(EditStoreId);

                SDSOnOffTime.SelectParameters["brid"].DefaultValue = EditAPIStoreId.ToString();
                SDSOnOffTime.Select(DataSourceSelectArguments.Empty);
                gvOnOffTime.DataBind();

            }
        }

        //protected void rdDefaultBrnach1_CheckedChanged(object sender, EventArgs e)
        //{
        //    RadioButton rbtn = (RadioButton)sender;
        //    Label lbl = (Label)rbtn.Parent.FindControl("lblRdDefaultBrnach1");

        //    string strbrid = lbl.Attributes["brid"];

        //    if (!String.IsNullOrEmpty(strbrid))
        //    {
        //        int brid = Convert.ToInt32(strbrid);
        //        Core.Services.APIService.SetDefaultStore(brid);
        //    }

        //    rptBranches.DataBind();
        //}

        protected void chkStatus_CheckedChanged(object sender, EventArgs e)
        {
            CheckBox chbtn = (CheckBox)sender;
            //Label lbl = (Label)chbtn.Parent.FindControl("lblRdDefaultBrnach1");

            //string strbrid = lbl.Attributes["brid"];

            if (chbtn != null && !String.IsNullOrEmpty(chbtn.Attributes["brid"]))
            {
                int brid = Convert.ToInt32(chbtn.Attributes["brid"]);
                int onlineStaus = (chbtn.Checked ? 1 : 0);
                string strSql = "UPDATE finascop_branch SET br_SalesOnline="+onlineStaus+" WHERE br_ID="+ brid + " and br_storeGroup=" + this.CurrentUser.APIStoreId +"";
                DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString());
            }

            rptBranches.DataBind();
        }

        //protected async void btnAdd_Click(object sender, EventArgs e)
        //{
        //    if (IsValid)
        //    {
        //        string strBusinessTypes = "";
        //        foreach (ListItem item in chkBusinessTypes.Items)
        //            if (item.Selected)
        //                strBusinessTypes += (String.IsNullOrWhiteSpace(strBusinessTypes) ? "" : ",") + item.Value;

        //        List<string> strHosts = new List<string>();
        //        strHosts.Add(lblCustomDomain.Text.Replace("[title]", txtStoreName.Text.Replace(" ", "").Trim().ToLower()));
        //        if (!String.IsNullOrEmpty(txtCustomDomain.Text))
        //            foreach (string strCustDomain in txtCustomDomain.Text.Split(','))
        //                strHosts.Add(strCustDomain.Trim());

        //        string strSqlCheck = String.Join("','", strHosts);
        //        if (!String.IsNullOrEmpty(strSqlCheck))
        //        {
        //            strSqlCheck = "select HostAddress from host where HostAddress in ('"+strSqlCheck+"') "+ (TenantId > 0? $"and TenantId <> {TenantId}" : "");
        //            DataTable dtHosts = DataService.GetDataTable(strSqlCheck, SDSStores.ConnectionString);
        //            if(dtHosts != null && dtHosts.Rows.Count > 0)
        //            {
        //                lblMessage.Text = "domain is already assigned to another tenant: ";
        //                foreach (DataRow dr in dtHosts.Rows)
        //                    lblMessage.Text += " "+dr["HostAddress"].ToString();
        //                return;
        //            }
        //        }

        //        string insertDoamin = " DELETE Host WHERE TenantId=@TenantId; ";
        //        List<KeyValuePair<String, Object>> headerParams = new List<KeyValuePair<string, object>>();
        //        int hcount = 0;
        //        foreach (string strHost in strHosts)
        //        {
        //            if (!String.IsNullOrEmpty(strHost.Trim()))
        //            {
        //                hcount++;
        //                headerParams.Add(new KeyValuePair<string, object>($"host{hcount}", strHost.Trim().ToLower()));
        //                insertDoamin += $" INSERT INTO Host(TenantId, HostAddress) VALUES(@TenantId, @host{hcount}); ";
        //            }
        //        }
        //        string insertStore = $" DELETE FROM Store WHERE TenantId=@TenantId;" +
        //            $" INSERT INTO Store(Name, GroupId, TenantId, MinMargin, Status, Package, BusinessType, DBConnectionString, SelectSql, CreatedBy)" +
        //            $" VALUES(@Name, @StoreGroupId, @TenantId, @MinMargin, @Status, @Package, @BusinessType, @DBConnectionString, @SelectSql, @User); " +
        //            $" SET @StrId=scope_identity(); {insertDoamin} ";

        //        string sqlUpdateStore = $" IF EXISTS(SELECT * FROM Store WHERE Id=@StrId) BEGIN UPDATE Store SET Name=@Name, " +
        //            $"GroupId=@StoreGroupId, TenantId=@TenantId, MinMargin=@MinMargin, Status=@Status, Package=@Package, BusinessType=@BusinessType, " +
        //            $"DBConnectionString=@DBConnectionString, SelectSql=@SelectSql, UpdatedBy=@User WHERE Id=@StrId AND TenantId=@TenantId; END " +
        //            $" ELSE BEGIN {insertStore} END {insertDoamin} ";

        //        string sqlInsertTenant = $"IF NOT EXISTS(SELECT * FROM AppTenant WHERE Name like '{txtStoreName.Text}') " +
        //            $"BEGIN INSERT INTO AppTenant(Name, Theme, APIUrl, CustomColor, CanCheckout, OnlinePaymentEnabled, StoreId, Status, ShowPWA, LogoImage, LogoSmall) " +
        //            $"VALUES(@Name, @Theme, @APIUrl, @CustomColor, @CanCheckout, @OnlinePaymentEnabled, @StoreGroupId, @Status, @ShowPWA, @LogoImage, @LogoSmall); " +
        //            $" SET @TenantId=scope_identity(); {insertStore} " +
        //            $" SELECT @TenantId; END ELSE BEGIN SELECT -2; END";

        //        string sqlUpdateTenant = $"UPDATE AppTenant SET Name=@Name, Theme=@Theme, APIUrl=@APIUrl, CustomColor=@CustomColor, CanCheckout=@CanCheckout, OnlinePaymentEnabled=@OnlinePaymentEnabled, " +
        //            $"StoreId=@StoreGroupId, Status=@Status, {(uploadLogo.HasFile || ( chkDelImgLogo.Visible && chkDelImgLogo.Checked) ? "LogoImage=@LogoImage," : "")} " +
        //            $" {(uploadLogoWhite.HasFile || (chkDelImgLogoWhite.Visible && chkDelImgLogoWhite.Checked) ? "LogoSmall=@LogoSmall," : "")} ShowPWA=@ShowPWA " +
        //            $" WHERE Id=@TenantId; {sqlUpdateStore} ";


        //        List<KeyValuePair<String, Object>> parmeters = new List<KeyValuePair<string, object>>();

        //        parmeters.Add(new KeyValuePair<string, object>("Name", txtStoreName.Text));
        //        parmeters.Add(new KeyValuePair<string, object>("Theme", selTheme.Text));
        //        parmeters.Add(new KeyValuePair<string, object>("APIUrl", System.Configuration.ConfigurationManager.AppSettings.Get("api.url")));
        //        parmeters.Add(new KeyValuePair<string, object>("CanCheckout", chkCheckout.Checked));
        //        parmeters.Add(new KeyValuePair<string, object>("OnlinePaymentEnabled", chkOnline.Checked));
        //        parmeters.Add(new KeyValuePair<string, object>("StoreGroupId", txtAPICode.Text));
        //        parmeters.Add(new KeyValuePair<string, object>("Status", chkStatus.Checked));
        //        parmeters.Add(new KeyValuePair<string, object>("ShowPWA", chkPWA.Checked));
        //        string strLogo = Guid.NewGuid().ToString();
        //        if (uploadLogo.HasFile)
        //        {
        //            string strExtention = System.IO.Path.GetExtension(uploadLogo.PostedFile.FileName);
        //            string resultLogo = await Service.Common.CreateBlob(uploadLogo.PostedFile.InputStream, strLogo + $"_logo{strExtention}");
        //            if(!string.IsNullOrEmpty(resultLogo))
        //                parmeters.Add(new KeyValuePair<string, object>("LogoImage", resultLogo));
        //        }
        //        if (uploadLogoWhite.HasFile)
        //        {
        //            string strExtention = System.IO.Path.GetExtension(uploadLogoWhite.PostedFile.FileName);
        //            string resultLogo = await Service.Common.CreateBlob(uploadLogoWhite.PostedFile.InputStream, strLogo + $"_logo_white{strExtention}");
        //            if (!string.IsNullOrEmpty(resultLogo))
        //                parmeters.Add(new KeyValuePair<string, object>("LogoSmall", resultLogo));
        //        }

        //        if(!parmeters.Any(k=> k.Key == "LogoImage"))
        //            parmeters.Add(new KeyValuePair<string, object>("LogoImage", ""));
        //        if (!parmeters.Any(k => k.Key == "LogoSmall"))
        //            parmeters.Add(new KeyValuePair<string, object>("LogoSmall", ""));

        //        parmeters.Add(new KeyValuePair<string, object>("CustomColor", txtColor.Text));
        //        //parmeters.Add(new KeyValuePair<string, object>("GroupId", txtAPICode.Text));
        //        parmeters.Add(new KeyValuePair<string, object>("TenantId", TenantId));
        //        parmeters.Add(new KeyValuePair<string, object>("StrId", StoreId));

        //        parmeters.Add(new KeyValuePair<string, object>("MinMargin", txtMinMargine.Text));
        //        //parmeters.Add(new KeyValuePair<string, object>("Status", chkStatus.Checked));
        //        parmeters.Add(new KeyValuePair<string, object>("Package", selPackage.Text));
        //        parmeters.Add(new KeyValuePair<string, object>("BusinessType", strBusinessTypes));
        //        parmeters.Add(new KeyValuePair<string, object>("DBConnectionString", txtConnectionString.Text));
        //        parmeters.Add(new KeyValuePair<string, object>("SelectSql", txtSelectSql.Text));
        //        parmeters.Add(new KeyValuePair<string, object>("User", User.Identity.Name));
        //        parmeters.AddRange(headerParams);

        //        if (TenantId > 0)
        //        {
        //            string sql = sqlUpdateTenant;
        //            int result =  DataService.ExecuteSql(sql, SDSStores.ConnectionString, parmeters);
        //            if (result < 1)
        //                lblMessage.Text = "Failure.";
        //            else
        //                lblMessage.Text = "Updated successfully!!";

        //            //int count = SDSStores.Insert();
        //            //if (count > 0)
        //            //    lblMessage.Text = "Store added successfully";
        //            //else
        //            //    lblMessage.Text = "Failed!! Store name already exists.";

        //            //if (count > 0)
        //            //    Reset();
        //        }
        //        else
        //        {
        //            string sql = sqlInsertTenant;
        //            object result = DataService.ExecuteScalar(sql, SDSStores.ConnectionString, parmeters);
        //            if (result is int && Convert.ToInt32(result) == -2)
        //                lblMessage.Text = "Error!! Store name already exists.";
        //            else
        //                lblMessage.Text = "Store added successfully!!";
        //        }

        //    }
        //}

        //private void Reset()
        //{
        //    txtStoreName.Text = "";
        //    txtAPICode.Text = "";
        //    txtConnectionString.Text = "";
        //    txtMinMargine.Text = "";
        //    txtColor.Text = "";
        //    txtSelectSql.Text = "";
        //    chkStatus.Checked = false;
        //    lblMessage.Text = "";
        //    txtCustomDomain.Text = "";

        //    chkCheckout.Checked = false;
        //    chkOnline.Checked = false;
        //    chkPWA.Checked = false;

        //    imgLogo.Visible = false;
        //    imgLogoWhite.Visible = false;
        //    chkDelImgLogo.Visible = false;
        //    chkDelImgLogoWhite.Visible = false;

        //}

        //protected void btnReset_Click(object sender, EventArgs e)
        //{
        //    Reset();
        //    pnlAddForm.Visible = false;
        //    pnlStoresList.Visible = true;
        //}

        //protected void AddEdit_Click(object sender, EventArgs e)
        //{
        //    Reset();
        //    pnlAddForm.Visible = true;
        //    pnlStoresList.Visible = false;
        //    ltrAction.Text = "Add New Store";
        //    btnAdd.Text = "Add Store";
        //    lblCustomDomain.Text = "[title].site.com";
        //    TenantId = -1;
        //    StoreId = -1;
        //    if (sender is LinkButton)
        //    {
        //        LinkButton btn = (LinkButton)sender;
        //        if (!String.IsNullOrEmpty(btn.Attributes["rowId"]))
        //        {
        //            ltrAction.Text = "Edit Store";
        //            btnAdd.Text = "Update Store";
        //            TenantId = Convert.ToInt32(btn.Attributes["rowId"]);

        //            string strSql = SDSStores.SelectCommand + " where a.Id = "+ btn.Attributes["rowId"];
        //            DataTable dt = DataService.GetDataTable(strSql, SDSStores.ConnectionString);
        //            if(dt.Rows.Count > 0)
        //            {
        //                txtStoreName.Text = dt.Rows[0]["Name"].ToString();
        //                lblCustomDomain.Text = $"{txtStoreName.Text.Replace(" ", "").ToLower()}.site.com";
        //                string strHosts = dt.Rows[0]["hosts"].ToString();
        //                foreach(string host in strHosts.Split(','))
        //                {
        //                    if (host != lblCustomDomain.Text)
        //                        txtCustomDomain.Text += (String.IsNullOrEmpty(txtCustomDomain.Text) ? "" : ",") + host;
        //                }
        //                //txtCustomDomain.Text = dt.Rows[0]["hosts"].ToString();
        //                txtMinMargine.Text = dt.Rows[0]["MinMargin"].ToString();
        //                txtColor.Text = dt.Rows[0]["CustomColor"].ToString();
        //                selTheme.Text = dt.Rows[0]["Theme"].ToString();
        //                txtAPICode.Text = dt.Rows[0]["StoreId"].ToString();
        //                if(!String.IsNullOrEmpty(dt.Rows[0]["tStoreId"].ToString()))
        //                    StoreId = Convert.ToInt32(dt.Rows[0]["tStoreId"]);// 
        //                txtConnectionString.Text = dt.Rows[0]["DBConnectionString"].ToString();
        //                txtSelectSql.Text = dt.Rows[0]["SelectSql"].ToString();

        //                string strBusinessType = dt.Rows[0]["BusinessType"].ToString();
        //                if (!String.IsNullOrEmpty(strBusinessType))
        //                {
        //                    foreach(string strBType in strBusinessType.Split(','))
        //                    {
        //                        foreach (ListItem item in chkBusinessTypes.Items)
        //                            if (item.Value == strBType.Trim())
        //                                item.Selected = true;
        //                    }
        //                }

        //                if(!String.IsNullOrEmpty(dt.Rows[0]["Package"].ToString()))
        //                    selPackage.Text = dt.Rows[0]["Package"].ToString();

        //                if (!String.IsNullOrEmpty(dt.Rows[0]["LogoImage"].ToString()))
        //                {
        //                    imgLogo.ImageUrl = dt.Rows[0]["LogoImage"].ToString();
        //                    imgLogo.Visible = true;
        //                    chkDelImgLogo.Visible = true;
        //                }
        //                if (!String.IsNullOrEmpty(dt.Rows[0]["LogoSmall"].ToString()))
        //                {
        //                    imgLogoWhite.ImageUrl = dt.Rows[0]["LogoSmall"].ToString();
        //                    imgLogoWhite.Visible = true;
        //                    chkDelImgLogoWhite.Visible = true;
        //                }

        //                try
        //                {
        //                    chkStatus.Checked = (dt.Rows[0]["Status"].Equals(true));
        //                    chkCheckout.Checked = (dt.Rows[0]["CanCheckout"].Equals(true));
        //                    chkOnline.Checked = (dt.Rows[0]["OnlinePaymentEnabled"].Equals(true));
        //                    chkPWA.Checked = (dt.Rows[0]["ShowPWA"].Equals(true));
        //                }
        //                catch
        //                {

        //                }
        //            }
        //        }
        //    }

        //}

        //protected void chk_CheckedChanged(object sender, EventArgs e)
        //{
        //    CheckBox chk = (CheckBox)sender;
        //    string strRowId = "", strFieldName = "";
        //    try {
        //        strRowId = chk.Attributes["rowId"];
        //        strFieldName = chk.Attributes["fieldname"];
        //        int chkChecked = chk.Checked ? 1 : 0;
        //        if(!String.IsNullOrEmpty(strRowId) && !String.IsNullOrEmpty(strFieldName))
        //        {
        //            string strSql = $"UPDATE AppTenant SET {strFieldName} = {chkChecked} WHERE Id = {strRowId}";
        //            SDSStores.UpdateCommand = strSql;
        //            SDSStores.Update();
        //            SDSStores.UpdateCommand = "";
        //            SDSStores.Select(DataSourceSelectArguments.Empty);
        //            lstStores.DataBind();
        //        }
        //    } catch { }
        //}

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

                //if (ConfigurationManager.AppSettings.Get("VATType") == "2" && String.IsNullOrEmpty(selGST.Text))
                //{
                //    ShowFailure("Validation failed", GSTLabel + $" is mandatory. Please select {GSTLabel} or you can add new {GSTLabel} in the add {GSTLabel} page");
                //    lblMessage.Text = GSTLabel + $" is mandatory. Please select {GSTLabel} or you can add new {GSTLabel} in the add {GSTLabel} page";
                //    return;
                //}

                CreateStore();

                //if (IsBranchView)
                //{
                //    if (APIBranchId > 0)
                //        await EditStore();
                //    else
                //        await CreateStore();
                //}
                //else
                //{
                //    if (storegroupid < 1)
                //        await CreateStore();
                //    else
                //        await EditStore();
                //}
            //}
        }
        private void UpgradeMasterEvent(int type)
        {
            plcStoreTiming.Visible = false;
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
            LinkButton lbtn = (LinkButton)sender;
            if(lbtn != null && !String.IsNullOrEmpty(lbtn.Attributes["brid"]))
            {
                int storeid = Convert.ToInt32(lbtn.Attributes["brid"]);
                if (storeid <= 0)
                    return;

                CurViewType = "2";
                Session["PreviousPage"] = Request.Url.AbsoluteUri;
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

                    //if (selGST.Items.Count <= 1)
                    //    selGST.DataBind();
                    //if (selBankAccount.Items.Count <= 1)
                    //    selBankAccount.DataBind();

                    //if (dr["GSTId"] != DBNull.Value)
                    //{
                    //    if(selGST.Items.Count > 1)
                    //    {
                    //        selGST.ClearSelection();
                    //        selGST.SelectedValue = dr["GSTId"].ToString();
                    //    }
                    //}

                    //if (dr["bankid"] != DBNull.Value)
                    //{
                    //    if (selBankAccount.Items.Count > 1)
                    //    {
                    //        selBankAccount.ClearSelection();
                    //        selBankAccount.SelectedValue = dr["bankid"].ToString();
                    //    }
                    //}

                    //txtIFSC.Text = dr["BankIFSC"].ToString();
                    //txtBankAcNo.Text = dr["BankNo"].ToString();
                    //txtGSTNo.Text = dr["GST"].ToString();
                    //txtLocation.Text = dr["MapLocation"].ToString();
                    //txtBPan.Text = dr["PAN"].ToString();
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

        //protected void btnCancel_Click(object sender, EventArgs e)
        //{
        //    CurViewType = "1";
        //    EditStoreId = -1;
        //    ClearInput();
        //}

        private async void CreateStore()
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
                //if (vattype == "2")
                //{
                //    if (String.IsNullOrEmpty(selGST.SelectedItem.Text))
                //    {
                //        lblMessage.Text = $"{strVATText} is mandatory. Please select {strVATText} from the select list. You can add more {strVATText} in the <a href='/tenant/store/gst-add'>add {strVATText}</a> page";
                //        ShowFailure("Store creation failed", $"{strVATText} is mandatory. Please select {strVATText} from the select list. You can add more {strVATText} in the <a href='/tenant/store/gst-add'>add {strVATText}</a> page");
                //        return;
                //    }

                //}

                //if (String.IsNullOrEmpty(selBankAccount.Text))
                //{
                //    lblMessage.Text = "Bank account is mandatory. Please select Bank Account from the select list. You can add more Bank Accounts in the <a href='/Tenant/Store/BankAccount-add'>add Bank Account</a> page";
                //    ShowFailure("Store creation failed", "Bank account is mandatory. Please select Bank Account from the select list. You can add more Bank Accounts in the <a href='/Tenant/Store/BankAccount-add'>add Bank Account</a> page");
                //    return;
                //}

                // Limit branch for intra state trading only? (0: no restriction, 1: Intra state only)
                int tradeRestrictionType = (ConfigurationManager.AppSettings.Get("VATType") == "2" ? 1 : 0);
                int taxtype = (tradeRestrictionType == 1 ? 1 : 0);
                //if (!String.IsNullOrEmpty(selGST.Text))
                //{
                //    List<KeyValuePair<string, object>> gstParams = new List<KeyValuePair<string, object>>();
                //    gstParams.Add(new KeyValuePair<string, object>("storegroupid", curUser.StoreGroupId));
                //    gstParams.Add(new KeyValuePair<string, object>("gstid", selGST.Text));
                //    DataTable dtGST = DataService.GetDataTable("SELECT * FROM GST g WHERE g.id=@gstid and tenantid=@storegroupid", parmeters: gstParams); //  and not exists(select * from StoreBranch where GSTID= g.id)
                //    if (dtGST == null || dtGST.Rows.Count <= 0)
                //    {
                //        lblMessage.Text = $"Sorry, the {strVATText} selected is not valid or not verified. Please try with another {strVATText}. You can add more {strVATText} in the <a href='/tenant/store/gst-add'>add {strVATText}</a> page";
                //        ShowFailure("Store creation failed", $"Sorry, the {strVATText} selected is not valid or not verified. Please try with another {strVATText}. You can add more {strVATText} in the <a href='/tenant/store/gst-add'>add {strVATText}</a> page");
                //        return;
                //    }

                //    gstId = Convert.ToInt32(dtGST.Rows[0]["id"]);
                //    gst = dtGST.Rows[0]["gstin"].ToString();

                //    if (ConfigurationManager.AppSettings.Get("VATType") == "2")
                //    {
                //        try
                //        {
                //            Services.GSTStatus CurGSTStatus = (dtGST.Rows[0]["isverified"] == DBNull.Value ? Services.GSTStatus.VerificationSkipped : (Convert.ToBoolean(dtGST.Rows[0]["isverified"]) ? Services.GSTStatus.Verified : Services.GSTStatus.VerificationSkipped));
                //            GSTData MyGSTData = System.Text.Json.JsonSerializer.Deserialize<GSTData>((string)dtGST.Rows[0]["gstdata"]);
                //            if (CurGSTStatus != Services.GSTStatus.Verified || MyGSTData == null || MyGSTData.result == null || MyGSTData.result.result == null
                //                    || MyGSTData.result.result.gstnDetailed == null)
                //            {
                //                if (MyGSTData.result.result.gstnDetailed.taxPayerType == "REGULAR")
                //                    tradeRestrictionType = 1;

                //                switch (MyGSTData.result.result.gstnDetailed.taxPayerType)
                //                {
                //                    case "REGULAR":
                //                        taxtype = 1;
                //                        break;
                //                    case "COMPOSITION":
                //                        taxtype = 2;
                //                        break;
                //                    case "UNREGISTERED":
                //                        taxtype = 3;
                //                        break;
                //                }
                //            }
                //        }
                //        catch { tradeRestrictionType = 1; }

                //    }

                //}

                //if (!String.IsNullOrEmpty(selBankAccount.Text))
                //{
                //    List<KeyValuePair<string, object>> bankParams = new List<KeyValuePair<string, object>>();
                //    bankParams.Add(new KeyValuePair<string, object>("storegroupid", curUser.StoreGroupId));
                //    bankParams.Add(new KeyValuePair<string, object>("bankaccount", selBankAccount.Text));
                //    DataTable dtBank = DataService.GetDataTable("SELECT * FROM BankAccount WHERE id=@bankaccount and tenantid=@storegroupid", parmeters: bankParams);
                //    if (dtBank == null || dtBank.Rows.Count <= 0)
                //    {
                //        lblMessage.Text = "Sorry, the Bank Account selected is not valid or not verified. Please try with another Bank Account. You can add more account in the <a href='/tenant/store/bankaccount-add'>add Bank Account</a> page";
                //        ShowFailure("Store creation failed", "Sorry, the Bank Account selected is not valid or not verified. Please try with another Bank Account. You can add more account in the <a href='/tenant/store/bankaccount-add'>add Bank Account</a> page");
                //        return;
                //    }

                //    bankId = Convert.ToInt32(dtBank.Rows[0]["id"]);
                //}
                string storecontactperson = txtCnName.Text;
                string storephone = txtCnNo.Text;
                string storeEmail = "";
            int expressdelivery = cbexpressdelivery.Checked ? 1 : 0;
            int courierdelivery = cbcourierdelivery.Checked ? 1 : 0;
            if (txtCnEmail.Text == "")
                {
                    storeEmail = curUser.Email;
                }
                else
                {
                    storeEmail = txtCnEmail.Text;
                }
                try
                {
                    var storebranch = Services.StoreService.CreateStore(txtAddr1.Text, strBrShort, curUser.APIStoreId, txtAddr2.Text, txtAddr3.Text, txtAddr4.Text, selDistrict.SelectedItem.Text, 
                        Convert.ToInt32(selState.Text), Convert.ToInt32(selDistrict.Text), txtPinCode.Text, storeEmail, storephone, 
                        hidLat.Value, hidLong.Value, storecontactperson, gst, tradeRestriction:tradeRestrictionType, taxType: taxtype, directDelivery: expressdelivery, courierDelivery: courierdelivery);
                    int branchId = storebranch;
                    hdbranchId.Value= branchId.ToString();
                   UpdateRelationshipOfficerOrConsultingPartner(branchId.ToString(), this.CurrentUser.APIStoreId, hdAreaId.Value, hdnRoId.Value,hdnId.Value);


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
                        if(gstId > 0)
                            brParmeters.Add(new KeyValuePair<string, object>("gstid", gstId));
                        else
                            brParmeters.Add(new KeyValuePair<string, object>("gstid", DBNull.Value));

                        //brParmeters.Add(new KeyValuePair<string, object>("bankid", bankId));
                        if (bankId > 0)
                            brParmeters.Add(new KeyValuePair<string, object>("bankid", gstId));
                        else
                            brParmeters.Add(new KeyValuePair<string, object>("bankid", DBNull.Value));
                        DataService.ExecuteSP("CreateStoreBranch", parmeters: brParmeters);

                    // Remove Redis cache entry
                    var cacheService = new RedisCacheService();
                    string cachekey = $"Retl.AppTenant.pendingtasks.count.{this.CurrentUser.APIStoreId}";
                    await cacheService.RemoveAsync(cachekey);

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

                            //var cacheService = new RedisCacheService();
                            //string cachekey = $"Retl.AppTenant.host." + this.CurrentUser.PublicSiteUrl.ToLower();
                            //await cacheService.RemoveAsync(cachekey);

                        }

                    //ShowSuccess("Store Created Successfully!!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Your store has been created successfully!</a></h5>" + strcontent);

                    ShowSuccess("Success!", "Store Created Successfully!!", "/Tenant/Branches?type=ManageBranch");
                }
                    catch(Exception ex2)
                    {
                        ShowFailure("Store creation failed", "Sorry, there is a technical error happened. Store creation is partially executed.<br/>Error: " + ex2.Message);
                        return;
                    }
                }
                catch(Exception ex)
                {
                    ShowFailure("Store creation failed", "Sorry, there is a technical error happened. Please contact support.<br/>Error: "+ ex.Message);
                }

                //btnAdd.Enabled = false;
                //Page.ClientScript.RegisterClientScriptBlock(typeof(string), "StoreCreated",
                //@"<script language='javascript'>$(document).ready(function () {showSuccess('Store Created Successfully. Please go to the store settings to setup inventory.'); window.location.href='/StoreSettings'; }); </script>");


            //}

        }

        private async void EditStore(int storeId)
        {
            //if (Page.IsValid)
            //{
            string vattype = ConfigurationManager.AppSettings.Get("VATType");
                string strVATText = (System.Configuration.ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT");
                int gstId = -1, bankId = -1;
                 GetRODetails(storeId);
            Service.User curUser = this.CurrentUser;// .GetCustomerByUsername(Page.User.Identity.Name);
                //int storegroupId = curUser.APIStoreId;
                //int tenantId = curUser.StoreGroupId;

                //List<string> strExcempt = new List<string>();
                //var tblBrShort = DataServiceMySql.GetDataTable("SELECT DISTINCT branch_shortname FROM finascop_branch", UserService.GetAPIConnectionString());
                //if (tblBrShort != null && tblBrShort.Rows.Count > 0)
                //{
                //    strExcempt = tblBrShort.AsEnumerable().Select(item => string.Format("{0}", item["branch_shortname"])).ToList();
                //}
                //string strBrShort = Common.RandomString(4, strExcempt?.ToArray());
                //if (String.IsNullOrEmpty(strBrShort))
                //{
                //    lblMessage.Text = "Sorry, there is a technical error on store code creation. Please try again later or contact support for more details";
                //    ShowFailure("Store creation failed", "Sorry, there is a technical error on store code creation. Please try again later or contact support for more details");
                //    return;
                //}
                List<KeyValuePair<string, object>> storeParams = new List<KeyValuePair<string, object>>();
                storeParams.Add(new KeyValuePair<string, object>("storegroupid", curUser.StoreGroupId));
                storeParams.Add(new KeyValuePair<string, object>("id", storeId));
                DataTable dtStore = DataService.GetDataTable("select * from StoreBranch where id=@id and StoreId=@storegroupid", parmeters: storeParams);
                if(dtStore == null || dtStore.Rows.Count <= 0 || dtStore.Rows[0]["APIBranchId"] == null || Convert.ToInt32(dtStore.Rows[0]["APIBranchId"]) <=0)
                {
                    lblMessage.Text = "Process Failed. Invalid Store. Operation failed because of the selected store is invalid.";
                    ShowFailure("Store creation failed", "Invalid Store. Operation failed because of the selected store is invalid");
                    return;
                }
                //if (vattype == "2")
                //{
                //    if (String.IsNullOrEmpty(selGST.SelectedItem.Text))
                //    {
                //        lblMessage.Text = $"{strVATText} is mandatory. Please select {strVATText} from the select list. You can add more {strVATText} in the <a href='/tenant/store/gst-add'>add {strVATText}</a> page";
                //        ShowFailure("Store creation failed", "GST is mandatory. Please select GST from the select list. You can add more GST in the <a href='/tenant/store/gst-add'>add GST</a> page");
                //        return;
                //    }
                //}

                //if (String.IsNullOrEmpty(selBankAccount.Text))
                //{
                //    lblMessage.Text = "Bank account is mandatory. Please select Bank Account from the select list. You can add more Bank Accounts in the <a href='/tenant/store/BankAccount-add'>add Bank Account</a> page";
                //    ShowFailure("Store creation failed", "Bank account is mandatory. Please select Bank Account from the select list. You can add more Bank Accounts in the <a href='/tenant/store/BankAccount-add'>add Bank Account</a> page");
                //    return;
                //}

                // Limit branch for intra state trading only? (0: no restriction, 1: Intra state only)
                int tradeRestrictionType = (ConfigurationManager.AppSettings.Get("VATType") == "2" ? 1 : 0);
                int taxtype = (tradeRestrictionType == 1 ? 1 : 0);
            //if (!String.IsNullOrEmpty(selGST.Text))
            //{
            //    List<KeyValuePair<string, object>> gstParams = new List<KeyValuePair<string, object>>();
            //    gstParams.Add(new KeyValuePair<string, object>("storegroupid", curUser.StoreGroupId));
            //    gstParams.Add(new KeyValuePair<string, object>("gstin", selGST.SelectedItem.Text));
            //    gstParams.Add(new KeyValuePair<string, object>("branchid", storeId));
            //    DataTable dtGST = DataService.GetDataTable("SELECT * FROM GST g WHERE g.gstin=@gstin and tenantid=@storegroupid", parmeters: gstParams); //  and not exists(select * from StoreBranch where id <> @branchid and GSTID= g.id and StoreId=@storegroupid)
            //    if (dtGST == null || dtGST.Rows.Count <= 0)
            //    {
            //        lblMessage.Text = $"Sorry, the {strVATText} selected is not valid or used for other store already. Please try with another {strVATText}. You can add more {strVATText} in the <a href='/tenant/store/gst-add'>add {strVATText}</a> page";
            //        ShowFailure("Store creation failed", $"Sorry, the {strVATText} selected is not valid or used for other store already. Please try with another {strVATText}. You can add more {strVATText} in the <a href='/tenant/store/gst-add'>add {strVATText}</a> page");
            //        return;
            //    }

            //    gstId = Convert.ToInt32(dtGST.Rows[0]["id"]);
            //    if (ConfigurationManager.AppSettings.Get("VATType") == "2")
            //    {
            //        try { 
            //            Services.GSTStatus CurGSTStatus = (dtGST.Rows[0]["isverified"] == DBNull.Value ? Services.GSTStatus.VerificationSkipped : (Convert.ToBoolean(dtGST.Rows[0]["isverified"]) ? Services.GSTStatus.Verified : Services.GSTStatus.VerificationSkipped)); 
            //         GSTData MyGSTData = System.Text.Json.JsonSerializer.Deserialize<GSTData>((string)dtGST.Rows[0]["gstdata"]);
            //            if (CurGSTStatus != Services.GSTStatus.Verified || MyGSTData == null || MyGSTData.result == null || MyGSTData.result.result == null
            //                    || MyGSTData.result.result.gstnDetailed == null)
            //            {
            //                if(MyGSTData.result.result.gstnDetailed.taxPayerType == "REGULAR")
            //                    tradeRestrictionType = 1;

            //                switch (MyGSTData.result.result.gstnDetailed.taxPayerType)
            //                {
            //                    case "REGULAR":
            //                        taxtype = 1;
            //                        break;
            //                    case "COMPOSITION":
            //                        taxtype = 2;
            //                        break;
            //                    case "UNREGISTERED":
            //                        taxtype = 3;
            //                        break;
            //                }
            //            }

            //        } 
            //        catch { tradeRestrictionType = 1; }

            //    }
            //}

            //if (!string.IsNullOrEmpty(selBankAccount.Text))
            //{
            //    List<KeyValuePair<string, object>> bankParams = new List<KeyValuePair<string, object>>();
            //    bankParams.Add(new KeyValuePair<string, object>("storegroupid", curUser.StoreGroupId));
            //    bankParams.Add(new KeyValuePair<string, object>("bankaccount", selBankAccount.Text));
            //    DataTable dtBank = DataService.GetDataTable("SELECT * FROM BankAccount WHERE id=@bankaccount and tenantid=@storegroupid", parmeters: bankParams);
            //    if (dtBank == null || dtBank.Rows.Count <= 0)
            //    {
            //        lblMessage.Text = "Sorry, the Bank Account selected is not valid or not verified. Please try with another Bank Account. You can add more account in the <a href='/tenant/store/bankaccount-add'>add Bank Account</a> page";
            //        ShowFailure("Store creation failed", "Sorry, the Bank Account selected is not valid or not verified. Please try with another Bank Account. You can add more account in the <a href='/tenant/store/bankaccount-add'>add Bank Account</a> page");
            //        return;
            //    }

            //    bankId = Convert.ToInt32(dtBank.Rows[0]["id"]);
            //}
            int expressdelivery = cbexpressdelivery.Checked ? 1 : 0;
            int courierdelivery = cbcourierdelivery.Checked ? 1 : 0;
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
                try
                {
                //var storebranch = Services.StoreService.CreateStore(txtAddr1.Text, strBrShort, curUser.APIStoreId, txtAddr2.Text, txtAddr1.Text, Convert.ToInt32(selState.Text), Convert.ToInt32(selDistrict.Text), txtPinCode.Text, Page.User.Identity.Name, curUser.Phone, hidLat.Value, hidLong.Value, curUser.Email);
                //int branchId = storebranch;
                       
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
                        expressdelivery = cbexpressdelivery.Checked ? 1 : 0;
                        courierdelivery = cbcourierdelivery.Checked ? 1 : 0;
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
                        Services.StoreService.UpdateStore(Convert.ToInt32(dtStore.Rows[0]["APIBranchId"]), txtAddr1.Text, curUser.APIStoreId, txtAddr2.Text, txtAddr3.Text, txtAddr4.Text,selDistrict.SelectedItem.Text, Convert.ToInt32(selState.Text), Convert.ToInt32(selDistrict.Text), txtPinCode.Text, storeEmail, storephone, storecontactperson, hidLat.Value, hidLong.Value, tradeRestrictionType, taxtype, expressdelivery, courierdelivery);

                    // Remove Redis cache entry
                    var cacheService = new RedisCacheService();
                    string cachekey = $"Retl.AppTenant.pendingtasks.count.{this.CurrentUser.APIStoreId}";
                    await cacheService.RemoveAsync(cachekey);

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
                        string Location = selDistrict.SelectedItem.Text;
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
                        //ShowSuccess("Store Edited Successfully!!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Store has been updated successfully!</a></h5>" + strcontent);
                    ShowSuccess("Success!", "Store has been updated successfully!", "/Tenant/Branches?type=ManageBranch");


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

                //btnAdd.Enabled = false;
                //Page.ClientScript.RegisterClientScriptBlock(typeof(string), "StoreCreated",
                //@"<script language='javascript'>$(document).ready(function () {showSuccess('Store Created Successfully. Please go to the store settings to setup inventory.'); window.location.href='/StoreSettings'; }); </script>");


            //}

        }


        //private void ShowSuccess(string title, string content)
        //{
        //    ltrErrorPopupTitle.Text = title;
        //    ltrErrorPopupText.Text = content;
        //    Type cstype = this.GetType();
        //    String csname1 = "PopupScript";
        //    ClientScriptManager cs = Page.ClientScript;
        //    ltrSuccessTitle.Text = title;
        //    ltrSuccessContent.Text = content;

        //    StringBuilder cstext1 = new StringBuilder();
        //    cstext1.Append("<script type=text/javascript> $('#modaldemo4').modal('show'); </");
        //    cstext1.Append("script>");

        //    cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());

        //    //    cs.RegisterClientScriptBlock(cstype, csname1, @"<script type='text/javascript'>$('#modaldemo4').on('hidden.bs.modal', function (e) {
        //    //      window.location.href='/bankaccount';
        //    //});</script>");
        //}

        private void ShowSuccess(string title, string content, string redirect = "")
        {
            Type cstype = this.GetType();
            String csname1 = "PopupScript";
            ClientScriptManager cs = Page.ClientScript;
            ltrSuccessTitle.Text = title;
            ltrSuccessContent.Text = content;

            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> $('#modaldemo4').modal('show'); {(string.IsNullOrEmpty(redirect) ? "" : "$('#modaldemo4').on('hidden.bs.modal', function (e) {window.location.href = '" + redirect + "'; });")}</");
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
                plcStoreTiming.Visible = false;
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
            if(EditAPIStoreId <= 0)
            {
                ShowFailure("Failure", "Invalid Store");
                return;
            }
            if(String.IsNullOrEmpty(txtTimeFrom.Text) || String.IsNullOrEmpty(txtTimeTo.Text))
            {
                ShowFailure("Failure", "Invalid Input");
                return;
            }
            var timeFrom = DateTime.Parse(txtTimeFrom.Text);
            var timeTo = DateTime.Parse(txtTimeTo.Text);

            if(timeFrom > timeTo)
            {
                ShowFailure("Failure", "Time To should be greater than time from.");
                return;
            }

            string sql = "INSERT INTO branch_timings(branch_id, br_open_time, br_close_time, createdBy) SELECT br_ID, @opentime, @closetime, 1 FROM finascop_branch WHERE br_ID=@brid AND br_StoreGroup=@StoreId";
            try
            {
                DateTime timeFrombranch = DateTime.Parse(txtTimeFrom.Text.Replace('.', ':'));
                DateTime timeTobranch = DateTime.Parse(txtTimeTo.Text.Replace('.', ':'));
                string strTimeFrom = timeFrom.ToString("HH:mm:ss").Replace('.', ':');
                string strTimeTo = timeTo.ToString("HH:mm:ss").Replace('.', ':');
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
                ShowFailure("Failure", "Sorry, there is a technical error happened. Error: " + ex2.Message);
                return;
            }


        }

        protected void SDSOnOffTime_Deleting(object sender, SqlDataSourceCommandEventArgs e)
        {
            if (e.Command.Parameters.Contains("storegroupid"))
                e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;

        }

        protected void btnSave_Click(object sender, EventArgs e)
        {
            //List<KeyValuePair<string, object>> sqlParams = new List<KeyValuePair<string, object>>();
            //sqlParams.Add(new KeyValuePair<string, object>("expressId", selExpDelivRule.Text));
            //sqlParams.Add(new KeyValuePair<string, object>("slottedId", selScheduleRule.Text));
            //sqlParams.Add(new KeyValuePair<string, object>("courierId", selCourDelivRule.Text));
            //sqlParams.Add(new KeyValuePair<string, object>("branchId", (EditStoreId > 0 ? EditStoreId : 0)));
            //string strUpdateSql = $"UPDATE finascop_branch SET br_rdrIdExpress=@expressId, br_rdrIdSlotted=@slottedId, br_rdrIdCourier=@courierId WHERE br_ID=@branchId";
            //DataServiceMySql.ExecuteSql(strUpdateSql, Service.UserService.GetAPIConnectionString(), sqlParams);
            //ShowSuccess("Success", "Delivery rule updated.");
            ////ShowSuccess("Success!", "Delivery slot created successfully", "/DeliverySlot");

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
            if(dl.ID == "selExpDelivRule")
            {
                sqlUpdateField = "br_rdrIdExpress=@expressId";
                sqlParams.Add(new KeyValuePair<string, object>("expressId", deliRule));
            }
            else if(dl.ID == "selCourDelivRule")
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
                    SDSDeliModes.Select(DataSourceSelectArguments.Empty);
                    rptDeliModes.DataBind();
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
                      Common.ShowCustomAlert( this.Page,"Success", "Relationship officer added / changed successfully!!",true, "/Tenant/Branches");
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


        //protected void SDSExpDeliv_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        //{
        //    e.Command.Parameters["branchid"].Value = 21;

        //}

        
    }
}