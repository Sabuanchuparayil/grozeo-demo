using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.Drivers;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Navigations
{
    public partial class StoreSettings : Base.BasePartnerPage
    {
        private bool EditView { get; set; }

        private void UpdateEvent(int type)
        {
            EditView = false;

            ctrlCreateStore1.Visible = false;
            plcConf.Visible = true;
            //ctrlCreateStore1.ResetBtn.Text = "Clear";

        }

        public int InventoryMapType
        {
            get
            {
                return Convert.ToInt32(ViewState["InventoryMapType"] ?? -1);
            }
            set
            {
                ViewState["InventoryMapType"] = value;
            }
        }
        private string[] BusinessTypes { get; set; }

        protected void Page_Load(object sender, EventArgs e)
        {
            PopupUpgradeConsent1.ParentButtonBinding += new Controls.PopupUpgradeConsent.ParentCustomHandler(UpdateEvent);
            plcWizard.Visible = this.CurrentUser.StoreGroupId <= 0 || (new int[] { 5, 6, 7 }).Contains(this.CurrentUser.TenantStage); // > 4;
            //plcNoneWizard.Visible = plcWizardBrudcrumb.Visible = !plcWizard.Visible;
            //ctrlInventorySetup1.RefreshInventoryBinding += new Controls.StoreSettings.ctrlInventorySetup.ParentCustomHandler(RefreshInventoryMap);
            ctrlCreateStore1.ParentButtonBinding += new Controls.StoreSettings.ctrlCreateStore.ParentCustomHandler(UpdateEvent);

            //if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
            //{
            //    panDetails.Visible = true;
            //    fssaiDetails.Visible = true;
            //}
            //else
            //{
            //    panDetails.Visible = false;
            //    fssaiDetails.Visible = false;
            //}

            //ltrStrPriceDefaultText.Visible = ltrStrInventoryDefaultText.Visible = true;
            //plcPrice.Visible = plcInventory.Visible = false;
            //cTitle.Visible = false;
            if (!IsPostBack)
            {
                ctrlCreateStore1.Visible = true;
                plcConf.Visible = false;
                LoadStoreInfo();
            }

        }

        private void LoadStoreInfo()
        {
            int storegroupid = this.CurrentUser.StoreGroupId; //Request.QueryString["sid"];
            int apistoregroupid = this.CurrentUser.APIStoreId;

            if (storegroupid > 0)
            {
                string strSql = $"SELECT a.Id, a.Name, a.Theme, a.APIUrl, a.CanCheckout, a.CustomColor, a.FavIcoImage, " +
                    $"Stuff((SELECT ',' + (t.HostAddress) FROM Host t WHERE a.Id LIKE t.TenantId order by id desc FOR Xml Path('')), 1, 1, '') as hosts, " +
                    $"a.LogoImage, a.LogoSmall, a.OnlinePaymentEnabled, a.ShowPWA, a.Status, a.StoreId as StoreGroupId, " +
                    $"s.APICode, s.BusinessType, s.SecondaryBusinessTypes, s.DisplayName, s.CreatedBy, s.CreatedOn, s.DBConnectionString, s.GroupId, s.Id as StoreId, " +
                    $"s.InventoryFile, s.MinMargin, s.Name as StoreName, s.Package, s.SelectSql, s.UpdatedOn, s.UpdatedBy, s.InventoryMapType " +
                    $"FROM AppTenant a inner join Store s on a.Id=s.Tenantid WHERE a.Id={storegroupid}";//SDSStores.SelectCommand + " where a.Id = " + strEditId;
                // (Page.User.IsInRole("SuperAdmin") || Page.User.IsInRole("RetalineProAgent") ? "0" : "1")
                if (!(Page.User.IsInRole("SuperAdmin") || Page.User.IsInRole("RetalineProAgent")))
                    strSql += " AND a.Id in (SELECT m.StoreGroupId FROM User_UserRole_Mapping m INNER JOIN [User] u on u.Id=m.UserId WHERE u.Email like @user)";
                List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                prms.Add(new KeyValuePair<string, object>("user", Page.User.Identity.Name));
                DataTable dt = DataService.GetDataTable(strSql, parmeters: prms);
                if (dt.Rows.Count > 0)
                {
                    DataRow dr = dt.Rows[0];

                    //APIStore=new Core.BussinessModel.Store.Store() { BranchId= storebranch, BranchName= txtLocation.Text, Address= txtAddr1.Text, Id=storegroupId, sto }
                    //var stores = Core.Services.APIService.GetStores((int)dr["StoreGroupId"]);
                    var stores = Core.Services.APIService.GetStores(apistoregroupid);
                    if (stores != null && stores.Count > 0)
                    {
                        var store = stores.OrderByDescending(b => b.BranchId).OrderByDescending(b => b.IsDefault).FirstOrDefault(); //stores.FirstOrDefault(s => s.IsDefault==1);
                        ctrlCreateStore1.APIStore = store;
                        ctrlCreateStore1.APIBranchId = store.BranchId;
                    }

                    this.Title = dr["Name"].ToString();
                    ltrPageTitle.Text = dr["Name"].ToString();
                    ltrStoreTitle.Text = dr["DisplayName"].ToString();
                    // ltrStoreName.Text = dr["Name"].ToString();
                    ctrlCreateStore1.Visible = false;// ltrStrPriceDefaultText.Visible = ltrStrInventoryDefaultText.Visible = false;
                    plcConf.Visible = true;// plcPrice.Visible = plcInventory.Visible = true;
                    string strLogo = dr["LogoSmall"].ToString();
                    string strTheme = dr["Theme"].ToString();
                    string strCustomColor = dr["CustomColor"].ToString();
                    //txtDBConnection.Text = dr["DBConnectionString"].ToString();
                    //txtSelectSql.Text = dr["SelectSql"].ToString();
                    string strBusinessTypes = dr["BusinessType"].ToString();
                    if (!String.IsNullOrEmpty(strBusinessTypes))
                        BusinessTypes = strBusinessTypes.Trim().Split(',');
                    //if(BusinessTypes != null)
                    //    foreach(string strType in BusinessTypes)
                    //        ltrBusinessTypes.Text += (String.IsNullOrEmpty(ltrBusinessTypes.Text)?"":", ")+ strType;
                    ltrBusinessTypes.Text = strBusinessTypes;

                    string strSecondaryBTypes = dr["SecondaryBusinessTypes"].ToString();
                    if (!String.IsNullOrEmpty(strSecondaryBTypes))
                        ltrBusinessTypes.Text += (String.IsNullOrEmpty(ltrBusinessTypes.Text) ? "" : ", ") + strSecondaryBTypes;

                    ltrBusinessTypes.Text = (Common.ShrinkText(ltrBusinessTypes.Text, 34));

                    string strInventoryMapType = dr["InventoryMapType"].ToString();
                    if (!String.IsNullOrEmpty(strInventoryMapType))
                    {
                        InventoryMapType = Convert.ToInt32(strInventoryMapType);
                        //ltrInventorySource.Text = String.Format(" - {0}", (InventoryMapType == 3 ? "Database Connection" : (InventoryMapType == 2 ? "CSV Upload" : "Master Data")));
                    }
                    string strThemeDefaultColor = "";
                    string strThemeDefaultLogo = "";
                    //if (!String.IsNullOrEmpty(strTheme))
                    //{
                    //    switch (strTheme)
                    //    {
                    //        case "Retaline":
                    //            strThemeDefaultColor = "#595856";
                    //            strThemeDefaultLogo = "https://www.demo.retaline.net/img/logo-new.png";
                    //            break;
                    //        case "Consumerfed":
                    //            strThemeDefaultColor = "#303391";
                    //            strThemeDefaultLogo = "https://www.consumerfed.in/themes/Consumerfed/img/CFed-cart-logo-new.png";
                    //            break;
                    //        case "DhanyaNew":
                    //            strThemeDefaultColor = "#da251c";
                    //            strThemeDefaultLogo = "https://shop.dhanyasupermarket.com/themes/DhanyaNew/img/Dhanya_White_Logo.png";
                    //            break;
                    //        case "Jewel":
                    //        case "916Cart":
                    //            strThemeDefaultColor = "#a67c00";
                    //            strThemeDefaultLogo = "https://www.916cart.com/themes/916Cart/img/916cart-logo-white.png";
                    //            break;
                    //    }
                    //}

                    //if (!String.IsNullOrEmpty(strCustomColor))
                    //    imgStore.Style.Add("background-color", strCustomColor);
                    //else if(!String.IsNullOrEmpty(strThemeDefaultColor))
                    //    imgStore.Style.Add("background-color", strThemeDefaultColor);

                    if (!String.IsNullOrEmpty(strLogo))
                        imgStore.Src = strLogo;
                    //else
                    //    imgStore.Visible = false;
                    //else if(!String.IsNullOrEmpty(strThemeDefaultLogo))
                    //    imgStore.Src = strThemeDefaultLogo;
                    string strHost = dr["hosts"].ToString(); ;
                    string[] strHosts = strHost.Split(',');
                    if (strHost.Length > 0)
                    {
                        hlPublicSite.HRef = strHosts[0];
                        if (!hlPublicSite.HRef.StartsWith("http"))
                            hlPublicSite.HRef = "http://" + hlPublicSite.HRef.TrimStart(new char[] { '/' });
                        hlPublicSite.Target = "_blank";
                    }

                    ltrCreatedon.Text = String.Format("{0:dd-MMMM-yyyy}", dr["CreatedOn"]);
                    //ltrDomains.Text = strHost; //dr["hosts"].ToString();
                    //hlDomain.Text = hlDomain.NavigateUrl = strHosts[0];
                    foreach (string host in strHosts)
                    {
                        if (!String.IsNullOrEmpty(host))
                        {
                            plcDomains.Controls.Add(new HyperLink() { Text = host, NavigateUrl = (host.Trim().StartsWith("http://") || host.Trim().StartsWith("https://") ? "" : "http://") + host.Trim(), Target = "_blank" });
                            break;
                        }
                    }
                    //iIsActive.Attributes["class"] = (((bool)dr["Status"])? "far fa-check-circle text-success" : "far fa-circle text-danger"); // 
                    //iCanCheckout.Attributes["class"] = (((bool)dr["CanCheckout"]) ? "far fa-check-circle text-success" : "far fa-circle text-danger");
                    //iOnlinePayment.Attributes["class"] = (((bool)dr["OnlinePaymentEnabled"]) ? "far fa-check-circle text-success" : "far fa-circle text-danger");
                    //iPWA.Attributes["class"] = (((bool)dr["ShowPWA"]) ? "far fa-check-circle text-success" : "far fa-circle text-danger");

                    //string sqlBranch = $"SELECT TOP 1 * FROM StoreBranch WHERE Storeid={storegroupid} ORDER BY IsDefault DESC";
                    //DataTable dtBranch = DataService.GetDataTable(sqlBranch);
                    int bankAccounts = 0, storesWithoutBank = 0, storesWithBank = 0, bankLinkedToStore = 0, gstscount = 0, gstNotLinkedToStore = 0,
                        totalStores = 0, storesOnline = 0, gstnNotVerified = 0, fssaiCount = 0, fssaiLinkedToStore = 0, fssaiNotLinkedToStore = 0;

                    DataTable tblStoreSummary = DataService.GetDataTable("StoreSummary", parmeters: new List<KeyValuePair<string, object>> { new KeyValuePair<string, object>("tenantid", storegroupid) }, isSP: true);
                    if (tblStoreSummary != null && tblStoreSummary.Rows.Count > 0)
                    {
                        bankAccounts = (tblStoreSummary.Rows.Count > 1 ? Convert.ToInt32(tblStoreSummary.Rows[1][0]) : 0);
                        //storesWithoutBank = (tblStoreSummary.Rows.Count > 0 ? Convert.ToInt32(tblStoreSummary.Rows[0][0]) : 0);
                        storesWithBank = (tblStoreSummary.Rows.Count > 0 ? Convert.ToInt32(tblStoreSummary.Rows[0][0]) : 0);
                        bankLinkedToStore = (tblStoreSummary.Rows.Count > 2 ? Convert.ToInt32(tblStoreSummary.Rows[2][0]) : 0);
                        gstscount = (tblStoreSummary.Rows.Count > 3 ? Convert.ToInt32(tblStoreSummary.Rows[3][0]) : 0);
                        gstNotLinkedToStore = (tblStoreSummary.Rows.Count > 4 ? Convert.ToInt32(tblStoreSummary.Rows[4][0]) : 0);
                        gstnNotVerified = (tblStoreSummary.Rows.Count > 5 ? Convert.ToInt32(tblStoreSummary.Rows[5][0]) : 0);
                        storesWithoutBank = (tblStoreSummary.Rows.Count > 0 ? Convert.ToInt32(tblStoreSummary.Rows[6][0]) : 0);
                        try { fssaiCount = (tblStoreSummary.Rows.Count > 7 ? Convert.ToInt32(tblStoreSummary.Rows[7][0]) : 0); } catch { fssaiCount = 0; }
                        try { fssaiNotLinkedToStore = (tblStoreSummary.Rows.Count > 8 ? Convert.ToInt32(tblStoreSummary.Rows[8][0]) : 0); } catch { fssaiNotLinkedToStore = 0; }
                        fssaiLinkedToStore = fssaiCount - fssaiNotLinkedToStore; //(tblStoreSummary.Rows.Count > 7 ? Convert.ToInt32(tblStoreSummary.Rows[7][0]) : 0);
                        fssaiLinkedToStore = (fssaiLinkedToStore < 0 ? 0 : fssaiLinkedToStore);
                    }
                    ltrBankAccountNum.Text = String.Format("{0}", bankAccounts);
                    ltrStoresWithBank.Text = storesWithBank.ToString();
                    ltrGSTINNum.Text = gstscount.ToString();
                    ltrGSTNotLinked.Text = gstNotLinkedToStore.ToString();
                    ltrAccountsLinkedToStore.Text = bankLinkedToStore.ToString();
                    ltrGSTINSNotVerified.Text = gstnNotVerified.ToString();
                    ltrFssaiLinkedToStore.Text = fssaiLinkedToStore.ToString();
                    ltrFssaiNotLinked.Text = fssaiNotLinkedToStore.ToString();
                    ltrFSSAICount.Text = fssaiCount.ToString();

                    //ltrBankName.Text = drBranch["BankName"].ToString();
                    //ltrBrankBranch.Text = drBranch["BankBranch"].ToString();
                    //ltrGST.Text = drBranch["GST"].ToString();
                    //ltrGSTLegalName.Text = Common.ShrinkText(drBranch["GSTINName"].ToString(), 37);
                    //ltrGSTAddress.Text = Common.ShrinkText(drBranch["GSTAddress"].ToString(), 40);

                    string sqlBranch = $"SELECT TOP 1 * FROM StoreBranch WHERE Storeid={storegroupid} ORDER BY IsDefault DESC";
                    DataTable dtBranch = DataService.GetDataTable(sqlBranch);
                    int branchProgressVal = 0;
                    if (dtBranch != null && dtBranch.Rows.Count > 0)
                    {
                        DataRow drBranch = dtBranch.Rows[0];

                        //ltrBrAddress.Text = Common.ShrinkText(String.Format("{0}, {1} {2} {3}", drBranch["Addr"], drBranch["District"], drBranch["State"], drBranch["Pin"]), 45);
                        //ltrBrDistrict.Text = drBranch["District"].ToString();
                        //ltrBrPin.Text = drBranch["Pin"].ToString();
                        //ltrBrState.Text = drBranch["State"].ToString();

                        // drBranch["BankAddr"].ToString();
                        // drBranch["BankBranch"].ToString();

                        string strLng = drBranch["Lang"].ToString();
                        string strLat = drBranch["Lat"].ToString();
                        //ltrBrLocation.Text = Common.ShrinkText(drBranch["Location"].ToString(), 37);
                        ltrPAN.Text = drBranch["PAN"].ToString();
                        ctrlCreateStore1.BranchId = (int)drBranch["Id"];
                        if (!String.IsNullOrEmpty(strLat) && !String.IsNullOrEmpty(strLng))
                        {
                            //imgMap.Visible = true;
                            //imgMap.ImageUrl = $"http://maps.google.com/maps/api/staticmap?key={ConfigurationSettings.AppSettings.Get("googleAPIKey")}&center={strLat},{strLng}&zoom=17&size=900x355&markers=size:mid|color:red|label:A|{strLat},{strLng}&sensor=false";
                            //branchProgressVal = (String.IsNullOrEmpty(ltrBrLocation.Text) ? 10 : 30);
                        }
                        else
                        {
                            //branchProgressVal = (string.IsNullOrEmpty(ltrBrLocation.Text) ? 0 : 10);
                            //ltrBrLocation.Text = (string.IsNullOrEmpty(ltrBrLocation.Text) ? "Please select branch" : ltrBrLocation.Text);
                            //ltrBrAddress.Text = "Set branch location in map from settings";
                        }
                    }
                    else
                    {
                        //branchProgressVal = (string.IsNullOrEmpty(ltrBrLocation.Text) ? 0 : 10);
                        //ltrBrLocation.Text = (string.IsNullOrEmpty(ltrBrLocation.Text) ? "Please select branch" : ltrBrLocation.Text);
                        //ltrBrAddress.Text = "Add your branch info from store config";
                    }

                    int bannercount = 0, contentpages = 0;
                    string sqlAppearance = @"SELECT COUNT(*) FROM app_pages WHERE page_type IN (1, 3) AND storegroup_id = @storegroupid
                        UNION ALL
                        SELECT COUNT(*) FROM app_advertisements a INNER JOIN app_adzones z ON a.adzone_id=z.adzone_id WHERE z.adzone_screen LIKE 'Home' AND z.adzone_type LIKE 'advertisement' AND a.storegroup_id= @storegroupid";
                    DataTable dtAppearance = DataServiceMySql.GetDataTable(sqlAppearance, UserService.GetAPIConnectionString(), new List<KeyValuePair<string, object>>() { new KeyValuePair<string, object>("storegroupid", apistoregroupid) });
                    if (dtAppearance != null && dtAppearance.Rows.Count > 0)
                    {
                        try { contentpages = (dtAppearance.Rows.Count > 0 ? Convert.ToInt32(dtAppearance.Rows[0][0]) : 0); } catch { contentpages = 0; }
                        try { bannercount = (dtAppearance.Rows.Count > 1 ? Convert.ToInt32(dtAppearance.Rows[1][0]) : 0); } catch { bannercount = 0; }

                    }
                    //ltrAppearanceProgressVal.Text = $"{(bannercount > 0 ? 35 : 0) + (contentpages > 0 ? 35 : 0) + 30}";
                    //pnlAppearanceProgress.CssClass = (ltrAppearanceProgressVal.Text == "100" ? "progress-bar bg-success wd-100p" : (ltrAppearanceProgressVal.Text == "65" ? "progress-bar bg-primary wd-50p" : "progress-bar bg-danger wd-0p"));
                    ltrContentPages.Text = contentpages.ToString();
                    ltrBanners.Text = bannercount.ToString();

                    //pnlBankInfoProgress.CssClass = (bankAccounts <= 0 ? "progress-bar bg-danger wd-0p" : (storesWithoutBank > 0 ? "progress-bar bg-primary wd-50p" : "progress-bar bg-success wd-100p"));
                    //ltrBankInfoProgressVal.Text = (bankAccounts <= 0 ? "0" : (storesWithoutBank > 0 ? "50" : "100"));
                    //pnlGSTInfoProgress.CssClass = (gstscount <= 0 ? "progress-bar bg-danger wd-0p" : (gstNotLinkedToStore > 0 || gstnNotVerified > 0 ? "progress-bar bg-primary wd-50p" : "progress-bar bg-success wd-100p"));
                    //ltrGSTInfoProgressVal.Text = (gstscount <= 0 ? "0" : (gstNotLinkedToStore > 0 || gstnNotVerified > 0 ? "50" : "100"));


                    //pnlFssaiInfoProgress.CssClass = (fssaiLinkedToStore <= 0 ? "progress-bar bg-danger wd-0p" : (fssaiNotLinkedToStore > 0 ? "progress-bar bg-primary wd-50p" : "progress-bar bg-success wd-100p"));
                    //ltrFssaiInfoProgressVal.Text = (fssaiLinkedToStore <= 0 ? "0" : (fssaiNotLinkedToStore > 0 ? "50" : "100"));



                    #region Additional Info
                    // Populate Additional info box values.
                    string sqlAdditionalInfo = $"SELECT COUNT(DISTINCT phone) FROM retaline_godown_boy gb INNER JOIN finascop_branch b ON b.br_ID=gb.branch_id WHERE br_status= 'Active' AND b.br_storeGroup= {apistoregroupid} AND gb.status=1 " +
                            $"UNION ALL SELECT COUNT(DISTINCT phone) FROM retaline_godown_boy gb INNER JOIN finascop_branch b ON b.br_ID=gb.branch_id WHERE br_status= 'Active' AND b.br_storeGroup= {apistoregroupid} AND gb.status=1 AND is_offline=0 " +
                            $"UNION ALL SELECT COUNT(*) FROM qugeo_driver dr INNER JOIN finascop_branch b ON b.br_ID= dr.br_id WHERE br_status= 'Active' AND b.br_storeGroup= {apistoregroupid} AND dr.d_Active=1  " +
                            $"UNION ALL SELECT COUNT(DISTINCT stit_id) AS val FROM finascop_stock_branch_inventory bi INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id WHERE br_status= 'Active' AND b.br_storeGroup={apistoregroupid} " +
                            $"UNION ALL SELECT COUNT(DISTINCT stit_id) FROM finascop_stock_branch_inventory bi INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id LEFT JOIN (SELECT item_id, SUM(`count`) AS blockedNum FROM finascop_stock_blocked GROUP BY item_id) blocked ON blocked.item_id = bi.stit_id " +
                            $"WHERE br_status= 'Active' AND b.br_storeGroup= {apistoregroupid} AND ((IFNULL(bi.item_count, 0) - IFNULL(blockedNum, 0)) <= 0  or mrp <= 0 or selling_price <= 0) " +
                            $"UNION ALL SELECT COUNT(*) FROM finascop_branch WHERE br_storeGroup= {apistoregroupid} AND br_SalesOnline = 1 " +
                            $"UNION ALL SELECT COUNT(*) FROM finascop_branch WHERE br_storeGroup= {apistoregroupid} ";

                    DataTable dtAdditionalInfo = DataServiceMySql.GetDataTable(sqlAdditionalInfo, UserService.GetAPIConnectionString());
                    if (dtAdditionalInfo != null && dtAdditionalInfo.Rows.Count > 0)
                    {
                        ltrOrderPickersCount.Text = ltrOrderPickersOnlineCount.Text = ltrDriversCount.Text = ltrDriversOnlineCount.Text = "0";
                        try { ltrOrderPickersCount.Text = dtAdditionalInfo.Rows[0][0].ToString(); } catch { ltrOrderPickersCount.Text = "0"; }
                        if (dtAdditionalInfo.Rows.Count > 1)
                            try { ltrOrderPickersOnlineCount.Text = dtAdditionalInfo.Rows[1][0].ToString(); } catch { ltrOrderPickersOnlineCount.Text = "0"; }

                        if (dtAdditionalInfo.Rows.Count > 2)
                            try { ltrDriversCount.Text = dtAdditionalInfo.Rows[2][0].ToString(); } catch { ltrDriversCount.Text = "0"; }
                        int onlineVehicles = 0;
                        try
                        {
                            var vehicleService = new VehicleService();
                            var liveVehiclesResponse = vehicleService.ListLiveVehicles(0, this.CurrentUser.APIStoreId);
                            if (liveVehiclesResponse?.Vehicles != null)
                                onlineVehicles = liveVehiclesResponse.Vehicles.Count;
                        }
                        catch { onlineVehicles = 0; }
                        try { if (!String.IsNullOrEmpty(ltrDriversCount.Text) && Convert.ToInt32(ltrDriversCount.Text) > 0) ltrDriversOnlineCount.Text = onlineVehicles.ToString(); else ltrDriversOnlineCount.Text = "0"; } catch { ltrDriversOnlineCount.Text = "0"; }

                        //if (dtAdditionalInfo.Rows.Count > 3)
                        //    try { ltrProductsCount.Text = dtAdditionalInfo.Rows[3][0].ToString(); } catch { ltrProductsCount.Text = "0"; }
                        //if (dtAdditionalInfo.Rows.Count > 4)
                        //    try { ltrOutofStockCount.Text = dtAdditionalInfo.Rows[4][0].ToString(); } catch { ltrOutofStockCount.Text = "0"; }
                        if (dtAdditionalInfo.Rows.Count > 5)
                            try { int onlinebranches = Convert.ToInt32(dtAdditionalInfo.Rows[5][0]); storesOnline = onlinebranches; lblBranchesOnlineFlag.CssClass = (onlinebranches > 0 ? "square-8 bg-success mg-r-5 rounded-circle" : "square-8 bg-warning mg-r-5 rounded-circle"); ltrOnlineBranches.Text = onlinebranches.ToString(); } catch { ltrOnlineBranches.Text = ""; }
                        if (dtAdditionalInfo.Rows.Count > 6)
                            try { totalStores = Convert.ToInt32(dtAdditionalInfo.Rows[6][0]); ltrTotalStores.Text = totalStores.ToString(); ltrOnlineStores.Text = storesOnline.ToString(); } catch { ltrOnlineBranches.Text = ""; }
                    }
                    branchProgressVal = (totalStores <= 0 ? 10 : 30);
                    //ltrBrAddress.Text = (totalStores <= 0? "Add store/branch in the store page." : "Stores page will list all the stores registered with add option.");

                    int additionalSettingsProgress = 10;
                    if (!string.IsNullOrEmpty(ltrOrderPickersCount.Text) && ltrOrderPickersCount.Text != "0")
                        additionalSettingsProgress += 20;
                    if (!string.IsNullOrEmpty(ltrOrderPickersOnlineCount.Text) && ltrOrderPickersOnlineCount.Text != "0")
                        additionalSettingsProgress += 10;

                    if (!string.IsNullOrEmpty(ltrDriversCount.Text) && ltrDriversCount.Text != "0")
                        additionalSettingsProgress += 20;
                    if (!string.IsNullOrEmpty(ltrDriversOnlineCount.Text) && ltrDriversOnlineCount.Text != "0")
                        additionalSettingsProgress += 10;

                    //if (!string.IsNullOrEmpty(ltrProductsCount.Text) && ltrProductsCount.Text != "0")
                    //    additionalSettingsProgress += 20;
                    //if (!string.IsNullOrEmpty(ltrOutofStockCount.Text) && ltrOutofStockCount.Text != "0")
                    //    additionalSettingsProgress += 10;

                    //pnlAdditionalSettingsProgress.CssClass = $"progress-bar bg-{(string.IsNullOrEmpty(ltrOrderPickersCount.Text) || ltrOrderPickersCount.Text == "0" ? "danger" : (additionalSettingsProgress > 50 ? "primary" : "warning"))} wd-{additionalSettingsProgress}p";// (String.IsNullOrEmpty(ltrGST.Text) ?  : (String.IsNullOrEmpty(ltrGSTLegalName.Text) ? "progress-bar bg-primary wd-50p" : "progress-bar bg-success wd-100p"));
                    //ltrAdditionalSettingsProgressVal.Text = additionalSettingsProgress.ToString();

                    #endregion

                    int storeConfigProgress = 10;
                    if (gstscount > 0)
                    {
                        storeConfigProgress += 20;
                        branchProgressVal += 15;
                    }

                    //if (ltrBankInfoProgressVal.Text == "100")
                    //{
                        storeConfigProgress += 10;
                        branchProgressVal += 5;
                    //}
                    //if (!string.IsNullOrEmpty(ltrOrderPickersOnlineCount.Text) && ltrOrderPickersOnlineCount.Text != "0")
                    //{
                    storeConfigProgress += 15;
                    branchProgressVal += 20;
                    //}
                    //if (!string.IsNullOrEmpty(ltrDriversOnlineCount.Text) && ltrDriversOnlineCount.Text != "0")
                    //{
                    storeConfigProgress += 15;
                    branchProgressVal += 10;
                    //}
                    //if (!string.IsNullOrEmpty(ltrOutofStockCount.Text) && ltrOutofStockCount.Text != "0")
                    //{
                    //    storeConfigProgress += 10;
                    //    branchProgressVal += 20;
                    //}
                    //if (branchProgressVal >= 50)
                    //    storeConfigProgress += 10;

                    //if (bannercount > 0)
                    //    storeConfigProgress += 5;
                    //if (contentpages > 0)
                    //    storeConfigProgress += 5;

                    pnlStoreConfigProgress.CssClass = (storeConfigProgress < 50 ? $"progress-bar bg-warning wd-{storeConfigProgress}p" : (storeConfigProgress == 100 ? "progress-bar bg-success wd-100p" : $"progress-bar bg-primary wd-{storeConfigProgress}p"));
                    ltrStoreConfigProgressVal.Text = storeConfigProgress.ToString(); //(!string.IsNullOrEmpty(ltrPrimaryBranchProgressVal.Text) && ltrPrimaryBranchProgressVal.Text != "10" ? "100" : "60");

                    //pnlPrimaryBranchProgress.CssClass = (branchProgressVal < 50 ? $"progress-bar bg-warning wd-{branchProgressVal}p" : (branchProgressVal == 100 ? "progress-bar bg-success wd-100p" : $"progress-bar bg-primary wd-{branchProgressVal}p"));
                    //ltrPrimaryBranchProgressVal.Text = branchProgressVal.ToString();


                    List<KeyValuePair<string, object>> userprms = new List<KeyValuePair<string, object>>();
                    userprms.Add(new KeyValuePair<string, object>("storegroupid", storegroupid));

                    string sqlUser = $"SELECT COUNT(*) FROM [User] WHERE StoreGroupId = @storegroupid";
                    DataTable dtUser = DataService.GetDataTable(sqlUser, parmeters: userprms);
                    int storeUser = 0;
                    if (dtUser != null && dtUser.Rows.Count > 0)
                    {
                        try { storeUser = (dtUser.Rows.Count > 0 ? Convert.ToInt32(dtUser.Rows[0][0]) : 0); } catch { storeUser = 0; }
                    }

                    //if (storeUser > 0)
                    //{
                    //    ltrUserCount.Text = storeUser.ToString();
                    //}
                    //else
                    //{
                    //    ltrUserCount.Text = "0";
                    //}


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
                        ltrSponsoredPrd.Text = "Enabled";
                    }
                    else
                    {
                        ltrSponsoredPrd.Text = "Disabled";
                    }

                }
            }

        }

        protected void Page_PreRender(object sender, EventArgs e)
        {
            lbtnEditStore.Visible = plcConf.Visible;
        }

        protected void lbtnEditStore_Click(object sender, EventArgs e)
        {
            EditView = true;
            ctrlCreateStore1.Visible = true;
            plcConf.Visible = false;

            var stores = Core.Services.APIService.GetStores(this.CurrentUser.APIStoreId);
            if (stores != null && stores.Count > 0)
            {
                var store = stores.OrderByDescending(b => b.BranchId).OrderByDescending(b => b.IsDefault).FirstOrDefault(); //stores.FirstOrDefault(s => s.IsDefault==1);
                ctrlCreateStore1.APIStore = store;
                ctrlCreateStore1.APIBranchId = store.BranchId;
            }
            ctrlCreateStore1.LoadInput();

            //ctrlCreateStore1.ResetBtn.Text = "Cancel";
        }

        protected void PageShow_Click(object sender, EventArgs e)
        {
            Type cstype = this.GetType();
            ClientScriptManager cs = Page.ClientScript;
            StringBuilder cstext1 = new StringBuilder();
            if (this.CurrentUser.PackageId < 2)
            {
                cstext1.Append("<script type=text/javascript> $('#modalupgrade').modal('show'); </");
                cstext1.Append("script>");
                cs.RegisterStartupScript(cstype, "Upgrade", cstext1.ToString());

                return;
            }
            else
            {
                Response.Redirect("/Tenant/ManageBusinessInfo");
            }
        }
    }
}