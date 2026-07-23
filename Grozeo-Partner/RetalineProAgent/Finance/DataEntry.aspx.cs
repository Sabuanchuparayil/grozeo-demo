using Antlr.Runtime;
//using RetalineProAgent.Core.BussinessModel.GST;
using RetalineProAgent.Core.BussinessModel.Inventory;
using RetalineProAgent.Service;
using NPOI.POIFS.Properties;
using RetalineProAgent.Core.Services;
using System;
using System.Collections.Generic;
using System.Data;
using System.Data.SqlClient;
using System.Security.Cryptography;
using System.Web.DynamicData;
using System.Web.UI;
using System.Web.UI.WebControls;
using NPOI.OpenXml4Net.OPC;
using System.Configuration;
using RetalineProAgent.Core.BussinessModel.Finance;
using static RetalineProAgent.Finance.VoucherEntry;
using System.Xml.Linq;
using System.Linq;
using MySqlX.XDevAPI.Relational;
using Org.BouncyCastle.Crypto;
using System.Web;
using QRCoder;
using System.Drawing;
using System.IO;
using static RetalineProAgent.DataEntry;
using Microsoft.Ajax.Utilities;
using System.Net.Http;
using System.Threading.Tasks;

namespace RetalineProAgent
{
    public partial class DataEntry : Base.BasePartnerPage
    {
        [Serializable]

        private class upload
        {
            public string DocumentID { get; set; }
            public string DocumentName { get; set; }
            public string DocumentNarration { get; set; }
            public string DocumentURL { get; set; }
            public string FileName { get; set; }
        }

        private class Generator
        {
            private int _rand;

            public Generator()
            {
                // Initialize with a combination of random number and current timestamp
                _rand = new Random().Next(1, 26) + DateTime.Now.Second;
            }

            public long GetId()
            {
                return _rand++;
            }
        }


        private List<upload> lstupload
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

        public string Folder
        {
            get { return (string)ViewState["FOLDER"]; }
            set { ViewState["FOLDER"] = value; }
        }

        public string FileName
        {
            get { return (string)ViewState["FILENAME"]; }
            set { ViewState["FILENAME"] = value; }
        }


        protected void Page_Load(object sender, EventArgs e)
        {
            txtFromDate.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
            txtToDate.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
            //txtFromDate.Text = DateTime.Now.ToString("yyyy-MM-dd");
            //txtToDate.Text = DateTime.Now.ToString("yyyy-MM-dd");
            if (!IsPostBack)
            {
                txtFromDate.Text = DateTime.Now.AddDays(-30).ToString("yyyy-MM-dd");
                txtToDate.Text = DateTime.Now.ToString("yyyy-MM-dd");
                lvdatatable.DataBind();

                DU_hfdBlobURL.Value = ConfigurationManager.AppSettings.Get("blobURL") + ConfigurationManager.AppSettings.Get("blobContainer");
                DU_DocumentName.Value = "Proof Document 1";
                DU_DocumentURL.Value = DU_Narration.Value = DU_blobFileURL.Value = DU_blobFileName.Value = "";


                lstupload = new List<upload>();
                rptrAttachments.DataSource = lstupload;
                rptrAttachments.DataBind();


                ScriptManager.RegisterStartupScript(this, this.GetType(), "ForcePostBack", "__doPostBack('', '');", true);

                DU_blobFileName.Value = string.Empty;
                DU_imgUploadQrcode.Src = string.Empty;

            }
            else
            {
                string cururl = GetCurrentUrl();

                string script = @"$(document).ready(function () {
                        $('#DU_blobFileName').val(idGen.getId()); // Update the hidden field
                        blobFileURL = generateFileUrl($('#DU_UploadFile').find('.qrqcode_btnicon'));
                        $('#DU_blobFileURL').val(blobFileURL);
                        console.log('blobFileURL 2 :' + blobFileURL);

                    });";

                ScriptManager.RegisterStartupScript(this, GetType(), "GenerateIdScript", script, true);

                FileName = DU_blobFileName.Value;

                string filename = FileName;

