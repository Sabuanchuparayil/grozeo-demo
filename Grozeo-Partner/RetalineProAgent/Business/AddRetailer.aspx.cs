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
    public partial class AddRetailer : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            
        }

        protected void lbtnSearch_Click(object sender, EventArgs e)
        {
            try
            {
                string searchCode = txtSearch.Text.Trim();

                if (!string.IsNullOrEmpty(searchCode) && searchCode.Length >= 6)
                {
                    string storeId = ExtractStoreId(searchCode);

                    if (!string.IsNullOrEmpty(storeId))
                    {
                        List<KeyValuePair<string, object>> baparams = new List<KeyValuePair<string, object>>();
                        baparams.Add(new KeyValuePair<string, object>("StoreID", storeId));

                        // Fetch store name, prospect_Id, and created_on in a single query
                        DataTable dt = DataServiceMySql.GetDataTable(
                            @"SELECT store_group_name, prospect_Id, created_on 
                      FROM finascop_branch_group 
                      WHERE store_group_id = @StoreID",
                            Service.UserService.GetAPIConnectionString(),
                            baparams);

                        if (dt != null && dt.Rows.Count > 0)
                        {
                            // Store found, fill the store name
                            txtStoreName.Text = dt.Rows[0]["store_group_name"].ToString();
                            int prospectId = dt.Rows[0]["prospect_Id"] != DBNull.Value ? Convert.ToInt32(dt.Rows[0]["prospect_Id"]) : 0;

                            if (prospectId == 0)
                            {
                                // If prospectId is 0, check the created_on date
                                DateTime createdOn = dt.Rows[0]["created_on"] != DBNull.Value ? Convert.ToDateTime(dt.Rows[0]["created_on"]) : DateTime.MinValue;
                                DateTime oneHourAgo = DateTime.Now.AddHours(-1);

                                if (createdOn > oneHourAgo)
                                {
                                    // If created_on is within 1 hour, generate the code
                                    dvCreateCode.Visible = true;
                                    GenerateAndSetCode(baparams);
                                }
                                else
                                {
                                    // Creation time has expired
                                    Common.ShowToastifyMessage(this.Page, "The code cannot be generated as the creation time has expired (beyond 1 hour).", "danger");
                                }
                            }
                            else
                            {
                                // Prospect already exists, no need to check created_on
                                dvCreateCode.Visible = false;
                                Common.ShowToastifyMessage(this.Page, "Referrer is already there for this branch.", "danger");
                            }
                        }
                        else
                        {
                            // No store found for the provided StoreID
                            txtStoreName.Text = "";
                            dvCreateCode.Visible = false;
                            Common.ShowToastifyMessage(this.Page, "No store found for this code.", "danger");
                        }
                    }
                    else
                    {
                        // Invalid store ID extracted
                        txtStoreName.Text = "";
                        dvCreateCode.Visible = false;
                        Common.ShowToastifyMessage(this.Page, "Invalid search code format.", "danger");
                    }
                }
                else
                {
                    // Invalid or short search code
                    txtStoreName.Text = "";
                    dvCreateCode.Visible = false;
                    Common.ShowToastifyMessage(this.Page, "Invalid search code.", "danger");
                }
            }
            catch (Exception ex)
            {
                // Handle unexpected errors
                Common.ShowToastifyMessage(this.Page, "An error occurred: " + ex.Message, "danger");
            }
        }

        private string ExtractStoreId(string searchCode)
        {
            if (string.IsNullOrEmpty(searchCode) || searchCode.Length < 6)
            {
                return string.Empty;
            }

            string storeId = string.Empty;
            int digitCount = 0;

            // Start from the end and collect digits
            for (int i = searchCode.Length - 1; i >= 0 && digitCount < 4; i--)
            {
                if (char.IsDigit(searchCode[i]))
                {
                    storeId = searchCode[i] + storeId;
                    digitCount++;
                }
            }

            // Remove leading zeros if storeId has more than one digit
            return storeId.TrimStart('0');
        }

        private void GenerateAndSetCode(List<KeyValuePair<string, object>> baparams)
        {
            txtGneratedCode.Visible = true;
            string randomString = GenerateRandomString(10);
            baparams.Add(new KeyValuePair<string, object>("invitationCode", randomString));
            txtGneratedCode.Text = randomString;
        }

        public string GenerateRandomString(int length = 10)
        {
            const string characters = "3479acdefghjklmnprtwxyACDEFHIJKLMNPRTWXY";
            int charactersLength = characters.Length;
            StringBuilder randomString = new StringBuilder();

            Random random = new Random();
            for (int i = 0; i < length; i++)
            {
                randomString.Append(characters[random.Next(0, charactersLength)]);
            }

            return randomString.ToString();
        }

        protected async void btnRetailerSubmit_Click(object sender, EventArgs e)
        {
            try
            {
                string searchCode = txtSearch.Text.Trim();

                if (!string.IsNullOrEmpty(searchCode) && searchCode.Length >= 6)
                {
                    string storeId = ExtractStoreId(searchCode);

                    if (!string.IsNullOrEmpty(storeId))
                    {
                        List<KeyValuePair<String, Object>> prospectParams = new List<KeyValuePair<string, object>>
                        {
                            new KeyValuePair<string, object>("storeName", txtStoreName.Text),
                            new KeyValuePair<string, object>("mode", 2),
                            new KeyValuePair<string, object>("StoreID", storeId)
                        };

                        DataTable dt = DataServiceMySql.GetDataTable(@"SELECT br_Name, br_Address, br_pincode, (SELECT st_name FROM              finascop_state WHERE st_ID=br_State) AS state, 
                            br_Lat, br_Lng, br_Incharge, br_Phone, br_Email, store_group_name, 
                            br_State, br_storeGroup, store_group_grosmartMerchant, areaid,
                            (SELECT areaName FROM area_entries WHERE id=areaid) AS areaName  
                            FROM finascop_branch_group bg 
                            INNER JOIN finascop_branch b ON b.br_storeGroup = bg.store_group_id
                            WHERE br_storeGroup = @StoreID 
                            GROUP BY br_storeGroup", Service.UserService.GetAPIConnectionString(), prospectParams);

                        if (dt != null && dt.Rows.Count > 0)
                        {
                            DataRow da = dt.Rows[0];
                            prospectParams.Add(new KeyValuePair<string, object>("location", da["br_Address"].ToString()));
                            prospectParams.Add(new KeyValuePair<string, object>("pincode", da["br_pincode"].ToString()));
                            prospectParams.Add(new KeyValuePair<string, object>("place", da["state"].ToString()));
                            prospectParams.Add(new KeyValuePair<string, object>("latitude", da["br_Lat"].ToString()));
                            prospectParams.Add(new KeyValuePair<string, object>("longitude", da["br_Lng"].ToString()));
                            prospectParams.Add(new KeyValuePair<string, object>("address", da["br_Address"].ToString()));
                            prospectParams.Add(new KeyValuePair<string, object>("contactperson", da["br_Incharge"].ToString()));
                            prospectParams.Add(new KeyValuePair<string, object>("contactnumber", da["br_Phone"].ToString()));
                            prospectParams.Add(new KeyValuePair<string, object>("email", da["br_Email"].ToString()));
                            prospectParams.Add(new KeyValuePair<string, object>("areaId", da["areaid"].ToString()));
                            prospectParams.Add(new KeyValuePair<string, object>("areaname", da["areaName"].ToString()));
                            prospectParams.Add(new KeyValuePair<string, object>("invitationcode", txtGneratedCode.Text));

                            var user = this.CurrentUser;
                            List<KeyValuePair<string, object>> baparams = new List<KeyValuePair<string, object>>
                            {
                                new KeyValuePair<string, object>("email", user.Email)
                            };

                            DataTable baTable = DataServiceMySql.GetDataTable(@"SELECT ba.id, baName FROM business_associate ba WHERE baEmail = @email", UserService.GetAPIConnectionString(), baparams);

                            string baId = "", baName = "";
                            if (baTable != null && baTable.Rows.Count > 0)
                            {
                                DataRow dz = baTable.Rows[0];
                                baId = dz["id"].ToString();
                                baName = dz["baName"].ToString();
                            }

                            prospectParams.Add(new KeyValuePair<string, object>("baId", baId));
                            prospectParams.Add(new KeyValuePair<string, object>("baName", baName));

                            // Insert into prospect table
                            string insertSql = @"INSERT INTO finascop_crm_prospect(crpr_orgName, crpr_mode, crpr_location, crpr_orgPincode, 
                                    crpr_gplace, glatitude, glongitude, crpr_orgAddress, crpr_indContactperson, crpr_orgContactNo, 
                                    crpr_orgEmail, baId, baName, areaId, areaName, invitationCode, storeGroupId) 
                                    VALUES(@storeName, @mode, @location, @pincode, @place, @latitude, @longitude, @address, 
                                    @contactperson, @contactnumber, @email, @baId, @baName, @areaId, @areaname, @invitationcode, 
                                    @StoreID); SELECT LAST_INSERT_ID();";

                            var result = DataServiceMySql.ExecuteScalar(insertSql, Service.UserService.GetAPIConnectionString(), prospectParams);
                            int lastInsertId = Convert.ToInt32(result);

                            if (lastInsertId > 0)
                            {
                                prospectParams.Add(new KeyValuePair<string, object>("lastInsertId", lastInsertId));

                                // Update branch group with prospect ID
                                string updateSql = @"UPDATE finascop_branch_group SET prospect_Id = @lastInsertId WHERE store_group_id = @StoreID";
                                DataServiceMySql.ExecuteSql(updateSql, Service.UserService.GetAPIConnectionString(), prospectParams);
                            }

                            ShowSuccess("Success!", "<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Retailer Added Successfully!!</a></h5>");
                        }
                        else
                        {
                            Common.ShowToastifyMessage(this.Page, "No matching retailer found.", "danger");
                        }
                    }
                    else
                    {
                        Common.ShowToastifyMessage(this.Page, "Invalid search code format.", "danger");
                    }
                }
                else
                {
                    Common.ShowToastifyMessage(this.Page, "Invalid search code.", "danger");
                }
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(this.Page, "Execution failed: " + ex.Message, "danger");
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
    }
}


