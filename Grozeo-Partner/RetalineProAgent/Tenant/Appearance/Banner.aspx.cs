using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Appearance
{
    public partial class Banner: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
        
        }

        protected void SDSHomeBanners_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
        }

        protected async void lbtnDelBanner_Click(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;
            if (lbtn != null && !String.IsNullOrEmpty(lbtn.Attributes["itemid"]))
            {
                List<KeyValuePair<String, Object>> parmeters = new List<KeyValuePair<string, object>>();
                parmeters.Add(new KeyValuePair<string, object>("StoreGroupId", this.CurrentUser.APIStoreId));
                parmeters.Add(new KeyValuePair<string, object>("advid", lbtn.Attributes["itemid"]));

                string sql = $"delete from app_advertisements where adv_id=@advid and storegroup_id=@StoreGroupId; ";
                int result = DataServiceMySql.ExecuteSql(sql, UserService.GetAPIConnectionString(), parmeters);

                if (!string.IsNullOrEmpty(lbtn.Attributes["imgurl"]))
                    await FileService.DeleteS3ImageAsync(lbtn.Attributes["imgurl"]);

                SDSOwnBanners.Select(DataSourceSelectArguments.Empty);
                rptOwnbanners.DataBind();
                if(result > 0)
                    Common.ShowToastifyMessage(this.Page, "Banner deleted succesfully.");
                else
                    Common.ShowToastifyMessage(this.Page, "Executed deletion.", "info");

                return;
            }
            Common.ShowToastifyMessage(this.Page, "Invalid operation", "danger");
        }
    }
}