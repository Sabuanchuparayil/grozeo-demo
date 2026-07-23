using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using RetalineProAgent.Core.Services;
//using RetalineProAgent.Core.BussinessModel.OnlineOrders;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.IO;
using System.Linq;
using System.Text;
using System.Text.RegularExpressions;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class Graphics : Base.BasePartnerPage
    {
        // Declare itemsBoundCount at the class level
        private int itemsBoundCount = 0;
        private string selectedApplicationValue;

        protected void Page_Load(object sender, EventArgs e)
        {
            // Reset the counter on each page load
            itemsBoundCount = 0;

            // Handle DataBound event after the Repeater is bound
            rptOwnGraphics_DataBound(sender, e);

            // Clear any previous error messages
            errorMessageLabel.Text = "";

            if (!IsPostBack)
            {
                // Initialize the DropDownList with default value
                selectapplication.SelectedIndex = -1;
                SelectApplicationChanged(sender, e); // Call the event handler

                // Attach the event handler to the SelectedIndexChanged event of selPostType
                selPostType.SelectedIndexChanged += SelPostType_SelectedIndexChanged;
            }
        }

        protected void rptOwnGraphics_DataBound(object sender, EventArgs e)
        {
            // Check if any items were bound
            if (itemsBoundCount == 0)
            {
                // If no items were bound, show the message
                noRecordsMessage.Visible = true;
                ltrGraphics.Visible = false;
                titleName.Visible = false;
            }
            else
            {
                // If items were bound, hide the message
                noRecordsMessage.Visible = false;
                ltrGraphics.Visible = true;
                titleName.Visible = true;
            }
        }

        protected void SelectApplicationChanged(object sender, EventArgs e)
        {
            string selectedValue = selectapplication.SelectedValue;
            selectedApplicationValue = selectedValue;

            if (!string.IsNullOrEmpty(selectedValue))
            {
                switch (selectedValue)
                {
                    case "3":
                        ltrGraphics.Text = "Facebook";
                        break;
                    case "4":
                        ltrGraphics.Text = "Instagram";
                        break;
                    case "5":
                        ltrGraphics.Text = "WhatsApp";
                        break;
                    default:
                        ltrGraphics.Text = "";
                        break;
                }

                if (int.TryParse(selectedValue, out int selectedApplication))
                {
                    int selectedIndex = PopulatePostTypeDropdown(selectedApplication);

                    // Trigger the SelectedIndexChanged event manually if the index is changed
                    if (selPostType.SelectedIndex != selectedIndex)
                    {
                        selPostType.SelectedIndex = selectedIndex;
                        SelPostType_SelectedIndexChanged(selPostType, EventArgs.Empty);
                    }
                }

                if (HasDataForSelectedApplication(selectedApplication))
                {
                    // Enable selPostType and disable selectposttheme
                    selPostType.Enabled = true;
                    selectposttheme.Enabled = false;

                    // Set default value for selectposttheme
                    selectposttheme.SelectedValue = "-1";
                }
                else
                {
                    // If no data, disable both selPostType and selectposttheme and set default values
                    SetDropdownDefaults();
                }
            }
            else
            {
                // Set a default value for other dropdowns when selectapplication is not selected
                SetDropdownDefaults();
                errorMessageLabel.Text = "Please choose a valid application.";
            }

            RefreshImages();
        }

        private bool HasDataForSelectedApplication(int applicationId)
        {
            // Use a DataTable to fetch data from the database
            List<KeyValuePair<string, object>> grpParams = new List<KeyValuePair<string, object>>();
            grpParams.Add(new KeyValuePair<string, object>("applicationId", applicationId));

            // Replace "app" with your actual table name
            DataTable postThemeQuery = DataServiceMySql.GetDataTable($"SELECT COUNT(*) FROM graphics_template_settings WHERE applicationId = @applicationId", Service.UserService.GetAPIConnectionString(), grpParams);

            // Check if there is at least one row of data
            return Convert.ToInt32(postThemeQuery.Rows[0][0]) > 0;
        }

        private void RefreshImages()
        {
            // Get the selected values
            string selectedApplication = selectapplication.SelectedValue;
            string selectedPostType = selPostType.SelectedValue;
            string selectedPostTheme = selectposttheme.SelectedValue;

            // Check if all three values are selected
            if (!string.IsNullOrEmpty(selectedApplication) &&
                !string.IsNullOrEmpty(selectedPostType) &&
                !string.IsNullOrEmpty(selectedPostTheme))
            {
                // Update the SqlDataSource parameters with the selected values
                SDSGraphicTemplates.SelectParameters["applicationId"].DefaultValue = selectedApplication;
                SDSGraphicTemplates.SelectParameters["postId"].DefaultValue = selectedPostType;
                SDSGraphicTemplates.SelectParameters["themeId"].DefaultValue = selectedPostTheme;

                // Rebind the Repeater to refresh the images based on the selected values
                rptOwnGraphics.DataBind();
            }
        }

        private void SetDropdownDefaults()
        {
            selPostType.Items.Clear();
            selectposttheme.Items.Clear();

            // Add default "Select" item to selPostType
            selPostType.Items.Add(new ListItem("Select Post Type", "-1"));
            selPostType.Enabled = false;

            // Add default "Select" item to selectposttheme
            selectposttheme.Items.Add(new ListItem("Select Post Theme", "-1"));
            selectposttheme.Enabled = false;
        }

        private int PopulatePostTypeDropdown(int applicationId)
        {
            // Clear existing items
            selPostType.Items.Clear();
            selPostType.Items.Add(new ListItem("Select Post Type", "-1"));

            // Use a DataTable to fetch data from the database
            List<KeyValuePair<string, object>> grpParams = new List<KeyValuePair<string, object>>();
            grpParams.Add(new KeyValuePair<string, object>("applicationId", applicationId));

            // Replace "app" with your actual table name
            DataTable postThemeQuery = DataServiceMySql.GetDataTable($"SELECT locationId,locationName,width,height FROM graphics_template_settings WHERE applicationId = @applicationId", Service.UserService.GetAPIConnectionString(), grpParams);

            // Hardcoded themes based on the selected application
            foreach (DataRow row in postThemeQuery.Rows)
            {
                selPostType.Items.Add(new ListItem(row["locationName"].ToString(), row["locationId"].ToString()));
            }

            // Enable selectposttheme if selPostType is selected and not the default value ("Select Post Type")
            //selectposttheme.Enabled = !string.IsNullOrEmpty(selPostType.SelectedValue) && selPostType.SelectedValue != "-1";
            if(!string.IsNullOrEmpty(selPostType.SelectedValue) && selPostType.SelectedValue != "-1")
            {
                selectposttheme.Enabled = true;
            }

            // If selPostType is not selected or is the default value, clear and disable selectposttheme
            if (string.IsNullOrEmpty(selPostType.SelectedValue) || selPostType.SelectedValue == "-1")
            {
                selectposttheme.Items.Clear();
                selectposttheme.Items.Add(new ListItem("Select Post Theme", "-1"));
                selectposttheme.Enabled = false;
            }
            else
            {
                // Populate Select Post Theme based on the selected application and locationId
                int selectedLocationId = Convert.ToInt32(selPostType.SelectedValue);
                selectposttheme.Enabled = true;
                PopulatePostThemeDropdown(applicationId);
            }

            // Return the selected index
            return selPostType.SelectedIndex;
        }

        protected void SelPostType_SelectedIndexChanged(object sender, EventArgs e)
        {
            if (!string.IsNullOrEmpty(selPostType.SelectedValue) && selPostType.SelectedValue != "-1")
            {
                // Enable selectposttheme
                int selectedApplicationId = Convert.ToInt32(selectapplication.SelectedValue);
                selectposttheme.Enabled = true;
                PopulatePostThemeDropdown(selectedApplicationId);
            }
            else
            {
                // Clear and disable selectposttheme if selPostType is not selected or is the default value
                selectposttheme.Items.Clear();
                selectposttheme.Items.Add(new ListItem("Select Post Theme", "-1"));
                selectposttheme.Enabled = false;
            }
        }

        private void PopulatePostThemeDropdown(int applicationId)
        {
            selectposttheme.Enabled = true;
            // Clear existing items
            selectposttheme.Items.Clear();
            selectposttheme.Items.Add(new ListItem("Select Post Theme", "-1"));

            // Hardcoded themes based on the selected application
            //if (applicationId == 1) // Web
            //{
            //    selectposttheme.Items.Add(new ListItem("Banner", "1"));
            //}
            //else if (applicationId == 2) // App
            //{
            //    selectposttheme.Items.Add(new ListItem("Banner", "1"));
            //}
            if (applicationId == 3) // Facebook
            {
                //selectposttheme.Items.Add(new ListItem("Banner", "1"));
                selectposttheme.Items.Add(new ListItem("Invitation", "2"));
                selectposttheme.Items.Add(new ListItem("Greetings", "3"));
                selectposttheme.Items.Add(new ListItem("Announcement", "4"));
                selectposttheme.Items.Add(new ListItem("Offers", "5"));
            }
            else if (applicationId == 4) // Instagram
            {
                //selectposttheme.Items.Add(new ListItem("Banner", "1"));
                selectposttheme.Items.Add(new ListItem("Invitation", "2"));
                selectposttheme.Items.Add(new ListItem("Greetings", "3"));
                selectposttheme.Items.Add(new ListItem("Announcement", "4"));
                selectposttheme.Items.Add(new ListItem("Offers", "5"));
            }
            else if (applicationId == 5) // WhatsApp
            {
                //selectposttheme.Items.Add(new ListItem("Banner", "1"));
                selectposttheme.Items.Add(new ListItem("Announcement", "4"));
                selectposttheme.Items.Add(new ListItem("Offers", "5"));
            }
            else
            {
                selectposttheme.Items.Add(new ListItem("Select Post Theme", "-1"));
            }
        }

        protected void rptOwnGraphics_ItemDataBound(object sender, RepeaterItemEventArgs e)
        {
            if (e.Item.ItemType == ListItemType.Item || e.Item.ItemType == ListItemType.AlternatingItem)
            {
                // Increment a counter for each item bound
                itemsBoundCount++;

                // Check if the designUrl is present in the current data item
                if (!string.IsNullOrEmpty(DataBinder.Eval(e.Item.DataItem, "designUrl")?.ToString()))
                {
                    // If designUrl is present, show the "Graphics for" literal
                    ltrGraphics.Visible = true;
                    titleName.Visible = true;
                    // Hide the "No record available" message
                    noRecordsMessage.Visible = false;
                }
                else
                {
                    // If designUrl is not present, hide the "Graphics for" literal
                    ltrGraphics.Visible = false;
                    titleName.Visible = false;
                    // Show the "No record available" message
                    noRecordsMessage.Visible = true;
                }
            }
        }

        protected void lbtnGo_Click(object sender, EventArgs e)
        {
            rptOwnGraphics.DataBind();
            rptOwnGraphics.Visible = true;
        }
    }
}