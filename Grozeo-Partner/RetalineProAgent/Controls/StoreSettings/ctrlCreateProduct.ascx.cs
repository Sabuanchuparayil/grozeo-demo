using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Configuration;
using System.IO;
using static NPOI.HSSF.Util.HSSFColor;
using RetalineProAgent.Core.Services.HelperServices;
using RestSharp;
using Newtonsoft.Json;
using System.Text.RegularExpressions;
using NPOI.SS.Formula.Functions;
using Amazon.DynamoDBv2.Model;
using RetalineProAgent.Core.Services.ActiveLog;
using StackExchange.Redis;
using NPOI.SS.Formula.Atp;
using RetalineProAgent.Core.Services.Cache;

namespace RetalineProAgent.Controls.StoreSettings
{
    public partial class ctrlCreateProduct : Base.BasePartnerUserControl
    {
        public delegate void ParentAddProductHandler(int status, int productid);
        public delegate void ParentAddBrandHandler(int status);
        public delegate void ParentMessageHandler(string title, string msg, int type);

        public event ParentAddProductHandler ParentAddProductBinding;
        public event ParentAddBrandHandler ParentAddBrandBinding;
        public event ParentAddProductHandler ParentCancelAddProductBinding;

        public event ParentMessageHandler ParentMessageBinding;

        public enum ViewMode
        {
            Edit = 1,
            Duplicate = 2,
            New = 0
        }
        public ViewMode ViewType
        {
            get
            {
                if (ViewState["VIEWMODE"] != null)
                    return (ViewMode)ViewState["VIEWMODE"];

                return ViewMode.New;
            }
            set
            {
                ViewState["VIEWMODE"] = value;
            }
        }
        public int EditProdId
        {
            get
            {
                return (int)ViewState["ISEDITPRODID"];
            }
            set
            {
                ViewState["ISEDITPRODID"] = value;
            }
        }
        public int VarientGroupId
        {
            get
            {
                if (ViewState["VARIENTGROUP"] != null)
                    return (int)ViewState["VARIENTGROUP"];
                else
                    return 0;
            }
            set
            {
                ViewState["VARIENTGROUP"] = value;
            }
        }
        protected bool groupVariant = false;
        protected void Page_Load(object sender, EventArgs e)
        {
            //if (!IsPostBack)
            //{                                              
            //    // If none GST then allow None GST categories only.
            //    //if (CurrentUser.TenantStatus == 2)
            //    //{
            //    //    SDSRetCat.SelectCommand = $"SELECT gbt.store_group_id, gbt.business_type_id, fgb.store_group_name, bt.business_type_name FROM finascop_branch_group_business_type gbt " +
            //    //        $"INNER JOIN finascop_branch_group fgb ON fgb.store_group_id=gbt.store_group_id INNER JOIN finascop_business_type bt ON bt.business_type_id=gbt.business_type_id " +
            //    //        $"WHERE gbt.store_group_id=@storegroup AND bt.business_type_id IN(SELECT ppc.parent_category_businessType FROM mypha_productparent_category ppc INNER JOIN mypha_productcategory pc ON pc.parent_category=ppc.parent_category_id " +
            //    //        $"INNER JOIN mypha_productsubcategory msc ON pc.category_id=msc.main_category  " +
            //    //        $"WHERE pc.status='1' AND msc.status=1 AND isNonGSTRetailer = 1) ORDER BY bt.business_type_name";

            //    //    SDSCat.SelectCommand = $"SELECT pc.category_id,pc.category_name,ppc.parent_category_businessType FROM mypha_productcategory pc " +
            //    //        $"INNER JOIN mypha_productparent_category ppc ON pc.parent_category=ppc.parent_category_id WHERE ppc.parent_category_businessType=@bussinessType AND pc.status='1' " +
            //    //        $"AND ppc.parent_category_id IN (SELECT pc.parent_category FROM mypha_productcategory pc INNER JOIN mypha_productsubcategory msc ON pc.category_id=msc.main_category " +
            //    //        $"WHERE pc.status='1' AND msc.status=1 AND isNonGSTRetailer = 1)";

            //    //    SDSSubCat.SelectCommand = $"SELECT msc.sub_category_id,msc.sub_category,msc.main_category,pc.category_name,pc.category_id FROM mypha_productsubcategory msc " +
            //    //        $"INNER JOIN mypha_productcategory pc ON pc.category_id=msc.main_category WHERE msc.main_category=@catName AND msc.status=1 AND msc.isNonGSTRetailer = 1";
            //    //}
            //}

            lblProductNameResult.Text = "";
            ltrAddBrandResult.Text = "";

            if (!IsPostBack)
            {
                txtPrdName.Enabled = selFoodType.Enabled = ViewType != ViewMode.Duplicate;
                selQuantity.DataBind();
                int brandId = -1;
                if (!String.IsNullOrEmpty(Request.QueryString["brandId"]))
                    try { brandId = Convert.ToInt32(Request.QueryString["brandId"]); } catch { brandId = 0; }

                if (brandId > 0)
                {
                    DataTable dt = DataServiceMySql.GetDataTable($"SELECT brand_id, brand_name FROM mypha_productbrands WHERE brand_id = {brandId}", Service.UserService.GetAPIConnectionString());
                    if (dt == null || dt.Rows.Count <= 0)
                    {
                        Common.ShowCustomAlert(this.Page, "Loading failed", "Invalid selection or the record is not existing", false, "/Tenant/BrandProduct");
                        return;
                    }

                    DataRow da = dt.Rows[0];
                    txtSelectedBrand.Text = da["brand_name"].ToString();
                }

                if (ViewType == ViewMode.Edit || ViewType == ViewMode.Duplicate)
                    LoadProductInfo();

                RestoreFormFromSession();

                // Clear all sessions
                ClearFormSessionKeys();
            }
            //if (IsPostBack)
            //{
            //    txtProductWebName.Text = "";
            //    if (txtSelectedBrand.Text == "Generic")
            //    {
            //        txtProductWebName.Text = String.Format("{0} {1} {2} {3}", txtPrdName.Text, txtVarient.Text, txtQuantity.Text, selUnit.SelectedItem.Text);
            //    }
            //    else
            //    {
            //        txtProductWebName.Text = String.Format("{0} {1} {2} {3} {4}", txtSelectedBrand.Text, txtPrdName.Text, txtVarient.Text, txtQuantity.Text, selUnit.SelectedItem.Text);
            //    }
            //}

            //string brandId = Request.QueryString["brandId"];


            List<KeyValuePair<string, object>> subPrdParams = new List<KeyValuePair<string, object>>();
            subPrdParams.Add(new KeyValuePair<string, object>("subcatId", selSubCat.Text));
            subPrdParams.Add(new KeyValuePair<string, object>("perishable", 1));
            DataTable dtSubPrd = DataServiceMySql.GetDataTable($"SELECT COUNT(*) AS cnt FROM mypha_productsubcategory WHERE isPerishable=@perishable AND sub_category_id=@subcatId", Service.UserService.GetAPIConnectionString(), subPrdParams);
            int subPrdCount = 0;
            if (dtSubPrd != null && dtSubPrd.Rows.Count > 0)
            {
                DataRow da = dtSubPrd.Rows[0];
                subPrdCount = Convert.ToInt32(da["cnt"]);
            }
            if (subPrdCount > 0)
            {
                // selDelMode.Items.FindByValue("1").Enabled = true;
                // selDelMode.Items.FindByValue("3").Enabled = true;
                selDelMode.SelectedValue = "2";
                rfvDelivMode.Visible = false;
            }
            else
            {
                selDelMode.Items.FindByValue("1").Enabled = true;
                selDelMode.Items.FindByValue("3").Enabled = true;
                selDelMode.Enabled = true;
                selDelMode.Enabled = true;
                rfvDelivMode.Visible = true;
            }
        }

        private void RestoreFormFromSession()
        {
            if (Session["txtPrdName"] != null)
                txtPrdName.Text = Session["txtPrdName"].ToString();

            if (Session["txtVarient"] != null)
                txtVarient.Text = Session["txtVarient"].ToString();

            if (Session["rbQtyChecked"] != null)
                rbQty.Checked = Convert.ToBoolean(Session["rbQtyChecked"]);

            if (Session["rbSizeChecked"] != null)
                rbSize.Checked = Convert.ToBoolean(Session["rbSizeChecked"]);

            if (Session["txtQuantity"] != null)
                txtQuantity.Text = Session["txtQuantity"].ToString();

            if (Session["selUnitText"] != null)
            {
                ListItem item = selUnit.Items.FindByText(Session["selUnitText"].ToString());
                if (item != null)
                    selUnit.SelectedValue = item.Value;
            }

            if (Session["txtDisplayQty"] != null)
                txtDisplayQty.Text = Session["txtDisplayQty"].ToString();

            if (Session["txtProductWebName"] != null)
                txtProductWebName.Text = Session["txtProductWebName"].ToString();
        }

