using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace RetalineProAgent.Infrastructure
{
    public partial class PartnerSession
    {
        #region Fields
        private RetalineProAgent.Service.User _partner;
        #endregion 

        #region Properties
        /// <summary>
        /// Gets or sets the customer session identifier
        /// </summary>
        public Guid PartnerSessionGuid { get; set; }

        /// <summary>
        /// Gets or sets the Partner identifier
        /// </summary>
        public int PartnerId { get; set; }

        /// <summary>
        /// Gets or sets the last accessed date and time
        /// </summary>
        public DateTime LastAccessed { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether the Partner session is expired
        /// </summary>
        public bool IsExpired { get; set; }
        #endregion

        #region Custom Properties
        /// <summary>
        /// Gets or sets the Partner
        /// </summary>
        public RetalineProAgent.Service.User Partner
        {
            get
            {
                if (_partner == null)
                    _partner = RetalineProAgent.Service.UserService.GetCustomerById(this.PartnerId); //IoC.Resolve<ICustomerService>().GetCustomerById(this.CustomerId);
                return _partner;
            }
        }
        #endregion

    }
}