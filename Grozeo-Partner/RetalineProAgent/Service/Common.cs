using Azure.Storage.Blobs;
using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Data;
using System.IO;
using System.Linq;
using System.Threading.Tasks;
using System.Web;
using System.Web.Hosting;
using System.Configuration;
using RetalineProAgent.Core.Services.HelperServices;
using RetalineProAgent.Core.BussinessModel.Catalog;
using RetalineProAgent.Core.Services;
using System.Web.UI;
using System.Text;
using Azure.Storage.Blobs.Models;
using Azure;
using System.Text.RegularExpressions;

namespace RetalineProAgent.Service
{
    public static class Common
    {
        private static DataTable dtInventory = null;
        public static DataTable DtInventory
        {
            get
            {
                if (dtInventory == null)
                    dtInventory = ReadFromExcel();
                return dtInventory;
            }
        }

        public static string ImageUrl(string imageName)
        {
            return String.Format("{0}{1}", ConfigurationManager.AppSettings.Get("ImageLocation"), imageName);
        }


        public static string OptimizedImageUrl(string imageName, int width = 150, int height = 150)
        {
            string imageLocation = ConfigurationManager.AppSettings.Get("ImageLocation");
            string imageUrl = $"{imageLocation}{imageName}";

            try
            {
                return GenerateResizedImageUrl(imageUrl, width, height);
            }
            catch (Exception ex)
            {
                return "/content/images/image_on_error.svg"; // Fallback image URL
            }
        }

        private static string GenerateResizedImageUrl(string imageUrl, int width, int height)
        {
            using (var client = new System.Net.WebClient())
            using (Stream imageStream = client.OpenRead(imageUrl))
            using (System.Drawing.Image originalImage = System.Drawing.Image.FromStream(imageStream))
            {
                // Calculate new dimensions while maintaining the aspect ratio
                int thumbnailWidth = width;
                int thumbnailHeight = height;
                float aspectRatio = (float)originalImage.Width / originalImage.Height;

                if (originalImage.Width > originalImage.Height)
                {
                    thumbnailHeight = (int)(thumbnailWidth / aspectRatio);
                }
                else
                {
                    thumbnailWidth = (int)(thumbnailHeight * aspectRatio);
                }

                // Resize the image
                using (var resizedImage = new System.Drawing.Bitmap(thumbnailWidth, thumbnailHeight))
                {
                    using (var graphics = System.Drawing.Graphics.FromImage(resizedImage))
                    {
                        graphics.CompositingQuality = System.Drawing.Drawing2D.CompositingQuality.HighQuality;
                        graphics.InterpolationMode = System.Drawing.Drawing2D.InterpolationMode.HighQualityBicubic;
                        graphics.SmoothingMode = System.Drawing.Drawing2D.SmoothingMode.HighQuality;

                        graphics.DrawImage(originalImage, 0, 0, thumbnailWidth, thumbnailHeight);
                    }

                    // Determine the MIME type based on the file extension
                    string fileExtension = Path.GetExtension(imageUrl);
                    string mimeType = GetMimeType(fileExtension);

                    // Convert resized image to Base64 string
                    using (var ms = new MemoryStream())
                    {
                        resizedImage.Save(ms, GetImageFormat(fileExtension));
                        byte[] imageBytes = ms.ToArray();
                        return $"data:{mimeType};base64,{Convert.ToBase64String(imageBytes)}";
                    }
                }
            }
        }

        private static string GetMimeType(string fileExtension)
        {
            switch (fileExtension.ToLower())
            {
                case ".jpg":
                case ".jpeg":
                    return "image/jpeg";
                case ".png":
                    return "image/png";
                case ".gif":
                    return "image/gif";
                default:
                    // Fallback to JPEG MIME type for unsupported extensions
                    return "image/jpeg";
            }
        }

