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

namespace RetalineProAgent
{
    public partial class UploadStock: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            if (Request.Files.Count > 0)
            {
                var result = ImportStock();
                if(result != null && result.Count > 0)
                {
                    SendResponse(String.Join("<br>", result.ToArray()), true);
                }
                

                #region Deleted code - Import
                //var file = Request.Files[0];

                //var strFile = file.FileName;

                //string ext = Path.GetExtension(file.FileName);
                //if (!".xlsx, .xlsm, .xls".Split(',').Contains(ext))
                //{
                //    SendResponse("Failure!! Invalid file selected. Please upload a valid excel file");

                //}

                //IWorkbook wb = new XSSFWorkbook(file.InputStream);

                //if (wb.NumberOfSheets < 1 || wb.GetSheetAt(0).PhysicalNumberOfRows < 2 || wb.GetSheetAt(0).GetRow(0).PhysicalNumberOfCells < 5)
                //{
                //    SendResponse("Failure!! Insufficient data in the document selected. Please upload excel with single sheet, contain more than 1 row and minimum 5 columns. Please refer the sample excel available using the link available in the page.");
                //    return;
                //}

                //var sheet = wb.GetSheetAt(0);
                //for (int i = 1; i < sheet.PhysicalNumberOfRows; i++)
                //{
                //    var row = sheet.GetRow(i);
                //    string stit_id = row.GetCell(0).StringCellValue; // stit_id or erp id
                //    string itemname = row.GetCell(1).StringCellValue; // Optional. Not required to import
                //    string stock = row.GetCell(2).StringCellValue; // stock value to be updated
                //    string mrp = row.GetCell(3).StringCellValue; // mrp of the stock item.
                //    string sellingPrice = row.GetCell(4).StringCellValue; // selling price of the stock item.
                //    // Update stock record with the data
                //    int itemId = Convert.ToInt32(stit_id);
                //    int itemStock = Convert.ToInt32(stock);
                //    decimal tlMrp = Convert.ToDecimal(mrp);
                //    decimal sellPrice = Convert.ToDecimal(sellingPrice);
                //    int storegroupid = this.CurrentUser.APIStoreId;
                //    var dtBranches = DataServiceMySql.GetDataTable($"SELECT br_ID FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
                //    string brId = null;
                //    foreach (DataRow dr in dtBranches.Rows)
                //    {
                //        brId = dr["br_ID"].ToString();
                //    }
                //    int branchId = Convert.ToInt32(brId);
                //    //string strUpdateSql1 = $"UPDATE finascop_stock_branch_inventory SET item_count = 0 WHERE branch_id = {branchId}";
                //    //DataServiceMySql.ExecuteSql(strUpdateSql1, UserService.GetAPIConnectionString());
                //    var dtStock = DataServiceMySql.GetDataTable($"SELECT bi.id, bi.stit_id, bi.item_count, ud.fbiu_id, iu.fbiu_status FROM finascop_stock_branch_inventory bi " +
                //        $"INNER JOIN finascop_stock_branch_inventory_upload iu ON branch_id = fbiu_branch INNER JOIN finascop_stock_branch_inventory_upload_detail ud " +
                //        $"ON ud.branch_id = bi.branch_id WHERE bi.branch_id  = {branchId}", UserService.GetAPIConnectionString());
                //    int invtUpload = 0;
                //    string status = null;
                //    if (dtStock != null && dtStock.Rows.Count > 0)
                //    {
                //        invtUpload = (int)dtStock.Rows[0]["fbiu_id"];
                //        status = dtStock.Rows[0]["fbiu_status"].ToString();
                //    }


                //    int uploadStatus = Convert.ToInt32(status);
                //    int upload = invtUpload;

                //    var uploadCount = DataServiceMySql.GetDataTable($"SELECT COUNT(*) AS cnt FROM finascop_stock_branch_inventory_upload_detail WHERE fbiu_id = {invtUpload}", UserService.GetAPIConnectionString());
                //    var stockDetails = DataServiceMySql.GetDataTable($"SELECT fbiu_id,stit_id,branch_id,item_count,mrp,selling_price,fsbg_id " +
                //        $"FROM finascop_stock_branch_inventory_upload_detail WHERE fbiu_id = {upload}", UserService.GetAPIConnectionString());

