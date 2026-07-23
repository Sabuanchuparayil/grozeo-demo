<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="ctrlPhoneNumber.ascx.cs" Inherits="RetalineProAgent.Controls.StoreSettings.ctrlPhoneNumber" %>

 <!-- Include the necessary styles and scripts -->
<link rel="stylesheet" href="/Content/css/custom/intlTelInput.css">
<script src="/Content/lib/jquery/js/jquery.js"></script>
<script src="/Content/js/custom/intlTelInput-jquery.min.js"></script>

<div class="w-100">
    <asp:TextBox ID="txtphone" runat="server" CssClass="form-control restrictmobile border-0" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" placeholder="Enter Phone Number" autocomplete="off" />
</div>

<script>
    $(document).ready(function () {
        const input = $("#<%= txtphone.ClientID %>"); // Use ClientID for ASP.NET control

        var allowDropdown = '<%= ConfigurationManager.AppSettings["CountryCode"] %>' === "IN" ? false : true;
        input.intlTelInput({
            autoHideDialCode: true,
            autoPlaceholder: "aggressive",
            dropdownContainer: document.body,
            formatOnDisplay: true,
            hiddenInput: "full_number",
            initialCountry: "<%= ConfigurationManager.AppSettings.Get("CountryCode") %>",  // Pre-select India
            nationalMode: true,
            placeholderNumberType: "MOBILE",
            preferredCountries: ["<%= ConfigurationManager.AppSettings.Get("CountryCode") %>"],
            separateDialCode: true,
            showSelectedDialCode: true,
            showFlags: true,
            allowDropdown: allowDropdown,
        });
        document.querySelectorAll(".restrictmobile").forEach(element => {
            element.addEventListener("input", function (event) {
                const inputValue = event.target.value;
                // Check if the first character is "0"
                if (inputValue.charAt(0) === "0") {
                    // Trim the leading "0" from the input value
                    event.target.value = inputValue.substring(1);
                }
            });
        });
        // Add validation logic
        input.on('blur', function () {
            const iti = input.intlTelInput("getInstance");
            if (iti.isValidNumber()) {
                console.log("Valid number:", iti.getNumber());
            } else {
                console.log("Invalid number");
            }
        });
    });
</script>
