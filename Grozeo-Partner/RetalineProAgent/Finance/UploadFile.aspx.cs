using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.IO;
using RetalineProAgent.Service;
using System.Text;
using System.Web.Services;

namespace RetalineProAgent.Finance
{
    public partial class UploadFile: Base.BasePartnerPage
    {
        // Define a dictionary of file extensions and their magic headers
        private static readonly Dictionary<string, byte[]> FileSignatures = new Dictionary<string, byte[]>
        {
            { ".jpg", new byte[] { 0xFF, 0xD8, 0xFF } },       // JPEG
            { ".png", new byte[] { 0x89, 0x50, 0x4E, 0x47 } }, // PNG
            { ".pdf", new byte[] { 0x25, 0x50, 0x44, 0x46 } }, // PDF
        };
        protected void Page_Load(object sender, EventArgs e)
        {
            lblMessage.Text = "";
            if (String.IsNullOrEmpty(Request.QueryString["key"])|| String.IsNullOrEmpty(Request.QueryString["file"]))
            {
                plcUpload.Visible = false;
                lblMessage.Text = "Invalid Operation.";
            }
            else
            {
                plcUpload.Visible = true;
                fupDocProof.Attributes["onchange"] = "UploadFile(this)";
            }
            plcScript.Visible = !IsPostBack;
            lblProofStatus.Text = $"";

        }

        private bool ValidateFile(Stream fileStream, string extension)
        {
            // Check if the extension is allowed
            if (!FileSignatures.ContainsKey(extension))
                return false;

            // Read the file's magic header
            byte[] header = new byte[FileSignatures[extension].Length];
            fileStream.Read(header, 0, header.Length);

            // Compare the file's magic header with the expected signature
            byte[] expectedHeader = FileSignatures[extension];
            for (int i = 0; i < expectedHeader.Length; i++)
            {
                if (header[i] != expectedHeader[i])
                    return false;
            }

            return true;
        }

        protected void btnupload_Click(object sender, EventArgs e)
        {
            lblProofStatus.Text = "";
            string strkey = Request.QueryString["key"];
            string fname = Request.QueryString["file"];
            if (String.IsNullOrEmpty(Request.QueryString["key"]) || String.IsNullOrEmpty(Request.QueryString["file"]))
            {
                plcUpload.Visible = false;
                lblMessage.Text = "Invalid Operation.";
                return;
            }
            if (String.IsNullOrEmpty(fname))
            {
                plcUpload.Visible = false;
                lblMessage.Text = "Invalid File name.";
                return;
            }
            if (fupDocProof.HasFile)
            {
                string filePath = fupDocProof.PostedFile.FileName;
                string extension = Path.GetExtension(filePath).ToLower();

                string[] validFileTypes = { "pdf", "png", "jpg", "jpeg" };
                string ext = System.IO.Path.GetExtension(fupDocProof.PostedFile.FileName);
                bool isValidFile = false;
                for (int i = 0; i < validFileTypes.Length; i++)
                {
                    if (ext == "." + validFileTypes[i])
                    {
                        isValidFile = true;
                        break;
                    }
                }
                if (!isValidFile)
                {
                    if (ValidateFile(fupDocProof.PostedFile.InputStream, extension))
                    {
                        lblProofStatus.ForeColor = System.Drawing.Color.Red;
                        lblProofStatus.Text = "Invalid File. Please upload a File with type " +
                                       string.Join(",", validFileTypes);
                    }

                }
                else
                {
                    string filename = fname; //Path.GetFileName(fupDocProof.FileName);
                    txtdocname.Text = filename;
                   
                    string resultProof = Common.CreateBlob(fupDocProof.PostedFile.InputStream, filename, $"finascopupload/{strkey}").Result;
                    if (!string.IsNullOrEmpty(resultProof))
                    {
                        //lblProofStatus.Text = resultProof;
                        lblProofStatus.ForeColor = System.Drawing.Color.Green;
                        lblProofStatus.Text = $"File uploaded successfully. ";// + resultProof;

                        documentupload_input.Attributes["class"] += " disabled"; // Add a 'disabled' CSS class
                        documentupload_input.Attributes["style"] = "pointer-events: none; opacity: 0.5;"; // Make it visually disabled

                        //https://odocartstorage.blob.core.windows.net/odo-files/finascopupload/44646413515316/SOINV2020000003.pdf
                        //lblProofStatus.Text += ", url= https://odocartstorage.blob.core.windows.net/odo-files/finascopupload/" + strkey +"/" + filename;
                    }
                    else
                    {
                        lblProofStatus.ForeColor = System.Drawing.Color.Red;
                        lblProofStatus.Text = "Failed to uploaad file to blob.";
                    }

                }



            }
            else
            {
                lblProofStatus.ForeColor = System.Drawing.Color.Red;
                lblProofStatus.Text = "No File selected. Please upload a File.";
                            
            }



        }

        [WebMethod]
        public static string deleteBlobFile(string blobFileURL)
        {
            bool success = Common.DeleteBlob(blobFileURL).Result;
            return "Success";
        }

    }
}