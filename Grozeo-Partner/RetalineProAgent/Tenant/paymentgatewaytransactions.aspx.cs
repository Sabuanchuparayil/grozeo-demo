using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.PaymentGateway;
using RetalineProAgent.Finance;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Web.Util;

namespace RetalineProAgent.Tenant
{

    public class paymentdetails
    {
        public string Paymentrefernceid { get; set; }

        public decimal Amount { get; set; }
        public string Status { get; set; }
        public string orderid { get; set; }
        public string order_order_id { get; set; }
        public string Orderstatus { get; set; }
        public DateTime Orderdate { get; set; }
        public string contact { get; set; }
        public string email { get; set; }
        public string namestatus { get; set; }

    }
    public partial class paymentgatewaytransactions : Base.BasePartnerPage
    {
        
        protected void Page_Load(object sender, EventArgs e)
        {

        }
        public List<paymentdetails> LoadOrderValues()
        {
            var service = new RazorpayService();
            var transaction = service.Gettransaction();
            decimal conversionRate = 82.5m;
            List<paymentdetails> results = transaction
            .Select(payment =>
            {
                // Get the corresponding order_order_id from your database
                var dbDetails = GetOrderOrderIdFromDb(payment.OrderId); 

                return new paymentdetails
                {
                    Paymentrefernceid = payment.PaymentId,
                    orderid = payment.OrderId,
                    order_order_id = dbDetails.OrderOrderId,
                    Amount = payment.Amount * conversionRate,
                    Status = payment.Status,
                    Orderstatus= dbDetails.Status,
                    Orderdate=payment.createdate,
                    contact=payment.contactnumber,
                    email=payment.email,
                    namestatus= dbDetails.statusdescription

                };
            })
            .ToList();
            return results;
        }

        private (string OrderOrderId, string Status,string statusdescription) GetOrderOrderIdFromDb(string orderId)
        {
            string orderOrderId = "";
            string status = "";
            string statusdescription = "";
            List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
            sqldaId.Add(new KeyValuePair<string, object>("order_id", orderId));
            string getorderid = "SELECT order_order_id,order_payment_gateway_req_refid,order_payment_gateway_refid,rc.status_id,admin_description FROM `retaline_customer_order` rc INNER JOIN  `retaline_customer_order_status` rs ON rc.status_id=rs.status_id WHERE order_payment_gateway_req_refid=@order_id";
            var getorderdetails = DataServiceMySql.GetDataTable(getorderid, Service.UserService.GetAPIConnectionString(), sqldaId);
            if (getorderdetails != null && getorderdetails.Rows.Count > 0)
            {
                var oredr = getorderdetails.Rows[0];
                orderOrderId= oredr["order_order_id"].ToString();
                status= oredr["status_id"].ToString();
                statusdescription = oredr["admin_description"].ToString();
            }

            return (orderOrderId, status, statusdescription);
        }




    }
}