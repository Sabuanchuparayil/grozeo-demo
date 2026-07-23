using RetalineProAgent.Core.Services;
using System;
using System.Collections.Generic;
using System.Data;
using System.Globalization;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Configuration;
using RetalineProAgent.Service;
using System.Text;
using Org.BouncyCastle.Ocsp;
using RetalineProAgent.Core.BussinessModel.Finance;
using RetalineProAgent.Core.BussinessModel.Inventory;
using RetalineProAgent.Core.Services.ActiveLog;
using System.EnterpriseServices;

namespace RetalineProAgent
{
    public partial class DeliveryRuleSettings: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            SDSBranch.ConnectionString = DataService.APIConnectionString(Service.UserService.GetAPIConnectionString());
            string strKmRate = $"KM {ConfigurationManager.AppSettings.Get("CurrencySymbol")}";
            txtRs1.Attributes.Add("placeholder", strKmRate);
            txtRs2.Attributes.Add("placeholder", strKmRate);
            txtRs3.Attributes.Add("placeholder", strKmRate);
            txtRs.Attributes.Add("placeholder", $"Above {ConfigurationManager.AppSettings.Get("CurrencySymbol")}.");

            if (selCalMode.SelectedItem.Text == "Distance Rate")
            {
                plHld1.Visible = true;
                plHld2.Visible = true;
                plHld3.Visible = true;
                plHld4.Visible = false;
            }
            else if (selCalMode.SelectedItem.Text == "Rate")
            {
                plHld1.Visible = false;
                plHld2.Visible = false;
                plHld3.Visible = false;
                plHld4.Visible = true;
            }
            if (isfree.Checked == true)
            {
                txtRs.Visible = true;
                rsValid.Visible = true;
            }
            else
            {
                txtRs.Visible = false;
                rsValid.Visible = false;
            }
        }

        protected void btnAdd_Click(object sender, EventArgs e)
        {
            decimal rate = 0, minrate = 0, maxrate = 0;
            if (plHld4.Visible == true)
            {
                if (String.IsNullOrEmpty(txtRate.Text) || String.IsNullOrEmpty(txtMinRate.Text) || String.IsNullOrEmpty(txtMaxRate.Text))
                {
                    Common.ShowToastifyMessage(Page, $"Missing data. Please add value for {(String.IsNullOrEmpty(txtRate.Text) ? "Rate / Km" : "")}{(String.IsNullOrEmpty(txtMinRate.Text) ? ", Min Rate" : "")}{(String.IsNullOrEmpty(txtMaxRate.Text) ? ", Max Rate" : "")}");
                    return;
                }

                try { rate = Convert.ToDecimal(txtRate.Text); } catch { rate = 0; }
                try { minrate = Convert.ToDecimal(txtMinRate.Text); } catch { minrate = 0; }
                try { maxrate = Convert.ToDecimal(txtMaxRate.Text); } catch { maxrate = 0; }
            }


            //if (plHld4.Visible == false)
            //{
            //    rate = 0;
            //    minrate = 0;
            //    maxrate = 0;
            //}
            //else
            //{
            //    rate = Convert.ToDecimal(txtRate.Text);
            //    minrate = Convert.ToDecimal(txtMinRate.Text);
            //    maxrate = Convert.ToDecimal(txtMaxRate.Text);
            //}

            decimal from1 = 0, to1 = 0, amt1 = 0, from2 = 0, to2 = 0, amt2 = 0, from3 = 0, to3 = 0, amt3 = 0;
            if (plHld1.Visible == true && plHld2.Visible == true && plHld3.Visible == true)
            {
                if (String.IsNullOrEmpty(txtFrom1.Text) || String.IsNullOrEmpty(txtTo1.Text) || String.IsNullOrEmpty(txtRs1.Text))
                {
                    Common.ShowToastifyMessage(Page, "Failure, ");
                    return;
                }

                // input variables.
                

                try { from1 = Convert.ToDecimal(txtFrom1.Text); } catch { from1 = 0; }
                try { to1 = Convert.ToDecimal(txtTo1.Text); } catch { to1 = 0; }
                try { amt1 = Convert.ToDecimal(txtRs1.Text); } catch { amt1 = 0; }

                if (!String.IsNullOrEmpty(txtFrom2.Text))
                    try { from2 = Convert.ToDecimal(txtFrom2.Text); } catch { from2 = 0; }
                if (!String.IsNullOrEmpty(txtTo2.Text))
                    try { to2 = Convert.ToDecimal(txtTo2.Text); } catch { to2 = 0; }
                if (!String.IsNullOrEmpty(txtRs2.Text))
                    try { amt2 = Convert.ToDecimal(txtRs2.Text); } catch { amt2 = 0; }
                if (!String.IsNullOrEmpty(txtFrom2.Text))
                    try { from3 = Convert.ToDecimal(txtFrom3.Text); } catch { from3 = 0; }
                if (!String.IsNullOrEmpty(txtFrom2.Text))
                    try { to3 = Convert.ToDecimal(txtTo3.Text); } catch { to3 = 0; }
                if (!String.IsNullOrEmpty(txtFrom2.Text))
                    try { amt3 = Convert.ToDecimal(txtRs3.Text); } catch { amt3 = 0; }
            }


            decimal freeAmt = 0;
            if(isfree.Checked == true)
            {
                freeAmt = Convert.ToDecimal(txtRs.Text);
            }
            else
            {
                freeAmt = 0;
            }

            int ruleFor = 0;
            if(rbAllStores.Checked == true)
            {
                ruleFor = 2;
            }
            else
            {
                ruleFor = 3;
            }

            string ruleForId = null;
            if (rbSelectStore.Checked == true)
            {
                ruleForId = selBranch.Text;
            }
            else
            {
                ruleForId = "0";
            }
            int branchId = Convert.ToInt32(ruleForId);


            List<KeyValuePair<string, object>> dvparams = new List<KeyValuePair<string, object>>();
            dvparams.Add(new KeyValuePair<string, object>("delivMode", selDelivMode.Text));
            dvparams.Add(new KeyValuePair<string, object>("calMode", selCalMode.Text));
            dvparams.Add(new KeyValuePair<string, object>("from1", from1));
            dvparams.Add(new KeyValuePair<string, object>("to1", to1));
            dvparams.Add(new KeyValuePair<string, object>("amt1", amt1));
            dvparams.Add(new KeyValuePair<string, object>("from2", from2));
            dvparams.Add(new KeyValuePair<string, object>("to2", to2));
            dvparams.Add(new KeyValuePair<string, object>("amt2", amt2));
            dvparams.Add(new KeyValuePair<string, object>("from3", from3));
            dvparams.Add(new KeyValuePair<string, object>("to3", to3));
            dvparams.Add(new KeyValuePair<string, object>("amt3", amt3));
            dvparams.Add(new KeyValuePair<string, object>("rate", rate));
            dvparams.Add(new KeyValuePair<string, object>("minrate", minrate));
            dvparams.Add(new KeyValuePair<string, object>("maxrate", maxrate));
            dvparams.Add(new KeyValuePair<string, object>("isfree", (isfree.Checked ? 1 : 0)));
            dvparams.Add(new KeyValuePair<string, object>("txtRs", freeAmt));
            dvparams.Add(new KeyValuePair<string, object>("branch", branchId));
            dvparams.Add(new KeyValuePair<string, object>("ruleName", txtRuleName.Text));
            dvparams.Add(new KeyValuePair<string, object>("ruleFor", ruleFor));
            //dvparams.Add(new KeyValuePair<string, object>("allStores", ruleForId));
            dvparams.Add(new KeyValuePair<string, object>("default", 0));
            dvparams.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));

            string strSql = $"INSERT INTO retaline_delivery_rules(rdr_ruleName, rdr_deliveryMode, rdr_calculationMode, rdr_fixedRateperkm, rdr_fixedRateMin, rdr_fixedRateMax, rdr_fromkm1, rdr_tokm1, " +
                $"rdr_amt1, rdr_fromkm2, rdr_tokm2, rdr_amt2, rdr_fromkm3, rdr_tokm3, rdr_amt3, rdr_isfreeDelivery, rdr_isfreeDeliveryAmt, rdr_ruleFor, rdr_ruleForId, is_default, rdr_storeGroupId) " +
                $"VALUES(@ruleName, @delivMode, @calMode, @rate, @minrate, @maxrate, @from1, @to1, @amt1, @from2, @to2, @amt2, @from3, @to3, @amt3, @isfree, @txtRs, @ruleFor, @branch, @default, @storegroupid)";
            DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), dvparams);
            // Activitylog
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            string Source = strUrl;
            int storegroupid = this.CurrentUser.APIStoreId;
            string Users = this.CurrentUser.Email;
            string delivMode = selDelivMode.Text;
            string calMode = selCalMode.Text;
            string from = from1.ToString();
            string to = to1.ToString();
            string atm = amt1.ToString();
            string From2 = from2.ToString();
            string To2 = to2.ToString();
            string FROM = from3.ToString();
            string To = to3.ToString();
            string Atm = amt2.ToString();
            string ATM = amt3.ToString();
            string Rate = rate.ToString();
            string Minrate = minrate.ToString();
            string Maxrate = maxrate.ToString();
            string Isfree = (isfree.Checked ? 1 : 0).ToString();
            string TxtRs = freeAmt.ToString();
            string Branch = branchId.ToString();
            string RuleFor = ruleFor.ToString();
            string Default = "0";
            string store = (this.CurrentUser.APIStoreId).ToString();
            var items = new[]
                {
                             new { Key = "Delivery Mode", Value = delivMode },
                             new { Key = "Calculation Mode", Value = calMode },
                             new { Key = "from1", Value = from },
                             new { Key = "to1", Value = to },
                             new { Key = "atm1", Value = atm },
                             new { Key = "From2", Value = From2 },
                             new { Key = "To2", Value = To2 },
                             new { Key = "FROM3", Value = FROM },
                             new { Key = "To3", Value = To },
                             new { Key = "Atm2", Value = Atm },
                             new { Key = "ATM3", Value = ATM },
                             new { Key = "Rate", Value = Rate },
                             new { Key = "Minrate", Value = Minrate },
                             new { Key = "Maxrate", Value = Maxrate },
                             new { Key = "Isfree", Value = Isfree },
                             new { Key = "TxtRs", Value = TxtRs },
                             new { Key = "Branch", Value = Branch },
                             new { Key = "RuleFor", Value = RuleFor },
                             new { Key = "Default", Value = Default },
                             new { Key = "store", Value = store },
                             };
            string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
            var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);
            ShowSuccess("Success!", "Delivery rule created successfully", "/DeliveryRules");
        }

        private void ShowSuccess(string title, string content, string redirect = "")
        {
            Type cstype = this.GetType();
            String csname1 = "PopupScript";
            ClientScriptManager cs = Page.ClientScript;
            ltrSuccessTitle.Text = title;
            ltrSuccessContent.Text = content;

            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> $('#modaldemo4').modal('show'); {(string.IsNullOrEmpty(redirect) ? "" : "$('#modaldemo4').on('hidden.bs.modal', function (e) {window.location.href = '" + redirect + "'; });")}</");
            cstext1.Append("script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }

        private void ShowFailure(string title, string content, string redirect = "")
        {
            Type cstype = this.GetType();
            String csname1 = "PopupScript";
            ClientScriptManager cs = Page.ClientScript;
            ltrErrorPopupTitle.Text = title;
            ltrErrorPopupText.Text = content;

            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> $('#modaldemo5').modal('show'); {(string.IsNullOrEmpty(redirect) ? "" : "$('#modaldemo5').on('hidden.bs.modal', function (e) {window.location.href = '" + redirect + "'; });")}</");
            cstext1.Append("script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }


        protected void SDSBranch_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        }
    }
}



