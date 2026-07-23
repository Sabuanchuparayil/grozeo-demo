using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;

namespace RetalineProAgent.Business.Controls
{
    public partial class BusinessHeaderUser: Base.BasePartnerUserControl
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            var user = this.CurrentUser;
            try
            {
                ltrUserShort.Text = (user.FullName.Length > 30 ? user.FullName.Substring(0, 28) + ".." : user.FullName);
                ltrUserLong.Text = user.FullName;
                ltrSmallText.Text = String.Format("Member since {0:MMM yyyy}", user.CreatedOn);
                ltrEmail.Text = Page.User.Identity.Name;
                if (Page.User.IsInRole("SuperAdmin"))
                    ltrRole.Text = "Super Admin";
                else if (Page.User.IsInRole("RetalineProAgent"))
                    ltrRole.Text = "Admin";
                else if (Page.User.IsInRole("Agent"))
                    ltrRole.Text = "Master";
                else if (Page.User.IsInRole("StoreAdmin"))
                    ltrRole.Text = "Store Admin";
                else if (Page.User.IsInRole("StoreManager"))
                    ltrRole.Text = "Store Manager";
                else if (Page.User.IsInRole("BranchManager"))
                    ltrRole.Text = "Branch Manager";
            }
            catch { }
            //plcManager.Visible = (Page.User.IsInRole("SuperAdmin"));
        }

        protected void lbtnDeleteMyAccount_Click(object sender, EventArgs e)
        {
            if (!Page.User.Identity.IsAuthenticated || System.Configuration.ConfigurationManager.AppSettings.Get("IsDemo") != "1")
            {
                Common.ShowToastifyMessage(this.Page, "Sorry, please contact adminitrator to delete user account.");
                return;
            }


            var user = this.CurrentUser;
            if (user == null || user.Id <= 0 || user.Email != Page.User.Identity.Name)
            {
                Common.ShowToastifyMessage(this.Page, "Error: Invalid user.");
                return;
            }

            try
            {
                List<KeyValuePair<string, object>> userParams = new List<KeyValuePair<string, object>>();
                userParams.Add(new KeyValuePair<string, object>("id", user.Id));
                string sqlSelectUser = "SELECT u.*, isnull(a.StoreId, -1) as APIStoreId, a.Id as StoreGroupId FROM [User] u left join AppTenant a on a.Id=u.StoreGroupId WHERE u.Id = @id";
                var dtUser = DataService.GetDataTable(sqlSelectUser, parmeters: userParams);
                if (dtUser == null || dtUser.Rows.Count <= 0)
                {
                    Common.ShowToastifyMessage(Page, "Invalid operation", "danger");
                    return;
                }
                var dr = dtUser.Rows[0];
                int apistoreid = Convert.ToInt32(dr["APIStoreId"]);
                if (apistoreid > 0)
                {
                    var dtWholesaleUser = DataServiceMySql.GetDataTable($"SELECT * FROM finascop_branch WHERE br_isWholesaler =1 AND br_storeGroup={apistoreid}");
                    if (dtWholesaleUser != null && dtWholesaleUser.Rows.Count > 0)
                    {
                        Common.ShowToastifyMessage(Page, "Operation failed. Cannot delete a whole sale merchant user", "danger");
                        return;
                    }
                }

                string sql = "if not exists(select * from User_UserRole_Mapping where UserId=@id and RoleId <= 3) begin delete User_UserRole_Mapping where UserId= @id; DELETE [User] WHERE Id=@id; end";
                int result = DataService.ExecuteSql(sql, parmeters: userParams);
                if (result <= 0)
                {
                    Common.ShowToastifyMessage(Page, "Operation failed. There is a technical error happened on delete user.", "danger");
                    return;
                }

                FormsAuthenticationService.SignOut();
                Response.Redirect("/signup");
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(Page, "Error: " + ex.Message, "danger");
            }



        }
    }
}