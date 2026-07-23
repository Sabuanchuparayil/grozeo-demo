using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Threading.Tasks;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using Amazon.DynamoDBv2;
using Amazon.DynamoDBv2.Model;
using RetalineProAgent.Core.BussinessModel.Order;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Navigations;
using RetalineProAgent.Service;
using StackExchange.Redis;
namespace RetalineProAgent.Business
{ 
    public partial class PackingDelayOrders : Base.BasePartnerPage
    {
        private int Filter_by_StoreId
        {
            get
            {
                if (ViewState["STOREFILTER "] == null)
                    return 0;
                return (int)ViewState["STOREFILTER"];
            }
            set
            {
                ViewState["STOREFILTER"] = value;
            }
        }
        protected void Page_Load(object sender, EventArgs e)
        {

        }
        protected void Page_PreRender(object sender, EventArgs e)
        {
            BindGrid();
        }
        protected void gvPackingFailedOrders_RowDataBound(object sender, GridViewRowEventArgs e)
        {

        }

        protected void gvPackingFailedOrders_PageIndexChanging(object sender, GridViewPageEventArgs e)
        {
            gvPackingFailedOrders.PageIndex = e.NewPageIndex;
            BindGrid();

        }

        protected void selBranches_SelectedIndexChanged(object sender, EventArgs e)
        {
            int brId = 0;
            try
            {
                if (!string.IsNullOrEmpty(selBranches.SelectedValue))
                    brId = Convert.ToInt32(selBranches.SelectedValue);
            }
            catch { brId = 0; }

            Filter_by_StoreId = brId;
        }

        protected void lbtnSearch_Click(object sender, EventArgs e)
        {

        }
        private void BindGrid()
        {
            List<PendingOrder> data = (List<PendingOrder>)ODSPackingDelayedOrders.Select();
                                   
            if (!IsPostBack)
            {
                selBranches.DataSource = data.Select(o => new { StoreId = o.BranchID, StoreName = o.MerchantName }).Distinct();
                selBranches.DataBind();
            }

            if (Filter_by_StoreId > 0)
            {
                data = data.Where(o => o.BranchID == Filter_by_StoreId.ToString()).ToList();
            }

            if (!String.IsNullOrEmpty(txtOrderId.Text))
            {
                data = data.Where(o => o.OrderOrderID == txtOrderId.Text).ToList();
            }

            gvPackingFailedOrders.DataSource = data;
            gvPackingFailedOrders.DataBind();

        }

        protected void lbtnFetchOrderPicker_Click(object sender, EventArgs e)
        {

            LinkButton btn = (LinkButton)sender;
            hidOrderId.Value = (btn.CommandArgument).ToString();
                       
            ClientScript.RegisterStartupScript(GetType(), "Show", "<script> $('#modalOrderPicker').modal('toggle');</script>");

        }
        protected void lbtnClicktocall_Click(object sender, EventArgs e)
        {
            LinkButton btn = (LinkButton)sender;
            string phoneNumber = btn.CommandArgument;

            if (!string.IsNullOrEmpty(phoneNumber) && phoneNumber.StartsWith("+"))
            {
                phoneNumber = phoneNumber.TrimStart('+');
            }

            var result = Core.Services.APIService.ClickToCallAPI_VoxBay(phoneNumber);
         }

        protected void lbtnViewOrderDetails_Click(object sender, EventArgs e)
        {
            LinkButton btn = (LinkButton)sender;
            string OrderID = btn.CommandArgument;

            hdOrderId.Value = OrderID;

            SDSViewOrderDetails.SelectParameters["orderId"].DefaultValue = OrderID;

            ClientScript.RegisterStartupScript(GetType(), "Show", "<script> $('#modalViewOrderDetails').modal('toggle');</script>");

        }
        protected void lbtnCancelOrder_Click(object sender, EventArgs e)
        {
            LinkButton btn = (LinkButton)sender;
            string OrderID = btn.CommandArgument;

            GridViewRow row = (GridViewRow)btn.NamingContainer;

            LinkButton uuidLinkButton = (LinkButton)row.FindControl("uuidLinkButton");
            string UUID = uuidLinkButton.CommandArgument;

            LinkButton tstampLinkButton = (LinkButton)row.FindControl("tstampLinkButton");
            string tstamp = tstampLinkButton.CommandArgument;

            LinkButton BranchIdLinkButton = (LinkButton)row.FindControl("BranchIdLinkButton");
            string BranchID = BranchIdLinkButton.CommandArgument;

            LinkButton MerDetailsLinkButton = (LinkButton)row.FindControl("MerDetailsLinkButton");
            string MerchantDetails= MerDetailsLinkButton.CommandArgument;

            hidCanOrderId.Value = OrderID;
            hidUuid.Value = UUID;
            hidtstamp.Value = tstamp;
            hidStoreId.Value = BranchID;
            hidMerDetails.Value = MerchantDetails;

            ClientScript.RegisterStartupScript(GetType(), "Show", "<script> $('#modalCancelOrder').modal('toggle');</script>");
        }

