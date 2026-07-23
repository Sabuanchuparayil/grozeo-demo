<%@ Page Language="C#" MasterPageFile="~/Business/BusinessMaster.master" Title="Manage Delivery Rates" AutoEventWireup="true" CodeBehind="DeliveryRate.aspx.cs" Inherits="RetalineProAgent.Business.DeliveryRate" %>
<%@ Register Src="~/Controls/ctrlDeliveryRule.ascx" TagPrefix="uc1" TagName="ctrlDeliveryRule" %>

<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">Manage Delivery Rates</h6>
        <p class="mb-0">Manage delivery rates for area</p>
    </div>
    
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNhead" runat="server">
    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
     <div class="card">
            <div class="card-body p-0 rounded-0 bg-transparent">
              <div class="row row-sm">
                <div class="col-12 delivery_rules_wrap">

                    <asp:PlaceHolder ID="plcDeliRulesControls" runat="server">
                     <uc1:ctrlDeliveryRule runat="server" id="ctrlDeliveryRuleHL" RuleTypeid="2" DeliveryRuleFor="2" DeliveryRuleTypeID="2" DeliveryRuleType="Hyperlocal Delivery" DeliveryRuleWeight="10" />
                     <uc1:ctrlDeliveryRule runat="server" id="ctrlDeliveryRuleLoc" RuleTypeid="3" DeliveryRuleFor="2" DeliveryRuleTypeID="3" DeliveryRuleType="Scheduled Local Delivery" DeliveryRuleWeight="25" />
                     <uc1:ctrlDeliveryRule runat="server" id="ctrlDeliveryRuleLong" RuleTypeid="4" DeliveryRuleFor="2" DeliveryRuleTypeID="4" DeliveryRuleType="Local Last Mile Delivery" DeliveryRuleWeight="10" />

                 </asp:PlaceHolder>


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


<script type="text/javascript">
    function saverules() {
        $('input:disabled').each(function () {
            $(this).removeAttr('disabled');
        });
    }


    function validateemptyzone(source, args) {
        var isValid = true;
        if ($(source).closest('div').is(':visible')) {
            var hasAllValues = true;
            $(source).closest('div').find('a.zoneSettings').each(function () {
                var hidid = $(this).attr('hidid');
                if (hidid != '' && $('#' + hidid).val() == '')
                    hasAllValues = false;
            });
            isValid = hasAllValues;
        }
        args.IsValid = isValid;
    }

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


    $('.dynamic-input').on('change', function (e) {
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
        var valArray = dynamicValues.split('|');
        // var lastRow = valArray[valArray.length - 1];
        var curData = $(type).find(":selected").val() + ',' + $(km).val() + ',' + $(val).val();
        valArray[valArray.length - 1] = curData;
        dynamicValues = valArray.join('|');
        $('#' + hidid).val(dynamicValues);

        var totalKg = 0; var indexvalue = 0;
        $.each(valArray, function (i) {
            if (valArray[i] && valArray[i] != '') {
                var valElement = valArray[i].split(',');
                var type = valElement[0]; var km = valElement[1]; var val = valElement[2];
                if (km && val && km != '' && val != '') {
                    if (!(slabtype == "2" && valElement[0] == 'Above')) {
                        indexvalue = parseInt(valElement[1]);
                if (indexvalue && !isNaN(indexvalue))
                        totalKg += indexvalue;
                    }
                }
            }
        });
        if (slabtype == "2")
            $(valueRow).closest('.slablist_wrap').next().find('input.slablist-above-kg').val(totalKg);


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

</script>

<script type="text/javascript">
    $('.check_delivery_type').on('change', function () {
        if ($(this).is(':checked')) {
            $(this).closest('.type_of_delivery').find('div.deliveryDteails').removeClass('disabled');
            $(this).closest('.type_of_delivery').find('div.DeliveryChargeManual').removeClass('hide');
            if ($(this).closest('.type_of_delivery').find('input.DeliByStore'))
                $(this).closest('.type_of_delivery').find('input.DeliByStore').prop("checked", true).trigger('change');
            $(this).closest('.type_of_delivery').find('input.chargetypeswitch[value=Fixed]').prop("checked", true).trigger('change');
        }
        else {
            $(this).closest('.type_of_delivery').find('div.deliveryDteails').addClass('disabled');
            $(this).closest('.type_of_delivery').find('div.DeliveryChargeManual').addClass('hide');
        }

    });
    $('.chargetypeswitch').on('change', function () {
        if ($(this).val() == 'Fixed') {
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