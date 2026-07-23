using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Navigations;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Net.NetworkInformation;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class ManageBusinessType: Base.BasePartnerPage
    {
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
        protected void Page_Load(object sender, EventArgs e)
        {
        }

        protected void Page_PreRender(object sender, EventArgs e) {
            plcListing.Visible = (String.IsNullOrEmpty(CurViewType) || CurViewType == "1");
            plcSettings.Visible = (CurViewType == "2");
            btnAddMore.OnClientClick = "";
            if (gvRetailCategories.Rows.Count < 1)
                gvRetailCategories.DataBind();
            int maxbusinessTypeRestricted = 0; try { maxbusinessTypeRestricted = Convert.ToInt32(ConfigurationManager.AppSettings.Get("MaxBusinessTypeRestricted") ?? "0"); } catch { maxbusinessTypeRestricted = 0; }

            if (maxbusinessTypeRestricted > 0 && gvRetailCategories.Rows.Count >= maxbusinessTypeRestricted && this.CurrentUser.PackageId < 2)
                btnAddMore.OnClientClick = "$('#modalupgrade').modal('show'); return false;";

        }

        protected void SDSRetailCategories_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["@storegroupid"].Value = this.CurrentUser.APIStoreId;
        }

        protected void btnAddMore_Click(object sender, EventArgs e)
        {
            Type cstype = this.GetType();
            ClientScriptManager cs = Page.ClientScript;
            StringBuilder cstext1 = new StringBuilder();
            int maxbusinessTypeRestricted = 0; try { maxbusinessTypeRestricted = Convert.ToInt32(ConfigurationManager.AppSettings.Get("MaxBusinessTypeRestricted") ?? "0"); } catch { maxbusinessTypeRestricted = 0; }

            if (maxbusinessTypeRestricted >0 && gvRetailCategories.Rows.Count >= maxbusinessTypeRestricted && this.CurrentUser.PackageId < 2)
            {
                cstext1.Append("<script type=text/javascript> $('#modalupgrade').modal('show'); </");
                cstext1.Append("script>");
                cs.RegisterStartupScript(cstype, "Upgrade", cstext1.ToString());

                return;
            }

            CurViewType = "2";
            //ltrAddTitle.Text = "Add new store";

            String csname1 = "PopupAddBType";
            cstext1.Append("<script type=text/javascript> $('#ADDRESS').modal('show'); </");
            cstext1.Append("script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());


        }

        protected void btnAddBTypes_Click(object sender, EventArgs e)
        {

            string sql = @"SELECT bt.business_type_id,bt.business_type_name,IF((STATUS=1),'Active','Inactive') AS STATUS FROM finascop_business_type bt
INNER JOIN finascop_branch_group_business_type sbt ON bt.business_type_id = sbt.business_type_id AND sbt.store_group_id=" + this.CurrentUser.APIStoreId;
            DataTable dtBTypes = DataServiceMySql.GetDataTable(sql, UserService.GetAPIConnectionString());

            int maxbusinessTypeRestricted = 0; try { maxbusinessTypeRestricted = Convert.ToInt32(ConfigurationManager.AppSettings.Get("MaxBusinessTypeRestricted") ?? "0"); } catch { maxbusinessTypeRestricted = 0; }

            int maxRetailTypes = (this.CurrentUser.PackageId > 1 ? 10 : 5);
            if(maxbusinessTypeRestricted > 0)
                maxRetailTypes = maxbusinessTypeRestricted;

            int remainingRetailTypesCount = maxRetailTypes - (dtBTypes == null ? 0 : dtBTypes.Rows.Count);

            if (maxbusinessTypeRestricted > 0 && remainingRetailTypesCount <= 0)
            {
                Common.ShowCustomAlert(this.Page, "Failure", "Sorry, you have selected the maximum allowed retail types already. Please upgrade your package for add more or contact support for details.", false);
                return;
            }

            List<int> blist = new List<int>();
            if(dtBTypes != null && dtBTypes.Rows.Count > 0)
                blist = dtBTypes.AsEnumerable().Select(r => (int)r["business_type_id"]).ToList();

            string secondaryBTypes = "";
            //string strBusinessTypes = "";// selBusinessTypes.SelectedItem.Text;
            List<int> secondaryBTypeIds = new List<int>();
            bool moreItemsSelected = false;
            if (maxbusinessTypeRestricted <=0 || remainingRetailTypesCount > 0)
                foreach (ListItem item in lstBusinessTypes.Items)
                {
                    if (item.Selected && remainingRetailTypesCount <= 0)
                        moreItemsSelected = true;

                    if (item.Selected && (maxbusinessTypeRestricted <= 0 || remainingRetailTypesCount > 0))
                    {
                        try
                        {
                            int secBType = Convert.ToInt32(item.Value);
                            if (blist.Contains(secBType))
                                continue;

                            secondaryBTypes += (String.IsNullOrWhiteSpace(secondaryBTypes) ? "" : ",") + item.Text;
                            secondaryBTypeIds.Add(secBType);
                            remainingRetailTypesCount--;
                        }
                        catch { }
                    }
                }

            if(secondaryBTypeIds.Count <= 0)
            {
                Common.ShowCustomAlert(this.Page, "Failure", "No item selected. Please make sure that your have selected the business type that was not in the list that you have already added", false);
                return;
            }

            List<KeyValuePair<String, Object>> parmeters = new List<KeyValuePair<string, object>>();
            string strLogo = Guid.NewGuid().ToString();
            parmeters.Add(new KeyValuePair<string, object>("StoreGroupId", this.CurrentUser.StoreGroupId));
            parmeters.Add(new KeyValuePair<string, object>("User", Page.User.Identity.Name));
            parmeters.Add(new KeyValuePair<string, object>("SecondaryBusinessTypes", secondaryBTypes));
            string updatesql = "UPDATE Store SET SecondaryBusinessTypes=SecondaryBusinessTypes + ', ' + @SecondaryBusinessTypes, UpdatedOn = getutcdate(), UpdatedBy=@User WHERE TenantId=@StoreGroupId;";
            int strresult = DataService.ExecuteSql(updatesql, parmeters: parmeters);
            // Activitylog
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            string Source = strUrl;
            int storegroupid = this.CurrentUser.APIStoreId;
            string Users = this.CurrentUser.Email;
            string store = storegroupid.ToString();
            string User = Page.User.Identity.Name;
            string SecondaryBusinessTypes = secondaryBTypes;           
            var items = new[]
             {
                    new { Key = "Store Group", Value = store },
                    new { Key = "User", Value = User },
                    new { Key = "SecondaryBusinessTypes", Value = SecondaryBusinessTypes },
             };
            string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
            var strresults = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);

            try
            {
                if (secondaryBTypeIds.Count > 0)
                    Services.StoreService.AppendBusinessTypes(this.CurrentUser.APIStoreId, secondaryBTypeIds);
            }
            catch { }
            Service.UserService.CachedDefaultUser = null;
            CurViewType = "1";
            SDSRetailCategories.Select(DataSourceSelectArguments.Empty);
            gvRetailCategories.DataBind();
            string msg = "Retail category added successfully. ";
            if(maxbusinessTypeRestricted > 0)
                msg+= "Maximum allowed count is: " + maxRetailTypes + (moreItemsSelected ? ". Additional items selected were skipped. You can upgrade your package or delete existing itmes to add more" : "");
            Common.ShowToastifyMessage(this.Page, msg);
        }

        protected void gvRetailCategories_RowCommand(object sender, GridViewCommandEventArgs e)
        {
            if (e.CommandName == "Delete" && e.CommandArgument != null)
            {
            }
        }

        protected void lbtnDelete_Click(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;
            if(lbtn != null && !String.IsNullOrEmpty(lbtn.Attributes["rcid"]))
            {
                int key = 0; try { key = Convert.ToInt32(lbtn.Attributes["rcid"]); } catch { key = 0; }
                if(key <= 0)
                {
                    Common.ShowToastifyMessage(this.Page, "Error! Invalid selection", "danger");
                    return;
                }

                if (key > 0)
                {
                    List<KeyValuePair<String, Object>> prms = new List<KeyValuePair<string, object>>();
                    prms.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));
                    prms.Add(new KeyValuePair<string, object>("rettype", key));
                    string sqlCheckProducts = @"SELECT COUNT(*) FROM finascop_stock_itemmaster i 
INNER JOIN (SELECT stit_id FROM finascop_stock_branch_inventory AS bi INNER JOIN finascop_branch AS b ON b.br_ID=bi.branch_id WHERE br_storeGroup=@storegroupid GROUP BY stit_id )br ON br.stit_id=i.stit_id
INNER JOIN mypha_productsubcategory sc ON sc.sub_category_id = i.product_category INNER JOIN mypha_productcategory c ON sc.main_category=c.category_id 
INNER JOIN mypha_productparent_category pc ON c.parent_category = pc.parent_category_id WHERE parent_category_businessType=@rettype";
                    DataTable dtProducts = DataServiceMySql.GetDataTable(sqlCheckProducts, UserService.GetAPIConnectionString(), prms);

                    if (dtProducts != null && dtProducts.Rows.Count > 0 && Convert.ToInt32(dtProducts.Rows[0][0]) > 0)
                    {
                        Common.ShowToastifyMessage(this.Page, "Execution failure. There are products available in the selected retail category for deletion. Please make sure to clear the existing products added to the retail category before delete.", "danger");
                        //Common.ShowCustomAlert(this.Page, "Failure", "Execution failure. There are products available in the selected retail category for deletion. Please make sure to clear the existing products added to the retail category before delete.", false);
                        return;
                    }

                    string sqlDelete = "DELETE FROM finascop_branch_group_business_type WHERE store_group_id=@storegroupid AND business_type_id=@rettype ";
                    int count = DataServiceMySql.ExecuteSql(sqlDelete, UserService.GetAPIConnectionString(), prms);
                    // Activitylog
                    String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                    String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                    string Source = strUrl;
                    int storegroupid = this.CurrentUser.APIStoreId;
                    string Users = this.CurrentUser.Email;
                    string store = storegroupid.ToString();
                    string User = Page.User.Identity.Name;
                    string business_type_id = key.ToString();
                    var items = new[]
                    {
                    new { Key = "Store Group", Value = store },
                    new { Key = "User", Value = User },
                    new { Key = "Business Type Ids", Value = business_type_id },
                    };
                    string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                    var strresults = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);
                    SDSRetailCategories.Select(DataSourceSelectArguments.Empty);
                    gvRetailCategories.DataBind();
                    Common.ShowToastifyMessage(this.Page, "Retail category deleted successfully");
                }


            }
        }

        protected void SDSBusinessTypes_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["@storegroupid"].Value = this.CurrentUser.APIStoreId;
            if (this.CurrentUser.TenantType == 2 && System.Configuration.ConfigurationManager.AppSettings.Get("StoreDisableNoneVAT") == "1")
            {
                e.Command.Parameters["@isNoGST"].Value = 1;
            }

        }

        protected void SDSBusinessCategories_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["@storegroupid"].Value = this.CurrentUser.APIStoreId;
            if (this.CurrentUser.TenantType == 2 && System.Configuration.ConfigurationManager.AppSettings.Get("StoreDisableNoneVAT") == "1")
            {
                 e.Command.Parameters["@isNoGST"].Value = 1;
            }

        }
    }
}