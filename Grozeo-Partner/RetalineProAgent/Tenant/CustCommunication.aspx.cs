using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class CustCommunication: Base.BasePartnerPage
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
            //foreach (GridViewRow gr in gvCustCommunication.Rows)
            //{
            //    CheckBox chkStatus = (CheckBox)gr.FindControl("chkStatus");
            //    if (chkStatus.Checked)
            //    {
            //        chkStatus.Enabled = true;
            //    }
            //    else
            //    {
            //        chkStatus.Enabled = false;
            //    }
            //}
            if (!IsPostBack && String.IsNullOrEmpty(hidFilterType.Value))
            {
                FilterType = 1; hidFilterType.Value = "1";
            }
            
            if (gvCustCommunication.HeaderRow != null)
                gvCustCommunication.HeaderRow.TableSection = TableRowSection.TableHeader;
            
        }

        protected void Page_PreRender(object sender, EventArgs e)
        {
            lbtnSMS.CssClass = String.Format("nav-link btn btn-block btn-outline-primary btn-sm p-1 px-2 {0}", (FilterType <= 1 ? "active" : ""));
            lbtnEmail.CssClass = String.Format("nav-link btn btn-block btn-outline-primary btn-sm p-1 px-2 {0}", (FilterType == 2 ? "active" : ""));
            lbtnWhatsapp.CssClass = String.Format("nav-link btn btn-block btn-outline-primary btn-sm p-1 px-2 {0}", (FilterType == 3 ? "active" : ""));
            //gvCustCommunication.DataBind();
            //if (!IsPostBack)
            //{
            //    gvCustCommunication.DataBind();
            //}
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

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvCustCommunication.PageIndex > 0)
                gvCustCommunication.PageIndex = gvCustCommunication.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvCustCommunication.PageIndex < gvCustCommunication.PageCount - 1)
                gvCustCommunication.PageIndex = gvCustCommunication.PageIndex + 1;
        }

        protected void gvCustCommunication_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvCustCommunication.PageIndex * gvCustCommunication.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvCustCommunication.Rows.Count - 1;
            //ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSCustCommunication.Select(DataSourceSelectArguments.Empty);
        }

        protected void SDSCustCommunication_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            hidFilterType.Value = FilterType.ToString();
            e.Command.Parameters["filterType"].Value = FilterType;
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        }

        protected void chkStatus_CheckedChanged(object sender, EventArgs e)
        {
            CheckBox chbtn = (CheckBox)sender;

            if (chbtn != null && !String.IsNullOrEmpty(chbtn.Attributes["commId"]))
            {
                int commId = Convert.ToInt32(chbtn.Attributes["commId"]);
                int isnewRequired = (chbtn.Checked ? 1 : 0);
                List<KeyValuePair<string, object>> cmparams = new List<KeyValuePair<string, object>>();
                cmparams.Add(new KeyValuePair<string, object>("storeGroupId", this.CurrentUser.APIStoreId));
                cmparams.Add(new KeyValuePair<string, object>("cmmId", commId));
                var dtCommEntryMap = DataServiceMySql.GetDataTable($"SELECT COUNT(*) AS entryCount FROM communication_entry_map WHERE ceId = @cmmId", Service.UserService.GetAPIConnectionString(), cmparams);
                DataRow dr = dtCommEntryMap.Rows[0];
                if (Convert.ToInt32(dr["entryCount"]) == 0)
                {
                    string insertQry = $"INSERT INTO communication_entry_map(ceId, storeGroupId) VALUES(@cmmId,@storeGroupId)";
                    DataServiceMySql.ExecuteSql(insertQry, Service.UserService.GetAPIConnectionString(), cmparams);
                    Common.ShowCustomAlert(this.Page, "Data updated!", "Data updated successfully!", true, "/Tenant/CustCommunication");
                }
                else 
                {
                    string insertQry = $"DELETE FROM communication_entry_map WHERE ceId=@cmmId";
                    DataServiceMySql.ExecuteSql(insertQry, Service.UserService.GetAPIConnectionString(), cmparams);
                    Common.ShowCustomAlert(this.Page, "Data updated!", "Data updated successfully!", true, "/Tenant/CustCommunication");
                }
                    
            }
            gvCustCommunication.DataBind();
        }

        protected void gvCustCommunication_RowDataBound(object sender, GridViewRowEventArgs e)
        {
            if (e.Row.RowType != DataControlRowType.DataRow)
                return;
            try
            {
                string newisRequired = Convert.ToString(DataBinder.Eval(e.Row.DataItem, "newisRequired"));
                string isRequired = Convert.ToString(DataBinder.Eval(e.Row.DataItem, "isRequired"));
                if (newisRequired != "1" && isRequired == "1")
                    e.Row.BackColor = System.Drawing.Color.Aquamarine;
            }
            catch (Exception ex)
            {

            }
        }
    }

}


