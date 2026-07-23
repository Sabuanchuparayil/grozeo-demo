using Finascop.BussinessModel;
using RetalineProAgent.Service;
using Google.Protobuf.WellKnownTypes;
using MySql.Data.MySqlClient.Memcached;
using Newtonsoft.Json;
using NPOI.POIFS.Crypt.Dsig;
using NPOI.POIFS.Properties;
using NPOI.SS.Formula.Functions;
using NPOI.Util;
using Org.BouncyCastle.Asn1.Crmf;
using Org.BouncyCastle.Ocsp;
//using RestSharp;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data.SqlTypes;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
//using Method = RestSharp.Method;
using RetalineProAgent.Core.Services;
using static NPOI.HSSF.Util.HSSFColor;
using RestSharp;

namespace RetalineProAgent.Finance
{
    public partial class CostCentreLogView : Base.BasePartnerPage
    {
                private List<CostCentreEntryView> ccViewData
        {
            get
            {
                if (ViewState["COSTCENTREENTRYLIST"] != null)
                    return (List<CostCentreEntryView>)ViewState["COSTCENTREENTRYLIST"];
                return new List<CostCentreEntryView>();
            }
            set
            {
                ViewState["COSTCENTREENTRYLIST"] = value;
            }
        }
        private CostCentreLogData ccData
        {
            get
            {
                if (ViewState["COSTCENTRELOGDATA"] != null)
                    return (CostCentreLogData)ViewState["COSTCENTRELOGDATA"];
                return new CostCentreLogData();
            }
            set
            {
                ViewState["COSTCENTRELOGDATA"] = value;
            }
        }
        protected void Page_Load(object sender, EventArgs e)
        {
            txtFromDate.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
            txtToDate.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));

