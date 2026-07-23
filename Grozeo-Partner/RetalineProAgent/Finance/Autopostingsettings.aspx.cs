using System;
using System.Collections.Generic;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Windows.Media;

namespace RetalineProAgent.Finance
{
    public partial class Autopostingsettings : System.Web.UI.Page
    {
        protected void Page_Load(object sender, EventArgs e)
        {

        }

        protected void btn_Edit_Click(object sender, EventArgs e)
        {
            string flag = "Edit";
            LinkButton lbtn = (LinkButton)sender;
            int Id = Convert.ToInt32(lbtn.Attributes["recid"]);
            Response.Redirect("/Finance/AutoPostingRules?Id=" + Id + "&flag=" + HttpUtility.UrlEncode(flag));
        }

        protected void btn_View_Click(object sender, EventArgs e)
        {
            string flag = "View";
            LinkButton lbtnView = (LinkButton)sender;
            int Id = Convert.ToInt32(lbtnView.Attributes["recid"]);
            Response.Redirect("/Finance/AutoPostingRules?Id=" + Id + "&flag=" + HttpUtility.UrlEncode(flag));
        }
         
        protected void gvcostpurpose_RowDataBound(object sender, GridViewRowEventArgs e)
        {
            if (e.Row.RowType == DataControlRowType.DataRow)
            {
                DataRowView rowView = e.Row.DataItem as DataRowView;
                if (rowView != null)
                {
                    int status = Convert.ToInt32(rowView["Status"]);
                    if (status == 0)
                    {
                        e.Row.BackColor = ColorTranslator.FromHtml("#ffdfd4");
                    }
                }
            }
        }


    }
}