using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
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
    public partial class ProductMasterSettings: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            //SDSBranches.ConnectionString = DataService.APIConnectionString(Service.UserService.GetAPIConnectionString());
            if (chkGrpProducts.Checked == true)
            {
                lbDisplayName.Visible = true;
                txtDisplayName.Visible = true;
            }
            else
            {
                lbDisplayName.Visible = false;
                txtDisplayName.Visible = false;
            }

            if (!IsPostBack)
            {

                LoadStoreInfo();
            }
        }

        private void LoadStoreInfo()
        {
            int itemId = Convert.ToInt32(Request.QueryString["id"]);
            if (itemId > 0)
            {
                DataTable datatable = DataServiceMySql.GetDataTable($"SELECT itemname_id,item_name,isItemGroup,itemDisplayName," +
                    $"iteamGroupImage,IF((status=1),'Active','Inactive')AS status FROM finascop_stock_itemmastername WHERE itemname_id = {itemId}", Service.UserService.GetAPIConnectionString());
                if (datatable != null && datatable.Rows.Count > 0)
                {
                    DataRow da = datatable.Rows[0];
                    txtName.Text = da["item_name"].ToString();
                    txtDisplayName.Text = da["itemDisplayName"].ToString();
                    DropDownList1.SelectedItem.Text = da["status"].ToString();
                    string groupItem = datatable.Rows[0]["isItemGroup"].ToString();
                    int itemGroup = Convert.ToInt32(groupItem);
                    if (itemGroup > 0)
                    {
                        chkGrpProducts.Checked = true;
                        lbDisplayName.Visible = true;
                        txtDisplayName.Visible = true;
                    }
                    else
                    {
                        chkGrpProducts.Checked = false;
                        lbDisplayName.Visible = false;
                        txtDisplayName.Visible = false;
                    }
                }
            }
        }
        protected void btnAdd_Click(object sender, EventArgs e)
        {
            string createdOn = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
            string updatedOn = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
            //string itemName = txtName.Text;
            //string displayName = txtDisplayName.Text;
            int storegroupid = this.CurrentUser.APIStoreId;
            int status = 0;
            if (DropDownList1.SelectedItem.Text == "Active")
            {
                status = 1;
            }
            else
            {
                status = 0;
            }
            int groupProducts = new int();
            if (chkGrpProducts.Checked)
            {
                groupProducts = 1;
            }
            string checkgroupProducts = Convert.ToString(groupProducts);
            int id = Convert.ToInt32(Request.QueryString["id"]);
            if (id == 0)
            {
                string strSql = $"INSERT INTO finascop_stock_itemmastername(item_name, isItemGroup, itemDisplayName, status, created_on) VALUES('" + txtName.Text + "' ,'" + checkgroupProducts + "','" + txtDisplayName.Text + "','" + status + "', '" + createdOn + "')";
                DataServiceMySql.ExecuteSql(strSql, Service.UserService.GetAPIConnectionString());
                Response.Write("<script>alert('Product master details saved successfully')</script>");
                //Page.ClientScript.RegisterClientScriptBlock(typeof(string), "Delivery boys details saved successfully",
                //@"<script language='javascript'>$(document).ready(function () {showSuccess('Delivery boys details saved successfully'); window.location.href='/DeliveryBoys'; }); </script>");
            }
            else
            {
                string strUpdateSql = $"UPDATE finascop_stock_itemmastername SET item_name ='" + txtName.Text + "', isItemGroup ='" + checkgroupProducts + "', " +
                "itemDisplayName ='" + txtDisplayName.Text + "', status ='" + status + "', updated_on = '" + updatedOn + "' WHERE itemname_id = '" + id + "'";
                DataServiceMySql.ExecuteSql(strUpdateSql, Service.UserService.GetAPIConnectionString());
                Response.Write("<script>alert('Product master details updated successfully')</script>");
                Response.Redirect("~/ProductMaster");
                //Page.ClientScript.RegisterClientScriptBlock(typeof(string), "Order Picker details updated successfully",
                //@"<script language='javascript'>$(document).ready(function () {showSuccess('Order Picker details updated successfully'); window.location.href='/DeliveryBoys'; }); </script>");
            }
        }
        protected void btnVerify_Click(object sender, EventArgs e)
        {
            int id = Convert.ToInt32(Request.QueryString["id"]);
            var isVerify = DataServiceMySql.GetDataTable($"SELECT isVerified FROM finascop_stock_itemmastername WHERE itemname_id = {id}", UserService.GetAPIConnectionString());
            string verified = isVerify.Rows[0]["isVerified"].ToString();
            int verify = Convert.ToInt32(verified);
            if (id != 0 && verify == 0)
            {
                string updateQry = $"UPDATE finascop_stock_itemmastername SET isVerified =1 WHERE itemname_id = '" + id + "'";
                DataServiceMySql.ExecuteSql(updateQry, Service.UserService.GetAPIConnectionString());
                Response.Write("<script>alert('Data verified successfully')</script>");
            }
            else
            {
                Response.Write("<script>alert('Data is already verified..')</script>");
            }
        }
    }
}




