using Microsoft.AspNetCore.Mvc;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using Retaline.Core.Services.Info;
using Retaline.Core.BusinessModel.InfoPages;
using Microsoft.Extensions.Configuration;
using Retaline.Core.Utilities;
using Microsoft.IdentityModel.Tokens;
using SaasKit.Multitenancy;

namespace Retaline.Web.Controllers
{
    public class InfoController : Controller
    {
        IPageService _pageService;
        private readonly IConfiguration _configuration;
        private readonly Core.ViewModel.Tenant.AppTenant _tenant;

        public InfoController(IPageService pageService, IConfiguration configuration, SaasKit.Multitenancy.ITenant<Core.ViewModel.Tenant.AppTenant> tenant)
        {
            _pageService = pageService;
            _configuration = configuration;
            this._tenant = tenant?.Value;
        }

        public IActionResult Index()
        {
            return View();
        }

        [Route("privacy")]
        public IActionResult Privacy()
        {
            var page = _pageService.GetPage(2).Result;
            return View(page);
        }

        [Route("terms-conditions")]
        public IActionResult Terms()
        {
            var page = _pageService.GetPage(3).Result;
            return View(page);

        }

        [Route("about-us")]
        public IActionResult About()
        {
            var page = _pageService.GetPage(1).Result;
            return View(page);
        }
        [Route("about-us-mini")]
        public IActionResult AboutMin()
        {
            try
            {
                var page = _pageService.GetPage(1).Result;
                if (page != null && !String.IsNullOrEmpty(page.Content))
                    return Json(new { status = 1, content = Service.Common.ShrinkText(Service.Common.FormatContentWithPlaceholder(page.Content, _tenant), 500) });
            }
            catch(Exception ex)
            {
                return Json(new { status = 0, content = ex.Message });
            }
            return Json(new { status = 0, content = "" });
        }

        [Route("how-it-works")]
        public IActionResult HowItWorks()
        {
            var page = _pageService.GetPage(4).Result;
            return View(page);
        }
        [Route("return-refund-policy")]
        public IActionResult ReturnRefundPolicy()
        {
            var page = _pageService.GetPage(6).Result;
            return View(page);
        }
        [Route("faq")]
        public async Task<IActionResult> FAQ()
        {
            //var page = _pageService.GetPage(5).Result;
            var faq = await _pageService.GetFAQ();

            return View(faq);
        }

        [Route("contact")]
        public IActionResult ContactUs()
        {
            //var page = _pageService.GetPage(5).Result;
            return View("Contact");
        }

        [HttpPost]
        [Route("contactus-submit")]
        public async Task<IActionResult> ContactUsSubmit([FromBody] Models.Info.Contact details)
        {
            bool isInternaCRM = true; try {isInternaCRM = Convert.ToBoolean(_configuration["ApiUrls:Info:InternaCRM"]); } catch { isInternaCRM = true; }
            if (!isInternaCRM)
            {
                if (!String.IsNullOrEmpty(details.OrderId) && !String.IsNullOrEmpty(details.OrderNum))
                {
                    var result = await _pageService.OrderHelpSubmit(details.Email, details.Phone, details.Message, details.OrderId, details.OrderNum, details.BranchName, details.OrderDate);
                }
                else
                {
                    var result = await _pageService.ContactSubmit(details.Email, details.Phone, details.Message);
                }
            }
            else
            {
                var result = await _pageService.SubmitFeedback(details.Phone, details.Email, details.Message + $"{(!String.IsNullOrEmpty(details.OrderId) && !String.IsNullOrEmpty(details.OrderNum) ? ", Order Info: Order number: " + details.OrderNum + ", Branch: " + details.BranchName + ", Order Date: " + details.OrderDate : "")}");
            }
            return Json(new { status = 1, message = "Thanks. We'll contact you shortly." });
        }

        [Route("install/{devise?}")]
        public IActionResult Install(string devise = "")
        {
            return View("Install", devise);
        }

        [Route("page-not-found")]
        public IActionResult PageNotFound()
        {
            return View();
        }
        [Route("bad-request")]
        public IActionResult BadRequest()
        {
            return View();
        }

        [Route("manifest")]
        public JsonResult Manifest()
        {
            string _name = (_tenant != null && !String.IsNullOrEmpty(_tenant.Name) ? _tenant.Name : "Grozeo");
            string strthemeLogo = "", strthemeLogoSmall = "";
            try
            {
                if (_tenant != null && _tenant != null)
                {
                    if (!String.IsNullOrEmpty(_tenant.LogoImage))
                        strthemeLogo = _tenant.LogoImage;

                    if (!String.IsNullOrEmpty(_tenant.LogoSmall))
                        strthemeLogoSmall = _tenant.LogoSmall;
                }
            }
            catch { }

            if (!String.IsNullOrEmpty(strthemeLogo) && String.IsNullOrEmpty(strthemeLogoSmall))
                strthemeLogoSmall = strthemeLogo;
            if (String.IsNullOrEmpty(strthemeLogo) && !String.IsNullOrEmpty(strthemeLogoSmall))
                strthemeLogo = strthemeLogoSmall;

            // still blank?
            if (String.IsNullOrEmpty(strthemeLogo))
                strthemeLogo = "/images/LOGO.svg";
            if (String.IsNullOrEmpty(strthemeLogoSmall))
                strthemeLogoSmall = "/images/Footer_Logo.svg";

            return new JsonResult(new {
                name = _name.Replace(" ", ""),
                short_name = _name.Replace(" ", "").ToLower(),
                start_url = "/",
                display = "standalone",
                scope = "/",
                background_color = "#fff",
                theme_color = "#7CBF21",
                orientation = "any",
                description = "Your convenient hypermarket",
                icons = new[] { new {
                        src= strthemeLogoSmall,
                        sizes= "96x96",
                        type= "image/svg+xml"
                    },
                    new {
                        src= strthemeLogoSmall,
                        sizes= "144x144 192x192",
                        type= "image/svg+xml"
                    },
                    new {
                        src= strthemeLogoSmall,
                        sizes= "256x256 384x384",
                        type= "image/svg+xml"
                    },
                    new {
                        src= strthemeLogoSmall,
                        sizes= "512x512",
                        type= "image/svg+xml"
                    }
                },
                shortcuts = new[] { new {
                        name= "Special offers",
                        short_name= "Offers",
                        description = "View special offers for you",
                        url= "/offers",
                        icons= new[]{ new {
                                src= strthemeLogoSmall,
                                sizes= "96x96",
                                type= "image/svg+xml"
                            }
                        }
                    },
                    new {
                        name= "Search Products",
                        short_name= "Search",
                        description = "Search your desired products",
                        url= "/search",
                        icons= new[]{ new {
                                src= strthemeLogoSmall,
                                sizes= "96x96",
                                type= "image/svg+xml"
                            }
                        }
                    }
                },
                screenshots = new[] { new {
                        src= strthemeLogo,
                        sizes= "512x512",
                        type= "image/svg+xml"
                    }
                },
                categories = new string[] { "shopping", "lifestyle", "food", "kids", "utilities" }
            });
        }

    }
}
