var wallet = function () {
    var walletMethods = {}, walletPrivateMethods = {};
    walletPrivateMethods.events = {};
    walletMethods.properties = {
        spendDetails: [],
        recieveDetails: []
    }

    walletPrivateMethods.properties = {
        startDate: '',
        endDate: ''
    }

    walletPrivateMethods.controls = {
        startDatePicker: '#fromdatepicker',
        endDatePicker: '#todatepicker',
        recievedTab: '.recieved-tab',
        spentTab: '.spent-tab',
        search: '#btn-search',
        recievedTile: '.recieved-tile',
        spentTile: '.spent-tile',
        reset: '#btn-reset',
        recievedEmpty: '#noitems_found_recieved',
        spentEmpty: '#noitems_found_spent'
    }
    walletMethods.setStartDate = function (date) {
        walletPrivateMethods.properties.startDate = date;
    }
    
    walletMethods.setEndDate = function (date) {
        walletPrivateMethods.properties.endDate = date;
    }
    walletPrivateMethods.filter = function () {
        if ($(walletPrivateMethods.controls.startDatePicker).datepicker('getDate') != null && $(walletPrivateMethods.controls.endDatePicker).datepicker('getDate') != null != '') {
            var currentClassName = '';
            var items = [];
            if ($(walletPrivateMethods.controls.recievedTab).hasClass('active')) {
                items = walletMethods.properties.recieveDetails;
                currentClassName = walletPrivateMethods.controls.recievedTile;
            } else {
                items = walletMethods.properties.spendDetails;
                currentClassName = walletPrivateMethods.controls.spentTile;
            }

            var filteredData = items.filter(function (a) {
                var date = new Date(a.order_date);
                var hitDateMatchExists = date >= walletPrivateMethods.properties.startDate && date <= walletPrivateMethods.properties.endDate ? true : false;
                return hitDateMatchExists;
            });

            if (filteredData.length == 0) {
                $(currentClassName).addClass('hide');
                if ($(walletPrivateMethods.controls.recievedTab).hasClass('active')) {
                    $(walletPrivateMethods.controls.recievedEmpty).removeClass('hide');
                } else {
                    $(walletPrivateMethods.controls.spentEmpty).removeClass('hide');
                }
            } else {
                $(currentClassName).addClass('hide');
                $.each(filteredData, function (index, item) {
                    $('#' + item.order_id).removeClass('hide');
                });
                if ($(walletPrivateMethods.controls.recievedTab).hasClass('active')) {
                    $(walletPrivateMethods.controls.recievedEmpty).addClass('hide');
                } else {
                    $(walletPrivateMethods.controls.spentEmpty).addClass('hide');
                }
            }
        } else {
            alert('Please provide start and end dates to perform search');
        }
    }

    walletPrivateMethods.events.onReset = function () {
        $(walletPrivateMethods.controls.startDatePicker).datepicker('update', '');
        $(walletPrivateMethods.controls.endDatePicker).datepicker('update', '');
        $(walletPrivateMethods.controls.recievedTile).removeClass('hide');
        $(walletPrivateMethods.controls.spentTile).removeClass('hide');
        walletPrivateMethods.CheckWhethereItsEmptyTab();
    };

    walletPrivateMethods.CheckWhethereItsEmptyTab = function () {
        if ($(walletPrivateMethods.controls.recievedTile).length > 0) {
            $(walletPrivateMethods.controls.recievedEmpty).addClass('hide');
        } else {
            $(walletPrivateMethods.controls.recievedEmpty).removeClass('hide');
        }

        if ($(walletPrivateMethods.controls.spentTile).length > 0) {
            $(walletPrivateMethods.controls.spentEmpty).addClass('hide');
        } else {
            $(walletPrivateMethods.controls.spentEmpty).removeClass('hide');
        }
    }

    walletPrivateMethods.initializeDatePicker = function () {
        $.fn.datepicker.defaults.format = "dd/mm/yyyy";
        $(walletPrivateMethods.controls.startDatePicker).datepicker({
            format: 'dd/mm/yyyy',
        }).on('changeDate', function (e) {
            walletPrivateMethods.properties.startDate = e.date;
            $(walletPrivateMethods.controls.endDatePicker).datepicker('setStartDate', e.date);
            $(walletPrivateMethods.controls.startDatePicker).datepicker('hide');
        });

        $(walletPrivateMethods.controls.endDatePicker).datepicker({
            format: 'dd/mm/yyyy',

        }).on('changeDate', function (e) {
            walletPrivateMethods.properties.endDate = e.date;
            $(walletPrivateMethods.controls.endDatePicker).datepicker('hide');
        });

      

    };

    walletPrivateMethods.events.initialize = function () {
        $(walletPrivateMethods.controls.search).unbind('click').on('click', function (event) {
            event.preventDefault();
            walletPrivateMethods.filter();
        });
        $(walletPrivateMethods.controls.reset).unbind('click').on('click', function (event) {
            event.preventDefault();
            walletPrivateMethods.events.onReset();
        });
    };


    walletMethods.initializePage = function () {
        walletPrivateMethods.initializeDatePicker();
        walletPrivateMethods.events.initialize();
        walletPrivateMethods.CheckWhethereItsEmptyTab();
    };

    return walletMethods;
}();
$(function () {
    wallet.initializePage();
});