using Newtonsoft.Json;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using Finascop.BussinessModel;
using Finascop.BussinessModel.Finascop;
using System.Data.SqlTypes;
using RestSharp;

namespace RetalineProAgent.Finance
{
    public partial class CostCentreEntry : System.Web.UI.Page
    {
        [Serializable]
        private class CostCentreEntryView
        {

            /// <summary>
            /// CostCentreRule
            /// </summary>
            public string CostCentreRule { get; set; }            
            /// <summary>
            /// Transaction ID of Data entry
            /// </summary>           
            public int TransactionId { get; set; }
            /// <summary>
            /// LedgerId
            /// </summary>
            public int LedgerId { get; set; }
            /// <summary>
            /// Cost Centre Name
            /// </summary>
            public string costCentreName { get; set; }
            /// <summary>
            /// Cost Centre id
            /// </summary>
            public int costCentreId { get; set; }
            /// <summary>
            /// Cost Allocation Amount
            /// </summary>
            public double amount { get; set; }
            /// <summary>
            /// Particulars
            /// </summary>
            public string particulars { get; set; }
            /// <summary>
            /// Is Debit
            /// </summary>
            public int isDebtor { get; set; }
        }

        private CostCentreLogData ccLogData
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

        private List<CostCentreEntryView> ccData
        {
            get
            {
                if (ViewState["CCENTRYLIST"] != null)
                    return (List<CostCentreEntryView>)ViewState["CCENTRYLIST"];
                return new List<CostCentreEntryView>();
            }
            set
            {
                ViewState["CCENTRYLIST"] = value;
            }
        }

        private String ETag
        {
            get
            {
                if (ViewState["ETAG"] != null)
                    return (String)ViewState["ETAG"];
                return "";
            }
            set
            {
                ViewState["ETAG"] = value;
            }
        }
        private String RowKey
        {
            get
            {
                if (ViewState["ROWKEY"] != null)
                    return (String)ViewState["ROWKEY"];
                return "";
            }
            set
            {
                ViewState["ROWKEY"] = value;
            }
        }
        [Serializable]
        private class CostCentre
        {
            public string CostCentreName { get; set; }
  
            public int CostCentreId { get; set; }
        }

        private List<CostCentre> CostCentres
        {
            get
            {
                if (ViewState["CCENTRYLIST"] != null)
                    return (List<CostCentre>)ViewState["COSTCENTRE"];
                return new List<CostCentre>();
            }
            set
            {
                ViewState["COSTCENTRE"] = value;
            }
        }

        private bool editingCostCentreEntry
        {
            get
            {
                if (ViewState["EDITING"] != null)
                    return (bool)ViewState["EDITING"];
                return false;
            }
            set
            {
                ViewState["EDITING"] = value;
            }
        }

        private ListViewItem selectedItem;

        protected void Page_Load(object sender, EventArgs e)
        {
            
            if (!IsPostBack)
            {
                if (!String.IsNullOrEmpty(Request.QueryString["ETag"]))
                {
                    ETag = Request.QueryString["ETag"];
                }

                if (!String.IsNullOrEmpty(Request.QueryString["RowKey"]))
                {
                    RowKey = Request.QueryString["RowKey"];
                }
                if (!String.IsNullOrEmpty(Request.QueryString["Data"]))
                {
                    ShowDiv.Visible = false;

                    string costCentreQry = "SELECT id AS costCentreId, name AS costCentreName FROM [cost_centre]";
                    //DataServiceMySql.GetDataTable()
                    var dtCostCentre = DataService.GetDataTable(costCentreQry, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString);
                    if (dtCostCentre == null || dtCostCentre.Rows.Count <= 0)
                    {
                        Common.ShowCustomAlert(this.Page, "Error", "No Cost Centers found in db.", false);

                        return;
                    }
                    else
                    {
                        CostCentres = Newtonsoft.Json.JsonConvert.DeserializeObject<List<CostCentre>>(JsonConvert.SerializeObject(dtCostCentre));
                    }

                    var encodedData = Request.QueryString["Data"];
                    var decodedData = HttpUtility.UrlDecode(encodedData);
                    ccLogData = Newtonsoft.Json.JsonConvert.DeserializeObject<CostCentreLogData>(decodedData, new JsonSerializerSettings { PreserveReferencesHandling = PreserveReferencesHandling.Objects });

                    TransactionData trdata = (TransactionData)ccLogData.CostCentre[0];
                    List<Finascop.BussinessModel.CostCentreEntry> tdata = (List<Finascop.BussinessModel.CostCentreEntry>)trdata.CostCentreEntries;
                    lbLedger.Text = (trdata.isDebtor == 1 ? "Debit : " : "Credit : ") + trdata.particulars + " : " + trdata.amount;
                    lbCostCentreRule.Text = ccLogData.costCentreRule;

                    ccData = new List<CostCentreEntryView>();
                    ccData = tdata.Select((item, index) => new CostCentreEntryView
                    {
                        TransactionId = Convert.ToInt32(trdata.transaction_id),
                        LedgerId = item.ledgerId,
                        costCentreId = item.costCentreId,
                        costCentreName = item.costCentreName,
                        amount = item.amount,
                        isDebtor = item.isDebtor,
                        particulars = item.particulars,
                    }).ToList();


                    lvCostCentreEntry.DataSource = ccData;
                    lvCostCentreEntry.DataBind();

                    editingCostCentreEntry = false;
                }
            }
        }

