using Microsoft.AspNetCore.Authentication.JwtBearer;
using Microsoft.AspNetCore.Builder;
using Microsoft.AspNetCore.Hosting;
using Microsoft.AspNetCore.Http;
using Microsoft.AspNetCore.Mvc.Razor;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.DependencyInjection.Extensions;
using Microsoft.Extensions.FileProviders;
using Microsoft.Extensions.Hosting;
using Retaline.Core;
using Retaline.Core.Caching;
using Retaline.Core.Configuration;
using Retaline.Core.Infra;
using Retaline.Core.Services.Authentication;
using Retaline.Core.Services.Caching;
using Retaline.Core.Services.Cart;
using Retaline.Core.Services.Catalog;
using Retaline.Core.Services.Checkout;
using Retaline.Core.Services.Common;
using Retaline.Core.Services.HelperServices;
using Retaline.Core.Services.Home;
using Retaline.Core.Services.Order;
using Retaline.Core.Services.ProductDetails;
using Retaline.Core.Services.ProfileManagement;
using Retaline.Core.Services.Wishlist;
using Retaline.Core.ViewModel.Tenant;
using Retaline.Web.Framework;
using Retaline.Web.Handlers;
using Retaline.Web.Service;
using Retaline.Web.Service.Tenant;
using System;
using System.IO;
using System.Text;
using Microsoft.AspNetCore.HttpOverrides;

namespace Retaline.Web
{
	public class Startup
	{
		//public IConfiguration Configuration { get; }
		private readonly IConfiguration _configuration;
		private readonly IWebHostEnvironment _webHostEnvironment;

		public Startup(IConfiguration configuration, IWebHostEnvironment webHostEnvironment)
		{
			//Configuration = configuration;
			_configuration = configuration;
			_webHostEnvironment = webHostEnvironment;
		}
		//public string AzureDataProtectionKeyFile => "DataProtectionKeys.xml";
		//public string DataProtectionKeysPath => "~/DataProtectionKeys";


