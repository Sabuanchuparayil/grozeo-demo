using Finascop.BussinessModel;
using Newtonsoft.Json;
using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using RestSharp;
using RetalineProAgent.Core.BussinessModel;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
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
    public partial class MangeBulkUpload : Base.BasePartnerPage
    {
        [Serializable]
        public class SettlementVoucherEntryData
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
        public List<SettlementVoucherEntryData> lstsettlementVoucherEntry
        {
            get
            {
                if (ViewState["LEDGERENTRYLIST"] != null)
                    return (List<SettlementVoucherEntryData>)ViewState["LEDGERENTRYLIST"];
                return new List<SettlementVoucherEntryData>();
            }
            set
            {
                ViewState["LEDGERENTRYLIST"] = value;
            }
        }

        protected void Page_Load(object sender, EventArgs e)
        {
            txtFromDate.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
            txtToDate.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
            if (!IsPostBack)
            {
                txtFromDate.Text = DateTime.Now.AddDays(-15).ToString("yyyy-MM-dd");
                txtToDate.Text = DateTime.Now.ToString("yyyy-MM-dd");
            }
        }

        protected void btnaction_Click(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;
            int SettleId = Convert.ToInt32(lbtn.Attributes["recid"]);
            btnapprove.Attributes.Add("recid", SettleId.ToString());
            List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
            sqldaId.Add(new KeyValuePair<string, object>("id", SettleId));
            string getsettle = "SELECT 0 as cr,'' AS filename , bg.storeRefId,bg.store_group_name,fb.br_name,fb.br_id,br_name,br_ID, status_id,UTRno,payout_amount FROM finance_transaction ms INNER JOIN finascop_branch fb ON ms.branch_id=fb.br_id INNER JOIN finascop_branch_group bg ON bg.store_group_id=br_storeGroup where FileId=@id union all SELECT Amount AS cr,Filename AS filename, '','','',0,'',0, 0,'',0 FROM `BankFileDetails` WHERE id=@id";
            DataTable dt = DataServiceMySql.GetDataTable(getsettle, parmeters: sqldaId);
            string fileName = "";
            List<SettlementVoucherEntryData> lstData = new List<SettlementVoucherEntryData>();
            foreach (DataRow dr in dt.Rows)
            {
                double cr = Convert.ToDouble(dr["cr"]);
                fileName = dr["FileName"].ToString();
                var getreferance = getnarration(SettleId);
                var ledgerDetails = (cr == 0 ? Getledgerdetails(dr["storeRefId"].ToString()) : ("", 0));
                lstData.Add(new SettlementVoucherEntryData
                {
                    credit = Convert.ToDouble(dr["cr"]),
                    debit = Convert.ToDouble(dr["payout_amount"]),
                    ledgerId = (cr == 0 ? ledgerDetails.Item2 : Convert.ToInt32(ConfigurationManager.AppSettings.Get("SettlementSourceBankId"))),
                    particulars = (cr == 0 ? ledgerDetails.Item1 : (ConfigurationManager.AppSettings.Get("SettlementSourceBankName"))),
                    Store_group_name = dr["store_group_name"].ToString(),
                    entityid = "",
                    entry_type = 1,
                    storeGroupRefId = dr["storeRefId"].ToString(),
                    br_Name_store_group = dr["br_name"].ToString(),
                    br_ID_store_group = Convert.ToInt32(dr["br_ID"].ToString()),
                    order_event = "Settlement Approval",
                    TransactionTypeId = "4",
                    docTypeID = "4"

                });
            }
            lbstoregroup.Text = fileName;
            lstsettlementVoucherEntry = lstData;
            lvsettlement.DataSource = lstsettlementVoucherEntry;
            lvsettlement.DataBind();
            Literal ltrdrtotal = (Literal)lvsettlement.FindControl("ltrDrTotal");
            Literal ltrcrtotal = (Literal)lvsettlement.FindControl("ltrCRTotal");
            if (ltrdrtotal != null && ltrcrtotal != null)
            {
                double drtotal = 0, crtotal = 0;
                try { drtotal = lstData.Sum(d => d.debit); } catch { }
                try { crtotal = lstData.Sum(d => d.credit); } catch { }

                ltrdrtotal.Text = String.Format("{0:0.00}", drtotal).ToString();
                ltrcrtotal.Text = String.Format("{0:0.00}", crtotal).ToString();
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
            //Common.ShowCustomAlert(this.Page, "Success", "Saved successfully!", true, "/");

        }
        public (string, int) Getledgerdetails(string refno)
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
                    //debit = Convert.ToDouble(dtpaymentupdate.Rows[0]["payout_amount"].ToString());                                    
                    var ledger = dtledgern.Rows[0];
                    ledgername = ledger["name"].ToString();
                    ledger_id = Convert.ToInt32(ledger["id"].ToString());
                }
                return (ledgername, ledger_id);
            }
            catch { }

            return ("", 0);
        }
        protected Transaction.TransactionEntry CreateTEFromVED(List<SettlementVoucherEntryData> ved)
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
            trEntry.voucherDate=DateTime.Now;
            trEntry.finascopBrID = 1;
            trEntry.Account = ved.Select(a => new Transaction.TransactionData
            {
                ledgerId = a.ledgerId,
                reference = trEntry.reference,
                amount = a.debit,
                particulars = a.particulars,
                isDebtor = 1
            }).ToList();
            trEntry.Particulars = ved.Select(a => new Transaction.TransactionData
            {
                ledgerId = a.ledgerId,
                reference = trEntry.reference,
                amount = a.credit,
                particulars = a.particulars,
                isDebtor = 0
            }).ToList();
            return trEntry;
        }
        protected void btnapprove_Click(object sender, EventArgs e)
        {
            Button lbtn = (Button)sender;
            int Id = Convert.ToInt32(lbtn.Attributes["recid"]);
            if (lstsettlementVoucherEntry.Count < 0)
            {
                return;
            }
            Transaction.TransactionEntry te = CreateTEFromVED(lstsettlementVoucherEntry);
            string entryref = Guid.NewGuid().ToString();
            te.entry_RefId = entryref;
            var getreferance = getnarration(Id);
            te.reference = getreferance.Item2;
            te.Narration = getreferance.Item1;
            string content = JsonConvert.SerializeObject(te);
            getposting(content);
            string bStatus = "2";
            string tStatus = "7";
            List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
            sqldaId.Add(new KeyValuePair<string, object>("id", Id));
            sqldaId.Add(new KeyValuePair<string, object>("transactiono", txttransationno.Text));
            sqldaId.Add(new KeyValuePair<string, object>("date", txtdate.Text));
            sqldaId.Add(new KeyValuePair<string, object>("Ref", entryref));
            sqldaId.Add(new KeyValuePair<string, object>("bStatus", bStatus));
            sqldaId.Add(new KeyValuePair<string, object>("tStatus", tStatus));
            string statusid = "UPDATE BankFileDetails b  inner join finance_transaction t on t.FileId=b.id SET b.status_id=@bStatus,b.TransactionNumber=@transactiono,b.Transactiondate=@date,b.TransactionRef_id=@Ref,t.status_id=@tStatus WHERE b.id=@Id";
            DataServiceMySql.ExecuteSql(statusid, Service.UserService.GetAPIConnectionString(), sqldaId);
            // Activitylog
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            string Source = strUrl;
            int storegroupid = -1;
            string User = "Finance Admin";
            string transactiono = txttransationno.Text;
            string id = Convert.ToString(Id);
            string date = txtdate.Text;
            string Ref = Convert.ToString(entryref);
            string contents = Convert.ToString(content);
            string page = "Manage Bulk Upload";
            var items = new[]
            {
                            new { Key = "Transactio No", Value = transactiono },
                            new { Key = "Id", Value = id },
                            new { Key = "Date", Value = date },
                            new { Key = "Entry Ref", Value = Ref },
                            new { Key = "posting", Value = contents },
                            new { Key = "page", Value = page},
            };
            string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
            var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
        }

        public (string, string) getnarration(int Id)
        {
            string Narrationresult = "";
            string Refernce = "";
            string narration = "Being the merchant settlement proceeds for {#Settlement Date#} as per banking transaction ID {#Bank Transaction ID#} dated {#Bank Transaction Date#}.";
            string refernance = "Settlement Bank file {#File Name#} dated {#File date#}";
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("id", Id));
            string getnarration = "SELECT settlement_date,CreatedOn,IFNULL(FileName,FilePath) AS filename FROM merchant_settlements_order o INNER JOIN merchant_settlements ms ON ms.ref_id=o.ms_ref_id INNER JOIN `finance_transaction_log` tl ON ms.id=tl.ms_id  INNER JOIN  finance_transaction t ON t.id=tl.ft_id  INNER JOIN  BankFileDetails b ON t.FileId=b.id where FileId=@id  GROUP BY settlement_id";
            var dt = DataServiceMySql.GetDataTable(getnarration, parmeters: prms);
            if (dt != null && dt.Rows.Count > 0)
            {
                var refe = dt.Rows[0];
                string settlementdate = ((DateTime)refe["settlement_date"]).ToString("dd-MMM-yyyy");
                string filedate = ((DateTime)refe["CreatedOn"]).ToString("dd-MMM-yyyy");
                string filename = refe["filename"].ToString();
                string inputDate = txtdate.Text;             
                Refernce = refernance.Replace("{#File Name#}", filename)
                     .Replace("{#File date#}", filedate);
                Narrationresult = narration.Replace("{#Settlement Date#}", settlementdate)
                 .Replace("{#Bank Transaction ID#}", txttransationno.Text)
                 .Replace("{#Bank Transaction Date#}", inputDate);
            }
            return (Narrationresult, Refernce);
        }
        public static string narration(string TransactionNumber)
        {
            string narration = "";
            List<KeyValuePair<string, object>> sqlnarration = new List<KeyValuePair<string, object>>();
            sqlnarration.Add(new KeyValuePair<string, object>("id", TransactionNumber));
            string getnarration = "SELECT t.status_id,UTRno,payout_amount,fb.store_group_name,TransactionNumber,Transactiondate FROM finance_transaction t  INNER JOIN finascop_branch_group fb ON fb.store_group_id=t.storegroup_id INNER JOIN BankFileDetails b ON b.id=t.FileId where TransactionNumber=@id";
            var dtstoregroup = DataServiceMySql.GetDataTable(getnarration, parmeters: sqlnarration);
            if (dtstoregroup != null && dtstoregroup.Rows.Count > 0)
            {
                string storegroup = dtstoregroup.Rows[0]["store_group_name"].ToString();
                string date = ((DateTime)dtstoregroup.Rows[0]["Transactiondate"]).ToString("dd-MMM-yyyy");
                string Tno = dtstoregroup.Rows[0]["TransactionNumber"].ToString();
                string narrationresult = "Being the reversal of merchant settlement on {#transaction date#} vide transaction ID # {#transaction ID#}.";
                narration = narrationresult
                      .Replace("{#transaction date#}", date)
                      .Replace("{#transaction ID#}", Tno);
            }
            return narration;
        }
        protected void btnimgactio_Click(object sender, EventArgs e)
        {
            txttransationno.Enabled = false;
            txtdate.ReadOnly = true;
            btnapprove.Visible = false;
            LinkButton lbtn = (LinkButton)sender;
            string Id = lbtn.Attributes["getid"];
            getreject(Id);
        }

        protected void btnupdatepayment_Click(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;
            string GetFilename = (lbtn.Attributes["getfile"]);
            lblfileupload.Text = GetFilename;
            btnFileUploadHidClick.Attributes.Add("getfile", GetFilename.ToString());
            PopupupLoad();
        }
        protected void rbtnpayment_CheckedChanged(object sender, EventArgs e)
        {
            //pnlpayment.Visible= true;
            //pnlupload.Visible = false;

            PopupupLoad();
        }
        protected void btnSave_Click(object sender, EventArgs e)
        {
            string strResults = "", strFailures = "";
            string Ref = "";
            string TransactionNumber = "";
            string updatedTransactionRefId = "";
            List<SettlementVoucherEntryData> lstSettlementVoucherEntries = new List<SettlementVoucherEntryData>();
            string FileId = "";
            string getnarrationreject = "";
            if (gvupload.Rows.Count <= 0)
            {
                Common.ShowCustomAlert(this.Page, "Failure", "No record available", false, "/Finance/ManageBulkUpload");
                return;
            }
            foreach (GridViewRow item in gvupload.Rows)
            {
                string strResult = "";
                string settlementId = item.Cells[0].Text;
                string strUTRN = item.Cells[4].Text;
                strUTRN = strUTRN.Replace("&nbsp;", "");
                List<KeyValuePair<string, object>> sqlSettle = new List<KeyValuePair<string, object>>();
                sqlSettle.Add(new KeyValuePair<string, object>("settlementid", settlementId));
                string settlement = "SELECT t.status_id,TransactionNumber,UTRno,payout_amount,FileId,TransactionRef_id FROM finance_transaction t INNER JOIN finascop_branch_group fb ON fb.store_group_id=t.storegroup_id INNER JOIN BankFileDetails b ON b.id=t.FileId where settlement_id=@settlementid";
                var dtpaymentupdate = DataServiceMySql.GetDataTable(settlement, parmeters: sqlSettle);
                TransactionNumber = dtpaymentupdate.Rows[0]["TransactionNumber"].ToString();
                getnarrationreject = narration(TransactionNumber);
                if (dtpaymentupdate == null || dtpaymentupdate.Rows.Count <= 0)
                {
                    strResult = "No data available for settlement id: " + settlementId;
                    return;
                }
                if (dtpaymentupdate.Rows[0]["status_id"].ToString() == "3" && dtpaymentupdate.Rows[0]["UTRno"].ToString() != null)
                {
                    strResult = "Already updated payment for settlement id: " + settlementId;
                    return;
                }
                //utr number is not update by bank
                if (String.IsNullOrEmpty(strUTRN))
                {
                    SettlementVoucherEntryData lstSettlementVoucherEntryReject = CreateSettlementData(settlementId, out strResult);
                    if (lstSettlementVoucherEntryReject != null)
                        lstSettlementVoucherEntries.Add(lstSettlementVoucherEntryReject);
                    else
                        strFailures += "<br/>" + strResult;

                    //update the status bank rejected
                    string strSuccessResult = "UPDATE finance_transaction t inner join BankFileDetails b on t.FileId=b.id SET t.status_id=6,b.status_id=3 where t.settlement_id=@settlementid ";
                    DataServiceMySql.ExecuteSql(strSuccessResult, Service.UserService.GetAPIConnectionString(), sqlSettle);
                    // Activitylog
                    String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                    String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                    string Source = strUrl;
                    int storegroupid = -1;
                    string User = "Finance Admin";
                    string Settlementid = strResult;
                    string date = txtdate.Text;
                    string UTRnumber = Convert.ToString(strUTRN);
                    string page = "Manage Bulk Upload";
                    var itemes = new[]
                    {
                            new { Key = "Settlementid", Value = Settlementid },
                            new { Key = "Date", Value = date },
                            new { Key = "Entry Ref", Value = Ref },
                            new { Key = "page", Value = page},
                    };
                    string Description = string.Join(", ", itemes.Select(items => $"{items.Key}={items.Value}"));
                    var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
                }
                else
                {       //update the status Transfered        
                    sqlSettle.Add(new KeyValuePair<string, object>("strUTRN", strUTRN));
                    string strSuccessResult = "UPDATE finance_transaction t inner join BankFileDetails b on t.FileId=b.id SET t.status_id=3,t.UTRno=@strUTRN,b.status_id=3 where settlement_id=@settlementid ";
                    DataServiceMySql.ExecuteSql(strSuccessResult, Service.UserService.GetAPIConnectionString(), sqlSettle);
                    // Activitylog
                    String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                    String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                    string Source = strUrl;
                    int storegroupid = -1;
                    string User = "Finance Admin";
                    string transactiono = txttransationno.Text;
                    string date = txtdate.Text;
                    string UTRnumber = Convert.ToString(strUTRN);                    
                    string page = "Manage Bulk Upload";
                    var itemes = new[]
                    {
                            new { Key = "Transactio No", Value = transactiono },
                            new { Key = "Date", Value = date },
                            new { Key = "Entry Ref", Value = Ref },
                            new { Key = "page", Value = page},
                    };
                    string Description = string.Join(", ", itemes.Select(items => $"{items.Key}={items.Value}"));
                    var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
                    Common.ShowCustomAlert(this.Page, "Success", "Saved successfully!", true, "/Finance/ManageBulkUpload");
                }

                Ref = dtpaymentupdate.Rows[0]["TransactionRef_id"].ToString();
                updatedTransactionRefId = Ref + "_reversal";
            }
            //Post
            if (lstSettlementVoucherEntries.Count != 0)
            {
                lstSettlementVoucherEntries.Add(new SettlementVoucherEntryData
                {
                    IsDebit = true,
                    debit = lstSettlementVoucherEntries.Sum(c => c.credit),
                    ledgerId =Convert.ToInt32(ConfigurationManager.AppSettings.Get("SettlementSourceBankId")),
                    particulars =(ConfigurationManager.AppSettings.Get("SettlementSourceBankName"))
                });
                Transaction.TransactionEntry te = CreateTEFromVED(lstSettlementVoucherEntries);
                te.reference = getrefernce(Ref);
                te.Narration = getnarrationreject;
                //te.Narration = "Test Narration";
                te.entry_RefId = updatedTransactionRefId;
                string content = JsonConvert.SerializeObject(te);
                getposting(content);


            }
        }
        private void PopupupLoad()
        {
            string strAlertSCript = "$('#Puppaymentdetails').modal('show');";
            strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";
            System.Type cstype = this.GetType();
            String csname1 = "ShowConfirmPopup";
            ClientScriptManager cs = this.ClientScript;
            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
            cstext1.Append("script>");
            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }

        protected void btnUpload_OnClick(object sender, EventArgs e)
        {

            var file = Request.Files[0];
            var strFile = file.FileName;
            string getfilename = Path.GetFileNameWithoutExtension(strFile);
            string filename = getfilename.Substring(0, 15);
            LinkButton lbtn = (LinkButton)sender;
            string GetFilenameto = (lbtn.Attributes["getfile"]);
            string Bankfilename = GetFilenameto.Substring(0, 15);
            string Filename = Path.GetFileNameWithoutExtension(Bankfilename);
            if (filename == Filename)
            {
                uploadpaymet();
            }
            else
            {
                Common.ShowCustomAlert(this.Page, "Failed", "File is not Matching", false, "/Finance/ManageBulkUpload");
            }
            PopupupLoad();
        }
        private List<string> uploadpaymet()
        {
            List<string> strResults = new List<string>();
            if (Request.Files == null || Request.Files.Count <= 0)
            {
                SendResponse("No file selected. Please select the inventory excel file and upload");
                return default;
            }
            var file = Request.Files[0];
            var strFile = file.FileName;
            string ext = Path.GetExtension(file.FileName);
            if (!".xlsx, .xlsm, .xls".Split(',').Contains(ext))
            {
                SendResponse("Failure!! Invalid file selected. Please upload a valid excel file");
            }

            IWorkbook wb = new XSSFWorkbook(file.InputStream);

            if (wb.NumberOfSheets < 1 || wb.GetSheetAt(0).PhysicalNumberOfRows < 2 || wb.GetSheetAt(0).GetRow(0).PhysicalNumberOfCells < 5)
            {
                SendResponse("Failure!! Insufficient data in the document selected. Please upload excel with single sheet, contain more than 1 row and minimum 5 columns. Please refer the sample excel available using the link available in the page.");
                return default;
            }
            int importcount = 0, failureCount = 0;
            var sheet = wb.GetSheetAt(0);
            var headerrow = sheet.GetRow(0);
            int index_id = -1, index_Amount = -1, index_BankAccount = -1, index_utrno = -1;
            if (headerrow != null)
            {
                for (int i = 0; i < headerrow.PhysicalNumberOfCells; i++)
                {
                    ICell cell = headerrow.GetCell(i, MissingCellPolicy.RETURN_BLANK_AS_NULL);
                    if (cell == null)
                        continue;
                    //string val = (cell.CellType == CellType.Numeric ? cell.NumericCellValue.ToString() : cell.StringCellValue.Replace("{", "").Replace("}", ""));
                    string strVal = cell.StringCellValue;//.Replace("{", "").Replace("}", "").Trim().Replace(" ", "").ToLower();
                    if (strVal == "GROZEO Settlement ID")
                        index_id = i;
                    else if (strVal == "Amount")
                        index_Amount = i;
                    else if (strVal == "Beneficiary Account Number")
                        index_BankAccount = i;
                    else if (strVal == "UTR Number")
                        index_utrno = i;
                }
            }
            if (index_id < 0 || index_Amount < 0 || index_BankAccount < 0 || index_utrno < 0)
            {
                SendResponse("Failure!! Missing fields in file. Please ensure that the excel having header row with the missing fields " + (index_id < 0 ? "id" : "") + (index_Amount < 0 ? " ,Amount" : "") + (index_BankAccount < 0 ? " ,Beneficiary Account Number" : "") + (index_utrno < 0 ? " ,UTR Number" : ""));
                return default;
            }

            List<SettlementData> settlements = new List<SettlementData>();
            for (int i = 1; i < sheet.PhysicalNumberOfRows; i++)
            {
                string settlementid = "", Amount = "", BeneficiaryAccountNumber = "", UTRNumber = "";
                try
                {
                    var row = sheet.GetRow(i);
                    if (row != null)
                    {
                        ICell cell_id = row.GetCell(index_id, MissingCellPolicy.RETURN_BLANK_AS_NULL);
                        settlementid = (cell_id.CellType == CellType.Numeric ? cell_id.NumericCellValue.ToString() : cell_id.StringCellValue.Replace("{", "").Replace("}", "")); // stit_id or erp id
                                                                                                                                                                                 //int settlement_id = 0; try { settlement_id = Convert.ToInt32(settlementid); } catch { settlement_id = 0; }

                        ICell cell_stock = row.GetCell(index_Amount, MissingCellPolicy.RETURN_BLANK_AS_NULL);
                        Amount = (cell_stock.CellType == CellType.Numeric ? cell_stock.NumericCellValue.ToString() : cell_stock.StringCellValue.Replace("{", "").Replace("}", ""));
                        double SettleAmount = 0; try { SettleAmount = Convert.ToDouble(Amount); } catch { SettleAmount = 0; }

                        ICell cell_mrp = row.GetCell(index_BankAccount, MissingCellPolicy.RETURN_BLANK_AS_NULL);
                        BeneficiaryAccountNumber = (cell_mrp.CellType == CellType.Numeric ? cell_mrp.NumericCellValue.ToString() : cell_mrp.StringCellValue.Replace("{", "").Replace("}", ""));
                        //decimal AccountNumber = 0; try { AccountNumber = Convert.ToDecimal(BeneficiaryAccountNumber); } catch { AccountNumber = 0; }
                        string storegroupname = Getstoregroup(settlementid);
                        ICell cell_sellingPrice = row.GetCell(index_utrno, MissingCellPolicy.RETURN_BLANK_AS_NULL);
                        if (cell_sellingPrice != null)
                        {
                            UTRNumber = (cell_sellingPrice.CellType == CellType.Numeric ? cell_sellingPrice.NumericCellValue.ToString() : cell_sellingPrice.StringCellValue.Replace("{", "").Replace("}", ""));

                        }
                        settlements.Add(new SettlementData { AccountNo = BeneficiaryAccountNumber, Amount = SettleAmount, StoreGroup = storegroupname, SettlementId = settlementid, UTRno = UTRNumber });
                    }
                    //decimal utrno = 0; try { utrno = Convert.ToDecimal(UTRNumber); } catch { utrno = 0; }                    
                }
                catch (Exception ex)
                {
                    failureCount++;
                    strResults.Add($"Error stit_id: {settlementid}, {ex.Message}");
                }

                gvupload.DataSource = settlements;
                gvupload.DataBind();

            }
            if (failureCount > 0)
                strResults.Insert(0, $"Errors: {failureCount}");

            strResults.Insert(0, $"{importcount} records imported.");

            return strResults;
        }
        private void SendResponse(string content, bool success = false)
        {
            Response.Clear();
            Response.Buffer = true;
            Response.Charset = "";
            var obj = new { result = (success ? 1 : 0), status = (success ? "Success" : "Error"), data = content };

            string jsoncontent = JsonConvert.SerializeObject(obj);

            Response.Write(jsoncontent);

            Response.Flush();
            Response.End();

        }
        public static string Getstoregroup(string SettlementId)
        {
            string storegroup = "";
            List<KeyValuePair<string, object>> sqlSettle = new List<KeyValuePair<string, object>>();
            sqlSettle.Add(new KeyValuePair<string, object>("settlementid", SettlementId));
            string store = "SELECT fb.store_group_name FROM finance_transaction t INNER JOIN finascop_branch_group fb ON fb.store_group_id=t.storegroup_id where settlement_id=@settlementid";
            var dtstoregroup = DataServiceMySql.GetDataTable(store, parmeters: sqlSettle);
            if (dtstoregroup != null && dtstoregroup.Rows.Count > 0)
            {
                storegroup = dtstoregroup.Rows[0]["store_group_name"].ToString();
            }
            return (storegroup);
        }
        public class SettlementData
        {
            public string SettlementId { get; set; }
            public double Amount { get; set; }
            public string StoreGroup { get; set; }
            public string AccountNo { get; set; }
            public string UTRno { get; set; }

        }
        public SettlementVoucherEntryData CreateSettlementData(string settlementId, out string strResult)
        {
            strResult = "";
            double debit = 0.00, credit = 0.00;
            int ledgerId = 0, entry_type = 1, br_ID_store_group = 0;
            string particulars, reference, Store_group_name = "", entityid = "", storeGroupRefId = "", br_Name_store_group = "";
            string order_event = "", Narration = "", refno = "";

            // Check if the settlement id is not yet completed already. Otherwise add failure text to strFailureResult and continue.
            List<KeyValuePair<string, object>> sqlSettle = new List<KeyValuePair<string, object>>();
            sqlSettle.Add(new KeyValuePair<string, object>("settlementid", settlementId));
            particulars = "";
            string settlement = "SELECT bg.storeRefId,bg.store_group_name,fb.br_name,fb.br_id, status_id,UTRno,payout_amount AS cr,0 AS dr FROM finance_transaction ms INNER JOIN finascop_branch fb ON ms.branch_id=fb.br_id INNER JOIN finascop_branch_group bg ON bg.store_group_id=br_storeGroup where settlement_id=@settlementid";
            var dtpaymentupdate = DataServiceMySql.GetDataTable(settlement, parmeters: sqlSettle);
            if (dtpaymentupdate == null || dtpaymentupdate.Rows.Count <= 0)
            {
                strResult = "No data available for settlement id: " + settlementId;
                return null;
            }
            storeGroupRefId = dtpaymentupdate.Rows[0]["storeRefId"].ToString();
            //double Dr = Convert.ToDouble(dtpaymentupdate.Rows[0]["dr"].ToString());
            var ledgerDetails = Getledgerdetails(storeGroupRefId);
            //particulars = ledgerDetails.Item1;
            //ledgerId = ledgerDetails.Item2;
            credit = Convert.ToDouble(dtpaymentupdate.Rows[0]["cr"].ToString());
            Store_group_name = dtpaymentupdate.Rows[0]["store_group_name"].ToString();
            return new SettlementVoucherEntryData
            {
                credit = credit,
                debit = debit,
                ledgerId = ledgerDetails.Item2,
                particulars = ledgerDetails.Item1,
                Store_group_name = Store_group_name,
                entityid = entityid,
                entry_type = entry_type,
                storeGroupRefId = dtpaymentupdate.Rows[0]["storeRefId"].ToString(),
                br_Name_store_group = dtpaymentupdate.Rows[0]["br_name"].ToString(),
                br_ID_store_group = Convert.ToInt32(dtpaymentupdate.Rows[0]["br_ID"].ToString()),
                order_event = "Settlement Approval",
                TransactionTypeId = "3",
                docTypeID = "3"

            };
        }
        public static string getrefernce(string ref_id)
        {
            string refernance = "";
            List<KeyValuePair<string, object>> sqlref = new List<KeyValuePair<string, object>>();
            sqlref.Add(new KeyValuePair<string, object>("id", ref_id));
            string getRef = "select docSerialNo,createdOn from data_entry where entry_RefId=@id";
            var dtref = DataService.GetDataTable(getRef, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqlref);
            if (dtref != null && dtref.Rows.Count > 0)
            {
                var refno = dtref.Rows[0];
                string voucher = refno["docSerialNo"].ToString();
                string Date = ((DateTime)refno["createdOn"]).ToString("dd/MMM/yyyy");

                string Refernce = "Settlement voucher {#bank payment voucher no#} dated {#voucher date#}";
                refernance = Refernce.Replace("{#bank payment voucher no#}", voucher)
                      .Replace("{#voucher date#}", Date);
            }
            return refernance;
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
                    Common.ShowCustomAlert(this.Page, "Success", "Payment updated successfully.", true, "/Finance/ManageBulkUpload");

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

        protected void btnView_Click(object sender, EventArgs e)
        {
            txttransationno.Enabled = false;
            txtdate.ReadOnly = true;
            btnapprove.Visible = false;
            LinkButton lbtn = (LinkButton)sender;
            string Id = lbtn.Attributes["getrefid"];
            string Ref_id = Id + "_reversal";
            getreject(Ref_id);
        }
        public string getreject(string Ref_id)
        {
            string ref_id = "";
            List<KeyValuePair<string, object>> sqlposting = new List<KeyValuePair<string, object>>();
            sqlposting.Add(new KeyValuePair<string, object>("refno", Ref_id));
            string geposting = "SELECT tr.particulars,ledger_id, isnull (CASE WHEN [isDebtor] = 1 THEN  tr.amount  END,0) AS dr_amount,isnull (CASE WHEN [isDebtor] =0 THEN  tr.amount  END,0) AS cr_amount FROM transactions tr INNER JOIN  data_entry de ON tr.data_entry_id =de.id WHERE entry_RefId=@refno";
            DataTable dtposting = DataService.GetDataTable(geposting, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqlposting);
            List<SettlementVoucherEntryData> AddlstData = new List<SettlementVoucherEntryData>();
            if (dtposting == null || dtposting.Rows.Count <= 0)
            {
                return "";
            }
            foreach (DataRow dr in dtposting.Rows)
            {
                AddlstData.Add(new SettlementVoucherEntryData
                {
                    credit = Convert.ToDouble(dr["cr_amount"]),
                    debit = Convert.ToDouble(dr["dr_amount"]),
                    ledgerId = Convert.ToInt32(dr["ledger_id"]),
                    particulars = dr["particulars"].ToString(),


                });
            }
            lvsettlement.DataSource = AddlstData;
            lvsettlement.DataBind();
            Literal ltrdrtotal = (Literal)lvsettlement.FindControl("ltrDrTotal");
            Literal ltrcrtotal = (Literal)lvsettlement.FindControl("ltrCRTotal");
            if (ltrdrtotal != null && ltrcrtotal != null)
            {
                double drtotal = 0, crtotal = 0;
                try { drtotal = AddlstData.Sum(d => d.debit); } catch { }
                try { crtotal = AddlstData.Sum(d => d.credit); } catch { }

                ltrdrtotal.Text = String.Format("{0:0.00}", drtotal).ToString();
                ltrcrtotal.Text = String.Format("{0:0.00}", crtotal).ToString();
            }
            ref_id = Ref_id;
            if (!Ref_id.Contains("_reversal"))
            {
                ref_id = Ref_id.Replace("_reversal", "");
            }
            List<KeyValuePair<string, object>> sql = new List<KeyValuePair<string, object>>();
            sql.Add(new KeyValuePair<string, object>("refno", ref_id));
            string gettransactionno = "SELECT TransactionNumber,FilePath,Filename,Transactiondate FROM BankFileDetails WHERE TransactionRef_id=@refno";
            DataTable dttrno = DataServiceMySql.GetDataTable(gettransactionno, parmeters: sql);
            if (dttrno != null && dttrno.Rows.Count > 0)
            {
                var Trno = dttrno.Rows[0];
                txttransationno.Text = Trno["TransactionNumber"].ToString();
                string date = ((DateTime)Trno["Transactiondate"]).ToString("dd-MM-yyyy");
                txtdate.Text = date;
                txtdate.TextMode = (txtdate.ReadOnly ? TextBoxMode.SingleLine : TextBoxMode.Date);
                //txtdate.Text= Trno["Transactiondate"].ToString();
                lbstoregroup.Text = Trno["Filename"].ToString();
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