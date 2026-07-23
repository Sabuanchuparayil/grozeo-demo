using Retaline.Core.BusinessModel.ScheduleTasks;
using Retaline.Core.Services.Caching;

namespace Retaline.Core.Services.ScheduleTasks.Caching
{
    /// <summary>
    /// Represents a schedule task cache event consumer
    /// </summary>
    public partial class ScheduleTaskCacheEventConsumer : CacheEventConsumer<ScheduleTask>
    {
    }
}
