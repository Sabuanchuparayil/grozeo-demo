using Amazon.Runtime;
using Microsoft.Azure.Management.WebSites.Models;
using Newtonsoft.Json.Linq;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Drawing;
using System.Drawing.Drawing2D;
using System.Drawing.Imaging;
using System.IO;
using System.IO.Compression;
using System.Linq;
using System.Net;
using System.Net.Http;
using System.Security;
using System.Security.Cryptography;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.HtmlControls;
using System.Web.UI.WebControls;
using System.Windows.Documents;
using System.Xml;
using User = RetalineProAgent.Service.User;

namespace RetalineProAgent
{
   
    public partial class MobileApp : Base.BasePartnerPage
    {
        private string appPackage; // Declare this variable at the class level
        private static string bucketNameforAndroid = ConfigurationManager.AppSettings.Get("AWS_S3_BucketAndroid");
        private static string bucketNameforTenant = ConfigurationManager.AppSettings.Get("AWS_S3_BucketTenant");
        private static string bucketNameforSpashscreen = ConfigurationManager.AppSettings.Get("AWS_S3_SpashscreenBucket");
        private static string appregion = ConfigurationManager.AppSettings.Get("AWS_AppRegion");

        DateTime lastModifiedDateTime;
        protected void Page_Load(object sender, EventArgs e)
        {

            if (!IsPostBack)
            {
                LoadInfo();

            }
        }

        private void LoadInfo()
        {
            int storeGroupId = this.CurrentUser.APIStoreId;
            //plcCreateApp.Visible = true;
            //plcAndroidApp.Visible = false;


            string uploadResult = storeGroupId.ToString() + ".xml";
            int xmlFileExists = FileService.GetXmlFileCount(storeGroupId, uploadResult);
           
            if (xmlFileExists > 0)
            {
                ProcessXmlFile(uploadResult, storeGroupId);
            }
            else
            {
                progressMessages.Visible = true;
                frmmobileview.Visible = false;
                statusCheck.Visible = false;
                btnDownload.Visible = false;
                btnRebuild.Visible = false;
                phone.Visible = true;
                android.Visible = true;
                appScreen.Visible = false;
                plcCreateApp.Visible = true;
                plcAndroidApp.Visible = false;
            }
        }

        private void ProcessXmlFile(string uploadResult, int storeGroupId)
        {
            //string s3BucketName = "grozeoindia-tenentapp-frontenddata";
            string s3ObjectKey = "xml-files/" + "preview-" + uploadResult;
            string xmlContent = FileService.DownloadXmlFromS3(s3ObjectKey);

            // Process XML data
            ProcessXmlData(xmlContent);

            // Disable/enable controls as needed
            txtNameApp.Enabled = false;
            txtHeadLine.Enabled = false;
            txtDescription.Enabled = false;
            nameSuggest.Enabled = false;
            suggestHeadLine.Enabled = false;
            suggestDescription.Enabled = false;
            btnUpload.Enabled = false;
            //btnCancel.Enabled = false;
            applogoupload.Disabled = true;
            SplashScreenOneupload.Disabled = true;
            progressMessages.Visible = true;
            frmmobileview.Visible = false;
            statusCheck.Visible = true;
            createApp.Visible = false;
            appProcessing.Visible = true; // Show the "appProcessing" element.
            congratsMessage.Visible = false;
            btnDownload.Visible = false;
            btnRebuild.Visible = false;
            phone.Visible = false;
            android.Visible = true;
            appScreen.Visible = false;
            plcCreateApp.Visible = false;
            plcAndroidApp.Visible = true;
            lblBuildProgress.Visible = true;
            lblBuildComplete.Visible = false;


            string upResult = "key.json";
            int jsonFileExists = FileService.GetJsonFileCount(storeGroupId, upResult);
            if (jsonFileExists > 0)
            {
                // Retrieve XML data for JSON case if not retrieved earlier
                if (xmlContent == null)
                {
                    //string s3BktName = "grozeoindia-tenentapp-frontenddata";
                    string s3ObtKey = "xml-files/" + "preview-" + uploadResult;
                    xmlContent = FileService.DownloadXmlFromS3(s3ObtKey);

                    // Process XML data
                    ProcessXmlData(xmlContent);
                }
                btnDownload.Visible = true;
                btnRebuild.Visible = true;
                string updtResult = storeGroupId.ToString() + "/" + "key.json";
                //string s3BucktName = "grozeoindia-tenentapp-frontenddata";
                string s3ObjtKey = "Store-Data/" + updtResult;
                string jsonContent = FileService.ReadJsonFromS3(s3ObjtKey);

                if (jsonContent != null)
                {
                    // Proceed with processing the JSON content
                    string jsonRead = jsonContent;
                    // Parse JSON content as a JObject
                    JObject jsonObject = JObject.Parse(jsonContent);

                    // Access the desired property
                    string desiredValue = (string)jsonObject["publicKey"];

                    // Disable/enable controls as needed
                    txtNameApp.Enabled = false;
                    txtHeadLine.Enabled = false;
                    txtDescription.Enabled = false;
                    nameSuggest.Enabled = false;
                    suggestHeadLine.Enabled = false;
                    suggestDescription.Enabled = false;
                    btnUpload.Enabled = false;
                    //btnCancel.Enabled = false;
                    applogoupload.Disabled = true;
                    SplashScreenOneupload.Disabled = true;
                    progressMessages.Visible = true;
                    statusCheck.Visible = false;
                    createApp.Visible = false;
                    congratsMessage.Visible = false;
                    btnDownload.Visible = true;
                    btnRebuild.Visible = true;
                    frmmobileview.Visible = true;
                    phone.Visible = false;
                    android.Visible = false;
                    appScreen.Visible = true;
                    lblBuildProgress.Visible = false;
                    lblBuildComplete.Visible = true;
                    frmmobileview.Src = "https://appetize.io/embed/" + desiredValue + "?device=pixel4&osVersion=12.0&scale=75";
                    appProcessing.Visible = false;
                }
                else
                {
                    Common.ShowToastifyMessage(this.Page, "JSON file not fount.", "danger");
                }

            }
        }