        protected void Page_PreRender(object sender, EventArgs e)
        {
            try
            {
                lvCostCentreEntry.DataSource = ccData;
                lvCostCentreEntry.DataBind();
            }
            catch(Exception)
            {


            }

        }

        protected void lvCostCentreEntry_ItemCommand(object sender, ListViewCommandEventArgs e)
        {
            int index = 0;
            if (e.CommandName == "Delete")
            {
                var ventry = ccData;
                var item = ventry[e.Item.DataItemIndex];
                ventry.RemoveAt(e.Item.DataItemIndex);
                ccData = ventry;
                lvCostCentreEntry.DataSource = ccData;
                lvCostCentreEntry.EditIndex = -1;
                lvCostCentreEntry.DataBind();
                index = (e.Item.DataItemIndex > 0) ? e.Item.DataItemIndex - 1 : e.Item.DataItemIndex;
                {
                    string script = "selectAndScrollToRow(" + index + ");";
                    ScriptManager.RegisterStartupScript(this, GetType(), "FocusScript", script, true);
                }
                return;
            }


            if (e.CommandName == "Update")
            {
                var ventry = ccData;
                var item = ventry[e.Item.DataItemIndex];

                TextBox txtamount = (TextBox)e.Item.FindControl("txbAmount");
                DropDownList dlCostCentre = (DropDownList)e.Item.FindControl("ddlCostCentre");

                item.amount = Convert.ToDouble(txtamount.Text);
                item.costCentreId = Convert.ToInt32(dlCostCentre.SelectedValue);
                item.costCentreName = dlCostCentre.SelectedItem.Text;

                ventry[e.Item.DataItemIndex] = item;
                ccData = ventry;

                lvCostCentreEntry.DataSource = ccData;
                lvCostCentreEntry.DataBind();

                editingCostCentreEntry = false;
                index = e.Item.DataItemIndex;
                {
                    string script = "selectAndScrollToRow(" + index + ");";
                    ScriptManager.RegisterStartupScript(this, GetType(), "FocusScript", script, true);
                }
                lvCostCentreEntry.EditIndex = -1;
                return;

            }

            if (e.CommandName == "Edit")
            {
                editingCostCentreEntry = true;
                index = e.Item.DataItemIndex;
                selectedItem = lvCostCentreEntry.Items[index];
            }

            if (e.CommandName == "AddRowAbove" || e.CommandName == "AddRowBelow")
            {
                editingCostCentreEntry = true;
                

                var ventry = ccData;
                
                var item = ventry[e.Item.DataItemIndex];

                var newItem = new CostCentreEntryView();
                newItem.TransactionId = item.TransactionId;
                newItem.LedgerId = item.LedgerId;
                newItem.costCentreId = 0;
                newItem.costCentreName = "Select Cost Centre";
                newItem.amount = 0.00;

                index = (e.CommandName == "AddRowAbove") ? e.Item.DataItemIndex : e.Item.DataItemIndex + 1;
                

                ventry.Insert(index, newItem);
                ccData = ventry;

                lvCostCentreEntry.EditIndex = -1;
                lvCostCentreEntry.DataSource = ccData;
                lvCostCentreEntry.DataBind();

                lvCostCentreEntry.EditIndex = index;

                selectedItem = lvCostCentreEntry.Items[index];

            }

            if (selectedItem != null)
            {
                string script = "$('#" + selectedItem.ClientID + "_ddlCostCentre_" + index + "').focus(); scrollToRow(" + index + ");";
                ScriptManager.RegisterStartupScript(this, GetType(), "FocusScript", script, true);
            }

        }
        protected void lvCostCentreEntry_ItemDataBound(object sender, ListViewItemEventArgs e)
        {

            if (!editingCostCentreEntry)
            {
                var ventry = ccData;
                var item = ventry[e.Item.DataItemIndex];
                if (item != null)
                {

                    CostCentre costCentre = CostCentres.FirstOrDefault(cc => cc.CostCentreId == item.costCentreId);

                    if (costCentre == null)
                    {
                        item.costCentreId = 0;
                        item.costCentreName = "Select Cost Centre";

                        ventry[e.Item.DataItemIndex] = item;
                        ccData = ventry;

                        lvCostCentreEntry.DataSource = ccData;
                        lvCostCentreEntry.DataBind();
                    }
                }
            }
            return;
        }

