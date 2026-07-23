using Finascop.Services;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class DeliveryStaffSettings : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                reqpost.Enabled = ConfigurationManager.AppSettings["CountryCode"] != "AE";
                BindPrimaryLanguageDropdown();
                BindSecondaryLanguageDropdown(null);
                LoadStoreInfo();
                txtDOB.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
                txtLicenseValidity.Attributes.Add("min", DateTime.Now.ToString("yyyy-MM-dd"));
                bool isUK = ConfigurationManager.AppSettings.Get("CountryCode") != "IN";
                bool isLicenseChecked = chkvalidlicense.Checked= txtLicense.Enabled = txtLicenseValidity.Enabled;
                plclicence.Visible = isUK;
            }
            if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
            {
                txtPhone.Attributes.Add("maxlength", "10");
               
            }
            var isIndia = ConfigurationManager.AppSettings.Get("CountryCode") != "UK";
            rfvFirstLanguage.Enabled = rfvSecondLanguage.Enabled = isIndia;
            //else if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
            //{
            //    txtPhone.Attributes.Add("maxlength", "12");
            //}
          
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
            int boy_id = -1;
            if (!String.IsNullOrEmpty(Request.QueryString["id"]))
                try { boy_id = Convert.ToInt32(Request.QueryString["id"]); } catch { boy_id = 0; }

            if (boy_id > 0)
            {
                DataTable dt = DataServiceMySql.GetDataTable($"SELECT d_Name, l_Name, d_Add1, d_Add2, d_Add3, employee_type, emp_id, emp_ni_number, br_id, " +
                    $"emp_email_id, d_Ph1, d_dob, d_licence, d_licenceexpairy, d_DeliveryRange, d_isallowManualSchedule, d_isallowAutoSchedule FROM qugeo_driver WHERE d_ID = {boy_id}", Service.UserService.GetAPIConnectionString());
                if (dt == null || dt.Rows.Count <= 0)
                {
                    Common.ShowCustomAlert(this.Page, "Loading failed", "Invalid selection or the record is not existing", false, "/Tenant/DeliveryStaffs");
                    return;
                }

                DataRow da = dt.Rows[0];
                txtFirstName.Text = da["d_Name"].ToString();
                txtLastName.Text = da["l_Name"].ToString();
                txtAddress1.Text = da["d_Add1"].ToString();
                txtAddress2.Text = da["d_Add2"].ToString();
                txtPostCode.Text = da["d_Add3"].ToString();
                string strEmpType = da["employee_type"].ToString();
                string brid = da["br_id"].ToString();
                //if (selBranch.Items.Count < 2)
                //    selBranch.DataBind();
                //if (selBranch.Items.FindByValue(brid) != null)
                //    selBranch.SelectedValue = brid;

                if (!String.IsNullOrEmpty(strEmpType) && selEmpType.Items.FindByValue(strEmpType) != null)
                    selEmpType.Items.FindByValue(strEmpType).Selected = true;

                //selEmpType.SelectedItem.Text = da["employee_type"].ToString();
                txtEmpID.Text = da["emp_id"].ToString();
                txtEmpNINumber.Text = da["emp_ni_number"].ToString();
                txtEmailID.Text = da["emp_email_id"].ToString();               
                string phonenumber = da["d_Ph1"].ToString();
                string getphone = phonenumber.Replace((ConfigurationManager.AppSettings.Get("PhoneCountryCode")), "");
                txtPhone.Text = getphone;
                //txtDOB.Text = DateTime.Now.ToString("yyyy-MM-dd");
                try
                {
                    var dob = Convert.ToDateTime(da["d_dob"]);
                    if (dob != null)
                        txtDOB.Text = dob.ToString("yyyy-MM-dd");
                    else
                        txtDOB.Text = "";
                }
                catch { txtDOB.Text = ""; }
                if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
                {
                    try
                    {

                        var licenseExpiry = Convert.ToDateTime(da["d_licenceexpairy"]);
                        if (licenseExpiry != null)
                            txtLicenseValidity.Text = licenseExpiry.ToString("yyyy-MM-dd");
                        else
                            txtLicenseValidity.Text = "";
                    }
                    catch { txtLicenseValidity.Text = ""; }
                    txtLicense.Text = da["d_licence"].ToString();
                }
                else
                {
                    txtLicense.Text = da["d_licence"].ToString();
                    if (string.IsNullOrEmpty(da["d_licenceexpairy"].ToString()))
                    {
                        chkvalidlicense.Checked = false;
                        txtLicenseValidity.Text = null;

                    }
                    else
                    {
                        chkvalidlicense.Checked = true;
                    }

                }
                try
                {
                    var licenseExpiry = Convert.ToDateTime(da["d_licenceexpairy"]);
                    if (licenseExpiry != null)
                        txtLicenseValidity.Text = licenseExpiry.ToString("yyyy-MM-dd");
                    else
                        txtLicenseValidity.Text = "";
                }
                catch { txtLicenseValidity.Text = ""; }

                txtLicense.Text = da["d_licence"].ToString();
                //txtLicenseValidity.Text = DateTime.Now.ToString("yyyy-MM-dd");
                txtCoverageKM.Text = da["d_DeliveryRange"].ToString();

                string isManualSchedule = da["d_isallowManualSchedule"].ToString();
                string isAutoSChedule = da["d_isallowAutoSchedule"].ToString();

                chkManualSchedule.Checked = (isManualSchedule == "1");
                chkAutoSchedule.Checked = (isAutoSChedule == "1");

                DataTable dtFirstlanguage = DataServiceMySql.GetDataTable($"SELECT languageId, (SELECT name FROM language WHERE languageId=id) AS firstlanguageName FROM language_mapping WHERE type=5 AND typeId = {boy_id} AND isfeatured = 1", Service.UserService.GetAPIConnectionString());
                if (dtFirstlanguage != null && dtFirstlanguage.Rows.Count > 0)
                {
                    DataRow dr = dtFirstlanguage.Rows[0];
                    BindPrimaryLanguageDropdown(); 
                    //selFirstLanguage.SelectedItem.Text = dr["firstlanguageName"].ToString();
                    selFirstLanguage.SelectedValue = dr["languageId"].ToString();
                    rfvFirstLanguage.Visible = false;
                }
                DataTable dtSecondlanguage = DataServiceMySql.GetDataTable($"SELECT languageId, (SELECT name FROM language WHERE languageId=id) AS secondlanguageName FROM language_mapping WHERE type=5 AND typeId = {boy_id} AND isfeatured = 0", Service.UserService.GetAPIConnectionString());
                if (dtSecondlanguage != null && dtSecondlanguage.Rows.Count > 0)
                {
                    DataRow dz = dtSecondlanguage.Rows[0];
                    BindSecondaryLanguageDropdown(selFirstLanguage.SelectedValue);
                    //selSecondLanguage.SelectedItem.Text = dz["secondlanguageName"].ToString();
                    selSecondLanguage.SelectedValue = dz["languageId"].ToString();
                    rfvSecondLanguage.Visible = false;
                }
            }
        }

        

        protected async void btnAdd_Click(object sender, EventArgs e)
        {
            try
            {
                int minDigits = 0;
                if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
                {
                    txtPhone.Attributes.Add("maxlength", "10");
                    minDigits = 10;
                }
                //else if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
                //{
                //    txtPhone.Attributes.Add("maxlength", "12");
                //    minDigits = 12;
                //}
                string phoneNumber =(ConfigurationManager.AppSettings.Get("PhoneCountryCode")) + txtPhone.Text;
                if (phoneNumber.Length < minDigits && ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
                {
                    lblMessage.Text = "Phone number must have at least " + minDigits + " digits.";
                    lblMessage.Visible = true;
                    return;
                }
                else
                {
                    lblMessage.Visible = false;
                }

                int id = 0;
                if (!String.IsNullOrEmpty(Request.QueryString["id"]))
                    id = Convert.ToInt32(Request.QueryString["id"]);
                string dobdate = txtDOB.Text;
                DateTime date1 = Convert.ToDateTime(dobdate);
                string currentDate = DateTime.Now.ToString("yyyy-MM-dd");
                DateTime date2 = Convert.ToDateTime(currentDate);

                int years = DateTime.Now.Year - date1.Year;

                string licensedate = null;
                if (!string.IsNullOrEmpty(txtLicenseValidity.Text))
                {
                    licensedate = txtLicenseValidity.Text;
                }
                DateTime date3 = Convert.ToDateTime(licensedate);
                string todayDate = DateTime.Now.ToString("yyyy-MM-dd");
                DateTime date4 = Convert.ToDateTime(currentDate);
                
                List<KeyValuePair<string, object>> boyparams = new List<KeyValuePair<string, object>>();
                boyparams.Add(new KeyValuePair<string, object>("driverId", id));
                boyparams.Add(new KeyValuePair<string, object>("storegroup", this.CurrentUser.APIStoreId));
                boyparams.Add(new KeyValuePair<string, object>("phone", phoneNumber));
                DataTable dtboyPhone = DataServiceMySql.GetDataTable($"SELECT COUNT(*) AS cnt FROM qugeo_driver LEFT JOIN finascop_branch fb ON qugeo_driver.br_id=fb.br_ID WHERE d_Ph1=@phone AND d_ID != @driverId", Service.UserService.GetAPIConnectionString(), boyparams);
                //if (dtboyPhone != null && dtboyPhone.Rows.Count > 0)
                //{
                    DataRow dr = dtboyPhone.Rows[0];
                    if (Convert.ToInt32(dr["cnt"]) > 0)
                    {
                        Common.ShowToastifyMessage(this.Page, "Duplicate phone number", "danger");
                    }
                //}
                else
                {



                    if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
                    {
                        //DateTime date3 = Convert.ToDateTime(licensedate);
                        //string todayDate = DateTime.Now.ToString("yyyy-MM-dd");
                        //DateTime date4 = Convert.ToDateTime(currentDate);
                        if (years < 18)
                        {
                            Common.ShowToastifyMessage(this.Page, "Please check Date of Birth", "danger");
                            return;
                        }

                        else if (date3 <= date4)
                        {
                            Common.ShowToastifyMessage(this.Page, "Please check Licence expiry date, licence expiry date should be greater than current date", "danger");
                            return;
                        }


                    }
                    else
                    {
                        if (years < 17)
                        {
                            Common.ShowToastifyMessage(this.Page, "Please check Date of Birth", "danger");
                        }

                    }
                    //if (years < 18)
                    //{
                    //    Common.ShowToastifyMessage(this.Page, "Please check Date of Birth", "danger");
                    //}

                        //else if (date3 <= date4)
                        //{
                        //    Common.ShowToastifyMessage(this.Page, "Please check Licence expiry date, licence expiry date should be greater than current date", "danger");
                        //}

                     if (chkAutoSchedule.Checked == false && chkManualSchedule.Checked == false)
                    {
                        Common.ShowToastifyMessage(this.Page, "Please select auto schedule or manual schedule", "danger");
                    }

                    else
                    {
                        int manulschedule = new int();
                        if (chkManualSchedule.Checked)
                        {
                            manulschedule = 1;
                        }
                        string checkmanualschedule = Convert.ToString(manulschedule);
                        int autoschedule = new int();
                        if (chkAutoSchedule.Checked)
                        {
                            autoschedule = 1;
                        }
                        string checkautoschedule = Convert.ToString(autoschedule);
                        var user = this.CurrentUser;
                        List<KeyValuePair<string, object>> baparams = new List<KeyValuePair<string, object>>();
                        baparams.Add(new KeyValuePair<string, object>("email", user.Email));
                        var tblNextDriverId = DataServiceMySql.GetDataTable($"SELECT coalesce(max( d_ID ),0)+1 AS dID FROM qugeo_driver", Service.UserService.GetAPIConnectionString());
                        DataTable result = DataServiceMySql.GetDataTable($"SELECT ba.id, balatitude, balongitude  FROM business_associate ba WHERE baEmail = @email", UserService.GetAPIConnectionString(), baparams);
                        if (result == null || result.Rows.Count <= 0 || tblNextDriverId == null || tblNextDriverId.Rows.Count <= 0)
                        {
                            Common.ShowCustomAlert(this.Page, "Error", "Invalid store or there is a technical error happened. Please contact support for more details.", false, "/Business/DeliveryStaff");
                            return;
                        }


                        string baId = result.Rows[0]["id"].ToString();
                        string brLat = result.Rows[0]["balatitude"].ToString();
                        string brLng = result.Rows[0]["balongitude"].ToString();
                        string d_ID = tblNextDriverId.Rows[0]["dID"].ToString();


                        List<KeyValuePair<string, object>> dvparams = new List<KeyValuePair<string, object>>();
                        dvparams.Add(new KeyValuePair<string, object>("fname", txtFirstName.Text));
                        dvparams.Add(new KeyValuePair<string, object>("lname", txtLastName.Text));
                        dvparams.Add(new KeyValuePair<string, object>("address1", txtAddress1.Text));
                        dvparams.Add(new KeyValuePair<string, object>("address2", txtAddress2.Text));
                        dvparams.Add(new KeyValuePair<string, object>("postcode", txtPostCode.Text));
                        dvparams.Add(new KeyValuePair<string, object>("empType", selEmpType.Text));
                        dvparams.Add(new KeyValuePair<string, object>("empId", txtEmpID.Text));
                        dvparams.Add(new KeyValuePair<string, object>("empNINum", txtEmpNINumber.Text));
                        dvparams.Add(new KeyValuePair<string, object>("email", txtEmailID.Text));
                        dvparams.Add(new KeyValuePair<string, object>("phone", phoneNumber));
                        dvparams.Add(new KeyValuePair<string, object>("dob", dobdate));
                        dvparams.Add(new KeyValuePair<string, object>("license", txtLicense.Text));
                        dvparams.Add(new KeyValuePair<string, object>("licenseDate", licensedate));
                        //dvparams.Add(new KeyValuePair<string, object>("brid", brId));
                        dvparams.Add(new KeyValuePair<string, object>("latitude", brLat));
                        dvparams.Add(new KeyValuePair<string, object>("longitude", brLng));
                        dvparams.Add(new KeyValuePair<string, object>("coverageKM", txtCoverageKM.Text));
                        dvparams.Add(new KeyValuePair<string, object>("isManualScheudle", (chkManualSchedule.Checked ? 1 : 0)));
                        dvparams.Add(new KeyValuePair<string, object>("isAutoSChedule", (chkAutoSchedule.Checked ? 1 : 0)));
                        dvparams.Add(new KeyValuePair<string, object>("createdBy", 2));
                        dvparams.Add(new KeyValuePair<string, object>("sourceId", baId));
                        dvparams.Add(new KeyValuePair<string, object>("type", 5));

                        if (id <= 0)
                        {
                            dvparams.Add(new KeyValuePair<string, object>("driverId", d_ID));
                            string strSql = $"INSERT ignore INTO qugeo_driver(d_ID, d_Name, l_Name, d_Add1, d_Add2, d_Add3, employee_type, emp_id, emp_ni_number, " +
                            $"emp_email_id, d_Ph1, d_dob, d_licence, d_licenceexpairy, d_HomeLati, d_HomeLong, d_DeliveryRange, d_isallowManualSchedule, d_isallowAutoSchedule, createdBy, sourceId) " +
                            $"VALUES(@driverId,@fname,@lname,@address1,@address2,@postcode,@empType,@empId,@empNINum,@email,@phone,@dob,@license,@licenseDate,@latitude,@longitude,@coverageKM,@isManualScheudle,@isAutoSChedule, @createdBy, @sourceId); " +
                            $" CALL nearestBranchesEnableExpressDelivery(@latitude, @longitude, @coverageKM, -1, -1); ";
                            if (!String.IsNullOrEmpty(selFirstLanguage.SelectedValue))
                            {
                                dvparams.Add(new KeyValuePair<string, object>("primarylanguage", selFirstLanguage.Text));
                                dvparams.Add(new KeyValuePair<string, object>("isFeaturedPrimary", 1));
                                strSql += $"INSERT INTO language_mapping(languageId, type, typeId, isfeatured) VALUES(@primarylanguage, @type, @driverId, @isFeaturedPrimary); ";
                            }

                            if (!String.IsNullOrEmpty(selSecondLanguage.SelectedValue))
                            {
                                dvparams.Add(new KeyValuePair<string, object>("secondarylanguage", selSecondLanguage.Text));
                                dvparams.Add(new KeyValuePair<string, object>("isFeaturedSecondary", 0));
                                strSql += $"INSERT INTO language_mapping(languageId, type, typeId, isfeatured) VALUES(@secondarylanguage, @type, @driverId, @isFeaturedSecondary); ";
                            }
                            DataServiceMySql.ExecuteSql(strSql, Service.UserService.GetAPIConnectionString(), dvparams);
                            ShowSuccess("Success", "Delivery staff created successfully!!");
                        }
                        else
                        {
                            dvparams.Add(new KeyValuePair<string, object>("driverId", id));
                            string strUpdateSql = $"UPDATE qugeo_driver SET d_Name=@fname, l_Name=@lname, d_Add1=@address1, d_Add2=@address2, " +
                                $"d_Add3=@postcode, employee_type=@empType, emp_id=@empId, emp_ni_number=@empNINum, emp_email_id=@email, " +
                                $"d_Ph1=@phone, d_dob=@dob,d_licence=@license, d_licenceexpairy=@licenseDate , d_HomeLati=@latitude, " +
                                $"d_HomeLong=@longitude, d_DeliveryRange=@coverageKM , d_isallowManualSchedule=@isManualScheudle , d_isallowAutoSchedule=@isAutoSChedule, createdBy=@createdBy, sourceId=@sourceId WHERE d_ID=@driverId; " +
                                $" CALL nearestBranchesEnableExpressDelivery(@latitude, @longitude, @coverageKM, -1, -1); ";
                            if (!String.IsNullOrEmpty(selFirstLanguage.SelectedValue))
                            {
                                dvparams.Add(new KeyValuePair<string, object>("primarylanguage", selFirstLanguage.SelectedValue));
                                dvparams.Add(new KeyValuePair<string, object>("isFeaturedPrimary", 1));

                                // Check if the primary language already exists
                                string checkPrimarySql = "SELECT COUNT(*) FROM language_mapping WHERE type=@type AND typeId = @driverId AND isfeatured = 1";
                                int primaryExists = Convert.ToInt32(DataServiceMySql.ExecuteScalar(checkPrimarySql, Service.UserService.GetAPIConnectionString(), dvparams));

                                if (primaryExists > 0)
                                {
                                    strUpdateSql += @"
                                    UPDATE language_mapping SET languageId = @primarylanguage, type = @type, isfeatured = @isFeaturedPrimary 
                                    WHERE typeId = @driverId AND isfeatured = @isFeaturedPrimary;
                                ";
                                }
                                else
                                {
                                    strUpdateSql += @"
                                    INSERT INTO language_mapping(languageId, type, typeId, isfeatured)
                                    VALUES(@primarylanguage, @type, @driverId, @isFeaturedPrimary);
                                ";
                                }
                            }

                            if (!String.IsNullOrEmpty(selSecondLanguage.SelectedValue))
                            {
                                dvparams.Add(new KeyValuePair<string, object>("secondarylanguage", selSecondLanguage.SelectedValue));
                                dvparams.Add(new KeyValuePair<string, object>("isFeaturedSecondary", 0));

                                // Check if the secondary language already exists
                                string checkSecondarySql = "SELECT COUNT(*) FROM language_mapping WHERE type=@type AND typeId = @driverId AND isfeatured = 0";
                                int secondaryExists = Convert.ToInt32(DataServiceMySql.ExecuteScalar(checkSecondarySql, Service.UserService.GetAPIConnectionString(), dvparams));

                                if (secondaryExists > 0)
                                {
                                    strUpdateSql += @"
                                    UPDATE language_mapping SET languageId = @secondarylanguage, type = @type, isfeatured = @isFeaturedSecondary 
                                    WHERE typeId = @driverId AND isfeatured = @isFeaturedSecondary;
                                ";
                                }
                                else
                                {
                                    strUpdateSql += @"
                                    INSERT INTO language_mapping(languageId, type, typeId, isfeatured)
                                    VALUES(@secondarylanguage, @type, @driverId, @isFeaturedSecondary);
                                ";
                                }
                            }
                            DataServiceMySql.ExecuteSql(strUpdateSql, Service.UserService.GetAPIConnectionString(), dvparams);
                            ShowSuccess("Success", "Delivery staff updated successfully!!");
                        }
                    }
                }
                
            }
            catch (Exception ex)
            {
                ShowFailure("Error", "Failure with error: " + ex.Message);
            }
        }

        private void ShowSuccess(string title, string content, string redirect = "")
        {
            Type cstype = this.GetType();
            String csname1 = "PopupScript";
            ClientScriptManager cs = Page.ClientScript;
            ltrSuccessTitle.Text = title;
            ltrSuccessContent.Text = content;

            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> $('#modaldemo4').modal('show'); {(string.IsNullOrEmpty(redirect) ? "" : "$('#modaldemo4').on('hidden.bs.modal', function (e) {window.location.href = '" + redirect + "'; });")}</");
            cstext1.Append("script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }

        private void ShowFailure(string title, string content)
        {
            ltrErrorPopupTitle.Text = title;
            ltrErrorPopupText.Text = content;
            Type cstype = this.GetType();
            String csname1 = "PopupScript";
            ClientScriptManager cs = Page.ClientScript;


            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append("<script type=text/javascript> $('#modaldemo5').modal('show'); </");
            cstext1.Append("script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());

        }

        protected void chkvalidlicense_CheckedChanged(object sender, EventArgs e)
        {
            bool isUK = ConfigurationManager.AppSettings.Get("CountryCode") != "IN";
            bool isLicenseChecked = chkvalidlicense.Checked;
            rqdLicense.Enabled = reqLicenseValidity.Enabled = txtLicense.Enabled = txtLicenseValidity.Enabled = isUK && isLicenseChecked;
            rqdLicense.Enabled = reqLicenseValidity.Enabled = txtLicense.Enabled = txtLicenseValidity.Enabled = !isUK;
            if (chkvalidlicense.Checked && ConfigurationManager.AppSettings.Get("CountryCode") != "IN")
            {
                txtLicense.Enabled = txtLicenseValidity.Enabled = true;
                rqdLicense.Enabled = reqLicenseValidity.Enabled = true;
            }
        }
    }
}


