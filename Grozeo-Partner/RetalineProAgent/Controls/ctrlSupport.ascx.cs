using Google.Protobuf.WellKnownTypes;
using RetalineProAgent.Core.BussinessModel.Finance;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Finance;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Data.SqlTypes;
using System.IO;
using System.Linq;
using System.Text;
using System.Text.RegularExpressions;
using System.Threading.Tasks;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Security.Cryptography;
using System.Configuration;

namespace RetalineProAgent.Controls
{
    public partial class ctrlSupport : Base.BasePartnerUserControl
    {
        private bool ShowSupportView
        {
            get
            {
                if (ViewState["CTRSHOWSUPPORT"] == null)
                    return false;
                return Convert.ToBoolean(ViewState["CTRSHOWSUPPORT"]);
            }
            set
            {
                ViewState["CTRSHOWSUPPORT"] = value;
            }
        }

        protected string TawkToPropertyId = ConfigurationManager.AppSettings.Get("TawkToPropertyId ");
        protected string TawkToWidgetId = ConfigurationManager.AppSettings["TawkToWidgetId"];
        protected string TawkToSiteAPIKey = ConfigurationManager.AppSettings.Get("TawkToSiteAPIKey");

        protected string UserId;
        protected string UserName;
        protected string UserEmail;
        protected string Hash;

        protected void Page_Load(object sender, EventArgs e)
        {
            ltrLoadScript.Text = "";
            if (ShowSupportView)
            {
                ltrLoadScript.Text = "$('.template-options-wrapper').toggleClass('show');";//"showSupport('/Support/Tickets.aspx');";
                // call script to expand the support view.
                ShowSupportView = false;
            }

        }

        protected void Page_PreRender(object sender, EventArgs e)
        {
            if (ShowSupportView)
            {
                ltrLoadScript.Text = "$('.template-options-wrapper').toggleClass('show');"; // "showSupport('/Support/Tickets.aspx');";
                                                                                            // call script to expand the support view.
                ShowSupportView = false;
            }

          

        }
        protected void lbtnSubmit_Click(object sender, EventArgs e)
        {
           
            int supportType = 0;
            int storeId = 0;
            string filePath = "", generatedFileName = "";
            // Check if a file is selected
            if (actual_btn.PostedFile != null && actual_btn.PostedFile.ContentLength > 0)
            {
                //filePath = FileService.AttachFileToS3(actual_btn.PostedFile.InputStream, actual_btn.PostedFile.FileName);
                try
                {
                    var result = FileService.AttachFileToS3(actual_btn.PostedFile.InputStream, actual_btn.PostedFile.FileName);
                    generatedFileName = result.fileName;
                    filePath = result.fileUrl;
                }
                catch (Exception ex)
                {
                    // Handle exceptions appropriately
                    Console.WriteLine($"Error: {ex.Message}");
                }

            }

            if (this.Page.Master is Tenant.TenantMaster)
            {
                supportType = 4;
                storeId = this.CurrentUser.APIStoreId;
            }
            else if (this.Page.Master is Business.BusinessMaster)
            {
                supportType = 5;
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
                storeId = Convert.ToInt32(baId);
            }


            string phone = this.CurrentUser.Phone;
            string email = this.CurrentUser.Email;
            string name = this.CurrentUser.FullName;
            string title = txtSupportNeeded.Text;
            string description = txtRequirement.Text;
            int supportUnit =Convert.ToInt32(selSupportUnit.SelectedValue);

            try
            {
                var result = Core.Services.APIService.Support(supportType, phone, email, name, title, description, storeId, supportUnit, generatedFileName, filePath);

               
                if (result != null && result.Status  == "ok")
                {
                    string strMsg = result.Message;
                    string pattern = @"#([A-Z\d]+)";
                    // Match the pattern in the text
                    Match match = Regex.Match(strMsg, pattern);
                    string ticketNumber = match.Groups[1].Value;
                    ltrticket.Text = ticketNumber;                  
                    plcsupportticket.Visible=true;
                    plcsupport.Visible=false;
                    hidSupportId.Value= Convert.ToString(this.CurrentUser.APIStoreId);
                }
                else
                {
                    Common.ShowToastifyMessage(this.Page, "Failed to create ticket.", "danger");
                }
                txtSupportNeeded.Text = string.Empty;
                txtRequirement.Text = string.Empty;
                selSupportUnit.SelectedIndex = 0;
                // Activitylog
                String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                string Source = strUrl;
                int storegroupid = this.CurrentUser.APIStoreId;
                string Users = this.CurrentUser.Email;

                int support_Type = supportType;
                string Phone = this.CurrentUser.Phone; ;
                string Name = this.CurrentUser.FullName;
                string Title = txtSupportNeeded.Text;
                string Descriptions = txtRequirement.Text; ;
                int SupportUnit = selSupportUnit.SelectedIndex;
                string Result = result.ToString();
                string APIname = "Support";
                var items = new[]
                    {
                    new { Key = "Support Type", Value =Convert.ToString(supportType) },
                    new { Key = "Phone", Value =Phone },
                    new { Key = "Name", Value = Name },
                    new { Key = "Title", Value = Title },
                    new { Key = "Description", Value = Descriptions },
                    new { Key = "SupportUnit", Value =Convert.ToString(SupportUnit) },
                    new { Key = "APIname", Value =APIname },
                     new { Key = "Result", Value =Result },

                    };
                string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);
                plcevisible.Visible=false;
                plcsupport.Visible = false;
                ShowSupportView = true;            
            }
            catch
            {
                Common.ShowToastifyMessage(this.Page, "Failed to create ticket.", "danger");
            }
        }

        protected void SDSSupportUnit_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            int supportType = 0;

            if (this.Page.Master is Tenant.TenantMaster)
            {
                supportType = 4;
            }
            else if (this.Page.Master is Business.BusinessMaster)
            {
                supportType = 5;
            }
            e.Command.Parameters["typeId"].Value = supportType;
        }

        protected void btnsupport_Click(object sender, EventArgs e)
        {
            plcsupportticket.Visible = false;
            plcsupport.Visible = true;
            plcevisible.Visible = false;
            support.Style["display"] = "block";
            supportsave.Style["display"] = "block";

        }

        public static string GenerateHmacSha256(string userId, string TawkToSiteAPIKey)
        {
            using (var hmac = new HMACSHA256(Encoding.UTF8.GetBytes(TawkToSiteAPIKey)))
            {
                byte[] hash = hmac.ComputeHash(Encoding.UTF8.GetBytes(userId));
                return BitConverter.ToString(hash).Replace("-", "").ToLower();
            }
        }


    }
}