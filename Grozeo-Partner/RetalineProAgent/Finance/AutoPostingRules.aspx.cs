using Amazon.DynamoDBv2;
using Amazon.DynamoDBv2.Model;
using NPOI.SS.Formula.Functions;
using RetalineProAgent.Core.BussinessModel.Finance;
using RetalineProAgent.Core.BussinessModel.Inventory;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Data.SqlTypes;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.HtmlControls;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Finance
{
    public partial class AutoPostingRules : System.Web.UI.Page
    {
        [Serializable]
        public class Autopostingrule
        {
            public int Name_id { get; set; }

            public string Name { get; set; }

            public int Entry_Type_ID { get; set; }


            public string Entry_Type_name { get; set; }

            public int ValueHeader_ID { get; set; }

            public string ValueHeadere_name { get; set; }



            public string postinglocation { get; set; }

            public int postinglocation_id { get; set; }

            public int ledger_id { get; set; }


            public int group_id { get; set; }
            public bool hasCostCenterLinked { get; set; }
            
        }
        public List<Autopostingrule> Lstpostingrule
        {
            get
            {
                if (ViewState["AUTOLEDGERPOSTINLIST"] != null)
                    return (List<Autopostingrule>)ViewState["AUTOLEDGERPOSTINLIST"];
                return new List<Autopostingrule>();
            }
            set
            {
                ViewState["AUTOLEDGERPOSTINLIST"] = value;
            }
        }

        public bool Ledgerhasselected
        {
            get
            {
                if (ViewState["SHOWLEDGER"] == null)
                    return false;

                return (bool)ViewState["SHOWLEDGER"];

            }

            set
            {
                ViewState["SHOWLEDGER"] = value;
            }
        }

       


        protected void Page_Load(object sender, EventArgs e)
        {
           if (!IsPostBack)
            {
                BindListView();
                string flag = Request.QueryString["Flag"];
                if (!String.IsNullOrEmpty(Request.QueryString["Id"]))
                {
                       ShowDiv.Visible = false;
                       EditCost();
                       if(flag == "View")
                       {
                        txtdescription.Enabled= false;
                        txtNarration.Enabled= false;
                        lbaddentry.Visible = false;
                        lbnsave.Visible = false;
                        txtrule.Enabled= false;
                        ddlCostcentre.Enabled= false;
                        ddlcostcentrevaluehead.Enabled= false; 
                        ddlentrytype.Enabled= false;
                        ddleventmaster.Enabled= false;
                        ddlLedger.Enabled= false;
                        ddlpostingloaction.Enabled= false;
                        ddlselect.Enabled= false;
                        ddlvaluehead.Enabled= false;
                                            
                       }

                }


            }


        }

        protected void Page_PreRender(object sender, EventArgs e)
        {
            if (Lstpostingrule.Count > 0)
                hidSelectedLedgerIds.Value = String.Join(",", Lstpostingrule.Where(l => l.postinglocation_id == 0).Select(l => l.Name_id));
            else
                hidSelectedLedgerIds.Value = "";

            ddleventmaster.Enabled = !Ledgerhasselected;
            ddlvoucher.Enabled = !Ledgerhasselected;

            if (Lstpostingrule.Count == 0)
            {
                foreach (ListItem item in ddlpostingloaction.Items)
                {
                    if (item.Value == "1")
                    {
                        item.Attributes.Add("disabled", "disabled");
                    }

                }
            }
        }

        protected void lbAddEntry_Click(object sender, EventArgs e)
        {
            int group_id = 0;
            int Name_id = 0;
            string Name = "";
            int Entry_Type_ID;
            string Entry_Type_name;
            string ValueHeadere_name = "";
            int ValueHeader_ID = 0;
            string postinglocation;
            int postinglocation_id;

            Entry_Type_ID = Convert.ToInt32(ddlentrytype.SelectedItem.Value);
            Entry_Type_name = ddlentrytype.SelectedItem.Text;

            postinglocation = ddlpostingloaction.SelectedItem.Text;
            postinglocation_id = Convert.ToInt32(ddlpostingloaction.SelectedItem.Value);
            if (postinglocation_id == 0)
            {

                string spiltid = (ddlLedger.SelectedItem.Value);
                string ledgerid = spiltid.Split('_')[0];
                //Name = ddlLedger.SelectedItem.Text;
                Name_id = Convert.ToInt32(ledgerid);
                if (Name_id == 0)
                {
                    string spiltgroupid = (ddlLedger.SelectedItem.Value);
                    string groupid = spiltgroupid.Split('_')[1];
                    group_id = Convert.ToInt32(groupid);
                }
                //Name_id = Convert.ToInt32(ddlLedger.SelectedItem.Value);
                Name = ddlLedger.SelectedItem.Text;

                ValueHeader_ID = Convert.ToInt32(ddlvaluehead.SelectedItem.Value);
                ValueHeadere_name = ddlvaluehead.SelectedItem.Text;

            }
            else
            {

                string splitcostcentre = (ddlCostcentre.SelectedItem.Value);
                string costcentreid = splitcostcentre.Split('_')[0];
                Name_id = Convert.ToInt32(costcentreid);
                if (Name_id >= 1)
                {
                    string splitcostid = (ddlCostcentre.SelectedItem.Value);
                    string costid = splitcostid.Split('_')[1];
                    group_id = Convert.ToInt32(costid);
                }





                //Name_id = Convert.ToInt32(ddlCostcentre.SelectedItem.Value);
                Name = ddlCostcentre.SelectedItem.Text;
                ValueHeader_ID = Convert.ToInt32(ddlcostcentrevaluehead.SelectedItem.Value);
                ValueHeadere_name = ddlcostcentrevaluehead.SelectedItem.Text;
            }

            bool hasCostcenterLinked = false;
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("columnId", ValueHeader_ID));
            prms.Add(new KeyValuePair<string, object>("ledgerId", Name_id));
            DataTable dt1 = DataServiceMySql.GetDataTable("SELECT COUNT(*) FROM `cost_distribution_rule_new` r INNER JOIN cost_distribution_function rf ON r.cost_distribution_id = rf.id WHERE ledger_id=@ledgerId AND r.orderValueHead_id = @columnId", parmeters: prms);
            if (dt1 != null && dt1.Rows.Count > 0 && Convert.ToInt32(dt1.Rows[0][0]) > 0)
                hasCostcenterLinked = true;



            var ventries = Lstpostingrule;
            ventries.Add(new Autopostingrule
            {

                Name_id = Name_id,
                Name = Name,
                Entry_Type_ID = Entry_Type_ID,
                Entry_Type_name = Entry_Type_name,
                ValueHeadere_name = ValueHeadere_name,
                ValueHeader_ID = ValueHeader_ID,
                postinglocation = postinglocation,
                postinglocation_id = postinglocation_id,
                hasCostCenterLinked = hasCostcenterLinked,
                group_id = group_id
            });
            Lstpostingrule = ventries;
            lvfinanceposting.DataSource = Lstpostingrule;
            ShowDiv.Visible = false;
            lvfinanceposting.DataBind();




        }


        private void EditCost()
        {
            int Id = Convert.ToInt32(Request.QueryString["Id"]);
            List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
            sqldaId.Add(new KeyValuePair<string, object>("id", Id));
            // Query to fetch autoposting rule details
            string requestBody = "SELECT event_master_id,voucher_id,cost_centre_id,type_id,vouchernarration,DESCRIPTION,rulename,value_head_id,ledger_id,isDebtor,autoposting_rule_id" +
                                  $"  FROM finance_autoposting_rule cr INNER JOIN finance_autoposting_settings cd ON cr.id = cd.autoposting_rule_id WHERE cr.id=@id";
            var result = DataServiceMySql.GetDataTable(requestBody, Service.UserService.GetAPIConnectionString(), sqldaId);
            if (result != null && result.Rows.Count > 0)
            {
                List<Autopostingrule> _lstcostallocations = new List<Autopostingrule>();
                foreach (DataRow row in result.Rows)
                {
                    Autopostingrule _autoposting = new Autopostingrule();
                    // Set ValueHeader_ID and fetch header name
                    _autoposting.ValueHeader_ID = Convert.ToInt32(row["value_head_id"]);
                    _autoposting.ValueHeadere_name = GetValueHeadName(_autoposting.ValueHeader_ID);                 
                    _autoposting.Entry_Type_ID = Convert.ToInt32(row["isDebtor"]);
                    _autoposting.Entry_Type_name = Convert.ToInt32(row["isDebtor"]) == 1 ? "Debit" : "Credit";
                    // Determine if record is for ledger or cost centre

                    bool isLedger = row["cost_centre_id"].ToString() == "-1";
                    int nameId = isLedger ? Convert.ToInt32(row["ledger_id"]) : Convert.ToInt32(row["cost_centre_id"]);
                    _autoposting.Name_id=nameId;
                    _autoposting.group_id =Convert.ToInt32(row["type_id"].ToString());
                   _autoposting.Name = GetNameFromDb(nameId, isLedger, row["type_id"].ToString());
                    _lstcostallocations.Add(_autoposting);
                }
                Lstpostingrule = _lstcostallocations;
                lvfinanceposting.DataSource = Lstpostingrule;
                lvfinanceposting.DataBind();
                var narration = result.Rows[0];
                txtdescription.Text = narration["DESCRIPTION"].ToString();
                txtNarration.Text = narration["vouchernarration"].ToString();
                txtrule.Text = narration["rulename"].ToString();
                ddleventmaster.SelectedValue = narration["event_master_id"].ToString();
                //ddlvaluehead.SelectedValue= narration["value_head_id"].ToString();
                //ddlpostingloaction.SelectedValue= narration["value_head_id"].ToString();
                ddlvoucher.SelectedValue = narration["voucher_id"].ToString();


            }


        }
        // Get the name of the value head by its ID
        private string GetValueHeadName(int valueHeadId)
        {
            var param = new List<KeyValuePair<string, object>>
            {
              new KeyValuePair<string, object>("id", valueHeadId)
            }          ; 
            string query = "SELECT NAME FROM finance_calculation_heads WHERE id = @id";
            var table = DataServiceMySql.GetDataTable(query, Service.UserService.GetAPIConnectionString(), param);
            return table?.Rows.Count > 0 ? table.Rows[0]["NAME"].ToString() : string.Empty;
        }
        // Get name from either ledger or cost centre table based on ID and type
        private string GetNameFromDb(int id, bool isLedger, string typeId)
        {
            try
            {
                string name = string.Empty;
                // If not a ledger and typeId is valid, get the ledger group name

                if (Convert.ToInt32(typeId) > 0)
                {
                    var groupParams = new List<KeyValuePair<string, object>>
                    {
                      new KeyValuePair<string, object>("type", typeId)
                    };
                    var grouname = DataService.GetDataTable("SELECT ledger_group FROM entryTypeLedgerGroup WHERE type = @type", ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: groupParams);
                    if (grouname?.Rows.Count > 0)
                        name = grouname.Rows[0]["ledger_group"].ToString();
                }
                else
                {
                    //get the ledger ledger/costcentre
                    string query = isLedger ? "SELECT name FROM ledger WHERE id = @ledger_id" : "SELECT name FROM cost_centre WHERE id = @cost_centre_id";
                    string ID = isLedger ? "ledger_id" : "cost_centre_id";
                    var param = new List<KeyValuePair<string, object>>
                    {
                     new KeyValuePair<string, object>(ID, id)
                    };
                    var ledgername = DataService.GetDataTable(query, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: param);
                    if (ledgername?.Rows.Count > 0)
                        name = ledgername.Rows[0]["name"].ToString();
                    Ledgerhasselected = true;
                }
                return name;
            }
            catch
            {

            }
            return "";            
        }

        private void BindListView()
        {
            lvfinanceposting.DataSource = Lstpostingrule;
            lvfinanceposting.DataBind();
            if (Lstpostingrule.Count == 0)
            {
                EditCost();
            }

        }

        protected void lvfinanceposting_ItemCommand(object sender, ListViewCommandEventArgs e)
        {
            if (e.CommandName == "Delete")
            {
                var ventry = Lstpostingrule;
                var item = ventry[e.Item.DataItemIndex];
                ventry.RemoveAt(e.Item.DataItemIndex);
                Lstpostingrule = ventry;
                lvfinanceposting.DataSource = Lstpostingrule;
                lvfinanceposting.EditIndex = -1;
                lvfinanceposting.DataBind();



            }

            if (e.CommandName == "Update")
            {

                var ventry = Lstpostingrule;
                var item = ventry[e.Item.DataItemIndex];
                DropDownList value = (DropDownList)e.Item.FindControl("ddlvaluehead_update");
                DropDownList ledger = (DropDownList)e.Item.FindControl("ddlLedger_update");
                DropDownList entry = (DropDownList)e.Item.FindControl("ddlentrytype_update");
                item.ValueHeadere_name = value.SelectedItem.Text;
                item.ValueHeader_ID = Convert.ToInt32(value.SelectedItem.Value);
                item.Name = ledger.SelectedItem.Text;
                item.Name_id= Convert.ToInt32(ledger.SelectedItem.Value);
                item.Entry_Type_name = entry.SelectedItem.Text;
                ventry[e.Item.DataItemIndex] = item;
                Lstpostingrule = ventry;

                lvfinanceposting.EditIndex = -1;
                lvfinanceposting.DataSource = Lstpostingrule;
                lvfinanceposting.DataBind();


            }

        }

        protected void lvfinanceposting_ItemCanceling(object sender, ListViewCancelEventArgs e)
        {
            lvfinanceposting.EditIndex = -1;
            BindListView();
        }

        protected void lvfinanceposting_ItemDeleting(object sender, ListViewDeleteEventArgs e)
        {

        }

        protected void lvfinanceposting_ItemUpdating(object sender, ListViewUpdateEventArgs e)
        {

        }

        protected void lvfinanceposting_ItemEditing(object sender, ListViewEditEventArgs e)
        {
            lvfinanceposting.EditIndex = e.NewEditIndex;
            BindListView();
        }

        protected void lbnsave_Click(object sender, EventArgs e)
        {
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("eventmaster", ddleventmaster.SelectedItem.Value));
            prms.Add(new KeyValuePair<string, object>("Voucherid", ddlvoucher.SelectedItem.Value));
            prms.Add(new KeyValuePair<string, object>("vouchernarration", txtNarration.Text));
            prms.Add(new KeyValuePair<string, object>("description", txtdescription.Text));
            prms.Add(new KeyValuePair<string, object>("rulename", txtrule.Text));
            prms.Add(new KeyValuePair<string, object>("CreatedOn", DateTime.Now));
            prms.Add(new KeyValuePair<string, object>("CreatedBy", Page.User.Identity.Name));
            string rule = "";
            if (Request.QueryString["Id"]!= null)
            {
                prms.Add(new KeyValuePair<string, object>("Updatedby",Page.User.Identity.Name));
                prms.Add(new KeyValuePair<string, object>("Updatedbon", DateTime.Now));
                prms.Add(new KeyValuePair<string, object>("id",Convert.ToInt32(Request.QueryString["Id"])));
                rule = "update finance_autoposting_rule set status=0,UpdatedOn=@Updatedbon,Updatedby=@Updatedby where id=@id; ";
                //DataServiceMySql.ExecuteScalar(ruleupdate, Service.UserService.GetAPIConnectionString(), prms);
            }
             rule += "insert into finance_autoposting_rule (event_master_id,voucher_id,vouchernarration,description,rulename,CreatedOn,CreatedBy) values(@eventmaster,@Voucherid,@vouchernarration,@description,@rulename,@CreatedOn,@CreatedBy);SELECT LAST_INSERT_ID()";
            var cost = DataServiceMySql.ExecuteScalar(rule, Service.UserService.GetAPIConnectionString(), prms);
            // Activitylog
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            string Source = strUrl;
            int storegroupid = -1; 
            string User = "Finance Admin";
            string eventmaster = ddleventmaster.SelectedItem.Value;
            string Voucherid = ddlvoucher.SelectedItem.Value;
            string vouchernarration = txtNarration.Text;
            string rulename = txtrule.Text;
            var items = new[]
                {
                    new { Key = "Event Master", Value = eventmaster },
                    new { Key = "Voucher Id", Value = Voucherid },
                    new { Key = "Voucher Narration", Value = vouchernarration },
                    new { Key = "Rule Name", Value = rulename },
                };
            string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
            var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);

            for (int i = 0; i < lvfinanceposting.Items.Count; i++)
            {

                List<KeyValuePair<string, object>> sqlId = new List<KeyValuePair<string, object>>();
                Autopostingrule entry = Lstpostingrule[i];
                if (entry.postinglocation_id == 1)
                {
                    DropDownList dlLedgerid = (DropDownList)lvfinanceposting.Items[i].FindControl("ddlLedgercost");
                    if (dlLedgerid == null || String.IsNullOrEmpty(dlLedgerid.Text))
                    {
                        Common.ShowCustomAlert(this.Page, "Validation error", "Ledger is missing for cost center", false);
                        return;
                    }
                    entry.ledger_id = Convert.ToInt32(dlLedgerid.SelectedItem.Value);
                }


                if (entry.postinglocation_id == 1)
                {
                    sqlId.Add(new KeyValuePair<string, object>("ledger_id", entry.ledger_id));
                    sqlId.Add(new KeyValuePair<string, object>("cost_centre_id", entry.Name_id));
                }
                else
                {
                    sqlId.Add(new KeyValuePair<string, object>("ledger_id", entry.Name_id));
                    sqlId.Add(new KeyValuePair<string, object>("cost_centre_id", -1));
                }

                sqlId.Add(new KeyValuePair<string, object>("group_id", entry.group_id));
                sqlId.Add(new KeyValuePair<string, object>("value_head_id", entry.ValueHeader_ID));

                sqlId.Add(new KeyValuePair<string, object>("isDebtor", entry.Entry_Type_ID));
                sqlId.Add(new KeyValuePair<string, object>("autoposting_rule_id", cost));
                string costallocation = "insert into finance_autoposting_settings (value_head_id,ledger_id,type_id,cost_centre_id,isDebtor,autoposting_rule_id) values(@value_head_id,@ledger_id,@group_id,@cost_centre_id,@isDebtor,@autoposting_rule_id)";
                int costcentre = DataServiceMySql.ExecuteSql(costallocation, Service.UserService.GetAPIConnectionString(), sqlId);

                // Activitylog
                String strUrls = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                string Sources = strUrls;
                int storegroup = -1;
                string Users = "Finance Admin";
                string value_head_id =Convert.ToString(entry.ValueHeader_ID);
                string ledger_id = Convert.ToString(entry.Name_id);
                string Ledger_id = Convert.ToString(entry.ledger_id); 
                string group_id = Convert.ToString(entry.group_id);
                string isDebtor = Convert.ToString(entry.Entry_Type_ID);
                string autoposting_rule_id = Convert.ToString(cost);
                var item = new[]
                {
                    new { Key = "Value Head Id", Value = value_head_id },
                    new { Key = "Ledger_id", Value = ledger_id },
                    new { Key = "Ledger_id", Value = Ledger_id },
                    new { Key = "Group Id", Value = group_id },
                    new { Key = "IsDebtor", Value = isDebtor },
                    new { Key = "Autoposting Rule Id", Value = autoposting_rule_id },
                };
               string DescriptionS = string.Join(", ", item.Select(itemES => $"{itemES.Key}={itemES.Value}"));
               var strresultS = Activitylog.ActivitylogAsync(storegroup, Sources, Users, DescriptionS);

            }


            Common.ShowCustomAlert(this.Page, "Success", "saved successfully!", true, "/Finance/Autopostingsettings");


        }

        protected void ddlvaluehead_SelectedIndexChanged(object sender, EventArgs e)
        {

        }

        protected void ddleventmaster_SelectedIndexChanged(object sender, EventArgs e)
        {
            Ledgerhasselected = true;
        }

        protected void ddlvoucher_SelectedIndexChanged(object sender, EventArgs e)
        {
            Ledgerhasselected = true;
        }

        protected void ddlpostingloaction_SelectedIndexChanged(object sender, EventArgs e)
        {
            if (ddlpostingloaction.SelectedItem.Value == "0")
            {
                ddlLedger.Visible = true;
                ddlselect.Visible = false;
                ddlCostcentre.Visible = false;
                ddlvaluehead.Visible = true;
                ddlcostcentrevaluehead.Visible = false;
                ddlentrytype.Enabled = true;

            }
            if (ddlpostingloaction.SelectedItem.Value == "1")
            {
                ddlLedger.Visible = false;
                ddlselect.Visible = false;
                ddlCostcentre.Visible = true;
                ddlvaluehead.Visible = false;
                ddlcostcentrevaluehead.Visible = true;
                ddlentrytype.Enabled = false;
            }
        }

        protected void lvfinanceposting_ItemDataBound(object sender, ListViewItemEventArgs e)
        {
            int showLedger = Convert.ToInt32(DataBinder.Eval(e.Item.DataItem, "postinglocation_id"));
            DropDownList dl = (DropDownList)e.Item.FindControl("ddlLedgercost");
            if (dl != null)
                dl.Visible = showLedger == 1;

            if (e.Item.ItemType == ListViewItemType.DataItem)
            {
                LinkButton btndelete = e.Item.FindControl("btndelete") as LinkButton;
                LinkButton btnedit = e.Item.FindControl("btnedit") as LinkButton;

                string flag = Request.QueryString["Flag"];
                if (flag == "View")
                {
                    btndelete.Enabled = false;
                    btndelete.CssClass += " disabled";
                    btndelete.OnClientClick = "";
                    
                    btnedit.Enabled = false;
                }
            }


           

            }

        protected void ddlLedger_update_DataBound(object sender, EventArgs e)
        {
            DropDownList ddl = sender as DropDownList;
            if (ddl != null)
            {
                // Get the Name_id from your data source
                int nameId = Convert.ToInt32(Eval("Name_id"));

                if (nameId == 0)
                {
                    // Extract group ID from selected value (e.g., "123_4", taking '4')
                    if (ddl.SelectedItem != null)
                    {
                        string splitGroupId = ddl.SelectedItem.Value;
                        string[] parts = splitGroupId.Split('_');
                        if (parts.Length > 1)
                        {
                            ddl.SelectedValue = parts[1]; // Set to group ID
                        }
                    }
                }
                else
                {
                    ddl.SelectedValue = nameId.ToString();
                }
            }
        }
    }
    }
