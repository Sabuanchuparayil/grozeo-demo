using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Finance
{
    public partial class Invoicesalesreport : System.Web.UI.Page
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            txtDateFrom.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
            txtDateTo.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
            //txtFromDate.Text = DateTime.Now.ToString("yyyy-MM-dd");
            //txtToDate.Text = DateTime.Now.ToString("yyyy-MM-dd");


            txtDateFrom.Text = DateTime.Now.AddDays(-15).ToString("yyyy-MM-dd");
            txtDateTo.Text = DateTime.Now.ToString("yyyy-MM-dd");
        }
        protected void Page_PreRender(object sender, EventArgs e)
        {
            txtDateFrom.Enabled = seldate.Text == "1";
            txtDateTo.Enabled = seldate.Text == "1";
            pnlDateRange.Enabled = seldate.Text == "1";
        }

        protected void btninvoice_Click(object sender, EventArgs e)
        {

            LinkButton lbtn = (LinkButton)sender;

            string date = (lbtn.Attributes["recid"]);
            DateTime dt = Convert.ToDateTime(date);
            hidValueHeadOrderId.Value = dt.ToString("yyy-MM-dd");
            string Id = hidValueHeadOrderId.Value;

            //popup Action
            string strAlertSCript = "$('#Pupaction').modal('show');";
            strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";
            System.Type cstype = this.GetType();
            String csname1 = "ShowConfirmPopup";
            ClientScriptManager cs = this.ClientScript;
            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
            cstext1.Append("script>");
            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }
    }
}