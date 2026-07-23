using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;

namespace RetalineProAgent.Base
{
    public partial class BasePartnerMasterPage : MasterPage
    {
        
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
    }
}