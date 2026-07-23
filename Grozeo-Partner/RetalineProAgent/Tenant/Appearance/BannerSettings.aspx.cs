using MySql.Data.MySqlClient.Memcached;
using MySqlX.XDevAPI;
using Newtonsoft.Json;
using Newtonsoft.Json.Linq;
using RetalineProAgent.Core.BussinessModel.Store;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.IO;
using System.Linq;
using System.Threading.Tasks;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Appearance
{

    public class Location
    {
        public string type { get; set; }

        public int typeId { get; set; }
        public string image { get; set; }
        public int width { get; set; }
        public int height { get; set; }
    }

    public partial class BannerSettings: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            DateTime fromDate = DateTime.Today.Date;
            String fromToday = fromDate.ToString("yyyy-MM-dd");
            txtDateFrom.Attributes["min"] = fromToday;

            DateTime lastDate = DateTime.Today.Date;
            String lastDay = lastDate.ToString("yyyy-MM-dd");
            txtDateTo.Attributes["min"] = lastDay;
            

            if (rdAddsRangYes.Checked == true)
            {
                reqDateFrom.Visible = true;
                reqDateTo.Visible = true;
            }
            else
            {
                reqDateFrom.Visible = false;
                reqDateTo.Visible = false;
            }
            txtTheme.Text = txtloadtheme.InnerText = this.CurrentUser.Theme;

            if (!IsPostBack)
            {

                LoadStoreInfo();
            }
        }

        private void LoadStoreInfo()
        {
            int adv_id = 0;
            if (!String.IsNullOrEmpty(Request.QueryString["advId"]))
                try { adv_id = Convert.ToInt32(Request.QueryString["advId"]); } catch { adv_id = 0; }

            string strlocation = selBannerLocation.SelectedItem.Text;
            // (CASE WHEN adzone_name='Home left banner' THEN 'Inner Left Banner' ELSE adzone_name END)
            if (strlocation == "Inner Left Banner")
                strlocation = "Home Left Banner";
            List<KeyValuePair<String, Object>> sqlparams = new List<KeyValuePair<string, object>>();
            sqlparams.Add(new KeyValuePair<string, object>("advertisementId", adv_id));
            if (adv_id > 0)
            {
                DataTable dt = DataServiceMySql.GetDataTable($"SELECT adv_id,adv_title,adv_status, adv_usageType,IF(adv_applicable_category = 1,'Select Business Category','Select Retail Category') AS selCategory,adv_imageurl,adzone_id AS ad_id,(SELECT adzone_name FROM app_adzones WHERE adzone_id=adv.adzone_id) AS adzoneName,DATE_FORMAT(adv_startdate, '%Y-%m-%d') AS adv_startdate, DATE_FORMAT(adv_enddate, '%Y-%m-%d') AS adv_enddate, " +
                    $"CASE WHEN adv_offerType = 'Product' THEN (SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = adv_offerValueId) WHEN adv_offerType = 'Category' THEN(SELECT sub_category FROM mypha_productsubcategory WHERE sub_category_id = adv_offerValueId) " +
                    $"ELSE(SELECT brand_name FROM mypha_productbrands WHERE brand_id = adv_offerValueId) END AS adv_offerValue_name, " +
                    $"CASE WHEN adv_offer = 'product' THEN(SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = adv_offerValueId) " +
                    $"WHEN adv_offer = 'category' THEN(SELECT sub_category FROM mypha_productsubcategory WHERE sub_category_id = adv_offerValueId) " +
                    $"ELSE(SELECT brand_name FROM mypha_productbrands WHERE brand_id = adv_offerValueId) END AS adv_offerValue_namess, adv_applicable_for, " +
                    $"adv_applicable_category, adv_applicable_category_value, adv_offer, adv_offerType, adv_offerValueId, adv_offerpercent FROM app_advertisements adv  WHERE adv_id = @advertisementId", Service.UserService.GetAPIConnectionString(), sqlparams);
                if (dt == null || dt.Rows.Count <= 0)
                {
                    Common.ShowCustomAlert(this.Page, "Loading failed", "Invalid selection or the record is not existing", false, "/tenant/appearance/banner");
                    return;
                }

                DataRow da = dt.Rows[0];
                txtDateFrom.Text = da["adv_startdate"].ToString();
                txtDateTo.Text = da["adv_enddate"].ToString();
                selBannerLocation.SelectedItem.Text = da["adzoneName"].ToString();
                selCatType.SelectedItem.Text = da["selCategory"].ToString();
                selBannerBusinessType.SelectedItem.Text = da["adv_offerValue_namess"].ToString();
                selBannerRetailType.SelectedItem.Text = da["adv_offerValue_namess"].ToString();
                //selOfferOn.Text = da["adv_offer"].ToString();
                //selCategory.Text = da["adv_offer"].ToString();
                //selProduct.Text = da["adv_offer"].ToString();
                //selBrand.Text = da["adv_offer"].ToString();
            }



        }
        
        protected void Page_PreRender(object sender, EventArgs e)
        {
            selBannerBusinessType.Visible = selCatType.Text == "1";
            selBannerRetailType.Visible = selCatType.Text == "2";

            plcOfferExpand.Visible = rdOffer.Checked;
            plcBrandExpand.Visible = rdBrand.Checked || (rdOffer.Checked && selOfferOn.Text == "Brand");
            plcCategoryExpand.Visible = rdCategory.Checked || (rdOffer.Checked && selOfferOn.Text == "Category");
            plcProductExpand.Visible = rdProducts.Checked || (rdOffer.Checked && selOfferOn.Text == "SKU");
            plcSubcategory.Visible = rdSubCategory.Checked || (rdOffer.Checked && selOfferOn.Text == "Sub Category");
            plcDepartment.Visible = rdDepartment.Checked || (rdOffer.Checked && selOfferOn.Text == "Department");
            reqDateFrom.Enabled = rdAddsRangYes.Checked;
            reqDateTo.Enabled = rdAddsRangYes.Checked;
        }

        protected void SDSHomeBanners_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
        }

        protected async void btnSaveBanner_Click(object sender, EventArgs e)
        {
            if(rdAddsRangYes.Checked == true)
            {
                if (String.IsNullOrEmpty(txtDateFrom.Text))
                {
                    Common.ShowCustomAlert(this.Page, "Validation failure", "Please select the date range - From", false);
                    return;
                }
                if (String.IsNullOrEmpty(txtDateTo.Text))
                {
                    Common.ShowCustomAlert(this.Page, "Validation failure", "Please select the date range - To", false);
                    return;
                }

            }

            if (!BannerImgUploade.HasFile)
            {
                Common.ShowCustomAlert(this.Page, "Missing image file", "Please select the image file in correct format and size to upload", false);
                //lblMessage.Text = "Failure!! No file selected.";
                return;
            }

            // Limit file size upto 150 - as per task # 275
            int maxFileLength = 153600; // 150KB = 1024 * 150
            if (BannerImgUploade.PostedFile.ContentLength > maxFileLength)
            {
                Common.ShowCustomAlert(this.Page, "Large file", "Uploaded file size exceeded the maximum limit of 150 kb. Please upload the file with size less than 150kb.", false);
                return;
            }

                string strImageName = Guid.NewGuid().ToString();
            string banner1Url = "", banner2Url = "";

            string strExtention = System.IO.Path.GetExtension(BannerImgUploade.PostedFile.FileName);
            //banner1Url = await Common.CreateBlob(BannerImgUploade.PostedFile.InputStream, strImageName + $"_logo{strExtention}", "MerchantBanner");

            banner1Url = FileService.UploadMerchantImage(BannerImgUploade.PostedFile.InputStream, strImageName + $"_logo{strExtention}", "MerchantBanner");
            if (String.IsNullOrEmpty(banner1Url))
            {
                Common.ShowCustomAlert(this.Page, "Failure", "Error on uploading banner", false);
                return;
            }

            string offerType = "", offerTitle="Offer"; int offervalid = 0;
            if (rdOffer.Checked) {
                offerType = "Offer";
                switch (selOfferOn.Text)
                {
                    case "Product":
                        offervalid = Convert.ToInt32(selProduct.Text);
                        offerTitle = selProduct.SelectedItem.Text;
                        break;
                    case "Category":
                        offervalid = Convert.ToInt32(selCategory.Text);
                        offerTitle = selCategory.SelectedItem.Text;
                        break;
                    case "Brand":
                        offervalid = Convert.ToInt32(selBrand.Text);
                        offerTitle = selBrand.SelectedItem.Text;
                        break;
                    case "Sub Category":
                        offervalid = Convert.ToInt32(selSubcategory.Text);
                        offerTitle = selSubcategory.SelectedItem.Text;
                        break;
                    case "Department":
                        offervalid = Convert.ToInt32(selDepartment.Text);
                        offerTitle = selDepartment.SelectedItem.Text;
                        break;
                }
            }
            else if (rdCategory.Checked) {
                offerType = "Category";
                offervalid = Convert.ToInt32(selCategory.Text);
                offerTitle = selCategory.SelectedItem.Text;
            }
            else if (rdBrand.Checked) {
                offerType = "Brand";
                offervalid = Convert.ToInt32(selBrand.Text);
                offerTitle = selBrand.SelectedItem.Text;
            }
            else if (rdProducts.Checked) {
                offerType = "Product";
                offervalid = Convert.ToInt32(selProduct.Text);
                offerTitle = selProduct.SelectedItem.Text;
            }
            else if (rdSubCategory.Checked)
            {
                offerType = "Sub Category";
                offervalid = Convert.ToInt32(selSubcategory.Text);
                offerTitle = selSubcategory.SelectedItem.Text;
            }
            else if (rdDepartment.Checked)
            {
                offerType = "Department";
                offervalid = Convert.ToInt32(selDepartment.Text);
                offerTitle = selDepartment.SelectedItem.Text;
            }
            else
            {
                offerType = "";
                offervalid = 0;
                offerTitle = "";
            }
            double offerPercentage = 0;
            //if(rdOffer.Checked)
                //try { offerPercentage = Convert.ToDouble(txtPercentage.Text); } catch { offerPercentage = 0; }
            string strApplicableVal = (selCatType.Text == "1" ? selBannerBusinessType.Text : selBannerRetailType.Text);
            string strApplicableCat = (strApplicableVal == "0" ? "0" : selCatType.Text);

            int advid = Convert.ToInt32(Request.QueryString["advId"]);
            string startingDate = "0000-00-00";
            string endingDate = "0000-00-00";
             
            if (rdAddsRangYes.Checked == true)
            {
                var startDate = Convert.ToDateTime(txtDateFrom.Text);
                startingDate = startDate.ToString("yyyy-MM-dd");
                var endDate = Convert.ToDateTime(txtDateTo.Text);
                endingDate = endDate.ToString("yyyy-MM-dd");
            }
            else
            {
                DateTime todayDate = DateTime.Now;
                startingDate = todayDate.ToString("yyyy-MM-dd");
                DateTime lastDate = todayDate.AddYears(1);
                endingDate = lastDate.ToString("yyyy-MM-dd");
            }

            List<KeyValuePair<String, Object>> sqlparams = new List<KeyValuePair<string, object>>();
            sqlparams.Add(new KeyValuePair<string, object>("storegroupId", this.CurrentUser.APIStoreId));
            sqlparams.Add(new KeyValuePair<string, object>("offer", offerType));
            sqlparams.Add(new KeyValuePair<string, object>("advertisementId", advid));
            sqlparams.Add(new KeyValuePair<string, object>("offerpercent", offerPercentage));
            sqlparams.Add(new KeyValuePair<string, object>("offertype", (rdOffer.Checked && !String.IsNullOrEmpty(selOfferOn.Text) ? selOfferOn.Text : "")));
            sqlparams.Add(new KeyValuePair<string, object>("offerval", offervalid));
            sqlparams.Add(new KeyValuePair<string, object>("applicablecat", strApplicableCat));
            sqlparams.Add(new KeyValuePair<string, object>("applicablecatval", strApplicableVal));
            sqlparams.Add(new KeyValuePair<string, object>("offerTitle", offerTitle));
            sqlparams.Add(new KeyValuePair<string, object>("bannerUrl", banner1Url));
            sqlparams.Add(new KeyValuePair<string, object>("startDate", startingDate));
            sqlparams.Add(new KeyValuePair<string, object>("endDate", endingDate));

            string themeName = ViewState["ThemeName"]?.ToString();
            int themeId = ViewState["ThemeId"] != null ? (int)ViewState["ThemeId"] : 0;

            sqlparams.Add(new KeyValuePair<string, object>("themename", this.CurrentUser.Theme));

            string strlocation = selBannerLocation.SelectedItem.Text;
            // (CASE WHEN adzone_name='Home left banner' THEN 'Inner Left Banner' ELSE adzone_name END)
            if (strlocation == "Inner Left Banner")
                strlocation = "Home Left Banner";

            sqlparams.Add(new KeyValuePair<string, object>("location", strlocation)); // selBannerLocation.SelectedItem.Text));

            //if(advid == 0)
            //{
                string sql = $"INSERT INTO app_advertisements( adv_title, adv_usageType, adv_imageurl, adv_theme, adzone_id, adv_offer, adv_offerpercent, adv_offerValueId, adv_offerType, adv_startdate, adv_enddate, adv_status, storegroup_id, adv_applicable_category, adv_applicable_category_value, adv_applicable_for) " +
                $"SELECT @offerTitle, 1, @bannerUrl, (SELECT id FROM theme WHERE `name` =@themename), adzone_id, @offer, @offerpercent, @offerval, @offertype, @startDate, @endDate, 1, @storegroupId, @applicablecat, @applicablecatval, 2 FROM app_adzones WHERE adzone_screen " +
                $"LIKE 'Home' AND adzone_type LIKE 'advertisement' AND adzone_name = @location LIMIT 1; ";
                DataServiceMySql.ExecuteSql(sql, UserService.GetAPIConnectionString(), sqlparams);

                //Common.ShowToastifyMessage(this.Page, "Banner uploaded successfully!", );
                Common.ShowCustomAlert(this.Page, "Success", "Banner uploaded successfully!", true, "/tenant/appearance/banner");


                //string insertQry = $"INSERT INTO app_advertisements(adv_title, adv_usageType, adv_imageurl, adzone_id, adv_offer, adv_offerpercent, adv_offerValueId, adv_offerType, adv_startdate, adv_enddate, adv_status, storegroup_id, adv_applicable_category, adv_applicable_category_value, adv_applicable_for) " +
                //                $"VALUES(@offerTitle, 1, @bannerUrl, @location, @offer, @offerpercent, @offerval, @offertype, @startDate, @endDate, 1, @storegroupId, @applicablecat, @applicablecatval, 2)";
                //DataServiceMySql.ExecuteSql(insertQry, Service.UserService.GetAPIConnectionString(), sqlparams);
            //    //Common.ShowCustomAlert(this.Page, "Success", "Banner uploaded successfully!", true, "/appearance/banner");
            //}
            //else
            //{
            //    string updateQry = "UPDATE app_advertisements SET adv_usageType=1,adv_title=@offerTitle,adv_imageurl=@bannerUrl,adzone_id=adzone_id,adv_offer=@offer,adv_offerpercent=@offerpercent,adv_offerValueId=@offerval,adv_offerType=@offertype,adv_startdate=@startDate,adv_enddate=@endDate,adv_status=1,storegroup_id=@storegroupId,adv_applicable_category=@applicablecat,adv_applicable_category_value=@applicablecatval,adv_applicable_for=2 WHERE adv_id=@advertisementId";
            //    DataServiceMySql.ExecuteSql(updateQry, Service.UserService.GetAPIConnectionString(), sqlparams);

            //    Common.ShowCustomAlert(this.Page, "Data updated!", "Data updated successfully!", true, "/appearance/banner");
            //}
            
        }
        /// <summary>
        /// Retrieves the theme location settings from a theme.json file stored in S3.
        /// </summary>
        /// <param name="themeLocation">The S3 base path for the theme files.</param>
        /// <param name="storetheme">The specific store theme folder name.</param>
        /// <returns>A list of Location objects from the bannerSettings section.</returns>
        public  List<Location> GetThemeLocations(string themeLocation, string storetheme)
        {
            try
            {
                var content = FileService.ReadAllS3Files(themeLocation, storetheme, "")
                .FirstOrDefault(kvp => kvp.Key.EndsWith("theme.json", StringComparison.OrdinalIgnoreCase)).Value;
                if (string.IsNullOrWhiteSpace(content))
                {
                    throw new FileNotFoundException($"theme.json file not found in S3 location: {themeLocation}/{storetheme}");
                }
                var obj = JObject.Parse(content);
                var locationsToken = obj["bannerSettings"]?["locations"];
                if (locationsToken == null)
                {
                    throw new JsonException("Missing 'bannerSettings.locations' section in theme.json");
                }
                return locationsToken.ToObject<List<Location>>() ?? new List<Location>();
            }
            catch
            {
                return new List<Location>();
            }
            
        }
        /// <summary>
        /// Generates a JSON object representing banner settings (image URLs, dimensions, and names)
        /// for each banner location type, based on the current user's theme.json in S3.
        /// </summary>
        /// <returns>A JSON string containing banner data for use in JavaScript.</returns>
        public string GetBannerJsObject()
        {
            try
            {
                // Get the current user's theme
                string theme = this.CurrentUser.Theme;
                // Read files from S3 and find the one ending with "theme.json"
                var content = FileService.ReadAllS3Files("themes", theme, "")
                    .FirstOrDefault(kvp => kvp.Key.EndsWith("theme.json", StringComparison.OrdinalIgnoreCase)).Value;

                if (string.IsNullOrWhiteSpace(content))
                    throw new Exception($"theme.json not found for theme: {theme}");

                var obj = JObject.Parse(content);
                // Extract bannerSettings section
                var settings = obj["bannerSettings"];
                if (settings == null)
                    throw new Exception("Missing 'bannerSettings' section in theme.json");
                // Deserialize the 'locations' array into a list of Location objects
                var locations = settings?["locations"]?.ToObject<List<Location>>() ?? new List<Location>();
                string s3BaseUrl = ConfigurationManager.AppSettings.Get("S3Location") + "themes" + "/" + theme + "/";
                // Determine the default image URL
                string defaultImageName = settings?["defaultImage"]?.ToString();
                string defaultImageUrl = !string.IsNullOrEmpty(defaultImageName) ? s3BaseUrl + defaultImageName : "";
                // Build dictionary for each location, keyed by typeId
                var dict = locations.ToDictionary(
                    loc => loc.typeId.ToString(),
                    loc => new
                    {
                        url = s3BaseUrl + loc.image,
                        width = loc.width,
                        height = loc.height,
                        name = loc.type
                    });

                dict["default"] = new
                {
                    url = defaultImageUrl,
                    width = 0,
                    height = 0,
                    name = "Default Banner"
                };

                return JsonConvert.SerializeObject(dict);
            }
            catch(Exception ex)
            {
                return "{}";
            }

        }



        protected void ODSThemeJson_Selecting(object sender, ObjectDataSourceSelectingEventArgs e)
        {
            e.InputParameters["storetheme"] = this.CurrentUser.Theme;

        }
    }
}