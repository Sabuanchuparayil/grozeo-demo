using RetalineProAgent.Core.Services;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Tenant
{
    public partial class TenantMaster: Base.BasePartnerMasterPage
    {
        public delegate void MasterCustomHandler(int type);
        public event MasterCustomHandler MasterEventBinding;
        public bool ShowServerButtonUpgrade
        {
            get
            {
                return PopupUpgradeStore1.showServerButton;
            }
            set
            {
                PopupUpgradeStore1.showServerButton = value;
            }
        }

        public string TitleContent { set { PopupUpgradeStore1.TitleContent = value; } }
        public string HeadContent { set { PopupUpgradeStore1.HeadContent = value; } }
        public string BodyContent1 { set { PopupUpgradeStore1.BodyContent1 = value; } }
        public string BodyContent2 { set { PopupUpgradeStore1.BodyContent2 = value; } }


        protected void Page_Load(object sender, EventArgs e)
        {
            #region Wizard - Force Redirects

            // Temporary deleted the redirection to disable select products forcefully loaded after store creation. Tenant will be landed to dashboard immediately after creating the store. Select product is not a mandatory since sponsored products will be filling the space till added products.

            //Service.User _user = (Page.User.Identity.IsAuthenticated ? this.CurrentUser : null);
            //if ((Page.User.IsInRole("StoreAdmin") || Page.User.IsInRole("Agent") || Page.User.IsInRole("StoreManager") || Page.User.IsInRole("BranchManager")) && _user != null && _user.TenantStage > 0)
            //{
            //    if ((_user.StoreGroupId <= 0 || _user.TenantStage == 5) && this.Page.ToString().ToLower() != "asp.storesettings_aspx")
            //    {
            //        Response.Redirect("/tenant/store/storesettings", true);
            //        return;
            //    }
            //    if (_user.TenantStage == 6 && !(this.Page.ToString().ToLower() == "asp.selectproduct_aspx")) // || this.Page.ToString().ToLower() == "asp.storesettings_aspx"))
            //    {
            //        Response.Redirect("/selectproduct", true);
            //        return;
            //    }
            //    //if (_user.TenantStage == 7 && !(this.Page.ToString().ToLower() == "asp.itemsforsale_aspx" || this.Page.ToString().ToLower() == "asp.inventorymapping_aspx" || this.Page.ToString().ToLower() == "asp.storesettings_aspx"))
            //    //{
            //    //    Response.Redirect("/itemsforsale", true);
            //    //    return;
            //    //}
            //    //if (_user.TenantStage == 8 && !(this.Page.ToString().ToLower() == "asp.sponsoreditems_aspx" || this.Page.ToString().ToLower() == "asp.itemsforsale_aspx" || this.Page.ToString().ToLower() == "asp.inventorymapping_aspx" || this.Page.ToString().ToLower() == "asp.storesettings_aspx"))
            //    //{
            //    //    Response.Redirect("/sponsoreditems", true);
            //    //    return;
            //    //}
            //    //if (_user.TenantStage == 9 && !(this.Page.ToString().ToLower() == "asp.storecompletion_aspx" || this.Page.ToString().ToLower() == "asp.sponsoreditems_aspx" || this.Page.ToString().ToLower() == "asp.itemsforsale_aspx" || this.Page.ToString().ToLower() == "asp.inventorymapping_aspx" || this.Page.ToString().ToLower() == "asp.storesettings_aspx"))
            //    //{
            //    //    Response.Redirect("/StoreCompletion", true);
            //    //    return;
            //    //}

            //}
            #endregion

            pnlRibbon.Visible = (Page.User.Identity.IsAuthenticated && this.CurrentUser.TenantType == 1 && this.CurrentUser.TenantStatus == 2 && System.Configuration.ConfigurationManager.AppSettings.Get("StoreDisableNoneVAT") == "1");
            if (!this.CurrentUser.CanCheckout)
                pnlRibbon.Visible = true;

            PopupUpgradeStore1.ParentButtonBinding += new Controls.PopupUpgradeStore.ParentCustomHandler(UpdateEvent);
            if (!IsPostBack)
            {
                if (Request.RawUrl.EndsWith("?upgrade"))
                    Service.UserService.CachedDefaultUser = null;
            }

            if (Session != null && Session["SHOWPUBLICNAVHELP"] != null)
            {
                try { plcPublicSiteUrlNav.Visible = Convert.ToBoolean(Session["SHOWPUBLICNAVHELP"]); } catch { }
                try { Session.Remove("SHOWPUBLICNAVHELP"); } catch { }
            }

            int storegroupid = this.CurrentUser.StoreGroupId;
            string userlogoImage = this.CurrentUser.LogoImage;
            
            
            if (userlogoImage != "")
            {
                pnlLogo.Visible = true;
                logoImage.ImageUrl = userlogoImage;
                storeName.Visible = false;
            }
            else
            {
                pnlLogo.Visible = false;
                storeName.Visible = true;
            }

        }

        private void UpdateEvent(int type)
        {
            MasterEventBinding(type);
        }
    }
}