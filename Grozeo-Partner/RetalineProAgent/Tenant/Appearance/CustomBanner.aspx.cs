using Amazon.DynamoDBv2.Model;
using Newtonsoft.Json;
using RetalineProAgent.Core.Services;
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

namespace RetalineProAgent.Appearance
{
    public partial class CustomBanner : Base.BasePartnerPage
    {
        public string HiddenImageUrl
        {
            get { return hiddenImageUrl.Value; }
            set { hiddenImageUrl.Value = value; }
        }

        public string HiddenKey
        {
            get { return hiddenKey.Value; }
            set { hiddenKey.Value = value; }
        }

        public class ImageInfo
        {
            public string name { get; set; }
            public int type { get; set; }
            public string url { get; set; }
        }

        public string userlogoImage { get; set; }
        protected void Page_Load(object sender, EventArgs e)
        {
            string designId = Request.QueryString["designid"];
            designImage.Src = designId;
            viewImage.Src = designId;
            string templateId = Request.QueryString["id"];
            List<KeyValuePair<string, object>> templateparams = new List<KeyValuePair<string, object>>();
            templateparams.Add(new KeyValuePair<string, object>("templateId", templateId));
            DataTable dtTemplate = DataServiceMySql.GetDataTable($"SELECT templateID, templateUrl, designUrl FROM graphics_template WHERE STATUS=1 AND id= @templateId", Service.UserService.GetAPIConnectionString(), templateparams);
            if (dtTemplate != null && dtTemplate.Rows.Count > 0)
            {
                string templateUrl = dtTemplate.Rows[0]["templateUrl"].ToString();
                string resolvedUrl = ResolveUrl(templateUrl);
                HiddenImageUrl = resolvedUrl;

                String strUrl = this.CurrentUser.PublicSiteUrl;
                string qrcodeURL = GetFullUrl("~/GenQRCode.ashx?content=" + strUrl); //"https://chart.googleapis.com/chart?chs=350x350&cht=qr&chl=" + strUrl;
                HiddenKey = qrcodeURL;

                string storeName = this.CurrentUser.StoreGroupName;
                string storeAddress = this.CurrentUser.Address;
                string storeMail = this.CurrentUser.Email;
                string storePhone = this.CurrentUser.Phone;

                if (this.CurrentUser.LogoImage == "")
                {
                    Console.WriteLine("Please add a logo!");
                    userlogoImage = "";
                }
                else
                {
                    userlogoImage = this.CurrentUser.LogoImage;
                }


                var jsonData = new
                {
                    storename = storeName,
                    address = storeAddress,
                    email = storeMail,
                    websiteurl = strUrl,
                    phone = storePhone,
                    Images = new ImageInfo[]
                    {
                        new ImageInfo { name = "Template", type = 1, url = resolvedUrl },
                        new ImageInfo { name = "QRCode", type = 2, url = qrcodeURL },
                        new ImageInfo { name = "UserLogo", type = 3, url = userlogoImage },
                    }
                };

                // Convert the C# object to JSON
                string jsonString = JsonConvert.SerializeObject(jsonData);

                // Register the JavaScript variable
                Page.ClientScript.RegisterStartupScript(this.GetType(), "MyScript", $"var serverData = {jsonString};", true);

            }


        }

        //protected void selElement_SelectedIndexChanged(object sender, EventArgs e)
        //{
        //    if (selElement.SelectedValue == "QR Code")
        //    {
        //        HiddenKey = "https://qrcode.tec-it.com/API/QRCode?data=" + this.CurrentUser.PublicSiteUrl;
        //    }
        //    if (selElement.SelectedValue == "Logo")
        //    {
        //        HiddenKey = this.CurrentUser.LogoImage;
        //    }
        //}

        public string GetQRCodeURL()
        {
            String strUrl = this.CurrentUser.PublicSiteUrl;
            string qrCodeURL = GetFullUrl("~/GenQRCode.ashx?content=" + strUrl);//"https://chart.googleapis.com/chart?chs=350x350&cht=qr&chl=" + strUrl;

            return qrCodeURL;
        }

        //public string GetCurrentUrl()
        //{

        //    String strUrl = this.CurrentUser.PublicSiteUrl;
        //    string qrcodeURL = "https://chart.googleapis.com/chart?chs=350x350&cht=qr&chl=" + strUrl;
        //    HiddenKey = qrcodeURL;
        //    return HiddenKey;
        //}

        protected string ResolveImageUrl(string relativePath)
        {
            return ResolveUrl(relativePath);
        }

        protected string GetTemplateUrl()
        {
            // Construct the template URL based on the query string parameter
            string templateId = Request.QueryString["id"];
            string templateUrl = ResolveUrl("~/Tenant/Appearance/CustomBanner?id=" + templateId);

            return templateUrl;
        }