        private void ProcessXmlData(string xmlContent)
        {
            try
            {
                // Load the XML content into an XmlDocument
                XmlDocument xmlDoc = new XmlDocument();
                xmlDoc.LoadXml(xmlContent);

                // XPath expression to select all "string" nodes with a specific "name" attribute
                XmlNodeList nodes = xmlDoc.SelectNodes("//string[@name]");
                string tenantUrl = ConfigurationManager.AppSettings["tenantapp.url"];
                foreach (XmlNode node in nodes)
                {
                    string nameAttribute = node.Attributes["name"].Value;
                    string nodeValue = node.InnerText;

                    // Assign values to specific TextBox controls based on the "name" attribute
                    if (nameAttribute == "app_name")
                    {
                        txtNameApp.Text = nodeValue;
                        lblNameOfApp.Text = nodeValue;
                    }
                    else if (nameAttribute == "app_package")
                    {
                        appPackage = nodeValue;
                        hfAppPackage.Value = appPackage; // Store the value in the hidden field
                    }
                    else if (nameAttribute == "headLine")
                    {
                        txtHeadLine.Text = nodeValue;
                        lblHeadline.Text = nodeValue;
                    }
                    else if (nameAttribute == "description")
                    {
                        txtDescription.Text = nodeValue;
                        lblDescription.Text = nodeValue;
                    }
                    else if (nameAttribute == "logoImage")
                    {
                        string imageLogo = nodeValue;

                        imageMainLogo.Src = tenantUrl + "tenentapp-logo/mipmap-mdpi/" + "preview-" + imageLogo;

                        // Set the URLs in the corresponding divs using JavaScript
                        string script = @"
                        var logoData48 = '" + tenantUrl + "tenentapp-logo/mipmap-mdpi/" + "preview-" + imageLogo + @"';
                        var logoData72 = '" + tenantUrl + "tenentapp-logo/mipmap-hdpi/" + "preview-" + imageLogo + @"';
                        var logoData96 = '" + tenantUrl + "tenentapp-logo/mipmap-xhdpi/" + "preview-" + imageLogo + @"';
                        var logoData144 = '" + tenantUrl + "tenentapp-logo/mipmap-xxhdpi/" + "preview-" + imageLogo + @"';
                        var logoData192 = '" + tenantUrl + "tenentapp-logo/mipmap-xxxhdpi/" + "preview-" + imageLogo + @"';

                        document.getElementById('imgLogo48').src = logoData48;
                        document.getElementById('imgLogo72').src = logoData72;
                        document.getElementById('imgLogo96').src = logoData96;
                        document.getElementById('imgLogo144').src = logoData144;
                        document.getElementById('imgLogo192').src = logoData192;
                    ";

                        // Register the script to be executed when the page loads
                        ClientScript.RegisterStartupScript(this.GetType(), "LoadLogoImages", script, true);

                    }
                    else if (nameAttribute == "screenImage")
                    {
                        string imageScreen = nodeValue;
                        imageSplashScreen.Src = tenantUrl + "tenentappsplashscreen/" + "preview-" + imageScreen;
                    }

                }
            }
            catch
            {
                Common.ShowCustomAlert(this.Page, "Failed", "", false, "/tenant/mobileapp?test");
            }
           
        }

