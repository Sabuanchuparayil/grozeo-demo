using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Controls
{
    public partial class ctrlpendingaction : Base.BasePartnerUserControl
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                var merchantData = Services.StoreService.MerchantPendingActions(0, this.CurrentUser.APIStoreId);
                if (merchantData != null)
                {
                    var pendingActions = merchantData.PendingActions;
                    var pendingJobs = merchantData.PendingJobs;

                    if (pendingActions != null && pendingJobs != null)
                    {
                        rptPendingActions.DataSource = pendingActions;
                        rptPendingActions.DataBind();
                    }                    

                }
            }
        }
        /// <summary>
        /// Get the content to load based on the key (class name, navigation link, button text)
        /// </summary>
        /// <param name="name">Key</param>
        /// <param name="contentType">1 - class name, 2 - link, 3 - button text.</param>
        /// <param name="type">Action / Job</param>
        /// <returns>content</returns>
        public string GetContent(string name, int contentType, Core.BussinessModel.Store.PendingActvityType type = default)
        {
            string strResult = "";
            switch (name)
            {
                case "emailverified":
                    strResult = (contentType == 1 ? "fa-envelope" : (contentType == 2 ? "javascript:void(0)\" onclick=\"$(\'#modalverifyemail\').modal(\'show\');" : "Verify"));
                    break;
                case "branches":
                    strResult = (contentType == 1 ? "fa-sitemap" : (contentType == 2 ? "/Tenant/branches" : "Add"));
                    break;
                case "bankAccounts":
                    strResult = (contentType == 1 ? "fa-university" : (contentType == 2 ? "/Tenant/Store/BankAccount-Add" : "Add"));
                    break;
                case "storesWithoutBank":
                    strResult = (contentType == 1 ? "fa-briefcase" : (contentType == 2 ? "/Tenant/Store/BankAccount" : "Add"));
                    break;
                case "bankLinkedToStore":
                    strResult = (contentType == 1 ? "fa-money-bill-transfer" : (contentType == 2 ? "/Tenant/Store/BankAccount" : "Add"));
                    break;
                case "gstscount":
                    strResult = (contentType == 1 ? "fa-calculator" : (contentType == 2 ? "/Tenant/store/GST-Add" : "Add"));
                    break;
                case "gstNotLinkedToStore":
                    strResult = (contentType == 1 ? "fa-share-alt" : (contentType == 2 ? "/Tenant/store/gst" : "Manage"));
                    break;
                case "totalStores":
                    strResult = (contentType == 1 ? "fa-shopping-cart" : (contentType == 2 ? "/Tenant/branches" : "Manage"));
                    break;
                case "gstnNotVerified":
                    strResult = (contentType == 1 ? "fa-file-text-o" : (contentType == 2 ? "/Tenant/store/gst" : "Manage"));
                    break;
                case "orderPickers":
                    strResult = (contentType == 1 ? "fa-id-badge" : (contentType == 2 ? "/Tenant/orderpicker" : "Create"));
                    break;
                case "orderPickersOnline":
                    strResult = (contentType == 1 ? "fa-puzzle-piece" : (contentType == 2 ? "/Tenant/orderpicker" : "Manage"));
                    break;
                case "drivers":
                    strResult = (contentType == 1 ? "fa-user" : (contentType == 2 ? "/Tenant/DeliveryStaffs" : "Create"));
                    break;
                case "products":
                    strResult = (contentType == 1 ? "fa-exclamation-circle" : (contentType == 2 ? "/Tenant/MyProducts" : "Add"));
                    break;
                case "deliveryRules":
                    strResult = (contentType == 1 ? "fa-globe mr-2" : (contentType == 2 ? "/Tenant/DeliveryRules" : "Add"));
                    break;
                case "fssaiCount":
                    strResult = (contentType == 1 ? "fa-shopping-basket" : (contentType == 2 ? "/Tenant/Store/FSSAI" : "Add"));
                    break;
                case "fssaiNotLinked":
                    strResult = (contentType == 1 ? "fa-globe" : (contentType == 2 ? "/Tenant/Store/FSSAI" : "Manage"));
                    break;
                case "bankNoLinkedToStore":
                    strResult = (contentType == 1 ? "fa-money-bill-transfer" : (contentType == 2 ? "/Tenant/Store/BankAccount" : "Manage"));
                    break;
                case "merchantLanguagePreference":
                    strResult = (contentType == 1 ? "fa-regular fa-language mr-2 pendicon" : (contentType == 2 ? "javascript:void(0)\" onclick=\"$(\'#modallanguage\').modal(\'show\');" : "Set Language preference"));
                    break;
                case "subaccount":
                    strResult = (contentType == 1 ? "fa-regular fa-money-check-dollar-pen" : (contentType == 2 ? "/Tenant/paymentconfig" : "Add"));
                    break;

            }

            return strResult;

        }
    }
}