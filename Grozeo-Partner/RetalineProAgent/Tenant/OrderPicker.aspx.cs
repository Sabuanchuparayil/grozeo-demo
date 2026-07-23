using RetalineProAgent.Service;
using RetalineProAgent.Core.BussinessModel.Store;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.Cache;

namespace RetalineProAgent
{
    public partial class OrderPicker: Base.BasePartnerPage
    {
        List<Store> _myBranches = null;
        List<Store> MyBranches
        {
            get
            {

                if (_myBranches == null)
                {
                    _myBranches = Core.Services.APIService.GetStores(this.CurrentUser.APIStoreId, false);
                }
                return _myBranches;
            }
            set { _myBranches = value; }
        }

        protected void Page_Load(object sender, EventArgs e)
        {
            int storegroupid = this.CurrentUser.APIStoreId;
            var dtBranches = DataServiceMySql.GetDataTable($"SELECT br_ID,br_name FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
            DataRow dr = dtBranches.Rows[0];
            string branchName = dr["br_name"].ToString();

            var btStoreGrp = DataServiceMySql.GetDataTable($"SELECT COUNT(br_storeGroup) AS cnt FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
            DataRow dc = btStoreGrp.Rows[0];
            string storeGroup = dc["cnt"].ToString();
            if (Convert.ToInt32(storeGroup) == 1)
            {
                branchname.Visible = true;
                branchname.Value = dr["br_name"].ToString();
            }
            else
            {
                branchname.Visible = false;
            }

        }
        //protected void SDSOnlineOrders_Selected(object sender, SqlDataSourceStatusEventArgs e)
        //{
        //    e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        //    int startRowOnPage = (gvOnlineOrders.PageIndex * gvOnlineOrders.PageSize) + 1;
        //    int lastRowOnPage = startRowOnPage + gvOnlineOrders.Rows.Count - 1;
        //    int totalRows = e.AffectedRows;

        //    ltrPageCurStart.Text = startRowOnPage.ToString();
        //    ltrPageCurTotal.Text = lastRowOnPage.ToString();
        //    ltrPageTotal.Text = totalRows.ToString();
        //}

        protected void Page_PreRender(object sender, EventArgs e)
        {
            //if (selBranches.Items.Count < 1)
            //{
            //    selBranches.DataBind();
            //}
            //if (gvProducts.HeaderRow != null)
            //    gvProducts.HeaderRow.TableSection = TableRowSection.TableHeader;
            //if (selBranches.Items.Count > 1)
            //{
            //    selBranches.Items.Insert(0, new ListItem("Select Branch", "-1"));
            //}


        }
        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvOrderPicker.PageIndex > 0)
                gvOrderPicker.PageIndex = gvOrderPicker.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvOrderPicker.PageIndex < gvOrderPicker.PageCount - 1)
                gvOrderPicker.PageIndex = gvOrderPicker.PageIndex + 1;
        }

        protected void gvOrderPicker_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvOrderPicker.PageIndex * gvOrderPicker.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvOrderPicker.Rows.Count - 1;
            //ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSOrderPicker.Select(DataSourceSelectArguments.Empty);
        }

        protected void SDSOrderPicker_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
            if (Page.User.IsInRole("BranchManager"))
            {
                int brid = UserService.UserRoleBranchId;
                e.Command.Parameters["branchid"].Value = brid;
            }

            //if (selBranches.Items.Count < 1)
            //    selBranches.DataBind();

            //e.Command.Parameters["branchId"].Value = selBranches.Text;
        }
        //protected void btnOn_Click(object sender, EventArgs e)
        //{



        //}

        //protected void btnOff_Click(object sender, EventArgs e)
        //{

        //}

        //protected void btnSelect_Click(object sender, EventArgs e)
        //{
        //    Button btn = (Button)sender;
        //    //1: red   0:green

        //    if (btn.CommandName == "1")
        //    {
        //        string strSq1l = $"UPDATE retaline_godown_boy SET STATUS=1 WHERE id = '45'";
        //        DataServiceMySql.ExecuteSql(strSq1l, UserService.GetAPIConnectionString());
        //    }
        //    else
        //    {
        //        //change green to red
        //        string strSql2 = $"UPDATE retaline_godown_boy SET STATUS=0 WHERE id = '45'";
        //        DataServiceMySql.ExecuteSql(strSql2, UserService.GetAPIConnectionString());
        //    }

        //}

        protected async void chkStatus_CheckedChanged(object sender, EventArgs e)
        {
            CheckBox chbtn = (CheckBox)sender;

            if (chbtn != null && !String.IsNullOrEmpty(chbtn.Attributes["brid"]))
            {
                int boyId = Convert.ToInt32(chbtn.Attributes["boyId"]);
                int brid = Convert.ToInt32(chbtn.Attributes["brid"]);
                int activeStaus = (chbtn.Checked ? 1 : 0);
                List<KeyValuePair<string, object>> sqlparams = new List<KeyValuePair<string, object>>();
                sqlparams.Add(new KeyValuePair<string, object>("boyId", boyId));
                sqlparams.Add(new KeyValuePair<string, object>("brid", brid));
                sqlparams.Add(new KeyValuePair<string, object>("storegroup", this.CurrentUser.APIStoreId));
                
                if (activeStaus == 1)
                {
                    string strSql = "UPDATE retaline_godown_boy SET status=1 WHERE id = @boyId AND branch_id=@brid and branch_id in(SELECT br_ID FROM `finascop_branch` WHERE br_storeGroup=@storegroup)";
                    DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), sqlparams);
                }
                else
                {
                    string strSql = "UPDATE retaline_godown_boy SET status=0 WHERE id = @boyId AND branch_id=@brid and branch_id in(SELECT br_ID FROM `finascop_branch` WHERE br_storeGroup=@storegroup)";
                    DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), sqlparams);
                }

                // Remove Redis cache entry
                var cacheService = new RedisCacheService();
                string cachekey = $"Retl.AppTenant.pendingtasks.count.{this.CurrentUser.APIStoreId}";
                await cacheService.RemoveAsync(cachekey);
            }

            gvOrderPicker.DataBind();
        }

        protected void ODSStore_Selecting(object sender, ObjectDataSourceSelectingEventArgs e)
        {
            e.InputParameters["storegroupid"] = this.CurrentUser.APIStoreId;
        }

        protected void selBranches_SelectedIndexChanged(object sender, EventArgs e)
        {
            gvOrderPicker.PageIndex = 0;
            gvOrderPicker.DataBind();
            ltrBranchName.Text = (selBranches.SelectedIndex >= 0 ? selBranches.SelectedItem.Text : "");
        }

        protected void selBranches_DataBound(object sender, EventArgs e)
        {
            //pnlSelectBranchModel.Visible = selBranches.Items.Count > 1;
            plcSelectBranchModel.Visible = selBranches.Items.Count > 1;
            ltrBranchName.Text = (selBranches.SelectedIndex >= 0 ? selBranches.SelectedItem.Text : "");
            //pnlDummyContainer.Visible = !pnlSelectBranchModel.Visible;
        }

        protected void SDSBranches_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
            if (Page.User.IsInRole("BranchManager"))
            {
                int brid = UserService.UserRoleBranchId;
                e.Command.Parameters["branchid"].Value = brid;
            }

        }

        protected void SDSOrderPicker_Selected(object sender, SqlDataSourceStatusEventArgs e)
        {
            //ltrPageTotal.Text = e.AffectedRows.ToString();
        }
    }

}


