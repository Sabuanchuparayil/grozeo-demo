using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Tenant
{
    public partial class ProductGroup_Manage : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                int productGroupId = 0;
                if (!String.IsNullOrEmpty(Request.QueryString["id"]))
                {
                    try { productGroupId = Convert.ToInt32(Request.QueryString["id"]); }catch(Exception ex) { productGroupId = 0; }
                }

                if (productGroupId <= 0)
                {
                    Common.ShowCustomAlert(this.Page, "Error", "Invalid group or you do not have access to this group data", false, "/tenant/productgroup");
                    return;
                }

                DataTable dt = DataServiceMySql.GetDataTable("select * from product_group where @pgid > 0 and id=@pgid and StoreGroupId=@storeId", UserService.GetAPIConnectionString()
                    , new List<KeyValuePair<string, object>> { new KeyValuePair<string, object>("pgid", productGroupId), new KeyValuePair<string, object>("storeId", this.CurrentUser.APIStoreId) });
                if(dt == null || dt.Rows.Count <= 0)
                {
                    Common.ShowCustomAlert(this.Page, "Error", "Invalid group or you do not have access to this group data", false, "/tenant/productgroup");
                    return;
                }
                string groupName = dt.Rows[0]["Name"].ToString();
                ltrGroupName.Text = groupName;
            }
        }

        protected void SDSGroupProduct_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            if (e.Command.Parameters.Contains("storeId"))
                e.Command.Parameters["storeId"].Value = this.CurrentUser.APIStoreId;//.StoreGroupId; 

        }

        protected void SDSSKU_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            if (e.Command.Parameters.Contains("storeId"))
                e.Command.Parameters["storeId"].Value = this.CurrentUser.APIStoreId;//.StoreGroupId; 
        }

        protected void btnAddToGroup_Click(object sender, EventArgs e)
        {
            int pid = 0; try { pid = Convert.ToInt32(selProduct.Text); } catch { pid = 0; }
            int groupId = 0; try { groupId = Convert.ToInt32(Request.QueryString["id"]); } catch { groupId = 0; }
            if(pid <= 0 || groupId <= 0)
            {
                Common.ShowToastifyMessage(this.Page, "Invalid product selection", "danger");
                return;
            }

            string sql = "UPDATE finascop_stock_branch_inventory bi INNER JOIN finascop_branch AS b ON b.br_ID=bi.branch_id AND b.br_storegroup=@storeId SET bi.variantGroupId= @groupId WHERE bi.stit_id=@stitId";
            int resultCount = DataServiceMySql.ExecuteSql(sql, UserService.GetAPIConnectionString(), 
                new List<KeyValuePair<string, object>> { new KeyValuePair<string, object>("groupId", groupId), new KeyValuePair<string, object>("stitId", pid)
                , new KeyValuePair<string, object>("storeId", this.CurrentUser.APIStoreId)});

            if (resultCount > 0)
                Common.ShowToastifyMessage(this.Page, "Product added to group successfully!!");
            else
                Common.ShowToastifyMessage(this.Page, "Operation failure.", "danger");

            SDSGroupProduct.Select(DataSourceSelectArguments.Empty);
            gvProducts.DataBind();
        }

        protected void SDSGroupProduct_Deleting(object sender, SqlDataSourceCommandEventArgs e)
        {
            if (e.Command.Parameters.Contains("storeId"))
                e.Command.Parameters["storeId"].Value = this.CurrentUser.APIStoreId;//.StoreGroupId; 

        }
    }
}