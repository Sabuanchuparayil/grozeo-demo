<%@ Page Language="C#" AutoEventWireup="true" Title="Delivery Zone" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="Deliveryzone.aspx.cs" Inherits="RetalineProAgent.Tenant.Deliveryzone" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <a href="/Navigations/Delivery"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle">
            <asp:Literal ID="ltrTitle" runat="server" Text="Delivery Zone"></asp:Literal>
        </h6>
        <p class="mb-0">Create & Manage your delivery zones</p>
    </div>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="card">
        <div class="card-header shadow_top">
            <div class="row row-sm">


                <div class="col-12 d-flex flex-wrap flex-lg-nowrap px-0">

                        <div class="d-flex flex-wrap col-lg-4 input-group pl-0 pr-0 pr-lg-3">
                            <label for="txtBranch" runat="server" class="tx-dark mb-1 w-100">Store:</label>
                            <input name="branchname" type="text" id="branchname" value="" disabled="" class="form-control" placeholder="Branch" runat="server">  
                            <%--<span >
                              <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal>

                          </span>--%>
                            <asp:PlaceHolder ID="plcSelectBranchModel" runat="server">
                                <asp:DropDownList ID="selBranches" OnDataBound="selBranches_DataBound" AutoPostBack="true" DataSourceID="SDSBranches" DataTextField="br_Name"  ValidationGroup="StockUpdate" DataValueField="br_ID" CssClass="form-control select2" runat="server"><asp:ListItem Text="Select Store" Value=""></asp:ListItem></asp:DropDownList>
                                <asp:RequiredFieldValidator runat="server" SetFocusOnError="true" Display="Dynamic" ControlToValidate="selBranches" ValidationGroup="AddZone" ForeColor="Red" CssClass="error_msg_wrap" ErrorMessage="Select branch"></asp:RequiredFieldValidator>
                            </asp:PlaceHolder>
                            <asp:SqlDataSource ID="SDSBranches" runat="server" OnSelecting="SDSBranches_Selecting" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                SelectCommand="SELECT br_ID, br_Name, br_City, br_Address, br_directDelivery, br_courierDelivery FROM finascop_branch WHERE br_storeGroup = @storegroupid and (@branchid <= 0 or br_ID=@branchid)"
                                ProviderName="MySql.Data.MySqlClient">
                                <SelectParameters>
                                    <asp:Parameter Name="storegroupid" DefaultValue="-1" />
                                    <asp:Parameter Name="branchid" DefaultValue="-1" />
                                </SelectParameters>
                            </asp:SqlDataSource>
                        </div>



                <div class="col-12 d-flex flex-wrap mb-2 input-group">
                     <label for="txtBranch" runat="server" class="tx-dark mb-1 w-100">Zone Type:</label>
                    <div class="form-group mb-3 mb-md-0 d-flex mr-4">
                        <label class="rdiobox mr-3">
                            <asp:RadioButton ID="rbtnCountry" Checked="true" runat="server" AutoPostBack="true" GroupName="LocationGroup" />
                          <span class="p-0">Country</span>
                        </label>
                    </div>
                    <div class="form-group mb-3 mb-md-0 d-flex mr-4">
                        <label class="rdiobox mr-3">
                        <asp:RadioButton ID="rbtnState" runat="server" AutoPostBack="true" GroupName="LocationGroup" />
                          <span class="p-0">State / Province</span>
                        </label>
                    </div>
                    <div class="form-group mb-3 mb-md-0 d-flex">
                        <label class="rdiobox mr-3">
                        <asp:RadioButton ID="rbtnDistrict" runat="server" AutoPostBack="true" GroupName="LocationGroup" />
                          <span class="p-0">District / City</span>
                        </label>
                    </div>
                </div>

                    </div>
            </div>
            <div class="row row-sm mt-2">

                <div class="col-md-4 input-group mg-b-10 mg-md-b-0 px-2">
                    <label for="<%= selCountry.ClientID %>" class="tx-dark mb-1 w-100">Country<asp:RequiredFieldValidator SetFocusOnError="true" runat="server" ControlToValidate="selCountry" ValidationGroup="AddZone" ErrorMessage="Select Country" Text="*" ForeColor="Red" Display="Dynamic"></asp:RequiredFieldValidator> </label>
                    <asp:DropDownList ID="selCountry" AutoPostBack="true" CssClass="form-control select2" DataSourceID="SDSCountry" 
                        DataTextField="country_name" DataValueField="country_id" runat="server" OnDataBound="selCountry_DataBound"></asp:DropDownList>

                    <asp:SqlDataSource ID="SDSCountry" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                        SelectCommand="SELECT country_id, country_name FROM finascop_country where @isCountryZone=0 or country_id not in(SELECT countryId FROM `delivery_zone` WHERE storegroupId=0 AND stateId <= 0 AND districtId <= 0)"
                        ProviderName="MySql.Data.MySqlClient">
                        <SelectParameters><asp:ControlParameter ControlID="rbtnCountry" Name="isCountryZone" PropertyName="Checked" />
                            <asp:ControlParameter ControlID="rbtnState" Name="isStateZone" PropertyName="Checked" />
                            <asp:ControlParameter ControlID="rbtnDistrict" Name="isDistrictZone" PropertyName="Checked" />
                        </SelectParameters>
                    </asp:SqlDataSource>
                </div>

                <div class="col-md-4 input-group mg-b-10 mg-md-b-0 px-2">
                    <label for="<%= selStateProvince.ClientID %>" class="tx-dark mb-1 w-100">State/Province<asp:RequiredFieldValidator ID="rvState" SetFocusOnError="true" runat="server" ControlToValidate="selStateProvince" ValidationGroup="AddZone" ErrorMessage="Select State" Text="*" ForeColor="Red" Display="Dynamic"></asp:RequiredFieldValidator> </label>
                    <asp:DropDownList ID="selStateProvince" AutoPostBack="true" CssClass="form-control select2" DataSourceID="SDSState" 
                        DataTextField="st_name" DataValueField="st_ID" runat="server" OnDataBound="selStateProvince_DataBound"></asp:DropDownList>
                    <asp:SqlDataSource ID="SDSState" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                        SelectCommand="SELECT st_ID, st_name FROM finascop_state WHERE cnt_ID=@country_ID and ( @isStateZone=0 or st_ID not in(SELECT stateId FROM `delivery_zone` WHERE storegroupId=0 AND districtId <= 0))"
                        ProviderName="MySql.Data.MySqlClient">
                        <SelectParameters>
                            <asp:ControlParameter ControlID="selCountry" Name="country_ID" PropertyName="SelectedValue" Type="Int32" />
                            <asp:ControlParameter ControlID="rbtnState" Name="isStateZone" PropertyName="Checked" />
                            <asp:ControlParameter ControlID="rbtnDistrict" Name="isDistrictZone" PropertyName="Checked" />
                        </SelectParameters>
                    </asp:SqlDataSource>
                </div>


                <div class="col-md-4 d-flex flex-wrap">
                    
                    <div class="col-md-10 input-group mg-b-10 mg-md-b-0">
                        <label for="<%= selDistrictCity.ClientID %>" class="tx-dark mb-1 w-100">District/City<asp:RequiredFieldValidator ID="rvDistrict" SetFocusOnError="true" runat="server" ControlToValidate="selDistrictCity" ValidationGroup="AddZone" ErrorMessage="Select Country" Text="*" ForeColor="Red" Display="Dynamic"></asp:RequiredFieldValidator> </label>
                        <asp:DropDownList ID="selDistrictCity" CssClass="form-control select2" DataSourceID="SDSDistrict" ValidationGroup="AddZone" DataTextField="dst_Name" 
                            DataValueField="dst_Id" runat="server" OnDataBound="selDistrictCity_DataBound"></asp:DropDownList>
                        <asp:SqlDataSource ID="SDSDistrict" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                            SelectCommand="SELECT dst_Id, dst_Name FROM finascop_district WHERE st_Id=@stID and (@isDistrictZone=0 or dst_Id not in(SELECT districtId FROM `delivery_zone` WHERE storegroupId=0))"
                            ProviderName="MySql.Data.MySqlClient">
                            <SelectParameters>
                                <asp:ControlParameter ControlID="selCountry" Name="country_ID" PropertyName="SelectedValue" Type="Int32" />
                                <asp:ControlParameter ControlID="selStateProvince" Name="stID" PropertyName="SelectedValue" Type="Int32" />
                                <asp:ControlParameter ControlID="rbtnDistrict" Name="isDistrictZone" PropertyName="Checked" />
                            </SelectParameters>
                        </asp:SqlDataSource>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <asp:Button runat="server" ID="btnAdd" CssClass="btn btn-primary" OnClick="btnAdd_Click" Text="Add" ValidationGroup="AddZone" />
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="card- body">
        <div class="table-responsive">
            <asp:GridView ID="gvDeliveryZone" runat="server" GridLines="None" CssClass="table table-bordered mg-b-0 gridview_table"
                AutoGenerateColumns="false" DataSourceID="SDSDeliveryZone" DataKeyNames="ID" ShowFooter="false" PagerSettings-Visible="true" 
                AllowPaging="true" AllowSorting="true" PageSize="10">
                
                <Columns>
                    <asp:BoundField HeaderText="Name" DataField="Name" ReadOnly="true" />
                    <asp:TemplateField HeaderText="Country">
                        <ItemTemplate>
                            <%# Eval("country_name") %>
                        </ItemTemplate>
                        
                    </asp:TemplateField>

                    <asp:TemplateField HeaderText="State/Province">
                        <ItemTemplate>
                            <%# Eval("st_Name") %>
                        </ItemTemplate>
                    </asp:TemplateField>

                    <asp:TemplateField HeaderText="District/City">
                        <ItemTemplate>
                            <%# Eval("dst_Name") %>
                        </ItemTemplate>
                    </asp:TemplateField>

                    <asp:TemplateField HeaderText="Actions" ItemStyle-Width="100">
                        <ItemTemplate>