            //txtFromDate.Text = DateTime.Now.ToString("yyyy-MM-dd");
            //txtToDate.Text = DateTime.Now.ToString("yyyy-MM-dd");
            if (!IsPostBack)
            {
                txtFromDate.Text = DateTime.Now.AddDays(-30).ToString("yyyy-MM-dd");
                txtToDate.Text = DateTime.Now.ToString("yyyy-MM-dd");
            }
        }


        protected void gvpending_RowDataBound(object sender, GridViewRowEventArgs e)
        {

        }

        protected void gvpending_DataBound(object sender, EventArgs e)
        {

        }

        protected void gvpending_PageIndexChanged(object sender, EventArgs e)
        {

        }

        protected void lvDataEny_DataBound(object sender, EventArgs e)
        {

        }

        protected void btnaction_Click1(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;
            int Id = Convert.ToInt32(lbtn.Attributes["recid"]);
            btnyes.Attributes.Add("recid", Id.ToString());
            List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
            sqldaId.Add(new KeyValuePair<string, object>("id", Id));
            string requestBody = "select costCentreLogData from cost_centre_log where id = @Id";
            var refid = DataService.GetDataTable(requestBody, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqldaId);
            if (refid != null && refid.Rows.Count > 0)
            {
                var logid = refid.Rows[0];
                string costCentreData = logid["costCentreLogData"].ToString();
                //TransactionEntry data = JsonConvert.DeserializeObject<TransactionEntry>(transactiondata, new JsonSerializerSettings { PreserveReferencesHandling = PreserveReferencesHandling.Objects });
                ccData = JsonConvert.DeserializeObject<CostCentreLogData>(costCentreData, new JsonSerializerSettings { PreserveReferencesHandling = PreserveReferencesHandling.Objects });
                TransactionData trdata = (TransactionData)ccData.CostCentre[0];
                List<Finascop.BussinessModel.CostCentreEntry> tdata = (List<Finascop.BussinessModel.CostCentreEntry>)trdata.CostCentreEntries;
                lbLedger.Text = (trdata.isDebtor == 1 ? "Debit :" : "Credit : ") + trdata.particulars + ": " + trdata.amount;
                lbCostCentreRule.Text = ccData.costCentreRule;

                ccViewData = new List<CostCentreEntryView>();
                ccViewData = tdata.Select((item, index) => new CostCentreEntryView
                {
                    TransactionId = Convert.ToInt32(trdata.transaction_id),
                    LedgerId = item.ledgerId,
                    costCentreId = item.costCentreId,
                    costCentreName = item.costCentreName,
                    amount = item.amount,
                }).ToList();

                if (ccViewData.Count <= 0)
                {
                    ccViewData.Add(new CostCentreEntryView
                    {
                        TransactionId = Convert.ToInt32(trdata.transaction_id),
                        LedgerId = (int)trdata.ledgerId,
                        costCentreId = 0,
                        costCentreName = "Select Cost Centre",
                        amount = 0.00
                    });
                }


                lvDataEny.DataSource = ccViewData;
                lvDataEny.DataBind();
            }

            string strAlertSCript = "$('#priviewcostcentreentrypopup').modal('show');";

            strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";

            System.Type cstype = this.GetType();
            String csname1 = "ShowConfirmPopup";
            ClientScriptManager cs = this.ClientScript;

            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
            cstext1.Append("script>");
            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }

        protected async void btnapprove_Click(object sender, EventArgs e)
        {
            Button lbtn = (Button)sender;
            if (lbtn == null || String.IsNullOrEmpty(lbtn.Attributes["recid"]))
            {
                Common.ShowCustomAlert(this.Page, "Error", "");
            }

            int Id = Convert.ToInt32(lbtn.Attributes["recid"]);
            List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
            sqldaId.Add(new KeyValuePair<string, object>("id", Id));
            string requestBody = "select comments from finascop_log where id = @Id";
            //DataServiceMySql.GetDataTable()
            var refid = DataService.GetDataTable(requestBody, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqldaId);
            if (refid == null || refid.Rows.Count <= 0)
            {
                Common.ShowCustomAlert(this.Page, "Error", "", false);

                return;
            }
            // approval of posting
            string content = refid.Rows[0]["comments"].ToString();
            RestResponse res = null;
            try
            {
                string url = ConfigurationSettings.AppSettings.Get("FinascopAPIUrl");
                if (String.IsNullOrEmpty(url))
                    url = "https://finascopdataentry.azurewebsites.net/api/";
                url += "FinascopDataEntry";

                string key = ConfigurationSettings.AppSettings.Get("FinascopAPIKey");
                if (String.IsNullOrEmpty(key))
                    key = "P_5JtNckvvxLTUM6cF9py_7ZYIA5QM9ofmNaDvh__HoqAzFuAbEyZQ==";
                var client = new RestClient(url);
                var request = new RestRequest();
                request.Method = RestSharp.Method.Post;
                request.AddHeader("content-type", "application/json");
                request.AddHeader("x-functions-key", key);

                //request.AddBody("{" + content + "}", "application/json");
                request.AddBody(content, "application/json");
                res = client.ExecuteAsync<Result>(request).Result;

            }
            catch (Exception ex)
            {
                //ex.Message


            }
            Result result = null;
            if (res.StatusCode == System.Net.HttpStatusCode.OK) // Check if the request was successful
            {
                // Deserialize the content to your object type
                result = Newtonsoft.Json.JsonConvert.DeserializeObject<Result>(res.Content);

                // Now you have your object (YourObjectType) from the response content
                // Work with responseObject here
            }
            if (result.statusId == ResultType.Success)
            {
                string statusid = "UPDATE finascop_log SET status=2 WHERE id=@Id";
                int results = DataService.ExecuteSql(statusid, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, sqldaId);
                gvpending.DataBind();
            }
            else
            {
                Common.ShowCustomAlert(this.Page, "Error", "", false);
            }
        }
        // posting correction

        protected void btnCorrect_Click(object sender, EventArgs e)
        {

            List<Finascop.BussinessModel.CostCentreEntry> costCentreEntries = new List<Finascop.BussinessModel.CostCentreEntry>();

            costCentreEntries = ccViewData.Select((item, index) => new Finascop.BussinessModel.CostCentreEntry
            {
                costCentreName = item.costCentreName,
                costCentreRule = ccData.costCentreRule,
                costCentreId = item.costCentreId,
                ledgerId = (int)ccData.CostCentre[0].ledgerId,
                transactionId = item.TransactionId,
                amount = item.amount,
                particulars = ccData.CostCentre[0].particulars,
                isDebtor = ccData.CostCentre[0].isDebtor,
            }).ToList();

            ccData.CostCentre[0].CostCentreEntries = costCentreEntries;

            var ccDataJson = Newtonsoft.Json.JsonConvert.SerializeObject(ccData);
            var ccDataObject = HttpUtility.UrlEncode(ccDataJson);

            Response.Redirect($"/Finance/CostCentreEntry?Data={ccDataObject}");
        }
        protected void btnreject_Click(object sender, EventArgs e)
        {

            Button lbtn = (Button)sender;
            int Id = Convert.ToInt32(lbtn.Attributes["recid"]);
            Response.Redirect("/Finance/VoucherEntry?Id=" + Id);
        }

        protected void btnyes_Click(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;
            int Id = Convert.ToInt32(lbtn.Attributes["recid"]);
            List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
            sqldaId.Add(new KeyValuePair<string, object>("id", Id));
            string statusid = "UPDATE finascop_log SET status=5 WHERE id=@Id";
            int result = DataService.ExecuteSql(statusid, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, sqldaId);
            Common.ShowCustomAlert(this.Page, "Success", "successfully Escalated!", true, "/Finance/PendingEntries");

        }

        protected void btncomment_Click(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;
            int Id = Convert.ToInt32(lbtn.Attributes["recid"]);
            List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
            sqldaId.Add(new KeyValuePair<string, object>("id", Id));
            string commentsshow = "select costCentreLogData from cost_centre_log where id = @Id";
            var refcomment = DataService.GetDataTable(commentsshow, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqldaId);
            if (refcomment != null && refcomment.Rows.Count > 0)
            {
                var logid = refcomment.Rows[0];
                ltrcomment.Text = logid["costCentreLogData"].ToString();

            }
            string strAlertSCript = "$('#Pupcomment').modal('show');";

            strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";

            System.Type cstype = this.GetType();
            String csname1 = "ShowConfirmPopup";
            ClientScriptManager cs = this.ClientScript;

            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
            cstext1.Append("script>");
            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }
    }
}