        // Helper function to extract values from XML nodes
        private string GetXmlNodeValue(XmlDocument xmlDoc, string xpath)
        {
            XmlNode node = xmlDoc.SelectSingleNode(xpath);
            return (node != null) ? node.InnerText : null;
        }

        public Control FindControlRecursive(Control root, string id)
        {
            if (root == null)
            {
                return null;
            }

            Control foundControl = root.FindControl(id);
            if (foundControl != null)
            {
                return foundControl;
            }

            foreach (Control control in root.Controls)
            {
                foundControl = FindControlRecursive(control, id);
                if (foundControl != null)
                {
                    return foundControl;
                }
            }

            return null;
        }



        protected string UploadImages(int storeGroupId)
        {
            if (storeGroupId < 0)
                return string.Empty;

            HtmlInputFile applogoupload = (HtmlInputFile)FindControlRecursive(this, "applogoupload");
            if (applogoupload != null)
            {     

                try
                {
                    string strFileLogo = FileService.UploadFileToS3(appregion,applogoupload.PostedFile.InputStream, applogoupload.PostedFile.FileName, bucketNameforAndroid);
                    return strFileLogo;

                }
                catch (Exception ex)
                {
                    Common.ShowToastifyMessage(this.Page, "Image is not selected.", "danger");
                }
            }
            return string.Empty;
        }


        protected string UploadSceenImage(int storeGroupId)
        {
            if (storeGroupId < 0)
                return string.Empty;

            HtmlInputFile SplashScreenOneupload = (HtmlInputFile)FindControlRecursive(this, "SplashScreenOneupload");
            if (SplashScreenOneupload != null)
            {
                try
                {
                    string strFileSplashScreen = FileService.UploadFileToS3(appregion,SplashScreenOneupload.PostedFile.InputStream, SplashScreenOneupload.PostedFile.FileName, bucketNameforSpashscreen);
                    return strFileSplashScreen;
                }
                catch
                {
                    Common.ShowToastifyMessage(this.Page, "Image is not selected.", "danger");
                }
            }
            return string.Empty;
        }


        public class AppMetadata
        {
            public string AppName { get; set; }
            public string AppPackage { get; set; }
            public int StoreGroupId { get; set; }
          
            public string HeadLine { get; set; }
            public string Description { get; set; }

        }        
        private static void AppendXmlElement(XmlDocument doc, XmlElement parent, string name, string value)
        {
            XmlElement element = doc.CreateElement("string");
            element.SetAttribute("name", name);
            element.InnerText = value;
            parent.AppendChild(element);
        }
        public static string GenerateXml(string txtNameApp, string storeGroupId, string site, string txtHeadLine, string txtDescription, string logoImage, string screenImage)
        {
            XmlDocument doc = new XmlDocument();
            XmlElement root = doc.CreateElement("resources");
            doc.AppendChild(root);

            // Properly escape special characters in all the inputs before adding them to the XML
            AppendXmlElement(doc, root, "app_name", SecurityElement.Escape(txtNameApp));
            AppendXmlElement(doc, root, "app_package", SecurityElement.Escape(txtNameApp.Replace(" ", "")));
            AppendXmlElement(doc, root, "storegroupid", SecurityElement.Escape(storeGroupId));
            AppendXmlElement(doc, root, "site", SecurityElement.Escape(site));
            AppendXmlElement(doc, root, "headLine", SecurityElement.Escape(txtHeadLine));
            AppendXmlElement(doc, root, "description", SecurityElement.Escape(txtDescription));
            AppendXmlElement(doc, root, "logoImage", SecurityElement.Escape(logoImage));
            AppendXmlElement(doc, root, "screenImage", SecurityElement.Escape(screenImage));

            // Use StringWriter and XmlTextWriter to format the XML
            StringWriter sw = new StringWriter();
            XmlTextWriter xmlWriter = new XmlTextWriter(sw);
            xmlWriter.Formatting = Formatting.Indented;
            doc.WriteTo(xmlWriter);

            return sw.ToString();
        }

