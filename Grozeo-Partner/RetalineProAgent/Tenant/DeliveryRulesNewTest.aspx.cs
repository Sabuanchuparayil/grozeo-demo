using Microsoft.Ajax.Utilities;
using RetalineProAgent.Controls;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Tenant
{
    public partial class DeliveryRulesNewTest : Base.BasePartnerPage
    {

        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                 int brId = 0;
                if (!String.IsNullOrEmpty(Request.QueryString["brid"]))
                {
                    try { brId = Convert.ToInt32(Request.QueryString["brid"]); } catch { brId = 0; }
                    DataTable dtBranch = DataServiceMySql.GetDataTable("select * from finascop_branch where br_ID=@brid and br_storegroup=@storegroupid", UserService.GetAPIConnectionString(), new List<KeyValuePair<string, object>> { new KeyValuePair<string, object>("storegroupid", CurrentUser.APIStoreId), new KeyValuePair<string, object>("brid", brId) });
                    if (dtBranch == null || dtBranch.Rows.Count < 1)
                        brId = 0;
                    else
                        ltrBranchName.Text = dtBranch.Rows[0]["br_Name"].ToString();
                }

                if (brId <= 0)
                {
                    Common.ShowCustomAlert(this.Page, "Invalid operation", "Invalid store selection", false, "/Tenant/Branches");
                    return;
                }

                //string sqlLoadData = $"SELECT * FROM(SELECT 2 AS deliType,'Hyperlocal Delivery' AS typeName, 10 AS maxWeight UNION SELECT 3, 'Scheduled Local Delivery', 25 " +
                //     $"UNION SELECT 4, 'Local Last Mile Delivery', 10 UNION SELECT 1, 'Courier Delivery', 10 UNION SELECT 5, 'Parcel Delivery', 100 UNION SELECT 6, 'Cargo Delivery', 250) delimodes " +
                //     $"LEFT JOIN (SELECT * FROM retaline_delivery_rules WHERE rdr_ruleFor = 3 AND rdr_ruleForId= @brid AND rdr_storeGroupId=@storeId) r ON delimodes.deliType = rdr_deliveryMode";
                string sqlLoadData = $"SELECT *, (SELECT GROUP_CONCAT( CONCAT((CASE WHEN slabType=1 AND r.rdr_calculationMode = 4 THEN 'First' WHEN slabType=1 AND r.rdr_calculationMode <> 4 THEN 'Upto' " +
                    $"WHEN slabType=3 OR (slabType=2 AND r.rdr_calculationMode <> 4) THEN 'Above' ELSE 'Next' END), ',', (CASE WHEN r.rdr_calculationMode = 1 THEN slabKm ELSE weight END), ',', slabAmount) SEPARATOR '|' ) AS val " +
                    $"FROM `delivery_rule_slab` WHERE r.rdr_calculationMode <> 5 AND drId = r.rdr_id) AS slabs " +
                    $"FROM retaline_delivery_rules r WHERE rdr_ruleFor = 3 AND rdr_ruleForId= @brid AND rdr_storeGroupId=@storeId";
                DataTable dtRuleData = DataServiceMySql.GetDataTable(sqlLoadData, UserService.GetAPIConnectionString(), new List<KeyValuePair<string, object>> { new KeyValuePair<string, object>("storeId", CurrentUser.APIStoreId), new KeyValuePair<string, object>("brid", brId) });
                if (dtRuleData != null && dtRuleData.Rows.Count > 0)
                {
                    foreach (Control ctrl in plcDeliRulesControls.Controls)
                    {
                        if (ctrl is ctrlDeliveryRule)
                        {
                            var ctrlRule = ctrl as ctrlDeliveryRule;
                            DataRow drHL = dtRuleData.AsEnumerable().Where(r => r["rdr_deliveryMode"].ToString() == ctrlRule.Attributes["RuleTypeid"]).Select(r => r).FirstOrDefault();
                            if (drHL != null)
                            {
                                int calculationMode = Convert.ToInt32(drHL["rdr_calculationMode"]);

                                ctrlRule.RuleID = Convert.ToInt32(drHL["rdr_id"].ToString());
                                ctrlRule.CalculationMode = calculationMode;
                                ctrlRule.FixedRateperkm = drHL["rdr_fixedRateperkm"].ToString();
                                ctrlRule.FixedRateMax = drHL["rdr_fixedRateMax"].ToString();
                                ctrlRule.FixedRateVal = drHL["rdr_amt1"].ToString();
                                ctrlRule.FixedRateMin = drHL["rdr_fixedRateMin"].ToString();
                                ctrlRule.FixedRateFree = drHL["rdr_isfreeDeliveryAmt"].ToString();
                                ctrlRule.SlabValues = drHL["slabs"].ToString();
                            }
                        }
                    }
                }

            }
        }

        protected void btnSave_Click(object sender, EventArgs e)
        {
            int affectedRows = 0;

            foreach (Control ctrl in plcDeliRulesControls.Controls)
            {
                if (ctrl is ctrlDeliveryRule)
                {
                    affectedRows += (ctrl as ctrlDeliveryRule).SaveDeliveryRule();
                }
            }


            if (affectedRows > 0)
            {
                string sqlDeliCostShare = $"delete from delivery_cost_share where storeGroupId=@storegroupid; ";
                List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                prms.Add(new KeyValuePair<string, object>("storegroupid", CurrentUser.APIStoreId));
                if (chk_share_delivery.Checked)
                {
                    sqlDeliCostShare = $"insert into delivery_cost_share(storeGroupId, shareCost, shareSubject, shareValue, shareType) " +
                        $"values(@storegroupid, @shareCost, @shareSubject, @shareValue, @shareType) ON DUPLICATE KEY UPDATE " +
                        $"shareCost = VALUES(shareCost), shareSubject = VALUES(shareSubject), shareValue = VALUES(shareValue), shareType = VALUES(shareType);";
                    prms.Add(new KeyValuePair<string, object>("shareCost", ConvertToDoubleOrDefault(txtCostShareCost.Text)));
                    prms.Add(new KeyValuePair<string, object>("shareSubject", ConvertToDoubleOrDefault(txtCostShareSubjectTo.Text)));
                    prms.Add(new KeyValuePair<string, object>("shareValue", ConvertToDoubleOrDefault(txtCostShareVal.Text)));
                    prms.Add(new KeyValuePair<string, object>("shareType", selCostShareType.Text));
                }
                int updateCSResult = DataServiceMySql.ExecuteSql(sqlDeliCostShare, UserService.GetAPIConnectionString(), prms);
                affectedRows += updateCSResult;

                var request = HttpContext.Current.Request;

                // Construct the full URL
                string url = $"{request.Url.Scheme}://{request.Url.Authority}{request.Url.AbsolutePath}{request.Url.Query}";

                Common.ShowCustomAlert(this.Page, "Success", "Delivery rule updated successfully!!", true, url);
                //Common.ShowToastifyMessage(this.Page, "Delivery rule updated successfully!", (affectedRows > 0 ? "success" : "info"));
            }
            else
            {
                Common.ShowToastifyMessage(this.Page, "Delivery rule updation failed!", "danger");
            }

        }

        public double ConvertToDoubleOrDefault(string input, double def = 0)
        {
            double result = def;
            if (!String.IsNullOrEmpty(input))
            {
                try
                {
                    result = Convert.ToDouble(input);
                }
                catch
                {
                    result = def;
                }
            }
            return result;
        }


    }
}