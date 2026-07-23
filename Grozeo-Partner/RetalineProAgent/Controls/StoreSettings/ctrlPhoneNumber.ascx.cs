using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Controls.StoreSettings
{
    public partial class ctrlPhoneNumber : System.Web.UI.UserControl
    {
        public string PhoneNumber
        {
            get
            {
                return txtphone.Text;
            }
            set
            {
                txtphone.Text = value;
            }
        }
        public string MobileNumber
        {
            get
            {
                return ViewState["PHONENUMBER"] == null ? string.Empty : (string)ViewState["PHONENUMBER"];
            }
            set
            {
                ViewState["PHONENUMBER"] = value;
            }
        }

        protected void Page_Load(object sender, EventArgs e)
        {

        }
    }
}