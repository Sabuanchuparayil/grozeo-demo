using log4net;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;

namespace RetalineProAgent.Base
{
    public partial class BasePartnerUserControl : UserControl
    {
		private static readonly ILog log = LogManager.GetLogger(typeof(BasePartnerPage));
		protected void LogError(string content)
		{
			//try
			//{
			Type derivedType = this.GetType();
			if (derivedType != null)
			{
				try
				{
					content = String.Format("{0}, {1}, {2}, Error: {3}", derivedType.FullName, derivedType.Namespace, derivedType.Assembly.FullName, content);
				}
				catch { }
			}
			log.Error(content);
			//}
			//catch { }
		}

		protected Service.User CurrentUser
        {
            get
            {
                if (_curuser != null)
                    return _curuser;

                _curuser = Infrastructure.PartnerContext.Current.User ?? Service.UserService.CachedDefaultUser;
                return _curuser ?? default;
            }
        }
        private Service.User _curuser = null;

        protected string ActiveMenuCss(string virtualPath, string pageType)
        {
            string css = "";
            string queryType = Request.QueryString["type"]?.ToLower();
            switch (pageType)
            {
                case "business-navigation-resource":
                    if (virtualPath == "~/Business/BusinessNavigations/Resources.aspx" || virtualPath == "~/Business/RelationshipOfficer.aspx" || virtualPath == "~/Business/AreaManager.aspx" || virtualPath == "~/Business/DeliveryStaff.aspx" || virtualPath == "~/Business/DeliveryStaffSettings.aspx")
                        css = "mega-dropdown active";
                    break;
                case "business-home":
                    if (virtualPath == "~/Business/Default.aspx")
                        css = "mega-dropdown active";
                    break;
                case "business-contact":
                    if ((virtualPath == "~/Business/ClientManagement.aspx" && queryType == "contact") || virtualPath == "~/Business/ContactSettings.aspx")
                        css = "mega-dropdown active";
                    break;
                case "business-leads":
                    if ((virtualPath == "~/Business/ClientManagement.aspx" && queryType == "lead") || virtualPath == "~/Business/AsstLeadSettings.aspx")
                        css = "mega-dropdown active";
                    break;
                case "business-prospects":
                    if (virtualPath == "~/Business/ClientManagement.aspx" && queryType == "prospect")
                        css = "mega-dropdown active";
                    break;
                case "business-orderdelivery":
                    if (virtualPath == "~/Business/DeliveryBookingFailed.aspx")
                        css = "mega-dropdown active";
                    break;
                case "business-retailMerchants":
                    if (virtualPath == "~/Business/CRMRetailers.aspx" || virtualPath == "~/Business/AddRetailer.aspx")
                        css = "mega-dropdown active";
                    break;
                case "business-merchandisingPrds":
                    if (virtualPath == "~/Business/BSponsoredItems.aspx")
                        css = "mega-dropdown active";
                    break;
                //case "business-support":
                //    if (virtualPath == "~/Business/CRMRetailers.aspx")
                //        css = "mega-dropdown active";
                //    break;


                //case "business-navigation-crm":
                //    if (virtualPath == "~/Business/BusinessNavigations/BusinessCRM.aspx" || virtualPath == "~/Business/Contacts.aspx" || virtualPath == "~/Business/RetailerLeads.aspx" || virtualPath == "~/Business/WholesalerLeads.aspx" || virtualPath == "~/Business/CRMRetailers.aspx" || virtualPath == "~/Business/AssociateLeads.aspx")
                //        css = "mega-dropdown active";
                //    break;
                case "business-navigation-accounts":
                    if (virtualPath == "~/Business/BusinessNavigations/BusinessAccounts.aspx" || virtualPath == "~/Business/BusinessNavigations/RevenueStream.aspx" || virtualPath == "~/Business/BusinessNavigations/BusinessReports.aspx")
                        css = "mega-dropdown active";
                    break;
                case "business-area":
                    if (virtualPath == "~/Business/Area.aspx")
                        css = "mega-dropdown active";
                    break;
                case "tenant-home":
                    if (virtualPath == "~/Tenant/Default.aspx" || virtualPath == "~/Tenant/PendingActions.aspx")
                        css = "mega-dropdown active";
                    break;
                case "tenant-product":
                    if (virtualPath == "~/Navigations/Products.aspx" || virtualPath == "~/Tenant/InventoryMapping.aspx" || virtualPath == "~/Tenant/PrivateInventory.aspx" || virtualPath == "~/Tenant/Products.aspx" || virtualPath == "~/Tenant/ItemsForSale.aspx" || virtualPath == "~/Tenant/UploadStock.aspx" || virtualPath == "~/Tenant/SponsoredProducts.aspx" || virtualPath == "~/Tenant/API_connector.aspx" || virtualPath == "~/Tenant/StoreCategory.aspx" || virtualPath == "~/Tenant/StoreCatSettings.aspx" || virtualPath == "~/Tenant/PrivateCategory.aspx" || virtualPath == "~/Tenant/PrivateCatSettings.aspx" || virtualPath == "~/Tenant/PrivateCatItems.aspx" || virtualPath == "~/Tenant/MyProducts.aspx" || virtualPath == "~/Tenant/BrandProduct.aspx" || virtualPath == "~/Tenant/StockPrice.aspx" || virtualPath == "~/Tenant/DuplicateInventory.aspx" || virtualPath == "~/Tenant/ViewPrivateInventory.aspx" || virtualPath == "~/Tenant/BulkImport.aspx" || virtualPath == "~/Tenant/BulkImportAPI.aspx" || virtualPath == "~/Tenant/ProductGroup.aspx" || virtualPath == "~/Tenant/Brands.aspx" || virtualPath == "~/Tenant/Comboproduct.aspx")
                        css = "mega-dropdown active";
                    break;
                case "tenant-sales":
                    if (virtualPath == "~/Tenant/SaleAndReturnOrders.aspx" || virtualPath == "~/Tenant/SaleOrdDetails.aspx")
                        css = "mega-dropdown active";
                    break;
                case "tenant-packingDelivery":
                    if (virtualPath == "~/Navigations/PackingDelivery.aspx" || virtualPath == "~/Tenant/PendingOrders.aspx" || virtualPath == "~/Tenant/OrderDetails.aspx" || virtualPath == "~/Tenant/ManualPacking.aspx" || virtualPath == "~/Tenant/AssignOrderPicker.aspx" || virtualPath == "~/Tenant/ViewAndUpdate.aspx" || virtualPath == "~/Tenant/AssignOrdPicker.aspx")
                        css = "mega-dropdown active";
                    break;
                case "tenant-delivery":
                    if (virtualPath == "~/Tenant/MerchantDelivery.aspx" || virtualPath == "~/Tenant/DelivOrderDetails.aspx" || virtualPath == "~/Tenant/ManualDelivery.aspx" || virtualPath == "~/Tenant/LiveVehicles.aspx")
                        css = "mega-dropdown active";
                    break;
                case "tenant-accounts":
                    if (virtualPath == "~/Navigations/Accounts.aspx" || virtualPath == "~/Tenant/SalesReport.aspx" || virtualPath == "~/Tenant/packingReport.aspx" || virtualPath == "~/Tenant/Finance/deliveryReport.aspx" || virtualPath == "~/Tenant/Passbook.aspx" || virtualPath == "~/Tenant/Finance/Taxreport.aspx" || virtualPath == "~/Navigations/salesReports.aspx" || virtualPath == "~/Tenant/Finance/SalesReport.aspx" || virtualPath == "~/Tenant/Finance/DetailedSalesReport.aspx" || virtualPath == "~/Tenant/Finance/SettlementReport.aspx" || virtualPath == "~/Tenant/ReceiveCash.aspx" || virtualPath == "~/Tenant/CCViewJobs.aspx" || virtualPath == "~/Tenant/Finance/PayOutReports.aspx" || virtualPath == "~/Tenant/Finance/PerformanceReports.aspx" || virtualPath == "~/Tenant/SMSReport.aspx")
                        css = "mega-dropdown active";
                    break;
                case "tenant-support":
                    if (virtualPath == "~/Navigations/Support.aspx" || virtualPath == "~/Tenant/Contact.aspx" || virtualPath == "~/Tenant/BusinessFAQ.aspx" || virtualPath == "~/Tenant/MessageCenter.aspx" || virtualPath == "~/Tenant/CustCommunication.aspx")
                        css = "mega-dropdown active";
                    break;
                case "tenant-analytics":
                    if (virtualPath == "~/Tenant/Analytics.aspx")
                        css = "mega-dropdown active";
                    break;
                case "tenant-settings":
                    if (virtualPath == "~/Navigations/StoreSettings.aspx" || virtualPath == "~/Navigations/StoreConfigSett.aspx" || virtualPath == "~/Navigations/BusinessSettings.aspx" || virtualPath == "~/Tenant/Store/StoreSettings.aspx" || virtualPath == "~/Tenant/Store/GST.aspx" || virtualPath == "~/Tenant/Store/BankAccount.aspx" || virtualPath == "~/Tenant/Store/BankAccount-Add.aspx" || virtualPath == "~/Tenant/Branches.aspx" || virtualPath == "~/Tenant/ManageBusinessType.aspx" || virtualPath == "~/Tenant/OrderPicker.aspx" || virtualPath == "~/Tenant/OrderPickerSettings.aspx" || virtualPath == "~/Tenant/DeliveryStaffs.aspx" || virtualPath == "~/Tenant/DeliveryStaffCreate.aspx" || virtualPath == "~/Tenant/Store/GST-Add.aspx" || virtualPath == "~/Navigations/Delivery.aspx" || virtualPath == "~/Tenant/DeliveryRules.aspx" || virtualPath == "~/Tenant/DeliveryRuleSettings.aspx" || virtualPath == "~/Tenant/DeliverySlot.aspx" || virtualPath == "~/Navigations/Appearance.aspx" || virtualPath == "~/Tenant/Appearance/Logo.aspx" || virtualPath == "~/Tenant/Appearance/Banner.aspx" || virtualPath == "~/Tenant/Appearance/BannerSettings.aspx" || virtualPath == "~/Navigations/ContentsPages.aspx" || virtualPath == "~/Tenant/AboutContent.aspx" || virtualPath == "~/Tenant/HowItWorks.aspx" || virtualPath == "~/Tenant/FAQ.aspx" || virtualPath == "~/Tenant/PrivacyPolicy.aspx" || virtualPath == "~/Tenant/TermsOfUse.aspx" || virtualPath == "~/Tenant/Appearance/Themes.aspx" || virtualPath == "~/Navigations/crm.aspx" || virtualPath == "~/Tenant/Leads.aspx" || virtualPath == "~/Tenant/LeadSettings.aspx" || virtualPath == "~/Tenant/LeadSettings.aspx" || virtualPath == "~/Tenant/Customers.aspx" || virtualPath == "~/Navigations/Users.aspx" || virtualPath == "~/Tenant/Store/Users.aspx" || virtualPath == "~/Tenant/Store/ManageUser.aspx" || virtualPath == "~/Tenant/Campaigns.aspx" || virtualPath == "~/Tenant/DomainControl.aspx" || virtualPath == "~/Tenant/ManageBusinessInfo.aspx" || virtualPath == "~/Tenant/Store/GST.aspx" || virtualPath == "~/Tenant/Store/GST-Add.aspx" || virtualPath == "~/Tenant/Store/FSSAI.aspx" || virtualPath == "~/Tenant/Store/FSSAI-Add.aspx" || virtualPath == "~/Tenant/MobileApp.aspx" || virtualPath == "~/Tenant/Appearance/Graphics.aspx" || virtualPath == "~/Tenant/Appearance/CustomBanner.aspx" || virtualPath == "~/Tenant/Appearance/CustomisedGraphics.aspx" || virtualPath == "~/Tenant/PackageType.aspx" || virtualPath == "~/Tenant/DiscountCoupons.aspx" || virtualPath == "~/Tenant/CreateCoupon.aspx")
                        css = "mega-dropdown active";
                    break;
                case "finance_home":
                    if (virtualPath == "~/Finance/Default.aspx")
                        css = "mega-dropdown active";
                    break;
                case "finance_Accounting":
                    if (virtualPath == "~/Finance/Navigations/Accounting.aspx" || virtualPath == "~/Finance/DataEntry.aspx" || virtualPath == "~/Finance/VoucherEntry.aspx" || virtualPath == "~/Finance/PendingEntries.aspx")
                        css = "mega-dropdown active";
                    break;
                case "finance_Subscription":
                    if (virtualPath == "~/Finance/FinanceSubscriptions")
                        css = "mega-dropdown active";
                    break;
                case "finance_AccountBooks":
                    if (virtualPath == "~/Finance/Navigations/AccountBooks.aspx" || virtualPath == "~/Finance/Daybook.aspx" || virtualPath == "~/Finance/Ledger.aspx" || virtualPath == "~/Finance/CostAllocationReports.aspx" || virtualPath == "~/Finance/SettlementReports.aspx")
                        css = "mega-dropdown active";
                    break;
                case "finance_Reports":
                    if (virtualPath == "~/Finance/Navigations/Reports.aspx" || virtualPath == "~/Finance/Trialbalance.aspx" || virtualPath == "~/Finance/ProfitAndLoss.aspx" || virtualPath == "~/Finance/BalanceSheet.aspx")
                        css = "mega-dropdown active";
                    break;
                case "finance_TaxesandDuties":
                    if (virtualPath == "~/Finance/Navigations/TaxesandDuties.aspx")
                        css = "mega-dropdown active";
                    break;
                case "finance_ChartofAccounts":
                    if (virtualPath == "~/Finance/Navigations/ChartofAccounts.aspx" || virtualPath == "~/Finance/costallocationrules.aspx" || virtualPath == "~/Finance/AccountSetup.aspx" ||  virtualPath == "~/Finance/Autopostingsettings.aspx") 
                        css = "mega-dropdown active";
                    break;
                case "finance_Costallocationandautoposting":
                    if (virtualPath == "~/Finance/Navigations/Costallocationandautoposting.aspx" || virtualPath == "~/Finance/payment_type.aspx"||  virtualPath == "~/Finance/valuehead.aspx" ||  virtualPath == "~/Finance/CostAllocation.aspx" || virtualPath == "~/Finance/orderCalculationHeads.aspx" || virtualPath == "~/Finance/event_master.aspx" || virtualPath == "~/Finance/deliverytype.aspx" || virtualPath == "~/Finance/areatype.aspx" || virtualPath == "~/Finance/AutoPostingRules.aspx" || virtualPath == "~/Finance/marginapplicable.aspx" || virtualPath == "~/Finance/GroupManagement.aspx" || virtualPath == "~/Finance/LedgerManagement.aspx" || virtualPath == "~/Finance/CostCategory.aspx" || virtualPath == "~/Finance/CostCentre.aspx" || virtualPath == "~/Finance/CostCentre.aspx" || virtualPath == "~/Finance/Costpurpose.aspx")
                        css = "mega-dropdown active";
                    break;
            }

            return css;
        }

    }
}