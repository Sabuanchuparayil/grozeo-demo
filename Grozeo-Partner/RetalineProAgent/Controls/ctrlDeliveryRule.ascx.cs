using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Security.Policy;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Controls
{
    public partial class ctrlDeliveryRule : Base.BasePartnerUserControl
    {
        #region control properties
        public int DeliveryRuleTypeID
        {
            get
            {
                if (ViewState["CTRL_DELIRULE_TYPEID"] != null)
                    return (int)ViewState["CTRL_DELIRULE_TYPEID"];
                return 0;
            }
            set
            {
                ViewState["CTRL_DELIRULE_TYPEID"] = value;
            }

        }

        /// <summary>
        /// 1: Merchant, 2: Area, 3: Common
        /// </summary>
        public int DeliveryRuleFor
        {
            get
            {
                if (ViewState["CTRL_DELIRULE_FOR"] != null)
                    return (int)ViewState["CTRL_DELIRULE_FOR"];
                return 1;
            }
            set
            {
                ViewState["CTRL_DELIRULE_FOR"] = value;
            }

        }


        public string DeliveryRuleType
        {
            set
            {
                lblDeliveryRule.Text = value;
                lblDeliveryRulenew.Text = value;
            }
        }
        public string DeliveryRuleWeight { set { lblDeliveryRuleWeight.Text = String.Format("(Max Weight: {0}Kg)", value); } }
        public int RuleID { set { chk_delivery.Checked = value > 0; } }
        public int CalculationMode {  get; set; }
        public string FixedRateperkm {  get; set; }
        public string FixedRateMax {  get; set; }
        public string FixedRateVal {  get; set; }
        public string FixedRateMin {  get; set; }
        public string FixedRateFree {  get; set; }
        public string SlabValues { get; set; }

        #endregion

        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                plcCourierDeliveryControls.Visible = !(new int[] { 2, 3, 4 }).Contains(DeliveryRuleTypeID);
            }
        }

        protected void Page_PreRender(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                rb_Fixed.Checked = (CalculationMode == 2);
                rb_Dynamic.Checked = (CalculationMode == 1);
                rb_Weight.Checked = (CalculationMode == 4);
                rb_ZoneWeight.Checked = (CalculationMode == 5);

                // Express: - (Hyper Local or Scheduled Local or Local Last Mile)
                bool isExpress = (new int[] { 2, 3, 4 }).Contains(DeliveryRuleTypeID);

                // Express delivery mode or selected fixed rate.
                if (isExpress || CalculationMode == 2)
                {
                    txtMinAmount.Text = FixedRateMin;
                    txtMaxAmount.Text = FixedRateMax;
                    txtRatePerKM.Text = FixedRateperkm;
                    txtFreeAbove.Text = FixedRateFree;
                }
                else if (CalculationMode == 1) // Dynamic
                {
                    txtDynamicMaxVal.Text = FixedRateMax;
                    txtDynamicMinVal.Text = FixedRateMin;
                    hidDynamic.Value = SlabValues;
                }
                else if (CalculationMode == 4) // Weight
                {
                    hidWeight.Value = SlabValues;
                }

            }
        }

        /// <summary>
        /// Save delivery rule in database.
        /// </summary>
        /// <returns>Number of records affected</returns>
        public int SaveDeliveryRule()
        {
            int forId = CurrentUser.APIStoreId, ruleFor = 3, storegroupId =0;
            if (DeliveryRuleFor == 2)
            {
                forId = CurrentUser.AreaId.Value;
                ruleFor = 4;
            }
            else
            {
                storegroupId = CurrentUser.APIStoreId;
                if (!String.IsNullOrEmpty(Request.QueryString["brid"]))
                {
                    int brId = 0; try { brId = Convert.ToInt32(Request.QueryString["brid"]); } catch { brId = 0; }
                    if (brId > 0)
                    {
                        ruleFor = 3; forId = brId;
                    }
                }
            }

            int recordsAffected = 0;
            string sql = " UPDATE retaline_delivery_rules SET `Status` = 0 WHERE rdr_deliveryMode = @deliMode and rdr_ruleFor = @ruleFor and rdr_ruleForId= @ruleForId and rdr_storeGroupId=@storegroupid; ";

            string strInsertSql = "";
            string strInsertFields = "rdr_storeGroupId, rdr_deliveryMode,rdr_calculationMode, rdr_fixedRateperkm, rdr_fixedRateMax, rdr_amt1, rdr_fixedRateMin, rdr_amt2, rdr_ruleFor, rdr_ruleForId, rdr_isfreeDeliveryAmt";

            List<KeyValuePair<string, object>> insertPrms = new List<KeyValuePair<string, object>>(){
                new KeyValuePair<string, object>("storegroupid", storegroupId),
                new KeyValuePair<string, object>("ruleFor", ruleFor),
                new KeyValuePair<string, object>("ruleForId", forId),
                new KeyValuePair<string, object>("deliMode", DeliveryRuleTypeID)
            };

            if (chk_delivery.Checked)
            {
                bool isExpress = (new int[] { 2, 3, 4 }).Contains(DeliveryRuleTypeID);
                int calcMode = (rb_Dynamic.Checked ? 1 : (rb_Weight.Checked ? 4 : (rb_ZoneWeight.Checked ? 5 : 2)));
                calcMode = ((new int[] { 2, 3, 4 }).Contains(DeliveryRuleTypeID) ? 2 : calcMode);

                insertPrms.Add(new KeyValuePair<string, object>($"{DeliveryRuleTypeID}_CalcMode", calcMode));
                insertPrms.Add(new KeyValuePair<string, object>($"{DeliveryRuleTypeID}_rdr_fixedRateperkm", isExpress || calcMode == 2 ? ConvertToDoubleOrDefault(txtRatePerKM.Text) : 0 ));
                insertPrms.Add(new KeyValuePair<string, object>($"{DeliveryRuleTypeID}_rdr_fixedRateMax", isExpress || calcMode == 2 ? ConvertToDoubleOrDefault(txtMaxAmount.Text) : (calcMode == 1 ? ConvertToDoubleOrDefault(txtDynamicMaxVal.Text) : 0)));
                insertPrms.Add(new KeyValuePair<string, object>($"{DeliveryRuleTypeID}_rdr_amt1", isExpress || calcMode == 2 ? ConvertToDoubleOrDefault(txtRatePerKM.Text) : 0));
                insertPrms.Add(new KeyValuePair<string, object>($"{DeliveryRuleTypeID}_rdr_fixedRateMin", isExpress || calcMode == 2 ? ConvertToDoubleOrDefault(txtMinAmount.Text) : (calcMode == 1 ? ConvertToDoubleOrDefault(txtDynamicMinVal.Text) : 0) ));
                insertPrms.Add(new KeyValuePair<string, object>($"{DeliveryRuleTypeID}_rdr_amt2", isExpress || calcMode == 2 ? ConvertToDoubleOrDefault(txtMaxAmount.Text) : (calcMode == 1 ? ConvertToDoubleOrDefault(txtDynamicMaxVal.Text) : 0)));
                insertPrms.Add(new KeyValuePair<string, object>($"{DeliveryRuleTypeID}_freeDeliveryAmt", isExpress || calcMode == 2 ? ConvertToDoubleOrDefault(txtFreeAbove.Text) : 0 ));

                strInsertSql += (String.IsNullOrEmpty(strInsertSql) ? "" : ",") + $"(@storegroupid, @deliMode, @{DeliveryRuleTypeID}_CalcMode, @{DeliveryRuleTypeID}_rdr_fixedRateperkm, " +
                    $"@{DeliveryRuleTypeID}_rdr_fixedRateMax, @{DeliveryRuleTypeID}_rdr_amt1, @{DeliveryRuleTypeID}_rdr_fixedRateMin, @{DeliveryRuleTypeID}_rdr_amt2, @ruleFor, @ruleForId, @{DeliveryRuleTypeID}_freeDeliveryAmt)";
            }

            if (!String.IsNullOrEmpty(strInsertSql))
                sql = sql + " insert into retaline_delivery_rules(" + strInsertFields + ") values" + strInsertSql + "; ";
            int result = DataServiceMySql.ExecuteSqlWithTransaction(sql, UserService.GetAPIConnectionString(), insertPrms);
            recordsAffected += result;

            DataTable dtNewData = DataServiceMySql.GetDataTable("select * from retaline_delivery_rules where rdr_deliveryMode = @deliMode and rdr_ruleFor = @ruleFor and rdr_ruleForId= @ruleForId and  rdr_storeGroupId=@storegroupid",
                UserService.GetAPIConnectionString(), new List<KeyValuePair<string, object>> { new KeyValuePair<string, object>("storegroupid", storegroupId),
                    new KeyValuePair<string, object>("deliMode", DeliveryRuleTypeID), new KeyValuePair<string, object>("ruleForId", forId), new KeyValuePair<string, object>("ruleFor", ruleFor) });

            if (recordsAffected > 0 && dtNewData != null && dtNewData.Rows.Count > 0)
            {
                string strInsertSlabSql = "";
                string strInsertSlabFields = "drId, slabType, slabKm, slabAmount, weight, zoneId";
                List<KeyValuePair<string, object>> insertSlabPrms = new List<KeyValuePair<string, object>>();

                // Slab
                try
                {
                    DataRow dr = dtNewData.AsEnumerable().Where(r => Convert.ToInt32(r["rdr_deliveryMode"]) == DeliveryRuleTypeID).FirstOrDefault();
                    int rdrId = 0; try { if (dr != null) rdrId = Convert.ToInt32(dr["rdr_id"]); } catch { rdrId = 0; }
                    if (rdrId > 0 && chk_delivery.Checked)
                    {
                        if (rb_Dynamic.Checked && !String.IsNullOrEmpty(hidDynamic.Value))
                        {
                            int indx = 1;
                            foreach (string strVal in hidDynamic.Value.Split(new char[] { '|' }))
                            {
                                if (String.IsNullOrEmpty(strVal))
                                    continue;
                                var strData = strVal.Split(',');
                                insertSlabPrms.Add(new KeyValuePair<string, object>("rdrId_" + indx, rdrId));
                                insertSlabPrms.Add(new KeyValuePair<string, object>("slabType_" + indx, (strData[0] == "Upto" ? 1 : 2)));
                                insertSlabPrms.Add(new KeyValuePair<string, object>("slabKm_" + indx, strData[1]));
                                insertSlabPrms.Add(new KeyValuePair<string, object>("slabAmount_" + indx, strData[2]));
                                strInsertSlabSql += (String.IsNullOrEmpty(strInsertSlabSql) ? "" : ",") + "(@rdrId_" + indx + ", @slabType_" + indx + ", @slabKm_" + indx + ", @slabAmount_" + indx + ", -1, null)";
                                indx++;
                            }
                        }
                        else if (rb_Weight.Checked && !String.IsNullOrEmpty(hidWeight.Value))
                        {
                            int indx = 1; string strWeightVal = hidWeight.Value;
                            if (!String.IsNullOrEmpty(txtDynamicAboveVal.Text) && !string.IsNullOrEmpty(txtDynamicAboveKG.Text))
                            {
                                string strAbove = String.Format("Above,{0},{1}", txtDynamicAboveKG.Text, txtDynamicAboveVal.Text);
                                strWeightVal = string.Join("|", strWeightVal.Split(new char[] { '|' }).Where(s=> !s.StartsWith("Above,")).ToArray()) + "|" + strAbove;
                                //strWeightVal += "|" + strAbove;
                                hidWeight.Value = strWeightVal;
                            }
                            foreach (string strVal in hidWeight.Value.Split(new char[] { '|' }))
                            {
                                if (String.IsNullOrEmpty(strVal))
                                    continue;
                                var strData = strVal.Split(',');
                                insertSlabPrms.Add(new KeyValuePair<string, object>("WrdrId_" + indx, rdrId));
                                insertSlabPrms.Add(new KeyValuePair<string, object>("WslabType_" + indx, (strData[0] == "First" ? 1 : (strData[0] == "Above" ? 3 : 2))));
                                insertSlabPrms.Add(new KeyValuePair<string, object>("WslabKm_" + indx, strData[1]));
                                insertSlabPrms.Add(new KeyValuePair<string, object>("WslabAmount_" + indx, strData[2]));
                                insertSlabPrms.Add(new KeyValuePair<string, object>("WslabKg_" + indx, strData[1]));
                                strInsertSlabSql += (String.IsNullOrEmpty(strInsertSlabSql) ? "" : ",") + "(@WrdrId_" + indx + ", @WslabType_" + indx + ", @WslabKm_" + indx + ", @WslabAmount_" + indx + ", @WslabKg_" + indx + ", null)";
                                indx++;
                            }
                        }
                        else if (rb_ZoneWeight.Checked)
                        {
                            int indx = 1;
                            foreach (RepeaterItem rptitem in rptZones.Items)
                            {
                                HiddenField hlzone = (HiddenField)rptitem.FindControl("hidZone1Val");
                                HyperLink hl = (HyperLink)rptitem.FindControl("hlZone");
                                int zoneId = 0; try { zoneId = Convert.ToInt32(hl.Attributes["zoneid"]); } catch { zoneId = 0; }

                                if (hlzone != null && !String.IsNullOrEmpty(hlzone.Value) && zoneId > 0)
                                {
                                    foreach (string strVal in hlzone.Value.Split(new char[] { '|' }))
                                    {
                                        if (String.IsNullOrEmpty(strVal))
                                            continue;
                                        var strData = strVal.Split(',');
                                        insertSlabPrms.Add(new KeyValuePair<string, object>("ZrdrId_" + indx, rdrId));
                                        insertSlabPrms.Add(new KeyValuePair<string, object>("ZslabType_" + indx, (strData[0] == "First" ? 1 : (strData[0] == "Above" ? 3 : 2))));
                                        insertSlabPrms.Add(new KeyValuePair<string, object>("ZslabKm_" + indx, strData[1]));
                                        insertSlabPrms.Add(new KeyValuePair<string, object>("ZslabAmount_" + indx, strData[2]));
                                        insertSlabPrms.Add(new KeyValuePair<string, object>("ZslabKg_" + indx, strData[1]));
                                        insertSlabPrms.Add(new KeyValuePair<string, object>("ZslabZoneId_" + indx, zoneId));
                                        strInsertSlabSql += (String.IsNullOrEmpty(strInsertSlabSql) ? "" : ",") + "(@ZrdrId_" + indx + ", @ZslabType_" + indx + ", @ZslabKm_" + indx + ", @ZslabAmount_" + indx + ", @ZslabKg_" + indx + ", @ZslabZoneId_" + indx + ")";
                                        indx++;
                                    }

                                }
                            }

                        }

                    }
                }
                catch (Exception ex) {
                    string strException = ex.Message;

                }

                if (!String.IsNullOrEmpty(strInsertSlabSql))
                {
                    strInsertSlabSql = "insert into delivery_rule_slab(" + strInsertSlabFields + ") values" + strInsertSlabSql;
                    DataServiceMySql.ExecuteSql(strInsertSlabSql, UserService.GetAPIConnectionString(), insertSlabPrms);
                }


            }

            return recordsAffected;

        }

        protected void SDSZone_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            if (e.Command.Parameters.Contains("storeId"))
                e.Command.Parameters["storeId"].Value = this.CurrentUser.APIStoreId;
            if (e.Command.Parameters.Contains("delimode"))
                e.Command.Parameters["delimode"].Value = DeliveryRuleTypeID;

        }

        protected void rptZones_ItemDataBound(object sender, RepeaterItemEventArgs e)
        {
            if (e.Item.ItemType == ListItemType.Item || e.Item.ItemType == ListItemType.AlternatingItem)
            {
                HyperLink hl = (HyperLink)e.Item.FindControl("hlZone");
                HiddenField hid = (HiddenField)e.Item.FindControl("hidZone1Val");
                if (hid != null && hl != null)
                {
                    hl.Attributes.Add("hidid", hid.ClientID);
                }
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