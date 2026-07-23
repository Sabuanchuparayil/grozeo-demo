using Amazon;
using Amazon.DynamoDBv2;
using Amazon.DynamoDBv2.DataModel;
using Amazon.DynamoDBv2.DocumentModel;
using Amazon.DynamoDBv2.Model;
using Amazon.Runtime;
using Amazon.Runtime.Internal.Endpoints.StandardLibrary;
using RetalineProAgent.Core.BussinessModel.Dynamo;
using RetalineProAgent.Core.Services.Drivers;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Globalization;
using System.Linq;
using System.Threading.Tasks;


namespace RetalineProAgent.Core.Services
{
    public static class DynamoService
    {

        private static AmazonDynamoDBClient _dynamoDbClient;

        private static string bucketName = ConfigurationManager.AppSettings.Get("AWS_S3_BucketName");
        private static string accessKeyId = ConfigurationManager.AppSettings.Get("AWS_Key_ID");
        private static string accessSecret = ConfigurationManager.AppSettings.Get("AWS_Secret");
        private static string region = ConfigurationManager.AppSettings.Get("AWS_Region");

        public static void Initialize(AmazonDynamoDBClient dynamoDbClient)
        {
            _dynamoDbClient = dynamoDbClient;
        }

        public static async Task<int> SaveToDynamoDb(string tableName, Dictionary<string, AttributeValue> item)
        {
            var awsCredentials = new BasicAWSCredentials(accessKeyId, accessSecret);
            AmazonDynamoDBClient client = new AmazonDynamoDBClient(awsCredentials, Amazon.RegionEndpoint.GetBySystemName(region));
            DynamoService.Initialize(client);
            var request = new PutItemRequest
            {
                TableName = tableName,
                Item = item,
            };
            var response = await client.PutItemAsync(request);

            // Check if the operation was successful
            if (response.HttpStatusCode == System.Net.HttpStatusCode.OK)
            {
                // The item was successfully put into the DynamoDB table
                //Console.WriteLine("Item was successfully added to the DynamoDB table.");
                return 1;
            }
            else
            {
                return 0;
                // Print an error message or handle the failure appropriately
                //Console.WriteLine($"Error putting item to DynamoDB. HTTP Status Code: {response.HttpStatusCode}");
            }
        }
        public static async Task<int> UpdateToDynamoDb(string tableName, Dictionary<string, AttributeValue> key, Dictionary<string, AttributeValueUpdate> attributeUpdates)
        {
            var awsCredentials = new BasicAWSCredentials(accessKeyId, accessSecret);
            AmazonDynamoDBClient client = new AmazonDynamoDBClient(awsCredentials, Amazon.RegionEndpoint.GetBySystemName(region));
            DynamoService.Initialize(client);
            var request = new UpdateItemRequest
            {
                TableName = tableName,
                Key = key,
                AttributeUpdates = attributeUpdates
            };
            try
            {
                var response = await client.UpdateItemAsync(request);

                // Check if the operation was successful
                if (response.HttpStatusCode == System.Net.HttpStatusCode.OK)
                {
                    // The item was successfully updated in the DynamoDB table
                    return 1;
                }
                else
                {
                    // Print an error message or handle the failure appropriately
                    Console.WriteLine($"Error updating item in DynamoDB. HTTP Status Code: {response.HttpStatusCode}");
                    return 0;
                }
            }
            catch (Exception ex)
            {
                // Handle the exception
                Console.WriteLine($"Exception updating item in DynamoDB: {ex.Message}");
                return 0;
            }
        }

        public static List<GraphicsData> GetGraphicsDataByStoreId(string tableName, string attributeName, string attributeValue)
        {
            try
            {
                var awsCredentials = new BasicAWSCredentials(accessKeyId, accessSecret);
                AmazonDynamoDBClient client = new AmazonDynamoDBClient(awsCredentials, Amazon.RegionEndpoint.GetBySystemName(region));

                var scanFilter = new Dictionary<string, Condition>
                {
                    { attributeName, new Condition
                        {
                            ComparisonOperator = ComparisonOperator.EQ,
                            AttributeValueList = new List<AttributeValue> { new AttributeValue { N = attributeValue } }
                        }
                    }
                };

                var scanRequest = new ScanRequest
                {
                    TableName = tableName,
                    ScanFilter = scanFilter,
                };

                Console.WriteLine($"Executing query: {scanRequest}");
                var response = client.Scan(scanRequest);

                Console.WriteLine($"Scan Response: {Newtonsoft.Json.JsonConvert.SerializeObject(response)}");

                var items = response.Items;

                Console.WriteLine($"Number of items retrieved: {items.Count}");

                var graphicsDataList = new List<GraphicsData>();

                foreach (var item in items)
                {
                    // Log each item for inspection
                    Console.WriteLine($"DynamoDB Item: {Newtonsoft.Json.JsonConvert.SerializeObject(item)}");

                    var graphicsData = new GraphicsData();

                    // Map attributes to properties
                    if (item.TryGetValue("graphicsURL", out var imageUrlAttribute) && imageUrlAttribute.S != null)
                    {
                        graphicsData.graphicsURL = imageUrlAttribute.S;
                    }

                    // Continue mapping other attributes as needed

                    graphicsDataList.Add(graphicsData);
                }
                graphicsDataList.Reverse();
                return graphicsDataList;
            }
            catch (Exception ex)
            {
                // Log the exception or handle it appropriately
                Console.WriteLine($"Exception: {ex}");
                return new List<GraphicsData>(); // Return an empty list or handle the failure case
            }
        }