        private static System.Drawing.Imaging.ImageFormat GetImageFormat(string fileExtension)
        {
            switch (fileExtension.ToLower())
            {
                case ".jpg":
                case ".jpeg":
                    return System.Drawing.Imaging.ImageFormat.Jpeg;
                case ".png":
                    return System.Drawing.Imaging.ImageFormat.Png;
                case ".gif":
                    return System.Drawing.Imaging.ImageFormat.Gif;
                default:
                    // Fallback to JPEG format for unsupported extensions
                    return System.Drawing.Imaging.ImageFormat.Jpeg;
            }
        }

        public static DataTable ReadFromExcel(string filePath = "")
        {
            if (String.IsNullOrEmpty(filePath))
                filePath = HostingEnvironment.MapPath("~/App_Data/data/TheMarket.xlsx");
            DataTable dt = new DataTable();
            //HSSFWorkbook hssfwb;
            XSSFWorkbook xssfwb;
            using (FileStream file = new FileStream(filePath, FileMode.Open, FileAccess.Read))
            {
                //hssfwb = new HSSFWorkbook(file);
                xssfwb = new XSSFWorkbook(file);
            }
            ISheet sheet = xssfwb.GetSheetAt(0);
            foreach (ICell cell in sheet.GetRow(0).Cells)
            {
                if (!String.IsNullOrEmpty(cell.StringCellValue) && !dt.Columns.Contains(cell.StringCellValue))
                    dt.Columns.Add(cell.StringCellValue);
            }

            for (int row = 1; row <= sheet.LastRowNum; row++)
            {
                IRow excelRow = sheet.GetRow(row);

                if (excelRow != null) //null is when the row only contains empty cells 
                {
                    DataRow dr = dt.NewRow();
                    for (int i = 0; i < dt.Columns.Count; i++)
                    {
                        ICell cell = excelRow.GetCell(i);
                        if (cell != null)
                        {
                            dr[i] = (cell.CellType == CellType.Numeric ? cell.NumericCellValue.ToString() : cell.StringCellValue);
                        }
                    }
                    dt.Rows.Add(dr);
                }
            }

            return dt;

        }

        public static DataTable GetBrands(string searchKey, int storeId, double defaultMargine)
        {
            string strSql = $"select BarCode as BrandCode, MarginePercentage from Margine where StoreId={storeId} and MargineType=1";
            DataTable dtMargine = DataService.GetDataTable(strSql);

            DataTable dt = new DataTable();
            string[] selectedColumns = new[] { "MIH_ITEM_MFR_CODE", "MMM_MFR_NAME" };
            if (DtInventory != null && DtInventory.Rows.Count > 0)
            {
                if (!String.IsNullOrEmpty(searchKey))
                {
                    var data = DtInventory.Select($"MMM_MFR_NAME like '%{searchKey}%'");
                    if (data != null && data.Length > 0)
                        dt = new DataView(data.CopyToDataTable()).ToTable(false, selectedColumns);
                }
                else
                {
                    dt = new DataView(DtInventory).ToTable(false, selectedColumns);
                }
            }

            if (dt.Rows.Count > 0)
            {
                if (!dt.Columns.Contains("Count"))
                    dt.Columns.Add("Count", typeof(int));
                DataTable dt2 = dt.AsEnumerable()
    .GroupBy(r => new { Name = r["MMM_MFR_NAME"], Code = r["MIH_ITEM_MFR_CODE"] })
    .Select(g =>
    {
        var row = dt.NewRow();

        row["MIH_ITEM_MFR_CODE"] = g.Key.Code;
        row["MMM_MFR_NAME"] = g.Key.Name;
        row["Count"] = g.Count();

        return row;

    }).CopyToDataTable();
                dt = dt2;
            }

            if (!dt.Columns.Contains("Margine"))
            {
                dt.Columns.Add("Margine", typeof(int));
            }
            foreach (DataRow dr in dt.Rows)
            {
                var data = dtMargine.Select($"BrandCode='{dr["MIH_ITEM_MFR_CODE"]}'");
                if (data.Length > 0)
                    dr["Margine"] = data[0]["MarginePercentage"];
                else
                    dr["Margine"] = defaultMargine;
            }

            return dt;
        }

