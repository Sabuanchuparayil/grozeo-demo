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
    public partial class DeliverySlot : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            int storegroupid = this.CurrentUser.APIStoreId;
            var dtBranches = DataServiceMySql.GetDataTable($"SELECT br_ID,br_name FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
            DataRow dr = dtBranches.Rows[0];
            string branchName = dr["br_name"].ToString();

            var btStoreGrp = DataServiceMySql.GetDataTable($"SELECT COUNT(br_storeGroup) AS cnt FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
            DataRow dc = btStoreGrp.Rows[0];
            string storeCount = dc["cnt"].ToString();
            if (Convert.ToInt32(storeCount) == 1)
            {
                branchname.Visible = true;
                branchname.Value = dr["br_name"].ToString();
            }
            else
            {
                branchname.Visible = false;
            }

            if (!Page.IsPostBack)
            {
                BindTime();
            }

        }
        protected void Page_PreRender(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                int storegroupid = this.CurrentUser.APIStoreId;
                var dtBranches = DataServiceMySql.GetDataTable($"SELECT br_ID,br_name FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
                DataRow dr = dtBranches.Rows[0];
                string branchName = dr["br_name"].ToString();

                var btStoreGrp = DataServiceMySql.GetDataTable($"SELECT COUNT(br_storeGroup) AS cnt FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
                DataRow dc = btStoreGrp.Rows[0];
                string storeCount = dc["cnt"].ToString();
                if (Convert.ToInt32(storeCount) == 1)
                {
                    LoadStoreInfo();
                }

                //if (selBranches.Items.Count < 1)
                //    selBranches.DataBind();
                else
                {
                    //selBranches.Visible = true;
                    //selBranches.SelectedIndex = 1;
                    //string brid = selBranches.Text;
                    selBranches.DataBind();

                    LoadStoreInfo();
                }
            }
        }

        private void LoadStoreInfo()
        {
            int storegroupid = this.CurrentUser.APIStoreId;
            var btStoreGrp = DataServiceMySql.GetDataTable($"SELECT br_ID, COUNT(br_storeGroup) AS cnt FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
            DataRow dc = btStoreGrp.Rows[0];
            string storeGroup = dc["cnt"].ToString();
            if (Convert.ToInt32(storeGroup) == 1)
            {
                List<KeyValuePair<string, object>> scheduleparams = new List<KeyValuePair<string, object>>();
                scheduleparams.Add(new KeyValuePair<string, object>("branchId", dc["br_ID"].ToString()));
                DataTable dtSchedulePack = DataServiceMySql.GetDataTable($"SELECT br_schedulePackiing FROM finascop_branch WHERE br_ID = @branchId", Service.UserService.GetAPIConnectionString(), scheduleparams);
                DataRow db = dtSchedulePack.Rows[0];
                txtTime.Text = db["br_schedulePackiing"].ToString();
            }

            else
            {

                List<KeyValuePair<string, object>> ptparams = new List<KeyValuePair<string, object>>();
                ptparams.Add(new KeyValuePair<string, object>("branchId", selBranches.Text));
                DataTable dt = DataServiceMySql.GetDataTable($"SELECT br_schedulePackiing FROM finascop_branch WHERE br_ID = @branchId", Service.UserService.GetAPIConnectionString(), ptparams);
                DataRow da = dt.Rows[0];

                txtTime.Text = da["br_schedulePackiing"].ToString();
            }

        }

        private void BindTime()
        {
            // Set the start time (00:00 means 12:00 AM)
            DateTime StartTime = DateTime.ParseExact("00:00", "HH:mm", null);
            // Set the end time (23:55 means 11:55 PM)
            DateTime EndTime = DateTime.ParseExact("23:55", "HH:mm", null);
            //Set 1 hour interval
            TimeSpan Interval = new TimeSpan(1, 0, 0);
            ddlTimeFrom.Items.Clear();
            ddlTimeTo.Items.Clear();
            while (StartTime <= EndTime)
            {
                ddlTimeFrom.Items.Add(new ListItem(StartTime.ToShortTimeString(), StartTime.ToString("HH:mm:ss")));
                ddlTimeTo.Items.Add(new ListItem(StartTime.ToShortTimeString(), StartTime.ToString("HH:mm:ss")));
                StartTime = StartTime.Add(Interval);
            }
            ddlTimeFrom.Items.Insert(0, new ListItem("--Select--", ""));
            ddlTimeTo.Items.Insert(0, new ListItem("--Select--", ""));
        }

        protected void gvDelivSlot_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvDelivSlot.PageIndex * gvDelivSlot.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvDelivSlot.Rows.Count - 1;


            var dv = (DataView)SDSDelivSlot.Select(DataSourceSelectArguments.Empty);
        }

        protected void SDSDelivSlot_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
            if (selBranches.Items.Count < 1)
                selBranches.DataBind();
            if (Page.User.IsInRole("BranchManager"))
            {
                int brid = UserService.UserRoleBranchId;
                e.Command.Parameters["branchId"].Value = brid;
            }
            else
            {
                e.Command.Parameters["branchId"].Value = selBranches.Text;
            }
        }

        protected void selBranches_SelectedIndexChanged(object sender, EventArgs e)
        {
            gvDelivSlot.PageIndex = 0;
            gvDelivSlot.DataBind();
            ltrBranchName.Text = (selBranches.SelectedIndex >= 0 ? selBranches.SelectedItem.Text : "");
            LoadStoreInfo();
        }

        protected void selBranches_DataBound(object sender, EventArgs e)
        {

            //if (selBranches.Items.Count < 1)
            //{
            //    selBranches.DataBind();
            //}
            plcSelectBranchModel.Visible = selBranches.Items.Count > 1;
            ltrBranchName.Text = (selBranches.SelectedIndex >= 0 ? selBranches.SelectedItem.Text : "");

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
        public string FormatToShotTime(object objTime)
        {
            string formatedcontent = "";
            if (objTime != null && !String.IsNullOrEmpty(objTime.ToString()))
            {
                try
                {
                    DateTime strtTime = DateTime.ParseExact(objTime.ToString(), "HH:mm:ss", null);
                    formatedcontent = strtTime.ToShortTimeString();
                }
                catch { }
            }

            return formatedcontent;
        }
        protected void btnAdd_Click(object sender, EventArgs e)
        {
            List<KeyValuePair<string, object>> slparams = new List<KeyValuePair<string, object>>();
            slparams.Add(new KeyValuePair<string, object>("branchId", selBranches.Text));
            slparams.Add(new KeyValuePair<string, object>("fromTime", ddlTimeFrom.Text));
            slparams.Add(new KeyValuePair<string, object>("toTime", ddlTimeTo.Text));
            slparams.Add(new KeyValuePair<string, object>("slot", txtSlot.Text));
            string strSql = $"INSERT INTO retaline_branch_delivery_slot(branch_id, rbds_time_from, rbds_time_to, rbds_time_maxslot) " +
                $"VALUES(@branchId, @fromTime, @toTime, @slot)";
            DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), slparams);
            // Activitylog
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            string Source = strUrl;
            int storegroupid = this.CurrentUser.APIStoreId;
            string Users = this.CurrentUser.Email;
            string BranchId = selBranches.Text;
            string fromTime = ddlTimeFrom.Text;
            string toTime = ddlTimeTo.Text;
            var items = new[]
                {
                    new { Key = "BranchId", Value = BranchId },
                    new { Key = " FromTime", Value = fromTime },
                    new { Key = "To Time", Value = toTime },                   
                    };
            string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
            var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);
            //ShowSuccess("Success!", "Delivery slot created successfully", "/DeliverySlot");
            SDSDelivSlot.Select(DataSourceSelectArguments.Empty);
            gvDelivSlot.DataBind();
            txtSlot.Text = "";
            ddlTimeFrom.ClearSelection();
            ddlTimeTo.ClearSelection();

            Common.ShowToastifyMessage(this.Page, "Delivery slot created successfully");

        }


        protected void DeleteItem_Click(object sender, EventArgs e)
        {
            LinkButton delSlot = (LinkButton)sender;
            if (delSlot == null)
                return;


            List<KeyValuePair<string, object>> sqlParams = new List<KeyValuePair<string, object>>();
            sqlParams.Add(new KeyValuePair<string, object>("slotId", delSlot.Attributes["slotId"]));
            DataTable dtSlotCount = DataServiceMySql.GetDataTable($"SELECT COUNT(order_slot_id) AS slotCount FROM retaline_customer_order WHERE order_slot_id=@slotId AND status_id >= 4", Service.UserService.GetAPIConnectionString(), sqlParams);
            int slotCount = 0;
            if (dtSlotCount != null && dtSlotCount.Rows.Count > 0)
            {
                DataRow dr = dtSlotCount.Rows[0];
                slotCount = Convert.ToInt32(dr["slotCount"]);
            }
            if (slotCount > 0)
            {
                Common.ShowToastifyMessage(this.Page, "You are not allowed to delete this slot", "danger");
            }
            else
            {
                string strSql = $"DELETE FROM retaline_branch_delivery_slot WHERE rbds_id= @slotId";
                int result = DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), sqlParams);
                // Activitylog
                String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                string Source = strUrl;
                int storegroupid = this.CurrentUser.APIStoreId;
                string Users = this.CurrentUser.Email;
                string BranchId = selBranches.Text;
                string slotId = delSlot.Attributes["slotId"];               
                var items = new[]
                    {
                    new { Key = "BranchId", Value = BranchId },
                    new { Key = "SlotId", Value = slotId },                   
                    };
                string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);
                //ShowSuccess("Deleted Successfully!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Time Slot has been deleted successfully!</a></h5>");

                Common.ShowToastifyMessage(this.Page, "Time Slot has been deleted successfully!");
                SDSDelivSlot.Select(DataSourceSelectArguments.Empty);
                gvDelivSlot.DataBind();
                ddlTimeFrom.ClearSelection();
                ddlTimeTo.ClearSelection();
            }
        }

        protected void btnSave_Click(object sender, EventArgs e)
        {
            string packBeforeTime = txtTime.Text;
            List<KeyValuePair<string, object>> packparams = new List<KeyValuePair<string, object>>();
            packparams.Add(new KeyValuePair<string, object>("branchId", selBranches.Text));
            packparams.Add(new KeyValuePair<string, object>("packingSchedule", packBeforeTime));
            string updateQry = "UPDATE finascop_branch SET br_schedulePackiing=@packingSchedule WHERE br_ID=@branchId";
            DataServiceMySql.ExecuteSql(updateQry, Service.UserService.GetAPIConnectionString(), packparams);
            // Activitylog
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            string Source = strUrl;
            int storegroupid = this.CurrentUser.APIStoreId;
            string Users = this.CurrentUser.Email;
            string BranchId = selBranches.Text;
            string packingSchedule = packBeforeTime;          
            var items = new[]
                {
                    new { Key = "BranchId", Value = BranchId },
                    new { Key = "PackingSchedule", Value = packingSchedule },                  
                    };
            string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
            var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);
            Common.ShowCustomAlert(this.Page, "Data updated!", "Packing hour config updated.!", true, "/Tenant/DeliverySlot");
        }

        //private void ShowSuccess(string title, string content)
        //{
        //    ltrErrorPopupTitle.Text = title;
        //    ltrErrorPopupText.Text = content;
        //    Type cstype = this.GetType();
        //    String csname1 = "PopupScript";
        //    ClientScriptManager cs = Page.ClientScript;
        //    ltrSuccessTitle.Text = title;
        //    ltrSuccessContent.Text = content;

        //    StringBuilder cstext1 = new StringBuilder();
        //    cstext1.Append("<script type=text/javascript> $('#modaldemo4').modal('show'); </");
        //    cstext1.Append("script>");

        //    cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        //}
    }
}