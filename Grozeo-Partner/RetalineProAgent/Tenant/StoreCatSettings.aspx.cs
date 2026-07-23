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
using RetalineProAgent.Core.Services.ActiveLog;

namespace RetalineProAgent
{
    public partial class StoreCatSettings: Base.BasePartnerPage
    {
        private string strBTypes;

        protected void Page_Load(object sender, EventArgs e)
        {

            if (!Page.IsPostBack)
            {
                LoadStoreInfo();
            }
        }

        private void LoadStoreInfo()
        {
            int businessCatId = Convert.ToInt32(Request.QueryString["id"]);
            if (businessCatId > 0)
            {

                List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                prms.Add(new KeyValuePair<string, object>("catid", businessCatId));
                prms.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));
                DataTable dataTable = DataServiceMySql.GetDataTable($"SELECT business_category_id,business_category_name,status AS comboMasterBusinessCategorysStatus,business_category_ingroup,rbc_business_type FROM retaline_business_category WHERE business_category_id = @catid and store_group_id=@storegroupid", Service.UserService.GetAPIConnectionString(), prms);
                if (dataTable == null || dataTable.Rows.Count < 1)
                {
                    ShowFailure("Invalid Category", "The category selected is invalid.", "/StoreCategory");
                    return;
                }

                if (dataTable != null && dataTable.Rows.Count > 0)
                {
                    DataRow da = dataTable.Rows[0];
                    txtPrivateCat.Text = da["business_category_name"].ToString();
                    string strBTypes = da["rbc_business_type"].ToString();
                    if (lstBusinessTypes.Items.Count <= 0)
                        lstBusinessTypes.DataBind();

                    foreach (string strbtype in strBTypes.Split(','))
                    {
                        if (!String.IsNullOrEmpty(strbtype) && lstBusinessTypes.Items.FindByValue(strbtype.Trim()) != null)
                            lstBusinessTypes.Items.FindByValue(strbtype.Trim()).Selected = true;
                            //lstBusinessTypes.SelectedValue = strbtype.Trim();
                    }
                }
            }
        }
        
        protected void lstBusinessTypes_DataBound(object sender, EventArgs e)
        {
            if (lstBusinessTypes.Items.Count > 0)
            {
                string strKey = lstBusinessTypes.Attributes["DefaultBType"];
                if (!String.IsNullOrEmpty(strKey))
                {
                    string[] strbtypes = strKey.Trim().Split(',');
                    if (strbtypes.Length > 0)
                    {
                        foreach (string btype in strbtypes)
                            if (!String.IsNullOrEmpty(btype.Trim()) && lstBusinessTypes.Items.FindByText(btype.Trim()) != null)
                                lstBusinessTypes.Items.FindByText(btype.Trim()).Selected = true;
                    }
                    //selBusinessTypes.Text = (selBusinessTypes.Items.FindByText(strKey).Value);
                }
            }
        }


        //protected void SDSDepartment_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        //{
        //    e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        //}

        //protected void SDSCat_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        //{
        //        e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        //}

        //protected void SDSStatus_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        //{
        //        e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        //}

        protected void btnSubmit_Click(object sender, EventArgs e)
        {
            string selectedItems = null;
            int businessCatId = -1; 
            if(!String.IsNullOrEmpty(Request.QueryString["id"]))
                try { businessCatId = Convert.ToInt32(Request.QueryString["id"]); }catch { businessCatId = -1; }

            //if (String.IsNullOrEmpty(txtPrivateCat.Text))
            //{
            //    ShowFailure("Failed", "Store Category is a required field. Please enter Store Category");
            //    lblResult.Text = "Please enter IFSC";
            //    return;
            //}

            //if (String.IsNullOrEmpty(selectedItems))
            //{
            //    ShowFailure("Failed", "Store Category is a required field. Please enter Store Category");
            //    lblResult.Text = "Please enter IFSC";
            //    return;
            //}
            int storegroupid = this.CurrentUser.APIStoreId;
            //int count = 0;
            foreach (ListItem item in lstBusinessTypes.Items)
            {
                if (item.Selected)
                {
                    selectedItems += item.Value + ",";
                }
            }
            selectedItems = selectedItems.Remove(selectedItems.Length - 1);

            string createdOn = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("bussinessCatName", txtPrivateCat.Text));
            prms.Add(new KeyValuePair<string, object>("retailerBusCat", selectedItems));
            prms.Add(new KeyValuePair<string, object>("createdDate", createdOn));
            prms.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));
            
            if (txtPrivateCat.Text == null)
            {
                ShowFailure("Verification failed", "Private category is not added. Please enter private category.");
                return;
            }
            if (selectedItems == null)
            {
                ShowFailure("Verification failed", "Retailer category is not selected. Please enter retailer category.");
                return;
            }
            string strSql = $"INSERT INTO retaline_business_category(business_category_name, business_category_ingroup, rbc_business_type, status,created_on,store_group_id) " +
                                            $"VALUES(@bussinessCatName,1 ,@retailerBusCat,1,@createdDate,@storegroupid)";
            if (businessCatId > 0)
            {
                prms.Add(new KeyValuePair<string, object>("catid", businessCatId));
                strSql = "UPDATE retaline_business_category SET business_category_name = @bussinessCatName, rbc_business_type = @retailerBusCat  WHERE  business_category_id = @catid and store_group_id=@storegroupid";
            }

            DataServiceMySql.ExecuteSql(strSql, Service.UserService.GetAPIConnectionString(), prms);
            // Activitylog
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            string Source = strUrl;
            //int storegroupid = this.CurrentUser.APIStoreId;
            string Users = this.CurrentUser.Email;
            string storegroup = (this.CurrentUser.APIStoreId).ToString();
            string business_category_name = txtPrivateCat.Text;
            string retailerBuscategory = selectedItems;
            string createdDate = createdOn;            
            var items = new[]
                {
                    new { Key = "Business Category Name", Value = business_category_name },
                    new { Key = "Retailer Business Category", Value = retailerBuscategory },
                    new { Key = "Store Group", Value = storegroup },
                    new { Key = "Created Date", Value = createdDate },
                  };
            string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
            var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);
            //Response.Write("<script>alert('Product details saved successfully')</script>");
            ShowSuccess("Store Category Created Successfully!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Your store category has been validated and added successfully!</a></h5>");

        }

        protected void SDSBusinessTypes_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        }
        private void ShowSuccess(string title, string content)
        {
            ltrErrorPopupTitle.Text = title;
            ltrErrorPopupText.Text = content;
            Type cstype = this.GetType();
            String csname1 = "PopupScript";
            ClientScriptManager cs = Page.ClientScript;
            ltrSuccessTitle.Text = title;
            ltrSuccessContent.Text = content;

            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append("<script type=text/javascript> $('#modaldemo4').modal('show'); </");
            cstext1.Append("script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());

            //    cs.RegisterClientScriptBlock(cstype, csname1, @"<script type='text/javascript'>$('#modaldemo4').on('hidden.bs.modal', function (e) {
            //      window.location.href='/bankaccount';
            //});</script>");
        }


        private void ShowFailure(string title, string content, string redirectUrl="")
        {
            ltrErrorPopupTitle.Text = title;
            ltrErrorPopupText.Text = content;
            Type cstype = this.GetType();
            String csname1 = "PopupScript";
            ClientScriptManager cs = Page.ClientScript;


            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append("<script type=text/javascript> $('#modaldemo5').modal('show'); "+(String.IsNullOrEmpty(redirectUrl)? "" 
                : "$('#modaldemo5').on('hidden.bs.modal', function (e) {window.location.href = '"+ redirectUrl + "'; });") +"</");
            cstext1.Append("script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());

        }
    }
}



