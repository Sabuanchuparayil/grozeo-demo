using Amazon.DynamoDBv2;
using Amazon.DynamoDBv2.Model;
using RetalineProAgent.Core.BussinessModel.Store;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Linq;
using System.Reflection;
using System.Text;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.Services.ActiveLog
{
    public class Activitylog
    {
        public static async Task ActivitylogAsync(int storegroupid,string Source,string User,string Description)
        {
            try
            {
                Guid uuid = Guid.NewGuid();
                string uuidAsString = uuid.ToString();
                DateTime currentDateTime = DateTime.Now;
                TimeZoneInfo timeZone = TimeZoneInfo.FindSystemTimeZoneById("UTC");
                DateTime currentTimeInDesiredTimeZone = TimeZoneInfo.ConvertTime(currentDateTime, timeZone);
                //string formattedDateTime = currentTimeInDesiredTimeZone.ToString("yyyyMMdd");
                string formatDateTime = currentTimeInDesiredTimeZone.ToString("yyyy-MM-dd HH:mm:ss"); ;
                var itemToWrite = new Dictionary<string, AttributeValue>
                            {
                    { "uuid", new AttributeValue { S = uuidAsString } },
                                { "storegroupid", new AttributeValue { N = storegroupid.ToString() } },
                                { "tstamp", new AttributeValue { S = formatDateTime } },
                                { "source", new AttributeValue { S = Source } },
                                { "User", new AttributeValue { S = User } },
                                { "Description", new AttributeValue { S = Description } }                                                            
                            };

                   string tableprefix= ConfigurationManager.AppSettings.Get("AWS_Prefix");
                   string table = "activitylogs";
                   string tableName = String.Concat(tableprefix, table); 
                   DynamoService.SaveToDynamoDb(tableName, itemToWrite);
            }
            catch(Exception ex)
            {
                throw ex;
            }
        }

        public static async Task SignuplogAsync(int storegroupid, int isPartner, int signupStatus, string mobile,string Status)
        {
            try
            {
                Guid uuid = Guid.NewGuid();
                string uuidAsString = uuid.ToString();
                DateTime currentDateTime = DateTime.Now;
                TimeZoneInfo timeZone = TimeZoneInfo.FindSystemTimeZoneById("UTC");
                DateTime currentTimeInDesiredTimeZone = TimeZoneInfo.ConvertTime(currentDateTime, timeZone);
                //string formattedDateTime = currentTimeInDesiredTimeZone.ToString("yyyyMMdd");
                string formatDateTime = currentTimeInDesiredTimeZone.ToString("yyyy-MM-dd HH:mm:ss"); ;
                var itemToWrite = new Dictionary<string, AttributeValue>
                            {
                                { "uuid", new AttributeValue { S = uuidAsString } },
                                { "mobile", new AttributeValue { S = mobile.ToString() } },
                                { "storegroupid", new AttributeValue { N = storegroupid.ToString() } },
                                { "tstamp", new AttributeValue { S = formatDateTime } },
                                { "isPartner", new AttributeValue { N = isPartner.ToString() } },
                                { "status", new AttributeValue { N = signupStatus.ToString() } },
                                { "Signupstatus", new AttributeValue { N = Status.ToString() } }
                            };

                string tableprefix = ConfigurationManager.AppSettings.Get("AWS_Prefix");
                string table = "signuplogs";
                string tableName = String.Concat(tableprefix, table);
                DynamoService.SaveToDynamoDb(tableName, itemToWrite);
            }
            catch (Exception ex)
            {
                throw ex;
            }
        }

        public static async Task SignupUpdatelogAsync(string mobile)
        {
            try
            {
                Guid uuid = Guid.NewGuid();
                string uuidAsString = uuid.ToString();
                DateTime currentDateTime = DateTime.Now;
                TimeZoneInfo timeZone = TimeZoneInfo.FindSystemTimeZoneById("UTC");
                DateTime currentTimeInDesiredTimeZone = TimeZoneInfo.ConvertTime(currentDateTime, timeZone);
                //string formattedDateTime = currentTimeInDesiredTimeZone.ToString("yyyyMMdd");
                string formatDateTime = currentTimeInDesiredTimeZone.ToString("yyyy-MM-dd HH:mm:ss"); ;
               

                string tableprefix = ConfigurationManager.AppSettings.Get("AWS_Prefix");
                string table = "signuplogs";
                string tableName = String.Concat(tableprefix, table);

                var key = new Dictionary<string, AttributeValue>
        {
            { "mobile", new AttributeValue { S = mobile } }, // Replace with the actual mobile value
            { "isPartner", new AttributeValue { N = "1" } }             // Assuming isPartner is a numeric value
        };

                // Define the attribute updates dictionary
                   var attributeUpdates = new Dictionary<string, AttributeValueUpdate>
                   {
                       { "status", new AttributeValueUpdate
                            {
                               Action = AttributeAction.PUT,
                              Value = new AttributeValue { N = "6" }             // Assuming status is a numeric value
                            }
                       },
                       { "Signupstatus", new AttributeValueUpdate
                              {
                                 Action = AttributeAction.PUT,
                                Value = new AttributeValue { N = "2" }             // Assuming Signupstatus is also a numeric value
                              }
                       }
                   };

                DynamoService.UpdateToDynamoDb(tableName, key, attributeUpdates);
            }
            catch (Exception ex)
            {
                throw ex;
            }
        }

    }
}
