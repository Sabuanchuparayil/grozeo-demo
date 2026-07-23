using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Finance
{
    public partial class Trialbalance: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            txtFromDate.Attributes.Add("max", DateTime.Now.AddDays(-1).ToString("yyyy-MM-dd"));
            txtToDate.Attributes.Add("max", DateTime.Now.AddDays(-1).ToString("yyyy-MM-dd"));
            //ctlNestedGroup1.ParentId = 0;
            if (!IsPostBack)
            {
                txtFromDate.Text = DateTime.Now.AddDays(-31).ToString("yyyy-MM-dd");
                txtToDate.Text = DateTime.Now.AddDays(-1).ToString("yyyy-MM-dd");
                ctlNestedGroup1.FromDate = txtFromDate.Text;
                ctlNestedGroup1.ToDate = txtToDate.Text;

                ctlNestedGroup1.RealoadGrid();

            }
        }
        private void ShowAddItem()
        {
            Type cstype = this.GetType();
            String csname1 = "AddItemPopupScript";
            ClientScriptManager cs = Page.ClientScript;

            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append("<script type=text/javascript> $('#gvsubgroup').modal('show'); </");
            cstext1.Append("script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }
        protected void imggroup_Click(object sender, ImageClickEventArgs e)
        {

        }

        protected void gvGroup_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvGroup.PageIndex * gvGroup.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvGroup.Rows.Count - 1;


            var dv = (DataView)SDSGroups.Select(DataSourceSelectArguments.Empty);
        }

        protected void gvGroup_SelectedIndexChanged(object sender, EventArgs e)
        {

            var gvRow = gvGroup.SelectedRow;
            if (gvRow != null)
            {
                LinkButton lbtn = (LinkButton)gvRow.FindControl("lbShowChild");
                GridView gvchild = (GridView)gvRow.FindControl("gvsubgroup");
                if (lbtn != null && gvchild != null && !String.IsNullOrEmpty(lbtn.Attributes["dataid"]))
                {
                    int did = Convert.ToInt32(lbtn.Attributes["dataid"]);

                    SDSChildGroup.SelectParameters["parent_id"].DefaultValue = did.ToString();
                    gvchild.DataSource = SDSChildGroup.Select(DataSourceSelectArguments.Empty);
                    gvchild.DataBind();

                }
                //&& lbtn != null && !String.IsNullOrEmpty(lbtn.Attributes["dataid"])
                // gvsubgroup


            }


        }

        protected void lbtnSearch_Click(object sender, EventArgs e)
        {
            ctlNestedGroup1.FromDate = txtFromDate.Text;
            ctlNestedGroup1.ToDate = txtToDate.Text;

            ctlNestedGroup1.RealoadGrid();
        }
    }
}

