
using System;
using System.Web.UI.WebControls;


namespace RetalineProAgent.Finance
{
    public partial class SettlementReport : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            txtFromDate.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
            txtToDate.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
        }

        protected void Page_PreRender(object sender, EventArgs e)
        {
            
        }
        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvSettlementReport.PageIndex > 0)
                gvSettlementReport.PageIndex = gvSettlementReport.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvSettlementReport.PageIndex < gvSettlementReport.PageCount - 1)
                gvSettlementReport.PageIndex = gvSettlementReport.PageIndex + 1;
        }

        protected void gvSettlementReport_RowDataBound(object sender, GridViewRowEventArgs e)
        {
            
        }


        protected void gvSettlementReport_DataBound(object sender, EventArgs e)
        {

        }              
               
        protected void lbtnaction_Click(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;
            hidValueHeadOrderId.Value = (lbtn.Attributes["orderid"]);
            hidValueHeadStorRef.Value= (lbtn.Attributes["storeref"]);            
        }

        protected void lvsettlement_DataBound(object sender, EventArgs e)
        {           
            
        }

        protected void SDSSettlementReport_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            //e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId.ToString();
        }
        
    }
}