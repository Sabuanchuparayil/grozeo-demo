//using RetalineProAgent.Core.BussinessModel.OnlineOrders;
using RetalineProAgent.Core.Services;
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
    public partial class GSTReport: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {

        }
        //protected void SDSOnlineOrders_Selected(object sender, SqlDataSourceStatusEventArgs e)
        //{
        //    e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        //    int startRowOnPage = (gvOnlineOrders.PageIndex * gvOnlineOrders.PageSize) + 1;
        //    int lastRowOnPage = startRowOnPage + gvOnlineOrders.Rows.Count - 1;
        //    int totalRows = e.AffectedRows;

        //    ltrPageCurStart.Text = startRowOnPage.ToString();
        //    ltrPageCurTotal.Text = lastRowOnPage.ToString();
        //    ltrPageTotal.Text = totalRows.ToString();
        //}

        //protected void lstView_Changed(object sender, EventArgs e)
        //{
        //    int storegroupid = this.CurrentUser.APIStoreId;
        //    var dtBranches = DataServiceMySql.GetDataTable($"SELECT br_ID FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
        //    foreach (DataRow dr in dtBranches.Rows)
        //    {
        //        string brId = dr["br_ID"].ToString();
        //        int branchid = Convert.ToInt32(brId);
        //        if (list1.SelectedItem.Text == "IGST")
        //        {
        //            string ledgerId = DataServiceMySql.GetDataTable($"SELECT finascop_accounts_ledger.accled_Ledger_Id FROM " +
        //                $"finascop_accounts_ledgertype_default INNER JOIN finascop_accounts_ledgertype " +
        //                $"ON finascop_accounts_ledgertype.ledgertypedefaultid = finascop_accounts_ledgertype_default.ledgertypedefaultid " +
        //                $"INNER JOIN finascop_accounts_ledger ON finascop_accounts_ledger.ledgertypeid = finascop_accounts_ledgertype.ledgertypeid " +
        //                $"WHERE ledgertypedefaultname = 'IGST' AND accled_BranchId = '{branchid}", UserService.GetAPIConnectionString());

        //        }
        //    }
        //}

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvGSTReport.PageIndex > 0)
                gvGSTReport.PageIndex = gvGSTReport.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvGSTReport.PageIndex < gvGSTReport.PageCount - 1)
                gvGSTReport.PageIndex = gvGSTReport.PageIndex + 1;
        }

        protected void gvGSTReport_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvGSTReport.PageIndex * gvGSTReport.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvGSTReport.Rows.Count - 1;
            ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSGSTReport.Select(DataSourceSelectArguments.Empty);
        }

        protected void SDSGSTReport_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
        }
    }
}