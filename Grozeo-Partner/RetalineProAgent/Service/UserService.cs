using RetalineProAgent.Core.Services;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Web;

namespace RetalineProAgent.Service
{
    public static class UserService
    {
        private static string HashedPasswordFormat => "";
        /// <summary>
        /// Get customer by email
        /// </summary>
        /// <param name="email">Email</param>
        /// <returns>Customer</returns>
        public static User GetCustomerByEmail(string email)
        {
            if (string.IsNullOrWhiteSpace(email))
                return null;

           List<KeyValuePair<string, object>> param = new List<KeyValuePair<string, object>>();
           param.Add(new KeyValuePair<string, object>("email", email));
           DataTable dt = DataService.GetDataTable($@"
SELECT u.*, (select top 1 HostAddress from Host where TenantId=u.StoreGroupId order by Id desc) as host, isnull(a.StoreId, -1) as APIStoreId, 
c.APIConnection, c.APIUrl, a.LogoSmall, a.LogoImage, a.OwnBannerOnly, a.Stage, a.Status as TenantStatus, a.TenantType, a.PackageId, 
a.Theme, a.AnalyticsId, a.CanCheckout, a.OnlinePaymentEnabled, a.PODEnabled, a.HasPaymentMethod, 
(select top 1 BranchId from User_UserRole_Mapping where UserId = u.Id and StoreGroupId = u.StoreGroupId and BranchId is not null) as BranchId 
FROM [User] u left join AppTenant a on a.Id=u.StoreGroupId left join appconfig c on a.ApiID = c.Id 
WHERE u.Email like @email", parmeters: param);

            if(dt != null && dt.Rows.Count > 0)
            {
                User user = PopulateUser(dt.Rows[0]);//new User();
                return user;

            }
            return null;
        }

        public static User GetCustomerByMobile(string mobile)
        {
            //if (string.IsNullOrWhiteSpace(mobile) || mobile.Length != 10)
            //    return null;       
            if (string.IsNullOrWhiteSpace(mobile)||(ConfigurationManager.AppSettings.Get("CountryCode") == "IN" && mobile.Length != 10))
            {
                return null;
            }
            List<KeyValuePair<string, object>> param = new List<KeyValuePair<string, object>>();
            param.Add(new KeyValuePair<string, object>("mobile", mobile));
            DataTable dt = DataService.GetDataTable("SELECT u.*, (select top 1 HostAddress from Host where TenantId=u.StoreGroupId order by Id desc) as host, isnull(a.StoreId, -1) as APIStoreId, c.APIConnection, c.APIUrl, a.LogoSmall, a.LogoImage, a.OwnBannerOnly, a.Stage, a.Status as TenantStatus, a.TenantType, a.PackageId, a.Theme, a.AnalyticsId, a.CanCheckout, a.OnlinePaymentEnabled, a.PODEnabled, a.HasPaymentMethod FROM [User] u left join AppTenant a on a.Id=u.StoreGroupId left join appconfig c on a.ApiID = c.Id WHERE u.Mobile like @mobile", parmeters: param);

            if (dt != null && dt.Rows.Count > 0)
            {
                User user = PopulateUser(dt.Rows[0]);//new User();
                return user;

            }
            return null;
        }

        public static User GetCustomerById(int id)
        {
            if (id < 1)
                return null;

            List<KeyValuePair<string, object>> param = new List<KeyValuePair<string, object>>();
            param.Add(new KeyValuePair<string, object>("id", id));
            DataTable dt = DataService.GetDataTable("SELECT u.*, (select top 1 HostAddress from Host where TenantId=u.StoreGroupId order by Id desc) as host, isnull(a.StoreId, -1) as APIStoreId, c.APIConnection, c.APIUrl, a.LogoSmall, a.LogoImage, a.OwnBannerOnly, a.Stage, a.Status as TenantStatus, a.TenantType, a.PackageId, a.Theme, a.AnalyticsId, a.CanCheckout, a.OnlinePaymentEnabled, a.PODEnabled, a.HasPaymentMethod FROM [User] u left join AppTenant a on a.Id=u.StoreGroupId left join appconfig c on a.ApiID = c.Id WHERE u.Id = @id", parmeters: param);

            if (dt != null && dt.Rows.Count > 0)
            {
                User user = PopulateUser(dt.Rows[0]);
                return user;

            }
            return null;
        }


        /// <summary>
        /// Get customer by username
        /// </summary>
        /// <param name="username">Username</param>
        /// <returns>Customer</returns>
        public static User GetCustomerByUsername(string username)
        {
            if (string.IsNullOrWhiteSpace(username))
                return null;
            return GetCustomerByEmail(username);
            //var query = from c in _customerRepository.Table
            //            orderby c.Id
            //            where c.Username == username
            //            select c;
            //var customer = query.FirstOrDefault();
            //return customer;
        }

        public static User PopulateUser(DataRow dr)
        {
            User user = new User();
            if (dr == null)
                return null;

            user.Id = (int)dr["Id"];
            user.Email = dr["Email"].ToString();
            user.StoreGroupId = (int)dr["StoreGroupId"];
            if (dr.Table.Columns.Contains("APIStoreId") && dr["APIStoreId"] != DBNull.Value)
                user.APIStoreId = (int)dr["APIStoreId"];
            if (dr.Table.Columns.Contains("LogoSmall") && dr["LogoSmall"] != DBNull.Value)
                user.LogoSmall = dr["LogoSmall"].ToString();
            if (dr.Table.Columns.Contains("LogoImage") && dr["LogoImage"] != DBNull.Value)
                user.LogoImage = dr["LogoImage"].ToString();

            user.StoreGroupName = dr["StoreGroupName"].ToString();
            user.Phone = dr["Mobile"].ToString();
            user.Password = dr["Password"].ToString();
            user.FullName = dr["FullName"].ToString();
            user.Address = dr["Address"].ToString();
            user.City = dr["City"].ToString();
            user.State = dr["State"].ToString();
            user.Country = dr["Country"].ToString();
            user.Photo = dr["Photo"].ToString();
            user.PasswordFormat = (PasswordFormat)dr["PasswordType"];
            user.PasswordSalt = dr["PasswordSalt"].ToString();
            user.Active = (bool)dr["Status"];
            if(dr["CreatedOn"] != DBNull.Value)
                user.CreatedOn= (DateTime)dr["CreatedOn"];
            if (dr["UpdatedOn"] != DBNull.Value)
                user.UpdatedOn = (DateTime)dr["UpdatedOn"];
            try
            {
                if (dr.Table.Columns.Contains("OwnBannerOnly") && dr["OwnBannerOnly"] != DBNull.Value)
                    user.OwnBannerOnly = (bool)dr["OwnBannerOnly"];
                else
                    user.OwnBannerOnly = false;
            }
            catch { }
            try
            {
                if (dr.Table.Columns.Contains("APIConnection") && dr["APIConnection"] != DBNull.Value)
                    user.APIConnection = dr["APIConnection"].ToString();
            }
            catch { }
            try
            {
                if (dr.Table.Columns.Contains("hasVerifiedEmail") && dr["hasVerifiedEmail"] != DBNull.Value)
                    user.HasVerifiedEmail = (bool)dr["hasVerifiedEmail"];
            }
            catch { }
            try
            {
                if (dr.Table.Columns.Contains("hasVerifiedMobile") && dr["hasVerifiedMobile"] != DBNull.Value)
                    user.MobileVerified = (bool)dr["hasVerifiedMobile"];
            }
            catch { }

            try
            {
                if (dr["host"] != DBNull.Value)
                    user.PublicSiteUrl = (string)dr["host"];
            }
            catch { }
            try
            {
                if (dr.Table.Columns.Contains("Stage") && dr["Stage"] != DBNull.Value)
                    user.TenantStage = (int)dr["Stage"];
            }
            catch { }
            try
            {
                if (dr.Table.Columns.Contains("TenantStatus") && dr["TenantStatus"] != DBNull.Value)
                    user.TenantStatus = Convert.ToInt32(dr["TenantStatus"]);
            }
            catch { }
            try
            {
                if (dr.Table.Columns.Contains("TenantType") && dr["TenantType"] != DBNull.Value)
                    user.TenantType = Convert.ToInt32(dr["TenantType"]);
            }
            catch { }
            try
            {
                if (dr.Table.Columns.Contains("PackageId") && dr["PackageId"] != DBNull.Value)
                    user.PackageId = Convert.ToInt32(dr["PackageId"]);
            }
            catch { }
            try
            {
                if (dr.Table.Columns.Contains("Theme") && dr["Theme"] != DBNull.Value)
                    user.Theme = dr["Theme"].ToString();
            }
            catch { }
            try
            {
                if (dr.Table.Columns.Contains("AreaId") && dr["AreaId"] != DBNull.Value)
                    user.AreaId = Convert.ToInt32(dr["AreaId"]);
            }
            catch { }
            try
            {
                if (dr.Table.Columns.Contains("FleetId") && dr["FleetId"] != DBNull.Value)
                    user.FleetId = Convert.ToInt32(dr["FleetId"]);
            }
            catch { }
            try
            {
                if (dr.Table.Columns.Contains("AnalyticsId") && dr["AnalyticsId"] != DBNull.Value)
                    user.AnalyticsId = Convert.ToInt32(dr["AnalyticsId"]);
            }
            catch { }

            try
            {
                if (dr.Table.Columns.Contains("CanCheckout") && dr["CanCheckout"] != DBNull.Value)
                    user.CanCheckout = Convert.ToBoolean(dr["CanCheckout"]);
            }
            catch { }
            try
            {
                if (dr.Table.Columns.Contains("OnlinePaymentEnabled") && dr["OnlinePaymentEnabled"] != DBNull.Value)
                    user.OnlinePaymentEnabled = Convert.ToBoolean(dr["OnlinePaymentEnabled"]);
            }
            catch { }
            try
            {
                if (dr.Table.Columns.Contains("PODEnabled") && dr["PODEnabled"] != DBNull.Value)
                    user.PODEnabled = Convert.ToBoolean(dr["PODEnabled"]);
            }
            catch { }
            try
            {
                if (dr.Table.Columns.Contains("HasPaymentMethod") && dr["HasPaymentMethod"] != DBNull.Value)
                    user.HasPaymentMethod = Convert.ToBoolean(dr["HasPaymentMethod"]);
            }
            catch { }
            try { 
                if (dr.Table.Columns.Contains("BranchId") && dr["BranchId"] != DBNull.Value)
                    user.APIRoleBranchId = Convert.ToInt32(dr["BranchId"]);
            } catch { }

            return user;
        }


        /// <summary>
        /// Validate customer
        /// </summary>
        /// <param name="usernameOrEmail">Username or email</param>
        /// <param name="password">Password</param>
        /// <returns>Result</returns>
        public static CustomerLoginResults ValidateCustomer(string usernameOrEmail, string password, out User user, bool isFedLogin = false, bool isSuccessOTPLogin=false, bool isEmail=true)
        {
            var customer = (!isEmail? GetCustomerByMobile(usernameOrEmail) : GetCustomerByEmail(usernameOrEmail));
            user = customer;
            if (customer == null)
                return CustomerLoginResults.CustomerNotExist;
            if (customer.Deleted)
                return CustomerLoginResults.Deleted;
            if (!customer.Active)
                return CustomerLoginResults.NotActive;
            if (!(customer.HasVerifiedEmail || customer.MobileVerified))
                return CustomerLoginResults.NotVerified;
            if (!customer.HasVerifiedEmail)
                return CustomerLoginResults.PendingEmailVerification;
            //only registered can login
            //if (!customer.IsRegistered())
            //    return CustomerLoginResults.NotRegistered;
            string pwd;
            switch (customer.PasswordFormat)
            {
                case PasswordFormat.Encrypted:
                    pwd = EncryptionService.EncryptText(password);
                    break;
                case PasswordFormat.Hashed:
                    pwd = EncryptionService.CreatePasswordHash(password, customer.PasswordSalt, HashedPasswordFormat);
                    break;
                default:
                    pwd = password;
                    break;
            }

            bool isValid = (isFedLogin || isSuccessOTPLogin || (!String.IsNullOrEmpty(pwd) && pwd == customer.Password));
            if (!isValid)
                return CustomerLoginResults.WrongPassword;

            //save last login date
            //customer.LastLoginDateUtc = DateTime.UtcNow;
            //_customerService.UpdateCustomer(customer);

            // since success login and if still email not verified then set is as verified because of the password will be available in email only.
            // This will avoid and unnecessary step to verify email again.
            if (!customer.HasVerifiedEmail)
            {
                //SetEmailVerified(customer.Id);
                //customer.HasVerifiedEmail = true;
            }

            return CustomerLoginResults.Successful;
        }

        public static void SetEmailVerified(int userid)
        {
            if (userid <=0)
                return;

            List<KeyValuePair<string, object>> param = new List<KeyValuePair<string, object>>();
            param.Add(new KeyValuePair<string, object>("userid", userid));
            string sql = "UPDATE [USER] SET hasVerifiedEmail=1 where Id=@userid";
            //DataTable dt = DataService.GetDataTable("SELECT u.*, (select top 1 HostAddress from Host where TenantId=u.StoreGroupId order by Id desc) as host, isnull(a.StoreId, -1) as APIStoreId, c.APIConnection, c.APIUrl, a.LogoSmall, a.LogoImage, a.OwnBannerOnly, a.Stage, a.Status as TenantStatus, a.TenantType, a.PackageId, a.Theme FROM [User] u left join AppTenant a on a.Id=u.StoreGroupId left join appconfig c on a.ApiID = c.Id WHERE u.Email like @email", parmeters: param);
            DataService.ExecuteSql(sql, parmeters: param);
        }

        public static string GetAPIConnectionString()
        {
            //if (CachedDefaultUser == null || String.IsNullOrEmpty(CachedDefaultUser.APIConnection))
                return ConfigurationManager.ConnectionStrings["mySqlConnection"].ConnectionString.Replace("{0}", ConfigurationManager.AppSettings["api.DefaultDB"]);

            //return CachedDefaultUser.APIConnection;

        }
        public static int UserRoleBranchId
        {
            get
            {
                User user = CachedDefaultUser;
                int brid = (user != null ? user.APIRoleBranchId : -1);
                if(user != null && user.APIRoleBranchId < -1)
                {
                    try
                    {
                        user.APIRoleBranchId = -1;
                        List<KeyValuePair<string, object>> param = new List<KeyValuePair<string, object>>();
                        param.Add(new KeyValuePair<string, object>("id", user.Id));
                        param.Add(new KeyValuePair<string, object>("storegroup", user.StoreGroupId));
                        DataTable dt = DataService.GetDataTable("select isnull(BranchId, -1) as BranchId from User_UserRole_Mapping WHERE UserId = @id and RoleId=8 and StoreGroupId=@storegroup", parmeters: param);
                        if (dt != null && dt.Rows.Count > 0)
                        {
                            brid = (int)dt.Rows[0]["BranchId"];
                            user.APIRoleBranchId = brid;
                        }
                        CachedDefaultUser = user;
                    }
                    catch { }
                }
                return brid;
            }
        }
        public static User CachedDefaultUser
        {
            get
            {

                User user = Infrastructure.PartnerContext.Current.User;
                
                //if (HttpContext.Current != null && HttpContext.Current.Session != null)
                //    try { if(HttpContext.Current.Session["CURUSER"] != null) user = (User)HttpContext.Current.Session["CURUSER"]; Infrastructure.PartnerContext.Current.User = user; } catch { } //HttpContext.Current.Cache.Get("CURUSER");
                //else if(HttpContext.Current != null && HttpContext.Current.Cache != null)
                //    try { if (HttpContext.Current.Cache["CURUSER"] != null) user = (User)HttpContext.Current.Cache["CURUSER"]; } catch { } //HttpContext.Current.Cache.Get("CURUSER");

                if (user == null || user.StoreGroupId < 1)
                {
                    user = UserService.GetCustomerByEmail(HttpContext.Current.User.Identity.Name);
                    Infrastructure.PartnerContext.Current.User = user;

                    //if (user != null && HttpContext.Current != null && HttpContext.Current.Session != null)
                    //{
                    //    HttpContext.Current.Session["CURUSER"] = new User
                    //    //HttpContext.Current.Cache["CURUSER"] = new User
                    //    {
                    //        FullName = user.FullName,
                    //        Email = user.Email,
                    //        Id = user.Id,
                    //        Phone = user.Phone,
                    //        StoreGroupId = user.StoreGroupId,
                    //        StoreGroupName = user.StoreGroupName,
                    //        CreatedOn = user.CreatedOn,
                    //        APIStoreId = user.APIStoreId,
                    //        APIConnection = user.APIConnection,
                    //        LogoSmall = user.LogoSmall,
                    //        LogoImage = user.LogoImage,
                    //        OwnBannerOnly = user.OwnBannerOnly,
                    //        PublicSiteUrl = user.PublicSiteUrl,
                    //        TenantStage = user.TenantStage,
                    //        TenantStatus = user.TenantStatus,
                    //        HasVerifiedEmail = user.HasVerifiedEmail,
                    //        TenantType = user.TenantType,
                    //        PackageId = user.PackageId
                    //    };

                        
                    //}

                }

                //User user = FormsAuthenticationService.GetAuthenticatedCustomer();
                if (user != null)
                    return user;

                return new User();
            }
            set
            {
                Infrastructure.PartnerContext.Current.User = value;
                //if (HttpContext.Current != null && HttpContext.Current.Session != null)
                //    HttpContext.Current.Session.Remove("CURUSER");
                //HttpContext.Current.Cache.Remove("CURUSER");

                //if (value != null && HttpContext.Current != null && HttpContext.Current.Session != null)
                //    HttpContext.Current.Session["CURUSER"]= new User
                //    //HttpContext.Current.Cache["CURUSER"] = new User
                //    {
                //        FullName = value.FullName,
                //        Phone = value.Phone,
                //        Id=value.Id,
                //        Email = value.Email,
                //        StoreGroupId = value.StoreGroupId,
                //        StoreGroupName = value.StoreGroupName,
                //        CreatedOn = value.CreatedOn,
                //        APIStoreId = value.APIStoreId,
                //        APIConnection = value.APIConnection,
                //        LogoSmall = value.LogoSmall,
                //        LogoImage = value.LogoImage,
                //        OwnBannerOnly = value.OwnBannerOnly,
                //        PublicSiteUrl=value.PublicSiteUrl,
                //        TenantStage = value.TenantStage,
                //        TenantStatus = value.TenantStatus,
                //        HasVerifiedEmail=value.HasVerifiedEmail,
                //        TenantType = value.TenantType,
                //        PackageId = value.PackageId
                //    };
            }
        }

        //internal static int CreateStoreGroup(string text, int v1, string v2)
        //{
        //    throw new NotImplementedException();
        //}
    }

    public enum PasswordFormat
    {
        Clear = 1,
        Hashed = 2,
        Encrypted = 3,
        Temporary = 4
    }
}