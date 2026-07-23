using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.Cache;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Controls
{
    public partial class ctrlLanguages : Base.BasePartnerUserControl
    {
        private string associateId = "";
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                BindPrimaryLanguageDropdown();
                BindSecondaryLanguageDropdown(null);
                LoadStoreInfo();
            }
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
            ScriptManager.RegisterStartupScript(this, GetType(), "ShowModalScript", "$('#modallanguage').modal('show');", true);
        }

        private void LoadStoreInfo()
        {
            try
            {
                List<KeyValuePair<string, object>> baparams = new List<KeyValuePair<string, object>>();
                baparams.Add(new KeyValuePair<string, object>("email", this.CurrentUser.Email));
                DataTable dtAssociate = DataServiceMySql.GetDataTable("SELECT id, baName FROM business_associate WHERE baEmail = @email",
                    UserService.GetAPIConnectionString(), baparams);

                if (dtAssociate != null && dtAssociate.Rows.Count > 0)
                {
                    DataRow firstRow = dtAssociate.Rows[0];
                    associateId = firstRow["id"].ToString();
                    AssocicateProfile(associateId);
                }
                else if(this.CurrentUser.APIStoreId != 0 && this.CurrentUser.APIStoreId > 0)
                {
                    int storegroupId = this.CurrentUser.APIStoreId;
                    TenantProfile(storegroupId);
                }
                
            }
            catch
            {
                Common.ShowToastifyMessage(this.Page, "Execution failed" + "Execution failure No item matches the criteria. Please contact support for more details.", "danger");
            }
            
        }

        private void AssocicateProfile(string associateId)
        {
            try
            {
                if (Convert.ToInt32(associateId) > 0)
                {
                    List<KeyValuePair<string, object>> associatelangparams = new List<KeyValuePair<string, object>>();
                    associatelangparams.Add(new KeyValuePair<string, object>("associateId", associateId));
                    DataTable dtFirstlanguage = DataServiceMySql.GetDataTable($"SELECT languageId, (SELECT name FROM language WHERE languageId=id) AS firstlanguageName FROM language_mapping WHERE type=3 AND typeId=@associateId AND isfeatured = 1", Service.UserService.GetAPIConnectionString(), associatelangparams);
                    if (dtFirstlanguage != null && dtFirstlanguage.Rows.Count > 0)
                    {
                        DataRow dr = dtFirstlanguage.Rows[0];
                        BindPrimaryLanguageDropdown();
                        selFirstLanguage.SelectedValue = dr["languageId"].ToString();
                        //rfvFirstLanguage.Visible = false;
                    }
                    DataTable dtSecondlanguage = DataServiceMySql.GetDataTable($"SELECT languageId, (SELECT name FROM language WHERE languageId=id) AS secondlanguageName FROM language_mapping WHERE type=3 AND typeId=@associateId AND isfeatured = 0", Service.UserService.GetAPIConnectionString(), associatelangparams);
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

        private void TenantProfile(int storegroupId)
        {
            try
            {
                if (storegroupId > 0)
                {
                    List<KeyValuePair<string, object>> tenantlangparams = new List<KeyValuePair<string, object>>();
                    tenantlangparams.Add(new KeyValuePair<string, object>("storegroupId", storegroupId));
                    DataTable dtFirstlanguage = DataServiceMySql.GetDataTable($"SELECT languageId, (SELECT name FROM language WHERE languageId=id) AS firstlanguageName FROM language_mapping WHERE type=2 AND typeId=@storegroupId AND isfeatured = 1", Service.UserService.GetAPIConnectionString(), tenantlangparams);
                    if (dtFirstlanguage != null && dtFirstlanguage.Rows.Count > 0)
                    {
                        DataRow dr = dtFirstlanguage.Rows[0];
                        BindPrimaryLanguageDropdown();
                        selFirstLanguage.SelectedValue = dr["languageId"].ToString();
                        //rfvFirstLanguage.Visible = false;
                    }
                    DataTable dtSecondlanguage = DataServiceMySql.GetDataTable($"SELECT languageId, (SELECT name FROM language WHERE languageId=id) AS secondlanguageName FROM language_mapping WHERE type=2 AND typeId=@storegroupId AND isfeatured = 0", Service.UserService.GetAPIConnectionString(), tenantlangparams);
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
        
        protected void btnSave_Click(object sender, EventArgs e)
        {
            try
            {
                List<KeyValuePair<string, object>> baparams = new List<KeyValuePair<string, object>>();
                baparams.Add(new KeyValuePair<string, object>("email", this.CurrentUser.Email));
                DataTable dtAssociate = DataServiceMySql.GetDataTable("SELECT id, baName FROM business_associate WHERE baEmail = @email",
                    UserService.GetAPIConnectionString(), baparams);

                if (dtAssociate != null && dtAssociate.Rows.Count > 0)
                {
                    DataRow firstRow = dtAssociate.Rows[0];
                    associateId = firstRow["id"].ToString();

                    if(Convert.ToInt32(associateId) > 0)
                    {
                        try
                        {
                            SaveAssocicateLanguage(associateId);
                        }
                        catch
                        {
                            Common.ShowToastifyMessage(this.Page, "Execution failed" + "Execution failure No item matches the criteria. Please contact support for more details.", "danger");
                        }
                        
                    }
                    else
                    {
                        Common.ShowToastifyMessage(this.Page, "Execution failed" + "Execution failure No item matches the criteria. Please contact support for more details.", "danger");
                    }
                }

                else if (this.CurrentUser.APIStoreId != 0 && this.CurrentUser.APIStoreId > 0)
                {
                    try
                    {
                        int storegroupId = this.CurrentUser.APIStoreId;
                        SaveTenantLanguage(storegroupId);
                    }
                    catch
                    {
                        Common.ShowToastifyMessage(this.Page, "Execution failed" + "Execution failure No item matches the criteria. Please contact support for more details.", "danger");
                    }
                }
            }
            catch
            {
                Common.ShowToastifyMessage(this.Page, "Execution failed" + "Execution failure No item matches the criteria. Please contact support for more details.", "danger");
            }
        }

        private async void SaveAssocicateLanguage(string associateId)
        {
            try
            {
                string strUpdateSql = "";
                List<KeyValuePair<string, object>> associateparams = new List<KeyValuePair<string, object>>();
                associateparams.Add(new KeyValuePair<string, object>("associateId", associateId));
                associateparams.Add(new KeyValuePair<string, object>("type", 3));
                if (!String.IsNullOrEmpty(selFirstLanguage.SelectedValue))
                {
                    associateparams.Add(new KeyValuePair<string, object>("primarylanguage", selFirstLanguage.SelectedValue));
                    associateparams.Add(new KeyValuePair<string, object>("isFeaturedPrimary", 1));

                    // Check if the primary language already exists
                    string checkPrimarySql = "SELECT COUNT(*) FROM language_mapping WHERE type=@type AND typeId = @associateId AND isfeatured = 1";
                    int primaryExists = Convert.ToInt32(DataServiceMySql.ExecuteScalar(checkPrimarySql, Service.UserService.GetAPIConnectionString(), associateparams));

                    if (primaryExists > 0)
                    {
                        strUpdateSql = @"
                                    UPDATE language_mapping SET languageId = @primarylanguage, type = @type, isfeatured = @isFeaturedPrimary 
                                    WHERE typeId = @associateId AND isfeatured = @isFeaturedPrimary;
                                ";
                    }
                    else
                    {
                        strUpdateSql += @"
                                    INSERT INTO language_mapping(languageId, type, typeId, isfeatured)
                                    VALUES(@primarylanguage, @type, @associateId, @isFeaturedPrimary);
                                ";
                    }
                }

                if (!String.IsNullOrEmpty(selSecondLanguage.SelectedValue))
                {
                    associateparams.Add(new KeyValuePair<string, object>("secondarylanguage", selSecondLanguage.SelectedValue));
                    associateparams.Add(new KeyValuePair<string, object>("isFeaturedSecondary", 0));

                    // Check if the secondary language already exists
                    string checkSecondarySql = "SELECT COUNT(*) FROM language_mapping WHERE type=@type AND typeId = @associateId AND isfeatured = 0";
                    int secondaryExists = Convert.ToInt32(DataServiceMySql.ExecuteScalar(checkSecondarySql, Service.UserService.GetAPIConnectionString(), associateparams));

                    if (secondaryExists > 0)
                    {
                        strUpdateSql += @"
                                    UPDATE language_mapping SET languageId = @secondarylanguage, type = @type, isfeatured = @isFeaturedSecondary 
                                    WHERE typeId = @associateId AND isfeatured = @isFeaturedSecondary;
                                ";
                    }
                    else
                    {
                        strUpdateSql += @"
                                    INSERT INTO language_mapping(languageId, type, typeId, isfeatured)
                                    VALUES(@secondarylanguage, @type, @associateId, @isFeaturedSecondary);
                                ";
                    }
                }
                DataServiceMySql.ExecuteSql(strUpdateSql, Service.UserService.GetAPIConnectionString(), associateparams);

                // Remove Redis cache entry
                var cacheService = new RedisCacheService();
                string cachekey = $"Retl.AppTenant.pendingtasks.count.{this.CurrentUser.APIStoreId}";
                await cacheService.RemoveAsync(cachekey);

                Common.ShowToastifyMessage(this.Page, "Language preferences saved successfully!");
            }
            catch
            {
                Common.ShowToastifyMessage(this.Page, "Execution failed" + "Execution failure No item matches the criteria. Please contact support for more details.", "danger");
            }
        }

        private async void SaveTenantLanguage(int storegroupId)
        {
            try
            {
                string strUpdateSql = "";
                List<KeyValuePair<string, object>> tenantparams = new List<KeyValuePair<string, object>>();
                tenantparams.Add(new KeyValuePair<string, object>("storegroupId", storegroupId));
                tenantparams.Add(new KeyValuePair<string, object>("type", 2));
                if (!String.IsNullOrEmpty(selFirstLanguage.SelectedValue))
                {
                    tenantparams.Add(new KeyValuePair<string, object>("primarylanguage", selFirstLanguage.SelectedValue));
                    tenantparams.Add(new KeyValuePair<string, object>("isFeaturedPrimary", 1));

                    // Check if the primary language already exists
                    string checkPrimarySql = "SELECT COUNT(*) FROM language_mapping WHERE type=@type AND typeId = @storegroupId AND isfeatured = 1";
                    int primaryExists = Convert.ToInt32(DataServiceMySql.ExecuteScalar(checkPrimarySql, Service.UserService.GetAPIConnectionString(), tenantparams));

                    if (primaryExists > 0)
                    {
                        strUpdateSql = @"
                                    UPDATE language_mapping SET languageId = @primarylanguage, type = @type, isfeatured = @isFeaturedPrimary 
                                    WHERE typeId = @storegroupId AND isfeatured = @isFeaturedPrimary;
                                ";
                    }
                    else
                    {
                        strUpdateSql += @"
                                    INSERT INTO language_mapping(languageId, type, typeId, isfeatured)
                                    VALUES(@primarylanguage, @type, @storegroupId, @isFeaturedPrimary);
                                ";
                    }
                }

                if (!String.IsNullOrEmpty(selSecondLanguage.SelectedValue))
                {
                    tenantparams.Add(new KeyValuePair<string, object>("secondarylanguage", selSecondLanguage.SelectedValue));
                    tenantparams.Add(new KeyValuePair<string, object>("isFeaturedSecondary", 0));

                    // Check if the secondary language already exists
                    string checkSecondarySql = "SELECT COUNT(*) FROM language_mapping WHERE type=@type AND typeId = @storegroupId AND isfeatured = 0";
                    int secondaryExists = Convert.ToInt32(DataServiceMySql.ExecuteScalar(checkSecondarySql, Service.UserService.GetAPIConnectionString(), tenantparams));

                    if (secondaryExists > 0)
                    {
                        strUpdateSql += @"
                                    UPDATE language_mapping SET languageId = @secondarylanguage, type = @type, isfeatured = @isFeaturedSecondary 
                                    WHERE typeId = @storegroupId AND isfeatured = @isFeaturedSecondary;
                                ";
                    }
                    else
                    {
                        strUpdateSql += @"
                                    INSERT INTO language_mapping(languageId, type, typeId, isfeatured)
                                    VALUES(@secondarylanguage, @type, @storegroupId, @isFeaturedSecondary);
                                ";
                    }
                }
                DataServiceMySql.ExecuteSql(strUpdateSql, Service.UserService.GetAPIConnectionString(), tenantparams);

                // Remove Redis cache entry
                var cacheService = new RedisCacheService();
                string cachekey = $"Retl.AppTenant.pendingtasks.count.{this.CurrentUser.APIStoreId}";
                await cacheService.RemoveAsync(cachekey);

                Common.ShowCustomAlert(this.Page, "Success", "Language preferences saved successfully!! If you want to edit them, go to your profile and select Edit Language Preference.", true, "/profile");
                return;

            }
            catch
            {
                Common.ShowToastifyMessage(this.Page, "Execution failed" + "Execution failure No item matches the criteria. Please contact support for more details.", "danger");
            }
            
        }

    }
}