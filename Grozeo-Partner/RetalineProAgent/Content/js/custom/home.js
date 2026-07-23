var home = function () {
    var homeMethods = {}, homePrivateMethods = {}, homeALMethods = {};

    homeMethods.events = {};

    homeMethods.properties = {
        captchaKey: '',
    };
    homePrivateMethods.properties = {
        isTimerTriggered: false,
        tmrExecutor: null
    };
    homeMethods.url = {
        getDashboard: '',
        pendingOrders: '',
        pendingActions: '',
        captchaVerification: '',
        publicsite:''
    };

    homePrivateMethods.controls = {
        tblpendingorders: '#tblpendingorders',
        ifpublic: '#ifpublicsite'
    };

    homeMethods.ALGateway = {};

    homeALMethods.urls = {
        ALGatewayController: '',
    };

    homeALMethods.controls = {
        delayedOrders: '',
        totalOrders: ''
    }

    homeALMethods.updateOrderTotalAggregate = function () {
        console.log("updateOrderTotalAggregate");
        if (!homeALMethods.urls.ALGatewayController || homeALMethods.urls.ALGatewayController == '')
            return;
 
        onSuccess = function (data) {
            console.log('Success in updateOrderTotalAggregate');
            //$('#' + homeALMethods.controls.totalOrders).html(data.data.totalOrders);
            $('#' + homeALMethods.controls.delayedOrders).html(data.data.delayedOrders);
        };
        onError = function (data) {
            console.log('Error in updateOrderTotalAggregate');
                console.log(data);
            };

        const store_data = {
            storeGroupID: window.CurrentUser.APIStoreId,
            branchIDs: []
        };

        retMaster.ajax.JSONRequest(homeALMethods.urls.ALGatewayController, 'POST',store_data, onSuccess, onError);
    }

    homeMethods.ALGateway.controls = homeALMethods.controls;
    homeMethods.ALGateway.urls = homeALMethods.urls;

    homeMethods.controls = {
        orders: '',
        products: '',
        createdorderpicker: '',
        orderpickers: '',
        createddrivers: '',
        drivers: '',
        totalOrders: '',
        totalsales: '',
        totalcustomers: '',
        pendingaction: '',
        outofstock: ''

    };

    homePrivateMethods.getDashboardValues = function () {
        console.log("getDashboardValues");
        if (!homeMethods.url.getDashboard || homeMethods.url.getDashboard == '')
            return;

        //$(self).addClass('processing_loader');
        //$('.homeloading').addClass('processing_loader');
        onSuccess = function (data) {
            //$('.homeloading').removeClass('processing_loader');
            if (data && data.status === 'Success' && data.data) {
                var orders = $('#' + homeMethods.controls.orders).html();
                $('#' + homeMethods.controls.orders).html(data.data.neworders);
                $('#' + homeMethods.controls.products).html(data.data.forsale);
                $('#' + homeMethods.controls.outofstock).html(data.data.outOfStock);
                $('#' + homeMethods.controls.totalOrder).html(data.data.totalorders);
                $('#' + homeMethods.controls.totalSale).html(data.data.totalsales);
                $('#' + homeMethods.controls.ttlCustomers).html(data.data.totalcustomers);
                //var oPick = '<h3>' + data.data.onlineOrderPickers + '</h3><span class="dsb_card_dataerrormsg">Order Pickers Not Created.</span><label class="mb-0 ml-2 tx-gray-500 tx-12">Outof ' + data.data.orderpickers + '</label>';
                //$('#' + homeMethods.controls.orderpickers).html(oPick);

                var oPick;
                if (data.data.orderpickers === 0) {
                    oPick = '<h3>' + data.data.orderpickers + '</h3><span class="dsb_card_dataerrormsg">Order Picker/s Are Not Created.</span>';
                } else if (data.data.orderpickers !== 0 && data.data.onlineOrderPickers === 0) {
                    oPick = '<h3>' + data.data.onlineOrderPickers + '</h3><span class="dsb_card_dataerrormsg">Order Picker/s Are Not Online.</span><label class="mb-0 ml-2 tx-gray-500 tx-12">';
                } else if (data.data.orderpickers !== 0 && data.data.onlineOrderPickers !== 0) {
                    oPick = '<h3>' + data.data.onlineOrderPickers + '</h3><label class="mb-0 ml-2 tx-gray-500 tx-12" > Outof ' + data.data.orderpickers + '</label >'; 
                }
                $('#' + homeMethods.controls.orderpickers).html(oPick);

                //var DriverOnline = '<h3>' + data.data.onlineVehicles + '</h3><span class="dsb_card_dataerrormsg">You don\'t have delivery staff.</span><label class="mb-0 ml-2 tx-gray-500 tx-12">Outof ' + data.data.drivers + '</label>';
                //$('#' + homeMethods.controls.drivers).html(DriverOnline);

                var DriverOnline;
                if (data.data.drivers === 0) {
                    DriverOnline = '<h3>' + data.data.drivers + '</h3><span class="dsb_card_dataerrormsg">Driver/s Are Not Created.</span>';
                } else if (data.data.drivers !== 0 && data.data.onlineVehicles === 0) {
                    DriverOnline = '<h3>' + data.data.onlineVehicles + '</h3><span class="dsb_card_dataerrormsg">Driver/s Are Not Online.</span><label class="mb-0 ml-2 tx-gray-500 tx-12">';
                } else if (data.data.drivers !== 0 && data.data.onlineVehicles !== 0) {
                    DriverOnline = '<h3>' + data.data.onlineVehicles + '</h3><label class="mb-0 ml-2 tx-gray-500 tx-12" > Outof ' + data.data.drivers + '</label >';
                }
                $('#' + homeMethods.controls.drivers).html(DriverOnline);


                $('#' + homeMethods.controls.orderpickers).closest('.Dashboard_widgets').removeClass('noDashboardContent');
                if (!(data.data.onlineOrderPickers > 0))
                    $('#' + homeMethods.controls.orderpickers).closest('.Dashboard_widgets').addClass('noDashboardContent');

                $('#' + homeMethods.controls.drivers).closest('.Dashboard_widgets').removeClass('noDashboardContent');
                if (!(data.data.onlineVehicles > 0))
                    $('#' + homeMethods.controls.drivers).closest('.Dashboard_widgets').addClass('noDashboardContent');


                $('#' + homeMethods.controls.orders).closest('.Dashboard_widgets').removeClass('noDashboardContent');
                if (!(data.data.neworders > 0))
                    $('#' + homeMethods.controls.orders).closest('.Dashboard_widgets').addClass('noDashboardContent');

                $('#' + homeMethods.controls.totalOrder).closest('.Dashboard_widgets').removeClass('noDashboardContent');
                if (!(data.data.totalorders > 0))
                    $('#' + homeMethods.controls.totalOrder).closest('.Dashboard_widgets').addClass('noDashboardContent');

                $('#' + homeMethods.controls.totalSale).closest('.Dashboard_widgets').removeClass('noDashboardContent');
                if (!(data.data.totalsales > 0))
                    $('#' + homeMethods.controls.totalSale).closest('.Dashboard_widgets').addClass('noDashboardContent');

                $('#' + homeMethods.controls.ttlCustomers).closest('.Dashboard_widgets').removeClass('noDashboardContent');
                if (!(data.data.totalcustomers > 0))
                    $('#' + homeMethods.controls.ttlCustomers).closest('.Dashboard_widgets').addClass('noDashboardContent');

                // data.data.forsale
                $('#' + homeMethods.controls.products).closest('.widgets_Products_sale').removeClass('products_warning');
                if (!(data.data.forsale > 0))
                    $('#' + homeMethods.controls.products).closest('.widgets_Products_sale').addClass('products_warning');

                // data.data.outOfStock
                $('#' + homeMethods.controls.outofstock).closest('.widgets_Products_sale').removeClass('products_warning');
                if (!(data.data.forsale > 0))
                    $('#' + homeMethods.controls.outofstock).closest('.widgets_Products_sale').addClass('products_warning');

                if (orders != $('#' + homeMethods.controls.orders).html())
                    homePrivateMethods.getPendingOrders();
                //$('#' + homeMethods.controls.totalOrder).html(data.data.totalorders);
                //$('#' + homeMethods.controls.totalSale).html(data.data.totalsales);
                //$('#' + homeMethods.controls.ttlCustomers).html(data.data.totalcustomers);
            }

            // Run once immediately
            //homeALMethods.updateOrderTotalAggregate();

            // Then every 5 minutes
            //setInterval(homeALMethods.updateOrderTotalAggregate, 5 * 60 * 1000);

        };

        onError = function (data) {
            console.log(data);
            //$('.processing_loader').removeClass('processing_loader');
        };
        retMaster.ajax.JSONRequest(homeMethods.url.getDashboard, 'POST', { isPendingOrders: 0}, onSuccess, onError);
    }

    homePrivateMethods.getPendingActions = function () {
        if (!homeMethods.url.pendingActions || homeMethods.url.pendingActions == '')
            return;

        //$(self).addClass('processing_loader');
        //$('.homeloading').addClass('processing_loader');
        onSuccess = function (data) {
            //$('.homeloading').removeClass('processing_loader');
            if (data && data.status === 'Success' && data.data) {
                var pendingActions = data.data;
                $('#pendingActionsCount').hide();
                if (pendingActions > 0) {
                    $('#pendingActionsCount').find('#pendingCount').text(pendingActions);
                    $('#pendingActionsCount').show();
                    $('#' + homeMethods.controls.pendingActions).html(data.data.pendingActions);
                }
                else {
                    $('#pendingActionsCount').hide();
                }
           }
        };

        onError = function (data) {
            console.log(data);
            //$('.processing_loader').removeClass('processing_loader');
        };
        retMaster.ajax.JSONRequest(homeMethods.url.pendingActions, 'GET', { }, onSuccess, onError);

    }

    homePrivateMethods.getPendingOrders = function () {
        onSuccess = function (data) {
            //$('.homeloading').removeClass('processing_loader');
            if (data && data.status === 'Success' && data.data) {
                $(homePrivateMethods.controls.tblpendingorders).find('tbody').html('');
                if (data.data.length > 0) {
                    for (var i = 0; i < data.data.length; i++) {
                        //result[i].id = businessTypeId;
                        $(homePrivateMethods.controls.tblpendingorders).find('tbody').append('<tr><td><a class="tx-inverse tx-14 tx-medium d-block" href="/Tenant/orderdetails?ordId=' + data.data[i].orderid + '&toid=' + data.data[i].uid + '&orderid=' + data.data[i].fstoid + '">' + data.data[i].orderNum + '</a><span class=""><i class="fa fa-clock-o"></i> ' + data.data[i].diff + '</span></td><td>' + data.data[i].branchName + '</td><td>' + data.data[i].city + '</td><td style="text-align: right;"><div class="sparkbar" data-color="#00a65a" data-height="20">' + data.data[i].total + '</div></td></tr>');
                        //var row = '<tr><td><a class="tx-inverse tx-14 tx-medium d-block" href="/orderdetails?id=' + data.data[i].orderid + '">' + data.data[i].orderNum + '</a>&nbsp;<small class="badge badge - danger"><i class="far fa - clock"></i> ' + data.data[i].diff + '</small></td><td>' + data.data[i].branchName + '</td><td>' + data.data[i].city + '</td><td style="text-align: right;"><div class="sparkbar" data-color="#00a65a" data-height="20">' + data.data[i].total +'</div></td></tr>';
                    }
                }
                else {
                    $(homePrivateMethods.controls.tblpendingorders).find('tbody').html('<tr><td colspan="4"><div class="text-center"><img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg"><h6 class="mb-3">No record available</h6></div></td></tr>');
                }
            }
        };

        onError = function (data) {
            console.log(data);
            //$('.processing_loader').removeClass('processing_loader');
        };
        retMaster.ajax.JSONRequest(homeMethods.url.pendingOrders, 'POST', { isPendingOrders: 1}, onSuccess, onError);

    }

    homeMethods.initializePage = function () {
        homeMethods.events.initialize();
    };

    homeMethods.events.initialize = function () {
            window.homeInitCalled = true;
            setTimeout(homePrivateMethods.getPendingActions, 1000 * 30);
            // setTimeout(homePrivateMethods.getDashboardValues(), 1000 * 20);

            if (homeMethods.url.getDashboard && homeMethods.url.getDashboard != '')
                homePrivateMethods.getDashboardValues();
            // homePrivateMethods.properties.tmrExecutor = setTimeout(homePrivateMethods.getDashboardValues, 1000);
            //clearTimeout(timer);
            $(homePrivateMethods.controls.ifpublic).attr('src', homeMethods.url.publicsite);
            setInterval(homeALMethods.updateOrderTotalAggregate, 60000);

    };

    return homeMethods;
}();

window.homeInitCalled = window.homeInitCalled || false;

$(document).ready(function () {
    if (!window.homeInitCalled) {
        window.homeInitCalled = true;
        home.initializePage();
    }
});;