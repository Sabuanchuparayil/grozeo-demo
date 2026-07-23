using Finascop.BussinessModel;
using Newtonsoft.Json;
using RestSharp;
using RetalineProAgent.Core.Services;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Finascop.Services.FinascopServices
{
    public class ReportingService
    {
        public static List<Finascop.BussinessModel.Finascop.SalesReport> DailySalesReport(int storegroupid, string fromDate, string toDate)
        {
            try
            {
                string url = ConfigurationSettings.AppSettings.Get("FinascopAPIUrl");
                if (String.IsNullOrEmpty(url))
                    url = "https://finascopdataentry.azurewebsites.net/api/";
                url += "DailySalesReport";

                string key = ConfigurationSettings.AppSettings.Get("FinascopAPIKey");
                if (String.IsNullOrEmpty(key))
                    key = "P_5JtNckvvxLTUM6cF9py_7ZYIA5QM9ofmNaDvh__HoqAzFuAbEyZQ==";
                    
                //string content = (string)JObject.Parse(JsonConvert.SerializeObject(voucher)); //$"\"acc\": \"{accountNo}\", \"ifsc\": \"{ifsc}\", \"fetchIfsc\": true";
                string content = JsonConvert.SerializeObject(new { store_group_id = storegroupid, from_date= fromDate, to_date= toDate });

                var client = new RestClient(url);
                var request = new RestRequest();//api/FinascopDataEntry (Method.Post);
                request.Method = Method.Post;
                request.AddHeader("content-type", "application/json");
                request.AddHeader("x-functions-key", key); //'"P_5JtNckvvxLTUM6cF9py_7ZYIA5QM9ofmNaDvh__HoqAzFuAbEyZQ==");

                //request.AddBody("{" + content + "}", "application/json");
                request.AddBody(content, "application/json");
                var response =  client.Execute<List<Finascop.BussinessModel.Finascop.SalesReport>>(request);
            return response.Data;

            }
            catch (Exception ex)
            {
            }
            return default;

        }
    }

}
