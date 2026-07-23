using Amazon.Runtime.Internal.Transform;
using Microsoft.Azure.Management.WebSites.Models;
using Newtonsoft.Json;
using Newtonsoft.Json.Linq;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.PaymentGateway;
using RetalineProAgent.Core.Services.Subscription;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Net;
using System.Net.Http;
using System.Web;
using System.Web.Http;
using System.Windows.Input;

namespace RetalineProAgent.Controller
{
    public class SupportController: ApiController
    {
        // GET api/<controller>
        public IEnumerable<string> Get()
        {
            return new string[] { "value1", "value2" };
        }

        // GET api/<controller>/5
        public string Get(int id)
        {
            return "value";
        }
        [HttpPost]
        public IHttpActionResult SupportReopen([FromBody] JObject requestBody)
        {
            try
            {
                JObject input = requestBody["input"] as JObject;
                string ticketId = input?["ticketId"]?.ToString();
                string supportUnit = input?["unitId"]?.ToString();
                List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                prms.Add(new KeyValuePair<string, object>("GetTicket", ticketId));
                prms.Add(new KeyValuePair<string, object>("supportunit", supportUnit));
                string aceeptTicket = "Update `support_ticket` set ticketStage=9 ,ticketStatus=1 where ticketId=@GetTicket ;";
                aceeptTicket += "INSERT INTO  support_ticket_log(ticketId,ticketType,ticketStatus,ticketStage,ticketRemarks,ticketSupportUnit) VALUES(@GetTicket,1,1,9,'Ticket Reopened',@supportunit)";
                DataServiceMySql.ExecuteSql(aceeptTicket, Service.UserService.GetAPIConnectionString(), prms);
                return Json(new { result = 1, status = "Success", data = "" });
            }
            catch
            {
                return null;
            }

        }
    }
}