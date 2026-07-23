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

namespace RetalineProAgent
{
    public partial class Area: Base.BasePartnerPage
    {


        protected void Page_Load(object sender, EventArgs e)
        {

        }

        protected void Page_PreRender(object sender, EventArgs e)
        {

        }


        protected void SDSArea_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
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

            e.Command.Parameters["isAdmin"].Value = (Page.User.IsInRole("SuperAdmin") ? 1 : 0);

        }

        protected void lbSelectArea_Click(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;
            var _partner = this.CurrentUser;

            int areaId = 0;
            if (lbtn != null && !String.IsNullOrEmpty(lbtn.Attributes["areaid"]))
                try { areaId = Convert.ToInt32(lbtn.Attributes["areaid"]); } catch { areaId = 0; }
            if(areaId <= 0)
            {
                Common.ShowCustomAlert(this.Page, "Failure", "Invalid area selection", false);
                return;
            }

            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("areaId", areaId));
            prms.Add(new KeyValuePair<string, object>("userId", _partner.Id));

            DataService.ExecuteSql("UPDATE [User] SET AreaId=@areaId WHERE Id=@userId", parmeters: prms);
            Service.UserService.CachedDefaultUser = null;
            gvArea.DataBind();
        }

        protected void gvArea_RowDataBound(object sender, GridViewRowEventArgs e)
        {
            if(e.Row.RowType == DataControlRowType.DataRow)
            {
                LinkButton lb = (LinkButton)e.Row.FindControl("lbSelectArea");
                if(lb != null)
                {
                    try
                    {
                        lb.Visible = lb.Attributes["areaid"] != this.CurrentUser.AreaId.ToString();
                    }
                    catch { }
                }
            }
        }
    }

}


