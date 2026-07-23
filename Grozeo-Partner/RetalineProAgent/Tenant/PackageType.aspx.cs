using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Xml.Linq;

namespace RetalineProAgent
{
    public partial class PackageType : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
                //if(selStore.SelectedIndex == 0 || selStore.SelectedIndex == 1)
                //{
                //    dvBranch.Visible = false;
                //}
                //else if(selStore.SelectedIndex == 2)
                //{
                //    dvBranch.Visible = true;
                //}
        }

        protected void gvPackageType_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvPackageType.PageIndex * gvPackageType.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvPackageType.Rows.Count - 1;


            var dv = (DataView)SDSPackageTypes.Select(DataSourceSelectArguments.Empty);
        }

        protected void selBranches_SelectedIndexChanged(object sender, EventArgs e)
        {
            gvPackageType.PageIndex = 0;
            gvPackageType.DataBind();
            //ltrBranchName.Text = (selBranches.SelectedIndex >= 0 ? selBranches.SelectedItem.Text : "");
        }

        //protected void selBranches_DataBound(object sender, EventArgs e)
        //{
        //    if (selBranches.Items.Count > 1)
        //        selBranches.Items.Insert(0, new ListItem("Select Branch", ""));
        //    //plcSelectBranchModel.Visible = selBranches.Items.Count > 1;
        //    //ltrBranchName.Text = (selBranches.SelectedIndex >= 0 ? selBranches.SelectedItem.Text : "");
        //}
        protected void SDSBranches_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
            if (Page.User.IsInRole("BranchManager"))
            {
                int brid = UserService.UserRoleBranchId;
                e.Command.Parameters["branchid"].Value = brid;
            }
        }

        protected void SDSPackageTypes_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
            //if (selBranches.Items.Count < 1)
            //    selBranches.DataBind();
            //if (Page.User.IsInRole("BranchManager"))
            //{
            //    int brid = UserService.UserRoleBranchId;
            //    e.Command.Parameters["branchid"].Value = brid;
            //}
            //else
            //{
            //    e.Command.Parameters["branchid"].Value = selBranches.Text;
            //}
        }

        protected void lbtnAddPackageType_Click(object sender, EventArgs e)
        {
            string sql = @"SELECT count(*) as storecount, br_ID, br_Name, br_City, br_Address FROM finascop_branch WHERE br_storeGroup=" + 
                this.CurrentUser.APIStoreId;
            DataTable dtBTypes = DataServiceMySql.GetDataTable(sql, UserService.GetAPIConnectionString());

            List<KeyValuePair<string, object>> packageparams = new List<KeyValuePair<string, object>>();
            packageparams.Add(new KeyValuePair<string, object>("package", txtPackage.Text));
            packageparams.Add(new KeyValuePair<string, object>("type", selType.SelectedValue));
            packageparams.Add(new KeyValuePair<string, object>("length", txtLength.Text));
            packageparams.Add(new KeyValuePair<string, object>("breadth", txtbreadth.Text));
            packageparams.Add(new KeyValuePair<string, object>("height", txtHeight.Text));
            packageparams.Add(new KeyValuePair<string, object>("allstores", this.CurrentUser.APIStoreId));

            //if(dvBranch.Visible == true)
            //{
                List<int> blist = new List<int>();
                if (dtBTypes != null && dtBTypes.Rows.Count > 0)
                    blist = dtBTypes.AsEnumerable().Select(r => (int)r["br_ID"]).ToList();
                string selectedBranches = "";
            int storeCount = Convert.ToInt32(dtBTypes.Rows[0]["storecount"]);
            List<int> storeBranches = new List<int>();
                foreach (ListItem item in lstBranches.Items)
                {
                    if (item.Selected)
                    {
                        try
                        {
                            int storeBranch = Convert.ToInt32(item.Value);
                            selectedBranches += (String.IsNullOrWhiteSpace(selectedBranches) ? "" : ",") + item.Value;
                            storeBranches.Add(storeBranch);
                        }
                        catch { }
                    }
                }
                if (storeBranches.Count < storeCount)
                {
                    packageparams.Add(new KeyValuePair<string, object>("branch", selectedBranches));
                }
                else
                {
                    packageparams.Add(new KeyValuePair<string, object>("branch", 0));
                }
            
            DataTable dtpackage = DataServiceMySql.GetDataTable($"SELECT COUNT(*) AS cnt FROM retaline_package_master WHERE rpckm_name = @package AND rpckm_type = @type AND rpckm_length = @length AND  rpckm_breadth = @breadth and  rpckm_height = @height", Service.UserService.GetAPIConnectionString(), packageparams);
                DataRow dr = dtpackage.Rows[0];
                if (Convert.ToInt32(dr["cnt"]) > 0)
                {
                    ShowFailure("Error", "Failure with error: " + "Duplicate package type.");

                }
            else
            {
                string strSql = $"INSERT INTO retaline_package_master(rpckm_name, rpckm_type, rpckm_length, rpckm_breadth, rpckm_height, store_group_id, branchId) " +
                            $"VALUES(@package, @type, @length, @breadth, @height, @allstores, @branch)";
                DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), packageparams);
                //// Activitylog
                //String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                //String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                //string Source = strUrl;
                //int storegroupid = this.CurrentUser.APIStoreId;
                //string Users = this.CurrentUser.Email;
                //string BranchId = selBranches.Text;
                //string name = txtPackage.Text;
                //string type = selType.Text;
                //string length = txtLength.Text;
                //string breadth = txtbreadth.Text;
                //string height = txtHeight.Text;
                //var items = new[]
                //    {
                //    new { Key = "BranchId", Value = BranchId },
                //    new { Key = " FromTime", Value = fromTime },
                //    new { Key = "To Time", Value = toTime },
                //    };
                //string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                //var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);
                //ShowSuccess("Success!", "Delivery slot created successfully", "/DeliverySlot");
                SDSPackageTypes.Select(DataSourceSelectArguments.Empty);
                gvPackageType.DataBind();
                txtPackage.Text = "";
                txtLength.Text = "";
                txtbreadth.Text = "";
                txtHeight.Text = "";
                selType.ClearSelection();
                //selStore.ClearSelection();
                lstBranches.ClearSelection();
                //dvBranch.Visible = false;
                Common.ShowToastifyMessage(this.Page, "Package type created successfully");
            }
        }

        private void ShowFailure(string title, string content)
        {
            ltrErrorPopupTitle.Text = title;
            ltrErrorPopupText.Text = content;
            Type cstype = this.GetType();
            String csname1 = "PopupScript";
            ClientScriptManager cs = Page.ClientScript;


            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append("<script type=text/javascript> $('#modaldemo5').modal('show'); </");
            cstext1.Append("script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());

        }
    }
}