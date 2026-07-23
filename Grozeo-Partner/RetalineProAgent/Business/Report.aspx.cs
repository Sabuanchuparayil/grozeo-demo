using NPOI.SS.Formula.UDF;
using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class Report : System.Web.UI.Page
    {
        public int RecCount { 
            get {
                if(ViewState["MRCREPORTINGCOUNT"] != null)
                    return (int)ViewState["MRCREPORTINGCOUNT"];
                return 0;
            } 
            set {
                ViewState["MRCREPORTINGCOUNT"] = value;
            } 
        }
        protected void Page_Load(object sender, EventArgs e)
        {
        }

        protected void btnDownload_Click(object sender, EventArgs e)
        {
            int period = 1; try { period = Convert.ToInt32(selPeriod.Text); } catch { period = 1; }
            int areaId = 0; try { areaId = Convert.ToInt32(selArea.Text); } catch { areaId = 0; }
            List<Core.BussinessModel.Store.MerchantData> data = GetMerchantReport(0, 0, ODSReport.SortParameterName, period, areaId, txtSearchStore.Text);

            IWorkbook workbook = new XSSFWorkbook();
            ISheet sheet = workbook.CreateSheet("Orders");
            ICreationHelper creationHelper = workbook.GetCreationHelper();
            IRow headerRow = sheet.CreateRow(0);
            int columnIndex = 0;
            string strHeads = "MerchantName,CreatedOn,PlanName,CanCheckout,BankAccounts,Products,OrderPickers,PendingActions,PendingJobs,Orders,OrderValue,RoName,BranchNames,BranchAreas,Listed";
            foreach (string strHead in strHeads.Split(','))
            {
                ICell headerCell = headerRow.CreateCell(columnIndex++);
                headerCell.SetCellValue(creationHelper.CreateRichTextString(strHead));
            }
            int rowIndex = 1;
            foreach (var merchantData in data)
            {
                IRow excelRow = sheet.CreateRow(rowIndex++);
                columnIndex = 0;

                ICell dataCell = excelRow.CreateCell(columnIndex++);
                string cellValue = merchantData?.MerchantName?.ToString() ?? string.Empty;
                dataCell.SetCellValue(creationHelper.CreateRichTextString(cellValue));
                ICell dataCell2 = excelRow.CreateCell(columnIndex++);
                dataCell2.SetCellValue(creationHelper.CreateRichTextString(merchantData?.CreatedOn.ToString() ?? string.Empty));

                ICell dataCell3 = excelRow.CreateCell(columnIndex++);
                dataCell3.SetCellValue(creationHelper.CreateRichTextString(merchantData?.PlanName.ToString() ?? string.Empty));

                ICell dataCell4 = excelRow.CreateCell(columnIndex++);
                dataCell4.SetCellValue(creationHelper.CreateRichTextString(merchantData?.CanCheckout.ToString() ?? string.Empty));
                ICell dataCell5 = excelRow.CreateCell(columnIndex++);
                dataCell5.SetCellValue(creationHelper.CreateRichTextString(merchantData?.BankAccounts.ToString() ?? string.Empty));
                ICell dataCell6 = excelRow.CreateCell(columnIndex++);
                dataCell6.SetCellValue(creationHelper.CreateRichTextString(merchantData?.Products.ToString() ?? string.Empty));
                ICell dataCell7 = excelRow.CreateCell(columnIndex++);
                dataCell7.SetCellValue(creationHelper.CreateRichTextString(merchantData?.OrderPickers.ToString() ?? string.Empty));
                ICell dataCell8 = excelRow.CreateCell(columnIndex++);
                dataCell8.SetCellValue(creationHelper.CreateRichTextString(merchantData?.PendingActions?.ToString() ?? string.Empty));
                ICell dataCell9 = excelRow.CreateCell(columnIndex++);
                dataCell9.SetCellValue(creationHelper.CreateRichTextString(merchantData?.PendingJobs?.ToString() ?? string.Empty));
                ICell dataCell10 = excelRow.CreateCell(columnIndex++);
                dataCell10.SetCellValue(creationHelper.CreateRichTextString(merchantData?.Orders.ToString() ?? string.Empty));
                ICell dataCell11 = excelRow.CreateCell(columnIndex++);
                dataCell11.SetCellValue(creationHelper.CreateRichTextString(merchantData?.OrderValue.ToString() ?? string.Empty));
                ICell dataCell12 = excelRow.CreateCell(columnIndex++);
                dataCell12.SetCellValue(creationHelper.CreateRichTextString(merchantData?.RoName?.ToString() ?? string.Empty));
                ICell dataCell13 = excelRow.CreateCell(columnIndex++);

                dataCell13.SetCellValue(creationHelper.CreateRichTextString(merchantData?.BranchNames?.ToString() ?? string.Empty));
                ICell dataCell14 = excelRow.CreateCell(columnIndex++);

                dataCell4.SetCellValue(creationHelper.CreateRichTextString(merchantData?.BranchAreaName?.ToString() ?? string.Empty));
                ICell dataCell15 = excelRow.CreateCell(columnIndex++);
                dataCell15.SetCellValue(creationHelper.CreateRichTextString(merchantData?.IsFeatured.ToString() ?? string.Empty));






            }

            Response.Clear();
            Response.Buffer = true;
            Response.Charset = "";
            Response.ContentType = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
            Response.AddHeader("content-disposition", "attachment;filename=Orders.xlsx");
            workbook.Write(Response.OutputStream);
            Response.Flush();
            Response.End();
        }




        public int Count(int period, int areaId, string storeNamePref) {
            return RecCount;
        }
        public List<Core.BussinessModel.Store.MerchantData> GetMerchantReport(int startIndex, int pageSize, string sortBy, int period, int areaId, string storeNamePref)
        {
            try
            {
                RecCount = 0;
                List<Core.BussinessModel.Store.MerchantData> combinedData = Services.StoreService.MerchantsReport(period, areaId, storeNamePref);
                if (combinedData != null && combinedData.Count > 0)
                {
                    try
                    {
                        combinedData = combinedData.OrderByDescending(c => c.APIStoregroupId).ToList();
                        if (!String.IsNullOrEmpty(sortBy))
                        {
                            switch (sortBy.ToLower())
                            {
                                case "merchantname":
                                    combinedData = combinedData.OrderBy(c => c.MerchantName).ToList();
                                    break;
                                case "merchantname desc":
                                    combinedData = combinedData.OrderByDescending(c => c.MerchantName).ToList();
                                    break;
                                case "bankaccounts":
                                    combinedData = combinedData.OrderBy(c => c.BankAccounts).ToList();
                                    break;
                                case "bankaccounts desc":
                                    combinedData = combinedData.OrderByDescending(c => c.BankAccounts).ToList();
                                    break;
                                case "createdon":
                                    combinedData = combinedData.OrderBy(c => c.CreatedOn).ToList();
                                    break;
                                case "createdon desc":
                                    combinedData = combinedData.OrderByDescending(c => c.CreatedOn).ToList();
                                    break;
                                case "isfeatured":
                                    combinedData = combinedData.OrderBy(c => c.IsFeatured).ToList();
                                    break;
                                case "isfeatured desc":
                                    combinedData = combinedData.OrderByDescending(c => c.IsFeatured).ToList();
                                    break;
                                case "totalstores":
                                    combinedData = combinedData.OrderBy(c => c.TotalStores).ToList();
                                    break;
                                case "totalstores desc":
                                    combinedData = combinedData.OrderByDescending(c => c.TotalStores).ToList();
                                    break;
                                case "planname":
                                    combinedData = combinedData.OrderBy(c => c.PlanName).ToList();
                                    break;
                                case "planname desc":
                                    combinedData = combinedData.OrderByDescending(c => c.PlanName).ToList();
                                    break;
                                case "cancheckout":
                                    combinedData = combinedData.OrderBy(c => c.CanCheckout).ToList();
                                    break;
                                case "cancheckout desc":
                                    combinedData = combinedData.OrderByDescending(c => c.CanCheckout).ToList();
                                    break;
                                case "products":
                                    combinedData = combinedData.OrderBy(c => c.Products).ToList();
                                    break;
                                case "products desc":
                                    combinedData = combinedData.OrderByDescending(c => c.Products).ToList();
                                    break;
                                case "orderpickers":
                                    combinedData = combinedData.OrderBy(c => c.OrderPickers).ToList();
                                    break;
                                case "orderpickers desc":
                                    combinedData = combinedData.OrderByDescending(c => c.OrderPickers).ToList();
                                    break;
                                case "pendingactions":
                                    combinedData = combinedData.OrderBy(c => c.PendingActions).ToList();
                                    break;
                                case "pendingactions desc":
                                    combinedData = combinedData.OrderByDescending(c => c.PendingActions).ToList();
                                    break;
                                case "pendingjobs":
                                    combinedData = combinedData.OrderBy(c => c.PendingJobs).ToList();
                                    break;
                                case "pendingjobs desc":
                                    combinedData = combinedData.OrderByDescending(c => c.PendingJobs).ToList();
                                    break;
                                case "orders":
                                    combinedData = combinedData.OrderBy(c => c.Orders).ToList();
                                    break;
                                case "orders desc":
                                    combinedData = combinedData.OrderByDescending(c => c.Orders).ToList();
                                    break;
                                case "ordervalue":
                                    combinedData = combinedData.OrderBy(c => c.OrderValue).ToList();
                                    break;
                                case "ordervalue desc":
                                    combinedData = combinedData.OrderByDescending(c => c.OrderValue).ToList();
                                    break;
                                case "roname":
                                    combinedData = combinedData.OrderBy(c => c.RoName).ToList();
                                    break;
                                case "roname desc":
                                    combinedData = combinedData.OrderByDescending(c => c.RoName).ToList();
                                    break;
                                default:
                                    combinedData = combinedData.OrderByDescending(c => c.APIStoregroupId).ToList();
                                    break;
                            }
                        }
                    }
                    catch { }
                    if (pageSize > 0)
                    {
                        RecCount = combinedData.Count;
                        //ltrTotal.Text = RecCount.ToString();
                    }
                    if (pageSize > 0)
                        return combinedData.Skip(startIndex).Take(pageSize).ToList();
                    else 
                        return combinedData;
                }
            }
            catch(Exception ex) { 
                string msg = ex.Message;
            }
            return default;
        }
        public string GetProspectMode(object CpMode)
        {
            string strMode = "";
            if(CpMode != null)
            {
                try {
                    int cpMode = Convert.ToInt32(CpMode);
                    switch (cpMode)
                    {
                        case 1:
                            strMode = "Site/Campaign Enquiries";
                            break;
                        case 2:
                            strMode = "Web form contact";
                            break;
                        case 3:
                            strMode = "CRM App";
                            break;
                        case 4:
                            strMode = "CRM App with google address API";
                            break;
                    }
                } catch { }
            }
            return strMode;
        }




    }
}