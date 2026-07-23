using MySql.Data.MySqlClient;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Text.RegularExpressions;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Finance
{
    public partial class ValueHead : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            ltrScript.Text = "";

            if (!IsPostBack)
            {
                LoadSelSourceFields();
                LoadSourceFields();
                // Create a new DataView to access the SqlDataSource data
                DataView dv = (DataView)SDSFields.Select(DataSourceSelectArguments.Empty);

                // Iterate through the results and add each item to the dropdown list
                foreach (DataRowView row in dv)
                {
                    ddlField.Items.Add(new ListItem(row["name"].ToString(), row["column_name"].ToString()));
                }

            }
        }

        //selSourceField

        private void LoadSelSourceFields()
        {
            try
            {
                // Define SQL query to fetch data
                string sql = "SELECT name FROM finance_ValueHeadSourceField WHERE mode = 0 ORDER BY `name` DESC";

                // Fetch DataTable using your custom method
                var tblItems = DataServiceMySql.GetDataTable(sql, UserService.GetAPIConnectionString());

                // Bind data to DropDownList
                selSourceField.Items.Clear();
                selSourceField.Items.Add(new ListItem("Select Field")); // Optional default item

                foreach (DataRow row in tblItems.Rows)
                {
                    string fieldName = row["name"].ToString();

                    selSourceField.Items.Add(new ListItem(fieldName));
                }
            }
            catch (Exception ex)
            {
                // Handle any errors (e.g., log them or display a message to the user)
                Console.WriteLine("Error: " + ex.Message);
            }
        }

        private void LoadSourceFields()
        {
            try
            {
                // Define SQL query to fetch data
                string sql = "SELECT name FROM finance_ValueHeadSourceField WHERE mode = 0 OR mode = 1 ORDER BY `name` DESC";

                // Fetch DataTable using your custom method
                var tblItems = DataServiceMySql.GetDataTable(sql, UserService.GetAPIConnectionString());

                // Bind data to DropDownList
                ddlField.Items.Clear();
                ddlField.Items.Add(new ListItem("Select Field")); // Optional default item

                foreach (DataRow row in tblItems.Rows)
                {
                    string fieldName = row["name"].ToString();

                    ddlField.Items.Add(new ListItem(fieldName));
                }
            }
            catch (Exception ex)
            {
                // Handle any errors (e.g., log them or display a message to the user)
                Console.WriteLine("Error: " + ex.Message);
            }
        }

        protected void btnSaveFormula_Click(object sender, EventArgs e)
        {
            int rowId = 0;
            if (!String.IsNullOrEmpty(hidSelRowId.Value))
                try { rowId = Convert.ToInt32(hidSelRowId.Value); } catch { rowId = 0; }

            if (!IsValidCalculation())
                return;
            string strCalculation = hidSelectedFormula.Value;

            if (rbtnTernaryOperator.Checked)
            {
                strCalculation = tfdTernaryOperator.Text;
            }
            else if (rbtnTable.Checked)
                strCalculation = selSourceField.Text;
            int positionBefore = 0; try { positionBefore = Convert.ToInt32(selPositionBefore.SelectedValue); } catch { positionBefore = 0; }
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>() {
                new KeyValuePair<string, object>("p_rowid", rowId),
                new KeyValuePair<string, object>("p_name",txtValueHead.Text),
                new KeyValuePair<string, object>("p_calculation", strCalculation),
                new KeyValuePair<string, object>("p_description",txtDescription.Text),
                new KeyValuePair<string, object>("p_column_name",Regex.Replace(txtValueHead.Text.Replace(" ", "_"), "[^a-zA-Z0-9_]+", "", RegexOptions.Compiled)),
                new KeyValuePair<string, object>("p_event",selPopupEvent.SelectedItem.Text),
                new KeyValuePair<string, object>("p_displayorder_beforeid",positionBefore),
                new KeyValuePair<string, object>("p_eventId", selPopupEvent.SelectedValue),
                new KeyValuePair<string, object>("p_sourceType", rbtnCalculation.Checked ? 1 : rbtnTernaryOperator.Checked ? 2: 0),
                new KeyValuePair<string, object>("p_costcentre_enabled", chkHasCostCenter.Checked ? 1 : 0),
                new KeyValuePair<string, object>("p_Type", selpouptype.SelectedItem.Text)
            };

            DataServiceMySql.ExecuteSql("finance_add_edit_valuehead", UserService.GetAPIConnectionString(), prms, true);
            SDSValueHeads.Select(DataSourceSelectArguments.Empty);
            gvValueHeads.DataBind();
            Common.ShowCustomAlert(this.Page, "Success", "Value Head Saved successfully", true, "/Finance/ValueHead");
        }

        private bool IsValidCalculation()
        {
            // Skip if it is not calcuation head. Default behavior is table column which is out side the scope of the validate expression.
            if (!rbtnCalculation.Checked) return true;

            // Proceed validation only if the expression is not empty.
            if (!String.IsNullOrEmpty(hidSelectedFormula.Value))
            {
                string[] strFields = hidSelectedFormula.Value.Split(',');
                bool isFirstInSquareBrackets = Regex.IsMatch(strFields.First(), @"^\[.*\]$");
                if (!isFirstInSquareBrackets)
                {
                    Common.ShowCustomAlert(this.Page, "Failure", "Invalid calculation expression. The calculation express should start with a field or constant. It cannot start with a calculation symbol", false);
                    ltrScript.Text = "loadCalculation(''); $('#formulaModal').modal({ backdrop: 'static', keyboard: false }, 'show');";
                    return false;
                }
                bool isLastInSquareBrackets = Regex.IsMatch(strFields.Last(), @"^\[.*\]$");
                if (!isLastInSquareBrackets)
                {
                    Common.ShowCustomAlert(this.Page, "Failure", "Invalid calculation expression. The calculation express should end with a field or constant. It cannot end with a calculation symbol", false);
                    ltrScript.Text = "loadCalculation(''); $('#formulaModal').modal({ backdrop: 'static', keyboard: false }, 'show');";
                    return false;
                }

                int positionId = 0; try { positionId = Convert.ToInt32(selPositionBefore.Text); } catch { positionId = 0; }
                int eventId = 0; try { eventId = Convert.ToInt32(selPopupEvent.Text); } catch { eventId = 0; }

                string pattern = @"\[(?!\d+$)([A-Za-z0-9_]+)\]";
                List<string> noneValidHeads = new List<string>();
                // Use Regex to find all fields.
                MatchCollection matches = Regex.Matches(hidSelectedFormula.Value, pattern);
                if (matches.Count > 0)
                {
                    DataView view = (DataView)SDSFields.Select(DataSourceSelectArguments.Empty);
                    DataTable dtHeads = view.ToTable();

                    foreach (Match match in matches)
                    {
                        string strHead = match.Groups[1].Value;
                        // Check if the value head used in the calculation express is positioned before the selected/new value head
                        // and it's value will be filled before using the field in the calculation.
                        // Otherwise, the calculation express can result null due to the missing data which will be populated later
                        if (dtHeads.AsEnumerable().Any(h => h["column_name"].ToString() == strHead && Convert.ToInt32(h["eventId"]) <= eventId &&
                                (positionId == 0 || Convert.ToInt32(h["displayorder"]) > positionId))
                            )
                            noneValidHeads.Add(match.Groups[1].Value); // Get the text inside the brackets
                    }

                    if (noneValidHeads.Count > 0)
                    {
                        Common.ShowCustomAlert(this.Page, "Validation Failure", "The following field/s used in the formula are not prepopulated with value for this head", false);
                        ltrScript.Text = "loadCalculation(''); $('#formulaModal').modal({ backdrop: 'static', keyboard: false }, 'show');";
                        return false;
                    }
                }
            }

            return true;
        }

    }
}