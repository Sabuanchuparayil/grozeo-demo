using Finascop.Services;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using RestSharp;

namespace RetalineProAgent
{
    public partial class ManualDelivery: Base.BasePartnerPage
    {

        protected void Page_Load(object sender, EventArgs e)
        {
            txtDate.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
            if (!IsPostBack)
            {
                string fsto_id = Convert.ToString(Request.QueryString["fsto_id"]);
                List<KeyValuePair<string, object>> toparams = new List<KeyValuePair<string, object>>();
                toparams.Add(new KeyValuePair<string, object>("fstoid", fsto_id));
                toparams.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));

                var tblOrderInfo = DataServiceMySql.GetDataTable($"SELECT co.order_id,co.order_order_id, qo.quor_id, qo.quor_TransferOrder_id  FROM retaline_customer_order co " +
                    $"INNER JOIN qugeo_order qo ON qo.quor_RefNo = co.order_order_id INNER JOIN finascop_stock_transfer_order fo ON co.order_id=fo.fstr_id " +
                    $"INNER JOIN finascop_branch fb ON fb.br_ID=co.order_branch_id " +
                    $" WHERE fo.fsto_id = @fstoid AND fb.br_storeGroup = @storegroupid", UserService.GetAPIConnectionString(), toparams);

                //AND co.storegroup_id = @storegroupid for public site orders