        protected void Page_PreRender(object sender, EventArgs e)
        {
            selUnit.Enabled = !rbSize.Checked || rbQty.Checked;           
            pnlQuantityInput.Visible = !pnlQuantitySelect.Visible;

        }
        protected void SDSRetCat_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
            //if (CurrentUser.TenantType == 2)
            //    e.Command.Parameters["isNoGST"].Value = 1;
        }

        protected void btnCancelSaveProduct_Click(object sender, EventArgs e)
        {
            ParentCancelAddProductBinding(-1, -1);
            //hidCurTab.Value = "1";
        }


        protected void selBrd_DataBound(object sender, EventArgs e)
        {
            //selBrd.Items.Insert(0, new ListItem("Select Brand", ""));
        }
        protected void btnAddBrand_Click(object sender, EventArgs e)
        {
            if (String.IsNullOrEmpty(txtBrand.Text))
            {
                ltrAddBrandResult.Text = "Please enter brand name and manufacturer.";
                //ShowAddBrandPopup();
                if (ParentAddBrandBinding != null)
                    ParentAddBrandBinding(0);
                else
                    Common.ShowToastifyMessage(this.Page, "Please enter brand name and manufacturer.");
                return;
            }

            var brandParams = new List<KeyValuePair<string, object>>();
            brandParams.Add(new KeyValuePair<string, object>("brandname", txtBrand.Text));
            brandParams.Add(new KeyValuePair<string, object>("manufacture", txtManufacturer.Text));
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
                        //SDSBrand.Select(DataSourceSelectArguments.Empty);
                        //selBrand.DataBind();
                        //selBrd.DataBind();
                        //var bd = selBrd.Items.FindByValue(brandid.ToString());
                        //selBrand.ClearSelection();
                        //if (bd != null)
                        //{
                        //    selBrd.SelectedValue = bd.Value;
                        //    //selBrd.Items.FindByValue(brandid.ToString()).Selected = true;
                        //}
                        count = 1;
                        Common.ShowToastifyMessage(Page, (isnew > 0 ? "Brand created successfully!" : "Brand name is already existing. It is selected in the brand select box to continue"), (isnew > 0 ? "success" : "info"));
                    }
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
                //ltrAddBrandResult.Text = "There is a technical problem on creating brand.";
                //ShowAddBrandPopup();
            }

        }
        private async void AddProduct()
        {
            string brandId = (ViewType == ViewMode.Duplicate) ? Request.QueryString["id2"] : Request.QueryString["brandId"];

            if (!Page.IsValid)
            {
                Common.ShowToastifyMessage(Page, "Failure. Please ensure all required input values are provided", "danger");
                return;
            }

            if (selDelMode.Items.FindByValue("1").Enabled == true && selDelMode.Items.FindByValue("3").Enabled == true && selDelMode.Enabled == true && selDelMode.Enabled == true && rfvDelivMode.Visible == true)
            {
                if (String.IsNullOrEmpty(selDelMode.Text))
                {
                    Common.ShowToastifyMessage(Page, "Failure. Please select delivery type", "danger");
                    //ltrResult.Text = "Please select delivery type";
                    return;
                }
            }
            int courierDeliv = 0, directDeliv = 0;
            int deliveryType = Convert.ToInt32(selDelMode.SelectedValue);

            switch (deliveryType)
            {
                case 1:
                    courierDeliv = 1;
                    break;
                case 2:
                    directDeliv = 1;
                    break;
                case 3:
                    courierDeliv = directDeliv = 1;
                    break;
            }


            int spotReturn = new int();
            if (chkSpotReturn.Checked)
            {
                spotReturn = 1;
            }
            else
            {
                spotReturn = 0;
            }
            string checkSpotReturn = Convert.ToString(spotReturn);

            int returnDays = 0;
            if (txtReturn.Text == "")
            {
                returnDays = 0;
            }
            else
            {
                returnDays = Convert.ToInt32(txtReturn.Text);
            }
            int branchid = 0;
            List<KeyValuePair<string, object>> brandParams = new List<KeyValuePair<string, object>>();
            brandParams.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.APIStoreId));
            DataTable branchaTable = DataServiceMySql.GetDataTable($"SELECT br_ID FROM finascop_branch WHERE br_storeGroup = @tenantId LIMIT 1", Service.UserService.GetAPIConnectionString(), brandParams);
            if (branchaTable != null && branchaTable.Rows.Count > 0)
            {
                DataRow da = branchaTable.Rows[0];
                branchid = Convert.ToInt32(da["br_ID"].ToString());
            }
            string strQuantity = (pnlQuantitySelect.Visible ? selQuantity.SelectedItem.Value : txtQuantity.Text);
            string strqty = (pnlQuantitySelect.Visible ? selQuantity.SelectedItem.Text : txtQuantity.Text);
            string sku = "";
            if (String.IsNullOrEmpty(txtProductWebName.Text))
                txtProductWebName.Text = String.Format("{0} {1} {2} {3} {4}", (txtSelectedBrand.Text == "Generic" ? "" : txtSelectedBrand.Text), txtPrdName.Text, txtVarient.Text, strQuantity, selUnit.SelectedItem.Text).Trim();

            if (txtSelectedBrand.Text == "Generic")
                sku = txtProductWebName.Text.Replace("Generic", string.Empty);
            else
                sku = txtProductWebName.Text;
            string displayQuantity = strqty + " " + selUnit.SelectedItem.Text;
            List<KeyValuePair<string, object>> sqlPrdParams = new List<KeyValuePair<string, object>>();
            sqlPrdParams.Add(new KeyValuePair<string, object>("sku", sku));
            sqlPrdParams.Add(new KeyValuePair<string, object>("businesstypeid", selRetCat.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("categoryid", selCat.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("subcategoryid", selSubCat.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("brand", brandId));
            sqlPrdParams.Add(new KeyValuePair<string, object>("variant", txtVarient.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("quantity", strQuantity));
            sqlPrdParams.Add(new KeyValuePair<string, object>("unit", selUnit.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("hsncode", selHSN.Text));
            string rawGst = !string.IsNullOrWhiteSpace(selType.SelectedItem.Text) ? selType.SelectedItem.Text : txtTax.Text.Trim();

            double gstValue;
            if (!double.TryParse(rawGst.Replace("%", "").Trim(), out gstValue))
            {
                gstValue = 0; 
            }

            sqlPrdParams.Add(new KeyValuePair<string, object>("gst", gstValue));
            sqlPrdParams.Add(new KeyValuePair<string, object>("productName", txtPrdName.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("hsnCodeSelected", selHSN.SelectedItem.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("returndays", returnDays));
            sqlPrdParams.Add(new KeyValuePair<string, object>("foodtype", selFoodType.SelectedValue));
            sqlPrdParams.Add(new KeyValuePair<string, object>("countryid", selCountry.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("courierDelivery", courierDeliv));
            sqlPrdParams.Add(new KeyValuePair<string, object>("directDelivery", directDeliv));
            sqlPrdParams.Add(new KeyValuePair<string, object>("shortdescription", txtShortDescription.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("longdescription", summernote.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("categoryName", selSubCat.SelectedItem.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("brandName", txtSelectedBrand.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.APIStoreId));
            sqlPrdParams.Add(new KeyValuePair<string, object>("spotReturn", checkSpotReturn));
            sqlPrdParams.Add(new KeyValuePair<string, object>("barcode", txtBarcode.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("taxValue", selType.SelectedValue));
            sqlPrdParams.Add(new KeyValuePair<string, object>("courierWt", txtpackageweigt.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("qty", displayQuantity));
            sqlPrdParams.Add(new KeyValuePair<string, object>("packageLength", txtPackageLength.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("packageWidth", txtPackageWidth.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("packageHeight", txtPackageHeight.Text));
            double mrp = 0, stock = 0, sellingprice = 0, discount = 0;
            //try { mrp = Convert.ToDouble(mrprrp.Text); } catch { mrp = 0; }
            //try { stock = Convert.ToDouble(newquantity.Text); } catch { stock = 0; }
            //try { discount = Convert.ToDouble(newPercentage.Text); } catch { discount = 0; }
            int groupId = 0;
            if (ViewType == ViewMode.Duplicate && groupVariant)
            {
                if (VarientGroupId <= 0)
                {
                    string sqlSetVariant = @"
                        insert into product_group(`Name`, `StoreGroupId`, `CreatedBy`, brandId) values(@name, @store, @user, @brandId); 
                        UPDATE finascop_stock_branch_inventory SET variantGroupId = LAST_INSERT_ID() WHERE stit_id= @id AND branch_id IN(SELECT br_ID FROM finascop_branch WHERE br_storegroup = @store); 
                        select LAST_INSERT_ID() as variantGroupId";
                    DataTable dtGrouipId = DataServiceMySql.GetDataTable(sqlSetVariant, UserService.GetAPIConnectionString(),
                      parmeters: new List<KeyValuePair<string, object>>() {
                        new KeyValuePair<string, object>("id", Request.QueryString["id"]), new KeyValuePair<string, object>("name", txtPrdName.Text),
                        new KeyValuePair<string, object>("user", this.CurrentUser.Id), new KeyValuePair<string, object>("store", this.CurrentUser.APIStoreId),
                        new KeyValuePair<string, object>("brandId", brandId)
                          //,new KeyValuePair<string, object>("groupid", 0)
                      });
                    if (dtGrouipId.Rows.Count > 0)
                    {
                        try { groupId = Convert.ToInt32(dtGrouipId.Rows[0][0]); } catch { groupId = 0; }
                    }
                }
                else
                {
                    groupId = VarientGroupId;
                }
            }
            sqlPrdParams.Add(new KeyValuePair<string, object>("groupID", groupId));
            sellingprice = mrp;
            try
            {
                if (mrp > 0 && discount > 0)
                    sellingprice = mrp - ((mrp * discount) / 100);
            }
            catch { }
            sqlPrdParams.Add(new KeyValuePair<string, object>("itemmrp", mrp));
            sqlPrdParams.Add(new KeyValuePair<string, object>("stock", stock));
            sqlPrdParams.Add(new KeyValuePair<string, object>("sellingprice", sellingprice));
            try
            {
                DataTable dtResult = DataServiceMySql.GetDataTable("addPrivateProduct", UserService.GetAPIConnectionString(), sqlPrdParams, true);

                //SDSProducts.Select(DataSourceSelectArguments.Empty);
                //lstProducts.DataBind();
                //SDSSelectedProducts.Select(DataSourceSelectArguments.Empty);
                //lstSelectedProducts.DataBind();
                int stitid = 0;
                if (dtResult != null && dtResult.Rows.Count > 0)
                {
                    stitid = Convert.ToInt32(dtResult.Rows[0]["stit_id"]);
                    int productId = stitid;
                    UploadImages(stitid);
                    string productIndiaResult = Core.Services.APIService.ProductIndia(productId);
                }

                // Remove Redis cache entry
                var cacheService = new RedisCacheService();
                string cachekey = $"Retl.AppTenant.pendingtasks.count.{this.CurrentUser.APIStoreId}";
                await cacheService.RemoveAsync(cachekey);

                // Activitylog
                String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                string Source = strUrl;
                int storegroupid = this.CurrentUser.APIStoreId;
                string Users = this.CurrentUser.Email;
                string storegroup_id = (this.CurrentUser.APIStoreId).ToString();
                string Productname = sku;
                string businesstypeid = selRetCat.Text;
                string subcategoryid = selSubCat.Text;
                string brand = brandId;
                string variant = txtVarient.Text;
                string quantity = strQuantity;
                string unit = selUnit.Text;
                string hsncode = selHSN.Text;
                string hsngst = selType.SelectedItem.Text;
                string productName = txtPrdName.Text;
                string hsnCode = selHSN.SelectedItem.Text;
                string Returndays = returnDays.ToString();
                string Foodtype = selFoodType.SelectedValue;
                string Countryid = selCountry.Text;
                string CourierDelivery = courierDeliv.ToString();
                string DirectDelivery = directDeliv.ToString();
                string Shortdescription = txtShortDescription.Text;
                string longdescription = summernote.Text;
                string CategoryName = selSubCat.SelectedItem.Text;
                string BrandName = txtSelectedBrand.Text;
                string SpotReturn = checkSpotReturn;
                string barcode = txtBarcode.Text;
                 string taxValue = selType.SelectedValue;
                string courierWt = txtpackageweigt.Text;

                int tenantId = this.CurrentUser.APIStoreId;
                var items = new[]
                {
                      new { Key = "Productname", Value = Productname },
                      new { Key = "Businesstype Id", Value =businesstypeid },
                      new { Key = "subcategory Id", Value =subcategoryid },
                      new { Key = "Brandid", Value =brand },
                      new { Key = "variant", Value =variant },
                      new { Key = "Quantity", Value =quantity },
                      new { Key = "Unit", Value =unit },
                      new { Key = "hsncode", Value =hsncode },
                      new { Key = "hsngst", Value =hsngst },
                      new { Key = "ProductName", Value =productName },
                      new { Key = "hsnCode", Value =hsnCode },
                      new { Key = "Returndays", Value =Returndays },
                      new { Key = "Foodtype", Value =Foodtype },
                      new { Key = "Country Id", Value =Countryid },
                      new { Key = "CourierDelivery", Value =CourierDelivery },
                      new { Key = "DirectDelivery", Value =DirectDelivery },
                      new { Key = "Shortdescription", Value =Shortdescription },
                      new { Key = "longdescription", Value =longdescription },
                      new { Key = "CategoryName", Value =CategoryName },
                      new { Key = "BrandName", Value =BrandName },
                      new { Key = "SpotReturn", Value =SpotReturn },
                      new { Key = "barcode", Value =barcode },
                      new { Key = "TaxValue", Value =taxValue },
                      new { Key = "courierWt", Value =courierWt },
                    };
                string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);//end of Activitylog
                //string packingMode = rbpackindependently.Checked ? "1" :
                //                    rbpackingtogather.Checked ? "2" : "0";
                string packingMode = ddlPackingType.SelectedValue;
                sqlPrdParams.Add(new KeyValuePair<string, object>("br_id", branchid));
                sqlPrdParams.Add(new KeyValuePair<string, object>("stirid", stitid));
                sqlPrdParams.Add(new KeyValuePair<string, object>("packingmode", packingMode));
                string packingmode = "INSERT INTO BranchProductSettings (stitId,BranchId,PackingMode,StoregroupId) Values(@stirid,@br_id,@packingmode,@tenantId)";
                if (!String.IsNullOrEmpty(packingmode))
                    DataServiceMySql.ExecuteSql(packingmode, UserService.GetAPIConnectionString(), sqlPrdParams);
                //hidCurTab.Value = "0";
                //Common.ShowToastifyMessage(Page, "Product added to your list of items");

                ParentAddProductBinding(1, stitid);
            }
            catch (Exception ex)
            {
                if (ex.Message.EndsWith("for key 'NewIndex1'"))
                {
                    ParentMessageBinding("Validation failed", "Product name is already existing. You can find the product from the select product from gallery listing to add stock. If you still want to continue, please enter a new name.", 2);
                    //Common.ShowToastifyMessage(this.Page, "Product name is already existing. You can find the product from the select product from gallery listing to add stock. If you still want to continue, please enter a new name.", "danger");
                    lblProductNameResult.Text = "Duplicate name";
                }
                else
                {
                    ParentMessageBinding("Operation failed", "Error: " + "Failed to create your product due to some technical issue. Please try again later", 2);
                    //ParentMessageBinding("Operation failed", "Error: " + "Product name is already existing", 2);
                    //Common.ShowToastifyMessage(this.Page, "Error: " + ex.Message, "danger");
                }
            }
        }
        protected void btnAddProduct_Click(object sender, EventArgs e)
        {



        }

        private void UploadImages(int productid)
        {
            if (productid < 0)
                return;

            List<KeyValuePair<string, object>> sqlInsertParams = new List<KeyValuePair<string, object>>();
            sqlInsertParams.Add(new KeyValuePair<string, object>("prodid", productid));
            sqlInsertParams.Add(new KeyValuePair<string, object>("imgFolder", "products/"));
            string insertSql = ""; bool selectedDefault = false;
            List<string> lstDeleteFiles = new List<string>();
            for (int i = 1; i <= 5; i++)
            {
                if (ViewType == ViewMode.Edit || ViewType == ViewMode.Duplicate)
                {
                    Image img = (Image)this.FindControl("productImg" + i.ToString());
                    Label lbl = (Label)this.FindControl("lblProd" + i.ToString());
                    HiddenField hidpid = (HiddenField)this.FindControl("hidProdImg" + i.ToString());
                    //if(lbl != null && lbl.Attributes["deleted"] == "1")

                    if (img != null && !String.IsNullOrEmpty(img.Attributes["imgid"]) && hidpid.Value == img.Attributes["imgid"])
                    {
                        lstDeleteFiles.Add(img.ImageUrl);
                        sqlInsertParams.Add(new KeyValuePair<string, object>($"img{i}Id", img.Attributes["imgid"]));
                        insertSql += $" DELETE FROM finascop_stock_item_images WHERE id= @img{i}Id and product_id= @prodid; ";
                    }
                }

                FileUpload fileUpload = (FileUpload)this.FindControl("imgUpload" + i.ToString());
                if (fileUpload != null && fileUpload.HasFile)
                {
                    string strFile = FileService.UploadImage(fileUpload.PostedFile.InputStream, fileUpload.FileName);
                    if (!string.IsNullOrEmpty(strFile))
                    {
                        sqlInsertParams.Add(new KeyValuePair<string, object>("imgUrl" + i.ToString(), strFile));
                        int imageType = (i > 1 ? 0 : 1);
                        sqlInsertParams.Add(new KeyValuePair<string, object>("imgType" + i.ToString(), imageType));
                        if (!selectedDefault) { imageType = 0; selectedDefault = true; }
                        insertSql += @" INSERT INTO finascop_stock_item_images(product_id, image_url, image_folder, image_type, created_at)
                                            VALUES(@prodid, @imgUrl" + i.ToString() + ", @imgFolder, @imgType" + i.ToString() + ", NOW()); ";

                    }

                }

            }

            if (!String.IsNullOrEmpty(insertSql))
                DataServiceMySql.ExecuteSql(insertSql, UserService.GetAPIConnectionString(), sqlInsertParams);
            foreach (var strUrl in lstDeleteFiles)
            {
                Core.Services.FileService.DeleteImage(strUrl.Replace(ConfigurationManager.AppSettings.Get("ImageLocation"), ""));
            }
        }

        private void LoadProductInfo(bool stopIfInvalid = true)
        {
            //int id = Convert.ToInt32(Request.QueryString["id"]);

            if (EditProdId > 0)
            {
                string selectSql = @"SELECT i.stit_itemId, i.stit_itemBarcode, i.stit_GST, i .taxValueId, i.stit_custInitiate,sc.`packingMode`,
                 i.stit_itemReturnTime,i.stit_SKU,i.stit_HSNCode, i.stit_GST,i.stit_Description,i.stit_product_variant,
                i.pdt_brand,i.product_category,i.stit_quantity,i.stit_long_description,i.stit_itemName,i.stit_HSN_code,i.stit_hsnId,
                 i.stit_category_name,i.stit_brand_name,i.courierDelivery,
                i.directDelivery,i.stit_foodtype,i.stit_orgin_country,i.stit_unit,IF((SELECT COUNT(*) FROM unit_value WHERE unitId = stit_unit) > 0,(SELECT `id` FROM unit_value WHERE id = stit_qty) ,i.stit_qty) AS unitvalue,i.stit_qty,i.stit_StoreGroup, i.stit_fsiuid, i.stit_MRP,i.stit_courierWt,
                pc.parent_category, c.category_id,sc.sub_category_id,pc.parent_category_id,ps.PackingMode AS mPackingMode,pc.parent_category_businessType,
                 IFNULL(g.id, 0) AS variantGroupId, g.`name` AS variantGroupName, i.item_length, i.item_breadth, i.item_height
                FROM finascop_stock_itemmaster i INNER JOIN mypha_productsubcategory sc ON sc.sub_category_id = i.product_category INNER JOIN mypha_productcategory c ON c.category_id = sc.main_category 
                INNER JOIN mypha_productparent_category pc ON pc.parent_category_id=c.parent_category LEFT JOIN (SELECT pg.id, pg.`Name`, bi.stit_id FROM product_group pg 
                INNER JOIN finascop_stock_branch_inventory bi ON bi.variantGroupId = pg.id  WHERE StoreGroupId=@storegroupid AND bi.stit_id=@stitid AND IFNULL(bi.variantGroupId, 0) > 0 GROUP BY bi.stit_id LIMIT 1) g ON g.stit_id=i.stit_id
                LEFT JOIN `BranchProductSettings` ps ON ps.`stitId`=i.`stit_ID` WHERE i.stit_Id=@stitid LIMIT 1";

                List<KeyValuePair<string, object>> selectParams = new List<KeyValuePair<string, object>>();
                selectParams.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));
                selectParams.Add(new KeyValuePair<string, object>("stitid", EditProdId));
                DataTable dataTable = DataServiceMySql.GetDataTable(selectSql, Service.UserService.GetAPIConnectionString(), selectParams);

                if (dataTable == null || dataTable.Rows.Count <= 0)
                {
                    ParentMessageBinding("Invalid Product", "The product is invalid or you do not have access to the product.", 3);
                    return;
                }

                DataRow da = dataTable.Rows[0];

                txtPrdName.Text = da["stit_itemName"].ToString();
                txtVarient.Text = da["stit_product_variant"].ToString();
                // txtQuantity.Text = da["stit_qty"].ToString();
                //selType.Text = da["stit_GST"].ToString();
                txtShortDescription.Text = da["stit_Description"].ToString();
                txtReturn.Text = da["stit_itemReturnTime"].ToString();
                string isReturn = da["stit_custInitiate"].ToString();
                chkSpotReturn.Checked = (isReturn == "1");
                summernote.Text = da["stit_long_description"].ToString();
                //txtBarcode.Text = da["stit_itemBarcode"].ToString();
                txtSelectedBrand.Text = da["stit_brand_name"].ToString();
                txtProductWebName.Text = da["stit_SKU"].ToString();
                txtpackageweigt.Text = da["stit_courierWt"].ToString();
                txtDisplayQty.Text = da["stit_quantity"].ToString();
                txtPackageLength.Text = da["item_length"].ToString();
                txtPackageHeight.Text = da["item_height"].ToString();
                txtPackageWidth.Text = da["item_breadth"].ToString();

                if (selRetCat.Items.Count <= 1)
                    selRetCat.DataBind();
                if (selRetCat.Items.FindByValue(da["parent_category_businessType"].ToString()) != null)
                    selRetCat.Text = da["parent_category_businessType"].ToString();

                if (selDepartment.Items.Count <= 1)
                    selDepartment.DataBind();
                if (selDepartment.Items.FindByValue(da["parent_category_id"].ToString()) != null)
                    selDepartment.Text = da["parent_category_id"].ToString();

                if (selCat.Items.Count <= 1)
                    selCat.DataBind();
                if (selCat.Items.FindByValue(da["category_id"].ToString()) != null)
                    selCat.Text = da["category_id"].ToString();

                if (selSubCat.Items.Count <= 1)
                    selSubCat.DataBind();
                if (selSubCat.Items.FindByValue(da["sub_category_id"].ToString()) != null)
                    selSubCat.Text = da["sub_category_id"].ToString();

                //if (selBrd.Items.Count <= 1)
                //    selBrd.DataBind();
                //if (selBrd.Items.FindByValue(da["pdt_brand"].ToString()) != null)
                //    selBrd.Text = da["pdt_brand"].ToString();

                if (selFoodType.Items.FindByValue(da["stit_foodtype"].ToString()) != null)
                    selFoodType.Text = da["stit_foodtype"].ToString();

                if (selUnit.Items.Count <= 1)
                    selUnit.DataBind();
                if (selUnit.Items.FindByValue(da["stit_unit"].ToString()) != null)
                    selUnit.Text = da["stit_unit"].ToString();
                if (selQuantity.Items.Count <= 1)
                {
                    SDSQuantity.SelectParameters["unitId"].DefaultValue = selUnit.SelectedValue;
                    SDSQuantity.DataBind();
                    selQuantity.DataBind();
                    string unitValue = da["unitvalue"].ToString();
                    ListItem item = selQuantity.Items.FindByValue(unitValue);
                    if (item != null)
                    {
                        selQuantity.SelectedValue = unitValue;
                        rbSize.Checked = string.Equals(selUnit.SelectedItem?.Text, "Size", StringComparison.OrdinalIgnoreCase);
                        SDSQuantity.DataBind();
                        rbQty.Checked = !rbSize.Checked;
                    }
                    else
                    {
                        txtQuantity.Text = da["unitvalue"].ToString();
                    }
                }

                if (selHSN.Items.Count <= 1)
                    selHSN.DataBind();
                if (selHSN.Items.FindByValue(da["stit_hsnId"].ToString()) != null)
                    selHSN.Text = da["stit_hsnId"].ToString();


                string hsnId = da["stit_hsnId"].ToString();

                string gstCountQuery = "SELECT COUNT(*) FROM hsn_value WHERE hsnId = @hsnId";
                var gstCountParams = new List<KeyValuePair<string, object>>()
                {
                    new KeyValuePair<string, object>("hsnId", hsnId)
                };
                int gstCount = Convert.ToInt32(DataServiceMySql.ExecuteScalar(gstCountQuery, UserService.GetAPIConnectionString(), gstCountParams));

                string taxValueId = da["taxValueId"]?.ToString();
                ViewState["taxValueId"] = taxValueId;

                if (gstCount > 1)
                {
                    selType.Visible = true;
                    txtTax.Visible = false;

                    BindTaxDropdown(hsnId, taxValueId);
                }
                else
                {
                    selType.Visible = false;
                    txtTax.Visible = true;

                    txtTax.Text = da["stit_GST"]?.ToString();

                    string cessQuery = "SELECT hsnCess FROM hsn_value WHERE hsnId = @hsnId LIMIT 1";
                    object cessVal = DataServiceMySql.ExecuteScalar(cessQuery, UserService.GetAPIConnectionString(), gstCountParams);
                    txtCESS.Text = cessVal?.ToString() ?? string.Empty;
                }


                //if (selDays.Items.FindByValue(da["stit_itemReturnTime"].ToString()) != null)
                //    selDays.Text = da["stit_itemReturnTime"].ToString();
                try
                {
                    VarientGroupId = Convert.ToInt32(da["variantGroupId"]); hidVarientGroupName.Value = da["variantGroupName"].ToString();
                    //if (VarientGroupId > 0)
                }
                catch (Exception ex) { }

                if (selCountry.Items.Count <= 1)
                    selCountry.DataBind();
                if (selCountry.Items.FindByValue(da["stit_orgin_country"].ToString()) != null)
                    selCountry.Text = da["stit_orgin_country"].ToString();
                // courierDelivery,i.directDelivery
                if (da["courierDelivery"].ToString() == "1" && da["directDelivery"].ToString() == "1")
                    selDelMode.Text = "3";
                else if (da["directDelivery"].ToString() == "1")
                    selDelMode.Text = "2";
                else if (da["courierDelivery"].ToString() == "1")
                    selDelMode.Text = "1";
                else
                    selDelMode.Text = "";
                string editpackingmode = da["mPackingMode"].ToString() != null ? da["mPackingMode"].ToString() : da["packingMode"].ToString();
                //rbpackindependently.Checked = editpackingmode == "1";
                //rbpackingtogather.Checked = editpackingmode == "2";
                //rbdefault.Checked = editpackingmode != "1" && editpackingmode != "2";

                if (ddlPackingType.Items.FindByValue(editpackingmode) != null)
                {
                    ddlPackingType.SelectedValue = editpackingmode;
                }
                else
                {
                    ddlPackingType.SelectedValue = "0"; 
                }
                string strBarcodeSql = @"SELECT fsipc_id, fsipc_stit_id, fsipc_code FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id =  @stitid";
                DataTable dtBarcode = DataServiceMySql.GetDataTable(strBarcodeSql, Service.UserService.GetAPIConnectionString(), selectParams);
                if (dtBarcode != null && dtBarcode.Rows.Count > 0)
                {
                    DataRow dz = dtBarcode.Rows[0];
                    txtBarcode.Text = dz["fsipc_code"].ToString();
                }
                if (ViewType == ViewMode.Edit)
                {
                    string strImageSql = @"select id, image_url, image_folder from finascop_stock_item_images where product_id = @stitid order by image_type desc, id";
                    DataTable dtImages = DataServiceMySql.GetDataTable(strImageSql, Service.UserService.GetAPIConnectionString(), selectParams);
                    if (dtImages != null && dtImages.Rows.Count > 0)
                    {
                        string imageUrl = ConfigurationManager.AppSettings.Get("ImageLocation"); //"https://odomedsdev.s3.ap-southeast-1.amazonaws.com/products/";
                        productImg1.ImageUrl = String.Format("{0}{1}", imageUrl, dtImages.Rows[0]["image_url"]);
                        productImg1.Attributes.Add("imgid", dtImages.Rows[0]["id"].ToString());
                        if (dtImages.Rows.Count > 1)
                        {
                            productImg2.ImageUrl = String.Format("{0}{1}", imageUrl, dtImages.Rows[1]["image_url"]);
                            productImg2.Attributes.Add("imgid", dtImages.Rows[1]["id"].ToString());
                        }
                        if (dtImages.Rows.Count > 2)
                        {
                            productImg3.ImageUrl = String.Format("{0}{1}", imageUrl, dtImages.Rows[2]["image_url"]);
                            productImg3.Attributes.Add("imgid", dtImages.Rows[2]["id"].ToString());
                        }
                        if (dtImages.Rows.Count > 3)
                        {
                            productImg4.ImageUrl = String.Format("{0}{1}", imageUrl, dtImages.Rows[3]["image_url"]);
                            productImg4.Attributes.Add("imgid", dtImages.Rows[3]["id"].ToString());
                        }
                        if (dtImages.Rows.Count > 4)
                        {
                            productImg5.ImageUrl = String.Format("{0}{1}", imageUrl, dtImages.Rows[4]["image_url"]);
                            productImg5.Attributes.Add("imgid", dtImages.Rows[4]["id"].ToString());
                        }
                    }
                }


            }
        }


        private void BindTaxDropdown(string hsnId, string selectedTaxValueId)
        {
            if (!string.IsNullOrEmpty(hsnId))
            {
                string query = "SELECT id, hsnGst, hsnCess FROM hsn_value WHERE hsnId = @hsnId ORDER BY id";

                var param = new List<KeyValuePair<string, object>>()
                {
                    new KeyValuePair<string, object>("hsnId", hsnId)
                };

                DataTable dt = DataServiceMySql.GetDataTable(query, UserService.GetAPIConnectionString(), param);

                selType.Items.Clear();
                selType.Items.Add(new ListItem("Select Tax", ""));

                if (dt != null && dt.Rows.Count > 0)
                {
                    foreach (DataRow row in dt.Rows)
                    {
                        string gstText = row["hsnGst"]?.ToString();
                        string gstId = row["id"]?.ToString();

                        selType.Items.Add(new ListItem(gstText, gstId));
                    }

                    // Set the selected tax and corresponding CESS
                    if (!string.IsNullOrEmpty(selectedTaxValueId) &&
                        selType.Items.FindByValue(selectedTaxValueId) != null)
                    {
                        selType.SelectedValue = selectedTaxValueId;
                        DataRow selectedRow = dt.Select("id = '" + selectedTaxValueId + "'").FirstOrDefault();
                        if (selectedRow != null)
                            txtCESS.Text = selectedRow["hsnCess"]?.ToString();
                    }
                    else if (dt.Rows.Count == 1)
                    {
                        selType.SelectedIndex = 1;
                        txtCESS.Text = dt.Rows[0]["hsnCess"]?.ToString();
                    }
                }
            }
        }

        //private void BindTaxDropdown()
        //{
        //    string hsnId = selHSN.SelectedValue;
        //    if (!string.IsNullOrEmpty(hsnId))
        //    {
        //        string query = "SELECT id, hsnGst, hsnCess FROM hsn_value WHERE hsnId = @hsnId ORDER BY id";

        //        List<KeyValuePair<string, object>> param = new List<KeyValuePair<string, object>>()
        //        {
        //            new KeyValuePair<string, object>("hsnId", hsnId)
        //        };

        //        DataTable dt = DataServiceMySql.GetDataTable(query, UserService.GetAPIConnectionString(), param);

        //        selType.Items.Clear();
        //        selType.Items.Add(new ListItem("Select Tax", ""));

        //        foreach (DataRow row in dt.Rows)
        //        {
        //            selType.Items.Add(new ListItem(row["hsnGst"].ToString(), row["id"].ToString()));
        //        }
        //    }
        //}

        protected async void btnEditProduct_Click(object sender, EventArgs e)
        {
            if (EditProdId <= 0)
            {
                ParentMessageBinding("Invalid Product", "The product is invalid or you do not have access to the product.", 3);
                //Common.ShowToastifyMessage(Page, "Failure. Invalid product or you don't have access", "danger");
                return;
            }
            if (!Page.IsValid)
            {
                ParentMessageBinding("Validation failed", "Please ensure all required input values are provided.", 2);
                //Common.ShowToastifyMessage(Page, "Failure. Please ensure all required input values are provided", "danger");
                return;
            }
            if (selDelMode.Items.FindByValue("1").Enabled == true && selDelMode.Items.FindByValue("3").Enabled == true && selDelMode.Enabled == true && selDelMode.Enabled == true && rfvDelivMode.Visible == true)
            {
                if (String.IsNullOrEmpty(selDelMode.Text))
                {
                    Common.ShowToastifyMessage(Page, "Failure. Please select delivery type", "danger");
                    //ltrResult.Text = "Please select delivery type";
                    return;
                }
            }

            List<KeyValuePair<string, object>> selectParams = new List<KeyValuePair<string, object>>();
            selectParams.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));
            selectParams.Add(new KeyValuePair<string, object>("stitid", EditProdId));

            int courierDeliv = 0, directDeliv = 0;
            int deliveryType = Convert.ToInt32(selDelMode.SelectedValue);

            switch (deliveryType)
            {
                case 1:
                    courierDeliv = 1;
                    break;
                case 2:
                    directDeliv = 1;
                    break;
                case 3:
                    courierDeliv = directDeliv = 1;
                    break;
            }
            int spotReturn = new int();
            if (chkSpotReturn.Checked)
            {
                spotReturn = 1;
            }
            else
            {
                spotReturn = 0;
            }
            string checkSpotReturn = Convert.ToString(spotReturn);

            int returnDays = 0;
            if (txtReturn.Text == "")
            {
                returnDays = 0;
            }
            else
            {
                returnDays = Convert.ToInt32(txtReturn.Text);
            }

            string strQuantity = (pnlQuantitySelect.Visible ? selQuantity.SelectedItem.Value : txtQuantity.Text);
            string strqty = (pnlQuantitySelect.Visible ? selQuantity.SelectedItem.Text : txtQuantity.Text);
            string sku = "";
            if (String.IsNullOrEmpty(txtProductWebName.Text))
                txtProductWebName.Text = String.Format("{0} {1} {2} {3} {4}", (txtSelectedBrand.Text == "Generic" ? "" : txtSelectedBrand.Text), txtPrdName.Text, txtVarient.Text, strQuantity, selUnit.SelectedItem.Text).Trim();

            if (txtSelectedBrand.Text == "Generic" && txtProductWebName.Text.ToLower().StartsWith("generic"))
                sku = txtProductWebName.Text.Replace("Generic", string.Empty);
            else
                sku = txtProductWebName.Text;
            string displayQuantity = strqty + " " + selUnit.SelectedItem.Text;
            List<KeyValuePair<string, object>> sqlPrdParams = new List<KeyValuePair<string, object>>();
            sqlPrdParams.Add(new KeyValuePair<string, object>("product_stit_id", EditProdId));
            sqlPrdParams.Add(new KeyValuePair<string, object>("sku", sku));
            sqlPrdParams.Add(new KeyValuePair<string, object>("businesstypeid", selRetCat.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("categoryid", selCat.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("subcategoryid", selSubCat.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("brand", Request.QueryString["id2"]));
            sqlPrdParams.Add(new KeyValuePair<string, object>("variant", txtVarient.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("quantity", strQuantity));
            sqlPrdParams.Add(new KeyValuePair<string, object>("unit", selUnit.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("hsncode", selHSN.Text));
            string rawGst = !string.IsNullOrWhiteSpace(selType.SelectedItem.Text) ? selType.SelectedItem.Text : txtTax.Text.Trim();

            double gstValue;
            if (!double.TryParse(rawGst.Replace("%", "").Trim(), out gstValue))
            {
                gstValue = 0;
            }

            sqlPrdParams.Add(new KeyValuePair<string, object>("gst", gstValue));
            sqlPrdParams.Add(new KeyValuePair<string, object>("productName", txtPrdName.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("hsnCodeSelected", selHSN.SelectedItem.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("returndays", returnDays));
            sqlPrdParams.Add(new KeyValuePair<string, object>("foodtype", selFoodType.SelectedValue));
            sqlPrdParams.Add(new KeyValuePair<string, object>("countryid", selCountry.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("courierDelivery", courierDeliv));
            sqlPrdParams.Add(new KeyValuePair<string, object>("directDelivery", directDeliv));
            sqlPrdParams.Add(new KeyValuePair<string, object>("shortdescription", txtShortDescription.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("longdescription", summernote.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("categoryName", selSubCat.SelectedItem.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("brandName", txtSelectedBrand.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.APIStoreId));
            sqlPrdParams.Add(new KeyValuePair<string, object>("spotReturn", checkSpotReturn));
            sqlPrdParams.Add(new KeyValuePair<string, object>("barcode", txtBarcode.Text));
            string taxValueID = !string.IsNullOrEmpty(selType.SelectedValue) ? selType.SelectedValue : ViewState["taxValueId"]?.ToString();
            sqlPrdParams.Add(new KeyValuePair<string, object>("taxValue", taxValueID));
            sqlPrdParams.Add(new KeyValuePair<string, object>("courierWt", txtpackageweigt.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("qty", displayQuantity));
            sqlPrdParams.Add(new KeyValuePair<string, object>("packageLength", txtPackageLength.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("packageWidth", txtPackageWidth.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("packageHeight", txtPackageHeight.Text));
            try
            {
                var result = DataServiceMySql.ExecuteSP("editPrivateProduct", UserService.GetAPIConnectionString(), sqlPrdParams);
                //string packingMode = rbpackindependently.Checked ? "1" :
                //                     rbpackingtogather.Checked ? "2" : "0";
                string packingMode = ddlPackingType.SelectedValue;
                sqlPrdParams.Add(new KeyValuePair<string, object>("editpackingmode", packingMode));
                string editpackindmode = "update BranchProductSettings set PackingMode=@editpackingmode where stitId=@product_stit_id";
                if (!String.IsNullOrEmpty(editpackindmode))
                    DataServiceMySql.ExecuteSql(editpackindmode, UserService.GetAPIConnectionString(), sqlPrdParams);


                UploadImages(EditProdId);
                if (result > 0)
                {
                    ParentMessageBinding("Success", "The product has been updated successfully.", 1);
                }
                //Common.ShowToastifyMessage(Page, "Product added to your list of items");

                //ParentAddProductBinding(1);

                // Remove Redis cache entry
                var cacheService = new RedisCacheService();
                string cachekey = $"Retl.AppTenant.pendingtasks.count.{this.CurrentUser.APIStoreId}";
                await cacheService.RemoveAsync(cachekey);

                // Activitylog
                String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                string Source = strUrl;
                int storegroupid = this.CurrentUser.APIStoreId;
                string Users = this.CurrentUser.Email;
                string storegroup_id = (this.CurrentUser.APIStoreId).ToString();
                string Productname = sku;
                string Editproduct = EditProdId.ToString();
                string businesstypeid = selRetCat.Text;
                string subcategoryid = selSubCat.Text;
                string brand = Request.QueryString["id2"];
                string variant = txtVarient.Text;
                string quantity = strQuantity;
                string unit = selUnit.Text;
                string hsncode = selHSN.Text;
                string hsngst = selType.SelectedItem.Text;
                string productName = txtPrdName.Text;
                string hsnCode = selHSN.SelectedItem.Text;
                string Returndays = returnDays.ToString();
                string Foodtype = selFoodType.SelectedValue;
                string Countryid = selCountry.Text;
                string CourierDelivery = courierDeliv.ToString();
                string DirectDelivery = directDeliv.ToString();
                string Shortdescription = txtShortDescription.Text;
                string longdescription = summernote.Text;
                string CategoryName = selSubCat.SelectedItem.Text;
                string BrandName = txtSelectedBrand.Text;
                string SpotReturn = checkSpotReturn;
                string barcode = txtBarcode.Text;
                string taxValue = taxValueID;
                string courierWt = txtpackageweigt.Text;

                int tenantId = this.CurrentUser.APIStoreId;
                var items = new[]
                {
                      new { Key = "Productname", Value = Productname },
                      new { Key = "EditProduct Id", Value = Editproduct },
                      new { Key = "Businesstype Id", Value =businesstypeid },
                      new { Key = "subcategory Id", Value =subcategoryid },
                      new { Key = "Brandid", Value =brand },
                      new { Key = "variant", Value =variant },
                      new { Key = "Quantity", Value =quantity },
                      new { Key = "Unit", Value =unit },
                      new { Key = "hsncode", Value =hsncode },
                      new { Key = "hsngst", Value =hsngst },
                      new { Key = "ProductName", Value =productName },
                      new { Key = "hsnCode", Value =hsnCode },
                      new { Key = "Returndays", Value =Returndays },
                      new { Key = "Foodtype", Value =Foodtype },
                      new { Key = "Country Id", Value =Countryid },
                      new { Key = "CourierDelivery", Value =CourierDelivery },
                      new { Key = "DirectDelivery", Value =DirectDelivery },
                      new { Key = "Shortdescription", Value =Shortdescription },
                      new { Key = "longdescription", Value =longdescription },
                      new { Key = "CategoryName", Value =CategoryName },
                      new { Key = "BrandName", Value =BrandName },
                      new { Key = "SpotReturn", Value =SpotReturn },
                      new { Key = "barcode", Value =barcode },
                      new { Key = "TaxValue", Value =taxValue },
                      new { Key = "courierWt", Value =courierWt },
                    };
                string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);//end of Activitylog
            }
            catch (Exception ex)
            {
                ParentMessageBinding("Operation failure", "Error: " + ex.Message, 2);
                //Common.ShowToastifyMessage(this.Page, "Error: " + ex.Message, "danger");
            }

        }

        //private bool IsValidImage(Stream imageStream, int maxWidth, int maxHeight, int maxSizeKB)
        //{
        //    try
        //    {
        //        using (System.Drawing.Image img = System.Drawing.Image.FromStream(imageStream))
        //        {
        //            if (img.Width <= maxWidth && img.Height <= maxHeight && imageStream.Length <= maxSizeKB * 1024)
        //            {
        //                return true;
        //            }
        //        }
        //    }
        //    catch (Exception)
        //    {
        //        // Handle exceptions for non-supported image formats (e.g., WebP)
        //    }
        //    return false;
        //}

        protected void selSubCat_SelectedIndexChanged(object sender, EventArgs e)
        {
            string selectedSubCategoryId = selSubCat.SelectedValue;

        }

        public (int HasRestaurantService, string PackingMode) GetSubCategoryDetails(int subCategoryId)
        {
            var subCatparams = new List<KeyValuePair<string, object>>
            {
              new KeyValuePair<string, object>("subcatId", subCategoryId)
            };

            var query = "SELECT sub_category_id, sub_category,packingMode, hasRestaurantService FROM mypha_productsubcategory WHERE status=1 AND sub_category_id = @subcatId";
            var dt = DataServiceMySql.GetDataTable(query, Service.UserService.GetAPIConnectionString(), subCatparams);

            if (dt?.Rows.Count > 0)
            {
                var da = dt.Rows[0];
                int hasRestaurantService = da["hasRestaurantService"].ToString() == "1" ? 1 : 0;
                string packingMode = da["packingMode"].ToString();

                return (hasRestaurantService, packingMode);
            }

            return (0, null); // Default values if no data is found
        }

        protected void selCat_DataBound(object sender, EventArgs e)
        {
            selCat.Items.Insert(0, new ListItem("Select category", ""));
        }

        protected void selSubCat_DataBound(object sender, EventArgs e)
        {
            //if (selSubCat.Items.Count <= 0)
            if (selSubCat.Items.FindByValue("") == null)
                selSubCat.Items.Insert(0, new ListItem("Select sub-category", ""));
        }

        protected void selCountry_DataBound(object sender, EventArgs e)
        {
            selCountry.Items.Insert(0, new ListItem("Select country of orgin", ""));
        }

        protected void selUnit_DataBound(object sender, EventArgs e)
        {
            selUnit.Items.Insert(0, new ListItem("Select unit", ""));
        }

        protected void selHSN_DataBound(object sender, EventArgs e)
        {
            selHSN.Items.Insert(0, new ListItem("Select HSN", ""));
        }

        protected void SDSCat_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            if (CurrentUser.TenantType == 2)
                e.Command.Parameters["isNoGST"].Value = 1;
        }

        protected void SDSSubCat_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            if (CurrentUser.TenantType == 2)
                e.Command.Parameters["isNoGST"].Value = 1;
        }

        protected void SDSDepartment_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        }

        protected void selDepartment_DataBound(object sender, EventArgs e)
        {
            selDepartment.Items.Insert(0, new ListItem("Select department", ""));
        }

        protected void lbtnhsn_Click(object sender, EventArgs e)
        {

        }

        protected void lnkGo_Click(object sender, EventArgs e)
        {
            if (rbCode.Checked == true)
            {
                rptDetails.DataSourceID = "SDSCodeList";
            }
            else if (rbItem.Checked == true)
            {
                rptDetails.DataSourceID = "SDSItem";
            }
            if (rbCode.Checked == false && rbItem.Checked == false)
            {
                lblErrormsg.Text = "Check any of the checkbox";
            }
            else if (rbCode.Checked == true || rbItem.Checked == true)
            {
                lblErrormsg.Text = "";
            }

            rptDetails.DataBind();
            ScriptManager.RegisterStartupScript(this, this.GetType(), "keepModalOpenScript", "$('#hsnsearch').modal('show');", true);
        }

        protected void btnSelect_Click(object sender, EventArgs e)
        {
            Button btn = (Button)sender;
            if (btn == null || String.IsNullOrEmpty(btn.Attributes["hsnCode"]))
            {
                Common.ShowToastifyMessage(this.Page, "Invalid item selected!", "danger");
                return;
            }
            //txtSearch.Text = "";
            //txtSearch.Visible = false;
            //lnkGo.Visible = false;
            //rbCode.Checked = false;
            //rbItem.Checked = false;
            string hsnCode = btn.Attributes["hsnCode"];
            selHSN.Text = hsnCode;
            ScriptManager.RegisterStartupScript(this, this.GetType(), "CloseModalScript", "closeModal();", true);
        }

        protected void btnlongdescription_Click(object sender, EventArgs e)
        {
            try
            {
                string productDescription = string.Empty;
                // Ensure all required fields are non-null and non-empty
                if (!string.IsNullOrWhiteSpace(txtPrdName?.Text))
                {
                     productDescription = Core.Services.APIService.GenerateProductDescription(txtPrdName.Text,txtSelectedBrand?.Text ?? string.Empty,selCat?.Text ?? string.Empty, txtShortDescription?.Text ?? string.Empty);
                }
                // Initialize formatted description from the original product description
                string formattedDescription = productDescription?.Trim() ?? string.Empty;
                if (!string.IsNullOrWhiteSpace(formattedDescription))
                {
                    // Replace **text** with <strong>text</strong> for bold emphasis
                    formattedDescription = Regex.Replace(formattedDescription, @"\*\*(.*?)\*\*", "<strong>$1</strong>");

                    // Convert lines starting with * to HTML bullet points
                    formattedDescription = Regex.Replace(formattedDescription, @"(?:\r?\n)?\* (.*?)(?:\r?\n|$)", "<ul><li>$1</li></ul>\n");

                    // Replace double newlines with paragraph breaks
                    formattedDescription = formattedDescription.Replace("\n\n", "</p><p>");

                    // Wrap the entire content in paragraph tags for structure
                    formattedDescription = $"<p>{formattedDescription.Trim()}</p>";
                }
                if (!string.IsNullOrEmpty(formattedDescription))
                {
                    Common.ShowCustomAlert(this.Page, "AI-Generated Content Alert", "This product description is AI-generated. Please review and make any necessary edits before submitting. Refining the content will help enhance your product’s appeal and drive more business.", true);
                    summernote.Text = formattedDescription;
                }
                else
                {
                    Common.ShowToastifyMessage(this.Page, "AI could not generate a description for this product.", "danger");
                }

            }
            catch
            {

            }

        }

        protected void selQuantity_DataBound(object sender, EventArgs e)
        {

            if (selQuantity.Items.FindByValue("") == null)
                selQuantity.Items.Insert(0, new ListItem("Select Quantity", ""));
            pnlQuantitySelect.Visible = selQuantity.Items.Count > 1;


        }
        protected void selUnit_SelectedIndexChanged(object sender, EventArgs e)
        {
            rbSize.Checked = string.Equals(selUnit.SelectedItem?.Text, "Size", StringComparison.OrdinalIgnoreCase);
            rbQty.Checked = !rbSize.Checked;
            selQuantity.DataBind();
        }

        protected void lbtnVariantGroupYes_Click(object sender, EventArgs e)
        {
            groupVariant = true;
            AddProduct();
        }

        protected void lbtnVariantGroupNo_Click(object sender, EventArgs e)
        {
            groupVariant = false;
            AddProduct();
        }

        protected void btnhidecategory_Click(object sender, EventArgs e)
        {
            string value = hdnIDS.Value;

            try
            {
                if (!string.IsNullOrEmpty(value))
                {
                    string[] ids = value.Split(',');

                    if (ids.Length == 4)
                    {
                        string BusinessType = ids[0];
                        string ParentCategory = ids[1];
                        string Category = ids[2];
                        string SubCategory = ids[3];
                        bool valueExists = selRetCat.Items.Cast<ListItem>().Any(item => item.Value == BusinessType);
                        if (valueExists)
                        {
                            ListItem item = selRetCat.Items.FindByValue(BusinessType);
                            if (item != null)
                            {
                                selRetCat.SelectedValue = BusinessType;
                            }

                        }
                        else
                        {
                            string sql = "INSERT INTO finascop_branch_group_business_type(store_group_id, business_type_id, is_primary) VALUES(@groupid, @business_type_id, 1); ";
                            List<KeyValuePair<string, object>> input = new List<KeyValuePair<string, object>>();
                            input.Add(new KeyValuePair<string, object>("groupid", this.CurrentUser.APIStoreId));
                            input.Add(new KeyValuePair<string, object>("business_type_id", BusinessType));
                            DataServiceMySql.ExecuteSql(sql, UserService.GetAPIConnectionString(), input);
                            selRetCat.Items.Clear();
                            SDSRetCat.Select(DataSourceSelectArguments.Empty);
                            selRetCat.DataBind();
                            if (selRetCat.Items.FindByValue(BusinessType) != null)
                                selRetCat.Text = BusinessType;

                        }
                        selDepartment.DataBind();
                        if (selDepartment.Items.FindByValue(ParentCategory) != null)
                            selDepartment.Text = ParentCategory;

                        selCat.DataBind();
                        if (selCat.Items.FindByValue(Category) != null)
                            selCat.Text = Category;

                        selSubCat.DataBind();
                        if (selSubCat.Items.FindByValue(SubCategory) != null)
                            selSubCat.Text = SubCategory;

                        if (!string.IsNullOrEmpty(SubCategory))
                        {
                            if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
                            {
                                int restaurantServiceFlag = GetSubCategoryDetails(Convert.ToInt32(SubCategory)).HasRestaurantService;
                                if (restaurantServiceFlag == 1)
                                {
                                    selHSN.Items.Clear();
                                    DataTable dt = DataServiceMySql.GetDataTable($"SELECT hsn_id,hsn_code,gst_percent FROM finascop_hsn WHERE hsn_code LIKE '996331%'  ORDER BY hsn_code", Service.UserService.GetAPIConnectionString());
                                    foreach (DataRow row in dt.Rows)
                                    {
                                        selHSN.Items.Add(new ListItem(row["hsn_code"].ToString(), row["hsn_id"].ToString()));
                                    }
                                }
                                else
                                {
                                    if (selHSN.Items.Count == 0)
                                    {
                                        selHSN.DataBind();
                                    }
                                }
                            }
                            //string packingMode = GetSubCategoryDetails(Convert.ToInt32(SubCategory)).PackingMode;
                            //rbpackindependently.Checked = packingMode == "1";
                            //rbpackingtogather.Checked = packingMode == "2";
                            //rbdefault.Checked = packingMode != "1" && packingMode != "2";
                            string packingMode = GetSubCategoryDetails(Convert.ToInt32(SubCategory)).PackingMode;

                            if (ddlPackingType.Items.FindByValue(packingMode) != null)
                            {
                                ddlPackingType.SelectedValue = packingMode;
                            }
                            else
                            {
                                ddlPackingType.SelectedValue = "0"; 
                            }
                        }

                    }

                }
            }
            catch
            {
                Common.ShowCustomAlert(this.Page, "Technical Error", "Technical Error", false);
            }


        }


        protected void rbQty_CheckedChanged(object sender, EventArgs e)
        {
         

        }

        protected void rbSize_CheckedChanged(object sender, EventArgs e)
        {
            var sizeItem = selUnit.Items.Cast<ListItem>().FirstOrDefault(item => string.Equals(item.Text, "Size", StringComparison.OrdinalIgnoreCase));
            var sizeValue = sizeItem?.Value;
            if (!string.IsNullOrEmpty(sizeValue))
            {
                selUnit.SelectedValue = sizeValue;
                if (string.Equals(selUnit.SelectedItem?.Text, "Size", StringComparison.OrdinalIgnoreCase))
                    selUnit.Enabled = false;
                selQuantity.DataBind();
            }

        }


        protected void selHSN_SelectedIndexChanged(object sender, EventArgs e)
        {;
            selType.Items.Clear();
            txtTax.Text = "";
            txtCESS.Text = "";

            string hsnId = selHSN.SelectedValue;
            if (string.IsNullOrEmpty(hsnId)) return;

            var param = new List<KeyValuePair<string, object>>
            {
                new KeyValuePair<string, object>("hsnId", hsnId)
            };

            DataTable dt = DataServiceMySql.GetDataTable("SELECT id, hsnGst, hsnCess FROM hsn_value WHERE hsnId = @hsnId ORDER BY id", Service.UserService.GetAPIConnectionString(), param);

            if (dt == null || dt.Rows.Count == 0)
            {
                selType.Visible = false;
                txtTax.Visible = false;
                return;
            }

            if (dt.Rows.Count == 1)
            {
                string gst = dt.Rows[0]["hsnGst"].ToString();
                string cess = dt.Rows[0]["hsnCess"].ToString();
                string id = dt.Rows[0]["id"].ToString();

                selType.Items.Add(new ListItem(gst, id));
                selType.SelectedIndex = 0;

                txtCESS.Text = cess;
                txtTax.Text = gst;

                selType.Visible = false;
                txtTax.Visible = true;
                txtTax.Enabled = false;
            }
            else
            {
                selType.DataSource = dt;
                selType.DataTextField = "hsnGst";
                selType.DataValueField = "id";
                selType.DataBind();

                selType.Visible = true;
                txtTax.Visible = false;
            }
        }
        protected void selType_SelectedIndexChanged(object sender, EventArgs e)
        {
            txtCESS.Text = "";

            string selectedId = selType.SelectedValue;
            if (string.IsNullOrEmpty(selectedId)) return;

            var param = new List<KeyValuePair<string, object>>
            {
                new KeyValuePair<string, object>("id", selectedId)
            };

            DataTable dt = DataServiceMySql.GetDataTable("SELECT hsnCess FROM hsn_value WHERE id = @id", Service.UserService.GetAPIConnectionString(), param);

            if (dt != null && dt.Rows.Count > 0)
            {
                txtCESS.Text = dt.Rows[0]["hsnCess"].ToString();
            }
        }

        protected void selType_DataBound(object sender, EventArgs e)
        {
            selType.Items.Insert(0, new ListItem("Select Tax", ""));
        }

        protected void btnSaveHsn_Click(object sender, EventArgs e)
        {
            try
            {
                List<KeyValuePair<string, object>> hsnparams = new List<KeyValuePair<string, object>>()
                {
                    new KeyValuePair<string, object>("hsn", txtNewHsn.Text.Trim()),
                    new KeyValuePair<string, object>("gst", selNewTax.SelectedValue),
                    new KeyValuePair<string, object>("cess", txtNewCess.Text.Trim()),
                    new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId)
                };

                string insertQuery = @"INSERT INTO finascop_hsn(hsn_code, gst_percent, cess, storegroupId) SELECT @hsn, @gst, @cess, @storegroupid FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM finascop_hsn WHERE hsn_code = @hsn); SELECT LAST_INSERT_ID();";

                object result = DataServiceMySql.ExecuteScalar(insertQuery, UserService.GetAPIConnectionString(), hsnparams);
                int insertedId = Convert.ToInt32(result);

                if (insertedId > 0)
                {
                    hsnparams.Add(new KeyValuePair<string, object>("hsnId", insertedId));

                    string insertHsnValue = @"INSERT INTO hsn_value(hsnId, hsnGst, hsnCess) VALUES (@hsnId, @gst, @cess)";

                    DataServiceMySql.ExecuteSql(insertHsnValue, UserService.GetAPIConnectionString(), hsnparams);
                    SaveFormDataToSession();
                    string brandId = Request.QueryString["brandId"]; 
                    string redirectUrl = "/Tenant/PrivateInventory?brandId=" + brandId;

                    Common.ShowCustomAlert(this.Page, "Success", "HSN saved successfully.", true, redirectUrl);
                }
                else
                {
                    Common.ShowToastifyMessage(this.Page, "Duplicate HSN code found. Please enter a unique HSN.", "danger");
                    ScriptManager.RegisterStartupScript(this, this.GetType(), "showModal", "$('#hsnModal').modal('show');", true);
                }
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(this.Page, "An error occurred: " + ex.Message, "danger");
            }
        }

        private void SaveFormDataToSession()
        {
            Session["txtPrdName"] = txtPrdName.Text.Trim();
            Session["txtVarient"] = txtVarient.Text.Trim();
            Session["rbQtyChecked"] = rbQty.Checked;
            Session["rbSizeChecked"] = rbSize.Checked;
            Session["selUnitValue"] = selUnit.SelectedValue;
            Session["selUnitText"] = selUnit.SelectedItem.Text;
            Session["txtQuantity"] = txtQuantity.Text.Trim();
            Session["selQuantityValue"] = selQuantity.SelectedValue;
            Session["selQuantityText"] = selQuantity.SelectedItem.Text;
            Session["txtDisplayQty"] = txtDisplayQty.Text.Trim();
            Session["txtProductWebName"] = txtProductWebName.Text.Trim();
        }

        private void ClearFormSessionKeys()
        {
            string[] keysToClear = new[]
            {
                "txtPrdName", 
                "txtVarient", 
                "rbQtyChecked", 
                "rbSizeChecked",
                "selUnitValue",
                "selUnitText",
                "txtQuantity",
                "selQuantityValue",
                "selQuantityText",
                "txtDisplayQty", 
                "txtProductWebName"
            };

            foreach (var key in keysToClear)
                Session.Remove(key);
        }
    }
}