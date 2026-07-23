using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Manage
{
    public partial class CustomDomain : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            DataTable dt = DataServiceMySql.GetDataTable("SELECT * FROM sys_configuration WHERE cfg_Name LIKE 'CUSTOM-DOMAIN-IP' OR cfg_Name LIKE 'CUSTOM-DOMAIN-TXT' limit 2", Service.UserService.GetAPIConnectionString());
            if (dt != null && dt.Rows.Count >= 2)
            {
                txtIP.Text = dt.Rows[0]["cfg_Value"].ToString();
                txtTXTRecord.Text = dt.Rows[1]["cfg_Value"].ToString();
            }
        }

        protected void btnUpdateDNSSettings_Click(object sender, EventArgs e)
        {
            if(String.IsNullOrEmpty(txtIP.Text) || string.IsNullOrEmpty(txtTXTRecord.Text))
            {
                Common.ShowToastifyMessage(this.Page, "Failure: IP and TXT records are required fields!", "danger");
                return;
            }
            string sql = "DELETE sys_configuration WHERE cfg_Name LIKE 'CUSTOM-DOMAIN-IP' OR cfg_Name LIKE 'CUSTOM-DOMAIN-TXT; INSERT INTO sys_configuration(cfg_Name, cfg_Value) VALUES('CUSTOM-DOMAIN-IP', @ip), ('CUSTOM-DOMAIN-TXT', @txt);";
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>> { new KeyValuePair<string, object>("ip", txtIP.Text), new KeyValuePair<string, object>("txt", txtTXTRecord.Text) };
            DataServiceMySql.ExecuteSqlWithTransaction(sql, Service.UserService.GetAPIConnectionString(), prms);
            Common.ShowCustomAlert(this.Page, "Success", "DNS settings updated successfully", true, "/manage/customdomain");

        }

        public string GetStatusText(object statusId)
        {
            string strStatus = "";
            try {
                int status = Convert.ToInt32(statusId);
                switch(status)
                {
                    case 0:
                        strStatus = "DNS pending";
                        break; 
                    case 1:
                        strStatus = "Completed";
                        break; 
                    case 2:
                        strStatus = "In Progress";
                        break; 
                    case 3:
                        strStatus = "SSL Pending";
                        break;

                }

            } catch { }

            return strStatus;
        }

    }
}