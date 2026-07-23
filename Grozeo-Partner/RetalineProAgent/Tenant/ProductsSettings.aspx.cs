using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Globalization;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Data.SqlClient;
using RetalineProAgent.Core.Services;

namespace RetalineProAgent
{
    public partial class ProductsSettings: Base.BasePartnerPage
    {
        int count = 0;
        protected void Page_Load(object sender, EventArgs e)
        {
            SDSSubCat.ConnectionString = DataService.APIConnectionString(Service.UserService.GetAPIConnectionString());
            SDSBrand.ConnectionString = DataService.APIConnectionString(Service.UserService.GetAPIConnectionString());
            SDSProductMaster.ConnectionString = DataService.APIConnectionString(Service.UserService.GetAPIConnectionString());
            SDSUnit.ConnectionString = DataService.APIConnectionString(Service.UserService.GetAPIConnectionString());
            SDSHsn.ConnectionString = DataService.APIConnectionString(Service.UserService.GetAPIConnectionString());
            SDSCountry.ConnectionString = DataService.APIConnectionString(Service.UserService.GetAPIConnectionString());
            SDSPackage.ConnectionString = DataService.APIConnectionString(Service.UserService.GetAPIConnectionString());
            SDSSku.ConnectionString = DataService.APIConnectionString(Service.UserService.GetAPIConnectionString());
            SDSPurchaseUnit2.ConnectionString = DataService.APIConnectionString(Service.UserService.GetAPIConnectionString());
            SDSPurchaseUnit3.ConnectionString = DataService.APIConnectionString(Service.UserService.GetAPIConnectionString());
            SDSPurchaseUnit4.ConnectionString = DataService.APIConnectionString(Service.UserService.GetAPIConnectionString());
            SDSOnlineSale.ConnectionString = DataService.APIConnectionString(Service.UserService.GetAPIConnectionString());
            SDSCounterSale.ConnectionString = DataService.APIConnectionString(Service.UserService.GetAPIConnectionString());
            SDSDistSale.ConnectionString = DataService.APIConnectionString(Service.UserService.GetAPIConnectionString());
            SDSStokistSale.ConnectionString = DataService.APIConnectionString(Service.UserService.GetAPIConnectionString());
            lbNumbers2.Visible = false;
            txtnumbers2.Visible = false;
            lbContains2.Visible = false;
            txtcontains2.Visible = false;
            lbPurchaseUnit2.Visible = false;
            selPurchaseUnit2.Visible = false;
            lbNumbers3.Visible = false;
            txtnumbers3.Visible = false;
            lbContains3.Visible = false;
            txtcontains3.Visible = false;
            lbPurchaseUnit3.Visible = false;
            selPurchaseUnit3.Visible = false;
            lbNumbers4.Visible = false;
            txtnumbers4.Visible = false;
            lbContains4.Visible = false;
            txtcontains4.Visible = false;
            lbPurchaseUnit4.Visible = false;
            selPurchaseUnit4.Visible = false;
            if (!IsPostBack)
            {

                LoadStoreInfo();
            }
        }

