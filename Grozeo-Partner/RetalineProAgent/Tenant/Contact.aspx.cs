using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class Contact : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            return;


            ltrEmail.Text = String.Format("Email: {0}", ConfigurationManager.AppSettings.Get("FromEmail"));
            string addr = "", email = "", phone = "";
            List<KeyValuePair<string, object>> input = new List<KeyValuePair<string, object>>();
            input.Add(new KeyValuePair<string, object>("storeGroup", this.CurrentUser.APIStoreId));
            string sql = "select StoreAddress, StoreEmail, StorePhone from Store where TenantId=1 or TenantId=@storeGroup order by TenantId desc";
            var tblContact = DataService.GetDataTable(sql, parmeters: input);
            if(tblContact != null && tblContact.Rows.Count> 0)
            {
                var dr = tblContact.Rows[0];
                try {
                    addr = dr["StoreAddress"].ToString();
                    email = dr["StoreEmail"].ToString();
                    phone = dr["StorePhone"].ToString();
                } catch { }

                if(String.IsNullOrEmpty(addr) && tblContact.Rows.Count > 1)
                {
                    dr = tblContact.Rows[1];
                    try
                    {
                        addr = dr["StoreAddress"].ToString();
                        email = dr["StoreEmail"].ToString();
                        phone = dr["StorePhone"].ToString();
                    }
                    catch { }
                    if (!String.IsNullOrEmpty(addr))
                    {
                        ltrAddress.Text = addr;
                        ltrEmail.Text = email;
                        ltrPhone.Text = phone;
                        ltrUKAddr.Visible = false;
                    }
                }
            }
        }

        protected void btnSubmit_Click(object sender, EventArgs e)
        {
            string strUrl = ConfigurationManager.AppSettings.Get("CrmContactUrl");
            if (String.IsNullOrEmpty("strUrl"))
            {
                Common.ShowCustomAlert(this.Page, "Failure", "Submit function is not enabled.", false);
                return;
            }


            List<KeyValuePair<string, string>> data = new List<KeyValuePair<string, string>>();
            data.Add(new KeyValuePair<string, string>("email", inputEmail.Value));
            data.Add(new KeyValuePair<string, string>("phone", inputPhone.Value));
            data.Add(new KeyValuePair<string, string>("message", inputMessage.Value));
            data.Add(new KeyValuePair<string, string>("tenantid", this.CurrentUser.APIStoreId.ToString()));
            data.Add(new KeyValuePair<string, string>("inputName", inputName.Value));

            //var val=new KeyValuePair<string, string>()
            //var requestParams = new Dictionary<string, object>
            //{
            //    { "email", email},
            //    { "phone", phone},
            //    { "message", message }
            //};
            APIService.SubmitForm(strUrl, data);
            Common.ShowCustomAlert(this.Page, "Contact submitted", "Your input has been submitted", true, "/Tenant/contact");

        }
    }
}