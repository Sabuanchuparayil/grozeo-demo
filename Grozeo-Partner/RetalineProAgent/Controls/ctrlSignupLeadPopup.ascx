<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="ctrlSignupLeadPopup.ascx.cs" Inherits="RetalineProAgent.Controls.ctrlSignupLeadPopup" %>
        <script src="https://maps.googleapis.com/maps/api/js?key=<%= ConfigurationManager.AppSettings.Get("googleAPIKey") %>&libraries=places&v=weekly"></script>

        <div class="modl_cont text-center d-none-">
          <h3 class="mb-0" style="font-size: 1.5rem;">Grozeo is available in selected cities only!</h3>
          <p>We have released Grozeo for selected locations only. Please provide your details to help us contact you when we start rolling at your city</p>

            <div class="input-group mb-2">
                <asp:TextBox ID="txtLeadName" CssClass="form-control" runat="server" placeholder="Name"></asp:TextBox>
              <%--<input class="form-control" type="text" placeholder="Name">--%>
            </div>
                <asp:RequiredFieldValidator runat="server" ControlToValidate="txtLeadName" Display="Dynamic" ErrorMessage="Name is required" ForeColor="Red" ValidationGroup="SignupLead"></asp:RequiredFieldValidator>
            <div class="input-group mb-2">
              <input class="form-control" type="text" id="txtLeadMobile" runat="server" placeholder="Mobile No" maxlength="10" autocomplete="off" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
            </div>
                <asp:RequiredFieldValidator runat="server" ControlToValidate="txtLeadMobile" Display="Dynamic" ErrorMessage="Mobile is required" ForeColor="Red" ValidationGroup="SignupLead"></asp:RequiredFieldValidator>
            <div class="input-group mb-2">
                <asp:TextBox ID="txtLocation" runat="server" CssClass="form-control" placeholder="Location"></asp:TextBox>
              <%--<input class="form-control" type="text" placeholder="Location">--%>
            </div>
                <asp:RequiredFieldValidator runat="server" ControlToValidate="txtLocation" Display="Dynamic" ErrorMessage="Location is required" ForeColor="Red" ValidationGroup="SignupLead"></asp:RequiredFieldValidator>
            <div class="input-group mb-2">
                <asp:DropDownList ID="selBCategory" runat="server" CssClass="form-control form-control-sm" DataSourceID="SDSBusinessTypes" DataTextField="business_type_name" DataValueField="business_type_id" AppendDataBoundItems="true"><asp:ListItem Text="Select business type" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:SqlDataSource ID="SDSBusinessTypes" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT business_type_id,business_type_name,IF((STATUS=1),'Active','Inactive') AS STATUS FROM finascop_business_type bt WHERE EXISTS(SELECT * FROM retaline_business_category bc WHERE Store_group_Id=0 AND FIND_IN_SET(bt.business_type_id, bc.rbc_business_type) > 0)"
                ProviderName="MySql.Data.MySqlClient"></asp:SqlDataSource>
            </div>
                <asp:RequiredFieldValidator runat="server" ControlToValidate="selBCategory" Display="Dynamic" ErrorMessage="Business type is required" ForeColor="Red" ValidationGroup="SignupLead"></asp:RequiredFieldValidator>
            <div class="formtbtn mt-3">
                <asp:LinkButton runat="server" Text="Submit" CssClass="btn btn-primary btn-drk-green px-3" ValidationGroup="SignupLead" OnClick="btnSignupLeadSubmit_Click" />
              <asp:Button ID="btnSignupLeadSubmit" runat="server" Text="Submit" CssClass="btn btn-primary btn-drk-green px-3" ValidationGroup="SignupLead" Visible="false" OnClick="btnSignupLeadSubmit_Click" />
              <%--<input class="btn btn-primary btn-drk-green px-3" value="submit" type="submit">--%>
            </div>
            <asp:HiddenField ID="hidLeadLat" runat="server" /><asp:HiddenField ID="hidLeadLong" runat="server" />
        </div><!--modl_cont 1st step-->

<script type="text/javascript">

    var autocomplete = new google.maps.places.Autocomplete(
        document.getElementById('<%= txtLocation.ClientID %>'),
            { types: [], fields: ["address_components", "geometry"], componentRestrictions: { country: "<%= ConfigurationManager.AppSettings.Get("CountryCode")??"IN" %>" } }
        );


    var fillInAddress = function () {
        var place = autocomplete.getPlace();
        fillInAddressProperties(place);
    };
    autocomplete.setFields(["address_component, geometry"]);
    autocomplete.addListener("place_changed", fillInAddress);

    var fillInAddressProperties = function (place) {
        $('#<%= hidLeadLat.ClientID%>').val(place.geometry.location.lat());
        $('#<%= hidLeadLong.ClientID%>').val(place.geometry.location.lng());
    }

</script>