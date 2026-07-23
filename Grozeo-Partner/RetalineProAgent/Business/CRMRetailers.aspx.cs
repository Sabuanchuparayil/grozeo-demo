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
using System.Text;

namespace RetalineProAgent
{
    public partial class CRMRetailers: Base.BasePartnerPage
    {
        public int FilterType
        {
            get
            {
                if (ViewState["ORDFILTERTYPE"] == null)
                    return 0;
                else
                    return (int)ViewState["ORDFILTERTYPE"];
            }
            set
            {
                ViewState["ORDFILTERTYPE"] = value;
            }
        }

        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack && String.IsNullOrEmpty(hidFilterType.Value))
            {

                FilterType = 1; hidFilterType.Value = "1";
            }
        }

        protected void Page_PreRender(object sender, EventArgs e)
        {
            lbtnRetailer.CssClass = String.Format("btn btn-block btn-outline-primary {0}", (FilterType <= 1 ? "active" : ""));
            lbtnMechant.CssClass = String.Format("btn btn-block btn-outline-primary {0}", (FilterType == 2 ? "active" : ""));
        }

        

        protected void btnFilterType_Click(object sender, EventArgs e)
        {
            LinkButton btn = (LinkButton)sender;
            if (btn != null && !String.IsNullOrEmpty(btn.Attributes["typeid"]))
            {
                int btypeid = Convert.ToInt32(btn.Attributes["typeid"]);
                FilterType = btypeid;
                hidFilterType.Value = btypeid.ToString();

            }
        }

        protected void SDSRetailerLeads_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            var user = this.CurrentUser;
            hidFilterType.Value = FilterType.ToString();
            e.Command.Parameters["filterType"].Value = FilterType;
            if (user.AreaId > 0)
                e.Command.Parameters["areaId"].Value = user.AreaId;
        }


        protected string GetButtonToolTip(object grosmartMerchantValue)
        {
            int merchantValue = Convert.ToInt32(grosmartMerchantValue);

            return merchantValue == 1 ? "Downgrade to Retailer" : "Promote as Merchant";
        }

        protected string GetClientClickScript(object grosmartMerchantValue)
        {
            int merchantValue = Convert.ToInt32(grosmartMerchantValue);

            return merchantValue == 1
                ? "return confirm('Do you wish to Downgrade this retailer to a Retailer?');"
                : "return confirm('Do you wish to Promote this retailer to a Merchant?');";
        }


        protected void btnMerchant_Click(object sender, EventArgs e)
        {
            Button btnMerchant = (Button)sender;
            string storeId = btnMerchant.Attributes["storegroupId"];

            if (Convert.ToInt32(storeId) > 0)
            {
                List<KeyValuePair<String, Object>> updateParams = new List<KeyValuePair<string, object>>();
                updateParams.Add(new KeyValuePair<string, object>("storeGroupId", storeId));

                if (btnMerchant.Text == "Promote as Merchant")
                {
                    // Promote process
                    updateParams.Add(new KeyValuePair<string, object>("isgrosmerchant", 1));
                    string updateSql = "UPDATE finascop_branch_group SET store_group_grosmartMerchant=@isgrosmerchant where store_group_id = @storeGroupId";
                           DataServiceMySql.ExecuteSql(updateSql, Service.UserService.GetAPIConnectionString(), updateParams);
                           Common.ShowToastifyMessage(Page, "Promoted as Merchant.");
                    gvRetailers.DataBind();
                }
                else if (btnMerchant.Text == "Downgrade to Retailer")
                {
                    // Downgrade process
                    updateParams.Add(new KeyValuePair<string, object>("notgrosmerchant", 0));
                    string updateSql = "UPDATE finascop_branch_group SET store_group_grosmartMerchant=@notgrosmerchant where store_group_id = @storeGroupId";
                            DataServiceMySql.ExecuteSql(updateSql, Service.UserService.GetAPIConnectionString(), updateParams);
                            Common.ShowToastifyMessage(Page, "Downgraded to Retailer.");
                    gvRetailers.DataBind();
                }

                ToggleButtonState(btnMerchant);
            }
        }

        private void ToggleButtonState(Button btn)
        {
            if (btn.Text == "Promote")
            {
                btn.Text = "Downgrade";
                btn.ToolTip = "Downgrade Merchant";
                btn.OnClientClick = "return confirm('Do you wish to Downgrade this merchant?');";
            }
            else if (btn.Text == "Downgrade")
            {
                btn.Text = "Promote";
                btn.ToolTip = "Promote as Merchant";
                btn.OnClientClick = "return confirm('Do you wish to Promote this retailer to merchant?');";
            }
        }

		protected void DeligateStore_Click(object sender, EventArgs e)
		{
			if (!(Page.User.IsInRole("SuperAdmin") || Page.User.IsInRole("RetalineProAgent") || Page.User.IsInRole("Deligation")))
            {
                Common.ShowToastifyMessage(this.Page, "Permission denied!", "danger");
                return;
            }

            LinkButton lbtn = (LinkButton)sender;
            string selSGId = lbtn.Attributes["sgid"];
            int storegroupid = 0; try { storegroupid = Convert.ToInt32(selSGId); } catch { storegroupid = 0; }
            if(storegroupid <= 0)
            {
				Common.ShowToastifyMessage(this.Page, "Invalid store!", "danger");
				return;
			}

			List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
			prms.Add(new KeyValuePair<string, object>("sgid", storegroupid));
			prms.Add(new KeyValuePair<string, object>("userId", this.CurrentUser.Id));
			string sql = "UPDATE u SET StoreGroupId= a.Id, StoreGroupName=a.[Name] FROM [User] u JOIN (select top 1 *, @userId as usrid from AppTenant where StoreId=@sgid) a " +
                "ON u.id = a.usrid WHERE u.id = @userId";

			//if (!(Page.User.IsInRole("SuperAdmin") || Page.User.IsInRole("RetalineProAgent")))
			//	sql = String.Format("IF EXISTS(SELECT * FROM User_UserRole_Mapping WHERE StoreGroupId=@sid) BEGIN {0} END", sql);
			int result = DataService.ExecuteSql(sql, parmeters: prms);
			Service.UserService.CachedDefaultUser = null;
			User user = this.CurrentUser; //FormsAuthenticationService.GetAuthenticatedCustomer();
			if (result > 0 && user != null)
			{
				//user.StoreGroupId = storegroupid;
				//user.StoreGroupName = selSwitchStore.SelectedItem.Text; //rbtn.Text;
				Service.UserService.CachedDefaultUser = user;
				Page.Response.Redirect("/Tenant/", true);
			}


		}

        protected void gvRetailers_RowDataBound(object sender, GridViewRowEventArgs e)
        {
            if (e.Row.RowType != DataControlRowType.DataRow)
                return;

            //  data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo"
            e.Row.Attributes.Add("data-toggle", "collapse");
            e.Row.Attributes.Add("data-target", String.Format("#collapse{0}", e.Row.DataItemIndex));
            e.Row.Attributes.Add("aria-expanded", "false");
            e.Row.Attributes.Add("aria-controls", String.Format("collapse{0}", e.Row.DataItemIndex));
        }

        protected void btnPendingActions_Click(object sender, EventArgs e)
        {
            try
            {
                // Retrieve attributes from the clicked button
                Button btnMerchant = (Button)sender;
                string storeId = btnMerchant.Attributes["storegroupId"];

                var merchantData = Services.StoreService.MerchantPendingActions(0, Convert.ToInt32(storeId));
                if (merchantData != null)
                {
                    var pendingActions = merchantData.PendingActions;
                    var pendingJobs = merchantData.PendingJobs;

                    if (pendingActions != null && pendingJobs != null)
                    {
                        var combinedList = pendingActions.Concat(pendingJobs).ToList();
                        foreach (var action in pendingActions)
                        {
                            if (action.Description == "Set Language preference.")
                            {
                                action.Description = "Language preference has not been added.";
                            }
                        }

                        rptPendingActions.DataSource = combinedList;
                        rptPendingActions.DataBind();
                    }
                }
                ScriptManager.RegisterStartupScript(this, this.GetType(), "showPopup", "$('#modalStoreDetails').modal('show');", true);
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(this.Page, "An unexpected error occurred: " + ex.Message, "danger");
            }
            
        }

        protected void btnPerformanceBoard_Click(object sender, EventArgs e)
        {
            try
            {
                Button btnMerchant = (Button)sender;
                hidstoreId.Value = (btnMerchant.Attributes["storegroupId"]);
                string storegroupId = hidstoreId.Value;
                LoadSalesData(storegroupId);
                //popup Action
                string strAlertSCript = "$('#modalPerformanceBoard').modal('show');";
                strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";
                System.Type cstype = this.GetType();
                String csname1 = "ShowConfirmPopup";
                ClientScriptManager cs = this.ClientScript;
                StringBuilder cstext1 = new StringBuilder();
                cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
                cstext1.Append("script>");
                cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(this.Page, "An unexpected error occurred: " + ex.Message, "danger");
            }

        }

        private void LoadSalesData(string storegroupId)
        {
            try
            {
                List<KeyValuePair<String, Object>> prms = new List<KeyValuePair<string, object>>();
                prms.Add(new KeyValuePair<string, object>("storeGroupId", storegroupId));
                string salesquery = @"SELECT COALESCE(SUM(CASE WHEN fsto_createdOn >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END), 0) AS last_7_days,COALESCE(SUM(CASE WHEN YEARWEEK(fsto_createdOn, 1) = YEARWEEK(CURDATE(), 1) THEN 1 ELSE 0 END), 0) AS this_week,
                  COALESCE(SUM(CASE WHEN DATE_FORMAT(fsto_createdOn, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') THEN 1 ELSE 0 END), 0) AS this_month FROM finascop_stock_transfer_order s INNER JOIN finascop_branch b ON s.fsto_source = b.br_ID WHERE b.br_storegroup = @storeGroupId;";
                var tblsales = DataServiceMySql.GetDataTable(salesquery, UserService.GetAPIConnectionString(), prms);
                if (tblsales != null && tblsales.Rows.Count > 0)
                {
                    var ta = tblsales.Rows[0];
                    ltrDaysSales.Text = ta["last_7_days"].ToString();
                    ltrWeekSales.Text = ta["this_week"].ToString();
                    ltrMonthSales.Text = ta["this_month"].ToString();
                }

                string revenuequery = @"SELECT COALESCE(SUM(CASE WHEN created_at >= CURDATE() - INTERVAL 7 DAY THEN total ELSE 0 END), 0) AS last_7_days,COALESCE(SUM(CASE WHEN YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1) THEN total ELSE 0 END), 0) AS this_week,
                  COALESCE(SUM(CASE WHEN DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') THEN total ELSE 0 END), 0) AS this_month FROM retaline_customer_order WHERE storegroup_id = @storeGroupId AND status_id >= 4 AND status_id NOT IN (19, 24)";
                var tblrevenue = DataServiceMySql.GetDataTable(revenuequery, UserService.GetAPIConnectionString(), prms);
                if (tblrevenue != null && tblrevenue.Rows.Count > 0)
                {
                    var ta = tblrevenue.Rows[0];
                    ltrDaysAmt.Text = ta["last_7_days"].ToString();
                    ltrWeekAmt.Text = ta["this_week"].ToString();
                    ltrMonthAmt.Text = ta["this_month"].ToString();
                }
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(this.Page, "An unexpected error occurred: " + ex.Message, "danger");
            }
        }

        protected void lbtnSearch_Click(object sender, EventArgs e)
        {
            try
            {
                string searchCode = txtSearch1.Text.Trim();

                if (!string.IsNullOrEmpty(searchCode) && searchCode.Length >= 6)
                {
                    string storeId = ExtractStoreId(searchCode);

                    if (!string.IsNullOrEmpty(storeId))
                    {
                        List<KeyValuePair<string, object>> baparams = new List<KeyValuePair<string, object>>();
                        baparams.Add(new KeyValuePair<string, object>("StoreID", storeId));

                        // Fetch store name, prospect_Id, and created_on in a single query
                        DataTable dt = DataServiceMySql.GetDataTable(@"SELECT store_group_name, prospect_Id, created_on FROM finascop_branch_group WHERE store_group_id = @StoreID",Service.UserService.GetAPIConnectionString(),baparams);

                        if (dt != null && dt.Rows.Count > 0)
                        {
                            // Store found, fill the store name
                            txtStoreName.Text = dt.Rows[0]["store_group_name"].ToString();
                            int prospectId = dt.Rows[0]["prospect_Id"] != DBNull.Value ? Convert.ToInt32(dt.Rows[0]["prospect_Id"]) : 0;

                            if (prospectId == 0)
                            {
                                // If prospectId is 0, check the created_on date
                                DateTime createdOn = dt.Rows[0]["created_on"] != DBNull.Value ? Convert.ToDateTime(dt.Rows[0]["created_on"]) : DateTime.MinValue;
                                DateTime oneHourAgo = DateTime.Now.AddHours(-1);

                                if (createdOn > oneHourAgo)
                                {
                                    // If created_on is within 1 hour, generate the code
                                    dvCreateCode.Visible = true;
                                    GenerateAndSetCode(baparams);
                                    ScriptManager.RegisterStartupScript(this, this.GetType(), "OpenModal", "$('#modalAddRetailer').modal('show');", true);
                                }
                                else
                                {
                                    btnAdd.Enabled = false;
                                    // Creation time has expired
                                    Common.ShowToastifyMessage(this.Page, "The code cannot be generated as the creation time has expired (beyond 1 hour).", "danger");
                                    ScriptManager.RegisterStartupScript(this, this.GetType(), "OpenModal", "$('#modalAddRetailer').modal('show');", true);
                                }
                            }
                            else
                            {
                                // Prospect already exists, no need to check created_on
                                dvCreateCode.Visible = false;
                                Common.ShowToastifyMessage(this.Page, "Referrer is already there for this branch.", "danger");
                                ClearRetailerPopupFields();
                            }
                        }
                        else
                        {
                            // No store found for the provided StoreID
                            txtStoreName.Text = "";
                            dvCreateCode.Visible = false;
                            Common.ShowToastifyMessage(this.Page, "No store found for this code.", "danger");
                        }
                    }
                    else
                    {
                        // Invalid store ID extracted
                        txtStoreName.Text = "";
                        dvCreateCode.Visible = false;
                        Common.ShowToastifyMessage(this.Page, "Invalid search code format.", "danger");
                    }
                }
                else
                {
                    // Invalid or short search code
                    txtStoreName.Text = "";
                    dvCreateCode.Visible = false;
                    Common.ShowToastifyMessage(this.Page, "Invalid search code.", "danger");
                }
            }
            catch (Exception ex)
            {
                // Handle unexpected errors
                Common.ShowToastifyMessage(this.Page, "An error occurred: " + ex.Message, "danger");
            }
        }

        private void ClearRetailerPopupFields()
        {
            txtSearch1.Text = string.Empty;
            txtStoreName.Text = string.Empty;
            txtGneratedCode.Text = string.Empty;
        }

        private string ExtractStoreId(string searchCode)
        {
            if (string.IsNullOrEmpty(searchCode) || searchCode.Length < 6)
            {
                return string.Empty;
            }

            string storeId = string.Empty;
            int digitCount = 0;

            // Start from the end and collect digits
            for (int i = searchCode.Length - 1; i >= 0 && digitCount < 4; i--)
            {
                if (char.IsDigit(searchCode[i]))
                {
                    storeId = searchCode[i] + storeId;
                    digitCount++;
                }
            }

            // Remove leading zeros if storeId has more than one digit
            return storeId.TrimStart('0');
        }

        private void GenerateAndSetCode(List<KeyValuePair<string, object>> baparams)
        {
            txtGneratedCode.Visible = true;
            string randomString = GenerateRandomString(10);
            baparams.Add(new KeyValuePair<string, object>("invitationCode", randomString));
            txtGneratedCode.Text = randomString;
        }

        public string GenerateRandomString(int length = 10)
        {
            const string characters = "3479acdefghjklmnprtwxyACDEFHIJKLMNPRTWXY";
            int charactersLength = characters.Length;
            StringBuilder randomString = new StringBuilder();

            Random random = new Random();
            for (int i = 0; i < length; i++)
            {
                randomString.Append(characters[random.Next(0, charactersLength)]);
            }

            return randomString.ToString();
        }

        protected void btnRetailerSubmit_Click(object sender, EventArgs e)
        {
            try
            {
                string searchCode = txtSearch1.Text.Trim();
                if (string.IsNullOrWhiteSpace(txtStoreName.Text) && string.IsNullOrWhiteSpace(txtGneratedCode.Text))
                {
                    ScriptManager.RegisterStartupScript(this, GetType(), "ShowParentModal", "$('#modalAddRetailer').modal('show');", true);
                    Common.ShowToastifyMessage(this.Page, "Store Name cannot be empty!", "danger");
                    return;
                }

                if (dvCreateCode.Visible && string.IsNullOrWhiteSpace(txtGneratedCode.Text))
                {
                    ScriptManager.RegisterStartupScript(this, GetType(), "ShowParentModal", "$('#modalAddRetailer').modal('show');", true);
                    Common.ShowToastifyMessage(this.Page, "Invitation Code cannot be empty!", "danger");
                    return;
                }

                if (!string.IsNullOrEmpty(searchCode) && searchCode.Length >= 6)
                {
                    string storeId = ExtractStoreId(searchCode);

                    if (!string.IsNullOrEmpty(storeId))
                    {
                        List<KeyValuePair<String, Object>> prospectParams = new List<KeyValuePair<string, object>>
                        {
                            new KeyValuePair<string, object>("storeName", txtStoreName.Text),
                            new KeyValuePair<string, object>("mode", 2),
                            new KeyValuePair<string, object>("StoreID", storeId)
                        };

                        DataTable dt = DataServiceMySql.GetDataTable(@"SELECT br_Name, br_Address, br_pincode, (SELECT st_name FROM              finascop_state WHERE st_ID=br_State) AS state, 
                            br_Lat, br_Lng, br_Incharge, br_Phone, br_Email, store_group_name, 
                            br_State, br_storeGroup, store_group_grosmartMerchant, areaid,
                            (SELECT areaName FROM area_entries WHERE id=areaid) AS areaName  
                            FROM finascop_branch_group bg 
                            INNER JOIN finascop_branch b ON b.br_storeGroup = bg.store_group_id
                            WHERE br_storeGroup = @StoreID 
                            GROUP BY br_storeGroup", Service.UserService.GetAPIConnectionString(), prospectParams);

                        if (dt != null && dt.Rows.Count > 0)
                        {
                            DataRow da = dt.Rows[0];
                            prospectParams.Add(new KeyValuePair<string, object>("location", da["br_Address"].ToString()));
                            prospectParams.Add(new KeyValuePair<string, object>("pincode", da["br_pincode"].ToString()));
                            prospectParams.Add(new KeyValuePair<string, object>("place", da["state"].ToString()));
                            prospectParams.Add(new KeyValuePair<string, object>("latitude", da["br_Lat"].ToString()));
                            prospectParams.Add(new KeyValuePair<string, object>("longitude", da["br_Lng"].ToString()));
                            prospectParams.Add(new KeyValuePair<string, object>("address", da["br_Address"].ToString()));
                            prospectParams.Add(new KeyValuePair<string, object>("contactperson", da["br_Incharge"].ToString()));
                            prospectParams.Add(new KeyValuePair<string, object>("contactnumber", da["br_Phone"].ToString()));
                            prospectParams.Add(new KeyValuePair<string, object>("email", da["br_Email"].ToString()));
                            prospectParams.Add(new KeyValuePair<string, object>("areaId", da["areaid"].ToString()));
                            prospectParams.Add(new KeyValuePair<string, object>("areaname", da["areaName"].ToString()));
                            prospectParams.Add(new KeyValuePair<string, object>("invitationcode", txtGneratedCode.Text));

                            var user = this.CurrentUser;
                            List<KeyValuePair<string, object>> baparams = new List<KeyValuePair<string, object>>
                            {
                                new KeyValuePair<string, object>("email", user.Email)
                            };

                            DataTable baTable = DataServiceMySql.GetDataTable(@"SELECT ba.id, baName FROM business_associate ba WHERE baEmail = @email", UserService.GetAPIConnectionString(), baparams);

                            string baId = "", baName = "";
                            if (baTable != null && baTable.Rows.Count > 0)
                            {
                                DataRow dz = baTable.Rows[0];
                                baId = dz["id"].ToString();
                                baName = dz["baName"].ToString();
                            }
                            if (string.IsNullOrWhiteSpace(baId))
                            {
                                Common.ShowToastifyMessage(this.Page, "You don't have permission to add a retailer.", "danger");
                                ClearRetailerPopupFields();
                                return;
                            }

                            prospectParams.Add(new KeyValuePair<string, object>("baId", baId));
                            prospectParams.Add(new KeyValuePair<string, object>("baName", baName));

                            // Insert into prospect table
                            string insertSql = @"INSERT INTO finascop_crm_prospect(crpr_orgName, crpr_mode, crpr_location, crpr_orgPincode, 
                                    crpr_gplace, glatitude, glongitude, crpr_orgAddress, crpr_indContactperson, crpr_orgContactNo, 
                                    crpr_orgEmail, baId, baName, areaId, areaName, invitationCode, storeGroupId) 
                                    VALUES(@storeName, @mode, @location, @pincode, @place, @latitude, @longitude, @address, 
                                    @contactperson, @contactnumber, @email, @baId, @baName, @areaId, @areaname, @invitationcode, 
                                    @StoreID); SELECT LAST_INSERT_ID();";

                            var result = DataServiceMySql.ExecuteScalar(insertSql, Service.UserService.GetAPIConnectionString(), prospectParams);
                            int lastInsertId = Convert.ToInt32(result);

                            if (lastInsertId > 0)
                            {
                                prospectParams.Add(new KeyValuePair<string, object>("lastInsertId", lastInsertId));

                                // Update branch group with prospect ID
                                string updateSql = @"UPDATE finascop_branch_group SET prospect_Id = @lastInsertId WHERE store_group_id = @StoreID";
                                DataServiceMySql.ExecuteSql(updateSql, Service.UserService.GetAPIConnectionString(), prospectParams);
                            }

                            ShowSuccess("Success!", "<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Retailer Added Successfully!!</a></h5>");
                            ClearRetailerPopupFields();
                        }
                        else
                        {
                            Common.ShowToastifyMessage(this.Page, "No matching retailer found.", "danger");
                            ClearRetailerPopupFields();
                        }
                    }
                    else
                    {
                        Common.ShowToastifyMessage(this.Page, "Invalid search code format.", "danger");
                        ClearRetailerPopupFields();
                    }
                }
                else
                {
                    Common.ShowToastifyMessage(this.Page, "Invalid search code.", "danger");
                    ClearRetailerPopupFields();
                }
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(this.Page, "Execution failed: " + ex.Message, "danger");
            }
        }

        private void ShowSuccess(string title, string content, string redirect = "")
        {
            Type cstype = this.GetType();
            String csname1 = "PopupScript";
            ClientScriptManager cs = Page.ClientScript;
            ltrSuccessTitle.Text = title;
            ltrSuccessContent.Text = content;

            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> $('#modaldemo4').modal('show'); {(string.IsNullOrEmpty(redirect) ? "" : "$('#modaldemo4').on('hidden.bs.modal', function (e) {window.location.href = '" + redirect + "'; });")}</");
            cstext1.Append("script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }

        protected void gvRetailers_DataBound(object sender, EventArgs e)
        {
            if (gvRetailers.Rows.Count == 0 && gvRetailers.Controls.Count > 0 && gvRetailers.Controls[0].Controls.Count > 0)
            {
                Label lblEmptyMessage = gvRetailers.Controls[0].Controls[0].FindControl("lblEmptyMessage") as Label;
                if (lblEmptyMessage != null)
                {
                    lblEmptyMessage.Text = FilterType == 1 ? "No retailers to list." :
                                           FilterType == 2 ? "No merchants to list." :
                                           "No data to list.";
                }
            }
        }
    }
}


