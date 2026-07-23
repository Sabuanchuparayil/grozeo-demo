using RetalineProAgent.Core.BussinessModel.Catalog;
using RetalineProAgent.Core.BussinessModel.Finance;
using RetalineProAgent.Core.BussinessModel.Inventory;
using RetalineProAgent.Core.BussinessModel.Store;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Core.Services.Cache;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.ComponentModel.Design;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Runtime.InteropServices;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.HtmlControls;
using System.Web.UI.WebControls;
using static System.Windows.Forms.VisualStyles.VisualStyleElement.Window;

namespace RetalineProAgent.Controls.StoreSettings
{
    public partial class ctrlMyProducts : Base.BasePartnerUserControl
    {
        public delegate void ParentAddProductHandler(int status);
        public delegate void ParentAddBrandHandler(int status);
        public delegate void ParentMessageHandler(string title, string msg, int type);

        public event ParentAddProductHandler ParentAddProductBinding;
        public event ParentAddBrandHandler ParentAddBrandBinding;
        public event ParentAddProductHandler ParentCancelAddProductBinding;

        public event ParentMessageHandler ParentMessageBinding;

        int flgBrnd;
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                int storeId = this.CurrentUser.APIStoreId;
                SDSSubcategory.SelectParameters["storeId"].DefaultValue = storeId.ToString();
                hdnModalOpen.Value = "0";
            }

            if (IsPostBack && hdnModalOpen.Value == "1")
            {
                hdnModalOpen.Value = "0";
            }

