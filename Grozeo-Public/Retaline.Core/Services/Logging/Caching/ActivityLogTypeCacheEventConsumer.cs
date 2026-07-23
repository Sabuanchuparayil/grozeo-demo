using Retaline.Core.BusinessModel.Logging;
using Retaline.Core.Services.Caching;
using System.Threading.Tasks;

namespace Retaline.Core.Services.Logging.Caching
{
    /// <summary>
    /// Represents a activity log type cache event consumer
    /// </summary>
    public partial class ActivityLogTypeCacheEventConsumer : CacheEventConsumer<ActivityLogType>
    {
    }
}
