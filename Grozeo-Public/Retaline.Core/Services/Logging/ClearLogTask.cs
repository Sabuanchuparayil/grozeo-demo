using System;
using Retaline.Core.Services.Logging;
//using Retaline.Core.BusinessModel.Common;
using Retaline.Core.Services.ScheduleTasks;

namespace Retaline.Core.Services.Logging
{
    /// <summary>
    /// Represents a task to clear [Log] table
    /// </summary>
    public partial class ClearLogTask : IScheduleTask
    {
        #region Fields

        private readonly ILogger _logger;
        private readonly double _logRetentionDays = 10;
        #endregion

        #region Ctor

        public ClearLogTask(ILogger logger)
        {
            _logger = logger;
        }

        #endregion

        #region Methods

        /// <summary>
        /// Executes a task
        /// </summary>
        public virtual async System.Threading.Tasks.Task ExecuteAsync()
        {
            var utcNow = DateTime.UtcNow;
            
            await _logger.ClearLogAsync(utcNow.AddDays(_logRetentionDays));
        }

        #endregion
    }
}