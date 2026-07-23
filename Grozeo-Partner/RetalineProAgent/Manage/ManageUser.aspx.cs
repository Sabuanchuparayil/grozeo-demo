using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data.SqlClient;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.HtmlControls;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Manage
{
    public partial class ManageUser : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {

        }


        protected void btnDelete_Click(object sender, EventArgs e)
        {
            Common.ShowToastifyMessage(Page, "Cannot proceed with this action", "danger");
            return;

            Button btn = (Button)sender;
            if (!String.IsNullOrEmpty(btn.Attributes["userid"]))
            {
                try
                {
                    int userid = Convert.ToInt32(btn.Attributes["userid"]);
                    List<KeyValuePair<string, object>> userParams = new List<KeyValuePair<string, object>>();
                    userParams.Add(new KeyValuePair<string, object>("id", userid));
                    string sqlSelectUser = "SELECT u.*, isnull(a.StoreId, -1) as APIStoreId, a.Id as StoreGroupId FROM [User] u left join AppTenant a on a.Id=u.StoreGroupId WHERE u.Id = @id";
                    var dtUser = DataService.GetDataTable(sqlSelectUser, parmeters: userParams);
                    if (dtUser == null || dtUser.Rows.Count <= 0)
                    {
                        Common.ShowToastifyMessage(Page, "Invalid operation", "danger");
                        return;
                    }
                    var dr = dtUser.Rows[0];
                    int apistoreid = Convert.ToInt32(dr["APIStoreId"]);
                    if(apistoreid > 0)
                    {                        
                        var dtWholesaleUser = DataServiceMySql.GetDataTable($"SELECT * FROM finascop_branch WHERE br_isWholesaler =1 AND br_storeGroup={apistoreid}");
                        if(dtWholesaleUser != null && dtWholesaleUser.Rows.Count > 0)
                        {
                            Common.ShowToastifyMessage(Page, "Operation failed. Cannot delete a whole sale merchant user", "info");
                            return;
                        }
                    }

                    string sql = "if not exists(select * from User_UserRole_Mapping where UserId=@id and RoleId <= 3) begin delete User_UserRole_Mapping where UserId= @id; DELETE [User] WHERE Id=@id; end";
                    DataService.ExecuteSql(sql, parmeters: userParams);

                    SDSUsers.Select(DataSourceSelectArguments.Empty);
                    gvUsers.DataBind();
                    Common.ShowToastifyMessage(Page, "User deleted successfully!");
                }
                catch (Exception ex)
                {
                    Common.ShowToastifyMessage(Page, "Error: "+ ex.Message, "danger");
                }
            }
        }

        protected void selRole_DataBound(object sender, EventArgs e)
        {
            if(selRole.Items.Count >  0) {
                selRole.Items.Insert(0, new ListItem("All roles", "-1"));
            }

        }

        protected void gvUsers_RowDataBound(object sender, GridViewRowEventArgs e)
        {
            //if(e.Row.RowState == DataControlRowState.Edit)
            //{
            //    Repeater clist = (Repeater)e.Row.FindControl("rptRoles");
            //    var userId = DataBinder.Eval(e.Row.DataItem, "Id").ToString();
            //    SDSUserRoles.SelectParameters["userId"].DefaultValue = userId;
            //    clist.DataSource = SDSUserRoles.Select(DataSourceSelectArguments.Empty);
            //    clist.DataBind();


            //}
        }

        protected void clRoles_DataBound(object sender, EventArgs e)
        {

        }

        protected void rptRoles_ItemDataBound(object sender, RepeaterItemEventArgs e)
        {
            //ckRole2 = new System.Web.UI.HtmlControls.HtmlInputCheckBox
            HtmlInputCheckBox cbox = (HtmlInputCheckBox)e.Item.FindControl("ckRole");
            HtmlControl lbl = (HtmlControl)e.Item.FindControl("lbRole");
            lbl.Attributes.Add("for", cbox.ClientID);
            string strSelVal = cbox.Attributes["selId"];
            if (strSelVal == cbox.Value)
                cbox.Checked = true;
            //lbRole2 = new HtmlGenericControl
        }

        protected void gvUsers_RowEditing(object sender, GridViewEditEventArgs e)
        {
            //this.gvUsers.EditIndex = e.NewEditIndex;
            //// gvUsers.Rows[e.NewEditIndex].FindControl
            //Repeater clist = (Repeater)gvUsers.Rows[e.NewEditIndex].FindControl("rptRoles");
            //var userId = gvUsers.DataKeys[e.NewEditIndex].Value.ToString(); //DataBinder.Eval(e.Row.DataItem, "Id").ToString();
            //SDSUserRoles.SelectParameters["userId"].DefaultValue = userId;
            //clist.DataSource = SDSUserRoles.Select(DataSourceSelectArguments.Empty);
            //clist.DataBind();
        }

        protected void SDSUserRoles_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            if(gvUsers.EditIndex >= 0)
                e.Command.Parameters["@userId"].Value = gvUsers.DataKeys[gvUsers.EditIndex].Value;
        }

        protected void gvUsers_RowUpdating(object sender, GridViewUpdateEventArgs e)
        {
            int userId = Convert.ToInt32(gvUsers.DataKeys[e.RowIndex].Value);
            Repeater repeaterRoles = (Repeater)gvUsers.Rows[e.RowIndex].FindControl("rptRoles");
            List<string> lstRoleId = new List<string>();
            foreach (RepeaterItem item in repeaterRoles.Items)
            {
                HtmlInputCheckBox cbox = (HtmlInputCheckBox)item.FindControl("ckRole");
                if (cbox.Checked)
                    lstRoleId.Add(cbox.Value);
            }
            SDSUsers.UpdateParameters["Roles"].DefaultValue = string.Join(",", lstRoleId.ToArray());
            SDSUsers.UpdateParameters["Id"].DefaultValue = userId.ToString();


        }

        protected void SDSUsers_Updating(object sender, SqlDataSourceCommandEventArgs e)
        {
            // SDSUsers.UpdateCommand = " ";
            string strParam = e.Command.Parameters["@Roles"].Value.ToString();
            string strUserId = e.Command.Parameters["@Id"].Value.ToString();
            int userId = 0; try { userId = Convert.ToInt32(strUserId); } catch { userId = 0; }
            List<string> lstParams = new List<string>();
            if (!string.IsNullOrEmpty(strParam) && userId > 0)
            {
                foreach(string strVal in strParam.Split(','))
                {
                    int roleId = 0; try { roleId = Convert.ToInt32(strVal); } catch { roleId = 0; }
                    if (roleId > 0)
                        lstParams.Add($"(@Id, {roleId}, 1)");
                }
            }
            if(lstParams.Count > 0)
            {
                List<KeyValuePair<string, object>> prms=new List<KeyValuePair<string, object>> {new KeyValuePair<string, object>("Id", userId) };
                string sql = $" delete User_UserRole_Mapping where UserId=@Id; insert into User_UserRole_Mapping(UserId, RoleId, StoreGroupId) values {string.Join(",", lstParams.ToArray())}";
                DataService.ExecuteSql(sql, parmeters: prms);
            }

        }
    }
}