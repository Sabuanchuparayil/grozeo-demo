var productDetails = function () {
    var productDetailsMethods = {
        properties: {
            productid: -1,
            groupid: -1,
            categoryid: -1,
            brid: -1,
            brtypeid: -1,
            variantGroupID: -1
        }
    };
    productDetailsPrivateMethods = {
        events: {},
        properties: {},
        controls: {
            productSlider: '.product-slider',
            productSliderNavaigation: '.slider-nav',
            productZoom: '#zoom_0',
            productThumb: '.product-thumb-img img',
            similarAndOthers: '#dvsimilar_and_others'
        },
        url: {
            similarAndOthers: '/similarproducts/',
            productVariants: '/productvariants'
        }
    };

    productDetailsPrivateMethods.events.onZoom = function (self) {
        var text = self.attr("date-type");
        $('#' + text).elevateZoom({
            zoomType: "inner",
            cursor: "crosshair",
            zoomWindowFadeIn: 500,
            zoomWindowFadeOut: 750
        });
    }

    productDetailsPrivateMethods.initializeSlickSlider = function () {
        var controls = productDetailsPrivateMethods.controls;
        $(controls.productSlider).slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: false,
            fade: true,
            asNavFor: controls.productSliderNavaigation
        });

        $(controls.productSliderNavaigation).slick({
            slidesToShow: 4,
            slidesToScroll: 1,
            asNavFor: controls.productSlider,
            dots: false,
            vertical: true,
            centerMode: true,
            focusOnSelect: true,
            responsive: [
                {
                    breakpoint: 991,
                    settings: {
                        slidesToShow: 9,
                        vertical: false,
                        centerMode: false,
                    }
                },
                {
                    breakpoint: 767,
                    settings: {
                        slidesToShow: 6,
                        vertical: false,
                        centerMode: false,
                    }
                },

                {
                    breakpoint: 450,
                    settings: {
                        slidesToShow: 5,
                        vertical: false,
                        centerMode: false,
                    }
                },

                {
                    breakpoint: 350,
                    settings: {
                        slidesToShow: 3,
                        vertical: false,
                        centerMode: false,
                    }
                },

            ]
        });
    }
    productDetailsPrivateMethods.loadSimilarProducts = function () {
        if (productDetailsMethods.properties.productid > 0 && productDetailsMethods.properties.groupid > 0 && productDetailsMethods.properties.brid > 0) {
            var url = productDetailsPrivateMethods.url.similarAndOthers + productDetailsMethods.properties.productid + '/'
                + productDetailsMethods.properties.brid + '/' + productDetailsMethods.properties.brtypeid + '/'
                + productDetailsMethods.properties.groupid + '/' + productDetailsMethods.properties.categoryid;
            onSuccess = function (result) {
                $(productDetailsPrivateMethods.controls.similarAndOthers).html(result);
            };
            onError = function (data) {
            };
            master.ajax.JSONRequest(url, 'GET', {}, onSuccess, onError);
        }

    }
    productDetailsPrivateMethods.loadProductVariants = function () {
        if (productDetailsMethods.properties.variantGroupID > 0) {
            var details = { productid: productDetailsMethods.properties.productid, variantGroupId: productDetailsMethods.properties.variantGroupID };
            onSuccess = function (data) {
                if (data && data.status == 1) {
                    //var containerTemplate = '<div class="varientslist_wrap w-100 mt-4 p-3 rounded border"><div class="titlewrap mb-2"><h2>Other Varients</h2><div class="seeall_slidercontro"><a href="#" class="seeall m-0">See all</a></div></div><div class="varientslist d-flex flex-wrap">{{VARIANTGROUPS}}</div></div>';
                    var containerTemplate = '<div class="variants_wrap"><h6>Variants</h6><div class="swatch-variants_list">{{VARIANTGROUPS}}</div></div>';
                    //var itemtemplate = '<a href="{{PRODUCTLINK}}" class="varientslist_items"><div class="varientIMG w-100 h-100 d-flex justify-content-center align-items-center"><img src="{{PRODUCTIMAGEURL}}" onerror="this.src=\'/images/p-no-image.png\'"></div></a>';
                    var variants = '';
                    if (data.data)
                        $.each(data.data, function (key, value) {
                            // add to list
                            variants += '<a ' + (value.id == productDetailsMethods.properties.productid ? "" : "href=\"" + value.prodUrl + "\"") + ' class="varientslist_items ' + (value.id == productDetailsMethods.properties.productid ? 'btn-border' : '') + '"><div class="varientIMG w-100 h-100 d-flex justify-content-center align-items-center"><img src="' + value.imgurl + '" onerror="this.src=\'/images/p-no-image.png\'"></div></a>';
                            //variants += itemtemplate.replace("{{PRODUCTLINK}}", value.prodUrl ).replace("{{PRODUCTIMAGEURL}}", value.imgurl);
                        });

                    if (variants != '') {
                        variants = containerTemplate.replace("{{VARIANTGROUPS}}", variants);
                        $('.product-meta').after(variants);
                    }

                    var sizeVariants = '';
                    console.log(data.sizedata)
                    if (data.sizedata)
                        $.each(data.sizedata, function (key, value) {
                            const isActive = value.id === productDetailsMethods.properties.productid;
                            const isStrikeOut = value.strikeOut === true;

                            const classAttr = isActive ? ' class="active"' : '';
                            const styleAttr = isStrikeOut ? ' style="text-decoration: line-through red; opacity: 0.6; pointer-events: none;"' : '';

                            const content = (isActive || isStrikeOut)
                                ? `<a href=""${classAttr}${styleAttr}>${value.unit}</a>`
                                : `<a href="${value.prodUrl}">${value.unit}</a>`;

                            sizeVariants += `<li>${content}</li>`;
                        });

                    if (sizeVariants != '') {
                        sizeVariants = '<div class="swatch-size_wrap"><ul class="swatch-size_list">' + sizeVariants + '</ul></div>';
                        $('.product-meta').after(sizeVariants);
                    }
                }
            };

            onError = function (data) {
                console.log(data);
            };
            master.ajax.JSONRequest(productDetailsPrivateMethods.url.productVariants + '/' + productDetailsMethods.properties.productid + '/' + productDetailsMethods.properties.variantGroupID, 'GET', {}, onSuccess, onError);

        }

    }

    productDetailsPrivateMethods.events.initialize = function () {
        var controls = productDetailsPrivateMethods.controls;

        $(controls.productZoom).elevateZoom({
            zoomType: "inner",
            cursor: "crosshair",
            zoomWindowFadeIn: 500,
            zoomWindowFadeOut: 750
        });

        $(controls.productSliderNavaigation).unbind('click').on("click", function () {
            productDetailsPrivateMethods.events.onZoom($(this));
        });
        productDetailsPrivateMethods.loadProductVariants();
        productDetailsPrivateMethods.loadSimilarProducts();
    };

    productDetailsMethods.initializePage = function () {
        productDetailsPrivateMethods.events.initialize();
        productDetailsPrivateMethods.initializeSlickSlider();
    }
    return productDetailsMethods;

}();
$(function () {
    productDetails.initializePage();
});