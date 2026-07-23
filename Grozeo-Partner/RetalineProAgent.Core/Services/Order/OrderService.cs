using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Runtime.Remoting.Contexts;
//using System.Security.AccessControl;
using System.Text;
using System.Threading.Tasks;
using Amazon;
using Amazon.DynamoDBv2;
using Amazon.DynamoDBv2.DataModel;
using Amazon.DynamoDBv2.Model;
using Amazon.Runtime;
using Newtonsoft.Json;
using RetalineProAgent.Core.BussinessModel.Order;
using SendGrid.Helpers.Mail;
using StackExchange.Redis;

namespace RetalineProAgent.Core.Services.Order
{
    public class OrderService
    {

        public static Core.BussinessModel.Order.Order GetOrder(int orderId)
        {
            throw new NotImplementedException();
        }

        public static List<Core.BussinessModel.Order.Order> GetOrdersByGroupId(string groupId)
        {
            throw new NotImplementedException();
        }

        public static void AddOrderHistoryData(int orderId, int orderStatusId, string action)
        {
            // insert into order history table
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("order_status", orderStatusId));
            prms.Add(new KeyValuePair<string, object>("order_id", orderId));
            prms.Add(new KeyValuePair<string, object>("created_at", DateTime.Now));
            prms.Add(new KeyValuePair<string, object>("updated_at", DateTime.Now));
            prms.Add(new KeyValuePair<string, object>("order_action", action));
            string updateorderhistory = "INSERT INTO retaline_customer_order_history (order_id,order_action,order_status,created_at) VALUES (@order_id,@order_action,@order_status,@created_at)";
            DataServiceMySql.ExecuteScalar(updateorderhistory, "", prms);

        }

        public static void UpdateOrderStatus(int orderId, int orderStatusId, int storeGroupId = 0)
        {
            // update order status
            List<KeyValuePair<string, object>> rms = new List<KeyValuePair<string, object>>();
            rms.Add(new KeyValuePair<string, object>("order_id", orderId));
            rms.Add(new KeyValuePair<string, object>("status_id", orderStatusId));
            rms.Add(new KeyValuePair<string, object>("updated_at", DateTime.Now));
            string updatestatus = "UPDATE retaline_customer_order set status_id=@status_id,updated_at=@updated_at where order_id=@order_id; ";
            DataServiceMySql.ExecuteScalar(updatestatus, "", rms);

        }

        public static void UpdateQueGeoStatus(int quor_id, int orderStatusId, int storeGroupId = 0)
        {
            // update qugeo_order status
            List<KeyValuePair<string, object>> qrms = new List<KeyValuePair<string, object>>();
            qrms.Add(new KeyValuePair<string, object>("quor_id", quor_id));
            qrms.Add(new KeyValuePair<string, object>("status_id", orderStatusId));
            qrms.Add(new KeyValuePair<string, object>("updated_at", DateTime.Now));
            string updatequgeo_orderstatus = "UPDATE qugeo_order set quor_Status=@status_id,quor_UpdateOn=@updated_at where quor_id=@quor_id";
            DataServiceMySql.ExecuteScalar(updatequgeo_orderstatus, "", qrms);
        }

        public List<PendingOrder> DelayedOrders(int branchid = 0, string orderID = null)
        {
            return Task.Run(async () => await LoadOrdersAsync(branchid, orderID)).GetAwaiter().GetResult();
        }

