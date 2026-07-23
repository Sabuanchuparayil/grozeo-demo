
var default_avatar = '/images/banner/defaultbannerimg.jpg';
function handleError(image) {
    image.src = default_avatar;
}

$(document).ready(function () {

    if ($('.MainBanner div.swiper-wrapper div.swiper-slide').length <= 0) {
        $('.MainBanner div.swiper-wrapper').html('<div class="swiper-slide"><a href="/offers"><img src="/images/banner/defaultbannerimg.jpg"></a></div> ');
    }
    else {

        $(".MainBanner a").each(function () {
            if ($(this).find('img').length <= 0) {
                var img = document.createElement("img");
                // Set the src attribute of the img element
                img.setAttribute("src", "/images/banner/defaultbannerimg.jpg");
                // Append the img element to the a
                this.appendChild(img);
            }
            else if ($(this).find('img').attr('src') == '') {
                $(this).find('img').attr('src') = default_avatar;
            }
        });
    }
    $(".open-login-modal").unbind("click").on("click", function () {
        $("#login-modal").modal("show");
        setTimeout(function () {
            $("#txt-mobile").focus();
        }, 500);

    });

    /* For responsiveness menu */
    $('header .burger').click(function () {
        $(this).toggleClass('active');
        $('.burger_menu').slideToggle();
        $('body').toggleClass('mobile-menu-visible');
        return false;
    });



    $('header .TopMenuCategoriesTogel').click(function (event) {
        event.stopPropagation();
        $(this).toggleClass('active');
        $('.TopMenuCategories').slideToggle();
        $('.TopMenuCategories').toggleClass('active');
        $('#site-wrapper').toggleClass('menu-visible');

        $(".Profiletogle").hide();
        $('.trigerprofile').removeClass('active');
        $('.Profiletogle').removeClass('active');
        $('#site-wrapper').removeClass('profile-visible');

        $(".Carttoggelwrap").hide();
        $('.Carttoggelwrap').removeClass('active');
        $('.trigerCartMenu').removeClass('active');
        $('#site-wrapper').removeClass('Cart-visible');

        $(".delivery_adress_togle").hide();
        $('.delivery_adress_triger').removeClass('active');
        $('.delivery_adress_togle').removeClass('active');
        $('#site-wrapper').removeClass('delivery_adress_visible');

        $(".BookaSlotToggle").hide();
        $('#site-wrapper').removeClass('BookaSlot-visible');

        $('.ShareTriger').removeClass('active');
        $(".Sharetogle").hide();
    });
    $(".TopMenuCategories").on("click", function (event) {
        event.stopPropagation();
    });

    $('.open-delivery-address').click(function (event) {
        event.stopPropagation();
        $(this).toggleClass('active');
        showSelAddress();
    });

    $(".delivery_adress_togle").on("click", function (event) {
        event.stopPropagation();
    });
    $('header .close_adress_togle').click(function () {
        $(".delivery_adress_togle").hide();
        $('.delivery_adress_togle').removeClass('active');
        $('.delivery_adress_triger').removeClass('active');
        $('#site-wrapper').removeClass('delivery_adress_visible');
    });

    $('.add_new_delvadress').click(function () {
        $(".delivery_adress_togle").hide();
        $('.delivery_adress_togle').removeClass('active');
        $('.delivery_adress_triger').removeClass('active');
        $('#site-wrapper').removeClass('delivery_adress_visible');
    });

    $('.close_togle').click(function () {
        $(".Profiletogle").hide();
        $('.trigerprofile').removeClass('active');
        $('.Profiletogle').removeClass('active');
        $('#site-wrapper').removeClass('profile-visible');
    });





    $('.delivery_adress').click(function () {
        $('.delivery_adress').removeClass('active');
        $(this).addClass('active');
    });




    //BookaSlot
    $('.BookaSlotTriger').click(function (event) {
        event.stopPropagation();
        $(this).next('.BookaSlotToggle').slideToggle();
        $('#site-wrapper').toggleClass('BookaSlot-visible');
    });
    $(".BookaSlotToggle").on("click", function (event) {
        event.stopPropagation();
    });

    $('.TileList ul > li').click(function () {
        $(this).toggleClass('active');
        return false;
    });


    //Share this
    $('.ShareWrap .ShareTriger').click(function (event) {
        event.stopPropagation();
        $(this).toggleClass('active');
        $('.Sharetogle').slideToggle();
        return false;
    });


    $('header .burger').click(function (event) {
        $(".delivery_adress_togle").hide();
        $('.delivery_adress_togle').removeClass('active');
        $('.delivery_adress_triger').removeClass('active');
        $('#site-wrapper').removeClass('delivery_adress_visible');

        $(".TopMenuCategories").hide();
        $('.TopMenuCategoriesTogel').removeClass('active');
        $('.TopMenuCategories').removeClass('active');
        $('#site-wrapper').removeClass('menu-visible');

        $(".Profiletogle").hide();
        $('.trigerprofile').removeClass('active');
        $('.Profiletogle').removeClass('active');
        $('#site-wrapper').removeClass('profile-visible');

        $(".Carttoggelwrap").hide();
        $('.Carttoggelwrap').removeClass('active');
        $('.trigerCartMenu').removeClass('active');
        $('#site-wrapper').removeClass('Cart-visible');
    });

    /* For mobile footer menu */
    $('.mobilefootersec .footer-mobl-btn-categories').click(function () {
        $(this).toggleClass('active');
        $('.TopMenuCategories').slideToggle();
        $('.TopMenuCategories').toggleClass('active');
        $('#site-wrapper').toggleClass('menu-visible');
        $('.burger').addClass('active');
        $('.burger_menu').show();
        return false;
    });
    $('.burger').click(function () {
        $('.mobilefootersec .footer-mobl-btn-categories').removeClass('active');
    });
    $('.TopMenuCategoriesTogel').click(function () {
        $('.mobilefootersec .footer-mobl-btn-categories').toggleClass('active');
    });

    $('.guest-to-login').click(function () {
        $('#search-address-modal').modal('hide');
        $('#login-modal').modal('show');
    });
    
    $(document).on("click", function () {

        $('.mobilefootersec .footer-mobl-btn-categories').removeClass('active');

        $(".TopMenuCategories").hide();
        $('.TopMenuCategoriesTogel').removeClass('active');
        $('.TopMenuCategories').removeClass('active');
        $('#site-wrapper').removeClass('menu-visible');

        $(".delivery_adress_togle").hide();
        $('.delivery_adress_togle').removeClass('active');
        $('.delivery_adress_triger').removeClass('active');
        $('#site-wrapper').removeClass('delivery_adress_visible');

        $(".Profiletogle").hide();
        $('.trigerprofile').removeClass('active');
        $('.Profiletogle').removeClass('active');
        $('#site-wrapper').removeClass('profile-visible');

        $(".Carttoggelwrap").hide();
        $('.Carttoggelwrap').removeClass('active');
        $('.trigerCartMenu').removeClass('active');
        $('#site-wrapper').removeClass('Cart-visible');

        $(".BookaSlotToggle").hide();
        $('#site-wrapper').removeClass('BookaSlot-visible');

        $('.ShareTriger').removeClass('active');
        $(".Sharetogle").hide();

        $('.sortbytogle').hide();
        $('.tigersortby').removeClass('active');


    });


    //updateScrollState function updates the states of the scroll buttons
    function updateScrollState(container) {
        if (!container) return;

        //scrollable content element
        const scrollContent = container.querySelector('.scroll-content');
        const prevBtn = container.querySelector('.prev'); //prev button
        const nextBtn = container.querySelector('.next'); //next button
        if (!scrollContent || !prevBtn || !nextBtn) return; 

        // Checking for scrollable items.
        //hasHorizontalScroll will be true if there are more items to scroll
        const hasHorizontalScroll = scrollContent.scrollWidth > scrollContent.clientWidth;

        // Disable/Hide Arrows based on scrollability
        if (hasHorizontalScroll) {
            // Only show arrows if scrolling is possible
            prevBtn.style.display = 'flex'; 
            nextBtn.style.display = 'flex';

            // Check current scroll position
            const isAtStart = scrollContent.scrollLeft <= 0;
            const isAtEnd = Math.ceil(scrollContent.scrollLeft) >= (scrollContent.scrollWidth - scrollContent.clientWidth);

            prevBtn.classList.toggle('disabled', isAtStart);
            nextBtn.classList.toggle('disabled', isAtEnd);
            prevBtn.setAttribute('aria-disabled', isAtStart);
            nextBtn.setAttribute('aria-disabled', isAtEnd);

        } else {
            // If there's no scroll, hiding both arrows
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
        }
    }
    //Runs once per page load
    $(document).ready(function () {
        const sliders = document.querySelectorAll('.prefered_listswrap');

        // Iterates through the sliders in DOM and updates scroll button based on length of listing
        sliders.forEach(slider => {

            updateScrollState(slider);

            // Listening for resize or responsiveness
            window.addEventListener('resize', () => updateScrollState(slider));

            // Listening for user scrolls
            const scrollContent = slider.querySelector('.scroll-content');
            if (scrollContent) {
                scrollContent.addEventListener('scroll', () => updateScrollState(slider));
            }
        });

        document.querySelectorAll('.slider-controll-arrow').forEach(button => {
            let isScrolling = false;

            button.addEventListener('click', () => {
                const container = button.closest('.prefered_listswrap');
                const scrollContainer = container?.querySelector('.scroll-content');
                if (isScrolling || !scrollContainer) return;

                isScrolling = true;

                const scrollAll = $(container).data('scroll') || false;

                const cards = Array.from(scrollContainer.querySelectorAll('.product-card'));
                if (cards.length === 0) {
                    isScrolling = false;
                    return;
                }

                const cardWidth = cards[0].offsetWidth;
                // Setting Grid gap dynamically
                const gridGap = parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--grid-gap')) || 0;

                const containerWidth = scrollContainer.clientWidth;
                const productsPerRow = parseFloat(getComputedStyle(scrollContainer).getPropertyValue('--section-products-per-row')) || 1;
                const cardsInView = scrollAll ? Math.round(containerWidth / (cardWidth + gridGap)) : 1;

                const scrollAmount = cardsInView * (cardWidth + gridGap);

                const direction = button.classList.contains('next') ? 1 : -1;
                const newScrollLeft = scrollContainer.scrollLeft + direction * scrollAmount;

                const maxScroll = scrollContainer.scrollWidth - containerWidth;
                const clampedScroll = Math.max(0, Math.min(newScrollLeft, maxScroll));
               
                scrollContainer.scrollTo({ left: clampedScroll, behavior: 'smooth' });

                  setTimeout(() => {
                    isScrolling = false;
                    updateScrollState(container);
                }, 350);

            });
        });
    });



    /*Slider*/

    var HomeCategorySliderswiper = new Swiper(".HomeCategorySlider", {
        // Default parameters
        slidesPerView: 12,
        spaceBetween: 15,

        navigation: {
            nextEl: ".HomeCategorySliderwrap .swiper-button-next",
            prevEl: ".HomeCategorySliderwrap .swiper-button-prev",
        },
        breakpoints: {
            0: {
                //loop: true,
                slidesPerView: 'auto',
                spaceBetween: 10,
            },
            1050: {
                //loop: true,
                slidesPerView: 'auto',
                spaceBetween: 10,
            },
            1080: {
                slidesPerView: 10,
                spaceBetween: 10,
            },
        },

    });

    var subcategory_menu_sliderswiper = new Swiper(".subcategory_menu_slider", {
        // Default parameters
        slidesPerView: 9,
        spaceBetween: 19,

        navigation: {
            nextEl: ".subcategory_menu_sliderwrap .swiper-button-next",
            prevEl: ".subcategory_menu_sliderwrap .swiper-button-prev",
        },
        breakpoints: {
            0: {
                loop: false,
                slidesPerView: 'auto',
                spaceBetween: 10,
            },
            1024: {
                loop: false,
                slidesPerView: 'auto',
                spaceBetween: 10,
            },
            1200: {
                slidesPerView: 9,
            },
        },

    });

    $('.subcategory_menu_sliderwrap .catogeryAllIn_ListItems').click(function () {
        $('.subcategory_menu_sliderwrap .catogeryAllIn_ListItems').removeClass('active');
        $(this).addClass('active');
    });

    if ($(".MainBanner_Slider .swiper-slide").length == 3) {
        $('.MainBanner_Slider .swiper-wrapper').addClass("disabled");
        $('.paginationwrap').addClass("d-none")
        // $('.MainBanner_Slider .swiper-button-next').hide();
        //$('.MainBanner_Slider .swiper-button-prev').hide();
    }
    $('.MainBanner_Slider .swiper-wrapper').hover(function () {
        MainBanner_Sliderswiper.autoplay.stop();
    }, function () {
        MainBanner_Sliderswiper.autoplay.start();
    });



    var DealoftheDaySliderswiper = new Swiper(".DealoftheDaySlider", {
        slidesPerView: 6,
        spaceBetween: 0,

        navigation: {
            nextEl: ".DaySlider .swiper-button-next",
            prevEl: ".DaySlider .swiper-button-prev",
        },
        breakpoints: {
            0: {
                loop: false,
                slidesPerView: 'auto',
                spaceBetween: 2,
            },
            1024: {
                loop: false,
                slidesPerView: 'auto',
                spaceBetween: 2,
            },
            1200: {
                slidesPerView: 5,
            },
            1400: {
                slidesPerView: 6,
            },
        },

    });


    var shop_by_group_slider = new Swiper(".shop_by_group_slider", {
        slidesPerView: "auto",
        spaceBetween: 15,

        navigation: {
            nextEl: ".shop_by_group_sec .swiper-button-next",
            prevEl: ".shop_by_group_sec .swiper-button-prev",
        },


    });

    if ($(".shop_by_group_slider .swiper-slide").length < 7) {
        $('.shop_by_group_slider .swiper-wrapper').addClass("slider_item_center");
        $(".shop_by_group_slider .swiper-wrapper").addClass("group_slider_count_" + $('.shop_by_group_slider .swiper-slide').length);
    }
    if ($(".HomeCategorySlider .swiper-slide").length < 10) {
        $('.HomeCategorySlider .swiper-wrapper').addClass("slider_item_center");
        $(".HomeCategorySlider .swiper-wrapper").addClass("catogery_slider_count_" + $('.HomeCategorySlider .swiper-slide').length);
    }

    if ($(".itemsListingWrap.dealoftheday_list ul.itemsListing > li").length < 6) {
        $(".itemsListingWrap.dealoftheday_list ul.itemsListing").addClass("justify-content-center");
        $(".itemsListingWrap.dealoftheday_list ul.itemsListing").addClass("itemsListing_count_" + $('.itemsListingWrap.dealoftheday_list ul.itemsListing > li').length);
    }

    if ($(".store_list_wrap_sec .store_list_wrap").length < 4) {
        $(".store_list_wrap_sec").addClass("justify-content-center");
        $(".store_list_wrap_sec").addClass("store_list_count_" + $('.store_list_wrap_sec .store_list_wrap').length);
    }

    var EveryDayOfferSliderswiper = new Swiper(".EveryDayOfferSlider", {
        slidesPerView: 1,
        spaceBetween: 0,
        speed: 4000,
        autoplay: {
            delay: 2500,
        },

        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },

    });
    $('.EveryDayOfferSlider').hover(function () {
        EveryDayOfferSliderswiper.autoplay.stop();
    }, function () {
        EveryDayOfferSliderswiper.autoplay.start();
    });

    //var mobileoffersliderwiper = new Swiper(".mobileofferslider", {
    //    loop: true,
    //    slidesPerView: 'auto',
    //    spaceBetween: 15,
    //    speed: 4000,
    //    autoplay: {
    //        delay: 2500,
    //    },

    //});



    var PopularProductSlideswiper = new Swiper(".PopularProductSlide", {
        slidesPerView: 6,
        spaceBetween: 0,

        navigation: {
            nextEl: ".PopularSlide .swiper-button-next",
            prevEl: ".PopularSlide .swiper-button-prev",
        },
        breakpoints: {
            0: {
                loop: false,
                slidesPerView: 'auto',
                spaceBetween: 2,
            },
            1024: {
                loop: false,
                slidesPerView: 'auto',
                spaceBetween: 2,
            },
            1200: {
                slidesPerView: 5,
            },
            1400: {
                slidesPerView: 6,
            },
        },

    });

    var YourListSliderswiper = new Swiper(".YourListSlider", {
        slidesPerView: 6,
        spaceBetween: 0,

        navigation: {
            nextEl: ".YourListSlideBTN .swiper-button-next",
            prevEl: ".YourListSlideBTN .swiper-button-prev",
        },
        breakpoints: {
            0: {
                loop: false,
                slidesPerView: 'auto',
                spaceBetween: 2,
            },
            1024: {
                loop: false,
                slidesPerView: 'auto',
                spaceBetween: 2,
            },
            1200: {
                slidesPerView: 5,
            },
            1400: {
                slidesPerView: 6,
            },
        },

    });

    var Branches_Sliderswiper = new Swiper(".Branches_Slider", {
        slidesPerView: 'auto',
        spaceBetween: 10,
        centerMode: true,

        navigation: {
            nextEl: ".Branches_Slider_wrap .swiper-button-next",
            prevEl: ".Branches_Slider_wrap .swiper-button-prev",
        },


    });

    /* For Branches select  */
    $('.Branches_Slider .swiper-slide > a').click(function () {
        $(this).toggleClass('select');
    });


    var itemsMenuSliderswiper = new Swiper(".itemsMenuSlider", {
        slidesPerView: 'auto',
        spaceBetween: 15,
        loop: false,

        navigation: {
            nextEl: ".itemsMenuSlidingwrap .swiper-button-next",
            prevEl: ".itemsMenuSlidingwrap .swiper-button-prev",
        },
    });

    $('.itemsMenuSlider .swiper-slide a').click(function () {
        $('.itemsMenuSlider .swiper-slide a').removeClass('active');
        $(this).addClass('active');
    });


    var cartListingsliderswiper = new Swiper(".cartListingslider", {

        pagination: {
            el: ".swiper-pagination",
            dynamicBullets: false,
            clickable: true,
        },

    });





    //Subcategory
    $('.Subcategory_menu_Items_triger').click(function () {
        $(this).parent().siblings().find('.Subcategory_menu_Items_toggel').slideUp();
        $(this).siblings().removeClass('active');
        $(this).parent().siblings().find('.Subcategory_menu_Items_triger').removeClass('active');
        $(this).siblings().slideToggle('.Subcategory_menu_Items_toggel');
        $(this).toggleClass('active');
    });


    /* For sortb filter */
    $('.tigersortby').click(function () {
        $(this).toggleClass('active');
        $('.sortbytogle').slideToggle();
        return false;
    });


    //affix

    var topofHeader = $("#site-wrapper > header").offset().top;

    $(window).scroll(function () {

        if ($(window).scrollTop() > (topofHeader)) {

            $("#site-wrapper > header").addClass(' affix');

        }

        else {

            $("#site-wrapper > header").removeClass(' affix');

        }

    });


    //Mach Heights
    $.fn.machHeights = function () {
        var max_height = 0;
        $(this).each(function () {
            max_height = Math.max($(this).height(), max_height);
        });
        $(this).each(function () {
            $(this).height(max_height);
        });
    };

    /* $('.DealSliderWrap .productTitle').machHeights();
    $('.PopularSliderWrap .productTitle').machHeights();
    $('.YourListSliderWrap .productTitle').machHeights(); */

    $('.group_items h3').machHeights();
    $('.Branches_Slider .swiper-slide a').machHeights();

    //Mach Heights End

    //alert message close
    $('.close_alert').click(function () {
        $(this).parents('.grozeo-alert').fadeOut(300);
    });

    $('.addtowishlist').click(function () {
        $(this).toggleClass('active');
        return false;
    });
    //To open and close accordion in search (Category list page)
    $('.dropdownslider-card_head').click(function () {
        const menuList = $(this).next('.dropdownslider-card_body'); // The clicked menu
        $(this).next().slideToggle(300);
        $(this).toggleClass('active');
    });
    //==product layout view switch function==//

    $('.prodt-view-layout-switch').on('click', '.view-switch-button', function () {
        var v = $(this).data('view'), $c = $('.products-collection-view');
        $('.view-switch-button').removeClass('is-active');
        $(this).addClass('is-active');
        $c.removeClass('collection-listview collection-grid-two collection-grid-three collection-grid-four collection-grid-five collection-grid-six');
        $c.addClass('collection-' + (v === 'list' ? 'listview' : v));
    });
    //==product layout view switch function end==//
    var $li = $('.orderlistitemswrap li').hover(
        function () {
            var self = this;
            hovertimer = setTimeout(function () {
                $(self).parent('.ordertemsinfowrap').parent('.orderlistitems-info').parent('.orderlistitems').parent('li').children('.orderlistitems-btn').children('.cancel-order').hide();
                $(self).parent('.ordertemsinfowrap').parent('.orderlistitems-info').parent('.orderlistitems').parent('li').children('.orderlistitems-btn').children('.trackorder').css("display", "block");
            }, 10500);
        }
    );

    if ($("header .delivery_adress_wap li").length > 1) {
        $('header .delivery_adress_wap').addClass("moreadress");
    }

    // preferredstoreslider

    var preferredstoreslider = new Swiper(".preferredstoreslider", {

        navigation: {
            nextEl: ".preferredstoreslider .swiper-button-next",
            prevEl: ".preferredstoreslider .swiper-button-prev",
        },
        breakpoints: {
            0: {
                slidesPerView: 'auto',
                spaceBetween: 0,
            },
            480: {
                slidesPerView: 2,
                spaceBetween: 0,
            },
            767: {
                slidesPerView: 3,
                spaceBetween: 0,
            },
            1080: {
                slidesPerView: 4,
                spaceBetween: 0,
            },
            1260: {
                slidesPerView: 5,
                spaceBetween: 0,
            },
        },
    });

    if ($(".preferredstoreslider .swiper-slide").length < 5) {
        $('.preferredstoreslider .swiper-wrapper').addClass("slider_item_center");
    }

    // for header menu tab active 
    const body = document.querySelector('body');
    function handleScroll() {
        if (window.scrollY === 0) {
            body.classList.add('sit_scrolled_top');
        } else {
            body.classList.remove('sit_scrolled_top');
        }
    }
    window.addEventListener('scroll', handleScroll);

    //reaload 
    const header = document.querySelector('header');
    function checkScrollPosition() {
        if (window.scrollY === 0) {
            header.classList.add('menutab_reld');
        } else {
            header.classList.remove('menutab_reld');
        }
    }
    window.onload = checkScrollPosition;
    // end header menu tab 


});

