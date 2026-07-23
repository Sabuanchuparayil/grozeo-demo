using ODOCart.Core.BussinessModel.UserDetails;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace ODOCart.Core.Services.HelperServices
{
    public interface IHttpHelperService
    {
        Task<T> Get<T>(string uri, object value = null);
         //Task<T> Post<T>(string uri, List<KeyValuePair<string, string>> cnt, int type = 0);
       Task<T> Post<T>(string uri, object value);
    }
}