        public static DataTable GetProducts(int storeId, double defaultMargine, int brandId = -1, string searchKey = "")
        {
            string strSql = $"select MargineType, BarCode, MarginePercentage from Margine where StoreId={storeId} order by MargineType desc";
            DataTable dtMargine = DataService.GetDataTable(strSql);

            DataTable dt = new DataTable();
            string strSearch = (brandId > 0 ? $"MIH_ITEM_MFR_CODE = '{brandId}'" : "");
            if (!String.IsNullOrEmpty(searchKey))
                strSearch += (String.IsNullOrEmpty(strSearch) ? "" : " and ") + $"MIH_ITEM_NAME like '%{searchKey}%'";

            if (!String.IsNullOrEmpty(strSearch))
            {
                var data = DtInventory.Select(strSearch);
                if (data != null)
                    dt = data.CopyToDataTable();
            }
            else
            {
                dt = DtInventory;
            }

            if (dt != null && dt.Rows.Count > 0)
            {
                if (!dt.Columns.Contains("Margine"))
                {
                    dt.Columns.Add("Margine", typeof(int));
                }
                foreach (DataRow dr in dt.Rows)
                {
                    var data = dtMargine.Select($"BarCode='{dr["MIH_ITEM_MFR_CODE"]}' or BarCode = '{dr["mid_eancode"]}'");
                    if (data.Length > 0)
                        dr["Margine"] = data[0]["MarginePercentage"];
                    else
                        dr["Margine"] = defaultMargine;
                }
            }
            return dt;
        }

        public static string ReadBlobAsString(string fileName, string folder)
        {

            string strConString = ConfigurationManager.AppSettings.Get("blobConnectionstring");
            string strContainer = ConfigurationManager.AppSettings.Get("blobContainer");

            if (String.IsNullOrEmpty(strConString) || String.IsNullOrEmpty(strContainer))
                return "";

            BlobServiceClient blobServiceClient = new BlobServiceClient(strConString);
            BlobContainerClient containerClient = blobServiceClient.GetBlobContainerClient(strContainer);
            BlobClient blobClient = containerClient.GetBlobClient($"/{folder}/{fileName}");
            if (!blobClient.Exists())
            {
                return null;
            }
            BlobDownloadInfo download = blobClient.DownloadAsync().Result;

            using (MemoryStream stream = new MemoryStream())
            {
                download.Content.CopyToAsync(stream);
                stream.Seek(0, SeekOrigin.Begin);

                string content = new StreamReader(stream).ReadToEnd();
                return content;
            }
        }
        public static async Task<string> CreateBlob(String content, string fileName, string folder)
        {

            string strConString = ConfigurationManager.AppSettings.Get("blobConnectionstring");
            string strContainer = ConfigurationManager.AppSettings.Get("blobContainer");

            if (String.IsNullOrEmpty(strConString) || String.IsNullOrEmpty(strContainer))
                return "";

            BlobServiceClient blobServiceClient = new BlobServiceClient(strConString);
            BlobContainerClient containerClient = blobServiceClient.GetBlobContainerClient(strContainer);
            BlobClient blobClient = containerClient.GetBlobClient($"/{folder}/{fileName}");

            var byteArray = System.Text.Encoding.UTF8.GetBytes(content);

            using (var stream = new MemoryStream(byteArray))
            {
                try
                {
                    var blobInfo = blobClient.Upload(stream);
                    //var blobInfo = await blobClient.UploadAsync(stream, overwrite: true);
                    string strBlobUrl = blobClient.Uri.AbsoluteUri;
                    if (strBlobUrl.EndsWith(fileName))
                        return strBlobUrl;
                }
                catch (RequestFailedException ReqFailedEx)
                {
                    throw new Exception(blobClient.Uri.AbsoluteUri);
                }
                catch (Exception ex)
                {
                    throw ex;
                }

            }
            return blobClient.Uri.AbsoluteUri;
        }

