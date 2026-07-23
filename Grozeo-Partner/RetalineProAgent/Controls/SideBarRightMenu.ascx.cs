using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Controls
{
    public partial class SideBarRightMenu: Base.BasePartnerUserControl
    {
        public string plcSwitchButton { get; set; }
        public string plcSideMenuContainer { get; set; }
        protected void Page_Load(object sender, EventArgs e)
        {

        }
        protected void Page_PreRender(object sender, EventArgs e)
        {
            if (selSwitchStore.Items.Count <= 0)
                selSwitchStore.DataBind();

            if (selSwitchStore.Items.FindByValue(this.CurrentUser.StoreGroupId.ToString()) != null)
                selSwitchStore.SelectedValue = this.CurrentUser.StoreGroupId.ToString();
        }

        protected void btnSwitchStore(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;
            string strstoreid = lbtn.Attributes["storeid"];
            string strstorename = lbtn.Attributes["lbltext"];
            if (!String.IsNullOrEmpty(strstoreid))
            {
                int storegroupid = Convert.ToInt32(strstoreid);
                List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                prms.Add(new KeyValuePair<string, object>("sid", storegroupid));
                prms.Add(new KeyValuePair<string, object>("sname", strstorename));
                prms.Add(new KeyValuePair<string, object>("email", Page.User.Identity.Name));
                string sql = "UPDATE [User] SET StoreGroupId= @sid, StoreGroupName=@sname WHERE Email like @email";
                if (!(Page.User.IsInRole("SuperAdmin") || Page.User.IsInRole("RetalineProAgent")))
                    sql = String.Format("IF EXISTS(SELECT * FROM User_UserRole_Mapping WHERE StoreGroupId=@sid) BEGIN {0} END", sql);
                int result = DataService.ExecuteSql(sql, parmeters: prms);
                Service.UserService.CachedDefaultUser = null;
                User user = this.CurrentUser; //FormsAuthenticationService.GetAuthenticatedCustomer();
                if (result > 0 && user != null)
                {
                    user.StoreGroupId = storegroupid;
                    user.StoreGroupName = strstorename; //rbtn.Text;
                    Service.UserService.CachedDefaultUser = user;
                    Page.Response.Redirect(Page.Request.Url.ToString(), true);
                }
            }
        }

        protected void SDSStoreAccounts_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["@usertype"].Value = (Page.User.IsInRole("SuperAdmin") || Page.User.IsInRole("RetalineProAgent") ?"0":"1");
            e.Command.Parameters["@user"].Value = Page.User.Identity.Name;
            if(!String.IsNullOrEmpty(System.Configuration.ConfigurationManager.AppSettings.Get("APIID")))
                e.Command.Parameters["@apiid"].Value = System.Configuration.ConfigurationManager.AppSettings.Get("APIID");
        }

        protected void SDSStoreAccounts_Selected(object sender, SqlDataSourceStatusEventArgs e)
        {
            int rows = e.AffectedRows;
            if (!String.IsNullOrEmpty(plcSwitchButton))
            {
                PlaceHolder plc = (PlaceHolder)this.Parent.FindControl(plcSwitchButton);
                if (plc != null)
                    plc.Visible = rows > 1;
            }
            if (!String.IsNullOrEmpty(plcSideMenuContainer))
            {
                PlaceHolder plc = (PlaceHolder)this.Parent.FindControl(plcSideMenuContainer);
                if (plc != null)
                    plc.Visible = rows > 1;
            }
        }

        protected void selSwitchStore_SelectedIndexChanged(object sender, EventArgs e)
        {
            if(String.IsNullOrEmpty(selSwitchStore.Text))
            {
                Common.ShowToastifyMessage(this.Page, "Invalid Store", "danger");
                return;
            }
            //LinkButton lbtn = (LinkButton)sender;
            //string strstoreid = lbtn.Attributes["storeid"];
            //string strstorename = lbtn.Attributes["lbltext"];
            int storegroupid = Convert.ToInt32(selSwitchStore.Text);
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("sid", storegroupid));
            prms.Add(new KeyValuePair<string, object>("sname", selSwitchStore.SelectedItem.Text));
            prms.Add(new KeyValuePair<string, object>("email", Page.User.Identity.Name));
            string sql = "UPDATE [User] SET StoreGroupId= @sid, StoreGroupName=@sname WHERE Email like @email";
            if (!(Page.User.IsInRole("SuperAdmin") || Page.User.IsInRole("RetalineProAgent")))
                sql = String.Format("IF EXISTS(SELECT * FROM User_UserRole_Mapping WHERE StoreGroupId=@sid) BEGIN {0} END", sql);
            int result = DataService.ExecuteSql(sql, parmeters: prms);
            Service.UserService.CachedDefaultUser = null;
            User user = this.CurrentUser; //FormsAuthenticationService.GetAuthenticatedCustomer();
            if (result > 0 && user != null)
            {
                user.StoreGroupId = storegroupid;
                user.StoreGroupName = selSwitchStore.SelectedItem.Text; //rbtn.Text;
                Service.UserService.CachedDefaultUser = user;
                Page.Response.Redirect(Page.Request.Url.ToString(), true);
            }

        }
    }
}