using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using log4net;

namespace RetalineProAgent.Base
{
    public partial class BasePartnerPage : Page
    {
        private static readonly ILog log = LogManager.GetLogger(typeof(BasePartnerPage));
        protected Service.User CurrentUser
        {
            get
            {
                if (_curuser != null)
                    return _curuser;

                _curuser = Infrastructure.PartnerContext.Current.User??Service.UserService.CachedDefaultUser;
                return _curuser ?? default;
            }
        }
        private Service.User _curuser = null;

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
    }
}