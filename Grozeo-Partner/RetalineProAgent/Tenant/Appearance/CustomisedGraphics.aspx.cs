using Amazon.DynamoDBv2;
using Amazon.DynamoDBv2.Model;
using Amazon.Runtime;
using RetalineProAgent.Core.BussinessModel.Dynamo;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.IO;
using System.Linq;
using System.Net;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Appearance
{
    public partial class CustomisedGraphics : Base.BasePartnerPage
    {
        protected async void Page_Load(object sender, EventArgs e)
        {
            int storeGroupId = this.CurrentUser.APIStoreId;
            string storeId = Convert.ToString(storeGroupId);
            //string tableName = "grozeodev_customergraphics";
            string tableprefix = ConfigurationManager.AppSettings.Get("AWS_Prefix");
            string table = "customergraphics";
            string tableName = String.Concat(tableprefix, table);

            // Declare and initialize the graphicsDataList
            List<GraphicsData> graphicsDataList = new List<GraphicsData>();

            graphicsDataList = DynamoService.GetGraphicsDataByStoreId(tableName, "storeid", storeId);
            graphicsDataList.Reverse();
            // Bind the graphicsDataList to the Repeater control
            rptOwnbanners.DataSource = graphicsDataList;
            rptOwnbanners.DataBind();
            this.DataBind();
            //rptOwnbanners.ItemCommand += rptOwnbanners_ItemCommand;
        }


        protected void btnDownload_OnClick(object sender, EventArgs e)
        {
            LinkButton lnkDownload = (LinkButton)sender;
            string graphicsURL = lnkDownload.CommandArgument;

            try
            {
                // Extract the file name from the URL
                string fileName = Path.GetFileName(new Uri(graphicsURL).LocalPath);

                // Download the file from the URL
                using (WebClient webClient = new WebClient())
                {
                    byte[] data = webClient.DownloadData(graphicsURL);

                    // Set response headers to initiate the download
                    Response.Clear();
                    Response.ClearHeaders();
                    Response.ClearContent();
                    Response.AddHeader("Content-Disposition", $"attachment; filename={fileName}");
                    Response.ContentType = GetMimeType(fileName);

                    // Write the downloaded data to the response stream
                    Response.BinaryWrite(data);

                    // Flush the response buffer before ending
                    Response.Flush();
                    Response.SuppressContent = true;  // Suppress further content
                    HttpContext.Current.ApplicationInstance.CompleteRequest();  // End request without aborting thread
                }
            }
            catch (Exception ex)
            {
                // Log or handle exceptions
                Response.Write("Error: " + ex.Message);
            }
        }

        private string GetMimeType(string fileName)
        {
            string mimeType = MimeMapping.GetMimeMapping(fileName);
            return string.IsNullOrEmpty(mimeType) ? "application/octet-stream" : mimeType;
        }
    }
}