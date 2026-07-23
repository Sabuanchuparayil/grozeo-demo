using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using static RetalineProAgent.Finance.CostAllocation;

namespace RetalineProAgent.Finance
{
    public partial class Testcost : System.Web.UI.Page
    {
        [Serializable]
        public class Allocationrules
        {
           
            /// <summary>
            /// Ledger ID
            /// </summary>
            public int ledgerId { get; set; }

            /// <summary>
            /// CostPurpose
            /// </summary>
            public string CostPurpose { get; set; }
            /// <summary>
            /// CostCategory
            /// </summary>

            public string CostCategory { get; set; }
            /// <summary>
            /// CostCentre
            /// </summary>
            public string CostCentre { get; set; }
            /// <summary>
            /// Allocation
            /// </summary>
            public double Allocation { get; set; }

            public int CostPurposeid { get; set; }
            public int CostCategoryid { get; set; }
            public int CostCentreid { get; set; }
            public int mode { get; set; }
        }
        public List<Allocationrules> lstcostallocations
        {
            get
            {
                if (ViewState["COSTALLOCATIONLIST"] != null)
                    return (List<Allocationrules>)ViewState["COSTALLOCATIONLIST"];
                return new List<Allocationrules>();
            }
            set
            {
                ViewState["COSTALLOCATIONLIST"] = value;
            }
        }

        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                BindListView();
            }
        }

        protected void lbAddEntry_Click(object sender, EventArgs e)
        {
           
            
           
             
                int ledgerId = 0;
                string CostPurpose;
                string CostCategory;
                string CostCentre;
                double Allocation;
                int CostPurposeid;
                int CostCategoryid;
                int CostCentreid;
                int Mode;


             
                ledgerId = int.Parse(ddlledger.SelectedItem.Value);
                CostPurpose = ddlCostpurpose.SelectedItem.Text;
                CostCategory = ddlCostCategory.SelectedItem.Text;
                CostCentre = ddlCostCentre.SelectedItem.Text;
                Allocation = Convert.ToDouble(txtAllocation.Text);
                CostPurposeid = int.Parse(ddlCostpurpose.SelectedItem.Value);
                CostCategoryid = int.Parse(ddlCostCategory.SelectedItem.Value);
                CostCentreid = int.Parse(ddlCostCentre.SelectedItem.Value);
                Mode = int.Parse(Ddlmode.SelectedValue);


                var ventries = lstcostallocations;
                ventries.Add(new Allocationrules
                {

                    ledgerId = ledgerId,
                    CostPurpose = CostPurpose,
                    CostCategory = CostCategory,
                    CostCentre = CostCentre,
                    Allocation = Allocation,
                    CostPurposeid = CostPurposeid,
                    CostCategoryid = CostCategoryid,
                    CostCentreid = CostCentreid,
                    mode = Mode,

                });
                lstcostallocations = ventries;
                lvcostallocation.DataSource = lstcostallocations;
                lvcostallocation.DataBind();
                ddlCostCategory.SelectedIndex = -1;
                ddlCostCentre.SelectedIndex = -1;
                ddlCostpurpose.SelectedIndex = -1;
                Ddlmode.SelectedIndex = -1;
                txtAllocation.Text = "";
            


        }

        protected void btnSave_Click(object sender, EventArgs e)
        {

        }

        private void BindListView()
        {
            lvcostallocation.DataSource = lstcostallocations; 
            lvcostallocation.DataBind();
        }

        protected void lvcostallocation_ItemEditing(object sender, ListViewEditEventArgs e)
        {
            lvcostallocation.EditIndex = e.NewEditIndex;
            BindListView();
        }

        protected void lvcostallocation_ItemCommand(object sender, ListViewCommandEventArgs e)
        {
          
            if (e.CommandName == "Delete")
            {
                var ventry = lstcostallocations;
                var item = ventry[e.Item.DataItemIndex];
                ventry.RemoveAt(e.Item.DataItemIndex);
                lstcostallocations = ventry;
                lvcostallocation.DataSource = lstcostallocations;
                lvcostallocation.EditIndex = -1;
                lvcostallocation.DataBind();



            }
            if (e.CommandName == "Update")
            {
                var ventry = lstcostallocations;
                var item = ventry[e.Item.DataItemIndex];
                DropDownList cost = (DropDownList)e.Item.FindControl("ddlCostpurpose_update");
                DropDownList CostCategory = (DropDownList)e.Item.FindControl("ddlCostCategory_update");
                DropDownList CostCentre = (DropDownList)e.Item.FindControl("ddlCostCentre_update");
                TextBox txtallocation = (TextBox)e.Item.FindControl("txtAllocation_update");
                item.CostPurpose = CostCategory.Text;
                item.CostCentre = CostCentre.Text;
                item.CostPurpose = cost.Text;
                item.Allocation = Convert.ToDouble(txtallocation);
                ventry[e.Item.DataItemIndex] = item;
                lstcostallocations = ventry;

                lvcostallocation.EditIndex = -1;
                lvcostallocation.DataSource = lstcostallocations;
                lvcostallocation.DataBind();
            }
        }

        protected void lvcostallocation_ItemUpdating(object sender, ListViewUpdateEventArgs e)
        {

        }

        protected void lvcostallocation_ItemCanceling(object sender, ListViewCancelEventArgs e)
        {
            lvcostallocation.EditIndex = -1;
            BindListView();

        }

        protected void lvcostallocation_ItemDeleting(object sender, ListViewDeleteEventArgs e)
        {

        }

        protected void cancel_Click(object sender, EventArgs e)
        {
            Response.Redirect("/Finance/costallocationrules");
        }
    }
}