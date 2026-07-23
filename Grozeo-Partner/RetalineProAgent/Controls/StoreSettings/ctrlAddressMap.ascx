<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="ctrlAddressMap.ascx.cs" Inherits="RetalineProAgent.Controls.StoreSettings.ctrlAddressMap" %>
<style>
    div.pac-container {
    z-index: 99999999999 !important;
}
</style>

<div class="modal fade" id="ADDRESS">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content tx-size-sm">
            <%--<div class="modal-header">
              <h4 class="modal-title">Locate your store</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>--%>
            <div class="modal-body pd-20">

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <h5 class="modal-title tx-dark mb-3">Search Location</h5>
                <div class="row row-sm">
                    <% if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
                        { %>
                    <div class="form-group w-100">
                        <div class="position-relative postcode_lookup_wrap">
                            <div class="d-flex align-items-end searchaddressfilde mb-3 input-field" id="postcode_lookup">
                                <span class="position-absolute end-0 addr-not-listed hide" id="addr-not-listed">My address not found here</span>
                            </div>
                        </div>
                    </div>
                    <% } %>
                    <div class="col-12">
                        <div class="w-100 input-group d-flex flex-nowrap">
                            <div class="w-100 <%= (ConfigurationManager.AppSettings.Get("CountryCode") == "UK" ? "hide" : "") %>"
                            id="map-location-field">
                            <div class="input-field">
                                <label class="mb-0 mr-2"><%= (ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "Search Location" : "Enter a location") %></label>
                            </div>

                            <div class="form-group form-group1 searchlocation d-flex flex-wrap flex-sm-nowrap">
                                <asp:TextBox ID="txtLocation" Style="max-width: 100%;" runat="server" CssClass="form-control required mb-0 mr-0 mr-sm-3"></asp:TextBox>
                                <span class="your_location d-flex align-items-center justify-content-center border mt-2 mt-sm-0">
                                    <i class="fa-solid fa-location-crosshairs mr-1"></i>
                                    <span>Get Location</span>
                                </span>
                            </div>
                        </div>
                        </div>
                        
                        <div id="map"></div>
                        <div class="map_canvas"></div>
                    </div>
                    <div class="col-12">
                        <div class="modal-footer pb-0 d-flex justify-content-center">
                            <button type="button" class="btn btn-drk-green btn-primary mr-2" data-dismiss="modal" onclick="saveChanges()" aria-label="Close">Submit</button>
                            <button type="button" class="btn btn-drk-green btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <%--<div id="getaddress_error_message" class="btn position-absolute end-0 addr-not-listed hide-">My address not found here</div>--%>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<% if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
        { %>

<script src="https://cdn.getaddress.io/scripts/getaddress-find-2.0.0.min.js"></script>
<script>
    var fullLocationAddresss = '';
    var locationDetails = null;
    var map = null;
    var markers = [];
    var locationDetails = {
        street_number: "",
        route: "",
        locality: "",
        administrative_area_level_1: "",
        country: "",
        postal_code: "",
        latitude: <%= Lat %>,
        longitude: <%= Lng %>
    };
    var myLatlng = new google.maps.LatLng(locationDetails.latitude, locationDetails.longitude);
    var geocoder = new google.maps.Geocoder();

    var initializeMap = function () {
        $('#map').css('height', '300px');
        $('#map').css('width', '100%');
        $('#map').css('overflow', 'unset');

        var mapOptions = {
            center: myLatlng,
            zoom: 17,
            scrollwheel: true,
            draggable: true,
            panControl: false,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle.SMALL
            },
            mapTypeControl: false,
            scaleControl: false,
            streetViewControl: false,
        };

        map = new google.maps.Map(document.getElementById('map'),
            mapOptions);

        var marker = new google.maps.Marker({
            position: myLatlng,
            map: map,
        });
        google.maps.event.addListener(map, 'center_changed', function () {
            window.setTimeout(function () {
                var center = map.getCenter();
                marker.setPosition(center);
            }, 100);
        });

        google.maps.event.addListener(map, 'dragend', function () {
            window.setTimeout(function () {
                geocodePosition(marker.getPosition());
            }, 100);
        });
    };

    // Function to handle address selection from postcoder dropdown
    document.addEventListener("getaddress-find-address-selected", function (e) {
        const selectedAddress = e.address;

        // Update map and location details with the selected address
        updateMapWithAddress(selectedAddress);
    });

    // Function to update map with the selected address
    function updateMapWithAddress(address) {
        var selectedLatlng = new google.maps.LatLng(address.latitude, address.longitude);

        // Clear existing markers
        clearMarkers();

        // Center the map at the selected address
        map.setCenter(selectedLatlng);

        // Set marker at the selected address
        var marker = new google.maps.Marker({
            position: selectedLatlng,
            map: map,
        });

        // Store the marker in a global variable for later use
        markers.push(marker);

        // Geocode the position to fetch address details
        geocodePosition(marker.getPosition());
    }

    // Function to clear all markers from the map
    function clearMarkers() {
        for (var i = 0; i < markers.length; i++) {
            markers[i].setMap(null);
        }
        markers = [];
    }

    // Initialize the map
    initializeMap();

    // Function to geocode position
   function geocodePosition(pos) {
        geocoder.geocode({
            latLng: pos
        }, function (responses) {
            if (responses && responses.length > 0) {
                var addr = responses[0].formatted_address;
                var newAddr = responses[0].formatted_address.split(',');
                newAddr.shift();
                $('#<%= txtLocation.ClientID %>').val(newAddr.join(', '));
                fullLocationAddresss = responses[0].formatted_address;
                fillInAddressProperties(responses[0]);
            } else {
                //updateMarkerAddress('Cannot determine address at this location.');=[[]]
            }
        });
    }

    getAddress.find(
        'postcode_lookup',
        '<%= ConfigurationManager.AppSettings.Get("GetAddressIOAPIKey")%>',
        {
            input: {
                id: 'getaddress_input', /* The id of the textbox' */
                name: 'getaddress_input', /* The name of the textbox' */
                class: 'form-control mr-2', /* The class of the textbox' */
                label: 'Enter your Postcode' /* The label of the textbox' */
            },
            button: {
                id: 'getaddress_button', /* The id of the botton' */
                class: 'btn btn-primary mr-2', /* The class of the botton' */
                label: 'Find', /* The label of the botton' */
                disabled_message: 'Fetching Address'
            },
            dropdown: {
                id: 'getaddress_dropdown', /* The id of the dropdown' */
                class: 'form-control mt-2 mt-sm-0 w-100', /* The class of the dropdown' */
                select_message: 'Select your Address', /* The select message of the dropdown' */
                template: '' /* The suggestion template of the dropdown' (see Autocomplete API)*/
            },
            error_message: {
                id: 'getaddress_error_message',
                not_found: 'My address not found here',
                class: 'btn position-absolute end-0 addr-not-listed hide-'
            }
        }
    );


    document.addEventListener("getaddress-find-address-selected", function (e) {
        const addressString = [
            e.address.line_1,
            e.address.line_2,
            e.address.line_3,
            e.address.district,
        ].join(", ");
        console.log(addressString);
        $('#<%= txtLocation.ClientID %>').val(addressString);

        $("#<%= ParentAddrClientId %>").val(e.address.line_1 || "");
        $("#<%= ParentAddr2ClientId %>").val(e.address.line_2 || "");
        $("#<%= ParentAddr3ClientId %>").val(e.address.line_3); // addr.line_3
        //$("#txt-addressline-two").val(addr.line_4);
        $("#<%= ParentPinClientId %>").val(e.address.postcode || "");


    });

    $(function () {
        // Function to show the addr-not-listed span and map-location-field
        function showAddrNotListed() {
            $('#addr-not-listed').removeClass('hide');
        }

        // Check if the getaddress_dropdown element exists initially
        if ($('#getaddress_dropdown').length > 0) {
            showAddrNotListed();
        }

        // Listen for changes in the DOM
        $(document).on('DOMNodeInserted', function (e) {
            // Check if the inserted node is the getaddress_dropdown element
            if ($(e.target).is('#getaddress_dropdown')) {
                showAddrNotListed();
            }
        });

        // Handle the click event on the error message
        $('#addr-not-listed').on('click', function () {
            $('#postcode_lookup').addClass('hide'); // Hide the postcode lookup
            $('#getaddress_dropdown').addClass('hide'); // Hide the dropdown
            $('#map-location-field').removeClass('hide'); // Show the map location field
        });
    });