                //    //var bmdDetails = DataServiceMySql.GetDataTable($"SELECT bmd_id, bmd_name, bmd_company, bmd_hub, bmd_incentive, bmd_technology, bmd_customer, bmd_cs, bmd_distributor, " +
                //    //    $"bmd_retailor, bmd_management, bmd_promotion, bmd_logistics, bmd_driver, bmd_courier, bmd_pickup, bmd_bank, status, " +
                //    //    $"is_default, created_by, created_on, updated_by, updated_on  FROM retaline_margindistributions WHERE is_default = 1", UserService.GetAPIConnectionString());
                //    //int brnId = Convert.ToInt32(brId);
                //    var stockDetl = DataServiceMySql.GetDataTable($"SELECT fbiu_id,stit_id,branch_id,item_count,mrp,selling_price,fsbg_id FROM finascop_stock_branch_inventory_upload_detail WHERE fbiu_id = {upload}", UserService.GetAPIConnectionString());
                //    if (uploadCount != null && stockDetl.Rows.Count > 0)
                //    {
                //        foreach (DataRow dr in stockDetails.Rows)
                //        {
                //            //string strUpdateSql1 = $"UPDATE finascop_stock_branch_inventory SET item_count = 0 WHERE branch_id = {branchId}";
                //            //DataServiceMySql.ExecuteSql(strUpdateSql1, UserService.GetAPIConnectionString());
                //            string fsbgId = null;
                //            int itemCount = 0;
                //            float price = 0;
                //            if (stockDetl != null && stockDetl.Rows.Count > 0)
                //                fsbgId = stockDetl.Rows[0]["fsbg_id"].ToString();
                //            itemCount = (int)stockDetl.Rows[0]["item_count"];
                //            price = (float)stockDetl.Rows[0]["mrp"];
                //            int bgId = Convert.ToInt32(fsbgId);
                //            var fsbiCount = DataServiceMySql.GetDataTable($"SELECT id FROM finascop_stock_branch_inventory WHERE stit_id = {itemId} AND branch_id = {branchId} AND fsbg_id = {bgId}", UserService.GetAPIConnectionString());
                //            int id = 0;
                //            if (fsbiCount != null && fsbiCount.Rows.Count > 0)
                //                id = (int)fsbiCount.Rows[0]["id"];
                //            decimal totalMrp = Convert.ToDecimal(mrp);

                //            decimal itemMRP = 0;
                //            decimal itemLanding = sellPrice;
                //            int itemLandingCost = Convert.ToInt32(itemLanding);

                //            itemMRP = totalMrp;

                //            decimal itemMM = (itemMRP - itemLanding);
                //            int itemMMG = Convert.ToInt32(itemMM);
                //            string fpod_poMMGleastSKU = Convert.ToString(itemMMG);

                //            var dt = DataServiceMySql.GetDataTable($"SELECT bmd_company, bmd_incentive, bmd_cs," +
                //            $"bmd_distributor, bmd_retailor, bmd_driver, bmd_courier, bmd_pickup FROM retaline_margindistributions WHERE is_default = 1", UserService.GetAPIConnectionString());

                //            int fpod_spHmDel = 0;
                //            int fpod_spetCouDel = 0;
                //            int fpod_spPikup = 0;
                //            if (dt != null && dt.Rows.Count > 0)
                //            {
                //                foreach (DataRow da in dt.Rows)