        public static async Task<Dictionary<string, AttributeValue>> GetItemAsync(string tableName, string partitionKeyName, string partitionKeyValue, string sortKeyName, string sortKeyValue)
        {
            var awsCredentials = new BasicAWSCredentials(accessKeyId, accessSecret);
            AmazonDynamoDBClient client = new AmazonDynamoDBClient(awsCredentials, Amazon.RegionEndpoint.GetBySystemName(region));
            DynamoService.Initialize(client);
            var request = new GetItemRequest
            {
                TableName = tableName,
                Key = new Dictionary<string, AttributeValue>
                {
                  { partitionKeyName, new AttributeValue { S = partitionKeyValue } },
                  { sortKeyName, new AttributeValue { S = sortKeyValue } }
                }
            };

            var response = await client.GetItemAsync(request);

            if (response.Item == null || response.Item.Count == 0)
            {
                Console.WriteLine($"Item with {partitionKeyName} = {partitionKeyValue} and {sortKeyName} = {sortKeyValue} not found.");
                return null;
            }

            return response.Item;
        }
        public static async Task<List<Dictionary<string, AttributeValue>>> ReadDynamoDBAsync(string tablename, Dictionary<string, object> data)
        {
            var awsCredentials = new BasicAWSCredentials(accessKeyId, accessSecret);
            AmazonDynamoDBClient client = new AmazonDynamoDBClient(awsCredentials, Amazon.RegionEndpoint.GetBySystemName(region));
            DynamoService.Initialize(client);
            // Extracting data from the dictionary          
            var partitionKeyInfo = (Dictionary<string, string>)data["PartitionKey"];
            var sortKeyInfo = (Dictionary<string, string>)data["SortKey"];
            var indexName = data["IndexName"] as string;
            var queryAttributes = data["queryAttributes"] as List<string>;
            var conditions = data["Condition"] as List<Dictionary<string, string>>;

            // Defining expression attribute values
            var expressionAttributeValues = new Dictionary<string, AttributeValue>
            {
                { ":partitionKeyVal", new AttributeValue { S = partitionKeyInfo["val"] } },
                { ":sortKeyVal", new AttributeValue { S = sortKeyInfo["val"] } }
            };

            // Defining expression attribute names dynamically
            var expressionAttributeNames = new Dictionary<string, string>();
            string keyConditionExpression = "";
            string projectionExpression = "";

            string pkPlaceholder = $"#attr{partitionKeyInfo["col"]}";
            string skPlaceholder = $"#attr{sortKeyInfo["col"]}";

            expressionAttributeNames[pkPlaceholder] = partitionKeyInfo["col"];
            expressionAttributeNames[skPlaceholder] = sortKeyInfo["col"];

            keyConditionExpression = "#pk = :partitionKeyVal AND #sk >= :sortKeyVal";

            // Construct the ProjectionExpression dynamically
            foreach (var attribute in queryAttributes)
            {
                string attributePlaceholder = $"#attr{attribute}";
                expressionAttributeNames[attributePlaceholder] = attribute;
                projectionExpression += $"{attributePlaceholder}, ";
            }

            // Additional condition expressions (for filtering, not key conditions)
            string filterExpression = string.Empty;
            if (conditions != null && conditions.Count > 0)
            {
                foreach (var condition in conditions)
                {
                    string conditionPlaceholder = $"#attr{condition["col"]}";
                    filterExpression += $"{conditionPlaceholder} {condition["oper"]} :{condition["col"]}Val ";
                    expressionAttributeValues.Add($":{condition["col"]}Val", new AttributeValue { N = condition["val"] });
                    expressionAttributeNames[conditionPlaceholder] = condition["col"];
                }


            }

            // Create the QueryRequest
            var request = new QueryRequest
            {
                TableName = tablename,
                IndexName = indexName,
                KeyConditionExpression = keyConditionExpression,
                ExpressionAttributeValues = expressionAttributeValues,
                ExpressionAttributeNames = expressionAttributeNames,
                ProjectionExpression = projectionExpression.TrimEnd(',', ' ')  // Remove the last comma
            };

            if (!string.IsNullOrEmpty(filterExpression))
            {
                request.FilterExpression = filterExpression.Trim();
            }

            var response = await client.QueryAsync(request);
            return response.Items;
        }

