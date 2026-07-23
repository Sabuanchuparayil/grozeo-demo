using RetalineProAgent.Core.Services;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class BranchSettings: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!String.IsNullOrEmpty(Request.QueryString["brid"]))
            {
                try
                {
                    this.Title = "Edit Brnach";
                    int branchId = Convert.ToInt32(Request.QueryString["brid"]);
                    if (branchId < 1)
                        throw new Exception("Invalid branch");

                    var stores = Core.Services.APIService.GetStores(this.CurrentUser.APIStoreId);
                    if (stores == null || !stores.Any(s => s.BranchId == branchId))
                        throw new Exception("Store not exists");

                    var store = stores.FirstOrDefault(s => s.BranchId == branchId);
                    ctrlCreateStore1.APIStore = store;
                    ctrlCreateStore1.APIBranchId = branchId;

                    string strbid = Request.QueryString["id"];
                    if (!String.IsNullOrEmpty(strbid) && strbid != "-1")
                    {
                        DataTable dt = DataService.GetDataTable($"SELECT * FROM StoreBranch WHERE Id={strbid} and APIBranchId= {branchId} AND StoreId={this.CurrentUser.StoreGroupId}");
                        if (dt != null && dt.Rows.Count > 0)
                        {
                            try
                            {
                                ctrlCreateStore1.BranchId = (int)dt.Rows[0]["Id"];
                            }
                            catch { }
                        }
                    }
                    if(!IsPostBack)
                        ctrlCreateStore1.LoadInput();

                }
                catch (Exception ex)
                {
                    this.Title = "Edit Branch - Error";
                    ctrlCreateStore1.Visible = false;
                    Page.ClientScript.RegisterClientScriptBlock(typeof(string), "InvalidBranch",
                @"<script language='javascript'>$(document).ready(function () {showError('Error. "+ex.Message.Replace(",", "")+".'); window.location.href='/Branches'; }); </script>");

                }
            }
        }
    }
}