using Retaline.Core.BusinessModel.API;
using Retaline.Core.BusinessModel.Captcha;
using Retaline.Core.BusinessModel.UserDetails;
using Retaline.Core.ViewModel.Authentication;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace Retaline.Core.Services.Authentication
{
    public interface ICustomAuthenticationService
    {
        //Task<GuestData> GetGuestUser();
        Task<Dictionary<string, string>> GetOtp(string inputData, string url, int usePsw = 1, int type = 0);
        Task<UserDetailsFromApi> VerifyOtp(VerifyUserViewModel details, string url);
        Task CreateAuthenticationTicket(User user);
        User GetUserFromClaims();
        Task<APIModel<User>> SignUpCustomer(RegistrationViewModel details, string url);
        Task<APIModel<User>> SignUp(ViewModel.Address.AddressViewModel details, string url, string refCode);
        int GetBranchId();
        Task<string> ImpersonateUserById(string userId);

		Task<string> ImpersonateUser(string mobile);
        Task<string> ExitImpersonation();
        Task<CaptchaResponse> VerifyToken(string token);
        Task<User> GetAuthenticatedCustomerAsync();
        Task<UserDetailsFromApi> GetUserFromExternalAuth(string source, string code);
        Task<UserDetailsFromApi> VerifyPassword(VerifyUserPswViewModel inputData);
        Task <string>ConfirmLegalAge(int verify);

    }
}
