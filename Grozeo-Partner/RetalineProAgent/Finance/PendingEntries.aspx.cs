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
using RetalineProAgent.Core.Services.ActiveLog;

namespace RetalineProAgent.Finance
{
    public partial class PendingEntries : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            txtFromDate.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
            txtToDate.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));

            //txtFromDate.Text = DateTime.Now.ToString("yyyy-MM-dd");
            //txtToDate.Text = DateTime.Now.ToString("yyyy-MM-dd");
            if (!IsPostBack)
            {                

                string transactionId = Request.QueryString["id"];

                if (!string.IsNullOrEmpty(transactionId))
                {
                    int Id = Convert.ToInt32(transactionId);
                    btnapprove.Attributes.Add("recid", Id.ToString());
                    btnreject.Attributes.Add("recid", Id.ToString());
                    btnyes.Attributes.Add("recid", Id.ToString());
                    btnsubmit.Attributes.Add("recid", Id.ToString());
                    List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
                    sqldaId.Add(new KeyValuePair<string, object>("id", Id));
                    string requestBody = "select status,comments from finascop_log where id = @Id";
                    //DataServiceMySql.GetDataTable()
                    var refid = DataService.GetDataTable(requestBody, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqldaId);
                    if (refid != null && refid.Rows.Count > 0)
                    {
                        var logid = refid.Rows[0];
                        string transactiondata = logid["comments"].ToString();                        
                        TransactionEntry data = JsonConvert.DeserializeObject<TransactionEntry>(transactiondata, new JsonSerializerSettings { PreserveReferencesHandling = PreserveReferencesHandling.Objects });
                        List<TransactionData> tdata = data.Account;
                        tdata.AddRange(data.Particulars);
                        //var list = new List<object> { data };
                        lvDataEny.DataSource = tdata;
                        lvDataEny.DataBind();
                        lbNarration.Text = data.narration;

                        Literal ltrdrtotal = (Literal)lvDataEny.FindControl("ltrDrTotal");
                        Literal ltrcrtotal = (Literal)lvDataEny.FindControl("ltrCRTotal");
                        if (ltrdrtotal != null && ltrcrtotal != null)
                        {
                            double drtotal = 0, crtotal = 0;
                            try { drtotal = tdata.Sum(d => d.isDebtor == 1 ? d.amount : 0); } catch { }
                            try { crtotal = tdata.Sum(d => d.isDebtor == 1 ? 0 : d.amount); } catch { }

                            ltrdrtotal.Text = String.Format("{0:0.00}", drtotal).ToString();
                            ltrcrtotal.Text = String.Format("{0:0.00}", crtotal).ToString();
                        }
                        lbstoregroup.Text = data.br_Name_store_group;
                        string status = logid["status"].ToString();
                        btnsupreject.Visible = (status == "10");
                        lblname.Text = (status == "10") ? "Suspense Entry" : "Store Group Name :";

                    }

                    string strAlertSCript = "$('#priviewledgerpopup').modal('show');";

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
            btnapprove.Attributes.Add("recid", Id.ToString());
            btnreject.Attributes.Add("recid", Id.ToString());
            btnyes.Attributes.Add("recid", Id.ToString());
            btnsubmit.Attributes.Add("recid", Id.ToString());
            List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
            sqldaId.Add(new KeyValuePair<string, object>("id", Id));
            string requestBody = "select status, comments from finascop_log where id = @Id";
            //DataServiceMySql.GetDataTable()
            var refid = DataService.GetDataTable(requestBody, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqldaId);
            if (refid != null && refid.Rows.Count > 0)
            {
                var logid = refid.Rows[0];
                string transactiondata = logid["comments"].ToString();
                TransactionEntry data = JsonConvert.DeserializeObject<TransactionEntry>(transactiondata, new JsonSerializerSettings { PreserveReferencesHandling = PreserveReferencesHandling.Objects });
                List<TransactionData> tdata = data.Account;
                tdata.AddRange(data.Particulars);

                //var list = new List<object> { data };
                lvDataEny.DataSource = tdata;
                lvDataEny.DataBind();
                lbNarration.Text = data.narration;

                Literal ltrdrtotal = (Literal)lvDataEny.FindControl("ltrDrTotal");
                Literal ltrcrtotal = (Literal)lvDataEny.FindControl("ltrCRTotal");
                if (ltrdrtotal != null && ltrcrtotal != null)
                {
                    double drtotal = 0, crtotal = 0;
                    try { drtotal = tdata.Sum(d => d.isDebtor == 1 ? d.amount : 0); } catch { }
                    try { crtotal = tdata.Sum(d => d.isDebtor == 1 ? 0 : d.amount); } catch { }

                    ltrdrtotal.Text = String.Format("{0:0.00}", drtotal).ToString();
                    ltrcrtotal.Text = String.Format("{0:0.00}", crtotal).ToString();
                }
                lbstoregroup.Text = data.br_Name_store_group;
                string status = logid["status"].ToString();
                btnsupreject.Visible = (status == "9");
                lblname.Text = (status == "9") ? "Suspense Entry" : "Store Group Name :";


            }

            string strAlertSCript = "$('#priviewledgerpopup').modal('show');";

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
                // Activitylog
                String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                string Source = strUrl;
                int storegroupid = -1;
                string User = "Finance Admin";
                string id = Convert.ToString(Id);
                string posting = Convert.ToString(content);
                string page = "Pending Entries";
                var items = new[]
                {                          
                            new { Key = "Id", Value = id },                           
                            new { Key = "Posting", Value = posting },
                            new { Key = "page", Value = page },
                        };
                string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
                gvpending.DataBind();
            }
            else
            {
                Common.ShowCustomAlert(this.Page, "Error", "", false);
            }
        }
        // posting correction
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
            string commentsshow = "select comments from finascop_log where id = @Id";
            var refcomment = DataService.GetDataTable(commentsshow, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqldaId);
            if (refcomment != null && refcomment.Rows.Count > 0)
            {
                var logid = refcomment.Rows[0];
                ltrcomment.Text = logid["comments"].ToString();

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

        protected void btnsearch_Click(object sender, EventArgs e)
        {
            
        }

        protected void btnsubmit_Click(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;
            int Id = Convert.ToInt32(lbtn.Attributes["recid"]);
            List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
            sqldaId.Add(new KeyValuePair<string, object>("id", Id));
            sqldaId.Add(new KeyValuePair<string, object>("reason", txtreason.InnerText));
            string updatreject = "UPDATE finascop_log SET description=@reason, status=10 WHERE id=@Id";
            int result = DataService.ExecuteSql(updatreject, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, sqldaId);
            Common.ShowCustomAlert(this.Page, "Success", "successfully Rejected!", true, "/Finance/PendingEntries");
        }
    }
}