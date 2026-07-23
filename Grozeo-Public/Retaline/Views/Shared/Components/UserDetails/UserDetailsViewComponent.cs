using Microsoft.AspNetCore.Mvc;
using Retaline.Core.BusinessModel.UserDetails;
using Retaline.Core.Services.Authentication;

namespace Retaline.Web.Views.Shared.Components.UserDetails
{
    public class UserDetailsViewComponent : ViewComponent
    {
        private readonly ICustomAuthenticationService _customAuthenticationService;
        public UserDetailsViewComponent(ICustomAuthenticationService customAuthenticationService)
        {
            _customAuthenticationService = customAuthenticationService;
        }
        public IViewComponentResult Invoke()
        {
            User user = new();
            try
            {
                user = _customAuthenticationService.GetUserFromClaims();
                if (user == null)
                    user = new User();
            }
            catch
            {
                user = new User();
            }
            return View(user);
        }
    }
}
