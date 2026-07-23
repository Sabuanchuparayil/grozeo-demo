using RetalineProAgent.Service;
using RetalineProAgent.Core.BussinessModel.Store;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using RetalineProAgent.Core.Services;
using System.EnterpriseServices;
using System.Text;
using System.Web.Script.Serialization;

namespace RetalineProAgent.Business
{
    public partial class BSponsoredItems : Base.BasePartnerPage
    {

        protected void Page_Load(object sender, EventArgs e)
        {
            
        }

        protected void SDSSponsoredPrd_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            var user = this.CurrentUser;
            List<KeyValuePair<string, object>> baparams = new List<KeyValuePair<string, object>>();
            baparams.Add(new KeyValuePair<string, object>("email", user.Email));
            DataTable result = DataServiceMySql.GetDataTable($"SELECT ba.id  FROM business_associate ba WHERE baEmail = @email", UserService.GetAPIConnectionString(), baparams);
            if (result != null && result.Rows.Count > 0)
            {
                DataRow dr = result.Rows[0];
                string baId = dr["id"].ToString();

                e.Command.Parameters["baId"].Value = baId;
            }
            if (user.AreaId > 0)
                e.Command.Parameters["areaId"].Value = user.AreaId;
        }

        protected void gvSProducts_RowCommand(object sender, GridViewCommandEventArgs e)
        {
            if (e.CommandName == "ViewDetails")
            {
                int rowIndex = Convert.ToInt32(e.CommandArgument);
                // Retrieve data for the clicked row
                DataRowView drv = (DataRowView)gvSProducts.Rows[rowIndex].DataItem;
                string total_count = drv["total_count"].ToString();

                if (total_count == "1")
                {
                    // Disable the button for total_count = 1
                    ((Button)gvSProducts.Rows[rowIndex].FindControl("btnView")).Enabled = false;
                }
            }
        }
    }

}