        public static async Task<string> CreateBlob(Stream fileStream, string fileName, string folder = "MerchantLogo")
        {
            if (String.IsNullOrEmpty(folder))
                folder = "MerchantLogo";
            string strConString = ConfigurationManager.AppSettings.Get("blobConnectionstring");
            string strContainer = ConfigurationManager.AppSettings.Get("blobContainer");

            if (String.IsNullOrEmpty(strConString) || String.IsNullOrEmpty(strContainer))
                return "";

            BlobServiceClient blobServiceClient = new BlobServiceClient(strConString);
            BlobContainerClient containerClient = blobServiceClient.GetBlobContainerClient(strContainer);
            BlobClient blobClient = containerClient.GetBlobClient($"/{folder}/{fileName}");

            var blobInfo = blobClient.UploadAsync(fileStream, true).Result;
            fileStream.Close();
            string strBlobUrl = blobClient.Uri.AbsoluteUri;
            if (strBlobUrl.EndsWith(fileName))
                return strBlobUrl;

            return blobInfo.ToString();
        }

        public static async Task<bool> DeleteBlob(string url)
        {
            if (String.IsNullOrEmpty(url))
                return false;

            string strConString = ConfigurationManager.AppSettings.Get("blobConnectionstring");
            string strContainer = ConfigurationManager.AppSettings.Get("blobContainer");

            if (String.IsNullOrEmpty(strConString) || String.IsNullOrEmpty(strContainer))
                return false;

            if (url.StartsWith("http") && url.Contains("/" + strContainer + "/"))
                url = url.Substring(url.IndexOf("/" + strContainer + "/") + ("/" + strContainer + "/").Length);

            BlobServiceClient blobServiceClient = new BlobServiceClient(strConString);
            BlobContainerClient containerClient = blobServiceClient.GetBlobContainerClient(strContainer);

            try
            {
                if (containerClient != null)
                {
                    await containerClient.DeleteBlobAsync(url);
                    return true;
                }
            }
            catch
            {
                return false;
            }

            return false;
        }


        public static object GetBrands(int storeid, int typeid)
        {
            if (typeid == 1)
                return Core.Services.APIService.GetMasterBrands(storeid);
            //return Core.Services.APIService.GetHomeBrands(storeid);
            return null;
        }
        public static object GetCategories(int storeid, int typeid)
        {
            if (typeid == 2)
                return Core.Services.APIService.Categories(storeid);
            return null;
        }

        public static object GetFilterItems(int storeid, int typeid)
        {
            if (typeid == 2)
                return Core.Services.APIService.Categories(storeid);
            else
                return Core.Services.APIService.GetHomeBrands(storeid);
        }

        public static CategoryProducts GetFilteredProducts(int storeid, int typeid, int brandid = -1, int catid = -1, int catlevelid = -1)
        {
            if (typeid == 2)
                return Core.Services.APIService.GetProductsByCategoy(catid, storeid, 1, catlevelid);
            else
                return null; //Core.Services.APIService.GetBrandProducts(brandid, storeid);

        }

        public static Core.BussinessModel.Inventory.Products GetProducts(int storeid, int typeid, int brandid = -1, int catid = -1, int catlevelid = -1, int pageid = 1)
        {
            if (typeid == 2)
                return Core.Services.APIService.GetBrandProducts(storeid, 0, catid, catlevelid, pageid: pageid);
            else
                return Core.Services.APIService.GetBrandProducts(storeid, brandid, 0, 0, pageid: pageid);

        }

