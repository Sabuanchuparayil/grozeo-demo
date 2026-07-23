using Amazon.DynamoDBv2;
using NPOI.OpenXmlFormats.Dml.Diagram;
using NPOI.SS.Formula.Functions;
using Org.BouncyCastle.Asn1.Cmp;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Windows.Controls.Primitives;
using static System.Windows.Forms.VisualStyles.VisualStyleElement.TreeView;
using System.Windows.Input;

namespace RetalineProAgent.Tenant
{
    public partial class MerchantControls : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {

        }
        protected void chkPackageDetails_CheckedChanged(object sender, EventArgs e)
        {
            CheckBox chkBox = (CheckBox)sender;
            GridViewRow row = (GridViewRow)chkBox.NamingContainer;
            int rowIndex = row.RowIndex;
            int branchID = Convert.ToInt32(gvBranches.DataKeys[rowIndex]["br_ID"]);

            if (row != null)
            {
                CheckBox chkPackageDetails = row.FindControl("chkPackageDetails") as CheckBox;
                if (chkPackageDetails != null)
                {
                    bool isChecked = chkPackageDetails.Checked;
                    int tpValue = isChecked ? 1 : 0;

                    List<KeyValuePair<string, object>> mcprms = new List<KeyValuePair<string, object>>()
                    {
                       new KeyValuePair<string, object>("branchId", branchID),
                       new KeyValuePair<string, object>("tp_value", tpValue)
                    };

                    string strSqlUpdate = @"UPDATE branch_settings SET tp_value = @tp_value WHERE branch_id = @branchId AND tp_name = 'Collect Package Details';";
                    var dtstrSqlUpdate = DataServiceMySql.GetDataTable(strSqlUpdate, UserService.GetAPIConnectionString(), mcprms);
                    if (dtstrSqlUpdate.Rows.Count==0)
                    {
                        string strSqlInsert = @"INSERT INTO branch_settings (branch_id, tp_type, tp_name, tp_value)
                                        VALUES (@branchId, 5, 'Collect Package Details', @tp_value);";

                        DataServiceMySql.ExecuteSql(strSqlInsert, UserService.GetAPIConnectionString(), mcprms);
                    }   
                  
                }
            }
        }
        protected void SDSMerchantControl_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {

            e.Command.Parameters["storegroupId"].Value = this.CurrentUser.APIStoreId;
        }

        protected void chkInvoiceDetails_CheckedChanged(object sender, EventArgs e)
        {
            CheckBox chkBox = (CheckBox)sender;
            GridViewRow row = (GridViewRow)chkBox.NamingContainer;
            int rowIndex = row.RowIndex;
            int branchID = Convert.ToInt32(gvBranches.DataKeys[rowIndex]["br_ID"]);

            if (row != null)
            {
                CheckBox chkInvoiceDetails = row.FindControl("chkInvoiceDetails") as CheckBox;
                if (chkInvoiceDetails != null)
                {
                    bool isChecked = chkInvoiceDetails.Checked;
                    int tpValue = isChecked ? 1 : 0;

                    List<KeyValuePair<string, object>> mcprms = new List<KeyValuePair<string, object>>()
                    {
                       new KeyValuePair<string, object>("branchId", branchID),
                       new KeyValuePair<string, object>("tp_value", tpValue)
                    };

                    string strSqlUpdate = @"UPDATE branch_settings SET tp_value = @tp_value WHERE branch_id = @branchId AND tp_name = 'Collect Invoice Details';";
                    //DataServiceMySql.ExecuteSql(strSqlUpdate, UserService.GetAPIConnectionString(), mcprms);
                    int rowsAffected = DataServiceMySql.ExecuteSql(strSqlUpdate, UserService.GetAPIConnectionString(), mcprms);
                    if (rowsAffected == 0)
                    {
                        string strSqlInsert = @"INSERT INTO branch_settings (branch_id, tp_type, tp_name, tp_value)
                                        VALUES (@branchId, 5, 'Collect Invoice  Details', @tp_value);";

                        DataServiceMySql.ExecuteSql(strSqlInsert, UserService.GetAPIConnectionString(), mcprms);
                    }
                }
            }

        }
    }
}