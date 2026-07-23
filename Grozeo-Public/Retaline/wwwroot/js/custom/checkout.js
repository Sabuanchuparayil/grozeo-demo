var checkout = function () {
    var checkoutMethods = {}, checkoutPrivateMethods = {};

    checkoutPrivateMethods.events = {};

    checkoutMethods.properties = {
     
    };
    checkoutPrivateMethods.properties = {
     

    };
    checkoutMethods.url = {
       
    };

    checkoutPrivateMethods.controls = {
        paymentModeController:'.paymentmodecheck'
    };
    
    

    checkoutMethods.initializePage = function () {
        checkoutPrivateMethods.events.initialize();
    };
    checkoutPrivateMethods.events.initialize = function () {
        $(checkoutPrivateMethods.controls.paymentModeController).on('change', function () {
            const $this = $(this);
            const $parentDiv = $this.closest('.payment-mode_list > div'); // Target direct child divs of .payment-mode_list

            // Remove the 'selected' class from all payment mode options
            $('.payment-mode_list > div').removeClass('selected');

            // Add the 'selected' class to the parent div of the currently checked radio
            if ($this.prop('checked')) {
                $parentDiv.addClass('selected');
                $('#btncontinuePay').addClass('hide')
                // Handle payment mode logic
                if ($this.attr('id') === 'payby_card') {
                    $('#submitonlinepayment').removeClass('hide');
                    $('#onlinePaymentSection').show();    
                    $('#cashOnDeliverySection').hide(); 
                } else if ($this.attr('id') === 'payby_cashondelivery') {
                    $('#submitonlinepayment').addClass('hide');
                    $('#onlinePaymentSection').hide();
                    $('#cashOnDeliverySection').show(); 
                }

                
            }

        });
    };
    return checkoutMethods;
}();
$(function () {
    checkout.initializePage();
});