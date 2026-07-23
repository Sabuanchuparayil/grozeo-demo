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

namespace RetalineProAgent.Controls.StoreSettings
{
    public partial class ctrlDuplicatedProduct : Base.BasePartnerUserControl
    {
        public delegate void ParentAddProductHandler(int status);
        public delegate void ParentAddBrandHandler(int status);
        public delegate void ParentMessageHandler(string title, string msg, int type);

        public event ParentAddProductHandler ParentAddProductBinding;
        public event ParentAddBrandHandler ParentAddBrandBinding;
        public event ParentAddProductHandler ParentCancelAddProductBinding;

        public event ParentMessageHandler ParentMessageBinding;


        public bool IsEditView
        {
            get
            {
                if (ViewState["ISEDITVIEW"] != null)
                    return (bool)ViewState["ISEDITVIEW"];
                else
                    return false;
            }
            set
            {
                ViewState["ISEDITVIEW"] = value;
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

        protected void Page_Load(object sender, EventArgs e)
        {
            lblProductNameResult.Text = "";
            ltrAddBrandResult.Text = "";


            if (!IsPostBack)
            {
                LoadProductInfo();
                //btnAddPrivateProduct.OnClientClick = "if(confirm('Would you like to group the item with the other one chosen for duplication? The grouped items will appear on the product details page as options for selecting the preferred variant.')){ $('#" + hidGroupItem.ClientID + "').val('1'); }else{$('#" + hidGroupItem.ClientID + "').val('0');}";
                //btnAddPrivateProduct.OnClientClick = "showConfirm('Do you really want to proceed?', function (result) { if (result) { alert('Confirmed!'); } else { alert('Cancelled!'); } });";

            }

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
                selDelMode.Items.FindByValue("1").Enabled = false;
                selDelMode.Items.FindByValue("3").Enabled = false;
                selDelMode.SelectedItem.Text = "Direct";
                selDelMode.Enabled = false;
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

        //protected void LoadWebName(object sender, EventArgs e)
        //{
        //    if (txtSelectedBrand.Text == "Generic")
        //    {
        //        txtProductWebName.Text = String.Format("{0} {1} {2} {3}", txtPrdName.Text, txtVarient.Text, txtQuantity.Text, selUnit.SelectedItem.Text);
        //    }
        //    else
        //    {
        //        txtProductWebName.Text = String.Format("{0} {1} {2} {3} {4}", txtSelectedBrand.Text, txtPrdName.Text, txtVarient.Text, txtQuantity.Text, selUnit.SelectedItem.Text);
        //    }
        //}

        //protected void SDSRetCat_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        //{
        //    e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        //}

        protected void btnCancelSaveProduct_Click(object sender, EventArgs e)
        {
            ParentCancelAddProductBinding(-1);
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

        protected void btnAddProduct_Click(object sender, EventArgs e)
        {
                AddProduct();
        }
        private void AddProduct(bool groupVariant = false)
        {
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
            if (selDelMode.Items.FindByValue("1").Enabled == true && selDelMode.Items.FindByValue("3").Enabled == true && selDelMode.Enabled == true && selDelMode.Enabled == true && rfvDelivMode.Visible == true)
            {
                int deliveryType = Convert.ToInt32(selDelMode.Text);
                if (deliveryType == 1)
                {
                    courierDeliv = 1;
                    directDeliv = 0;
                }
                else if (deliveryType == 2)
                {
                    courierDeliv = 0;
                    directDeliv = 1;
                }
                else if (deliveryType == 3)
                {
                    courierDeliv = 1;
                    directDeliv = 1;
                }
                else
                {
                    courierDeliv = 0;
                    directDeliv = 0;
                }
            }
            else if (selDelMode.SelectedItem.Text == "Direct")
            {
                courierDeliv = 0;
                directDeliv = 1;
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
            
            string sku = "";
            if (String.IsNullOrEmpty(txtProductWebName.Text))
                txtProductWebName.Text = String.Format("{0} {1} {2} {3} {4}", (txtSelectedBrand.Text == "Generic" ? "" : txtSelectedBrand.Text), txtPrdName.Text, txtVarient.Text, txtQuantity.Text, selUnit.SelectedItem.Text).Trim();

            if (txtSelectedBrand.Text == "Generic")
                sku = txtProductWebName.Text.Replace("Generic", string.Empty);
            else
                sku = txtProductWebName.Text;

            int brandId = -1; try { brandId = Convert.ToInt32(Request.QueryString["id2"]); } catch { brandId = 0; }
            List<KeyValuePair<string, object>> sqlPrdParams = new List<KeyValuePair<string, object>>();
            sqlPrdParams.Add(new KeyValuePair<string, object>("sku", sku));
            sqlPrdParams.Add(new KeyValuePair<string, object>("businesstypeid", selRetCat.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("categoryid", selCat.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("subcategoryid", selSubCat.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("brand", brandId.ToString()));
            sqlPrdParams.Add(new KeyValuePair<string, object>("variant", txtVarient.Text));
            //sqlPrdParams.Add(new KeyValuePair<string, object>("isMedicine", 0));
            //sqlPrdParams.Add(new KeyValuePair<string, object>("count", 0));
            sqlPrdParams.Add(new KeyValuePair<string, object>("quantity", txtQuantity.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("unit", selUnit.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("hsncode", selHSN.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("gst", selType.SelectedItem.Text));
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
            sqlPrdParams.Add(new KeyValuePair<string, object>("taxValue", selType.Text));
            int groupId = 0;
            if (groupVariant)
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

            double mrp = 0, stock = 0, sellingprice = 0, discount = 0;
            try { mrp = Convert.ToDouble(mrprrp.Text); } catch { mrp = 0; }
            try { stock = Convert.ToDouble(newquantity.Text); } catch { stock = 0; }
            try { discount = Convert.ToDouble(newPercentage.Text); } catch { discount = 0; }
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

                if (dtResult != null && dtResult.Rows.Count > 0)
                {
                    int stitid = Convert.ToInt32(dtResult.Rows[0]["stit_id"]);
                    int productId = stitid;
                    UploadImages(stitid);
                    string productIndiaResult = Core.Services.APIService.ProductIndia(productId);
                }
                //hidCurTab.Value = "0";
                //Common.ShowToastifyMessage(Page, "Product added to your list of items");

                ParentAddProductBinding(1);
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
                    ParentMessageBinding("Operation failed", "Error: " + "Product name is already existing Or Image size is incorrect", 2);
                    //ParentMessageBinding("Operation failed", "Error: " + "Product name is already existing", 2);
                    //Common.ShowToastifyMessage(this.Page, "Error: " + ex.Message, "danger");
                }
            }
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
                if (IsEditView)
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
            int productId = -1;
            if (!String.IsNullOrEmpty(Request.QueryString["id"]))
                try { productId = Convert.ToInt32(Request.QueryString["id"]); } catch { productId = 0; }
            string selectSql = @"SELECT i.stit_itemId, i.stit_itemBarcode, i.stit_GST, i .taxValueId, i.stit_custInitiate, i.stit_itemReturnTime,i.stit_SKU,i.stit_HSNCode, i.stit_GST,i.stit_Description,i.stit_product_variant,
i.pdt_brand,i.product_category,i.stit_quantity,i.stit_long_description,i.stit_itemName,i.stit_HSN_code,i.stit_hsnId, i.stit_category_name,i.stit_brand_name,i.courierDelivery,
i.directDelivery,i.stit_foodtype,i.stit_orgin_country,i.stit_unit,i.stit_qty,i.stit_StoreGroup, i.stit_fsiuid, i.stit_MRP
,pc.parent_category, c.category_id, sc.sub_category_id, pc.parent_category_businessType, ifnull(g.id, 0) as variantGroupId, g.`name` as variantGroupName
FROM finascop_stock_itemmaster i INNER JOIN mypha_productsubcategory sc ON sc.sub_category_id = i.product_category
INNER JOIN mypha_productcategory c ON c.category_id = sc.main_category INNER JOIN mypha_productparent_category pc ON pc.parent_category_id=c.parent_category
LEFT JOIN (SELECT pg.id, pg.`Name`, bi.stit_id FROM product_group pg inner join finascop_stock_branch_inventory bi on bi.variantGroupId = pg.id WHERE StoreGroupId= @storegroupid AND bi.stit_id=@stitid AND IFNULL(bi.variantGroupId, 0) > 0 group by bi.stit_id limit 1) g on g.stit_id=i.stit_id
WHERE i.stit_Id=@stitid LIMIT 1";

                List<KeyValuePair<string, object>> selectParams = new List<KeyValuePair<string, object>>();
                selectParams.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));
                selectParams.Add(new KeyValuePair<string, object>("stitid", productId));
                DataTable dataTable = DataServiceMySql.GetDataTable(selectSql, Service.UserService.GetAPIConnectionString(), selectParams);

                if (dataTable == null || dataTable.Rows.Count <= 0)
                {
                    ParentMessageBinding("Invalid Product", "The product is invalid or you do not have access to the product.", 3);
                    return;
                }

                DataRow da = dataTable.Rows[0];

                txtPrdName.Text = da["stit_itemName"].ToString();
                //txtVarient.Text = da["stit_product_variant"].ToString();
                //txtQuantity.Text = da["stit_qty"].ToString();
                //selType.Text = da["stit_GST"].ToString();
                txtShortDescription.Text = da["stit_Description"].ToString();
                //txtReturn.Text = da["stit_itemReturnTime"].ToString();
                string isReturn = da["stit_custInitiate"].ToString();
                chkSpotReturn.Checked = (isReturn == "1");
                summernote.Text = da["stit_long_description"].ToString();
                //txtBarcode.Text = da["stit_itemBarcode"].ToString();
                txtSelectedBrand.Text = da["stit_brand_name"].ToString();
                txtProductWebName.Text = da["stit_SKU"].ToString();

                try { VarientGroupId = Convert.ToInt32(da["variantGroupId"]); hidVarientGroupName.Value = da["variantGroupName"].ToString();
                    //if (VarientGroupId > 0)
                } catch(Exception ex) { }

                if (selRetCat.Items.Count <= 1)
                    selRetCat.DataBind();
                if (selRetCat.Items.FindByValue(da["parent_category_businessType"].ToString()) != null)
                    selRetCat.Text = da["parent_category_businessType"].ToString();

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

                //if (selUnit.Items.Count <= 1)
                //    selUnit.DataBind();
                //if (selUnit.Items.FindByValue(da["stit_unit"].ToString()) != null)
                //    selUnit.Text = da["stit_unit"].ToString();

                if (selHSN.Items.Count <= 1)
                    selHSN.DataBind();
                if (selHSN.Items.FindByValue(da["stit_hsnId"].ToString()) != null)
                    selHSN.Text = da["stit_hsnId"].ToString();

                if (selType.Items.Count <= 1)
                    selType.DataBind();
                if (selType.Items.FindByValue(da["taxValueId"].ToString()) != null)
                    selType.Text = da["taxValueId"].ToString();

                //if (selDays.Items.FindByValue(da["stit_itemReturnTime"].ToString()) != null)
                //    selDays.Text = da["stit_itemReturnTime"].ToString();

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

            string strBarcodeSql = @"SELECT fsipc_id, fsipc_stit_id, fsipc_code FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id =  @stitid";
            DataTable dtBarcode = DataServiceMySql.GetDataTable(strBarcodeSql, Service.UserService.GetAPIConnectionString(), selectParams);
            if (dtBarcode != null && dtBarcode.Rows.Count > 0)
            {
                DataRow dz = dtBarcode.Rows[0];
                txtBarcode.Text = dz["fsipc_code"].ToString();
            }

            //string strImageSql = @"select id, image_url, image_folder from finascop_stock_item_images where product_id = @stitid order by image_type desc, id";
            //DataTable dtImages = DataServiceMySql.GetDataTable(strImageSql, Service.UserService.GetAPIConnectionString(), selectParams);
            //if (dtImages != null && dtImages.Rows.Count > 0)
            //{
            //    string imageUrl = ConfigurationManager.AppSettings.Get("ImageLocation"); //"https://odomedsdev.s3.ap-southeast-1.amazonaws.com/products/";
            //    productImg1.ImageUrl = String.Format("{0}{1}", imageUrl, dtImages.Rows[0]["image_url"]);
            //    productImg1.Attributes.Add("imgid", dtImages.Rows[0]["id"].ToString());
            //    if (dtImages.Rows.Count > 1)
            //    {
            //        productImg2.ImageUrl = String.Format("{0}{1}", imageUrl, dtImages.Rows[1]["image_url"]);
            //        productImg2.Attributes.Add("imgid", dtImages.Rows[1]["id"].ToString());
            //    }
            //    if (dtImages.Rows.Count > 2)
            //    {
            //        productImg3.ImageUrl = String.Format("{0}{1}", imageUrl, dtImages.Rows[2]["image_url"]);
            //        productImg3.Attributes.Add("imgid", dtImages.Rows[2]["id"].ToString());
            //    }
            //    if (dtImages.Rows.Count > 3)
            //    {
            //        productImg4.ImageUrl = String.Format("{0}{1}", imageUrl, dtImages.Rows[3]["image_url"]);
            //        productImg4.Attributes.Add("imgid", dtImages.Rows[3]["id"].ToString());
            //    }
            //    if (dtImages.Rows.Count > 4)
            //    {
            //        productImg5.ImageUrl = String.Format("{0}{1}", imageUrl, dtImages.Rows[4]["image_url"]);
            //        productImg5.Attributes.Add("imgid", dtImages.Rows[4]["id"].ToString());
            //    }
            //}


        }

        protected void btnEditProduct_Click(object sender, EventArgs e)
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
            if (selDelMode.Items.FindByValue("1").Enabled == true && selDelMode.Items.FindByValue("3").Enabled == true && selDelMode.Enabled == true && selDelMode.Enabled == true && rfvDelivMode.Visible == true)
            {
                int deliveryType = Convert.ToInt32(selDelMode.Text);
                if (deliveryType == 1)
                {
                    courierDeliv = 1;
                    directDeliv = 0;
                }
                else if (deliveryType == 2)
                {
                    courierDeliv = 0;
                    directDeliv = 1;
                }
                else if (deliveryType == 3)
                {
                    courierDeliv = 1;
                    directDeliv = 1;
                }
                else
                {
                    courierDeliv = 0;
                    directDeliv = 0;
                }
            }
            else if (selDelMode.SelectedItem.Text == "Direct")
            {
                courierDeliv = 0;
                directDeliv = 1;
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

            //string sku = "";
            //if (txtSelectedBrand.Text == "Generic")
            //{
            //    sku = txtProductWebName.Text.Replace("Generic", string.Empty);
            //}
            //else
            //{
            //    sku = txtProductWebName.Text;
            //}
            txtProductWebName.Text = "";
            if (txtSelectedBrand.Text == "Generic")
            {
                txtProductWebName.Text = String.Format("{0} {1} {2} {3}", txtPrdName.Text, txtVarient.Text, txtQuantity.Text, selUnit.SelectedItem.Text);
            }
            else
            {
                txtProductWebName.Text = String.Format("{0} {1} {2} {3} {4}", txtSelectedBrand.Text, txtPrdName.Text, txtVarient.Text, txtQuantity.Text, selUnit.SelectedItem.Text);
            }

            List<KeyValuePair<string, object>> sqlPrdParams = new List<KeyValuePair<string, object>>();
            sqlPrdParams.Add(new KeyValuePair<string, object>("product_stit_id", EditProdId));
            sqlPrdParams.Add(new KeyValuePair<string, object>("sku", txtProductWebName.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("businesstypeid", selRetCat.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("categoryid", selCat.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("subcategoryid", selSubCat.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("brand", Request.QueryString["id2"]));
            sqlPrdParams.Add(new KeyValuePair<string, object>("variant", txtVarient.Text));
            //sqlPrdParams.Add(new KeyValuePair<string, object>("isMedicine", 0));
            //sqlPrdParams.Add(new KeyValuePair<string, object>("count", 0));
            sqlPrdParams.Add(new KeyValuePair<string, object>("quantity", txtQuantity.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("unit", selUnit.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("hsncode", selHSN.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("gst", selType.SelectedItem.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("productName", txtPrdName.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("hsnCodeSelected", selHSN.SelectedItem.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("returndays", returnDays));
            sqlPrdParams.Add(new KeyValuePair<string, object>("foodtype", selFoodType.SelectedValue));
            sqlPrdParams.Add(new KeyValuePair<string, object>("countryid", selCountry.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("courierDelivery", courierDeliv));
            sqlPrdParams.Add(new KeyValuePair<string, object>("directDelivery", directDeliv));
            sqlPrdParams.Add(new KeyValuePair<string, object>("shortdescription", txtShortDescription.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("longdescription", summernote.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("categoryName", selCat.SelectedItem.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("brandName", txtSelectedBrand.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.APIStoreId));
            sqlPrdParams.Add(new KeyValuePair<string, object>("spotReturn", checkSpotReturn));
            sqlPrdParams.Add(new KeyValuePair<string, object>("barcode", txtBarcode.Text));
            sqlPrdParams.Add(new KeyValuePair<string, object>("taxValue", selType.Text));
            try
            {
                var result = DataServiceMySql.ExecuteSP("editPrivateProduct", UserService.GetAPIConnectionString(), sqlPrdParams);
                UploadImages(EditProdId);
                if (result > 0)
                {
                    ParentMessageBinding("Success", "The product has been updated successfully.", 1);
                }
                //Common.ShowToastifyMessage(Page, "Product added to your list of items");

                //ParentAddProductBinding(1);
            }
            catch (Exception ex)
            {
                ParentMessageBinding("Operation failure", "Error: " + ex.Message, 2);
                //Common.ShowToastifyMessage(this.Page, "Error: " + ex.Message, "danger");
            }

        }

        protected void selCat_DataBound(object sender, EventArgs e)
        {
            selCat.Items.Insert(0, new ListItem("Select category", ""));
        }

        protected void selSubCat_DataBound(object sender, EventArgs e)
        {
            if (selSubCat.Items.Count <= 0)
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

        protected void lbtnVariantGroupYes_Click(object sender, EventArgs e)
        {
            AddProduct(true);
        }

        protected void lbtnVariantGroupNo_Click(object sender, EventArgs e)
        {
            AddProduct();
        }
    }
}