using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Finance
{
    public partial class DetailedGSTTCSReport : Base.BasePartnerPage
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
    }
}