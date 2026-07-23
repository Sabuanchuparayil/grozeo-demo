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
    public partial class WLeadSettings: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            txtLeadType.Text = "Wholesale merchant";
            if (!IsPostBack)
            {
                if (ConfigurationManager.AppSettings["CountryCode"] == "AE")
                {
                    txtPinCode.Attributes.Remove("required");
                }
                else
                {
                    txtPinCode.Attributes.Add("required", "required");
                }
                LoadStoreInfo();
            }

            ctrlAddressMap1.ParentLocationClientId = hidMapAddr.ClientID; // txtLocation.ClientID;
            ctrlAddressMap1.ParentLatClientId = hidLat.ClientID;
            ctrlAddressMap1.ParentLongClientId = hidLong.ClientID;
            ctrlAddressMap1.ParentPinClientId = txtPinCode.ClientID;
            ctrlAddressMap1.ParentLocationNameClientId = txtAddr1.ClientID;
            ctrlAddressMap1.ParentAddrClientId = txtAddr2.ClientID;
            ctrlAddressMap1.ParentStateClientId = hidState.ClientID;
            ctrlAddressMap1.ParentDistrictClientId = hidLocality.ClientID;
            ctrlAddressMap1.ParentPlaceClientId = hidState.ClientID;
            ctrlAddressMap1.ParentCountryClientId = hidCountry.ClientID;
            if (!String.IsNullOrEmpty(hidMapAddr.Value))
                txtLocation.Text = hidMapAddr.Value;
        }

        private void LoadStoreInfo()
        {
            int rleadId = -1;
            if (!String.IsNullOrEmpty(Request.QueryString["id"]))
                try { rleadId = Convert.ToInt32(Request.QueryString["id"]); } catch { rleadId = 0; }
            List<KeyValuePair<string, object>> crmparams = new List<KeyValuePair<string, object>>();
            crmparams.Add(new KeyValuePair<string, object>("rleadId", rleadId));
            if (rleadId > 0)
            {
                DataTable dt = DataServiceMySql.GetDataTable($"SELECT id, crle_orgName, crle_mode, crle_type, (SELECT name FROM crm_contact_type ct WHERE ct.id=crle_type) AS contactType, crle_location, crle_orgPincode, crle_orgCountry, crle_glocality, (SELECT business_type_name FROM finascop_business_type WHERE business_type_id = retailCategory) AS businessType, " +
                    $"crle_gplace, glatitude, glongitude, crle_orgAddress, crle_indContactperson, crle_indMobile, crle_orgContactNo, retailCategory, crle_orgEmail, crle_CreatedFrom, crle_isActive FROM finascop_crm_lead WHERE id = @rleadId", Service.UserService.GetAPIConnectionString(), crmparams);
                if (dt == null || dt.Rows.Count <= 0)
                {
                    Common.ShowCustomAlert(this.Page, "Loading failed", "Invalid selection or the record is not existing", false, "/Business/RetailerLeads");
                    return;
                }

                DataRow da = dt.Rows[0];
                txtStoreName.Text = da["crle_orgName"].ToString();
                txtLeadType.Text = da["contactType"].ToString();
                txtAddr1.Text = da["crle_location"].ToString();
                txtPinCode.Text = da["crle_orgPincode"].ToString();
                txtAddr2.Text = da["crle_orgAddress"].ToString();
                txtContactPerson.Text = da["crle_indContactperson"].ToString();
                txtMobile.Text = da["crle_indMobile"].ToString();
                ctrlAddressMap1.Lat = hidLat.Value;
                ctrlAddressMap1.Lng = hidLong.Value;
                txtTelephoneNumber.Text = da["crle_orgContactNo"].ToString();
                selRetailCategory.SelectedItem.Text = da["businessType"].ToString();
                txtEmail.Text = da["crle_orgEmail"].ToString();
                txtLocation.Text = da["crle_location"].ToString();
                //if (lstContactType.Items.Count <= 0)
                //    lstContactType.DataBind();

                //foreach (string strctype in strContactTypes.Split(','))
                //{
                //    if (!String.IsNullOrEmpty(strctype) && lstContactType.Items.FindByValue(strctype.Trim()) != null)
                //        lstContactType.Items.FindByValue(strctype.Trim()).Selected = true;
                //    //lstBusinessTypes.SelectedValue = strbtype.Trim();
                //}
            }
        }

        //protected void lstContactType_DataBound(object sender, EventArgs e)
        //{
        //    if (lstContactType.Items.Count > 0)
        //    {
        //        string strKey = lstContactType.Attributes["DefaultBType"];
        //        if (!String.IsNullOrEmpty(strKey))
        //        {
        //            string[] strbtypes = strKey.Trim().Split(',');
        //            if (strbtypes.Length > 0)
        //            {
        //                foreach (string btype in strbtypes)
        //                    if (!String.IsNullOrEmpty(btype.Trim()) && lstContactType.Items.FindByText(btype.Trim()) != null)
        //                        lstContactType.Items.FindByText(btype.Trim()).Selected = true;
        //            }
        //        }
        //    }
        //}

        protected async void btnContactSubmit_Click(object sender, EventArgs e)
        {
            string primaryMobile = txtMobile.Text;
            string secondaryMobile = txtTelephoneNumber.Text;
            if (secondaryMobile == primaryMobile)
            {
                Common.ShowToastifyMessage(this.Page, "Duplicate phone number.", "danger");
            }
            else
            {
                try
                {
                    int rleadId = -1;
                    if (!String.IsNullOrEmpty(Request.QueryString["id"]))
                        rleadId = Convert.ToInt32(Request.QueryString["id"]);

                    //string selectedItems = null;
                    //foreach (ListItem item in lstContactType.Items)
                    //{
                    //    if (item.Selected)
                    //    {
                    //        selectedItems += item.Value + ",";
                    //    }

                    //}

                    //selectedItems = selectedItems.Remove(selectedItems.Length - 1);

                    List<KeyValuePair<string, object>> fcparams = new List<KeyValuePair<string, object>>();
                    fcparams.Add(new KeyValuePair<string, object>("rleadId", rleadId));
                    fcparams.Add(new KeyValuePair<string, object>("name", txtStoreName.Text));
                    fcparams.Add(new KeyValuePair<string, object>("crmMode", 2));
                    fcparams.Add(new KeyValuePair<string, object>("contactType", 3));
                    fcparams.Add(new KeyValuePair<string, object>("location", txtAddr2.Text));
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
                    fcparams.Add(new KeyValuePair<string, object>("retailerCat", selRetailCategory.Text));
                    fcparams.Add(new KeyValuePair<string, object>("email", txtEmail.Text));
                    fcparams.Add(new KeyValuePair<string, object>("createdFrom", 2));
                    fcparams.Add(new KeyValuePair<string, object>("activeStatus", 1));

                    if (rleadId <= 0)
                    {
                        string strSql = $"INSERT INTO finascop_crm_lead(crle_orgName, crle_mode, crle_type, crle_location, crle_orgPincode, crle_orgCountry, crle_glocality, crle_gplace, glatitude, glongitude, crle_orgAddress, crle_indContactperson, crle_indMobile, crle_orgContactNo, retailCategory, crle_orgEmail, crle_CreatedFrom, crle_isActive) " +
                            $"VALUES(@name, @crmMode, @contactType, @location, @pincode, @country, @locality, @place, @latitude, @longitude, @address, @contactPerson, @mobileNumber, @contactNumber, @retailerCat, @email, @createdFrom, @activeStatus)";
                        DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), fcparams);

                        ShowSuccess("Success!", "Contact created successfully!!", "/Business/WholesalerLeads");
                    }
                    else
                    {
                        string strUpdateSql = $"UPDATE finascop_crm_lead SET crle_orgName=@name, crle_mode=@crmMode, crle_type=@contactType, crle_location=@location, " +
                            $"crle_orgPincode=@pincode, crle_orgCountry=@country, crle_glocality=@locality, crle_gplace=@place, glatitude=@latitude, glongitude=@longitude, crle_orgAddress=@address, crle_indContactperson=@contactPerson, " +
                            $"crle_indMobile=@mobileNumber, crle_orgContactNo=@contactNumber, retailCategory=@retailerCat, crle_orgEmail=@email , crle_CreatedFrom=@createdFrom , crle_isActive=@activeStatus WHERE id=@rleadId";
                        DataServiceMySql.ExecuteSql(strUpdateSql, Service.UserService.GetAPIConnectionString(), fcparams);
                        ShowSuccess("Success", "Contact updated successfully!!", "/Business/WholesalerLeads");
                    }
                }
                catch
                {
                    Common.ShowToastifyMessage(this.Page, "Execution failed" + "Execution failure No item matches the criteria. Please contact support for more details.", "danger");
                }
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
            txtAddr2.Text = "";
            txtPinCode.Text = "";
            hidLong.Value = "";
            hidLat.Value = "";
        }

    }
}


