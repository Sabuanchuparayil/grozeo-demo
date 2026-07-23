using Retaline.Core.Caching;

namespace Retaline.Core.Services.Common
{
    /// <summary>
    /// Represents default values related to common services
    /// </summary>
    public static partial class RetalineCommonDefaults
    {

        /// <summary>
        /// Gets a name of generic attribute to store the value of 'EuCookieLawAccepted'
        /// </summary>
        public static string EuCookieLawAcceptedAttribute => "EuCookieLaw.Accepted";

        #region Caching defaults

        #region Generic attributes

        /// <summary>
        /// Gets a key for caching
        /// </summary>
        /// <remarks>
        /// {0} : entity ID
        /// {1} : key group
        /// </remarks>
        public static CacheKey GenericAttributeCacheKey => new("Retaline.genericattribute.{0}-{1}");

        #endregion

        #endregion
    }
}