        protected async void btnUploadMobile_Click(object sender, EventArgs e)
        {
            try
            {
                int storeGroupId = this.CurrentUser.APIStoreId;
                string urlFromConfig = ConfigurationManager.AppSettings["api.url"];
                string site = "";
                if (urlFromConfig != null)
                {
                    site = ConfigurationManager.AppSettings["SiteName"];
                }              
                string logoImage = UploadImages(storeGroupId);
                string screenImage = UploadSceenImage(storeGroupId);

                string strXmlMetadata = GenerateXml(
                    txtNameApp.Text,   // First parameter: txtNameApp (app name)
                   Convert.ToString(storeGroupId),      // Second parameter: storeGroupId
                    site,              // Third parameter: site
                    txtHeadLine.Text,  // Fourth parameter: txtHeadLine (headline)
                    txtDescription.Text, // Fifth parameter: txtDescription
                    logoImage,         // Sixth parameter: logoImage
                    screenImage        // Seventh parameter: screenImage
                );
                //  string strXmlMetadata = @"
                //<resources>
                //   <string name=""app_name"">" + ReplaceSpecialCharacters(txtNameApp.Text) + @"</string>
                //   <string name=""app_package"">" + ReplaceSpecialCharacters(txtNameApp.Text.Replace(" ", "")) + @"</string>
                //   <string name=""storegroupid"">" + storeGroupId + @"</string>
                //   <string name=""site"">" + site + @"</string>
                //   <string name=""headLine"">" + ReplaceSpecialCharacters(txtHeadLine.Text) + @"</string>
                //   <string name=""description"">" + ReplaceSpecialCharacters(txtDescription.Text) + @"</string>
                //   <string name=""logoImage"">" + logoImage + @"</string>
                //   <string name=""screenImage"">" + screenImage + @"</string>
                //</resources>";

                string xmlFilename = storeGroupId.ToString() + ".xml";
                FileService.S3UploadResult uploadResult = FileService.UploadFileToS3(strXmlMetadata, xmlFilename, "xml-files/", "");
                if (uploadResult != null)
                {
                    Common.ShowCustomAlert(this.Page, "Success", "Thanks for choosing Grozeo to build your Mobile Application. Please check your status.", true, "/tenant/mobileapp?test");
                }
                return;
            }
            catch
            {
                Common.ShowCustomAlert(this.Page, "Failed", "", false, "/tenant/mobileapp?test");
            }
           
        }
        protected async void btnCancel_Click(object sender, EventArgs e)
        {
            Response.Redirect("/Tenant/MobileApp");
        }

        protected void nameSuggest_Click(object sender, EventArgs e)
        {
            // Check character count before proceeding
            if (!IsCharacterCountValid(txtNameApp, 30))
            {
                // Display an error message or take appropriate action
                // For example, show an alert:
                ScriptManager.RegisterStartupScript(this, GetType(), "CharacterCountError", "alert('Head line should be 50 characters or less.');", true);
                return;
            }
            User user = this.CurrentUser;
            txtNameApp.Text = user.StoreGroupName;
            //txtNameApp.Visible = false;
        }

        protected void suggestHeadLine_Click(object sender, EventArgs e)
        {
            // Check character count before proceeding
            if (!IsCharacterCountValid(txtHeadLine, 80))
            {
                // Display an error message or take appropriate action
                // For example, show an alert:
                ScriptManager.RegisterStartupScript(this, GetType(), "CharacterCountError", "alert('Head line should be 50 characters or less.');", true);
                return;
            }
            txtHeadLine.Text = "Welcome to Grozeo India!";
            //txtHeadLine.Visible = false;
            //txtHeadLine.Attributes.Add("maxlength", "50");
        }

        private bool IsCharacterCountValid(TextBox textBox, int maxLength)
        {
            return textBox.Text.Length <= maxLength;
        }