        protected async void btnYes_Click(object sender, EventArgs e)
        {
            string orderId = hidCanOrderId.Value;
            string UUID = hidUuid.Value;
            string tstamp = hidtstamp.Value;
            string storeId = hidStoreId.Value;
            string MerDetails = hidMerDetails.Value;
            int custId = 0;
            int OrdID = 0;

            List<KeyValuePair<string, object>> CustParams = new List<KeyValuePair<string, object>>
            {
              new KeyValuePair<string, object>("OrderId", orderId),
              
            };

            string custdetalis = "SELECT order_id,order_customer_id FROM retaline_customer_order WHERE order_order_id=@orderId";
            DataTable custItemTbl = DataServiceMySql.GetDataTable(custdetalis, UserService.GetAPIConnectionString(), CustParams);
            DataRow dc = custItemTbl.Rows[0];
            custId = Convert.ToInt32(dc["order_customer_id"]);
            OrdID= Convert.ToInt32(dc["order_id"]);

            try
            {
                List<KeyValuePair<string, object>> InsertParams = new List<KeyValuePair<string, object>>
                {
                  new KeyValuePair<string, object>("orderId", OrdID),
                  new KeyValuePair<string, object>("customerId", custId),
                  new KeyValuePair<string, object>("reason", ddlCancelReason.SelectedItem),
                  new KeyValuePair<string, object>("cancelledId", this.CurrentUser.Id),
                  new KeyValuePair<string, object>("created_at", DateTime.Now),
                };

                try
                {
                    string inrtQry = $"INSERT INTO retaline_customer_order_cancellationdets(customer_id, order_id, reason, cancelled_by_type, cancelled_by_id, created_at) VALUES(@customerId,@orderId, @reason, 5, @cancelledId, @created_at)";
                    var result = DataServiceMySql.ExecuteScalar(inrtQry, UserService.GetAPIConnectionString(), InsertParams);

                    await UpdateDynamoDbOrder(UUID, tstamp, 18);

                    // Activity log
                    String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                    String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                    string Source = strUrl;

                    string Users = this.CurrentUser.Email;
                    int storegroup = Convert.ToInt32(storeId);
                    string orderid = orderId;
                    string remarks = $"The order '{orderid}' is cancelled by '{Users}' from store group '{storegroup}' after taking approval from '{MerDetails}' at {DateTime.Now}";
                    var strresult = Activitylog.ActivitylogAsync(storegroup, Source, Users, remarks);

                    Common.ShowCustomAlert(this.Page, "Success!", "The order has been successfully submitted for cancellation", true, "/Business/PackingDelayOrders");
                }
                catch (Exception ex)
                {
                    Common.ShowCustomAlert(this.Page, "Failed!", "Order cancellation failed. Please retry again later", false, "/Business/PackingDelayOrders");
                    return;
                }
            }
            catch (Exception ex)
            {
                // Handle outer exception
                Common.ShowCustomAlert(this.Page, "Error!", "An unexpected error occurred. Please contact support.", false);
            }
        }

        public static async Task UpdateDynamoDbOrder(string UID, string Tstamp, int action)
        {
            try
            {
                string tableprefix = ConfigurationManager.AppSettings.Get("AWS_Prefix");
                string table = "delayed_orders";  
                string tableName = String.Concat(tableprefix, table);

                var key = new Dictionary<string, AttributeValue>
                {
                  { "uuid", new AttributeValue { S = UID } },
                  { "tstamp", new AttributeValue { S = Tstamp } }
                };

                
                var attributeUpdates = new Dictionary<string, AttributeValueUpdate>
            {
                { "skipDate", new AttributeValueUpdate
                    {
                        Action = AttributeAction.PUT,
                        Value = new AttributeValue { S = DateTime.Now.AddHours(24).ToString("yyyy-MM-dd HH:mm:ss") } // Set skipDate to today's date, or pass your desired value
                    }
                },
                { "action", new AttributeValueUpdate
                    {
                        Action = AttributeAction.PUT,
                        Value = new AttributeValue { N = action.ToString() } // Set action to the passed integer value
                    }
                }
            };

                // Call the DynamoDB update method to apply the updates
                await DynamoService.UpdateToDynamoDb(tableName, key, attributeUpdates);
            }
            catch (Exception ex)
            {
                // Log or rethrow the exception as needed
                throw new Exception("Error updating order in DynamoDB", ex);
            }
        }
    }
}
