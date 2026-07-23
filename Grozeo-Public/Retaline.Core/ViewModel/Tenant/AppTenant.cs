namespace Retaline.Core.ViewModel.Tenant
{
    public class AppTenant
    {
        public int Id { get; set; }
        public string Name { get; set; }
        public string[] Hostnames { get; set; }
        public string Theme { get; set; }
        public string APIUrl { get; set; }
        public bool CanCheckout { get; set; }
        public bool OnlinePaymentEnabled { get; set; }
        public string StoreId { get; set; }
        public int Status { get; set; }
        public bool ShowPWA { get; set; }
        public string LogoImage { get; set; }
        public string CustomColor { get; set; }
        public string LogoSmall { get; set; }
        public string FavIcoImage { get; set; }
        public string PaymentGateway { get; set; }
        public int Stage { get; set; }
        public bool OwnBannerOnly { get; set; }
        public bool EnableAnalytics { get; set; }
        public string Address { get; set; }
        public string ContactEmail { get; set; }
        public string ContactPhone { get; set; }
        public string AnalyticsId { get; set; }
        public bool PODEnabled { get; set; }

        public string SM_FB {  get; set; }
        public string SM_Twiter { get; set; }
        public string SM_Insta { get; set;}
        public string SM_WP { get; set; }
        public string SM_Other { get; set; }
        public string AppUrlAndroid { get; set; }
        public string AppUrlIOS { get; set;}
    }
}
