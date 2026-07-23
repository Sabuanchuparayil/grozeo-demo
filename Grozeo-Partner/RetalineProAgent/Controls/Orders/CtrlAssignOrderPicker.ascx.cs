using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Controls.Orders
{
    public partial class CtrlAssignOrderPicker : Base.BasePartnerUserControl
    {
        public string hdnfstoid_ClientId
        {
            get { return hdnfstoid.ClientID; }
        }
        public string hdnorderorderid_ClientId
        {
            get { return hdnorderorderid.ClientID; }
        }
        public string hdntoid_ClientId
        {
            get { return hdntoid.ClientID; }
        }
        public string hdnorderid_ClientId
        {
            get { return hdnorderid.ClientID; }
        }
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                SDSOrderPickers.DataBind(); 
            }
        }

        protected void SDSOrderPickers_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;

        }

        protected void btnAdd_Click(object sender, EventArgs e)
        {
            Button btnAssign = (Button)sender;
            if (btnAssign == null || String.IsNullOrEmpty(btnAssign.Attributes["orderpickerid"]))
            {
                // show error
                return;
            }
            int branchid = Convert.ToInt32(btnAssign.Attributes["branchid"]);
            int storegroupid = this.CurrentUser.APIStoreId;

            string orderPIckerId = Convert.ToString(btnAssign.Attributes["orderpickerid"]);
            string transferOrderId = hdntoid.Value;
            int orderId =Convert.ToInt32((hdnorderid.Value).ToString());
            string result = Core.Services.APIService.AssignOrderPicker(transferOrderId, orderId, orderPIckerId, branchid, storegroupid);
            string status = (result);
            if (status == null)
            {
                Common.ShowToastifyMessage(this.Page, "Sorry, Boy already polled.", "danger");
            }
            else
            {
                Common.ShowCustomAlert(this.Page, "Assigned Successfully!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Order picker has been assigned successfully!</a></h5>", true, "/Tenant/PendingOrders");
            }            

        }
    }
}