            if (gvProducts.HeaderRow != null)
                gvProducts.HeaderRow.TableSection = TableRowSection.TableHeader;
        }


        protected void Page_PreRender(object sender, EventArgs e)
        {
            try
            {
                if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
                    gvProducts.HeaderRow.Cells[4].Text = "VAT";
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(this.Page, "An error occurred: " + ex.Message, "danger");
            }
        }
        

        protected void OBJ_Selecting(object sender, ObjectDataSourceSelectingEventArgs e)
        {
            if (e.InputParameters.Contains("storeId"))
                e.InputParameters["storeId"] = this.CurrentUser.APIStoreId;//.StoreGroupId;
        }

        protected void lstProducts_DataBound(object sender, EventArgs e)
        {

        }

        protected void SDSBrands_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            if (e.Command.Parameters.Contains("storeId"))
                e.Command.Parameters["storeId"].Value = this.CurrentUser.APIStoreId;//.StoreGroupId; 
        }
        //protected void SDSBrand_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        //{
        //    if (e.Command.Parameters.Contains("storeId"))
        //        e.Command.Parameters["storeId"].Value = this.CurrentUser.APIStoreId;//.StoreGroupId; 
        //}

        //protected void btnbrand_Click(object sender, EventArgs e)
        //{
        //    LinkButton lbtn = (LinkButton)sender;
        //    int Id = 37;
        //    Response.Redirect("BrandProduct.aspx?brandId=" + Id + "");
        //}


        protected void lnkBrand_Click(object sender, EventArgs e)
        {
           
            string Id = selBrd.SelectedValue;
            Response.Redirect($"BrandProduct.aspx?brandId={Id}");
        }       
           
  
        protected void lnkSubcategory_Click(object sender, EventArgs e)
        {
           
            string catId = selSubCategory.SelectedValue;
            Response.Redirect($"BrandProduct.aspx?catId={catId}");


        }
        protected void SDSProducts_Selected(object sender, SqlDataSourceStatusEventArgs e)
        {


        }

        protected void btnAddBrand_Click(object sender, EventArgs e)
        {
            if (String.IsNullOrEmpty(txtNewBrand.Text))
            {
                ltrAddBrandResult.Text = "Please enter brand name and manufacturer.";
                //ShowAddBrandPopup();
                if (ParentAddBrandBinding != null)
                    ParentAddBrandBinding(0);
                else
                    Common.ShowToastifyMessage(this.Page, "Please enter brand name.");
                return;
            }


            DataTable dtManufacturer = DataServiceMySql.GetDataTable($"SELECT manufacture_id, manufacture_name FROM mypha_productmanufacture WHERE manufacture_name='Multiple'", Service.UserService.GetAPIConnectionString());
            int manufactureId = 0;
            if (dtManufacturer != null && dtManufacturer.Rows.Count > 0)
            {
                DataRow dr = dtManufacturer.Rows[0];
                manufactureId = Convert.ToInt32(dr["manufacture_id"]);

            }


            var brandParams = new List<KeyValuePair<string, object>>();
            brandParams.Add(new KeyValuePair<string, object>("brandname", txtNewBrand.Text));
            brandParams.Add(new KeyValuePair<string, object>("manufacture", manufactureId));
            brandParams.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.APIStoreId));

            //string strSql = $"INSERT INTO mypha_productbrands (brand_name, manufacture_id) SELECT * FROM (SELECT @brandname, ifnull((SELECT manufacture_id FROM `mypha_productmanufacture` WHERE LOWER(TRIM(REPLACE(manufacture_name, ' ', ''))) LIKE LOWER(TRIM(REPLACE(@manufacture, ' ', '')))), 1))tmp WHERE " +
            //    $"NOT EXISTS( SELECT * FROM mypha_productbrands WHERE brand_name = @brandname);";

            int count = 0;
            try
            {
                DataTable dtBrand = DataServiceMySql.GetDataTable("addPrivateBrand", UserService.GetAPIConnectionString(), brandParams, true);
                if (dtBrand != null && dtBrand.Rows.Count > 0)
                {
                    int brandid = Convert.ToInt32(dtBrand.Rows[0][0]);
                    int isnew = Convert.ToInt32(dtBrand.Rows[0][1]);
                    //hidSelectedBrand.Value = brandid.ToString();
                    if (brandid > 0)
                    {
                        string brandName = txtNewBrand.Text;
                        int manufacturerId = manufactureId;
                        int storegroupId = this.CurrentUser.APIStoreId;
                        var result = Core.Services.APIService.ProductBrand(brandName, manufacturerId, storegroupId);

                        List<KeyValuePair<string, object>> brandparams = new List<KeyValuePair<string, object>>();
                        brandparams.Add(new KeyValuePair<string, object>("brandId", brandid));
                        brandparams.Add(new KeyValuePair<string, object>("mappingId", result.brand_id));
                        string strUpdateSql = $"UPDATE mypha_productbrands SET mapping_id=@mappingId WHERE brand_id=@brandId";
                        DataServiceMySql.ExecuteSql(strUpdateSql, Service.UserService.GetAPIConnectionString(), brandparams);                        


                        if (isnew == 0)
                        {
                            Common.ShowToastifyMessage(Page, (isnew > 0 ? "Brand created successfully!" : "Brand name is already existing. It is selected in the brand select box to continue"), (isnew > 0 ? "success" : "info"));
                        }
                        else
                        {
                            count = 1;
                            string brandId = Convert.ToString(brandid);
                            Response.Redirect($"PrivateInventory.aspx?brandId={brandId}");
                        }
                    }

                    // Activitylog
                    String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                    String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                    string Source = strUrl;
                    int storegroupid = this.CurrentUser.APIStoreId;
                    string Users = this.CurrentUser.Email;
                    string storegroup_id = (this.CurrentUser.APIStoreId).ToString();
                    string brandname = txtNewBrand.Text;
                    int Brandid = brandid;
                    int ManufactureId = manufactureId;
                    int tenantId = this.CurrentUser.APIStoreId;
                    var items = new[]
                    {
                      new { Key = "Brandname", Value = brandname },
                      new { Key = "Manufacture Id", Value =Convert.ToString(ManufactureId) },
                      new { Key = "TenantId", Value =Convert.ToString(tenantId) },
                      new { Key = "Brandid", Value =Convert.ToString(Brandid) },
                    };
                    string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                    var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);
                }
                if (ParentAddBrandBinding != null)
                    ParentAddBrandBinding(1);
            }
            catch { count = 0; }
            if (count == 0)
            {
                Common.ShowToastifyMessage(Page, "The brand name already exists or there is a technical problem on creating brand.", "danger");
                if (ParentAddBrandBinding != null)
                    ParentAddBrandBinding(-1);

            }
        }




        protected void DeleteItem_Click(object sender, EventArgs e)
        {
            //LinkButton delProductItem = (LinkButton)sender;
            //if (delProductItem == null)
            //    return;

            //int storegroupid = this.CurrentUser.APIStoreId;
            //string strSql = $"DELETE FROM finascop_stock_branch_inventory WHERE stit_ID={delProductItem.Attributes["itemid"]} AND EXISTS(SELECT * FROM finascop_branch WHERE br_ID = finascop_stock_branch_inventory.branch_id AND br_storeGroup={storegroupid})";
            //DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString());

            //SDSProducts.Select(DataSourceSelectArguments.Empty);
            //lstProducts.DataBind();
        }




        protected void selBrand_DataBound(object sender, EventArgs e)
        {
            //selBrand.Items.Insert(0, new ListItem("All Brands", "0"));
        }

        protected void selectBrand_DataBound(object sender, EventArgs e)
        {
            //selBrand.Items.Insert(0, new ListItem("All Brands", "0"));
        }

        protected void SDSRetCat_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        }


        private void ShowAddItem()
        {
            Type cstype = this.GetType();
            String csname1 = "AddItemPopupScript";
            ClientScriptManager cs = Page.ClientScript;

            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append("<script type=text/javascript> $('#create_new_product').modal('show'); </");
            cstext1.Append("script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }

        protected void SDSInventory_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["@storeId"].Value = this.CurrentUser.APIStoreId;
            e.Command.Parameters["@startIndex"].Value = (gvProducts.PageIndex * gvProducts.PageSize);

            //if (Page.User.IsInRole("BranchManager"))
            //{
            //    int brid = UserService.UserRoleBranchId;
            //    e.Command.Parameters["@BranchId"].Value = brid;
            //}
        }

        protected void gvProducts_DataBound(object sender, EventArgs e)
        {

            int startRowOnPage = (gvProducts.PageIndex * gvProducts.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvProducts.Rows.Count - 1;
            //ltrPageCurTotal.Text = lastRowOnPage.ToString();
           
        }

        //protected void SDSInventory_Selected(object sender, SqlDataSourceStatusEventArgs e)
        //{
        //    //int startRowOnPage = (gvProducts.PageIndex * gvProducts.PageSize) + 1;
        //    //int lastRowOnPage = startRowOnPage + gvProducts.Rows.Count - 1;
        //    //int totalRows = e.AffectedRows;
        //    if (e.Command.Parameters.Contains("totalRecords") && e.Command.Parameters["totalRecords"].Value != null)
        //    {
        //        try
        //        {
        //            int totalRecords = (int)e.Command.Parameters["totalRecords"].Value;
        //            gvProducts.VirtualItemCount = totalRecords;
        //        }
        //        catch { }
        //    }
        //}

        protected void SDSInventory_Selected(object sender, SqlDataSourceStatusEventArgs e)
        {
            if (e.Command.Parameters.Contains("totalRecords") &&
                e.Command.Parameters["totalRecords"].Value != null &&
                e.Command.Parameters["totalRecords"].Value != DBNull.Value)
            {
                try
                {
                    int totalRecords = Convert.ToInt32(e.Command.Parameters["totalRecords"].Value);
                    gvProducts.VirtualItemCount = totalRecords;
                }
                catch (Exception ex)
                {
                    Common.ShowToastifyMessage(this.Page, "Error parsing totalRecords: " + ex.Message, "warning");
                }
            }
            else
            {
                gvProducts.VirtualItemCount = 0;
            }
        }


        protected void btnedit_Click(object sender, EventArgs e)
        {
            //Button btnAssign = (Button)sender;
            //if(btnAssign == null || String.IsNullOrEmpty(btnAssign.Attributes["stitId"]))
            //{
            //    Common.ShowToastifyMessage(this.Page, "Invalid item selected!", "danger");
            //    return;
            //}
            LinkButton lbtn = (LinkButton)sender;
            if (lbtn == null || String.IsNullOrEmpty(lbtn.Attributes["stitId"]))
            {
                Common.ShowToastifyMessage(this.Page, "Invalid item selected!", "danger");
                return;
            }
            string addaction = lbtn.Attributes["action"];

            //string orderId = btnAssign.Attributes["quorId"];
            if (addaction == "Edit")
            {
                int storeGroupId = Convert.ToInt32(lbtn.Attributes["storeGroupId"]);
                int stitId = Convert.ToInt32(lbtn.Attributes["stitId"]);
                int brandId = -1;
                try
                {
                    if (!String.IsNullOrEmpty(lbtn.Attributes["brandId"]))
                        brandId = Convert.ToInt32(lbtn.Attributes["brandId"]);
                }
                catch { }

                if (stitId > 0 && storeGroupId == this.CurrentUser.APIStoreId)
                {
                    Response.Redirect($"PrivateInventory.aspx?id=" + stitId + "&id2=" + brandId + "&type=" + 1);
                    //string url = "PrivateInventory.aspx?id=" + stitId + "&id2=" + brandId;
                    return;
                }

            }
            else if (addaction == "View")
            {
                int storeGroupId = Convert.ToInt32(lbtn.Attributes["storeGroupId"]);
                int stitId = Convert.ToInt32(lbtn.Attributes["stitId"]);
                int brandId = -1;
                try
                {
                    if (!String.IsNullOrEmpty(lbtn.Attributes["brandId"]))
                        brandId = Convert.ToInt32(lbtn.Attributes["brandId"]);
                }
                catch { }

                if (stitId > 0 && storeGroupId == 0)
                {
                    Response.Redirect($"ViewPrivateInventory.aspx?id=" + stitId + "&id2=" + brandId);
                    //string url = "PrivateInventory.aspx?id=" + stitId + "&id2=" + brandId;
                    return;
                }
            }


            Common.ShowToastifyMessage(this.Page, "Invalid item selected or there is a technical error happend!", "danger");
        }


        protected async void btnSetErpID_Click(object sender, EventArgs e)
        {
            if (String.IsNullOrEmpty(hidERP_stitid.Value))
            {
                // showModal('Set ERP Failed', 'Invalid product id', false);
                // show error;
                return;


            }

            int branchId = 0;

            int storegroupid = this.CurrentUser.APIStoreId;
            List<KeyValuePair<String, Object>> branchParams = new List<KeyValuePair<string, object>>();
            branchParams.Add(new KeyValuePair<string, object>("storegroupid", storegroupid));

            string storeId = "";
            if (rbSelectStore.Checked == true)
            {
                storeId = selBranch.Text;
            }
            else
            {
                storeId = "0";
            }

            List<KeyValuePair<String, Object>> erpParams = new List<KeyValuePair<string, object>>();
            erpParams.Add(new KeyValuePair<string, object>("itemId", hidERP_stitid.Value));
            erpParams.Add(new KeyValuePair<string, object>("branch", storeId));
            erpParams.Add(new KeyValuePair<string, object>("storeGroup", this.CurrentUser.APIStoreId));
            erpParams.Add(new KeyValuePair<string, object>("code", txtCode.Text));
            string mainstore = Convert.ToString(this.CurrentUser.APIStoreId);
            bool codeExists = false;
            int companyId = 0;
            if (Convert.ToInt32(selERPType.SelectedItem.Value) == 0)
            {
                companyId = 0;
            }
            else
            {
                companyId = 1;
            }

            DataTable dtCode = DataServiceMySql.GetDataTable("SELECT COUNT(*) AS cnt FROM finascop_stock_itemmaster_product_codes WHERE fsipc_code = @code", UserService.GetAPIConnectionString(), erpParams);
            if (dtCode != null && dtCode.Rows.Count > 0)
            {
                DataRow dr = dtCode.Rows[0];
                if (Convert.ToInt32(dr["cnt"]) > 0)
                {
                    Common.ShowToastifyMessage(this.Page, "Duplicate code. The code is already used for another product.", "danger");
                    return;
                }
            }
            DataTable dtCodeExists = DataServiceMySql.GetDataTable("SELECT * FROM finascop_stock_itemmaster_product_codes WHERE fsipc_storeGroup = @storeGroup AND ((fsipc_code = @code AND fsipc_store=@branch) OR (fsipc_stit_id=@itemId AND fsipc_store=@branch))", UserService.GetAPIConnectionString(), erpParams);
            if (dtCodeExists != null && dtCodeExists.Rows.Count > 0)
            {
                if (dtCodeExists.AsEnumerable().Any(r => r["fsipc_code"].ToString() == txtCode.Text && r["fsipc_store"].ToString() == storeId.ToString()))
                {
                    Common.ShowToastifyMessage(this.Page, "Duplicate code. The code is already used for another product in same store.", "danger");
                    return;
                }
                else if (dtCodeExists.AsEnumerable().Any(r => r["fsipc_stit_id"].ToString() == hidERP_stitid.Value && r["fsipc_store"].ToString() == storeId.ToString()))
                {
                    codeExists = true;
                }
            }

            erpParams.Add(new KeyValuePair<string, object>("codeType", selERPType.SelectedItem.Text));
            erpParams.Add(new KeyValuePair<string, object>("company", companyId));
            erpParams.Add(new KeyValuePair<string, object>("individual", 0));
            string strSql = $"INSERT INTO finascop_stock_itemmaster_product_codes(fsipc_stit_id, fsipc_code, fsipc_codeType, fsipc_isCompany, fsipc_storeGroup, " +
                $"fsipc_store, fsipc_isIndividual) " +
                $"VALUES(@itemId, @code, @codeType, @company, @storeGroup, @branch, @individual)";
            if (codeExists)
                strSql = "UPDATE finascop_stock_itemmaster_product_codes SET fsipc_code=@code, fsipc_codeType=@codeType, fsipc_isCompany=@company  where fsipc_stit_id=@itemId and fsipc_storeGroup=@storeGroup";
            try
            {
                int result = DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), erpParams);

                // Remove Redis cache entry
                var cacheService = new RedisCacheService();
                string cachekey = $"Retl.AppTenant.pendingtasks.count.{this.CurrentUser.APIStoreId}";
                await cacheService.RemoveAsync(cachekey);

                // Activitylog
                String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                string Source = strUrl;
                //int storegroupsid = this.CurrentUser.APIStoreId;
                string Users = this.CurrentUser.Email;
                string storegroup_id = (this.CurrentUser.APIStoreId).ToString();
                string brandname = txtNewBrand.Text;
                string CodeType = selERPType.SelectedItem.Text;
                string itemId = hidERP_stitid.Value;
                string branch = storeId;
                string code = txtCode.Text;
                int company_Id = companyId;
                int individual = 0;
                var items = new[]
                {
                      new { Key = "Brandname", Value = brandname },
                      new { Key = "Code Type", Value =CodeType},
                      new { Key = "Item Id", Value =itemId },
                      new { Key = "Branch", Value =branch },
                      new { Key = "company_Id", Value =Convert.ToString(company_Id) },
                      new { Key = "Individual", Value =Convert.ToString(individual) },

                    };
                string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);

                SDSInventory.Select(DataSourceSelectArguments.Empty);
                gvProducts.DataBind();
                selERPType.DataBind();
                rbAllStores.DataBind();
                rbSelectStore.DataBind();
                if (rbSelectStore.Checked)
                {
                    dvselectstore.DataBind();
                    selBranch.DataBind();
                }
                // Clear form fields
                //txtCode.Text = string.Empty;
                //selERPType.Text = "";
                //selBranch.Text = "";
                //rbAllStores.Checked = false;
                //rbSelectStore.Checked = false;
                //dvselectstore.InnerText = string.Empty;

                Common.ShowToastifyMessage(Page, "Executed successfully");
                //Common.ShowCustomAlert(this.Page, "Success", "Executed successfully", true);
                //ShowSuccess("Success!", "Executed successfully!!");
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(Page, "Failure, " + ex.Message, "danger");

            }
        }

        protected async void btnReturnDays_Click(object sender, EventArgs e)
        {
            int branchId = 0;
            int storegroupid = this.CurrentUser.APIStoreId;
            List<KeyValuePair<String, Object>> branchParams = new List<KeyValuePair<string, object>>();
            branchParams.Add(new KeyValuePair<string, object>("storegroupid", storegroupid));

            var dt = DataServiceMySql.GetDataTable($"SELECT br_ID FROM finascop_branch WHERE br_storeGroup = @storegroupid LIMIT 1", UserService.GetAPIConnectionString(), branchParams);
            string brndId = "";
            if (dt != null && dt.Rows.Count > 0)
            {
                DataRow da = dt.Rows[0];
                brndId = da["br_ID"].ToString();
            }
            branchId = Convert.ToInt32(brndId);

            List<KeyValuePair<String, Object>> erpParams = new List<KeyValuePair<string, object>>();
            erpParams.Add(new KeyValuePair<string, object>("itemId", hidERP_stitid.Value));
            erpParams.Add(new KeyValuePair<string, object>("branch", branchId));
            erpParams.Add(new KeyValuePair<string, object>("storeGroup", this.CurrentUser.APIStoreId));
            erpParams.Add(new KeyValuePair<string, object>("returnTime", txtReturnDays.Text));
            erpParams.Add(new KeyValuePair<string, object>("spotReturn", (chkSpotReturn.Checked ? 1 : 0)));


            DataTable dtReturnDays = DataServiceMySql.GetDataTable("SELECT stit_id, hasSpotReturn, returnTime FROM finascop_stock_branch_inventory WHERE stit_id = @itemId AND branch_id = @branch", UserService.GetAPIConnectionString(), erpParams);
            DataRow dr = dtReturnDays.Rows[0];
            try
            {
                if ((Convert.ToInt32(dr["stit_id"])) <= 0 && (Convert.ToInt32(dr["hasSpotReturn"])) == 0 && (Convert.ToInt32(dr["returnTime"])) <= 0)
                {
                    string insertQry = $"INSERT INTO finascop_stock_branch_inventory(stit_id, branch_id, hasSpotReturn, returnTime) " +
                    $"VALUES(@itemId, @branch, @spotReturn, @returnTime)";
                    DataServiceMySql.ExecuteSql(insertQry, Service.UserService.GetAPIConnectionString(), erpParams);

                    // Remove Redis cache entry
                    var cacheService = new RedisCacheService();
                    string cachekey = $"Retl.AppTenant.pendingtasks.count.{this.CurrentUser.APIStoreId}";
                    await cacheService.RemoveAsync(cachekey);

                    // Activitylog
                    String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                    String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                    string Source = strUrl;
                    int storegroupsid = this.CurrentUser.APIStoreId;
                    string Users = this.CurrentUser.Email;
                    int hasSpotReturn = (chkSpotReturn.Checked ? 1 : 0);
                    string brandname = txtNewBrand.Text;
                    string returnTime = txtReturnDays.Text;
                    string itemId = hidERP_stitid.Value;
                    var items = new[]
                    {
                      new { Key = "HasSpotReturn", Value = Convert.ToString(hasSpotReturn) },
                      new { Key = "Brand Name", Value =brandname},
                      new { Key = "Item Id", Value =itemId },
                      new { Key = "Return Time", Value =returnTime },
                      new { Key = "itemId", Value =Convert.ToString(itemId) },

                    };
                    string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                    var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);



                    Common.ShowToastifyMessage(Page, "Data created successfully");
                    gvProducts.DataBind();
                }
                else
                {
                    string updateSql = "UPDATE finascop_stock_branch_inventory SET hasSpotReturn=@spotReturn, returnTime=@returnTime where stit_id = @itemId AND branch_id = @branch";
                    DataServiceMySql.ExecuteSql(updateSql, Service.UserService.GetAPIConnectionString(), erpParams);
                    // Activitylog
                    String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                    String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                    string Source = strUrl;
                    int storegroupsid = this.CurrentUser.APIStoreId;
                    string Users = this.CurrentUser.Email;
                    int hasSpotReturn = (chkSpotReturn.Checked ? 1 : 0);
                    string brandname = txtNewBrand.Text;
                    string returnTime = txtReturnDays.Text;
                    string itemId = hidERP_stitid.Value;
                    var items = new[]
                    {
                      new { Key = "HasSpotReturn", Value = Convert.ToString(hasSpotReturn) },
                      new { Key = "Brand Name", Value =brandname},
                      new { Key = "Item Id", Value =itemId },
                      new { Key = "Return Time", Value =returnTime },
                      new { Key = "itemId", Value =Convert.ToString(itemId) },

                    };
                    string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                    var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);
                    Common.ShowToastifyMessage(Page, "Data updated successfully");
                    gvProducts.DataBind();
                }
                //gvProducts.DataBind();
            }

            catch (Exception ex)
            {
                Common.ShowToastifyMessage(Page, "Failure, " + ex.Message, "danger");

            }

        }


        private void ShowSuccess(string title, string content)
        {
            Type cstype = this.GetType();
            String csname1 = "PopupScript";
            ClientScriptManager cs = Page.ClientScript;
            ltrSuccessTitle.Text = title;
            ltrSuccessContent.Text = content;

            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> $('#modaldemo4').modal('show');</script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }

        //private void ShowSuccess(string title, string content, string redirect = "")
        //{
        //    Type cstype = this.GetType();
        //    String csname1 = "PopupScript";
        //    ClientScriptManager cs = Page.ClientScript;
        //    ltrSuccessTitle.Text = title;
        //    ltrSuccessContent.Text = content;

        //    StringBuilder cstext1 = new StringBuilder();
        //    cstext1.Append($"<script type=text/javascript> $('#modaldemo4').modal('show'); {(string.IsNullOrEmpty(redirect) ? "" : "$('#modaldemo4').on('hidden.bs.modal', function (e) {window.location.href = '" + redirect + "'; });")}</");
        //    cstext1.Append("script>");

        //    cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        //}

        //private void ShowSuccess(string title, string content)
        //{
        //    ltrErrorPopupTitle.Text = title;
        //    ltrErrorPopupText.Text = content;
        //    Type cstype = this.GetType();
        //    String csname1 = "PopupScript";
        //    ClientScriptManager cs = Page.ClientScript;
        //    ltrSuccessTitle.Text = title;
        //    ltrSuccessContent.Text = content;

        //    StringBuilder cstext1 = new StringBuilder();
        //    cstext1.Append("<script type=text/javascript> $('#modaldemo4').modal('show'); </");
        //    cstext1.Append("script>");

        //    cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());

        //    //    cs.RegisterClientScriptBlock(cstype, csname1, @"<script type='text/javascript'>$('#modaldemo4').on('hidden.bs.modal', function (e) {
        //    //      window.location.href='/bankaccount';
        //    //});</script>");
        //}

        protected void SDSBranch_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        }

        protected void selBrand_SelectedIndexChanged(object sender, EventArgs e)
        {
            gvProducts.PageIndex = 0;
        }
        protected void lbtnSearch_Click(object sender, EventArgs e)
        {
            gvProducts.PageIndex = 0;
        }

        protected void gvProducts_RowDataBound(object sender, GridViewRowEventArgs e)
        {
            string strGSTHeader = "GST", strMRPheader = "MRP";
            if (ConfigurationManager.AppSettings.Get("CountryCode") != "IN")
                strMRPheader = "RRP";
            if (ConfigurationManager.AppSettings.Get("VATType") != "2")
                strGSTHeader = "VAT";

            try
            {
                if (e.Row.RowType == DataControlRowType.Header)
                {
                    foreach (var headercell in e.Row.Cells)
                    {
                        if (headercell is DataControlFieldCell)
                        {
                            var headerField = ((DataControlFieldCell)headercell).ContainingField;

                            if (headerField != null)
                            {
                                if (headerField.HeaderText == "MRP")
                                    headerField.HeaderText = strMRPheader;
                                else if (headerField.HeaderText == "GST")
                                    headerField.HeaderText = strGSTHeader;
                            }
                        }
                    }
                }
            }
            catch
            {

            }

            if (e.Row.RowType == DataControlRowType.DataRow)
            {
                DataRowView rowView = (DataRowView)e.Row.DataItem;
                string stitId = rowView["stit_ID"].ToString();
                DataTable dtattribute = DataServiceMySql.GetDataTable($"SELECT * FROM `finascop_stock_itemmaster` fs INNER JOIN  attributeSubcategoryMap ap ON fs.product_category=subCategoryId INNER JOIN `attributeValue` av ON av.`attributeId`= ap.`attributeId` WHERE stit_ID={stitId} ", Service.UserService.GetAPIConnectionString());
                if (dtattribute != null && dtattribute.Rows.Count > 0)
                {
                    HtmlGenericControl divAttribute = (HtmlGenericControl)e.Row.FindControl("divAttribute");
                    if (divAttribute != null)
                    {
                        divAttribute.Visible = true; 
                    }
                }
            }

        }

        protected void selBranch_DataBound(object sender, EventArgs e)
        {
            if (selBranch.Items.Count > 1)
                selBranch.Items.Insert(0, new ListItem("Select Store", ""));
        }

        protected void btnview_Click(object sender, EventArgs e)
        {

            LinkButton lbtn = (LinkButton)sender;
            string stit_id= (lbtn.Attributes["stitId"]);
            List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
            sqldaId.Add(new KeyValuePair<string, object>("id", stit_id));
            string getproduct = "SELECT pb.brand_id,pb.brand_name,stit_category_name,pc.category_name,i.stit_id,mrp,stit_Description,stit_SKU,(SELECT image_url FROM finascop_stock_item_images WHERE product_id=i.stit_ID LIMIT 1) AS imageurl FROM  finascop_stock_itemmaster i INNER JOIN finascop_stock_branch_inventory bi ON bi.stit_id= i.stit_ID INNER JOIN mypha_productbrands pb ON i.pdt_brand=pb.brand_id INNER JOIN mypha_productsubcategory psc ON i.product_category=psc.sub_category_id INNER JOIN mypha_productcategory pc ON psc.main_category = pc.category_id where i.stit_id=@id GROUP BY stit_id";
            var dtproduct= DataServiceMySql.GetDataTable(getproduct, parmeters: sqldaId);
            if (dtproduct != null && dtproduct.Rows.Count > 0)
            {
                imgproduct.ImageUrl = RetalineProAgent.Service.Common.ImageUrl(dtproduct.Rows[0]["imageurl"].ToString());
                lblproduct.Text= dtproduct.Rows[0]["stit_SKU"].ToString();
                lbcategory.Text= dtproduct.Rows[0]["category_name"].ToString();
                lbbrand.Text= dtproduct.Rows[0]["brand_name"].ToString();
                lbmrp.Text = dtproduct.Rows[0]["mrp"].ToString();
                lbDescription.Text= dtproduct.Rows[0]["stit_Description"].ToString();
            }
            string strAlertSCript = "$('#ProductDetailesPopup').modal('show');";
            strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";
            System.Type cstype = this.GetType();
            String csname1 = "ShowConfirmPopup";
            ClientScriptManager cs = Page.ClientScript;
            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
            cstext1.Append("script>");
            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());


        }

        protected void btnattribute_Command(object sender, CommandEventArgs e)
        {
            if (e.CommandName == "ManageAttribute")
            {
                int productId= Convert.ToInt32(e.CommandArgument);
                hdnproductid.Value = productId.ToString();
                string product_id = hdnproductid.Value;
                if (!string.IsNullOrEmpty(product_id))
                {
                    DataTable dtattribute = DataServiceMySql.GetDataTable($"SELECT subCategoryId,stit_ID FROM `finascop_stock_itemmaster` fs INNER JOIN  attributeSubcategoryMap ap ON fs.product_category=subCategoryId WHERE stit_ID={product_id} ", Service.UserService.GetAPIConnectionString());
                    if (dtattribute != null && dtattribute.Rows.Count > 0)
                    {
                        string subCategoryId = dtattribute.Rows[0]["subCategoryId"].ToString();
                        HiddenSubCategoryId.Value = subCategoryId;

                    }
                }
                // Response.Redirect("/Tenant/Productattribute.aspx?productid=" + productId);
                string strAlertSCript = "$('#Popupattribute').modal('show');";
                strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";
                System.Type cstype = this.GetType();
                String csname1 = "ShowConfirmPopup";
                ClientScriptManager cs = Page.ClientScript;
                StringBuilder cstext1 = new StringBuilder();
                cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
                cstext1.Append("script>");
                cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());

            }
        }

        protected void btnpacking_Click(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;
            string stistid = lbtn.Attributes["stitId"];
            var prms = new List<KeyValuePair<string, object>> { new KeyValuePair<string, object>("id", stistid) };
            string packingmode = "SELECT PS.PackingMode AS mpacking,sc.packingMode FROM finascop_stock_itemmaster i INNER JOIN mypha_productsubcategory sc ON sc.sub_category_id = i.product_category LEFT JOIN BranchProductSettings PS ON PS.`stitId`=i.`stit_ID` WHERE  i.`stit_ID`=@id";
            DataTable dtpacking = DataServiceMySql.GetDataTable(packingmode, Service.UserService.GetAPIConnectionString(), prms);
            if (dtpacking != null && dtpacking.Rows.Count > 0)
            {
                DataRow dz = dtpacking.Rows[0];
                string editpackingmode = dz["mpacking"].ToString() != null ? dz["mpacking"].ToString() : dz["packingMode"].ToString();
                chkpackindividual.Checked = editpackingmode == "1";
                chkpackgroup.Checked = editpackingmode == "2";
                chkdefault.Checked = editpackingmode != "1" && editpackingmode != "2";
            }
            btnshowpopup.Attributes.Add("stitid", stistid);
            string strAlertSCript = "$('#modalpacking').modal('show');";
            strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";
            System.Type cstype = this.GetType();
            String csname1 = "ShowConfirmPopup";
            ClientScriptManager cs = Page.ClientScript;
            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
            cstext1.Append("script>");
            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
            ltrdeliverycost.Text = "Pack the items independently";
            ltrmannual.Text = "Pack same items together";
            ltrcanel.Text = "Group Packing";
          
        }

        protected void btnshowpopup_Click(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;
            string stitId = lbtn.Attributes["stitId"];

            var sqldaId = new List<KeyValuePair<string, object>>
            {
             new KeyValuePair<string, object>("id", stitId),
             new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId)
            };

            int dtproduct = Convert.ToInt32(DataServiceMySql.ExecuteScalar("SELECT COUNT(*) FROM BranchProductSettings WHERE stitId = @id",parmeters:sqldaId));
            string branchIdQuery = "SELECT br_ID FROM finascop_branch WHERE br_storeGroup = @storegroupid";
            string brndId = DataServiceMySql.ExecuteScalar(branchIdQuery, parmeters:sqldaId)?.ToString() ?? "0";
            int branchId = Convert.ToInt32(brndId);
            string packingMode = chkpackindividual.Checked ? "1" : chkpackgroup.Checked ? "2" : "0";
            sqldaId.Add(new KeyValuePair<string, object>("branchId", branchId));
            sqldaId.Add(new KeyValuePair<string, object>("packingmode", packingMode));
            string sqlQuery = dtproduct <= 0 ?
                "INSERT INTO BranchProductSettings (stitId, BranchId, PackingMode, StoregroupId) VALUES (@id, @branchId, @packingmode, @storegroupid)" :
                "UPDATE BranchProductSettings SET PackingMode = @packingmode WHERE stitId = @id";
            DataServiceMySql.ExecuteSql(sqlQuery, Service.UserService.GetAPIConnectionString(), sqldaId);
            Common.ShowToastifyMessage(Page, "Executed successfully");
        }

        protected void selSubCategory_DataBound(object sender, EventArgs e)
        {

        }

        protected void rbtnBrand_CheckedChanged(object sender, EventArgs e)
        {

        }
        protected void rptattribute_ItemDataBound(object sender, RepeaterItemEventArgs e)
        {
            if (e.Item.ItemType == ListItemType.Item || e.Item.ItemType == ListItemType.AlternatingItem)
            {
                var dataItem = e.Item.DataItem as DataRowView;
                if (dataItem != null)
                {

                    string product_id = hdnproductid.Value;
                    int valuemod = Convert.ToInt32(dataItem["valueMode"]);
                    ListBox ddl = e.Item.FindControl("selattributevalue") as ListBox;
                    int atrributeid = Convert.ToInt32(dataItem["attributeId"]);
                    string selectedValues = dataItem["selectedValues"].ToString();
                    HiddenField hfAttributeId = e.Item.FindControl("hfAttributeId") as HiddenField;
                    hfAttributeId.Value = atrributeid.ToString();
                    ddl.Visible = (valuemod == 1);
                    if (ddl.Visible)
                    {
                        var attribute = new List<KeyValuePair<string, object>>();
                        attribute.Add(new KeyValuePair<string, object>("atrributeid", atrributeid));
                        DataTable dtattribute = DataServiceMySql.GetDataTable($"SELECT id,attributeId,valueName FROM attributeValue WHERE attributeId = @atrributeid ORDER BY valueName", Service.UserService.GetAPIConnectionString(), parmeters: attribute);
                        ddl.DataSource = dtattribute;
                        ddl.DataBind();
                        foreach (string val in selectedValues.Split(','))
                        {
                            var ddlVal = ddl.Items.FindByValue(val);
                            if (ddlVal != null)
                                ddlVal.Selected = true;
                        }
                    }
                }
            }
        }

        protected void btnattributesave_Click(object sender, EventArgs e)
        {

            string sqlvalues = ""; int indx = 0;
            string product_id = hdnproductid.Value; 
            List<KeyValuePair<string, object>> attrsparams = new List<KeyValuePair<string, object>>() {
                new KeyValuePair<string, object>("product_id", product_id)
            };

            foreach (RepeaterItem item in rptattribute.Items)
            {
                if (item.ItemType == ListItemType.Item || item.ItemType == ListItemType.AlternatingItem)
                {
                    try
                    {
                        HiddenField hfAttributeId = item.FindControl("hfAttributeId") as HiddenField;
                        ListBox ddl = item.FindControl("selattributevalue") as ListBox;
                        if (ddl != null && hfAttributeId != null)
                        {
                            int attributeId = Convert.ToInt32(hfAttributeId.Value);
                            List<string> selectedValues = new List<string>();
                            foreach (ListItem listItem in ddl.Items)
                            {
                                if (listItem.Selected)
                                {
                                    // Build the insert values part of the sql for each selected attribute value. Increment index after the last item.
                                    attrsparams.Add(new KeyValuePair<string, object>($"{indx}_attributeId", attributeId));
                                    attrsparams.Add(new KeyValuePair<string, object>($"{indx}_attributevalue", listItem.Value));
                                    sqlvalues += (String.IsNullOrEmpty(sqlvalues) ? "" : ", ") + $"(@product_id, @{indx}_attributeId, @{indx++}_attributevalue)";
                                }
                            }
                        }
                    }
                    catch (Exception ex)
                    {
                        Common.ShowToastifyMessage(Page, "The Attribute Mapping is valid or there is a technical problem on Attribute Mapping.", "danger");
                    }
                }
            }

            if (!String.IsNullOrEmpty(sqlvalues))
            {
                // Delete existing records and insert the new selection. Execute within a transaction to roll back (deletion or insertion) if any failure.
                string insertQry = $"DELETE FROM attributeProductMap WHERE stitId=@product_id; INSERT INTO attributeProductMap(stitId, attributeId, attributeValueId) VALUES" + sqlvalues;
                var result = DataServiceMySql.ExecuteSqlWithTransaction(insertQry, Service.UserService.GetAPIConnectionString(), attrsparams);
            }

            Common.ShowCustomAlert( this.Page,"Success", "Product Attribute Successfully", true, "/Tenant/MyProducts");
        }

        protected void lnkVerify_Click(object sender, EventArgs e)
        {
            if (sender is LinkButton linkButton)
            {
                string taxValueIdStr = linkButton.Attributes["taxValue"];
                string hsnCode = linkButton.Attributes["hsncode"] ?? string.Empty;
                string tax = linkButton.Attributes["tax"] ?? string.Empty;
                string cess = linkButton.Attributes["hsnCess"] ?? string.Empty;
                string branchId = linkButton.Attributes["branchId"];
                string stitId = linkButton.Attributes["stitid"];
                hidStitId.Value = stitId;
                txtTax.Visible = true;

                bool isExistingTaxValue = int.TryParse(taxValueIdStr, out int taxValue) && taxValue > 0;

                // Mark modal to open on client side
                hdnModalOpen.Value = "1";

                txtHSNCode.Text = hsnCode;
                txtTax.Text = tax;
                txtCess.Text = cess;

                // Register startup script to show modal
                string modalScript = "$('#modalVerify').modal('show');";
                string readyScript = "$(document).ready(function () { " + modalScript + " });";

                ScriptManager.RegisterStartupScript(this, GetType(), "ShowModalVerify", readyScript, true);
            }
        }

        protected void btnHSNVerify_Click(object sender, EventArgs e)
        {
            try
            {
                    string stitId = hidStitId.Value; 
                    int storegroupid = this.CurrentUser.APIStoreId;

                    var branchParams = new List<KeyValuePair<string, object>>
                    {
                        new KeyValuePair<string, object>("storegroupid", storegroupid)
                    };

                    DataTable dtBranches = DataServiceMySql.GetDataTable("SELECT br_ID FROM finascop_branch WHERE br_storeGroup = @storegroupid", UserService.GetAPIConnectionString(),
                        branchParams
                    );

                    if (dtBranches == null || dtBranches.Rows.Count == 0)
                    {
                        Common.ShowToastifyMessage(this.Page, "No branches found for this store group.", "warning");
                        return;
                    }

                    List<string> branchIdParams = new List<string>();
                    List<KeyValuePair<string, object>> updateParams = new List<KeyValuePair<string, object>>
                    {
                        new KeyValuePair<string, object>("itemId", stitId),
                        new KeyValuePair<string, object>("hsn", string.IsNullOrWhiteSpace(txtHSNCode.Text) ? "0" : txtHSNCode.Text),
                        new KeyValuePair<string, object>("gst", string.IsNullOrWhiteSpace(txtTax.Text) ? "0" : txtTax.Text),
                        new KeyValuePair<string, object>("cess", string.IsNullOrWhiteSpace(txtCess.Text) ? "0" : txtCess.Text),
                    };

                    int index = 0;
                    foreach (DataRow row in dtBranches.Rows)
                    {
                        string paramName = "@branchId" + index;
                        branchIdParams.Add(paramName);
                        updateParams.Add(new KeyValuePair<string, object>(paramName.Substring(1), Convert.ToInt32(row["br_ID"])));
                        index++;
                    }

                    string inClause = string.Join(",", branchIdParams);
                    string updateSql = $@"UPDATE finascop_stock_branch_inventory SET hsnCode = @hsn, taxValue = @gst, cessValue = @cess WHERE stit_id = @itemId AND branch_id IN ({inClause})";
                    DataServiceMySql.ExecuteSql(updateSql, UserService.GetAPIConnectionString(), updateParams);
                    Common.ShowToastifyMessage(Page, "HSN Verified Successfully!!");
                    txtHSNCode.Text = "";
                    txtTax.Text = "";
                    txtCess.Text = "";
                    stitId = string.Empty;
                    hdnModalOpen.Value = "0";
                    int currentPage = gvProducts.PageIndex;
                    gvProducts.PageIndex = currentPage;
                    gvProducts.DataBind();
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(this.Page, "An error occurred: " + ex.Message, "danger");
            }
        }
        

        //protected void selHSN_SelectedIndexChanged(object sender, EventArgs e)
        //{
        //    hdnModalOpen.Value = "1"; 
        //    selType.Items.Clear();
        //    txtTax.Text = "";
        //    txtCess.Text = "";

        //    string hsnId = selHSN.SelectedValue;
        //    if (string.IsNullOrEmpty(hsnId)) return;

        //    var param = new List<KeyValuePair<string, object>>
        //    {
        //        new KeyValuePair<string, object>("hsnId", hsnId)
        //    };

        //    DataTable dt = DataServiceMySql.GetDataTable("SELECT id, hsnGst, hsnCess FROM hsn_value WHERE hsnId = @hsnId ORDER BY id", Service.UserService.GetAPIConnectionString(), param);

        //    if (dt == null || dt.Rows.Count == 0)
        //    {
        //        selType.Visible = false;
        //        txtTax.Visible = false;
        //        return;
        //    }

        //    if (dt.Rows.Count == 1)
        //    {
        //        string gst = dt.Rows[0]["hsnGst"].ToString();
        //        string cess = dt.Rows[0]["hsnCess"].ToString();
        //        string id = dt.Rows[0]["id"].ToString();

        //        selType.Items.Add(new ListItem(gst, id));
        //        selType.SelectedIndex = 0;

        //        txtCess.Text = cess;
        //        txtTax.Text = gst;

        //        selType.Visible = false;
        //        txtTax.Visible = true;
        //        txtTax.Enabled = false;
        //    }
        //    else
        //    {
        //        selType.DataSource = dt;
        //        selType.DataTextField = "hsnGst";
        //        selType.DataValueField = "id";
        //        selType.DataBind();

        //        selType.Visible = true;
        //        txtTax.Visible = false;
        //    }
        //}

        //protected void selType_SelectedIndexChanged(object sender, EventArgs e)
        //{
        //    hdnModalOpen.Value = "1"; 
        //    txtCess.Text = "";

        //    string selectedId = selType.SelectedValue;
        //    if (string.IsNullOrEmpty(selectedId)) return;

        //    var param = new List<KeyValuePair<string, object>>
        //    {
        //        new KeyValuePair<string, object>("id", selectedId)
        //    };

        //    DataTable dt = DataServiceMySql.GetDataTable("SELECT hsnCess FROM hsn_value WHERE id = @id", Service.UserService.GetAPIConnectionString(), param);

        //    if (dt != null && dt.Rows.Count > 0)
        //    {
        //        txtCess.Text = dt.Rows[0]["hsnCess"].ToString();
        //    }
        //}

        //protected void selType_DataBound(object sender, EventArgs e)
        //{
        //    selType.Items.Insert(0, new ListItem("Select Tax", ""));
        //}

        protected string GetVerifyText(object taxValue)
        {
            if (taxValue != null && taxValue != DBNull.Value)
                return "VERIFIED";
            return "VERIFY";
        }

        protected string GetVerifyClass(object taxValue)
        {
            if (taxValue != null && taxValue != DBNull.Value)
                return "hcn-verify-btn bg-success"; 
            return "hcn-verify-btn bg-danger"; 
        }
    }
}