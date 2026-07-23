using RetalineProAgent.Core.BussinessModel.Store;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class BulkImportAPI : Base.BasePartnerPage
    {
        protected bool hasDiscountSellingPrice;
        private string branchName;
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                string id = Request.QueryString["id"];
                branchName = Request.QueryString["branchName"];
                string branchAPICode = Request.QueryString["apiKey"];
                if (!string.IsNullOrEmpty(id) && !string.IsNullOrEmpty(branchName))
                {
                    lblApiBranchName.InnerText = branchName;
                    lblApiKey.InnerText = branchAPICode;
                }
                else
                {
                    Response.Write("ID, Branch Name, or Branch API Code is missing.");
                }

                var dtStoreGroup = DataServiceMySql.GetDataTable($"SELECT br_ID, br_Name, bg.store_group_grosmartMerchant FROM finascop_branch b INNER JOIN finascop_branch_group bg ON b.br_storeGroup = bg.store_group_id WHERE bg.store_group_id  = {this.CurrentUser.APIStoreId}", UserService.GetAPIConnectionString());
                int grosmartStore = 0;
                if (dtStoreGroup != null && dtStoreGroup.Rows.Count > 0)
                {
                    DataRow da = dtStoreGroup.Rows[0];
                    string grosmart = da["store_group_grosmartMerchant"].ToString();
                    grosmartStore = Convert.ToInt32(grosmart);
                    if (grosmartStore == 1)
                    {
                        hasDiscountSellingPrice = true;
                    }
                    else
                    {
                        hasDiscountSellingPrice = false;
                    }
                }
                
                hasDiscountSellingPrice = (grosmartStore == 1);
            }
        }
        protected void Page_PreRender(object sender, EventArgs e)
        {

        }

        protected void btnAction_Click(object sender, EventArgs e)
        {
            try
            {
                Button btn = (Button)sender;
                hidId.Value = btn.Attributes["fbiu_id"];
                string fbiu_id = hidId.Value;
                string branchId = btn.Attributes["branchId"];
                string datetime = btn.Attributes["dateTime"];
                string totalcount = btn.Attributes["totalcount"];
                string successcount = btn.Attributes["successcount"];
                string missedcount = btn.Attributes["failedcount"];
                string filename = btn.Attributes["filename"];
                string erpids = btn.Attributes["missingERPIds"]; // value directly from ASPX

                // Fill popup literals
                ltrDate.Text = datetime;
                ltrTtlRecords.Text = totalcount;
                ltrSuccess.Text = successcount;
                ltrFailed.Text = missedcount;
                ltrFileName.Text = !string.IsNullOrWhiteSpace(filename) ? filename : "Uploaded via API";
                branchName = Request.QueryString["branchName"];
                ltrStoreName.Text = branchName;

                bool uploadedByApi = !string.IsNullOrWhiteSpace(erpids);

                failedRecTable.Visible = !uploadedByApi;
                apiFailedRecTable.Visible = uploadedByApi;

                if (uploadedByApi)
                {
                    rptDetails.DataSourceID = null;

                    DataTable dtApiCustom = new DataTable();
                    dtApiCustom.Columns.Add("erpid");
                    dtApiCustom.Columns.Add("comment");

                    string[] idArray = erpids.Split(new[] { ',' }, StringSplitOptions.RemoveEmptyEntries);
                    if (idArray.Length > 0)
                    {
                        foreach (string id in idArray)
                        {
                            dtApiCustom.Rows.Add(id.Trim(), "Uploaded via API");
                        }
                    }
                    else
                    {
                        dtApiCustom.Rows.Add("N/A", "No failed ERP IDs found");
                    }

                    rptApiDetails.DataSource = dtApiCustom;
                    rptApiDetails.DataBind();
                }

                // Show modal popup
                string strAlertScript = "$('#ErrorDetails').modal('show');";
                strAlertScript = "$(document).ready(function () { " + strAlertScript + " });";
                ClientScript.RegisterStartupScript(this.GetType(), "ShowConfirmPopup", $"<script type='text/javascript'>{strAlertScript}</script>");
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(Page, "Failure, " + ex.Message, "danger");
            }
        }

        protected void SDSBulkImport_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            string brId = Request.QueryString["id"];
            e.Command.Parameters["branchid"].Value = brId;
        }
    }
}
