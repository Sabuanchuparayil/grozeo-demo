using RetalineProAgent.Service;
using RetalineProAgent.Core.BussinessModel.Store;
using System;
using System.Collections.Generic;
using System.Data;
using System.Text;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using RetalineProAgent.Core.Services;


namespace RetalineProAgent
{
    public partial class DeliveryStaff : Base.BasePartnerPage
    {


        protected void Page_Load(object sender, EventArgs e)
        {

        }

        protected void Page_PreRender(object sender, EventArgs e)
        {

        }

        protected void gvDeliveryBoy_DataBound(object sender, EventArgs e)
        {

        }

        protected void SDSDeliveryBoy_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
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
        }
    }

}


