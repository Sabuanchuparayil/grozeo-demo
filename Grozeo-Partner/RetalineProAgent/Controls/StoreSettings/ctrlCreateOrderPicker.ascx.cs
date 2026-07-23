using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Controls.StoreSettings
{
    public partial class ctrlCreateOrderPicker: Base.BasePartnerUserControl
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
               
                LoadStoreInfo();
            }
        }

        private void LoadStoreInfo()
        {
            int id = Convert.ToInt32(Request.QueryString["id"]);
                    if (id > 0)
                    {
                DataTable dt = DataServiceMySql.GetDataTable($"SELECT name, lname, phone, emp_id, emp_ni_number, emp_email_id, emp_add1, emp_add2, emp_pincode, branch_id, is_cpd, is_offline, is_allowManualSchedule,is_allowAutoSchedule FROM retaline_godown_boy WHERE id = {id}", UserService.GetAPIConnectionString());
                if (dt != null && dt.Rows.Count > 0)
                {
                    DataRow da = dt.Rows[0];
                    txtFirstName.Text = da["name"].ToString();
                    txtLastName.Text = da["lname"].ToString();
                    txtPhone.Text = da["phone"].ToString();
                    txtEmpID.Text = da["emp_id"].ToString();
                    txtEmpNINumber.Text = da["emp_ni_number"].ToString();
                    txtEmailID.Text = da["emp_email_id"].ToString();
                    txtAddress1.Text = da["emp_add1"].ToString();
                    txtAddress2.Text = da["emp_add2"].ToString();
                    txtPostCode.Text = da["emp_pincode"].ToString();
                    chkManualSchedule.Checked = true;
                    chkAutoSchedule.Checked = true;
                }
            }
        }
        protected void btnAdd_Click(object sender, EventArgs e)
        {
            int id = Convert.ToInt32(Request.QueryString["id"]);
            int storegroupid = this.CurrentUser.APIStoreId;
            int manulschedule = new int();
           if (chkManualSchedule.Checked)
            {
                manulschedule = 1;
            }
            string checkmanualschedule = Convert.ToString(manulschedule);
            int autoschedule = new int();
            if (chkAutoSchedule.Checked)
            {
                autoschedule = 1;
            }
            string checkautoschedule = Convert.ToString(autoschedule);
            
            var dtBranches = DataServiceMySql.GetDataTable($"SELECT br_ID, br_Name, br_PyramidLevel FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
            foreach (DataRow dr in dtBranches.Rows)
            {
                string brId = dr["br_ID"].ToString();
                string cpd = dr["br_PyramidLevel"].ToString();
                if (id == 0)
                {
                    string strSql = $"INSERT INTO retaline_godown_boy(name, lname, phone, emp_id, emp_ni_number, emp_email_id, emp_add1, emp_add2, " +
                        $"emp_pincode, branch_id, is_cpd, is_allowManualSchedule, is_allowAutoSchedule) " +
                        $"VALUES('" + txtFirstName.Text + "', '" + txtLastName.Text + "', '" + txtPhone.Text + "', '" + txtEmpID.Text + "', " +
                        "'" + txtEmpNINumber.Text + "', '" + txtEmailID.Text + "', '" + txtAddress1.Text + "', '" + txtAddress2.Text + "', " +
                        "'" + txtPostCode.Text + "', '" + brId + "', '" + cpd + "', '" + checkmanualschedule + "', '" + checkautoschedule + "')";
                    DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString());
                    Response.Write("<script>alert('Order Picker details saved successfully')</script>");
                    Response.Redirect("~/OrderPicker");
                }
                else
                {
                    string strUpdateSql = $"UPDATE retaline_godown_boy SET name ='" + txtFirstName.Text + "', lname ='" + txtLastName.Text + "', " +
                    $"phone ='" + txtPhone.Text + "', emp_id ='" + txtEmpID.Text + "', emp_ni_number ='" + txtEmpNINumber.Text + "', " +
                    $"emp_email_id ='" + txtEmailID.Text + "', emp_add1 ='" + txtAddress1.Text + "', emp_add2 ='" + txtAddress2.Text + "', " +
                    $"emp_pincode ='" + txtPostCode.Text + "', is_allowManualSchedule ='" + checkmanualschedule + "' , is_allowAutoSchedule ='" + checkautoschedule + "' WHERE id = '" + id + "'";
                    DataServiceMySql.ExecuteSql(strUpdateSql, UserService.GetAPIConnectionString());
                    Response.Write("<script>alert('Order Picker details updated successfully')</script>");
                    Response.Redirect("~/OrderPicker");
                }
            }
        }
    }
}