		// This method gets called by the runtime. Use this method to add services to the container.
		public void ConfigureServices(IServiceCollection services)
		{
			services.AddResponseCompression(options =>
			{
				options.EnableForHttps = true;
			});
			services.ConfigureApplicationServices(_configuration, _webHostEnvironment);
			services.AddMultitenancy<AppTenant, CachingAppTenantResolver>();

			services.AddControllersWithViews().AddRazorRuntimeCompilation();
			services.AddRazorPages();
			services.AddDistributedMemoryCache();

			//services.AddDataProtection().SetApplicationName("ODOCart").ProtectKeysWithCertificate("thumbprint");
			//services.AddSingleton<IXmlReposity, CustomDataProtectionRepository>();
			services.AddSingleton<IConfiguration>(_configuration);
			services.AddScoped<IHttpHelperService, HttpHelperService>();
			services.AddScoped<ICustomAuthenticationService, CustomAuthenticationService>();
			services.AddScoped<ICatalogService, CatalogService>();
			services.AddScoped<IHomePageService, HomePageService>();
			services.AddScoped<IProfileService, ProfileService>();
			services.AddScoped<ICartService, CartService>();
			services.AddScoped<IProductService, ProductService>();
			services.AddScoped<IWishlistService, WishlistService>();
			services.AddScoped<IOrderService, OrderService>();
			services.AddScoped<IDBService, DBService>();

			services.AddScoped<IHeaderAndFooterHandlerService, HeaderAndFooterHandlerService>();
			services.AddScoped<IHomePageHandlerService, HomePageHandlerService>();
			services.AddScoped<ICheckoutService, CheckoutService>();
			services.AddScoped<IProductDetailsService, ProductDetailsService>();
			services.AddScoped<Retaline.Core.Services.Info.IPageService, Retaline.Core.Services.Info.PageService>();
			services.AddScoped<ICustomHomeAndCategoryService, CustomHomeAndCategoryService>();


			services.Configure<RazorViewEngineOptions>(options =>
			{
				options.ViewLocationExpanders.Add(new TenantViewLocationExpander());
			});

			services.Configure<MultitenancyOptions>(_configuration.GetSection("Multitenancy"));

			var key = Encoding.ASCII.GetBytes(_configuration["Token:SecretKey"]);

			services.AddSession(options =>
			{
				options.IdleTimeout = TimeSpan.FromMinutes(60);
			});

            services.AddHttpClient();

            if (!String.IsNullOrEmpty(_configuration["DistributedCacheConfig:ConnectionString"]))
			{
				var distributedCacheConfig = _configuration.GetSection("DistributedCacheConfig");
				services.AddScoped<ILocker, RedisCacheManager>();
				services.AddScoped<IStaticCacheManager, RedisCacheManager>();

				services.AddStackExchangeRedisCache(options =>
				{
					options.Configuration = _configuration["DistributedCacheConfig:ConnectionString"];
				});
			}
			else
			{
				services.AddScoped<ILocker, MemoryDistributedCacheManager>();
				services.AddScoped<IStaticCacheManager, MemoryDistributedCacheManager>();

			}

			services.AddScoped<IStoreContext, WebStoreContext>();
			services.AddScoped<IGenericAttributeService, GenericAttributeService>();

			var appSettings = Singleton<AppSettings>.Instance;
			//var distributedCacheConfig = appSettings.Get<DistributedCacheConfig>();


			var authenticationBuilder = services.AddAuthentication(auth =>
			{
				//auth.DefaultAuthenticateScheme = JwtBearerDefaults.AuthenticationScheme;
				auth.DefaultChallengeScheme = JwtBearerDefaults.AuthenticationScheme;
				auth.DefaultScheme = JwtBearerDefaults.AuthenticationScheme;
				auth.DefaultSignInScheme = JwtBearerDefaults.AuthenticationScheme;
			});

			authenticationBuilder.AddCookie(JwtBearerDefaults.AuthenticationScheme, options =>
			{
				options.LoginPath = "/";
				options.Cookie.SameSite = SameSiteMode.Lax;
				options.Cookie.SecurePolicy = CookieSecurePolicy.Always;

			});
			//services.ConfigureApplicationCookie(options => {
			//    options.Cookie.SameSite = SameSiteMode.None;

			//});
			services.AddAntiforgery(o => o.SuppressXFrameOptionsHeader = true);

			services.AddHttpContextAccessor();
			services.AddCors();
			services.TryAddSingleton<Microsoft.AspNetCore.Mvc.Infrastructure.IActionContextAccessor, Microsoft.AspNetCore.Mvc.Infrastructure.ActionContextAccessor>();

			var engine = EngineContext.Create();

			engine.ConfigureServices(services, _configuration);

			services.AddAuthorization(options =>
			{
				//var simplyAuthenticated = new AuthorizationPolicy(new DenyAnonymousAuthorizationRequirementOverrideOthers().Yield(), new List<string>());
				//options.AddPolicy(name: PolicyConsts.AuthenticatedOnlyOverridePolicyName, policy: simplyAuthenticated);
				options.AddPolicy(name: "HavingPrimaryAddress", configurePolicy: policy => policy.RequireAssertion(e =>
				{
					//if (e.Resource is AuthorizationFilterContext afc)
					//{
					//    var noPolicy = afc.Filters.OfType<AuthorizeFilter>().Any(p => p.Policy.Requirements.Count == 1 && p.Policy.Requirements.Single() is DenyAnonymousAuthorizationRequirementOverrideOthers);
					//    if (noPolicy)
					//        return true;
					//}
					return true; //e.User.IsInRole(Consts.Admin);
				}));

			});

		}

