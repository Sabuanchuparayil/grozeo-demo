using Microsoft.AspNetCore.Mvc;
using Retaline.Core.BusinessModel.UserDetails;
using Retaline.Core.Services.ProfileManagement;
using System.Collections.Generic;
using System.Linq;


namespace Retaline.Web.Views.Shared.Components.ConfirmAddressModelViewComponent
{
    public class ConfirmAddressModelViewComponent : ViewComponent
    {
        private readonly IProfileService _profileService;

        public ConfirmAddressModelViewComponent(IProfileService profileService)
        {
            _profileService = profileService;
        }
        public IViewComponentResult Invoke(int addressCount)
        {
            var details = _profileService.GetAddress().Result;
            if (details != null && details.Data != null)
            {
                return View("ConfirmAddressModel", details.Data.OrderByDescending(a => a.IsPrimary).ThenByDescending(a => a.BranchId).ToList());
            }
            return View("ConfirmAddressModel", new List<Address>());

        }
    }
}
