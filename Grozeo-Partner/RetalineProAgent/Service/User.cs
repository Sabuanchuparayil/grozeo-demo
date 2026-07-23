using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace RetalineProAgent.Service
{
    public class User
    {
        public int Id { get; set; }
        public int StoreGroupId { get; set; }
        public string StoreGroupName { get; set; }
        public string Email { get; set; }
        public string Phone { get; set; }
        public string Password { get; set; }
        public string FullName { get; set; }
        public string Address { get; set; }
        public string City { get; set; }
        public string State { get; set; }
        public string Country { get; set; }
        public string Photo { get; set; }
        public bool Active { get; set; }
        public PasswordFormat PasswordFormat { get; set; }
        public string PasswordSalt { get; set; }
        public bool Deleted { get; set; }
        public DateTime CreatedOn { get; set; }
        public DateTime UpdatedOn { get; set; }
        public int APIStoreId { get; set; }
        public int ApiID { get; set; }
        public string APIConnection { get; set; }
        public string APIUrl { get; set; }
        public string LogoSmall { get; set; }
        public string LogoImage { get; set; }
        public bool OwnBannerOnly { get; set; }
        public bool HasVerifiedEmail { get; set; }
        public string PublicSiteUrl { get; set; }
        public int TenantStatus { get; set; }
        public int TenantStage { get; set; }
        public int TenantType { get; set; }
        public int PackageId { get; set; }
        public string Theme { get; set; }
        public UserRoleType UserType { get; set; } = UserRoleType.Tenant;
        public int APIRoleBranchId { get; set; } = -2;
        public int? AreaId { get; set; }
        public int? FleetId { get; set; }
        public int? AnalyticsId { get; set; }
        public bool CanCheckout { get; set; }
        public bool OnlinePaymentEnabled { get; set; }
        public bool PODEnabled { get; set; }
        public bool HasPaymentMethod { get; set;}
        public bool MobileVerified {  get; set; }
        public int[] roleId { get; set; }

    }
}