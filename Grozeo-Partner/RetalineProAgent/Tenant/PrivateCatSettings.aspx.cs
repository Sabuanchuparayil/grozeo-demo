using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Globalization;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Data.SqlClient;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.BussinessModel.Finance;
using RetalineProAgent.Core.Services.ActiveLog;

namespace RetalineProAgent
{
    public partial class PrivateCatSettings: Base.BasePartnerPage
    {
        public int SelectedItemId { get; private set; }

        protected async void Page_Load(object sender, EventArgs e)
        {
            // Keep the image selected on postbacks
            if (pnlUploadImage.Visible && fileUploadImgs.HasFile)
            {
                string strImgName = Guid.NewGuid().ToString();
                string strExtention = System.IO.Path.GetExtension(fileUploadImgs.PostedFile.FileName);
                string resultUrl = await Service.Common.CreateBlob(fileUploadImgs.PostedFile.InputStream, strImgName + $"{strExtention}", "vc");
                if (!string.IsNullOrEmpty(resultUrl))
                {
                    pnlCategoryImage.Visible = true;
                    pnlUploadImage.Visible = false; 
                    imgCatImage.ImageUrl = resultUrl;
                }
            }


            if (chkCat.Checked == true)
            {
                lblDept.Visible = true;
                selDept.Visible = true;
                lblCat.Visible = true;
                selCat.Visible = true; 
            }
            else
            {
                lblDept.Visible = false;
                selDept.Visible = false;
                lblCat.Visible = false;
                selCat.Visible = false;
            }
            if (!Page.IsPostBack)
            {
                LoadStoreInfo();
                
            }
        }
        public bool IsSelected(string stitId)
        {
            if (!String.IsNullOrEmpty(hidSelectedItems.Value))
            {
                try
                {
                    return hidSelectedItems.Value.Split(',').Contains(stitId);
                }
                catch { }
            }
            return false;
        }
        private void LoadStoreInfo()
        {
            int id = Convert.ToInt32(Request.QueryString["id"]);
            if (id > 0)
            {
                List<KeyValuePair<string, object>> sqlParams = new List<KeyValuePair<string, object>>();
                sqlParams.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));
                sqlParams.Add(new KeyValuePair<string, object>("vcid", id));
                string sqlSelect = $"SELECT vc_id,vc_name,vc_parentCategoryId,vc_categoryId,vc_isHome,vc_isInCategory,IF((vc_status=0),'Inactive','Active') AS vc_status, image_url FROM retaline_virtual_category WHERE vc_id = @vcid and store_group_id=@storegroupid";
                DataTable dataTable = DataServiceMySql.GetDataTable(sqlSelect, Service.UserService.GetAPIConnectionString(), sqlParams);
                if(dataTable == null || dataTable.Rows.Count < 1)
                {
                    // $('#modaldemo5').on('hidden.bs.modal', function (e) {window.location.href = "/PrivateCategory"; });
                    Type cstype = this.GetType();
                    String csname1 = "OnClosePopupScript";
                    ClientScriptManager cs = Page.ClientScript;

                    StringBuilder cstext1 = new StringBuilder();
                    cstext1.Append("<script type=text/javascript> $('#modaldemo5').on('hidden.bs.modal', function (e) {window.location.href = \"/Tenant/PrivateCategory\"; }); </");
                    cstext1.Append("script>");

                    cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());


                    ShowFailure("Invalid Category", "The category selected is invalid.");

                    return;

                }

