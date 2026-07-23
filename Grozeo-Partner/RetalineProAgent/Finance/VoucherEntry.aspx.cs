using Finascop.BussinessModel;
using RetalineProAgent.Core.BussinessModel;
using RetalineProAgent.Service;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using RetalineProAgent.Core.Services;
using System.Configuration;
using RetalineProAgent.Core.BussinessModel.Finance;
using System.Xml.Linq;
using System.IO;
using RestSharp;
using ResultType = Finascop.BussinessModel.ResultType;
using RetalineProAgent.Core.Services.ActiveLog;
using QRCoder;
using System.Drawing;

namespace RetalineProAgent.Finance
{
    public partial class VoucherEntry : Base.BasePartnerPage
    {
        [Serializable]
        public class VoucherEntryData
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

            public List<Costcentre> costcentres { get; set; }
        }
        [Serializable]
        public class upload
        {
            /// <summary>
            /// DocumentID
            /// </summary>
            public string DocumentID { get; set; }
            /// <summary>
            /// DocumentName
            /// </summary>
            public string DocumentName { get; set; }
            /// <summary>
            /// DocumentNarration
            /// </summary>
            public string DocumentNarration { get; set; }
            /// <summary>
            /// DocumentURL
            /// </summary>
            public string DocumentURL { get; set; }
            /// <summary>
            /// FileName
            /// </summary>
            public string FileName { get; set; }
        }
        public List<VoucherEntryData> lstVoucherEntry
        {
            get
            {
                if (ViewState["LEDGERENTRYLIST"] != null)
                    return (List<VoucherEntryData>)ViewState["LEDGERENTRYLIST"];
                return new List<VoucherEntryData>();
            }
            set
            {
                ViewState["LEDGERENTRYLIST"] = value;
            }
        }
        public List<upload> lstupload
        {
            get
            {
                if (ViewState["UPLOADLIST"] != null)
                    return (List<upload>)ViewState["UPLOADLIST"];
                return new List<upload>();
            }
            set
            {
                ViewState["UPLOADLIST"] = value;
            }
        }
        public List<Costcentre> lstcostcentre
        {
            get
            {
                if (ViewState["COSTCENTRE"] != null)
                    return (List<Costcentre>)ViewState["COSTCENTRE"];
                return new List<Costcentre>();
            }
            set
            {
                ViewState["COSTCENTRE"] = value;
            }
        }
        public string entryRef
        {
            get
            {
                return (string)ViewState["ENTRYREFID"];
            }
            set
            {
                ViewState["ENTRYREFID"] = value;
            }
        }

        public List<VoucherEntryData> Entries()
        {
            return lstVoucherEntry;
        }

        public string CreateUploadUrl()
        {
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            string guid = Guid.NewGuid().ToString();
            strUrl = String.Format("{0}/UploadFile?key={1}", strUrl.TrimEnd(new char[] { '/' }), guid);

            return strUrl;
        }
        public string GetCurrentUrl()
        {
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            string guid = Guid.NewGuid().ToString();

            return strUrl.TrimEnd(new char[] { '/' });
        }

