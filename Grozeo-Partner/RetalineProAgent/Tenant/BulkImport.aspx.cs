using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.IO;
using System.Text;
using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using System.Data;
using RetalineProAgent.Service;
using RetalineProAgent.Core.Services;
using Newtonsoft.Json;
using System.Configuration;
using System.Runtime.Serialization;

namespace RetalineProAgent
{
    public partial class BulkImport : Base.BasePartnerPage
    {
        public class SuccessfulItem
        {
            public int stitid { get; set; }
            public int brid { get; set; }
            public int stock { get; set; }
            public double mrp { get; set; }
            public double sellingP { get; set; }
            public double dSellingP { get; set; }

        }
        public class UnsuccessfulItem
        {
            public int stitid { get; set; }
            public double ttlmrp { get; set; }
            public int brid { get; set; }
            public string Comment { get; set; }
        }

        int updatecount = 0;

        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                //int storegroupid = this.CurrentUser.APIStoreId;
                //var dtBranches = DataServiceMySql.GetDataTable($"SELECT br_ID,br_name FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
                //string branchID = "";
                //if (dtBranches != null && dtBranches.Rows.Count > 0)
                //{
                //    DataRow dr = dtBranches.Rows[0];
                //    string branchName = dr["br_name"].ToString();

                //    var btStoreGrp = DataServiceMySql.GetDataTable($"SELECT COUNT(br_storeGroup) AS cnt FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
                //    if (btStoreGrp != null && btStoreGrp.Rows.Count > 0)
                //    {
                //        DataRow dc = btStoreGrp.Rows[0];
                //        string storeGroup = dc["cnt"].ToString();
                //        if (Convert.ToInt32(storeGroup) == 1)
                //        {
                //            branchname.Visible = true;
                //            branchname.Value = dr["br_name"].ToString();
                //            branchID = dr["br_ID"].ToString();
                //            // RegisterStartupScript to execute JavaScript on page load
                //            ClientScript.RegisterStartupScript(this.GetType(), "SetVisibilityScript", $@"
                //                var oneStore = document.getElementById('oneStore');
                //                var moreThanOneStr = document.getElementById('moreThanOneStr');

                //                // Execute JavaScript code
                //                oneStore.style.display = 'block';
                //                moreThanOneStr.style.display = 'none';
                //            ", true);
                //        }
                //        else
                //        {
                //            branchname.Visible = false;
                //            branchID = selBranches.Text;
                //            // RegisterStartupScript to execute JavaScript on page load
                //            ClientScript.RegisterStartupScript(this.GetType(), "SetVisibilityScript", $@"
                //            var oneStore = document.getElementById('oneStore');
                //            var moreThanOneStr = document.getElementById('moreThanOneStr');

