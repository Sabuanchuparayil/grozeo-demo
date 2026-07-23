using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using RetalineProAgent.Core.BussinessModel.Store;
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
    public partial class DiscountCoupons : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
        }

        protected void Page_PreRender(object sender, EventArgs e)
        {

        }

        protected void gvDiscountCoupon_DataBound(object sender, EventArgs e)
        {
           
        }

        protected void lbtnSearch_Click(object sender, EventArgs e)
        {
            gvDiscountCoupon.PageIndex = 0;
        }
        //get (Coupon name, (Code: [CODE], type: [TYPE]))  like this
        public static string GetOfferSummary(string name, string code, string discountType)
        {
            string Name = string.IsNullOrWhiteSpace(name) ? "Unnamed Coupon" : name;
            string Code = string.IsNullOrWhiteSpace(code) ? "N/A" : code;
            string Type = string.IsNullOrWhiteSpace(discountType) ? "Unknown" : discountType;
            return $"{Name} (Code: {Code}, Type: {Type})";
        }
        protected void btndelete_Click(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;           
            string Id = (lbtn.Attributes["discountid"]);
            List<KeyValuePair<String, Object>> parmeters = new List<KeyValuePair<string, object>>();
            parmeters.Add(new KeyValuePair<string, object>("discountid", Id));
            string strSql = $"UPDATE retaline_offer_management SET bom_status=0 WHERE bom_id=@discountid";
            DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), parmeters);            
            gvDiscountCoupon.DataBind();
            Common.ShowCustomAlert(this.Page, "Coupon Deleted", "The coupon has been successfully deleted.", true);

        }

        protected void SDSDiscountCoupon_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;

        }
        public static string GetBranchNames(string branchIds)
        {
            var ids = branchIds?.Split(',').Select(id => id.Trim()) .Where(id => !string.IsNullOrEmpty(id)) .ToList();
            if (ids == null || !ids.Any())
                return "All Store";
            var parameters = ids .Select((id, i) => new KeyValuePair<string, object>($"@id{i}", id)).ToList();
            var brid = string.Join(", ", parameters.Select(p => p.Key));
            var query = $"SELECT br_Name FROM finascop_branch WHERE br_ID IN ({brid})";
            var table = DataServiceMySql.GetDataTable(query, UserService.GetAPIConnectionString(), parameters);
            return (table?.Rows.Count ?? 0) == 0  ? "No Branch Found" : string.Join(", ", table.AsEnumerable().Select(r => r["br_Name"].ToString()));
        }

    }
}