                //                    fpod_spHmDel = itemLandingCost + (itemMMG * (int)da["bmd_company"] / 100) + (itemMMG * (int)da["bmd_incentive"] / 100) +
                //        (itemMMG * (int)da["bmd_cs"] / 100) + (itemMMG * (int)da["bmd_distributor"] / 100) + (itemMMG * (int)da["bmd_retailor"] / 100) +
                //        (itemMMG * (int)da["bmd_driver"] / 100);
                //            }
                //            var gst = DataServiceMySql.GetDataTable($"SELECT stit_GST, least_package_type_id FROM finascop_stock_itemmaster where stit_ID = {itemId}", UserService.GetAPIConnectionString());
                //            double finalexpressdelivery = 0;
                //            int packageTypeId = 0;
                //            if (gst != null && gst.Rows.Count > 0)
                //                finalexpressdelivery = (fpod_spHmDel * 100) / (100 + ((double)gst.Rows[0]["stit_GST"]));
                //            packageTypeId = (int)gst.Rows[0]["least_package_type_id"];
                //            if (dt != null && dt.Rows.Count > 0)
                //            {
                //                foreach (DataRow di in dt.Rows)
                //                    fpod_spetCouDel = itemLandingCost + (itemMMG * (int)di["bmd_company"] / 100) + (itemMMG * (int)di["bmd_incentive"] / 100) +
                //        (itemMMG * (int)di["bmd_cs"] / 100) + (itemMMG * (int)di["bmd_distributor"] / 100) + (itemMMG * (int)di["bmd_retailor"] / 100) +
                //        (itemMMG * (int)di["bmd_courier"] / 100);
                //            }

                //            double finalcourier = 0;
                //            if (gst != null && gst.Rows.Count > 0)
                //                finalcourier = (fpod_spetCouDel * 100) / (100 + ((double)gst.Rows[0]["stit_GST"]));
                //            if (dt != null && dt.Rows.Count > 0)
                //            {
                //                foreach (DataRow dz in dt.Rows)
                //                    fpod_spPikup = itemLandingCost + (itemMMG * (int)dz["bmd_company"] / 100) + (itemMMG * (int)dz["bmd_incentive"] / 100) +
                //        (itemMMG * (int)dz["bmd_cs"] / 100) + (itemMMG * (int)dz["bmd_distributor"] / 100) + (itemMMG * (int)dz["bmd_retailor"] / 100);
                //            }

                //            double finalpickup = 0;
                //            if (gst != null && gst.Rows.Count > 0)

                //                finalpickup = (fpod_spPikup * 100) / (100 + ((double)gst.Rows[0]["stit_GST"]));

                //            string expressdelivery = Convert.ToString(finalexpressdelivery);
                //            string courierdelivery = Convert.ToString(finalcourier);
                //            string pickup = Convert.ToString(finalpickup);

                //            //string mrPrice = mrp;
                //            //double pkStock = Convert.ToDouble(stock);
                //            //double pStock = pkStock;
                //            string dtTime = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
                //            DateTime dteTme = Convert.ToDateTime(dtTime);
                //            if (fsbiCount != null && fsbiCount.Rows.Count > 0)
                //            {
                //                string strUpdateSql2 = $"UPDATE finascop_stock_branch_inventory SET item_count = '" + itemCount + "', mrp = '" + mrp + "', selling_price = '" + sellingPrice + "', " +
                //                    $"updated_on = '" + dtTime + "', purchasing_unit = '" + packageTypeId + "', fpod_leastSKUmrp = '" + price + "', fpod_customerRateHmDel = '" + expressdelivery + "', " +
                //                    $"fpod_customerRateCouDel = '" + courierdelivery + "', fpod_customerRatePikup = '" + pickup + "', fpod_poLandingCostleastSKU = '" + itemLandingCost + "', " +
                //                    $"fpod_poMMGleastSKU = '" + itemMMG + "' WHERE id = '" + id + "'";
                //                DataServiceMySql.ExecuteSql(strUpdateSql2, UserService.GetAPIConnectionString());
                //            }
                //            else
                //            {
                //                string strUpdateSql3 = $"UPDATE finascop_stock_branch_inventory SET stit_id = '" + itemId + "', branch_id = '" + branchId + "', fsbg_id = '" + bgId + "', item_count = '" + itemCount + "', mrp = '" + mrp + "', selling_price = '" + sellingPrice + "', " +
                //                    $"updated_on = '" + dtTime + "', purchasing_unit = '" + packageTypeId + "', fpod_leastSKUmrp = '" + price + "', fpod_customerRateHmDel = '" + expressdelivery + "', " +
                //                    $"fpod_customerRateCouDel = '" + courierdelivery + "', fpod_customerRatePikup = '" + pickup + "', fpod_poLandingCostleastSKU = '" + itemLandingCost + "', " +
                //                    $"fpod_poMMGleastSKU = '" + itemMMG + "' WHERE id = '" + id + "'";
                //                DataServiceMySql.ExecuteSql(strUpdateSql3, UserService.GetAPIConnectionString());
                //            }
                //        }
                //    }