</script>  
<script type="text/javascript">
    const setCurrentLocation = function () {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                myLatlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
                setCurrentLocation();
            });
        }
        map.setCenter(myLatlng);
        geocodePosition(myLatlng);
        $('.your_location').addClass('active');
    };

    $('.your_location').unbind('click').on('click', function () {
        setCurrentLocation();
    });

</script>
<%} %>
<script type="text/javascript">
    var fullLocationAddresss = '';
    var locationDetails = null;
    var map = null;
    var locationDetails = {
        street_number: "",
        route: "",
        locality: "",
        administrative_area_level_1: "",
        country: "",
        postal_code: "",
        latitude: <%= Lat %>,
        longitude: <%= Lng %>
    };
    var myLatlng = new google.maps.LatLng(locationDetails.latitude, locationDetails.longitude);
    var geocoder = new google.maps.Geocoder();
   
    var initializeMap = function () {
        $('#map').css('height', '300px');
        $('#map').css('width', '100%');
        $('#map').css('overflow', 'unset');

        //var myLatlng = new google.maps.LatLng(addressPrivateMethods.properties.locationDetails.latitude, addressPrivateMethods.properties.locationDetails.longitude);
        var mapOptions = {
            center: myLatlng,
            zoom: 17,
            scrollwheel: true,
            draggable: true,
            panControl: false,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle.SMALL
            },
            mapTypeControl: false,
            scaleControl: false,
            streetViewControl: false,
        };

        map = new google.maps.Map(document.getElementById('map'),
            mapOptions);

        var marker = new google.maps.Marker({
            position: myLatlng,
            map: map,
            //title: 'Hello World!'
        });
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
            window.setTimeout(function () {
                //var center = map.getCenter();
                //marker.setPosition(center);
                geocodePosition(marker.getPosition());

            }, 100);
        });


    };


    var autocomplete = new google.maps.places.Autocomplete(
        document.getElementById('<%= txtLocation.ClientID %>'),
        { types: [], fields: ["address_components", "geometry"], componentRestrictions: { country: "<%= ConfigurationManager.AppSettings.Get("CountryCode")??"IN" %>" } }
    );
    

    var fillInAddress = function () {
        var place = autocomplete.getPlace();
        fillInAddressProperties(place);
        $('.searchlocation').addClass('searchlocationselected');
        //$('.searchlocation input.form-control').prop('disabled', true);
        initializeMap();
        if (place.geometry.viewport) {
            map.fitBounds(place.geometry.viewport);
        } else {
            map.setCenter(place.geometry.location);
            map.setZoom(17);
        }
        $('#<%= ParentLatClientId %>').val(place.geometry.location.lat());
        $('#<%= ParentLongClientId %>').val(place.geometry.location.lng());

    };
    autocomplete.setFields(["address_component, geometry"]);
    autocomplete.addListener("place_changed", fillInAddress);

    var fillInAddressProperties = function (place) {

        fullLocationAddresss = '';
        locationDetails = {
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
        place.address_components.forEach(comp => {
            comp.types.forEach(type => {
                if (locationDetails.hasOwnProperty(type)) {
                    locationDetails[type] = comp.long_name;
                    addressArray.push(comp.long_name);
                }
            });
        });

        const house = locationDetails.subpremise || locationDetails.premise || locationDetails.street_number;
        const street = locationDetails.route || locationDetails.neighborhood || "";
        const lineOne = (locationDetails.route !== null ? locationDetails.neighborhood : null) || locationDetails.locality || locationDetails.postal_town || "";
        const lineTwo = locationDetails.sublocality || locationDetails.administrative_area_level_2 || locationDetails.administrative_area_level_3 || "";

        const district = locationDetails.locality || locationDetails.postal_town || locationDetails.administrative_area_level_3 || "";
        const county = locationDetails.administrative_area_level_1;

        var pin = locationDetails.postal_code;
        var locationName = ''; var addr1 = ''; var dst = lineTwo; var stt = district; var country = county; var locality = '';
        locality = locationDetails.administrative_area_level_3;

        $('#<%= ParentPinClientId %>').val(pin);
        $('#<%= ParentLocationNameClientId %>').val(house);
        $('#<%= ParentDistrictClientId %> ').val(dst);
                $('#<%= ParentStateClientId %>').val(stt);
                $('#<%= ParentCountryClientId %>').val(country);
        $('#<%= ParentLocalityClientId %>').val(locality);
    }


    function geocodePosition(pos) {
        $('#<%= ParentLatClientId %>').val(pos.lat());
        $('#<%= ParentLongClientId %>').val(pos.lng());
                //addressPrivateMethods.properties.hasSelected = true;
        geocoder.geocode({
            latLng: pos
        }, function (responses) {
            if (responses && responses.length > 0) {
                var addr = responses[0].formatted_address;
                 var newAddr = responses[0].formatted_address.split(',');

                newAddr.shift();
                /*console.log(newAddr);*/
                $('#<%= txtLocation.ClientID %>').val(newAddr.join(', '));
                //addressPrivateMethods.properties.hasSelected = true;
                fullLocationAddresss = responses[0].formatted_address;//addressArray.join();
                fillInAddressProperties(responses[0]);

            } else {
                //updateMarkerAddress('Cannot determine address at this location.');=[[]]
            }
        });
    }


    initializeMap();

    $('#ADDRESS').on('shown.bs.modal', function (e) {
        $('#<%= txtLocation.ClientID %>').focus();
    });
    $('#<%= txtLocation.ClientID %>').unbind('change').on('change', function () {
        $('#<%= ParentLocationClientId %>').val($(this).val());
    });
    function saveChanges() {
        if ($('#<%= ParentStateClientId %>').val() != '')
            selectstate($('#<%= ParentStateClientId %>').val());
    }
</script>
<script type="text/javascript">
    const setCurrentLocation = function () {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                myLatlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
                setCurrentLocation();
            });
        }
        map.setCenter(myLatlng);
        geocodePosition(myLatlng);
        $('.your_location').addClass('active');
    };

    $('.your_location').unbind('click').on('click', function () {
        setCurrentLocation();
    });

</script>






