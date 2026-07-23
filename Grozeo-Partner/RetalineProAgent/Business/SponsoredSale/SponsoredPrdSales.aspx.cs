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
    public partial class SponsoredPrdSales : System.Web.UI.Page
    {

        protected void Page_Load(object sender, EventArgs e)
        {

        }

        protected void SDSListDetails_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            string storegroupId = Request.QueryString["storegroupId"];
            string branchId = Request.QueryString["branchId"];
            e.Command.Parameters["storegroupId"].Value = storegroupId;
            e.Command.Parameters["branchId"].Value = branchId;
        }

    }
}