                if (dataTable != null && dataTable.Rows.Count > 0)
                {
                    DataRow da = dataTable.Rows[0];
                    txtVirtCat.Text = da["vc_name"].ToString();
                    selDept.Text = da["vc_parentCategoryId"].ToString();
                    selCat.Text = da["vc_categoryId"].ToString();
                    string strImageUrl = da["image_url"].ToString();
                    if (!String.IsNullOrEmpty(strImageUrl))
                    {
                        pnlCategoryImage.Visible = true;
                        pnlUploadImage.Visible = false;
                        imgCatImage.ImageUrl = strImageUrl;

                    }
                    string strcheck = da["vc_isHome"].ToString();
                    if (strcheck == "1")
                    {
                        chkHome.Checked = true;
                        lblDept.Visible = true;
                        selDept.Visible = true;
                        lblCat.Visible = true;
                        selCat.Visible = true;
                    }
                    else
                    {
                        chkHome.Checked = false;
                        lblDept.Visible = false;
                        selDept.Visible = false;
                        lblCat.Visible = false;
                        selCat.Visible = false;
                    }
                    string strcheckbox = da["vc_isInCategory"].ToString();
                    if (strcheckbox == "1")
                    {
                        chkCat.Checked = true;
                        lblDept.Visible = true;
                        selDept.Visible = true;
                        lblCat.Visible = true;
                        selCat.Visible = true;
                    }
                    else
                    {
                        chkCat.Checked = false;
                        lblDept.Visible = false;
                        selDept.Visible = false;
                        lblCat.Visible = false;
                        selCat.Visible = false;
                    }
                }

