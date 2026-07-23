using Amazon.DynamoDBv2.DocumentModel;
using Finascop.Services;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class CreateCoupon : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {

            plcSelect.Visible = true;
            //if (IsPostBack)
            //{
            //    var selectedValues = lstBranches.Items.Cast<ListItem>()
            //                     .Where(i => i.Selected)
            //                     .Select(i => i.Value)
            //                     .ToArray();
            //    hdnSelectedBranches.Value = string.Join(",", selectedValues);
            //}

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

        protected void SDSApplicableFor_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
        }
        protected void btnAdd_Click(object sender, EventArgs e)
        {
            try
            {
                List<KeyValuePair<string, object>> couponparams = new List<KeyValuePair<string, object>>();
                couponparams.Add(new KeyValuePair<string, object>("DiscountType", selDiscountType.SelectedValue));
                couponparams.Add(new KeyValuePair<string, object>("DiscountMode", selDiscountMode.SelectedValue));            
                couponparams.Add(new KeyValuePair<string, object>("Value",(!String.IsNullOrEmpty(txtValue.Text) ? txtValue.Text:"0")));
                couponparams.Add(new KeyValuePair<string, object>("ApplicableFor", selApplicableFor.SelectedValue));
                couponparams.Add(new KeyValuePair<string, object>("TargetAmount", (!String.IsNullOrEmpty(txtTargetAmount.Text)? txtTargetAmount.Text:"0")));
                couponparams.Add(new KeyValuePair<string, object>("Buy",(!String.IsNullOrEmpty(txtBuy.Text)? txtBuy.Text:"0")));
                couponparams.Add(new KeyValuePair<string, object>("Get", (!String.IsNullOrEmpty(txtGet.Text) ? txtGet.Text:"0")));
                couponparams.Add(new KeyValuePair<string, object>("CouponCode", (!String.IsNullOrEmpty(txtCouponCode.Text) ? txtCouponCode.Text:"0")));
                couponparams.Add(new KeyValuePair<string, object>("ExpDate", txtExpDate.Text));
                couponparams.Add(new KeyValuePair<string, object>("Redemption", selRedemption.SelectedValue));
                couponparams.Add(new KeyValuePair<string, object>("CustomerCan", selcuscan.SelectedValue));
                couponparams.Add(new KeyValuePair<string, object>("CouponName", txtCouponName.Text));
                couponparams.Add(new KeyValuePair<string, object>("maxdiscountamount", txtmaxamount.Text));
                couponparams.Add(new KeyValuePair<string, object>("storeGroupId", Convert.ToInt32(this.CurrentUser.APIStoreId)));             
                couponparams.Add(new KeyValuePair<string, object>("Branchs", hdnSelectedBranches.Value));
                string applicableForId = selApplicableFor.SelectedValue == "1" ? selProduct.SelectedValue :
                         selApplicableFor.SelectedValue == "2" ? selBrand.SelectedValue :
                         selApplicableFor.SelectedValue == "3" ? selCategory.SelectedValue :
                         "0";// Default case
                if (applicableForId != null)
                {
                    couponparams.Add(new KeyValuePair<string, object>("applicableForId", applicableForId));
                }
                string strSql = $"INSERT INTO `retaline_offer_management`(discountType,bom_type,bom_offerType,applicableForId,customerRedemtion,bom_offrPlacement,bom_offerCode,bom_offrDiffer,bom_offrPromotion,bom_offrSupplier,bom_narration,bom_enddate,bom_use,storeGroupId,branch,maxDiscountValue)" +
                    $" value(@DiscountType,@DiscountMode,@ApplicableFor,@applicableForId,@CustomerCan,@Value,@CouponCode,@TargetAmount,@Buy,@Get,@CouponName,@ExpDate,@Redemption,@storeGroupId,@Branchs,@maxdiscountamount)  ";
                DataServiceMySql.ExecuteSql(strSql, Service.UserService.GetAPIConnectionString(), couponparams);
                Common.ShowCustomAlert(this.Page, "Success", "Coupon created successfully!!", true, "/Tenant/DiscountCoupons/");
            }
            catch(Exception ex)
            {
                Common.ShowCustomAlert(this.Page, "Failed", "Failed to Creat Coupon!", false, "/Tenant/DiscountCoupons/");
            }
          
        }
        protected void selApplicableFor_SelectedIndexChanged(object sender, EventArgs e)
        {
            int selctapplicablefor = selApplicableFor.SelectedIndex;

            // Set all panels to invisible first
            plcSelect.Visible = false;
            plcProductExpand.Visible = false;
            plcCategoryExpand.Visible = false;
            plcBrandExpand.Visible = false;

            // Switch to set the appropriate panel visible
            switch (selctapplicablefor)
            {
                case 1:
                    plcProductExpand.Visible = true;
                    break;
                case 2:
                    plcBrandExpand.Visible = true;
                    break;
                case 3:
                    plcCategoryExpand.Visible = true;
                    break;
                default:
                    plcSelect.Visible = true;
                    break;
            }


        }

        protected void selDiscountType_SelectedIndexChanged(object sender, EventArgs e)
        {

            string discountType = selDiscountType.SelectedValue;
            LoadApplicableForOptions(discountType);

        }
        private ListItem CreateListItem(string text, string value)
        {
            return new ListItem(text, value);
        }
      
        private void LoadApplicableForOptions(string discountType)
        {
            selApplicableFor.Items.Clear();

            switch (discountType)
            {
                case "1":
                    AddItemsToApplicableFor(
                        CreateListItem("Select Discount Mode", "0"),
                        CreateListItem("Offer Percentage", "1"),
                        CreateListItem("Coupon Offers", "2"));
                    break;

                case "3":
                    AddItemsToApplicableFor(
                        CreateListItem("Select Applicable for", "0"),
                        CreateListItem("SKU", "1"),
                        CreateListItem("Brand", "2"),
                        CreateListItem("Sub Category", "3"));
                    break;

                case "4":
                    AddItemsToApplicableFor(
                        CreateListItem("Select Applicable for", "0"),
                        CreateListItem("SKU", "1"),
                        CreateListItem("Brand", "2"));
                    break;

                case "other":
                    AddItemsToApplicableFor(
                        CreateListItem("Select Applicable for", "0"),
                        CreateListItem("Flat Offer", "1"),
                        CreateListItem("Category", "2"),
                        CreateListItem("Item Offer", "3"),
                        CreateListItem("SKU", "4"),
                        CreateListItem("Brand", "5"));
                    break;

                default:

                    AddItemsToApplicableFor(
                        CreateListItem("No data", "0"));
                    break;
            }

        }
        private void AddItemsToApplicableFor(params ListItem[] items)
        {
            selApplicableFor.Items.AddRange(items);
        }

        protected void selRedemption_SelectedIndexChanged(object sender, EventArgs e)
        {

        }
              
    }
}


