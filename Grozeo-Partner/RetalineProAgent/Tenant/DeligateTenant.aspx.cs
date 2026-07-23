using RetalineProAgent.Core.BussinessModel.Finance;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Tenant
{
    public partial class DeligateTenant : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            int apiStoregroupId = 0, storegroupid = 0;


			if (!(User.IsInRole("Deligation") || User.IsInRole("SuperAdmin")))
            {
                lblResult.Text = "Access denied or lack of permission";
                return;
            }
			if (!String.IsNullOrEmpty(Request.QueryString["sg"]))
				apiStoregroupId = Convert.ToInt32(Request.QueryString["sg"]);
            else if (!String.IsNullOrEmpty(Request.QueryString["storeid"]))
				storegroupid = Convert.ToInt32(Request.QueryString["storeid"]);

			if (apiStoregroupId <= 0 && storegroupid <= 0)
            {				
				lblResult.Text = "Invalid store selection.";
                return;
            }

            
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();            
            prms.Add(new KeyValuePair<string, object>("storeid", storegroupid));
			prms.Add(new KeyValuePair<string, object>("apistoreid", apiStoregroupId));
			DataTable dtStore = DataService.GetDataTable("SELECT top 1 * FROM AppTenant WHERE (@apistoreid > 0 and StoreId = @apistoreid) or (@storeid > 0 and Id=@storeid)", parmeters: prms);
            if(dtStore == null || dtStore.Rows.Count <= 0)
            {
                lblResult.Text = "Invalid store or the store does not exists!";
                return;
            }

            string sgid = dtStore.Rows[0]["id"].ToString();
            

            string strstorename = dtStore.Rows[0]["name"].ToString();
            prms.Add(new KeyValuePair<string, object>("sname", strstorename));
			prms.Add(new KeyValuePair<string, object>("sgid", sgid));

			prms.Add(new KeyValuePair<string, object>("userid", this.CurrentUser.Id));
            string sql = "UPDATE [User] SET StoreGroupId= @sgid, StoreGroupName=@sname WHERE Id like @userid";
            int result = DataService.ExecuteSql(sql, parmeters: prms);
            Service.UserService.CachedDefaultUser = null;
            User user = this.CurrentUser;
            if (result > 0 && user != null)
            {
                user.StoreGroupId = storegroupid;
                user.StoreGroupName = strstorename;
                Service.UserService.CachedDefaultUser = user;
                Page.Response.Redirect("/Tenant/", true);
            }

        }
    }
}