                //            // Execute JavaScript code
                //            oneStore.style.display = 'none';
                //            moreThanOneStr.style.display = 'block';
                //        ", true);
                //        }
                //    }
                //}
                if (Request.Files.Count > 0)
                {
                    var result = ImportStock();
                    if (result != null && result.Count > 0)
                    {
                        SendResponse(String.Join("<br>", result.ToArray()), true);
                    }
                }
                gvImportProcessData.DataBind();
            }

        }

        private void SendResponse(string content, bool success = false)
        {
            Response.Clear();
            Response.Buffer = true;
            Response.Charset = "";
            var obj = new { result = (success ? 1 : 0), status = (success ? "Success" : "Error"), data = content };

            string jsoncontent = JsonConvert.SerializeObject(obj);

            Response.Write(jsoncontent);

            Response.Flush();
            Response.End();

        }


        //private void ExportGridToExcel()
        //{
        //    DataView dv = (DataView)SDSInventory.Select(DataSourceSelectArguments.Empty);
        //    DataTable dt = dv.ToTable();
        //    IWorkbook wb = new XSSFWorkbook();
        //    ISheet sheet = wb.CreateSheet("Inventory");
        //    ICreationHelper cH = wb.GetCreationHelper();
        //    int rows = 0;
        //    IRow rowH = sheet.CreateRow(rows++);
        //    string[] strFieldLabels = Array.Empty<string>();

        //     strFieldLabels = "stit_ID,ID|stit_SKU,Name|item_count,Stock|mrp,MRP|selling_price,Selling Price".Split('|');
        //     strFieldLabels = "stit_ID,ID|stit_SKU,Name|item_count,Stock|selling_price,Selling Price".Split('|');
        //    string priceLabel = ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "MRP" : "RRP";

        //    int stockIndex = Array.FindIndex(strFieldLabels, label => label.Contains("Stock"));
        //    Array.Resize(ref strFieldLabels, strFieldLabels.Length + 1);
        //    Array.Copy(strFieldLabels, stockIndex + 1, strFieldLabels, stockIndex + 2, strFieldLabels.Length - stockIndex - 2);
        //    strFieldLabels[stockIndex + 1] = "mrp," + priceLabel;

        //    //var strFieldLabels = "fsipc_code,Product Barcode/ERP ID|selling_price,Item Selling Price|item_count,Stock to sell online|discount_selling_price,Sell through others|selling_price,Price".Split('|');
        //    //List<KeyValuePair<string, object>> dataparams = new List<KeyValuePair<string, object>>();
        //    //dataparams.Add(new KeyValuePair<string, object>("storeId", this.CurrentUser.APIStoreId));
        //    //var dtStoreGroup = DataServiceMySql.GetDataTable($"SELECT br_ID, br_Name, bg.store_group_grosmartMerchant FROM finascop_branch b INNER JOIN finascop_branch_group bg ON b.br_storeGroup = bg.store_group_id WHERE bg.store_group_id  = @storeId", UserService.GetAPIConnectionString(), dataparams);
        //    //int grosmartStore = 0;

        //    //if (dtStoreGroup != null && dtStoreGroup.Rows.Count > 0)
        //    //{
        //    //    DataRow da = dtStoreGroup.Rows[0];
        //    //    string grosmart = da["store_group_grosmartMerchant"].ToString();
        //    //    grosmartStore = Convert.ToInt32(grosmart);

        //    //    if (grosmartStore == 1)
        //    //    {
        //    //        strFieldLabels = "fsipc_code,Product Barcode/ERP ID|selling_price,Item Selling Price|item_count,Stock to sell online|discount_selling_price,Sell through others price".Split('|');
        //    //    }
        //    //    else
        //    //    {
        //    //        strFieldLabels = "fsipc_code,Product Barcode/ERP ID|selling_price,Item Selling Price|item_count,Stock to sell online".Split('|');
        //    //    }
        //    //}
        //    //string priceLabel = ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "SKU MRP" : "SKU RRP";

        //    //// Find the index of "Product Barcode/ERP ID"
        //    //int barcodeIndex = Array.FindIndex(strFieldLabels, label => label.Contains("Product Barcode/ERP ID"));

        //    //// Insert the priceLabel after the barcodeIndex
        //    //Array.Resize(ref strFieldLabels, strFieldLabels.Length + 1);
        //    //Array.Copy(strFieldLabels, barcodeIndex + 1, strFieldLabels, barcodeIndex + 2, strFieldLabels.Length - barcodeIndex - 2);
        //    //strFieldLabels[barcodeIndex + 1] = "mrp," + priceLabel;

        //    foreach (string dc in strFieldLabels)
        //    {
        //        ICell cell = rowH.CreateCell(rowH.Cells.Count);
        //        cell.SetCellValue(cH.CreateRichTextString(dc.Split(',')[1]));
        //    }

        //    //foreach (DataRow dr in dt.Rows)
        //    //{
        //    //    IRow row = sheet.CreateRow(rows++);
        //    //    for (int j = 0; j < strFieldLabels.Length; j++)
        //    //    {
        //    //        ICell cell = row.CreateCell(j);
        //    //        string strField = strFieldLabels[j].Split(',')[0];
        //    //        cell.SetCellValue(cH.CreateRichTextString(dr[strField].ToString()));
        //    //    }
        //    //}

        //    Response.Clear();
        //    Response.Buffer = true;
        //    Response.Charset = "";
        //    Response.ContentType = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
        //    Response.AddHeader("content-disposition", "attachment;filename=Inventory.xlsx");
        //    wb.Write(Response.OutputStream);

        //    Response.Flush();
        //    Response.End();

        //}

        private void ExportGridToExcel()
        {
            string[] strFieldLabels = Array.Empty<string>();
            DataView dv = (DataView)SDSInventory.Select(DataSourceSelectArguments.Empty);
            DataTable dt = dv.ToTable();
            IWorkbook wb = new XSSFWorkbook();
            ISheet sheet = wb.CreateSheet("Inventory");
            ICreationHelper cH = wb.GetCreationHelper();
            int rows = 0;
            IRow rowH = sheet.CreateRow(rows++);
            List<KeyValuePair<string, object>> dataparams = new List<KeyValuePair<string, object>>();
            dataparams.Add(new KeyValuePair<string, object>("storeId", this.CurrentUser.APIStoreId));
            var dtStoreGroup = DataServiceMySql.GetDataTable($"SELECT br_ID, br_Name, bg.store_group_grosmartMerchant FROM finascop_branch b INNER JOIN finascop_branch_group bg ON b.br_storeGroup = bg.store_group_id WHERE bg.store_group_id  = @storeId", UserService.GetAPIConnectionString(), dataparams);
            int grosmartStore = 0;
            if (dtStoreGroup != null && dtStoreGroup.Rows.Count > 0)
            {
                DataRow da = dtStoreGroup.Rows[0];
                string grosmart = da["store_group_grosmartMerchant"].ToString();
                grosmartStore = Convert.ToInt32(grosmart);
                strFieldLabels = (grosmartStore == 1)
                ? "stit_ID,id|stit_SKU,Name|item_count,Stock|mrp,MRP|selling_price,Selling Price|discount_selling_price,Discount Selling Price".Split('|')
                : "stit_ID,id|stit_SKU,Name|item_count,Stock|mrp,MRP|selling_price,Selling Price".Split('|');
                if (ConfigurationManager.AppSettings.Get("CountryCode") != "IN")
                {
                    for (int i = 0; i < strFieldLabels.Length; i++)
                    {
                        strFieldLabels[i] = strFieldLabels[i].Replace("mrp,MRP", "mrp,RRP");
                    }
                }
            }

            foreach (string dc in strFieldLabels)
            {
                ICell cell = rowH.CreateCell(rowH.Cells.Count);
                cell.SetCellValue(cH.CreateRichTextString(dc.Split(',')[1]));
            }

            foreach (DataRow dr in dt.Rows)
            {
                IRow row = sheet.CreateRow(rows++);
                for (int j = 0; j < strFieldLabels.Length; j++)
                {
                    ICell cell = row.CreateCell(j);
                    string strField = strFieldLabels[j].Split(',')[0];
                    cell.SetCellValue(cH.CreateRichTextString(dr[strField].ToString()));
                }
            }

            Response.Clear();
            Response.Buffer = true;
            Response.Charset = "";
            Response.ContentType = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
            Response.AddHeader("content-disposition", "attachment;filename=Inventory.xlsx");
            wb.Write(Response.OutputStream);

            Response.Flush();
            Response.End();

        }

        protected void lbtnDownloadExcel_Click(object sender, EventArgs e)
        {
            ExportGridToExcel();
        }

        protected void SDSInventory_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["@storeId"].Value = this.CurrentUser.APIStoreId;
            //e.Command.Parameters["@user"].Value = Page.User.Identity.Name;
            if (Page.User.IsInRole("BranchManager"))
            {
                int brid = UserService.UserRoleBranchId;
                e.Command.Parameters["@BranchId"].Value = brid;

            }
            else
            {
                if (selBranches.Items.Count < 1)
                    selBranches.DataBind();

                if (selBranches.Items.Count == 2)
                    e.Command.Parameters["@BranchId"].Value = selBranches.Items[1].Value;
                else if (selBranches.Items.Count > 0 && !String.IsNullOrEmpty(selBranches.Text))
                    e.Command.Parameters["@BranchId"].Value = selBranches.Text;
            }
        }
        protected void SDSBranches_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
            if (Page.User.IsInRole("BranchManager"))
            {
                int brid = UserService.UserRoleBranchId;
                e.Command.Parameters["branchid"].Value = brid;
            }

        }
        protected void selBranches_DataBound(object sender, EventArgs e)
        {
            if (selBranches.Items.Count > 1)
            {
                //ltrBranchName.Visible = selBranches.Items.Count == 2;
                //ltrBranchName.Text = selBranches.Items[1].Text;
                plcSelectBranchModel.Visible = selBranches.Items.Count > 1;

            }

        }

        /// <summary>
        /// This method is supposed for Ajax call and not by page postback. 
        /// The variables submitted will be from the ajax post so that it cannod call the controls directly.
        /// </summary>
        /// <returns>Import status string array</returns>
        private List<string> ImportStock()
        {
            List<string> strResults = new List<string>();
            if (Request.Files == null || Request.Files.Count <= 0)
            {
                SendResponse("No file selected. Please select the inventory excel file and upload");
                return default;
            }
            string brid = Request.Form["brid"];
            int branchId = 0;
            // Get branches of the store group that the current user is linked with.
            List<KeyValuePair<string, object>> getbranchParam = new List<KeyValuePair<string, object>>();
            getbranchParam.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));
            var dtbranch = DataServiceMySql.GetDataTable($"SELECT * FROM finascop_branch WHERE br_storegroup = @storegroupid", UserService.GetAPIConnectionString(), getbranchParam);
            if (dtbranch == null || dtbranch.Rows.Count <= 0)
            {
                SendResponse("Failure!! Invalid Store. The store is not active or you don't have enough permission to execute the action.");
                return default;
            }

            if (!String.IsNullOrEmpty(brid))
            {
                // if store group contains only one branch then the brid form data will be -1. Take the first branch from the get branches by store group above.
                if (brid == "-1")
                {
                    // If branches count is more than 1 then it is mandatory that user select the desired store for import stock.
                    if (dtbranch.Rows.Count > 1)
                    {
                        SendResponse("Invalid Store. Please select the store to import stock");
                        return default;
                    }
                    try { branchId = Convert.ToInt32(dtbranch.Rows[0]["br_ID"]); } catch { branchId = 0; }
                }
                else
                {
                    var drs = dtbranch.Select($"br_ID = {brid}");
                    if (drs.Length > 0)
                        try { branchId = Convert.ToInt32(drs[0]["br_ID"]); } catch { branchId = 0; }
                }
            }

            if (branchId <= 0)
            {
                SendResponse("Invalid Store. Please select the store to import stock");
                return default;
            }

            var file = Request.Files[0];
            var strFile = file.FileName;
            string ext = Path.GetExtension(file.FileName);
            if (!".xlsx, .xlsm, .xls".Split(',').Contains(ext))
            {
                SendResponse("Failure!! Invalid file selected. Please upload a valid excel file");
            }

            IWorkbook wb = new XSSFWorkbook(file.InputStream);
            var dtStoreGroup = DataServiceMySql.GetDataTable($"SELECT br_ID, br_Name, bg.store_group_grosmartMerchant FROM finascop_branch b INNER JOIN finascop_branch_group bg ON b.br_storeGroup = bg.store_group_id WHERE bg.store_group_id  = {this.CurrentUser.APIStoreId}", UserService.GetAPIConnectionString());
            int grosmartStore = 0;
            if (dtStoreGroup != null && dtStoreGroup.Rows.Count > 0)
            {
                DataRow da = dtStoreGroup.Rows[0];
                string grosmart = da["store_group_grosmartMerchant"].ToString();
                grosmartStore = Convert.ToInt32(grosmart);

                if (grosmartStore == 1)
                {
                    if (wb.NumberOfSheets < 1 || wb.GetSheetAt(0).PhysicalNumberOfRows < 2 || wb.GetSheetAt(0).GetRow(0).PhysicalNumberOfCells < 5)
                    {
                        SendResponse("Failure!! Insufficient data in the document selected. Please upload excel with single sheet, contain more than 1 row and minimum 5 columns. Please refer the sample excel available using the link available in the page.");
                        return default;
                    }
                }
                else
                {
                    if (wb.NumberOfSheets < 1 || wb.GetSheetAt(0).PhysicalNumberOfRows < 2 || wb.GetSheetAt(0).GetRow(0).PhysicalNumberOfCells < 4)
                    {
                        SendResponse("Failure!! Insufficient data in the document selected. Please upload excel with single sheet, contain more than 1 row and minimum 5 columns. Please refer the sample excel available using the link available in the page.");
                        return default;
                    }
                }
            }




            int importcount = 0, failureCount = 0;
            var sheet = wb.GetSheetAt(0);
            var headerrow = sheet.GetRow(0);
            int index_id = -1, index_stock = -1, index_mrp = -1, index_sellingPrice = -1, index_sellthrougothers = -1, index_rrp=-1;
            if (headerrow != null)
            {
                for (int i = 0; i <= 6; i++)
                {
                    ICell cell = headerrow.GetCell(i, MissingCellPolicy.RETURN_BLANK_AS_NULL);
                    if (cell == null)
                        continue;
                    //string val = (cell.CellType == CellType.Numeric ? cell.NumericCellValue.ToString() : cell.StringCellValue.Replace("{", "").Replace("}", ""));
                    string strVal = cell.StringCellValue.Replace("{", "").Replace("}", "").Trim().Replace(" ", "").ToLower();
                    if (!string.IsNullOrEmpty(strVal))
                    {
                        strVal = strVal.Trim().ToLower();
                        if (strVal == "id")
                            index_id = i;
                        else if (strVal == "stock")
                            index_stock = i;
                        else if ((ConfigurationManager.AppSettings["CountryCode"] == "IN" && strVal == "mrp") ||
                         (ConfigurationManager.AppSettings["CountryCode"] != "IN" && strVal == "rrp"))
                        {
                            if (ConfigurationManager.AppSettings["CountryCode"] == "IN" && strVal == "mrp")
                                index_mrp = i;
                            else
                                index_rrp = i;
                        }
                        else if (strVal == "selling price" || strVal == "sellingprice")
                            index_sellingPrice = i;
                        else if (strVal == "discountsellingprice")
                            index_sellthrougothers = i;
                    }
                    //if (strVal == "productbarcode/erpid")
                    //    index_id = i;
                    //else if (strVal == "skumrp" || strVal == "skurrp")
                    //    index_mrp = i;
                    //else if (strVal == "itemsellingprice")
                    //    index_sellingPrice = i;
                    //else if (strVal == "stocktosellonline")
                    //    index_stock = i;

                    //else if (strVal == "sellthroughothersprice")
                    //    index_sellthrougothers = i;
                }
            }
            string missingFields = (index_id < -1 ? "productbarcode/erpid" : "") +
                        (index_mrp < 0 && ConfigurationManager.AppSettings["CountryCode"] == "IN" ? " ,skumrp" : "") +
                        (index_rrp < 0 && ConfigurationManager.AppSettings["CountryCode"] != "IN" ? " ,skurrp" : "") +
                        (index_sellingPrice < 0 ? " ,itemsellingprice" : "") +
                        (index_stock < 0 ? " ,stocktosellonline" : "") +
                        (grosmartStore == 1 && index_sellthrougothers < 0 ? " ,sellthroughothersprice" : "");

            if (!string.IsNullOrEmpty(missingFields))
            {
                SendResponse("Failure!! Missing fields in file. Please ensure that the excel having header row with the missing fields " + missingFields);
                return default;
            }

            int itemId = 0, itemStock = 0, packageTypeId = 0, barcodeErpId = 0;
            string mrp = "", expressdelivery = "", courierdelivery = "", pickup = "";
            double finalexpressdelivery = 0, itemMMG = 0, dsellPrice = 0, itemMRP = 0, itemLandingCost = 0, sellPrice = 0, tlMrp = 0, discount_selling_price = 0;
            List<SuccessfulItem> successfulItems = new List<SuccessfulItem>();
            List<UnsuccessfulItem> unsuccessfulItems = new List<UnsuccessfulItem>();
            //int totalRows = sheet.LastRowNum;
            int totalRows = 0;
            int successCount = 0;
            for (int i = 1; i < sheet.PhysicalNumberOfRows; i++)
            {
                /*string stit_id="", stock="", mrp="", sellingPrice="";*/ //, itemname=""

                string sellingPrice = "", stock = "", discountSP = "", stitemId = "";
                int currentRow = i + 1;
                try
                {
                    var row = sheet.GetRow(i);
                    if (row == null || row.Cells.All(c => c.CellType == CellType.Blank || string.IsNullOrWhiteSpace(c.ToString())))
                    {
                        continue;
                    }
                                       
                     totalRows++;                    
                    ICell cell_Id = row.GetCell(index_id, MissingCellPolicy.RETURN_BLANK_AS_NULL);
                    if (cell_Id == null)
                    {
                        stitemId = string.Empty;
                    }
                    else
                    {
                        stitemId = (cell_Id.CellType == CellType.Numeric ? cell_Id.NumericCellValue.ToString() : cell_Id.StringCellValue.Replace("{", "").Replace("}", "")); // stit_id or erp id
                        itemId = 0; try { itemId = Convert.ToInt32(stitemId); } catch { itemId = 0; }
                    }
                    int indexToUse = (index_mrp >= 0 && ConfigurationManager.AppSettings["CountryCode"] == "IN") ? index_mrp : index_rrp;
                    ICell cell_mrp = row.GetCell(indexToUse, MissingCellPolicy.RETURN_BLANK_AS_NULL);
                    if (cell_mrp == null)
                    {
                        mrp = string.Empty;
                    }
                    else
                    {

                        mrp = (cell_mrp.CellType == CellType.Numeric ? cell_mrp.NumericCellValue.ToString() : cell_mrp.StringCellValue.Replace("{", "").Replace("}", ""));
                        tlMrp = 0; try { tlMrp = Convert.ToDouble(mrp); } catch { tlMrp = 0; }
                    }

                    ICell cell_sellingPrice = row.GetCell(index_sellingPrice, MissingCellPolicy.RETURN_BLANK_AS_NULL);
                    if (cell_sellingPrice == null)
                    {
                        sellingPrice = string.Empty;
                    }
                    else
                    {
                        sellingPrice = (cell_sellingPrice.CellType == CellType.Numeric ? cell_sellingPrice.NumericCellValue.ToString() : cell_sellingPrice.StringCellValue.Replace("{", "").Replace("}", ""));
                        sellPrice = 0; try { sellPrice = Convert.ToDouble(sellingPrice); } catch { sellPrice = 0; }
                    }

                    ICell cell_stock = row.GetCell(index_stock, MissingCellPolicy.RETURN_BLANK_AS_NULL);
                    if (cell_stock == null)
                    {
                        stock = string.Empty;
                    }
                    else
                    {
                        stock = (cell_stock.CellType == CellType.Numeric ? cell_stock.NumericCellValue.ToString() : cell_stock.StringCellValue.Replace("{", "").Replace("}", ""));
                        itemStock = 0; try { itemStock = Convert.ToInt32(stock); } catch { itemStock = 0; }
                    }


                    if (grosmartStore == 1)
                    {
                        ICell cell_dsellingPrice = row.GetCell(index_sellthrougothers, MissingCellPolicy.RETURN_BLANK_AS_NULL);
                        discountSP = (cell_dsellingPrice.CellType == CellType.Numeric ? cell_dsellingPrice.NumericCellValue.ToString() : cell_dsellingPrice.StringCellValue.Replace("{", "").Replace("}", ""));
                        dsellPrice = 0; try { dsellPrice = Convert.ToDouble(discountSP); } catch { dsellPrice = 0; }
                    }
                    //var stitIdDt = DataServiceMySql.GetDataTable($"SELECT fsipc_stit_id FROM finascop_stock_itemmaster_product_codes WHERE fsipc_code= {barcodeErpId} AND fsipc_storeGroup={this.CurrentUser.APIStoreId}", UserService.GetAPIConnectionString());
                    //if (stitIdDt != null && stitIdDt.Rows.Count > 0)
                    //{
                    //    DataRow da = stitIdDt.Rows[0];
                    //    stitemId = da["fsipc_stit_id"].ToString();
                    //    itemId = Convert.ToInt32(stitemId);
                    //}
                    //else
                    //{
                    //    //SendResponse($"Failure!! Error processing row {currentRow}: ERP ID / Barcode not available in the product details.");
                    //    //return default;
                    //}
                    //if (barcodeErpId <= 0)
                    //{
                    //    SendResponse($"Failure at row {i}, invalid data format. Product id field is wrong. Please ensure it added with numeric value");
                    //    return default;
                    //}

                    if (tlMrp <= 0)
                    {
                        string labelPrice = "";
                        if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
                        {
                            labelPrice = "RRP";
                        }
                        else
                        {
                            labelPrice = "MRP";
                        }
                        SendResponse($"Failure," + " " + labelPrice + " " + "should not be 0.");
                        return default;
                    }

                    if (sellPrice > tlMrp)
                    {
                        string labelPrice = "";
                        if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
                        {
                            labelPrice = "RRP";
                        }
                        else
                        {
                            labelPrice = "MRP";
                        }
                        //Common.ShowCustomAlert(this.Page, "Failed", "Check MRP & Selling Price", false);
                        SendResponse($"Failure, Selling Price should be less than or equal to" + " " + labelPrice);
                        return default;
                    }

                    if (itemStock == 0)
                    {
                        SendResponse($"Failure, Stock cannot be 0.");
                        return default;
                    }

                    //itemname = row.GetCell(1).StringCellValue; // Optional. Not required to import
                    int storegroupid = this.CurrentUser.APIStoreId;


                    List<KeyValuePair<string, object>> mmfFactorParams = new List<KeyValuePair<string, object>>();
                    mmfFactorParams.Add(new KeyValuePair<string, object>("stitid", itemId));

                    int brandId = 0, catId = 0, itemmId = 0;
                    var itemTable = DataServiceMySql.GetDataTable($"SELECT stit_itemId, pdt_brand, product_category FROM finascop_stock_itemmaster WHERE stit_ID = @stitid", UserService.GetAPIConnectionString(), mmfFactorParams);
                    if (itemTable != null && itemTable.Rows.Count > 0)
                    {
                        DataRow da = itemTable.Rows[0];
                        brandId = Convert.ToInt32(da["pdt_brand"]);
                        catId = Convert.ToInt32(da["product_category"]);
                        itemmId = Convert.ToInt32(da["stit_itemId"]);
                    }
                    mmfFactorParams.Add(new KeyValuePair<string, object>("brandId", brandId));
                    mmfFactorParams.Add(new KeyValuePair<string, object>("categoryId", catId));
                    mmfFactorParams.Add(new KeyValuePair<string, object>("itemId", itemmId));

                    var mmfFactorSKU = DataServiceMySql.GetDataTable($"SELECT mm_factor FROM minimum_margin_range WHERE mm_type = 1 AND mm_detail =@stitid", UserService.GetAPIConnectionString(), mmfFactorParams);


                    string mmf_factorSKU = "", spffactorBrand = "", spfFactorItem = "", spfFactorCS = "";
                    double mmFactor = 0, mmf_Factor = 0, spf_factorBrand = 0, mmfFactor = 0, factorItem = 0, factorSC = 0;

                    if (mmfFactorSKU != null && mmfFactorSKU.Rows.Count > 0)
                    {
                        mmf_factorSKU = mmfFactorSKU.Rows[0]["mm_factor"].ToString();
                        mmFactor = Convert.ToDouble(mmf_factorSKU);
                        if (mmFactor > 0)
                        {
                            mmfFactor = mmFactor;
                        }
                    }
                    else
                    {
                        var mmfFactorBrand = DataServiceMySql.GetDataTable($"SELECT mm_factor FROM minimum_margin_range WHERE mm_type = 2 AND mm_detail = @brandId", UserService.GetAPIConnectionString(), mmfFactorParams);
                        if (mmfFactorBrand != null && mmfFactorBrand.Rows.Count > 0)
                        {
                            spffactorBrand = mmfFactorBrand.Rows[0]["mm_factor"].ToString();
                            spf_factorBrand = Convert.ToDouble(spffactorBrand);
                            if (spf_factorBrand > 0)
                            {
                                mmfFactor = spf_factorBrand;
                            }
                        }

                        else
                        {
                            var mmf_factorItem = DataServiceMySql.GetDataTable($"SELECT mm_factor FROM minimum_margin_range WHERE mm_type = 3 AND mm_detail = @itemId", UserService.GetAPIConnectionString(), mmfFactorParams);
                            if (mmf_factorItem != null && mmf_factorItem.Rows.Count > 0)
                            {
                                spfFactorItem = mmf_factorItem.Rows[0]["mm_factor"].ToString();
                                factorItem = Convert.ToDouble(spfFactorItem);
                                if (factorItem > 0)
                                {
                                    mmfFactor = factorItem;
                                }
                            }
                            else
                            {
                                var mmf_factorSC = DataServiceMySql.GetDataTable($"SELECT mm_factor FROM minimum_margin_range WHERE mm_type = 4 AND mm_detail = @categoryId", UserService.GetAPIConnectionString(), mmfFactorParams);
                                if (mmf_factorSC != null && mmf_factorSC.Rows.Count > 0)
                                {
                                    spfFactorCS = mmf_factorSC.Rows[0]["mm_factor"].ToString();
                                    factorSC = Convert.ToDouble(spfFactorCS);
                                    if (factorSC > 0)
                                    {
                                        mmfFactor = factorSC;
                                    }
                                }
                            }
                        }
                    }

                    double mmfFactorCalc = 0, mmfFatCal = 0;
                    string mmfFactcal = "";

                    if (mmfFactor > 1)
                    {
                        mmfFactorCalc = mmfFactor;
                    }
                    else
                    {
                        var sysConfigTbll = DataServiceMySql.GetDataTable($"SELECT cfg_Name, cfg_Value, cfg_Type, cfg_Enabled FROM sys_configuration WHERE cfg_Name='DEFAULT_MM'", UserService.GetAPIConnectionString());
                        if (sysConfigTbll != null && sysConfigTbll.Rows.Count > 0)
                        {
                            mmfFactcal = sysConfigTbll.Rows[0]["cfg_Value"].ToString();
                            mmfFatCal = Convert.ToDouble(mmfFactcal);
                            mmfFactorCalc = mmfFatCal;
                        }
                    }




                    List<KeyValuePair<string, object>> countParams = new List<KeyValuePair<string, object>>();
                    countParams.Add(new KeyValuePair<string, object>("itemId", itemId));
                    countParams.Add(new KeyValuePair<string, object>("branchId", branchId));
                    countParams.Add(new KeyValuePair<string, object>("stitid", itemId));
                    var fsbiCount = DataServiceMySql.GetDataTable($"SELECT id FROM finascop_stock_branch_inventory WHERE stit_id = @itemId  AND branch_id = @branchId ", UserService.GetAPIConnectionString(), countParams);

                    var gst = DataServiceMySql.GetDataTable($"SELECT IFNULL(stit_GST, 0) AS stit_GST FROM finascop_stock_itemmaster where stit_ID = @stitid", UserService.GetAPIConnectionString(), countParams);

                    double desiredMargin = 0, calculatedSP = 0, grozeoMargin = 0;
                    if (grosmartStore == 1 && dsellPrice != 0)
                    {
                        if (dsellPrice > 0)
                        {
                            itemLandingCost = dsellPrice;
                            itemMMG = (Math.Round(sellPrice, 2)) - (Math.Round(itemLandingCost, 2)); //margin
                            desiredMargin = sellPrice * mmfFactorCalc / 100; //MRP*MM%
                            if (itemMMG >= desiredMargin)
                            {
                                discount_selling_price = dsellPrice;
                                calculatedSP = sellPrice; //textMrp - (itemMMG * spfFactorCalc / 100); //MRP - (MARGIN*SellingPriceFactor%)
                                grozeoMargin = calculatedSP - itemLandingCost; //(calculatedSP - landingCost)
                            }
                            else
                            {
                                //discount_selling_price = 0;
                                //calculatedSP = 0;
                                //grozeoMargin = 0;
                                //ShowSuccess("Warning!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Please check discount selling price.</a></h5>");
                                Common.ShowToastifyMessage(this.Page, "Please check discount selling price.", "danger");
                            }
                        }
                    }

                    else
                    {
                        itemLandingCost = sellPrice;
                        itemMMG = Math.Round(tlMrp - (itemLandingCost), 2);
                        discount_selling_price = 0;
                        calculatedSP = 0;
                        grozeoMargin = 0;
                    }


                    double itemmrp = 0; try { itemmrp = tlMrp; } catch { itemmrp = 0; }
                    double pStock = itemStock;

                    //if (pStock <= 0)
                    //    allWithStockAndPrice = false;

                    string fpod_poMMGleastSKU = Convert.ToString(itemMMG);
                    double fpod_spHmDel = 0, fpod_spPikup = 0;


                    double fcpod_spHmDel = Math.Round(calculatedSP, 2);
                    double fcpod_spCouDel = Math.Round(calculatedSP, 2);
                    double fcpod_spPikup = Math.Round(calculatedSP, 2);


                    double fpod_spetHmDel = (fcpod_spHmDel * 100) / (100 + ((double)gst.Rows[0]["stit_GST"]));
                    double fcpod_spetHmDel = Math.Round(fpod_spetHmDel, 2);
                    double fpod_spetCouDel = (fcpod_spCouDel * 100) / (100 + ((double)gst.Rows[0]["stit_GST"]));
                    double fcpod_spetCouDel = Math.Round(fpod_spetCouDel, 2);
                    double fpod_spetPikup = (fcpod_spPikup * 100) / (100 + ((double)gst.Rows[0]["stit_GST"]));
                    double fcpod_spetPikup = Math.Round(fpod_spetPikup, 2);

                    double margin = 0;
                    try
                    {
                        if (tlMrp > 0 && itemLandingCost > 0 && tlMrp > itemLandingCost)
                            margin = (100 - ((itemLandingCost * 100) / tlMrp));
                    }
                    catch { margin = 0; }

                    List<KeyValuePair<string, object>> sqlparams = new List<KeyValuePair<string, object>>();
                    sqlparams.Add(new KeyValuePair<string, object>("stitid", itemId));
                    sqlparams.Add(new KeyValuePair<string, object>("brid", branchId));
                    sqlparams.Add(new KeyValuePair<string, object>("itemCount", itemStock));
                    sqlparams.Add(new KeyValuePair<string, object>("mrp", tlMrp));
                    sqlparams.Add(new KeyValuePair<string, object>("sellingPrice", sellPrice));
                    sqlparams.Add(new KeyValuePair<string, object>("expressdelivery", fpod_spetHmDel));
                    sqlparams.Add(new KeyValuePair<string, object>("courierdelivery", fcpod_spCouDel));
                    sqlparams.Add(new KeyValuePair<string, object>("pickup", fcpod_spPikup));
                    sqlparams.Add(new KeyValuePair<string, object>("itemMMG", fpod_poMMGleastSKU));
                    sqlparams.Add(new KeyValuePair<string, object>("discountSP", discount_selling_price));

                    String sqlUpdateSql = @"UPDATE finascop_stock_branch_inventory 
                        SET item_count = @itemCount, mrp = @mrp, selling_price = @sellingPrice, fpod_leastSKUmrp = @mrp, 
                        fpod_customerRateHmDel = @expressdelivery, fpod_customerRateCouDel = @courierdelivery, 
                        fpod_customerRatePikup = @pickup, fpod_poLandingCostleastSKU = @sellingPrice, fpod_poMMGleastSKU = @itemMMG, discount_selling_price = @discountSP
                        WHERE branch_id = @brid AND stit_id = @stitid";
                    updatecount = DataServiceMySql.ExecuteSql(sqlUpdateSql, UserService.GetAPIConnectionString(), sqlparams);
                    string comment = "";
                    if (updatecount <= 0)
                    {
                        comment = $"No data updated for erpid: {barcodeErpId} as the system cannot match the data.";
                        strResults.Add(comment);
                        unsuccessfulItems.Add(new UnsuccessfulItem
                        {
                            stitid = itemId,
                            ttlmrp = tlMrp,
                            brid = branchId,
                            Comment = comment,
                        });
                    }
                    else
                    {
                        ////{ importcount}
                        successCount++;
                        importcount++;
                        successfulItems.Add(new SuccessfulItem
                        {
                            stitid = itemId,
                            brid = branchId,
                            stock = itemStock,
                            mrp = tlMrp,
                            sellingP = sellPrice,
                            dSellingP = discount_selling_price,
                        });
                        // SendResponse("Success!! File has been processed successfully", true);

                    }
                }
                catch (Exception ex)
                {
                    failureCount++;
                    //strResults.Add($"No product is available with this ERP ID / Barcode");
                    strResults.Add($"Error  MRP / Stock: {itemStock}, No product is available with this MRP / Stock");
                }

            }
            int failedCount = failureCount; //totalRows - importcount;
            List<KeyValuePair<string, object>> insertparams = new List<KeyValuePair<string, object>>();
            //insertparams.Add(new KeyValuePair<string, object>("stitid", itemId));
            insertparams.Add(new KeyValuePair<string, object>("brid", branchId));
            string currentdatetime = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
            insertparams.Add(new KeyValuePair<string, object>("totalCount", totalRows));
            insertparams.Add(new KeyValuePair<string, object>("missedCount", failedCount));
            insertparams.Add(new KeyValuePair<string, object>("successCount", successCount));
            insertparams.Add(new KeyValuePair<string, object>("filename", strFile));
            insertparams.Add(new KeyValuePair<string, object>("createdOn", currentdatetime));

            string strInsertSql1 = $"INSERT INTO finascop_stock_branch_inventory_upload(fbiu_branch, fbiu_createdOn, fbiu_updatedOn, totalCount, missedCount, successCount, filename) " +
                    $"VALUES(@brid, @createdOn, @createdOn, @totalCount, @missedCount, @successCount, @filename); SELECT LAST_INSERT_ID()";
            var result = DataServiceMySql.ExecuteScalar(strInsertSql1, Service.UserService.GetAPIConnectionString(), insertparams);
            int lastInsertId = Convert.ToInt32(result);
            if (lastInsertId > 0)
            {
                foreach (var successfulItem in successfulItems)
                {
                    if (successfulItem.stitid > 0 && updatecount > 0)
                    {
                        insertparams.Add(new KeyValuePair<string, object>("lastInserId", lastInsertId));
                        insertparams.Add(new KeyValuePair<string, object>("stitid", successfulItem.stitid));
                        insertparams.Add(new KeyValuePair<string, object>("branchid", successfulItem.brid));
                        insertparams.Add(new KeyValuePair<string, object>("itemCount", successfulItem.stock));
                        insertparams.Add(new KeyValuePair<string, object>("mrp", successfulItem.mrp));
                        insertparams.Add(new KeyValuePair<string, object>("sellingPrice", successfulItem.sellingP));
                        insertparams.Add(new KeyValuePair<string, object>("discountSP", successfulItem.dSellingP));
                        string strInsertSql2 = $"INSERT INTO finascop_stock_branch_inventory_upload_detail(fbiu_id, stit_id, branch_id, item_count, mrp, selling_price, discount_selling_price) " +
                                $"VALUES(@lastInserId, @stitid, @branchid, @itemCount, @mrp, @sellingPrice, @discountSP)";
                        DataServiceMySql.ExecuteSql(strInsertSql2, UserService.GetAPIConnectionString(), insertparams);

                        insertparams.Clear();
                    }
                }

                foreach (var unsuccessfulItem in unsuccessfulItems)
                {
                    if (unsuccessfulItem.stitid > 0 || unsuccessfulItem.stitid == 0 && updatecount < 0)
                    {
                        List<KeyValuePair<string, object>> intparams = new List<KeyValuePair<string, object>>();
                        intparams.Add(new KeyValuePair<string, object>("lastId", lastInsertId));
                        intparams.Add(new KeyValuePair<string, object>("skuId", unsuccessfulItem.stitid));
                        intparams.Add(new KeyValuePair<string, object>("mrp", unsuccessfulItem.ttlmrp));
                        intparams.Add(new KeyValuePair<string, object>("brId", unsuccessfulItem.brid));
                        intparams.Add(new KeyValuePair<string, object>("comment", unsuccessfulItem.Comment));
                        string strInsertSql3 = $"INSERT INTO inventory_upload_error_log(fbiu_id, stit_id, branch_id, mrp, comment) " +
                                $"VALUES(@lastId, @skuId, @brId, @mrp, @comment)";
                        DataServiceMySql.ExecuteSql(strInsertSql3, UserService.GetAPIConnectionString(), intparams);

                        intparams.Clear();
                    }
                }
                gvImportProcessData.DataBind();
            }
            if (failureCount > 0)
                strResults.Insert(0, $"Errors: {failureCount}");
            strResults.Insert(0, $"{importcount} records imported.");

            return strResults;

        }

        protected void gvImportProcessData_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvImportProcessData.PageIndex * gvImportProcessData.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvImportProcessData.Rows.Count - 1;
            //ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSBulkImport.Select(DataSourceSelectArguments.Empty);
        }

        protected void SDSBulkImport_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            string brId = selBranches.Text;
            e.Command.Parameters["branchid"].Value = brId;
        }

        protected void btnAction_Click(object sender, EventArgs e)
        {
            Button btn = (Button)sender;
            hidId.Value = (btn.Attributes["fbiu_id"]);
            string fbiu_id = hidId.Value;
            string branchId = (btn.Attributes["branchId"]);
            string datetime = (btn.Attributes["dateTime"]);
            string totalcount = (btn.Attributes["totalcount"]);
            string successcount = (btn.Attributes["successcount"]);
            string missedcount = (btn.Attributes["failedcount"]);
            string filename = (btn.Attributes["filename"]);
            string branchname = "";
            List<KeyValuePair<string, object>> branchParams = new List<KeyValuePair<string, object>>();
            branchParams.Add(new KeyValuePair<string, object>("branchId", branchId));
            var branchTable = DataServiceMySql.GetDataTable($"SELECT br_Name FROM finascop_branch WHERE br_ID = @branchId", UserService.GetAPIConnectionString(), branchParams);
            if (branchTable != null && branchTable.Rows.Count > 0)
            {
                DataRow da = branchTable.Rows[0];
                branchname = da["br_Name"].ToString();
            }

            ltrDate.Text = datetime;
            ltrTtlRecords.Text = totalcount;
            ltrSuccess.Text = successcount;
            ltrFailed.Text = missedcount;
            ltrFileName.Text = filename;
            ltrStoreName.Text = branchname;

            //popup Action
            string strAlertSCript = "$('#ErrorDetails').modal('show');";
            strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";
            System.Type cstype = this.GetType();
            String csname1 = "ShowConfirmPopup";
            ClientScriptManager cs = this.ClientScript;
            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
            cstext1.Append("script>");
            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }
    }
}

