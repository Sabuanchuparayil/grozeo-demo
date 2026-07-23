using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.Services.Finance
{
    public class Result
    {
        /// <summary>
        /// Result id
        /// </summary>
        public ResultType statusId;
        /// <summary>
        /// Message Description
        /// </summary>
        public string message;
        /// <summary>
        /// Reference Id - optional
        /// </summary>
        public string refId;
    }

    public enum ResultType
    {
        Success = 1,
        Failed = 2,
        Error = 3
    }
}
