var orderMaster = function () {
    var orderMethods = {}, orderPrivateMethods = {};
    orderMethods.events = {};

    orderMethods.properties = {
        showProgress: true,
        orderId: -1,
        canReturn: false
    };
    orderPrivateMethods.properties = {
        timer: null
    };
    orderMethods.url = {
        getStatus: '/orderstatus',
        cancelOrder: '/cancel-order',
        cancelRedirect: '',
        returnables: '/orderreturanables',
        return: '/orderreturanables/return'
    };

    orderPrivateMethods.controls = {
        //addProduct: '.btn-add-product-cart',
        //cartContainer: '#cart-container'
        returnables: '#OrderReturnModal'
    };

    orderPrivateMethods.currentPosition = function (index) {
        var pos = -1;
        if ($.inArray(index, [3, 4, 5]) >= 0)
            pos = 0;
        else if ($.inArray(index, [6, 7, 8, 23, 51]) >= 0)
            pos = 1;
        else if ($.inArray(index, [9, 10, 11, 12, 13, 14, 16]) >= 0)
            pos = 2;
        else if ($.inArray(index, [20, 15]) >= 0)
            pos = 3;
        else if ($.inArray(index, [17, 18]) >= 0)
            pos = 5;

        return pos;
    };

    orderPrivateMethods.updateProgress = function (pos) {
        //var pos = orderPrivateMethods.currentPosition(index);
        if (pos >= 0) {
            $('ul.order-processing_wrap li').removeClass('odr_processing');//completed
            $('ul.order-processing_wrap li').removeClass('odr_processing');
            $.each($('ul.order-processing_wrap li'), function (key, value) {
                if (key < pos)
                    $(value).addClass('odr_processing');//completed
                else if (key == pos)
                    $(value).addClass('odr_processing ');
            });
        }
    };

    orderPrivateMethods.cancelOrder = function (self) {

        var url = orderMethods.url.cancelOrder + '/' + $(self).attr('orderid');
        onSuccess = function (result) {
            if (result && result.status) {
                if (result.msg) {
                    alert(result.msg);
                }
                else if (result.error && result.error.msg)
                    alert(result.error.msg);
                if (result.status == 'error' && result.error.msg) {
                    $("#cancel-order-wrap").hide();
                }
                else if (result.status == 'ok') {
                    if (orderMethods.url.cancelRedirect != '')
                        window.location.replace(orderMethods.url.cancelRedirect);
                    else
                        window.location.reload(true);
                }
            }
        };

        onError = function (data) {
            console.log(data);
            alert('Error!! Request failed.');
        };
        master.ajax.JSONRequest(url, 'POST', {}, onSuccess, onError);

    }


    orderPrivateMethods.getStatus = function (orderId = 0) {
        var duration = 60000;
        if (orderId > 0) {
            orderMethods.properties.orderId = orderId;
            duration = 5000;
        }
        if (!orderMethods.properties.orderId || orderMethods.properties.orderId < 1)
            return;

        onSuccess = function (result) {
            if (result && result.status_id) {
                if (orderId > 0) {
                    if (result.status_id >= 4) {
                        window.location.href = window.location;
                    } else {
                        setTimeout(() => orderPrivateMethods.getStatus(orderId), duration);
                    }
                } else {
                    var pos = orderPrivateMethods.currentPosition(result.status_id);
                    orderPrivateMethods.updateProgress(pos);
                    if (pos >= 0 && pos < 4)
                        orderPrivateMethods.properties.timer = setTimeout(orderPrivateMethods.getStatus, duration);
                    else if (orderPrivateMethods.properties.timer)
                        clearTimeout(orderPrivateMethods.properties.timer);
                }
              
            }
        };

        onError = function (data) {
            console.log(data);
            orderPrivateMethods.properties.timer = setTimeout(orderPrivateMethods.getStatus, 60000);
        };
        master.ajax.JSONRequest(orderMethods.url.getStatus + '/' + orderMethods.properties.orderId, 'GET', {}, onSuccess, onError);

    }

    orderPrivateMethods.getItemsReturn = function () {
        if (!orderMethods.properties.orderId || orderMethods.properties.orderId < 1)
            return;

        onSuccess = function (data) {
            if (data && data.length > 0) {
                var div_data = "";
                $(orderPrivateMethods.controls.returnables).find('ul.itemsummarylist').html('');
                var template = '<li [DISABLEDCLASS]><span class="sumadiscrip" style="width:50%"><input class="form-check-input mt-0 mr-2 float-start" style="margin-right: 5px;" type="checkbox" value="" itemid="[ITEMID]" id="Checkbox_[ITEMID]"><div class="pro_img float-start"><img alt="" src="[IMAGEURL]"></div><span class="w-100">&amp; [SKU]</span><p class="m-0 lh-1 mt-1"><small>[RETURNPERIODTEMPLATE]</small></p></span><span class="sumaitemqty" style="width:10%">[ITEMORDERQTY]</span><span class="sumaitemprc" style="width:20%">[AMT]</span><span class="sumaitmtotl" style="width:20%">[TOTAL]</span></li>';
                $.each(data, function (k, v) {
                    var hasReturnRequested = v.item_return_qty_requested;
                    var strikeOut = (hasReturnRequested > 0 && v.item_order_qty == hasReturnRequested);

                    var li_attr = (v.item.stit_itemReturnTime > 0 && !strikeOut ? '' : ' class="disabled"');
                    var return_period = (v.item.stit_itemReturnTime > 0 ? 'Return possible within <strong>[RETURNPOSIBLEDAYS]</strong> Days' : 'Return Period is Over');
                    if (hasReturnRequested > 0)
                        return_period = 'Return requested for quantity: ' + hasReturnRequested;
                    var orderqty = v.item_order_qty_scanned; var returnQty = v.item_return_qty_requested;
                    var itemReturns = orderqty - returnQty;
                    var qtInput = '<input name="Qty" type="number" max="' + itemReturns + '" min="0" value="' + itemReturns + '" id="Qty_' + v.item_id + '" class="text-center w-100" oninput="this.value = this.value.replace(/[^0-9.]/g, \'\').replace(/(\..*)\./g, \'$1\');" required="">';
                    div_data += template.replaceAll('[ITEMORDERQTY]', qtInput).replaceAll('[DISABLEDCLASS]', li_attr).replaceAll('[RETURNPERIODTEMPLATE]', return_period).replaceAll('[ITEMID]', v.item_id)
                        .replaceAll('[IMAGEURL]', v.image.image_url).replaceAll('[SKU]', v.item.stit_sku)
                        .replaceAll('[RETURNPOSIBLEDAYS]', v.item.stit_itemReturnTime,).replaceAll('[AMT]', v.item_retail_price).replaceAll('[TOTAL]', v.item_price);
                });
                $(orderPrivateMethods.controls.returnables).find('ul.itemsummarylist').html(div_data);

            }
        };

        onError = function (data) {
            console.log(data);
        };
        master.ajax.JSONRequest(orderMethods.url.returnables + '/' + orderMethods.properties.orderId, 'GET', {}, onSuccess, onError);

    }
    orderMethods.getPaymentStatus = function (orderId) {
        orderPrivateMethods.getStatus(orderId);
    }
    orderMethods.initializePage = function () {
        orderMethods.events.initialize();
    };

    orderMethods.events.initialize = function () {
        $('.btncancelorder,.cancel-order').unbind('click').on('click', function (e) {
            e.stopImmediatePropagation();
            e.stopPropagation();
            var self = $(this);
            if (confirm('Are you sure you want to cancel this order?'))
                orderPrivateMethods.cancelOrder(self);

            return false;
        });
        $(orderPrivateMethods.controls.returnables).on('shown.bs.modal', function (e) {
            orderPrivateMethods.getItemsReturn();
        });

        $(orderPrivateMethods.controls.returnables).find('#btnreturnproceed').unbind('click').on('click', function () {
            var orderItems = [];
            $(orderPrivateMethods.controls.returnables).find('ul.itemsummarylist li').each(function () {
                var chk = $(this).find('input[type=checkbox]');
                var qtyInput = $(this).find('input[name=Qty]');
                var qty = 1;
                if (qtyInput && $(qtyInput).val() != '')
                    qty = $(qtyInput).val();
                if (!qty)
                    qty = 1;
                var _ordid = ''; var _itemid = 0;
                if ($(chk).is(":checked")) {
                    _ordid = orderMethods.properties.orderId;
                    _itemid = $(chk).attr('itemid');
                    orderItems.push({ item_id: _itemid, item_order_qty: qty, item_order_id: ""+_ordid });
                }
            });
            if (orderItems.length <= 0) {
                alert('No item selected');
                return;
            }
            if (!confirm('Are you sure you want to return the selected items?'))
                return;

            onSuccess = function (result) {
                if (result.status <= 0) {
                    alert(result.message);
                    return;
                }
                alert(result.message);
                window.location.reload();
            };

            onError = function (data) {
                alert('Failure! There is a technical error occurred. Please contact support for more details.');
                console.log(data);
            };
            master.ajax.JSONRequest(orderMethods.url.return, 'POST', orderItems, onSuccess, onError);



        });

        const bars = document.querySelectorAll(".round-time-bar");
        bars.forEach((bar) => {
            bar.classList.remove("round-time-bar");
            bar.offsetWidth;
            bar.classList.add("round-time-bar");
        });

        if (orderMethods.properties.showProgress) {
            orderPrivateMethods.getStatus();
        }

        $('#btnpodpaynow').unbind('click').on('click', function (e) {
            e.preventDefault();
            var Checkout = {
                PaymentMethod: 2,//$(frm).find('#PaymentMethod').val(),
                TimeSlote: undefined,
                OrderId: $(this).attr('orderid'),
                CustomerId: undefined,
                OrderNum: "",
                NetAmount: undefined,
                UseWallet: false,
                CouponCode: "",
                OrderGroupId: $(this).attr('ordgroup'),
                IsPodToOnline: 1
            };
            master.getPaymentPage(Checkout, 1);
            return false;
        });


    };

    return orderMethods;
}();

$(function () {
    orderMaster.initializePage();
});