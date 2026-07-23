using NPOI.POIFS.Properties;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Data.SqlTypes;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using static RetalineProAgent.Finance.AutoPostingRules;

namespace RetalineProAgent.Business
{
    public partial class ViewActivity : System.Web.UI.Page
    {

        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                // Retrieve the crle_orgName parameter from the query string
                string crleOrgName = Request.QueryString["crle_orgName"];

                // Check if crle_orgName is not null or empty
                if (!string.IsNullOrEmpty(crleOrgName))
                {
                    // Set the modal title dynamically
                    ltrTitle.Text = Server.HtmlEncode(crleOrgName) + " - CRM Activities";
                }
                else
                {
                    // Handle the case when crle_orgName is not provided
                    ltrTitle.Text = "CRM Activities";
                }
            }
        }

        protected void SDSListDetails_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            string leadId = Request.QueryString["leadId"];
            e.Command.Parameters["leadId"].Value = leadId;
            //string prospectId = Request.QueryString["prospectId"];
            //e.Command.Parameters["prospectId"].Value = prospectId;
        }

        protected void SDSListDetails_Selected(object sender, SqlDataSourceStatusEventArgs e)
        {
            // Check if there is any data returned
            if (e.AffectedRows == 0)
            {
                // If no rows were returned, show the "No activity" message
                phNoData.Visible = true;
            }
            else
            {
                // If rows were returned, hide the "No activity" message
                phNoData.Visible = false;
            }
        }
    }
}