                string fileuploadurl = $"{cururl}/Finance/UploadFile?key={Folder}&file={filename}";
                string qrCodeBase64 = GenerateQRCodeBase64(fileuploadurl, 325);
                DU_imgUploadQrcode.Src = "data:image/png;base64," + qrCodeBase64;

                string eventTarget = Request.Params["__EVENTTARGET"];
                if (!string.IsNullOrEmpty(eventTarget))
                {
                    if (eventTarget.Contains("DataPager1"))
                    {
                        lstupload = new List<upload>();
                        rptrAttachments.DataSource = lstupload;
                        rptrAttachments.DataBind();
                    }
                }

                DU_DocumentURL.Value = "";

                if (DU_fupPdfFileUpload.HasFile)
                {
                    string resultProof = Common.CreateBlob(DU_fupPdfFileUpload.PostedFile.InputStream, $"{DU_blobFileName.Value}", $"finascopupload/{DU_folder.Value}").Result;
                    DU_DocumentURL.Value = resultProof;
                    updateVentries(DU_DocumentID.Value, DU_DocumentName.Value, DU_Narration.Value, DU_blobFileURL.Value, filename);

                }
                else if (DU_fupImageUpload.HasFile)
                {
                    string resultProof = Common.CreateBlob(DU_fupImageUpload.PostedFile.InputStream, $"{DU_blobFileName.Value}", $"finascopupload/{DU_folder.Value}").Result;
                    DU_DocumentURL.Value = resultProof;
                    updateVentries(DU_DocumentID.Value, DU_DocumentName.Value, DU_Narration.Value, DU_blobFileURL.Value, filename);
                }
                else
                {
                    bool doesFileExist = Common.DoesBlobExist($"{DU_blobFileName.Value}", $"finascopupload/{DU_folder.Value}").Result;
                    if (doesFileExist)
                    {
                        DU_DocumentURL.Value = DU_blobFileURL.Value;
                        updateVentries(DU_DocumentID.Value, DU_DocumentName.Value, DU_Narration.Value, DU_blobFileURL.Value, filename);
                    }
                }

                
            }

