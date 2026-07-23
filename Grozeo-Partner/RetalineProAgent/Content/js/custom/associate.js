var associate = function () {
    var associateMethods = {}, associatePrivateMethods = {};

    associateMethods.events = {};

    associateMethods.properties = {
        captchaKey: '',
    };
    associatePrivateMethods.properties = {
        isTimerTriggered: false,
        tmrExecutor: null
    };
    associateMethods.url = {
        getDashboard: '',
        pendingOrders: '',
        pendingActions: '',
        captchaVerification: '',
        publicsite:''
    };

    associatePrivateMethods.controls = {
        tblpendingorders: '#tblpendingorders',
        ifpublic: '#ifpublicsite',
        tblprospect: '#tblprospect'
    };

    associateMethods.controls = {
        newContacts: '',
        newLeads: '',
        newProspects: '',
        newRetailers: ''

    };

    associatePrivateMethods.getDashboardValues = function () {
        //$(self).addClass('processing_loader');
        //$('.homeloading').addClass('processing_loader');
        onSuccess = function (data) {
            //$('.homeloading').removeClass('processing_loader');
            if (data && data.status === 'Success' && data.data) {
                var newContacts = $('#' + associateMethods.controls.newContacts).html();
                $('#' + associateMethods.controls.newContacts).html(data.data.contacts);
                $('#' + associateMethods.controls.newLeads).html(data.data.leads);
                $('#' + associateMethods.controls.newProspects).html(data.data.prospects);
                $('#' + associateMethods.controls.newRetailers).html(data.data.retailers);

                
                
                $('#' + associateMethods.controls.newContacts).closest('.Dashboard_widgets').removeClass('noDashboardContent');
                if (!(data.data.contacts > 0))
                    $('#' + associateMethods.controls.newContacts).closest('.Dashboard_widgets').addClass('noDashboardContent');

                $('#' + associateMethods.controls.newLeads).closest('.Dashboard_widgets').removeClass('noDashboardContent');
                if (!(data.data.leads > 0))
                    $('#' + associateMethods.controls.newLeads).closest('.Dashboard_widgets').addClass('noDashboardContent');

                $('#' + associateMethods.controls.newProspects).closest('.Dashboard_widgets').removeClass('noDashboardContent');
                if (!(data.data.prospects > 0))
                    $('#' + associateMethods.controls.newProspects).closest('.Dashboard_widgets').addClass('noDashboardContent');

                $('#' + associateMethods.controls.newRetailers).closest('.Dashboard_widgets').removeClass('noDashboardContent');
                if (!(data.data.retailers > 0))
                    $('#' + associateMethods.controls.newRetailers).closest('.Dashboard_widgets').addClass('noDashboardContent');

                if (newContacts != $('#' + associateMethods.controls.newContacts).html())
                    associatePrivateMethods.getPendingOrders();

                if (newContacts != $('#' + associateMethods.controls.newContacts).html())
                    associatePrivateMethods.getPendingOrders();
                
            }
        };

        onError = function (data) {
            console.log(data);
            //$('.processing_loader').removeClass('processing_loader');
        };
        retMaster.ajax.JSONRequest(associateMethods.url.getDashboard, 'POST', { isPendingOrders: 0}, onSuccess, onError);
    }
    

    associatePrivateMethods.getPendingOrders = function () {
        onSuccess = function (data) {
            if (data && data.status === 'Success' && data.data) {
                $(associatePrivateMethods.controls.tblpendingorders).find('tbody').html('');
                if (data.data.length > 0) {
                    for (var i = 0; i < data.data.length; i++) {
                        $(associatePrivateMethods.controls.tblpendingorders).find('tbody').append('<tr><td>' + data.data[i].storeName + '</td><td>' + data.data[i].contactNumber + '</td><td>' + data.data[i].contactType + '</td></tr>');
                    }
                }
                else {
                    $(associatePrivateMethods.controls.tblpendingorders).find('tbody').html('<tr><td colspan="4"><div class="text-center"><img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg"><h6 class="mb-3">No leads available</h6></div></td></tr>');
                }
            }
        };

        onError = function (data) {
            console.log(data);
            //$('.processing_loader').removeClass('processing_loader');
        };
        retMaster.ajax.JSONRequest(associateMethods.url.getDashboard, 'POST', { isPendingOrders: 1}, onSuccess, onError);

    }

    associatePrivateMethods.getProspect = function () {
        onSuccess = function (data) {
            if (data && data.status === 'Success' && data.data) {
                $(associatePrivateMethods.controls.tblprospect).find('tbody').html('');
                if (data.data.length > 0) {
                    for (var i = 0; i < data.data.length; i++) {
                        $(associatePrivateMethods.controls.tblprospect).find('tbody').append('<tr><td>' + data.data[i].storeName + '</td><td>' + data.data[i].contactNumber + '</td><td>' + data.data[i].state + '</td></tr>');
                    }
                }
                else {
                    $(associatePrivateMethods.controls.tblprospect).find('tbody').html('<tr><td colspan="4"><div class="text-center"><img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg"><h6 class="mb-3">No leads available</h6></div></td></tr>');
                }
            }
        };

        onError = function (data) {
            console.log(data);
            //$('.processing_loader').removeClass('processing_loader');
        };
        retMaster.ajax.JSONRequest(associateMethods.url.getDashboard, 'POST', { isPendingOrders: 1 }, onSuccess, onError);

    }

    associateMethods.initializePage = function () {
        associateMethods.events.initialize();
    };

    associateMethods.events.initialize = function () {
        $(document).ready(function () {
            /*associatePrivateMethods.getPendingActions();*/
            associatePrivateMethods.getDashboardValues();

            associatePrivateMethods.properties.tmrExecutor = setTimeout(associatePrivateMethods.getDashboardValues, 1000 * 40);
            //clearTimeout(timer);
            $(associatePrivateMethods.controls.ifpublic).attr('src', associateMethods.url.publicsite);
        });

    };



    return associateMethods;
}();
$(function () {
    associate.initializePage();
});