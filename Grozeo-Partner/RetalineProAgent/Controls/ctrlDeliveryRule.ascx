<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="ctrlDeliveryRule.ascx.cs" EnableTheming="true" Inherits="RetalineProAgent.Controls.ctrlDeliveryRule" %>

                  <div class="type_of_delivery d-flex flex-wrap p-3 mb-3 card-body delivery_type_<%= DeliveryRuleTypeID %>">

                    <%--<div class="deliveryTitle d-flex w-100">
                      <label class="ckbox w-auto mr-2">
                        <input id="chk_delivery" runat="server" class="chk check_delivery_type" type="checkbox">
                          <asp:Label ID="lblDeliveryRule" runat="server" CssClass="tx-uppercase tx-bold"></asp:Label>                          
                      </label>
                        <asp:Label ID="lblDeliveryRuleWeight" runat="server"></asp:Label>
                      
                    </div>--%>
                       <div class="deliveryTitle d-flex w-100">
                      <div class="type_head mr-3">
                          <asp:Label ID="lblDeliveryRule" runat="server" CssClass="tx-uppercase tx-bold"></asp:Label>                          
                        <asp:Label ID="lblDeliveryRuleWeight" runat="server"></asp:Label>
                      </div>
                      <label class="ckbox w-auto mr-2">
                        <input id="chk_delivery" runat="server" class="chk check_delivery_type" type="checkbox">
                        <span id="cpMainContent_cpMainContent_ctrlDeliveryRuleHL_lblDeliveryRule" class="tx-medium">Store will manage the Rates &amp; Delivery</span>
                      </label>   
                    </div>                        
                    <div class="type_delivery_dteails p-2 deliveryDteails w-100  <%= (chk_delivery.Checked ? "" : "hide") %>">
                      <div class="d-flex flex-wrap py-1 type_of_charge DeliveryCharge  <%= (chk_delivery.Checked ? "" : "disable") %> ">

                          <asp:PlaceHolder ID="plcCourierDeliveryControls" runat="server">

                            <div class="charge_type_switch d-flex flex-wrap flex-md-nowrap mt-2 mt-lg-0 w-100">
                              <label class="rdiobox mr-3">
                                <input id="rb_Fixed" runat="server" name="delivery_type" type="radio" value="Fixed" class="chargetypeswitch" >
                                <span class="p-0">Fixed Charge</span>
                              </label>
  
                              <label class="rdiobox mr-3">
                                <input id="rb_Dynamic" runat="server" name="delivery_type" type="radio" value="Dynamic" class="chargetypeswitch" >
                                <span class="p-0">Distance Based Charge</span>
                              </label>

                              <label class="rdiobox mr-3">
                                <input id="rb_Weight" runat="server" name="delivery_type" type="radio" value="Weight" class="chargetypeswitch" >
                                <span class="p-0">Weight Slab</span>
                              </label>

                              <label class="rdiobox">
                                <input id="rb_ZoneWeight" runat="server" name="delivery_type" type="radio" value="ZoneWeight" class="chargetypeswitch" >
                                <span class="p-0">Zone Weight Slab</span>
                              </label>

                            </div><!--charge_type_switch-->

                        </asp:PlaceHolder>

                            <div class="d-flex flex-wrap w-100 mt-1 chargedtails">                          

                              <div class="d-flex flex-wrap py-1 fixedharges w-100 <%= (!plcCourierDeliveryControls.Visible || rb_Fixed.Checked ? "" : "hide") %>">

                              <div class="d-flex align-items-center w-auto mr-0 mr-sm-3 mb-2 mb-sm-0">
                                Rate per KM<asp:CustomValidator ID="CustomValidator5" runat="server" ControlToValidate="txtRatePerKM" ValidationGroup="CreateRule" SetFocusOnError="true"
                ErrorMessage="Required input" ClientValidationFunction="validateInput" ValidateEmptyText="true" ForeColor="Red" Text="*"></asp:CustomValidator> 
                                <%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %><asp:TextBox ID="txtRatePerKM" ValidationGroup="CreateRule" runat="server" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox> 
                                   
                              </div>

                              <div class="d-flex align-items-center w-auto mr-0 mr-sm-3 mb-2 mb-sm-0">
                                Min Amount<asp:CustomValidator ID="CustomValidator3" runat="server" ControlToValidate="txtMinAmount" ValidationGroup="CreateRule" SetFocusOnError="true"
                ErrorMessage="Required input" ClientValidationFunction="validateInput" ValidateEmptyText="true" ForeColor="Red" Text="*"></asp:CustomValidator> 
                                <%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %><asp:TextBox ID="txtMinAmount" ValidationGroup="CreateRule" runat="server" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox> 
                                   
                              </div>

                              <div class="d-flex align-items-center w-auto mr-0 mr-sm-3 mb-2 mb-sm-0">
                                Max Amount<asp:CustomValidator ID="CustomValidator4" runat="server" ControlToValidate="txtMaxAmount" ValidationGroup="CreateRule" SetFocusOnError="true"
                ErrorMessage="Required input" ClientValidationFunction="validateInput" ValidateEmptyText="true" ForeColor="Red" Text="*"></asp:CustomValidator> 
                                <%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %><asp:TextBox ID="txtMaxAmount" ValidationGroup="CreateRule" runat="server" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox> 
                                   
                              <div class="d-flex align-items-center w-auto">
                                Free Above
                                <%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %><asp:TextBox ID="txtFreeAbove" ValidationGroup="CreateRule" runat="server" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox> 
                                   
                              </div>


                              </div>
                                 
                              </div><!--col-12-->
                                <asp:HiddenField ID="hidDynamic" runat="server" />
                              <div class="d-flex flex-wrap py-1 Dynamiccharges  <%= (rb_Dynamic.Checked ? "" : "hide") %>">
                                <div class="slablist_wrap" hidid="<%= hidDynamic.ClientID %>">
                              
                                  <div class="slablist p-1 p-sm-2 border mb-1 slablist-row">
                                    <div class="d-flex align-items-baseline align-items-sm-center flex-wrap flex-sm-nowrap">
                                      <div class="d-flex align-items-center mr-2 mr-sm-3 mb-0 mb-sm-0">
                                        <select class="form-control wd-70 mr-1 dynamic-type" tabindex="-1">
                                          <option value="Upto">Up to</option>
                                          <option value="Above">Above</option>
                                        </select>
                                        <input class="form-control wd-50 mx-1 ht-20 py-0 text-center dynamic-km dynamic-input" maxlength="5" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"> Km
                                      </div>
                                      <div class="d-flex align-items-center">
                                        <input class="form-control wd-50 mx-1 ht-20 py-0 text-center dynamic-val dynamic-input" maxlength="5" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"> <%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %>/ Km
                                      </div>
                                      <div class="addcharge ml-2 ml-sm-3">
                                        <a href="javascript:void(0)" class="bg-transparent btn p-1 addcharge_btn d-flex align-items-center justify-content-center dynamic-add"><i class="fa-regular fa-plus tx-16"></i></a>
                                      </div>
                                    </div>
                                  </div><!--slablist-->
                              

                                </div><!--slablist_wrap-->

                                <div class=" d-flex flex-wrap w-100 mt-2">
                                  <div class="d-flex align-items-center w-auto mr-0 mr-sm-3 mb-2 mb-sm-0">
                                    Min Charge<asp:CustomValidator ID="CustomValidator24" runat="server" ControlToValidate="txtDynamicMinVal" ValidationGroup="CreateRule" SetFocusOnError="true"
                    ErrorMessage="Required input" ClientValidationFunction="validateInput" ValidateEmptyText="true" ForeColor="Red" Text="*"></asp:CustomValidator> 
                                    <%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %><asp:TextBox ID="txtDynamicMinVal" ValidationGroup="CreateRule" runat="server" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox> 
                                   
                                  </div>
                                  <div class="d-flex align-items-center w-auto">
                                    Max Charge<asp:CustomValidator ID="CustomValidator25" runat="server" ControlToValidate="txtDynamicMaxVal" ValidationGroup="CreateRule" SetFocusOnError="true"
                    ErrorMessage="Required input" ClientValidationFunction="validateInput" ValidateEmptyText="true" ForeColor="Red" Text="*"></asp:CustomValidator> 
                                    <%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %><asp:TextBox ID="txtDynamicMaxVal" ValidationGroup="CreateRule" runat="server" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox> 
                                   
                                  </div>
                                </div><!--col-12-->
                              </div><!--col-12-->

                                <asp:HiddenField ID="hidWeight" runat="server" />
                              <div class="d-flex flex-wrap py-1 Weightcharges  <%= (rb_Weight.Checked ? "" : "hide") %>">

                                <div class="slablist_wrap" hidid="<%= hidWeight.ClientID %>">
                              
                                  <div class="slablist p-1 p-sm-2 border mb-1 slablist-row" slabtype="2">
                                    <div class="d-flex align-items-baseline align-items-sm-center flex-wrap flex-sm-nowrap">
                                      <div class="d-flex align-items-center mr-2 mr-sm-3 mb-0 mb-sm-0">
                                        <select class="form-control wd-70 mr-1 dynamic-type" tabindex="-1" disabled="disabled">
                                          <option value="First">First</option>
                                        </select>
                                        <input class="form-control wd-50 mx-1 ht-20 py-0 text-center dynamic-km dynamic-input" maxlength="5" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"> Kg
                                      </div>
                                      <div class="d-flex align-items-center">
                                        <%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %><input class="form-control wd-50 mx-1 ht-20 py-0 text-center dynamic-val dynamic-input" maxlength="5" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
                                      </div>
                                      <div class="addcharge ml-2 ml-sm-3">
                                        <a href="javascript:void(0)" class="bg-transparent btn p-1 addcharge_btn d-flex align-items-center justify-content-center dynamic-add"><i class="fa-regular fa-plus tx-16"></i></a>
                                      </div>
                                    </div>
                                  </div><!--slablist-->
                              

                                </div><!--slablist_wrap-->

                                <div class=" d-flex flex-wrap w-100 mt-2">
                                  <div class="d-flex align-items-center w-auto mr-0 mr-sm-3 mb-2 mb-sm-0 slablist-above">
                                    Above<asp:CustomValidator ID="CustomValidator1" runat="server" ControlToValidate="txtDynamicAboveKG" ValidationGroup="CreateRule" SetFocusOnError="true"
                    ErrorMessage="Required input" ClientValidationFunction="validateInput" ValidateEmptyText="true" ForeColor="Red" Text="*"></asp:CustomValidator> 
                                    <asp:TextBox ID="txtDynamicAboveKG" ValidationGroup="CreateRule" runat="server" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center slablist-above-kg" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox> 
                                       kg
                                  </div>
                                  <div class="d-flex align-items-center w-auto">
                                    <asp:CustomValidator ID="CustomValidator2" runat="server" ControlToValidate="txtDynamicAboveVal" ValidationGroup="CreateRule" SetFocusOnError="true"
                    ErrorMessage="Required input" ClientValidationFunction="validateInput" ValidateEmptyText="true" ForeColor="Red" Text="*"></asp:CustomValidator> 
                                    <%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %><asp:TextBox ID="txtDynamicAboveVal" ValidationGroup="CreateRule" runat="server" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center slablist-above-val" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox> 
                                   
                                  </div>
                                </div><!--col-12-->


                              </div><!--col-12-->

                              <div class="d-flex flex-wrap py-1 ZoneWeightCharges w-100 <%= (rb_ZoneWeight.Checked ? "" : "hide") %>">
                                <div class="d-flex align-items-center w-auto">
                                  <asp:Repeater ID="rptZones" runat="server" DataSourceID="SDSZone" OnItemDataBound="rptZones_ItemDataBound">
                                      <ItemTemplate>
                                          <div class="form-group">
                                          <asp:HyperLink ID="hlZone" runat="server" NavigateUrl="javascript:void(0)" CssClass='<%# String.Format("btn {0} zoneSettings mr-3", (Eval("zoneval").ToString() == "" ? "btn-light" : "btn-primary")) %>' hidid="" zoneid='<%# Eval("id") %>' Text='<%# Eval("Name") %>'></asp:HyperLink>
                                          <asp:HiddenField ID="hidZone1Val" runat="server" Value='<%# Eval("zoneval") %>' />
                                                          <br /><asp:TextBox ID="txtZvalHid" CssClass="hidDuplicateVal" runat="server" Width="0" Height="0" ReadOnly="true" BorderStyle="None" ValidationGroup="CreateRule"></asp:TextBox><asp:CustomValidator runat="server" ControlToValidate="txtZvalHid" ValidationGroup="CreateRule" SetFocusOnError="true" Display="Dynamic"
