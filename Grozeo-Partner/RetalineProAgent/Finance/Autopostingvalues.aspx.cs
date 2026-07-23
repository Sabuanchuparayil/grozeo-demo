using RetalineProAgent.Core.Services;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Finance
{
    public partial class Autopostingvalues : System.Web.UI.Page
    {
        private DataTable dtEvents = null;
        private DataTable dthead = null;
        private DataTable dttype = null;
        private DataTable dtcostcentre = null;
        private DataTable dtledger = null;
        public class ColumnData
        {
            public string ColumnName { get; set; }
            public string ColumnValue { get; set; }

            public string splitdrvalue { get; set; }

            public string splitcrvalue { get; set; }

            public string costcentre { get; set; }
            public string costcentrevalue { get; set; }


            public string eventName { get; set; }
            public bool isCostCenter { get; set; } = false;
            public string ledgerId { get; set; }
            public bool isDr { get; set; } = false;

        }
        protected void Page_Load(object sender, EventArgs e)
        {

            txtdrsum.Text = "";
            txtcrsum.Text = "";
        }
        protected void Page_PreRender(object sender, EventArgs e)
        {


            if (IsPostBack && !String.IsNullOrEmpty(hidValueHeadOrderId.Value))
                ValueHeadInfoload(Convert.ToInt32(hidValueHeadOrderId.Value));
        }
        protected void btnaction_Click(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;
            ddltype.Text = "0";
            ddlfiter.Text = "-1";
            hidValueHeadOrderId.Value = (lbtn.Attributes["recid"]);
            int Id = Convert.ToInt32(hidValueHeadOrderId.Value);
            //LoadOrderAutoValueHeads(Id);
        }
        public List<ColumnData> LoadOrderValueHeads(int Id = 0, int eventId = 0, int recordType = 0, int ruleId = 0)
        {
            if (Id <= 0)
                return default;

            List<ColumnData> columnDataList = new List<ColumnData>();

            // Allocations and postings
            if (recordType != 3)
            {
                List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
                sqldaId.Add(new KeyValuePair<string, object>("order_id", Id));
                string Columnname = "SELECT  * FROM  `finascop_autoposting_calculations`fc INNER JOIN `finance_calculation_heads`fh ON fc.head_id=fh.id WHERE order_id=@order_id";
                var Columvalues = DataServiceMySql.GetDataTable(Columnname, Service.UserService.GetAPIConnectionString(), sqldaId);
                if (Columvalues != null && Columvalues.Rows.Count > 0)
                {

                    //string value = "SELECT ledger_id, CASE WHEN isDebtor=1 THEN COLUMN_NAME END dr_name ,CASE WHEN isDebtor=0 THEN COLUMN_NAME END cr_name FROM  finance_autoposting_settings fs INNER  JOIN finance_calculation_heads fc ON  fs.value_head_id=fc.id";
                    string sqlGetColumns = $"SELECT h.column_name, ledger_id, CASE WHEN isDebtor=1 THEN COLUMN_NAME END dr_name ," +
                        $"CASE WHEN isDebtor=0 THEN COLUMN_NAME END cr_name, h.type FROM finance_calculation_heads h " +
                        $"LEFT JOIN finance_autoposting_settings fs ON fs.value_head_id=h.id  " +
                        $"WHERE h.column_name IS NOT NULL AND ( @rectype = -1 OR (@rectype = 1 AND h.type = 'Posting') " +
                        $"OR (@rectype = 2 AND h.type = 'Computation') OR (@rectype = 3 AND h.type = 'Allocation') " +
                        $"OR (@rectype = 0 AND h.type <> 'Computation'))  AND ( @eventid = -1 OR (@eventid = 1 AND h.event = 'Checkout') OR (@eventid = 2 AND h.event = 'Order Placing') OR (@eventid = 3 AND h.event = 'Packing')" +
                        $" OR (@eventid = 4 AND h.event = 'Delivery Confirmation') OR (@eventid = 5 AND h.event = 'Pickup for Delivery') OR (@eventid = 6 AND h.event = 'Cancellation'))  AND (@ruleid <= 0 or autoposting_rule_id=@ruleid)";

                    List<KeyValuePair<string, object>> colparams = new List<KeyValuePair<string, object>>();
                    colparams.Add(new KeyValuePair<string, object>("rectype", recordType));
                    colparams.Add(new KeyValuePair<string, object>("eventid", eventId));
                    colparams.Add(new KeyValuePair<string, object>("ruleid", ruleId));
                    DataTable dtColumns = DataServiceMySql.GetDataTable(sqlGetColumns, parmeters: colparams);
                    var orderLedger = dtColumns.AsEnumerable().Select(r => new {
                        fieldName = r["column_name"].ToString(),
                        ledgerid = r["ledger_id"].ToString(),
                        drname = r["dr_name"].ToString(),
                        crname = r["cr_name"].ToString(),
                        coltype = r["type"].ToString()
                    }).ToList();

                    //DataRow row = Columvalues.Rows[0];
                    foreach (DataRow row in Columvalues.Rows)
                    {                 
                        string columnName = row["name"].ToString();
                        string columnValue = row["head_value"].ToString();
                        columnDataList.Add(new ColumnData
                        {
                            ColumnName = columnName,
                            ColumnValue = columnValue,
                            splitdrvalue = (orderLedger.Any(v => v.drname == columnName) ? columnValue : columnValue),
                            splitcrvalue = (orderLedger.Any(v => v.crname == columnName) ? columnValue : columnValue),
                            ledgerId = orderLedger.Any(v => v.drname == columnName || v.crname == columnName)
                                ? orderLedger.Where(v => v.drname == columnName || v.crname == columnName).FirstOrDefault().ledgerid : "", //strledgerId,
                        });
                    }
                }
            }

            // Cost centers
            if (recordType == 0 || recordType == 3)
            {
                string sql = "SELECT od.id, od.allocation_amount, em.name, cd.cost_centre_id, cf.ledger_id FROM order_cost_distribution_allocations od INNER JOIN cost_distribution_rule_new cd ON cd.cost_distribution_id= od.rule_id INNER JOIN finance_event_master em ON em.id = od.event_master_id  INNER JOIN cost_distribution_function cf ON cf.id = cd.cost_distribution_id WHERE order_id = @orderId GROUP BY od.id";
                List<KeyValuePair<string, object>> prmsCosts = new List<KeyValuePair<string, object>>();
                prmsCosts.Add(new KeyValuePair<string, object>("orderId", Id));
                // DataTable tblCostCenters = Execute sql to get the cost centers and values on the particular order.
                DataTable dtcostcentre = DataServiceMySql.GetDataTable(sql, parmeters: prmsCosts); //DataService.GetDataTable("select id,name from cost_centre", ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString);
                                                                                                   // For each tblCostCenters.Rows
                foreach (DataRow dr in dtcostcentre.Rows)
                {
                    string strledgerId = dr["ledger_id"].ToString();

                    columnDataList.Add(new ColumnData
                    {
                        ColumnValue = dr["allocation_amount"].ToString(),
                        //splitdrvalue = splitdrvalue,
                        //splitcrvalue = splitcrvalue,
                        ColumnName = dr["cost_centre_id"].ToString(),
                        costcentre = dr["cost_centre_id"].ToString(),
                        eventName = dr["name"].ToString(),
                        isCostCenter = true,
                        splitdrvalue = (columnDataList.Any(v => v.ledgerId == strledgerId && !string.IsNullOrEmpty(v.splitdrvalue)) ? dr["allocation_amount"].ToString() : ""),
                        splitcrvalue = (columnDataList.Any(v => v.ledgerId == strledgerId && !string.IsNullOrEmpty(v.splitcrvalue)) ? "" : dr["allocation_amount"].ToString()),
                        isDr = columnDataList.Any(v => v.ledgerId == strledgerId && v.isDr)
                    });
                }
            }

            //lvDataEny.DataSource = columnDataList;
            //lvDataEny.DataBind();
            //ValueHeadInfo(Id);

            return columnDataList;


        }
        private void ValueHeadInfoload(int Id)
        {
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("order_id", Id));
            string details = "SELECT fc.id,rc.order_order_id,fc.order_id,rc.created_at,total,rc.order_confirmed_on,br_storeGroup,(SELECT store_group_name FROM finascop_branch_group fb WHERE fb.store_group_id=br_storeGroup) AS storegroupname,CASE WHEN baType=1 AND baMode=1 THEN 'Direct Area Associate' WHEN baType=1 AND baMode=2 THEN 'Partner Area Associate'WHEN  baType=2 AND baMode=1 THEN 'Direct Business Associate' " +
                 $"WHEN baType=2 AND baMode=2 THEN 'Partner Business Associate' ELSE 'Not Avalible '  END AS areaassociate,CASE WHEN payment_mode = 1 THEN 'Pay On Delivery' WHEN payment_mode = 2 THEN  'Online Payment' WHEN payment_mode = 3 THEN 'Wallet'  WHEN payment_mode = 4 THEN 'COD With Wallet' " +
                $"WHEN payment_mode = 5 THEN 'Online With Wallet'  WHEN payment_mode = 6 THEN 'Online On Delivery' WHEN payment_mode = 7 THEN 'Cash On Delivery' END AS paymentcode,CASE WHEN order_method = 1 THEN 'Direct Delivery' WHEN order_method = 2 THEN 'Customer Collect' WHEN order_method = 3 THEN 'Courier Delivery' END AS order_method FROM finascop_autoposting_calculations fc " +
                $"INNER JOIN retaline_customer_order rc  ON  rc.order_id=fc.order_id  INNER JOIN finascop_branch fb ON rc.order_branch_id=fb.br_id LEFT JOIN area_entries ae ON fb.areaId=ae.id LEFT JOIN business_associate ba ON ae.areaBusinessAssociate =ba.id where fc.order_id=@order_id GROUP BY order_id";
            DataTable valuehead = DataServiceMySql.GetDataTable(details, parmeters: prms);
            if (valuehead != null && valuehead.Rows.Count > 0)
            {
                var total = valuehead.Rows[0];
                if (total != null)
                {
                    ltrorderid.Text = total["order_order_id"].ToString();
                    string date = total["order_confirmed_on"].ToString();
                    if (!string.IsNullOrWhiteSpace(date))
                    {
                        DateTime dt = Convert.ToDateTime(date);
                        ltrdate.Text = dt.ToString("dd MMM yyyy HH:mm:ss");
                    }
                    ltrstore.Text = total["storegroupname"].ToString();
                    //ltrTime.Text = dt.ToString("HH:mm:ss");
                    ltrpayment.Text = total["paymentcode"].ToString();
                    if (ltrpayment.Text == "")
                    {
                        ltrpayment.Text = "Payment Is Not Available";
                    }
                    ltrassociate.Text = total["areaassociate"].ToString();
                    ltrdeliverytype.Text = total["order_method"].ToString();
                }
            }

            string strAlertSCript = "$('#priviewledgerpopup').modal('show');";
            strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";
            System.Type cstype = this.GetType();
            String csname1 = "ShowConfirmPopup";
            ClientScriptManager cs = this.ClientScript;
            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
            cstext1.Append("script>");
            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());


        }

        protected void lvDataEny_DataBound(object sender, EventArgs e)
        {

        }
        public string GetEventOnHead(string head)
        {
            if (dtEvents == null)
            {
                dtEvents = DataServiceMySql.GetDataTable("SELECT h.event, h.column_name,h.name FROM finance_calculation_heads h ORDER BY displayorder ASC ");
            }

            string strEvents = String.Join(",",
                dtEvents.AsEnumerable().Where(r => r["name"].ToString() == head)
                .Select(r => r["event"].ToString()).ToArray());
            return strEvents;

        }
        public string GetOrdervalue(string head)
        {
            if (dthead == null)
            {
                dthead = DataServiceMySql.GetDataTable("SELECT h.column_name,h.name FROM finance_calculation_heads h  ORDER BY displayorder ASC");
            }

            string strEvents = String.Join(",",
                dthead.AsEnumerable().Where(r => r["name"].ToString() == head)
                .Select(r => r["name"].ToString()).ToArray());
            return strEvents;

        }
        public string Getcostcentre(string costcentreid)
        {
            if (String.IsNullOrEmpty(costcentreid))
                return "";

            if (dtcostcentre == null)
            {
                dtcostcentre = DataService.GetDataTable("select id,[name] from cost_centre", ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString);
            }
            try
            {
                if (!dtcostcentre.AsEnumerable().Any(r => r["id"].ToString() == costcentreid))
                    return "";

                string strcost = String.Join(",",
                  dtcostcentre.AsEnumerable().Where(r => r["id"].ToString() == costcentreid)
                 .Select(r => r["name"].ToString()).ToArray());
                return strcost;
            }
            catch { }
            return "";
        }
        protected void chkvaluehead_CheckedChanged(object sender, EventArgs e)
        {

        }

        public string Getordertype(string head)
        {
            if (dttype == null)
            {
                dttype = DataServiceMySql.GetDataTable("SELECT id,name,TYPE FROM finance_calculation_heads ");
            }
            string strcost = String.Join(",",
                dttype.AsEnumerable().Where(r => r["name"].ToString() == head)
                .Select(r => "For" + " " + r["TYPE"].ToString()).ToArray());
            return strcost;
        }

        public string Getledgername(string ledgerid)
        {
            if (string.IsNullOrEmpty(ledgerid) || ledgerid == "0")
                return "Not Applicable";

            if (dtledger == null)
            {
                dtledger = DataService.GetDataTable("select id,name from ledger", ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString);
            }

            string strledger = String.Join(",",
                dtledger.AsEnumerable().Where(r => r["id"].ToString() == ledgerid)
                .Select(r => r["name"].ToString()).ToArray());
            return strledger;
        }
    }
}