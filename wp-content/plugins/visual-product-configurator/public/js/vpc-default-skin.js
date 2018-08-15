var VPC_CONFIG = (function ($, vpc_config) {
  'use strict';
  var element3 = $('#vpc-form-builder-wrap');
  var limitY = 0;

  function vpc_preview_follow_scroll(){
        var element = $('#vpc-preview-wrap'); // element2 = $('.VPC_ln_Skin .vpc-component .vpc-component-content');
        var elementY, fullHeight;

    if(  typeof element3 !== 'undefined' && element.length) {
      elementY = element.offset().top;
      fullHeight = element.innerHeight(); // + 20; //  20 for slider nav margin top
    }
    console.log("element3.length : "+element3.length);
    if(  typeof element3 !== 'undefined' && element3.length ) {
      limitY = element3.offset().top;
    }

    var scrollTop;
    // Space between element and top of screen (when scrolling)
    var topMargin = 0;

    // Should probably be set in CSS; but here just for emphasis
    // element.css('position', 'relative');
    if(  typeof element3 !== 'undefined' && element.length ) {
      //$(document).on("scroll", 'body', function (e) {

      window.addEventListener('scroll', function(e){
        scrollTop = $(window).scrollTop();

        if(scrollTop < elementY) {
          element.stop(false, false).animate({
            top: 0
          }, 100);

        }
        //console.log((scrollTop + fullHeight + topMargin) < limitY);
        if(scrollTop > elementY && (scrollTop + fullHeight + topMargin) < limitY) {
          element.stop(false, false).animate({
            top: scrollTop - elementY + topMargin
          }, 100);

        }
        if(limitY < (scrollTop + fullHeight + topMargin)) {
          element.stop(false, false).animate({
            top: 'auto'
          }, 100);

        }

      });

    }

  }

  $(document).ready(function () {

   // wp.hooks.addAction('vpc.ajax_loading_complete', vpc_ds_load_tooltips);

    // wp.hooks.addAction('vpc.ajax_loading_complete', vpc_preview_follow_scroll);

    vpc_ds_load_tooltips();

    $(document).on("change", ".vpc-options input", function (e) {
      //e.preventDefault();
      //            console.log(e.originalEvent);
      wp.hooks.doAction('vpc.option_change', $(this), e);
      //To avoid unecessary server solicitation, we won't trigger the change if it's not a manual click
      //            if(typeof e.originalEvent !== 'undefined')
      //            {
      var selector = $(this).attr("id");
      vpc_build_preview();
      vpc_apply_rules("#" + selector);
      //            }
      var checked_elements_values = $(this).parents(".vpc-options").find(":input:checked").map(function () {
        return $(this).val();
      }).get().join(' ');
      ;
      var checked_elements_img = $(this).parents(".vpc-options").find(":input:checked").map(function () {
        if ($(this).data('icon'))
        return "<img src='" + $(this).data('icon') + "'>";
        else
        return "";
      }).get().join('');
      ;
      //            console.log(checked);
      $(this).parents('.vpc-component').find('.vpc-selected-icon').html(checked_elements_img);
      $(this).parents('.vpc-component').find('.vpc-selected').html(checked_elements_values);
    });

    //Spot the checked element right before the checked item changes. That way we can trigger the reverse rules
    //$("label").on("mousedown", function() {
    $(document).on("mousedown", "label.custom", function (e) {
      //We trigger the change even for previously checked items
      var element = $(this);
      //            var about_to_change_id=element.parent().parent().find("input:checked").attr("id");
      var about_to_change_id = element.parents('.vpc-options').find("input:checked").attr("id");
      if(vpc.trigger){
        setTimeout(
          function () {
            $("#" + about_to_change_id).trigger("change");
          }, 200);
        }
      });


      $(document).on("click", ".vpc-component-header", function (e) {
        if(typeof(vpc.config["components-behavior-on-click"]!="undefined")&& vpc.config["components-behavior-on-click"]=="close-others")
        {
          $('.vpc-options').hide();
        }
        $(this).parents('.vpc-component').find('.vpc-options').slideToggle('fast');
      });
      if (vpc.wvpc_conditional_rules.length == 0)
        vpc_build_preview();
      //        else
        vpc_load_options();


      //scroll_follow code begins here

      $(document).on("click", "#vpc-components .vpc-component", function (e) {
        var element3 = $('#vpc-form-builder-wrap');
        //$("#vpc-components .vpc-component").on("click", function() {
        if( typeof element3 !== 'undefined' && element3.length ) {
          setTimeout(function(){
            limitY = element3.offset().top;
          }, 150);
        }
      });


      $(window).on('load', function() {
        setTimeout(function(){
          vpc_preview_follow_scroll();
        },2000);
      });

    });

    return vpc_config;
  }(jQuery, VPC_CONFIG));