ErrorMessage="Please add delivery zone" ClientValidationFunction="validateemptyzone" ValidateEmptyText="true" CssClass="mr-3 customvalidator" ForeColor="Red" Text="Zone rate is required"></asp:CustomValidator>
                                              </div>
                                      </ItemTemplate>                                      
                                  </asp:Repeater>
                                    <% if (rptZones.Items.Count <= 0)
                                        { %>
    No data available. Please create your delivery zones. &nbsp;<a href="/tenant/Deliveryzone" class="btn btn-primary ml-2">Create</a>
                                    <asp:TextBox ID="txtNoZone" runat="server" BorderStyle="None" ReadOnly="true" Enabled="false" Width="0" onfocus="blur()"></asp:TextBox>
                <asp:CustomValidator ID="CustomValidator6" runat="server" ControlToValidate="txtNoZone" ValidationGroup="CreateRule" SetFocusOnError="true"
ErrorMessage="Please add delivery zone" ClientValidationFunction="validateInput" ValidateEmptyText="true" ForeColor="Red" Text="Please add delivery zone"></asp:CustomValidator>

<% } %>

                                    <asp:SqlDataSource ID="SDSZone" OnSelecting="SDSZone_Selecting" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" ProviderName="MySql.Data.MySqlClient"
                                        SelectCommand="SELECT *, (SELECT GROUP_CONCAT( CONCAT((CASE WHEN slabType=1 THEN 'First' WHEN slabType=3 THEN 'Above' ELSE 'Next' END), ',', weight, ',', slabAmount) SEPARATOR '|' ) AS val 
                                            FROM `delivery_rule_slab` ds INNER JOIN retaline_delivery_rules r ON ds.drId=r.rdr_id 
                                            WHERE ds.zoneId= z.id AND r.rdr_deliveryMode = @delimode AND r.rdr_ruleFor = 3 AND r.rdr_ruleForId= @brid AND r.rdr_storeGroupId=@storeId
                                            ) AS zoneval FROM `delivery_zone` z WHERE storegroupId=0 OR storegroupId=@storeId ORDER BY districtId, stateId, countryid" >
                                        <SelectParameters>
                                            <asp:Parameter Name="storeId" /><asp:QueryStringParameter QueryStringField="brid" Name="brid" ConvertEmptyStringToNull="false" DefaultValue="-1" />
                                            <asp:Parameter Name="delimode" />
                                        </SelectParameters>
                                    </asp:SqlDataSource>

                                    <br />
                                </div>
                                    <%--<% if (rptZones.Items.Count > 0)
                                        { %>

                <asp:TextBox ID="txtValidZoneData" runat="server" BorderStyle="None" ReadOnly="true" Enabled="false" Width="0" onfocus="blur()"></asp:TextBox><asp:CustomValidator ID="CustomValidator7" runat="server" ControlToValidate="txtValidZoneData" ValidationGroup="CreateRule" SetFocusOnError="true"
ErrorMessage="Please configure atleast one delivery zone" ClientValidationFunction="validateemptyzone" ValidateEmptyText="true" ForeColor="Red" Text="Please configure the delivery zone"></asp:CustomValidator>

                                  <%} %>--%>
                              </div><!--col-12-->

                                <div class="invalid-feedback valsummary">Please provide required input.</div>
                            </div><!--chargedtails-->

                      </div><!--col-12 -->

                    </div><!--type_delivery_dteails-->
                    <div class="<%= (chk_delivery.Checked ? "hide" : "") %> showinfo p-2 w-100 tx-dark">
                        <i class="fa-regular fa-circle-info tx-16"></i> The rates for <asp:Label ID="lblDeliveryRulenew" runat="server"></asp:Label> are now managed by the system.
                    </div>
                  </div><!--delivery_type-->
