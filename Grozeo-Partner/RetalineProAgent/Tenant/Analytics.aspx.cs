using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using RetalineProAgent.Core.Services;
//using RetalineProAgent.Core.BussinessModel.OnlineOrders;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.IO;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class Analytics : Base.BasePartnerPage
    {
        
        protected void Page_Load(object sender, EventArgs e)
        {
            int analyticsId = 0;
            if (this.CurrentUser.AnalyticsId == null)
            {
                ifmAnalytics.Visible = false;
                analyticsId = 0;
            }
            else
            {
                ifmAnalytics.Visible = true;
                analyticsId = (int)this.CurrentUser.AnalyticsId;
                ifmAnalytics.Src = $"{ConfigurationSettings.AppSettings.Get("MatomoUrl")}index.php?module=Widgetize&action=iframe&moduleToWidgetize=Dashboard&actionToWidgetize=index&idSite={analyticsId}&period=week&date=yesterday&token_auth={ConfigurationSettings.AppSettings.Get("MatomoViewToken")}";
            }
            
        }
        
    }
}