using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Finance
{
    public partial class ProfitAndLoss: Base.BasePartnerPage
    {
        DataTable dtPLData { get; set; }
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
                txtFromDate.Text = DateTime.Now.AddDays(-30).ToString("yyyy-MM-dd");
                txtToDate.Text = DateTime.Now.AddDays(-1).ToString("yyyy-MM-dd");
            }
            DataView viewPLData = (DataView)SDSPAndLGroups.Select(DataSourceSelectArguments.Empty);
            if(viewPLData != null)
                dtPLData = viewPLData.ToTable();
        }
        protected void Page_PreRender(object sender, EventArgs e)
        {
            if(dtPLData != null && dtPLData.Rows.Count > 0)
            {
                LoadData();
            }
        }
        private void LoadData()
        {
            if (dtPLData == null || dtPLData.Rows.Count <= 0)
                return;

            var drs = dtPLData.AsEnumerable().Where(c => Convert.ToInt32(c["parent_id"]) == 0 && c["P_and_L_type"] != DBNull.Value && Convert.ToInt32(c["P_and_L_type"]) == 1).Select(c => c).ToList();
            DataTable pvtTable = PivotTable(drs);
            rptGP.DataSource = pvtTable;
            rptGP.DataBind();

            double DI = dtPLData.AsEnumerable().Where(c => Convert.ToInt32(c["parent_id"]) == 0 && c["P_and_L_type"] != DBNull.Value && Convert.ToInt32(c["P_and_L_type"]) == 1 && Convert.ToInt32(c["account_types_id"]) == 3).Sum(r => Convert.ToDouble(r["total"])*-1);
            double DE = dtPLData.AsEnumerable().Where(c => Convert.ToInt32(c["parent_id"]) == 0 && c["P_and_L_type"] != DBNull.Value && Convert.ToInt32(c["P_and_L_type"]) == 1 && Convert.ToInt32(c["account_types_id"]) == 4).Sum(r => Convert.ToDouble(r["total"]));
            double PndL = DI - DE;            
            double GP = (PndL > 0 ? Math.Abs(PndL) : 0), GL= (PndL > 0 ? 0 : Math.Abs(PndL));

            if(GP > 0)
            {
                Literal ltrGP = (Literal)rptGP.Controls[rptGP.Controls.Count - 1].Controls[0].FindControl("ltrGP"); //rptGP.FindControl("ltrGP");
                Literal ltrGPAmt = (Literal)rptGP.Controls[rptGP.Controls.Count - 1].Controls[0].FindControl("ltrGPAmt");
                if (ltrGP != null && ltrGPAmt != null)
                {
                    ltrGP.Text = "Gross Profit c/o";
                    ltrGPAmt.Text = String.Format("{0:0.00}", PndL);
                }
            }
            else if(GL > 0)
            {
                Literal ltrGL = (Literal)rptGP.Controls[rptGP.Controls.Count - 1].Controls[0].FindControl("ltrGL");
                Literal ltrGLAmt = (Literal)rptGP.Controls[rptGP.Controls.Count - 1].Controls[0].FindControl("ltrGLAmt");
                if (ltrGL != null && ltrGLAmt != null)
                {
                    ltrGL.Text = "Gross Loss c/o";
                    ltrGLAmt.Text = String.Format("{0:0.00}", GL);
                }
            }

            Literal ltrDITotal = (Literal)rptGP.Controls[rptGP.Controls.Count - 1].Controls[0].FindControl("ltrDITotal");
            Literal ltrDETotal = (Literal)rptGP.Controls[rptGP.Controls.Count - 1].Controls[0].FindControl("ltrDETotal");
            if(ltrDETotal != null && ltrDITotal != null)
            {
                ltrDETotal.Text = String.Format("{0:0.00}", (DE + GP));
                ltrDITotal.Text = String.Format("{0:0.00}", (DI + GL));
            }

            // Net Profit / Loss
            var drs2 = dtPLData.AsEnumerable().Where(c => Convert.ToInt32(c["parent_id"]) == 0 && c["P_and_L_type"] != DBNull.Value && Convert.ToInt32(c["P_and_L_type"]) == 2).Select(c => c).ToList();
            DataTable pvtTable2 = PivotTable(drs2);
            if(PndL != 0)
            {
                DataRow bfRow = pvtTable2.NewRow();
                bfRow[1] = (PndL > 0 ? " " : "Gross loss b/f");
                bfRow[2] = (PndL > 0 ? 0 : PndL);
                bfRow[4] = (PndL > 0 ? "Gross Profit b/f" : " ");
                bfRow[5] = (PndL > 0 ? PndL : 0);
                pvtTable2.Rows.InsertAt(bfRow, 0);
            }

            rptNP.DataSource = pvtTable2;
            rptNP.DataBind();

            double II = dtPLData.AsEnumerable().Where(c => Convert.ToInt32(c["parent_id"]) == 0 && c["P_and_L_type"] != DBNull.Value && Convert.ToInt32(c["P_and_L_type"]) == 2 && Convert.ToInt32(c["account_types_id"]) == 3).Sum(r => Convert.ToDouble(r["total"]) * -1);
            double IE = dtPLData.AsEnumerable().Where(c => Convert.ToInt32(c["parent_id"]) == 0 && c["P_and_L_type"] != DBNull.Value && Convert.ToInt32(c["P_and_L_type"]) == 2 && Convert.ToInt32(c["account_types_id"]) == 4).Sum(r => Convert.ToDouble(r["total"]));
            double PndL2 = II + PndL - IE;
            double NP = (PndL2 > 0 ? Math.Abs(PndL2) : 0), NL = (PndL2 > 0 ? 0 : Math.Abs(PndL2));
            if (NP > 0)
            {
                Literal ltrNP = (Literal)rptNP.Controls[rptNP.Controls.Count - 1].Controls[0].FindControl("ltrNP"); //rptGP.FindControl("ltrGP");
                Literal ltrNPAmt = (Literal)rptNP.Controls[rptNP.Controls.Count - 1].Controls[0].FindControl("ltrNPAmt");
                if (ltrNP != null && ltrNPAmt != null)
                {
                    ltrNP.Text = "Net Profit";
                    ltrNPAmt.Text = String.Format("{0:0.00}", NP);
                }
            }
            else if(NL > 0)
            {
                Literal ltrNL = (Literal)rptNP.Controls[rptNP.Controls.Count - 1].Controls[0].FindControl("ltrNL");
                Literal ltrNLAmt = (Literal)rptNP.Controls[rptNP.Controls.Count - 1].Controls[0].FindControl("ltrNLAmt");
                if (ltrNL != null && ltrNLAmt != null)
                {
                    ltrNL.Text = "Net Loss";
                    ltrNLAmt.Text = String.Format("{0:0.00}", NL);
                }
            }

            //double TI = dtPLData.AsEnumerable().Where(c => Convert.ToInt32(c["parent_id"]) == 0 && Convert.ToInt32(c["account_types_id"]) == 3).Sum(r => Convert.ToDouble(r["total"]));
            //double TE = dtPLData.AsEnumerable().Where(c => Convert.ToInt32(c["parent_id"]) == 0 && Convert.ToInt32(c["account_types_id"]) == 4).Sum(r => Convert.ToDouble(r["total"]));
            Literal ltrTotal1 = (Literal)rptNP.Controls[rptNP.Controls.Count - 1].Controls[0].FindControl("ltrTotal1");
            Literal ltrTotal2 = (Literal)rptNP.Controls[rptNP.Controls.Count - 1].Controls[0].FindControl("ltrTotal2");
            if (ltrTotal1 != null && ltrTotal2 != null)
            {
                //double diff = TI - TE;
                ltrTotal1.Text = String.Format("{0:0.00}", (IE + GL + NP ));
                ltrTotal2.Text = String.Format("{0:0.00}", (II + GP + NL));
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
                double amt = Convert.ToDouble(dr["total"]);
                if (amt == 0)
                    continue;
                var drow = pvtTable.AsEnumerable().Where(r => r["Amt1"] == DBNull.Value).FirstOrDefault();
                if (typeid == 3)
                    drow = pvtTable.AsEnumerable().Where(r => r["Amt2"] == DBNull.Value).FirstOrDefault();
                if (drow == null)
                {
                    drow = pvtTable.NewRow();
                    pvtTable.Rows.Add(drow);
                }
                if (typeid == 3)
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
        protected void lvProfitAndLoss_DataBound(object sender, EventArgs e)
        {

        }

        protected void rptGP_ItemDataBound(object sender, RepeaterItemEventArgs e)
        {
            int id1 = 0; try { if(DataBinder.Eval(e.Item.DataItem, "id1") != DBNull.Value) id1 = Convert.ToInt32(DataBinder.Eval(e.Item.DataItem, "id1")); } catch { id1 = 0; }
            int id2 = 0; try { if (DataBinder.Eval(e.Item.DataItem, "id2") != DBNull.Value) id2 = Convert.ToInt32(DataBinder.Eval(e.Item.DataItem, "id2")); } catch { id2 = 0; }

            Repeater rptGPChild1 = (Repeater)e.Item.FindControl("rptGPChild1");
            Repeater rptGPChild2 = (Repeater)e.Item.FindControl("rptGPChild2");
            if (rptGPChild1 != null && rptGPChild2 != null && dtPLData != null && dtPLData.Rows.Count > 0)
            {
                if (id1 > 0)
                {
                    try { 
                        DataTable dtGPChild1 = dtPLData.AsEnumerable().Where(r => Convert.ToInt32(r["parent_id"]) == id1).CopyToDataTable();
                        if (dtGPChild1 != null && dtGPChild1.Rows.Count > 0)
                        {
                            rptGPChild1.DataSource = dtGPChild1;
                            rptGPChild1.DataBind();
                        }
                    }
                    catch { }

                }
                if (id2 > 0)
                {
                    try {
                        rptGPChild2.DataSource = dtPLData.AsEnumerable().Where(r => Convert.ToInt32(r["parent_id"]) == id2).CopyToDataTable();
                        rptGPChild2.DataBind();
                    }
                    catch { id2 = 0; }
                    
                }
            }
        }

        protected void rptNP_ItemDataBound(object sender, RepeaterItemEventArgs e)
        {
            //int id = Convert.ToInt32(DataBinder.Eval(e.Item.DataItem, "id"));
            int id1 = 0; try { if (DataBinder.Eval(e.Item.DataItem, "id1") != DBNull.Value) id1 = Convert.ToInt32(DataBinder.Eval(e.Item.DataItem, "id1")); } catch { id1 = 0; }
            int id2 = 0; try { if (DataBinder.Eval(e.Item.DataItem, "id2") != DBNull.Value) id2 = Convert.ToInt32(DataBinder.Eval(e.Item.DataItem, "id2")); } catch { id2 = 0; }

            Repeater rptNPChild1 = (Repeater)e.Item.FindControl("rptNPChild1");
            Repeater rptNPChild2 = (Repeater)e.Item.FindControl("rptNPChild2");
            if (rptNPChild1 != null && rptNPChild2 != null && dtPLData != null && dtPLData.Rows.Count > 0)
            {
                if (id1 > 0)
                {
                    try
                    {
                        rptNPChild1.DataSource = dtPLData.AsEnumerable().Where(r => Convert.ToInt32(r["parent_id"]) == id1).CopyToDataTable();
                        rptNPChild1.DataBind();
                    }
                    catch { }
                   
                }
                if (id2 > 0)
                {
                    try
                    {
                        rptNPChild2.DataSource = dtPLData.AsEnumerable().Where(r => Convert.ToInt32(r["parent_id"]) == id2).CopyToDataTable();
                        rptNPChild2.DataBind();
                    }
                    catch { }
                }
            }
        }
    }
}