		public async Task<int> DelayedOrdersCountAsync(int branchid = 0, string orderOrderID = null)
        {
		    string accessKeyId = ConfigurationManager.AppSettings.Get("AWS_Key_ID");
		    string accessSecret = ConfigurationManager.AppSettings.Get("AWS_Secret");
		    string region = ConfigurationManager.AppSettings.Get("AWS_Region");
		    var awsCredentials = new BasicAWSCredentials(accessKeyId, accessSecret);

			var client = new AmazonDynamoDBClient(awsCredentials, Amazon.RegionEndpoint.GetBySystemName(region));

			string tableprefix = ConfigurationManager.AppSettings.Get("AWS_Prefix");
			string table = "delayed_orders";
			string tableName = $"{tableprefix}{table}";
            string IndexName = "type-skipDate-index";

            DateTime now = DateTime.UtcNow;

            var queryRequest = new QueryRequest
            {
                TableName = tableName,
                IndexName = IndexName,
                Select = "COUNT",
                KeyConditionExpression = "#type = :type2 AND #skipDate < :now",
                ExpressionAttributeNames = new Dictionary<string, string>
                {
                    { "#type", "type" },
                    { "#skipDate", "skipDate" }
                },
                ExpressionAttributeValues = new Dictionary<string, AttributeValue>
                {
                    { ":now", new AttributeValue { S = now.ToString("o") } },
                    { ":type2", new AttributeValue { N = "2" } } 
                }
            };

            var queryResponse = await client.QueryAsync(queryRequest);
            if (queryResponse != null)
                return queryResponse.Count;

            return -1;

		}



		public async Task<List<PendingOrder>> LoadOrdersAsync(int branchid = 0, string orderOrderID = null)
        {
            string accessKeyId = ConfigurationManager.AppSettings.Get("AWS_Key_ID");
            string accessSecret = ConfigurationManager.AppSettings.Get("AWS_Secret");
            string region = ConfigurationManager.AppSettings.Get("AWS_Region");
            var awsCredentials = new BasicAWSCredentials(accessKeyId, accessSecret);

            var client = new AmazonDynamoDBClient(awsCredentials, Amazon.RegionEndpoint.GetBySystemName(region));
            string tableprefix = ConfigurationManager.AppSettings.Get("AWS_Prefix");
            string table = "delayed_orders";
            string tableName = $"{tableprefix}{table}";

            var now = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");

             var request = new QueryRequest
            {
                TableName = tableName,
                IndexName = "type-skipDate-index",
                KeyConditionExpression = "#type = :typeVal AND #skipDate < :nowVal",
                ExpressionAttributeNames = new Dictionary<string, string>
                                            {
                                                { "#type", "type" },
                                                { "#skipDate", "skipDate" }
                                            },
                ExpressionAttributeValues = new Dictionary<string, AttributeValue>
                                            {
                                                { ":typeVal", new AttributeValue { N = "2" } },
                                                { ":nowVal", new AttributeValue { S = now } }
                                            }
            };

            var result = await client.QueryAsync(request);

            var orders = new List<PendingOrder>();

            foreach (var item in result.Items)
            {
                if (item.TryGetValue("orderID", out var orderIdAttr) &&
                    item.TryGetValue("orderOrderID", out var orderTypeAttr) &&
                    item.TryGetValue("type", out var typeAttr) &&
                    int.TryParse(orderIdAttr.N, out int orderIDParsed) &&
                    int.TryParse(typeAttr.N, out int type))
                {
                    string ID = item["orderID"].N;

                    // Check if branchid is -1 or matches the item's branchID
                    if (branchid <= 0 ||
                        (item.TryGetValue("branchID", out var branchIDAttr) &&
                         int.TryParse(branchIDAttr.N, out int branchID) &&
                         branchID == branchid))
                    {
                        // Use orderID as the primary identifier,
                        // filter by orderOrderID only if provided.
                        if (string.IsNullOrEmpty(orderOrderID) || item["orderOrderID"].N == orderOrderID)
                        {
                            string OrderOrderID = item["orderOrderID"].N;
                            string delMode = item["deliveryType"].S;
                            string customerDetailsStr = item["customerDetails"].S;
                            string merchantDetailsStr = item["merchantDetails"].S;
                            string orderTotal = item["orderTotal"].N;
                            string orderSubtotal = item["orderSubtotal"].N;
                            string orderDate = item["orderDate"].S;
                            string brID = item["branchID"].N;
                            string paymentMode = item["paymentMode"].N;
                            string mode = item["mode"].S;
                            string uuid = item["uuid"].S;
                            string timestamp = item["tstamp"].S;
                            string action = item["action"].N;
                            string modeMethod = item["modeMethod"].S;


							try
                            {
                                var customerDetails = JsonConvert.DeserializeObject<Dictionary<string, string>>(customerDetailsStr);
                                var merchantDetails = JsonConvert.DeserializeObject<Dictionary<string, string>>(merchantDetailsStr);

                                string custName = customerDetails.TryGetValue("name", out var name) ? name : string.Empty;
                                string custPhone = customerDetails.TryGetValue("phone", out var phone) ? phone : string.Empty;
                                string custAddress = customerDetails.TryGetValue("address", out var address) ? address : string.Empty;

                                string customerNamePhone = $"{custName}, {custPhone}".TrimEnd(',', ' ');

                                string merName = merchantDetails.TryGetValue("name", out var merchantName) ? merchantName : string.Empty;
                                string merPhone = merchantDetails.TryGetValue("phone", out var merchantPhone) ? merchantPhone : string.Empty;
                                string merAddress = merchantDetails.TryGetValue("address", out var merchantAddress) ? merchantAddress : string.Empty;

                                var order = new PendingOrder
                                {
                                    orderID = ID,
                                    OrderOrderID = OrderOrderID,
                                    DeliveryMode = delMode,
                                    CustomerDetails = customerNamePhone,
                                    Address = custAddress,
                                    MerchantName = merName,
                                    MerchantDetails = $"{merName}, {merPhone}, {merAddress}".TrimEnd(',', ' '),
                                    OrderTotal = orderTotal,
                                    OrderSubTotal = orderSubtotal,
                                    OrderDate = orderDate,
                                    BranchID = brID,
                                    PaymentMode = paymentMode,
                                    Mode = mode,
                                    UUID = uuid,
                                    Timestamp = timestamp,
                                    Action = action,
                                    ModeMethod = modeMethod,
                                };

                                orders.Add(order);
                            }
                            catch (JsonException ex)
                            {
                                Console.WriteLine($"Error deserializing customer or merchant details for order {ID}: {ex.Message}");
                            }
                        }
                    }
                }
                else
                {
                    Console.WriteLine("Error parsing orderID or type.");
                }
            }
            return orders.OrderBy(o => o.orderID).ToList();
        }

