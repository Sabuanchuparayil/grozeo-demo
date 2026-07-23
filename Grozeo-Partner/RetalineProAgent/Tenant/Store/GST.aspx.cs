using RetalineProAgent.Core.BussinessModel.Inventory;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Core.Services.Cache;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class GST: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            //if(UserService.CachedDefaultUser.TenantStage == 2)
            //{
            //    Response.Redirect("/gst-add");
            //}

            if (gvGST.HeaderRow != null)
                gvGST.HeaderRow.TableSection = TableRowSection.TableHeader;
        }
        protected void Page_PreRender(object sender, EventArgs e)
        {
            try
            {
                if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK" && gvGST.HeaderRow.Cells[0].Text == "GSTIN")
                    gvGST.HeaderRow.Cells[0].Text = "VAT";
            }
            catch { }
        }

        protected void SDSGST_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            if (e.Command.Parameters.Contains("@storeId"))
                e.Command.Parameters["@storeId"].Value = this.CurrentUser.StoreGroupId;

        }

        protected void gvGST_RowDataBound(object sender, GridViewRowEventArgs e)
        {
            try
            {
                BoundField stitGSTBoundField = (BoundField)((DataControlFieldCell)e.Row.Cells[0]).ContainingField;
                if (stitGSTBoundField != null)
                {
                    if (ConfigurationManager.AppSettings.Get("VATType") == "2")
                    {
                        stitGSTBoundField.HeaderText = "GSTIN";
                    }
                    else
                    {
                        stitGSTBoundField.HeaderText = "VAT";
                    }
                }
            }
            catch
            {

            }
            
        }

        protected void ODSStore_Selecting(object sender, ObjectDataSourceSelectingEventArgs e)
        {
            e.InputParameters["storegroupid"] = this.CurrentUser.StoreGroupId;
            e.InputParameters["apistoregroupid"] = this.CurrentUser.APIStoreId;
        }

        protected async void selGstin_SelectedIndexChanged(object sender, EventArgs e)
        {
            DropDownList dl = (DropDownList)sender;
            if (dl != null && !String.IsNullOrEmpty(dl.Attributes["storeid"]) && !String.IsNullOrEmpty(dl.SelectedValue))
            {
                int storeid = Convert.ToInt32(dl.Attributes["storeid"]);
                List<KeyValuePair<string, object>> gstParams = new List<KeyValuePair<string, object>>();
                gstParams.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.StoreGroupId));
                gstParams.Add(new KeyValuePair<string, object>("brnachId", storeid));
                gstParams.Add(new KeyValuePair<string, object>("gstid", dl.SelectedValue));

                string strSql = $"SELECT a.Id, a.Name,APIBranchId,Location FROM AppTenant a left join StoreBranch sb on sb.StoreId = a.Id WHERE a.Id = @tenantId and sb.Id = @brnachId";
                DataTable dt = DataService.GetDataTable(strSql, parmeters: gstParams);
                string strbranchId = "";
                if (dt != null && dt.Rows.Count > 0)
                {
                    DataRow dr = dt.Rows[0];
                    strbranchId = dr["APIBranchId"].ToString();

                }
                List<KeyValuePair<string, object>> gstParmeters = new List<KeyValuePair<string, object>>();
                gstParmeters.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.APIStoreId));
                gstParmeters.Add(new KeyValuePair<string, object>("brnachId", strbranchId));
                gstParmeters.Add(new KeyValuePair<string, object>("gstnid", dl.SelectedItem.Text));

                if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
                {
                    string strStateCode = $"SELECT br_ID,br_Name,br_GST,gst_state_code FROM finascop_branch INNER JOIN finascop_state ON br_State = st_ID WHERE br_storeGroup = @tenantId AND br_ID = @brnachId";
                    DataTable dz = DataServiceMySql.GetDataTable(strStateCode, UserService.GetAPIConnectionString(), parmeters: gstParmeters);
                    string strStecode = "";
                    if (dz != null && dz.Rows.Count > 0)
                    {
                        DataRow db = dz.Rows[0];
                        strStecode = db["gst_state_code"].ToString();
                    }
                    string trimmedString = dl.SelectedItem.Text.Substring(0, 2);
                    if (strStecode == trimmedString)
                    {
                        string sql = "UPDATE StoreBranch SET GSTId=@gstid WHERE Id=@brnachId AND StoreId=@tenantId AND EXISTS(SELECT * FROM GST WHERE id=@gstid AND tenantid=@tenantId)";
                        DataService.ExecuteSql(sql, parmeters: gstParams);


                        string sqlbranch = "UPDATE finascop_branch SET br_GST=@gstnid WHERE br_storeGroup=@tenantId AND br_ID=@brnachId";
                        DataServiceMySql.ExecuteSql(sqlbranch, Service.UserService.GetAPIConnectionString(), gstParmeters);
                    }
                    else
                    {

                        Common.ShowCustomAlert(this.Page, "Failure", "Your state code and GST is not matching. Please select matching GST.", false, "/Tenant/Store/GST");
                        return;
                    }
                }
                else if(ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
                {
                    string sql = "UPDATE StoreBranch SET GSTId=@gstid WHERE Id=@brnachId AND StoreId=@tenantId AND EXISTS(SELECT * FROM GST WHERE id=@gstid AND tenantid=@tenantId)";
                    DataService.ExecuteSql(sql, parmeters: gstParams);


                    string sqlbranch = "UPDATE finascop_branch SET br_GST=@gstnid WHERE br_storeGroup=@tenantId AND br_ID=@brnachId";
                    DataServiceMySql.ExecuteSql(sqlbranch, Service.UserService.GetAPIConnectionString(), gstParmeters);
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
                //string[] items = {"selling price" = textSellingPrice.ToString(), "discount selling price"= discount_selling_price.ToString(),"mrp=" mrp.ToString(), "stock" = pStock.ToString(),"Item Id"= itemId.ToString() };

                string tenantId = storegroupid.ToString();
                string brnachId = storeid.ToString();
                string gstid = dl.SelectedValue;
                var items = new[]
                    {
                    new { Key = "Tenant Id", Value = tenantId },
                    new { Key = "Branch Id", Value = brnachId },
                    new { Key = "GST Id", Value = gstid },
                    };
                string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
                gvStores.EditIndex = -1;
                ODSStore.Select();
                gvStores.DataBind();
            }
        }

        protected void gvStores_RowCommand(object sender, GridViewCommandEventArgs e)
        {
            if (e.CommandName == "Update" && gvStores.SelectedIndex >= 0 && gvStores.SelectedRow != null)
            {
                DropDownList dl = (DropDownList)gvStores.SelectedRow.FindControl("selGstin");
                if (dl != null && !String.IsNullOrEmpty(dl.Attributes["storeid"]) && !String.IsNullOrEmpty(dl.SelectedValue))
                {
                    int storeid = Convert.ToInt32(dl.Attributes["storeid"]);
                    List<KeyValuePair<string, object>> gstParams = new List<KeyValuePair<string, object>>();
                    gstParams.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.StoreGroupId));
                    gstParams.Add(new KeyValuePair<string, object>("brnachId", storeid));
                    gstParams.Add(new KeyValuePair<string, object>("gstid", dl.SelectedValue));
                    string sql = "UPDATE StoreBranch SET GSTId=@gstid WHERE Id=@brnachId AND StoreId=@tenantId AND EXISTS(SELECT * FROM GST WHERE id=@gstid AND tenantid=@tenantId)";
                    DataService.ExecuteSql(sql, parmeters: gstParams);
                    String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                    String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                    string Source = strUrl;
                    int storegroupid = this.CurrentUser.APIStoreId; ;
                    string User = this.CurrentUser.Email;
                    //string[] items = {"selling price" = textSellingPrice.ToString(), "discount selling price"= discount_selling_price.ToString(),"mrp=" mrp.ToString(), "stock" = pStock.ToString(),"Item Id"= itemId.ToString() };

                    string tenantId = storegroupid.ToString();
                    string brnachId = storeid.ToString();
                    string gstid = dl.SelectedValue;
                    var items = new[]
                        {
                    new { Key = "Tenant Id", Value = tenantId },
                    new { Key = "Branch Id", Value = brnachId },
                    new { Key = "GST Id", Value = gstid },
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