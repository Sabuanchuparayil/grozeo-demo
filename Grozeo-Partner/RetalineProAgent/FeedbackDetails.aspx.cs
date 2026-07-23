using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Threading.Tasks;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class FeedbackDetails: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            string feedbackId = Request.QueryString["id"];
            Service.User usr = this.CurrentUser;

            if (!String.IsNullOrEmpty(feedbackId))
            {
                string sql = $"SELECT fb_id,fb_mobile,fb_email,fb_comments FROM app_feedback WHERE fb_id = '{feedbackId}'";



                var tblItems = DataServiceMySql.GetDataTable(sql, UserService.GetAPIConnectionString());

                if (tblItems != null && tblItems.Rows.Count > 0)
                {
                    var tr = tblItems.Rows[0];
                    ltrMobile.Text = tr["fb_mobile"].ToString();
                    ltrEmail.Text = tr["fb_email"].ToString();
                    ltrCmmts.Text = tr["fb_comments"].ToString();
                }
            }
        }
    }
}