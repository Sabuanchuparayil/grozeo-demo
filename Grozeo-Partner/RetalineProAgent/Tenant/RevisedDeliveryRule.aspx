<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="RevisedDeliveryRule.aspx.cs" MasterPageFile="~/Tenant/TenantMaster.master" Inherits="RetalineProAgent.Tenant.RevisedDeliveryRule" %>
<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <a href="javascript:void(0)" onclick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle">
            <asp:Literal ID="ltrTitle1" runat="server" Text="Delivery Rules"></asp:Literal>
        </h6>
        <p class="mb-0">Customizable Delivery Parameters</p>
    </div>
    <style>
        table.table table, table.table table td {
            border: 0px !important;
            padding: 5px;
        }
    </style>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <div class="card">
            <div class="card-body p-0 rounded-0 bg-transparent">
              <div class="row row-sm">
                <div class="col-12 delivery_rules_wrap">

                  <div class="type_of_delivery d-flex flex-wrap p-3 mb-3 card-body hyperlocal_delivery_type">

                    <div class="deliveryTitle d-flex w-100">
                      <label class="fw-bold w-auto mr-2">
                          <asp:CheckBox id="hyperlocal_delivery"  class="chk check_delivery_type" runat="server" OnCheckedChanged="hyperlocal_delivery_CheckedChanged" Text="Hyperlocal Delivery"/>
                        <%--<input id="hyperlocal_delivery"  class="chk check_delivery_type" type="checkbox" runat="server">--%>
                          <%--<span class="tx-uppercase tx-bold"></span>--%>
                      </label>
                      <span>(Max Weight: 10Kg)</span>
                    </div>

                    <div id="deliveryDteails" class="type_delivery_dteails p-2 deliveryDteails w-100 <%= (hyperlocal_delivery.Checked ? "" : "disabled") %>">
                      <div class="manage_delivery d-flex flex-wrap flex-md-nowrap">
                          <div><asp:RadioButton runat="server" GroupName="manageDeliveryMode" Text="Store will manage delivery" /></div>
                            <div> <asp:RadioButton runat="server" GroupName="manageDeliveryMode" Text="Let Grozeo manage delivery" /></div>
                        <%--<label class="rdiobox mr-4">
                          <input id="store_hyperlocal" name="hyperlocal_delivery" type="radio" value="store_hyperlocal" class="DeliByGroze" runat="server">
                          <span class="p-0">Store will manage delivery</span>
                        </label>

                        <label class="rdiobox">
                          <input id="grozeo_hyperlocal" name="hyperlocal_delivery" type="radio" value="grozeo_hyperlocal" class="DeliByGroze" runat="server">
                          <span class="p-0">Let Grozeo manage delivery</span>
                        </label>--%>
                      </div><!--manage_delivery-->

                      <div class="d-flex flex-wrap py-1 type_of_charge DeliveryCharge ">
                        <div class="charge_type_switch d-flex flex-wrap flex-md-nowrap mt-2 mt-lg-0 w-100">
                          <label class="rdiobox mr-3">
                            <input id="HyperlocalFixed" name="hyperlocal_delivery_type" type="radio" value="HyperlocalFixed" class="chargetypeswitch" runat="server">
                            <span class="p-0">Fixed Charge</span>
                          </label>
  
                          <label class="rdiobox">
                            <input id="HyperlocalDynamic" name="hyperlocal_delivery_type" type="radio" value="HyperlocalDynamic" class="chargetypeswitch" runat="server">
                            <span class="p-0">Distance Based Charge</span>
                          </label>
                        </div><!--charge_type_switch-->

                        <div class="d-flex flex-wrap w-100 mt-1 chargedtails">
                          <div class="d-flex flex-wrap py-1 fixedharges w-100">
                            <div class="d-flex align-items-center w-auto">
                              Amount 
                                <asp:TextBox runat="server" ID="txtFixedCharge" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
