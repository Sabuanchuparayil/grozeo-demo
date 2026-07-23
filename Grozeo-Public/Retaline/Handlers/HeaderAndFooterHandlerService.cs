using Retaline.Core.ViewModel.Home;

namespace Retaline.Web.Handlers
{
    public class HeaderAndFooterHandlerService : IHeaderAndFooterHandlerService
    {
        public HeaderAndFooterViewModel GetHeaderAndFooterContent()
        {
            HeaderAndFooterViewModel homePageContent = new();
            
            return homePageContent;
        }

    }
}
