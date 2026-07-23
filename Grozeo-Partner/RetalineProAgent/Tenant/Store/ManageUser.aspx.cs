using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Navigations;
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
    public partial class ManageUser: Base.BasePartnerPage
    {

        protected void Page_Load(object sender, EventArgs e)
        {
            plcRoles.Visible= (Page.User.IsInRole("SuperAdmin") || Page.User.IsInRole("RetalineProAgent")
                || Page.User.IsInRole("Agent") || Page.User.IsInRole("StoreAdmin")); // StoreAdmin,StoreManager
            if (IsPostBack)
                return;

            if (!String.IsNullOrEmpty(Request.QueryString["id"]))
            {
                //divPassword.Visible = false;

                int userid = Convert.ToInt32(Request.QueryString["id"]);
                string strSql = @"SELECT u.*, ur.RoleName, ur.Id as RoleId FROM [User] u 
left join User_UserRole_Mapping um on um.UserId=u.Id and (um.RoleId=1 or um.RoleId=2 or um.StoreGroupId= @storegroupid)
        left join UserRole ur on ur.Id=um.RoleId
		where u.Id=@userid and (um.RoleId is null or um.RoleId >= (select top 1 RoleId from User_UserRole_Mapping m inner join [User] u on u.Id=m.UserId where u.Email like @user order by RoleId asc))
and (EXISTS(SELECT * FROM User_UserRole_Mapping where StoreGroupId = @storegroupid and UserId=u.Id)) 
";
                List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                prms.Add(new KeyValuePair<string, object>("userid", userid));
                prms.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.StoreGroupId));
                prms.Add(new KeyValuePair<string, object>("user", Page.User.Identity.Name));

                DataTable dt= DataService.GetDataTable(strSql, parmeters: prms);
                //var user = UserService.GetCustomerById(userid);
                User user = null;
                if(dt != null && dt.Rows.Count > 0)
                    user = UserService.PopulateUser(dt.Rows[0]);
                if (user == null)
                {
                    ClientScript.RegisterClientScriptBlock(typeof(string), "InvalidStoreId",
                    @"<script language='javascript'>alert('Invalid user'); window.location.href='/Tenant/Store/Users'</script>");
                    return;
                }
                int? roleId = (int?)dt.Rows[0]["RoleId"];
                if (roleId != null && roleId > 0)
                {
                    if (selRoles.Items.Count <= 1)
                        selRoles.DataBind();
                    if (selRoles.Items.FindByValue(ParseUserRoleIdToRole(roleId.Value)) != null)
                        selRoles.Text = ParseUserRoleIdToRole(roleId.Value);
                    else
                        plcRoles.Visible = false;
                }
                this.Title = "Edit User";
                ltrTitle.Text = "Edit User";
                txtFullName.Text = user.FullName;
                txtEmail.Text = user.Email;
                txtEmail.Enabled = false;
                //txtPassword.Enabled = false;
                txtAddress.Text = user.Address;
                txtCity.Text = user.City;
                txtState.Text = user.State;
                txtMobile.Text = user.Phone;
                txtMobile.Enabled = false;
                btnAdd.Text = "Save";
            }
            var isIndia = ConfigurationManager.AppSettings.Get("CountryCode") != "UK";
            rfvFirstLanguage.Enabled = rfvSecondLanguage.Enabled = isIndia;
            if (!IsPostBack)
            {
                BindPrimaryLanguageDropdown();
                BindSecondaryLanguageDropdown(null);
                LoadStoreInfo();
            }

        }
        protected void Page_PreRender(object sender, EventArgs e)
        {
            plcBranches.Visible = selRoles.Text == "8";
        }

        protected void SDS_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        }

        protected void btnAdd_Click(object sender, EventArgs e)
        {

            int roleId = 5; // Store manager (lowest role)
            if (plcRoles.Visible && !String.IsNullOrEmpty(selRoles.SelectedValue))
                roleId = ParseUserRoleToRoleId(selRoles.SelectedItem.Text); //Convert.ToInt32(selRoles.SelectedValue);
            User _curUser = this.CurrentUser;

            if (!String.IsNullOrEmpty(Request.QueryString["id"]))
            {
                int userid = Convert.ToInt32(Request.QueryString["id"]);
                if (!plcRoles.Visible || String.IsNullOrEmpty(selRoles.SelectedValue))
                    roleId = -1;

                List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                prms.Add(new KeyValuePair<string, object>("userid", userid));
                prms.Add(new KeyValuePair<string, object>("fullName", txtFullName.Text));
                prms.Add(new KeyValuePair<string, object>("address", txtAddress.Text));
                prms.Add(new KeyValuePair<string, object>("city", txtCity.Text));
                prms.Add(new KeyValuePair<string, object>("state", txtState.Text));
                prms.Add(new KeyValuePair<string, object>("country", System.Configuration.ConfigurationManager.AppSettings.Get("CountryCode")));
                prms.Add(new KeyValuePair<string, object>("storegroupid", _curUser.StoreGroupId));
                prms.Add(new KeyValuePair<string, object>("createdby", User.Identity.Name));
                prms.Add(new KeyValuePair<string, object>("roleId", roleId));
                //prms.Add(new KeyValuePair<string, object>("usertype", (Page.User.IsInRole("SuperAdmin") || Page.User.IsInRole("RetalineProAgent") ? "0" : "1")));

                DataTable dt = DataService.GetDataTable("UpdateUser", parmeters: prms, isSP: true);

                if (ConfigurationManager.AppSettings.Get("CountryCode") != "UK")
                {
                    string strUpdateSql = "";
                    List<KeyValuePair<string, object>> userlangparams = new List<KeyValuePair<string, object>>();
                    userlangparams.Add(new KeyValuePair<string, object>("userid", userid));
                    userlangparams.Add(new KeyValuePair<string, object>("type", 4));
                    if (!String.IsNullOrEmpty(selFirstLanguage.SelectedValue))
                    {
                        userlangparams.Add(new KeyValuePair<string, object>("primarylanguage", selFirstLanguage.SelectedValue));
                        userlangparams.Add(new KeyValuePair<string, object>("isFeaturedPrimary", 1));

                        // Check if the primary language already exists
                        string checkPrimarySql = "SELECT COUNT(*) FROM language_mapping WHERE type=@type AND typeId = @userid AND isfeatured = 1";
                        int primaryExists = Convert.ToInt32(DataServiceMySql.ExecuteScalar(checkPrimarySql, Service.UserService.GetAPIConnectionString(), userlangparams));

                        if (primaryExists > 0)
                        {
                            strUpdateSql = @"
                                    UPDATE language_mapping SET languageId = @primarylanguage, type = @type, isfeatured = @isFeaturedPrimary 
                                    WHERE typeId = @userid AND isfeatured = @isFeaturedPrimary;
                                ";
                        }
                        else
                        {
                            strUpdateSql += @"
                                    INSERT INTO language_mapping(languageId, type, typeId, isfeatured)
                                    VALUES(@primarylanguage, @type, @userid, @isFeaturedPrimary);
                                ";
                        }
                    }

                    if (!String.IsNullOrEmpty(selSecondLanguage.SelectedValue))
                    {
                        userlangparams.Add(new KeyValuePair<string, object>("secondarylanguage", selSecondLanguage.SelectedValue));
                        userlangparams.Add(new KeyValuePair<string, object>("isFeaturedSecondary", 0));

                        // Check if the secondary language already exists
                        string checkSecondarySql = "SELECT COUNT(*) FROM language_mapping WHERE type=@type AND typeId = @userid AND isfeatured = 0";
                        int secondaryExists = Convert.ToInt32(DataServiceMySql.ExecuteScalar(checkSecondarySql, Service.UserService.GetAPIConnectionString(), userlangparams));

                        if (secondaryExists > 0)
                        {
                            strUpdateSql += @"
                                    UPDATE language_mapping SET languageId = @secondarylanguage, type = @type, isfeatured = @isFeaturedSecondary 
                                    WHERE typeId = @userid AND isfeatured = @isFeaturedSecondary;
                                ";
                        }
                        else
                        {
                            strUpdateSql += @"
                                    INSERT INTO language_mapping(languageId, type, typeId, isfeatured)
                                    VALUES(@secondarylanguage, @type, @userid, @isFeaturedSecondary);
                                ";
                        }
                    }
                    DataServiceMySql.ExecuteSql(strUpdateSql, Service.UserService.GetAPIConnectionString(), userlangparams);
                }

                // Activitylog
                String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                string Source = strUrl;
                int storegroupid = this.CurrentUser.APIStoreId; 

                string Users = this.CurrentUser.Email;
                string user_id = userid.ToString();
                string fullName = txtFullName.Text;
                string address = txtAddress.Text;
                string city = txtCity.Text;
                string state = txtState.Text;
                string country = System.Configuration.ConfigurationManager.AppSettings.Get("CountryCode");
                string storegroup_id = _curUser.StoreGroupId.ToString();
                string createdby = User.Identity.Name;
                string role_Id = roleId.ToString();
                var items = new[]
                    {
                    new { Key = "User Id", Value = user_id },
                    new { Key = "Full Name", Value = fullName },
                    new { Key = "Address", Value = address },
                    new { Key = "City", Value = city },
                    new { Key = "State", Value = state },
                    new { Key = "Country", Value = country },
                    new { Key = "StoregroupId", Value = storegroup_id },
                    new { Key = "Createdby", Value = createdby },
                    new { Key = "role Id", Value = role_Id },

                    };
                string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);
                if (dt == null || dt.Rows.Count < 1)
                {
                    Common.ShowToastifyMessage(this.Page, "Failure! There is a technical error happened while executing.", "danger");
                    //lblMessage.Text = "Failure! There is a technical error happened while executing.";
                }

                int result = (int)dt.Rows[0][0];
                if (result >= 1)
                {
                    Common.ShowCustomAlert(this.Page, "Success", "User updated successfully!!", true, "/Tenant/Store/Users");
                    //lblMessage.Text = "User updated successfully!";
                }
                else
                {
                    Common.ShowToastifyMessage(this.Page, "Operation failure", "danger");
                    //lblMessage.Text = "Failed!";
                }

                try {
                    if (selRoles.Text == "8" && selBranch.Text != "")
                    {
                        string sqlUpdate = "update User_UserRole_Mapping set BranchId = @brid, RoleId=@roleid where UserId=@userid and StoreGroupId=@storegroupid";
                        prms = new List<KeyValuePair<string, object>>();
                        prms.Add(new KeyValuePair<string, object>("storegroupid", _curUser.StoreGroupId));
                        prms.Add(new KeyValuePair<string, object>("userid", userid));
                        prms.Add(new KeyValuePair<string, object>("roleid", 8));
                        prms.Add(new KeyValuePair<string, object>("brid", selBranch.Text));
                        DataService.ExecuteSql(sqlUpdate, parmeters: prms);
                    }
                }
                catch { }

                return;
            }
            else
            {

                List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                prms.Add(new KeyValuePair<string, object>("email", txtEmail.Text));
                prms.Add(new KeyValuePair<string, object>("mobile", txtMobile.Text));

                DataTable dtCheckuser = DataService.GetDataTable("select * from [User] where Email like @email or Mobile like @mobile", parmeters: prms);
                if(dtCheckuser != null && dtCheckuser.Rows.Count > 0)
                {
                    Common.ShowCustomAlert(this.Page, "Failure", "Mobile number or email id is already exists. Please try with a different id", false);
                    return;
                }

                string tempPsw = System.Web.Security.Membership.GeneratePassword(8, 0);

                prms.Add(new KeyValuePair<string, object>("password", tempPsw));// txtPassword.Text));
                prms.Add(new KeyValuePair<string, object>("passwordType", 1));
                prms.Add(new KeyValuePair<string, object>("fullName", txtFullName.Text));
                prms.Add(new KeyValuePair<string, object>("address", txtAddress.Text));
                prms.Add(new KeyValuePair<string, object>("city", txtCity.Text));
                prms.Add(new KeyValuePair<string, object>("state", txtState.Text));
                prms.Add(new KeyValuePair<string, object>("country", System.Configuration.ConfigurationManager.AppSettings.Get("CountryCode")));
                prms.Add(new KeyValuePair<string, object>("storegroupid", _curUser.StoreGroupId));
                prms.Add(new KeyValuePair<string, object>("storegroupname", _curUser.StoreGroupName));
                prms.Add(new KeyValuePair<string, object>("createdby", User.Identity.Name));
                prms.Add(new KeyValuePair<string, object>("roleId", roleId));
                prms.Add(new KeyValuePair<string, object>("usertype", (Page.User.IsInRole("SuperAdmin") || Page.User.IsInRole("RetalineProAgent") ? "0" : "1")));

                string sql = "CreateUser";//@"exec CreateUser @email, @mobile, @password, @passwordType, @fullName, @address, @city, @state, @country, @storegroupid, @storegroupname, @createdby";
                DataTable dt = DataService.GetDataTable(sql, parmeters: prms, isSP: true);

                DataTable dtuserid = DataService.GetDataTable("select Id from [User] where Email like @email or Mobile like @mobile", parmeters: prms);
                if((ConfigurationManager.AppSettings.Get("CountryCode") != "UK"))
                {
                    if (dtuserid != null && dtuserid.Rows.Count > 0)
                    {
                        DataRow dr = dtuserid.Rows[0];
                        string userId = dr["Id"].ToString();
                        string strInsertSql = "";
                        List<KeyValuePair<string, object>> userlangparams = new List<KeyValuePair<string, object>>();
                        userlangparams.Add(new KeyValuePair<string, object>("userid", userId));
                        userlangparams.Add(new KeyValuePair<string, object>("type", 4));
                        if (!String.IsNullOrEmpty(selFirstLanguage.SelectedValue))
                        {
                            userlangparams.Add(new KeyValuePair<string, object>("primarylanguage", selFirstLanguage.SelectedValue));
                            userlangparams.Add(new KeyValuePair<string, object>("isFeaturedPrimary", 1));
                            strInsertSql += @"INSERT INTO language_mapping(languageId, type, typeId, isfeatured) VALUES(@primarylanguage, @type, @userid, @isFeaturedPrimary); ";

                        }
                        if (!String.IsNullOrEmpty(selSecondLanguage.SelectedValue))
                        {
                            userlangparams.Add(new KeyValuePair<string, object>("secondarylanguage", selSecondLanguage.SelectedValue));
                            userlangparams.Add(new KeyValuePair<string, object>("isFeaturedSecondary", 0));
                            strInsertSql += @"INSERT INTO language_mapping(languageId, type, typeId, isfeatured) VALUES(@secondarylanguage, @type, @userid, @isFeaturedSecondary); ";
                        }
                        DataServiceMySql.ExecuteSql(strInsertSql, Service.UserService.GetAPIConnectionString(), userlangparams);
                    }
                }                
                // Activitylog
                String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                String strUrls = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                string Source = strUrls;
                int storegroupid = this.CurrentUser.APIStoreId;

                string Users = this.CurrentUser.Email;
                string storegroupname = _curUser.StoreGroupName;
                string fullName = txtFullName.Text;
                string address = txtAddress.Text;
                string city = txtCity.Text;
                string state = txtState.Text;
                string country = System.Configuration.ConfigurationManager.AppSettings.Get("CountryCode");
                string storegroup_id = _curUser.StoreGroupId.ToString();
                string createdby = User.Identity.Name;
                string role_Id = roleId.ToString();
                string usertype = (Page.User.IsInRole("SuperAdmin") || Page.User.IsInRole("RetalineProAgent") ? "0" : "1");
                var items = new[]
                    {
                    new { Key = "Store Groupname", Value = storegroupname },
                    new { Key = "Full Name", Value = fullName },
                    new { Key = "Address", Value = address },
                    new { Key = "City", Value = city },
                    new { Key = "State", Value = state },
                    new { Key = "Country", Value = country },
                    new { Key = "StoregroupId", Value = storegroup_id },
                    new { Key = "Createdby", Value = createdby },
                    new { Key = "role Id", Value = role_Id },
                    new { Key = "User Type", Value = usertype },
                    };
                string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);
                if (dt == null || dt.Rows.Count < 1)
                {
                    Common.ShowToastifyMessage(this.Page, "Failure! There is a technical error happened while executing.", "danger");
                    //lblMessage.Text = "Failure! There is a technical error happened while executing.";
                }

                int result = (int)dt.Rows[0][0];
                if (result == 1)
                {
                    Common.ShowCustomAlert(this.Page, "Success", "User created successfully!", true, "/Tenant/Store/Users");
                    //lblMessage.Text = "User created successfully!";
                }
                else if (result == 2)
                {
                   Common.ShowCustomAlert(this.Page, "Success", "User linked to store successfully!", true, "/Tenant/Store/Users");
                   //lblMessage.Text = "User linked to store successfully!";
                }
                else if (result == -1)
                {
                    Common.ShowToastifyMessage(this.Page, "Failed, the email or mobile conflicted with an existing record. Please contact admin!", "danger");
                    //lblMessage.Text = "Failed, the email or mobile conflicted with an existing record. Please contact admin!";
                }
                else if (result == -2)
                {
                    Common.ShowCustomAlert(this.Page, "Failure", "Sorry you dont have permission to execute!", false, "/Tenant/Store/Users");
                    //lblMessage.Text = "Failed, lack of permission!";
                }
                try
                {
                    if (result == 1 || result == 2)
                    {
                        prms = new List<KeyValuePair<string, object>>();
                        prms.Add(new KeyValuePair<string, object>("email", txtEmail.Text));
                        prms.Add(new KeyValuePair<string, object>("mobile", txtMobile.Text));
                        string strsql = "UPDATE [User] SET hasVerifiedEmail=1 WHERE Email=@email AND Mobile=@mobile";
                        DataService.ExecuteSql(strsql, parmeters: prms);
                     
                    }                   

                }
                catch
                {

                }
                // Set Branch manager role if applicable.
                try
                {
                    if ((result == 1 || result == 2) && selRoles.Text == "8" && selBranch.Text != "")
                    {
                        string sqlUpdate = "update User_UserRole_Mapping set BranchId = @brid, RoleId=@roleid where UserId=(select Id from [User] where Email=@email and Mobile = @mobile) and StoreGroupId=@storegroupid";
                        prms = new List<KeyValuePair<string, object>>();
                        prms.Add(new KeyValuePair<string, object>("storegroupid", _curUser.StoreGroupId));
                        prms.Add(new KeyValuePair<string, object>("email", txtEmail.Text));
                        prms.Add(new KeyValuePair<string, object>("mobile", txtMobile.Text));
                        prms.Add(new KeyValuePair<string, object>("roleid", 8));
                        prms.Add(new KeyValuePair<string, object>("brid", selBranch.Text));
                        DataService.ExecuteSql(sqlUpdate, parmeters: prms);
                    }
                }
                catch(Exception ex) {
                    string strmsg = ex.Message;
                }
                // Send email
                try {
                    String strUrl = Request.Url.AbsoluteUri.Replace(Request.Url.PathAndQuery, "/").TrimEnd(new char[] {'/', '\\', ' ' });
                    List<KeyValuePair<string, string>> replacements = new List<KeyValuePair<string, string>>();
                    replacements.Add(new KeyValuePair<string, string>("[URLPART]", strUrl));
                    replacements.Add(new KeyValuePair<string, string>("[USER]", txtFullName.Text));

                    replacements.Add(new KeyValuePair<string, string>("[STORENAME]", _curUser.StoreGroupName));
                    replacements.Add(new KeyValuePair<string, string>("[MOBILE]", txtMobile.Text));
                    replacements.Add(new KeyValuePair<string, string>("[EMAIL]", txtEmail.Text));
                    replacements.Add(new KeyValuePair<string, string>("[TEMPORARYPSW]", tempPsw));
                    replacements.Add(new KeyValuePair<string, string>("[STOREURL]", strUrl));

                    string strBody = EmailService.CreateEmailbody(EmailType.NewStoreUserCreated, replacements);
                    // Send activation email.
                    Core.Services.APIService.SendEmail(txtEmail.Text, "Grozeo Store - New User", strBody, txtFullName.Text, true);

                }
                catch (Exception ex) { }

            }
        }

        protected void SDSRoles_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["@usertype"].Value = (Page.User.IsInRole("SuperAdmin") || Page.User.IsInRole("RetalineProAgent") ? "0" : "1");
            e.Command.Parameters["@user"].Value = Page.User.Identity.Name;
            if (e.Command.Parameters.Contains("@storeid"))
                e.Command.Parameters["@storeid"].Value = this.CurrentUser.StoreGroupId;
        }

        //public DataTable StoreRoles()
        //{
        //    DataTable dtRoles = new DataTable();
        //    dtRoles.Columns.Add("Id", typeof(string));
        //    dtRoles.Columns.Add("RoleName", typeof(string));

        //    dtRoles.Rows.Add(new object[] { "StoreAdmin", "Store Admin" });
        //    dtRoles.Rows.Add(new object[] { "StoreManager", "Store Manager" });

        //    return dtRoles;
        //}

        private int ParseUserRoleToRoleId(string strrole)
        {
            switch (strrole.ToLower())
            {
                case "storemanager":
                    return 5;
                case "storeadmin":
                    return 4;
                case "branchmanager":
                    return 8;
            }
            return 5; // Default store role is StoreManager
        }
        private string ParseUserRoleIdToRole(int roleid)
        {
            switch (roleid)
            {
                case 5:
                    return "StoreManager";
                case 4:
                    return "StoreAdmin";
            }
            return "StoreManager"; // Default store role is StoreManager
        }

        private void BindPrimaryLanguageDropdown()
        {
            var dt = DataServiceMySql.GetDataTable("SELECT id, name FROM language ORDER BY name", UserService.GetAPIConnectionString());

            selFirstLanguage.Items.Clear();
            selFirstLanguage.Items.Add(new ListItem("Select first preference", ""));

            selFirstLanguage.DataSource = dt;
            selFirstLanguage.DataTextField = "name";
            selFirstLanguage.DataValueField = "id";
            selFirstLanguage.DataBind();
        }

        private void BindSecondaryLanguageDropdown(string selectedPrimaryLanguage)
        {
            var dt = DataServiceMySql.GetDataTable("SELECT id, name FROM language WHERE isPreferred=1 ORDER BY name", UserService.GetAPIConnectionString());

            if (!string.IsNullOrEmpty(selectedPrimaryLanguage))
            {
                DataRow[] rowsToRemove = dt.Select($"id = '{selectedPrimaryLanguage}'");
                foreach (DataRow row in rowsToRemove)
                {
                    dt.Rows.Remove(row);
                }
            }

            selSecondLanguage.Items.Clear();
            selSecondLanguage.Items.Add(new ListItem("Select second preference", ""));

            selSecondLanguage.DataSource = dt;
            selSecondLanguage.DataTextField = "name";
            selSecondLanguage.DataValueField = "id";
            selSecondLanguage.DataBind();
        }

        protected void selFirstLanguage_SelectedIndexChanged(object sender, EventArgs e)
        {
            string selectedPrimaryLanguage = selFirstLanguage.SelectedValue;
            BindSecondaryLanguageDropdown(selectedPrimaryLanguage);
        }

        private void LoadStoreInfo()
        {
            try
            {
                if (!String.IsNullOrEmpty(Request.QueryString["id"]))
                {
                    string userId = Request.QueryString["id"];
                    List<KeyValuePair<string, object>> userlangparams = new List<KeyValuePair<string, object>>();
                    userlangparams.Add(new KeyValuePair<string, object>("userId", userId));
                    DataTable dtFirstlanguage = DataServiceMySql.GetDataTable($"SELECT languageId, (SELECT name FROM language WHERE languageId=id) AS firstlanguageName FROM language_mapping WHERE type=4 AND typeId=@userId AND isfeatured = 1", Service.UserService.GetAPIConnectionString(), userlangparams);
                    if (dtFirstlanguage != null && dtFirstlanguage.Rows.Count > 0)
                    {
                        DataRow dr = dtFirstlanguage.Rows[0];
                        BindPrimaryLanguageDropdown();
                        selFirstLanguage.SelectedValue = dr["languageId"].ToString();
                        //rfvFirstLanguage.Visible = false;
                    }
                    DataTable dtSecondlanguage = DataServiceMySql.GetDataTable($"SELECT languageId, (SELECT name FROM language WHERE languageId=id) AS secondlanguageName FROM language_mapping WHERE type=4 AND typeId=@userId AND isfeatured = 0", Service.UserService.GetAPIConnectionString(), userlangparams);
                    if (dtSecondlanguage != null && dtSecondlanguage.Rows.Count > 0)
                    {
                        DataRow dz = dtSecondlanguage.Rows[0];
                        BindSecondaryLanguageDropdown(selFirstLanguage.SelectedValue);
                        selSecondLanguage.SelectedValue = dz["languageId"].ToString();
                        //rfvSecondLanguage.Visible = false;
                    }
                }

            }
            catch
            {
                Common.ShowToastifyMessage(this.Page, "Execution failed" + "Execution failure No item matches the criteria. Please contact support for more details.", "danger");
            }

        }

    }
}