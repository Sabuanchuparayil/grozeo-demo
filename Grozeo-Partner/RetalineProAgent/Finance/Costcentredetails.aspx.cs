using NPOI.POIFS.Properties;
using RetalineProAgent.Core.Services;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Data.SqlTypes;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using static RetalineProAgent.Finance.AutoPostingRules;

namespace RetalineProAgent.Finance
{
    public partial class Costcentredetails : System.Web.UI.Page
    {
        [Serializable]
        public class Costcentre
        {
            public string CostCentreName { get; set; }
            public string RuleName { get; set; }
        }

        public List<Costcentre> lstcostcentre
        {
            get
            {
                if (ViewState["COSTCENTRE"] != null)
                    return (List<Costcentre>)ViewState["COSTCENTRE"];
                return new List<Costcentre>();
            }
            set
            {
                ViewState["COSTCENTRE"] = value;
            }
        }
        protected void Page_Load(object sender, EventArgs e)
        {
            if (Request.QueryString["Name_id"] != null)
            {
                int ledger_id = Convert.ToInt32(Request.QueryString["Name_id"]);
                List<KeyValuePair<string, object>> sqldatavId = new List<KeyValuePair<string, object>>();
                sqldatavId.Add(new KeyValuePair<string, object>("Id", ledger_id));
                string costcentre = $"SELECT r.cost_centre_id,rf.ledger_id, rulename FROM `cost_distribution_rule_new` r INNER JOIN `cost_distribution_function` rf ON r.`cost_distribution_id` = rf.id WHERE ledger_id=@id";
                var result = DataServiceMySql.GetDataTable(costcentre, Service.UserService.GetAPIConnectionString(), sqldatavId);
                if(result != null && result.Rows.Count > 0)
                {
                    List<Costcentre> _lstcostcentre = new List<Costcentre>();
                    foreach (DataRow row in result.Rows)
                    {
                        
                        int costcentreid = Convert.ToInt32(row["cost_centre_id"].ToString());
                       
                        Costcentre _costcentre = new Costcentre();
                        if (costcentreid != 0)
                        {
                            _costcentre.RuleName = result.Rows[0]["rulename"].ToString();                           
                            List<KeyValuePair<string, object>> sql = new List<KeyValuePair<string, object>>();
                            sql.Add(new KeyValuePair<string, object>("Id", costcentreid));
                            string costcentrefind = "select name from cost_centre where id=@Id";
                            var ledgername = DataService.GetDataTable(costcentrefind, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sql);
                            if (ledgername != null && ledgername.Rows.Count > 0)
                            {
                                _costcentre.CostCentreName = ledgername.Rows[0]["name"].ToString();
                            }
                        }
                        _lstcostcentre.Add(_costcentre);
                    }
                    lstcostcentre = _lstcostcentre;
                    rptEditCostCenter.DataSource = lstcostcentre;
                    rptEditCostCenter.DataBind();
                }
            }
        }
    }
}