using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using RetalineProAgent.Core.BussinessModel.Finance;
using RetalineProAgent.Core.BussinessModel.Store;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.IO;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class PrivateCategory: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                ConfigurePage();
                LoadStoreInfo();
                SetEditMode();
            }

            HandleCheckFeaturedAction();
            HandleImageUploadAsync().GetAwaiter().GetResult(); 
            ToggleDepartmentCategoryVisibility();
        }

        private void ConfigurePage()
        {
            string categoryType = (Request.QueryString["type"] ?? "private").ToLower();
            lblHeader.Text = GetHeaderText(categoryType);
            lblDescription.Text = GetDescriptionText(categoryType);
            Page.Title = lblHeader.Text;

            ConfigureGridAndForm(categoryType);
        }

        private string GetHeaderText(string categoryType)
        {
            switch (categoryType)
            {
                case "featured": return "Featured List";
                case "preferred": return "Preferred List";
                default: return "Private Category";
            }
        }

        private string GetDescriptionText(string categoryType)
        {
            switch (categoryType)
            {
                case "featured": return "Highlighted as a standout product for increased visibility.";
                case "preferred": return "Marked as a preferred choice for prioritized recognition.";
                default: return "Exclusive Product Segmentation.";
            }
        }

        private void ConfigureGridAndForm(string categoryType)
        {
            bool isFeaturedOrPreferred = categoryType == "featured" || categoryType == "preferred";
            gvPrivateCat.Columns[1].Visible = !isFeaturedOrPreferred;
            gvPrivateCat.Columns[2].Visible = !isFeaturedOrPreferred;
            listdropdown.Visible = !isFeaturedOrPreferred;
            homeCatList.Visible = !isFeaturedOrPreferred;
            createform.Visible = true;
            btnSubmit.Text = isFeaturedOrPreferred ? "Create" : "Create Category";
            string header = GetHeaderText(categoryType).Replace(" List", "");
            gvPrivateCat.Columns[0].HeaderText = header;
            lblPrivateCategory.Text = $"{header}: <span class='tx-danger'>*</span>";
            txtVirtCat.Attributes["placeholder"] = $"Enter {header.ToLower()} name";
            txtVirtCat.ToolTip = $"Enter {header.ToLower()} name";
            gvPrivateCat.DataBind();
        }

        private void SetEditMode()
        {
            bool isEditMode = !string.IsNullOrEmpty(Request.QueryString["id"]);
            chkHome.Enabled = !isEditMode;
            chkCat.Enabled = !isEditMode;
            selDept.Enabled = !isEditMode;
            selCat.Enabled = !isEditMode;
        }

        private void HandleCheckFeaturedAction()
        {
            if (Request.QueryString["action"] == "checkFeatured")
            {
                string featuredId = GetFeaturedId();
                Response.ContentType = "application/json";
                Response.Write($"{{\"vc_id\": \"{featuredId}\"}}");
                Response.End();
            }
        }

        private async Task HandleImageUploadAsync()
        {
            if (pnlUploadImage.Visible && fileUploadImgs.HasFile)
            {
                string filename = Guid.NewGuid() + System.IO.Path.GetExtension(fileUploadImgs.FileName);
                string resultUrl = await Service.Common.CreateBlob(fileUploadImgs.PostedFile.InputStream, filename, "vc");

                if (!string.IsNullOrEmpty(resultUrl))
                {
                    pnlCategoryImage.Visible = true;
                    pnlUploadImage.Visible = false;
                    imgCatImage.ImageUrl = resultUrl;
                    myImage.ImageUrl = resultUrl;
                }
            }
        }

        private void ToggleDepartmentCategoryVisibility()
        {
            bool isCategoryChecked = chkCat.Checked;
            lblDept.Visible = isCategoryChecked;
            selDept.Visible = isCategoryChecked;
            lblCat.Visible = isCategoryChecked;
            selCat.Visible = isCategoryChecked;
            department.Visible = isCategoryChecked;
            category.Visible = isCategoryChecked;
        }


        private string GetFeaturedId()
        {
            SqlDataSource sdsPrivateCat = Page.FindControl("SDSPrivateCat") as SqlDataSource;

            if (sdsPrivateCat != null)
            {
                var currentUser = HttpContext.Current.Items["CurrentUser"] as User;

                if (currentUser != null)
                {
                    sdsPrivateCat.SelectParameters["storegroup"].DefaultValue = currentUser.APIStoreId.ToString();
                    sdsPrivateCat.SelectParameters["categoryType"].DefaultValue = "featured";

                    DataView dv = (DataView)sdsPrivateCat.Select(DataSourceSelectArguments.Empty);

                    if (dv != null && dv.Count > 0)
                    {
                        return dv[0]["vc_id"].ToString();
                    }
                }
            }

            return "null"; 
        }

        protected void Page_PreRender(object sender, EventArgs e)
        {
            //if (gvProducts.HeaderRow != null)
            //    gvProducts.HeaderRow.TableSection = TableRowSection.TableHeader;
            //if (selBranches.Items.Count > 1)
            //{
            //    selBranches.Items.Insert(0, new ListItem("Select Branch", "-1"));
            //}

            //ltrBranchName.Visible = selBranches.Items.Count <= 2;

            //if (ltrBranchName.Visible && selBranches.Items.Count > 1)
            //    ltrBranchName.Text = selBranches.Items[1].Text;



        }

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvPrivateCat.PageIndex > 0)
                gvPrivateCat.PageIndex = gvPrivateCat.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvPrivateCat.PageIndex < gvPrivateCat.PageCount - 1)
                gvPrivateCat.PageIndex = gvPrivateCat.PageIndex + 1;
        }

        protected void gvPrivateCat_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvPrivateCat.PageIndex * gvPrivateCat.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvPrivateCat.Rows.Count - 1;
            //ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSPrivateCat.Select(DataSourceSelectArguments.Empty);
        }

        protected void DeleteItem_Click(object sender, EventArgs e)
        {
            LinkButton delStoreCat = (LinkButton)sender;
            if (delStoreCat == null)
                return;
            string categoryType = Request.QueryString["type"];
            List<KeyValuePair<string, object>> sqlParams = new List<KeyValuePair<string, object>>();
            sqlParams.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));
            sqlParams.Add(new KeyValuePair<string, object>("vcid", delStoreCat.Attributes["itemid"]));

            //string strSql2 = $"DELETE FROM retaline_vc_items WHERE vc_id=@vcid";
            string strSql2 = "DELETE ci FROM retaline_vc_items ci INNER JOIN retaline_virtual_category c on ci.vc_id = c.vc_id WHERE ci.vc_id = @vcid and c.store_group_id=@storegroupid";
            DataServiceMySql.ExecuteSql(strSql2, UserService.GetAPIConnectionString(), sqlParams);

            string strSql1 = $"DELETE FROM retaline_virtual_category WHERE vc_id= @vcid and store_group_id = @storegroupid";
            int result= DataServiceMySql.ExecuteSql(strSql1, UserService.GetAPIConnectionString(), sqlParams);
            // Activitylog
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            string Source = strUrl;
            int storegroupid = this.CurrentUser.APIStoreId;
            string Users = this.CurrentUser.Email;
            string storegroup_id = (this.CurrentUser.APIStoreId).ToString();
            string vcid = delStoreCat.Attributes["itemid"];           
            var items = new[]
                {
                    new { Key = "Storegroup Id", Value = storegroup_id },
                    new { Key = "Virtual Category", Value = vcid },                   
                  };
            string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
            var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);
            gvPrivateCat.DataBind();
            Common.ShowToastifyMessage(this.Page, "Deleted successfully");

            string redirectUrl = "/Tenant/PrivateCategory";
            if (!string.IsNullOrEmpty(categoryType))
            {
                redirectUrl += "?type=" + Server.UrlEncode(categoryType);
            }

            Response.Redirect(redirectUrl, false);
        }
        
        protected void SDSPrivateCat_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
            string categoryType = Request.QueryString["type"];
            e.Command.Parameters["@categoryType"].Value = string.IsNullOrEmpty(categoryType) ? DBNull.Value : (object)categoryType;
            List<KeyValuePair<string, object>> sqlParams = new List<KeyValuePair<string, object>>();
            sqlParams.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));
            string query = $"SELECT COUNT(*) AS cnt FROM retaline_virtual_category WHERE store_group_id = @storegroupid AND isFeatured = 1";
            DataTable dataTable = DataServiceMySql.GetDataTable(query, Service.UserService.GetAPIConnectionString(), sqlParams);
            if (dataTable != null && dataTable.Rows.Count > 0)
            {
                DataRow da = dataTable.Rows[0];
                string count = da["cnt"].ToString();
                if(Convert.ToInt32(count) > 0 && categoryType == "featured")
                createform.Visible = false;
            }
        }
        private void ShowSuccess(string title, string content, string categoryType)
        {
            ltrErrorPopupTitle.Text = title;
            ltrErrorPopupText.Text = content;
            ltrSuccessTitle.Text = title;
            ltrSuccessContent.Text = content;

            // Set the category type in a hidden field
            hidCategoryType.Value = categoryType;

            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append("<script type=\"text/javascript\"> $('#modaldemo4').modal('show'); </script>");

            ClientScriptManager cs = Page.ClientScript;
            cs.RegisterStartupScript(this.GetType(), "PopupScript", cstext1.ToString());
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

        protected void lbtnDeleteImg_Click(object sender, EventArgs e)
        {
            try
            {
                pnlCategoryImage.Visible = false;
                pnlUploadImage.Visible = true;
                imgCatImage.ImageUrl = "";
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(Page, "Failure, " + ex.Message, "danger");
            }
        }

        protected void SDSDepartment_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        }

        protected void SDSCat_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        }

        private void LoadStoreInfo()
        {
            int id = Convert.ToInt32(Request.QueryString["id"]);
            if (id > 0)
            {
                List<KeyValuePair<string, object>> sqlParams = new List<KeyValuePair<string, object>>();
                sqlParams.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));
                sqlParams.Add(new KeyValuePair<string, object>("vcid", id));
                string sqlSelect = $"SELECT vc_id,vc_name,vc_parentCategoryId,pc.parent_category,vc_categoryId,prc.category_name,vc_isHome,vc_isInCategory,IF((vc_status = 0), 'Inactive', 'Active') AS vc_status, vc.image_url, isFeatured, isPreferred FROM retaline_virtual_category vc LEFT JOIN mypha_productparent_category pc ON pc.parent_category_id = vc.vc_parentCategoryId LEFT JOIN mypha_productcategory prc ON prc.category_id = vc.vc_categoryId WHERE vc_id = @vcid and store_group_id=@storegroupid";
                DataTable dataTable = DataServiceMySql.GetDataTable(sqlSelect, Service.UserService.GetAPIConnectionString(), sqlParams);
                if (dataTable == null || dataTable.Rows.Count < 1)
                {
                    // $('#modaldemo5').on('hidden.bs.modal', function (e) {window.location.href = "/PrivateCategory"; });
                    Type cstype = this.GetType();
                    String csname1 = "OnClosePopupScript";
                    ClientScriptManager cs = Page.ClientScript;

                    StringBuilder cstext1 = new StringBuilder();
                    cstext1.Append("<script type=text/javascript> $('#modaldemo5').on('hidden.bs.modal', function (e) {window.location.href = \"/Tenant/PrivateCategory\"; }); </");
                    cstext1.Append("script>");

                    cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());


                    ShowFailure("Invalid Data", "The data selected is invalid.");

                    return;

                }

                if (dataTable != null && dataTable.Rows.Count > 0)
                {
                    DataRow da = dataTable.Rows[0];
                    txtVirtCat.Text = da["vc_name"].ToString();
                    selDept.SelectedItem.Value = da["vc_parentCategoryId"].ToString();
                    selDept.SelectedItem.Text = da["parent_category"].ToString();
                    selCat.SelectedItem.Value = da["vc_categoryId"].ToString();
                    selCat.SelectedItem.Text = da["category_name"].ToString();
                    string strImageUrl = da["image_url"].ToString();
                    if (!String.IsNullOrEmpty(strImageUrl))
                    {
                        pnlCategoryImage.Visible = true;
                        pnlUploadImage.Visible = false;
                        imgCatImage.ImageUrl = strImageUrl;
                        myImage.ImageUrl = strImageUrl;
                        myImage.DataBind();
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
                    //string strcheckFeatured = da["isFeatured"].ToString();
                    if (strcheck == "1")
                    {
                        //chkFeatured.Checked = true;
                        lblDept.Visible = true;
                        selDept.Visible = true;
                        lblCat.Visible = true;
                        selCat.Visible = true;
                    }
                    else
                    {
                        //chkFeatured.Checked = false;
                        lblDept.Visible = false;
                        selDept.Visible = false;
                        lblCat.Visible = false;
                        selCat.Visible = false;
                    }
                    //string strcheckPreferred = da["isPreferred"].ToString();
                    if (strcheck == "1")
                    {
                        //chkPreferred.Checked = true;
                        lblDept.Visible = true;
                        selDept.Visible = true;
                        lblCat.Visible = true;
                        selCat.Visible = true;
                    }
                    else
                    {
                        //chkPreferred.Checked = false;
                        lblDept.Visible = false;
                        selDept.Visible = false;
                        lblCat.Visible = false;
                        selCat.Visible = false;
                    }
                }

                string sqlItems = $"SELECT stit_id FROM retaline_vc_items WHERE vc_id=@vcid";
                DataTable dtItems = DataServiceMySql.GetDataTable(sqlItems, Service.UserService.GetAPIConnectionString(), sqlParams);
                hidSelectedItems.Value = String.Join(",", dtItems.AsEnumerable().Select(item => string.Format("{0}", item["stit_id"])).ToArray());
                btnSubmit.Text = "Save Changes";
                btnRefresh.Visible = true;
            }
        }

        protected async void btnSubmit_Click(object sender, EventArgs e)
        {
            try
            {
                int virtualCatId = Convert.ToInt32(Request.QueryString["id"]);
                string categoryType = Request.QueryString["type"]?.ToLower();
                string createdOn = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
                string updatedOn = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
                List<string> lstInsertSqls = new List<string>();
                string insertSql = String.Join("; ", lstInsertSqls.ToArray());
                int parentCat = 0;
                int catId = 0;
                if (chkCat.Checked == true)
                {
                    parentCat = Convert.ToInt32(selDept.Text);
                    catId = Convert.ToInt32(selCat.Text);
                }
                else
                {
                    parentCat = 0;
                    catId = 0;
                }
                string imgUrl = "";
                if (pnlCategoryImage.Visible && !string.IsNullOrEmpty(imgCatImage.ImageUrl))
                {
                    imgUrl = imgCatImage.ImageUrl;
                    if (categoryType == "preferred")
                    {
                        try
                        {
                            using (var webClient = new System.Net.WebClient())
                            {
                                byte[] imageData = webClient.DownloadData(imgUrl);

                                using (var memoryStream = new MemoryStream(imageData))
                                {
                                    using (System.Drawing.Image img = System.Drawing.Image.FromStream(memoryStream))
                                    {
                                        if (img.Width != 1260 || img.Height != 300)
                                        {
                                            Common.ShowToastifyMessage(this.Page, "Uploaded image size must be exactly 1260 × 300 pixels.", "danger");
                                            return;
                                        }
                                    }
                                }
                            }
                        }
                        catch
                        {
                            Common.ShowToastifyMessage(this.Page, "Unable to verify uploaded image. Please check the image URL or format.", "danger");
                            return;
                        }
                    } 
                }
                int isFeatured = categoryType == "featured" ? 1 : 0;
                int isPreferred = categoryType == "preferred" ? 1 : 0;
                bool isFeaturedExists = CheckIfFeaturedExists();
                if (isFeatured == 1 && isFeaturedExists)
                {
                    Common.ShowCustomAlert(this.Page, "Action Not Allowed", "A featured item already exists. To add a new one, please delete the existing featured item first.", false, "/Tenant/PrivateCategory?type=featured");
                    return;
                }
                List<KeyValuePair<string, object>> sqlParams = new List<KeyValuePair<string, object>>();
                sqlParams.Add(new KeyValuePair<string, object>("vcname", txtVirtCat.Text));
                sqlParams.Add(new KeyValuePair<string, object>("parentcatid", parentCat));
                sqlParams.Add(new KeyValuePair<string, object>("catid", catId));
                sqlParams.Add(new KeyValuePair<string, object>("ishome", (chkHome.Checked ? 1 : 0)));
                sqlParams.Add(new KeyValuePair<string, object>("iscat", (chkCat.Checked ? 1 : 0)));
                sqlParams.Add(new KeyValuePair<string, object>("createdDate", createdOn));
                sqlParams.Add(new KeyValuePair<string, object>("updatedDate", updatedOn));
                sqlParams.Add(new KeyValuePair<string, object>("storegroupId", this.CurrentUser.APIStoreId));
                sqlParams.Add(new KeyValuePair<string, object>("imgUrl", imgUrl));
                sqlParams.Add(new KeyValuePair<string, object>("isFeatured", isFeatured));
                sqlParams.Add(new KeyValuePair<string, object>("isPreferred", isPreferred));

                int vcid = virtualCatId;
                if (virtualCatId == 0)
                {
                    string strSql = $"INSERT INTO retaline_virtual_category(vc_name, vc_parentCategoryId, vc_categoryId, vc_isHome, vc_isInCategory, vc_status, vc_createdOn,store_group_id, image_url, isFeatured, isPreferred) " +
                                        $"VALUES(@vcname,@parentcatid,@catid,@ishome,@iscat,1,@createdDate,@storegroupId, @imgUrl, @isFeatured, @isPreferred); select LAST_INSERT_ID() ";
                    var result = DataServiceMySql.ExecuteScalar(strSql, Service.UserService.GetAPIConnectionString(), sqlParams);
                    vcid = Convert.ToInt32(result);

                    gvPrivateCat.DataBind();
                    ResetForm();
                    // Activitylog
                    String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                    String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                    string Source = strUrl;
                    int storegroupid = this.CurrentUser.APIStoreId;
                    string Users = this.CurrentUser.Email;
                    string storegroup_id = storegroupid.ToString();
                    string vcname = txtVirtCat.Text;
                    string parentcatid = parentCat.ToString();
                    string catid = catId.ToString();
                    string ishome = (chkHome.Checked ? 1 : 0).ToString();
                    string iscat = (chkCat.Checked ? 1 : 0).ToString();
                    string createdDate = createdOn;
                    string updatedDate = updatedOn;
                    string ImgUrl = imgUrl;
                    var items = new[]
                    {
                        new { Key = "Storegroup Id", Value = storegroup_id },
                        new { Key = "Virtual Category Name", Value = vcname },
                        new { Key = "Virtual Category_parentCategory Id", Value = parentcatid },
                        new { Key = "Category Id", Value = catid },
                        new { Key = "ishome", Value = ishome },
                        new { Key = "iscat", Value = iscat },
                        new { Key = "createdDate", Value = createdDate },
                        new { Key = "updatedDate", Value = updatedDate },
                        new { Key = "ImgUrl", Value = ImgUrl },
                        new { Key = "isFeatured", Value = isFeatured.ToString() },
                        new { Key = "isPreferred", Value = isPreferred.ToString() }
                    };
                    string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                    var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);

                    Common.ShowToastifyMessage(this.Page, "Created successfully");
                }
                else
                {
                    sqlParams.Add(new KeyValuePair<string, object>("vcid", virtualCatId));
                    string strUpdateSql = $"UPDATE retaline_virtual_category SET vc_name=@vcname, vc_parentCategoryId=@parentcatid, " +
                    $"vc_categoryId=@catid, vc_isHome=@ishome, vc_isInCategory=@iscat, " +
                    $"vc_status =1, vc_updatedOn=@updatedDate, image_url = @imgUrl, isFeatured = @isFeatured, isPreferred = @isPreferred WHERE vc_id = @vcid";
                    var result = DataServiceMySql.ExecuteScalar(strUpdateSql, Service.UserService.GetAPIConnectionString(), sqlParams);

                    gvPrivateCat.DataBind();
                    ResetForm();
                    // Activitylog
                    String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                    String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                    string Source = strUrl;
                    int storegroupid = this.CurrentUser.APIStoreId;
                    string Users = this.CurrentUser.Email;
                    string storegroup_id = (this.CurrentUser.APIStoreId).ToString();
                    string vcname = txtVirtCat.Text;
                    string parentcatid = parentCat.ToString();
                    string catid = catId.ToString();
                    string ishome = (chkCat.Checked ? 1 : 0).ToString();
                    string iscat = (chkCat.Checked ? 1 : 0).ToString();
                    string createdDate = createdOn;
                    string updatedDate = updatedOn;
                    string ImgUrl = imgUrl;
                    var items = new[]
                        {
                    new { Key = "Storegroup Id", Value = storegroup_id },
                    new { Key = "Virtual Category Name", Value = vcname },
                    new { Key = "Virtual Category_parentCategory Id", Value = vcname },
                    new { Key = "Category Id", Value = catid },
                    new { Key = "ishome", Value = ishome },
                    new { Key = "iscat", Value = iscat },
                    new { Key = "createdDate", Value = createdDate },
                    new { Key = "updatedDate", Value = updatedDate },
                    new { Key = "ImgUrl", Value = ImgUrl },
                    new { Key = "isFeatured", Value = isFeatured.ToString() },
                    new { Key = "isPreferred", Value = isPreferred.ToString() }
                  };
                    string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                    var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);
                    Common.ShowToastifyMessage(this.Page, "Updated successfully");
                }

                List<KeyValuePair<string, object>> sqlInsItemsParams = new List<KeyValuePair<string, object>>();
                sqlInsItemsParams.Add(new KeyValuePair<string, object>("vcid", vcid));
                sqlInsItemsParams.Add(new KeyValuePair<string, object>("selectedIds", hidSelectedItems.Value));
                sqlInsItemsParams.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));

                string sql = "DELETE ci FROM retaline_vc_items ci INNER JOIN retaline_virtual_category c on ci.vc_id = c.vc_id WHERE ci.vc_id = @vcid and c.store_group_id=@storegroupid and NOT FIND_IN_SET(stit_id, @selectedIds); ";
                sql += "INSERT ignore INTO retaline_vc_items(stit_type, vc_id, stit_id) SELECT 2, @vcid, stit_id FROM finascop_stock_itemmaster WHERE FIND_IN_SET(stit_id, @selectedIds);";
                DataServiceMySql.ExecuteSql(sql, UserService.GetAPIConnectionString(), sqlInsItemsParams);
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(Page, "Failure, " + ex.Message, "danger");
            }
        }

        private bool CheckIfFeaturedExists()
        {
            string query = "SELECT COUNT(*) FROM retaline_virtual_category WHERE isFeatured = 1 AND store_group_id = @storegroupId";
            var sqlParams = new List<KeyValuePair<string, object>>()
            {
                new KeyValuePair<string, object>("storegroupId", this.CurrentUser.APIStoreId)
            };

            var result = DataServiceMySql.ExecuteScalar(query, Service.UserService.GetAPIConnectionString(), sqlParams);
            int count = Convert.ToInt32(result);
            return count > 0;
        }

        private void ResetForm()
        {
            // Clear text fields and uncheck checkboxes
            txtVirtCat.Text = "";
            chkHome.Checked = false;
            chkCat.Checked = false;

            // Reset dropdowns to the default value
            selDept.SelectedIndex = 0;
            selCat.SelectedIndex = 0;

            // Hide dropdowns and labels
            selDept.Visible = false;
            selCat.Visible = false;
            lblDept.Visible = false;
            lblCat.Visible = false;

            // Manage image panels visibility
            pnlCategoryImage.Visible = false;
            pnlUploadImage.Visible = true;
        }

        protected string GetBackLink()
        {
            string categoryType = Request.QueryString["type"];
            return string.IsNullOrEmpty(categoryType) ? "/Navigations/Products" : $"/Navigations/Others";
        }

        protected void gvPrivateCat_RowDataBound(object sender, GridViewRowEventArgs e)
        {
            if (e.Row.RowType == DataControlRowType.DataRow)
            {
                HyperLink lnkEdit = (HyperLink)e.Row.FindControl("lnkEdit");
                string vc_id = DataBinder.Eval(e.Row.DataItem, "vc_id").ToString();
                string categoryType = Request.QueryString["type"];

                string redirectUrl = "/Tenant/PrivateCategory.aspx?id=" + vc_id;

                if (categoryType == "featured")
                {
                    redirectUrl += "&type=featured";
                    gvPrivateCat.Columns[2].Visible = false;
                    gvPrivateCat.Columns[1].Visible = false;
                } 
                else if (categoryType == "preferred")
                {
                    redirectUrl += "&type=preferred";
                }
            }
        }

        protected void btnRefresh_Click(object sender, EventArgs e)
        {
            string categoryType = Request.QueryString["type"]?.ToLower();
            string redirectUrl = "/Tenant/PrivateCategory";

            if (categoryType == "featured")
                redirectUrl += ".aspx?type=featured";
            else if (categoryType == "preferred")
                redirectUrl += ".aspx?type=preferred";

            Response.Redirect(redirectUrl);
        }


        protected async void gvPrivateCat_RowUpdating(object sender, GridViewUpdateEventArgs e)
        {
            try
            {
                GridViewRow row = gvPrivateCat.Rows[e.RowIndex];

                string vc_id = ((Button)row.FindControl("btnUpdate")).CommandArgument;
                string vc_name = ((TextBox)row.FindControl("txtName")).Text;

                // Get controls
                FileUpload fileUpload = (FileUpload)row.FindControl("fileimgUpload");
                HiddenField hidImg = (HiddenField)row.FindControl("hidImage");
                string image_url = hidImg.Value;
                string categoryType = Request.QueryString["type"]?.ToLower();
                if (fileUpload.HasFile)
                {
                    byte[] imageBytes = fileUpload.FileBytes;

                    using (var img = System.Drawing.Image.FromStream(new MemoryStream(imageBytes)))
                    {
                        if (categoryType == "preferred" && (img.Width != 1260 || img.Height != 300))
                        {
                            Common.ShowToastifyMessage(this.Page, "Uploaded image size must be exactly 1260 × 300 pixels.", "danger");
                            return;
                        }
                    }

                    using (var uploadStream = new MemoryStream(imageBytes))
                    {
                        string filename = Guid.NewGuid() + System.IO.Path.GetExtension(fileUpload.FileName);
                        string resultUrl = await Service.Common.CreateBlob(uploadStream, filename, "vc");

                        if (!string.IsNullOrEmpty(resultUrl))
                        {
                            image_url = resultUrl; 
                        }
                    }
                }

                // Update the database
                SqlDataSource SDSPrivateCat = (SqlDataSource)gvPrivateCat.DataSourceObject;
                SDSPrivateCat.UpdateParameters["vc_id"].DefaultValue = vc_id;
                SDSPrivateCat.UpdateParameters["vc_name"].DefaultValue = vc_name;
                SDSPrivateCat.UpdateParameters["image_url"].DefaultValue = image_url;

                SDSPrivateCat.Update();

                gvPrivateCat.EditIndex = -1;
                gvPrivateCat.DataBind();
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(Page, "Failure, " + ex.Message, "danger");
            }

        }
    }
}


