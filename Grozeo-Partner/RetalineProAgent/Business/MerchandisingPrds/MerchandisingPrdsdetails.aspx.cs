using NPOI.POIFS.Properties;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Data.SqlTypes;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using static RetalineProAgent.Finance.AutoPostingRules;

namespace RetalineProAgent.Business
{
    public partial class MerchandisingPrdsdetails : System.Web.UI.Page
    {
        
        protected void Page_Load(object sender, EventArgs e)
        {

        }

        //protected void rptDetails_ItemDataBound(object sender, RepeaterItemEventArgs e)
        //{
        //        Label lblTitle = e.Item.FindControl("lblTitle") as Label;
        //        DataRowView drv = e.Item.DataItem as DataRowView;
        //        string stit_SKU = drv["stit_SKU"].ToString();

        //        // Set the title dynamically
        //        lblTitle.Text = stit_SKU;
        //}

        protected void SDSListDetails_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            string itemId = Request.QueryString["stit_id"];
            e.Command.Parameters["itemId"].Value = itemId;
        }
    }
}