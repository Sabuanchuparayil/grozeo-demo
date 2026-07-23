using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Navigations;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Globalization;
using System.Linq;
using System.Net.NetworkInformation;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class LeadSettings: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            //SDSBranches.ConnectionString = DataService.APIConnectionString(Service.UserService.GetAPIConnectionString());

            if (!IsPostBack && !String.IsNullOrEmpty(Request.QueryString["id"]))
            {
                LoadStoreInfo();
            }
        }

        private void LoadStoreInfo()
        {
            int leadCustId = Convert.ToInt32(Request.QueryString["id"]);
            if (leadCustId > 0)
            {
                List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                prms.Add(new KeyValuePair<string, object>("id", leadCustId));
                prms.Add(new KeyValuePair<string, object>("storegroup", this.CurrentUser.APIStoreId));
                DataTable dt = DataServiceMySql.GetDataTable($"SELECT * FROM merchant_contact WHERE storeGroup=@storegroup and id=@id", parmeters: prms);
                if (dt != null && dt.Rows.Count > 0)
                {
                    DataRow da = dt.Rows[0];
                    txtName.Text = da["name"].ToString();
                    txtMobile.Text = da["phone"].ToString();
                    txtEmail.Text = da["email"].ToString();
                    return;
                }
            }
            Common.ShowCustomAlert(this.Page, "Invalid contact", "The contact is not available or there is a technical error happened!", false, "/tenant/leads");
        }
        protected void btnAdd_Click(object sender, EventArgs e)
        {
            if(string.IsNullOrEmpty(txtName.Text) && string.IsNullOrEmpty(txtMobile.Text) && string.IsNullOrEmpty(txtEmail.Text))
            {
                Common.ShowCustomAlert(this.Page, "Missing required data", "Please input the data to submit", false);
                return;
            }

            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("storegroup", this.CurrentUser.APIStoreId));
            prms.Add(new KeyValuePair<string, object>("name", txtName.Text));
            prms.Add(new KeyValuePair<string, object>("email", txtEmail.Text));
            prms.Add(new KeyValuePair<string, object>("phone", txtMobile.Text));

            string sql = "INSERT INTO merchant_contact(name, email, phone, storeGroup) VALUES(@name, @email, @phone, @storegroup)";
            string msgResult = "Lead created successfully!";
            string checkSql = "SELECT * FROM merchant_contact WHERE storegroup = @storegroup and (email like @email or phone like @phone)";
            if (!String.IsNullOrEmpty(Request.QueryString["id"]))
            {
                prms.Add(new KeyValuePair<string, object>("id", Request.QueryString["id"]));
                sql = "UPDATE merchant_contact SET name = @name, email = @email, phone = @phone WHERE storeGroup = @storegroup AND id=@id";
                msgResult = "Contact updated successfully!";
                checkSql += " AND id <> @id";
            }
            var dtCheckResult = DataServiceMySql.GetDataTable(checkSql, parmeters: prms);
            if(dtCheckResult != null && dtCheckResult.Rows.Count > 0)
            {
                Common.ShowCustomAlert(this.Page, "Validation failed", "The email or phone number already exists. Please check the customers list and make sure that the data is not duplicated", false);
                return;
            }

            int result = DataServiceMySql.ExecuteSql(sql, parmeters: prms);
            // Activitylog
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            string Source = strUrl;
            int storegroupid = this.CurrentUser.APIStoreId;

            string Users = this.CurrentUser.Email;
            string storegroup = (this.CurrentUser.APIStoreId).ToString();
            string name = txtName.Text;
            string email = txtEmail.Text;
            string phone = txtMobile.Text;            
            var items = new[]
                {
                    new { Key = "Store Group", Value = storegroup },
                    new { Key = " Name", Value = name },
                    new { Key = "Email", Value = email },
                    new { Key = "Phone", Value = phone },                    
                    };
            string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
            var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);

            Common.ShowCustomAlert(this.Page, "Success", msgResult, true, "/tenant/leads");
        }
    }
}
    




