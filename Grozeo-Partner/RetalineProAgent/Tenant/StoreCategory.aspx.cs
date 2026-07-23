using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using RetalineProAgent.Core.BussinessModel.Store;
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
    public partial class StoreCategory: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            //if (!IsPostBack && String.IsNullOrEmpty(hidFilterType.Value))
            //{
            //    FilterType = 0; hidFilterType.Value = "0";

            //}

            //lblResult.Text = "";

        }
        protected void lstBusinessTypes_DataBound(object sender, EventArgs e)
        {
            var lstBusinessTypes = (ListBox)sender;

            if (lstBusinessTypes.Items.Count > 0)
            {
                string strKey = lstBusinessTypes.Attributes["businesstypes"];
                if (!String.IsNullOrEmpty(strKey))
                {
                    string[] strbtypes = strKey.Trim().Split(',');
                    if (strbtypes.Length > 0)
                    {
                        foreach (string btype in strbtypes)
                            if (!String.IsNullOrEmpty(btype.Trim()) && lstBusinessTypes.Items.FindByValue(btype.Trim()) != null)
                                lstBusinessTypes.Items.FindByValue(btype.Trim()).Selected = true;
                    }
                    //selBusinessTypes.Text = (selBusinessTypes.Items.FindByText(strKey).Value);
                }
            }
        }

        protected void Page_PreRender(object sender, EventArgs e)
        {
            //if (gvProducts.HeaderRow != null)
            //    gvProducts.HeaderRow.TableSection = TableRowSection.TableHeader;
            //if (selBranches.Items.Count > 1)
            //{
            //    selBranches.Items.Insert(0, new ListItem("Select Branch", "-1"));
            //}

            //ltrBranchName.Visible = selBranches.Items.Count <= 2;

            //if (ltrBranchName.Visible && selBranches.Items.Count > 1)
            //    ltrBranchName.Text = selBranches.Items[1].Text;



        }

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvStoreCat.PageIndex > 0)
                gvStoreCat.PageIndex = gvStoreCat.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvStoreCat.PageIndex < gvStoreCat.PageCount - 1)
                gvStoreCat.PageIndex = gvStoreCat.PageIndex + 1;
        }

        protected void gvStoreCat_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvStoreCat.PageIndex * gvStoreCat.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvStoreCat.Rows.Count;// - 1;
            ltrPageCurStart.Text = startRowOnPage.ToString();
            ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSStoreCat.Select(DataSourceSelectArguments.Empty);
        }


        protected void SDSStoreCat_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        }

        //protected void selBranches_SelectedIndexChanged(object sender, EventArgs e)
        //{
        //    gvStoreCat.PageIndex = 0;
        //    gvStoreCat.DataBind();
        //    ltrBranchName.Text = (selBranches.SelectedIndex >= 0 ? selBranches.SelectedItem.Text : "");
        //}

        //protected void selBranches_DataBound(object sender, EventArgs e)
        //{

        //if (selBranches.Items.Count < 1)
        //{
        //    selBranches.DataBind();
        //}
        //    plcSelectBranchModel.Visible = selBranches.Items.Count > 1;
        //    ltrBranchName.Text = (selBranches.SelectedIndex >= 0 ? selBranches.SelectedItem.Text : "");

        //}



        //protected void SDSBranches_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        //{
        //    e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;

        //}

        protected void DeleteItem_Click(object sender, EventArgs e)
        {
            LinkButton delStoreCat = (LinkButton)sender;
            if (delStoreCat == null)
                return;

            string strSql = $"DELETE FROM retaline_business_category WHERE business_category_id={delStoreCat.Attributes["itemid"]}";
            DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString());
            // Activitylog
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            string Source = strUrl;
            int storegroupid = this.CurrentUser.APIStoreId;

            string Users = this.CurrentUser.Email;
            string storegroup = (this.CurrentUser.APIStoreId).ToString();
            string business_category_id = delStoreCat.Attributes["itemid"] ;           
            var items = new[]
                {
                    new { Key = "Business Category Id", Value = business_category_id },
                    new { Key = " Storegroup", Value = storegroup },                  
                  };
            string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
            var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);
            ShowSuccess("Deleted Successfully!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Your category group has been deleted successfully!</a></h5>");
            //Response.Redirect("~/StoreCategory");
            //SDSInventory.Select(DataSourceSelectArguments.Empty);
            //gvProducts.DataBind();
            //ctrlInventorySetup1.ResetInventory();
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
        }

        protected void SDSStoreCat_Selected(object sender, SqlDataSourceStatusEventArgs e)
        {
            ltrPageTotal.Text = e.AffectedRows.ToString();
        }
    }

}


