(function ($) {
    'use strict';

    $(window).load(function () {
        enable_scroll();
        mobile_preview();
        vpc_follow_toggle();
        wp.hooks.addAction('vpc.ajax_loading_complete', function () {
            enable_scroll();
            mobile_preview();
            vpc_follow_toggle();
        });

        function vpc_follow_toggle() {
            $("#vpc-container .vpc-preview-toggle").toggle(
                    function () {
                        $(".vpc-global-preview").css("display", "none");
                        $("#vpc-follow-scroll").css("background-color", "transparent");
                        $('#vpc-components').css({'margin-top':'25%', 'top':0});
                    },
                    function () {
                        $(".vpc-global-preview").css("display", "block");
                        $("#vpc-follow-scroll").css("background-color", "#fff");
                        var preview_container_height = $("#vpc-follow-scroll").outerHeight();
                        $('#vpc-components').css({'margin-top':preview_container_height}); 
                    }

            );
        }

        function mobile_preview() {
            var windowsize = $(window).width();
            if (windowsize <= 768 && window.innerHeight > window.innerWidth) {
                var wpadminbar = $("#wpadminbar").outerHeight();
                var position = $("header").outerHeight();
                if(wpadminbar != null && wpadminbar != undefined){
                    wpadminbar = wpadminbar;
                }
                else{
                    wpadminbar = 0;
                }
                if(position != null && position != undefined){
                    position = position;
                }
                else{
                    position = 0;
                }
                var total_space= parseInt(wpadminbar) + parseInt(position);
                var price_container_height = $("#vpc-price-container").outerHeight();
                $('#vpc-follow-scroll').css({top:total_space, 'margin-top':price_container_height});
                $(window).bind('touchmove scroll', function () {
                    if ($(window).scrollTop() > total_space) {
                        $('#vpc-follow-scroll').addClass('fixed-preview');
                        $('#vpc-follow-scroll').css({'top':wpadminbar, 'margin-top':0});

                    } else {
                        $('#vpc-follow-scroll').removeClass('fixed-preview');
                        $('#vpc-follow-scroll').css({'top':0, 'margin-top':price_container_height});
                    }
                });

                var preview_container_height = $("#vpc-follow-scroll").outerHeight();
                $('#vpc-components').css({'margin-top':preview_container_height}); 

            } else {
                $('#vpc-follow-scroll').simpleScrollFollow({
                    enabled: false
                });
            }
        }

        function enable_scroll() {
            $('#vpc-follow-scroll').simpleScrollFollow({
                limit_elem: '#vpc-components'
            });
        }


    });

})(jQuery);