        protected void Page_PreRender(object sender, EventArgs e)
        {
            //ODSEntries.Select();
            //gvEntry.DataBind();

            if (!IsPostBack)
            {
                folder.Value = Guid.NewGuid().ToString();
                string script = @"
                    $(document).ready(function () {
                        $('#blobFileName0').val(idGen.getId()); // Update the hidden field
                        blobFileURL = generateFileUrl($('#UploadFile0').find('.qrqcode_btnicon'));
                        $('#blobFileURL0').val(blobFileURL);
                        $('#blobFileName1').val(idGen.getId()); // Update the hidden field
                        blobFileURL = generateFileUrl($('#UploadFile1').find('.qrqcode_btnicon'));
                        $('#blobFileURL1').val(blobFileURL);
                        $('#blobFileName2').val(idGen.getId()); // Update the hidden field
                        blobFileURL = generateFileUrl($('#UploadFile2').find('.qrqcode_btnicon'));
                        $('#blobFileURL2').val(blobFileURL);
                            __doPostBack('blobFileName0', 'TriggerPostBack');
                    });
                ";

                ScriptManager.RegisterStartupScript(this, GetType(), "GenerateIdScript", script, true);
            }


            if (IsPostBack)
            {
                string cururl = GetCurrentUrl();
                
                string filename = blobFileName0.Value;
                string fileuploadurl = $"{cururl}/Finance/UploadFile?key={folder.Value}&file={filename}";
                string qrCodeBase64 = GenerateQRCodeBase64(fileuploadurl, 325);
                imgUploadQrcode0.Src = "data:image/png;base64," + qrCodeBase64;

                filename = blobFileName1.Value;
                fileuploadurl = $"{cururl}/Finance/UploadFile?key={folder.Value}&file={filename}";
                qrCodeBase64 = GenerateQRCodeBase64(fileuploadurl, 325);
                imgUploadQrcode1.Src = "data:image/png;base64," + qrCodeBase64;

                filename = blobFileName2.Value;
                fileuploadurl = $"{cururl}/Finance/UploadFile?key={folder.Value}&file={filename}";
                qrCodeBase64 = GenerateQRCodeBase64(fileuploadurl, 325);
                imgUploadQrcode2.Src = "data:image/png;base64," + qrCodeBase64;

                lstupload = new List<upload>();
                modalDialogShow.Value = "false";

                if (DocumentURL0.Value != "")
                {
                    updateVentries(DocumentID0.Value, DocumentName0.Value, Narration0.Value, blobFileURL0.Value);
                }
                else if (fupPdfFileUpload1.HasFile)
                {
                    string resultProof = Common.CreateBlob(fupPdfFileUpload1.PostedFile.InputStream, $"{blobFileName0.Value}", $"finascopupload/{folder.Value}").Result;
                    DocumentURL0.Value = resultProof;
                    updateVentries(DocumentID0.Value, DocumentName0.Value, Narration0.Value, blobFileURL0.Value);

                }
                else if (fupImageUpload1.HasFile)
                {
                    string resultProof = Common.CreateBlob(fupImageUpload1.PostedFile.InputStream, $"{blobFileName0.Value}", $"finascopupload/{folder.Value}").Result;
                    DocumentURL0.Value = resultProof;
                    updateVentries(DocumentID0.Value, DocumentName0.Value, Narration0.Value, blobFileURL0.Value);
                }

                if (DocumentURL1.Value != "")
                {
                    updateVentries(DocumentID1.Value, DocumentName1.Value, Narration1.Value, blobFileURL1.Value);
                }
                else if (fupPdfFileUpload2.HasFile)
                {
                    string resultProof = Common.CreateBlob(fupPdfFileUpload2.PostedFile.InputStream, $"{blobFileName1.Value}", $"finascopupload/{folder.Value}").Result;
                    DocumentURL1.Value = resultProof;
                    updateVentries(DocumentID1.Value, DocumentName1.Value, Narration1.Value, blobFileURL1.Value);

                }
                else if (fupImageUpload2.HasFile)
                {
                    string resultProof = Common.CreateBlob(fupImageUpload2.PostedFile.InputStream, $"{blobFileName1.Value}", $"finascopupload/{folder.Value}").Result;
                    DocumentURL1.Value = resultProof;
                    updateVentries(DocumentID1.Value, DocumentName1.Value, Narration1.Value, blobFileURL1.Value);
                }

                if (DocumentURL2.Value != "")
                {
                    updateVentries(DocumentID2.Value, DocumentName2.Value, Narration2.Value, blobFileURL2.Value);
                }
                else if (fupPdfFileUpload3.HasFile)
                {
                    string resultProof = Common.CreateBlob(fupPdfFileUpload3.PostedFile.InputStream, $"{blobFileName2.Value}", $"finascopupload/{folder.Value}").Result;
                    DocumentURL2.Value = resultProof;
                    updateVentries(DocumentID2.Value, DocumentName2.Value, Narration2.Value, blobFileURL2.Value);

                }
                else if (fupImageUpload3.HasFile)
                {
                    string resultProof = Common.CreateBlob(fupImageUpload3.PostedFile.InputStream, $"{blobFileName2.Value}", $"finascopupload/{folder.Value}").Result;
                    DocumentURL2.Value = resultProof;
                    updateVentries(DocumentID2.Value, DocumentName2.Value, Narration2.Value, blobFileURL2.Value);
                }
                //createUploadDetailsXML();
                Repterupload.DataSource = lstupload;
                Repterupload.DataBind();
            }
            try
            {
                lvVoucherEntry.DataSource = lstVoucherEntry;
                lvVoucherEntry.DataBind();
                double credittotal = 0, debittotal = 0;
                try { credittotal = lstVoucherEntry.Sum(v => v.credit); } catch { }
                try { debittotal = lstVoucherEntry.Sum(v => v.debit); } catch { }
                ltrDrTotal.Text = String.Format("{0:0,0.00}", debittotal);
                ltrCrTotal.Text = String.Format("{0:0,0.00}", credittotal);


            }
            catch
            {

            }
        }

        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                if (!String.IsNullOrEmpty(Request.QueryString["Id"]))
                {
                    btnSave.Enabled = true;
                    ShowDiv.Visible = false;

                    int Id = Convert.ToInt32(Request.QueryString["Id"]);
                    List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
                    sqldaId.Add(new KeyValuePair<string, object>("id", Id));
                    string requestBody = "select comments from finascop_log where id = @Id";
                    //DataServiceMySql.GetDataTable()
                    var refid = DataService.GetDataTable(requestBody, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqldaId);
                    if (refid == null || refid.Rows.Count <= 0)
                    {
                        Common.ShowCustomAlert(this.Page, "Errror", "", false);

                        return;
                    }
                    if (refid != null && refid.Rows.Count > 0)
                    {

                        var logid = refid.Rows[0];
                        string transactiondata = logid["comments"].ToString();
                        TransactionEntry data = JsonConvert.DeserializeObject<TransactionEntry>(transactiondata);
                        //TransactionEntry data = JsonConvert.DeserializeObject<TransactionEntry>(transactiondata, new JsonSerializerSettings { PreserveReferencesHandling = PreserveReferencesHandling.Objects });
                        List<TransactionData> tdata = data.Account;
                        tdata.AddRange(data.Particulars);
                        //data.entry_RefId

                        List<VoucherEntryData> newdata = tdata.Select(d => new VoucherEntryData
                        {
                            ledgerId = Convert.ToInt32(d.ledgerId),
                            particulars = d.particulars,
                            credit = (d.isDebtor == 1 ? 0 : d.amount),
                            debit = (d.isDebtor == 1 ? d.amount : 0),
                            IsDebit = (d.isDebtor == 1 ? true : false),
                            reference = d.ledgerRefId,
                            Store_group_name = data.StoreGroupName,
                            storeGroupRefId = data.storeGroupRefId,
                            entityid = data.order_order_id,
                            entry_type = data.entry_type,
                            br_Name_store_group = data.br_Name_store_group,
                            br_ID_store_group = data.br_ID_store_group,
                            order_event = data.order_event,
                            costcentres = lstcostcentre
                        }).ToList();
                        lstVoucherEntry = newdata;
                        txtNarration.Text = data.narration;
                        txtreference.Text = data.reference;
                        entryRef = data.entry_RefId;
                        string voucherdate = data.voucherDate.ToString("yyyy-MM-dd");//.ToString("dd-MM-yyyy");
                        txtVoucherDate.Text = voucherdate; //voucherdate.ToString();
                        ddlEntryType.DataBind();
                        ListItem item = ddlEntryType.Items.FindByValue(((int)data.docTypeID).ToString());
                        if (item != null)
                        {
                            item.Selected = true;
                        }
                        //if (ddlEntryType.Items.FindByText(data.docTypeID.ToString()) != null) ddlEntryType.Items.FindByText(data.docTypeID.ToString()).Selected = true;                        //string reId = entryRef;
                        //reId=data.entry_RefId;
                    }
                }
                hfdBlobURL.Value = ConfigurationManager.AppSettings.Get("blobURL") + ConfigurationManager.AppSettings.Get("blobContainer");
                DocumentName0.Value = "Proof Document 1";
                DocumentURL0.Value = Narration0.Value = blobFileURL0.Value = blobFileName0.Value = "";
                DocumentName1.Value = "Proof Document 2";
                DocumentURL1.Value = Narration1.Value = blobFileURL1.Value = blobFileName1.Value = "";
                DocumentName2.Value = "Proof Document 3";
                DocumentURL2.Value = Narration2.Value = blobFileURL2.Value = blobFileName2.Value = "";
                modalDialogShow.Value = "false";
            }
            if (modalDialogShow.Value == "true")
            {
                ScriptManager.RegisterStartupScript(this, GetType(), "showModal", "$('#DocumentUploadpopup').modal('show');", true);
            }
            ddlEntryType.Enabled = string.IsNullOrWhiteSpace(ddlEntryType.Text);
            txtVoucherDate.Enabled = string.IsNullOrWhiteSpace(txtVoucherDate.Text);
            txtreference.Enabled = string.IsNullOrWhiteSpace(txtreference.Text);
        }

        private string GenerateQRCodeBase64(string url, int imgSize)
        {
            QRCodeGenerator qrGenerator = new QRCodeGenerator();
            QRCodeData qrCodeData = qrGenerator.CreateQrCode(url, QRCodeGenerator.ECCLevel.M);
            QRCode qrCode = new QRCode(qrCodeData);
            using (Bitmap qrCodeImage = qrCode.GetGraphic(15))
            {
                using (MemoryStream ms = new MemoryStream())
                {
                    qrCodeImage.Save(ms, System.Drawing.Imaging.ImageFormat.Png);
                    byte[] byteImage = ms.ToArray();
                    return Convert.ToBase64String(byteImage);
                }
            }
        }

        protected void lbAddEntry_Click(object sender, EventArgs e)
        {
            btnSave.Enabled = true;
            lblError.Text = lblentry.Text = lblledgershow.Text = "";
            double debit = 0.00, credit = 0.00;
            bool isDebit = Ddlentertype.Text == "1";
            string reference = txtreference.Text;           
            if (!string.IsNullOrWhiteSpace(txtAmount.Text))
            {
                double amount = Double.Parse(txtAmount.Text);
                if (isDebit)
                    debit = amount;
                else
                    credit = amount;
            }
            else
            {
                lblError.Text = "Error: Amount is required.";
                return;
            }

            if (debit < 0 || credit < 0 || (debit + credit) <= 0)
            {
                lblError.Text = "Error: Credit / Debit amount <= 0 not allowed";
                return;
            }
            int entry_type=0;
            if (entry_type == 0)
            {
                entry_type = 2;
            }
             // default value
            if (string.IsNullOrWhiteSpace(ddlEntryType.SelectedItem?.Value))
            {
                lblentry.Text = "please select voucher type";
                return;
            }

            if (string.IsNullOrWhiteSpace(selLedger.SelectedItem?.Value))
            {
                lblledgershow.Text = "please select ledger";
                return;
            }

            // Extract values after validation
            string ledgerId = selLedger.SelectedItem.Value;
            string particulars = selLedger.SelectedItem.Text;
            string order_event = "";
            string storeGroupRefId = "";
            string entityid = "";
            string Store_group_name = "";
            string br_Name_store_group = "";
            int br_ID_store_group = 0;
            var ventries = lstVoucherEntry;
            ventries.Add(new VoucherEntryData
            {

                credit = credit,
                debit = debit,
                ledgerId = int.Parse(ledgerId),
                IsDebit= isDebit,
                particulars = particulars,
                reference = reference,
                Store_group_name = Store_group_name,
                entityid = entityid,
                entry_type = entry_type,
                storeGroupRefId = storeGroupRefId,
                br_Name_store_group = br_Name_store_group,
                br_ID_store_group = br_ID_store_group,
                order_event = order_event,
                costcentres = lstcostcentre.Select(c => c).ToList()

            });
            lstVoucherEntry = ventries;
            ShowDiv.Visible = false;
            //lvVoucherEntry.DataSource = lstVoucherEntry;
            //lvVoucherEntry.DataBind();
            //lvVoucherEntry.Items.Clear();

            ///*gvEntry.DataBind()*/;

            lstcostcentre.Clear();
            lvcostcentre.DataBind();

        }

        private void loadHiddenFieldsFromXML()
        {
            String uploadListXML = Common.ReadBlobAsString($"UploadDetails.xml", $"finascopupload/{folder.Value}");
            XDocument xmlDoc = XDocument.Parse(uploadListXML);

            List<upload> uploadList = xmlDoc.Descendants("UploadDetails")
                .Select(item => new upload
                {
                    DocumentID = item.Attribute("DOC_Index")?.Value,
                    DocumentName = item.Element("DocumentName")?.Value,
                    DocumentNarration = item.Element("DocumentNarration")?.Value,
                    DocumentURL = item.Element("DocumentURL")?.Value,
                    FileName = item.Element("FileName")?.Value
                }).ToList();

            var currentElem = uploadList.ElementAtOrDefault(0);
            if (currentElem != null)
            {
                DocumentName0.Value = currentElem.DocumentName;
                Narration0.Value = currentElem.DocumentNarration;
                DocumentURL0.Value = currentElem.DocumentURL;
                blobFileURL0.Value = currentElem.DocumentURL;
                blobFileName0.Value = currentElem.FileName;

            }

            currentElem = uploadList.ElementAtOrDefault(1);
            if (currentElem != null)
            {
                DocumentName1.Value = currentElem.DocumentName;
                Narration1.Value = currentElem.DocumentNarration;
                DocumentURL1.Value = currentElem.DocumentURL;
                blobFileURL1.Value = currentElem.DocumentURL;
                blobFileName1.Value = currentElem.FileName;
            }

            currentElem = uploadList.ElementAtOrDefault(2);
            if (currentElem != null)
            {
                DocumentName2.Value = currentElem.DocumentName;
                Narration2.Value = currentElem.DocumentNarration;
                DocumentURL2.Value = currentElem.DocumentURL;
                blobFileURL2.Value = currentElem.DocumentURL;
                blobFileName2.Value = currentElem.FileName;
            }
        }
        protected static string GenerateVoucherSerial(int docType)
        {
            string DocSerialNumber = "";
            List<KeyValuePair<string, object>> sidparams = new List<KeyValuePair<string, object>>();
            sidparams.Add(new KeyValuePair<string, object>("@typeId", docType));
            DataTable typeid = DataService.GetDataTable("SELECT dbo.[GenerateVoucherSerial] (@typeId)", ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sidparams, isSP: true);
            if (typeid != null)
            {
                var ttype = typeid.Rows[0];
                DocSerialNumber = ttype.ToString();

            }
            return DocSerialNumber;

        }
        protected Transaction.TransactionEntry createTEFromVED(List<VoucherEntryData> ved)
        {
            Transaction.TransactionEntry trEntry = new Transaction.TransactionEntry();
            List<Transaction.TransactionData> Account = new List<Transaction.TransactionData>();
            List<Transaction.TransactionData> Particulars = new List<Transaction.TransactionData>();

            trEntry.TransactionTypeId = (Transaction.TransactionType)Enum.Parse(typeof(Transaction.TransactionType), ddlEntryType.SelectedValue.ToString());
            trEntry.Narration = txtNarration.Text;
            trEntry.reference = txtreference.Text;
            trEntry.docTypeID = int.Parse(ddlEntryType.SelectedValue);
            trEntry.StoreGroupName = ved[0].Store_group_name;
            trEntry.storeGroupRefId = string.IsNullOrEmpty(ved[0].storeGroupRefId) ? "-1" : ved[0].storeGroupRefId;
            trEntry.storeGroupId = -1;
            trEntry.br_ID_store_group = ved[0].br_ID_store_group;
            trEntry.br_Name_store_group = ved[0].br_Name_store_group;
            trEntry.order_order_id = ved[0].entityid;
            trEntry.order_event = ved[0].order_event;
            trEntry.entry_type = ved[0].entry_type;
            trEntry.blob_storage_folder = folder.Value;
            trEntry.voucherDate = DateTime.Parse(txtVoucherDate.Text);
            trEntry.finascopBrID = 1; // The default company id (Head Office). This will be replaced with the corresponding company id later when more registrations done at multiple states.
            foreach (var item in ved)
            {
                if (item.debit > 0)
                {

                    Transaction.TransactionData trData = new Transaction.TransactionData()
                    {
                        ledgerId = item.ledgerId,
                        reference = trEntry.reference,
                        amount = item.debit,
                        particulars = item.particulars,
                        isDebtor = 1,
                        costCentreEntries = item.costcentres
                    };
                    Account.Add(trData);

                }

                if (item.credit > 0)
                {

                    Transaction.TransactionData trData = new Transaction.TransactionData()
                    {
                        ledgerId = item.ledgerId,
                        reference = trEntry.reference,
                        amount = item.credit,
                        particulars = item.particulars,
                        isDebtor = 0,
                        costCentreEntries = item.costcentres
                    };
                    Particulars.Add(trData);
                }

            }
            trEntry.Account = Account;
            trEntry.Particulars = Particulars;
            return trEntry;
        }
        protected void DeleteFile(object sender, EventArgs e)
        {
            lstupload = new List<upload>();
            LinkButton clickedButton = (LinkButton)sender;
            string buttonID = clickedButton.ID;
            bool success;
            switch (buttonID)
            {
                case "lbnBlobDelete0":
                    if (DocumentURL0.Value != "")
                    {
                        Common.DeleteBlob(DocumentURL0.Value);
                        DocumentURL0.Value = "";
                    }
                    break;
                case "lbnBlobDelete1":
                    if (DocumentURL1.Value != "")
                    {
                        Common.DeleteBlob(DocumentURL1.Value);
                        DocumentURL1.Value = "";
                    }
                    break;
                case "lbnBlobDelete2":
                    if (DocumentURL2.Value != "")
                    {
                        Common.DeleteBlob(DocumentURL2.Value);
                        DocumentURL2.Value = "";
                    }
                    break;
            }
            if (DocumentURL0.Value != "")
            {
                updateVentries(DocumentID0.Value, DocumentName0.Value, Narration0.Value, blobFileURL0.Value);
            }
            else if (fupPdfFileUpload1.HasFile)
            {
                string resultProof = Common.CreateBlob(fupPdfFileUpload1.PostedFile.InputStream, $"{blobFileName0.Value}", $"finascopupload/{folder.Value}").Result;
                DocumentURL0.Value = resultProof;
                updateVentries(DocumentID0.Value, DocumentName0.Value, Narration0.Value, blobFileURL0.Value);

            }
            else if (fupImageUpload1.HasFile)
            {
                string resultProof = Common.CreateBlob(fupImageUpload1.PostedFile.InputStream, $"{blobFileName0.Value}", $"finascopupload/{folder.Value}").Result;
                DocumentURL0.Value = resultProof;
                updateVentries(DocumentID0.Value, DocumentName0.Value, Narration0.Value, blobFileURL0.Value);
            }

            if (DocumentURL1.Value != "")
            {
                updateVentries(DocumentID1.Value, DocumentName1.Value, Narration1.Value, blobFileURL1.Value);
            }
            else if (fupPdfFileUpload2.HasFile)
            {
                string resultProof = Common.CreateBlob(fupPdfFileUpload2.PostedFile.InputStream, $"{blobFileName1.Value}", $"finascopupload/{folder.Value}").Result;
                DocumentURL1.Value = resultProof;
                updateVentries(DocumentID1.Value, DocumentName1.Value, Narration1.Value, blobFileURL1.Value);

            }
            else if (fupImageUpload2.HasFile)
            {
                string resultProof = Common.CreateBlob(fupImageUpload2.PostedFile.InputStream, $"{blobFileName1.Value}", $"finascopupload/{folder.Value}").Result;
                DocumentURL1.Value = resultProof;
                updateVentries(DocumentID1.Value, DocumentName1.Value, Narration1.Value, blobFileURL1.Value);
            }


            if (DocumentURL2.Value != "")
            {
                updateVentries(DocumentID2.Value, DocumentName2.Value, Narration2.Value, blobFileURL2.Value);
            }
            else if (fupPdfFileUpload3.HasFile)
            {
                string resultProof = Common.CreateBlob(fupPdfFileUpload3.PostedFile.InputStream, $"{blobFileName2.Value}", $"finascopupload/{folder.Value}").Result;
                DocumentURL2.Value = resultProof;
                updateVentries(DocumentID2.Value, DocumentName2.Value, Narration2.Value, blobFileURL2.Value);

            }
            else if (fupImageUpload3.HasFile)
            {
                string resultProof = Common.CreateBlob(fupImageUpload3.PostedFile.InputStream, $"{blobFileName2.Value}", $"finascopupload/{folder.Value}").Result;
                DocumentURL2.Value = resultProof;
                updateVentries(DocumentID2.Value, DocumentName2.Value, Narration2.Value, blobFileURL2.Value);
            }


            Repterupload.DataSource = lstupload;
            Repterupload.DataBind();
        }

        protected void savemod_Click(object sender, EventArgs e)
        {
            var ltdrtotal = "";
            var ltcrtotal = "";
            if (ltdrtotal == null || ltcrtotal == null || String.IsNullOrEmpty(ltrDrTotal.Text) || String.IsNullOrEmpty(ltrCrTotal.Text))
            {
                Common.ShowCustomAlert(this.Page, "Errror", "Invalid total", false);

                return;
            }
            double drTotal = 0, crTotal = 0;
            try { drTotal = Convert.ToDouble(ltrDrTotal.Text); } catch { drTotal = 0; }
            try { crTotal = Convert.ToDouble(ltrCrTotal.Text); } catch { crTotal = 0; }
            if (drTotal <= 0 || drTotal != crTotal)
            {
                Common.ShowCustomAlert(this.Page, "Failure", "Dr and CR should match and must be greater than 0", false);
                return;

            }

            Transaction.TransactionEntry te = createTEFromVED(lstVoucherEntry);           
            if (!String.IsNullOrEmpty(entryRef) && !String.IsNullOrEmpty(Request.QueryString["Id"]))
            {
                te.entry_RefId = entryRef;

            }
            else
            {
                te.entry_RefId = Guid.NewGuid().ToString();
            }


            
            string content = JsonConvert.SerializeObject(te);
            bool isAnyLedgerIdZero = lstVoucherEntry.Any(entry => entry.ledgerId == 0);
            if (isAnyLedgerIdZero)
            {
                Finascop_log(-2, "Suspense Account", 9, content, "", "Suspense Account", te.entry_RefId);
                return;
            }

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
            if (result.statusId == ResultType.Success)
            {
                createUploadDetailsXML();
                string Id = Request.QueryString["Id"];
                if (!String.IsNullOrEmpty(Id))
                {
                    List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
                    sqldaId.Add(new KeyValuePair<string, object>("Id", Id));
                    sqldaId.Add(new KeyValuePair<string, object>("comment", content));
                    string statusid = "UPDATE finascop_log SET status=3,[comments]=@comment WHERE id=@Id";
                    int results = DataService.ExecuteSql(statusid, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, sqldaId);
                    // Activitylog
                    String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                    String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                    string Source = strUrl;
                    int storegroupid = -1;
                    string User = "Finance Admin";
                    string id = Id;
                    string comment = content;
                    string page = "Voucher correction";
                    var items = new[]
                    {
                            new { Key = "Id", Value = Id },                          
                            new { Key = "comment", Value = comment },
                            new { Key = "page", Value = page },
                    };
                    string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                    var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
                    Common.ShowCustomAlert(this.Page, "Success", "Saved successfully!", true, "/Finance/PendingEntries");
                }
       
                Common.ShowCustomAlert(this.Page, "Success", "Saved successfully!", true, "/Finance/DataEntry");
                return;

            }
            else
            {

                string Id = Request.QueryString["Id"];
                if (!String.IsNullOrEmpty(Id))
                {
                    List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
                    sqldaId.Add(new KeyValuePair<string, object>("Id", Id));
                    sqldaId.Add(new KeyValuePair<string, object>("log_edit_results", "duplicate entry"));
                    string statusid = "UPDATE finascop_log SET [log_edit_results]=@log_edit_results,correctedOn=GETUTCDATE() ,status=3 WHERE id=@Id";
                    int resu = DataService.ExecuteSql(statusid, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, sqldaId);

                }

                Common.ShowCustomAlert(this.Page, "Failed", "Duplicate entry", false, "/Finance/PendingEntries");

                return;
            }


        }

        protected void ddlEntryType_SelectedIndexChanged(object sender, EventArgs e)
        {
            if (ddlEntryType.SelectedIndex == 0)
            {

            }
            else
            {
                lstVoucherEntry = new List<VoucherEntryData>();
                // txtVoucherDate.Text = DateTime.Today.ToString("yyyy-MM-dd");//ToString("dd-MM-yyyy");
                //txtVoucherDate.ReadOnly = true;

                lvVoucherEntry.DataSource = lstVoucherEntry;
                lvVoucherEntry.DataBind();

            }
        }

        protected void lvVoucherEntry_DataBound(object sender, EventArgs e)
        {

        }

        protected void btnsanve_Click(object sender, EventArgs e)
        {
            ltrdate.Text = txtVoucherDate.Text;
            ltrnarration.Text = txtNarration.Text += " Created by " + this.CurrentUser.FullName + "."; 
            ltrTitle.Text = ddlEntryType.SelectedItem.Text;
            gvpopup.DataSource = lstVoucherEntry;
            gvpopup.DataBind();
            rptupdate.DataSource = lstupload;
            rptupdate.DataBind();

            if (hfdHasSuspenseAccount.Value == "1" && hfdHasDocumentAttached.Value == "False" )
            {
                Common.ShowCustomAlert(this.Page, "Failure", "Please upload atleast one proof document!", false);
                return;
            }

            if (txtNarration.Text.Trim() == string.Empty)
            {
                Common.ShowCustomAlert(this.Page, "Failure", "please enter narration", false);
                return;
            }

            string strAlertSCript = "$('#priviewledgerpopup').modal('show');";

            //strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";

            Type cstype = this.GetType();
            String csname1 = "ShowConfirmPopup";
            ClientScriptManager cs = this.ClientScript;

            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
            cstext1.Append("script>");
            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());

        }

        protected void gvpopup_DataBound(object sender, EventArgs e)
        {
            gvpopup.FooterRow.Cells[0].Text = "Total";
            gvpopup.FooterRow.Cells[1].Text = ltrDrTotal.Text;
            gvpopup.FooterRow.Cells[2].Text = ltrCrTotal.Text;
        }

        private void updateVentries(string DocumentID, string DocumentName, string DocumentNarration, string blobUrl)
        {
            List<upload> ventries = lstupload;

            ventries.Add(new upload
            {
                DocumentID = DocumentID,
                DocumentName = DocumentName,
                DocumentNarration = DocumentNarration.Replace("<br />", Environment.NewLine),
                DocumentURL = blobUrl
            });
            lstupload = ventries;
        }

        protected void btnupload_Click(object sender, EventArgs e)
        {
            lstupload = new List<upload>();
            modalDialogShow.Value = "false";

            if (DocumentURL0.Value != "")
            {
                updateVentries(DocumentID0.Value, DocumentName0.Value, Narration0.Value, blobFileURL0.Value);
            }
            else if (fupPdfFileUpload1.HasFile)
            {
                string resultProof = Common.CreateBlob(fupPdfFileUpload1.PostedFile.InputStream, $"{blobFileName0.Value}", $"finascopupload/{folder.Value}").Result;
                DocumentURL0.Value = resultProof;
                updateVentries(DocumentID0.Value, DocumentName0.Value, Narration0.Value, blobFileURL0.Value);

            }
            else if (fupImageUpload1.HasFile)
            {
                string resultProof = Common.CreateBlob(fupImageUpload1.PostedFile.InputStream, $"{blobFileName0.Value}", $"finascopupload/{folder.Value}").Result;
                DocumentURL0.Value = resultProof;
                updateVentries(DocumentID0.Value, DocumentName0.Value, Narration0.Value, blobFileURL0.Value);
            }

            if (DocumentURL1.Value != "")
            {
                updateVentries(DocumentID1.Value, DocumentName1.Value, Narration1.Value, blobFileURL1.Value);
            }
            else if (fupPdfFileUpload2.HasFile)
            {
                string resultProof = Common.CreateBlob(fupPdfFileUpload2.PostedFile.InputStream, $"{blobFileName1.Value}", $"finascopupload/{folder.Value}").Result;
                DocumentURL1.Value = resultProof;
                updateVentries(DocumentID1.Value, DocumentName1.Value, Narration1.Value, blobFileURL1.Value);

            }
            else if (fupImageUpload2.HasFile)
            {
                string resultProof = Common.CreateBlob(fupImageUpload2.PostedFile.InputStream, $"{blobFileName1.Value}", $"finascopupload/{folder.Value}").Result;
                DocumentURL1.Value = resultProof;
                updateVentries(DocumentID1.Value, DocumentName1.Value, Narration1.Value, blobFileURL1.Value);
            }

            if (DocumentURL2.Value != "")
            {
                updateVentries(DocumentID2.Value, DocumentName2.Value, Narration2.Value, blobFileURL2.Value);
            }
            else if (fupPdfFileUpload3.HasFile)
            {
                string resultProof = Common.CreateBlob(fupPdfFileUpload3.PostedFile.InputStream, $"{blobFileName2.Value}", $"finascopupload/{folder.Value}").Result;
                DocumentURL2.Value = resultProof;
                updateVentries(DocumentID2.Value, DocumentName2.Value, Narration2.Value, blobFileURL2.Value);

            }
            else if (fupImageUpload3.HasFile)
            {
                string resultProof = Common.CreateBlob(fupImageUpload3.PostedFile.InputStream, $"{blobFileName2.Value}", $"finascopupload/{folder.Value}").Result;
                DocumentURL2.Value = resultProof;
                updateVentries(DocumentID2.Value, DocumentName2.Value, Narration2.Value, blobFileURL2.Value);
            }
            //createUploadDetailsXML();
            Repterupload.DataSource = lstupload;
            Repterupload.DataBind();

        }

        protected void createUploadDetailsXML()
        {
            String fileUrl = "";
            String uploadInfoXML = CreateXMLFromList();
            bool xmlFileCreated = false;
            int attempts = 0;
            while (!xmlFileCreated && attempts < 3)
            {
                attempts++;
                try
                {

                    fileUrl = Common.CreateBlob(uploadInfoXML, $"UploadDetails.xml", $"finascopupload/{folder.Value}").Result;
                    xmlFileCreated = true;
                }
                catch (Exception blobExists)
                {
                    var result = Common.DeleteBlob(blobExists.InnerException.Message);
                }
            }
        }

        protected String CreateXMLFromList()
        {
            XDocument xmlDocument = new XDocument(
                new XDeclaration("1.0", "utf-8", "yes"),
                new XComment("Finascop Upload Document Details"),
                new XElement("Upload",
                    from upload in lstupload
                    select new XElement("UploadDetails", new XAttribute("DOC_Index", upload.DocumentID),
                           new XElement("DocumentName", upload.DocumentName),
                           new XElement("DocumentNarration", new XText(upload.DocumentNarration)),
                           new XElement("DocumentURL", upload.DocumentURL),
                           new XElement("FileName", upload.FileName)
                 )));

            return xmlDocument.ToString();
        }

        protected void Repterupload_DataBinding(object sender, EventArgs e)
        {

        }

        protected void rptupdate_DataBinding(object sender, EventArgs e)
        {

        }

        protected void lvVoucherEntry_ItemEditing(object sender, ListViewEditEventArgs e)
        {
        }

        protected void lvVoucherEntry_ItemCanceling(object sender, ListViewCancelEventArgs e)
        {
            lvVoucherEntry.EditIndex = -1;
        }

        protected void lvVoucherEntry_ItemUpdating(object sender, ListViewUpdateEventArgs e)
        {

        }

        protected void lvVoucherEntry_ItemCommand(object sender, ListViewCommandEventArgs e)
        {
            if (e.CommandName == "Delete")
            {
                var ventry = lstVoucherEntry;
                var item = ventry[e.Item.DataItemIndex];
                ventry.RemoveAt(e.Item.DataItemIndex);
                lstVoucherEntry = ventry;
                lvVoucherEntry.DataSource = lstVoucherEntry;
                lvVoucherEntry.EditIndex = -1;
                lvVoucherEntry.DataBind();
            }


            if (e.CommandName == "Update")
            {
                var ventry = lstVoucherEntry;
                var item = ventry[e.Item.DataItemIndex];
                DropDownList dlType = (DropDownList)e.Item.FindControl("dlentrytpeupdae");
                DropDownList dlledger = (DropDownList)e.Item.FindControl("selLedger2");
                if (dlType.SelectedValue == "1")
                {
                    TextBox txtamount = (TextBox)e.Item.FindControl("txtamoundup");
                    item.credit = 0;

                    item.debit = Convert.ToDouble(txtamount.Text);
                }
                if (dlType.SelectedValue == "2")
                {
                    TextBox txtamount = (TextBox)e.Item.FindControl("txtamoundup");
                    item.debit = 0;
                    item.credit = Convert.ToDouble(txtamount.Text);
                }
                Repeater rpt = (Repeater)e.Item.FindControl("rptEditCostCenter");
                if (rpt != null)
                {
                    item.costcentres = lstcostcentre;
                }
                item.ledgerId = Convert.ToInt32(dlledger.SelectedItem.Value);
                item.particulars = dlledger.SelectedItem.Text;
                ventry[e.Item.DataItemIndex] = item;
                item.costcentres = lstcostcentre;
                lstVoucherEntry = ventry;

                lvVoucherEntry.EditIndex = -1;
                lvVoucherEntry.DataSource = lstVoucherEntry;
                lvVoucherEntry.DataBind();

            }

        }

        protected void lvVoucherEntry_ItemDeleting(object sender, ListViewDeleteEventArgs e)
        {

        }

        protected void Repterupload_ItemCommand(object source, RepeaterCommandEventArgs e)
        {
            if (e.CommandName == "Delete")
            {
                var ventry = lstupload;
                var item = ventry[e.Item.ItemIndex];
                switch (item.DocumentID)
                {
                    case "DOC0": DocumentURL0.Value = ""; break;
                    case "DOC1": DocumentURL1.Value = ""; break;
                    case "DOC2": DocumentURL2.Value = ""; break;
                }
                Common.DeleteBlob(item.DocumentURL);
                ventry.RemoveAt(e.Item.ItemIndex);
                lstupload = ventry;
                Repterupload.DataSource = lstupload;
                Repterupload.DataBind();
            }
        }

        protected void lbncostcentre_Click(object sender, EventArgs e)
        {
            string CostCentreName = "";
            double CostAmount = 0;
            int ledgerId = 0;
            int CostCentreId = 0;
            int IsDebit = -1;
            if (Ddlentertype.Text == "1")
            {
                IsDebit = 1;
            }
            else if (Ddlentertype.Text == "2")
            {
                IsDebit = 0;
            }
            else
            {
                ltrselectdebit.Text = "please select the entry type";
            }

            CostCentreId = Convert.ToInt32(ddlcostcentre.SelectedValue);
            ledgerId = Convert.ToInt32(selLedger.SelectedValue);
            CostCentreName = ddlcostcentre.SelectedItem.Text;
            CostAmount = Double.Parse(txtcostamount.Text);
            var ventries = lstcostcentre;
            ventries.Add(new Costcentre
            {

                CostCentreName = CostCentreName,
                CostAmount = CostAmount,
                ledgerId = ledgerId,
                CostCentreId = CostCentreId,
                IsDebit = IsDebit


            });
            lstcostcentre = ventries;
            lvcostcentre.DataSource = lstcostcentre;
            lvcostcentre.DataBind();
            txtcostamount.Text = "";

        }

        protected void selLedger_SelectedIndexChanged(object sender, EventArgs e)
        {
            int ledgerId = 0;
            ledgerId = Convert.ToInt32(selLedger.SelectedValue);

            List<KeyValuePair<string, object>> sqlId = new List<KeyValuePair<string, object>>();
            sqlId.Add(new KeyValuePair<string, object>("Ledid", ledgerId));
            string Costcentre = $"select hasCostCentre from ledger where id=@Ledid";
            DataTable cost = DataService.GetDataTable(Costcentre, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqlId);
            if (cost != null && cost.Rows.Count > 0)
            {
                var name = cost.Rows[0];
                if (name["hasCostCentre"].ToString() == "True")
                {
                    plccostcentre.Visible = true;

                }
                else
                {
                    plccostcentre.Visible = false;
                }
            }




        }

        protected void selLedger2_SelectedIndexChanged(object sender, EventArgs e)
        {

        }

        protected void lvVoucherEntry_ItemDataBound(object sender, ListViewItemEventArgs e)
        {
            if (lvVoucherEntry.EditIndex > -1)
            {
                //DropDownList dl = (DropDownList)lvVoucherEntry.EditItem.FindControl("selLedger2");
                Repeater rpt = (Repeater)e.Item.FindControl("rptEditCostCenter");
                if (rpt != null)
                {
                    VoucherEntryData dsource = (VoucherEntryData)e.Item.DataItem;
                    if (dsource != null && dsource.costcentres != null && dsource.costcentres.Count > 0)
                    {
                        rpt.DataSource = ((VoucherEntryData)e.Item.DataItem).costcentres;
                        rpt.DataBind();

                    }

                }
            }
        }

        protected void selLedger2_DataBound(object sender, EventArgs e)
        {

        }
        public Control FindControlRecursive(Control Root, string Id)
        {
            if (Root.ID == Id)
                return Root;
            foreach (Control Ctl in Root.Controls)
            {
                Control FoundCtl = FindControlRecursive(Ctl, Id);
                if (FoundCtl != null)
                    return FoundCtl;
            }
            return null;
        }

        public void  Finascop_log(int id, string type, int status, string comments, string order_order_id, string order_event, string entry_RefId)
        {
            try
            {
                string sqlInsertLog = $"INSERT INTO [finascop_log] (entity_id, type, status,comments,order_order_id,order_event,entry_RefId) " +
                  $"VALUES (@entity_id, @type,@status, @comments,@order_order_id,@order_event,@entry_RefId)";
                List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                prms.Add(new KeyValuePair<string, object>("entity_id", id));
                prms.Add(new KeyValuePair<string, object>("type", type));
                prms.Add(new KeyValuePair<string, object>("status", status));
                prms.Add(new KeyValuePair<string, object>("comments", comments));
                prms.Add(new KeyValuePair<string, object>("order_order_id", order_order_id));
                prms.Add(new KeyValuePair<string, object>("order_event", order_event));
                prms.Add(new KeyValuePair<string, object>("entry_RefId", entry_RefId));
                DataTable supendence = DataService.GetDataTable(sqlInsertLog, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: prms);
            }
            catch
            {
                Common.ShowCustomAlert(this.Page, "Failed", "", false, "/Finance/DataEntry");

            }
            Common.ShowCustomAlert(this.Page, "Success", "Saved successfully!", true, "/Finance/PendingEntries");

        }

        protected void FileUploadValidator_ServerValidate(object source, ServerValidateEventArgs args)
        {
            bool isAnyLedgerIdZero = lstVoucherEntry.Any(entry => entry.ledgerId == 0);
            hfdHasDocumentAttached.Value = "false";
            if (isAnyLedgerIdZero)
            {
                args.IsValid = DocumentURL0.Value != "" || DocumentURL1.Value != "" || DocumentURL2.Value != "";

                hfdHasSuspenseAccount.Value = "1";
                hfdHasDocumentAttached.Value = args.IsValid.ToString();
            }
            else
            {
                args.IsValid = true;
                hfdHasSuspenseAccount.Value = "0";
            }
            
        }

    }
}