                //    if (uploadStatus == 0)
                //    {
                //        string strUpdateSql4 = $"UPDATE finascop_stock_branch_inventory_upload SET fbiu_status=1 WHERE fbiu_id = {upload}";
                //        DataServiceMySql.ExecuteSql(strUpdateSql4, UserService.GetAPIConnectionString());
                //        Response.Write("<script>alert('Uploaded stock confirmed.')</script>");
                //    }
                //    else
                //    {
                //        string strDeleteSql = $"DELETE FROM finascop_stock_branch_inventory_upload WHERE fbiu_id = {upload}";
                //        DataServiceMySql.ExecuteSql(strDeleteSql, UserService.GetAPIConnectionString());
                //        Response.Write("<script>alert('Nothing To upload')</script>");
                //    }

                //}

                //SendResponse("Upload executed successfully!!");

                #endregion
            }
        }

        private void SendResponse(string content, bool success=false)
        {
            Response.Clear();
            Response.Buffer = true;
            Response.Charset = "";
            var obj= new { result = (success ? 1 : 0), status = (success ? "Success" : "Error"), data = content };

            string jsoncontent = JsonConvert.SerializeObject(obj);

            Response.Write(jsoncontent);

            Response.Flush();
            Response.End();

        }


        private void ExportGridToExcel()
        {
            DataView dv = (DataView)SDSInventory.Select(DataSourceSelectArguments.Empty);
            DataTable dt = dv.ToTable();
            IWorkbook wb = new XSSFWorkbook();
            ISheet sheet = wb.CreateSheet("Inventory");
            ICreationHelper cH = wb.GetCreationHelper();
            int rows = 0;
            IRow rowH = sheet.CreateRow(rows++);
            string[] strFieldLabels = Array.Empty<string>();

            //var strFieldLabels = "stit_ID,ID|stit_SKU,Name|item_count,Stock|mrp,MRP|selling_price,Selling Price".Split('|');
            //var strFieldLabels = "stit_ID,ID|stit_SKU,Name|item_count,Stock|selling_price,Selling Price".Split('|');
            //string priceLabel = ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "MRP" : "RRP";

            //int stockIndex = Array.FindIndex(strFieldLabels, label => label.Contains("Stock"));
            //Array.Resize(ref strFieldLabels, strFieldLabels.Length + 1);
            //Array.Copy(strFieldLabels, stockIndex + 1, strFieldLabels, stockIndex + 2, strFieldLabels.Length - stockIndex - 2);
            //strFieldLabels[stockIndex + 1] = "mrp," + priceLabel;

            //var strFieldLabels = "fsipc_code,Product Barcode/ERP ID|selling_price,Item Selling Price|item_count,Stock to sell online|discount_selling_price,Sell through others|selling_price,Price".Split('|');
            List<KeyValuePair<string, object>> dataparams = new List<KeyValuePair<string, object>>();
            dataparams.Add(new KeyValuePair<string, object>("storeId", this.CurrentUser.APIStoreId));
            var dtStoreGroup = DataServiceMySql.GetDataTable($"SELECT br_ID, br_Name, bg.store_group_grosmartMerchant FROM finascop_branch b INNER JOIN finascop_branch_group bg ON b.br_storeGroup = bg.store_group_id WHERE bg.store_group_id  = @storeId", UserService.GetAPIConnectionString(), dataparams);
            int grosmartStore = 0;

            if (dtStoreGroup != null && dtStoreGroup.Rows.Count > 0)
            {
                DataRow da = dtStoreGroup.Rows[0];
                string grosmart = da["store_group_grosmartMerchant"].ToString();
                grosmartStore = Convert.ToInt32(grosmart);

                if (grosmartStore == 1)
                {
                    strFieldLabels = "fsipc_code,Product Barcode/ERP ID|selling_price,Item Selling Price|item_count,Stock to sell online|discount_selling_price,Sell through others price".Split('|');
                }
                else
                {
                    strFieldLabels = "fsipc_code,Product Barcode/ERP ID|selling_price,Item Selling Price|item_count,Stock to sell online".Split('|');
                }
            }
            string priceLabel = ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "SKU MRP" : "SKU RRP";

            // Find the index of "Product Barcode/ERP ID"
            int barcodeIndex = Array.FindIndex(strFieldLabels, label => label.Contains("Product Barcode/ERP ID"));

            // Insert the priceLabel after the barcodeIndex
            Array.Resize(ref strFieldLabels, strFieldLabels.Length + 1);
            Array.Copy(strFieldLabels, barcodeIndex + 1, strFieldLabels, barcodeIndex + 2, strFieldLabels.Length - barcodeIndex - 2);
            strFieldLabels[barcodeIndex + 1] = "mrp," + priceLabel;

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
                plcSelectBranchModel.Visible = selBranches.Items.Count > 2;

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
            if(Request.Files == null || Request.Files.Count <= 0)
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
                    if(dtbranch.Rows.Count > 1)
                    {
                        SendResponse("Invalid Store. Please select the store to import stock");
                        return default;
                    }
                    try { branchId = Convert.ToInt32(dtbranch.Rows[0]["br_ID"]); } catch { branchId = 0; }
                }
                else
                {
                    var drs = dtbranch.Select($"br_ID = {brid}");
                    if(drs.Length > 0)
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

            if (wb.NumberOfSheets < 1 || wb.GetSheetAt(0).PhysicalNumberOfRows < 2 || wb.GetSheetAt(0).GetRow(0).PhysicalNumberOfCells < 5)
            {
                SendResponse("Failure!! Insufficient data in the document selected. Please upload excel with single sheet, contain more than 1 row and minimum 5 columns. Please refer the sample excel available using the link available in the page.");
                return default;
            }

            var dtMargin = DataServiceMySql.GetDataTable($"SELECT bmd_company, bmd_incentive, bmd_cs," +
$"bmd_distributor, bmd_retailor, bmd_driver, bmd_courier, bmd_pickup FROM retaline_margindistributions WHERE is_default = 1 limit 1", UserService.GetAPIConnectionString());
            int bmd_company=0, bmd_incentive=0, bmd_cs=0, bmd_distributor=0, bmd_retailor=0, bmd_driver=0, bmd_courier=0;

            if (dtMargin != null && dtMargin.Rows.Count > 0)
            {
                DataRow da = dtMargin.Rows[0];
                try { bmd_company = Convert.ToInt32(da["bmd_company"]); } catch { bmd_company = 0; }
                try { bmd_incentive = Convert.ToInt32(da["bmd_incentive"]); } catch { bmd_incentive = 0; }
                try { bmd_cs = Convert.ToInt32(da["bmd_cs"]); } catch { bmd_cs = 0; }
                try { bmd_distributor = Convert.ToInt32(da["bmd_distributor"]); } catch { bmd_distributor = 0; }
                try { bmd_retailor = Convert.ToInt32(da["bmd_retailor"]); } catch { bmd_retailor = 0; }
                try { bmd_driver = Convert.ToInt32(da["bmd_driver"]); } catch { bmd_driver = 0; }
                try { bmd_courier = Convert.ToInt32(da["bmd_courier"]); } catch { bmd_courier = 0; }
            }

            int importcount = 0, failureCount=0;
            var sheet = wb.GetSheetAt(0);
            var headerrow = sheet.GetRow(0);
            int index_id = -1, index_stock = -1, index_mrp = -1, index_sellingPrice = -1, index_sellthrougothers = -1;
            if(headerrow != null)
            {
                for (int i = 0; i <= 5; i++)
                {
                    ICell cell = headerrow.GetCell(i, MissingCellPolicy.RETURN_BLANK_AS_NULL);
                    if (cell == null)
                        continue;
                    //string val = (cell.CellType == CellType.Numeric ? cell.NumericCellValue.ToString() : cell.StringCellValue.Replace("{", "").Replace("}", ""));
                    string strVal = cell.StringCellValue.Replace("{", "").Replace("}", "").Trim().Replace(" ", "").ToLower();
                    //if (strVal == "id")
                    //    index_id = i;
                    //else if (strVal == "stock")
                    //    index_stock = i;
                    //else if (strVal == "mrp" || strVal == "rrp")
                    //    index_mrp = i;
                    //else if (strVal == "sellingprice")
                    //    index_sellingPrice = i;
                    if (strVal == "productbarcode/erpid")
                        index_id = i;
                    else if (strVal == "skumrp" || strVal == "skurrp")
                        index_mrp = i;
                    else if (strVal == "itemsellingprice")
                        index_sellingPrice = i;
                    else if (strVal == "stocktosellonline")
                        index_stock = i;
                    else if (strVal == "sellthroughothersprice")
                        index_sellthrougothers = i;
                }
            }

            if (index_id < 0 || index_mrp < 0 || index_sellingPrice < 0 || index_stock < 0  || index_sellthrougothers < 0)
            {
                SendResponse("Failure!! Missing fields in file. Please ensure that the excel having header row with the missing fields " + (index_id < 0 ? "productbarcode/erpid" : "") + (index_mrp < 0 ? " ,skumrp" : "") + (index_sellingPrice < 0 ? " ,itemsellingprice" : "") + (index_stock < 0 ? " ,stocktosellonline" : "") + (index_sellthrougothers < 0 ? " ,sellthroughothersprice" : ""));
                return default;
            }

            for (int i = 1; i < sheet.PhysicalNumberOfRows; i++)
            {
                /*string stit_id="", stock="", mrp="", sellingPrice="";*/ //, itemname=""

                string erpId = "", mrp = "", sellingPrice = "", stock = "", discountSP="", stitemId = "";

                try
                {
                    var row = sheet.GetRow(i);

                    ICell cell_id = row.GetCell(index_id, MissingCellPolicy.RETURN_BLANK_AS_NULL);
                    erpId = (cell_id.CellType == CellType.Numeric ? cell_id.NumericCellValue.ToString() : cell_id.StringCellValue.Replace("{", "").Replace("}", "")); // stit_id or erp id
                    //int itemId = 0; try { itemId = Convert.ToInt32(stit_id); } catch { itemId = 0; }
                    int barcodeErpId = 0; try { barcodeErpId = Convert.ToInt32(erpId); } catch { barcodeErpId = 0; }

                    ICell cell_mrp = row.GetCell(index_mrp, MissingCellPolicy.RETURN_BLANK_AS_NULL);
                    mrp = (cell_mrp.CellType == CellType.Numeric ? cell_mrp.NumericCellValue.ToString() : cell_mrp.StringCellValue.Replace("{", "").Replace("}", ""));
                    decimal tlMrp = 0; try { tlMrp = Convert.ToDecimal(mrp); } catch { tlMrp = 0; }

                    ICell cell_sellingPrice = row.GetCell(index_sellingPrice, MissingCellPolicy.RETURN_BLANK_AS_NULL);
                    sellingPrice = (cell_sellingPrice.CellType == CellType.Numeric ? cell_sellingPrice.NumericCellValue.ToString() : cell_sellingPrice.StringCellValue.Replace("{", "").Replace("}", ""));
                    decimal sellPrice = 0; try { sellPrice = Convert.ToDecimal(sellingPrice); } catch { sellPrice = 0; }

                    ICell cell_stock = row.GetCell(index_stock, MissingCellPolicy.RETURN_BLANK_AS_NULL);
                    stock = (cell_stock.CellType == CellType.Numeric ? cell_stock.NumericCellValue.ToString() : cell_stock.StringCellValue.Replace("{", "").Replace("}", ""));
                    int itemStock = 0; try { itemStock = Convert.ToInt32(stock); } catch { itemStock = 0; }

                    ICell cell_dsellingPrice = row.GetCell(index_sellingPrice, MissingCellPolicy.RETURN_BLANK_AS_NULL);
                    discountSP = (cell_sellingPrice.CellType == CellType.Numeric ? cell_dsellingPrice.NumericCellValue.ToString() : cell_dsellingPrice.StringCellValue.Replace("{", "").Replace("}", ""));
                    decimal dsellPrice = 0; try { dsellPrice = Convert.ToDecimal(discountSP); } catch { dsellPrice = 0; }

                    var stitIdDt = DataServiceMySql.GetDataTable($"SELECT fsipc_stit_id FROM finascop_stock_itemmaster_product_codes WHERE fsipc_code= {barcodeErpId} AND fsipc_storeGroup={this.CurrentUser.APIStoreId}", UserService.GetAPIConnectionString());
                    if(stitIdDt != null && stitIdDt.Rows.Count > 0)
                    {
                        DataRow da = stitIdDt.Rows[0];
                        stitemId = da["fsipc_stit_id"].ToString();
                    }

                    int itemId = Convert.ToInt32(stitemId);
                    //if (barcodeErpId <= 0)
                    //{
                    //    SendResponse($"Failure at row {i}, invalid data format. Product id field is wrong. Please ensure it added with numeric value");
                    //    return default;
                    //}

                    if (sellPrice > tlMrp)
                    {
                        string labelPrice = "";
                        if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK"){
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

                    //itemname = row.GetCell(1).StringCellValue; // Optional. Not required to import
                    int storegroupid = this.CurrentUser.APIStoreId;

                    decimal totalMrp = Convert.ToDecimal(mrp);

                    decimal itemMRP = 0;
                    decimal itemLanding = sellPrice;
                    int itemLandingCost = Convert.ToInt32(itemLanding);

                    itemMRP = totalMrp;

                    decimal itemMM = (itemMRP - itemLanding);
                    int itemMMG = Convert.ToInt32(itemMM);
                    string fpod_poMMGleastSKU = Convert.ToString(itemMMG);

                    int fpod_spHmDel = 0, fpod_spetCouDel = 0, fpod_spPikup = 0;
                    try
                    {
                        fpod_spHmDel = itemLandingCost + (itemMMG * bmd_company / 100) + (itemMMG * bmd_incentive / 100) +
                            (itemMMG * bmd_cs / 100) + (itemMMG * bmd_distributor / 100) + (itemMMG * bmd_retailor / 100) +
                            (itemMMG * bmd_driver / 100);
                    }
                    catch { fpod_spHmDel = itemLandingCost; }

                    var gst = DataServiceMySql.GetDataTable($"SELECT stit_GST, least_package_type_id FROM finascop_stock_itemmaster where stit_ID = {itemId}", UserService.GetAPIConnectionString());
                    double finalexpressdelivery = 0;
                    int packageTypeId = 0;
                    if (gst != null && gst.Rows.Count > 0)
                        finalexpressdelivery = (fpod_spHmDel * 100) / (100 + ((double)gst.Rows[0]["stit_GST"]));
                    packageTypeId = (int)gst.Rows[0]["least_package_type_id"];

                    try {
                        fpod_spetCouDel = itemLandingCost + (itemMMG * bmd_company / 100) + (itemMMG * bmd_incentive / 100) +
                            (itemMMG * bmd_cs / 100) + (itemMMG * bmd_distributor / 100) + (itemMMG * bmd_retailor / 100) + (itemMMG * bmd_courier / 100);
                    }
                    catch { fpod_spetCouDel = 0; }

                    double finalcourier = 0;
                    if (gst != null && gst.Rows.Count > 0)
                        finalcourier = (fpod_spetCouDel * 100) / (100 + ((double)gst.Rows[0]["stit_GST"]));

                    try {
                        fpod_spPikup = itemLandingCost + (itemMMG * bmd_company / 100) + (itemMMG * bmd_incentive / 100) +
                            (itemMMG * bmd_cs / 100) + (itemMMG * bmd_distributor / 100) + (itemMMG * bmd_retailor / 100);
                    }
                    catch { fpod_spPikup = itemLandingCost; }

                    double finalpickup = 0;
                    if (gst != null && gst.Rows.Count > 0)

                        finalpickup = (fpod_spPikup * 100) / (100 + ((double)gst.Rows[0]["stit_GST"]));

                    string expressdelivery = Convert.ToString(finalexpressdelivery);
                    string courierdelivery = Convert.ToString(finalcourier);
                    string pickup = Convert.ToString(finalpickup);


                    List<KeyValuePair<string, object>> sqlparams = new List<KeyValuePair<string, object>>();
                    sqlparams.Add(new KeyValuePair<string, object>("stitid", itemId));
                    sqlparams.Add(new KeyValuePair<string, object>("brid", branchId));
                    sqlparams.Add(new KeyValuePair<string, object>("bgId", 0));
                    sqlparams.Add(new KeyValuePair<string, object>("itemCount", itemStock));
                    sqlparams.Add(new KeyValuePair<string, object>("mrp", itemMRP));
                    sqlparams.Add(new KeyValuePair<string, object>("sellingPrice", sellPrice));
                    sqlparams.Add(new KeyValuePair<string, object>("packageTypeId", packageTypeId));
                    sqlparams.Add(new KeyValuePair<string, object>("price", mrp));//price));
                    sqlparams.Add(new KeyValuePair<string, object>("expressdelivery", expressdelivery));
                    sqlparams.Add(new KeyValuePair<string, object>("courierdelivery", courierdelivery));
                    sqlparams.Add(new KeyValuePair<string, object>("pickup", pickup));
                    sqlparams.Add(new KeyValuePair<string, object>("itemLandingCost", itemLandingCost));
                    sqlparams.Add(new KeyValuePair<string, object>("itemMMG", itemMMG));
                    //sqlparams.Add(new KeyValuePair<string, object>("id", id));


                    //    String sqlUpdateSql = @"INSERT INTO finascop_stock_branch_inventory(stit_id, branch_id, fsbg_id, item_count, mrp, selling_price, purchasing_unit, fpod_leastSKUmrp, fpod_customerRateHmDel, 
                    //    fpod_customerRateCouDel, fpod_customerRatePikup, fpod_poLandingCostleastSKU, fpod_poMMGleastSKU)
                    //  VALUES(@stitid, @brid, @bgId, @itemCount, @mrp, @sellingPrice, @packageTypeId, @price, @expressdelivery, @courierdelivery, @pickup, @itemLandingCost, @itemMMG)

                    //  ON DUPLICATE KEY UPDATE
                    //    item_count = VALUES(item_count), mrp = VALUES(mrp), selling_price = VALUES(selling_price), fpod_leastSKUmrp = VALUES(fpod_leastSKUmrp), 
                    //    fpod_customerRateHmDel = VALUES(fpod_customerRateHmDel), fpod_customerRateCouDel = VALUES(fpod_customerRateCouDel), 
                    //    fpod_customerRatePikup = VALUES(fpod_customerRatePikup), fpod_poLandingCostleastSKU =VALUES(fpod_poLandingCostleastSKU),
                    //    fpod_poMMGleastSKU = VALUES(fpod_poMMGleastSKU)
                    //";
                    String sqlUpdateSql = @"UPDATE finascop_stock_branch_inventory 
                        SET item_count = @itemCount, mrp = @mrp, selling_price = @sellingPrice, fpod_leastSKUmrp = @mrp, 
                        fpod_customerRateHmDel = @expressdelivery, fpod_customerRateCouDel = @courierdelivery, 
                        fpod_customerRatePikup = @pickup, fpod_poLandingCostleastSKU = @itemLandingCost, fpod_poMMGleastSKU = @itemMMG 
                        WHERE branch_id = @brid AND stit_id = @stitid";
                    int updatecount = DataServiceMySql.ExecuteSql(sqlUpdateSql, UserService.GetAPIConnectionString(), sqlparams);
                    if(updatecount <= 0)
                        strResults.Add($"No data updated for id: {itemId} as the system cannot match the data.");
                    importcount++;
                }
                catch(Exception ex) {
                    failureCount++;
                    strResults.Add($"Error stit_id: {stitemId}, {ex.Message}");
                }
            }

            if (failureCount > 0)
                strResults.Insert(0, $"Errors: {failureCount}");

            strResults.Insert(0, $"{importcount} records imported.");

            return strResults;

        }
    }
}