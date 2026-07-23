using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using RetalineProAgent.Core.BussinessModel.Store;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class SMSReport : Base.BasePartnerPage
    {
        List<Store> _myBranches = null;
        List<Store> MyBranches
        {
            get
            {

                if (_myBranches == null)
                {
                    _myBranches = Core.Services.APIService.GetStores(this.CurrentUser.APIStoreId, false);
                }
                return _myBranches;
            }
            set { _myBranches = value; }
        }
        protected void Page_Load(object sender, EventArgs e)
        {
        }

        protected void Page_PreRender(object sender, EventArgs e)
        {

        }

        protected void gvSms_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvSms.PageIndex * gvSms.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvSms.Rows.Count - 1;
            //ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSSms.Select(DataSourceSelectArguments.Empty);
        }

        protected void SDSSms_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        }

        
    }
}


