using System;
using System.Collections.Generic;
using System.Text;


namespace DataEntry
{
    /// <summary>
    /// Result object of financial transation
    /// </summary>
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
        Error = 3,
        Exception = 4,
        NoData = 5,
        Undefined = 100,
    }
}