        protected void suggestDescription_Click(object sender, EventArgs e)
        {
            // Check character count before proceeding
            if (!IsCharacterCountValid(txtDescription, 4000))
            {
                // Display an error message or take appropriate action
                // For example, show an alert:
                ScriptManager.RegisterStartupScript(this, GetType(), "CharacterCountError", "alert('Description should be 100 characters or less.');", true);
                return;
            }
            txtDescription.Text = "Welcome to Grozeo India! We are a locally owned and operated business committed to providing our customers with top-quality products and exceptional customer service.";
            //txtDescription.Visible = false;
            //txtDescription.Attributes.Add("maxlength", "100");
        }


        protected void btnDownload_Click(object sender, EventArgs e)
        {
            try
            {
                int storeGroupId = this.CurrentUser.APIStoreId;
                string storedAppPackage = hfAppPackage.Value;
                string updtResult = storeGroupId.ToString() + "/" + storedAppPackage + ".apk";
                string s3FilePath = "Store-Data/" + updtResult;
                string s3BucketUrl = ConfigurationManager.AppSettings["tenantapp.url"];
                string fileName = storedAppPackage + ".apk";

                // Construct the full URL to the APK file on S3
                string s3FileUrl = s3BucketUrl + s3FilePath;


                // Check if the file exists on S3 before attempting to download
                if (!FileExistsOnS3(s3FileUrl))
                {
                    Response.Write("Error: File not found on S3.");
                    return;
                }

                // Set response headers to initiate the download
                Response.Clear();
                Response.ClearHeaders();
                Response.ClearContent();
                Response.AddHeader("Content-Disposition", "attachment; filename=" + fileName);
                Response.ContentType = "application/vnd.android.package-archive";

                // Download the APK file from S3
                using (WebClient client = new WebClient())
                {
                    byte[] data = client.DownloadData(s3FileUrl);
                    Response.BinaryWrite(data);
                }

                // Flush the response buffer before ending
                Response.Flush();
                Response.SuppressContent = true;  // Suppress further content
                HttpContext.Current.ApplicationInstance.CompleteRequest();  // End request without aborting thread
            }
            catch (Exception ex)
            {
                // Log or handle exceptions
                Response.Write("Error: " + ex.Message);
            }
        }

        //Helper function to check if the file exists on S3
        private bool FileExistsOnS3(string s3FileUrl)
        {
            try
            {
                HttpWebRequest request = (HttpWebRequest)WebRequest.Create(s3FileUrl);
                request.Method = "HEAD"; // Use HEAD method to check file existence without downloading it
                using (HttpWebResponse response = (HttpWebResponse)request.GetResponse())
                {
                  return response.StatusCode == HttpStatusCode.OK;

                }

            }
            catch (WebException)
            {
                return false; // File not found or other error
            }
        }

        private void GetXmlFileDate(string s3XmlFileUrl)
        {
            try
            {
                HttpWebRequest request = (HttpWebRequest)WebRequest.Create(s3XmlFileUrl);
                request.Method = "HEAD"; // Use HEAD method to fetch metadata without downloading the file

                using (HttpWebResponse response = (HttpWebResponse)request.GetResponse())
                {
                    if (response.StatusCode == HttpStatusCode.OK)
                    {
                        // Get the Last-Modified header
                        string lastModifiedHeader = response.Headers["Last-Modified"];

                        // Parse the Last-Modified header to DateTime and save it to the class-level variable
                        lastModifiedDateTime = Convert.ToDateTime(lastModifiedHeader);
                    }
                    else
                    {
                        // Handle the case where the date parsing fails
                        lastModifiedDateTime = DateTime.MinValue; // Or another default value
                    }
                    }

            }
            catch (WebException)
            {
                // Handle the exception as needed, and possibly set a default or error value
                lastModifiedDateTime = DateTime.MinValue; // Or another default value
            }
        }


