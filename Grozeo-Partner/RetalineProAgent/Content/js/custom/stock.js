var stock = function () {
    var stockMethods = {}, stockPrivateMethods = {};

    stockMethods.events = {};

    stockMethods.properties = {};
    stockPrivateMethods.properties = { };
    stockMethods.url = {
        validateMargin: '/api/home/ValidateMargin'
    };

    stockPrivateMethods.controls = {
        margininput: '.idiscountsp'
    };


    stockPrivateMethods.validateMargin = function (obj) {
        var pid = $(obj).attr('pid');
        var val = $(obj).val();
        var brId = $(obj).attr('brId');
        if (!(val > 0))
            return;

        onSuccess = function (data) {
            
            $(obj).parent().find('p.errorsmg').remove(); 
            //$('.homeloading').removeClass('processing_loader');
            if (data && data.status === 'Success') {
                /*$(obj).after('<p class="errorsmg">Success</p>')*/
            }
            else {
                $(obj).after('<p class="errorsmg" style="color:red; font-size:9px; margin-bottom:0px; margin-top:3px; position: absolute; margin-left:-6px; ">Check Discount SP</p>');
            }
        };

        onError = function (data) {
            console.log(data);
            
            //$('.processing_loader').removeClass('processing_loader');
        };
        retMaster.ajax.JSONRequest(stockMethods.url.validateMargin, 'POST', { pid: pid, amt: val, brId: brId }, onSuccess, onError);

    }


    stockMethods.initializePage = function () {
        stockMethods.events.initialize();
    };

    stockMethods.events.initialize = function () {
        $(document).ready(function () {
            $(stockPrivateMethods.controls.margininput).unbind("change").on("change", function () {
                stockPrivateMethods.validateMargin($(this));
            });

        });

    };



    return stockMethods;
}();
$(function () {
    stock.initializePage();
});