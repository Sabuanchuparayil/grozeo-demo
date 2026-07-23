using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Globalization;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Data.SqlClient;
using RetalineProAgent.Core.Services;

namespace RetalineProAgent
{
    public partial class DuplicateInventory : Base.BasePartnerPage
    {

        protected void Page_Load(object sender, EventArgs e)
        {
            //if (this.CurrentUser.TenantType == 2 && System.Configuration.ConfigurationManager.AppSettings.Get("StoreDisableNoneVAT") == "1")
            //{
            //    Response.Redirect("/Tenant/SponsoredProducts");
            //    return;
            //}
            if (Page.User.IsInRole("StoreManager"))
            {
                Response.Redirect("/Tenant");
                return;
            }

            ctrlDuplicatedProduct.ParentAddProductBinding += new Controls.StoreSettings.ctrlDuplicatedProduct.ParentAddProductHandler(AddProductPostEvent);
            ctrlDuplicatedProduct.ParentCancelAddProductBinding += new Controls.StoreSettings.ctrlDuplicatedProduct.ParentAddProductHandler(CancelAddProductPostEvent);
            ctrlDuplicatedProduct.ParentMessageBinding += new Controls.StoreSettings.ctrlDuplicatedProduct.ParentMessageHandler(ShowResult);

            if (!String.IsNullOrEmpty(Request.QueryString["id"]))
            {
                //ctrlDuplicatedProduct.IsEditView = true;
                ctrlDuplicatedProduct.EditProdId = Convert.ToInt32(Request.QueryString["id"]);
            }
        }

        private void AddProductPostEvent(int type)
        {
            if (type == 1)
            {
                ctrlMessagebox.ShowResult("Success", "Product Created Successfully", 1, "/Tenant/MyProducts");
                //Response.Redirect("/Products");
            }
        }
        private void CancelAddProductPostEvent(int type)
        {
            //if (type == 1)
            //{
            Response.Redirect("/Tenant/MyProducts");
            //}
        }

        private void ShowResult(string title, string content, int type)
        {
            if (type == 3)
                ctrlMessagebox.ShowResult(title, content, 2, "/Tenant/MyProducts");
            else if (type == 2)
                ctrlMessagebox.ShowResult(title, content, 2);
            else
                ctrlMessagebox.ShowResult(title, content, type, "/Tenant/MyProducts");
        }


    }
}



