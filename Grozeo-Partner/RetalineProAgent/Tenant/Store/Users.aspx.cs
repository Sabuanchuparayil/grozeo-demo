using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class Users: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {

        }

        protected void SDSUsers_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["@user"].Value = Page.User.Identity.Name;

            int storegrupid = this.CurrentUser.StoreGroupId;
            if (storegrupid > 0)
                e.Command.Parameters["@storegroupid"].Value = this.CurrentUser.StoreGroupId;
            else
                e.Command.Parameters["@storegroupid"].Value = 0;
           
        }
        protected void SDSOrderPicker_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        }
        protected void SDSDeliveryBoy_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
            //if (selBranches.Items.Count < 1)
            //    selBranches.DataBind();

            //e.Command.Parameters["branchId"].Value = selBranches.Text;            
        }


    }
}