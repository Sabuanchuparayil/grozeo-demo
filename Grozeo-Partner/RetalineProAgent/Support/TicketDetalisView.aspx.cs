using RetalineProAgent.Core.Services;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Support
{
    public partial class TicketDetalisView : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            try
            {
                string ticketid = (Request.QueryString["ticketId"].ToString());
                List<KeyValuePair<string, object>> sqlda = new List<KeyValuePair<string, object>>();
                sqlda.Add(new KeyValuePair<string, object>("id", ticketid));
                string ticket = "SELECT st.ticketId,st.ticketNumber  FROM `support_ticket` st WHERE ticketId=@id";
                var tickedetails = DataServiceMySql.GetDataTable(ticket, Service.UserService.GetAPIConnectionString(), sqlda);
                if (tickedetails != null && tickedetails.Rows.Count > 0)
                {
                    ltrticketNo.Text = tickedetails.Rows[0]["ticketNumber"].ToString();
                }
            }
            catch
            {

            }
           
        }
        protected void btnrepones_Click(object sender, EventArgs e)
        {

        }
    }
}