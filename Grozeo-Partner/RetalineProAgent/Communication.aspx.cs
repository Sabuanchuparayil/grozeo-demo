using System;
using System.Collections.Generic;
using System.Data;
using System.Globalization;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class Communication: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            txtDOB.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
        }

       
        protected void btnAdd_Click(object sender, EventArgs e)
        {
            
        }
    }
}