        protected void btnRebuild_Click(object sender, EventArgs e)
        {
            try
            {
                int storeGroupId = this.CurrentUser.APIStoreId;
                string updtResult = storeGroupId.ToString();
                //string s3BucketName = "grozeoindia-tenentapp-frontenddata";
                string uploadResult = storeGroupId.ToString() + ".xml";
                string sourcePrefix = "Store-Data/" + updtResult;
                string destinationPrefix = "Store-Data/Archieved/" + updtResult;

                string xmlPreview = "xml-files/" + "preview-" + uploadResult;
                string destinationXmlPrefix = "Store-Data/Archieved/" + storeGroupId.ToString() + "/" + "preview-" + uploadResult;

                bool isFoldersMoved = FileService.MoveFileAndXmlPreviewToFolder(xmlPreview, sourcePrefix, destinationPrefix, destinationXmlPrefix);

                if (isFoldersMoved)
                {
                    // Enable/disable controls as needed
                    txtNameApp.Enabled = true;
                    txtHeadLine.Enabled = true;
                    txtDescription.Enabled = true;
                    nameSuggest.Enabled = true;
                    suggestHeadLine.Enabled = true;
                    suggestDescription.Enabled = true;
                    btnUpload.Enabled = true;
                    applogoupload.Disabled = false;
                    SplashScreenOneupload.Disabled = false;
                    progressMessages.Visible = false;
                    frmmobileview.Visible = false;
                    statusCheck.Visible = false;
                    btnDownload.Visible = false;
                    btnRebuild.Visible = false;
                    phone.Visible = true;
                    createApp.Visible = true;
                    android.Visible = false;
                    appScreen.Visible = false;
                    plcCreateApp.Visible = true;
                    plcAndroidApp.Visible = false;
                    string clearScript = @"document.getElementById('" + txtNameApp.ClientID + "').value = '';document.getElementById('" + txtHeadLine.ClientID + "').value = '';  document.getElementById('" + txtDescription.ClientID + "').value = ''; document.getElementById('" + phone.ClientID + "').style.display = 'block';  document.getElementById('" + createApp.ClientID + "').style.display = 'block';  ";
                    ScriptManager.RegisterStartupScript(this, GetType(), "ClearFieldsScript", clearScript, true);
                }
                else
                {
                    Response.Write("Error: Folder move operation failed.");
                }
            }
            catch (Exception ex)
            {
                Response.Write("Error: " + ex.Message);
            }
        }

        protected void statusCheck_Click(object sender, EventArgs e)
        {
            int storeGroupId = this.CurrentUser.APIStoreId;
            string storedAppPackage = hfAppPackage.Value;
            string updtResult = storeGroupId.ToString() + "/" + storedAppPackage + ".apk";
            string s3FilePath = "Store-Data/" + updtResult;

            string uploadResult = storeGroupId.ToString() + ".xml";
            string destinationPrefix = "Store-Data/Archieved/" + updtResult;

            string xmlPreview = "xml-files/" + "preview-" + uploadResult;

            string s3BucketUrl = ConfigurationManager.AppSettings["tenantapp.url"];
            string fileName = storedAppPackage + ".apk";

            // Construct the full URL to the APK file on S3
            string s3FileUrl = s3BucketUrl + s3FilePath;
            string s3XmlFileUrl = s3BucketUrl + xmlPreview;

            GetXmlFileDate(s3XmlFileUrl);

            DateTime date = lastModifiedDateTime;
            DateTime currentdate = DateTime.Now;

            TimeSpan timeDifference = currentdate - lastModifiedDateTime;
            int differenceInHours =(int)timeDifference.TotalHours;

            bool fileExists = FileExistsOnS3(s3FileUrl);

            if (differenceInHours >= 1)
            {
                // Check if the file exists on S3 before attempting to download
                if (!fileExists)
                {
                    Common.ShowCustomAlert(this.Page, "Failed", "Sorry.. Something went wrong!!Unfortunately this time we could not build the Android mobile app for you. We are checking the details of failure.You can retry to submit the request after 24 hours.", false);
                    lblBuildProgress.Text = "App build failed";
                    lblAppStatus.Text = "App build failed";
                    divlodingbusy.Visible = false;
                    lblAppMessage.Text = "Please try again after 24 hours.";
                    statusCheck.Visible = false;
                    btnRebuild.Visible = true;
                    return;
                }
            }
            else
            {
                Common.ShowCustomAlert(this.Page, "", "Your Android mobile app is being build.Please wait.", true);
                lblBuildProgress.Text = "Your App is being build";
                lblAppStatus.Text = "Your App is being build";
                divlodingbusy.Visible = true;
                lblAppMessage.Text = "We are building components of your mobile app.. worth waiting.. please check back after a couple of minutes.";
                statusCheck.Visible = true;
                btnRebuild.Visible = false;
                //btnCancel.Visible = false;
                return;

            }
          
            Response.Redirect("/Tenant/MobileApp");
        }
    }
}