function LogoIMGError(obj) {
    if (!obj)
        obj = $('.desktoplogo_img');
    $(obj).closest('span.desktoplogo').hide();
    $(obj).hide();
    var logoalt = $(obj).attr("alt");
    $(obj).closest('a').append("<span class='logotext' title><i class='fa-solid fa-store me-2'></i><span>" + logoalt + "</span></span>");
}

function showSelAddress() {
    $('.delivery_adress_togle').slideToggle();
    $('.delivery_adress_togle').toggleClass('active');
    $('#site-wrapper').toggleClass('delivery_adress_visible');

    $(".TopMenuCategories").hide();
    $('.TopMenuCategoriesTogel').removeClass('active');
    $('.TopMenuCategories').removeClass('active');
    $('#site-wrapper').removeClass('menu-visible');

    $(".Profiletogle").hide();
    $('.trigerprofile').removeClass('active');
    $('.Profiletogle').removeClass('active');
    $('#site-wrapper').removeClass('profile-visible');

    $(".Carttoggelwrap").hide();
    $('.Carttoggelwrap').removeClass('active');
    $('.trigerCartMenu').removeClass('active');
    $('#site-wrapper').removeClass('Cart-visible');

    $(".BookaSlotToggle").hide();
    $('#site-wrapper').removeClass('BookaSlot-visible');

    $('.ShareTriger').removeClass('active');
    $(".Sharetogle").hide();
}