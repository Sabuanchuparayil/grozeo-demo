<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" Title="Delivery Rules" CodeBehind="DeliveryRulesNewTest.aspx.cs" Inherits="RetalineProAgent.Tenant.DeliveryRulesNewTest" %>

<%@ Register Src="~/Controls/ctrlDeliveryRule.ascx" TagPrefix="uc1" TagName="ctrlDeliveryRule" %>


<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <a href="/Tenant/Branches"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">Manage Delivery Rates for <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal> </h6>
        <p class="mb-0">Manage delivery rates</p>
    </div>
    
</asp:Content>
<asp:Content ContentPlaceHolderID="head" runat="server">
    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
     <div class="card">
            <div class="card-body p-0 rounded-0 bg-transparent">
              <div class="row row-sm">
                <div class="col-12 delivery_rules_wrap">

                    <asp:PlaceHolder ID="plcDeliRulesControls" runat="server">
                     <uc1:ctrlDeliveryRule runat="server" id="ctrlDeliveryRuleHL" RuleTypeid="2" DeliveryRuleTypeID="2" DeliveryRuleType="Hyperlocal Delivery" DeliveryRuleWeight="10" />
                     <uc1:ctrlDeliveryRule runat="server" id="ctrlDeliveryRuleLoc" RuleTypeid="3" DeliveryRuleTypeID="3" DeliveryRuleType="Scheduled Local Delivery" DeliveryRuleWeight="25" />
                     <uc1:ctrlDeliveryRule runat="server" id="ctrlDeliveryRuleLong" RuleTypeid="4" DeliveryRuleTypeID="4" DeliveryRuleType="Local Last Mile Delivery" DeliveryRuleWeight="10" />
                     <uc1:ctrlDeliveryRule runat="server" id="ctrlDeliveryRuleCo" RuleTypeid="1" DeliveryRuleTypeID="1" DeliveryRuleType="Courier Delivery" DeliveryRuleWeight="10" />
                     <uc1:ctrlDeliveryRule runat="server" id="ctrlDeliveryRule5" RuleTypeid="5" DeliveryRuleTypeID="5" DeliveryRuleType="Parcel Delivery" DeliveryRuleWeight="100" />
                     <uc1:ctrlDeliveryRule runat="server" id="ctrlDeliveryRule6" RuleTypeid="6" DeliveryRuleTypeID="6" DeliveryRuleType="Cargo Delivery" DeliveryRuleWeight="250" />

                 </asp:PlaceHolder>

                  <div class="type_of_delivery d-flex flex-wrap p-3 mb-3 card-body share_delivery_type">

                    <div class="deliveryTitle d-flex w-100">
                      <label class="ckbox w-auto">
                        <input id="chk_share_delivery" runat="server" class="chk check_delivery_type" type="checkbox"><span
                          class="tx-uppercase tx-bold">Delivery Cost Share</span>
                      </label>
                    </div>
                    
                    <div class="type_delivery_dteails p-2 deliveryDteails w-100 <%= (chk_share_delivery.Checked ? "" : "disabled") %>">

                      <div class="d-flex flex-wrap py-1 type_of_charge">
                        

                        <div class="d-flex flex-wrap w-100 chargedtails">                          

                          <div class="d-flex flex-wrap py-1">
                            <div class="d-flex flex-wrap align-items-center w-auto">
                              Share the delivery cost upto<asp:CustomValidator ID="CustomValidator26" runat="server" ControlToValidate="txtCostShareCost" ValidationGroup="CreateRule" SetFocusOnError="true"
                ErrorMessage="Required input" ClientValidationFunction="validateInput" ValidateEmptyText="true" ForeColor="Red" Text="*" style="position: unset;"></asp:CustomValidator> 
                                <asp:TextBox ID="txtCostShareCost" ValidationGroup="CreateRule" runat="server" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>% 
                                subject to<asp:CustomValidator ID="CustomValidator27" runat="server" ControlToValidate="txtCostShareSubjectTo" ValidationGroup="CreateRule" SetFocusOnError="true"
                ErrorMessage="Required input" ClientValidationFunction="validateInput" ValidateEmptyText="true" ForeColor="Red" Text="*" style="position: unset;"></asp:CustomValidator> 
                                <asp:TextBox ID="txtCostShareSubjectTo" ValidationGroup="CreateRule" runat="server" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>% of product value 
                                or <%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %><asp:CustomValidator ID="CustomValidator28" runat="server" ControlToValidate="txtCostShareVal" ValidationGroup="CreateRule" SetFocusOnError="true"
                ErrorMessage="Required input" ClientValidationFunction="validateInput" ValidateEmptyText="true" ForeColor="Red" Text="*" style="position: unset;"></asp:CustomValidator> 
                                <asp:TextBox ID="txtCostShareVal" ValidationGroup="CreateRule" runat="server" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox>                                 
                                whichever is <asp:DropDownList ID="selCostShareType" runat="server" CssClass="form-control wd-70 mr-1 dynamic-type">
                                    <asp:ListItem Text="Higher" Value="Higher"></asp:ListItem><asp:ListItem Text="Lower" Value="Lower"></asp:ListItem>
                                </asp:DropDownList> (Higher/ Lower)
                            </div>
                          </div><!--col-12-->                          
                            <div class="invalid-feedback valsummary">Please provide required input.</div>
                        </div><!--chargedtails-->

                      </div><!--col-12 -->

                    </div><!--type_delivery_dteails-->

                  </div><!--share_delivery_type-->


                  <div class="deliv_filter_wrap p-3 bg-white">

                    <div class="row row-sm">
                      <div class="col-12 d-flex">
                          <asp:Button ID="btnSave" ValidationGroup="CreateRule" runat="server" CssClass="btn btn-primary" OnClientClick="saverules()" Text="Save Delivery Rule" OnClick="btnSave_Click" />
                        <%--<button type="submit" class="btn btn-primary">Save Delivery Rule</button>--%>
                      </div><!-- col-12 -->
                    </div>
                  </div>
                    
                </div><!-- col-12 -->
              </div><!-- row -->
            </div><!-- card-body -->
          </div><!-- card -->


    <div id="modaldelisettingspopup" class="modal fade">
      <div class="modal-dialog" role="document">
        <div class="modal-content tx-size-sm">
          <div class="modal-body tx-center pd-y-20 pd-x-20">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="tx-success tx-semibold mg-b-20 modalcustomtittle mr-4">Delivery rule for branch</h4>
            <p class="mg-b-20 mg-x-20 modalcustombody">


                            <asp:HiddenField ID="hidPopupDynamic" ClientIDMode="Static" runat="server" />
                          <div class="flex-wrap py-1 Dynamiccharges ">
                            <div class="slablist_wrap" hidid="<%= hidPopupDynamic.ClientID %>">
                              
                              <div class="slablist p-1 p-sm-2 border mb-1 slablist-row" slabtype="2">
                                <div class="d-flex align-items-baseline align-items-sm-center flex-wrap flex-sm-nowrap">
                                  <div class="d-flex align-items-center mr-2 mr-sm-3 mb-0 mb-sm-0">
                                    <select class="form-control wd-70 mr-1 dynamic-type" tabindex="-1" disabled="disabled">
                                      <option value="First">First</option>
                                    </select>
                                    <input class="form-control wd-50 mx-1 ht-20 py-0 text-center dynamic-km" maxlength="5" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"> Kg
                                  </div>
                                  <div class="d-flex align-items-center">
                                    <%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %><input class="form-control wd-50 mx-1 ht-20 py-0 text-center dynamic-val" maxlength="5" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
                                  </div>
                                  <div class="addcharge ml-2 ml-sm-3">
                                    <a href="javascript:void(0)" class="bg-transparent btn p-1 addcharge_btn d-flex align-items-center justify-content-center dynamic-add"><i class="fa-regular fa-plus tx-16"></i></a>
                                  </div>
                                </div>
                              </div><!--slablist-->
                              

                            </div><!--slablist_wrap-->

                            <div class=" d-flex flex-wrap w-100 mt-2">
                              <div class="d-flex align-items-center w-auto mr-0 mr-sm-3 mb-2 mb-sm-0 slablist-above">
                                Above<asp:CustomValidator ID="CustomValidatorPopup1" runat="server" ControlToValidate="txtPopupDynamicAboveKG" ValidationGroup="CreateRule" SetFocusOnError="true"
                ErrorMessage="Required input" ClientValidationFunction="validateInput" ValidateEmptyText="true" ForeColor="Red" Text="*"></asp:CustomValidator> 
                                <asp:TextBox ID="txtPopupDynamicAboveKG" ValidationGroup="CreateRule" runat="server" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center slablist-above-kg" Enabled="false" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox> 
                                   kg
                              </div>
                              <div class="d-flex align-items-center w-auto">
                                <asp:CustomValidator ID="CustomValidator29" runat="server" ControlToValidate="txtPopupDynamicAboveKGVal" ValidationGroup="CreateRule" SetFocusOnError="true"
                ErrorMessage="Required input" ClientValidationFunction="validateInput" ValidateEmptyText="true" ForeColor="Red" Text="*"></asp:CustomValidator> 
                                <%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %><asp:TextBox ID="txtPopupDynamicAboveKGVal" ValidationGroup="CreateRule" runat="server" CssClass="form-control wd-55 mx-1 ht-20 py-0 text-center slablist-above-val" MaxLength="5" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"></asp:TextBox> 
                                   
                              </div>
                            </div><!--col-12-->
                          </div><!--col-12-->                          
                            <div class="invalid-feedback valsummary">Please provide required input.</div>


