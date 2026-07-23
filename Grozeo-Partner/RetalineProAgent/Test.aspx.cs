using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Data.SqlClient;
using System.Data;
using System.Configuration;
using RetalineProAgent.Service;
using System.Threading.Tasks;
using RetalineProAgent.Core.Services;

namespace RetalineProAgent
{
    public partial class Test: Base.BasePartnerPage
    {

        protected void Page_Load(object sender, EventArgs e)
        {
            //FailureText.Text = "";

            //sendmail().ConfigureAwait(false);

        }

        protected void Unnamed_Click(object sender, EventArgs e)
        {
            string str= EncryptionService.EncryptText("5479");
            string str2 = str;
        }
    }
}