        private void LoadStoreInfo()
        {
            //int boy_id = Convert.ToInt32(Request.QueryString["id"]);
            //if (boy_id > 0)
            //{
            //    DataTable dt = DataServiceMySql.GetDataTable($"SELECT d_Name, l_Name, d_Add1, d_Add2, d_Add3, employee_type, emp_id, emp_ni_number, " +
            //        $"emp_email_id, d_Ph1, d_dob, d_licence, d_licenceexpairy, d_DeliveryRange, d_isallowManualSchedule, d_isallowAutoSchedule FROM qugeo_driver WHERE d_ID = {boy_id}", Service.UserService.GetAPIConnectionString());
            //    if (dt != null && dt.Rows.Count > 0)
            //    {
            //        DataRow da = dt.Rows[0];
            //        txtFirstName.Text = da["d_Name"].ToString();
            //        txtLastName.Text = da["l_Name"].ToString();
            //        txtAddress1.Text = da["d_Add1"].ToString();
            //        txtAddress2.Text = da["d_Add2"].ToString();
            //        txtPostCode.Text = da["d_Add3"].ToString();
            //        DropDownList1.SelectedItem.Text = da["employee_type"].ToString();
            //        txtEmpID.Text = da["emp_id"].ToString();
            //        txtEmpNINumber.Text = da["emp_ni_number"].ToString();
            //        txtEmailID.Text = da["emp_email_id"].ToString();
            //        txtPhone.Text = da["d_Ph1"].ToString();
            //        //txtDOB.Text = DateTime.Now.ToString("yyyy-MM-dd");
            //        //string dz = da["d_dob"].ToString();
            //        //DateTime di = Convert.ToDateTime(dz);
            //        //txtDOB.Text = Convert.ToDateTime(di,);
            //        //DateTime di = ((DateTime)da["d_dob"]);
            //        //txtDOB.Text = string.Format((string)da["d_dob"]);
            //        txtDOB.Text = DateTime.Now.ToString("yyyy-MM-dd");
            //        //txtDOB.Text = Convert.ToDateTime(dz);
            //        txtLicense.Text = da["d_licence"].ToString();
            //        txtLicenseValidity.Text = DateTime.Now.ToString("yyyy-MM-dd");
            //        txtCoverageKM.Text = da["d_DeliveryRange"].ToString();
            //        chkManualSchedule.Checked = true;
            //        chkAutoSchedule.Checked = true;
            //    }
            //}
        }

        protected void btnAdd_Click(object sender, EventArgs e)
        {
           
            

        }
       

    

        protected void delete1_Click(object sender, EventArgs e)
        {
            lbNumbers2.Visible = false;
            txtnumbers2.Visible = false;
            lbContains2.Visible = false;
            txtcontains2.Visible = false;
            lbPurchaseUnit2.Visible = false;
            //DropDownList9.Visible = false;
            delete1.Visible = false;
        }
        protected void delete2_Click(object sender, EventArgs e)
        {
            lbNumbers3.Visible = false;
            txtnumbers3.Visible = false;
            lbContains3.Visible = false;
            txtcontains3.Visible = false;
            lbPurchaseUnit3.Visible = false;
            //DropDownList10.Visible = false;
            delete2.Visible = false;
        }

