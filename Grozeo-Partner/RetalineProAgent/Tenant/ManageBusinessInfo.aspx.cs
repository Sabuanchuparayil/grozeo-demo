using Newtonsoft.Json;
using Org.BouncyCastle.Ocsp;
using RetalineProAgent.Core.BussinessModel.Finance;
using RetalineProAgent.Core.BussinessModel.Inventory;
using RetalineProAgent.Core.BussinessModel.Store;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Core.Services.Cache;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Data.SqlTypes;
using System.EnterpriseServices;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class ManageBusinessInfo : Base.BasePartnerPage
    {
        protected string roArea;
        protected string areaId;
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
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
                LoadInput();
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

                    txtFBUrl.Text = dr["SM_FB"].ToString();
                    txtTwitterUrl.Text = dr["SM_Twiter"].ToString();
                    txtInstaUrl.Text = dr["SM_Insta"].ToString();

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

                    //txtAddr1.Text = dr["Location"].ToString();
                    //txtPinCode.Text = dr["Pin"].ToString();
                    //txtAddr2.Text = dr["Addr"].ToString();

                    //string strState = dr["State"].ToString();
                    //if (!String.IsNullOrEmpty(strState))
                    //    selState.Attributes["DefaultState"] = strState;

                    //string strDistrict = dr["District"].ToString();
                    //if (!String.IsNullOrEmpty(strDistrict))
                    //    selDistrict.Attributes["DefaultDistrict"] = strDistrict;

                    //string strBusinessType = dr["BusinessType"].ToString();

                    //if (!String.IsNullOrEmpty(strBusinessType) && selBusinessTypes.Items.FindByText(strBusinessType) != null)
                    //    selBusinessTypes.Text = (selBusinessTypes.Items.FindByText(strBusinessType).Value);
                    //if (!String.IsNullOrEmpty(strBusinessType))
                    //    selBusinessTypes.Attributes["DefaultBType"] = strBusinessType;

                    //string strSecondaryBusinessTypes = dr["SecondaryBusinessTypes"].ToString();
                    //if (!String.IsNullOrEmpty(strSecondaryBusinessTypes))
                    //{
                    //    lstBusinessTypes.Attributes["DefaultBType"] = strSecondaryBusinessTypes;
                    //    string[] strbtypes = strSecondaryBusinessTypes.Trim().Split(',');
                    //    if (strbtypes.Length > 0)
                    //    {
                    //        foreach (string btype in strbtypes)
                    //            if (!String.IsNullOrEmpty(btype.Trim()) && lstBusinessTypes.Items.FindByText(btype.Trim()) != null)
                    //                lstBusinessTypes.Items.FindByText(btype.Trim()).Selected = true;
                    //    }
                    //}

                    //string strHost = dr["hosts"].ToString(); ;
                    //string[] strHosts = strHost.Split(',');
                }
                string roName = "", roId = "", areaId = "", cpId = "", referrerType = "", cpName = "", areaName = "";
                int relationshipofficerId = 0, consultingpartnerId = 0, roAreaId = 0;
                List<KeyValuePair<string, object>> dataparams = new List<KeyValuePair<string, object>>();
                dataparams.Add(new KeyValuePair<string, object>("storeId", this.CurrentUser.APIStoreId));
                dataparams.Add(new KeyValuePair<string, object>("branchId", strbranchId));





				//else if (roAreaId > 0 && relationshipofficerId <= 0)
				//{
				//    var dtStoreGroup = DataServiceMySql.GetDataTable($"SELECT fb.areaId, ae.areaName,CONCAT(areaName, ', ', (SELECT dst_Name FROM finascop_district fd WHERE ro.rodst_Id = fd.dst_Id), ', ', (SELECT st_name FROM finascop_state fs WHERE ro.rost_id = fs.st_ID)) AS grozeoarea, ro.roName, CONCAT(roName,' ' , '-', ' ', roMobile) AS roDetails FROM finascop_branch fb INNER JOIN area_entries ae ON fb.areaId = ae.id INNER JOIN relationship_officer ro ON fb.areaId = ro.roArea WHERE br_ID = @branchId AND br_storeGroup = @storeId", UserService.GetAPIConnectionString(), dataparams);
				//    if (dtStoreGroup != null && dtStoreGroup.Rows.Count > 0)
				//    {
				//        DataRow da = dtStoreGroup.Rows[0];
				//        areaName = da["grozeoarea"].ToString();
				//        roName = da["roDetails"].ToString();
				//        txtGrozeoArea.Text = areaName;
				//        if (roName != null)
				//        {
				//            txtRO.Text = roName;
				//        }
				//        else
				//        {
				//            txtRO.Text = "";
				//        }
				//    }
				//}
				//else
				//{
				//Common.ShowCustomAlert(this.Page, "Failure!", "Grozeo area or Relationship officer is not there for this store.", false, "/Tenant/ManageBusinessInfo");
				//}

				if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
                {
                    rbPODEnabled.Checked = rbPODDisabled.Checked = rbPODEnabled.Enabled = rbPODDisabled.Enabled = false;                    
                }


			}
        }


        protected void Page_PreRender(object sender, EventArgs e)
        {
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

        protected async void btnEditStore_Click(object sender, EventArgs e)
        {
            //int maxRetailTypes = (this.CurrentUser.PackageId > 1 ? 10 : 5);
            //int remainingRetailTypesCount = maxRetailTypes - lstSelectedTypes.Items.Count;

            //string secondaryBTypes = "";
            ////string strBusinessTypes = "";// selBusinessTypes.SelectedItem.Text;
            //List<int> secondaryBTypeIds = new List<int>();

            //if (remainingRetailTypesCount > 0)
            //foreach (ListItem item in lstBusinessTypes.Items)
            //{
            //        if (item.Selected && remainingRetailTypesCount > 0)
            //        {
            //            try
            //            {
            //                int secBType = Convert.ToInt32(item.Value);
            //                secondaryBTypes += (String.IsNullOrWhiteSpace(secondaryBTypes) ? "" : ",") + item.Text;
            //                secondaryBTypeIds.Add(secBType);
            //                remainingRetailTypesCount--;
            //            }
            //            catch { }
            //        }
            //}
            string strCheckoutMessage = "";
            bool canchekout = rbCheckoutEnabled.Checked;

            // If checkout was previously disabled and it is enabled now,
            // then validate pending jobs.
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
            if(canchekout == false)
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

            parmeters.Add(new KeyValuePair<string, object>("fburl", txtFBUrl.Text));
            parmeters.Add(new KeyValuePair<string, object>("twitter", txtTwitterUrl.Text));
            parmeters.Add(new KeyValuePair<string, object>("insta", txtInstaUrl.Text));

            parmeters.Add(new KeyValuePair<string, object>("cancheckout", canchekout));
            parmeters.Add(new KeyValuePair<string, object>("canPayOnline", canPayOnline));
            parmeters.Add(new KeyValuePair<string, object>("canPOD", canPOD));

            parmeters.Add(new KeyValuePair<string, object>("ApiStoreGroupId", this.CurrentUser.APIStoreId));

            //string sql = "UPDATE AppTenant SET [Name] = @Displayname WHERE Id= @StoreGroupId; UPDATE Store SET Displayname=@Displayname, SecondaryBusinessTypes=SecondaryBusinessTypes + ', ' + @SecondaryBusinessTypes, StoreContactName=@contactname, StoreAddress=@addr, StoreEmail=@storeemail, StorePhone=@storephone, UpdatedOn = getutcdate(), UpdatedBy=@User WHERE TenantId=@StoreGroupId; Update [User] set StoreGroupName = @Displayname where StoreGroupId= @StoreGroupId;";
            string sql = "UPDATE AppTenant SET [Name] = @Displayname, CanCheckout = @cancheckout, OnlinePaymentEnabled = @canPayOnline, PODEnabled = @canPOD WHERE Id= @StoreGroupId; " +
                "UPDATE Store SET Displayname=@Displayname, StoreContactName=@contactname, StoreAddress=@addr, StoreEmail=@storeemail, StorePhone=@storephone, " +
                "UpdatedOn = getutcdate(), UpdatedBy=@User, SM_FB=@fburl, SM_Twiter=@twitter, SM_Insta=@insta WHERE TenantId=@StoreGroupId; " +
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
            string fburl = txtFBUrl.Text;
            string twitter = txtTwitterUrl.Text;
            string insta = txtInstaUrl.Text;
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
                             new { Key = "Face Book Url", Value = fburl },
                             new { Key = "Twitter", Value = twitter },
                             new { Key = "Instagram", Value = insta },
                             new { Key = "CanCheckOut", Value = cancheckout },
                             new { Key = "CanPayOnlines", Value = canPayOnlines },
                             new { Key = "CanPod", Value = canpod },
               };
            string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
            var strresults = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);

            var cacheService = new RedisCacheService();
            string cachekey = $"Retl.AppTenant.host." + this.CurrentUser.PublicSiteUrl.ToLower();
            await cacheService.RemoveAsync(cachekey);


            //try
            //{
            //    if(secondaryBTypeIds.Count > 0)
            //        Services.StoreService.AppendBusinessTypes(UserService.CachedDefaultUser.APIStoreId, secondaryBTypeIds);
            //}
            //catch { }
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
            //Response.Redirect("/InventoryMapping");


        }


        //protected void selBusinessTypes_DataBound(object sender, EventArgs e)
        //{
        //    if (selBusinessTypes.Items.Count > 0)
        //    {
        //        string strKey = selBusinessTypes.Attributes["DefaultBType"];
        //        if (!String.IsNullOrEmpty(strKey) && selBusinessTypes.Items.FindByText(strKey) != null)
        //            selBusinessTypes.Text = (selBusinessTypes.Items.FindByText(strKey).Value);
        //    }
        //}

        //protected void lstBusinessTypes_DataBound(object sender, EventArgs e)
        //{
        //    if (lstBusinessTypes.Items.Count > 0)
        //    {
        //        string strKey = lstBusinessTypes.Attributes["DefaultBType"];
        //        if (!String.IsNullOrEmpty(strKey))
        //        {
        //            string[] strbtypes = strKey.Trim().Split(',');
        //            if (strbtypes.Length > 0)
        //            {
        //                foreach (string btype in strbtypes)
        //                    if (!String.IsNullOrEmpty(btype.Trim()) && lstBusinessTypes.Items.FindByText(btype.Trim()) != null)
        //                    {
        //                        lstBusinessTypes.Items.FindByText(btype.Trim()).Selected = true;
        //                        //lstBusinessTypes.Items.FindByText(btype.Trim()).Enabled = false;
        //                    }
        //            }
        //            //selBusinessTypes.Text = (selBusinessTypes.Items.FindByText(strKey).Value);
        //        }
        //    }
        //}


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
            //int maxRetailTypes = (UserService.CachedDefaultUser.PackageId > 1 ? 10 : 5);
            //int remainingRetailTypesCount = maxRetailTypes - lstSelectedTypes.Items.Count;
            //selBusinessTypes.Enabled = remainingRetailTypesCount > 0;
            //lstBusinessTypes.Enabled = remainingRetailTypesCount > 0;

            //pnlBCategories.Visible= pnlRCategories.Visible = remainingRetailTypesCount > 0;
        }

        protected void SDSBusinessTypes_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["@storeid"].Value = this.CurrentUser.APIStoreId;
        }

      

       


        //public void ShowInfo(string title, string message, string redirectUrl)
        //{
        //    string script = $@"alert('{title}: {message}'); window.location.href='{redirectUrl}';";
        //    ScriptManager.RegisterStartupScript(this, GetType(), "showInfoScript", script, true);
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
     
        private static void diablesponesedproduct(int storegroupid)
        {
            try
            {
                List<KeyValuePair<string, object>> updateparam = new List<KeyValuePair<string, object>>();
                updateparam.Add(new KeyValuePair<string, object>("storegroupid", storegroupid));
                string strUpdateSql = "UPDATE  finascop_stock_branch_inventory SET issponsered=0 WHERE branch_id in (SELECT br_id FROM `finascop_branch` WHERE  br_storeGroup=@storegroupid)";
                DataServiceMySql.ExecuteSql(strUpdateSql, UserService.GetAPIConnectionString(), updateparam);
            }
            catch(Exception ex)
            {

            }

        }


    }
}