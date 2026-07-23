using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Finance;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Xml.Linq;

namespace RetalineProAgent
{
    public partial class SponsoredProducts: Base.BasePartnerPage
    {

        protected void Page_Load(object sender, EventArgs e)
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
                    ltrSponsoredPrd.Text = "Enabled";
                    
                }
                else
                {
                    ltrSponsoredPrd.Text = "Disabled";
                   
                }
                                                
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

        //protected void Page_PreRender(object sender, EventArgs e)
        //{
        //    if (this.CurrentUser.PackageId > 1)
        //    {
        //        List<KeyValuePair<string, object>> storeparams = new List<KeyValuePair<string, object>>();
        //        storeparams.Add(new KeyValuePair<string, object>("storegroup", this.CurrentUser.APIStoreId));
        //        var dtStoreGroup = DataServiceMySql.GetDataTable($"SELECT store_group_id, showSponsered FROM finascop_branch_group WHERE store_group_id = @storegroup", Service.UserService.GetAPIConnectionString(), storeparams);
        //        string sponsoredStore = "";
        //        if (dtStoreGroup != null && dtStoreGroup.Rows.Count > 0)
        //        {
        //            sponsoredStore = dtStoreGroup.Rows[0]["showSponsered"].ToString();
        //        }
        //        if (Convert.ToInt32(sponsoredStore) == 1)
        //        {
        //            rdEnabled.Checked = true;
        //            rdDisabled.Checked = false;
        //        }
        //        else
        //        {
        //            rdEnabled.Checked = false;
        //            rdDisabled.Checked = true;
        //        }
        //    }
        //}

        //protected void rdEnabled_CheckedChanged(object sender, EventArgs e)
        //{
        //    SaveRadioButtonValue("Enabled");
        //}

        //protected void rdDisabled_CheckedChanged(object sender, EventArgs e)
        //{
        //    SaveRadioButtonValue("Disabled");
        //}

        //private void SaveRadioButtonValue(string selectedValue)
        //{
        //    int sponsoredPrd = 0;
        //    if (rdEnabled.Checked == true)
        //    {
        //        sponsoredPrd = 1;
        //    }
        //    else
        //    {
        //        sponsoredPrd = 0;
        //    }
        //    List<KeyValuePair<string, object>> storegrpparams = new List<KeyValuePair<string, object>>();
        //    storegrpparams.Add(new KeyValuePair<string, object>("showSponsered", sponsoredPrd));
        //    storegrpparams.Add(new KeyValuePair<string, object>("storeGroupId", this.CurrentUser.APIStoreId));
        //    string strUpdateSql = $"UPDATE finascop_branch_group SET showSponsered = @showSponsered WHERE store_group_id=@storeGroupId";
        //    int rowsupdated = DataServiceMySql.ExecuteSql(strUpdateSql, UserService.GetAPIConnectionString(), storegrpparams);
        //}

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
            // Activitylog
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            string Source = strUrl;
            int storegroupid = this.CurrentUser.APIStoreId;
            string Users = this.CurrentUser.Email;
            string storegroup = (this.CurrentUser.APIStoreId).ToString();
            string Stage = "1";            
            var items = new[]
                {
                    new { Key = "Store Group", Value = storegroup },
                    new { Key = " Stage", Value = Stage },
                   
                    };
            string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
            var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);
            Session["SHOWPUBLICNAVHELP"] = true;
            Response.Redirect("/");

        }

        protected void btnsptdprdt_Click(object sender, EventArgs e)
        {
               int packageid = this.CurrentUser.PackageId;
                if (packageid == 2)
                {
                    btnyes.Visible=true;
                    ltrlupgrade.Text = "Do you want to permanently disable listing all the sponsored products on your website?";
                }
                else
                {
                    ltrlupgrade.Text = "You can not deselect the sponsored products being a free package user. Please upgrade to our paid plan to manage the Sponsored Products";
                }
            
            string strAlertSCript = "$('#modelalert').modal('show');";
            strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";
            System.Type cstype = this.GetType();
            String csname1 = "ShowConfirmPopup";
            ClientScriptManager cs = this.ClientScript;
            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
            cstext1.Append("script>");
            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }

        protected void lstProducts_ItemDataBound(object sender, ListViewItemEventArgs e)
        {
            List<KeyValuePair<string, object>> storeparams = new List<KeyValuePair<string, object>>();
            storeparams.Add(new KeyValuePair<string, object>("storegroup", this.CurrentUser.APIStoreId));
             var dtStoreGroup = DataServiceMySql.GetDataTable($"SELECT store_group_id, showSponsered FROM finascop_branch_group WHERE store_group_id = @storegroup", Service.UserService.GetAPIConnectionString(), storeparams);
            if (dtStoreGroup != null && dtStoreGroup.Rows.Count > 0)
            {
                   string sponsoredStore = dtStoreGroup.Rows[0]["showSponsered"].ToString();
                 LinkButton btnsptdprdt = (LinkButton)e.Item.FindControl("btnsptdprdt");
                if (btnsptdprdt == null)
                    return;
                if (Convert.ToInt32(sponsoredStore) == 1)
                {
                    btnsptdprdt.Enabled = true;
                }
                else
                {
                    btnsptdprdt.Enabled=false;
                }
            }
        }

        protected void btnyes_Click(object sender, EventArgs e)
        {
              List<KeyValuePair<string, object>> storegrpparams = new List<KeyValuePair<string, object>>();
               storegrpparams.Add(new KeyValuePair<string, object>("storeGroupId", this.CurrentUser.APIStoreId));
               string strUpdateSql = $"UPDATE finascop_branch_group SET showSponsered = 0 WHERE store_group_id=@storeGroupId";
               int rowsupdated = DataServiceMySql.ExecuteSql(strUpdateSql, UserService.GetAPIConnectionString(), storegrpparams);
            // Activitylog
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            string Source = strUrl;
            int storegroupid = this.CurrentUser.APIStoreId;
            string Users = this.CurrentUser.Email;
            string storegroup = (this.CurrentUser.APIStoreId).ToString();
            string showSponsered = "0";
            var items = new[]
                {
                    new { Key = "Store Group", Value = storegroup },
                    new { Key = " ShowSponsered", Value = showSponsered },

                 };
            string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
            var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);
            Common.ShowCustomAlert(this.Page, "Success", "successfully Disable!", true, "/Tenant/SponsoredProducts");
        }
    }
}
