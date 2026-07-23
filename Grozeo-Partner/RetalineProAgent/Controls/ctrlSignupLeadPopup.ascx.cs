using RetalineProAgent.Core.Services;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Controls
{
    public partial class ctrlSignupLeadPopup : System.Web.UI.UserControl
    {
        protected void Page_Load(object sender, EventArgs e)
        {

        }

        protected void btnSignupLeadSubmit_Click(object sender, EventArgs e)
        {
            try
            {
                List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                prms.Add(new KeyValuePair<string, object>("name", txtLeadName.Text));
                prms.Add(new KeyValuePair<string, object>("mobile", txtLeadMobile.Value));
                prms.Add(new KeyValuePair<string, object>("location", txtLocation.Text));
                prms.Add(new KeyValuePair<string, object>("retailcategory", selBCategory.Text));
                prms.Add(new KeyValuePair<string, object>("glat", hidLeadLat.Value));
                prms.Add(new KeyValuePair<string, object>("glng", hidLeadLong.Value));

                //string sql = "INSERT INTO finascop_crm_enquiry(crme_name, crme_mobile, crme_description) VALUES(@name, @mobile, @desc)";
                string sql = "INSERT INTO finascop_crm_contact(crco_orgName, crco_location, crco_indMobile, retailCategory, crco_type, crco_mode, glatitude, glongitude) VALUES(@name, @location, @mobile, @retailcategory, 1, 1, @glat, @glng)";
                DataServiceMySql.ExecuteSql(sql, "", prms);

                RetalineProAgent.Service.Common.ShowCustomAlert(this.Page, "Success", "Your enquiry has been submitted successfully.", true, "/signup");
            }catch(Exception ex)
            {
                RetalineProAgent.Service.Common.ShowCustomAlert(this.Page, "Error", "There is a technical error happened.", false);
            }

        }
    }
}