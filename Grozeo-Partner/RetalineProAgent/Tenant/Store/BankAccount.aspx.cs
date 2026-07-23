using RetalineProAgent.Core.BussinessModel.Inventory;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Core.Services.Cache;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class BankAccount: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            if (gvBankAccounts.HeaderRow != null)
                gvBankAccounts.HeaderRow.TableSection = TableRowSection.TableHeader;
        }

        protected void SDSBanks_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            if (e.Command.Parameters.Contains("@storeId"))
                e.Command.Parameters["@storeId"].Value = this.CurrentUser.StoreGroupId;

        }

        protected async void selBankAccount_SelectedIndexChanged(object sender, EventArgs e)
        {
            DropDownList dl = (DropDownList)sender;
            if(dl != null && !String.IsNullOrEmpty(dl.Attributes["storeid"]) && !String.IsNullOrEmpty(dl.SelectedValue))
            {
                int storeid = Convert.ToInt32(dl.Attributes["storeid"]);
                List<KeyValuePair<string, object>> bankParams = new List<KeyValuePair<string, object>>();
                bankParams.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.StoreGroupId));
                bankParams.Add(new KeyValuePair<string, object>("brnachId", storeid));
                bankParams.Add(new KeyValuePair<string, object>("bankid", dl.SelectedValue));
                string sql = "UPDATE StoreBranch SET BankId=@bankid WHERE Id=@brnachId AND StoreId=@tenantId AND EXISTS(SELECT * FROM BankAccount WHERE Id=@bankid AND TenantId=@tenantId)";
                DataService.ExecuteSql(sql, parmeters: bankParams);

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
                //string[] items = {"selling price" = textSellingPrice.ToString(), "discount selling price"= discount_selling_price.ToString(),"mrp=" mrp.ToString(), "stock" = pStock.ToString(),"Item Id"= itemId.ToString() };

                string tenantId = storegroupid.ToString();
                string brnachId = storeid.ToString();
                string bankid = dl.SelectedValue;
                var items = new[]
                    {
                    new { Key = "Tenant Id", Value = tenantId },
                    new { Key = "Branch Id", Value = brnachId },
                    new { Key = "Bank Id", Value = bankid },
                    };
                string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
                gvStores.EditIndex = -1;
                ODSStore.Select();
                gvStores.DataBind();
            }
        }

        protected void chkStatus_CheckedChanged(object sender, EventArgs e)
        {
            CheckBox chbtn = (CheckBox)sender;
            if(chbtn == null || String.IsNullOrEmpty(chbtn.Attributes["brid"]))
            {
                Common.ShowToastifyMessage(this.Page, "Invalid Store", "danger");
                return;
            }
            string strbrid = chbtn.Attributes["brid"];

            if (!String.IsNullOrEmpty(strbrid))
            {
                try
                {
                    int brid = Convert.ToInt32(strbrid);
                    //Core.Services.APIService.ChangeBranchStatus(brid, chbtn.Checked);
                    int onlineStaus = (chbtn.Checked ? 1 : 0);
                    List<KeyValuePair<string, object>> sqlparams = new List<KeyValuePair<string, object>>();
                    sqlparams.Add(new KeyValuePair<string, object>("brid", brid));
                    sqlparams.Add(new KeyValuePair<string, object>("storeid", this.CurrentUser.APIStoreId));
                    sqlparams.Add(new KeyValuePair<string, object>("salesOnline", onlineStaus));
                    string strSql = "UPDATE finascop_branch SET br_SalesOnline= @salesOnline WHERE br_ID=@brid and br_storeGroup=@storeid";
                    DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), sqlparams);
                    // Activitylog
                    String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                    String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                    string Source = strUrl;
                    int storegroupid = this.CurrentUser.APIStoreId; ;
                    string User = this.CurrentUser.Email;

                    string br_ID = brid.ToString();
                    string salesOnline = onlineStaus.ToString();                  
                    var items = new[]
                        {
                    new { Key = "Branch Id", Value =br_ID },
                    new { Key = "Sales Online", Value = salesOnline },                                   
                    };
                    string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                    var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
                    Common.ShowToastifyMessage(this.Page, "Updated successfully");
                }
                catch (Exception ex)
                {
                    Common.ShowToastifyMessage(this.Page, "Execution failed" + ex.Message, "danger");
                }
            }

            //rptBranches.DataBind();
        }

        protected void ODSStore_Selecting(object sender, ObjectDataSourceSelectingEventArgs e)
        {
            e.InputParameters["storegroupid"] = this.CurrentUser.StoreGroupId;
            e.InputParameters["apistoregroupid"] = this.CurrentUser.APIStoreId;
        }

        protected void gvStores_RowCommand(object sender, GridViewCommandEventArgs e)
        {
            if(e.CommandName == "Update" && gvStores.SelectedIndex >= 0 && gvStores.SelectedRow != null)
            {
                DropDownList dl = (DropDownList)gvStores.SelectedRow.FindControl("selBankAccount");
                if (dl != null && !String.IsNullOrEmpty(dl.Attributes["storeid"]) && !String.IsNullOrEmpty(dl.SelectedValue))
                {
                    int storeid = Convert.ToInt32(dl.Attributes["storeid"]);
                    List<KeyValuePair<string, object>> bankParams = new List<KeyValuePair<string, object>>();
                    bankParams.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.StoreGroupId));
                    bankParams.Add(new KeyValuePair<string, object>("brnachId", storeid));
                    bankParams.Add(new KeyValuePair<string, object>("bankid", dl.SelectedValue));
                    string sql = "UPDATE StoreBranch SET BankId=@bankid WHERE Id=@brnachId AND StoreId=@tenantId AND EXISTS(SELECT * FROM BankAccount WHERE Id=@bankid AND TenantId=@tenantId)";
                    DataService.ExecuteSql(sql, parmeters: bankParams);
                    String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                    String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                    string Source = strUrl;
                    int storegroupid = this.CurrentUser.APIStoreId; ;
                    string User = this.CurrentUser.Email;
                    //string[] items = {"selling price" = textSellingPrice.ToString(), "discount selling price"= discount_selling_price.ToString(),"mrp=" mrp.ToString(), "stock" = pStock.ToString(),"Item Id"= itemId.ToString() };

                    string tenantId = storegroupid.ToString();
                    string brnachId = storeid.ToString();
                    string bankid = dl.SelectedValue;                   
                    var items = new[]
                        {
                    new { Key = "Tenant Id", Value = tenantId },
                    new { Key = "Branch Id", Value = brnachId },
                    new { Key = "Bank Id", Value = bankid },                   
                    };
                    string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                    var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
                    //ODSStore.Select();
                    //gvStores.DataBind();
                }


            }
        }
    }
}