        public static string MinutesToDiff(int minutes)
        {
            string str = "";
            if (minutes >= 60)
            {
                if (minutes / 60 >= 24)
                    str = String.Format("<small class=\"badge badge - danger\"><i class=\"far fa - clock\"></i> {0} day/s</small>", Convert.ToInt32(minutes / 60 / 24));
                else
                    str = String.Format("<small class=\"badge badge - info\"><i class=\"far fa - clock\"></i> {0} hr/s</small>", Convert.ToInt32(minutes / 60));
            }
            else
            {
                str = String.Format("<small class=\"badge badge - success\"><i class=\"far fa - clock\"></i> {0} min/s</small>", minutes);
            }

            return str;
        }
        public static async Task<bool> DeleteFileFromStorage(string url, string folder)
        {
            try
            {
                if (String.IsNullOrEmpty(url))
                    return false;

                string strConString = ConfigurationManager.AppSettings.Get("blobConnectionstring");
                string strContainer = ConfigurationManager.AppSettings.Get("blobContainer");

                if (String.IsNullOrEmpty(strConString) || String.IsNullOrEmpty(strContainer))
                    return false;
                Uri uri = new Uri(url);
                string filename = Path.GetFileName(uri.LocalPath);
                Console.WriteLine(filename);
                Console.WriteLine(url);
                Console.WriteLine(uri.LocalPath);

                BlobServiceClient blobServiceClient = new BlobServiceClient(strConString);
                BlobContainerClient blobContainerClient = blobServiceClient.GetBlobContainerClient(strContainer);

                //Azure.Response response = blobContainerClient.DeleteBlob($"{folder}/{filename}");
                //return (response.Status == 200) ? true : false;
                BlobClient blobClient = blobContainerClient.GetBlobClient($"/{folder}/{filename}");

                var result = blobClient.DeleteIfExistsAsync(DeleteSnapshotsOption.IncludeSnapshots).Result;
                return true;
            }
            catch
            {
                throw;
            }
        }


        private static Random random = new Random();

        public static string RandomString(int length, string[] strExcludes)
        {
            string strNew = "";
            int iteration = 0, maxIteration = 10000 * 10000;
            const string chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            do
            {
                strNew = new string(Enumerable.Repeat(chars, length)
                    .Select(s => s[random.Next(s.Length)]).ToArray());
                iteration++;
            } while (iteration < maxIteration && strExcludes.Contains(strNew));

            if (strExcludes.Contains(strNew))
                return "";

            return strNew;
        }

        public static string GenStoreDomain(int length, string[] strExcludes, string format = "{0}")
        {
            string strNew = "";
            int iteration = 0, maxIteration = 10000 * 10000;
            const string chars = "0123456789";//"ap2bc3rd4se5tf6uv7hjx8kymzn";
            do
            {
                //strNew = Guid.NewGuid().ToString().Split('-').LastOrDefault();
                //if (length > 4)
                //{
                strNew = new string(Enumerable.Repeat(chars, length)
                    .Select(s => s[random.Next(s.Length)]).ToArray());
                //}
                try { strNew = Convert.ToInt32(strNew).ToString("D3"); } catch { }
                iteration++;
            } while (iteration < maxIteration && !String.IsNullOrEmpty(strNew) && strExcludes.Contains(String.Format(format, strNew)));

            if (strExcludes.Contains(String.Format(format, strNew)))
                return "";
            if (format.Contains("{0}"))
                return String.Format(format, strNew);

            return strNew;
        }
        public static string RandomDomainKey(int length, string[] strExcludes, string format = "{0}")
        {
            string strNew = "";
            int iteration = 0, maxIteration = 10000 * 10000;
            const string chars = "ap2bc3rd4se5tf6uv7hjx8kymzn";
            do
            {
                strNew = Guid.NewGuid().ToString().Split('-').LastOrDefault();
                if (length > 4)
                {
                    strNew += new string(Enumerable.Repeat(chars, length)
                        .Select(s => s[random.Next(s.Length)]).ToArray());
                }

                iteration++;
            } while (iteration < maxIteration && !String.IsNullOrEmpty(strNew) && strExcludes.Contains(String.Format(format, strNew)));

            if (strExcludes.Contains(String.Format(format, strNew)))
                return "";
            if (format.Contains("{0}"))
                return String.Format(format, strNew);

            return strNew;
        }

        public static string FavIcon
        {
            get
            {
                string strfavIcon = ConfigurationSettings.AppSettings.Get("FavIcon");
                if (!String.IsNullOrEmpty(strfavIcon))
                    return strfavIcon;

                return "/content/images/logo/retaline_favicon.ico";
            }
        }

