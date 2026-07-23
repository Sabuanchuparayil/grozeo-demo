using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.Optimization;
using System.Web.Routing;
using System.Web.Security;
using System.Web.SessionState;
using System.Web.Http;
using log4net.Config;
using log4net;

namespace RetalineProAgent
{
    public class Global : HttpApplication
    {
        private static readonly ILog log = LogManager.GetLogger(typeof(Global));
        void Application_Start(object sender, EventArgs e)
        {
            // Code that runs on application startup
            RouteConfig.RegisterRoutes(RouteTable.Routes);
            BundleConfig.RegisterBundles(BundleTable.Bundles);

            //RouteTable.Routes.MapHttpRoute(
            //    name: "DefaultApi",
            //    routeTemplate: "api/{controller}/{id}",
            //    defaults: new { id = System.Web.Http.RouteParameter.Optional }
            //);
            RouteTable.Routes.MapHttpRoute(
                name: "DefaultApi",
                routeTemplate: "api/{controller}/{action}/{id}",
                defaults: new { id = RouteParameter.Optional }
            );
            XmlConfigurator.Configure(new System.IO.FileInfo(Server.MapPath("~/log4net.config")));
        }

        protected void Application_Error(object sender, EventArgs e)
        {
            // Get the last error
            Exception ex = Server.GetLastError();

            // Log the error
            log.Error("Unhandled exception occurred", ex);

            // Clear the error
            Server.ClearError();

            // Redirect to an error page if needed
            // Response.Redirect("~/ErrorPage.aspx");
        }

    }
}