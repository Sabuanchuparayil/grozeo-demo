using Azure.Core;
using RetalineProAgent.Core.Services;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.HtmlControls;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Finance
{
    public partial class settlementdetails : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            try
            {
                string orderid = (Request.QueryString["order_order_id"]);
                string storeref = (Request.QueryString["storeRefId"]);
                List<KeyValuePair<string, object>> sqldaRefId = new List<KeyValuePair<string, object>>();
                sqldaRefId.Add(new KeyValuePair<string, object>("Refid", storeref));
                sqldaRefId.Add(new KeyValuePair<string, object>("orderid", orderid));
                string orderdetailes = "SELECT bg.storeRefId, rc.order_order_id,DATE(order_confirmed_on) AS order_confirmed_on,DATE(quor_DeliveredTime) AS quor_DeliveredTime,IFNULL(DATE(rc.settlement_date),CURDATE()) AS settlement_date,DATE(quor_DeliveryConfTime) AS quor_DeliveryConfTime,br_name,so.amount_due,created_date,so.order_id FROM  merchant_settlements_order so " +
                    $" INNER JOIN  merchant_settlements ms ON so.ms_ref_id= ms.ref_id  INNER JOIN `retaline_customer_order` rc  ON rc.order_id=so.order_id INNER JOIN finascop_branch fb ON rc.order_branch_id=fb.br_id " +
                    $" INNER JOIN settlementDays_master ON fb.br_sdId = settlementDays_master.sdId " +
                    $" INNER JOIN finascop_branch_group bg ON bg.store_group_id=br_storeGroup" +
                    $" INNER JOIN finascop_stock_transfer_order ON fstr_id = rc.order_id AND fsto_orderType = 1 " +
                    $" INNER JOIN qugeo_order ON quor_TransferOrder_id = fsto_id where bg.storeRefId=@Refid and order_order_id=@orderid";
                var dtposting = DataServiceMySql.GetDataTable(orderdetailes, parmeters: sqldaRefId);
                if (dtposting != null && dtposting.Rows.Count > 0)
                {
                    var postingdetails = dtposting.Rows[0];
                    lborderno.Text = postingdetails["order_order_id"].ToString(); ;
                    lborderdate.Text = ((DateTime)postingdetails["order_confirmed_on"]).ToString("dd MMM yyyy");
                    lbconfirmed.Text = ((DateTime)postingdetails["quor_DeliveryConfTime"]).ToString("dd MMM yyyy");
                    lbsettledate.Text = ((DateTime)postingdetails["settlement_date"]).ToString("dd MMM yyyy");
                    lbsettleamount.Text = string.Format("{0:n2}", postingdetails["amount_due"]);
                }
                ltrsettlementhead.Text = Request.QueryString["type"] == "2" ? "ledger posting" : "settlement calculation";
            }
            catch (Exception ex)
            {
                

            }
        }
        protected void lvposting_DataBound(object sender, EventArgs e)
        {

            try
            {
                string orderid = (Request.QueryString["order_order_id"]);
                string storeref = (Request.QueryString["storeRefId"]);
                List<KeyValuePair<string, object>> sqldaorderRefId = new List<KeyValuePair<string, object>>();
                sqldaorderRefId.Add(new KeyValuePair<string, object>("Refid", storeref));
                sqldaorderRefId.Add(new KeyValuePair<string, object>("order_id", orderid));
                HtmlTableRow trTotalRow = lvposting.FindControl("trTotalRow") as HtmlTableRow;
                HtmlTableRow trSettleRow = lvposting.FindControl("trSettleRow") as HtmlTableRow;
                string postingdetails = "SELECT SUM(CASE WHEN t.isDebtor = 1 THEN t.amount ELSE 0 END) AS dr_amount, SUM(CASE WHEN t.isDebtor = 0 THEN t.amount ELSE 0 END) AS cr_amount FROM transactions t INNER JOIN data_entry d ON t.data_entry_id = d.id INNER JOIN ledger l ON l.id = t.ledger_id WHERE d.entity_id =@order_id  AND l.refId = @Refid;";
                string cssClass = Request.QueryString["type"] == "2" ? "d-none" : "";
                trTotalRow.Attributes["class"] = cssClass;
                trSettleRow.Attributes["class"] = cssClass;
                var amount = DataService.GetDataTable(postingdetails, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqldaorderRefId);
                Literal ltrdrtotal = (Literal)lvposting.FindControl("ltrdr");
                Literal ltrcrtotal = (Literal)lvposting.FindControl("ltrcr");
                Literal ltrsettlecrtotal = (Literal)lvposting.FindControl("ltramonttobepaid");

                if (ltrdrtotal != null && ltrcrtotal != null)
                {
                    var total = amount.Rows[0];
                    decimal drAmount = total["dr_amount"] != DBNull.Value ? Convert.ToDecimal(total["dr_amount"]) : 0m;
                    decimal crAmount = total["cr_amount"] != DBNull.Value ? Convert.ToDecimal(total["cr_amount"]) : 0m;
                    ltrdrtotal.Text = drAmount.ToString("0.00");
                    ltrcrtotal.Text = crAmount.ToString("0.00");
                    decimal settlement = crAmount - drAmount;
                    ltrsettlecrtotal.Text = settlement.ToString("0.00");

                }
            }
            catch (Exception ex)
            {
            }



        }

        protected void SDSRelationshipOfficer_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            if (Request.QueryString["type"] == "1")
            {
                e.Command.CommandText = @"select d.entity_id, t.data_entry_id,d.createdOn,d.docSerialNo,(select vt.name from voucher_type vt where d.voucher_type_id=vt.id) as Voucher,
                d.event, t.particulars,CASE WHEN [isDebtor] = 1 THEN  t.amount  END AS dr_amount,CASE WHEN [isDebtor] =0 THEN  t.amount 
                END AS cr_amount  from transactions t inner join data_entry d on t.data_entry_id=d.id inner join [ledger] l on l.id=t.ledger_id
                where d.[entity_id] = @order_id and l.refId=@storeRefId";
            }
            else
            { 
                e.Command.CommandText = @"with x as(
                SELECT de.entity_id,data_entry_id FROM transactions tr 
	                INNER JOIN  data_entry de ON tr.data_entry_id =de.id 
	                inner join [ledger] l on l.id=tr.ledger_id where de.[entity_id] = @order_id 
                )
                select de.entity_id, tr.data_entry_id,de.createdOn,de.docSerialNo,(select vt.name from voucher_type vt where de.voucher_type_id=vt.id) as Voucher,
                                                                de.event, tr.particulars,CASE WHEN [isDebtor] = 1 THEN  tr.amount  END AS dr_amount,CASE WHEN [isDebtor] =0 THEN  tr.amount 
                                                                END AS cr_amount 
                from transactions tr INNER JOIN  data_entry de ON tr.data_entry_id =de.id inner join x on x.data_entry_id = tr.data_entry_id  
				group by  tr.data_entry_id,de.createdOn,de.docSerialNo,de.entity_id,de.voucher_type_id,de.event,tr.particulars,tr.[isDebtor],tr.amount
                ";

            }
        }

    }
}