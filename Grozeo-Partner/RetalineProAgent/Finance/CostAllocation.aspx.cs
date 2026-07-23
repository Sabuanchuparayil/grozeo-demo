using RetalineProAgent.Service;
using NPOI.POIFS.Properties;
using Org.BouncyCastle.Asn1.Ocsp;
using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.Data;
using System.Data.SqlTypes;
using System.Diagnostics;
using System.Linq;
using System.Text.RegularExpressions;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Xml.Linq;
using RetalineProAgent.Core.Services;
using System.Configuration;
using NPOI.OpenXmlFormats.Dml.Diagram;
using RetalineProAgent.Core.BussinessModel.Finance;
using System.Web.Optimization;
using NPOI.POIFS.FileSystem;
using Org.BouncyCastle.Asn1.Crmf;
using Amazon.DynamoDBv2;
using Microsoft.Ajax.Utilities;
using NPOI.SS.Formula.Functions;
using RetalineProAgent.Core.Services.ActiveLog;

namespace RetalineProAgent.Finance
{
    public enum CostCenterGroupType
    {
        ReferalMerchant = 1,
        SourceMerchant = 2,
        BusinessAssociate = 3,
        AreaAssociate = 4,
        GrozeoLogisticsPartners = 5
    }

    public partial class CostAllocation : System.Web.UI.Page
    {

        [Serializable]
        public class Allocationrules
        {


            /// <summary>
            /// CostCentre
            /// </summary>
            public string Ordervaluehead { get; set; }
            /// <summary>
            /// Allocation
            /// </summary>
            public double Allocation { get; set; }

            public string Costcentrename { get; set; }

            public int Costcentrename_id { get; set; }
            public int OrderValueHeadid { get; set; }

            public int cost_category_id { get; set; }

            public int cost_centre_id { get; set; }

        }
        public List<Allocationrules> Lstcostallocations
        {
            get
            {
                if (ViewState["COSTALLOCATIONLIST"] != null)
                    return (List<Allocationrules>)ViewState["COSTALLOCATIONLIST"];
                return new List<Allocationrules>();
            }
            set
            {
                ViewState["COSTALLOCATIONLIST"] = value;
            }
        }

        public bool Financefunctionhasselected
        {
            get
            {
                if (ViewState["SHOWFINANCEFUNCTION"] == null)
                    return false;

                return (bool)ViewState["SHOWFINANCEFUNCTION"];

            }

            set
            {
                ViewState["SHOWFINANCEFUNCTION"] = value;
            }
        }

