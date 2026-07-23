using ODOCart.Core.BussinessModel.API;
using ODOCart.Core.BussinessModel.UserDetails;
using ODOCart.Core.ViewModel.Authentication;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace ODOCart.Core.Services.Authentication
{
    public interface ICustomAuthenticationService
    {
        //Task<GuestData> GetGuestUser();
        Task<Dictionary<string, string>> GetOtp(string mobile, string url);
        Task<UserDetailsFromApi> VerifyOtp(VerifyUserViewModel details, string url);
        Task CreateAuthenticationTicket(User user);
        User GetUserFromClaims();
        Task<APIModel<User>> SignUp(RegistrationViewModel details, string url);
    }
}
