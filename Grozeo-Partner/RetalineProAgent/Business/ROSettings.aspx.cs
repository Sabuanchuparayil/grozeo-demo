using Finascop.Services;
using log4net.Core;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.IO;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.HtmlControls;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class ROSettings: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                reqpostcode.Enabled=rfvPassport.Enabled= rfvPAN.Enabled= ConfigurationManager.AppSettings["CountryCode"] != "AE";
                LoadStoreInfo();

            }
        }
        public string GetPlaceholderText(string labelText)
        {
            return $"Enter {labelText}";
        }
        public string GeterrorMessage(string label)
        {
            return $"{label} is required";
        }
        private void LoadStoreInfo()
        {
            string adhar = (ConfigurationManager.AppSettings.Get("CountryCode") == "UK" ? "NI" :(ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "Aadhaar" : (ConfigurationManager.AppSettings.Get("CountryCode") == "AE" ? "EmiratesID" : "")));
            txtAadhar.Attributes["placeholder"] = GetPlaceholderText(adhar);
            rfvAadhar.ErrorMessage= GeterrorMessage(adhar);
            string PAN =  (ConfigurationManager.AppSettings.Get("CountryCode") == "UK" ? "Share Code" : (ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "PAN" : ""));
            txtPAN.Attributes["placeholder"]= GetPlaceholderText(PAN);
            rfvPAN.ErrorMessage= GeterrorMessage(PAN);
            string IFSC= (ConfigurationManager.AppSettings.Get("CountryCode") == "UK" ? "Sort Code" : (ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "IFSC code" : (ConfigurationManager.AppSettings.Get("CountryCode") == "AE" ? "IBAN" : "")));
            txtIFSC.Attributes["placeholder"] = GetPlaceholderText(IFSC); 
            rfvCode.ErrorMessage = GeterrorMessage(IFSC); 
            string UPI = (ConfigurationManager.AppSettings.Get("CountryCode") == "UK" ? "Passport/ID Number" : (ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "UPI ID is required" : ""));
            txtUPI.Attributes["placeholder"] = GetPlaceholderText(UPI); ;
            rfvPassport.ErrorMessage = GeterrorMessage(UPI);
        }

        protected void selState_DataBound(object sender, EventArgs e)
        {
            if (selState.Items.Count > 0)
            {
                string strKey = selState.Attributes["DefaultState"];
                if (!String.IsNullOrEmpty(strKey) && selState.Items.FindByText(strKey) != null)
                    selState.Text = (selState.Items.FindByText(strKey).Value);
            }
            selState.Items.Insert(0, new ListItem($"Select {RetalineProAgent.Service.Common.StateLabel}", ""));

        }
        protected void selDistrict_DataBound(object sender, EventArgs e)
        {
            if (selDistrict.Items.Count > 0)
            {
                string strKey = selDistrict.Attributes["DefaultDistrict"];
                if (!String.IsNullOrEmpty(strKey) && selDistrict.Items.FindByText(strKey) != null)
                    selDistrict.Text = (selDistrict.Items.FindByText(strKey).Value);
            }          
            //selDistrict.Items.Insert(0, new ListItem("Select District", ""));
            if (selDistrict.Items.Count > 1 && !String.IsNullOrEmpty(hidDistrict.Value) && selDistrict.Text != hidDistrict.Value && selDistrict.Items.FindByText(hidDistrict.Value) != null)
                selDistrict.SelectedValue = selDistrict.Items.FindByText(hidDistrict.Value).Value; //selState.Items.FindByText(strState).Value;
            selDistrict.Items.Remove(selDistrict.Items.FindByText($"Select {RetalineProAgent.Service.Common.DistrictLabel}"));
            selDistrict.Items.Insert(0, new ListItem($"Select {RetalineProAgent.Service.Common.DistrictLabel}", ""));
        }



        protected async void btnROSubmit_Click(object sender, EventArgs e)
        {
                string primaryMobile = (ConfigurationManager.AppSettings.Get("PhoneCountryCode"))+ txtMobile.Text;
                string secondaryMobile = (ConfigurationManager.AppSettings.Get("PhoneCountryCode")) + txtTelephoneNumber.Text;
                if (secondaryMobile == primaryMobile)
                {
                    Common.ShowToastifyMessage(this.Page, "Duplicate phone number.", "danger");
                }

                else
                {
                    try
                    {
                    string photoUrl = UploadROFile(filePhoto, "relationshipofficer/photo/");
                    string cvUrl = UploadROFile(fileCV, "relationshipofficer/cv/");

                    int roId = -1;
                        if (!String.IsNullOrEmpty(Request.QueryString["id"]))
                            roId = Convert.ToInt32(Request.QueryString["id"]);
                        var user = this.CurrentUser;
                        List<KeyValuePair<string, object>> baparams = new List<KeyValuePair<string, object>>();
                        baparams.Add(new KeyValuePair<string, object>("AreaId", this.CurrentUser.AreaId));
                        //baparams.Add(new KeyValuePair<string, object>("password", user.Password));
                        DataTable result = DataServiceMySql.GetDataTable($"SELECT  areaBusinessAssociate FROM `area_entries` WHERE id=@AreaId", UserService.GetAPIConnectionString(), baparams);
                        if (result != null && result.Rows.Count > 0)
                        {
                            DataRow dr = result.Rows[0];

                            List<KeyValuePair<string, object>> roparams = new List<KeyValuePair<string, object>>();
                            roparams.Add(new KeyValuePair<string, object>("roId", roId));
                            roparams.Add(new KeyValuePair<string, object>("type", 1));
                            roparams.Add(new KeyValuePair<string, object>("name", txtName.Text));
                            roparams.Add(new KeyValuePair<string, object>("address", txtAddress.Text));
                            roparams.Add(new KeyValuePair<string, object>("state", selState.Text));
                            roparams.Add(new KeyValuePair<string, object>("district", selDistrict.Text));
                            roparams.Add(new KeyValuePair<string, object>("postCode", txtPostCode.Text));
                            roparams.Add(new KeyValuePair<string, object>("email", txtEmail.Text));
                            roparams.Add(new KeyValuePair<string, object>("mobile", primaryMobile));
                            roparams.Add(new KeyValuePair<string, object>("contactPerson", txtContactPerson.Text));
                            roparams.Add(new KeyValuePair<string, object>("contactNumber", secondaryMobile));
                            roparams.Add(new KeyValuePair<string, object>("qualification", selQualification.Text));
                            roparams.Add(new KeyValuePair<string, object>("experience", txtExp.Text));
                            roparams.Add(new KeyValuePair<string, object>("bloodGroup", txtBloodGrp.Text));
                            roparams.Add(new KeyValuePair<string, object>("license", txtLicense.Text));
                            roparams.Add(new KeyValuePair<string, object>("aadharNumber", txtAadhar.Text));
                            roparams.Add(new KeyValuePair<string, object>("pan", txtPAN.Text));
                            roparams.Add(new KeyValuePair<string, object>("banckAct", txtAccount.Text));
                            roparams.Add(new KeyValuePair<string, object>("upiId", txtUPI.Text));
                            roparams.Add(new KeyValuePair<string, object>("businessAssociate", dr["areaBusinessAssociate"].ToString()));
                            roparams.Add(new KeyValuePair<string, object>("area", this.CurrentUser.AreaId));
                            roparams.Add(new KeyValuePair<string, object>("photoUrl", photoUrl));
                            roparams.Add(new KeyValuePair<string, object>("cvUrl", cvUrl));

                        if (roId <= 0)
                            {
                                string strSql = $"INSERT INTO relationship_officer(type, roName, roAddress, rost_id, rodst_Id, roPincode, roMobile, " +
                                    $"roContactPerson, roContactMobile, roQualification, roExperience, roBloodGroup, roLicenceNo, roAadhaar, roPanNo, " +
                                    $"roBankAccount, roUPI, roBusAssociate, roArea, imagePath, cvLink) " +
                                    $"VALUES(@type, @name, @address, @state, @district, @postCode, @mobile, @contactPerson, @contactNumber, @qualification, " +
                                    $"@experience, @bloodGroup, @license, @aadharNumber, @pan, @banckAct, @upiId, @businessAssociate, @area, @photoUrl, @cvUrl)";
                                DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), roparams);

                                ShowSuccess("Success!", "Relationship officer created successfully!!", "/Business/RelationshipOfficer");
                            }
                            else
                            {
                                string strUpdateSql = $"UPDATE relationship_officer SET roName=@name, roAddress=@address, rost_id=@state, rodst_Id=@district, " +
                                    $"roPincode=@postCode, roMobile=@mobile, roContactPerson=@contactPerson, roContactMobile=@contactNumber, roQualification=@qualification, " +
                                    $"roExperience=@experience, roExperience=@bloodGroup, roLicenceNo=@license, roAadhaar=@aadharNumber, roPanNo=@pan, roBankAccount=@banckAct, " +
                                    $"roUPI=@upiId, roBusAssociate=@businessAssociate, roArea=@area, imagePath=@photoUrl, cvLink=@cvUrl WHERE id=@roId";
                                DataServiceMySql.ExecuteSql(strUpdateSql, Service.UserService.GetAPIConnectionString(), roparams);
                                ShowSuccess("Success", "Contact updated successfully!!", "/Business/RelationshipOfficer");
                            }
                        }
                        else
                        {
                            Common.ShowCustomAlert(this.Page, "Failure!", "RO cannot be created because of associated area is invalid or not active", false, "/Business/RelationshipOfficer");
                        }
                    }
                    catch
                    {
                        Common.ShowToastifyMessage(this.Page, "Execution failed" + "Execution failure No item matches the criteria. Please contact support for more details.", "danger");
                    }

                }
        }

        public Control FindControlRecursive(Control root, string id)
        {
            if (root == null)
            {
                return null;
            }

            Control foundControl = root.FindControl(id);
            if (foundControl != null)
            {
                return foundControl;
            }

            foreach (Control control in root.Controls)
            {
                foundControl = FindControlRecursive(control, id);
                if (foundControl != null)
                {
                    return foundControl;
                }
            }

            return null;
        }
        private string UploadROFile(HtmlInputFile fileUpload, string filePath)
        {
            string uploadedFilePath = string.Empty;
            if (fileUpload != null && fileUpload.PostedFile != null && fileUpload.PostedFile.ContentLength > 0)
            {
                try
                {
                    string fileName = Path.GetFileName(fileUpload.PostedFile.FileName);
                    string fileExtension = Path.GetExtension(fileName);
                    string contentType = fileUpload.PostedFile.ContentType;
                    using (var fileStream = fileUpload.PostedFile.InputStream)
                    {
                        uploadedFilePath = FileService.UploadROPhotoCV(fileStream, fileName, filePath);
                    }
                }
                catch (Exception ex)
                {
                    Common.ShowToastifyMessage(this.Page, "File upload failed: " + ex.Message, "danger");
                }
            }
            else
            {
                Common.ShowToastifyMessage(this.Page, "File is not selected.", "danger");
            }
            return uploadedFilePath;
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
    }
}


