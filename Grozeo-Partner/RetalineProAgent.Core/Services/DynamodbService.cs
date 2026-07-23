using Amazon.DynamoDBv2.Model;
using Amazon.DynamoDBv2;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Amazon;
using Amazon.DynamoDBv2.DocumentModel;

namespace RetalineProAgent.Core.Services
{
    /// <summary>
    /// Connect to DynamoDB and return a DynamoDB client instance.
    /// </summary>
    public class Dynamodb
    {
        /// <summary>
        /// Perform a scan operation on the specified DynamoDB table with the given scan filters.
        /// </summary>
        /// <param name="tableName"></param>
        /// <param name="scanFilter"></param>
        /// <returns></returns>
        public static List<Dictionary<string, AttributeValue>> getAllItems(string tableName, Dictionary<string, Condition> scanFilter)
        {
            var client = new AmazonDynamoDBClient("AKIAWJZ2KRAU7KUBZPLK", "ATmda4x9JHQUdmWEKB+kkpUzBabuS/beVSIq5hVx", RegionEndpoint.APSoutheast1);

            var request = new ScanRequest
            {
                TableName = tableName,
                ScanFilter = scanFilter,
            };

            var response = client.Scan(request);
            var result = response.Items;
            return result;
        }

        public static void WriteItem(string tableName, Dictionary<string, AttributeValue> item)
        {
            var client = new AmazonDynamoDBClient("AKIAWJZ2KRAU7KUBZPLK", "ATmda4x9JHQUdmWEKB+kkpUzBabuS/beVSIq5hVx", RegionEndpoint.APSoutheast1);

            var request = new PutItemRequest
            {
                TableName = tableName,
                Item = item,
            };
            var response = client.PutItem(request);
           

        }




    }
}
