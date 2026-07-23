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

namespace RetalineProAgent.Business
{
    public partial class DeliveryRate : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                int areaDrivers = 0; // get the number of drivers available for the Area
                string sqlDrivers = "SELECT COUNT(*) FROM qugeo_driver  WHERE createdBy = 2 AND sourceId = (SELECT areaBusinessAssociate FROM area_entries WHERE id = @areaId)";
                DataTable dtDrivers = DataServiceMySql.GetDataTable(sqlDrivers, UserService.GetAPIConnectionString(), 
                    new List<KeyValuePair<string, object>> { new KeyValuePair<string, object>("areaId", CurrentUser.AreaId) });
                if (dtDrivers == null || dtDrivers.Rows.Count > 0)
                    areaDrivers = Convert.ToInt32(dtDrivers.Rows[0][0]);

                if (areaDrivers <= 0)
                {
                    Common.ShowCustomAlert(this.Page, "Invalid operation", "No drivers available. Please add drivers before setting rate", false, "/Business/BusinessNavigations/Resources");
                    return;
                }

                string sqlLoadData = $"SELECT * FROM retaline_delivery_rules r WHERE rdr_ruleFor = 4 AND rdr_ruleForId= @areaid";
                DataTable dtRuleData = DataServiceMySql.GetDataTable(sqlLoadData, UserService.GetAPIConnectionString(), new List<KeyValuePair<string, object>> { new KeyValuePair<string, object>("storeId", CurrentUser.APIStoreId), new KeyValuePair<string, object>("areaid", CurrentUser.AreaId) });
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

                var request = HttpContext.Current.Request;
                string url = $"{request.Url.Scheme}://{request.Url.Authority}{request.Url.AbsolutePath}{request.Url.Query}";
                Common.ShowCustomAlert(this.Page, "Success", "Delivery rule updated successfully!!", true, url);
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