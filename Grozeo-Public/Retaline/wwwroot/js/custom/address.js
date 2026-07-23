var address = function () {
    var addressMethods = {}, addressPrivateMethods = {};

    addressPrivateMethods.events = {};

    addressMethods.properties = {
        hasAddress: true,
        logoutOnClose: false,
        signupPhone: '',
        signupEmail: '',
        signupName: '',
        needDelivery: true,
        addressKey: '',
        deflatitude: 28.5935552,
        deflongitude: 77.2961699,
        password: '',
        outsideUk: master.properties.countryCode ? (master.properties.countryCode == 'UK' ? true : false) : false,
        locality: ''
    };
    addressPrivateMethods.properties = {
        map: null,
        autocomplete: null,
        geocoder: null,
        locationDetails: {
            street_number: "",
            route: "",
            locality: "",
            administrative_area_level_1: "",
            country: "",
            postal_code: "",
            latitude: 28.5935552,
            longitude: 77.2961699
        },
        fullLocationAddresss: '',
        isInvokedFromHeader: false,
        hasSelected: false,
        selectedAddressId: 0,
        countryNumber: '1',
        selectedStateId: '',
        selectedStateName: '',
        selectedDistrictName: '',
        stateList: [],
        addressType: "Home",
    };
    addressMethods.url = {
        addAddress: '',
        verifyOtp: '',
        signUp: '',
        changeAddress: '/Address/ChangeAddress',
        removeAddress: '/Address/DeleteAddress',
        branches: '/branches',
        switchbranch: '/switchbranch',
        getAddrStates: '/getAddrStates',
        getDistricts: '/getDistricts'
    };

    addressPrivateMethods.controls = {
        addressModal: '#address-modal',
        location: 'txt-location',
        map: 'map',
        deliveryMap: 'delivery-map',
        //submit: '#btn-add-address',
        showModal: '.btn-show-address-popup',
        chooseLocation: "#choose-location",
        chooseLocationModal: "#choose-location-modal",
        addressForm: "#frmaddaddress",
        removeAddress: '.remove_adress',
        chooseDeliveryMethod: '#choose-deliverymethod',
        deliveryAddress: '.delivery_adress',
        branchModel: '#BranchesModal',
        chooseDeliveryType: '#choose-deliverytype-modal',
        addAddressButton: '#btn-add-address',
        searchLocation: '.searchlocation',
        houseNumber: '#txt-house-number',
        houseName: '#txt-street-name',
        streetName: '#txt-street-name',
        addressLineOne: '#txt-addressline-one',
        addressLineTwo: '#txt-addressline-two',
        state: '#txt-state',
        addressType: '#drp-address-type',
        postalcode: '#txt-post-code',
        landmark: '#txt-landmark',
        district: '#txt-district',
        addressTypeOtherSection: '.other-address-Type',
        otherType: '#txt-other-type',
        addressTile: '.address-tile-',
        addressChangeConfirmationModal: '#address-change-confirmation',
        changeAddressConfirm: '#btn-confirm-address-change',
        changeAddressCancel: '#btn-cancel-address-change',
        districtLabel: '#lbl-district',
        your_location_btn: '.your_location',
        outsideUk: '#outsideUk',
        withinUk: '#withinUk',
        mapLocationField: '#map-location-field',
        postCodeLookup: '.postcode_lookup_wrap',
        addrNotListedLabel: '.addr-not-listed'
    };

    addressMethods.initializeMap = function () {
        $(addressPrivateMethods.controls.searchLocation).find('#' + addressPrivateMethods.controls.map).show();
        $('#' + addressPrivateMethods.controls.map).css('height', '400px');
        $('#' + addressPrivateMethods.controls.map).css('width', '100%');
        $('#' + addressPrivateMethods.controls.map).css('overflow', 'unset');

        var myLatlng = new google.maps.LatLng(addressMethods.properties.deflatitude, addressMethods.properties.deflongitude);
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                myLatlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);

            })
            
        }
        var scrollwheelanddraggable =
            master.properties.countryCode !== 'UK'
                ? true
                : ($('#getaddress_dropdown').val() && $('#getaddress_dropdown').val() !== 'open' ? false : true);


        var mapOptions = {
            center: myLatlng,
            zoom: 17,
            disableDefaultUI: !scrollwheelanddraggable,
            scrollwheel: scrollwheelanddraggable,
            draggable: scrollwheelanddraggable,
            panControl: false,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle.SMALL
            },
            mapTypeControl: false,
            scaleControl: false,
            streetViewControl: false,
        };

        var map = new google.maps.Map(document.getElementById(addressPrivateMethods.controls.map),
            mapOptions);

        var marker = new google.maps.Marker({
            position: myLatlng,
            map: map
        });
        addressPrivateMethods.properties.geocoder = new google.maps.Geocoder();
        google.maps.event.addListener(map, 'center_changed', function () {

            // 0.1 seconds after the center of the map has changed,
            // set back the marker position.
            window.setTimeout(function () {
                var center = map.getCenter();
                marker.setPosition(center);
                //geocodePosition(marker.getPosition());
            }, 100);
        });
        google.maps.event.addListener(map, 'dragend', function () { // 'idle', function () {

            // 0.1 seconds after the center of the map has changed,
            // set back the marker position.
            window.setTimeout(function () {
                //var center = map.getCenter();
                //marker.setPosition(center);
                geocodePosition(marker.getPosition());
                $('.your_location').removeClass('active')

            }, 100);
        });
        // Create the DIV to hold the control.
        const centerControlDiv = document.createElement("div");
        // Create the control.
        const centerControl = createCenterControl(map);

        function createCenterControl(map) {
            const controlButton = document.createElement("button");

            // Emulate the style of Google Maps Current Location button
            controlButton.style.backgroundColor = "white";
            controlButton.style.width = "auto";
            controlButton.style.height = "auto";
            controlButton.style.minHeight = "auto";
            controlButton.style.border = "1px solid #ccc";
            controlButton.style.borderRadius = "8px";
            controlButton.style.boxShadow = "0 1px 4px rgba(0, 0, 0, 0.3)";
            controlButton.style.cursor = "pointer";
            controlButton.style.fontFamily = "Roboto,Arial,sans-serif";
            controlButton.style.fontSize = "14px";
            controlButton.style.padding = "10px";
            controlButton.style.margin = "10px";
            controlButton.style.textAlign = "center";
            controlButton.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 21 21" fill="none">
												<circle cx="10.25" cy="10.7149" r="6.99419" stroke="black" stroke-width="1.5" />
												<path d="M10.25 1.25V5.98256" stroke="black" stroke-width="1.5" stroke-linecap="round" />
												<path d="M10.25 15.0176V19.7501" stroke="black" stroke-width="1.5" stroke-linecap="round" />
												<path d="M19.5 10.5L14.7674 10.5" stroke="black" stroke-width="1.5" stroke-linecap="round" />
												<path d="M5.73242 10.5L0.999864 10.5" stroke="black" stroke-width="1.5" stroke-linecap="round" />
												<circle cx="10.2503" cy="10.7152" r="2.5814" fill="black" />
											</svg>`
            //controlButton.style.backgroundImage = "~\images\mylocation.png";
            controlButton.classList.add("your_location");


            controlButton.title = "Your Location";
            controlButton.type = "button";

            // Set hover effect
            controlButton.addEventListener("mouseenter", () => {
                controlButton.style.backgroundColor = "#f9f9f9";
            });

            controlButton.addEventListener("mouseleave", () => {
                controlButton.style.backgroundColor = "white";
            });

            controlButton.addEventListener("click", () => {
                map.setCenter(myLatlng);
                window.setTimeout(function () {
                    geocodePosition(marker.getPosition());
                    $('.your_location').addClass('active')
                }, 100);

            });
            addressMethods.setCurrentLoaction = function () {
                map.setCenter(myLatlng);
                window.setTimeout(function () {
                    geocodePosition(marker.getPosition());
                    $('.your_location').addClass('active')

                }, 100);
            };
            return controlButton;
        }

        // Append the control to the DIV.
        if (scrollwheelanddraggable) {
            centerControlDiv.appendChild(centerControl);
            map.controls[google.maps.ControlPosition.TOP_RIGHT].push(centerControlDiv);
        }


    };

    function geocodePosition(pos) {
        addressPrivateMethods.properties.geocoder.geocode({
            latLng: pos
        }, function (responses) {
            if (responses && responses.length > 0) {
                var addr = responses[0].formatted_address;
                $('#' + addressPrivateMethods.controls.location).val(addr);
                addressPrivateMethods.properties.hasSelected = true;
                addressPrivateMethods.properties.fullLocationAddresss = responses[0].formatted_address;
                addressPrivateMethods.fillInAddressProperties(responses[0]);

            }
        });
    }

    addressPrivateMethods.initializeAddressAutocompleteUsingGoogleMap = function () {
        var addressInput = document.getElementById(addressPrivateMethods.controls.location);
        destroyAutocomplete();
        addressPrivateMethods.properties.autocomplete = createAutocomplete(addressInput);
    };
    function destroyAutocomplete() {
        if (addressPrivateMethods.properties.autocomplete) {
            google.maps.event.clearInstanceListeners(addressPrivateMethods.properties.autocomplete);
            $(".pac-container").remove(); // Removing the suggestions dropdown from dom
            // Remove the autocomplete instance
            addressPrivateMethods.properties.autocomplete = null;
        }
    }
    function createAutocomplete(input) {
        var options = {
            types: [],
            fields: ["address_components", "geometry"]
        };

        if (master.properties.countryCode == 'IN' || (master.properties.countryCode == 'UK' && !addressMethods.properties.outsideUk)) {
            options.componentRestrictions = {
                country: master.properties.countryCode
            };
        }

        var autocomplete = new google.maps.places.Autocomplete(input, options);
        autocomplete.setFields(["address_component", "geometry"]);
        autocomplete.addListener("place_changed", addressPrivateMethods.fillInAddress);

        return autocomplete;
    }

    //function updateAutocomplete() {
    //    var addressInput = document.getElementById(addressPrivateMethods.controls.location);
    //    addressPrivateMethods.properties.autocomplete = createAutocomplete(addressInput);
    //}

    addressPrivateMethods.initializeAddressAutocomplete = function () {
        IdealPostcodes.AddressFinder.setup({
            apiKey: addressMethods.properties.addressKey,
            outputFields: {
                line_1: addressPrivateMethods.controls.addressLineOne,
                line_2: addressPrivateMethods.controls.addressLineTwo,
                postcode: addressPrivateMethods.controls.postalcode,
                post_town: addressPrivateMethods.controls.district
            },
        });
    }

    addressPrivateMethods.changeAddress = function (self) {
        if (!addressPrivateMethods.properties.selectedAddressId || addressPrivateMethods.properties.selectedAddressId < 1)
            return;

        $(addressPrivateMethods.controls.changeAddressConfirm).prop("disabled", true);
        $(addressPrivateMethods.controls.changeAddressCancel).prop("disabled", true);
        $(self).addClass("processing");

        onSuccess = function (data) {
            if (window.location.pathname != '/')
                window.location.href = '/';
            else
                window.location.reload(true);
        };

        onError = function (data) {
            console.log(data);
            $(self).removeClass('processing');
        };
        master.ajax.JSONRequest(addressMethods.url.changeAddress, 'POST', { AddressId: addressPrivateMethods.properties.selectedAddressId }, onSuccess, onError);
    };

    addressMethods.getBranches = function (obj) {

        onSuccess = function (data) {
            if (data && data.length > 0) {
                var div_data = "";
                var template = '<div class="card hover-overlay ripple shadow-1-strong rounded" style="width: 99%; margin: 3px auto; cursor: pointer; box-shadow: 0 4px 8px 0 rgb(0 0 0 / 20%); transition: .3s; height: 100%; " onclick="address.switchBranch(this);" brid="[BRANCHID]"><div class="row myorder" style="padding: 20px;"><div class="col-md-12 col-xs-12"><b><i class="fa fa-shopping-cart" style="margin-right: 4px;"></i> [NAME]</b></div></div></div>';
                $.each(data, function (k, v) {
                    div_data += template.replace('[NAME]', v.br_Name + '<br/> ' + v.br_Address).replace('[BRANCHID]', v.br_ID);
                });
                $(obj).html(div_data);
            }

        };

        onError = function (data) {
            console.log(data);
        };
        master.ajax.JSONRequest(addressMethods.url.branches, 'POST', {}, onSuccess, onError);
    };
    addressMethods.switchBranch = function (obj) {
        var brid = $(obj).attr('brid');
        onSuccess = function (data) {
            window.location.reload(true);
        };

        onError = function (data) {
            console.log(data);
        };
        master.ajax.JSONRequest(addressMethods.url.switchbranch, 'POST', { br_id: brid }, onSuccess, onError);
    };


    addressMethods.deleteAddress = function (self) {
        var isPrimary = $(self).find('.delivery_location.delivery_adress').data('primary');
        var addressId = $(self).find('.delivery_location.delivery_adress').data('addrid');
        console.log(isPrimary, addressId)
        if (isPrimary == '1') {
            alert('Sorry, you cannot remove primary address!');
            return;
        }
        if (!confirm("Are you sure you want to remove this address?"))
            return;

        onSuccess = function (data) {
            //const $li = $(this).closest('li.delivery_adress_list');
            //const $ul = $li.closest('ul.delivery_adress_wap');
            //const $section = $li.closest('.delivery_adress_sec');

            //if ($ul.children('li.delivery_adress_list').length > 1) {
            //    $li.remove(); // Remove just this li
            //} else {
            //    $section.remove(); // Remove entire section
            //}
            window.location.reload()
        };

        onError = function (data) {
            console.log(data);
        };
        master.ajax.JSONRequest(addressMethods.url.removeAddress, 'POST', { AddressId: addressId }, onSuccess, onError);
    };

    addressPrivateMethods.fillInAddress = function () {
        var place = addressPrivateMethods.properties.autocomplete.getPlace();
        addressPrivateMethods.fillInAddressProperties(place);
        $(addressPrivateMethods.controls.searchLocation).addClass('searchlocationselected');
        $(addressPrivateMethods.controls.searchLocation + ' input.form-control').prop('disabled', true);

        addressMethods.properties.deflatitude = place.geometry.location.lat();
        addressMethods.properties.deflongitude = place.geometry.location.lng();
        addressMethods.initializeMap();
        if (loggedIn == "False") {
            master.updateguestuser();
        }
    };

    addressPrivateMethods.fillInAddressProperties = async function (place) {

        $(addressPrivateMethods.controls.addressForm).find("p.Error").remove();
        addressPrivateMethods.properties.fullLocationAddresss = '';
        addressPrivateMethods.properties.locationDetails = {
            subpremise: "", premise: "",
            neighborhood: "", postal_town: "", sublocality: "",
            street_number: "",
            route: "",
            locality: "",
            administrative_area_level_3: "",
            administrative_area_level_2: "",
            administrative_area_level_1: "",
            country: "",
            postal_code: "",
            latitude: 0,
            longitude: 0

        };

        var addressArray = [];
        //for (const component of place.address_components) {
        //    const addressType = component.types[0];
        //    addressArray.push(component['long_name']);
        //    for (const component of place.address_components) {
        //        const addressType = component.types[0];

        //        if (typeof addressPrivateMethods.properties.locationDetails[addressType] !== "undefined") {
        //            addressPrivateMethods.properties.locationDetails[addressType] = component['long_name'];
        //        }

        //    }

        //}

        place.address_components.forEach(comp => {
            comp.types.forEach(type => {
                if (addressPrivateMethods.properties.locationDetails.hasOwnProperty(type)) {
                    addressPrivateMethods.properties.locationDetails[type] = comp.long_name;
                    addressArray.push(comp.long_name);
                }
            });
        });


        const house = addressPrivateMethods.properties.locationDetails.subpremise || addressPrivateMethods.properties.locationDetails.premise || addressPrivateMethods.properties.locationDetails.street_number;
        const street = addressPrivateMethods.properties.locationDetails.route || addressPrivateMethods.properties.locationDetails.neighborhood || "";
        const lineOne = (addressPrivateMethods.properties.locationDetails.route !== null ? addressPrivateMethods.properties.locationDetails.neighborhood : null) || addressPrivateMethods.properties.locationDetails.locality || addressPrivateMethods.properties.locationDetails.postal_town || "";
        const lineTwo = addressPrivateMethods.properties.locationDetails.sublocality || addressPrivateMethods.properties.locationDetails.administrative_area_level_2 || "";
        //const district = addressPrivateMethods.properties.locationDetails.locality || addressPrivateMethods.properties.locationDetails.postal_town || "";
        //const county = addressPrivateMethods.properties.locationDetails.administrative_area_level_1;


        $('.populate-autoselection-primary').html(lineOne)
       // $('.populate-autoselection-secondary').html(house)


        //$(addressPrivateMethods.controls.houseNumber).val(house || "");
        //$(addressPrivateMethods.controls.houseName).val(street || "");
        //$(addressPrivateMethods.controls.addressLineOne).val(lineOne || "");
        //$(addressPrivateMethods.controls.addressLineTwo).val(lineTwo || "");
        //$(controls.postalcode).val(components.postal_code || "");
        //$(controls.district).attr('seldist', district || "");
        //$(controls.state).find('option:contains(' + county + ')').prop("selected", true);

        if (addressArray.length > 0)
            addressPrivateMethods.properties.hasSelected = true;

        addressPrivateMethods.properties.fullLocationAddresss = addressArray.join();
        addressPrivateMethods.properties.locationDetails.latitude = place.geometry.location.lat();
        addressPrivateMethods.properties.locationDetails.longitude = place.geometry.location.lng();

        if (addressPrivateMethods.properties.locationDetails.administrative_area_level_1 != '') {
            $(addressPrivateMethods.controls.state).val(addressPrivateMethods.properties.locationDetails.administrative_area_level_1);
            if (master.properties.countryCode == "IN") {
                addressMethods.getAddrStates()
            }

        }

        if (addressPrivateMethods.properties.locationDetails.administrative_area_level_2 != '')
            $(addressPrivateMethods.controls.district).val(addressPrivateMethods.properties.locationDetails.administrative_area_level_2);
        else if (addressPrivateMethods.properties.locationDetails.locality != '')
            $(addressPrivateMethods.controls.district).val(addressPrivateMethods.properties.locationDetails.locality);
        if (addressPrivateMethods.properties.locationDetails.postal_code != '')
            $(addressPrivateMethods.controls.postalcode).val(addressPrivateMethods.properties.locationDetails.postal_code);

    }
    addressMethods.fillInFindAddress = function (addr, lat, lng, district, post, city, state) {
        $(addressPrivateMethods.controls.addressForm).find("p.Error").remove();
        addressPrivateMethods.properties.fullLocationAddresss = '';
        addressPrivateMethods.properties.locationDetails = {
            street_number: "",
            route: "",
            locality: city,
            administrative_area_level_3: "",
            administrative_area_level_2: district,
            administrative_area_level_1: state,
            country: "",
            postal_code: post,
            latitude: lat,
            longitude: lng
        };

        addressPrivateMethods.properties.hasSelected = true;
        addressPrivateMethods.properties.fullLocationAddresss = addr;
        addressMethods.properties.deflatitude = lat; addressMethods.properties.deflongitude = lng; addressMethods.properties.locality = addr;
        addressMethods.initializeMap();
    }
    addressMethods.backSearchView = function () {
        $(addressPrivateMethods.controls.searchLocation).removeClass('searchlocationselected');
        $(addressPrivateMethods.controls.searchLocation + ' input.form-control').prop('disabled', false);
        $(addressPrivateMethods.controls.searchLocation).find('#' + addressPrivateMethods.controls.map).hide();
        $('#' + addressPrivateMethods.controls.location).removeProp('disabled');
        $('#' + addressPrivateMethods.controls.location).val('');
        $('#' + addressPrivateMethods.controls.location).focus();
    }
    addressMethods.showSelAddress = function (logoutOnClose) {
        addressMethods.backSearchView();
        $(addressPrivateMethods.controls.addressModal).modal('hide');

        $(addressPrivateMethods.controls.chooseLocationModal).modal({ backdrop: 'static', keyboard: false });
        $(addressPrivateMethods.controls.chooseLocationModal).find('.model-left').css('width', '100%');
        if (logoutOnClose) {
            $(addressPrivateMethods.controls.chooseLocationModal).find('.close').unbind('click').on('click', function () {
                if (confirm("You need to select address to avail the service of store. You'll be logged out if you close now. Are you sure to skip and continue?"))
                    window.location.href = '/logout';
                else
                    return false;
            });
        }
    }

    addressPrivateMethods.onAddAddressInvoke = function (logoutOnClose) {
        var controls = addressPrivateMethods.controls;
        if ($(controls.chooseLocationModal).hasClass('show')) {
            $(controls.chooseLocationModal).modal('hide');
            addressPrivateMethods.properties.isInvokedFromHeader = true;
        }
        if (logoutOnClose) {
            $(controls.addressModal).find('.closepopup').unbind('click').on('click', function () {
                if (confirm("You need atleast one address to avail the service of store. You'll be logged out if you close now. Are you sure to cancel?"))
                    window.location.href = '/logout';
                else
                    return false;
            });
        }
        $(controls.addressModal).modal('show');
        //addressPrivateMethods.initializeAddressAutocomplete();
    };

    addressPrivateMethods.isValidForm = function () {
        var isValid = true;
        $(addressPrivateMethods.controls.addressForm).find("p.Error").remove();

        if (!addressPrivateMethods.properties.hasSelected) {
            $(addressPrivateMethods.controls.addressForm).find('#' + addressPrivateMethods.controls.location).parent().append("<p class='Error'>Invalid location. Please select location from list.</p>");
            return false;
        }
        $(addressPrivateMethods.controls.addressForm).find('input[type="text"]').each(function () {
            var isValid = true;
            $(this).siblings("p.Error").remove();
            if ($(this).hasClass('required') && $(this).val() == '') {
                $(this).parent().append("<p class='Error'>This field is required</p>");
                isValid = false;
            }
        });

        if ($(addressPrivateMethods.controls.addressType).val() == '') {
            $(addressPrivateMethods.controls.addressType).parent().append("<p class='Error'>This field is required</p>");
        } else {
            $(addressPrivateMethods.controls.addressType).siblings("p.Error").remove();
        }

        if ($(addressPrivateMethods.controls.addressForm).find("p.Error").length > 0)
            isValid = false;

        return isValid;
    };

    addressMethods.loadNearestStores = function (lat, lng) {
        $('.dvnearestbranch').html('');
        $('.dvnearestbranch').parent().next('.preferredstoreslider').remove();
        onSuccess = function (data) {
            if (data.stores.data.length <= 0) {
                $('.dvnearestbranch').html("<p>No nearby stores are currently available. Kindly utilize the 'change location' option above to locate stores in a different area.</p>");
                return;
            }

            var featuredOuterTemplate = '<div class="swiper preferredstoreslider store_list_wrap_sec d-flex flex-wrap row g-3"><div id="cnt_neareststores" class="swiper-wrapper p-0 m-0">[ITEMREPEAT]</div><div class="swiper-button-next"></div><div class="swiper-button-prev"></div></div>';
            var featuredRepeatTemplate = '<div class="swiper-slide"><div class="store_list_wrap justify-content-center d-flex flex-wrap"><div class="card p-3"><a href="/br/[STOREID]/[STORENAME]"><span class="highlight_store"><i class="fa-solid fa-star"></i></span><div class="store_logo d-flex justify-content-center align-items-center mb-2">[STORELOGO] <h4>[STORENAME]</h4></div><div style="position: relative;" class=""><div class="store_location w-100 text-center"> &nbsp;</div><div class="store_category w-100 text-center">[STOREADDRESS]</div><div class="store_visite"><span class="visite_store">Visit Store Now</span></div></div></a></div></div></div>';

            var template = '<div class="store_list_wrap col-12 col-sm-6 col-md-4 col-lg-3 justify-content-center d-flex flex-wrap"><div class="card p-3"><a href="/br/[STOREID]/[STORENAME]"><div class="store_awayfrom text-center"><span>[DISTANCE] km</span> Away</div><div class="store_logo d-flex justify-content-center align-items-center mb-2">[STORELOGO] <h4>[STORENAME]</h4></div><div style="position: relative;" class=""><div class="store_location w-100 text-center"> &nbsp;</div><div class="store_category w-100 text-center">[STOREADDRESS]</div><div class="store_visite"><span class="visite_store">Visit Store</span></div></div></a></div></div>';
            var div_data = '';
            var div_featured_data = '';
            var indx = 0;
            var hasFeaturedTemplate = 0;
            for (var i = 0; i < data.stores.data.length && (i < (data.stores.data.length >= 8 ? 4 : 8)); i++) {
                var dst = Math.round(data.stores.data[i].distance * 100) / 100;
                var logoTemplate = (data.stores.data[i].logo && data.stores.data[i].logo != '' ? '<img src="' + data.stores.data[i].logo + '"/>' : '');
                div_data += template.replace('[STORENAME]', data.stores.data[i].br_Name).replace('[STORENAME]', data.stores.data[i].br_Name).replace('[STORELOGO]', logoTemplate).replace('[STOREID]', data.stores.data[i].br_ID).replace('[DISTANCE]', dst).replace('[STOREADDRESS]', data.stores.data[i].br_Address + ',' + data.stores.data[i].br_City);
                indx += 1;
            }
            for (var i = indx; i < data.stores.data.length; i++) {
                var dst = Math.round(data.stores.data[i].distance * 100) / 100;
                var logoTemplate = (data.stores.data[i].logo && data.stores.data[i].logo != '' ? '<img src="' + data.stores.data[i].logo + '"/>' : '');
                div_featured_data += featuredRepeatTemplate.replace('[STORENAME]', data.stores.data[i].br_Name).replace('[STORENAME]', data.stores.data[i].br_Name).replace('[STORELOGO]', logoTemplate)
                    .replace('[STOREID]', data.stores.data[i].br_ID).replace('[DISTANCE]', dst)
                    .replace('[STOREADDRESS]', data.stores.data[i].br_Address + ',' + data.stores.data[i].br_City).replace('[STORELOGO]', '');
                indx += 1;
                hasFeaturedTemplate += 1;
            }

            //$.each(data.stores.data, function (k, v) {
            //    div_data += template.replace('[STORENAME]', v.br_Name).replace('[STORENAME]', v.br_Name).replace('[STOREID]', v.br_ID).replace('[DISTANCE]', v.distance).replace('[STOREADDRESS]', v.br_Address + ',' + v.br_City);
            //});
            $('.dvnearestbranch').html(div_data);
            if (hasFeaturedTemplate > 0) {
                $('.dvnearestbranch').parent().after(featuredOuterTemplate.replace('[ITEMREPEAT]', div_featured_data));
            }

        };

        onError = function (data) {
            $('.dvnearestbranch').html("<p>Your location is not accessible. Please use the change location button above to find the stores nearby.</p>");
        };
        master.ajax.JSONRequest('/findneareststores/' + lat + '/' + lng, 'POST', {}, onSuccess, onError);
    };

    addressPrivateMethods.addAddress = function (self) {


        var controls = addressPrivateMethods.controls;
        var locationDetails = addressPrivateMethods.properties.locationDetails;
        var addrType = addressPrivateMethods.properties.addressType;
        var mobile = addressMethods.properties.signupPhone ? addressMethods.properties.signupPhone : $('#txt-addr-phoneNumber').val();
        $(controls.addAddressButton).prop('disabled', true);
        $(controls.addAddressButton).addClass('processing');


        if (!addressPrivateMethods.isValidForm()) {
            $(controls.addAddressButton).prop('disabled', false);
            $(self).find('.processing').removeClass('processing');
            return false;
        }
        if (addrType == 'other' && $(controls.otherType).val() != '')
            addrType = $(controls.otherType).val();
        let deliveryState = ($(addressPrivateMethods.controls.state).val() == '' ? locationDetails.administrative_area_level_1 : $(addressPrivateMethods.controls.state).val());
        let deliveryCity = locationDetails.locality;
        if (master.properties.countryCode == "UAE") {
            deliveryCity = addressPrivateMethods.properties.selectedDistrictName;
        } else if (master.properties.countryCode != "GB") {
            deliveryState = addressPrivateMethods.properties.selectedStateName == '' ? locationDetails.administrative_area_level_1 : addressPrivateMethods.properties.selectedStateName;
        }
        var deliDistrict = $(controls.district + ' option:selected').text(); //$(controls.district).val(); 
        var deliveryName = $('#txt-addr-username').val()
        var details = {
            deli_delivery_pin: $(controls.postalcode).val(),
            deli_house_no: $(controls.houseNumber).val(),
            deli_house_name: $(controls.houseName).val(),
            deli_land_mark: $(controls.landmark).val(),
            deli_city: deliveryCity,
            deli_state: deliveryState,
            deli_district: deliDistrict, //deliveryCity,
            deli_type: addrType,
            deli_latitude: locationDetails.latitude,
            deli_longitude: locationDetails.longitude,
            deli_google_pin: locationDetails.postal_code === '' ? $(controls.postalcode).val() : locationDetails.postal_code,
            deli_post: locationDetails.postal_code === '' ? $(controls.postalcode).val() : locationDetails.postal_code,
            deli_address: $(controls.addressLineOne).val(),
            deli_address2: $(controls.addressLineTwo).val(),
            deli_google_address: addressPrivateMethods.properties.fullLocationAddresss,
            deli_is_primary: 0,// ($(controls.isPrimaryAddress).is(':checked') ? 1 : 0),
            deli_contact_no: mobile,
            deli_name: deliveryName,
            deli_email: addressMethods.properties.signupEmail,//Not necessary for now, in India. may be necessary for UK
            signupEmail: addressMethods.properties.signupEmail,
            signupName: addressMethods.properties.signupName,
            password: address.properties.password
        };
        onSuccess = function (data) {
            if (window.location.pathname != '/')
                window.location.href = '/';
            else
                window.location.reload(true);
        };

        onError = function (data) {
            $(self).find('.processing').removeClass('processing');
            console.log(data);
        };
       // console.log(details)
        master.ajax.JSONRequest(addressMethods.url.addAddress, 'POST', details, onSuccess, onError);
    }

    addressPrivateMethods.events.onChooseLocation = function () {
        var controls = addressPrivateMethods.controls;
        $(controls.chooseDeliveryType).modal('hide');
        $(controls.chooseDeliveryType).find('.model-left').css('width', '0%');
        $(controls.chooseLocationModal).modal('show');
        $(controls.chooseLocationModal).find('.model-left').css('width', '100%');
    };

    addressPrivateMethods.events.onChooseDeliveryType = function () {
        var controls = addressPrivateMethods.controls;
        $(controls.chooseDeliveryType).modal('show');
        $(controls.chooseDeliveryType).find('.model-left').css('width', '100%');
    };

    addressPrivateMethods.events.toggleOtherAddressSection = function (selectedType) {
        console.log(selectedType)
        var controls = addressPrivateMethods.controls;
        addressPrivateMethods.properties.addressType = selectedType;
        if (selectedType === 'other') {
            $(controls.addressTypeOtherSection).removeClass('hide');
            $(controls.otherType).addClass('required');
        } else {
            $(controls.addressTypeOtherSection).addClass('hide');
            $(controls.otherType).removeClass('required');
        }
    }

    addressMethods.triggerAddAddress = function (logoutOnClose) {
        addressPrivateMethods.onAddAddressInvoke(logoutOnClose);
    };
    addressMethods.returnGuestLocation = function () {
        return addressPrivateMethods.properties.locationDetails;
    };
    addressMethods.returnGuestLocationAutoDetect = function () {
        return {
            latitude: addressMethods.properties.deflatitude,
            longitude: addressMethods.properties.deflongitude,
            locality: addressMethods.properties.locality
        };
    };

    addressMethods.getAddrStates = function () {
        var countryNumber = addressPrivateMethods.properties.countryNumber;
        if (master.properties.countryCode == "UAE")
            countryNumber = '3';
        else if (master.properties.countryCode == "UK") {
            countryNumber = '2';
        }
        var onSuccess = function (data, apiSkipped) {
            if (data.status == 'ok' || apiSkipped) {
                addressPrivateMethods.properties.stateList = data.data;
                var select = document.getElementById('txt-state');
                select.innerHTML = '<option value="">Select a State</option>'; // Reset the select options

                var locationDetails = addressPrivateMethods.properties.locationDetails;
                var selectedStateId = null;

                // Populate the select options
                data.data.forEach(function (state) {
                    var option = document.createElement('option');
                    option.value = state.st_ID;
                    option.text = state.st_name;
                    if ($(select).attr('selst') == state.st_name || (locationDetails.administrative_area_level_1 && locationDetails.administrative_area_level_1 === state.st_name)) {
                        option.selected = true;
                        selectedStateId = state.st_ID;
                    }
                    select.appendChild(option);
                });

                // If a state was selected, save its ID and name
                if (selectedStateId) {
                    addressPrivateMethods.properties.selectedStateId = selectedStateId;
                    addressPrivateMethods.properties.selectedStateName = locationDetails.administrative_area_level_1;
                    addressMethods.onStateChange();
                }
            }
        };

        var onError = function (data) {
            console.log(data);
        };

        if (addressPrivateMethods.properties.stateList.length == 0)
            master.ajax.JSONRequest(addressMethods.url.getAddrStates, 'POST', countryNumber, onSuccess, onError);
        else {
            var a = { data: addressPrivateMethods.properties.stateList }
            onSuccess(a, true)
        }
    };
    $(document).on('change', '#txt-state', function () {
        addressMethods.onStateChange()
    });

    addressMethods.onStateChange = function () {
        var selectElement = document.getElementById('txt-state');
        var selectedStateId = selectElement.value;
        var selectedStateName = selectElement.options[selectElement.selectedIndex].text;

        if (!selectedStateId) return; // Exit if no state is selected
        addressPrivateMethods.properties.selectedStateId = selectedStateId;
        addressPrivateMethods.properties.selectedStateName = selectedStateName;

        var onSuccess = function (data) {
            if (data.status === 'ok') {
                var select = document.getElementById('txt-district');
                select.innerHTML = '<option value="">Select a City/District</option>'; // Reset the select options

                var locationDetails = addressPrivateMethods.properties.locationDetails;
                var selectedDistrictId = null;

                data.data.forEach(function (district) {
                    var option = document.createElement('option');
                    option.value = district.dst_Id;
                    option.text = district.dst_Name;
                    if ($(select).attr('seldist') == district.dst_Name || (locationDetails.administrative_area_level_3 && locationDetails.administrative_area_level_3 === district.dst_Name)) {
                        option.selected = true;
                        selectedDistrictId = district.dst_Id;
                    }
                    select.appendChild(option);
                });

                // If a district was selected, save its ID and name
                if (selectedDistrictId) {
                    addressPrivateMethods.properties.selectedDistrictId = selectedDistrictId;
                    addressPrivateMethods.properties.selectedDistrictName = locationDetails.administrative_area_level_3;
                }
            }
        };

        var onError = function (data) {
            console.log(data);
        };

        master.ajax.JSONRequest(addressMethods.url.getDistricts, 'POST', selectedStateId, onSuccess, onError);
    };


    addressMethods.onDistrictChange = function () {
        var selectElement = document.getElementById('txt-district');
        // var selectedDistrictId = selectElement.value;
        var selectedDistrictName = selectElement.options[selectElement.selectedIndex].text;
        addressPrivateMethods.properties.selectedDistrictName = selectedDistrictName;


    };
    addressPrivateMethods.events.initialize = function () {
        var controls = addressPrivateMethods.controls;
        $('#btn-search-address').unbind('click').on('click', function () {
            var locationDetails = addressPrivateMethods.properties.locationDetails;
            address.loadNearestStores(locationDetails.latitude, locationDetails.longitude);
            $('#search-address-modal').modal('hide');
        });

        $('#search-address-modal').on('shown.bs.modal', function (e) {
            addressPrivateMethods.controls.map = 'searchmap';
            addressPrivateMethods.controls.location = 'search-txt-location';
            addressMethods.initializeMap();
            addressPrivateMethods.initializeAddressAutocompleteUsingGoogleMap();

            addressPrivateMethods.controls.map = 'map';
            addressPrivateMethods.controls.location = 'txt-location';

        });

        $(controls.addressForm).unbind('submit').on('submit', function (event) {
            event.preventDefault();
            console.log('addAddress')
            addressPrivateMethods.addAddress($(this));
        });

        $('#' + controls.location).unbind('change').on('change', function () {
            addressPrivateMethods.properties.hasSelected = false;
        });

        $(controls.showModal).unbind('click').on('click', function () {
            addressPrivateMethods.onAddAddressInvoke(addressMethods.properties.logoutOnClose);
        });

        $(controls.chooseLocation).unbind('click').on('click', function () {
            addressPrivateMethods.events.onChooseLocation();
        });

        $(controls.chooseDeliveryMethod).unbind('click').on('click', function () {
            addressPrivateMethods.events.onChooseDeliveryType();
        });

        $(controls.removeAddr).unbind('click').on('click', function (e) {
            e.stopImmediatePropagation(); e.stopPropagation();
            addressMethods.deleteAddress(this);
        });

        $(controls.branchModel).on('shown.bs.modal', function (e) {
            addressMethods.getBranches($(this).find('.lstbranches'));
        });

        $(controls.deliveryAddress).unbind('click').on('click', function () {
            if ($(this).parent().hasClass("active") || $(this).hasClass("active")) {
                $('#adressDropdown').modal('hide');
            } else {
                addressPrivateMethods.properties.selectedAddressId =
                    $(this).parent().data('addrid') || $(this).data('addrid');
                addressPrivateMethods.changeAddress($(this).parent());
            }

        });

        $(controls.removeAddress).unbind('click').on('click', function () {
            console.log($(this).parent())
            addressMethods.deleteAddress($(this).parent());
        });
        $('.address-type').change(function () {
            let selectedValue = $(this).val();
            addressPrivateMethods.events.toggleOtherAddressSection(selectedValue);

        });
        $(controls.changeAddressConfirm).unbind('click').on('click', function () {
            addressPrivateMethods.changeAddress($(this));
        })
        $(controls.your_location_btn).unbind('click').on('click', function () {
            addressMethods.setCurrentLoaction()

        })
        $('.detect-location').unbind('click').on('click', function () {
            $('.detect-location').addClass('processing');
            $('.search_address_input_text').html('<p>Locating you — this may take a few seconds…<p>')
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;

                        addressMethods.properties.deflatitude = lat;
                        addressMethods.properties.deflongitude = lng;

                        // Reverse Geocoding
                        const geocoder = new google.maps.Geocoder();
                        const latlng = { lat: lat, lng: lng };

                        geocoder.geocode({ location: latlng }, function (results, status) {
                            if (status === "OK" && results[0]) {
                                let locality = "";
                                //results[0].address_components.forEach(component => {
                                //    if (component.types.includes("locality")) {
                                //        locality = component.long_name;
                                //    }
                                //});
                                locality = results[0].formatted_address;
                                addressMethods.properties.locality = locality;
                                $('.detect-location').removeClass('processing')
                                master.updateGuestUserByAutoDetect();
                            } else {
                                addressMethods.properties.locality = "Nearest Location";
                                master.updateGuestUserByAutoDetect();

                            }
                        });
                    },
                    function (error) {
                        console.error("Error getting location:", error);
                      
                    }
                );
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        })
        $(controls.addrNotListedLabel).unbind('click').on('click', function () {
            $(controls.mapLocationField).removeClass('hide')
            $(controls.postCodeLookup).addClass('hide')
        })
        
        $('.btn-more-address').unbind('click').on('click', function () {
            if ($('#txt-location').val() !== "" && $(addressPrivateMethods.controls.state).val() !== "") {
                //$('.populate-autoselection-primary').html($(addressPrivateMethods.controls.addressLineOne).val())
                //$('.populate-autoselection-secondary').html($(addressPrivateMethods.controls.houseNumber).val())
                $('.address-details').toggleClass('hide')
                $('.address-location-map').toggleClass('hide')
            } else {
                alert('Please select a valid location from map')
            }
          
        })

        $('.changeDeliveryNumber').unbind('click').on('click', function () {
            $('#txt-addr-phoneNumber').attr('disabled', false)
            $('.changeDeliveryNumber').addClass('hide')

        })
        // Function to log messages for checked and unchecked radio buttons
        function changeLocationPref(event) {
            const radioId = event.target.id;
            const checkedStatus = event.target.checked;

            // Log messages based on checked status
            if (checkedStatus) {
                switch (radioId) {
                    case "outsideUk":
                        $(controls.mapLocationField).removeClass('hide')
                        $(controls.postCodeLookup).addClass('hide')
                        addressMethods.properties.outsideUk = true;
                        addressPrivateMethods.initializeAddressAutocompleteUsingGoogleMap();
                        $('.curt_locationwrap').removeClass('hide');
                        $('#getaddress_dropdown').val('open')
                        addressMethods.initializeMap();

                        break;
                    case "withinUk":
                        $(controls.mapLocationField).addClass('hide')
                        $(controls.postCodeLookup).removeClass('hide')
                        addressMethods.properties.outsideUk = false;
                        addressPrivateMethods.initializeAddressAutocompleteUsingGoogleMap();
                        $('.curt_locationwrap').addClass('hide');


                        break;
                    default:
                        console.log('checked withinUk - default')
                        break;
                }
            }
        }

        // Attach event listeners to the radio buttons
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', changeLocationPref);
        });
    };

    addressMethods.initializePage = function () {
        if (master.properties.countryCode != "IN") {
            $(addressPrivateMethods.controls.districtLabel).text("City");
        }
        if (master.properties.countryCode == "UAE") {
            $(addressPrivateMethods.controls.postalcode).removeClass('required');
        }
        addressPrivateMethods.events.initialize();
        addressPrivateMethods.initializeAddressAutocompleteUsingGoogleMap();
        //if (master.properties.countryCode == 'UK' && addressMethods.properties.addressKey != '') {
        //    addressPrivateMethods.initializeAddressAutocomplete();
        //}
        getLocation = function (position) {
            addressPrivateMethods.properties.locationDetails.latitude = position.coords.latitude;
            addressPrivateMethods.properties.locationDetails.longitude = position.coords.longitude;
            addressMethods.initializeMap();
        };

        handleError = function (error) {
            addressMethods.initializeMap();
        }
        //if (navigator.geolocation) {
        //    navigator.geolocation.getCurrentPosition(getLocation, handleError);
        //} else {
        addressMethods.initializeMap();
        addressMethods.getAddrStates();    //}

    };
    return addressMethods;
}();
$(function () {
    address.initializePage();
});