        protected void lvCostCentreEntry_DataBound(object sender, EventArgs e)
        {
            double total = ccData.Sum(item => item.amount);
            ltrTotal.Text = Convert.ToDouble(total) != 0 ? total.ToString("0.00") : "0.00";
        }
        protected void btnsave_Click(object sender, EventArgs e)
        {
            var invalidCCData = ccData.Where(costCentre => costCentre.costCentreId == 0).ToList();
            if (invalidCCData.Count > 0)
            {
                Common.ShowCustomAlert(this.Page, "Error", "Invalid Cost Centers found in List. Please save update or delete.", false);
            }

            List<Finascop.BussinessModel.CostCentreEntry> tdata = new List<Finascop.BussinessModel.CostCentreEntry>();
            tdata = ccData.Select((item, index) => new Finascop.BussinessModel.CostCentreEntry
            {
                costCentreRule = item.CostCentreRule,
                costCentreId = item.costCentreId,
                costCentreName = item.costCentreName,
                amount = item.amount,
                isDebtor = item.isDebtor,
                ledgerId = item.LedgerId,
                particulars = item.particulars

            }).ToList();

            ccLogData.CostCentre[0].CostCentreEntries = tdata;
            var data = JsonConvert.SerializeObject(ccLogData);
            try
            {
                RestResponse res = null;
                try
                {
                    string url = ConfigurationSettings.AppSettings.Get("FinascopAPIUrl");
                    if (String.IsNullOrEmpty(url))
                        url = "https://finascopdataentry.azurewebsites.net/api/";
                    url += "AddCostCentreEntries";

                    string key = ConfigurationSettings.AppSettings.Get("FinascopAPIKey");
                    if (String.IsNullOrEmpty(key))
                        key = "P_5JtNckvvxLTUM6cF9py_7ZYIA5QM9ofmNaDvh__HoqAzFuAbEyZQ==";
                    var client = new RestClient(url);
                    var request = new RestRequest();
                    request.Method = RestSharp.Method.Post;
                    request.AddHeader("content-type", "application/json");
                    request.AddHeader("x-functions-key", key);

                    //request.AddBody("{" + content + "}", "application/json");
                    request.AddBody(data, "application/json");
                    res = client.ExecuteAsync<Result>(request).Result;

                }
                catch (Exception ex)
                {
                    Common.ShowCustomAlert(this.Page, "Exception !", "Exception Thrown. ", false);
                }

                Result result = null;
                if (res.StatusCode == System.Net.HttpStatusCode.OK) // Check if the request was successful
                {
                    result = Newtonsoft.Json.JsonConvert.DeserializeObject<Result>(res.Content);
                    if (result.statusId == ResultType.Success)
                    {
                        Common.ShowCustomAlert(this.Page, "Success", "Successfully updated cost centre log entry.", true);
                        return;
                    }
                    else
                    {
                        Common.ShowCustomAlert(this.Page, "Error", "Failed to update cost centre log entry.", false);
                        return;
                    }
                }
                else
                {
                    Common.ShowCustomAlert(this.Page, "Server Error", res.StatusDescription.ToString(), false);
                }
            }
            catch (Exception ex)
            {

                Common.ShowCustomAlert(this.Page, "Exception Thrown.", "", false);
                return;

            }


        }

        protected void gvpopup_DataBound(object sender, EventArgs e)
        {

        }
        protected void rptupdate_DataBinding(object sender, EventArgs e)
        {

        }
        protected void savemod_Click(object sender, EventArgs e)
        {
                return;
        }

        protected void lvCostCentreEntry_ItemEditing(object sender, ListViewEditEventArgs e)
        {
           
        }

        protected void lvCostCentreEntry_ItemUpdating(object sender, ListViewUpdateEventArgs e)
        {
            return;
        }

        protected void lvCostCentreEntry_ItemCanceling(object sender, ListViewCancelEventArgs e)
        {
            lvCostCentreEntry.EditIndex = -1;
        }

        protected void lvCostCentreEntry_ItemDeleting(object sender, ListViewDeleteEventArgs e)
        {

        }

        protected void ddlCostCentre_SelectedIndexChanged(object sender, EventArgs e)
        {

        }

        protected void lvCostCentreEntry_SelectedIndexChanging(object sender, ListViewSelectEventArgs e)
        {


        }
    }
}