</p>

            <button type="button" id="modaldelisettingspopup_save" class="btn btn-success pd-x-25" data-dismiss="modal" aria-label="Close">Save</button>
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div><!-- modal -->




<script type="text/javascript">
    function saverules() {
        $('input:disabled').each(function () {
            $(this).removeAttr('disabled');
        });
    }

    $('a.zoneSettings').on('click', function (e) {
        var hidid = $(this).attr('hidid');
        hidval = $('#' + hidid).val();
        $('#modaldelisettingspopup').find('.slablist_wrap .slablist-item').each(function () {
            $(this).remove();
        });
        var valueRow = $('#modaldelisettingspopup').find('.slablist-row');
        var type = $(valueRow).find('.dynamic-type');

        $(type).find(":selected").text('First');
        $(type).find(":selected").val('First');
        $('#<%= txtPopupDynamicAboveKG.ClientID %>').val('');
        $('#<%= txtPopupDynamicAboveKGVal.ClientID %>').val('');

        $('#<%= hidPopupDynamic.ClientID %>').val(hidval);
        loadDynamicValList(valueRow, "2");
        $('#modaldelisettingspopup_save').attr('hidid', hidid);

        $('#modaldelisettingspopup').find('.is-invalid').removeClass('is-invalid');
        $('#modaldelisettingspopup .modalcustomtittle').text($(this).text() + ' Zone Rates');
        $('#modaldelisettingspopup').modal({ backdrop: 'static', keyboard: false }, 'show');
    });

    $('#modaldelisettingspopup_save').on('click', function (e) {
        var isValid = true; var isEmpty = false;
        var hidval = $('#<%= hidPopupDynamic.ClientID %>').val();
        var aboveKg = $('#<%= txtPopupDynamicAboveKG.ClientID %>');
        var aboveKgVal = $('#<%= txtPopupDynamicAboveKGVal.ClientID %>');

        var km =  $('#modaldelisettingspopup').find('input.dynamic-km');
        var val = $('#modaldelisettingspopup').find('input.dynamic-val');
        var hidid = $(this).attr('hidid');

        isValid = (hidval != '');
        if (!isValid) {
            isEmpty = true;
            isValidDelInput(km);
            isValidDelInput(val);
        }

        if (isValid) {
            isValid = (isValidDelInput(aboveKg) && isValid);
            isValid = (isValidDelInput(aboveKgVal) && isValid);
        }
        if (!isValid) {
            if (isEmpty && confirm('You have not added any rule for this zone. The particular zone will be deactivated if you continue with saving. Are you sure you want to continue?')) {
                $('#' + hidid).val('');
                $('a[hidid="' + hidid + '"]').removeClass('btn-primary').addClass('btn-light');
                return true;
            }
            else {
                e.preventDefault();
                e.stopImmediatePropagation();
                Toastify({ text: 'Invalid input or empty data.', duration: 5000, stopOnFocus: true, className: 'danger' }).showToast();

                return false;
            }
        }
        if (hidval != '') {
            hidval += '|' + 'Above' + ',' + $(aboveKg).val() + ',' + $(aboveKgVal).val();
            $('a[hidid="' + hidid + '"]').removeClass('btn-light').addClass('btn-primary');
        }

        $('#' + hidid).val(hidval);
    });

    function validateInput(source, args) {
        var validator = document.getElementById(source.id);
        var textBox = document.getElementById(validator.controltovalidate);
        var parentDiv = $(textBox).closest('.type_of_delivery');
        $(parentDiv).find('div.valsummary').addClass('hide');

        $(textBox).removeClass('is-invalid');
        var isValid = true;
        var isParentDisabled = $(textBox).closest('.type_delivery_dteails').hasClass('disabled')
        if ($(textBox).is(":visible") && !isParentDisabled) {
            var inputVal = args.Value;
            // Check if the value is a number and >= than 0
            isValid = !isNaN(inputVal) && inputVal != '' && inputVal >= 0;
            args.IsValid = isValid;
            if(!isValid)
                $(textBox).addClass('is-invalid');
        }

        if(!isValid){
            $(parentDiv).find('div.valsummary').removeClass('hide');
            $(parentDiv).find('div.valsummary').show();
        }
    }

    $(document).ready(function () {
        $('.slablist-row').each(function () {
            loadDynamicValList($(this), $(this).attr('slabtype'));
        });
    });

    $('.dynamic-add').on('click', function () {
        var valueRow = $(this).closest('.slablist-row');
        var type = $(valueRow).find('.dynamic-type');
        var slabtype = $(valueRow).attr('slabtype');
        var hidid = $(valueRow).closest('.slablist_wrap').attr('hidid');
        var km = $(valueRow).find('input.dynamic-km');
        var val = $(valueRow).find('input.dynamic-val');
        //if (!isValidDelInput(type))
        //    return false;
        if (!isValidDelInput(km))
            return false;
        if (!isValidDelInput(val))
            return false;

        var dynamicValues = $('#' + hidid).val();
        if (!dynamicValues)
            dynamicValues = '';
        //var isDuplicate = false;

        //$.each(dynamicValues.split('|'), function (i) {
        //    var valElement = dynamicValues.split('|')[i].split(',');
        //    if (valElement[0] == $(type).find(":selected").val() && valElement[1] == $(km).val()) { // } && valElement[2] == $(val).val()) {
        //        isDuplicate = true;
        //        return;
        //    }
        //});
        //if (isDuplicate) {
        //    $(km).addClass('is-invalid');
        //    $(val).addClass('is-invalid');
        //    Toastify({text: 'Duplicate value', duration: 5000, stopOnFocus: true, className: 'danger'}).showToast();
        //    return;
        //}

        dynamicValues += (dynamicValues == '' ? '' : '|') + $(type).find(":selected").val() + ',' + $(km).val() + ',' + $(val).val();
        $('#' + hidid).val(dynamicValues);
        loadDynamicValList(valueRow, slabtype);
        if (slabtype == "2") {
            $(type).find(":selected").text('Next');
            $(type).find(":selected").val('Next');
        }
        $(km).val(''); $(val).val('');
    });
    function isValidDelInput(obj) {
        if ($(obj).val() == '') {
            $(obj).addClass('is-invalid');
            return false;
        }
        else {
            $(obj).removeClass('is-invalid');
        }
        return true;
    }
    function loadDynamicValList(valueRow, slabtype) {
        var hidid = $(valueRow).closest('.slablist_wrap').attr('hidid');
        var valArray = $('#'+hidid).val().split('|');
        var template = '<div class="slablist p-1 p-sm-2 border mb-1 slablist-item"><div class="d-flex align-items-baseline align-items-sm-center flex-wrap flex-sm-nowrap"><div class="d-flex align-items-center mr-2 mr-sm-3 mb-0 mb-sm-0"><input class="form-control wd-65 mx-1 ht-20 py-0 text-center dynamic-type" value="[TYPE]" disabled="disabled"><input id="txtCourierDynamicKm" name="dynamicval" class="form-control wd-50 mx-1 ht-20 py-0 text-center dynamic-km" maxlength="5" type="text" value="[KM]" disabled="disabled"> Km</div><div class="d-flex align-items-center"><input class="form-control wd-50 mx-1 ht-20 py-0 text-center dynamic-val" maxlength="5" type="text" value="[VAL]" disabled="disabled"> <%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %>/ Km</div><div class="addcharge ml-2 ml-sm-3"><a href="javascript:void(0)" onclick="if(confirm(\'Are you sure you want to delete this rule?\')) deleteDynamicVal(this)" class="bg-transparent btn p-1 delcharge_btn d-flex align-items-center justify-content-center dynamic-add"><i class="fa-regular fa-remove tx-16"></i></a></div></div></div>';
        if (slabtype && slabtype == "2")
            template = '<div class="slablist p-1 p-sm-2 border mb-1 slablist-item"><div class="d-flex align-items-baseline align-items-sm-center flex-wrap flex-sm-nowrap"><div class="d-flex align-items-center mr-2 mr-sm-3 mb-0 mb-sm-0"><input class="form-control wd-65 mx-1 ht-20 py-0 text-center dynamic-type" value="[TYPE]" disabled="disabled"><input id="txtCourierDynamicKm" name="dynamicval" class="form-control wd-50 mx-1 ht-20 py-0 text-center dynamic-km" maxlength="5" type="text" value="[KM]" disabled="disabled"> Kg</div><div class="d-flex align-items-center"><%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %><input class="form-control wd-50 mx-1 ht-20 py-0 text-center dynamic-val" maxlength="5" type="text" value="[VAL]" disabled="disabled"></div><div class="addcharge ml-2 ml-sm-3"><a href="javascript:void(0)" onclick="if(confirm(\'Are you sure you want to delete this rule?\')) deleteDynamicVal(this)" slabtype="2" class="bg-transparent btn p-1 delcharge_btn d-flex align-items-center justify-content-center dynamic-add"><i class="fa-regular fa-remove tx-16"></i></a></div></div></div>';

        $(valueRow).parent().find('.slablist-item').remove();
        $.each(valArray, function (i) {
            if (valArray[i] && valArray[i] != '') {
                var valElement = valArray[i].split(',');
                var type = valElement[0]; var km = valElement[1]; var val = valElement[2];
                if (km && val && km != '' && val != '') {
                    if (slabtype == "2" && valElement[0] == 'Above') {
                        $(valueRow).closest('.slablist_wrap').next().find('input.slablist-above-kg').val(valElement[1]);
                        $(valueRow).closest('.slablist_wrap').next().find('input.slablist-above-val').val(valElement[2]);
                        //$('#<%= txtPopupDynamicAboveKG.ClientID %>').val(valElement[1]);
                        //$('#<%= txtPopupDynamicAboveKGVal.ClientID %>').val(valElement[2]);
                    }
                    else {
                        var element = template.replace('[TYPE]', valElement[0]).replace('[KM]', valElement[1]).replace('[VAL]', valElement[2]);
                        $(valueRow).closest('.slablist_wrap').children().last().before(element);
                    }
                }
            }
        });
        if (slabtype == "2") {
            var type = $(valueRow).find('.dynamic-type');
            $(type).find(":selected").text(($(valueRow).closest('.slablist_wrap').find('.slablist-item').length > 0 ? 'Next' : 'First'));
            $(type).find(":selected").val(($(valueRow).closest('.slablist_wrap').find('.slablist-item').length > 0 ? 'Next' : 'First'));

            $(valueRow).closest('.slablist_wrap').next().find('input.slablist-above-kg').prop('disabled', true);

            $(valueRow).closest('.slablist_wrap').find('.slablist-item').not(":last").find('div.addcharge').hide();
            var lastkg = $(valueRow).closest('.slablist_wrap').find('.slablist-item:last').find('input.dynamic-km').val();
            if (lastkg && !isNaN(lastkg))
                $(valueRow).closest('.slablist_wrap').next().find('.slablist-above-kg').val(lastkg);
        }
    }
    function deleteDynamicVal(obj) {
        var valueRow = $(obj).closest('.slablist_wrap').find('.slablist-row');

        var selectedRow = $(obj).closest('.slablist-item');
        var hidid = $(selectedRow).closest('.slablist_wrap').attr('hidid');
        var parentRow = $(selectedRow).parent();
        var slabtype = $(obj).attr('slabtype');
        $(obj).closest('.slablist_wrap').next().find('.slablist-above-kg').val('');
        $(obj).closest('.slablist_wrap').next().find('.slablist-above-val').val('');

        $(selectedRow).remove();

        var valRows = $(parentRow).find('.slablist-item');
        var dynamicValues = '';
        $.each(valRows, function (i) {
            if ($(valRows[i]) != $(selectedRow))
                dynamicValues += (dynamicValues == '' ? '' : '|') + $(valRows[i]).find('input.dynamic-type').val() + ',' + $(valRows[i]).find('input.dynamic-km').val() + ',' + $(valRows[i]).find('input.dynamic-val').val();
        });
        $('#' + hidid).val(dynamicValues);

        loadDynamicValList(valueRow, slabtype);
    }

