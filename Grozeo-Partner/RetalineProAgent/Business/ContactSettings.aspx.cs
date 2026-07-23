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
    public partial class ContactSettings : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                rqdpincod.Enabled= ConfigurationManager.AppSettings["CountryCode"] != "AE";
                LoadStoreInfo();
            }

            ctrlAddressMap1.ParentLocationClientId = hidMapAddr.ClientID; // txtLocation.ClientID;
            ctrlAddressMap1.ParentLocationClientId = txtLocation.ClientID;
            ctrlAddressMap1.ParentLatClientId = hidLat.ClientID;
            ctrlAddressMap1.ParentLongClientId = hidLong.ClientID;
            //ctrlAddressMap1.ParentPinClientId = txtPinCode.ClientID;
            ctrlAddressMap1.ParentPinClientId = txtPinCode.ClientID;
            ctrlAddressMap1.ParentLocationNameClientId = txtAddr1.ClientID;
            //ctrlAddressMap1.ParentAddrClientId = txtAddr2.ClientID;
            //ctrlAddressMap1.ParentAddrClientId = txtAddr1.ClientID;
            ctrlAddressMap1.ParentStateClientId = hidState.ClientID;
            ctrlAddressMap1.ParentDistrictClientId = hidLocality.ClientID;
            ctrlAddressMap1.ParentPlaceClientId = hidState.ClientID;
            ctrlAddressMap1.ParentCountryClientId = hidCountry.ClientID;
            if (!String.IsNullOrEmpty(hidMapAddr.Value))
                txtLocation.Text = hidMapAddr.Value;
        }

        private void LoadStoreInfo()
        {
            int crmId = -1;
            if (!String.IsNullOrEmpty(Request.QueryString["id"]))
                try { crmId = Convert.ToInt32(Request.QueryString["id"]); } catch { crmId = 0; }
            List<KeyValuePair<string, object>> crmparams = new List<KeyValuePair<string, object>>();
            crmparams.Add(new KeyValuePair<string, object>("crmId", crmId));
            if (crmId > 0)
            {
                DataTable dt = DataServiceMySql.GetDataTable($"SELECT id, crco_orgName, crco_location, crco_type, crco_remarks, (SELECT NAME FROM crm_contact_type WHERE id=crco_type) AS contactType, crco_indContactperson, crco_orgPincode, crco_orgAddress, crco_indMobile, crco_orgContactNo, crco_orgEmail, (SELECT business_type_name FROM finascop_business_type WHERE business_type_id = retailCategory) AS businessType, (SELECT business_category_name FROM retaline_business_category WHERE business_category_id = retailCategory) AS businessCategory, retailCategory FROM finascop_crm_contact WHERE id = @crmId", Service.UserService.GetAPIConnectionString(), crmparams);
                if (dt == null || dt.Rows.Count <= 0)
                {
                    Common.ShowCustomAlert(this.Page, "Loading failed", "Invalid selection or the record is not existing", false, "/Business/ClientManagement?type=contact");
                    return;
                }

                DataRow da = dt.Rows[0];
                txtStoreName.Text = da["crco_orgName"].ToString();
                string strContactTypes = da["crco_type"].ToString();
                txtAddr1.Text = da["crco_location"].ToString();
                txtPinCode.Text = da["crco_orgPincode"].ToString();
                txtAddr2.Text = da["crco_orgAddress"].ToString();
                txtContactPerson.Text = da["crco_indContactperson"].ToString();
                txtMobile.Text = da["crco_indMobile"].ToString();
                ctrlAddressMap1.Lat = hidLat.Value;
                ctrlAddressMap1.Lng = hidLong.Value;
                txtTelephoneNumber.Text = da["crco_orgContactNo"].ToString();
                selBusinessCat.SelectedItem.Text = da["businessCategory"].ToString();
                txtEmail.Text = da["crco_orgEmail"].ToString();
                txtLocation.Text = da["crco_location"].ToString();
                txtRemarks.Text = da["crco_remarks"].ToString();
                if (lstContactType.Items.Count <= 0)
                    lstContactType.DataBind();

                foreach (string strctype in strContactTypes.Split(','))
                {
                    if (!String.IsNullOrEmpty(strctype) && lstContactType.Items.FindByValue(strctype.Trim()) != null)
                        lstContactType.Items.FindByValue(strctype.Trim()).Selected = true;
                    //lstBusinessTypes.SelectedValue = strbtype.Trim();
                }
            }
        }

        protected void Page_PreRender(object sender, EventArgs e)
        {
            if (selBusinessCat.Text == "-1")
            {
                otherBusinessCat.Visible = true;
            }
            else
            {
                otherBusinessCat.Visible = false;
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
                    int crmId = -1;
                    if (!String.IsNullOrEmpty(Request.QueryString["id"]))
                        crmId = Convert.ToInt32(Request.QueryString["id"]);
                    var user = this.CurrentUser;
                    List<KeyValuePair<string, object>> baparams = new List<KeyValuePair<string, object>>();
                    baparams.Add(new KeyValuePair<string, object>("email", user.Email));
                    DataTable baTable = DataServiceMySql.GetDataTable($"SELECT ba.id  FROM business_associate ba WHERE baEmail = @email", UserService.GetAPIConnectionString(), baparams);
                    string baId = "";
                    if (baTable != null && baTable.Rows.Count > 0)
                    {
                        DataRow dz = baTable.Rows[0];
                        baId = dz["id"].ToString();
                    }

                    List<KeyValuePair<string, object>> fcparams = new List<KeyValuePair<string, object>>();
                    fcparams.Add(new KeyValuePair<string, object>("crmId", crmId));
                    fcparams.Add(new KeyValuePair<string, object>("name", txtStoreName.Text));
                    fcparams.Add(new KeyValuePair<string, object>("crmMode", 2));
                    fcparams.Add(new KeyValuePair<string, object>("contactType", lstContactType.Text));
                    fcparams.Add(new KeyValuePair<string, object>("location", txtAddr1.Text));
                    fcparams.Add(new KeyValuePair<string, object>("pincode", txtPinCode.Text));
                    fcparams.Add(new KeyValuePair<string, object>("address", txtAddr2.Text));
                    fcparams.Add(new KeyValuePair<string, object>("latitude", hidLat.Value));
                    fcparams.Add(new KeyValuePair<string, object>("longitude", hidLong.Value));
                    fcparams.Add(new KeyValuePair<string, object>("contactPerson", txtContactPerson.Text));
                    fcparams.Add(new KeyValuePair<string, object>("country", hidCountry.Value));
                    fcparams.Add(new KeyValuePair<string, object>("locality", hidLocality.Value));
                    fcparams.Add(new KeyValuePair<string, object>("place", hidState.Value));
                    fcparams.Add(new KeyValuePair<string, object>("mobileNumber", primaryMobile));
                    fcparams.Add(new KeyValuePair<string, object>("contactNumber", secondaryMobile));
                    fcparams.Add(new KeyValuePair<string, object>("remarks", txtRemarks.Text));
                    //fcparams.Add(new KeyValuePair<string, object>("retailerCat", selBusinessCat.Text));
                    if (selBusinessCat.Text == "-1")
                    {
                        fcparams.Add(new KeyValuePair<string, object>("retailerCat", selBusinessCategory.Text));
                    }
                    else
                    {
                        fcparams.Add(new KeyValuePair<string, object>("retailerCat", selBusinessCat.Text));
                    }
                    fcparams.Add(new KeyValuePair<string, object>("isOthers", 1));
                    fcparams.Add(new KeyValuePair<string, object>("email", txtEmail.Text));
                    fcparams.Add(new KeyValuePair<string, object>("createdFrom", 2));
                    fcparams.Add(new KeyValuePair<string, object>("createdBy", baId));
                    fcparams.Add(new KeyValuePair<string, object>("activeStatus", 1));
                    fcparams.Add(new KeyValuePair<string, object>("crmStatus", 1));
                    if (crmId > 0)
                    {
                        if (selBusinessCat.Text == "-1")
                        {
                            string strSql2 = $"INSERT INTO finascop_crm_contact(crco_orgName, crco_mode, crco_type, crco_location, crco_orgPincode, crco_orgCountry, crco_glocality, crco_gplace, glatitude, glongitude, crco_orgAddress, crco_indContactperson, crco_indMobile, crco_orgContactNo, retailCategory, retailCategory_isOthers, crco_orgEmail, crco_CreatedFrom, crco_CreatedBy, crco_isActive, crmu_id, crco_remarks) " +
                            $"VALUES(@name, @crmMode, @contType, @location, @pincode, @country, @locality, @place, @latitude, @longitude, @address, @contactPerson, @mobileNumber, @contactNumber, @retailerCat, @isOthers, @email, @createdFrom, @createdBy, @activeStatus, @crmStatus, @remarks)";
                            var resultQuery2 = DataServiceMySql.ExecuteScalar(strSql2, UserService.GetAPIConnectionString(), fcparams);
                        }
                        else if (selBusinessCat.Text != "-1")
                        {
                            string strUpdateSql = $"UPDATE finascop_crm_contact SET crco_orgName=@name, crco_mode=@crmMode, crco_type=@contactType, crco_location=@location, " +
                                $"crco_orgPincode=@pincode, crco_orgCountry=@country, crco_glocality=@locality, crco_gplace=@place, glatitude=@latitude, glongitude=@longitude, crco_orgAddress=@address, crco_indContactperson=@contactPerson, " +
                                $"crco_indMobile=@mobileNumber, crco_orgContactNo=@contactNumber, retailCategory=@retailerCat, crco_orgEmail=@email , crco_CreatedFrom=@createdFrom , crco_CreatedBy=@createdBy, crco_isActive=@activeStatus, crco_remarks=@remarks WHERE id=@crmId";
                            DataServiceMySql.ExecuteSql(strUpdateSql, Service.UserService.GetAPIConnectionString(), fcparams);

                            int lastId = crmId;
                            string latitude = hidLat.Value;
                            string longitude = hidLong.Value;
                            var result = Core.Services.APIService.getAreaForLead(latitude, longitude);
                            if (result == null)
                            {

                            }

                            else if (result.id > 0 && result != null)
                            {

                                List<KeyValuePair<string, object>> areaparams = new List<KeyValuePair<string, object>>();
                                areaparams.Add(new KeyValuePair<string, object>("id", result.id));
                                areaparams.Add(new KeyValuePair<string, object>("businessAssociate", result.areaBusinessAssociate));
                                DataTable dtArea = DataServiceMySql.GetDataTable($"SELECT ro.id as roId,COUNT(fcl.id) AS fclCount FROM     relationship_officer AS ro LEFT JOIN finascop_crm_lead AS fcl ON fcl.assignedRO = ro.id WHERE              ro.roArea=@id ORDER BY fclCount ASC",
                                Service.UserService.GetAPIConnectionString(), areaparams);
                                DataRow da = dtArea.Rows[0];

                                DataTable dtBAName = DataServiceMySql.GetDataTable($"SELECT baName FROM business_associate WHERE ID =      @businessAssociate", Service.UserService.GetAPIConnectionString(), areaparams);
                                string baName = "";
                                if (dtBAName != null && dtBAName.Rows.Count > 0)
                                {
                                    DataRow db = dtBAName.Rows[0];
                                    baName = db["baName"].ToString();
                                }

                                if (Convert.ToInt32(da["roId"]) > 0)
                                {
                                    List<KeyValuePair<string, object>> leadparams = new List<KeyValuePair<string, object>>();
                                    //leadparams.Add(new KeyValuePair<string, object>("leadId", crmId));
                                    leadparams.Add(new KeyValuePair<string, object>("name", txtStoreName.Text));
                                    leadparams.Add(new KeyValuePair<string, object>("crmMode", 2));
                                    leadparams.Add(new KeyValuePair<string, object>("contaType", lstContactType.Text));
                                    leadparams.Add(new KeyValuePair<string, object>("location", txtAddr1.Text));
                                    leadparams.Add(new KeyValuePair<string, object>("pincode", txtPinCode.Text));
                                    leadparams.Add(new KeyValuePair<string, object>("address", txtAddr2.Text));
                                    leadparams.Add(new KeyValuePair<string, object>("latitude", hidLat.Value));
                                    leadparams.Add(new KeyValuePair<string, object>("longitude", hidLong.Value));
                                    leadparams.Add(new KeyValuePair<string, object>("contactPerson", txtContactPerson.Text));
                                    leadparams.Add(new KeyValuePair<string, object>("country", hidCountry.Value));
                                    leadparams.Add(new KeyValuePair<string, object>("locality", hidLocality.Value));
                                    leadparams.Add(new KeyValuePair<string, object>("route", hidLocality.Value));
                                    leadparams.Add(new KeyValuePair<string, object>("place", hidState.Value));
                                    leadparams.Add(new KeyValuePair<string, object>("mobileNumber", primaryMobile));
                                    leadparams.Add(new KeyValuePair<string, object>("contactNumber", secondaryMobile));
                                    leadparams.Add(new KeyValuePair<string, object>("retailerCat", selBusinessCat.Text));
                                    leadparams.Add(new KeyValuePair<string, object>("email", txtEmail.Text));
                                    leadparams.Add(new KeyValuePair<string, object>("createdFrom", 2));
                                    leadparams.Add(new KeyValuePair<string, object>("activeStatus", 1));
                                    //leadparams.Add(new KeyValuePair<string, object>("crmStatus", 2));
                                    leadparams.Add(new KeyValuePair<string, object>("assignedRO", da["roId"].ToString()));
                                    leadparams.Add(new KeyValuePair<string, object>("contactId", lastId));
                                    leadparams.Add(new KeyValuePair<string, object>("isLeadAreaAssigned", 1));
                                    leadparams.Add(new KeyValuePair<string, object>("baId", result.areaBusinessAssociate));
                                    leadparams.Add(new KeyValuePair<string, object>("baName", baName));
                                    leadparams.Add(new KeyValuePair<string, object>("areaId", result.id));
                                    leadparams.Add(new KeyValuePair<string, object>("areaName", result.areaName));
                                    leadparams.Add(new KeyValuePair<string, object>("createdBy", baId));
                                    leadparams.Add(new KeyValuePair<string, object>("leadStatus", 2));

                                    string leadInsert = $"INSERT INTO finascop_crm_lead(crle_orgName, crle_mode, crle_type, crle_location, crle_orgPincode, crle_orgCountry, crle_groute, crle_glocality, crle_gplace, glatitude, glongitude, crle_orgAddress, crle_indContactperson, crle_indMobile, crle_orgContactNo, retailCategory, crle_orgEmail, crle_CreatedFrom, crle_CreatedBy, crle_isActive, assignedRO, contactId, crmuId, isLeadAreaAssigned, baId, baName, areaId, areaName) " +
                                        $"VALUES(@name, @crmMode, @contaType, @location, @pincode, @country, @route, @locality, @place, @latitude, @longitude, @address, @contactPerson, @mobileNumber, @contactNumber, @retailerCat, @email, @createdFrom, @createdBy, @activeStatus, @assignedRO, @contactId, @leadStatus, @isLeadAreaAssigned, @baId, @baName, @areaId, @areaName)";
                                    DataServiceMySql.ExecuteSql(leadInsert, UserService.GetAPIConnectionString(), leadparams);

                                    string strUpdateSql1 = $"UPDATE finascop_crm_contact SET crmu_id=2 WHERE id=@contactId";
                                    DataServiceMySql.ExecuteSql(strUpdateSql1, Service.UserService.GetAPIConnectionString(), leadparams);
                                }
                            }
                        }
                        ShowSuccess("Success", "Contact updated successfully!!", "/Business/ClientManagement?type=contact");
                    }
                    else
                    {
                        string selectedItems = null;
                        foreach (ListItem item in lstContactType.Items)
                        {
                            if (item.Selected)
                            {
                                selectedItems = item.Value;
                            }
                            if (item.Selected && selectedItems != null)
                            {
                                List<KeyValuePair<string, object>> ccparams = new List<KeyValuePair<string, object>>();
                                ccparams.Add(new KeyValuePair<string, object>("crmId", crmId));
                                ccparams.Add(new KeyValuePair<string, object>("name", txtStoreName.Text));
                                ccparams.Add(new KeyValuePair<string, object>("crmMode", 2));
                                ccparams.Add(new KeyValuePair<string, object>("contType", selectedItems));
                                ccparams.Add(new KeyValuePair<string, object>("location", txtAddr1.Text));
                                ccparams.Add(new KeyValuePair<string, object>("pincode", txtPinCode.Text));
                                ccparams.Add(new KeyValuePair<string, object>("address", txtAddr2.Text));
                                ccparams.Add(new KeyValuePair<string, object>("latitude", hidLat.Value));
                                ccparams.Add(new KeyValuePair<string, object>("longitude", hidLong.Value));
                                ccparams.Add(new KeyValuePair<string, object>("contactPerson", txtContactPerson.Text));
                                ccparams.Add(new KeyValuePair<string, object>("country", hidCountry.Value));
                                ccparams.Add(new KeyValuePair<string, object>("locality", hidLocality.Value));
                                ccparams.Add(new KeyValuePair<string, object>("place", hidState.Value));
                                ccparams.Add(new KeyValuePair<string, object>("mobileNumber", primaryMobile));
                                ccparams.Add(new KeyValuePair<string, object>("remarks", txtRemarks.Text));
                                ccparams.Add(new KeyValuePair<string, object>("contactNumber", secondaryMobile));
                                //ccparams.Add(new KeyValuePair<string, object>("retailerCat", selBusinessCat.Text));
                                if (selBusinessCat.Text == "-1")
                                {
                                    ccparams.Add(new KeyValuePair<string, object>("retailerCat", selBusinessCategory.Text));
                                }
                                else
                                {
                                    ccparams.Add(new KeyValuePair<string, object>("retailerCat", selBusinessCat.Text));
                                }
                                ccparams.Add(new KeyValuePair<string, object>("isOthers", 1));
                                ccparams.Add(new KeyValuePair<string, object>("email", txtEmail.Text));
                                ccparams.Add(new KeyValuePair<string, object>("createdFrom", 2));
                                ccparams.Add(new KeyValuePair<string, object>("createdBy", baId));
                                ccparams.Add(new KeyValuePair<string, object>("activeStatus", 1));
                                ccparams.Add(new KeyValuePair<string, object>("crmStatus", 1));

                                int lastId = crmId;
                                DataTable dt = DataServiceMySql.GetDataTable($"SELECT COUNT(*) AS cnt FROM finascop_crm_contact WHERE crco_indMobile=@mobileNumber and " +
                                  $"crco_type = @contType", Service.UserService.GetAPIConnectionString(), ccparams);
                                if (dt != null && dt.Rows.Count > 0)
                                {
                                    DataRow dr = dt.Rows[0];
                                    if (Convert.ToInt32(dr["cnt"]) > 0)
                                    {
                                    //Common.ShowCustomAlert(this.Page, "Loading failed", "Invalid selection or the record is not existing", false, "/Business/ClientManagement?type=contact");
                                    //return;
                                    Common.ShowToastifyMessage(this.Page, "Duplicate mobile number", "danger");
                                        return;
                                    }
                                }

                                if (selBusinessCat.Text == "-1")
                                {
                                    string strSql1 = $"INSERT INTO finascop_crm_contact(crco_orgName, crco_mode, crco_type, crco_location, crco_orgPincode, crco_orgCountry, crco_glocality, crco_gplace, glatitude, glongitude, crco_orgAddress, crco_indContactperson, crco_indMobile, crco_orgContactNo, retailCategory, retailCategory_isOthers, crco_orgEmail, crco_CreatedFrom, crco_CreatedBy, crco_isActive, crmu_id, crco_remarks) " +
                                    $"VALUES(@name, @crmMode, @contType, @location, @pincode, @country, @locality, @place, @latitude, @longitude, @address, @contactPerson, @mobileNumber, @contactNumber, @retailerCat, @isOthers, @email, @createdFrom, @createdBy, @activeStatus, @crmStatus, @remarks)";
                                    var resultQuery1 = DataServiceMySql.ExecuteScalar(strSql1, UserService.GetAPIConnectionString(), ccparams);
                                }
                                else if (selBusinessCat.Text != "-1")
                                {
                                    string strSql = $"INSERT INTO finascop_crm_contact(crco_orgName, crco_mode, crco_type, crco_location, crco_orgPincode, crco_orgCountry, crco_glocality, crco_gplace, glatitude, glongitude, crco_orgAddress, crco_indContactperson, crco_indMobile, crco_orgContactNo, retailCategory, retailCategory_isOthers, crco_orgEmail, crco_CreatedFrom, crco_CreatedBy, crco_isActive, crmu_id, crco_remarks) " +
                                    $"VALUES(@name, @crmMode, @contType, @location, @pincode, @country, @locality, @place, @latitude, @longitude, @address, @contactPerson, @mobileNumber, @contactNumber, @retailerCat, 0, @email, @createdFrom, @createdBy, @activeStatus, @crmStatus, @remarks); select LAST_INSERT_ID()";
                                    var resultQuery = DataServiceMySql.ExecuteScalar(strSql, UserService.GetAPIConnectionString(), ccparams);
                                    int contactLastId = Convert.ToInt32(resultQuery);
                                    string latitude = hidLat.Value;
                                    string longitude = hidLong.Value;
                                    var result = Core.Services.APIService.getAreaForLead(latitude, longitude);
                                    if (result == null)
                                    {

                                    }

                                    else if (result.id > 0 && result != null)
                                    {

                                        List<KeyValuePair<string, object>> areaparams = new List<KeyValuePair<string, object>>();
                                        areaparams.Add(new KeyValuePair<string, object>("id", result.id));
                                        areaparams.Add(new KeyValuePair<string, object>("businessAssociate", result.areaBusinessAssociate));
                                        DataTable dtArea = DataServiceMySql.GetDataTable($"SELECT ro.id as roId,COUNT(fcl.id) AS fclCount FROM     relationship_officer AS ro LEFT JOIN finascop_crm_lead AS fcl ON fcl.assignedRO = ro.id WHERE              ro.roArea=@id ORDER BY fclCount ASC",
                                        Service.UserService.GetAPIConnectionString(), areaparams);
                                        DataRow da = dtArea.Rows[0];

                                        DataTable dtBAName = DataServiceMySql.GetDataTable($"SELECT baName FROM business_associate WHERE ID =      @businessAssociate", Service.UserService.GetAPIConnectionString(), areaparams);
                                        string baName = "";
                                        if (dtBAName != null && dtBAName.Rows.Count > 0)
                                        {
                                            DataRow db = dtBAName.Rows[0];
                                            baName = db["baName"].ToString();
                                        }

                                        if (Convert.ToInt32(da["roId"]) > 0)
                                        {
                                            List<KeyValuePair<string, object>> leadparams = new List<KeyValuePair<string, object>>();
                                            //leadparams.Add(new KeyValuePair<string, object>("leadId", crmId));
                                            leadparams.Add(new KeyValuePair<string, object>("name", txtStoreName.Text));
                                            leadparams.Add(new KeyValuePair<string, object>("crmMode", 2));
                                            leadparams.Add(new KeyValuePair<string, object>("contaType", selectedItems));
                                            leadparams.Add(new KeyValuePair<string, object>("location", txtAddr1.Text));
                                            leadparams.Add(new KeyValuePair<string, object>("pincode", txtPinCode.Text));
                                            leadparams.Add(new KeyValuePair<string, object>("address", txtAddr2.Text));
                                            leadparams.Add(new KeyValuePair<string, object>("latitude", hidLat.Value));
                                            leadparams.Add(new KeyValuePair<string, object>("longitude", hidLong.Value));
                                            leadparams.Add(new KeyValuePair<string, object>("contactPerson", txtContactPerson.Text));
                                            leadparams.Add(new KeyValuePair<string, object>("country", hidCountry.Value));
                                            leadparams.Add(new KeyValuePair<string, object>("locality", hidLocality.Value));
                                            leadparams.Add(new KeyValuePair<string, object>("route", hidLocality.Value));
                                            leadparams.Add(new KeyValuePair<string, object>("place", hidState.Value));
                                            leadparams.Add(new KeyValuePair<string, object>("mobileNumber", primaryMobile));
                                            leadparams.Add(new KeyValuePair<string, object>("contactNumber", secondaryMobile));
                                            leadparams.Add(new KeyValuePair<string, object>("retailerCat", selBusinessCat.Text));
                                            leadparams.Add(new KeyValuePair<string, object>("email", txtEmail.Text));
                                            leadparams.Add(new KeyValuePair<string, object>("createdFrom", 2));
                                            leadparams.Add(new KeyValuePair<string, object>("activeStatus", 1));
                                            //leadparams.Add(new KeyValuePair<string, object>("crmStatus", 2));
                                            leadparams.Add(new KeyValuePair<string, object>("assignedRO", da["roId"].ToString()));
                                            leadparams.Add(new KeyValuePair<string, object>("contactId", contactLastId));
                                            leadparams.Add(new KeyValuePair<string, object>("isLeadAreaAssigned", 1));
                                            leadparams.Add(new KeyValuePair<string, object>("baId", result.areaBusinessAssociate));
                                            leadparams.Add(new KeyValuePair<string, object>("baName", baName));
                                            leadparams.Add(new KeyValuePair<string, object>("areaId", result.id));
                                            leadparams.Add(new KeyValuePair<string, object>("areaName", result.areaName));
                                            leadparams.Add(new KeyValuePair<string, object>("createdBy", baId));
                                            leadparams.Add(new KeyValuePair<string, object>("leadStatus", 2));

                                            string leadInsert = $"INSERT INTO finascop_crm_lead(crle_orgName, crle_mode, crle_type, crle_location, crle_orgPincode, crle_orgCountry, crle_groute, crle_glocality, crle_gplace, glatitude, glongitude, crle_orgAddress, crle_indContactperson, crle_indMobile, crle_orgContactNo, retailCategory, crle_orgEmail, crle_CreatedFrom, crle_CreatedBy, crle_isActive, assignedRO, contactId, crmuId, isLeadAreaAssigned, baId, baName, areaId, areaName) " +
                                                    $"VALUES(@name, @crmMode, @contaType, @location, @pincode, @country, @route, @locality, @place, @latitude, @longitude, @address, @contactPerson, @mobileNumber, @contactNumber, @retailerCat, @email, @createdFrom, @createdBy, @activeStatus, @assignedRO, @contactId, @leadStatus, @isLeadAreaAssigned, @baId, @baName, @areaId, @areaName)";
                                            DataServiceMySql.ExecuteSql(leadInsert, UserService.GetAPIConnectionString(), leadparams);

                                            string strUpdateSql = $"UPDATE finascop_crm_contact SET crmu_id=2 WHERE id=@contactId";
                                            DataServiceMySql.ExecuteSql(strUpdateSql, Service.UserService.GetAPIConnectionString(), leadparams);
                                        }
                                    }
                                }
                                //}
                            }
                        }

                        ShowSuccess("Success!", "Contact created successfully!!", "/Business/ClientManagement?type=contact");
                    }
                }
                catch
                {
                    Common.ShowToastifyMessage(this.Page, "Execution failed" + "Execution failure No item matches the criteria. Please contact support for more details.", "danger");
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

        private void ClearInput()
        {
            txtAddr1.Text = "";
            //txtAddr2.Text = "";
            txtPinCode.Text = "";
            hidLong.Value = "";
            hidLat.Value = "";
        }
    }
}


