using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Business
{
    public partial class Default: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
           
        }

        //protected void SDSContacts_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        //{
        //    var user = this.CurrentUser;
        //    List<KeyValuePair<string, object>> baparams = new List<KeyValuePair<string, object>>();
        //    baparams.Add(new KeyValuePair<string, object>("email", user.Email));
        //    DataTable result = DataServiceMySql.GetDataTable($"SELECT ba.id  FROM business_associate ba WHERE baEmail = @email", UserService.GetAPIConnectionString(), baparams);
        //    if (result != null && result.Rows.Count > 0)
        //    {
        //        DataRow dr = result.Rows[0];
        //        string baId = dr["id"].ToString();

        //        e.Command.Parameters["baId"].Value = baId;
        //    }
        //}

        protected void SDSProspect_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
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

        protected void SDSLeads_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
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
            //if (user.AreaId > 0)
            //    e.Command.Parameters["areaId"].Value = user.AreaId;
        }
    }
}