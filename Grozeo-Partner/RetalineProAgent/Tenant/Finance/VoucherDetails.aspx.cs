using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Tenant.Finance
{
    public partial class VoucherDetails : System.Web.UI.Page
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            if (Request.QueryString["id"] != null)
            {
                int dataEntryId = Convert.ToInt32(Request.QueryString["id"]);
                List<KeyValuePair<string, object>> sqldatavId = new List<KeyValuePair<string, object>>();
                sqldatavId.Add(new KeyValuePair<string, object>("dataEntrytId", dataEntryId));
                string narration = $"SELECT de.id,de.createdOn,de.narration,de.docSerialNo,(SELECT name FROM voucher_type WHERE id=de.voucher_type_id)as name,de.amount,de.narration FROM data_entry de WHERE de.id= @dataEntrytId";
                var payment = DataService.GetDataTable(narration, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqldatavId);
                if (payment != null && payment.Rows.Count > 0)
                {
                    var dataEntry = payment.Rows[0];
                    lbVoucher.Text = dataEntry["name"].ToString();
                    string date = dataEntry["createdOn"].ToString();
                    DateTime dt = Convert.ToDateTime(date);
                    lbDate.Text = dt.ToString("dd/MMM/yyyy");
                    lbVocherId.Text = dataEntry["docSerialNo"].ToString();
                    lbNarration.Text = dataEntry["narration"].ToString();


                }
            }
        }
        protected void lvDataEny_DataBound(object sender, EventArgs e)
        {
            if (Request.QueryString["id"] != null)
            {
                int dEntryId = Convert.ToInt32(Request.QueryString["id"]);
                List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
                sqldaId.Add(new KeyValuePair<string, object>("dataEntryvId", dEntryId));
                string totalamount = $" SELECT SUM( CASE WHEN [isDebtor] = 1 THEN tr.amount END) AS  dr_sum, SUM( CASE WHEN [isDebtor] = 0 THEN tr.amount END) AS  cr_sum FROM transactions tr WHERE data_entry_id =@dataEntryvId";
                var amount = DataService.GetDataTable(totalamount, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqldaId);
                Literal ltrdrtotal = (Literal)lvDataEny.FindControl("ltrDrTotal");
                Literal ltrcrtotal = (Literal)lvDataEny.FindControl("ltrCRTotal");
                if (ltrdrtotal != null && ltrcrtotal != null)
                {
                    var total = amount.Rows[0];
                    ltrdrtotal.Text = String.Format("{0:0.00}", total["dr_sum"]).ToString();
                    ltrcrtotal.Text = String.Format("{0:0.00}", total["cr_sum"]).ToString();
                }
            }
        }
    }
}