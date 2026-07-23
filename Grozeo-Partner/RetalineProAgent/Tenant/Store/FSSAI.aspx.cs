using RetalineProAgent.Core.BussinessModel.Inventory;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using RetalineProAgent.Core.Services.Cache;

namespace RetalineProAgent.Tenant.Store
{
    public partial class FSSAI : Base.BasePartnerPage
    {

        protected void Page_Load(object sender, EventArgs e)
        {
            if (gvFSSAIAccounts.HeaderRow != null)
                gvFSSAIAccounts.HeaderRow.TableSection = TableRowSection.TableHeader;
        }

        protected void SDSFSSAIs_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            if (e.Command.Parameters.Contains("@storeId"))
                e.Command.Parameters["@storeId"].Value = this.CurrentUser.StoreGroupId;

        }

        protected async void selFSSAIAccount_SelectedIndexChanged(object sender, EventArgs e)
        {
            DropDownList dl = (DropDownList)sender;
            int storeid = -1;

            if (dl != null && !String.IsNullOrEmpty(dl.Attributes["storeid"]) && !String.IsNullOrEmpty(dl.SelectedValue))
                try { storeid = Convert.ToInt32(dl.Attributes["storeid"]); } catch { }

            if(storeid <= 0)
            {
                Common.ShowCustomAlert(this.Page, "Failure", "Invalid store", false);
                return;
            }

            if (storeid > 0)
            {
                List<KeyValuePair<string, object>> FSSAIParams = new List<KeyValuePair<string, object>>();
                FSSAIParams.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.StoreGroupId));
                FSSAIParams.Add(new KeyValuePair<string, object>("brnachId", storeid));
                FSSAIParams.Add(new KeyValuePair<string, object>("FSSAIid", dl.SelectedValue));
                string sql = "UPDATE StoreBranch SET FSSAI_Id=@FSSAIid WHERE Id=@brnachId AND StoreId=@tenantId AND EXISTS(SELECT * FROM FSSAI WHERE Id=@FSSAIid AND TenantId=@tenantId); select * from StoreBranch WHERE Id=@brnachId AND StoreId=@tenantId ";
                DataTable dtStores = DataService.GetDataTable(sql, parmeters: FSSAIParams);
                bool success = false;
                if(dtStores != null && dtStores.Rows.Count > 0)
                {
                    try
                    {
                        int apistoreid = Convert.ToInt32(dtStores.Rows[0]["APIBranchId"]);
                        if(apistoreid > 0)
                        {
                            string fssid = dl.SelectedItem.Text;
                            if(fssid.Contains("-"))
                                fssid = fssid.Substring(0, fssid.IndexOf("-")).Trim();

                            FSSAIParams = new List<KeyValuePair<string, object>>();
                            FSSAIParams.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.APIStoreId));
                            FSSAIParams.Add(new KeyValuePair<string, object>("brnachId", apistoreid));
                            FSSAIParams.Add(new KeyValuePair<string, object>("FSSAIid", fssid));
                            sql = "UPDATE finascop_branch set fssaiNo = @FSSAIid  WHERE br_id= @brnachId AND br_storeGroup=@tenantId";
                            DataServiceMySql.ExecuteSql(sql, parmeters: FSSAIParams);
                            success = true;
                        }
                    }
                    catch
                    {

                    }
                }

                // Remove Redis cache entry
                var cacheService = new RedisCacheService();
                string cachekey = $"Retl.AppTenant.pendingtasks.count.{this.CurrentUser.APIStoreId}";
                await cacheService.RemoveAsync(cachekey);

                // Activitylog
                String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                string Source = strUrl;
                int storegroupid = this.CurrentUser.APIStoreId; ;
                string User = this.CurrentUser.Email;
                string FSSAIid = dl.SelectedValue;
                string TenantId = storegroupid.ToString();
                string brnachId = (dtStores.Rows[0]["APIBranchId"]).ToString();                
                var items = new[]
                    {
                    new { Key = "FSSAI Id", Value = FSSAIid },
                    new { Key = "Tenant Id", Value = TenantId },
                    new { Key = "Brnach Id", Value = brnachId },                   
                };
                string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
                gvStores.EditIndex = -1;
                ODSStore.Select();
                gvStores.DataBind();
                if (success)
                    Common.ShowToastifyMessage(this.Page, "FSSAI updated successfully!");
                else
                    Common.ShowToastifyMessage(this.Page, "Failure! There is a technical error happened.", "danger");
            }
        }


        protected void ODSStore_Selecting(object sender, ObjectDataSourceSelectingEventArgs e)
        {
            e.InputParameters["storegroupid"] = this.CurrentUser.StoreGroupId;
            e.InputParameters["apistoregroupid"] = this.CurrentUser.APIStoreId;
        }

        protected void gvStores_RowCommand(object sender, GridViewCommandEventArgs e)
        {
            if (e.CommandName == "Update" && gvStores.SelectedIndex >= 0 && gvStores.SelectedRow != null)
            {
                DropDownList dl = (DropDownList)gvStores.SelectedRow.FindControl("selFSSAIAccount");
                if (dl != null && !String.IsNullOrEmpty(dl.Attributes["storeid"]) && !String.IsNullOrEmpty(dl.SelectedValue))
                {
                    int storeid = Convert.ToInt32(dl.Attributes["storeid"]);
                    List<KeyValuePair<string, object>> FSSAIParams = new List<KeyValuePair<string, object>>();
                    FSSAIParams.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.StoreGroupId));
                    FSSAIParams.Add(new KeyValuePair<string, object>("brnachId", storeid));
                    FSSAIParams.Add(new KeyValuePair<string, object>("FSSAIid", dl.SelectedValue));
                    string sql = "UPDATE StoreBranch SET FSSAI_Id=@FSSAIid WHERE Id=@brnachId AND StoreId=@tenantId AND EXISTS(SELECT * FROM FSSAI WHERE Id=@FSSAIid AND TenantId=@tenantId); select * from StoreBranch WHERE Id=@brnachId AND StoreId=@tenantId ";
                    //DataService.ExecuteSql(sql, parmeters: FSSAIParams);

                    DataTable dtStores = DataService.GetDataTable(sql, parmeters: FSSAIParams);
                    bool success = false;
                    if (dtStores != null && dtStores.Rows.Count > 0)
                    {
                        try
                        {
                            int apistoreid = Convert.ToInt32(dtStores.Rows[0]["APIBranchId"]);
                            if (apistoreid > 0)
                            {
                                string fssid = dl.SelectedItem.Text;
                                if (fssid.Contains("-"))
                                    fssid = fssid.Substring(0, fssid.IndexOf("-")).Trim();
                                FSSAIParams = new List<KeyValuePair<string, object>>();
                                FSSAIParams.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.APIStoreId));
                                FSSAIParams.Add(new KeyValuePair<string, object>("brnachId", apistoreid));
                                FSSAIParams.Add(new KeyValuePair<string, object>("FSSAIid", fssid));
                                sql = "UPDATE finascop_branch set fssaiNo = @FSSAIid  WHERE br_id= @brnachId AND br_storeGroup=@tenantId";
                                DataServiceMySql.ExecuteSql(sql, parmeters: FSSAIParams);
                                success = true;
                            }
                        }
                        catch
                        {

                        }
                    }
                    // Activitylog
                    String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                    String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                    string Source = strUrl;
                    int storegroupid = this.CurrentUser.APIStoreId; ;
                    string User = this.CurrentUser.Email;
                    string FSSAIid = dl.SelectedValue;
                    string TenantId = storegroupid.ToString();
                    string brnachId = (dtStores.Rows[0]["APIBranchId"]).ToString();
                    var items = new[]
                        {
                    new { Key = "FSSAI Id", Value = FSSAIid },
                    new { Key = "Tenant Id", Value = TenantId },
                    new { Key = "Brnach Id", Value = brnachId },
                    };
                    string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                    var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
                    if (success)
                        Common.ShowToastifyMessage(this.Page, "FSSAI updated successfully!");
                    else
                        Common.ShowToastifyMessage(this.Page, "Failure! There is a technical error happened.", "danger");

                    //ODSStore.Select();
                    //gvStores.DataBind();
                }


            }
        }

    }
}