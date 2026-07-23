using Amazon.DynamoDBv2.Model;
using RetalineProAgent.Core.BussinessModel.Store;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.Services.ActiveLog
{
    public class ActiveLog
    {
        public static async Task ActivelogAsync(int storegroupid,string Source,string User,string Description)
        {
            try
            {
                Guid uuid = Guid.NewGuid();
                string uuidAsString = uuid.ToString();
                DateTime currentDateTime = DateTime.Now;
                TimeZoneInfo timeZone = TimeZoneInfo.FindSystemTimeZoneById("UTC");
                DateTime currentTimeInDesiredTimeZone = TimeZoneInfo.ConvertTime(currentDateTime, timeZone);
                string formattedDateTime = currentTimeInDesiredTimeZone.ToString("yyyyMMdd");
                string formatDateTime = currentTimeInDesiredTimeZone.ToString("yyyy-MM-dd HH:mm:ss");
                var itemToWrite = new Dictionary<string, AttributeValue>
                            {
                    { "uuid", new AttributeValue { S = uuidAsString } },
                                { "storegroupid", new AttributeValue { N = storegroupid.ToString() } },
                                { "source", new AttributeValue { S = Source } },
                                { "User", new AttributeValue { S = User } },
                                { "Description", new AttributeValue { S = Description } },
                                { "createddate", new AttributeValue { N = formattedDateTime } },
                                { "createdtime", new AttributeValue { S = formatDateTime } }
                            };

                   string tableName = "grozeodev_activelog";
                   DynamoService.SaveToDynamoDb(tableName, itemToWrite);
            }
            catch(Exception ex)
            {
                throw ex;
            }
        }

    }
}