        public static async Task<List<Dictionary<string, AttributeValue>>> ScanDynamoDBAsync(string tablename, Dictionary<string, object> data)
        {
            var awsCredentials = new BasicAWSCredentials(accessKeyId, accessSecret);
            AmazonDynamoDBClient client = new AmazonDynamoDBClient(awsCredentials, Amazon.RegionEndpoint.GetBySystemName(region));
            DynamoService.Initialize(client);

            var expressionAttributeValues = new Dictionary<string, AttributeValue>();
            var expressionAttributeNames = new Dictionary<string, string>();
            string filterExpression = string.Empty;

            // Handle filter expressions if conditions are provided
            if (data.ContainsKey("Condition") && data["Condition"] is List<Dictionary<string, object>> conditions)  // Changed to object
            {
                foreach (var condition in conditions)
                {
                    string conditionPlaceholder = $"#attr{condition["col"]}";
                    filterExpression += $"{conditionPlaceholder} {condition["oper"]} :{condition["col"]}Val ";

                    // Check the type of value and add it appropriately
                    if (condition["val"] is int) // If the value is an integer
                    {
                        expressionAttributeValues.Add($":{condition["col"]}Val", new AttributeValue { N = condition["val"].ToString() }); // Store as number
                    }
                    else // Handle as a string otherwise
                    {
                        expressionAttributeValues.Add($":{condition["col"]}Val", new AttributeValue { S = condition["val"].ToString() });
                    }

                    expressionAttributeNames[conditionPlaceholder] = condition["col"].ToString();

                    // Append 'AND' for subsequent conditions
                    if (conditions.IndexOf(condition) < conditions.Count - 1)
                    {
                        filterExpression += " AND ";
                    }
                }
            }

            // Create the ScanRequest
            var request = new ScanRequest
            {
                TableName = tablename,
                ExpressionAttributeValues = expressionAttributeValues,
                ExpressionAttributeNames = expressionAttributeNames,
                FilterExpression = filterExpression.Trim(),
               
            };

            var response = await client.ScanAsync(request);
            return response.Items;
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

        public static List<Dictionary<string, AttributeValue>> ReadFromDynamoDB(string tableName, int br_id, int? createdDate = null, bool onlyLive = true, List<string> queryAttributes = null, string indexName = "ReportingBranch-createddate-index")
        {
            // Initialize AWS client
            var awsCredentials = new BasicAWSCredentials(accessKeyId, accessSecret);
            var client = new AmazonDynamoDBClient(awsCredentials, Amazon.RegionEndpoint.GetBySystemName(region));
            DynamoService.Initialize(client);

            // Expression attribute values
            var expressionAttributeValues = new Dictionary<string, AttributeValue>
            {
                { ":partitionKeyVal", new AttributeValue { N = br_id.ToString() } }
            };

            string keyConditionExpression = "ReportingBranch = :partitionKeyVal";

            if (createdDate.HasValue)
            {
                keyConditionExpression += " AND createddate = :sortKeyVal";
                expressionAttributeValues[":sortKeyVal"] = new AttributeValue { N = createdDate.Value.ToString() };
            }

            // Projection expression
            string projectionExpression = queryAttributes != null && queryAttributes.Count > 0
                ? string.Join(", ", queryAttributes)
                : null;

            // Filter expression
            string filterExpression = null;
            if (onlyLive)
            {
                filterExpression = "Is_Live = :Is_LiveVal";
                expressionAttributeValues[":Is_LiveVal"] = new AttributeValue { N = "1" };
            }

            var request = new QueryRequest
            {
                TableName = tableName,
                IndexName = indexName,
                KeyConditionExpression = keyConditionExpression,
                ExpressionAttributeValues = expressionAttributeValues,
                ProjectionExpression = projectionExpression
            };

            if (!string.IsNullOrEmpty(filterExpression))
                request.FilterExpression = filterExpression;

            // Execute synchronously
            var response = client.Query(request);

            return response.Items;
        }
    }
}



