using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Finance.Controls
{
    public partial class ctlNestedGroup: Base.BasePartnerUserControl
    {
        public int ParentId
        {
            get {
                if(ViewState["THISPARENTID"] != null)
                    return (int)ViewState["THISPARENTID"];
                return -1;
            }
            set {
                ViewState["THISPARENTID"] = value;
            }
        }
        public string FromDate
        {
            get
            {
                if (ViewState["THISFROMDATE"] != null)
                    return (string)ViewState["THISFROMDATE"];
                return "";
            }
            set
            {
                ViewState["THISFROMDATE"] = value;
            }
        }
        public string ToDate
        {
            get
            {
                if (ViewState["THISTODATE"] != null)
                    return (string)ViewState["THISTODATE"];
                return "";
            }
            set
            {
                ViewState["THISTODATE"] = value;
            }
        }
        public int ControlType
        {
            get
            {
                if (ViewState["CTRLTYPE"] != null)
                    return (int)ViewState["CTRLTYPE"];
                return 0;
            }
            set
            {
                ViewState["CTRLTYPE"] = value;
            }
        }
        public bool showtotal { get; set; }
        public void RealoadGrid()
        {
            SDSGroups.Select(DataSourceSelectArguments.Empty);
            gvGroup.DataBind();
        }
        protected void Page_Load(object sender, EventArgs e)
        {

        }
        protected void Page_PreRender(object sender, EventArgs e)
        {
            if(ControlType == 2)
            {
                gvGroup.Visible = false;
                rpGroups.Visible = true;
            }
            else
            {
                gvGroup.Visible = true;
                rpGroups.Visible = false;
            }
        }

        protected void SDSGroups_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["@parent_id"].Value = ParentId;
            e.Command.Parameters["@fromDate"].Value = FromDate;
            e.Command.Parameters["@toDate"].Value = ToDate;
        }

        protected void gvGroup_DataBound(object sender, EventArgs e)
        {
            if (showtotal && gvGroup.FooterRow != null && gvGroup.FooterRow.Cells.Count > 3)
            {
                double totalDr = 0, totalCr = 0, totalOpening=0, totalClosing=0;
                foreach(GridViewRow gr in gvGroup.Rows)
                {
                    if (gr.RowType == DataControlRowType.DataRow)
                    {
                        double dr = 0; try { dr = Convert.ToDouble(gr.Cells[2].Text); } catch { dr = 0; }
                        double cr = 0; try { cr = Convert.ToDouble(gr.Cells[3].Text); } catch { cr = 0; }
                        double opening = 0; try { opening = Convert.ToDouble(gr.Cells[1].Text); } catch { opening = 0; }
                        Literal ltrClosing = (Literal)gr.Cells[4].FindControl("ltrClosing");
                        double closing = 0; try { closing = Convert.ToDouble(ltrClosing.Text); } catch { closing = 0; }
                        totalDr += dr; totalCr += cr; totalOpening += opening; totalClosing += closing;
                    }
                }
                string strFooter = "Total: " + totalDr.ToString();
                //bool isMatching = (totalOpening + totalDr - totalCr) == totalClosing;
                double diffDr = 0, diffCr=0;
                               
                    PlaceHolder plcSA = (PlaceHolder)gvGroup.Rows[gvGroup.Rows.Count - 1].FindControl("plcSuspenseAccount");
                    if (plcSA != null)
                    {
                        if (totalDr > totalCr)
                            diffCr = totalDr - totalCr;
                        else
                            diffDr = totalCr - totalDr;
                        try
                        {

                            Literal ltrSAName = (Literal)plcSA.FindControl("ltrSAName");
                            Literal ltrSAOpening = (Literal)plcSA.FindControl("ltrSAOpening");
                            Literal ltrSACR = (Literal)plcSA.FindControl("ltrSACR");
                            Literal ltrSADR = (Literal)plcSA.FindControl("ltrSADR");
                            Literal ltrSAClosing = (Literal)plcSA.FindControl("ltrSAClosing");
                            plcSA.Visible = true;
                            ltrSAName.Text = "Balancing Difference";
                            ltrSAOpening.Text = String.Format("{0:0.00}", (0-totalOpening));
                            ltrSADR.Text = String.Format("{0:0.00}", diffCr);
                            ltrSACR.Text = String.Format("{0:0.00}", diffDr);
                            ltrSAClosing.Text = String.Format("{0:0.00}", (0-totalOpening + diffDr - +diffCr));
                        }
                        catch { }
                        if (diffCr == 0 && diffDr == 0)
                        {
                           plcSA.Visible = false;
                        }
                }
                
                bool isMatching = (totalOpening + totalDr - totalCr).ToString("0:0.00") == totalClosing.ToString("0:0.00");
                double diff = Math.Round((totalOpening + totalDr - totalCr), 2) - Math.Round(totalClosing, 2);
                if (!isMatching)
                    isMatching = diff > 0;

                //var ltrftr = (Literal)gvGroup.FooterRow.FindControl("ltrFooter");
                //gvGroup.FooterRow.Cells.Add(new TableCell() { Text = "Total" });
                gvGroup.FooterRow.Cells[2].Text = String.Format("{0:0.00}", totalDr + diffDr);
                gvGroup.FooterRow.Cells[3].Text = String.Format("{0:0.00}", totalCr + diffCr);

                //gvGroup.FooterRow.Cells[1].Text = String.Format("{0:0.00}", totalOpening);
                //gvGroup.FooterRow.Cells[4].Text = (isMatching ? "" : $"<i class=\"fas fa-exclamation-circle text-warning\" Title=\"Diff: {String.Format("{0:0.00}", diff)}\"></i>") + String.Format("{0:0.00}", totalClosing);
            }
            //int startRowOnPage = (gvGroup.PageIndex * gvGroup.PageSize) + 1;
            //int lastRowOnPage = startRowOnPage + gvGroup.Rows.Count - 1;


            //var dv = (DataView)SDSGroups.Select(DataSourceSelectArguments.Empty);
        }

        public bool MatchClosing(object objopening, object objdr, object objcr, object objclosing)
        {
            try {
                double opening = Convert.ToDouble(objopening);
                double dr = Convert.ToDouble(objdr);
                double cr = Convert.ToDouble(objcr);
                double closing = Convert.ToDouble(objclosing);

                bool isMatching = (opening + dr - cr).ToString("0:0.00") == closing.ToString("0:0.00");
                double diff = Math.Round((opening + dr - cr), 2) - Math.Round(closing, 2);
                if (!isMatching)
                    isMatching = diff > 0;

                return isMatching; //(opening + dr - cr) == closing;

            } catch { }
            return false;
        }

        protected void gvGroup_SelectedIndexChanged(object sender, EventArgs e)
        {

            var gvRow = gvGroup.SelectedRow;
            if (gvRow != null)
            {
                LinkButton lbtn = (LinkButton)gvRow.FindControl("lbShowChild");
                PlaceHolder plcGroup = (PlaceHolder)gvRow.FindControl("plcGroup");
                if (lbtn != null && plcGroup != null)
                {
                    int did = Convert.ToInt32(lbtn.Attributes["dataid"]);
                    ctlNestedGroup ctrl = (ctlNestedGroup)LoadControl("~/Finance/Controls/ctlNestedGroup.ascx");
                    ctrl.ParentId = did;
                    ctrl.FromDate = FromDate;
                    ctrl.ToDate = ToDate;

                    plcGroup.Controls.Add(ctrl);

                    //SDSChildGroup.SelectParameters["parent_id"].DefaultValue = did.ToString();
                    //gvchild.DataSource = SDSChildGroup.Select(DataSourceSelectArguments.Empty);
                    //gvchild.DataBind();

                }
                //&& lbtn != null && !String.IsNullOrEmpty(lbtn.Attributes["dataid"])
                // gvsubgroup


            }

        }

        protected void gvGroup_RowDataBound(object sender, GridViewRowEventArgs e)
        {
            if (e.Row.RowType != DataControlRowType.DataRow)
                return;

            try { 

                //double opening = Convert.ToInt32(DataBinder.Eval(e.Row.DataItem, "opening"));
                //double dr = Convert.ToInt32(DataBinder.Eval(e.Row.DataItem, "dr"));
                //double cr = Convert.ToInt32(DataBinder.Eval(e.Row.DataItem, "cr"));
                //double closing = Convert.ToInt32(DataBinder.Eval(e.Row.DataItem, "closing"));

                if(!MatchClosing(DataBinder.Eval(e.Row.DataItem, "opening"), DataBinder.Eval(e.Row.DataItem, "dr"), DataBinder.Eval(e.Row.DataItem, "cr"), DataBinder.Eval(e.Row.DataItem, "closing")))
                {
                    Literal ltr = (Literal)e.Row.FindControl("ltrWarning");
                    if(ltr != null)
                    {
                        ltr.Text = $"<i class=\"fas fa-exclamation-circle text-warning\"></i>";
                    }
                    // 
                }
            }
            catch { }
            int groupId = Convert.ToInt32(DataBinder.Eval(e.Row.DataItem, "groups_id"));
            int ltype = -1; try { ltype = Convert.ToInt32(DataBinder.Eval(e.Row.DataItem, "ltype")); } catch { ltype = -1; }

            PlaceHolder plcGroup = (PlaceHolder)e.Row.FindControl("plcGroup");
            if (ltype == 0 && groupId > 0 && plcGroup != null)
            {
                //int did = Convert.ToInt32(lbtn.Attributes["dataid"]);
                ctlNestedGroup ctrl = (ctlNestedGroup)LoadControl("~/Finance/Controls/ctlNestedGroup.ascx");
                ctrl.ParentId = groupId;
                ctrl.FromDate = FromDate;
                ctrl.ToDate = ToDate;
                ctrl.ControlType = 2;
                plcGroup.Controls.Add(ctrl);

                //SDSChildGroup.SelectParameters["parent_id"].DefaultValue = did.ToString();
                //gvchild.DataSource = SDSChildGroup.Select(DataSourceSelectArguments.Empty);
                //gvchild.DataBind();

            }
            else if(ltype == 1)
            {
                e.Row.Cells[0].Font.Bold = false;
                e.Row.Cells[0].Font.Italic = true;
            }

        }
    }
    }