        public string GetGroupName(CostCenterGroupType grouptypeid)
        {
            string strName = "";
            switch (grouptypeid)
            {
                case CostCenterGroupType.AreaAssociate:
                    strName = "Area Associate (group)";
                    break;
                case CostCenterGroupType.BusinessAssociate:
                    strName = "Business Associate(group)";
                    break;
                case CostCenterGroupType.SourceMerchant:
                    strName = "Source Merchant (group)";
                    break;
                case CostCenterGroupType.ReferalMerchant:
                    strName = "Referal Merchant (group)";
                    break;
                case CostCenterGroupType.GrozeoLogisticsPartners:
                    strName = "Grozeo Logistics Partners (GLP)";
                    break;
            }

            return strName;
        }

        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                BindListView();
                if (!String.IsNullOrEmpty(Request.QueryString["Id"]))
                {
                    ShowDiv.Visible = false;
                    EditCost();
                }
            }


        }
        protected void Page_PreRender(object sender, EventArgs e)
        {
            ddleventmaster.Enabled = !Financefunctionhasselected;
            ddlsalestype.Enabled = !Financefunctionhasselected;
            ddlpaymentmod.Enabled = !Financefunctionhasselected;
            ddldeliverytype.Enabled = !Financefunctionhasselected;
            ddlAreaType.Enabled = !Financefunctionhasselected;
            ddlitemvaluehead.Enabled = !Financefunctionhasselected;

        }

        private void EditCost()
        { // edit the rules
            int Id = Convert.ToInt32(Request.QueryString["Id"]);
            List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
            sqldaId.Add(new KeyValuePair<string, object>("id", Id));
            string requestBody = "SELECT rulename,DESCRIPTION,item_value_head_id,area_type_id,cost_category_id,delivery_type_id,payment_type_id,sale_type_id,event_master_id,allocation,cost_centre_id,orderValueHead_id,cost_distribution_id FROM cost_distribution_function  cr INNER JOIN cost_distribution_rule_new cd ON cr.id=cd.cost_distribution_id  WHERE cr.id=@id";
            var result = DataServiceMySql.GetDataTable(requestBody, Service.UserService.GetAPIConnectionString(), sqldaId);
            if (result != null && result.Rows.Count > 0)
            {
                List<Allocationrules> _lstcostallocations = new List<Allocationrules>();
                foreach (DataRow row in result.Rows)
                {

                    Allocationrules _costallocations = new Allocationrules();
                    _costallocations.Allocation = Convert.ToDouble(row["allocation"]);
                    _costallocations.Costcentrename_id = Convert.ToInt32(row["cost_centre_id"]);

                    _costallocations.OrderValueHeadid = Convert.ToInt32(row["orderValueHead_id"]);
                    if (_costallocations.Costcentrename_id == 0)
                        _costallocations.Costcentrename = "Merchants";

                    if (_costallocations.OrderValueHeadid > 0)
                    {
                        try
                        {

                            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                            prms.Add(new KeyValuePair<string, object>("CostCentreid", _costallocations.Costcentrename_id));
                            string CostCentre = "select  id,name from cost_centre where id=@CostCentreid";
                            var CostCentreid = DataService.GetDataTable(CostCentre, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: prms);
                            if (CostCentreid != null && CostCentreid.Rows.Count > 0)
                            {

                                _costallocations.Costcentrename = CostCentreid.Rows[0]["name"].ToString();
                            }
                            List<KeyValuePair<string, object>> srms = new List<KeyValuePair<string, object>>();
                            srms.Add(new KeyValuePair<string, object>("orderValueHead_id", _costallocations.OrderValueHeadid));
                            string overhead = "SELECT id,NAME FROM finance_calculation_heads where id=@orderValueHead_id";
                            var name = DataServiceMySql.GetDataTable(overhead, Service.UserService.GetAPIConnectionString(), srms);
                            if (name != null && name.Rows.Count > 0)
                            {
                                _costallocations.Ordervaluehead = name.Rows[0]["name"].ToString();
                            }
                        }
                        catch { }
                        Financefunctionhasselected = true;
                    }

                    _lstcostallocations.Add(_costallocations);

                }
                Lstcostallocations = _lstcostallocations;
                lvcostallocation.DataSource = Lstcostallocations;
                lvcostallocation.DataBind();
                var narration = result.Rows[0];
                txtNarration.Text = narration["DESCRIPTION"].ToString();
                txtrule.Text = narration["rulename"].ToString();
                ddleventmaster.SelectedValue = narration["event_master_id"].ToString();
                ddlsalestype.SelectedValue = narration["sale_type_id"].ToString();
                ddlAreaType.SelectedValue = narration["area_type_id"].ToString();
                ddldeliverytype.SelectedValue = narration["delivery_type_id"].ToString();
                ddlpaymentmod.SelectedValue = narration["payment_type_id"].ToString();
                ddlitemvaluehead.SelectedValue = narration["item_value_head_id"].ToString();

            }


        }
        protected void lbAddEntry_Click(object sender, EventArgs e)
        {
            // add the cost centre and value head to the table
            string Ordervaluehead;
            double Allocation = 0;
            int Costcentrename_id;
            int OrderValueHeadid;
            string Costcentrename;
            int cost_category_id = 0;

            string spiltid = (ddlcostcentre.SelectedItem.Value);
            string ledgerid = spiltid.Split('_')[0];
            Costcentrename_id = Convert.ToInt32(ledgerid);
            if (Costcentrename_id > 0)
            {
                string spiltgroupid = (ddlcostcentre.SelectedItem.Value);
                string groupid = spiltgroupid.Split('_')[1];
                cost_category_id = Convert.ToInt32(groupid);
            }
            Allocation = Convert.ToDouble(txtAllocation.Text);
            OrderValueHeadid = int.Parse(ddlvaluehead.SelectedItem.Value);
            Ordervaluehead = ddlvaluehead.SelectedItem.Text;
            //Costcentrename_id = int.Parse(ddlcostcentre.SelectedItem.Value);
            Costcentrename = ddlcostcentre.SelectedItem.Text;
            if (Ordervaluehead.EndsWith("_Reserve"))
            {
                //reserve revenu alloaction
                double allocationtotal = Lstcostallocations.Sum(X => X.Allocation);
                Allocation = 100 - allocationtotal;
            }
            //aviod the  Revenue reserve adding multiple time
            if (Ordervaluehead.EndsWith("_Reserve") && Lstcostallocations.Any(v => v.Ordervaluehead.EndsWith("_Reserve")))
            {
                Common.ShowToastifyMessage(this.Page, "Revenue reserve has already been added.", "danger");
                return;
            }
            var ventries = Lstcostallocations;
            ventries.Add(new Allocationrules
            {
                Ordervaluehead = Ordervaluehead,
                Allocation = Allocation,
                Costcentrename_id = Costcentrename_id,
                OrderValueHeadid = OrderValueHeadid,
                Costcentrename = Costcentrename,
                cost_category_id = cost_category_id,
            });
            Lstcostallocations = ventries;
            lvcostallocation.DataSource = Lstcostallocations;
            txtAllocation.Text = "";
            ShowDiv.Visible = false;
            lvcostallocation.DataBind();

        }

        private void BindListView()
        {
            lvcostallocation.DataSource = Lstcostallocations;
            lvcostallocation.DataBind();
            if (Lstcostallocations.Count == 0)
            {
                EditCost();
            }

        }
        protected void btnSave_Click(object sender, EventArgs e)
        {
            //save the data into database
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("eventmaster", ddleventmaster.SelectedItem.Value));
            prms.Add(new KeyValuePair<string, object>("salestype", ddlsalestype.SelectedItem.Value));
            prms.Add(new KeyValuePair<string, object>("paymentmod", ddlpaymentmod.SelectedItem.Value));
            prms.Add(new KeyValuePair<string, object>("deliverytype", ddldeliverytype.SelectedItem.Value));
            prms.Add(new KeyValuePair<string, object>("AreaType", ddlAreaType.SelectedItem.Value));
            prms.Add(new KeyValuePair<string, object>("itemValueHeadid", ddlitemvaluehead.SelectedItem.Value));
            prms.Add(new KeyValuePair<string, object>("description", txtNarration.Text));
            prms.Add(new KeyValuePair<string, object>("rulename", txtrule.Text));
            string costrule = "insert into cost_distribution_function (event_master_id,sale_type_id,payment_type_id,delivery_type_id,area_type_id,item_value_head_id,description,rulename) values(@eventmaster,@salestype,@paymentmod,@deliverytype,@AreaType,@itemValueHeadid,@description,@rulename);SELECT LAST_INSERT_ID()";
            var cost = DataServiceMySql.ExecuteScalar(costrule, Service.UserService.GetAPIConnectionString(), prms);
            // Activitylog
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            string Source = strUrl;
            int storegroupid = -1;
            string User = "Finance Admin";
            string eventmaster = ddleventmaster.SelectedItem.Value;
            string salestype = ddlsalestype.SelectedItem.Value;
            string paymentmod = ddlpaymentmod.SelectedItem.Value;
            string deliverytype = ddldeliverytype.SelectedItem.Value;
            string AreaType = ddlAreaType.SelectedItem.Value;
            string itemValueHeadid = ddlitemvaluehead.SelectedItem.Value;
            string description = txtNarration.Text;
            string rulename = txtrule.Text;
            var items = new[]
                {
                    new { Key = "Event Master", Value = eventmaster },
                    new { Key = "Sales Type", Value = salestype },
                    new { Key = "Payment Mod", Value = paymentmod },
                    new { Key = "Delivery Type", Value = deliverytype },
                    new { Key = "Area Type", Value = AreaType },
                    new { Key = "Item Value Headid", Value = itemValueHeadid },
                    new { Key = "Description", Value = description },
                    new { Key = "Rulename", Value = rulename },
                };
            string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
            var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
            foreach (var entry in Lstcostallocations)
            {

                List<KeyValuePair<string, object>> sqlId = new List<KeyValuePair<string, object>>();
                sqlId.Add(new KeyValuePair<string, object>("costcategory", entry.cost_category_id));
                sqlId.Add(new KeyValuePair<string, object>("CostCentreid", entry.Costcentrename_id));
                sqlId.Add(new KeyValuePair<string, object>("Overvalueheadid", entry.OrderValueHeadid));
                sqlId.Add(new KeyValuePair<string, object>("allocation", entry.Allocation));
                sqlId.Add(new KeyValuePair<string, object>("ruleid", cost));
                string costallocation = "insert into cost_distribution_rule_new (cost_category_id,cost_centre_id,orderValueHead_id,allocation,cost_distribution_id) values(@costcategory,@CostCentreid,@Overvalueheadid,@allocation,@ruleid)";
                int costcentre = DataServiceMySql.ExecuteSql(costallocation, Service.UserService.GetAPIConnectionString(), sqlId);
                // Activitylog
                String strUrls = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                string Sources = strUrls;
                int storegroup = -1;
                string Users = "Finance Admin";
                string Costcategory = Convert.ToString(entry.cost_category_id);
                string CostCentreid = Convert.ToString(entry.Costcentrename_id);
                string Overvalueheadid = Convert.ToString(entry.OrderValueHeadid);
                string allocation = Convert.ToString(entry.Allocation);
                string ruleid = Convert.ToString(cost);
                var item = new[]
                {
                    new { Key = "Costcategory", Value = Costcategory },
                    new { Key = "CostCentreid", Value = CostCentreid },
                    new { Key = "Overvalueheadid", Value = Overvalueheadid },
                    new { Key = "Allocation", Value = allocation },
                    new { Key = "Rule Id", Value = ruleid },
                };
                string DescriptionS = string.Join(", ", item.Select(itemES => $"{itemES.Key}={itemES.Value}"));
                var strresultS = Activitylog.ActivitylogAsync(storegroup, Sources, Users, DescriptionS);
            }
            Common.ShowCustomAlert(this.Page, "Success", "saved successfully!", true, "/Finance/costallocationrules");
        }
        protected void lvcostallocation_ItemEditing(object sender, ListViewEditEventArgs e)
        {
            lvcostallocation.EditIndex = e.NewEditIndex;
            BindListView();
        }
        //delete and update the value heads
        protected void lvcostallocation_ItemCommand(object sender, ListViewCommandEventArgs e)
        {
            if (e.CommandName == "Delete")
            {
                var ventry = Lstcostallocations;
                var item = ventry[e.Item.DataItemIndex];
                ventry.RemoveAt(e.Item.DataItemIndex);
                Lstcostallocations = ventry;
                lvcostallocation.DataSource = Lstcostallocations;
                lvcostallocation.EditIndex = -1;
                lvcostallocation.DataBind();
            }
            if (e.CommandName == "Update")
            {

                var ventry = Lstcostallocations;
                var item = ventry[e.Item.DataItemIndex];
                DropDownList costcentre = (DropDownList)e.Item.FindControl("ddlcostcentre_update");
                DropDownList CostCentre = (DropDownList)e.Item.FindControl("ddlvaluehead_update");
                TextBox txtallocation = (TextBox)e.Item.FindControl("txtAllocation_update");
                item.Ordervaluehead = CostCentre.SelectedItem.Text;
                item.Costcentrename_id = Convert.ToInt32(costcentre.SelectedItem.Value);
                item.Costcentrename = costcentre.SelectedItem.Text;
                item.Allocation = Convert.ToDouble(txtallocation.Text);
                ventry[e.Item.DataItemIndex] = item;
                Lstcostallocations = ventry;

                lvcostallocation.EditIndex = -1;
                lvcostallocation.DataSource = Lstcostallocations;
                lvcostallocation.DataBind();


            }

        }

        protected void lvcostallocation_ItemUpdating(object sender, ListViewUpdateEventArgs e)
        {

        }

        protected void lvcostallocation_ItemCanceling(object sender, ListViewCancelEventArgs e)
        {
            lvcostallocation.EditIndex = -1;
            BindListView();
        }
        protected void lvcostallocation_ItemDeleting(object sender, ListViewDeleteEventArgs e)
        {


        }

        protected void cancel_Click(object sender, EventArgs e)
        {
            Response.Redirect("/Finance/costallocationrules");
        }

        protected void ddlledger_SelectedIndexChanged(object sender, EventArgs e)
        {

        }
        //total calculation
        protected void lvcostallocation_DataBound(object sender, EventArgs e)
        {
            //double total = Lstcostallocations.Sum(X => X.Allocation);
            //txttotal.Text = (100 - total).ToString();

            //if (total > 99)
            //{
            //    lbnsave.Enabled = false;
            //    txterror.Text = "The allocation for the cost center cannot be added if it exceeds 99.";
            //}
            double allocationtotal = Lstcostallocations.Sum(X => X.Allocation);
            double totalallocation = (100 - allocationtotal);
            ltrallocationtotal.Text = (allocationtotal + totalallocation).ToString();
        }

        protected void ddleventmaster_SelectedIndexChanged(object sender, EventArgs e)
        {
            Financefunctionhasselected = true;
        }

        protected void ddlsalestype_SelectedIndexChanged(object sender, EventArgs e)
        {
            Financefunctionhasselected = true;
        }

        protected void ddlpaymentmod_SelectedIndexChanged(object sender, EventArgs e)
        {
            Financefunctionhasselected = true;
        }

        protected void ddldeliverytype_SelectedIndexChanged(object sender, EventArgs e)
        {
            Financefunctionhasselected = true;
        }

        protected void ddlAreaType_SelectedIndexChanged(object sender, EventArgs e)
        {
            Financefunctionhasselected = true;
        }

        protected void ddlmarginapply_SelectedIndexChanged(object sender, EventArgs e)
        {
            Financefunctionhasselected = true;
        }
        //disable the allocation when value head are reserve revenu
        protected void ddlvaluehead_SelectedIndexChanged(object sender, EventArgs e)
        {
            string reserve = (ddlvaluehead.SelectedItem.Text);
            List<KeyValuePair<string, object>> sqlda = new List<KeyValuePair<string, object>>();
            sqlda.Add(new KeyValuePair<string, object>("reserve", reserve));
            string revenu = "SELECT id,NAME FROM finance_calculation_heads where @reserve LIKE '%_Reserve%'";
            var reservename = DataServiceMySql.GetDataTable(revenu, Service.UserService.GetAPIConnectionString(), sqlda);
            if (reservename != null && reservename.Rows.Count > 0)
            {
                txtAllocation.Text = "0";
                txtAllocation.Enabled = false;
            }
            else
            {
                txtAllocation.Enabled = true;
            }
        }
    }
}