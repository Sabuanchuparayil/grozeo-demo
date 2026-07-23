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
    public partial class FAQ: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                
            }
            
        }

        protected void Page_PreRender(object sender, EventArgs e)
        {
            

        }

        protected void gvFAQ_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvFAQ.PageIndex * gvFAQ.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvFAQ.Rows.Count - 1;
            //ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSFAQ.Select(DataSourceSelectArguments.Empty);
        }


        //protected void chkStatus_CheckedChanged(object sender, EventArgs e)
        //{
        //    CheckBox chbtn = (CheckBox)sender;

        //    if (chbtn != null && !String.IsNullOrEmpty(chbtn.Attributes["faqId"]))
        //    {
        //        int faqId = Convert.ToInt32(chbtn.Attributes["faqId"]);
        //        int activeStatus = (chbtn.Checked ? 1 : 0);

        //        List<KeyValuePair<string, object>> faqparams = new List<KeyValuePair<string, object>>();
        //        faqparams.Add(new KeyValuePair<string, object>("faqId", faqId));
        //        faqparams.Add(new KeyValuePair<string, object>("activeStatus", activeStatus));
                
        //        string strSql = "UPDATE app_faqs SET faq_status=@activeStatus WHERE faq_id=@faqId";
        //        DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), faqparams);
        //        Common.ShowCustomAlert(this.Page, "Data updated!", "Data updated successfully!", true, "/Tenant/FAQ");
        //    }
        //    gvFAQ.DataBind();
        //}
    }
}
