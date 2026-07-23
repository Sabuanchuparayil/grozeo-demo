using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.BussinessModel.Store
{
    public class MerchantData
    {
        public int StoregroupId { get; set; }
        public int APIStoregroupId { get; set; }
        public string MerchantName { get; set; }
        public bool CanCheckout { get; set; }
        public bool PayOnline { get; set; }
        public bool PodEnabled { get; set; }
        public bool HasPWA { get; set; }
        public int BankAccounts { get; set; }
        public int BankAccountLinkedToStores { get; set; }
        public int StoresWithBank { get; set; }
        public int StoresWithoutBank { get; set; }
        public int GSTs { get; set; }
        public int GSTNotVerified { get; set; }
        public int GSTsNotLinkedToStore { get; set; }
        public int FSSAIs { get; set; }
        public int FSSAIsNotLinkedToStore { get; set; }
        public int StoreUsers { get; set; }
        public int EmailVerified { get; set; }
        public int MobileVerified { get; set; }
        public int Drivers { get; set; }
        public int Products { get; set; }
        public int TotalStores { get; set; }
        public int StoresOnline { get; set; }
        public int OrderPickers { get; set; }
        public int OrderPickersOnline { get; set; }
        public int ProductsWithStockAndPrice { get; set; }
        public int DeliveryRules { get; set; }
        public int RestaurantProducts { get; set; }
        public int Subaccount { get; set; }
        public int MerchantLanguage { get; set; }
        public List<PendingActvity> PendingActions { get; set; }=new List<PendingActvity>();
        public List<PendingActvity> PendingJobs { get; set; } = new List<PendingActvity>();
        public int TenantType { get; set; }
        public DateTime CreatedOn { get; set; }
        public string LogoImage {  get; set; }
        public string PackageType {  get; set; }
        public string PlanName { get; set; }
        public int Orders { get; set; }
        public double OrderValue {  get; set; }

        public string CpOrganizationName {  get; set; }
        public int CpMode { get; set; }
        public string RoName {  get; set; }
        public string BAName { get; set; }
        public string Areaname { get; set; }
        public string AreaLocation { get; set; }
        public bool IsFeatured {  get; set; }
        public string BranchNames {  get; set; }
        public string BranchAreaName { get; set; }
    }

}