<%--                                <input class="form-control wd-55 mx-1 ht-20 py-0 text-center" type="text" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"> --%>
                                Rupees
                            </div>
                          </div>

                          <div class="d-flex flex-wrap py-1 Dynamiccharges ">
                            <div class="d-flex align-items-center w-auto mr-3 mr-lg-4 mb-2 mb-lg-0">
                              Max Distance 
                                <asp:TextBox runat="server" ID="dynamicMaxDistance" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                <%--<input class="form-control wd-55 mx-1 ht-20 py-0 text-center" maxlength="5" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                Km
                            </div>
                            <div class="d-flex align-items-center w-auto mr-3 mr-lg-4 mb-2 mb-lg-0">
                              Rate per KM 
                                <asp:TextBox runat="server" ID="dynamicRateKm" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                <%--<input class="form-control wd-55 mx-1 ht-20 py-0 text-center" maxlength="5" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                Rupees
                            </div>
                            <div class="d-flex align-items-center w-auto mr-3 mr-lg-4 mb-2 mb-lg-0">
                              Min Charge 
                                <asp:TextBox runat="server" ID="dynamicMinCharge" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                <%--<input class="form-control wd-55 mx-1 ht-20 py-0 text-center" maxlength="5" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                Rupees
                            </div>
                          </div>

                        </div><!--chargedtails-->

                      </div><!--col-12 -->

                    </div><!--type_delivery_dteails-->

                  </div><!--hyperlocal_delivery_type-->

                  <div class="type_of_delivery d-flex flex-wrap p-3 mb-3 card-body local_delivery_type">

                    <div class="deliveryTitle d-flex w-100">
                      <label class="ckbox fw-bold w-auto mr-2">
                        <input id="local_delivery" class="chk check_delivery_type" type="checkbox" runat="server"><span
                          class="tx-uppercase tx-bold">Scheduled Local Delivery</span>
                      </label>
                      <span>(Max Weight: 25Kg)</span>
                    </div>
                    
                    <div class="type_delivery_dteails p-2 deliveryDteails w-100 ">
                      <div class="manage_delivery d-flex flex-wrap flex-md-nowrap">
                        <label class="rdiobox mr-4">
                          <input id="store_local" name="local_delivery" value="store_local" type="radio" class="DeliByGroze" runat="server">
                          <span class="p-0">Store will manage delivery</span>
                        </label>

                        <label class="rdiobox">
                          <input id="grozeo_local" name="local_delivery" value="grozeo_local" type="radio" class="DeliByGroze" runat="server">
                          <span class="p-0">Let Grozeo manage delivery</span>
                        </label>
                      </div><!--col-12-->

                      <div class="d-flex flex-wrap py-1 type_of_charge DeliveryCharge  ">
                        <div class="charge_type_switch d-flex flex-wrap flex-md-nowrap mt-2 mt-lg-0 w-100">
                          <label class="rdiobox mr-3">
                            <input id="LocalFixed" name="local_delivery_type" type="radio" value="LocalFixed" class="chargetypeswitch" runat="server">
                            <span class="p-0">Fixed Charge</span>
                          </label>
  
                          <label class="rdiobox">
                            <input id="LocalDynamic" name="local_delivery_type" type="radio" value="LocalDynamic" class="chargetypeswitch" runat="server">
                            <span class="p-0">Distance Based Charge</span>
                          </label>
                        </div><!--charge_type_switch-->

                        <div class="d-flex flex-wrap w-100 mt-1 chargedtails">                          

                          <div class="d-flex flex-wrap py-1 fixedharges w-100">
                            <div class="d-flex align-items-center w-auto">
                              Amount 
                                <asp:TextBox runat="server" ID="txtSldFixedRate" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                <%--<input class="form-control wd-55 mx-1 ht-20 py-0 text-center" type="text" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                Rupees
                            </div>
                          </div><!--col-12-->

                          <div class="d-flex flex-wrap py-1 Dynamiccharges ">
                            <div class="d-flex align-items-center w-auto mr-0 mr-lg-4 mb-2 mb-lg-0 mb-lg-0">
                              Max Distance 
                                <asp:TextBox runat="server" ID="txtsldDynamicMaxDistance" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                <%--<input class="form-control wd-55 mx-1 ht-20 py-0 text-center" maxlength="5" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                Km
                            </div>
                            <div class="d-flex align-items-center w-auto mr-0 mr-lg-4 mb-2 mb-lg-0 mb-lg-0">
                              Rate per KM 
                                <asp:TextBox runat="server" ID="txtSldDynamicRateKm" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                <%--<input class="form-control wd-55 mx-1 ht-20 py-0 text-center" maxlength="5" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                Rupees
                            </div>
                            <div class="d-flex align-items-center w-auto mr-0 mr-lg-4 mb-2 mb-lg-0 mb-lg-0">
                              Min Charge 
                                <asp:TextBox runat="server" ID="txtSldDynamicMinCharge" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                <%--<input class="form-control wd-55 mx-1 ht-20 py-0 text-center" maxlength="5" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                Rupees
                            </div>
                            <div class="d-flex align-items-center w-auto mr-0 mr-lg-4 mb-2 mb-lg-0 mb-lg-0">
                              Max Charge 
                                <asp:TextBox runat="server" ID="txtSldDynamicMaxCharge" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                <%--<input class="form-control wd-55 mx-1 ht-20 py-0 text-center" maxlength="5" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                Rupees
                            </div>
                          </div><!--col-12-->                          

                        </div><!--chargedtails-->

                      </div><!--col-12 -->

                    </div><!--type_delivery_dteails-->

                  </div><!--local_delivery_type-->

                  <div class="type_of_delivery d-flex flex-wrap p-3 mb-3 card-body long_delivery_type">

                    <div class="deliveryTitle d-flex w-100">
                      <label class="ckbox w-auto mr-2">
                        <input id="long_delivery" class="chk check_delivery_type" type="checkbox" runat="server"><span
                          class="tx-uppercase tx-bold">Local Last Mile Delivery</span>
                      </label>
                      <span>(Max Weight: 10Kg)</span>
                    </div>
                    
                    <div class="type_delivery_dteails p-2 deliveryDteails w-100 ">
                      <div class="manage_delivery d-flex flex-wrap flex-md-nowrap">

                        <label class="rdiobox mr-4">
                          <input id="store_long" name="long_delivery" type="radio" value="store_long" class="DeliByGroze" runat="server">
                          <span class="p-0">Store will manage delivery</span>
                        </label>

                        <label class="rdiobox">
                          <input id="grozeo_long" name="long_delivery" type="radio" value="grozeo_long" class="DeliByGroze" runat="server">
                          <span class="p-0">Let Grozeo manage delivery</span>
                        </label>
                      </div><!--col-12-->

                      <div class="d-flex flex-wrap py-1 type_of_charge DeliveryCharge  ">
                        <div class="charge_type_switch d-flex flex-wrap flex-md-nowrap mt-2 mt-lg-0 w-100">
                          <label class="rdiobox mr-3">
                            <input id="LongFixed" name="long_delivery_type" type="radio" value="LongFixed" class="chargetypeswitch" runat="server">
                            <span class="p-0">Fixed Charge</span>
                          </label>
  
                          <label class="rdiobox">
                            <input id="LongDynamic" name="long_delivery_type" type="radio" value="LongDynamic" class="chargetypeswitch" runat="server">
                            <span class="p-0">Distance Based Charge</span>
                          </label>
                        </div><!--charge_type_switch-->

                        <div class="d-flex flex-wrap w-100 mt-1 chargedtails">                          

                          <div class="d-flex flex-wrap py-1 fixedharges w-100">
                            <div class="d-flex align-items-center w-auto">
                              Amount 
                                <asp:TextBox runat="server" ID="txtLlmFixedRate" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                <%--<input class="form-control wd-55 mx-1 ht-20 py-0 text-center" type="text" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                Rupees
                            </div>
                          </div><!--col-12-->

                          <div class="d-flex flex-wrap py-1 Dynamiccharges ">
                            <div class="d-flex align-items-center w-auto mr-0 mr-lg-4 mb-2 mb-lg-0 mb-lg-0">
                              Max Distance 
                                <asp:TextBox runat="server" ID="txtLlmDynamicMaxDistance" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                <%--<input class="form-control wd-55 mx-1 ht-20 py-0 text-center" maxlength="5" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                Km
                            </div>
                            <div class="d-flex align-items-center w-auto mr-0 mr-lg-4 mb-2 mb-lg-0 mb-lg-0">
                              Rate per KM 
                                <asp:TextBox runat="server" ID="txtLlmDynamicRateKm" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                <%--<input class="form-control wd-55 mx-1 ht-20 py-0 text-center" maxlength="5" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                Rupees
                            </div>
                            <div class="d-flex align-items-center w-auto mr-0 mr-lg-4 mb-2 mb-lg-0 mb-lg-0">
                              Min Charge 
                                <asp:TextBox runat="server" ID="txtLlmDynamicMinCharge" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                <%--<input class="form-control wd-55 mx-1 ht-20 py-0 text-center" maxlength="5" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                Rupees
                            </div>
                            <div class="d-flex align-items-center w-auto mr-0 mr-lg-4 mb-2 mb-lg-0 mb-lg-0">
                              Max Charge 
                                <asp:TextBox runat="server" ID="txtLlmDynamicMaxCharge" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                <%--<input class="form-control wd-55 mx-1 ht-20 py-0 text-center" maxlength="5" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                Rupees
                            </div>
                          </div><!--col-12-->                          

                        </div><!--chargedtails-->

                      </div><!--col-12 -->

                      

                    </div><!--type_delivery_dteails-->

                  </div><!--long_delivery_type-->

                  <div class="type_of_delivery d-flex flex-wrap p-3 mb-3 card-body courier_delivery_type">
                    
                    <div class="deliveryTitle d-flex w-100">
                      <label class="ckbox w-auto mr-2">
                        <input id="courier_delivery" class="chk check_delivery_type" type="checkbox" runat="server"><span
                          class="tx-uppercase tx-bold">Courier Delivery</span>
                      </label>
                      <span>(Max Weight: 10Kg)</span>
                    </div>

                    <div class="type_delivery_dteails p-2 deliveryDteails w-100 ">
                      <div class="manage_delivery d-flex flex-wrap flex-md-nowrap">

                        <label class="rdiobox mr-4">
                          <input id="store_courier" name="courier_delivery" type="radio" value="store_courier" class="DeliByGroze" runat="server">
                          <span class="p-0">Store will manage delivery</span>
                        </label>

                        <label class="rdiobox">
                          <input id="grozeo_courier" name="courier_delivery" type="radio" value="grozeo_courier" class="DeliByGroze" runat="server">
                          <span class="p-0">Let Grozeo manage delivery</span>
                        </label>
                      </div><!--col-12-->

                      <div class="d-flex flex-wrap py-1 type_of_charge DeliveryCharge  ">
                        <div class="charge_type_switch d-flex flex-wrap flex-md-nowrap mt-2 mt-lg-0 w-100">
                          <label class="rdiobox mr-3">
                            <input id="courierFixed" name="courier_delivery_type" type="radio" value="courierFixed" class="chargetypeswitch" runat="server">
                            <span class="p-0">Fixed Charge</span>
                          </label>
  
                          <label class="rdiobox">
                            <input id="courierDynamic" name="courier_delivery_type"  type="radio" value="courierDynamic" class="chargetypeswitch" runat="server">
                            <span class="p-0">Distance Based Charge</span>
                          </label>
                        </div><!--charge_type_switch-->

                        <div class="d-flex flex-wrap w-100 mt-1 chargedtails">                          

                          <div class="d-flex flex-wrap py-1 fixedharges w-100">
                            <div class="d-flex align-items-center w-auto">
                              Amount 
                                <asp:TextBox runat="server" ID="txtCourFixedRate" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                <%--<input class="form-control wd-55 mx-1 ht-20 py-0 text-center" type="text" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                Rupees
                            </div>
                          </div><!--col-12-->

                          <div class="d-flex flex-wrap py-1 Dynamiccharges ">
                            <div class="slablist_wrap">

                              <div class="slablist p-1 p-sm-2 border mb-1">
                                <div class="d-flex align-items-baseline align-items-sm-center flex-wrap flex-sm-nowrap">
                                  <div class="d-flex align-items-center mr-2 mr-sm-3 mb-0 mb-sm-0">
                                      <asp:DropDownList ClientIDMode="Static" ID="ddlCourierDistSlabType" CssClass="form-control wd-75 mr-1" runat="server">
                                                            <asp:ListItem Text="Up To" Value="1"></asp:ListItem>
                                                            <asp:ListItem Text="Above" Value="2"></asp:ListItem>
                                                        </asp:DropDownList>
                                    <%--<select name="" id="" class="form-control wd-70 mr-1" tabindex="-1">
                                      <option value="Upto">Up to</option>
                                      <option value="Above">Above</option>
                                    </select>--%>
                                      <asp:TextBox runat="server" ClientIDMode="Static" ID="txtCourierDistSlabKm" CssClass="form-control wd-50 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                    <%--<input class="form-control wd-50 mx-1 ht-20 py-0 text-center" maxlength="5" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                      Km
                                  </div>
                                  <div class="d-flex align-items-center">
                                      <asp:TextBox runat="server" ClientIDMode="Static" ID="txtCourierDistSlabRate" CssClass="form-control wd-50 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                    <%--<input class="form-control wd-50 mx-1 ht-20 py-0 text-center" maxlength="5" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                      ₹/ Km
                                  </div>
                                  <div class="addcharge ml-2 ml-sm-3">
                                      <asp:LinkButton ID="btncourierDistanceSlab" CssClass="bg-transparent btn p-1 addcharge_btn d-flex align-items-center justify-content-center" OnClick="btncourierDistanceSlab_Click" OnClientClick="storeAndDisplayData(); return false;"   runat ="server"><i class="fa-regular fa-plus tx-16"></i></asp:LinkButton>
                                    <%--<button class="bg-transparent btn p-1 addcharge_btn d-flex align-items-center justify-content-center"><i class="fa-regular fa-plus tx-16"></i></button>--%>
                                  </div>
                                </div>
                              </div><!--slablist-->
                              <asp:Repeater ID="rptCourierDistanceSlab" runat="server" >
                                                <ItemTemplate>
                                                    <div class="repslablist p-1 p-sm-2 border mb-1">
                                                <div class="d-flex align-items-baseline align-items-sm-center flex-wrap flex-sm-nowrap">
                                                    <div class="d-flex align-items-center mr-2 mr-sm-3 mb-0 mb-sm-0">
                                                        <asp:Label runat="server" ID="txtCourierDistSlabTypeRept" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center"   Text='<%# Eval("slabTypeName")%>'></asp:Label>

                                                        <asp:Label runat="server" ID="txtCourierDistSlabKmRept" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" Text='<%# Eval("slabDistance")%>'></asp:Label> Km
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        <asp:Label runat="server" ID="txtCourierDistSlabRateRept" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" Text='<%# Eval("slabAmount")%>'></asp:Label>
                                                        ₹/ Km
                                                    </div>                                                    
                                                </div>
                                            </div>
                                                </ItemTemplate>
                                            </asp:Repeater>
                                            <!--repeater with columnname -->
                                            <!--slablist-->

                            </div><!--slablist_wrap-->

                            <div class=" d-flex flex-wrap w-100 mt-2">
                              <div class="d-flex align-items-center w-auto mr-0 mr-sm-3 mb-2 mb-sm-0">
                                Min Charge 
                                  <asp:TextBox runat="server" ID="txtCourDynamicMinCharge" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                  <%--<input class="form-control wd-55 mx-1 ht-20 py-0 text-center" type="text" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                  Rupees
                              </div>
                              <div class="d-flex align-items-center w-auto">
                                Max Charge 
                                  <asp:TextBox runat="server" ID="txtCourDynamicMaxCharge" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                  <%--<input class="form-control wd-55 mx-1 ht-20 py-0 text-center" type="text" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                  Rupees
                              </div>
                            </div><!--col-12-->
                          </div><!--col-12-->                            

                        </div><!--chargedtails-->

                      </div><!--col-12 -->

                    </div><!--type_delivery_dteails-->

                  </div><!--courier_delivery_type-->

                  <div class="type_of_delivery d-flex flex-wrap p-3 mb-3 card-body parcel_delivery_type">

                    <div class="deliveryTitle d-flex w-100">
                      <label class="ckbox w-auto mr-2">
                        <input id="parcel_delivery" class="chk check_delivery_type" type="checkbox" runat="server"><span class="tx-uppercase tx-bold">Parcel Delivery</span>
                      </label>
                      <span>(Max Weight: 100Kg)</span>
                    </div>
                    
                    <div class="type_delivery_dteails p-2 deliveryDteails w-100 ">
                      <div class="manage_delivery d-flex flex-wrap flex-md-nowrap">

                        <label class="rdiobox mr-4">
                          <input id="store_parcel" name="parcel_delivery" type="radio" value="store_parcel" class="DeliByGroze" runat="server">
                          <span class="p-0">Store will manage delivery</span>
                        </label>

                        <label class="rdiobox">
                          <input id="grozeo_parcel" name="parcel_delivery" type="radio" value="grozeo_parcel" class="DeliByGroze" runat="server">
                          <span class="p-0">Let Grozeo manage delivery</span>
                        </label>
                      </div><!--col-12-->

                      <div class="d-flex flex-wrap py-1 type_of_charge DeliveryCharge  ">
                        <div class="charge_type_switch d-flex flex-wrap flex-md-nowrap mt-2 mt-lg-0 w-100">
                          <label class="rdiobox mr-3">
                            <input id="parcelFixed" name="parcel_delivery_type" type="radio" value="parcelFixed" class="chargetypeswitch" runat="server">
                            <span class="p-0">Fixed Charge</span>
                          </label>
  
                          <label class="rdiobox">
                            <input id="parcelDynamic" name="parcel_delivery_type" type="radio" value="parcelDynamic" class="chargetypeswitch" runat="server">
                            <span class="p-0">Distance Based Charge</span>
                          </label>
                        </div><!--charge_type_switch-->

                        <div class="d-flex flex-wrap w-100 mt-1 chargedtails">                          

                          <div class="d-flex flex-wrap py-1 fixedharges w-100">
                            <div class="d-flex align-items-center w-auto">
                              Amount 
                                <asp:TextBox runat="server" ID="txtParcelFixedRate" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                <%--<input class="form-control wd-55 mx-1 ht-20 py-0 text-center" type="text" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                Rupees
                            </div>
                          </div><!--col-12-->

                          <div class="d-flex flex-wrap py-1 Dynamiccharges ">
                            <div class="slablist_wrap">
                              
                              <div class="slablist p-1 p-sm-2 border mb-1">
                                <div class="d-flex align-items-baseline align-items-sm-center flex-wrap flex-sm-nowrap">
                                  <div class="d-flex align-items-center mr-2 mr-sm-3 mb-0 mb-sm-0">
                                    <select name="" id="" class="form-control wd-70 mr-1" tabindex="-1">
                                      <option value="Upto">Up to</option>
                                      <option value="Above">Above</option>
                                    </select>
                                    <input class="form-control wd-50 mx-1 ht-20 py-0 text-center" maxlength="5" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"> Km
                                  </div>
                                  <div class="d-flex align-items-center">
                                    <input class="form-control wd-50 mx-1 ht-20 py-0 text-center" maxlength="5" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"> ₹/ Km
                                  </div>
                                  <div class="addcharge ml-2 ml-sm-3">
                                    <button class="bg-transparent btn p-1 addcharge_btn d-flex align-items-center justify-content-center"><i class="fa-regular fa-plus tx-16"></i></button>
                                  </div>
                                </div>
                              </div><!--slablist-->
                              

                            </div><!--slablist_wrap-->

                            <div class=" d-flex flex-wrap w-100 mt-2">
                              <div class="d-flex align-items-center w-auto mr-0 mr-sm-3 mb-2 mb-sm-0">
                                Min Charge 
                                  <asp:TextBox runat="server" ID="txtParcelDynamicMinCharge" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                  <%--<input class="form-control wd-55 mx-1 ht-20 py-0 text-center" type="text" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                  Rupees
                              </div>
                              <div class="d-flex align-items-center w-auto">
                                Max Charge 
                                  <asp:TextBox runat="server" ID="txtParcelDynamicMaxCharge" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                  <%--<input class="form-control wd-55 mx-1 ht-20 py-0 text-center" type="text" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                  Rupees
                              </div>
                            </div><!--col-12-->
                          </div><!--col-12-->                          

                        </div><!--chargedtails-->

                      </div><!--col-12 -->

                    </div><!--type_delivery_dteails-->

                  </div><!--parcel_delivery_type-->


                  <div class="type_of_delivery d-flex flex-wrap p-3 mb-3 card-body cargo_delivery_type">

                    <div class="deliveryTitle d-flex w-100">
                      <label class="ckbox w-auto mr-2">
                        <input id="cargo_delivery" class="chk check_delivery_type" type="checkbox" runat="server"><span
                          class="tx-uppercase tx-bold">Cargo Delivery</span>
                      </label>
                      <span>(Max Weight: 250Kg)</span>
                    </div>
                    
                    <div class="type_delivery_dteails p-2 deliveryDteails w-100 ">
                      <div class="manage_delivery d-flex flex-wrap flex-md-nowrap">

                        <label class="rdiobox mr-4">
                          <input id="store_cargo" name="cargo_delivery" type="radio" value="store_cargo" class="DeliByGroze" runat="server">
                          <span class="p-0">Store will manage delivery</span>
                        </label>

                        <label class="rdiobox">
                          <input id="grozeo_cargo" name="cargo_delivery" type="radio" value="grozeo_cargo" class="DeliByGroze" runat="server">
                          <span class="p-0">Let Grozeo manage delivery</span>
                        </label>
                      </div><!--col-12-->

                      <div class="d-flex flex-wrap py-1 type_of_charge DeliveryCharge  ">
                        <div class="charge_type_switch d-flex flex-wrap flex-md-nowrap mt-2 mt-lg-0 w-100">
                          <label class="rdiobox mr-3">
                            <input id="cargoFixed" name="cargo_delivery_type" type="radio" value="cargoFixed" class="chargetypeswitch" runat="server">
                            <span class="p-0">Fixed Charge</span>
                          </label>
  
                          <label class="rdiobox">
                            <input id="cargoDynamic" name="cargo_delivery_type" type="radio" value="cargoDynamic" class="chargetypeswitch" runat="server">
                            <span class="p-0">Distance Based Charge</span>
                          </label>
                        </div><!--charge_type_switch-->

                        <div class="d-flex flex-wrap w-100 mt-1 chargedtails">                          

                          <div class="d-flex flex-wrap py-1 fixedharges w-100">
                            <div class="d-flex align-items-center w-auto">
                              Amount 
                                <asp:TextBox runat="server" ID="txtCargoFixedRate" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                <%--<input class="form-control wd-55 mx-1 ht-20 py-0 text-center" type="text" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                Rupees
                            </div>
                          </div><!--col-12-->

                          <div class="d-flex flex-wrap py-1 Dynamiccharges ">
                            <div class="slablist_wrap">
                              
                              <div class="slablist p-1 p-sm-2 border mb-1">
                                <div class="d-flex align-items-baseline align-items-sm-center flex-wrap flex-sm-nowrap">
                                  <div class="d-flex align-items-center mr-2 mr-sm-3 mb-0 mb-sm-0">
                                    <select name="" id="" class="form-control wd-70 mr-1" tabindex="-1">
                                      <option value="Upto">Up to</option>
                                      <option value="Above">Above</option>
                                    </select>
                                    <input class="form-control wd-50 mx-1 ht-20 py-0 text-center" maxlength="5" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"> Km
                                  </div>
                                  <div class="d-flex align-items-center">
                                    <input class="form-control wd-50 mx-1 ht-20 py-0 text-center" maxlength="5" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"> ₹/ Km
                                  </div>
                                  <div class="addcharge ml-2 ml-sm-3">
                                    <button class="bg-transparent btn p-1 addcharge_btn d-flex align-items-center justify-content-center"><i class="fa-regular fa-plus tx-16"></i></button>
                                  </div>
                                </div>
                              </div><!--slablist-->
                              

                            </div><!--slablist_wrap-->

                            <div class=" d-flex flex-wrap w-100 mt-2">
                              <div class="d-flex align-items-center w-auto mr-0 mr-sm-3 mb-2 mb-sm-0">
                                Min Charge 
                                  <asp:TextBox runat="server" ID="txtCargoDynamicMinCharge" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                  <%--<input class="form-control wd-55 mx-1 ht-20 py-0 text-center" type="text" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                  Rupees
                              </div>
                              <div class="d-flex align-items-center w-auto">
                                Max Charge 
                                  <asp:TextBox runat="server" ID="txtCargoDynamicMaxCharge" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                  <%--<input class="form-control wd-55 mx-1 ht-20 py-0 text-center" type="text" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                  Rupees
                              </div>
                            </div><!--col-12-->
                          </div><!--col-12-->                          

                        </div><!--chargedtails-->

                      </div><!--col-12 -->

                    </div><!--type_delivery_dteails-->

                  </div><!--cargo_delivery_type-->

                  
                  <div class="type_of_delivery d-flex flex-wrap p-3 mb-3 card-body manual_delivery_type">

                    <div class="deliveryTitle d-flex w-100">
                      <label class="ckbox w-auto">
                        <input id="manual_delivery" class="chk check_delivery_type" type="checkbox" runat="server"><span
                          class="tx-uppercase tx-bold">Manual Delivery</span>
                      </label>
                    </div>
                    
                    <div class="type_delivery_dteails p-2 w-100 ManualDelivery ">
                     
                      <div class="d-flex flex-wrap py-1 type_of_charge ManualDeliveryCharge">
                        <div class="charge_type_switch d-flex flex-wrap flex-md-nowrap w-100">
                          <label class="rdiobox mr-3">
                            <input id="manualFixed" name="manual_delivery_type" type="radio" value="manualFixed" class="chargetypeswitch" runat="server">
                            <span class="p-0">Fixed Charge</span>
                          </label>
  
                          <label class="rdiobox">
                            <input id="manualDynamic" name="manual_delivery_type" type="radio" value="manualDynamic" class="chargetypeswitch" runat="server">
                            <span class="p-0">Distance Based Charge</span>
                          </label>
                        </div><!--charge_type_switch-->

                        <div class="d-flex flex-wrap w-100 mt-1 chargedtails">                          

                          <div class="d-flex flex-wrap py-1 fixedharges w-100">
                            <div class="d-flex align-items-center w-auto">
                              Amount 
                                <asp:TextBox runat="server" ID="txtManualFixedRate" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                <%--<input class="form-control wd-55 mx-1 ht-20 py-0 text-center" type="text" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                Rupees
                            </div>
                          </div><!--col-12-->

                          <div class="d-flex flex-wrap py-1 Dynamiccharges ">
                            <div class="slablist_wrap">
                              
                              <div class="slablist p-1 p-sm-2 border mb-1">
                                <div class="d-flex align-items-baseline align-items-sm-center flex-wrap flex-sm-nowrap">
                                  <div class="d-flex align-items-center mr-2 mr-sm-3 mb-0 mb-sm-0">
                                    <select name="" id="" class="form-control wd-70 mr-1" tabindex="-1">
                                      <option value="Upto">Up to</option>
                                      <option value="Above">Above</option>
                                    </select>
                                    <input class="form-control wd-50 mx-1 ht-20 py-0 text-center" maxlength="5" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"> Km
                                  </div>
                                  <div class="d-flex align-items-center">
                                    <input class="form-control wd-50 mx-1 ht-20 py-0 text-center" maxlength="5" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"> ₹/ Km
                                  </div>
                                  <div class="addcharge ml-2 ml-sm-3">
                                    <button class="bg-transparent btn p-1 addcharge_btn d-flex align-items-center justify-content-center"><i class="fa-regular fa-plus tx-16"></i></button>
                                  </div>
                                </div>
                              </div><!--slablist-->
                              

                            </div><!--slablist_wrap-->

                            <div class=" d-flex flex-wrap w-100 mt-2">
                              <div class="d-flex align-items-center w-auto mr-0 mr-sm-3 mb-2 mb-sm-0">
                                Min Charge 
                                  <asp:TextBox runat="server" ID="txtManualDynamicMinCharge" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                  <%--<input class="form-control wd-55 mx-1 ht-20 py-0 text-center" type="text" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                  Rupees
                              </div>
                              <div class="d-flex align-items-center w-auto">
                                Max Charge 
                                  <asp:TextBox runat="server" ID="txtManualDynamicMaxCharge" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                  <%--<input class="form-control wd-55 mx-1 ht-20 py-0 text-center" type="text" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                  Rupees
                              </div>
                            </div><!--col-12-->
                          </div><!--col-12-->                            

                        </div><!--chargedtails-->

                      </div><!--col-12 -->

                    </div><!--type_delivery_dteails-->

                  </div><!--manual_delivery_type-->


                  <div class="type_of_delivery d-flex flex-wrap p-3 mb-3 card-body share_delivery_type">

                    <div class="deliveryTitle d-flex w-100">
                      <label class="ckbox w-auto">
                        <input id="share_delivery" class="chk check_delivery_type" type="checkbox" runat="server"><span
                          class="tx-uppercase tx-bold">Delivery Cost Share</span>
                      </label>
                    </div>
                    
                    <div class="type_delivery_dteails p-2 deliveryDteails w-100 ">

                      <div class="d-flex flex-wrap py-1 type_of_charge">
                        

                        <div class="d-flex flex-wrap w-100 chargedtails">                          

                          <div class="d-flex flex-wrap py-1">
                            <div class="d-flex flex-wrap align-items-center w-auto">
                              Share the delivery cost upto 
                                <asp:TextBox runat="server" ID="txtShareCost" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                <%--<input class="form-control wd-55 mx-1 ht-20 py-0 mb-1 text-center" type="text" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%>
                                % subject to 
                                <asp:TextBox runat="server" ID="txtShareSubject" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                <%--<input class="form-control wd-55 mx-1 ht-20 py-0 mb-1 text-center" type="text" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%>
                                % of product value or Rs. 
                                <asp:TextBox runat="server" ID="txtShareValue" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>
                                <%--<input class="form-control wd-55 mx-1 ht-20 py-0 text-center" type="text" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                whichever is 
                                <asp:TextBox runat="server" ID="txtShareType" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="10" ></asp:TextBox>
                                <%--<input class="form-control wd-55 mx-1 ht-20 py-0 mb-1 text-center" type="text" maxlength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">--%> 
                                (Higher/ Lower)
                            </div>
                          </div><!--col-12-->                          

                        </div><!--chargedtails-->

                      </div><!--col-12 -->

                    </div><!--type_delivery_dteails-->

                  </div><!--share_delivery_type-->


                  <div class="deliv_filter_wrap p-3 bg-white">

                    <div class="row row-sm">
                      <div class="col-12 d-flex">
                          <asp:Button runat="server" ID="btnSaveRevDeliveryRule" CssClass="btn btn-primary" Text="Save Delivery Rule" OnClick="btnSaveRevDeliveryRule_Click"/>
                        <%--<button type="submit" class="btn btn-primary">Save Delivery Rule</button>--%>
                      </div><!-- col-12 -->
                    </div>
                  </div>

                </div><!-- col-12 -->
              </div><!-- row -->
            </div><!-- card-body -->
          </div><!-- card -->


</asp:Content>