                if (tblOrderInfo == null || tblOrderInfo.Rows.Count <= 0)
                {
                    Common.ShowCustomAlert(this.Page, "Invalid Order", "Invalid order or not accessible", false, "/Tenant/MerchantDelivery");
                    return;
                }
                txtOrdId.Text = tblOrderInfo.Rows[0]["order_order_id"].ToString();
                LoadStoreInfo();
            }

        }

        private void LoadStoreInfo()
        {
            //txtDate.Text = DateTime.Now.ToString("yyyy-MM-dd");
            //txtTime.Text = DateTime.Now.ToString("HH:mm:ss");
            if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
            {
                var IN = TimeZoneInfo.ConvertTimeBySystemTimeZoneId(DateTimeOffset.UtcNow, "India Standard Time");
                txtDate.Text = IN.ToString("yyyy-MM-dd");
                txtTime.Text = IN.ToString("HH:mm:ss");
            }
            else if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
            {
                var UK = TimeZoneInfo.ConvertTimeBySystemTimeZoneId(DateTimeOffset.UtcNow, "GMT Standard Time");
                txtDate.Text = UK.ToString("yyyy-MM-dd");
                txtTime.Text = UK.ToString("HH:mm:ss");
            }
        }

        protected async void btnManualDeliverySubmit_Click(object sender, EventArgs e)
        {
            string fsto_id = Convert.ToString(Request.QueryString["fsto_id"]);
            List<KeyValuePair<string, object>> toparams = new List<KeyValuePair<string, object>>();
            toparams.Add(new KeyValuePair<string, object>("fstoid", fsto_id));
            toparams.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));
            var tblOrdInfo = DataServiceMySql.GetDataTable($"SELECT qo.quor_AmountCollectible,co.order_id,co.order_order_id, qo.quor_id, qo.quor_TransferOrder_id  FROM retaline_customer_order co " +
                    $"INNER JOIN qugeo_order qo ON qo.quor_RefNo = co.order_order_id INNER JOIN finascop_stock_transfer_order fo ON co.order_id=fo.fstr_id " +
                    $" WHERE fo.fsto_id = @fstoid", 
                    UserService.GetAPIConnectionString(), toparams);
            if(tblOrdInfo == null || tblOrdInfo.Rows.Count <= 0)
            {
                Common.ShowCustomAlert(this.Page, "Failure", "Invalid order or the process cannot execute at this time.", false, "/Tenant/MerchantDelivery");
                return;
            }

            string order_id = tblOrdInfo.Rows[0]["order_id"].ToString();
            string quor_id = tblOrdInfo.Rows[0]["quor_id"].ToString();
            double quor_AmountCollectible = Convert.ToDouble(tblOrdInfo.Rows[0]["quor_AmountCollectible"]);

            int qugeoId = Convert.ToInt32(quor_id);
            int orderID = Convert.ToInt32(order_id);           
            string quor_DeliveryConfTime = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
            string quorDeliveryConfTime = DateTime.Now.ToString("yyyy-MM-dd 00:00:00");
            var data = new List<Dictionary<string, object>>();
            data.Add(new Dictionary<string, object> {
                            {"qmd_deliveredBy", txtDelivBoy.Text },
                            {"qmd_Date", txtDate.Text },
                            {"qmd_Time", txtTime.Text },
                            {"quor_id", qugeoId },
                            {"qmd_createdOn", quor_DeliveryConfTime },
                            {"qmd_createdBy", 1 }
                        });

            toparams = new List<KeyValuePair<string, object>>();
            toparams.Add(new KeyValuePair<string, object>("qmddeliveredBy", txtDelivBoy.Text));
            toparams.Add(new KeyValuePair<string, object>("qmdDate", txtDate.Text));
            toparams.Add(new KeyValuePair<string, object>("qmdTime", txtTime.Text));
            toparams.Add(new KeyValuePair<string, object>("quorid", qugeoId));
            toparams.Add(new KeyValuePair<string, object>("qmdcreatedOn", quor_DeliveryConfTime));
            string strSql = $"INSERT INTO qugeo_manual_deliver(qmd_deliveredBy, qmd_Date, qmd_Time, quor_id, qmd_createdOn, qmd_createdBy) " +
                    $"VALUES(@qmddeliveredBy, @qmdDate, @qmdTime, @quorid, @qmdcreatedOn, 1)";
            DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), toparams);

            toparams = new List<KeyValuePair<string, object>>();
            toparams.Add(new KeyValuePair<string, object>("quorDeliveryConfTime", quorDeliveryConfTime));
            toparams.Add(new KeyValuePair<string, object>("quorUpdateOn", quor_DeliveryConfTime));
            toparams.Add(new KeyValuePair<string, object>("quorid", qugeoId));
            string fstostatus = $"UPDATE qugeo_order SET quor_Type = 6, quor_DeliveryConfTime = @quorDeliveryConfTime,quor_Status = 15, quor_UpdateOn = @quorUpdateOn WHERE quor_id = @quorid";
            DataServiceMySql.ExecuteSql(fstostatus, UserService.GetAPIConnectionString(), toparams);

            toparams = new List<KeyValuePair<string, object>>();
            toparams.Add(new KeyValuePair<string, object>("updateat", quor_DeliveryConfTime));
            toparams.Add(new KeyValuePair<string, object>("orderid", orderID));
            string updateQry = $"UPDATE retaline_customer_order SET status_id = 18, order_status_addinfo = '###2', payment_mode = IF(payment_mode=1,'1',payment_mode), order_ondel_bankref_id = '###7', " +
                $"updated_at = @updateat WHERE order_id = @orderid ";
            DataServiceMySql.ExecuteSql(updateQry, UserService.GetAPIConnectionString(), toparams);

            toparams = new List<KeyValuePair<string, object>>();
            toparams.Add(new KeyValuePair<string, object>("updateat", quor_DeliveryConfTime));
            toparams.Add(new KeyValuePair<string, object>("orderid", orderID));
            string intQry = $"INSERT INTO retaline_customer_order_history(order_id, order_status, created_at, updated_at) " +
                    $"VALUES(@orderid, 18, @updateat, @updateat)";
            DataServiceMySql.ExecuteSql(intQry, UserService.GetAPIConnectionString(), toparams);

            try
            {
                //var result = DeliveryService.DeliveryVoucher(fsto_id, UserService.GetAPIConnectionString(), this.CurrentUser.APIStoreId).ConfigureAwait(false);
                //await result;
                //order cancellation call

                string url = ConfigurationSettings.AppSettings.Get("api.url");
                if (String.IsNullOrEmpty(url))
                {
                    url = "http://bizapi.dev.grozeo.in";
                }

                var options = new RestClientOptions(url);

                var client = new RestClient(options);

                var request = new RestRequest("/api/finascop/finascopPostingService", Method.Post);
                request.AlwaysMultipartFormData = true;
                request.AddParameter("order_id", order_id);
                request.AddParameter("finascopEventRefId", "07802425-38d7-11ee-9967-065723bafb24");
                request.AddParameter("storegroup_id", this.CurrentUser.APIStoreId);
                RestResponse response = client.ExecuteAsync(request).Result;

                var DeliveryConfirmationRequest = new RestRequest("/api/finascop/finascopPostingService", Method.Post);
                DeliveryConfirmationRequest.AlwaysMultipartFormData = true;
                DeliveryConfirmationRequest.AddParameter("order_id", order_id);
                DeliveryConfirmationRequest.AddParameter("finascopEventRefId", "07802530-38d7-11ee-9967-065723bafb24");
                DeliveryConfirmationRequest.AddParameter("storegroup_id", this.CurrentUser.APIStoreId);
                RestResponse DeliveryConfirmationResponse = client.ExecuteAsync(DeliveryConfirmationRequest).Result;


                if (quor_AmountCollectible > 0)
                {
                    //var result3 = PayOnDelivery.PODVoucher(fsto_id, UserService.GetAPIConnectionString(), this.CurrentUser.APIStoreId).ConfigureAwait(false);
                    //await result3;
                    //var PayOnDeliveryRequest = new RestRequest($"/api/finascop/finascopPostingService/{order_id}/", Method.Get);
                    //RestResponse PayOnDeliveryResponse = client.ExecuteAsync(PayOnDeliveryRequest).Result;
                    //Console.WriteLine(PayOnDeliveryResponse.Content);
                }
            }
            catch (Exception ex)
            {
                string strError = ex.Message;
            }

            Common.ShowCustomAlert(this.Page, "Delivered Successfully!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Your items delivered successfully!</a></h5>", true, "/Tenant/MerchantDelivery");
            
            
            string ordNum = tblOrdInfo.Rows[0]["order_order_id"].ToString();
            List<KeyValuePair<string, object>> custparams = new List<KeyValuePair<string, object>>();
            custparams.Add(new KeyValuePair<string, object>("orderId", ordNum));
            custparams.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));
            var custTbl = DataServiceMySql.GetDataTable($"SELECT order_order_id, order_customer_id, cust_email, co.storegroup_id, store_group_name, cust_mobile, cust_customer_name FROM retaline_customer_order co INNER JOIN finascop_branch_group ON store_group_id = co.storegroup_id INNER JOIN retaline_customer ON cust_id = order_customer_id WHERE order_order_id = @orderId",
                    UserService.GetAPIConnectionString(), custparams);
            if (custTbl == null || custTbl.Rows.Count <= 0)
            {
                Common.ShowCustomAlert(this.Page, "Failure", "Invalid order or the process cannot execute at this time.", false, "/Tenant/MerchantDelivery");
                return;
            }
            string name = custTbl.Rows[0]["cust_customer_name"].ToString();
            string storeName = custTbl.Rows[0]["store_group_name"].ToString();
            string email = custTbl.Rows[0]["cust_email"].ToString();
            var emailresult = Core.Services.APIService.OrdDelivConfEmail(name, email, storeName, ordNum);
        }

    }
}