        public static string ShrinkText(string content, int maxSize, bool addDots = true)
        {
            if (content.Length > maxSize)
                return content.Substring(0, maxSize) + (addDots ? ".." : "");
            return content;
        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="page"></param>
        /// <param name="msg"></param>
        /// <param name="isSuccess"></param>
        /// <param name="classname">success, info, danger</param>
        public static void ShowToastifyMessage(Page page, string msg, string classname = "success")
        {
            string strToastifySCript = @"Toastify({
                      text: '" + msg.Replace("'", "") + @"',
                      duration: 5000,
                      stopOnFocus: true,
                      className: '" + classname + @"',
                    }).showToast();";
            //System.Diagnostics.StackFrame frame = new System.Diagnostics.StackFrame(1);
            //var method = frame.GetMethod();
            //var type = method.DeclaringType;

            Type cstype = page.GetType();//this.GetType();
            String csname1 = "AddTostifyScript";
            ClientScriptManager cs = page.ClientScript;

            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strToastifySCript} </");
            cstext1.Append("script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }
        /// <summary>
        /// 
        /// </summary>
        /// <param name="page"></param>
        /// <param name="msg"></param>
        /// <param name="isSuccess"></param>
        /// <param name="classname">success, info, danger</param>
        public static void ShowCustomAlert(Page page, string title, string msg, bool isSuccess = true, string OnCloseRedirectUrl = "")
        {
            string strAlertSCript = $"showModal('{title}', '{msg}', {(isSuccess ? "true" : "false")}, '{OnCloseRedirectUrl}');";

            strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";

            Type cstype = page.GetType();//this.GetType();
            String csname1 = "ShowCustomAlert";
            ClientScriptManager cs = page.ClientScript;

            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
            cstext1.Append("script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }

        public static bool IsValidEmail(string emailaddress)
        {
            try
            {
                System.Net.Mail.MailAddress m = new System.Net.Mail.MailAddress(emailaddress);

                return true;
            }
            catch (FormatException)
            {
                return false;
            }
        }

        public static string TenantTypeText(int tenantType)
        {
            string strType = "";
            switch (tenantType)
            {
                case 1:
                    strType = "Merchant";
                    break;
                case 2:
                    strType = "Affiliate";
                    break;
                case 3:
                    strType = "Whole sale";
                    break;
                case 4:
                    strType = "Whole sale with retail";
                    break;
            }
            return strType;
        }

        public static string DistrictLabel
        {
            get
            {
                string countryCode = ConfigurationManager.AppSettings["CountryCode"];
                switch (countryCode)
                {
                    case "IN":
                        return "District";
                    case "UK":
                        return "County";
                    case "AE":
                        return "Area";
                    default:
                        return "";

                }

            }
        }

        public static string StateLabel
        {
            get
            {
                string countryCode = ConfigurationManager.AppSettings["CountryCode"];
                switch (countryCode)
                {
                    case "IN":
                        return "State";
                    case "UK":
                        return "Province";
                    case "AE":
                        return "Emirate";
                    default:
                        return "";

                }

            }
        }


        public static string GetFullUrl(string relativeFilePath)
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

        public static string StripHTML(string input)
        {
            return Regex.Replace(input, "<.*?>", String.Empty);
        }

        public static async Task<bool> DoesBlobExist(string fileName, string folder)
        {
            if (String.IsNullOrEmpty(folder))
                folder = "MerchantLogo";
            string strConString = ConfigurationManager.AppSettings.Get("blobConnectionstring");
            string strContainer = ConfigurationManager.AppSettings.Get("blobContainer");

            if (String.IsNullOrEmpty(strConString) || String.IsNullOrEmpty(strContainer))
                return false;

            BlobServiceClient blobServiceClient = new BlobServiceClient(strConString);
            BlobContainerClient containerClient = blobServiceClient.GetBlobContainerClient(strContainer);
            BlobClient blobClient = containerClient.GetBlobClient($"/{folder}/{fileName}");

            bool exists = blobClient.ExistsAsync().Result;

            return exists;

        }


    }

}