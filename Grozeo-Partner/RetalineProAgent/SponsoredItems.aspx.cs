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
    public partial class SponsoredItems: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {

        }

        protected void SDS_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            if (e.Command.Parameters.Contains("storeId"))
                e.Command.Parameters["storeId"].Value = this.CurrentUser.APIStoreId;//.StoreGroupId;
            if (e.Command.Parameters.Contains("type"))
            {
                e.Command.Parameters["type"].Value = 0;// (rbNotAddedProducts.Checked ? 2 : (rbAddedProducts.Checked ? 1 : 0));
            }
            //if (e.Command.Parameters["@brsearch"] != null && !String.IsNullOrEmpty(e.Command.Parameters["@brsearch"].Value.ToString()))
            //{
            //    e.Command.Parameters["@brsearch"].Value = String.Format("%{0}%", e.Command.Parameters["@brsearch"]);
            //}
        }

        protected void selDepartment_SelectedIndexChanged(object sender, EventArgs e)
        {
            if (selDepartment.SelectedIndex > 0)
            {
                DataPager pager = (DataPager)lstProducts.FindControl("DataPager1");
                if (pager != null)
                {
                    pager.SetPageProperties(0, 25, true);
                    //var categories = (List<CategoryData>)ODSCategoriesDirect.Select();
                    //if (categories != null)
                    //{
                    //    var selectedCategory = categories.Where(c => c.ParentCategoryId.ToString() == selDepartment.Text).FirstOrDefault();
                    //    if (selectedCategory != null)
                    //    {
                    //        selCategory.DataSource = selectedCategory.Subcategories;
                    //        selCategory.DataBind();
                    //        selCategory.Items.Insert(0, new ListItem("All Categories", "0"));
                    //    }
                    //}
                }

            }


        }


        protected void selCategory_DataBound(object sender, EventArgs e)
        {
            selCategory.Items.Insert(0, new ListItem("All Categories", "0"));
        }

        protected void selBrand_DataBound(object sender, EventArgs e)
        {
            selBrand.Items.Insert(0, new ListItem("All Brands", "0"));
        }

        protected void lbtnConfirmSponsored_Click(object sender, EventArgs e)
        {
            List<KeyValuePair<String, Object>> tenantParmeters = new List<KeyValuePair<string, object>>();
            tenantParmeters.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.StoreGroupId));
            //DataService.ExecuteSql("UPDATE AppTenant SET Stage = 9 WHERE Stage = 8 AND Id=@tenantId", parmeters: tenantParmeters);
            DataService.ExecuteSql("UPDATE AppTenant SET Stage = 1 WHERE Id=@tenantId", parmeters: tenantParmeters);
            Service.UserService.CachedDefaultUser = null;
            Session["SHOWPUBLICNAVHELP"] = true;
            Response.Redirect("/");
        }

    }
}