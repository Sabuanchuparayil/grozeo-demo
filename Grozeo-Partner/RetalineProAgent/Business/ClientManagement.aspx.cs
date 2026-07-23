using RetalineProAgent.Service;
using RetalineProAgent.Core.BussinessModel.Store;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using RetalineProAgent.Core.Services;
using System.EnterpriseServices;
using System.Web.UI.HtmlControls;
using System.Text;
using System.Configuration;
using RetalineProAgent.Core.Services.HelperServices;
using System.Text.Json;
using System.IO;
using System.Xml.Linq;
using static RetalineProAgent.Finance.VoucherEntry;

namespace RetalineProAgent
{
    public partial class ClientManagement : Base.BasePartnerPage
    {
        string type;
        public int FilterType
        {
            get
            {
                if (ViewState["ORDFILTERTYPE"] == null)
                    return 0;
                else
                    return (int)ViewState["ORDFILTERTYPE"];
            }
            set
            {
                ViewState["ORDFILTERTYPE"] = value;
            }
        }

        public List<upload> lstupload
        {
            get
            {
                if (ViewState["UPLOADLIST"] != null)
                    return (List<upload>)ViewState["UPLOADLIST"];
                return new List<upload>();
            }
            set
            {
                ViewState["UPLOADLIST"] = value;
            }
        }
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                type = Request.QueryString["type"]?.ToLower();
                ConfigurePageForType(type);
                SetPageTitleBasedOnType(type);
                SetBreadcrumbForType(type);
                hfdBlobURL.Value = ConfigurationManager.AppSettings.Get("blobURL") + ConfigurationManager.AppSettings.Get("blobContainer");
            }
        }

        private void ConfigurePageForType(string type)
        {
            // Hide all placeholders initially
            phContact.Visible = false;
            phLeadProspect.Visible = false;

            ltrTitle.Text = type == "contact" ? "Contacts" : "Leads & Prospects";

            switch (type)
            {
                case "contact":
                    phContact.Visible = true;
                    BindContactData();
                    break;
                case "lead":
                    phLeadProspect.Visible = true;

                    // Set common logic for both leads and prospects
                    if (String.IsNullOrEmpty(hidFilterType.Value))
                    {
                        FilterType = 1;
                        hidFilterType.Value = "1";
                        BindTime();
                    }

                    // Dynamically set labels and modal titles for leads and prospects
                    lblModalTitle.Text = "Schedule Meetings";

                    BindLeadProspectData();
                    break;
                default:
                    ltrTitle.Text = "Invalid Type";
                    break;
            }
        }



        private void SetPageTitleBasedOnType(string type)
        {
            if (type == "lead")
            {
                Page.Title = "Leads";
            }
            else if (type == "prospect")
            {
                Page.Title = "Prospects";
            }
            else
            {
                Page.Title = "Contacts";
            }
        }

        private void SetBreadcrumbForType(string type)
        {
            string breadcrumbText;

            if (type == "lead")
            {
                breadcrumbText = "Leads";
            }
            else
            {
                breadcrumbText = "Contacts";
            }

            breadcrumbType.InnerText = breadcrumbText; 
        }

        private void BindTime()
        {
            // Set the start time (00:00 means 12:00 AM)
            DateTime StartTime = DateTime.ParseExact("00:00", "HH:mm", null);

            // Set the end time (23:55 means 11:55 PM)
            DateTime EndTime = DateTime.ParseExact("23:55", "HH:mm", null);

            // Set 1-hour interval
            TimeSpan Interval = new TimeSpan(1, 0, 0);

            // Clear existing items in the dropdowns
            ddlTime.Items.Clear();
            ddlSTime.Items.Clear();

            // Add items to ddlTime dropdown
            while (StartTime <= EndTime)
            {
                ddlTime.Items.Add(new ListItem(StartTime.ToShortTimeString(), StartTime.ToString("HH:mm:ss")));
                ddlSTime.Items.Add(new ListItem(StartTime.ToShortTimeString(), StartTime.ToString("HH:mm:ss")));
                StartTime = StartTime.Add(Interval);
            }

            // Insert default "--Select--" item at the top of both ddlTime and ddlSTime
            ddlTime.Items.Insert(0, new ListItem("--Select--", ""));
            ddlSTime.Items.Insert(0, new ListItem("--Select--", ""));
        }

        protected void Page_PreRender(object sender, EventArgs e)
        {
            lbtnRetailer.CssClass = String.Format("btn btn-block btn-outline-primary {0}", (FilterType <= 1 ? "active" : ""));
            lbtnWholsaler.CssClass = String.Format("btn btn-block btn-outline-primary {0}", (FilterType == 2 ? "active" : ""));
            if (!IsPostBack)
            {
                folder.Value = Guid.NewGuid().ToString();
                string script = @"
                    $(document).ready(function () {
                        $('#blobFileName0').val(idGen.getId()); // Update the hidden field
                        blobFileURL = generateFileUrl($('#UploadFile0').find('.qrqcode_btnicon'));
                        $('#blobFileURL0').val(blobFileURL);
                            __doPostBack('blobFileName0', 'TriggerPostBack');
                    });
                ";

                ScriptManager.RegisterStartupScript(this, GetType(), "GenerateIdScript", script, true);
            }
        }

        
        private void SetSqlDataSourceParameters(SqlDataSourceSelectingEventArgs e, string filterTypeValue = null)
        {
            var user = this.CurrentUser;

            // Retrieve Business Associate ID
            string baId = GetBusinessAssociateId(user.Email);
            if (!string.IsNullOrEmpty(baId))
            {
                e.Command.Parameters["baId"].Value = baId;
            }

            // Set Area ID if available
            if (user.AreaId > 0)
            {
                e.Command.Parameters["areaId"].Value = user.AreaId;
            }

            // Set Filter Type if provided
            if (!string.IsNullOrEmpty(filterTypeValue))
            {
                hidFilterType.Value = filterTypeValue;
                e.Command.Parameters["filterType"].Value = filterTypeValue;
            }
        }

        private string GetBusinessAssociateId(string email)
        {
            List<KeyValuePair<string, object>> baparams = new List<KeyValuePair<string, object>>
            {
                new KeyValuePair<string, object>("email", email)
            };

            DataTable result = DataServiceMySql.GetDataTable(
                "SELECT ba.id FROM business_associate ba WHERE baEmail = @email",
                UserService.GetAPIConnectionString(),
                baparams
            );

            if (result != null && result.Rows.Count > 0)
            {
                return result.Rows[0]["id"].ToString();
            }

            return null;
        }

        protected void SDSLeads_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            SetSqlDataSourceParameters(e, FilterType.ToString());
        }

        protected void SDSContacts_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            SetSqlDataSourceParameters(e);
        }

        private void BindContactData()
        {
            gvContacts.DataBind();
        }

        private void BindLeadProspectData()
        {
            gvLeadProspect.DataBind();
        }

        protected string GetImagePath(object type, object crleImage, object crprImage)
        {
            string typeValue = type?.ToString();
            if (typeValue == "Lead")
            {
                return crleImage?.ToString();
            }
            else if (typeValue == "Prospect")
            {
                return crprImage?.ToString();
            }
            return null;
        }

        protected void btnFilterType_Click(object sender, EventArgs e)
        {
            LinkButton btn = (LinkButton)sender;
            if (btn != null && !String.IsNullOrEmpty(btn.Attributes["typeid"]))
            {
                int btypeid = Convert.ToInt32(btn.Attributes["typeid"]);
                FilterType = btypeid;
                hidFilterType.Value = btypeid.ToString();

            }
        }

        protected void SDSRO_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            var user = this.CurrentUser;
            if (user.AreaId > 0)
                e.Command.Parameters["areaId"].Value = user.AreaId;
        }

        protected void btnDeligateLead_Click(object sender, EventArgs e)
        {
            var user = this.CurrentUser;
            int roid = Convert.ToInt32(selRO.Text);
            string areaName = hidAreaName.Value;
            string clientType = hidType.Value;
            string leadId = hidleadId.Value;

            if (user.AreaId.HasValue && user.AreaId.Value > 0 && roid > 0)
            {
                if (clientType.Equals("Lead", StringComparison.OrdinalIgnoreCase))
                {
                    UpdateLead(roid, user.AreaId.Value, areaName, leadId);
                }
                else if (clientType.Equals("Prospect", StringComparison.OrdinalIgnoreCase))
                {
                    UpdateProspect(roid, user.AreaId.Value, areaName, leadId);
                }

                Common.ShowToastifyMessage(Page, "Data updated successfully");
                gvLeadProspect.DataBind();
            }
        }

        private void UpdateLead(int roId, int areaId, string areaName, string leadId)
        {
            List<KeyValuePair<string, object>> parameters = new List<KeyValuePair<string, object>>()
            {
                new KeyValuePair<string, object>("areaId", areaId),
                new KeyValuePair<string, object>("roId", roId),
                new KeyValuePair<string, object>("areaName", areaName),
                new KeyValuePair<string, object>("leadId", leadId)
            };

            string updateSql = @"UPDATE finascop_crm_lead SET assignedRO = @roId, areaId = @areaId, areaName = @areaName WHERE id = @leadId";
            DataServiceMySql.ExecuteSql(updateSql, Service.UserService.GetAPIConnectionString(), parameters);
        }

        private void UpdateProspect(int roId, int areaId, string areaName, string leadId)
        {
            List<KeyValuePair<string, object>> parameters = new List<KeyValuePair<string, object>>()
            {
                new KeyValuePair<string, object>("areaId", areaId),
                new KeyValuePair<string, object>("roId", roId),
                new KeyValuePair<string, object>("areaName", areaName),
                new KeyValuePair<string, object>("leadId", leadId)
            };

            string updateSql = @"UPDATE finascop_crm_prospect SET assignedRO = @roId, areaId = @areaId, areaName = @areaName WHERE leadId = @leadId";
            DataServiceMySql.ExecuteSql(updateSql, Service.UserService.GetAPIConnectionString(), parameters);
        }

        protected void lbtnUpgradeToProspect_Click(object sender, EventArgs e)
        {
            string leadId = hidleadId.Value;

            // Check if email exists for the lead
            string email = GetLeadEmail(leadId);

            if (string.IsNullOrEmpty(email))
            {
                // Display modal to enter email
                ScriptManager.RegisterStartupScript(this, this.GetType(), "showModalLeadEmail", "$('#modalLeadEmail').modal('show');", true);
                return;
            }

            // Continue with upgrade process
            UpgradeToProspect(leadId);
        }

        private string GetLeadEmail(string leadId)
        {
            string leadEmail = "";
            List<KeyValuePair<String, Object>> leadParams = new List<KeyValuePair<string, object>>();
            leadParams.Add(new KeyValuePair<string, object>("leadId", hidleadId.Value));
            if (hidleadId.Value == "")
            {
                Common.ShowCustomAlert(this.Page, "Loading failed", "Invalid selection or the record is not existing", false, "/Business/ClientManagement?type=lead");
            }

            DataTable dtEmailLead = DataServiceMySql.GetDataTable("SELECT id, crle_orgName, crle_orgEmail FROM finascop_crm_lead WHERE id= @leadId", UserService.GetAPIConnectionString(), leadParams);
            if (dtEmailLead != null && dtEmailLead.Rows.Count > 0)
            {
                DataRow da = dtEmailLead.Rows[0];
                leadEmail = da["crle_orgEmail"].ToString();
            }

            return leadEmail;
        }

        private void UpgradeToProspect(string leadId)
        {
            List<KeyValuePair<String, Object>> leadParams = new List<KeyValuePair<string, object>>();
            leadParams.Add(new KeyValuePair<string, object>("leadId", hidleadId.Value));
            if (hidleadId.Value == "")
            {
                Common.ShowCustomAlert(this.Page, "Loading failed", "Invalid selection or the record is not existing", false, "/Business/ClientManagement?type=lead");
            }

            leadParams.Add(new KeyValuePair<string, object>("status", 3));
            leadParams.Add(new KeyValuePair<string, object>("remarks", "Converted to Prospect"));
            leadParams.Add(new KeyValuePair<string, object>("followupDate", DateTime.Now.ToString("yyyy-MM-dd")));
            string strSql = $"INSERT INTO finascop_crm_communication(crle_id, crmu_id, crmc_remark) " +
                            $"VALUES(@leadId, @status, @remarks)";
            DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), leadParams);

            string strUpdateSql = $"UPDATE finascop_crm_lead SET crmuId=@status, crmFollowupDate=@followupDate, crmRemarks=@remarks WHERE id = @leadId";
            DataServiceMySql.ExecuteSql(strUpdateSql, UserService.GetAPIConnectionString(), leadParams);



            DataTable dtCrmLead = DataServiceMySql.GetDataTable("SELECT crle_orgName, crle_mode, crle_type, crle_description, crle_location, crle_orgPincode, crle_orgCountry, crle_groute, crle_glocality, crle_gplace, glatitude, glongitude, crle_orgAddress, crle_indContactperson, crle_indMobile, crle_orgContactNo, retailCategory, crle_orgEmail, assignedRO, baId, baName, areaId, areaName FROM finascop_crm_lead WHERE id = @leadId", UserService.GetAPIConnectionString(), leadParams);
            if (dtCrmLead != null && dtCrmLead.Rows.Count > 0)
            {
                DataRow da = dtCrmLead.Rows[0];
                leadParams.Add(new KeyValuePair<string, object>("organizationName", da["crle_orgName"].ToString()));
                leadParams.Add(new KeyValuePair<string, object>("mode", da["crle_mode"].ToString()));
                leadParams.Add(new KeyValuePair<string, object>("type", da["crle_type"].ToString()));
                leadParams.Add(new KeyValuePair<string, object>("description", da["crle_description"].ToString()));
                leadParams.Add(new KeyValuePair<string, object>("location", da["crle_location"].ToString()));
                leadParams.Add(new KeyValuePair<string, object>("pincode", da["crle_orgPincode"].ToString()));
                leadParams.Add(new KeyValuePair<string, object>("country", da["crle_orgCountry"].ToString()));
                leadParams.Add(new KeyValuePair<string, object>("groute", da["crle_groute"].ToString()));
                leadParams.Add(new KeyValuePair<string, object>("locality", da["crle_glocality"].ToString()));
                leadParams.Add(new KeyValuePair<string, object>("place", da["crle_gplace"].ToString()));
                leadParams.Add(new KeyValuePair<string, object>("latitude", da["glatitude"].ToString()));
                leadParams.Add(new KeyValuePair<string, object>("longitude", da["glongitude"].ToString()));
                leadParams.Add(new KeyValuePair<string, object>("address", da["crle_orgAddress"].ToString()));
                leadParams.Add(new KeyValuePair<string, object>("contactPerson", da["crle_indContactperson"].ToString()));
                leadParams.Add(new KeyValuePair<string, object>("mobile", da["crle_indMobile"].ToString()));
                leadParams.Add(new KeyValuePair<string, object>("contactNo", da["crle_orgContactNo"].ToString()));
                leadParams.Add(new KeyValuePair<string, object>("retailCategory", da["retailCategory"].ToString()));
                leadParams.Add(new KeyValuePair<string, object>("email", da["crle_orgEmail"].ToString()));
                leadParams.Add(new KeyValuePair<string, object>("assignedRO", da["assignedRO"].ToString()));
                leadParams.Add(new KeyValuePair<string, object>("baId", da["baId"].ToString()));
                leadParams.Add(new KeyValuePair<string, object>("baName", da["baName"].ToString()));
                leadParams.Add(new KeyValuePair<string, object>("areaId", da["areaId"].ToString()));
                leadParams.Add(new KeyValuePair<string, object>("areaName", da["areaName"].ToString()));
                string randomString = GenerateRandomString(10);
                leadParams.Add(new KeyValuePair<string, object>("invitationCode", randomString));
                leadParams.Add(new KeyValuePair<string, object>("prospectStatus", 3));


                string stringSql = $"INSERT INTO finascop_crm_prospect(crpr_orgName, crpr_mode, crpr_type, crpr_description, crpr_location, crpr_orgPincode, crpr_orgCountry, crpr_groute, crpr_glocality, crpr_gplace, glatitude, glongitude, crpr_orgAddress, crpr_indContactperson, crpr_indMobile, crpr_orgContactNo, retailCategory, crpr_orgEmail, leadId, crmuId, assignedRO, baId, baName, areaId, areaName, invitationCode) " +
                            $"VALUES(@organizationName, @mode, @type, @description, @location, @pincode, @country, @groute, @locality, @place, @latitude, @longitude, @address, @contactPerson, @mobile, @contactNo, @retailCategory, @email, @leadId, @prospectStatus, @assignedRO, @baId, @baName, @areaId, @areaName, @invitationCode)";
                DataServiceMySql.ExecuteSql(stringSql, UserService.GetAPIConnectionString(), leadParams);
                ShowSuccess("Success!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Converted to Prospect!!</a></h5>");
            }
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

        protected void btnSchedule_Click(object sender, EventArgs e)
        {
            try
            {
                List<KeyValuePair<string, object>> scheduleParams = new List<KeyValuePair<string, object>>()
                {
                    new KeyValuePair<string, object>("leadId", hidleadId.Value),
                    new KeyValuePair<string, object>("date", txtDate.Text),
                    new KeyValuePair<string, object>("time", ddlTime.SelectedValue),
                    new KeyValuePair<string, object>("remarks", txtRemarks.Text)
                };


                string stringSql = @"INSERT INTO crm_meetings (crmUserId, meetingDate, meetingTime, meetingRemarks) VALUES (@leadId, @date, @time, @remarks)";
                DataServiceMySql.ExecuteSql(stringSql, UserService.GetAPIConnectionString(), scheduleParams);
                gvLeadProspect.DataBind();
               
                Common.ShowToastifyMessage(Page, "Meeting scheduled successfully.");
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(this.Page, "An error occurred", "danger");
            }
        }

        protected void btnAction_Click(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;
            hidleadId.Value = (lbtn.Attributes["leadid"]);
            string leadId = hidleadId.Value;
            string clientType = lbtn.Attributes["clientType"];
            hidType.Value = clientType;

            //popup Action
            string strAlertSCript = "$('#Communication').modal('show');";
            strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";
            System.Type cstype = this.GetType();
            String csname1 = "ShowConfirmPopup";
            ClientScriptManager cs = this.ClientScript;
            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
            cstext1.Append("script>");
            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }

        
        protected void btnStatusUpt_Click(object sender, EventArgs e)
        {
            try
            {
                Button btn = (Button)sender;
                if (btn == null) return;

                List<KeyValuePair<string, object>> prospectParams = new List<KeyValuePair<string, object>>()
                {
                    new KeyValuePair<string, object>("prospectId", hidProspectId.Value),
                    new KeyValuePair<string, object>("status", selStatus.Text),
                    new KeyValuePair<string, object>("date", txtDatePicker.Text),
                    new KeyValuePair<string, object>("remarks", txtRemarks.Text)
                };


                string insertSql = "INSERT INTO finascop_crm_communication(prospectId, crmu_id, crmc_remark) VALUES(@prospectId, @status, @remarks)";
                ExecuteQuery(insertSql, prospectParams);


                string updateSql = "UPDATE finascop_crm_prospect SET crmuId=@status, crmFollowupDate=@date, crmRemarks=@remarks WHERE id = @prospectId";
                ExecuteQuery(updateSql, prospectParams);
                ShowSuccess("Success!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Status updated!!</a></h5>");
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(this.Page, "An error occurred while updating status", "danger");
            }
        }


        private void ExecuteQuery(string query, List<KeyValuePair<string, object>> parameters)
        {
            DataServiceMySql.ExecuteSql(query, UserService.GetAPIConnectionString(), parameters);
        }


        protected void btnLeadEmail_Click(object sender, EventArgs e)
        {
            List<KeyValuePair<String, Object>> leadParams = new List<KeyValuePair<string, object>>();
            leadParams.Add(new KeyValuePair<string, object>("leademail", txtLeadEmail.Text));
            leadParams.Add(new KeyValuePair<string, object>("leadId", hidleadId.Value));
            DataTable dtemailPhone = DataServiceMySql.GetDataTable($"SELECT COUNT(*) AS cnt FROM finascop_crm_lead  WHERE crle_orgEmail=@leademail", Service.UserService.GetAPIConnectionString(), leadParams);
            DataRow dr = dtemailPhone.Rows[0];
            if (Convert.ToInt32(dr["cnt"]) > 0)
            {
                ShowFailure("Error", "Failure with error: " + "Duplicate email id.");
            }
            else
            {
                string strUpdateSql = $"UPDATE finascop_crm_lead SET crle_orgEmail=@leademail WHERE id = @leadId";
                DataServiceMySql.ExecuteSql(strUpdateSql, UserService.GetAPIConnectionString(), leadParams);
                txtLeadEmail.Text = "";
                ScriptManager.RegisterStartupScript(this, this.GetType(), "clearTextBox", "clearTextBox();", true);
                ShowSuccess("Success!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Email Added Successfully!!</a></h5>");
            }
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

        protected void btnEdit_Click(object sender, EventArgs e)
        {
            LinkButton btn = (LinkButton)sender;
            string clientType = btn.Attributes["clientType"];
            string redirectUrl;

            if (clientType == "Lead")
            {
                string leadId = btn.Attributes["leadid"];
                redirectUrl = $"AsstLeadSettings.aspx?type=Lead&leadid={leadId}";
            }
            else if (clientType == "Prospect")
            {
                string prospectId = btn.Attributes["prospectid"];
                redirectUrl = $"AsstLeadSettings.aspx?type=Prospect&prospectid={prospectId}";
            }
            else
            {
                throw new InvalidOperationException("Unexpected client type.");
            }

            Response.Redirect(redirectUrl);
        }


        protected void btnSentInvitation_Click(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;
            if (lbtn != null && !String.IsNullOrEmpty(lbtn.Attributes["prospectId"]))
            {
                string prospectId = Convert.ToString(lbtn.Attributes["prospectId"]);
                List<KeyValuePair<String, Object>> prosparams = new List<KeyValuePair<string, object>>();
                prosparams.Add(new KeyValuePair<string, object>("prospectId", prospectId));

                DataTable dtCrmProspect = DataServiceMySql.GetDataTable("SELECT invitationSent, invitationCode FROM finascop_crm_prospect WHERE id = @prospectId", UserService.GetAPIConnectionString(), prosparams);
                if (dtCrmProspect != null && dtCrmProspect.Rows.Count > 0)
                {
                    DataRow da = dtCrmProspect.Rows[0];
                    string organisationName = Convert.ToString(lbtn.Attributes["orgName"]);
                    string email = Convert.ToString(lbtn.Attributes["email"]);
                    string code = da["invitationCode"].ToString();
                    string fullname = organisationName;
                    int invitationSentStatus = Convert.ToInt32(da["invitationSent"]);
                    if (Convert.ToInt32(prospectId) > 0)
                    {
                            try
                            {
                                List<KeyValuePair<string, string>> replacements = new List<KeyValuePair<string, string>>();
                                replacements.Add(new KeyValuePair<string, string>("code", da["invitationCode"].ToString()));
                                replacements.Add(new KeyValuePair<string, string>("organisationName", organisationName));
                                replacements.Add(new KeyValuePair<string, string>("email", email));
                                var prospectResult = CallProspectSendInviteApi(code, fullname, email);
                                if (prospectResult != null)
                                {
                                    String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                                    String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                                    replacements.Add(new KeyValuePair<string, string>("[URLPART]", strUrl));
                                    replacements.Add(new KeyValuePair<string, string>("[USER]", fullname));
                                    replacements.Add(new KeyValuePair<string, string>("[SIGNUPURL]", prospectResult));
                                    string strBody = Service.EmailService.CreateEmailbody(Service.EmailType.ProspectInvite, replacements);
                                    var result = Core.Services.APIService.SendEmail(email, "Welcome to Grozeo Store", strBody, organisationName, true);
                                    string invitationUrl = prospectResult.ToString();

                                    DateTime currentDateTime = DateTime.Now;
                                    string formattedDateTime = currentDateTime.ToString("yyyy-MM-dd HH:mm:ss");
                                    List<KeyValuePair<String, Object>> prospectparams = new List<KeyValuePair<string, object>>();
                                    prospectparams.Add(new KeyValuePair<string, object>("prospectId", prospectId));
                                    prospectparams.Add(new KeyValuePair<string, object>("expiredDate", DateTime.UtcNow.AddMinutes(30)));
                                    prospectparams.Add(new KeyValuePair<string, object>("updatedOn", formattedDateTime));
                                    prospectparams.Add(new KeyValuePair<string, object>("invitationSent", 1));
                                    prospectparams.Add(new KeyValuePair<string, object>("invitationLink", prospectResult));
                                    string updateSql = "UPDATE finascop_crm_prospect SET crpr_UpdatedOn=@updatedOn, crpr_ExpiredOn=@expiredDate, invitationSent=@invitationSent, invitationLink=@invitationLink where id = @prospectId";
                                    DataServiceMySql.ExecuteSql(updateSql, Service.UserService.GetAPIConnectionString(), prospectparams);
                                    //Common.ShowToastifyMessage(Page, "Invitation sent successfully");
                                    ShowSuccess("Success!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Invitation sent successfully!!</a></h5>");
                                }
                                else
                                {
                                    Common.ShowToastifyMessage(this.Page, "Failed, the email or mobile conflicted with an existing record. Please try with another email and mobile!", "danger");
                                }

                            }
                            catch (Exception ex) 
                            {
                                Common.ShowToastifyMessage(this.Page, "An error occurred: " + ex.Message, "danger");
                            }
                    }
                }
            }
        }


        protected static string CallProspectSendInviteApi(string code, string fullname, string email)
        {
            string apiProspectUrl = "";
            try
            {
                var requestData = new
                {
                    code = code,
                    fullname = fullname,
                    email = email
                };

                try
                {
                    //string apiUrl = "https://partner.dev.grozeo.in/api/Register/ProspectSendInvite";
                    string apiUrl = $"{ConfigurationSettings.AppSettings.Get("partner.url")}api/Register/ProspectSendInvite";
                    var aPIData = HttpHelperService.Post<Core.BussinessModel.API.APIProspectModel>(apiUrl, requestData, 0);
                    if (aPIData != null && aPIData.URL != null)
                        //return aPIData.URL;
                        apiProspectUrl = aPIData.URL;
                }
                catch (JsonException ex)
                {
                    // Log the exception for debugging purposes.
                    Console.WriteLine("JSON serialization error: " + ex.Message);
                }

            }
            catch (Exception ex)
            {

            }
            return apiProspectUrl;
        }

        protected void lbtnView_Click(object sender, EventArgs e)
        {
            try
            {
                LinkButton lbtn = sender as LinkButton;
                if (lbtn == null) return;

                string imageUrl = string.Empty;

                if (!string.IsNullOrEmpty(lbtn.Attributes["contactImage"]))
                {
                    imageUrl = lbtn.Attributes["contactImage"];
                }
                else if (!string.IsNullOrEmpty(lbtn.Attributes["leadImage"]))
                {
                    imageUrl = lbtn.Attributes["leadImage"];
                }
                else if (!string.IsNullOrEmpty(lbtn.Attributes["prospectImage"]))
                {
                    imageUrl = lbtn.Attributes["prospectImage"];
                }

                // Ensure the image URL is valid
                if (!string.IsNullOrEmpty(imageUrl))
                {
                    ScriptManager.RegisterStartupScript(this, this.GetType(), "ShowImageModal",
                        $"showImageModal('{ResolveUrl(imageUrl)}');", true);
                }
                else
                {
                    Common.ShowToastifyMessage(Page, "Image not found.", "warning");
                }
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(Page, "An error occurred while loading the image.", "danger");
            }
        }

        protected void gvLeadProspect_RowDataBound(object sender, GridViewRowEventArgs e)
        {
            if (e.Row.RowType != DataControlRowType.DataRow)
                return;

            if (e.Row.RowType == DataControlRowType.DataRow)
            {
                // Retrieve the Type for the current row (Lead or Prospect)
                string clientType = DataBinder.Eval(e.Row.DataItem, "Type") as string;

                // Determine background color based on Type
                System.Drawing.Color backgroundColor = clientType == "Lead"
                    ? System.Drawing.Color.White
                    : System.Drawing.Color.PeachPuff;

                // Set common attributes
                e.Row.BackColor = backgroundColor;
                AddCollapseAttributes(e.Row, e.Row.DataItemIndex);
            }
        }

        private void AddCollapseAttributes(GridViewRow row, int dataItemIndex)
        {
            string collapseId = $"collapse{dataItemIndex}";

            row.Attributes.Add("data-toggle", "collapse");
            row.Attributes.Add("data-target", $"#{collapseId}");
            row.Attributes.Add("aria-expanded", "false");
            row.Attributes.Add("aria-controls", collapseId);
        }

        protected void btnSubmitActivity_Click(object sender, EventArgs e)
        {
            string selectedActivity = ddlActivity.SelectedValue;
            string leadId = hidleadId.Value;
            string prospectId = string.IsNullOrEmpty(hidProspectId.Value) ? "0" : hidProspectId.Value;
            string notes = txtNotes.Text.Trim();
            string attachmentPath = string.Empty;

            try
            {
                // Validate activity selection
                if (string.IsNullOrEmpty(selectedActivity))
                {
                    Common.ShowToastifyMessage(this.Page, "Select activity.", "danger");
                    return;
                }

                // Handle file upload
                if (fuAttachment.HasFile)
                {
                    attachmentPath = UploadAttachFile(fuAttachment, "activity/attachment/");
                }

                // Declare common query and parameters list
                string query = string.Empty;
                List<KeyValuePair<string, object>> activityParams = new List<KeyValuePair<string, object>>();

                // Check activity type and process accordingly
                if (new[] { "3", "4", "9", "10", "11", "12" }.Contains(selectedActivity))
                {
                    // Activities that require only notes
                    if (string.IsNullOrEmpty(notes))
                    {
                        Common.ShowToastifyMessage(this.Page, "Note is required for this activity.", "danger");
                        return;
                    }

                    query = @"INSERT INTO finascop_crm_communication (crle_id, prospectId, crca_id, crmc_remark) 
                        VALUES (@leadID, @prospectId, @activityId, @notes)";
                    activityParams.AddRange(new[]
                    {
                        new KeyValuePair<string, object>("leadID", leadId),
                        new KeyValuePair<string, object>("prospectId", prospectId),
                        new KeyValuePair<string, object>("activityId", selectedActivity),
                        new KeyValuePair<string, object>("notes", notes)
                    });

                    DataServiceMySql.ExecuteSql(query, UserService.GetAPIConnectionString(), activityParams);
                }
                else if (new[] { "5", "6", "7", "8" }.Contains(selectedActivity))
                {
                    // Activities that require either notes or an attachment
                    if (string.IsNullOrEmpty(notes) && attachmentPath == "")
                    {
                        Common.ShowToastifyMessage(this.Page, "Either Notes or an Attachment is required for this activity.", "danger");
                        return;
                    }

                     query = @"INSERT INTO finascop_crm_communication (crle_id, prospectId, crca_id, crmc_remark, crmc_HasFile) VALUES (@leadID, @prospectId, @activityId, @notes, @hasFile); SELECT LAST_INSERT_ID();";
                    activityParams.AddRange(new[]
                    {
                        new KeyValuePair<string, object>("leadID", leadId),
                        new KeyValuePair<string, object>("prospectId", prospectId),
                        new KeyValuePair<string, object>("activityId", selectedActivity),
                        new KeyValuePair<string, object>("notes", string.IsNullOrEmpty(notes) ? "" : notes),
                        new KeyValuePair<string, object>("hasFile", string.IsNullOrEmpty(attachmentPath) ? 0 : 1)
                    });

                    // Execute query and retrieve last inserted ID
                    int lastInsertedId = Convert.ToInt32(DataServiceMySql.ExecuteScalar(query, UserService.GetAPIConnectionString(), activityParams));
                    //int lastInsertedId = GetLastInsertedId();

                    // If valid ID and attachment, insert into the file table
                    if (lastInsertedId > 0 && !string.IsNullOrEmpty(attachmentPath))
                    {
                        InsertAttachment(lastInsertedId, attachmentPath);
                    }
                }
                else if (new[] { "13", "14", "15" }.Contains(selectedActivity))
                {
                    // Activities with scheduled date/time
                    string scheduledDate = calendar.Text;
                    string scheduledTime = string.IsNullOrEmpty(ddlSTime.Text) ? "00:00:00" : ddlSTime.Text;
                    string scheduledDateTime = $"{scheduledDate} {scheduledTime}";

                    if (string.IsNullOrEmpty(scheduledDate))
                    {
                        Common.ShowToastifyMessage(this.Page, "Date is required for this activity.", "danger");
                        return;
                    }

                    query = @"INSERT INTO finascop_crm_communication (crle_id, prospectId, crca_id, crmc_Communication_Time) 
                        VALUES (@leadID, @prospectId, @activityId, @dateTime)";
                    activityParams.AddRange(new[]
                    {
                        new KeyValuePair<string, object>("leadID", leadId),
                        new KeyValuePair<string, object>("prospectId", prospectId),
                        new KeyValuePair<string, object>("activityId", selectedActivity),
                        new KeyValuePair<string, object>("dateTime", scheduledDateTime)
                    });

                    DataServiceMySql.ExecuteSql(query, UserService.GetAPIConnectionString(), activityParams);
                }
                else
                {
                    Common.ShowToastifyMessage(this.Page, "Invalid activity selected.", "danger");
                    return;
                }

                //Common.ShowToastifyMessage(this.Page, "Activity added successfully!");
                ShowSuccess("Success!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Activity added successfully!!</a></h5>");
                ResetModalFields();
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(this.Page, "An error occurred while adding the activity: " + ex.Message, "danger");
            }
        }

        private int GetLastInsertedId()
        {
            string query = @"SELECT LAST_INSERT_ID();";
            var result = DataServiceMySql.ExecuteScalar(query, UserService.GetAPIConnectionString(), null);
            return result != null ? Convert.ToInt32(result) : 0;
        }

        private void InsertAttachment(int lastInsertedId, string attachmentPath)
        {
            string query = @"INSERT INTO finascop_crm_communication_file (crle_id, crmc_id, crmf_bucketname, crmf_filepath, crmf_filename) 
                      VALUES (@leadID, @lastInsertId, @bucketName, @filePath, @fileName)";
            List<KeyValuePair<string, object>> fileParams = new List<KeyValuePair<string, object>>
            {
                new KeyValuePair<string, object>("leadID", hidleadId.Value),
                new KeyValuePair<string, object>("lastInsertId", lastInsertedId),
                new KeyValuePair<string, object>("bucketName", ConfigurationManager.AppSettings.Get("AWS_S3_BucketProducts")),
                new KeyValuePair<string, object>("filePath", attachmentPath),
                new KeyValuePair<string, object>("fileName", Path.GetFileName(attachmentPath))
            };
            DataServiceMySql.ExecuteSql(query, UserService.GetAPIConnectionString(), fileParams);
        }

        private string UploadAttachFile(FileUpload fileUpload, string filePath)
        {
            if (fileUpload.HasFile)
            {
                try
                {
                    string fileName = Path.GetFileName(fileUpload.PostedFile.FileName);
                    string contentType = fileUpload.PostedFile.ContentType;
                    using (var fileStream = fileUpload.PostedFile.InputStream)
                    {
                        string uploadedFilePath = FileService.UploadROPhotoCV(fileStream, fileName, filePath);

                        // Store the uploaded file path in ViewState for later reference
                        ViewState["UploadedFilePath"] = uploadedFilePath;

                        return uploadedFilePath;
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
            return string.Empty;
        }

        private void ResetModalFields()
        {
            ddlActivity.SelectedIndex = 0;
            txtNotes.Text = string.Empty;
            txtInvitationCode.Text = string.Empty;
            fuAttachment.Attributes.Clear();
            calendar.Text = string.Empty;
            ddlSTime.SelectedIndex = 0;
            txtAdditionalNotes.Text = string.Empty;
            txtCustomActivity.Text = string.Empty;
            txtFileName.Text= string.Empty;
            txtFileName.Visible = false;
        }

        protected void btnSubmitAdditionalFeatures_Click(object sender, EventArgs e)
        {
            string leadId = hidleadId.Value;
            string prospectId = string.IsNullOrEmpty(hidProspectId.Value) ? "0" : hidProspectId.Value;
            string notes = txtAdditionalNotes.Text.Trim();
            string attachmentPath = string.Empty;
            string customActivity = txtCustomActivity.Text.Trim();

            try
            {
                if (string.IsNullOrEmpty(customActivity))
                {
                    Common.ShowToastifyMessage(this.Page, "Activity name is required for this activity.", "danger");
                    return;
                }
                if (string.IsNullOrEmpty(notes) && hfAttachmentPath.Value == "")
                {
                    Common.ShowToastifyMessage(this.Page, "Either Notes or an Attachment is required for this activity.", "danger");
                    return;
                }
                if(hfAttachmentPath.Value != "")
                {
                    attachmentPath = hfAttachmentPath.Value;
                }
                int notetype = rbPrivate.Checked ? 2 : 1;

                List<KeyValuePair<string, object>> activityParams = new List<KeyValuePair<string, object>>
                {
                    new KeyValuePair<string, object>("leadID", leadId),
                    new KeyValuePair<string, object>("prospectId", prospectId),
                    new KeyValuePair<string, object>("activityId", 16),
                    new KeyValuePair<string, object>("notes", string.IsNullOrEmpty(notes) ? "" : notes),
                    new KeyValuePair<string, object>("hasFile", string.IsNullOrEmpty(attachmentPath) ? 0 : 1),
                    new KeyValuePair<string, object>("activity", customActivity),
                    new KeyValuePair<string, object>("noteType", notetype)
                };

                string query = @"INSERT INTO finascop_crm_communication (crle_id, prospectId, crca_id, crmc_remark, crmc_HasFile, noteType, activity) VALUES (@leadID, @prospectId, @activityId, @notes, @hasFile, @noteType, @activity); SELECT LAST_INSERT_ID();";

                int lastInsertedId = Convert.ToInt32(DataServiceMySql.ExecuteScalar(query, UserService.GetAPIConnectionString(), activityParams));

                //int lastInsertedId = GetLastInsertedId();
                if (lastInsertedId > 0 && !string.IsNullOrEmpty(attachmentPath))
                {
                    InsertAttachment(lastInsertedId, attachmentPath);
                }
                ShowSuccess("Success!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Custom activity added successfully!!</a></h5>");
                ResetModalFields();
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(this.Page, "An unexpected error occurred: " + ex.Message, "danger");
            }
        }

        public string GetCurrentUrl()
        {
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            string guid = Guid.NewGuid().ToString();

            return strUrl.TrimEnd(new char[] { '/' });
        }

        protected void btnupload_Click(object sender, EventArgs e)
        {
            try
            {
                // Retrieve previous file details
                string previousFilePath = ViewState["UploadedFilePath"] as string;
                string uploadSource = ViewState["UploadSource"] as string;

                // Delete the previously uploaded file (if any)
                DeletePreviousFile(previousFilePath, uploadSource);

                // Upload new file
                string attachmentPath = UploadFile(out string attachmentFile);

                if (string.IsNullOrEmpty(attachmentPath))
                {
                    Common.ShowToastifyMessage(this.Page, "Attachment is required.", "danger");
                    ResetUI();
                    ShowModals();
                    return;
                }

                // Store uploaded file details in ViewState
                ViewState["UploadedFilePath"] = attachmentPath;
                ViewState["UploadSource"] = DetermineUploadSource();
                hfAttachmentPath.Value = attachmentPath;

                // Update UI after successful upload
                UpdateUIAfterUpload(attachmentFile);
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(this.Page, $"An error occurred: {ex.Message}", "danger");
            }
        }

        private string UploadFile(out string attachmentFile)
        {
            attachmentFile = string.Empty;

            if (fuCustomAttachment.HasFile)
            {
                attachmentFile = fuCustomAttachment.FileName;
                ViewState["UploadSource"] = "S3";
                ScriptManager.RegisterStartupScript(this, GetType(), "ShowParentModal", "$('#modalActivities').modal('show');", true);
                ScriptManager.RegisterStartupScript(this, GetType(), "ShowSecondModal", "$('#modalCustomActivity').modal('show');", true);
                return UploadAttachFile(fuCustomAttachment, "customactivity/attachment/");
            }
            else if (!string.IsNullOrEmpty(blobFileName0.Value) && !string.IsNullOrEmpty(blobFileURL0.Value))
            {
                attachmentFile = blobFileName0.Value;
                ViewState["UploadSource"] = "Blob";
                ScriptManager.RegisterStartupScript(this, GetType(), "ShowParentModal", "$('#modalActivities').modal('show');", true);
                ScriptManager.RegisterStartupScript(this, GetType(), "ShowSecondModal", "$('#modalCustomActivity').modal('show');", true);
                return blobFileURL0.Value;
            }

            return string.Empty;
        }

        private string DetermineUploadSource()
        {
            return ViewState["UploadSource"] as string ?? string.Empty;
        }

        protected void btnChange_Click(object sender, EventArgs e)
        {
            try
            {
                // Retrieve previous file info
                string previousFilePath = ViewState["UploadedFilePath"] as string;
                string uploadSource = ViewState["UploadSource"] as string;

                // Delete previous file if exists
                DeletePreviousFile(previousFilePath, uploadSource);

                // Reset UI elements
                ResetUI();
                ShowModals();
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(this.Page, "Error while changing file: " + ex.Message, "danger");
            }
        }

        private void UpdateUIAfterUpload(string attachmentFile)
        {
            txtFileName.Text = attachmentFile;
            txtFileName.Visible = true;
            txtFileName.Enabled = false;
            btnChange.Visible = true;
            documentUpload.Visible = false;

            // Clear hidden fields
            blobFileName0.Value = string.Empty;
            blobFileURL0.Value = string.Empty;
        }

        private void ResetUI()
        {
            txtFileName.Text = string.Empty;
            hfAttachmentPath.Value = string.Empty;
            txtFileName.Visible = false;
            btnChange.Visible = false;
            documentUpload.Visible = true;
            btnupload.Visible = true;

            // Clear hidden fields
            blobFileName0.Value = string.Empty;
            blobFileURL0.Value = string.Empty;
        }

        private void DeletePreviousFile(string filePath, string uploadSource)
        {
            if (!string.IsNullOrEmpty(filePath))
            {
                if (uploadSource == "S3")
                {
                    string s3FilePath = filePath.Substring(filePath.IndexOf("customactivity/attachment/"));
                    FileService.DeleteROPhotoCV(s3FilePath);
                }
                else if (uploadSource == "Blob")
                {
                    DeleteFile(filePath);
                }

                // Clear ViewState after deletion
                ViewState["UploadedFilePath"] = null;
                ViewState["UploadSource"] = null;
            }
        }

        protected void createUploadDetailsXML()
        {
            String fileUrl = "";
            String uploadInfoXML = CreateXMLFromList();
            bool xmlFileCreated = false;
            int attempts = 0;
            while (!xmlFileCreated && attempts < 3)
            {
                attempts++;
                try
                {

                    fileUrl = Common.CreateBlob(uploadInfoXML, $"UploadDetails.xml", $"finascopupload/{folder.Value}").Result;
                    xmlFileCreated = true;
                }
                catch (Exception blobExists)
                {
                    var result = Common.DeleteBlob(blobExists.InnerException.Message);
                }
            }
        }

        protected String CreateXMLFromList()
        {
            XDocument xmlDocument = new XDocument(
                new XDeclaration("1.0", "utf-8", "yes"),
                new XComment("Finascop Upload Document Details"),
                new XElement("Upload",
                    from upload in lstupload
                    select new XElement("UploadDetails", new XAttribute("DOC_Index", upload.DocumentID),
                           new XElement("DocumentName", upload.DocumentName),
                           new XElement("DocumentNarration", new XText(upload.DocumentNarration)),
                           new XElement("DocumentURL", upload.DocumentURL),
                           new XElement("FileName", upload.FileName)
                 )));

            return xmlDocument.ToString();
        }

        protected void DeleteFile(string blobUrl)
        {
            try
            {
                if (!string.IsNullOrEmpty(blobUrl))
                {
                    Common.DeleteBlob(blobUrl);
                    ViewState["UploadedFilePath"] = null;
                    ViewState["UploadSource"] = null;
                }
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(this.Page, $"Error deleting Blob file: {ex.Message}", "danger");
            }
        }

        private void ShowModals()
        {
            ScriptManager.RegisterStartupScript(this, GetType(), "ShowParentModal", "$('#modalActivities').modal('show');", true);
            ScriptManager.RegisterStartupScript(this, GetType(), "ShowSecondModal", "$('#modalCustomActivity').modal('show');", true);
            ScriptManager.RegisterStartupScript(this, GetType(), "ShowThirdModal", "$('#DocumentUploadpopup').modal('show');", true);
        }

        protected void btnCancel_Click(object sender, EventArgs e)
        {
            ViewState["UploadedFilePath"] = null;
            ViewState["UploadSource"] = null;

            // Reset hidden fields for Blob
            blobFileName0.Value = string.Empty;
            blobFileURL0.Value = string.Empty;

            // Reset UI elements
            txtFileName.Text = string.Empty;
            hfAttachmentPath.Value = string.Empty;
            txtFileName.Visible = false;
            ddlActivity.SelectedIndex = 0;

            //Call the JavaScript function to handle the modal visibility and txtFileName behavior
            ScriptManager.RegisterStartupScript(this, GetType(), "ShowCustomActivityModal", "showCustomActivityModal();", true);
        }
    }
}


