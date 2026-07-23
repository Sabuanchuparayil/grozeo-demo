using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Controls.StoreSettings
{
    public partial class ctrlMessagebox: Base.BasePartnerUserControl
    {
        protected void Page_Load(object sender, EventArgs e)
        {

        }

        public void ShowResult(string title, string content, int type, string onCloseRedirectTo = "") {
            if (type == 1)
                ShowSuccess(title, content, onCloseRedirectTo);
            else
                ShowFailure(title, content, onCloseRedirectTo);
        }
        public void ShowFailure(string title, string content, string onCloseRedirectTo="")
        {
            ltrErrorPopupTitle.Text = title;
            ltrErrorPopupText.Text = content;
            Type cstype = this.Page.GetType();
            String csname1 = "PopupScript";
            ClientScriptManager cs = Page.ClientScript;


            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append("<script type=text/javascript> $('#modalfailure').modal('show'); ");
            if(!String.IsNullOrEmpty(onCloseRedirectTo))
                cstext1.Append("$('#modalfailure').on('hidden.bs.modal', function (e) {window.location.href = '" + onCloseRedirectTo + "'; });");
            cstext1.Append("</");
            cstext1.Append("script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());

        }

        public void ShowSuccess(string title, string content, string onCloseRedirectTo = "")
        {
            ltrSuccessTitle.Text = title;
            ltrSuccessContent.Text = content;
            Type cstype = this.Page.GetType();
            String csname1 = "PopupScript";
            ClientScriptManager cs = Page.ClientScript;


            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append("<script type=text/javascript> $('#modalsuccess').modal('show'); ");
            if (!String.IsNullOrEmpty(onCloseRedirectTo))
                cstext1.Append("$('#modalsuccess').on('hidden.bs.modal', function (e) {window.location.href = '" + onCloseRedirectTo + "'; });");
            cstext1.Append("</");
            cstext1.Append("script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());

        }

    }
}