            rptrAttachments.DataSource = lstupload;
            rptrAttachments.DataBind();

            
        }

        private void LoadVoucherInfo()
        {
            if (!String.IsNullOrEmpty(hidVoucherId.Value))
            {
                int dataEntryId = Convert.ToInt32(hidVoucherId.Value);
                if (dataEntryId < 1)
                    return;

                List<KeyValuePair<string, object>> sqldataId = new List<KeyValuePair<string, object>>();
                sqldataId.Add(new KeyValuePair<string, object>("dataEntryId", dataEntryId));



                string narration = $"SELECT de.id,de.createdOn,de.narration,de.docSerialNo,entry_type,de.voucherSlNoString, " +
                    $"(case when entry_type=1 then 'A' when entry_type=2 then 'M' END) as entrytype ," +
                    $"(case when entry_type=1 then 'Auto Posting' when entry_type=2 then 'Manual Posting ' END) as entrytypeNA," +
                    $"(SELECT name FROM voucher_type WHERE id=de.voucher_type_id)as name," +
                    $"CONVERT(datetime, SWITCHOFFSET(createdOn, DATEPART(TZOFFSET,createdOn AT TIME ZONE 'India Standard Time'))) as datetime," +
                    $"de.amount,de.narration,entry_RefId,blob_storage_folder FROM data_entry de WHERE de.id=@dataEntryId";
                var payment = DataService.GetDataTable(narration, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, sqldataId);
                var dataEntry = payment.Rows[0];
                if (payment != null && payment.Rows.Count > 0)
                {
                    lbNarration.Text = dataEntry["narration"].ToString();
                    lbVoucher.Text = dataEntry["name"].ToString();
                    string date = dataEntry["datetime"].ToString();
                    //string date = DateTime.Now.ToString("dd/MM/yyyy");
                    DateTime dt = Convert.ToDateTime(date);
                    lbDate.Text = dt.ToString("dd /MMM/ yyyy");
                    lbtime.Text = dt.ToString("dddd, HH:mm");
                    lbVocherId.Text = dataEntry["voucherSlNoString"].ToString();
                    lblentrytype.Text = dataEntry["entrytype"].ToString();
                    lblname.Text = dataEntry["entrytypeNA"].ToString();
                }

                string entry_RefId = dataEntry["entry_RefId"].ToString();
                lstupload = null;
                rptrAttachments.DataSource = lstupload;
                rptrAttachments.DataBind();
                DU_folder.Value = string.IsNullOrEmpty(dataEntry["blob_storage_folder"]?.ToString()) ? Guid.NewGuid().ToString() : dataEntry["blob_storage_folder"].ToString();

                Folder = DU_folder.Value;
                FileName = DU_blobFileName.Value;
                if (dataEntry["blob_storage_folder"].ToString().Length > 10)
                {

                    String uploadListXML = Common.ReadBlobAsString($"UploadDetails.xml", $"finascopupload/{dataEntry["blob_storage_folder"].ToString()}");
                    if (uploadListXML != null)
                    {
                        XDocument xmlDoc = XDocument.Parse(uploadListXML);

                        lstupload = xmlDoc.Descendants("UploadDetails")
                            .Select(item => new upload
                            {
                                DocumentName = item.Element("DocumentName")?.Value,
                                DocumentNarration = item.Element("DocumentNarration")?.Value,
                                DocumentURL = item.Element("DocumentURL")?.Value,
                                FileName = item.Element("FileName")?.Value,

                            }).ToList();
                    }
                    else
                    {
                        lstupload = null;
                    }

                    rptrAttachments.DataSource = lstupload;
                    rptrAttachments.DataBind();
                }

            }

        }


        protected void lvDataEntry_DataBound(object sender, EventArgs e)
        {
            lstupload = new List<upload>();
            rptrAttachments.DataSource = lstupload;
            rptrAttachments.DataBind();

            if (lvDataEntry.Items.Count <= 0)
            {
                return;
            }

            LinkButton lbtn = (LinkButton)lvDataEntry.Items[0].FindControl("lbSelectData");
            if (String.IsNullOrEmpty(hidVoucherId.Value))
            {

                if (lbtn != null && !String.IsNullOrEmpty(lbtn.Attributes["dataid"]))
                {
                    hidVoucherId.Value = lbtn.Attributes["dataid"];
                }
            }
            if (hidOrderOrderId.Value == null && String.IsNullOrEmpty(hidOrderOrderId.Value))
            {
                hidOrderOrderId.Value = lbtn.Attributes["order_order_id"];
            }

            sdsCommentLog.SelectParameters["order_order_id"].DefaultValue = hidOrderOrderId.Value;

            DataView dv = (DataView)sdsCommentLog.Select(DataSourceSelectArguments.Empty);

            // Check if there's data and do something with it
            if (dv != null && dv.Count > 0)
            {

                string result = dv[0]["ids"].ToString();

                if (!String.IsNullOrEmpty(result))
                {
                    //divTranLogStatus.Visible = true;
                    string[] ids = result.Split(new char[] { ',', ' ' }, StringSplitOptions.RemoveEmptyEntries);

                    // Create links for each ID
                    string links = "";
                    foreach (string id in ids)
                    {
                        // Create a link for each ID, pointing to a hypothetical page that displays transaction details
                        string link = $"<a target='_blank' rel='noopener noreferrer' href='PendingEntries.aspx?id={id}' style='color:red;'>{id}</a>";
                        links += link + ", "; // Append the link to the result, with some spacing
                    }

                    // Set the text of lblStatus with the generated links
                    lblStatus.Text = $"<b>Posting attempt(s) Transaction IDs {links} available for verification in Transaction Log.</b>";
                    divTranLogStatus.Visible = true;
                }
                else
                {
                    divTranLogStatus.Visible = false;
                }
            }
            else
            {
                divTranLogStatus.Visible = false;
            }

            if (String.IsNullOrEmpty(hidVoucherId.Value))
                return;

            int dEntryId = Convert.ToInt32(hidVoucherId.Value); //Request.QueryString["id"];
            List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
            sqldaId.Add(new KeyValuePair<string, object>("dataEntryId", dEntryId));
            string totalamount = $"SELECT  SUM(CASE WHEN [isDebtor] = 1 THEN tr.amount END) AS  dr_sum, SUM( CASE WHEN [isDebtor] = 0 THEN tr.amount END) AS  cr_sum FROM transactions tr inner join ledger le on  le.id=tr.ledger_id WHERE data_entry_id =@dataEntryId ";
            var amount = DataService.GetDataTable(totalamount, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqldaId);
            Literal ltrdrtotal = (Literal)lvDataEntry.FindControl("ltrDrTotal");
            Literal ltrcrtotal = (Literal)lvDataEntry.FindControl("ltrCRTotal");
            if (ltrdrtotal != null && ltrcrtotal != null)
            {
                var total = amount.Rows[0];
                ltrdrtotal.Text = String.Format("{0:0.00}", total["dr_sum"]).ToString();
                ltrcrtotal.Text = String.Format("{0:0.00}", total["cr_sum"]).ToString();

            }
            LoadVoucherInfo();


        }







        protected void lvdatatable_DataBound(object sender, EventArgs e)
        {
            if (lvdatatable.Items.Count > 0 && (String.IsNullOrEmpty(hidVoucherId.Value) || hidVoucherId.Value == "0"))
            {
                LinkButton lbtn = (LinkButton)lvdatatable.Items[0].FindControl("lbSelectData");
                if (lbtn != null && !String.IsNullOrEmpty(lbtn.Attributes["dataid"]))
                {
                    hidVoucherId.Value = lbtn.Attributes["dataid"];
                    lvdatatable.SelectedIndex = 0;

                }
                if (lbtn != null && !string.IsNullOrEmpty(lbtn.Attributes["order_order_id"]))
                {
                    hidOrderOrderId.Value = lbtn.Attributes["order_order_id"];
                    lvdatatable.SelectedIndex = 0;
                }
            }
        }



        protected void lvdatatable_SelectedIndexChanged(object sender, EventArgs e)
        {

        }

        protected void gvDataEntry_DataBound(object sender, EventArgs e)
        {

        }

        protected void lbSelectData_Click(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;
            //LinkButton lbtn = (LinkButton)lvDataEntry.Items[0].FindControl("lbSelectData");
            if (lbtn != null && !string.IsNullOrEmpty(lbtn.Attributes["dataid"]))
            {
                hidVoucherId.Value = lbtn.Attributes["dataid"];

            }
            if (lbtn != null && !string.IsNullOrEmpty(lbtn.Attributes["order_order_id"]))
            {
                hidOrderOrderId.Value = lbtn.Attributes["order_order_id"];

            }

            if (sdsCommentLog != null)
            {
                DataView dataView = (DataView)sdsCommentLog.Select(DataSourceSelectArguments.Empty);

                if (dataView != null && dataView.Count > 0)
                {
                    DataRowView row = dataView[0];
                    string result = row["ids"].ToString();

                    if (!String.IsNullOrEmpty(result))
                    {
                        //divTranLogStatus.Visible = true;
                        string[] ids = result.Split(new char[] { ',', ' ' }, StringSplitOptions.RemoveEmptyEntries);

                        // Create links for each ID
                        string links = "";
                        foreach (string id in ids)
                        {
                            // Create a link for each ID, pointing to a hypothetical page that displays transaction details
                            string link = $"<a target='_blank' rel='noopener noreferrer' href='PendingEntries.aspx?id={id}' style='color:red;'>{id}</a>";
                            links += link + ", "; // Append the link to the result, with some spacing
                        }

                        // Set the text of lblStatus with the generated links

                        lblStatus.Text = $"<b>Posting attempt(s) Transaction IDs {links} available for verification in Transaction Log.</b>";
                        divTranLogStatus.Visible = true;
                    }
                    else
                    {
                        divTranLogStatus.Visible = false;
                    }
                }
            }



            lvdatatable.DataBind();
        }

        protected void chkshow_CheckedChanged(object sender, EventArgs e)
        {


        }
        protected void lvcostcentre_DataBound(object sender, EventArgs e)
        {

            if (String.IsNullOrEmpty(hidVoucherId.Value))
                return;

            int dataentryid = Convert.ToInt32(hidVoucherId.Value);
            List<KeyValuePair<string, object>> sqlcost = new List<KeyValuePair<string, object>>();
            sqlcost.Add(new KeyValuePair<string, object>("dataEntryId", dataentryid));
            string costtotal = "SELECT SUM(CASE WHEN cc.[isDebtor] = 1 THEN cc.amount END) AS  dr_sum, SUM( CASE WHEN cc.[isDebtor] = 0 THEN cc.amount END) AS  cr_sum FROM cost_centre_entries cc inner join  transactions tr  on tr.id=cc.transactions_id  WHERE tr.data_entry_id=@dataEntryId";
            var costamount = DataService.GetDataTable(costtotal, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqlcost);
            Literal ltrdrtotal = (Literal)lvcostcentre.FindControl("ltrcostDrTotal");
            Literal ltrcrtotal = (Literal)lvcostcentre.FindControl("ltrcostCRTotal");
            if (ltrdrtotal != null && ltrcrtotal != null)
            {
                var total = costamount.Rows[0];
                ltrdrtotal.Text = String.Format("{0:0.00}", total["dr_sum"]).ToString();
                ltrcrtotal.Text = String.Format("{0:0.00}", total["cr_sum"]).ToString();
            }

            string costledger = "select tr.particulars,tr.amount,le.hascostcentre from cost_centre_entries cc inner join transactions tr  on tr.id=cc.transactions_id inner join [ledger]  le on le.id=tr.ledger_id WHERE tr.data_entry_id=@dataEntryId group by  tr.particulars,tr.amount,le.hascostcentre";
            var getcostledger = DataService.GetDataTable(costledger, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqlcost);

            if (getcostledger != null && getcostledger.Rows.Count > 0)
            {
                var getledgercentre = getcostledger.Rows[0];
                ltrledger.Text = getledgercentre["particulars"].ToString();
                ltrcostamount.Text = getledgercentre["amount"].ToString();
            }
            else
            {
                ltrledger.Text = "";
                ltrcostamount.Text = "";
            }
        }

        private void enableCommentLine()
        {
            if (lvDataEntry.Items.Count <= 0)
                return;
            LinkButton lbtn = (LinkButton)lvDataEntry.Items[0].FindControl("lbSelectData");
            if (lbtn != null && !string.IsNullOrEmpty(lbtn.Attributes["order_order_id"]))
            {
                hidOrderOrderId.Value = lbtn.Attributes["order_order_id"];
                if (sdsCommentLog != null)
                {
                    DataView dataView = (DataView)sdsCommentLog.Select(DataSourceSelectArguments.Empty);

                    if (dataView != null && dataView.Count > 0)
                    {
                        DataRowView row = dataView[0];
                        string result = row["ids"].ToString();

                        if (!String.IsNullOrEmpty(result))
                        {
                            //divTranLogStatus.Visible = true;
                            string[] ids = result.Split(new char[] { ',', ' ' }, StringSplitOptions.RemoveEmptyEntries);

                            // Create links for each ID
                            string links = "";
                            foreach (string id in ids)
                            {
                                // Create a link for each ID, pointing to a hypothetical page that displays transaction details
                                string link = $"<a target='_blank' rel='noopener noreferrer' href='PendingEntries.aspx?id={id}' style='color:red;'>{id}</a>";
                                links += link + ", "; // Append the link to the result, with some spacing
                            }

                            // Set the text of lblStatus with the generated links
                            lblStatus.Text = $"<b>Posting attempt(s) Transaction IDs {links} available for verification in Transaction Log.</b>";
                            divTranLogStatus.Visible = true;
                        }
                        else
                        {
                            divTranLogStatus.Visible = false;
                        }

                    }
                }

                lvdatatable.DataBind();
            }


        }
        protected void btnsearch_Click(object sender, EventArgs e)
        {
            enableCommentLine();
            DataPager1.SetPageProperties(0, DataPager1.PageSize, true);
        }

        protected void lvDataEntry_ItemCommand(object sender, ListViewCommandEventArgs e)
        {

        }

        protected void SDSDataEntry_Updated(object sender, SqlDataSourceStatusEventArgs e)
        {
            DataPager dataPager = lvdatatable.FindControl("DataPager1") as DataPager;
            if (dataPager != null)
            {
                dataPager.SetPageProperties(0, dataPager.PageSize, true);
            }

            // Rebind ListView
            lvdatatable.DataBind();
        }

        protected void SDSDataEntry_Updating(object sender, SqlDataSourceCommandEventArgs e)
        {

        }

        protected void lbnSearch_Click(object sender, EventArgs e)
        {
            if (DataPager1 != null)
            {
                DataPager1.SetPageProperties(0, DataPager1.PageSize, true);
            }

            // Rebind ListView
            lvdatatable.DataBind();

        }

        protected void btnupload_Click(object sender, EventArgs e)
        {
            int dataEntryId = Convert.ToInt32(hidVoucherId.Value);
            if (dataEntryId < 1)
                return;

            List<KeyValuePair<string, object>> sqlData = new List<KeyValuePair<string, object>>();
            sqlData.Add(new KeyValuePair<string, object>("dataEntryId", dataEntryId));
            sqlData.Add(new KeyValuePair<string, object>("blob_storage_folder", Folder));
            string UpdateQry = "UPDATE data_entry SET [blob_storage_folder]=@blob_storage_folder WHERE id=@dataEntryId  AND (blob_storage_folder IS NULL OR blob_storage_folder = '')";
            int resu = DataService.ExecuteSql(UpdateQry, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, sqlData);

            DU_blobFileName.Value = "";
            rptrAttachments.DataSource = lstupload;
            rptrAttachments.DataBind();
            createUploadDetailsXML();
        }


        public string GetCurrentUrl()
        {
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            string guid = Guid.NewGuid().ToString();

            return strUrl.TrimEnd(new char[] { '/' });
        }

        protected void DeleteFile(object sender, EventArgs e)
        {

        }

        private void updateVentries(string DocumentID, string DocumentName, string DocumentNarration, string blobUrl, string fileName)
        {
            List<upload> ventries = lstupload;

            if (!blobUrl.IsNullOrWhiteSpace())
            {
                ventries.Add(new upload
                {
                    DocumentID = DocumentID,
                    DocumentName = DocumentName,
                    DocumentNarration = DocumentNarration.Replace("<br />", Environment.NewLine),
                    DocumentURL = blobUrl,
                    FileName = fileName
                });
            }


            lstupload = ventries;

            rptrAttachments.DataSource = lstupload;
            rptrAttachments.DataBind();


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

        protected String CreateXMLFromList()
        {
            XDocument xmlDocument = new XDocument(
                new XDeclaration("1.0", "utf-8", "yes"),
                new XComment("Finascop Upload Document Details"),
                new XElement("Upload",
                    from upload in lstupload
                    where upload != null // Ensure upload itself is not null
                    select new XElement("UploadDetails",
                        upload.DocumentID != null ? new XAttribute("DOC_Index", upload.DocumentID) : null,
                        upload.DocumentName != null ? new XElement("DocumentName", upload.DocumentName) : null,
                        upload.DocumentNarration != null ? new XElement("DocumentNarration", new XText(upload.DocumentNarration)) : null,
                        upload.DocumentURL != null ? new XElement("DocumentURL", upload.DocumentURL) : null,
                        upload.FileName != null ? new XElement("FileName", upload.FileName) : null
                    )
                )
            );


            return xmlDocument.ToString();
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

                    fileUrl = Common.CreateBlob(uploadInfoXML, $"UploadDetails.xml", $"finascopupload/{Folder}").Result;
                    xmlFileCreated = true;
                }
                catch (Exception blobExists)
                {
                    var result = Common.DeleteBlob(blobExists.InnerException.Message);
                }
            }
        }

    }

}