        public List<PendingOrder> PackingDelayedOrders(int branchid = 0, string orderID = null)
        {
            return Task.Run(async () => await LoadPackingOrdersAsync(branchid, orderID)).GetAwaiter().GetResult();
        }

        public async Task<List<PendingOrder>> LoadPackingOrdersAsync(int branchid = 0, string orderOrderID = null)
        {
            string accessKeyId = ConfigurationManager.AppSettings.Get("AWS_Key_ID");
            string accessSecret = ConfigurationManager.AppSettings.Get("AWS_Secret");
            string region = ConfigurationManager.AppSettings.Get("AWS_Region");
            var awsCredentials = new BasicAWSCredentials(accessKeyId, accessSecret);

            var client = new AmazonDynamoDBClient(awsCredentials, Amazon.RegionEndpoint.GetBySystemName(region));

            string tableprefix = ConfigurationManager.AppSettings.Get("AWS_Prefix");
            string table = "delayed_orders";
            string tableName = $"{tableprefix}{table}";

            var now = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");

            var request = new QueryRequest
            {
                TableName = tableName,
                IndexName = "type-skipDate-index",
                KeyConditionExpression = "#type = :typeVal AND #skipDate < :nowVal",
                ExpressionAttributeNames = new Dictionary<string, string>
                                            {
                                                { "#type", "type" },
                                                { "#skipDate", "skipDate" }
                                            },
                ExpressionAttributeValues = new Dictionary<string, AttributeValue>
                                            {
                                                { ":typeVal", new AttributeValue { N = "3" } },
                                                { ":nowVal", new AttributeValue { S = now } }
                                            }
            };

            var response = await client.QueryAsync(request);

            var orders = new List<PendingOrder>();

            foreach (var item in response.Items)
            {
                if (item.TryGetValue("orderID", out var orderIdAttr) &&
                    item.TryGetValue("orderOrderID", out var orderTypeAttr) &&
                    item.TryGetValue("type", out var typeAttr) &&
                    int.TryParse(orderIdAttr.N, out int orderIDParsed) &&
                    int.TryParse(typeAttr.N, out int type))
                {
                    string ID = item["orderID"].N;

                    if (branchid <= 0 ||
                        (item.TryGetValue("branchID", out var branchIDAttr) &&
                         int.TryParse(branchIDAttr.N, out int branchID) &&
                         branchID == branchid))
                    {
                        if (string.IsNullOrEmpty(orderOrderID) || item["orderOrderID"].N == orderOrderID)
                        {
                            string OrderOrderID = item["orderOrderID"].N;
                            string delMode = item["deliveryType"].S;
                            string customerDetailsStr = item["customerDetails"].S;
                            string merchantDetailsStr = item["merchantDetails"].S;
                            string orderTotal = item["orderTotal"].N;
                            string orderSubtotal = item["orderSubtotal"].N;
                            string orderDate = item["orderDate"].S;
                            string brID = item["branchID"].N;
                            string paymentMode = item["paymentMode"].N;
                            string mode = item["mode"].S;
                            string Otype = item["type"].N;
                            string uuid = item["uuid"].S;
                            string timestamp = item["tstamp"].S;
                            string action = item["action"].N;

                            try
                            {
                                var customerDetails = JsonConvert.DeserializeObject<Dictionary<string, string>>(customerDetailsStr);
                                var merchantDetails = JsonConvert.DeserializeObject<Dictionary<string, string>>(merchantDetailsStr);

                                string custName = customerDetails.TryGetValue("name", out var name) ? name : string.Empty;
                                string custPhone = customerDetails.TryGetValue("phone", out var phone) ? phone : string.Empty;
                                string custAddress = customerDetails.TryGetValue("address", out var address) ? address : string.Empty;

                                string customerNamePhone = $"{custName}, {custPhone}".TrimEnd(',', ' ');

                                string merName = merchantDetails.TryGetValue("name", out var merchantName) ? merchantName : string.Empty;
                                string merPhone = merchantDetails.TryGetValue("phone", out var merchantPhone) ? merchantPhone : string.Empty;
                                string merAddress = merchantDetails.TryGetValue("address", out var merchantAddress) ? merchantAddress : string.Empty;

                                var order = new PendingOrder
                                {
                                    orderID = ID,
                                    OrderOrderID = OrderOrderID,
                                    DeliveryMode = delMode,
                                    CustomerDetails = customerNamePhone,
                                    Address = custAddress,
                                    MerchantName = merName,
                                    MerchantDetails = $"{merName}, {merPhone}, {merAddress}".TrimEnd(',', ' '),
                                    OrderTotal = orderTotal,
                                    OrderSubTotal = orderSubtotal,
                                    OrderDate = orderDate,
                                    BranchID = brID,
                                    PaymentMode = paymentMode,
                                    Mode = mode,
                                    Type = Otype,
                                    UUID = uuid,
                                    Timestamp = timestamp,
                                    Action = action,
                                };

                                orders.Add(order);
                            }
                            catch (JsonException ex)
                            {
                                Console.WriteLine($"Error deserializing customer or merchant details for order {ID}: {ex.Message}");
                            }
                        }
                    }
                }
                else
                {
                    Console.WriteLine("Error parsing orderID or type.");
                }
            }
            return orders.OrderBy(o => o.orderID).ToList();
        }
    }
}

