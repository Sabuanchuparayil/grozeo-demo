using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class Banner: Base.BasePartnerPage
    {
        private string CurViewType
        {
            get
            {
                return (string)ViewState["CURVIEWTYPE"];
            }
            set
            {
                ViewState["CURVIEWTYPE"] = value;
            }
        }

        protected void Page_Load(object sender, EventArgs e)
        {

        }
        protected void Page_PreRender(object sender, EventArgs e)
        {
            plcBannerList.Visible = (String.IsNullOrEmpty(CurViewType) || CurViewType == "1");
            plcBannerSettings.Visible = (CurViewType == "2");
        }
        protected void SDSHomeBanners_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
        }
        protected void selBanner1BusinessType_DataBound(object sender, EventArgs e)
        {
            if (selBanner1BusinessType.Items.Count > 1)
            {
                pnlBanner1FileUpload.CssClass = (selBanner1RetailType.Items.Count > 2 ? "col-md-3" : "col-md-6");
                pnlUploadBanner1BType.Visible = true;
                pnlUploadBanner1RType.Visible = selBanner1RetailType.Items.Count > 2;
            }
            else if (selBanner1RetailType.Items.Count > 1)
            {
                pnlBanner1FileUpload.CssClass = "col-md-6";
                pnlUploadBanner1BType.Visible = false;
                pnlUploadBanner1RType.Visible = true;
            }
            else
            {
                pnlBanner1FileUpload.CssClass = "col-md-9";
                pnlUploadBanner1BType.Visible = false;
                pnlUploadBanner1RType.Visible = false;
            }

        }

        protected async void btnUploadBanner_Click(object sender, EventArgs e)
        {
            // Service.User user = UserService.GetCustomerByUsername(Page.User.Identity.Name);
            string strImageName = Guid.NewGuid().ToString();
            string banner1Url = "", banner2Url = "";

            if (!FileUpload1.HasFile)
            {
                lblMessage.Text = "Failure!! No file selected.";
                return;
            }
            string strExtention = System.IO.Path.GetExtension(FileUpload1.PostedFile.FileName);
            banner1Url = await Common.CreateBlob(FileUpload1.PostedFile.InputStream, strImageName + $"_logo{strExtention}");

            //if (FileUpload2.HasFile)
            //{
            //    string strExtention = System.IO.Path.GetExtension(FileUpload2.PostedFile.FileName);
            //    banner2Url = Common.CreateBlob(FileUpload2.PostedFile.InputStream, strImageName + $"_logo_white{strExtention}").Result;
            //}

            if (!String.IsNullOrEmpty(banner1Url))
            {
                string strAdditionalFields = "", strAdditionalVals = "";
                if (selBanner1RetailType.SelectedIndex > 0)
                {
                    strAdditionalFields = ", adv_applicable_category, adv_applicable_category_value";
                    strAdditionalVals = $", 2, {selBanner1RetailType.Text}";
                }
                else if (selBanner1BusinessType.SelectedIndex > 0)
                {
                    strAdditionalFields = ", adv_applicable_category, adv_applicable_category_value";
                    strAdditionalVals = $", 1, {selBanner1BusinessType.Text}";
                }

                string sql = $"INSERT INTO app_advertisements( adv_title, adv_imageurl, adzone_id, adv_offer, adv_offerpercent, adv_offerValueId, adv_startdate, adv_enddate, adv_status, storegroup_id {strAdditionalFields}) " +
                    $"SELECT 'Offer', '{banner1Url}', adzone_id, 'Offer', 0, 1, NOW(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 1, {this.CurrentUser.APIStoreId} {strAdditionalVals} FROM app_adzones WHERE adzone_screen " +
                    $"LIKE 'Home' AND adzone_type LIKE 'advertisement' AND adzone_name = 'Home Top Banner' LIMIT 1; ";
                DataServiceMySql.ExecuteSql(sql, UserService.GetAPIConnectionString());
            }
            //if (!String.IsNullOrEmpty(banner2Url))
            //{
            //    string sql = $"INSERT INTO app_advertisements( adv_title, adv_imageurl, adzone_id, adv_offer, adv_offerpercent, adv_offerValueId, adv_startdate, adv_enddate, adv_status, storegroup_id) " +
            //        $"SELECT 'Offer', '{banner2Url}', adzone_id, 'Offer', 0, 1, NOW(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 2, {user.StoreGroupId} FROM app_adzones WHERE adzone_screen " +
            //        $"LIKE 'Home' AND adzone_type LIKE 'advertisement' AND adzone_name = 'Home Top Banner' LIMIT 1; ";
            //    DataService.ExecuteSql(sql);
            //}
            SDSOwnBanners.Select(DataSourceSelectArguments.Empty);
            rptOwnbanners.DataBind();
            Common.ShowToastifyMessage(this.Page, "Banner uploaded successfully!");
            CurViewType = "1";
        }

        protected void ownBannerOnly_CheckedChanged(object sender, EventArgs e)
        {
            List<KeyValuePair<String, Object>> parmeters = new List<KeyValuePair<string, object>>();
            parmeters.Add(new KeyValuePair<string, object>("StoreGroupId", this.CurrentUser.StoreGroupId));
            string sql = $"UPDATE AppTenant SET OwnBannerOnly = {(chkOwnBannerOnly.Checked ? "0" : "1")} WHERE Id=@StoreGroupId; ";

            DataService.ExecuteSql(sql, parmeters: parmeters);
            Service.UserService.CachedDefaultUser = null;
        }
        protected async void lbtnDelBanner_Click(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;
            if (lbtn != null && !String.IsNullOrEmpty(lbtn.Attributes["itemid"]))
            {
                List<KeyValuePair<String, Object>> parmeters = new List<KeyValuePair<string, object>>();
                parmeters.Add(new KeyValuePair<string, object>("StoreGroupId", this.CurrentUser.APIStoreId));
                parmeters.Add(new KeyValuePair<string, object>("advid", lbtn.Attributes["itemid"]));

                string sql = $"delete from app_advertisements where adv_id=@advid and storegroup_id=@StoreGroupId; ";
                int strresult = DataServiceMySql.ExecuteSql(sql, UserService.GetAPIConnectionString(), parmeters);

                SDSOwnBanners.Select(DataSourceSelectArguments.Empty);
                rptOwnbanners.DataBind();

                if (!string.IsNullOrEmpty(lbtn.Attributes["imgurl"]))
                    await Common.DeleteBlob(lbtn.Attributes["imgurl"]);
            }
        }

        protected void btnAddStore_Click(object sender, EventArgs e)
        {
            CurViewType = "2";
            //btnEdit.Visible = false;
            ltrAddTitle.Text = "Add new banner";
        }

        protected void rptOwnbanners_ItemDataBound(object sender, RepeaterItemEventArgs e)
        {
            plcNoBanner.Visible = (rptOwnbanners.Items.Count <= 0);
        }
    }
}