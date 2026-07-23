using Finascop.Services;
using Org.BouncyCastle.Asn1.Cms;
using RetalineProAgent.Core.BussinessModel.Store;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Runtime.CompilerServices;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using static QRCoder.PayloadGenerator;

namespace RetalineProAgent
{
    public partial class AMSettings: Base.BasePartnerPage
    {
     
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                reqpostcode.Enabled = ConfigurationManager.AppSettings["CountryCode"] != "AE";
                LoadStoreInfo();
            }           
            // Call the function 
            SetPlaceholdersAndAttributes(ConfigurationManager.AppSettings["CountryCode"]);
        }
        public (string MobileMaxLength, string AadhaarPlaceholder, string UPIPlaceholder, string PANPlaceholder) GetAttributes(string countryCode)
        {
            switch (countryCode)
            {
                case "IN":
                    return ("10", "Enter Aadhaar number", "Enter UPI ID", "Enter PAN number");
                case "UK":
                    return ("11", "Enter Passport/ID number", "Enter Sort Code", "Enter NI number");
                case "AE":
                    return ("9", "Enter EmiratesID", "", "");
                default:
                    return ("", "", "", "");
            }
        }

        public void SetPlaceholdersAndAttributes(string countryCode)
        {
            var attributes = GetAttributes(countryCode);

            SetAttributes(attributes.MobileMaxLength, attributes.AadhaarPlaceholder, attributes.UPIPlaceholder, attributes.PANPlaceholder);
        }

        private void SetAttributes(string mobileMaxLength, string aadharPlaceholder, string upiPlaceholder, string panPlaceholder)
        {
            txtMobile.Attributes.Add("maxlength", mobileMaxLength);
            txtAadhar.Attributes.Add("placeholder", aadharPlaceholder);
            txtUPI.Attributes.Add("placeholder", upiPlaceholder);
            txtPAN.Attributes.Add("placeholder", panPlaceholder);
        }

        private void LoadStoreInfo()
        {
            int RoId = -1;
            if (!String.IsNullOrEmpty(Request.QueryString["Id"]))
                try { RoId = Convert.ToInt32(Request.QueryString["Id"]); } catch { RoId = 0; }
            List<KeyValuePair<string, object>> crmparams = new List<KeyValuePair<string, object>>();
            crmparams.Add(new KeyValuePair<string, object>("Id", RoId));
            if (RoId > 0)
            {
                DataTable dt = DataServiceMySql.GetDataTable($"SELECT Id,roName,roAddress,rost_id,rodst_Id,roPincode,roEmailId,roMobile,roContactPerson,roContactMobile,roQualification,roExperience,roBloodGroup,roLicenceNo,roAadhaar,roPanNo,roBankAccount,roUPI FROM relationship_officer WHERE type=2 AND Id=@Id", Service.UserService.GetAPIConnectionString(), crmparams);
                if (dt == null || dt.Rows.Count <= 0)
                {
                    Common.ShowCustomAlert(this.Page, "Loading failed", "Invalid selection or the record is not existing", false, "/Business/AMSettings");
                    return;
                }

                DataRow da = dt.Rows[0];
                txtName.Text = da["roName"].ToString();
                txtAddress.Text = da["roAddress"].ToString();
                selState.SelectedValue = da["rost_id"].ToString();
                selDistrict.SelectedValue = da["rodst_Id"].ToString();
                txtPostCode.Text = da["roPincode"].ToString();
                txtEmail.Text = da["roEmailId"].ToString();
               string phonenumber = da["roMobile"].ToString();
                string getphone = phonenumber.Replace((ConfigurationManager.AppSettings.Get("PhoneCountryCode")), "");
                txtMobile.Text= getphone;
                txtContactPerson.Text = da["roContactPerson"].ToString();
                string telephoneNumber= da["roContactMobile"].ToString();
                string gettelephoneNumber= telephoneNumber.Replace((ConfigurationManager.AppSettings.Get("PhoneCountryCode")), "");
                txtTelephoneNumber.Text = gettelephoneNumber;
                selQualification.SelectedValue= da["roQualification"].ToString();
                txtExp.Text = da["roExperience"].ToString();
                txtBloodGrp.Text = da["roBloodGroup"].ToString();
                txtLicense.Text = da["roLicenceNo"].ToString();
                txtAadhar.Text = da["roAadhaar"].ToString();
                txtPAN.Text = da["roPanNo"].ToString();
                txtAccount.Text = da["roBankAccount"].ToString();
                txtUPI.Text = da["roUPI"].ToString();

            }
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
            if (selDistrict.Items.Count > 1 && !String.IsNullOrEmpty(hidDistrict.Value) && selDistrict.Text != hidDistrict.Value && selDistrict.Items.FindByText(hidDistrict.Value) != null)
                selDistrict.SelectedValue = selDistrict.Items.FindByText(hidDistrict.Value).Value; //selState.Items.FindByText(strState).Value;
            selDistrict.Items.Remove(selDistrict.Items.FindByText($"Select {RetalineProAgent.Service.Common.DistrictLabel}"));
            selDistrict.Items.Insert(0, new ListItem($"Select {RetalineProAgent.Service.Common.DistrictLabel}", ""));

        }



        protected async void btnROSubmit_Click(object sender, EventArgs e)
        {
            //List<KeyValuePair<string, object>> phoneparams = new List<KeyValuePair<string, object>>();
            //phoneparams.Add(new KeyValuePair<string, object>("phoneNo", txtMobile.Text));
            //phoneparams.Add(new KeyValuePair<string, object>("contactNo", txtTelephoneNumber.Text));

            //DataTable phoneDt = DataServiceMySql.GetDataTable($"SELECT COUNT(*) AS cnt FROM relationship_officer WHERE roMobile=@phoneNo ", Service.UserService.GetAPIConnectionString(), phoneparams);
            //if (phoneDt != null && phoneDt.Rows.Count > 0)
            //{
            //    DataRow da = phoneDt.Rows[0];
            //    if (da["cnt"] == txtMobile)
            //    {
            //        Common.ShowToastifyMessage(this.Page, "Duplicate phone number.", "danger");
            //    }
            //}
            //else
            //{
                string primaryMobile = (ConfigurationManager.AppSettings.Get("PhoneCountryCode")) + txtMobile.Text;
                string secondaryMobile = (ConfigurationManager.AppSettings.Get("PhoneCountryCode")) + txtTelephoneNumber.Text;
                if (secondaryMobile == primaryMobile)
                {
                    Common.ShowToastifyMessage(this.Page, "Duplicate phone number.", "danger");
                }
                else
                {
                    try
                    {
                        int roId = -1;
                        if (!String.IsNullOrEmpty(Request.QueryString["id"]))
                            roId = Convert.ToInt32(Request.QueryString["id"]);
                        var user = this.CurrentUser;
                        List<KeyValuePair<string, object>> baparams = new List<KeyValuePair<string, object>>();
                        baparams.Add(new KeyValuePair<string, object>("email", user.Email));
                        //baparams.Add(new KeyValuePair<string, object>("password", user.Password));
                        DataTable result = DataServiceMySql.GetDataTable($"SELECT ba.id AS businessAssociateId, ae.id AS areaId, baEmail, temporary_password, " +
                            $"areaName FROM business_associate ba INNER JOIN area_entries ae ON areaBusinessAssociate = ba.id WHERE baEmail = @email", UserService.GetAPIConnectionString(), baparams);
                        if (result != null && result.Rows.Count > 0)
                        {
                            DataRow dr = result.Rows[0];

                            List<KeyValuePair<string, object>> roparams = new List<KeyValuePair<string, object>>();
                            roparams.Add(new KeyValuePair<string, object>("roId", roId));
                            roparams.Add(new KeyValuePair<string, object>("type", 2));
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
                            roparams.Add(new KeyValuePair<string, object>("businessAssociate", dr["businessAssociateId"].ToString()));
                            roparams.Add(new KeyValuePair<string, object>("area", dr["areaId"].ToString()));



                            if (roId <= 0)
                            {
                                string strSql = $"INSERT INTO relationship_officer(type, roName, roAddress, rost_id, rodst_Id, roPincode,roEmailId, roMobile, " +
                                    $"roContactPerson, roContactMobile, roQualification, roExperience, roBloodGroup, roLicenceNo, roAadhaar, roPanNo, " +
                                    $"roBankAccount, roUPI, roBusAssociate, roArea) " +
                                    $"VALUES(@type, @name, @address, @state, @district, @postCode,@email, @mobile, @contactPerson, @contactNumber, @qualification, " +
                                    $"@experience, @bloodGroup, @license, @aadharNumber, @pan, @banckAct, @upiId, @businessAssociate, @area)";
                                DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), roparams);

                                ShowSuccess("Success!", "Area Manager created successfully!!", "/Business/AreaManager");
                            }
                            else
                            {
                                string strUpdateSql = $"UPDATE relationship_officer SET roName=@name, roAddress=@address, rost_id=@state, rodst_Id=@district, " +
                                    $"roPincode=@postCode,roEmailId=@email,roMobile=@mobile, roContactPerson=@contactPerson, roContactMobile=@contactNumber, roQualification=@qualification, " +
                                    $"roExperience=@experience, roExperience=@bloodGroup, roLicenceNo=@license, roAadhaar=@aadharNumber, roPanNo=@pan, roBankAccount=@banckAct, " +
                                    $"roUPI=@upiId, roBusAssociate=@businessAssociate, roArea=@area WHERE id=@roId";
                                DataServiceMySql.ExecuteSql(strUpdateSql, Service.UserService.GetAPIConnectionString(), roparams);
                                ShowSuccess("Success", "Contact updated successfully!!", "/Business/AreaManager");
                            }
                        }
                        else
                        {
                            Common.ShowCustomAlert(this.Page, "Failure!", " Area Manager cannot be created because of associated area is invalid or not active", false, "/Business/AreaManager");
                        }

                    }
                    catch
                    {
                        Common.ShowToastifyMessage(this.Page, "Execution failed" + "Execution failure No item matches the criteria. Please contact support for more details.", "danger");
                    }
                }
            //}
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


