using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class BalanceSheet: Base.BasePartnerPage
    {
        DataTable dtAssetData { get; set; }

        public object ConvertToMinus(object val)
        {
            try
            {
                Double amt = Convert.ToDouble(val);
                amt = amt * -1;
                return amt;
            }
            catch
            {

            }
            return val;
        }
        protected void Page_Load(object sender, EventArgs e)
        {
            txtFromDate.Attributes.Add("max", DateTime.Now.AddDays(-1).ToString("yyyy-MM-dd"));
            txtToDate.Attributes.Add("max", DateTime.Now.AddDays(-1).ToString("yyyy-MM-dd"));

            if (!IsPostBack)
            {
                //txtFromDate.Text = DateTime.Now.AddDays(-30).ToString("yyyy-MM-dd");
                //txtToDate.Text = DateTime.Now.ToString("yyyy-MM-dd");
                DataView viewPLData = (DataView)SDSBalancesheetGroups.Select(DataSourceSelectArguments.Empty);
                if (viewPLData != null)
                    dtAssetData = viewPLData.ToTable();
            }
        }

        protected void Page_PreRender(object sender, EventArgs e)
        {
            if (dtAssetData != null && dtAssetData.Rows.Count > 0)
            {
                LoadData();
            }
        }
        private void LoadData()
        {
            if (dtAssetData == null || dtAssetData.Rows.Count <= 0)
                return;

            var drs = dtAssetData.AsEnumerable().Where(c => Convert.ToInt32(c["parent_id"]) == 0).Select(c => c).ToList();
            DataTable pvtTable = PivotTable(drs);
            rptBalanceSheet.DataSource = pvtTable;
            rptBalanceSheet.DataBind();

            double TL = 0; try { TL = dtAssetData.AsEnumerable().Where(c => Convert.ToInt32(c["parent_id"]) == 0 && Convert.ToInt32(c["account_types_id"]) == 2).Sum(r => Convert.ToDouble(r["total"])*-1); } catch { TL = 0; }
            double TA = 0; try { TA = dtAssetData.AsEnumerable().Where(c => Convert.ToInt32(c["parent_id"]) == 0 && Convert.ToInt32(c["account_types_id"]) == 1).Sum(r => Convert.ToDouble(r["total"])); } catch { TA = 0; }
            double lDiff = 0, aDiff = 0;
            if(TA != TL)
            {
                string strDiff = String.Format("{0:0.00}", Math.Abs(TA - TL));
                if(strDiff != "0.00")
                {
                    PlaceHolder plcSP = (PlaceHolder)rptBalanceSheet.Controls[rptBalanceSheet.Controls.Count - 1].Controls[0].FindControl("plcSuspenseAccount");
                    if(TL > TA)
                    {
                        aDiff = TL - TA;
                        Literal ltrSpA = (Literal)rptBalanceSheet.Controls[rptBalanceSheet.Controls.Count - 1].Controls[0].FindControl("ltrSpA");
                        Literal ltrSpAAmt = (Literal)rptBalanceSheet.Controls[rptBalanceSheet.Controls.Count - 1].Controls[0].FindControl("ltrSpAAmt");
                        ltrSpA.Text = "Balancing Difference"; //"Suspense Account";
                        ltrSpAAmt.Text = strDiff;
                    }
                    else
                    {
                        lDiff = TA - TL;
                        Literal ltrSpL = (Literal)rptBalanceSheet.Controls[rptBalanceSheet.Controls.Count - 1].Controls[0].FindControl("ltrSpL");
                        Literal ltrSpLAmt = (Literal)rptBalanceSheet.Controls[rptBalanceSheet.Controls.Count - 1].Controls[0].FindControl("ltrSpLAmt");
                        ltrSpL.Text = "Balancing Difference"; // "Suspense Account";
                        ltrSpLAmt.Text = strDiff;
                    }
                    plcSP.Visible = true;
                }
            }
            Literal ltrLTotal = (Literal)rptBalanceSheet.Controls[rptBalanceSheet.Controls.Count - 1].Controls[0].FindControl("ltrLTotal");
            Literal ltrATotal = (Literal)rptBalanceSheet.Controls[rptBalanceSheet.Controls.Count - 1].Controls[0].FindControl("ltrATotal");
            if (ltrLTotal != null && ltrATotal != null)
            {
                ltrLTotal.Text = String.Format("{0:0.00}", TL + lDiff);
                ltrATotal.Text = String.Format("{0:0.00}", TA + aDiff);
            }

        }
        private DataTable PivotTable(List<DataRow> drs)
        {
            DataTable pvtTable = new DataTable();
            pvtTable.Columns.Add("id1", typeof(int));
            pvtTable.Columns.Add("Particulars1", typeof(string));
            pvtTable.Columns.Add("Amt1", typeof(double));
            pvtTable.Columns.Add("id2", typeof(int));
            pvtTable.Columns.Add("Particulars2", typeof(string));
            pvtTable.Columns.Add("Amt2", typeof(double));
            foreach (var dr in drs)
            {
                int typeid = Convert.ToInt32(dr["account_types_id"]);
                int id = Convert.ToInt32(dr["id"]);
                string particulars = dr["name"].ToString();
                double amt = 0; try {if(dr["total"] != DBNull.Value) amt = Convert.ToDouble(dr["total"]); } catch { amt = 0; }
                if (amt == 0)
                    continue;
                var drow = pvtTable.AsEnumerable().Where(r => r["Amt1"] == DBNull.Value).FirstOrDefault();
                if (typeid == 1)
                    drow = pvtTable.AsEnumerable().Where(r => r["Amt2"] == DBNull.Value).FirstOrDefault();
                if (drow == null)
                {
                    drow = pvtTable.NewRow();
                    pvtTable.Rows.Add(drow);
                }
                if (typeid == 1)
                {
                    drow["id2"] = id;
                    drow["Particulars2"] = particulars;
                    drow["Amt2"] = amt;
                }
                else
                {
                    drow["id1"] = id;
                    drow["Particulars1"] = particulars;
                    drow["Amt1"] = amt;
                }
            }
            return pvtTable;
        }

        protected void lvBalanceSheet_DataBound(object sender, EventArgs e)
        {

        }

        protected void rptBalanceSheet_ItemDataBound(object sender, RepeaterItemEventArgs e)
        {
            int id1 = 0; try { if (DataBinder.Eval(e.Item.DataItem, "id1") != DBNull.Value) id1 = Convert.ToInt32(DataBinder.Eval(e.Item.DataItem, "id1")); } catch { id1 = 0; }
            int id2 = 0; try { if (DataBinder.Eval(e.Item.DataItem, "id2") != DBNull.Value) id2 = Convert.ToInt32(DataBinder.Eval(e.Item.DataItem, "id2")); } catch { id2 = 0; }

            Repeater rptChild1 = (Repeater)e.Item.FindControl("rptChild1");
            Repeater rptChild2 = (Repeater)e.Item.FindControl("rptChild2");
            if (rptChild1 != null && rptChild2 != null && dtAssetData != null && dtAssetData.Rows.Count > 0)
            {
                if (id1 > 0 || id1 == -1)
                {
                    rptChild1.DataSource = dtAssetData.AsEnumerable().Where(r => Convert.ToInt32(r["parent_id"]) == id1).CopyToDataTable();
                    rptChild1.DataBind();
                }
                if (id2 > 0 || id2 == -1)
                {
                    rptChild2.DataSource = dtAssetData.AsEnumerable().Where(r => Convert.ToInt32(r["parent_id"]) == id2).CopyToDataTable();
                    rptChild2.DataBind();
                }
            }


        }

        protected void SDSBalancesheetGroups_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
           // string dateformat = "{0}/{1}/{2}";
           // int financialYearStartMonth = 4;
           // int financialYearEndMonth = 3;
           // int financialYearEndDay = 31;
           // string startDate = "", endDate = "";
           //      int fyear = DateTime.Now.Year;
           //if (selYear.Text == "1")
           // {
           //     //int fmonth = DateTime.Now.Month;
           //     if (DateTime.Now.Month < financialYearEndMonth)
           //         fyear -= 1;
           //     startDate = String.Format(dateformat, fyear, financialYearStartMonth, 1);
           //     endDate = DateTime.Now.ToString("yyyy-MM-dd");
           // }
           // else if(selYear.Text == "2")
           // {
           //     if (DateTime.Now.Month < financialYearEndMonth)
           //         fyear -= 1;
           //     int fmonth = financialYearStartMonth;
           //     startDate = String.Format(dateformat, fyear -1 , financialYearStartMonth, 1);
           //     endDate = String.Format(dateformat, fyear, financialYearEndMonth, financialYearEndDay);
           // }
           // e.Command.Parameters["@fromDate"].Value = startDate;
           // e.Command.Parameters["@toDate"].Value = endDate;
           // ltrFinancialYearDate.Text = Convert.ToDateTime(startDate).ToString("dd / MMM / yyyy") + " to " + Convert.ToDateTime(endDate).ToString("dd / MMM / yyyy");
        }

        protected void selYear_SelectedIndexChanged(object sender, EventArgs e)
        {
            DataView viewPLData = (DataView)SDSBalancesheetGroups.Select(DataSourceSelectArguments.Empty);
            if (viewPLData != null)
                dtAssetData = viewPLData.ToTable();
            //LoadData();
        }

        protected void lbtnSearch_Click(object sender, EventArgs e)
        {
            DataView viewPLData = (DataView)SDSBalancesheetGroups.Select(DataSourceSelectArguments.Empty);
            if (viewPLData != null)
                dtAssetData = viewPLData.ToTable();
        }
    }
}