using RetalineProAgent.Service;
using RetalineProAgent.Core.BussinessModel.Store;
using System;
using System.Collections.Generic;
using System.Data;
using System.Text;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using RetalineProAgent.Core.Services;
using System.Web.UI.HtmlControls;
using System.IO;

namespace RetalineProAgent
{
    public partial class RelationshipOfficer: Base.BasePartnerPage
    {


        protected void Page_Load(object sender, EventArgs e)
        {
            
        }

        
        protected void gvRelationshipOfficer_DataBound(object sender, EventArgs e)
        {

        }

        protected void SDSRelationshipOfficer_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
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

        protected void btnUploadAppointment_Click(object sender, EventArgs e)
        {
            try
            {
                string appointmentUrl = UploadROFile(fileAppointmentLetter, "relationshipofficer/appointmentletter/");
                List<KeyValuePair<string, object>> roparams = new List<KeyValuePair<string, object>>();
                roparams.Add(new KeyValuePair<string, object>("roId", hiddenRoid.Value));
                if (hiddenRoStatus.Value == "6")
                {
                    roparams.Add(new KeyValuePair<string, object>("rostatus", 9));

                }
                else
                {
                    roparams.Add(new KeyValuePair<string, object>("rostatus", 10));
                }               
                roparams.Add(new KeyValuePair<string, object>("roremarks", "RO Appointed by Associate"));               
                roparams.Add(new KeyValuePair<string, object>("roAppointmentOrder", appointmentUrl));
                string strSql = $"INSERT INTO relational_officer_log(roId, roStatus, roRemarks, roAppointmentOrder) " +
                                    $"VALUES(@roId, @rostatus, @roremarks, @roAppointmentOrder) ;";
                strSql += $"update relationship_officer set roStatus=@rostatus where id=@roId";
                DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), roparams);

                ShowSuccess("Success!", "Appointment order uploaded successfully!!", "/Business/RelationshipOfficer");
            }
            catch
            {
                Common.ShowToastifyMessage(this.Page, "Execution failed" + "Execution failure No item matches the criteria. Please contact support for more details.", "danger");
            }
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

        protected void lbtnView_Click(object sender, EventArgs e)
        {
            LinkButton lbtnView = (LinkButton)sender;
            string roId = lbtnView.CommandArgument;
            string curentRostatus = lbtnView.Attributes["currentRoStatus"];
            var appointmentLetterData = GetAppointmentLetterDataFromDatabase(roId, curentRostatus);
            string appointmentLetterUrl = appointmentLetterData.Url;
            string fileType = appointmentLetterData.Type;

            ScriptManager.RegisterStartupScript(this, this.GetType(), "ShowAppointmentLetterModal", $"showAppointmentLetterModal('{appointmentLetterUrl}', '{fileType}');", true);
        }

        private (string Url, string Type) GetAppointmentLetterDataFromDatabase(string roId,string rostatus)
        {
            List<KeyValuePair<string, object>> roparams = new List<KeyValuePair<string, object>>();
            roparams.Add(new KeyValuePair<string, object>("roId", roId));
            roparams.Add(new KeyValuePair<string, object>("rostatus", rostatus));
            string appointmentURL = "";
            DataTable result = DataServiceMySql.GetDataTable($"SELECT roAppointmentOrder FROM relational_officer_log WHERE roId = @roId and roStatus=@rostatus", UserService.GetAPIConnectionString(), roparams);
            if (result != null && result.Rows.Count > 0)
            {
                DataRow dr = result.Rows[0];
                appointmentURL = dr["roAppointmentOrder"].ToString();
                if (!appointmentURL.EndsWith(".pdf"))
                {
                    appointmentURL += ".pdf";
                }
            }
            return (appointmentURL, "pdf");
        }

    }

}


