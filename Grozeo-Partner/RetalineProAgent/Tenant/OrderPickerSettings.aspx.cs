using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Core.Services.Cache;
using RetalineProAgent.Navigations;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Net.NetworkInformation;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using static QRCoder.PayloadGenerator;

namespace RetalineProAgent
{
    public partial class OrderPickerSettings: Base.BasePartnerPage
    {

        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {

                LoadStoreInfo();
            }
            if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
            {
                txtMobileNumber.Attributes.Add("maxlength", "10");
                
            }
            //else if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
            //{
            //    txtMobileNumber.Attributes.Add("maxlength", "12");
            //}
        }

        private void LoadStoreInfo()
        {
            int id = Convert.ToInt32(Request.QueryString["id"]);
            if (id > 0)
            {
                var selectparams = new List<KeyValuePair<string, object>>();
                selectparams.Add(new KeyValuePair<string, object>("id", id));
                selectparams.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));

                DataTable dt = DataServiceMySql.GetDataTable($"SELECT bi.name, bi.lname, bi.phone, bi.emp_id, bi.emp_ni_number, bi.emp_email_id, bi.emp_add1, bi.emp_add2, bi.emp_pincode, bi.branch_id, bi.is_cpd, bi.is_offline, bi.is_allowManualSchedule,bi.is_allowAutoSchedule,bi.allowStoreClose,bi.allowInventoryControl FROM retaline_godown_boy bi INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id WHERE bi.id = @id and  b.br_storeGroup=@storegroupid", UserService.GetAPIConnectionString(), selectparams);
                if (dt != null && dt.Rows.Count > 0)
                {
                    DataRow da = dt.Rows[0];
                    txtFirstName.Text = da["name"].ToString();
                    txtLastName.Text = da["lname"].ToString();
                    string mobilenumber= da["phone"].ToString();
                    string getphone = mobilenumber.Replace((ConfigurationManager.AppSettings.Get("PhoneCountryCode")), "");
                    txtMobileNumber.Text = getphone.ToString();
                    txtEmpID.Text = da["emp_id"].ToString();
                    txtEmpNINumber.Text = da["emp_ni_number"].ToString();
                    txtEmailID.Text = da["emp_email_id"].ToString();
                    txtAddress1.Text = da["emp_add1"].ToString();
                    txtAddress2.Text = da["emp_add2"].ToString();
                    txtPostCode.Text = da["emp_pincode"].ToString();
                    bool isManualSchedule = Convert.ToBoolean(da["is_allowManualSchedule"]);
                    bool isAutoSChedule = Convert.ToBoolean(da["is_allowAutoSchedule"]);
                    chkManualSchedule.Checked = isManualSchedule;
                    chkAutoSchedule.Checked = isAutoSChedule;
                    bool storeClosed = Convert.ToBoolean(da["allowStoreClose"]);
                    bool inventoryCtrl = Convert.ToBoolean(da["allowInventoryControl"]);
                    chkStoreClose.Checked = storeClosed;
                    chkInvenCtrl.Checked = inventoryCtrl;

                    string brid = da["branch_id"].ToString();
                    if (selBranch.Items.Count < 2)
                        selBranch.DataBind();
                    if (selBranch.Items.FindByValue(brid) != null)
                        selBranch.SelectedValue = brid;

                }
                else
                {
                    Common.ShowCustomAlert(this.Page, "Error!", "Invalid Order picker!", false, "/Tenant/orderpicker");
                    return;
                }
            }
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

        protected async void btnAdd_Click(object sender, EventArgs e)
        {
            int minDigits = 0;
            if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
            {
                txtMobileNumber.Attributes.Add("maxlength", "10");
                minDigits = 10;
            }
            //else if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
            //{
            //    txtMobileNumber.Attributes.Add("maxlength", "12");
            //    minDigits = 12;
            //}


            string phoneNumber = (ConfigurationManager.AppSettings.Get("PhoneCountryCode"))+ txtMobileNumber.Text;
            if (phoneNumber.Length < minDigits && ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
            {
                lblMessage.Text = "Phone number must have at least " + minDigits + " digits.";
                lblMessage.Visible = true;
                return;
            }
            else
            {
                lblMessage.Visible = false;
            }
            int id = -1;
            if (!String.IsNullOrEmpty(Request.QueryString["id"]))
                try { id = Convert.ToInt32(Request.QueryString["id"]); } catch { }

            int storegroupid = this.CurrentUser.APIStoreId;
            string brId = selBranch.Text; //dr["br_ID"].ToString();
            if (Page.User.IsInRole("BranchManager"))
            {
                int brid = UserService.UserRoleBranchId;
                brId = brid.ToString();
            }

            if (chkAutoSchedule.Checked == false && chkManualSchedule.Checked == false)
            {
                Common.ShowToastifyMessage(this.Page, "Please select auto schedule or manual schedule", "danger");
            }

            var dtBranches = DataServiceMySql.GetDataTable($"SELECT br_ID, br_Name, br_PyramidLevel FROM finascop_branch WHERE br_ID= {brId} and br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
            if(dtBranches == null || dtBranches.Rows.Count <= 0)
            {
                Common.ShowCustomAlert(this.Page, "Loading Failure", "Invalid store or you don't have permission to access.", false, "/Tenant/orderpicker");
                return;
            }
            string cpd = dtBranches.Rows[0]["br_PyramidLevel"].ToString();
            List<KeyValuePair<string, object>> gbparams = new List<KeyValuePair<string, object>>();
            gbparams.Add(new KeyValuePair<string, object>("fname", txtFirstName.Text));
            gbparams.Add(new KeyValuePair<string, object>("lname", txtLastName.Text));
            gbparams.Add(new KeyValuePair<string, object>("phone", phoneNumber));
            gbparams.Add(new KeyValuePair<string, object>("empid", txtEmpID.Text));
            gbparams.Add(new KeyValuePair<string, object>("empniNum", txtEmpNINumber.Text));
            gbparams.Add(new KeyValuePair<string, object>("empEmail", txtEmailID.Text));
            gbparams.Add(new KeyValuePair<string, object>("addr1", txtAddress1.Text));
            gbparams.Add(new KeyValuePair<string, object>("addr2", txtAddress2.Text));
            gbparams.Add(new KeyValuePair<string, object>("pin", txtPostCode.Text));
            gbparams.Add(new KeyValuePair<string, object>("brid", brId));
            gbparams.Add(new KeyValuePair<string, object>("iscpd", cpd));
            gbparams.Add(new KeyValuePair<string, object>("isManualScheudle", (chkManualSchedule.Checked ? 1 : 0)));
            gbparams.Add(new KeyValuePair<string, object>("isAutoSChedule", (chkAutoSchedule.Checked ? 1 : 0)));
            gbparams.Add(new KeyValuePair<string, object>("storeClose", (chkStoreClose.Checked ? 1 : 0)));
            gbparams.Add(new KeyValuePair<string, object>("inventoryCtrl", (chkInvenCtrl.Checked ? 1 : 0)));
            gbparams.Add(new KeyValuePair<string, object>("isoffline",string.IsNullOrEmpty(hdnIsOffline.Value) ? 0 : Convert.ToInt32(hdnIsOffline.Value)));
            if (chkAutoSchedule.Checked == false && chkManualSchedule.Checked == false)
            {
                Common.ShowToastifyMessage(this.Page, "Please select auto schedule or manual schedule", "danger");
            }
            else
            {
                if (id <= 0)
                {
                    try
                    {
                        string strSql = $"INSERT INTO retaline_godown_boy(name, lname, phone, emp_id, emp_ni_number, emp_email_id, emp_add1, emp_add2, " +
                            $"emp_pincode, branch_id, is_cpd, is_allowManualSchedule, is_allowAutoSchedule, allowStoreClose, allowInventoryControl) " +
                            $"VALUES(@fname, @lname, @phone, @empid, @empniNum, @empEmail, @addr1, @addr2, @pin, @brid, @iscpd, @isManualScheudle, @isAutoSChedule, @storeClose, @inventoryCtrl)";
                        DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), gbparams);

                        // Remove Redis cache entry
                        var cacheService = new RedisCacheService();
                        string cachekey = $"Retl.AppTenant.pendingtasks.count.{this.CurrentUser.APIStoreId}";
                        await cacheService.RemoveAsync(cachekey);

                        // Activitylog
                        String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                        String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                        string Source = strUrl;
                        string Users = this.CurrentUser.Email;
                        string fname = txtFirstName.Text;
                        string lname = txtLastName.Text;
                        string phone = phoneNumber;
                        string empid = txtEmpID.Text;
                        string empniNum = txtEmpNINumber.Text;
                        string addr1 = txtAddress1.Text;
                        string addr2 = txtAddress2.Text;
                        string pin = txtPostCode.Text;
                        string brid = brId.ToString();
                        string iscpd = cpd.ToString();
                        string isManualScheudle = (chkManualSchedule.Checked ? 1 : 0).ToString();
                        string isAutoSChedule = (chkAutoSchedule.Checked ? 1 : 0).ToString();
                        string storeClose = (chkStoreClose.Checked ? 1 : 0).ToString();
                        string inventoryCtrl = (chkInvenCtrl.Checked ? 1 : 0).ToString();
                        var items = new[]
                            {
                    new { Key = "First Name", Value = fname },
                    new { Key = "Last Name", Value = lname },
                    new { Key = "Phone", Value = phoneNumber },
                    new { Key = "Employ Id", Value = empid },
                    new { Key = "National Insurance", Value = empniNum },
                    new { Key = "Address1", Value = addr1 },
                    new { Key = "Address2", Value = addr2 },
                    new { Key = "Pin", Value = pin },
                    new { Key = "Branch Id", Value = brid },
                    new { Key = "Central Purchase Department ", Value = iscpd },
                    new { Key = "Manual Scheudle", Value = isManualScheudle },
                    new { Key = "Auto SChedule", Value = isAutoSChedule },
                    new { Key = "Store Close", Value = storeClose },
                    new { Key = "InventoryCtrl", Value = inventoryCtrl },

                    };
                        string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                        var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);
                        Common.ShowCustomAlert(this.Page, "Success!", "Order picker created successfully", true, "/Tenant/OrderPicker");
                        return;
                    }
                    catch (Exception ex)
                    {
                        Common.ShowCustomAlert(this.Page, "Error!", (ex.Message.Contains("brm_godown_boy_phone_unique") ? "The number is already registered for another store. Please try with a different mobile number" : ex.Message), false);
                        return;
                    }
                    // Activitylog
                   
                }
                else
                {
                    try
                    {
                        gbparams.Add(new KeyValuePair<string, object>("id", id));
                        gbparams.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));

                        string strUpdateSql = $"UPDATE retaline_godown_boy SET name=@fname, lname=@lname, phone=@phone, emp_id=@empid, emp_ni_number=@empniNum, " +
                        $"emp_email_id=@empEmail, emp_add1=@addr1, emp_add2=@addr2, branch_id=@brid, " +
                        $"emp_pincode=@pin, is_allowManualSchedule=@isManualScheudle , is_allowAutoSchedule=@isAutoSChedule, allowStoreClose=@storeClose,is_offline=@isoffline ,allowInventoryControl=@inventoryCtrl" +
                        $" WHERE id = @id and @brid in (select br_ID from finascop_branch where br_storeGroup=@storegroupid)";
                        int rowsupdated = DataServiceMySql.ExecuteSql(strUpdateSql, UserService.GetAPIConnectionString(), gbparams);

                        // Remove Redis cache entry
                        var cacheService = new RedisCacheService();
                        string cachekey = $"Retl.AppTenant.pendingtasks.count.{this.CurrentUser.APIStoreId}";
                        await cacheService.RemoveAsync(cachekey);
                    }
                    catch (Exception ex)
                    {
                        Common.ShowCustomAlert(this.Page, "Error!", (ex.Message.Contains("brm_godown_boy_phone_unique") ? "The number is already registered for another store. Please try with a different mobile number" : ex.Message), false);
                        return;
                    }
                    // Activitylog
                    String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                    String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                    string Source = strUrl;                  
                    string Users = this.CurrentUser.Email;
                    string fname = txtFirstName.Text;
                    string lname = txtLastName.Text;
                    string phone = phoneNumber;
                    string empid = txtEmpID.Text;
                    string empniNum = txtEmpNINumber.Text;
                    string addr1 = txtAddress1.Text;
                    string addr2 = txtAddress2.Text;
                    string pin = txtPostCode.Text;
                    string brid = brId.ToString();
                    string iscpd = cpd.ToString();
                    string isManualScheudle = (chkManualSchedule.Checked ? 1 : 0).ToString();
                    string isAutoSChedule = (chkAutoSchedule.Checked ? 1 : 0).ToString();
                    string storeClose = (chkStoreClose.Checked ? 1 : 0).ToString();
                    string inventoryCtrl = (chkInvenCtrl.Checked ? 1 : 0).ToString();
                    var items = new[]
                        {
                    new { Key = "First Name", Value = fname },
                    new { Key = "Last Name", Value = lname },
                    new { Key = "Phone", Value = phoneNumber },
                    new { Key = "Employ Id", Value = empid },
                    new { Key = "National Insurance", Value = empniNum },
                    new { Key = "Address1", Value = addr1 },
                    new { Key = "Address2", Value = addr2 },
                    new { Key = "Pin", Value = pin },
                    new { Key = "Branch Id", Value = brid },
                    new { Key = "Central Purchase Department ", Value = iscpd },
                    new { Key = "Manual Scheudle", Value = isManualScheudle },
                    new { Key = "Auto SChedule", Value = isAutoSChedule },
                    new { Key = "Store Close", Value = storeClose },
                    new { Key = "InventoryCtrl", Value = inventoryCtrl },

                    };
                    string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                    var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);
                    Common.ShowCustomAlert(this.Page, "Success!", "Order picker updated successfully", true, "/Tenant/OrderPicker");
                }
            }
            
        }

        protected void selBranch_DataBound(object sender, EventArgs e)
        {
            if (selBranch.Items.Count > 1)
                selBranch.Items.Insert(0, new ListItem("Select Store", ""));
        }

    }
}