		// This method gets called by the runtime. Use this method to configure the HTTP request pipeline.
		public void Configure(IApplicationBuilder app, IWebHostEnvironment env)
		{
            var options = new ForwardedHeadersOptions
            {
                ForwardedHeaders = ForwardedHeaders.XForwardedFor | ForwardedHeaders.XForwardedHost
            };

            // TODO: Configure KnownProxies / KnownNetworks with production reverse-proxy IPs (e.g. Azure Front Door)
            // so forwarded headers are only applied from trusted proxies.

            app.UseForwardedHeaders(options);

            app.UseCors(
				options => options.WithOrigins(new string[] { "https://test.instamojo.com", "'https://footprints-staging.instamojo.com'" }).AllowAnyMethod()
			);
			app.UseResponseCompression();
			if (env.IsDevelopment())
			{
				app.UseDeveloperExceptionPage();
			}
			else
			{
				//app.Use(async (context, next) =>
				//{
				//    await next.Invoke();

				//    //After going down the pipeline check if we 404'd. 
				//    if (context.Response.StatusCode == StatusCodes.Status404NotFound)
				//    {
				//        string originalPath = context.Request.Path.Value;
				//        context.Items["originalPath"] = originalPath;
				//        context.Request.Path = "/page-not-found";
				//        await next();
				//        //await context.Response.WriteAsync("Woops! We 404'd");
				//    }
				//    else if(context.Response.StatusCode == StatusCodes.Status500InternalServerError)
				//    {
				//        string originalPath = context.Request.Path.Value;
				//        context.Items["originalPath"] = originalPath;
				//        context.Request.Path = "/internalerrorpage.html";
				//        await next();

				//    }
				//    else if (context.Response.StatusCode == StatusCodes.Status400BadRequest)
				//    {
				//        string originalPath = context.Request.Path.Value;
				//        context.Items["originalPath"] = originalPath;
				//        context.Request.Path = "/bad-request";
				//        await next();
				//    }
				//});
				//app.UseResponseCompression();
				app.UseExceptionHandler("/internalerrorpage.html");
				app.UseStatusCodePagesWithRedirects("/Error/{0}");

				//app.UseExceptionHandler("/Home/Error");
				// The default HSTS value is 30 days. You may want to change this for production scenarios, see https://aka.ms/aspnetcore-hsts.
				app.UseHsts();
			}


			if (!env.IsDevelopment())
			{
				app.UseHttpsRedirection();
			}
			app.UseStaticFiles();
			if (Directory.Exists(Path.Combine(env.ContentRootPath, "Themes")))
				app.UseStaticFiles(new StaticFileOptions
				{
					FileProvider = new PhysicalFileProvider(Path.Combine(env.ContentRootPath, "Themes")),
					RequestPath = "/Themes"
					//FileProvider = new PhysicalFileProvider(Path.Combine(env.WebRootPath, "Themes")),
					//RequestPath = new PathString($"/Themes")
				});

			string paymentGateway = _configuration["PaymentGateway"];
			var wellKnownPath = Path.Combine(Directory.GetCurrentDirectory(), "wwwroot", ".well-known");
			if (paymentGateway == "revolut" && Directory.Exists(wellKnownPath))
			{
				app.UseStaticFiles(new StaticFileOptions
				{
					FileProvider = new PhysicalFileProvider(
						Path.Combine(Directory.GetCurrentDirectory(), "wwwroot", ".well-known")),
					RequestPath = "/.well-known",
					ServeUnknownFileTypes = true // Allows files without extensions to be served
				});
			}

			app.UseRouting();
			app.UseMultitenancy<AppTenant>();
			app.UseCookiePolicy();
			app.UseSession();
			app.Use(async (context, next) =>
			{
				var JWToken = context.Session.GetString("JWToken");
				if (!string.IsNullOrEmpty(JWToken))
				{
					context.Request.Headers.Add("Authorization", "Bearer " + JWToken);
				}
				await next();
			});
			app.UseAuthentication();
			app.UseAuthorization();
			//app.UseFileServer(); // Uncomment to enable index.html as default page.
			app.UseEndpoints(endpoints =>
			{
                // This maps controllers that use attribute routing
                endpoints.MapControllers();

                // This maps the conventional default route for other controllers (e.g., Home/Index)
                endpoints.MapControllerRoute(
					name: "default",
					pattern: "{controller=Home}/{action=Index}/{id?}");
			});
		}


	}
}
