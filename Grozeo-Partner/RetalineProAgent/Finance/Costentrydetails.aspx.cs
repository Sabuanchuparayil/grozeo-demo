using RetalineProAgent.Core.Services;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Finance
{
    public partial class Costentrydetails : System.Web.UI.Page
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            if (Request.QueryString["transactions_id"] != null)
            {
                int transactionid = Convert.ToInt32(Request.QueryString["transactions_id"]);
                List<KeyValuePair<string, object>> sqldatavId = new List<KeyValuePair<string, object>>();
                sqldatavId.Add(new KeyValuePair<string, object>("transactionid", transactionid));
                string narration = $"SELECT de.id,tr.particulars,de.createdOn,tr.isDebtor,de.narration,tr.amount,de.docSerialNo,(SELECT name FROM voucher_type WHERE id=de.voucher_type_id)as name,de.amount,de.narration FROM data_entry de INNER JOIN [transactions] tr on [de].id = [tr].data_entry_id inner join  cost_centre_entries cc on tr.id=cc.transactions_id WHERE cc.transactions_id= @transactionid";
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
                    lbramounttotal.Text= dataEntry["amount"].ToString();
                    lbtotal.Text= dataEntry["particulars"].ToString();
                    if (Convert.ToInt32(dataEntry["isDebtor"]) == 1)
                    {
                        lbisdebtor.Text = " Debit";
                    }
                    else
                    {
                        lbisdebtor.Text = " Credit";
                    }



                }
            }
        }
        protected void lvDataEny_DataBound(object sender, EventArgs e)
        {
            if (Request.QueryString["transactions_id"] != null)
            {
                int transaction_id = Convert.ToInt32(Request.QueryString["transactions_id"]);
                List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
                sqldaId.Add(new KeyValuePair<string, object>("transactionid", transaction_id));
                string totalamount = $" SELECT SUM( CASE WHEN [isDebtor] = 1 THEN tr.amount END) AS  dr_sum, SUM( CASE WHEN [isDebtor] = 0 THEN tr.amount END) AS  cr_sum FROM cost_centre_entries tr WHERE transactions_id =@transactionid";
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