                string sqlItems = $"SELECT stit_id FROM retaline_vc_items WHERE vc_id=@vcid";
                DataTable dtItems = DataServiceMySql.GetDataTable(sqlItems, Service.UserService.GetAPIConnectionString(), sqlParams);
                hidSelectedItems.Value = String.Join(",", dtItems.AsEnumerable().Select(item => string.Format("{0}", item["stit_id"])).ToArray());
                btnSubmit.Text = "Save";
            }
        }


        protected async void btnSubmit_Click(object sender, EventArgs e)
        {
            int virtualCatId = Convert.ToInt32(Request.QueryString["id"]);
            int vcid = virtualCatId;
            List<KeyValuePair<string, object>> sqlInsItemsParams = new List<KeyValuePair<string, object>>();
            sqlInsItemsParams.Add(new KeyValuePair<string, object>("vcid", vcid));
            sqlInsItemsParams.Add(new KeyValuePair<string, object>("selectedIds", hidSelectedItems.Value));
            sqlInsItemsParams.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));

            string sql = "DELETE ci FROM retaline_vc_items ci INNER JOIN retaline_virtual_category c on ci.vc_id = c.vc_id WHERE ci.vc_id = @vcid and c.store_group_id=@storegroupid and NOT FIND_IN_SET(stit_id, @selectedIds); ";
            sql += "INSERT ignore INTO retaline_vc_items(stit_type, vc_id, stit_id) SELECT 2, @vcid, stit_id FROM finascop_stock_itemmaster WHERE FIND_IN_SET(stit_id, @selectedIds);";
            DataServiceMySql.ExecuteSql(sql, UserService.GetAPIConnectionString(), sqlInsItemsParams);
            // Activitylog
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            string Source = strUrl;
            int storegroupid = this.CurrentUser.APIStoreId;
            string Users = this.CurrentUser.Email;
            string storegroup_id = (this.CurrentUser.APIStoreId).ToString();
            string vc_id = vcid.ToString();
            var items = new[]
                {
                    new { Key = "Storegroup Id", Value = storegroup_id },
                    new { Key = "Virtual Category id", Value = vc_id },
                  };
            string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
            var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);
            //ShowSuccess("Items Added Successfully!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Your items has been added successfully!</a></h5>");
            Common.ShowToastifyMessage(this.Page, "Deleted successfully");
            // Retrieve the category type from the Query String
            string categoryType = Request.QueryString["type"];
            string redirectUrl;

            if (categoryType == "featured")
                redirectUrl = "/Tenant/PrivateCategory.aspx?type=featured";
            else if (categoryType == "preferred")
                redirectUrl = "/Tenant/PrivateCategory.aspx?type=preferred";
            else
                redirectUrl = "/Tenant/PrivateCategory";

            // Redirect to the appropriate category page
            Response.Redirect(redirectUrl);

        }

        //public bool IsSelected(string stitId)
        //{
        //    if (!String.IsNullOrEmpty(hidSelectedItems.Value))
        //    {
        //        try
        //        {
        //            return hidSelectedItems.Value.Split(',').Contains(stitId);
        //        }
        //        catch { }
        //    }
        //    return false;
        //}

        protected void chkProductItem_CheckedChanged(object sender, EventArgs e)
        {
            CheckBox chkProductItem = (CheckBox)sender;
            if (chkProductItem == null)
                return;

            //string stit_id = chkProductItem.Attributes["itemid"];
            //string delSql = $"DELETE FROM retaline_vc_items where stit_id= @stit_id and vc_id in (SELECT vc_id FROM retaline_virtual_category WHERE store_group_id = @storegroupid)";
            //string insSql = $"INSERT IGNORE INTO retaline_vc_items (stit_type, vc_id, stit_id) VALUES(2, @vcid, @itemid) WHERE vc_id = @vcid";
            //List<KeyValuePair<String, Object>> prdParmeters = new List<KeyValuePair<string, object>>();
            //prdParmeters.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));
            ////prdParmeters.Add(new KeyValuePair<string, object>("vcid", stit_id));
            //prdParmeters.Add(new KeyValuePair<string, object>("itemid", stit_id));

            if (chkProductItem.Checked)
            {

            }
                
            


        }

        protected void lstProducts_DataBound(object sender, EventArgs e)
        {
            DataPager pager = (DataPager)lstProducts.FindControl("DataPager1");
            if (pager != null)
            {
                int startRowOnPage = (pager.StartRowIndex) + 1;
                int lastRowOnPage = startRowOnPage; //startRowOnPage + lstProducts.Items.Count - 1;
                
            }
        }

        protected void lstProducts_ItemDataBound(object sender, ListViewItemEventArgs e)
        {

            
        }

        protected void SDSProducts_Selected(object sender, SqlDataSourceStatusEventArgs e)
        {
            DataPager pager = (DataPager)lstProducts.FindControl("DataPager1");
            if (pager != null)
            {
                //pager.SetPageProperties(0, 25, true); //.DataBind();
                // paging controls
                int startRowOnPage = (e.AffectedRows > pager.StartRowIndex ? pager.StartRowIndex + 1 : e.AffectedRows);
                int lastRowOnPage = (e.AffectedRows > pager.MaximumRows ? pager.MaximumRows : e.AffectedRows); // startRowOnPage + lstProducts.Items.Count - 1;

            }
            int totalRows = e.AffectedRows;
            
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

        private void ShowFailure(string title, string content)
        {
            ltrErrorPopupTitle.Text = title;
            ltrErrorPopupText.Text = content;
            Type cstype = this.GetType();
            String csname1 = "PopupScript";
            ClientScriptManager cs = Page.ClientScript;


            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append("<script type=text/javascript> $('#modaldemo5').modal('show'); </");
            cstext1.Append("script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());

        }

        protected void SDS_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            if (e.Command.Parameters.Contains("storeId"))
                e.Command.Parameters["storeId"].Value = this.CurrentUser.APIStoreId;
        }

        protected void lbtnDeleteImg_Click(object sender, EventArgs e)
        {
            pnlCategoryImage.Visible = false;
            pnlUploadImage.Visible = true;
            imgCatImage.ImageUrl = "";

        }

        protected void SDSDepartment_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        }

        protected void SDSCat_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        }

        protected string GetBackLink()
        {
            string categoryType = Request.QueryString["type"];
            string backLinkUrl;

            if (categoryType == "featured")
                backLinkUrl = "/Tenant/PrivateCategory.aspx?type=featured";
            else if (categoryType == "preferred")
                backLinkUrl = "/Tenant/PrivateCategory.aspx?type=preferred";
            else
                backLinkUrl = "/Tenant/PrivateCategory"; // Default for other categories

            return backLinkUrl;
        }
    }
}



