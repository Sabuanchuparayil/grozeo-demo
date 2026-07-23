using Google.Protobuf.WellKnownTypes;
using Newtonsoft.Json;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.HelperServices;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Dynamic;
using System.Linq;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Business
{
    public partial class Prospects : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
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
        protected void SDSRetailerLeads_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
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
            string areaName = btn.Attributes["areaName"];

            if (user.AreaId > 0 && roid > 0)
            {
                List<KeyValuePair<String, Object>> delegateParams = new List<KeyValuePair<string, object>>();
                delegateParams.Add(new KeyValuePair<string, object>("areaId", user.AreaId));
                delegateParams.Add(new KeyValuePair<string, object>("roId", selRO.Text));
                delegateParams.Add(new KeyValuePair<string, object>("areaName", areaName));
                delegateParams.Add(new KeyValuePair<string, object>("leadId", hidleadId.Value));

                string updateSql = "UPDATE finascop_crm_prospect SET assignedRO=@roId, areaId=@areaId, areaName=@areaName where leadId = @leadId";
                DataServiceMySql.ExecuteSql(updateSql, Service.UserService.GetAPIConnectionString(), delegateParams);
                Common.ShowToastifyMessage(Page, "Data updated successfully");
                gvRetailers.DataBind();
            }

        }


        private void ShowSuccess(string title, string content, string redirect = "")
        {
            System.Type cstype = this.GetType();
            String csname1 = "PopupScript";
            ClientScriptManager cs = Page.ClientScript;
            ltrSuccessTitle.Text = title;
            ltrSuccessContent.Text = content;

            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> $('#modaldemo4').modal('show'); {(string.IsNullOrEmpty(redirect) ? "" : "$('#modaldemo4').on('hidden.bs.modal', function (e) {window.location.href = '" + redirect + "'; });")}</");
            cstext1.Append("script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
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
            gvRetailers.DataBind();
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
                        if (invitationSentStatus == 0)
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
                                   

                                    List<KeyValuePair<String, Object>> prospectparams = new List<KeyValuePair<string, object>>();
                                    prospectparams.Add(new KeyValuePair<string, object>("prospectId", prospectId));
                                    prospectparams.Add(new KeyValuePair<string, object>("updatedOn", prospectId));
                                    prospectparams.Add(new KeyValuePair<string, object>("invitationSent", 1));
                                    prospectparams.Add(new KeyValuePair<string, object>("invitationLink", prospectResult));
                                    string updateSql = "UPDATE finascop_crm_prospect SET crpr_UpdatedOn=@updatedOn, invitationSent=@invitationSent, invitationLink=@invitationLink where id = @prospectId";
                                    DataServiceMySql.ExecuteSql(updateSql, Service.UserService.GetAPIConnectionString(), prospectparams);
                                    Common.ShowToastifyMessage(Page, "Invitation sent successfully");
                                }
                                else
                                {
                                    Common.ShowToastifyMessage(this.Page, "Failed, the email or mobile conflicted with an existing record. Please try with another email and mobile!", "danger");
                                }

                            }
                            catch (Exception ex) { }
                        }

                        else
                        {
                            Common.ShowToastifyMessage(this.Page, "Invitation already sent.", "danger");
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

        protected void btnStatusUpt_Click(object sender, EventArgs e)
        {
            Button btn = (Button)sender;
            string prospectId = hidProspectId.Value;
            if (btn != null)
            {
                List<KeyValuePair<String, Object>> prospectparams = new List<KeyValuePair<string, object>>();
                prospectparams.Add(new KeyValuePair<string, object>("prospectId", hidProspectId.Value));
                prospectparams.Add(new KeyValuePair<string, object>("status", selStatus.Text));
                prospectparams.Add(new KeyValuePair<string, object>("date", txtDatePicker.Text));
                prospectparams.Add(new KeyValuePair<string, object>("remarks", txtRemarks.Text));

                string strSql = $"INSERT INTO finascop_crm_communication(prospectId, crmu_id, crmc_remark) " +
                            $"VALUES(@prospectId, @status, @remarks)";
                DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), prospectparams);

                string strUpdateSql = $"UPDATE finascop_crm_prospect SET crmuId=@status, crmFollowupDate=@date, crmRemarks=@remarks WHERE id = @prospectId";
                DataServiceMySql.ExecuteSql(strUpdateSql, UserService.GetAPIConnectionString(), prospectparams);
                ShowSuccess("Success!", "Status updated!!", "/Business/Prospects");
            }
        }

        protected void btnAction_Click(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;
            hidProspectId.Value = (lbtn.Attributes["prospectid"]);
            string prospectId = hidProspectId.Value;

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
        protected void btnCreatePComm_Click(object sender, EventArgs e)
        {
            try
            {
                string currentdatetime = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
                List<KeyValuePair<string, object>> prospectparams = new List<KeyValuePair<string, object>>();
                prospectparams.Add(new KeyValuePair<string, object>("prospectId", hidProspectId.Value));
                prospectparams.Add(new KeyValuePair<string, object>("action", selAction.Text));
                prospectparams.Add(new KeyValuePair<string, object>("mode", selMode.Text));
                prospectparams.Add(new KeyValuePair<string, object>("remarks", txtCommRemarks.Text));
                prospectparams.Add(new KeyValuePair<string, object>("datetime", currentdatetime));
                string strSql = $"INSERT INTO finascop_crm_communication(prospectId, crca_id, crcm_id, crmc_remark, crmc_Communication_Time) " +
                            $"VALUES(@prospectId, @action, @mode, @remarks, @datetime)";
                DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), prospectparams);
                // Clear form fields
                selAction.SelectedIndex = -1; // Clears the selected index
                selMode.SelectedIndex = -1;
                txtCommRemarks.Text = string.Empty;
                rptDetails.DataBind();
                ScriptManager.RegisterStartupScript(this, this.GetType(), "closeSecondPopup", "$('#Create_communication').modal('hide');", true);
                ScriptManager.RegisterStartupScript(this, this.GetType(), "showFirstPopup", "$('#Communication').modal('show');", true);
                Common.ShowToastifyMessage(Page, "Communication saved successfully");
            }
            catch
            {

            }
        }
    }
}