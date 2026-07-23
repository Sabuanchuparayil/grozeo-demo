using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Retaline.Core.BusinessModel.InfoPages;
namespace Retaline.Core.Services.Info
{
    public interface IPageService
    {
        Task<List<Page>> GetPage();
        Task<Page> GetPage(int pageId);
        Task<string> ContactSubmit(string email, string phone, string message);
        Task<object> SubmitFeedback(string phone, string email, string msg);
        Task<string> OrderHelpSubmit(string email, string phone, string message, string orderId, string orderNum, string branch, string orderdate);
        Task<List<FaqContent>> GetFAQ();
        Task<string> GetAboutUsMiniContent(int maxSize);
    }
}
