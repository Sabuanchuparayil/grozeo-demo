using Retaline.Core.BusinessModel.UserDetails;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace Retaline.Core.Services.HelperServices
{
    public interface IHttpHelperService
    {
        /// <summary>
        /// GET Method.
        /// </summary>
        /// <typeparam name="T"></typeparam>
        /// <param name="uri"></param>
        /// <param name="value"></param>
        /// <returns></returns>
        Task<T> Get<T>(string uri, object value = null, int customStoreGroupId = -1, int customBranchId = -1);
        //Task<T> Post<T>(string uri, List<KeyValuePair<string, string>> cnt, int type = 0);
        /// <summary>
        /// POST Method.
        /// </summary>
        /// <typeparam name="T"></typeparam>
        /// <param name="uri"></param>
        /// <param name="value"></param>
        /// <returns></returns>
        Task<T> Post<T>(string uri, object value, int customStoreGroupId = -1, int customBranchId = -1);
        /// <summary>
        /// PUT Method.
        /// </summary>
        /// <typeparam name="T"></typeparam>
        /// <param name="uri"></param>
        /// <param name="value"></param>
        /// <returns></returns>
        Task<T> Put<T>(string uri, object value, int customStoreGroupId = -1, int customBranchId = -1);
        /// <summary>
        /// DELETE  Method.
        /// </summary>
        /// <typeparam name="T"></typeparam>
        /// <param name="uri"></param>
        /// <param name="value"></param>
        /// <returns></returns>
        Task<T> Delete<T>(string uri, object value, int customStoreGroupId = -1, int customBranchId = -1);
        /// <summary>
        /// Current guest user.
        /// </summary>
        GuestData GuestUser { get; }

        void SetGuestLocation(double lat, double lng, string guestLocality);
    }
}