<asp:LinkButton runat="server" CommandName="Delete" Visible='<%# Convert.ToInt32(Eval("storegroupId")) > 0 %>' CausesValidation="false" 
    OnClientClick="return confirm('Are you sure you want to delete this zone?')"><i class="fa-solid fa-trash ml-2 tx-gray-600" title="Save"></i></asp:LinkButton>
                        </ItemTemplate>
                    </asp:TemplateField>
                </Columns>

                <EmptyDataTemplate>
                    <div class="text-center">
                        <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                        <h6 class="mb-3">No record available</h6>
                    </div>
                </EmptyDataTemplate>

                <PagerStyle HorizontalAlign="Center" CssClass="cssPager" />
                <PagerSettings Mode="NumericFirstLast"  PageButtonCount="5" />
                
            </asp:GridView>
        </div>
    </div>

    <asp:SqlDataSource runat="server" ID="SDSDeliveryZone" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" ProviderName="MySql.Data.MySqlClient"
        SelectCommand="SELECT d.ID, d.Name, d.countryId, d.stateId, d.districtId, fc.country_name,fs.st_Name,fd.dst_Name,fb.br_name,d.storegroupId FROM delivery_zone d
                    LEFT JOIN finascop_country fc ON fc.country_id = d.countryId
                    LEFT JOIN finascop_state fs ON fs.st_ID = d.stateId
                    LEFT JOIN finascop_district fd ON fd.dst_Id = d.districtId
                    LEFT JOIN finascop_branch fb ON fb.br_ID=d.branchId
                    WHERE d.status = 1 and (d.storegroupId=0 OR (d.branchId= @branchId and d.storegroupId=@storegroupid)) order by storegroupId, fc.country_name, fs.st_Name, fd.dst_Name" OnSelecting="SDSDeliveryZone_Selecting" OnUpdating="SDSDeliveryZone_Updating"
         DeleteCommand="UPDATE delivery_zone SET status=0 WHERE ID=@ID">
        <SelectParameters>
            <asp:Parameter Name="storegroupid" />
            <asp:ControlParameter ControlID="selBranches" Name="branchId" PropertyName="Text" ConvertEmptyStringToNull="false" DefaultValue="0" />
        </SelectParameters>
    </asp:SqlDataSource>

</asp:Content>