        protected void delete3_Click(object sender, EventArgs e)
        {
            lbNumbers4.Visible = false;
            txtnumbers4.Visible = false;
            lbContains4.Visible = false;
            txtcontains4.Visible = false;
            lbPurchaseUnit4.Visible = false;
            //DropDownList11.Visible = false;
            delete3.Visible = false;
        }
        protected void btnSubmit_Click(object sender, EventArgs e)
        {
            //string dobdate = txtDOB.Text;
            ////DateTime dobdate2 = Convert.ToDateTime(dobdate);

            ////int dob1 = Convert.ToInt32(dobdate2);
            ////DateTime dob2 = DateTime.Now;
            ////int dob3 = Convert.ToInt32(dob2);

            ////int difference1 = (int)(dob1 - dob3) / (60 * 60 * 24);
            ////int difference2 = ((int)(difference1 / 30) / 20);
            ////if (difference2 < 18)
            ////{
            ////    ScriptManager.RegisterClientScriptBlock(this, this.GetType(), "alertMessage", "alert('Please check Date of Birth')", true);
            ////}

            //string licensedate = txtLicenseValidity.Text;

            //int storegroupid = this.CurrentUser.APIStoreId;
            //int manulschedule = new int();
            //if (chkManualSchedule.Checked)
            //{
            //    manulschedule = 1;
            //}
            //string checkmanualschedule = Convert.ToString(manulschedule);
            //int autoschedule = new int();
            //if (chkAutoSchedule.Checked)
            //{
            //    autoschedule = 1;
            //}
            //string checkautoschedule = Convert.ToString(autoschedule);

            //var dtBranches = DataServiceMySql.GetDataTable($"SELECT br_ID, br_Lat, br_Lng FROM finascop_branch WHERE br_storeGroup = {storegroupid}", Service.UserService.GetAPIConnectionString());
            //foreach (DataRow dr in dtBranches.Rows)
            //{
            //    string brId = dr["br_ID"].ToString();
            //    string brLat = dr["br_Lat"].ToString();
            //    string brLng = dr["br_Lng"].ToString();


            //    var qry = DataServiceMySql.GetDataTable($"SELECT coalesce(max( d_ID ),0)+1 AS dID FROM qugeo_driver", Service.UserService.GetAPIConnectionString());
            //    foreach (DataRow da in qry.Rows)
            //    {
            //        string d_ID = da["dID"].ToString();
            //        int id = Convert.ToInt32(Request.QueryString["id"]);
            //        if (id == 0)

            //        {
            //            string strSql = $"INSERT INTO qugeo_driver(d_ID, d_Name, l_Name, d_Add1, d_Add2, d_Add3, employee_type, emp_id, emp_ni_number, " +
            //            $"emp_email_id, d_Ph1, d_dob, d_licence, d_licenceexpairy, br_id, d_HomeLati, d_HomeLong, d_DeliveryRange, d_isallowManualSchedule, d_isallowAutoSchedule) " +
            //            $"VALUES(" + d_ID + " ,'" + txtFirstName.Text + "','" + txtLastName.Text + "','" + txtAddress1.Text + "','" + txtAddress2.Text + "','" + txtPostCode.Text + "'," +
            //            "'" + DropDownList1.SelectedItem.Text + "','" + txtEmpID.Text + "','" + txtEmpNINumber.Text + "','" + txtEmailID.Text + "'," +
            //            "'" + txtPhone.Text + "','" + dobdate + "','" + txtLicense.Text + "','" + licensedate + "'," +
            //            "'" + brId + "','" + brLat + "','" + brLng + "','" + txtCoverageKM.Text + "','" + checkmanualschedule + "','" + checkautoschedule + "')";
            //            DataServiceMySql.ExecuteSql(strSql, Service.UserService.GetAPIConnectionString());
            //            Response.Write("<script>alert('Delivery boys details saved successfully')</script>");
            //            //Page.ClientScript.RegisterClientScriptBlock(typeof(string), "Delivery boys details saved successfully",
            //            //@"<script language='javascript'>$(document).ready(function () {showSuccess('Delivery boys details saved successfully'); window.location.href='/DeliveryBoys'; }); </script>");
            //        }
            //        else
            //        {
            //            string strUpdateSql = $"UPDATE qugeo_driver SET d_Name ='" + txtFirstName.Text + "', l_Name ='" + txtLastName.Text + "', " +
            //            $"d_Add1 ='" + txtAddress1.Text + "', d_Add2 ='" + txtAddress2.Text + "', d_Add3 ='" + txtPostCode.Text + "', " +
            //            $"employee_type ='" + DropDownList1.SelectedItem.Text + "', emp_id ='" + txtEmpID.Text + "', emp_ni_number ='" + txtEmpNINumber.Text + "', " +
            //            $"emp_email_id ='" + txtEmailID.Text + "', d_Ph1='" + txtPhone.Text + "', d_licence='" + txtLicense.Text + "', d_licenceexpairy='" + licensedate + "' , " +
            //            "br_id='" + brId + "' , d_HomeLati='" + brLat + "', d_HomeLong='" + brLng + "', d_DeliveryRange='" + txtCoverageKM.Text + "' , d_isallowManualSchedule ='" + checkmanualschedule + "' , d_isallowAutoSchedule ='" + checkautoschedule + "' WHERE d_ID = '" + id + "'";
            //            DataServiceMySql.ExecuteSql(strUpdateSql, Service.UserService.GetAPIConnectionString());
            //            Response.Write("<script>alert('Delivery boys details updated successfully')</script>");
            //            Response.Redirect("~/DeliveryBoys");
            //            //Page.ClientScript.RegisterClientScriptBlock(typeof(string), "Order Picker details updated successfully",
            //            //@"<script language='javascript'>$(document).ready(function () {showSuccess('Order Picker details updated successfully'); window.location.href='/DeliveryBoys'; }); </script>");
            //        }
            //    }
            //}
        }
    }
}



