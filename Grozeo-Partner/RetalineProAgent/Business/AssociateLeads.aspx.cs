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
using System.Text;
using Newtonsoft.Json;

namespace RetalineProAgent
{
    public partial class AssociateLeads : Base.BasePartnerPage
    {
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


        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack && String.IsNullOrEmpty(hidFilterType.Value))
            {
                FilterType = 1; hidFilterType.Value = "1";
                BindTime();
            }
        }

        
        private void BindTime()
        {
            // Set the start time (00:00 means 12:00 AM)
            DateTime StartTime = DateTime.ParseExact("00:00", "HH:mm", null);
            // Set the end time (23:55 means 11:55 PM)
            DateTime EndTime = DateTime.ParseExact("23:55", "HH:mm", null);
            //Set 1 hour interval
            TimeSpan Interval = new TimeSpan(1, 0, 0);
            ddlTime.Items.Clear();
            while (StartTime <= EndTime)
            {
                ddlTime.Items.Add(new ListItem(StartTime.ToShortTimeString(), StartTime.ToString("HH:mm:ss")));
                StartTime = StartTime.Add(Interval);
            }
            ddlTime.Items.Insert(0, new ListItem("--Select--", ""));
        }

        protected void Page_PreRender(object sender, EventArgs e)
        {
            lbtnRetailer.CssClass = String.Format("btn btn-block btn-outline-primary {0}", (FilterType <= 1 ? "active" : ""));
            lbtnWholsaler.CssClass = String.Format("btn btn-block btn-outline-primary {0}", (FilterType == 2 ? "active" : ""));
        }


        protected void gvLead_DataBound(object sender, EventArgs e)
        {

        }

        protected void SDSLeads_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            var user = this.CurrentUser;
            List<KeyValuePair<string, object>> baparams = new List<KeyValuePair<string, object>>();
            baparams.Add(new KeyValuePair<string, object>("email", user.Email));
            DataTable result = DataServiceMySql.GetDataTable($"SELECT ba.id  FROM business_associate ba WHERE baEmail = @email", UserService.GetAPIConnectionString(), baparams);
            if (result != null && result.Rows.Count > 0)
            {
                DataRow dr = result.Rows[0];
                string baId = dr["id"].ToString();

                e.Command.Parameters["baId"].Value = baId;
            }
            if (user.AreaId > 0)
                e.Command.Parameters["areaId"].Value = user.AreaId;
            hidFilterType.Value = FilterType.ToString();
            e.Command.Parameters["filterType"].Value = FilterType;
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
            Button btn = (Button)sender;
            string areaName = hidAreaName.Value;

            if (user.AreaId > 0 && roid > 0)
            {
                List<KeyValuePair<String, Object>> delegateParams = new List<KeyValuePair<string, object>>();
                delegateParams.Add(new KeyValuePair<string, object>("areaId", user.AreaId));
                delegateParams.Add(new KeyValuePair<string, object>("roId", selRO.Text));
                delegateParams.Add(new KeyValuePair<string, object>("areaName", areaName));
                delegateParams.Add(new KeyValuePair<string, object>("leadId", hidleadId.Value));

                string updateSql = "UPDATE finascop_crm_lead SET assignedRO=@roId, areaId=@areaId, areaName=@areaName where id = @leadId";
                DataServiceMySql.ExecuteSql(updateSql, Service.UserService.GetAPIConnectionString(), delegateParams);
                Common.ShowToastifyMessage(Page, "Data updated successfully");
                gvLead.DataBind();
            }

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
                Common.ShowCustomAlert(this.Page, "Loading failed", "Invalid selection or the record is not existing", false, "/Business/AssociateLeads");
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
                Common.ShowCustomAlert(this.Page, "Loading failed", "Invalid selection or the record is not existing", false, "/Business/AssociateLeads");
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
                ShowSuccess("Success!", "Converted to Prospect!!", "/Business/AssociateLeads");
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
            List<KeyValuePair<String, Object>> scheduleParams = new List<KeyValuePair<string, object>>();
            scheduleParams.Add(new KeyValuePair<string, object>("leadId", hidleadId.Value));
            scheduleParams.Add(new KeyValuePair<string, object>("date", txtDate.Text));
            scheduleParams.Add(new KeyValuePair<string, object>("time", ddlTime.Text));
            scheduleParams.Add(new KeyValuePair<string, object>("remarks", txtRemarks.Text));
            string stringSql = $"INSERT INTO crm_meetings(crmUserId, meetingDate, meetingTime, meetingRemarks) " +
                            $"VALUES(@leadId, @date, @time, @remarks)";
            DataServiceMySql.ExecuteSql(stringSql, UserService.GetAPIConnectionString(), scheduleParams);
            gvLead.DataBind();
        }

        protected void btnAction_Click(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;
            hidleadId.Value = (lbtn.Attributes["leadid"]);
            string leadId = hidleadId.Value;

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

        protected void btnCreateLComm_Click(object sender, EventArgs e)
        {
            try
            {
                string currentdatetime = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
                List<KeyValuePair<string, object>> leadparams = new List<KeyValuePair<string, object>>();
                leadparams.Add(new KeyValuePair<string, object>("leadId", hidleadId.Value));
                leadparams.Add(new KeyValuePair<string, object>("action", selAction.Text));
                leadparams.Add(new KeyValuePair<string, object>("mode", selMode.Text));
                leadparams.Add(new KeyValuePair<string, object>("remarks", txtCommRemarks.Text));
                leadparams.Add(new KeyValuePair<string, object>("datetime", currentdatetime));
                string strSql = $"INSERT INTO finascop_crm_communication(crle_id, crca_id, crcm_id, crmc_remark, crmc_Communication_Time) " +
                            $"VALUES(@leadId, @action, @mode, @remarks, @datetime)";
                DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), leadparams);
                // Clear form fields
                selAction.SelectedIndex = -1; // Clears the selected index
                selMode.SelectedIndex = -1;
                txtCommRemarks.Text = string.Empty;
                rptDetails.DataBind();
                ScriptManager.RegisterStartupScript(this, this.GetType(), "closeSecondPopup", "$('#Create_communication').modal('hide');", true);
                //ScriptManager.RegisterStartupScript(this, this.GetType(), "clearFormFields", "clearSecondPopupForm();", true);
                ScriptManager.RegisterStartupScript(this, this.GetType(), "showFirstPopup", "$('#Communication').modal('show');", true);
                //ScriptManager.RegisterStartupScript(this, this.GetType(), "closeClearShowPopup", @"
                //    $('#Create_communication').modal('hide');
                //    clearSecondPopupForm();
                //    $('#Communication').modal('show');
                //", true);
                Common.ShowToastifyMessage(Page, "Communication saved successfully");
            }
            catch
            {

            }
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
                ShowSuccess("Success!", "Email Added Successfully!!", "/Business/AssociateLeads");
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

        protected void btnedit_Click(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;
            string leadId = (lbtn.Attributes["leadid"]);
            Response.Redirect($"AsstLeadSettings.aspx?id={leadId}");


        }
    }
}


