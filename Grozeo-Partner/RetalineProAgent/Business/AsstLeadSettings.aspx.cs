using Finascop.Services;
using log4net.Util.TypeConverters;
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
    public partial class AsstLeadSettings : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            //txtLeadType.Text = "Retail merchants";
            if (!IsPostBack)
            {

                LoadStoreInfo();
                if (!IsPostBack)
                {
                    if(ConfigurationManager.AppSettings["CountryCode"] == "AE")
                    {
                        txtPinCode.Attributes.Remove("required");
                    }
                    else
                    {
                        txtPinCode.Attributes.Add("required", "required");
                    }
                }
            }

            ctrlAddressMap1.ParentLocationClientId = hidMapAddr.ClientID; // txtLocation.ClientID;
            ctrlAddressMap1.ParentLocationClientId = txtLocation.ClientID;
            ctrlAddressMap1.ParentLatClientId = hidLat.ClientID;
            ctrlAddressMap1.ParentLongClientId = hidLong.ClientID;
            ctrlAddressMap1.ParentPinClientId = txtPinCode.ClientID;
            ctrlAddressMap1.ParentLocationNameClientId = txtAddr1.ClientID;
            //ctrlAddressMap1.ParentAddrClientId = txtAddr2.ClientID;
            ctrlAddressMap1.ParentStateClientId = hidState.ClientID;
            ctrlAddressMap1.ParentDistrictClientId = hidLocality.ClientID;
            ctrlAddressMap1.ParentPlaceClientId = hidState.ClientID;
            ctrlAddressMap1.ParentCountryClientId = hidCountry.ClientID;
            if (!String.IsNullOrEmpty(hidMapAddr.Value))
                txtLocation.Text = hidMapAddr.Value;
        }

        protected string GetPageTitle()
        {
            string type = Request.QueryString["type"];
            string id = type == "Lead" ? Request.QueryString["leadid"] : Request.QueryString["prospectid"];
            string action = string.IsNullOrEmpty(id) ? "Create New" : "Edit";
            string entityType = type == "Prospect" ? "Prospect" : "Lead";
            return $"{action} {entityType}";
        }

        private void LoadStoreInfo()
        {
            string type = Request.QueryString["type"];
            if (string.IsNullOrEmpty(type))
            {
                Common.ShowCustomAlert(this.Page, "Error", "Invalid selection or the record is not existing.", false, "/Business/ClientManagement?type=lead");
                return;
            }

            int id = GetIdFromQueryString(type);
            if (id <= 0)
            {
                Common.ShowCustomAlert(this.Page, "Loading failed", "Invalid selection or the record is not existing", false, "/Business/RetailerLeads");
                return;
            }

            if (type == "Lead")
            {
                LoadLeadData(id);
            }
            else if (type == "Prospect")
            {
                LoadProspectData(id);
            }
            else
            {
                Common.ShowCustomAlert(this.Page, "Loading failed", "Invalid selection or the record is not existing", false, "/Business/RetailerLeads");
            }
        }

        private int GetIdFromQueryString(string type)
        {
            string idLeadProspect = type == "Lead" ? "leadid" : "prospectid";
            if (int.TryParse(Request.QueryString[idLeadProspect], out int id))
            {
                return id;
            }
            return -1;
        }
        private void LoadLeadData(int leadId)
        {
            string query = @"
        SELECT id, crle_orgName, crle_mode, crle_type, 
               (SELECT id FROM crm_contact_type ct WHERE ct.id=crle_type) AS contactType, 
               crle_location, crle_orgPincode, crle_orgCountry, crle_glocality, 
               (SELECT business_type_name FROM finascop_business_type WHERE business_type_id = retailCategory) AS businessType,
               crle_gplace, glatitude, retailCategory, glongitude, areaId, areaName, 
               crle_orgAddress, crle_indContactperson, crle_indMobile, crle_orgContactNo, 
               crle_orgEmail, crle_CreatedFrom, crle_isActive 
        FROM finascop_crm_lead WHERE id = @rleadId";

            List<KeyValuePair<string, object>> parameters = new List<KeyValuePair<string, object>>()
            {
                new KeyValuePair<string, object>("@rleadId", leadId)
            };

            DataTable dt = DataServiceMySql.GetDataTable(query, Service.UserService.GetAPIConnectionString(), parameters);

            if (dt == null || dt.Rows.Count == 0)
            {
                Common.ShowCustomAlert(this.Page, "Loading failed", "Invalid selection or the record is not existing", false, "/Business/RetailerLeads");
                return;
            }

            PopulateFields(dt.Rows[0], "Lead");
        }

        private void LoadProspectData(int prospectId)
        {
            string query = @"SELECT id, crpr_orgName, crpr_mode, crpr_type, (SELECT id FROM crm_contact_type ct WHERE ct.id=crpr_type) AS contactType, crpr_location, crpr_orgPincode, crpr_orgCountry, crpr_glocality, (SELECT business_type_name FROM finascop_business_type WHERE business_type_id = retailCategory) AS businessType, crpr_gplace, glatitude, retailCategory, glongitude, areaId, areaName, crpr_orgAddress, crpr_indContactperson, crpr_indMobile, crpr_orgContactNo, crpr_orgEmail, crpr_CreatedFrom, crpr_isActive FROM finascop_crm_prospect WHERE id = @prospectId";

            List<KeyValuePair<string, object>> parameters = new List<KeyValuePair<string, object>>()
            {
                new KeyValuePair<string, object>("@prospectId", prospectId)
            };

            DataTable dt = DataServiceMySql.GetDataTable(query, Service.UserService.GetAPIConnectionString(), parameters);

            if (dt == null || dt.Rows.Count == 0)
            {
                Common.ShowCustomAlert(this.Page, "Loading failed", "Invalid selection or the record is not existing", false, "/Business/RetailerLeads");
                return;
            }

            PopulateFields(dt.Rows[0], "Prospect");
        }

        private void PopulateFields(DataRow data, string type)
        {
            try
            {
                if (data == null)
                {
                    Common.ShowCustomAlert(this.Page, "Error", "No data available to populate fields.", false, "/Business/ClientManagement?type=lead");
                    return;
                }

                string prefix = type == "Lead" ? "crle" : "crpr";

                hidLat.Value = data["glatitude"].ToString();
                hidLong.Value = data["glongitude"].ToString();
                txtStoreName.Text = data[$"{prefix}_orgName"].ToString();
                txtAddr1.Text = data[$"{prefix}_location"].ToString();
                txtPinCode.Text = data[$"{prefix}_orgPincode"].ToString();
                txtAddr2.Text = data[$"{prefix}_orgAddress"].ToString();
                txtContactPerson.Text = data[$"{prefix}_indContactperson"].ToString();
                txtMobile.Text = data[$"{prefix}_indMobile"].ToString();
                ctrlAddressMap1.Lat = hidLat.Value;
                ctrlAddressMap1.Lng = hidLong.Value;
                txtTelephoneNumber.Text = data[$"{prefix}_orgContactNo"].ToString();
                selRetailCategory.SelectedItem.Text = data["businessType"].ToString();
                selRetailCategory.SelectedValue = data["retailCategory"].ToString();
                txtEmail.Text = data[$"{prefix}_orgEmail"].ToString();
                txtLocation.Text = data[$"{prefix}_location"].ToString();
                hdfareaid.Value = data["areaId"].ToString();
                hdfareaname.Value = data["areaName"].ToString();
                hidCountry.Value = data[$"{prefix}_orgCountry"].ToString();
                hidLocality.Value = data[$"{prefix}_glocality"].ToString();

                if (lstContactType.Items.Count <= 0) lstContactType.DataBind();

                PopulateContactTypes(data["contactType"]?.ToString());
            }
            catch (Exception ex)
            {
                Common.ShowCustomAlert(this.Page, "Error", "An unexpected error occurred while populating fields.", false, "/Business/ClientManagement?type=lead");
            }
        }

        private void PopulateContactTypes(string contactTypes)
        {
            if (string.IsNullOrEmpty(contactTypes)) return;

            foreach (string type in contactTypes.Split(','))
            {
                if (!string.IsNullOrEmpty(type))
                {
                    ListItem selectedItem = lstContactType.Items.FindByValue(type.Trim());
                    if (selectedItem != null)
                    {
                        selectedItem.Selected = true;
                        txtleadtype.Text = selectedItem.Text;
                        txtleadtype.Visible = true;
                        txtleadtype.Enabled = false;
                        lstContactType.Visible = false;
                    }
                }
            }
        }

        protected void lstContactType_DataBound(object sender, EventArgs e)
        {
            if (lstContactType.Items.Count > 0)
            {
                string strKey = lstContactType.Attributes["DefaultBType"];
                if (!String.IsNullOrEmpty(strKey))
                {
                    string[] strbtypes = strKey.Trim().Split(',');
                    if (strbtypes.Length > 0)
                    {
                        foreach (string btype in strbtypes)
                            if (!String.IsNullOrEmpty(btype.Trim()) && lstContactType.Items.FindByText(btype.Trim()) != null)
                                lstContactType.Items.FindByText(btype.Trim()).Selected = true;
                    }
                }
            }
        }

        protected async void btnContactSubmit_Click(object sender, EventArgs e)
        {
            string primaryMobile = txtMobile.Text;
            string secondaryMobile = txtTelephoneNumber.Text;

            try
            {
                string type = Request.QueryString["type"];
                if (string.IsNullOrEmpty(type) || (type != "Lead" && type != "Prospect"))
                {
                    Common.ShowToastifyMessage(this.Page, "Invalid type.", "danger");
                    return;
                }

                int recordId = -1;
                if (type == "Lead" && !string.IsNullOrEmpty(Request.QueryString["leadid"]))
                    recordId = Convert.ToInt32(Request.QueryString["leadid"]);
                else if (type == "Prospect" && !string.IsNullOrEmpty(Request.QueryString["prospectid"]))
                    recordId = Convert.ToInt32(Request.QueryString["prospectid"]);

                var user = this.CurrentUser;

                List<KeyValuePair<string, object>> baparams = new List<KeyValuePair<string, object>>()
                {
                    new KeyValuePair<string, object>("email", user.Email)
                };
                DataTable baTable = DataServiceMySql.GetDataTable(
                    "SELECT ba.id, ba.baName FROM business_associate ba WHERE baEmail = @email",
                    UserService.GetAPIConnectionString(), baparams
                );

                string baId = "", baName = "";
                if (baTable != null && baTable.Rows.Count > 0)
                {
                    DataRow dz = baTable.Rows[0];
                    baId = dz["id"].ToString();
                    baName = dz["baName"].ToString();
                }

                // Determine area if not already set
                if (string.IsNullOrEmpty(hdfareaid.Value))
                {
                    var result = Core.Services.APIService.getAreaForLead(hidLat.Value, hidLong.Value);
                    hdfareaid.Value = Convert.ToString(result.id);
                    hdfareaname.Value = Convert.ToString(result.areaName);
                }

                // Process Selected Contact Types
                foreach (ListItem item in lstContactType.Items)
                {
                    if (!item.Selected) continue;

                    List<KeyValuePair<string, object>> parameters = new List<KeyValuePair<string, object>>()
                    {
                        new KeyValuePair<string, object>("recordId", recordId),
                        new KeyValuePair<string, object>("name", txtStoreName.Text),
                        new KeyValuePair<string, object>("crmMode", 2),
                        new KeyValuePair<string, object>("contactType", item.Value),
                        new KeyValuePair<string, object>("location", txtAddr2.Text),
                        new KeyValuePair<string, object>("pincode", txtPinCode.Text),
                        new KeyValuePair<string, object>("address", txtAddr2.Text),
                        new KeyValuePair<string, object>("latitude", hidLat.Value),
                        new KeyValuePair<string, object>("longitude", hidLong.Value),
                        new KeyValuePair<string, object>("contactPerson", txtContactPerson.Text),
                        new KeyValuePair<string, object>("country", hidCountry.Value),
                        new KeyValuePair<string, object>("locality", hidLocality.Value),
                        new KeyValuePair<string, object>("place", hidState.Value),
                        new KeyValuePair<string, object>("mobileNumber", primaryMobile),
                        new KeyValuePair<string, object>("contactNumber", secondaryMobile),
                        new KeyValuePair<string, object>("retailerCat", selRetailCategory.SelectedValue),
                        new KeyValuePair<string, object>("email", txtEmail.Text),
                        new KeyValuePair<string, object>("createdFrom", 2),
                        new KeyValuePair<string, object>("activeStatus", 1),
                        new KeyValuePair<string, object>("createdBy", baId),
                        new KeyValuePair<string, object>("assignedArea", 1),
                        new KeyValuePair<string, object>("areaId", hdfareaid.Value),
                        new KeyValuePair<string, object>("areaName", hdfareaname.Value)
                    };

                    if (recordId <= 0)
                    {
                        // Insert
                        if (type == "Lead")
                        {
                            InsertLead(parameters);
                        }
                        
                    }
                    else
                    {
                        // Update
                        if (type == "Lead")
                        {
                            UpdateLead(parameters);
                        }
                        else if (type == "Prospect")
                        {
                            UpdateProspect(parameters);
                        }
                    }

                    ShowSuccess("Updated Successfully!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Data has been updated successfully!</a></h5>");
                }
                
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(this.Page, $"Execution failed: {ex.Message}", "danger");
            }
        }

        private void InsertLead(List<KeyValuePair<string, object>> parameters)
        {
             string insertQuery = @"INSERT INTO finascop_crm_lead (crle_orgName, crle_mode, crle_type, crle_location, crle_orgPincode, crle_orgCountry, crle_glocality, crle_gplace, glatitude, glongitude, crle_orgAddress, crle_indContactperson, crle_indMobile, crle_orgContactNo, retailCategory, crle_orgEmail, crle_CreatedFrom, crle_CreatedBy, crle_isActive, isLeadAreaAssigned, areaId, areaName) VALUES (@name, @crmMode, @contactType, @location, @pincode, @country, @locality, @place, @latitude, @longitude, @address, @contactPerson, @mobileNumber, @contactNumber, @retailerCat, @email, @createdFrom, @createdBy, @activeStatus, @assignedArea, @areaId, @areaName)";
            DataServiceMySql.ExecuteSql(insertQuery, UserService.GetAPIConnectionString(), parameters);
        }

        private void UpdateLead(List<KeyValuePair<string, object>> parameters)
        {
             string updateQuery = @"UPDATE finascop_crm_lead SET crle_orgName = @name, crle_mode = @crmMode, crle_type = @contactType, crle_location = @location, crle_orgPincode = @pincode, crle_orgCountry = @country, crle_glocality = @locality, crle_gplace = @place, glatitude = @latitude, glongitude = @longitude, crle_orgAddress = @address, crle_indContactperson = @contactPerson, crle_indMobile = @mobileNumber, crle_orgContactNo = @contactNumber, retailCategory = @retailerCat, crle_orgEmail = @email, crle_isActive = @activeStatus WHERE id = @recordId";
            DataServiceMySql.ExecuteSql(updateQuery, UserService.GetAPIConnectionString(), parameters);
        }


        private void UpdateProspect(List<KeyValuePair<string, object>> parameters)
        {
             string updateQuery = @"UPDATE finascop_crm_prospect SET crpr_orgName = @name, crpr_mode = @crmMode, crpr_type = @contactType,crpr_location = @location, crpr_orgPincode = @pincode, crpr_orgCountry = @country, crpr_glocality = @locality, crpr_gplace = @place, glatitude = @latitude, glongitude = @longitude, crpr_orgAddress = @address, crpr_indContactperson = @contactPerson, crpr_indMobile = @mobileNumber, crpr_orgContactNo = @contactNumber, retailCategory = @retailerCat, crpr_orgEmail = @email, crpr_isActive = @activeStatus WHERE id = @recordId";
            DataServiceMySql.ExecuteSql(updateQuery, UserService.GetAPIConnectionString(), parameters);
        }

        private void ShowSuccess(string title, string content)
        {
            ltrErrorPopupTitle.Text = title;
            ltrErrorPopupText.Text = content;
            Type cstype = this.GetType();
            String csname1 = "PopupScript";
            ClientScriptManager cs = Page.ClientScript;
            ltrSuccessTitle.Text = title;
            ltrSuccessContent.Text = content;

            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append("<script type=text/javascript> $('#modaldemo4').modal('show'); </");
            cstext1.Append("script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }


        private void ClearInput()
        {
            txtAddr1.Text = "";
            txtAddr2.Text = "";
            txtPinCode.Text = "";
            hidLong.Value = "";
            hidLat.Value = "";
        }

    }
}


