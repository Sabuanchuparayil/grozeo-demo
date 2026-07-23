using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using RetalineProAgent.Core.BussinessModel.Store;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class DelivRules : Base.BasePartnerPage
    {
        
        protected void Page_Load(object sender, EventArgs e)
        {
            
        }

        protected void btnSaveRevDeliveryRule_Click(object sender, EventArgs e)
        {
            List<KeyValuePair<string, object>> sidparams = new List<KeyValuePair<string, object>>();
            string storeGroupId = this.CurrentUser.APIStoreId.ToString();
            sidparams.Add(new KeyValuePair<string, object>("storeGroupId", storeGroupId));
            sidparams.Add(new KeyValuePair<string, object>("rdr_ruleFor", 2));
            sidparams.Add(new KeyValuePair<string, object>("rdr_ruleForId", storeGroupId));
            if (hyperlocal_delivery.Checked == true)
            {
                string delRuleName = "HLDR_" + storeGroupId+'_'+ DateTime.Now.ToString("ddMMyyHHmmss");
                sidparams.Add(new KeyValuePair<string, object>("rdr_ruleName", delRuleName));
                sidparams.Add(new KeyValuePair<string, object>("deliveryMode", 2));
                
                if (HyperlocalFixed.Checked == true)
                {
                    sidparams.Add(new KeyValuePair<string, object>("calculationMode", 2));
                    sidparams.Add(new KeyValuePair<string, object>("fixedRate", txtFixedCharge.Text));

                    sidparams.Add(new KeyValuePair<string, object>("dynamicMaxDistance", 0));
                    sidparams.Add(new KeyValuePair<string, object>("dynamicMaxWeight", 0));
                    sidparams.Add(new KeyValuePair<string, object>("dynamicRateKm", 0));
                    sidparams.Add(new KeyValuePair<string, object>("dynamicMinCharge", 0));
                }
                else if (HyperlocalDynamic.Checked == true)
                {
                    sidparams.Add(new KeyValuePair<string, object>("calculationMode", 1));
                    sidparams.Add(new KeyValuePair<string, object>("dynamicMaxDistance", dynamicMaxDistance.Text));
                    sidparams.Add(new KeyValuePair<string, object>("dynamicMaxWeight", dynamicMaxWeight.Text));
                    sidparams.Add(new KeyValuePair<string, object>("dynamicRateKm", dynamicRateKm.Text));
                    sidparams.Add(new KeyValuePair<string, object>("dynamicMinCharge", dynamicMinCharge.Text));

                    sidparams.Add(new KeyValuePair<string, object>("fixedRate", 0));
                }


                string revisedDeliveryRule = "insert into retaline_delivery_rules (rdr_ruleName,rdr_deliveryMode,rdr_calculationMode,rdr_fixedRateperkm,rdr_fixedRateMax,rdr_fromkm1,rdr_amt1,rdr_fixedRateMin,rdr_storeGroupId,rdr_ruleFor,rdr_ruleForId) " +
                    "values(@rdr_ruleName,@deliveryMode,@calculationMode,@fixedRate,@dynamicMaxDistance,@dynamicMaxWeight,@dynamicRateKm,@dynamicMinCharge,@storeGroupId,@rdr_ruleFor,@rdr_ruleForId)";
                var hldrSave = DataServiceMySql.ExecuteScalar(revisedDeliveryRule, Service.UserService.GetAPIConnectionString(), sidparams);
            }

            if (local_delivery.Checked == true)
            {
                string delRuleName = "SLDR_" + storeGroupId + '_' + DateTime.Now.ToString("ddMMyyHHmmss");
                sidparams.Add(new KeyValuePair<string, object>("slrdr_ruleName", delRuleName));
                sidparams.Add(new KeyValuePair<string, object>("sldeliveryMode", 3));

                if (LocalFixed.Checked == true)
                {
                    sidparams.Add(new KeyValuePair<string, object>("slcalculationMode", 2));
                    sidparams.Add(new KeyValuePair<string, object>("slfixedRate", txtSldFixedRate.Text));

                    sidparams.Add(new KeyValuePair<string, object>("sldynamicMaxDistance", 0));
                    sidparams.Add(new KeyValuePair<string, object>("sldynamicMaxWeight", 0));
                    sidparams.Add(new KeyValuePair<string, object>("sldynamicRateKm", 0));
                    sidparams.Add(new KeyValuePair<string, object>("sldynamicMinCharge", 0));
                    sidparams.Add(new KeyValuePair<string, object>("sldynamicMaxCharge", 0));
                }
                else if (LocalDynamic.Checked == true)
                {
                    sidparams.Add(new KeyValuePair<string, object>("slcalculationMode", 1));
                    sidparams.Add(new KeyValuePair<string, object>("sldynamicMaxDistance", txtsldDynamicMaxDistance.Text));
                    sidparams.Add(new KeyValuePair<string, object>("sldynamicMaxWeight", txtSldDynamicMaxWeight.Text));
                    sidparams.Add(new KeyValuePair<string, object>("sldynamicRateKm", txtSldDynamicRateKm.Text));
                    sidparams.Add(new KeyValuePair<string, object>("sldynamicMinCharge", txtSldDynamicMinCharge.Text));
                    sidparams.Add(new KeyValuePair<string, object>("sldynamicMaxCharge", txtSldDynamicMaxCharge.Text));

                    sidparams.Add(new KeyValuePair<string, object>("slfixedRate", 0));
                }

                string revisedDeliveryRule = "insert into retaline_delivery_rules (rdr_ruleName,rdr_deliveryMode,rdr_calculationMode,rdr_fixedRateperkm,rdr_fixedRateMax,rdr_fromkm1,rdr_amt1,rdr_fixedRateMin,rdr_storeGroupId,rdr_ruleFor,rdr_ruleForId,rdr_amt2) " +
                   "values(@slrdr_ruleName,@sldeliveryMode,@slcalculationMode,@slfixedRate,@sldynamicMaxDistance,@sldynamicMaxWeight,@sldynamicRateKm,@sldynamicMinCharge,@storeGroupId,@rdr_ruleFor,@rdr_ruleForId,@sldynamicMaxCharge)";
                var sldrSave = DataServiceMySql.ExecuteScalar(revisedDeliveryRule, Service.UserService.GetAPIConnectionString(), sidparams);
            }

            if (long_delivery.Checked == true)
            {
                string delRuleName = "LLDR_" + storeGroupId + '_' + DateTime.Now.ToString("ddMMyyHHmmss");
                sidparams.Add(new KeyValuePair<string, object>("llrdr_ruleName", delRuleName));
                sidparams.Add(new KeyValuePair<string, object>("lldeliveryMode", 4));

                if (LongFixed.Checked == true)
                {
                    sidparams.Add(new KeyValuePair<string, object>("llcalculationMode", 2));
                    sidparams.Add(new KeyValuePair<string, object>("llfixedRate", txtLlmFixedRate.Text));

                    sidparams.Add(new KeyValuePair<string, object>("lldynamicMaxDistance", 0));
                    sidparams.Add(new KeyValuePair<string, object>("lldynamicMaxWeight", 0));
                    sidparams.Add(new KeyValuePair<string, object>("lldynamicRateKm", 0));
                    sidparams.Add(new KeyValuePair<string, object>("lldynamicMinCharge", 0));
                    sidparams.Add(new KeyValuePair<string, object>("lldynamicMaxCharge", 0));
                }
                else if (LongDynamic.Checked == true)
                {
                    sidparams.Add(new KeyValuePair<string, object>("llcalculationMode", 1));
                    sidparams.Add(new KeyValuePair<string, object>("lldynamicMaxDistance", txtLlmDynamicMaxDistance.Text));
                    sidparams.Add(new KeyValuePair<string, object>("lldynamicMaxWeight", txtLlmDynamicMaxWeight.Text));
                    sidparams.Add(new KeyValuePair<string, object>("lldynamicRateKm", txtLlmDynamicRateKm.Text));
                    sidparams.Add(new KeyValuePair<string, object>("lldynamicMinCharge", txtLlmDynamicMinCharge.Text));
                    sidparams.Add(new KeyValuePair<string, object>("lldynamicMaxCharge", txtLlmDynamicMaxCharge.Text));

                    sidparams.Add(new KeyValuePair<string, object>("llfixedRate", 0));
                }


                string revisedDeliveryRule = "insert into retaline_delivery_rules (rdr_ruleName,rdr_deliveryMode,rdr_calculationMode,rdr_fixedRateperkm,rdr_fixedRateMax,rdr_fromkm1,rdr_amt1,rdr_fixedRateMin,rdr_storeGroupId,rdr_ruleFor,rdr_ruleForId,rdr_amt2) " +
                    "values(@llrdr_ruleName,@lldeliveryMode,@llcalculationMode,@llfixedRate,@lldynamicMaxDistance,@lldynamicMaxWeight,@lldynamicRateKm,@lldynamicMinCharge,@storeGroupId,@rdr_ruleFor,@rdr_ruleForId,@lldynamicMaxCharge)";
                var lldrSave = DataServiceMySql.ExecuteScalar(revisedDeliveryRule, Service.UserService.GetAPIConnectionString(), sidparams);
            }

            if (courier_delivery.Checked == true)
            {
                string delRuleName = "CUDR_" + storeGroupId + '_' + DateTime.Now.ToString("ddMMyyHHmmss");
                sidparams.Add(new KeyValuePair<string, object>("curdr_ruleName", delRuleName));
                sidparams.Add(new KeyValuePair<string, object>("cudeliveryMode", 1));

                if (courierFixed.Checked == true)
                {
                    sidparams.Add(new KeyValuePair<string, object>("cucalculationMode", 2));
                    sidparams.Add(new KeyValuePair<string, object>("cufixedRate", txtCourFixedRate.Text));

                    sidparams.Add(new KeyValuePair<string, object>("cudynamicMinCharge", 0));
                    sidparams.Add(new KeyValuePair<string, object>("cudynamicMaxCharge", 0));
                }
                else if (courierDynamic.Checked == true)
                {
                    sidparams.Add(new KeyValuePair<string, object>("cucalculationMode", 1));
                    sidparams.Add(new KeyValuePair<string, object>("cudynamicMinCharge", txtCourDynamicMinCharge.Text));
                    sidparams.Add(new KeyValuePair<string, object>("cudynamicMaxCharge", txtCourDynamicMaxCharge.Text));

                    sidparams.Add(new KeyValuePair<string, object>("cufixedRate", 0));
                }


                string revisedDeliveryRule = "insert into retaline_delivery_rules (rdr_ruleName,rdr_deliveryMode,rdr_calculationMode,rdr_fixedRateperkm,rdr_fixedRateMin,rdr_storeGroupId,rdr_ruleFor,rdr_ruleForId,rdr_amt2) " +
                    "values(@curdr_ruleName,@cudeliveryMode,@cucalculationMode,@cufixedRate,@cudynamicMinCharge,@storeGroupId,@rdr_ruleFor,@rdr_ruleForId,@cudynamicMaxCharge)";
                var courdrSave = DataServiceMySql.ExecuteScalar(revisedDeliveryRule, Service.UserService.GetAPIConnectionString(), sidparams);
            }

            if (parcel_delivery.Checked == true)
            {
                string delRuleName = "PRDR_" + storeGroupId + '_' + DateTime.Now.ToString("ddMMyyHHmmss");
                sidparams.Add(new KeyValuePair<string, object>("prrdr_ruleName", delRuleName));
                sidparams.Add(new KeyValuePair<string, object>("prdeliveryMode", 5));

                if (parcelFixed.Checked == true)
                {
                    sidparams.Add(new KeyValuePair<string, object>("prcalculationMode", 2));
                    sidparams.Add(new KeyValuePair<string, object>("prfixedRate", txtParcelFixedRate.Text));

                    sidparams.Add(new KeyValuePair<string, object>("prdynamicMinCharge", 0));
                    sidparams.Add(new KeyValuePair<string, object>("prdynamicMaxCharge", 0));
                }
                else if (parcelDynamic.Checked == true)
                {
                    sidparams.Add(new KeyValuePair<string, object>("prcalculationMode", 1));
                    sidparams.Add(new KeyValuePair<string, object>("prdynamicMinCharge", txtParcelDynamicMinCharge.Text));
                    sidparams.Add(new KeyValuePair<string, object>("prdynamicMaxCharge", txtParcelDynamicMaxCharge.Text));

                    sidparams.Add(new KeyValuePair<string, object>("prfixedRate", 0));
                }


                string revisedDeliveryRule = "insert into retaline_delivery_rules (rdr_ruleName,rdr_deliveryMode,rdr_calculationMode,rdr_fixedRateperkm,rdr_fixedRateMin,rdr_storeGroupId,rdr_ruleFor,rdr_ruleForId,rdr_amt2) " +
                    "values(@prrdr_ruleName,@prdeliveryMode,@prcalculationMode,@prfixedRate,@prdynamicMinCharge,@storeGroupId,@rdr_ruleFor,@rdr_ruleForId,@prdynamicMaxCharge)";
                var parceldrSave = DataServiceMySql.ExecuteScalar(revisedDeliveryRule, Service.UserService.GetAPIConnectionString(), sidparams);
            }

            if (cargo_delivery.Checked == true)
            {
                string delRuleName = "CADR_" + storeGroupId + '_' + DateTime.Now.ToString("ddMMyyHHmmss");
                sidparams.Add(new KeyValuePair<string, object>("cardr_ruleName", delRuleName));
                sidparams.Add(new KeyValuePair<string, object>("cadeliveryMode", 6));

                if (cargoFixed.Checked == true)
                {
                    sidparams.Add(new KeyValuePair<string, object>("cacalculationMode", 2));
                    sidparams.Add(new KeyValuePair<string, object>("cafixedRate", txtCargoFixedRate.Text));

                    sidparams.Add(new KeyValuePair<string, object>("cadynamicMinCharge", 0));
                    sidparams.Add(new KeyValuePair<string, object>("cadynamicMaxCharge", 0));
                }
                else if (cargoDynamic.Checked == true)
                {
                    sidparams.Add(new KeyValuePair<string, object>("cacalculationMode", 1));
                    sidparams.Add(new KeyValuePair<string, object>("cadynamicMinCharge", txtCargoDynamicMinCharge.Text));
                    sidparams.Add(new KeyValuePair<string, object>("cadynamicMaxCharge", txtCargoDynamicMaxCharge.Text));

                    sidparams.Add(new KeyValuePair<string, object>("cafixedRate", 0));
                }


                string revisedDeliveryRule = "insert into retaline_delivery_rules (rdr_ruleName,rdr_deliveryMode,rdr_calculationMode,rdr_fixedRateperkm,rdr_fixedRateMin,rdr_storeGroupId,rdr_ruleFor,rdr_ruleForId,rdr_amt2) " +
                    "values(@cardr_ruleName,@cadeliveryMode,@calculationMode,@cafixedRate,@cadynamicMinCharge,@storeGroupId,@rdr_ruleFor,@rdr_ruleForId,@cadynamicMaxCharge)";
                var cargodrSave = DataServiceMySql.ExecuteScalar(revisedDeliveryRule, Service.UserService.GetAPIConnectionString(), sidparams);
            }
            if (manual_delivery.Checked == true)
            {
                string delRuleName = "MNDR_" + storeGroupId + '_' + DateTime.Now.ToString("ddMMyyHHmmss");
                sidparams.Add(new KeyValuePair<string, object>("mnrdr_ruleName", delRuleName));
                sidparams.Add(new KeyValuePair<string, object>("mndeliveryMode", 7));

                if (manualFixed.Checked == true)
                {
                    sidparams.Add(new KeyValuePair<string, object>("mncalculationMode", 2));
                    sidparams.Add(new KeyValuePair<string, object>("mnfixedRate", txtManualFixedRate.Text));

                    sidparams.Add(new KeyValuePair<string, object>("mndynamicMinCharge", 0));
                    sidparams.Add(new KeyValuePair<string, object>("mndynamicMaxCharge", 0));
                }
                else if (manualDynamic.Checked == true)
                {
                    sidparams.Add(new KeyValuePair<string, object>("mncalculationMode", 1));
                    sidparams.Add(new KeyValuePair<string, object>("mndynamicMinCharge", txtManualDynamicMinCharge.Text));
                    sidparams.Add(new KeyValuePair<string, object>("mndynamicMaxCharge", txtManualDynamicMaxCharge.Text));

                    sidparams.Add(new KeyValuePair<string, object>("mnfixedRate", 0));
                }


                string revisedDeliveryRule = "insert into retaline_delivery_rules (rdr_ruleName,rdr_deliveryMode,rdr_calculationMode,rdr_fixedRateperkm,rdr_fixedRateMin,rdr_storeGroupId,rdr_ruleFor,rdr_ruleForId,rdr_amt2) " +
                    "values(@mnrdr_ruleName,@mndeliveryMode,@mncalculationMode,@mnfixedRate,@mndynamicMinCharge,@storeGroupId,@rdr_ruleFor,@rdr_ruleForId,@mndynamicMaxCharge)";
                var manualdrSave = DataServiceMySql.ExecuteScalar(revisedDeliveryRule, Service.UserService.GetAPIConnectionString(), sidparams);
            }

            if (share_delivery.Checked == true)
            {
                sidparams.Add(new KeyValuePair<string, object>("shareCost", txtShareCost.Text));
                sidparams.Add(new KeyValuePair<string, object>("shareSubject", txtShareSubject.Text));
                sidparams.Add(new KeyValuePair<string, object>("shareValue", txtShareValue.Text));
                sidparams.Add(new KeyValuePair<string, object>("shareType", txtShareType.Text));

                string revisedDeliveryRule = "insert into delivery_cost_share (storeGroupId,shareCost,shareSubject,shareValue,shareType) " +
                    "values(@storeGroupId,@shareCost,@shareSubject,@shareValue,@shareType)";
                var costshareSave = DataServiceMySql.ExecuteScalar(revisedDeliveryRule, Service.UserService.GetAPIConnectionString(), sidparams);
            }

        }
    }
}