        protected async void lbtnUpload_Click(object sender, EventArgs e)
        {
            string imageData = hiddenImageData.Value;
            int storeGroupId = this.CurrentUser.APIStoreId;
            if (!string.IsNullOrEmpty(imageData))
            {
                // Remove the data URI prefix before converting to bytes
                int index = imageData.IndexOf(',');
                if (index >= 0)
                {
                    imageData = imageData.Substring(index + 1);
                }

                try
                {
                    byte[] fileBytes = Convert.FromBase64String(imageData);
                    // Check if fileBytes is not null and has valid data
                    if (fileBytes != null && fileBytes.Length > 0)
                    {
                        using (MemoryStream fileStream = new MemoryStream(fileBytes))
                        {
                            string filename = GenerateUniqueFilename();
                            string graphicsFilename = filename;
                            string strFileGraphics = FileService.UploadGraphics(fileStream, filename, storeGroupId, graphicsFilename);
                            string graphicsURL = strFileGraphics;
                            Guid uuid = Guid.NewGuid();
                            string uuidAsString = uuid.ToString();
                            string storeId = Convert.ToString(storeGroupId);
                            string templateId = Request.QueryString["id"];
                            DateTime currentDateTime = DateTime.Now;
                            TimeZoneInfo timeZone = TimeZoneInfo.FindSystemTimeZoneById("UTC");
                            DateTime currentTimeInDesiredTimeZone = TimeZoneInfo.ConvertTime(currentDateTime, timeZone);
                            string formattedDateTime = currentTimeInDesiredTimeZone.ToString("yyyyMMdd");
                            string formatDateTime = currentTimeInDesiredTimeZone.ToString("yyyy-MM-dd HH:mm:ss");

                            var itemToWrite = new Dictionary<string, AttributeValue>
                            {
                                { "uuid", new AttributeValue { S = uuidAsString } },
                                { "storeid", new AttributeValue { N = storeId } },
                                { "templateid", new AttributeValue { S = templateId } },
                                { "graphicsURL", new AttributeValue { S = graphicsURL } },
                                { "createddate", new AttributeValue { N = formattedDateTime } },
                                { "createdtime", new AttributeValue { S = formatDateTime } }
                            };
                            //string tableName = "grozeodev_customergraphics";
                            string tableprefix = ConfigurationManager.AppSettings.Get("AWS_Prefix");
                            string table = "customergraphics";
                            string tableName = String.Concat(tableprefix, table);
                            //DynamoService.SaveToDynamoDb(tableName, itemToWrite);

                            DynamoService.SaveToDynamoDb(tableName, itemToWrite);

                            Response.Redirect("/Tenant/Appearance/CustomisedGraphics");
                            // Show a success message
                            //string successScript = "alert('Graphics created successfully!!');";
                            //successScript += "window.location.href = '/Tenant/Appearance/CustomisedGraphics.aspx';";
                            //ClientScript.RegisterStartupScript(this.GetType(), "SuccessScript", successScript, true);
                        }
                    }
                }
                catch (Exception ex)
                {
                    // Handle the exception, such as logging or displaying an error message
                    Console.WriteLine($"Error converting Base64 to byte array: {ex.Message}");
                }
                // ShowSuccess("Success", "Graphics created successfully!!");
            }
            else
            {
                Console.WriteLine("Error: Invalid or empty fileBytes");
            }

        }

        private void ShowSuccess(string title, string content)
        {
            ltrErrorPopupTitle.Text = title;
            ltrErrorPopupText.Text = content;
            Type cstype = this.GetType();
            String csname1 = "PopupScript";
            ClientScriptManager cs = Page.ClientScript;
            ltrSuccessTitle.Text = title;
            ltrSuccessContent.Text = content;

            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append("<script type=text/javascript> $('#modaldemo4').modal('show'); </");
            cstext1.Append("script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());

            //    cs.RegisterClientScriptBlock(cstype, csname1, @"<script type='text/javascript'>$('#modaldemo4').on('hidden.bs.modal', function (e) {
            //      window.location.href='/bankaccount';
            //});</script>");
        }

        private string GenerateUniqueFilename()
        {
            string template = Request.QueryString["tempid"];
            // Implement your logic to generate a unique filename (e.g., using a timestamp)
            string storeName = this.CurrentUser.StoreGroupName;
            string combinedString = storeName.Replace(" ", "");
            return this.CurrentUser.APIStoreId + "_" + combinedString + "_" + template + "_" + DateTime.Now.ToString("dd/MM/yyyy:hh:mm:ss") + ".png";
        }

        private string GetFullUrl(string relativeFilePath)
        {
            // Get the current request
            HttpRequest request = HttpContext.Current.Request;

            // Get the scheme (HTTP/HTTPS)
            string scheme = request.Url.Scheme;

            // Get the host (e.g., www.example.com)
            string host = request.Url.Host;

            // Get the port (if it's not the default port for the scheme)
            string port = request.Url.IsDefaultPort ? "" : ":" + request.Url.Port;

            // Combine to form the base URL
            string baseUrl = $"{scheme}://{host}{port}";

            // Combine the base URL with the relative file path
            string fullUrl = new Uri(new Uri(baseUrl), VirtualPathUtility.ToAbsolute(relativeFilePath)).ToString();

            return fullUrl;
        }

    }
}