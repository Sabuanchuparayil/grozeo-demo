using Finascop.BussinessModel;
using Newtonsoft.Json;
using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using RestSharp;
using RetalineProAgent.Core.BussinessModel;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
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
    public partial class SettlementReports : Base.BasePartnerPage
    {
        [Serializable]
        public class ManualSettlementVoucherEntryData
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
        public List<ManualSettlementVoucherEntryData> lstmanualsettlementVoucherEntry
        {
            get
            {
                if (ViewState["LEDGERENTRYLIST"] != null)
                    return (List<ManualSettlementVoucherEntryData>)ViewState["LEDGERENTRYLIST"];
                return new List<ManualSettlementVoucherEntryData>();
            }
            set
            {
                ViewState["LEDGERENTRYLIST"] = value;
            }
        }
        private static string appregion = "eu-west-2";
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
        protected void Page_PreRender(object sender, EventArgs e)
        {

        }
        protected void btndownload_Click(object sender, EventArgs e)
        {


        }

        protected void btnAction_Click(object sender, EventArgs e)
        {

            try
            {
                LinkButton lbtn = (LinkButton)sender;
                hidValueHeadOrderId.Value = (lbtn.Attributes["recid"]);
                string Id = hidValueHeadOrderId.Value;
                List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
                sqldaId.Add(new KeyValuePair<string, object>("id", Id));
                string settlement = "SELECT t.settlement_id,t.status_id,ms.created_at,t.ifsc_code,t.account_number,fb.store_group_name,settlement_date,ms.updated_at,payout_amount, SUM(o.sale_proceeds) AS amounttotal,SUM(o.expenses) AS expenses FROM finance_transaction t INNER JOIN `finance_transaction_log` tl ON t.id=tl.ft_id INNER JOIN merchant_settlements ms ON ms.id=tl.ms_id INNER JOIN finascop_branch_group fb ON fb.store_group_id=t.storegroup_id INNER JOIN merchant_settlements_order o ON ms.ref_id=o.ms_ref_id WHERE t.id=@id";
                var dt = DataServiceMySql.GetDataTable(settlement, parmeters: sqldaId);
                if (dt != null && dt.Rows.Count > 0)
                {
                    var settlementdetails = dt.Rows[0];
                    lbstoregroup.Text = settlementdetails["store_group_name"].ToString();
                    lbsettlementid.Text = settlementdetails["settlement_id"].ToString(); ;
                    lbsettlementdate.Text = ((DateTime)settlementdetails["settlement_date"]).ToString("dd MMM yyyy");
                    lbinitiateddate.Text = ((DateTime)settlementdetails["created_at"]).ToString("dd MMM yyyy");
                    string account = settlementdetails["account_number"]?.ToString();
                    string ifsc = settlementdetails["ifsc_code"]?.ToString();
                    lbbankaccount.Text = string.IsNullOrWhiteSpace(account) ? ifsc ?? "" : string.IsNullOrWhiteSpace(ifsc) ? account : $"{account}/{ifsc}";
                    lbamount.Text = decimal.TryParse(settlementdetails["payout_amount"]?.ToString(), out var amt) ? amt.ToString("N2") : "0.00";
                    bool isPending = settlementdetails["status_id"]?.ToString() == "1";
                    btnreject.Visible = btnapprove.Visible = isPending;
                    btnpayonline.Visible = settlementdetails["status_id"]?.ToString() == "2";
                }

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
            catch(Exception ex)
            {
                Common.ShowCustomAlert(this.Page, "Technical Error", "An unexpected error occurred while processing your request. Please try again later", false, "/Finance/SettlementReports");
            }
            //settlement Details

        }

        protected void btnExport_Click(object sender, EventArgs e)
        {
            int indx = 0;
            List<string> selectedIds = new List<string>();
            foreach (GridViewRow row in gvSettlementReport.Rows)
            {
                if (row.RowType == DataControlRowType.DataRow)
                {
                    CheckBox chkAction = (CheckBox)row.FindControl("chkAction");
                    if (chkAction != null && chkAction.Checked)
                    {
                        string currentId = gvSettlementReport.DataKeys[row.RowIndex].Value.ToString();

                        selectedIds.Add(currentId);
                    }
                }
            }
            List<KeyValuePair<string, object>> sqlParameters = new List<KeyValuePair<string, object>>();
            List<string> parameterNamePlaceholders = new List<string>();
            foreach (string id in selectedIds)
            {
                string paramName = $"@id{indx}";
                sqlParameters.Add(new KeyValuePair<string, object>(paramName, id));
                parameterNamePlaceholders.Add(paramName);
                indx++;
            }
            string inClause = string.Join(",", parameterNamePlaceholders);
            string settlement = "SELECT t.*,b.FileName FROM finance_transaction t  LEFT JOIN `BankFileDetails` b ON  t.FileId=b.id WHERE t.id in (" + inClause + ")";
            var db = DataServiceMySql.GetDataTable(settlement, parmeters: sqlParameters);
            List<DataRow> dataRowsNeedingNewFiles = new List<DataRow>();
            List<string> idsWithExistingFiles = new List<string>();
            List<string> downloadLinks = new List<string>();
            Dictionary<string, string> existingIdToFileNames = new Dictionary<string, string>();
            if (db != null && db.Rows.Count > 0)
            {
                foreach (DataRow row in db.Rows)
                {
                    string id = row["id"].ToString();
                    string transactionId = row["id"].ToString();
                    if (db.Columns.Contains("FileName") && row["FileName"] != DBNull.Value && !string.IsNullOrEmpty(row["FileName"].ToString()))
                    {
                        string FileName = row["FileName"].ToString();
                        idsWithExistingFiles.Add(transactionId);
                        existingIdToFileNames[transactionId] = FileName;
                        string fileName = row["FileName"].ToString();
                        existingIdToFileNames[id] = fileName;
                        string fileUrl = ConfigurationManager.AppSettings.Get("finance.url") + fileName;
                        downloadLinks.Add($"<a href=\"{fileUrl}\" download=\"{fileName}\" target=\"_blank\">Download file:{fileName}</a>");
                    }
                    else
                    {
                        dataRowsNeedingNewFiles.Add(row);
                    }
                   
                }
            }

            string combinedMessage = "";
            if (dataRowsNeedingNewFiles.Count > 0)
            {
                // generate file
                string newfilename = ExportGridToExcel(dataRowsNeedingNewFiles);
                if (newfilename != null)
                {
                    string strFileUrl = ConfigurationManager.AppSettings.Get("finance.url") + newfilename;
                    downloadLinks.Add($"<a href=\"{strFileUrl}\" download=\"{newfilename}\" target=\"_blank\">Download Generated File :{newfilename}</a>");
                }
            }
             combinedMessage = string.Join(", ", downloadLinks);
            if (combinedMessage != null)
            {
                Common.ShowCustomAlert(this.Page, "File Created", "To download the file, go to the Manage Bulk Upload menu.", true, " /Finance/SettlementReports");
            }
            else
            {
                Common.ShowCustomAlert(this.Page, "Technical Error", "An unexpected error occurred while processing your request. Please try again later", false);
            }
        }
        private string ExportGridToExcel(List<DataRow> dataToExport)
        {           
            try
            {
                if (dataToExport == null || !dataToExport.Any()) 
                {
                    throw new Exception("No data provided for export."); 
                }
                DataTable dt = new DataTable("ExportData");
                foreach (DataColumn col in dataToExport.First().Table.Columns)
                {
                    dt.Columns.Add(col.ColumnName, col.DataType);
                }
                foreach (DataRow row in dataToExport)
                {
                    DataRow newRow = dt.NewRow(); 

                    foreach (DataColumn col in dt.Columns)
                    {
                       
                        if (row.Table.Columns.Contains(col.ColumnName))
                        {
                            newRow[col.ColumnName] = row[col.ColumnName]; 
                        }
                    }
                    dt.Rows.Add(newRow); 
                }
                int settlecount = dt.Rows.Count;
                IWorkbook wb = new XSSFWorkbook();
                ISheet sheet = wb.CreateSheet("Settlement Report");
                ICreationHelper cH = wb.GetCreationHelper();
                int rows = 0;
                IRow rowH = sheet.CreateRow(rows++);
                double totalAmount = 0;
                string strFieldLabels = "bank_account_name,Beneficiary Name|account_number,Beneficiary Account Number|ifsc_code,IFSC|transaction_type,Transaction Type| ,Debit Account Number| ,Transaction Date|payout_amount,Amount|,Currency|bank_account_email,Beneficiary Email Id|,Remarks|settlement_id,GROZEO Settlement ID";//.Split('|');
                if (ConfigurationManager.AppSettings.Get("CountryCode") != "IN")
                    strFieldLabels = "bank_account_name,Name|,Recipient type|account_number,Account Number|ifsc_code,Sort code|,Recipient bank country|,Currency|payout_amount,Amount|,Remarks|settlement_id,GROZEO Settlement ID";//.Split('|');

                foreach (string dc in strFieldLabels.Split('|'))
                {
                    ICell cell = rowH.CreateCell(rowH.Cells.Count);
                    cell.SetCellValue(cH.CreateRichTextString(dc.Split(',')[1]));
                }

                IRow rowH2 = sheet.CreateRow(rows++);
                foreach (string dc in strFieldLabels.Split('|'))
                {
                    ICell cell = rowH2.CreateCell(rowH2.Cells.Count);
                    cell.SetCellValue(cH.CreateRichTextString(rowH2.Cells.Count.ToString()));
                }

                List<string> settlementIds = new List<string>();
                foreach (DataRow dr in dt.Rows)
                {
                    var fieldLabels = strFieldLabels.Split('|');
                    IRow row = sheet.CreateRow(rows++);
                    for (int j = 0; j < fieldLabels.Length; j++)
                    {
                        ICell cell = row.CreateCell(j);
                        string strField = fieldLabels[j].Split(',')[0];
                        string cellVal = "";
                        // Assign the hardcoded values for specific indices
                        if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
                        {
                            if (j == 4)
                            {
                                cellVal = "18283848580";

                            }
                            else if (j == 5)
                            {
                                cellVal = DateTime.Now.ToString("dd/MM/yyyy");
                            }
                            else if (j == 7)
                            {
                                cellVal = "INR";
                            }
                            else if (j == 9)
                            {
                                cellVal = "Grozeo Order Settlement";
                            }
                            else if (!String.IsNullOrEmpty(strField.Trim()) && dr.Table.Columns.Contains(strField))
                            {
                                try
                                {
                                    cellVal = dr[strField].ToString();
                                }
                                catch { }

                            }
                        }
                        else if (ConfigurationManager.AppSettings.Get("CountryCode") != "IN")
                        {
                            if (j == 1)
                            {
                                cellVal = "INDIVIDUAL";
                            }
                            else if (j == 4)
                            {
                                cellVal = "UK";
                            }
                            else if (j == 5)
                            {
                                cellVal = "pound";
                            }
                            else if (!String.IsNullOrEmpty(strField.Trim()) && dr.Table.Columns.Contains(strField))
                            {
                                try
                                {
                                    cellVal = dr[strField].ToString();
                                }
                                catch { }

                            }
                        }
                        cell.SetCellValue(cH.CreateRichTextString(cellVal));

                    }
                    if (dr.Table.Columns.Contains("settlement_id"))
                    {
                        // Get the value from the "settlement_id" column
                        string settlementId = dr["settlement_id"].ToString();

                        // Add the settlement ID to the list
                        settlementIds.Add(settlementId);
                    }
                    if (dr.Table.Columns.Contains("payout_amount"))
                    {
                        totalAmount += Convert.ToDouble(dr["payout_amount"].ToString());
                    }
                }
                string pmr_Bank_prefix = "";
                if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
                {
                    pmr_Bank_prefix = "IDFC";
                }
                else
                {
                    pmr_Bank_prefix = "RESOLUT";
                }
                string pmr_date_format_prefix = DateTime.Now.ToString("ddMMMyy").ToUpper();
                List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                prms.Add(new KeyValuePair<string, object>("pmr_Bank_prefix", pmr_Bank_prefix));
                prms.Add(new KeyValuePair<string, object>("pmr_date_format_prefix", pmr_date_format_prefix));
                DataTable invoice = DataServiceMySql.GetDataTable("generateBankFileName", UserService.GetAPIConnectionString(), prms, true);
                string fileNamenewm = "";
                if (invoice != null && invoice.Rows.Count > 0)
                {
                    fileNamenewm = invoice.Rows[0]["FileName"].ToString();

                }
                string fileNamenew = fileNamenewm + ".xlsx"; //"GMS_" + DateTime.Now.ToString("ddMMyyyy") + ".xlsx";
                string strFileUrl = "";
                string tempDir = HttpContext.Current.Server.MapPath("~/App_Data/Temp");
                // Ensure the directory exists
                if (!Directory.Exists(tempDir))
                    Directory.CreateDirectory(tempDir);

                // Combine the directory and file name to get the full temporary file path
                string tempFilePath = Path.Combine(tempDir, fileNamenew);
                // Create a FileStream to write the Excel file content to the temporary file
                using (FileStream fs = new FileStream(tempFilePath, FileMode.Create, FileAccess.Write))
                {
                    wb.Write(fs);
                }
                // Read the content of the file into a MemoryStream
                using (FileStream fileStream = new FileStream(tempFilePath, FileMode.Open, FileAccess.Read))
                {
                    MemoryStream memoryStream = new MemoryStream();
                    fileStream.CopyTo(memoryStream);
                    memoryStream.Position = 0;
                    string region = ConfigurationManager.AppSettings.Get("AWS_Region");
                    string Buckename = ConfigurationManager.AppSettings.Get("AWS_S3_BucketFinance");
                    strFileUrl = FileService.UploadFileToS3(region, memoryStream, fileNamenew, Buckename, "", "", true);

                }
                System.IO.File.Delete(tempFilePath);
                // add the bank file
                string getupload = "INSERT INTO BankFileDetails (status_id,FileName,FilePath,CreatedOn,SettlementCount,Amount) VALUES(1,@FileName,@Fileurl,@date,@count,@Amount);SELECT LAST_INSERT_ID()";
                string formattedDateTime = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
                List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
                sqldaId.Add(new KeyValuePair<string, object>("FileName", fileNamenew));
                sqldaId.Add(new KeyValuePair<string, object>("Fileurl", strFileUrl));
                sqldaId.Add(new KeyValuePair<string, object>("date", formattedDateTime));
                sqldaId.Add(new KeyValuePair<string, object>("count", settlecount));
                sqldaId.Add(new KeyValuePair<string, object>("Amount", totalAmount));
                var fileid = DataServiceMySql.ExecuteScalar(getupload, Service.UserService.GetAPIConnectionString(), sqldaId);
                int getfileid = Convert.ToInt32(fileid);
                sqldaId.Add(new KeyValuePair<string, object>("fileid", getfileid));
                //update the status of settlement
                foreach (string settlementId in settlementIds)
                {
                    if (sqldaId.Any(k => k.Key == "settleId"))
                        sqldaId.Remove(sqldaId.Where(k => k.Key == "settleId").FirstOrDefault());
                    sqldaId.Add(new KeyValuePair<string, object>("settleId", settlementId));
                    string getfile_id = "UPDATE finance_transaction SET status_id=5,FileId=@fileid WHERE settlement_id=@settleId";
                    DataServiceMySql.ExecuteSql(getfile_id, Service.UserService.GetAPIConnectionString(), sqldaId);
                } 
                // activity log
                String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                string Source = strUrl;
                int storegroupid = -1;
                string User = "Finance Admin";
                var items = new[]
                {
                    new { Key = "FileName", Value = fileNamenew },
                    new { Key = "Fileurl", Value = strFileUrl },
                    new { Key = "Date", Value = formattedDateTime },
                    new { Key = "count", Value = settlecount.ToString() },
                    new { Key = "Amount", Value = totalAmount.ToString() },
                    new { Key = "page", Value = "Settlement" },
                    new { Key = "settleId", Value = settlementIds.ToString() }
                };
                string description = string.Join(", ", items.Select(i => $"{i.Key}={i.Value}"));
                var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, description);               
                return fileNamenew;
            }
            catch(Exception ex)
            {
                throw new Exception("An exception occurred during file generation.", ex);
            }

        }

        protected void lvsettlement_ItemDataBound(object sender, ListViewItemEventArgs e)
        {

        }

        protected void lvsettlement_DataBound(object sender, EventArgs e)
        {
            try
            {
                //to get the sum values
                List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
                sqldaId.Add(new KeyValuePair<string, object>("id", hidValueHeadOrderId.Value));
                string settlementdetails = "SELECT t.settlement_id,fb.store_group_name,settlement_date,ms.updated_at,payout_amount, SUM(o.sale_proceeds) AS amounttotal,SUM(o.expenses) AS expenses FROM finance_transaction t INNER JOIN `finance_transaction_log` tl ON t.id=tl.ft_id INNER JOIN merchant_settlements ms ON ms.id=tl.ms_id INNER JOIN finascop_branch_group fb ON fb.store_group_id=t.storegroup_id INNER JOIN merchant_settlements_order o ON ms.ref_id=o.ms_ref_id WHERE t.id=@id";
                var dtsettle = DataServiceMySql.GetDataTable(settlementdetails, parmeters: sqldaId);
                if (dtsettle != null && dtsettle.Rows.Count > 0)
                {
                    var settlementdetailsamount = dtsettle.Rows[0];
                    Literal ltrtotalamount = (Literal)lvsettlement.FindControl("ltttotalamount");
                    Literal ltrtotaldeduction = (Literal)lvsettlement.FindControl("ltrdeduction");
                    Literal ltrsettlementamount = (Literal)lvsettlement.FindControl("ltrsettleamount");
                    if (ltrtotalamount != null && ltrtotalamount != null && ltrsettlementamount != null)
                    {
                        ltrtotalamount.Text = settlementdetailsamount["amounttotal"].ToString();
                        ltrtotaldeduction.Text = settlementdetailsamount["expenses"].ToString();
                        ltrsettlementamount.Text = settlementdetailsamount["payout_amount"].ToString();
                    }
                }
            }
            catch(Exception ex)
            {
                Common.ShowCustomAlert(this.Page, "Technical Error", "An unexpected error occurred while processing your request. Please try again later", false, "/Finance/SettlementReports");
            }

        }

        protected void btnposting_Click(object sender, EventArgs e)
        {

        }

        protected void btnapprove_Click(object sender, EventArgs e)
        {
            try
            {
                //approve the settlement
                string id = hidValueHeadOrderId.Value;
                User user = Infrastructure.PartnerContext.Current.User ?? Service.UserService.CachedDefaultUser;
                int currentuser = user.Id;
                string formattedDateTime = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
                List<KeyValuePair<string, object>> sqlRefId = new List<KeyValuePair<string, object>>();
                sqlRefId.Add(new KeyValuePair<string, object>("id", id));
                sqlRefId.Add(new KeyValuePair<string, object>("currentuser", currentuser));
                sqlRefId.Add(new KeyValuePair<string, object>("dateandtime", formattedDateTime));
                string excutesettlement = "UPDATE finance_transaction AS ft INNER JOIN finance_transaction_log ftl ON ft.id = ftl.ft_id INNER JOIN merchant_settlements ms ON ms.id=ftl.ms_id SET ft.status_id = 2,approved_by=@currentuser,approved_date=@dateandtime WHERE ftl.ft_id =@id ";
                DataServiceMySql.ExecuteSql(excutesettlement, Service.UserService.GetAPIConnectionString(), sqlRefId);
                // Activitylog
                String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                string Source = strUrl;
                int storegroupid = -1;
                string User = "Finance Admin";
                string Id = id;
                string Users = Convert.ToString(user.Id);
                string Date = formattedDateTime;
                string page = "Settlement Approve";
                var items = new[]
                {
                            new { Key = "Id", Value = Id },
                            new { Key = "Users", Value = Users },
                            new { Key = "Date", Value = Date },
                            new { Key = "page", Value = page },
            };
                string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
                Common.ShowCustomAlert(this.Page, "Success", "Approved successfully!", true, "/Finance/SettlementReports");
            }
            catch(Exception ex)
            {
                Common.ShowCustomAlert(this.Page, "Technical Error", "An unexpected error occurred while processing your request. Please try again later", false, "/Finance/SettlementReports");

            }

        }

        protected void btnreject_Click(object sender, EventArgs e)
        {   //reject the settlement
            string id = hidValueHeadOrderId.Value;
            List<KeyValuePair<string, object>> sqlRef = new List<KeyValuePair<string, object>>();
            sqlRef.Add(new KeyValuePair<string, object>("id", id));
            string excutesettlement = "UPDATE finance_transaction AS ft INNER JOIN finance_transaction_log ftl ON ft.id = ftl.ft_id INNER JOIN merchant_settlements ms ON ms.id=ftl.ms_id SET ft.status_id = 4 WHERE ftl.ft_id =@id ";
            DataServiceMySql.ExecuteSql(excutesettlement, Service.UserService.GetAPIConnectionString(), sqlRef);
            // Activitylog
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            string Source = strUrl;
            int storegroupid = -1;
            string User = "Finance Admin";
            string Id = id;           
            string page = "Settlement Reject";
            var items = new[]
            {
                            new { Key = "Id", Value = Id },                           
                            new { Key = "page", Value = page },
            };
            string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
            var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
            Common.ShowCustomAlert(this.Page, "Success", "Reject successfully!", true, "/Finance/SettlementReports");
        }
        protected void btnskip_Click(object sender, EventArgs e)
        {

        }

        protected void selsettlement_SelectedIndexChanged(object sender, EventArgs e)
        {

        }
        protected void dlentrytpeupdae_SelectedIndexChanged(object sender, EventArgs e)
        {
            string value = dlentrytpeupdae.SelectedValue;
            if (value == "2")
            {
                btnExport.Enabled = true;
                btnonlinepay.Enabled = true;
            }
            else
            {
                btnExport.Enabled = false;
                btnonlinepay.Enabled = false;

            }
        }

        protected void btnSubmitBankRef_Click(object sender, EventArgs e)
        {
            List<KeyValuePair<string, object>> rms = new List<KeyValuePair<string, object>>();
            rms.Add(new KeyValuePair<string, object>("id", hidValueHeadOrderId.Value));
            rms.Add(new KeyValuePair<string, object>("refernce", txtBankRef.Text));
            string mannualsettlement = "UPDATE finance_transaction SET status_id=3,bank_ref_no=@refernce where id=@id";
            DataServiceMySql.ExecuteSql(mannualsettlement, Service.UserService.GetAPIConnectionString(), rms);
            //getsettlementposing(Convert.ToInt32(hidValueHeadOrderId.Value));           
        }

        public void getsettlementposing(List<string> transactionIds, bool isRejected = false)
        {
            try
            {
                if (transactionIds == null || transactionIds.Count == 0)
                    return;
                var paramEntries = transactionIds.Select((txnId, index) =>
                    new KeyValuePair<string, object>($"@id{index}", txnId)).ToList();
                string idList = string.Join(",", paramEntries.Select(p => p.Key));

                string getsettle = "SELECT 0 AS cr, bg.storeRefId,bg.store_group_name,fb.br_name,fb.br_id,br_name,br_ID, status_id,UTRno,payout_amount FROM finance_transaction ms INNER JOIN finascop_branch fb ON ms.branch_id=fb.br_id  INNER JOIN finascop_branch_group bg ON bg.store_group_id=br_storeGroup WHERE ms.id IN (" + idList + ") UNION ALL SELECT payout_amount AS cr,(SELECT bg.storeRefId FROM finascop_branch_group bg INNER JOIN finascop_branch fb ON t.branch_id = fb.br_id WHERE bg.store_group_id = br_storeGroup LIMIT 1) AS storeRefId,'','',0,'',0, 0,'',0 FROM finance_transaction t WHERE t.id IN (" + idList + ")";
                DataTable dt = DataServiceMySql.GetDataTable(getsettle, parmeters: paramEntries);
                List<ManualSettlementVoucherEntryData> lstData = new List<ManualSettlementVoucherEntryData>();
                foreach (DataRow dr in dt.Rows)
                {
                    double cr = Convert.ToDouble(dr["cr"]);
                    var ledgerDetails =  Getledgerdetails(dr["storeRefId"].ToString());
                    int ledgerId = isRejected? (cr == 0 ? Convert.ToInt32(ConfigurationManager.AppSettings.Get("SettlementSourceBankId")): ledgerDetails.Item2): (cr == 0? ledgerDetails.Item2 : Convert.ToInt32(ConfigurationManager.AppSettings.Get("SettlementSourceBankId")));
                    string particulars = isRejected? (cr == 0? ConfigurationManager.AppSettings.Get("SettlementSourceBankName"): ledgerDetails.Item1): (cr == 0 ? ledgerDetails.Item1: ConfigurationManager.AppSettings.Get("SettlementSourceBankName"));

                    lstData.Add(new ManualSettlementVoucherEntryData
                    {
                        credit = Convert.ToDouble(dr["cr"]),
                        debit = Convert.ToDouble(dr["payout_amount"]),
                        ledgerId = ledgerId,
                        particulars = particulars,
                        Store_group_name = dr["store_group_name"].ToString(),
                        entityid = "",
                        entry_type = 1,
                        storeGroupRefId = dr["storeRefId"].ToString(),
                        br_Name_store_group = dr["br_name"].ToString(),
                        br_ID_store_group = Convert.ToInt32(dr["br_ID"].ToString()),
                        order_event = "Settlement Approval",
                        TransactionTypeId = isRejected ?"3": "4",
                        docTypeID = isRejected ? "3" : "4",

                    });
                }
                lstmanualsettlementVoucherEntry = lstData;
                Transaction.TransactionEntry te = CreateTEFromVED(lstmanualsettlementVoucherEntry);
                string entryref = Guid.NewGuid().ToString();
                te.entry_RefId = entryref;
                te.reference = isRejected? "Merchant settlement could not be processed.":"Merchant Settlement Processed";
                te.Narration = isRejected?"Payment transfer failed":"Settlement Transferred to Merchant Account";
                string content = JsonConvert.SerializeObject(te);
                getposting(content);
                // Activitylog
                String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                string Source = strUrl;
                int storegroupid = -1;
                string User = "Finance Admin";
                string transactiono = txtBankRef.Text;
                string id = Convert.ToString(idList);
                string date = txtdate.Text;
                string Ref = Convert.ToString(entryref);
                string contents = Convert.ToString(content);
                string page = "Manual settlement";
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
            catch(Exception ex)
            {
                Common.ShowCustomAlert(this.Page, "Technical Error", "A technical error occurred. Please try again later or contact support if the problem persists", false, "/Finance/SettlementReports");
            }
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
                    Common.ShowCustomAlert(this.Page, "Success", "Payment updated successfully.", true, "/Finance/SettlementReports");

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
                    var ledger = dtledgern.Rows[0];
                    ledgername = ledger["name"].ToString();
                    ledger_id = Convert.ToInt32(ledger["id"].ToString());
                }
                return (ledgername, ledger_id);
            }
            catch { }

            return ("", 0);
        }
       
        protected Transaction.TransactionEntry CreateTEFromVED(List<ManualSettlementVoucherEntryData> ved)
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

        public (string, string) getnarration()
        {
            string Narrationresult = "Settlement Transferred to Merchant Account";
            string Refernce = "Merchant Settlement Processed";
            return (Narrationresult, Refernce);
        }

        protected void gvSettlementReport_SelectedIndexChanged(object sender, EventArgs e)
        {
           
        }
        protected void btnonlinepay_Click(object sender, EventArgs e)
        {
            var payments = new List<(string payAccountId, decimal amount,string tranferId)>();
            var selectedList = new List<(string Id, string AccountId, decimal Amount)>();


            foreach (GridViewRow row in gvSettlementReport.Rows)
            {
                var chk = row.FindControl("chkAction") as CheckBox;
                if (chk != null && chk.Checked)
                {
                    string tranferId = (gvSettlementReport.DataKeys[row.RowIndex]["id"] ?? "").ToString();
                    string payAccountId = (gvSettlementReport.DataKeys[row.RowIndex]["PgAccountId"] ?? "").ToString();
                    string amountText = gvSettlementReport.DataKeys[row.RowIndex]["payout_amount"]?.ToString() ?? "0";
                    if (decimal.TryParse(amountText, out decimal amount) && !string.IsNullOrWhiteSpace(payAccountId))
                    {
                        payments.Add((payAccountId, amount, tranferId));
                        selectedList.Add((tranferId, payAccountId, amount));

                    }
                }
            }
            if (payments.Count > 0)
            {
                try
                {
                    // Step 1: Update status to "Initiated"
                    string transferInitiated = "7";
                    var allSelectedIds = selectedList.Select(x => x.Id).ToList();
                    bool initiatedUpdated = UpdateTransactionStatus(allSelectedIds, transferInitiated);
                    // Step 2: Process Paymentgateway payment
                    var gateway = ConfigurationManager.AppSettings["PaymentGateway"];
                    var accountMap = gateway == "razorpay"? payments.ToDictionary(p => $"{p.tranferId}-{p.payAccountId}", p => p.amount): payments.ToDictionary(p => p.payAccountId, p => p.amount);
                    var transfers = gateway == "razorpay" ? RazorpayService.TransferToAccounts(accountMap, "INR") : StripeService.TransferToAccounts(accountMap, "gbp");
                    var successfulTransfers = transfers.Where(t => t.IsSuccess && !string.IsNullOrWhiteSpace(t.TransferId)).ToList(); 
                    var failedTransfers = transfers.Where(t => !t.IsSuccess).ToList();
                    // Step 3: Update status to "Success" if payment completed
                    string TranferSuccess = "3";
                    string TranferFailed = "6";

                    // Step 3: Check for successful transfer
                    if (successfulTransfers.Count > 0)
                    {
                        try
                        {
                            var updateList = selectedList.Join(successfulTransfers, s => s.AccountId, t => t.AccountId, (s, t) => new { Id = s.Id, TransferId = t.TransferId }).ToList();
                            foreach (var item in updateList)
                            {
                                string accountId = item.Id;
                                string transferId = item.TransferId;
                                List<KeyValuePair<string, object>> sqlRef = new List<KeyValuePair<string, object>>();
                                sqlRef.Add(new KeyValuePair<string, object>("accountId", accountId));
                                sqlRef.Add(new KeyValuePair<string, object>("refernceno", transferId));
                                sqlRef.Add(new KeyValuePair<string, object>("sucessstatusId", TranferSuccess));
                                string sql = $"UPDATE `finance_transaction` SET status_id = @sucessstatusId, UTRno = @refernceno WHERE id=@accountId";
                                DataServiceMySql.ExecuteSql(sql, Service.UserService.GetAPIConnectionString(), sqlRef);

                            }
                            getsettlementposing(allSelectedIds);
                        }
                        catch
                        {

                        }
                        
                    }
                    // Step 4: Handle failed transfers
                    if (failedTransfers?.Any() == true)
                    {
                        var failedWithoutTransferIdIds = selectedList .Where(s => failedTransfers.Any(t => t.AccountId == s.AccountId && string.IsNullOrWhiteSpace(t.TransferId))).Select(s => s.Id).ToList();
                        if (UpdateTransactionStatus(failedWithoutTransferIdIds, TranferFailed))
                        {
                            getsettlementposing(failedWithoutTransferIdIds, true); // Pass rejection flag
                        }
                    }
                }
                catch
                {
                    Common.ShowCustomAlert(this.Page, "Technical Error", "A technical error occurred. Please try again later or contact support if the problem persists", false, "/Finance/SettlementReports");
                }
            }


        }



        private bool UpdateTransactionStatus(List<string> ids, string statusId, string referenceNo = null)
        {
            if (ids == null || ids.Count == 0) return false;

            try
            {
                List<KeyValuePair<string, object>> parameters = new List<KeyValuePair<string, object>>
                {
                    new KeyValuePair<string, object>("statusId", statusId)
                };
                if (!string.IsNullOrWhiteSpace(referenceNo))
                    parameters.Add(new KeyValuePair<string, object>("refernceno", referenceNo));
                var paramNames = ids.Select((id, index) =>
                {
                    string paramName = "@id" + index;
                    parameters.Add(new KeyValuePair<string, object>(paramName, id));
                    return paramName;
                }).ToList();

                string idList = string.Join(",", paramNames);
                string sql = $"UPDATE `finance_transaction` SET status_id = @statusId WHERE id IN ({idList})" ;

                DataServiceMySql.ExecuteSql(sql, Service.UserService.GetAPIConnectionString(), parameters);
                return true;
            }
            catch
            {
                return false;
            }
        }


    }

}