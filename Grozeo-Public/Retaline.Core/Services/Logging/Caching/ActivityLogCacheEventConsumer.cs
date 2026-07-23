using Retaline.Core.BusinessModel.Logging;
using Retaline.Core.Services.Caching;

namespace Retaline.Core.Services.Logging.Caching
{
    /// <summary>
    /// Represents an activity log cache event consumer
    /// </summary>
    public partial class ActivityLogCacheEventConsumer : CacheEventConsumer<ActivityLog>
    {
    }
}