using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class MyProducts : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            //if (this.CurrentUser.TenantType == 2 && System.Configuration.ConfigurationManager.AppSettings.Get("StoreDisableNoneVAT") == "1")
            //{
            //    Response.Redirect("/SponsoredItems");
            //    return;
            //}
            if (Page.User.IsInRole("StoreManager"))
            {
                Response.Redirect("/Tenant");
                return;
            }
            plcWizard.Visible = (new int[] { 5, 6, 7 }).Contains(this.CurrentUser.TenantStage);
            plcNoneWizard.Visible = plcWizardBrudcrumb.Visible = !plcWizard.Visible;

        }

        protected void Page_PreRender(object sender, EventArgs e)
        {
            string strSql = $"SELECT count(*) FROM InventoryMapping WHERE StoreId= {this.CurrentUser.StoreGroupId}";
            int selectedCount = (int)DataService.ExecuteScalar(strSql);
            //ltrTitleCount.Text = selectedCount.ToString();
        }
    }
}