</script>

<script type="text/javascript">
    $('.check_delivery_type').on('change', function () { 
        if ($(this).is(':checked')) {
            $(this).closest('.type_of_delivery').find('div.deliveryDteails').removeClass('disabled');
            $(this).closest('.type_of_delivery').find('div.DeliveryChargeManual').removeClass('hide');
            if($(this).closest('.type_of_delivery').find('input.DeliByStore'))
                $(this).closest('.type_of_delivery').find('input.DeliByStore').prop("checked",true).trigger('change');
            $(this).closest('.type_of_delivery').find('input.chargetypeswitch[value=Fixed]').prop("checked",true).trigger('change');
        }
        else { 
            $(this).closest('.type_of_delivery').find('div.deliveryDteails').addClass('disabled');
            $(this).closest('.type_of_delivery').find('div.DeliveryChargeManual').addClass('hide');
            $(this).closest('.type_of_delivery').find('.rdDeliBy').prop("checked", false).trigger('change');
        }

    });
    $('.rdDeliBy').on('change', function () {
        if ($(this).val()=='store') {
            $(this).closest('.type_of_delivery').find('div.DeliveryCharge').removeClass('hide');
        }
        else {
            $(this).closest('.type_of_delivery').find('div.DeliveryCharge').addClass('hide');
        }

    });
    $('.chargetypeswitch').on('change', function () {
        if ($(this).val() =='Fixed') {
            $(this).closest('.DeliveryCharge, .DeliveryChargeManual').find('div.fixedharges').removeClass('hide');
            $(this).closest('.DeliveryCharge, .DeliveryChargeManual').find('div.Dynamiccharges, div.Weightcharges, div.ZoneWeightCharges').addClass('hide');
        }
        else if ($(this).val() == 'Weight') {
            $(this).closest('.DeliveryCharge, .DeliveryChargeManual').find('div.Weightcharges').removeClass('hide');
            $(this).closest('.DeliveryCharge, .DeliveryChargeManual').find('div.Dynamiccharges, div.fixedharges, div.ZoneWeightCharges').addClass('hide');
        }
        else if ($(this).val() == 'ZoneWeight') {
            $(this).closest('.DeliveryCharge, .DeliveryChargeManual').find('div.ZoneWeightCharges').removeClass('hide');
            $(this).closest('.DeliveryCharge, .DeliveryChargeManual').find('div.Dynamiccharges, div.Weightcharges, div.fixedharges').addClass('hide');
        }
        else {
            $(this).closest('.DeliveryCharge, .DeliveryChargeManual').find('div.Dynamiccharges').removeClass('hide');
            $(this).closest('.DeliveryCharge, .DeliveryChargeManual').find('div.fixedharges, div.Weightcharges, div.ZoneWeightCharges').addClass('hide');
        }

    });


    // 

</script>
</asp:Content>