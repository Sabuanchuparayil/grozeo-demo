using Finascop.BussinessModel;
using Newtonsoft.Json;
using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using RestSharp;
using RetalineProAgent.Core.BussinessModel;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.PaymentGateway;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.IO;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Finance
{
    public partial class FinanceSubscriptions : Base.BasePartnerPage
    {
        [Serializable]
        public class SubscriptionVoucherEntryData
        {
            /// <summary>
            /// Particulars
            /// </summary>
            public string particulars { get; set; }
            /// <summary>
            /// Ledger ID
            /// </summary>
            public int ledgerId { get; set; }
            /// <summary>
            /// debit
            /// </summary>
            public double debit { get; set; }
            /// <summary>
            /// credit
            /// </summary>
            public double credit { get; set; }
            /// <summary>
            /// If true then Dr, else Cr.
            /// </summary>
            public bool IsDebit { get; set; }
            /// <summary>
            /// Reference
            /// </summary>
            public string reference { get; set; }
            /// <summary>
            /// store_group_name
            /// </summary>
            public string Store_group_name { get; set; }
            /// <summary>
            /// entityid
            /// </summary>
            public string entityid { get; set; }
            /// <summary>
            /// br_Name_store_group
            /// </summary>
            public string br_Name_store_group { get; set; }
            /// <summary>
            /// br_ID_store_group
            /// </summary>
            public int br_ID_store_group { get; set; }
            /// <summary>
            /// order_event
            /// </summary>
            public string order_event { get; set; }
            /// <summary>
            /// entry_type
            /// </summary>
            public int entry_type { get; set; }
            /// <summary>
            /// storeGroupRefId
            /// </summary>
            public string storeGroupRefId { get; set; }
            /// <summary>
            /// Narration
            /// </summary>
            public string Narration { get; set; }
            ///<summary>
            /// TransactionTypeId
            ///</summary>
            public string TransactionTypeId { get; set; }
            ///<summary>
            ///docTypeID
            ///</summary>
            public string docTypeID { get; set; }
        }
        public List<SubscriptionVoucherEntryData> lstSubscriptiontVoucherEntry
        {
            get
            {
                if (ViewState["LEDGERENTRYLIST"] != null)
                    return (List<SubscriptionVoucherEntryData>)ViewState["LEDGERENTRYLIST"];
                return new List<SubscriptionVoucherEntryData>();
            }
            set
            {
                ViewState["LEDGERENTRYLIST"] = value;
            }
        }
        protected void Page_Load(object sender, EventArgs e)
        {

        }

        protected void lbtnSearch_Click(object sender, EventArgs e)
        {

        }

        protected void lnkExport1_Click(object sender, EventArgs e)
        {
            DataView dv = (DataView)SDSsubscription.Select(DataSourceSelectArguments.Empty);
            if (dv != null)
            {
                ExportToExcel(dv.ToTable(), "Subscription.xlsx");
            }
        }
        private void ExportToExcel(DataTable dt, string fileName)
        {
            IWorkbook workbook = new XSSFWorkbook();
            ISheet sheet = workbook.CreateSheet("Subscription");
            ICreationHelper helper = workbook.GetCreationHelper();
            ICellStyle dateStyle = workbook.CreateCellStyle();
            dateStyle.DataFormat = helper.CreateDataFormat().GetFormat("dd-MMM-yyyy");

            var rowIndex = 0;

            var fieldLabels = "createdOn,CreatedOn|name,Store Group|planname,Subscription Item|PGSubscriptionId,Subscription ID|Pricepercycle,Rate|Discount,Discount|Pricepercycle,Amount| ,Referrer| paymentStatus,Status";

            var fields = fieldLabels
                .Split('|')
                .Select(f => new { Field = f.Split(',')[0].Trim(), Label = f.Split(',')[1].Trim() })
                .ToList();

            // Header rows
            IRow fieldHeaderRow = sheet.CreateRow(rowIndex++);
            for (int i = 0; i < fields.Count; i++)
            {
                fieldHeaderRow.CreateCell(i).SetCellValue(fields[i].Label);
            }
            foreach (DataRow dr in dt.Rows)
            {
                IRow row = sheet.CreateRow(rowIndex++);
                for (int col = 0; col < fields.Count; col++)
                {
                    var field = fields[col].Field;
                    ICell cell = row.CreateCell(col);

                    if (!string.IsNullOrWhiteSpace(field) && dt.Columns.Contains(field))
                    {
                        object value = dr[field];
                        try
                        {
                            if (field == "createdOn" && DateTime.TryParse(value?.ToString(), out DateTime dateVal))
                            {
                                cell.SetCellValue(dateVal);
                                cell.CellStyle = dateStyle;
                            }
                            else if (field == "planname")
                            {
                                string plan = dt.Columns.Contains("planname") ? dr["planname"]?.ToString() : "";
                                string cycle = dt.Columns.Contains("billingcycle") ? dr["billingcycle"]?.ToString() : "";
                                value = $"{plan} {cycle}".Trim();
                                cell.SetCellValue(helper.CreateRichTextString(value.ToString()));

                            }
                            else if (field == "paymentStatus")
                            {
                                string status = dr["paymentStatus"]?.ToString().ToLower() ?? "";
                                value = status == "pending" ? "Subscription Initialized"
                                       : status == "paid" ? "Subscription Successful"
                                       : status == "failed" ? "Subscription Failed"
                                       : "Unknown Status";
                                cell.SetCellValue(helper.CreateRichTextString(value.ToString()));
                            }
                            else if ((field == "Pricepercycle") && double.TryParse(value?.ToString(), out double number))
                            {
                                cell.SetCellValue(number);
                            }
                            else if ((field == "Discount") && double.TryParse(value?.ToString(), out double Discountnumber))
                            {
                                cell.SetCellValue(Discountnumber);
                            }
                            else
                            {
                                cell.SetCellValue(helper.CreateRichTextString(value?.ToString() ?? ""));
                            }
                        }
                        catch
                        {
                            cell.SetCellValue("");
                        }
                    }
                    else if (fields[col].Label == "Referrer")
                    {
                        cell.SetCellValue("NA");
                    }
                    else
                    {
                        cell.SetCellValue("");
                    }
                }
            }

            // Export to response
            using (MemoryStream ms = new MemoryStream())
            {
                workbook.Write(ms);
                byte[] file = ms.ToArray();

                HttpContext.Current.Response.Clear();
                HttpContext.Current.Response.Buffer = true;
                HttpContext.Current.Response.ContentType = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
                HttpContext.Current.Response.AddHeader("content-disposition", $"attachment;filename={fileName}");
                HttpContext.Current.Response.BinaryWrite(file);
                HttpContext.Current.Response.Flush();
                HttpContext.Current.Response.End();
            }
        }
        protected void btnsubscrib_Click(object sender, EventArgs e)
        {
            string subscriptionid = "";
            LinkButton lbtn = (LinkButton)sender;
            if (lbtn != null && !String.IsNullOrEmpty(lbtn.Attributes["subId"]))
            try { subscriptionid = lbtn.Attributes["subId"]; } catch { subscriptionid = "0"; }

            GridViewRow row = (GridViewRow)lbtn.NamingContainer;
            int index = row.RowIndex;
            string groupId = gvSubscription.DataKeys[row.RowIndex]["groupid"].ToString();
            string subscriptionId = gvSubscription.DataKeys[index].Value.ToString();
            string storeGroup = gvSubscription.DataKeys[index].Values["name"].ToString();      
            string rate = gvSubscription.DataKeys[index].Values["Pricepercycle"].ToString();
            string discount = gvSubscription.DataKeys[index].Values["Discount"].ToString();
            hdnstoregroupid.Value= groupId;
            var service = new RazorpayService();
            dynamic payments = service.GetSubscriptionDetails(subscriptionid);  
            if (payments != null)
            {
                string Paymentid = payments.Attributes.id;
                string description = payments.Attributes.description;
                decimal amount = payments.Attributes.amount;
                decimal fee = payments.Attributes.fee;
                decimal tax = payments.Attributes.tax;
                long createdUnix = payments.Attributes.created_at;
                DateTime startat = DateTimeOffset.FromUnixTimeSeconds(createdUnix).DateTime;
                string createdDateFormatted = startat.ToString("dd/MMM/yyyy HH:mm:ss");
                ltrstoregroup.Text = storeGroup;
                txtcollect.Text = Convert.ToDecimal(amount).ToString("0.00");
                ltrsubscription.Text = description;
                decimal gstRate = 0.18m;
                decimal includeAmount = Math.Round(Convert.ToDecimal(amount), 2);
                decimal amountWithoutGST = includeAmount / (1 + gstRate);
                decimal gstAmount = includeAmount - amountWithoutGST;
                string gstAmountStr = gstAmount.ToString("0.00");
                string amountWithoutGSTStr = amountWithoutGST.ToString("0.00");
                txtamount.Text = Math.Round(Convert.ToDouble(amountWithoutGSTStr)).ToString("0.00"); 
                ltrcrateddate.Text = createdDateFormatted;
                txtgst.Text = gstAmountStr.ToString();
                ltrrate.Text = rate;
                txtMDR.Text =Convert.ToDecimal(fee).ToString("0.00");
                ltrdiscount.Text = "Not Applicable";
                txtgstmdr.Text =Convert.ToDecimal(tax).ToString("0.00");
                decimal amountcollected = Math.Max(amount - (fee + tax), 0);
               bool BRPosting= finacebankreportposting(hdnstoregroupid.Value, amountcollected, Convert.ToDecimal(fee), Convert.ToDecimal(tax), Convert.ToDecimal(amount), subscriptionId, description, Paymentid);                               
               bool SJposting= Financesalereport(hdnstoregroupid.Value, amountWithoutGST, gstAmount, amount, subscriptionId, description, Paymentid);
                if(BRPosting && SJposting)
                {
                    List<KeyValuePair<string, object>> sqlledger = new List<KeyValuePair<string, object>>();
                    sqlledger.Add(new KeyValuePair<string, object>("SubId", subscriptionId));
                    string docpfixledger = "select id ,voucherSlNOString from data_entry where [entity_id]=@SubId";
                    DataTable dtdocprifix = DataService.GetDataTable(docpfixledger, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqlledger);
                    if (dtdocprifix.Rows.Count > 0)
                        hdnbankrecipt.Value= txtbrvocher.Text= dtdocprifix.Rows[0]["voucherSlNOString"].ToString();
                    if (dtdocprifix.Rows.Count > 1)
                        hdnsalerecipt.Value= txtsjvoucher.Text = dtdocprifix.Rows[1]["voucherSlNOString"].ToString();
                    string datetime = DateTime.Now.ToString("ddMMyy");
                    Random rnd = new Random();
                    int number;
                    number = rnd.Next(1, 10000);
                    string invoiceno = ""; string date = "";
                    string code = "B2";
                    (invoiceno, date) = Getinvoicenumber(code, 1, number, datetime);                
                    int num;
                    num = rnd.Next(1, 10000);
                    string rececpiteno = ""; string Resdate = "";
                    string Rescode = "GZR";
                    (rececpiteno, Resdate) = Getinvoicenumber(Rescode, 1, num, datetime);
                    txtreceiptno.Text = rececpiteno;
                    txtinvoiceno.Text = invoiceno;
                }
            }
            //popup Action
            string strAlertSCript = "$('#razorpayaccountDetails').modal('show');";
            strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";
            System.Type cstype = this.GetType();
            String csname1 = "ShowConfirmPopup";
            ClientScriptManager cs = this.ClientScript;
            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
            cstext1.Append("script>");
            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
            lbtn.Visible = false;
        }
        /// <summary>
        /// Generate subscriptionVoucherEntryData list using hardcoded ledgers but dynamic credit/debit values, only for now we are using this.
        /// </summary>
        /// <param name="ledgerInputs">List of ledger entries with ID, Name, Credit, Debit from inputs</param>
        /// <returns>Return result</returns>
        public bool finacebankreportposting(string storegroupid, decimal amountcollected,decimal fee,decimal tax,decimal amount,string subscriptionId,string description,string Paymentid)
        {
            var ledgerEntries = new List<(int Id, string Name,bool cr,decimal dramount,decimal cramount)>
            {
                (508, "Razorpay",false,amountcollected,0),
                (502, "PG Merchant discount rate",false,fee,0),
                (21, "IGST Input",false,tax,0)
            };
            List<SubscriptionVoucherEntryData> lstData = new List<SubscriptionVoucherEntryData>();
            var ledgerDetails= GetReference(storegroupid);
            foreach (var ledger in ledgerEntries)
            {
                lstData.Add(CreateLedgerEntry(ledger.Id, ledger.Name, (double)ledger.cramount, (double)ledger.dramount, ledgerDetails.storename, subscriptionId,"3"));
            }
            lstData.Add(CreateLedgerEntry(ledgerDetails.Item2, ledgerDetails.Item1, (double)amount, 0, ledgerDetails.storename, subscriptionId,"3"));

            lstSubscriptiontVoucherEntry = lstData;
            Transaction.TransactionEntry te = CreateTEFromVED(lstSubscriptiontVoucherEntry);
            string entryref = Guid.NewGuid().ToString();
            string narration = "Online Subscription received for the {#Feature Name#} from {#StoreGroupName#} through {#PaymentGatewayName#} vide Payment ID {#PaymenId#}.";
            String Narrationsaleproceed = narration.Replace("{#Feature Name#}", description)
                .Replace("{#StoreGroupName#}", ledgerDetails.storename)
                .Replace("{#PaymentGatewayName#}", (ConfigurationSettings.AppSettings.Get("PaymentGateway")) == "razorpay" ? "razorpay" : "Stripe")
                .Replace("{#PaymenId#}", Paymentid);
            string refernce = "Subscription for {#planName#} with Subscription ID {#subscriptionId#}.";
            string Referrce = refernce.Replace("{#planName#}",description)
                .Replace("{#subscriptionId#}", subscriptionId);
            te.entry_RefId = entryref;
            te.reference = Referrce;
            te.Narration = Narrationsaleproceed;
            string content = JsonConvert.SerializeObject(te);
           string pst= getposting(content);
            if (pst != null)
            {
                return true;
            }
            return false;
        }
        /// <summary>
        /// Generate subscriptionVoucherEntryData list using hardcoded ledgers but dynamic credit/debit values, only for now we are using this.
        /// </summary>
        /// <param name="ledgerInputs"></param>
        /// <returns>Return result</returns>
        public bool Financesalereport(string storegroupid, decimal amountWithoutGST, decimal gstAmount, decimal amount, string subscriptionId, string description,string Paymentid)
        {
            List<SubscriptionVoucherEntryData> lstbrdataData = new List<SubscriptionVoucherEntryData>();
            var ledgerBREntries = new List<(int Id, string Name, bool cr, decimal dramount, decimal cramount)>
            {
                (509, "Grozeo Seller Subscription",true,0,amountWithoutGST),
                (502, "IGST",true,0,gstAmount),
            };
            var ledgerBRDetails = GetReference(storegroupid);
            foreach (var ledgerBREntry in ledgerBREntries)
            {
                lstbrdataData.Add(CreateLedgerEntry(ledgerBREntry.Id, ledgerBREntry.Name, (double)ledgerBREntry.cramount, (double)ledgerBREntry.dramount, ledgerBRDetails.storename, subscriptionId, "7"));
            }
            lstbrdataData.Add(CreateLedgerEntry(ledgerBRDetails.Item2, ledgerBRDetails.Item1, 0, (double)amount, ledgerBRDetails.storename, subscriptionId, "7"));
            lstSubscriptiontVoucherEntry = lstbrdataData;
            Transaction.TransactionEntry te = CreateTEFromVED(lstSubscriptiontVoucherEntry);
            string entryref = Guid.NewGuid().ToString();
            te.entry_RefId = entryref;
            string salasenarration = "Sales revenue from Subscription created for the {#FeatureName#} from {#StoreGroupName#} through {#PaymentGatewayName#} vide Payment ID {#PaymentID#}";
            String Salesnarration = salasenarration.Replace("{#FeatureName#}", description)
                .Replace("{#StoreGroupName#}", ledgerBRDetails.storename)
                .Replace("{#PaymentGatewayName#}", (ConfigurationSettings.AppSettings.Get("PaymentGateway")) == "razorpay" ? "razorpay" : "Stripe")
                .Replace("{#PaymentID#}", Paymentid);
            string salesrefernce = "Payment {#paymentId#} for Subscription {#subscriptionId#} under Plan {#planName#}";
            string SalesReferance = salesrefernce.Replace("{#paymentId#}", Paymentid)
                .Replace("{#subscriptionId#}", subscriptionId)
                .Replace("{#planName#}", description);
            te.reference = SalesReferance;
            te.Narration = Salesnarration;
            string content = JsonConvert.SerializeObject(te);
            string pst = getposting(content);
            if (pst != null)
            {
                return true;
            }
            return false;

        }
        protected Transaction.TransactionEntry CreateTEFromVED(List<SubscriptionVoucherEntryData> ved)
        {
            Transaction.TransactionEntry trEntry = new Transaction.TransactionEntry();
            List<Transaction.TransactionData> Account = new List<Transaction.TransactionData>();
            List<Transaction.TransactionData> Particulars = new List<Transaction.TransactionData>();
            trEntry.TransactionTypeId = (Transaction.TransactionType)Enum.Parse(typeof(Transaction.TransactionType), ved[0].TransactionTypeId.ToString());
            trEntry.docTypeID = int.Parse(ved[0].docTypeID);
            trEntry.StoreGroupName = ved[0].Store_group_name;
            trEntry.storeGroupRefId = ved[0].storeGroupRefId;
            trEntry.br_ID_store_group = ved[0].br_ID_store_group;
            trEntry.br_Name_store_group = ved[0].br_Name_store_group;
            trEntry.order_order_id = ved[0].entityid;
            trEntry.order_event = ved[0].order_event;
            trEntry.entry_type = ved[0].entry_type;
            trEntry.blob_storage_folder = "";
            trEntry.voucherDate = DateTime.Now;
            trEntry.finascopBrID = 1;
            // Debit entries (isDebtor = 1)
            trEntry.Account = ved
                .Where(a => a.debit > 0)
                .GroupBy(a => new { a.ledgerId, a.particulars })
                .Select(g => new Transaction.TransactionData
                {
                    ledgerId = g.Key.ledgerId,
                    reference = trEntry.reference,
                    amount = g.Sum(x => x.debit),
                    particulars = g.Key.particulars,
                    isDebtor = 1
                })
                .ToList();

            // Credit entries (isDebtor = 0)
            trEntry.Particulars = ved
                .Where(a => a.credit > 0)
                .GroupBy(a => new { a.ledgerId, a.particulars })
                .Select(g => new Transaction.TransactionData
                {
                    ledgerId = g.Key.ledgerId,
                    reference = trEntry.reference,
                    amount = g.Sum(x => x.credit),
                    particulars = g.Key.particulars,
                    isDebtor = 0
                })
                .ToList();
            return trEntry;
        }
        public string getposting(string content)
        {
            // string content = JsonConvert.SerializeObject(te);
            RestResponse res = null;
            try
            {
                string url = ConfigurationSettings.AppSettings.Get("FinascopAPIUrl");
                if (String.IsNullOrEmpty(url))
                    url = "https://finascopdataentry.azurewebsites.net/api/";
                url += "FinascopDataEntry";

                string key = ConfigurationSettings.AppSettings.Get("FinascopAPIKey");
                if (String.IsNullOrEmpty(key))
                    key = "P_5JtNckvvxLTUM6cF9py_7ZYIA5QM9ofmNaDvh__HoqAzFuAbEyZQ==";
                var client = new RestClient(url);
                var request = new RestRequest();
                request.Method = Method.Post;
                request.AddHeader("content-type", "application/json");
                request.AddHeader("x-functions-key", key);

                //request.AddBody("{" + content + "}", "application/json");
                request.AddBody(content, "application/json");
                res = client.ExecuteAsync<Result>(request).Result;
                if (res.StatusCode == System.Net.HttpStatusCode.OK)
                {
                    return res.Content;
                }
            }
            catch (Exception ex)
            {
                //ex.Message
            }
            Result result = null;
            //RetalineProAgent.Core.Services.Finance.Result res = DataService.DataEntry(te);
            if (res.StatusCode == System.Net.HttpStatusCode.OK) // Check if the request was successful
            {
                // Deserialize the content to your object type
                result = Newtonsoft.Json.JsonConvert.DeserializeObject<Result>(res.Content);

                // Now you have your object (YourObjectType) from the response content
                // Work with responseObject here
            }
            return result?.ToString() ?? "";
        }
        private (string, int) Getledgerdetails(string refno)
        {
            try
            {
                string ledgername = "";
                int ledger_id = 0;
                List<KeyValuePair<string, object>> sqlledger = new List<KeyValuePair<string, object>>();
                sqlledger.Add(new KeyValuePair<string, object>("refno", refno));
                string getledger = "select id,name from ledger where refId=@refno";
                var dtledgern = DataService.GetDataTable(getledger, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqlledger);
                if (dtledgern != null && dtledgern.Rows.Count > 0)
                {
                    var ledger = dtledgern.Rows[0];
                    ledgername = ledger["name"].ToString();
                    ledger_id = Convert.ToInt32(ledger["id"].ToString());
                }
                return (ledgername, ledger_id);
            }
            catch { }

            return ("", 0);
        }
        public (string, int,string storename) GetReference(string storeid)
        {
            string refid = "";
            string ledgername = "";
            int ledger_id = 0;
            string storegroupname = "";
            List<KeyValuePair<string, object>> sqlref = new List<KeyValuePair<string, object>>();
            sqlref.Add(new KeyValuePair<string, object>("storegroupid", storeid));
            string getrefernce = "SELECT store_group_id,storeRefId,`store_group_name` FROM `finascop_branch_group` WHERE store_group_id=@storegroupid";
            var dtrefe = DataServiceMySql.GetDataTable(getrefernce, parmeters: sqlref);
            if (dtrefe != null && dtrefe.Rows.Count > 0)
            {
                var reffe = dtrefe.Rows[0];
                refid= reffe["storeRefId"].ToString();
               var getledger= Getledgerdetails(refid);
                storegroupname= reffe["store_group_name"].ToString();
                ledgername = getledger.Item1;
                ledger_id = getledger.Item2;
                return (ledgername, ledger_id, storegroupname);
            }
            return ("", 0,"");
        }
        private SubscriptionVoucherEntryData CreateLedgerEntry(int ledgerId, string particulars, double credit, double debit, string storeName, string subscriptionId,string doctype)
        {
            return new SubscriptionVoucherEntryData
            {
                ledgerId = ledgerId,
                particulars = particulars,
                credit = credit,
                debit = debit,
                Store_group_name = storeName,
                entityid = subscriptionId,
                entry_type = 1,
                storeGroupRefId = "",
                br_Name_store_group = "",
                br_ID_store_group = 1,
                order_event = "Subscription",
                TransactionTypeId = doctype,
                docTypeID = doctype
            };
        }
        public static (string, string) Getinvoicenumber(string pmr_office_prefix, int pmr_invoice_type, int pmr_order_id, string pmr_date_format_prefix)
        {
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("pmr_order_id", pmr_order_id));
            prms.Add(new KeyValuePair<string, object>("pmr_office_prefix", pmr_office_prefix));
            prms.Add(new KeyValuePair<string, object>("pmr_invoice_type", pmr_invoice_type));
            prms.Add(new KeyValuePair<string, object>("pmr_date_format_prefix", pmr_date_format_prefix));
            DataTable invoice = DataServiceMySql.GetDataTable("getInvoiceNumber", UserService.GetAPIConnectionString(), prms, true);
            string invoicenumber = "";
            string date = "";
            if (invoice != null && invoice.Rows.Count > 0)
            {
                invoicenumber = invoice.Rows[0]["inv_number"].ToString();
                date = invoice.Rows[0]["created_at"].ToString();
            }
            return (invoicenumber, date);
        }

        protected void btnviewBRvoucher_Click(object sender, EventArgs e)
        {
            string voucherid = hdnbankrecipt.Value;
            if (voucherid != null)
                getsubscriptionposting(voucherid);

        }

        protected void btnviewsalevoucher_Click(object sender, EventArgs e)
        {
            string vouchersaleid = hdnsalerecipt.Value;
            if(vouchersaleid!=null)
                getsubscriptionposting(vouchersaleid);  
        }

        public string getsubscriptionposting(string vouchernumber)
        {
            try
            {
                List<KeyValuePair<string, object>> sqlposting = new List<KeyValuePair<string, object>>();
                sqlposting.Add(new KeyValuePair<string, object>("voucherno", vouchernumber));
                string geposting = "SELECT tr.particulars,ledger_id, isnull (CASE WHEN [isDebtor] = 1 THEN  tr.amount  END,0) AS dr_amount,isnull (CASE WHEN [isDebtor] =0 THEN  tr.amount  END,0) AS cr_amount FROM transactions tr INNER JOIN  data_entry de ON tr.data_entry_id =de.id WHERE voucherSlNOString=@voucherno";
                DataTable dtposting = DataService.GetDataTable(geposting, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqlposting);
                List<SubscriptionVoucherEntryData> AddlstData = new List<SubscriptionVoucherEntryData>();
                if (dtposting == null || dtposting.Rows.Count <= 0)
                {
                    return "";
                }
                foreach (DataRow dr in dtposting.Rows)
                {
                    AddlstData.Add(new SubscriptionVoucherEntryData
                    {
                        credit = Convert.ToDouble(dr["cr_amount"]),
                        debit = Convert.ToDouble(dr["dr_amount"]),
                        ledgerId = Convert.ToInt32(dr["ledger_id"]),
                        particulars = dr["particulars"].ToString(),
                    });
                }
                lvsubscrionposting.DataSource = AddlstData;
                lvsubscrionposting.DataBind();
                Literal ltrdrtotal = (Literal)lvsubscrionposting.FindControl("ltrDrTotal");
                Literal ltrcrtotal = (Literal)lvsubscrionposting.FindControl("ltrCRTotal");
                if (ltrdrtotal != null && ltrcrtotal != null)
                {
                    double drtotal = 0, crtotal = 0;
                    try { drtotal = AddlstData.Sum(d => d.debit); } catch { }
                    try { crtotal = AddlstData.Sum(d => d.credit); } catch { }

                    ltrdrtotal.Text = String.Format("{0:0.00}", drtotal).ToString();
                    ltrcrtotal.Text = String.Format("{0:0.00}", crtotal).ToString();
                }
            }
            catch
            {

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
            return "";
        }

    }
}