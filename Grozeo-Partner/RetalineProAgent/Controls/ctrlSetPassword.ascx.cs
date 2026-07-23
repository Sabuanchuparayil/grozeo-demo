using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Controls
{
    public partial class ctrlSetPassword: Base.BasePartnerUserControl
    {
        protected void Page_Load(object sender, EventArgs e)
        {

        }

        protected void btnSetPassword_Click(object sender, EventArgs e)
        {
            if (String.IsNullOrEmpty(txtSetPassword1.Text))
            {
                lblResult.Text = "Invalid password 1";
                return;
            }
            else if (String.IsNullOrEmpty(txtSetPassword2.Text))
            {
                lblResult.Text = "Invalid Password 2";
                return;
            }
            else if (txtSetPassword1.Text != txtSetPassword2.Text)
            {
                lblResult.Text = "Password 1 and Password 2 are not matching";
                return;
            }
            string saltKey = EncryptionService.CreateSaltKey(5);
            string strEncPsw = EncryptionService.CreatePasswordHash(txtSetPassword1.Text, saltKey);

            string sql = "UPDATE [User] SET [Password] = @psw, [PasswordSalt] = @saltKey, [PasswordType] = 2  WHERE id= @id; ";
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("psw", strEncPsw));
            prms.Add(new KeyValuePair<string, object>("saltKey", saltKey));
            prms.Add(new KeyValuePair<string, object>("id", this.CurrentUser.Id));
            int rowsupdated = DataService.ExecuteSql(sql, parmeters: prms);
            if (rowsupdated > 0)
                Common.ShowCustomAlert(this.Page, "success", "Password set successfully",true, "/Tenant/Default");
            else
                Common.ShowToastifyMessage(this.Page, "Submitted for execution.", "info");

        }
    }
}