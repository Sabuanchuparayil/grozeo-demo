using System;
using System.Web.UI;
using System.Web.UI.WebControls;
using RetalineProAgent.Service;


namespace RetalineProAgent.Tenant.Finance
{
    public partial class MerchantTaxReport : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                ddlPeriods.SelectedValue = "DateRange";

                DateTime today = DateTime.Now; // Current date
                DateTime oneMonthAgo = today.AddMonths(-1).AddDays(1); // Same day, previous month

                txtDateFrom.Text = oneMonthAgo.ToString("yyyy-MM-dd");
                txtDateTo.Text = DateTime.Now.ToString("yyyy-MM-dd");



                divMonths.Visible = false;
                divDateFrom.Visible = true;
                divDateTo.Visible = true;
                divFinancialYear.Visible = false;

                int startYear = 2022; // Starting year for financial years
                int currentYear = DateTime.Now.Year;

                // Determine if the current date is before April 1st, which would make it part of the previous financial year
                if (DateTime.Now.Month < 4)
                {
                    currentYear--; // Go to the previous year if it's before April
                }

                for (int year = startYear; year <= currentYear; year++)
                {
                    // Create financial year representation (e.g., 2022-23, 2023-24, etc.)
                    string financialYear = $"{year}-{(year + 1).ToString().Substring(2, 2)}";
                    ddlFinancialYears.Items.Add(new ListItem(financialYear, financialYear));
                }

                // Optionally, set a default selected financial year
                ddlFinancialYears.SelectedValue = $"{currentYear}-{(currentYear + 1).ToString().Substring(2, 2)}";
            }
        }

        protected void SDSGroupBranches_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            if (e.Command.Parameters.Contains("storegroup"))
                e.Command.Parameters["@storegroup"].Value = this.CurrentUser.APIStoreId;
        }

        protected void lbtnSearch_Click(object sender, EventArgs e)
        {

        }

        protected void ddlPeriods_SelectedIndexChanged(object sender, EventArgs e)
        {
            string selectedValue = ddlPeriods.SelectedValue;
            switch (selectedValue)
            {
                case "FinancialYear":
                    divFinancialYear.Visible = true;
                    divMonths.Visible = false;
                    divDateFrom.Visible = false;
                    divDateTo.Visible = false;
                    financialYearChanged();
                    break;
                case "Month":
                    divMonths.Visible = true;
                    divDateFrom.Visible = false;
                    divDateTo.Visible = false;
                    divFinancialYear.Visible = true;

                    string selectedFinancialYear = ddlFinancialYears.SelectedValue;
                    int startYear = int.Parse(selectedFinancialYear.Substring(0, 4));
                    int endYear = startYear + 1;
                    int month = int.Parse(ddlMonths.SelectedValue);
                    int year = 0;
                    year = (month <= 3) ? endYear : startYear;

                    DateTime startDate = new DateTime(year, month, 1); // First day of the month

                    DateTime endDate = new DateTime(year, month, DateTime.DaysInMonth(year, month)); // Last day of the month

                    txtDateFrom.Text = startDate.ToString("yyyy-MM-dd");
                    txtDateTo.Text = endDate.ToString("yyyy-MM-dd");


                    break;
                case "DateRange":
                    DateTime today = DateTime.Now; // Current date
                    DateTime oneMonthAgo = today.AddMonths(-1).AddDays(1); // Same day, previous month

                    txtDateFrom.Text = oneMonthAgo.ToString("yyyy-MM-dd");
                    txtDateTo.Text = DateTime.Now.ToString("yyyy-MM-dd");

                    divMonths.Visible = false;
                    divFinancialYear.Visible = false;
                    divDateFrom.Visible = true;
                    divDateTo.Visible = true;
                    break;
            }
        }

        private void financialYearChanged()
        {
            if (divMonths.Visible == false)
            {
                string selectedFinancialYear = ddlFinancialYears.SelectedValue;

                // Extract the first year from the financial year string (assuming "YYYY-YY" format)
                int startYear = int.Parse(selectedFinancialYear.Substring(0, 4));
                int endYear = startYear + 1;

                // Calculate the start and end dates for the financial year
                DateTime startDate = new DateTime(startYear, 4, 1); // April 1st of the start year
                DateTime endDate = new DateTime(endYear, 3, 31); // March 31st of the end year

                txtDateFrom.Text = startDate.ToString("yyyy-MM-dd");
                txtDateTo.Text = endDate.ToString("yyyy-MM-dd");
            }
            else
            {
                string selectedFinancialYear = ddlFinancialYears.SelectedValue;
                int startYear = int.Parse(selectedFinancialYear.Substring(0, 4));
                int endYear = startYear + 1;
                int month = int.Parse(ddlMonths.SelectedValue);
                int year = 0;
                year = (month <= 3) ? endYear : startYear;

                DateTime startDate = new DateTime(year, month, 1); // First day of the month

                DateTime endDate = new DateTime(year, month, DateTime.DaysInMonth(year, month)); // Last day of the month

                txtDateFrom.Text = startDate.ToString("yyyy-MM-dd");
                txtDateTo.Text = endDate.ToString("yyyy-MM-dd");

            }
        }

        protected void ddlFinancialYears_SelectedIndexChanged(object sender, EventArgs e)
        {

            financialYearChanged();
        }

        protected void ddlMonths_SelectedIndexChanged(object sender, EventArgs e)
        {
            string selectedFinancialYear = ddlFinancialYears.SelectedValue;
            int startYear = int.Parse(selectedFinancialYear.Substring(0, 4));
            int endYear = startYear + 1;
            int month = int.Parse(ddlMonths.SelectedValue);
            int year = 0;
            year = (month <= 3) ? endYear : startYear;

            DateTime startDate = new DateTime(year, month, 1); // First day of the month

            DateTime endDate = new DateTime(year, month, DateTime.DaysInMonth(year, month)); // Last day of the month

            txtDateFrom.Text = startDate.ToString("yyyy-MM-dd");
            txtDateTo.Text = endDate.ToString("yyyy-MM-dd");

        }

        protected void SDSPassbookEntries_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            foreach (System.Data.Common.DbParameter param in e.Command.Parameters)
            {
                System.Diagnostics.Debug.WriteLine($"Parameter: {param.ParameterName} = {param.Value}");
            }
            System.Diagnostics.Debug.WriteLine("SQL Query: " + e.Command.CommandText);
        }
    }
}