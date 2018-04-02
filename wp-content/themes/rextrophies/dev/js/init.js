jQuery(document).ready(function($) {

	/* =============================================================================
	Slider functions
	============================================================================= */

	// $('.ww-slider').slick({
  //   	accessibility: true,
  //   	pauseOnHover: false,
  //   	// autoplay: true,
  //   	autoplaySpeed: 7000,
  //   	fade: true,
  //   	arrows: false //** the arrows cause horizontal scrolling **//
  // 	});



	( function( window, $, undefined ) {
	'use strict';

	$( '.nav-compact' ).before( '<button class="menu-toggle" role="button" aria-pressed="false"><i class="icon-menu"></i></button>' ); // Add toggles to menus
	console.log("menu toggle ******");

	// Show/hide the navigation
	$( '.menu-toggle' ).on( 'click', function() {
		var $this = $( this );
		$this.attr( 'aria-pressed', function( index, value ) {
			return 'false' === value ? 'true' : 'false';
			});

			$( '.menu-toggle i' ).attr( 'class', function( index, value ) {
				return 'icon-menu' === value ? 'icon-cross' : 'icon-menu';
				});


					// $('body').toggle(function () {
				  //   $('body').css({overflow: "hidden", height: $('.site-header').height()});
					// 	}, function () {
					// 	    $('body').css({overflow: "", height: "100%"});
					// });
			$this.toggleClass( 'activated' );


			// You'll need to enqueue this (lib/scripts.php) to use switchClass
			// $( '.menu-toggle i' ).switchClass( "icon-menu", "icon-cross", 1500, "easeInOutQuad" );
			$this.next( '.nav-compact' ).slideToggle( 'fast' );


			});


	})( this, jQuery );


	(hideMenu = function () {

		var obj = $( '.nav-compact' );
		var obj_primary = $( '.nav-primary' );
		var btn = $('.menu-toggle');

		if($(window).width() <= 865 ) // This should match "mama-bear" in the CSS minus 15 pixels for the browser scroll gutter
		{
			obj.hide();
			btn.show();
			obj_primary.hide();
			$( '.menu-toggle i' ).attr( 'class', 'icon-menu');
		}
		else
		{
			obj.hide();
			btn.hide();
			obj_primary.show();
		}
		// console.log("first timer");

	})(); // Run instantly

	$(window).on("orientationchange", hideMenu);

	$(window).resize(menuswitch);
	function menuswitch() {
		// Store the window width
    var windowWidth = $(window).width();

		var obj = $( '.nav-compact' );
		var obj_primary = $( '.nav-primary' );
		var btn = $('.menu-toggle');

		// Check window width has actually changed and it's not just iOS triggering a resize event on scroll
		// if ($(window).width() != windowWidth) {
			if($(window).width() <= 865 ) // This should match "mama-bear" in the CSS minus 15 pixels for the browser scroll gutter
			{
				obj.hide();
				btn.show();
				obj_primary.hide();
				$( '.menu-toggle i' ).attr( 'class', 'icon-menu');
			}
			else
			{
				obj.hide();
				btn.hide();
				obj_primary.show();
			}
			// console.log("been around the block");
		}


//
//
//Trophy Form
function hideForms(){
$('#wpcf7-f96-o1, #wpcf7-f97-o2, #wpcf7-f98-o3, #wpcf7-f99-o4, #wpcf7-f261-o5, #wpcf7-f262-o6').hide();
// $(".option-field select").removeClass('wpcf7-validates-as-required');
// $(".option-field select").attr('aria-required', 'false');
}
hideForms();

$('#style-selector').on('change',function(){
    var selection = $(this).val();
		hideForms();
    switch(selection){
    case "A":
    $("#wpcf7-f96-o1").show();
		// $(".option-field-a select").addClass('wpcf7-validates-as-required');
		// $(".option-field-a select").attr('aria-required', 'true');
   break;
	 case "B":
	 $("#wpcf7-f97-o2").show();
	//  $(".option-field-b select").addClass('wpcf7-validates-as-required');
	//  $(".option-field-b select").attr('aria-required', 'true');
		break;
		case "C":
		$("#wpcf7-f98-o3").show();
		// $(".option-field-c select").addClass('wpcf7-validates-as-required');
		// $(".option-field-c select").attr('aria-required', 'true');
	 break;
	 case "D":
	 $("#wpcf7-f99-o4").show();
	//  $(".option-field-d select").addClass('wpcf7-validates-as-required');
	//  $(".option-field-d select").attr('aria-required', 'true');
	break;
	case "E":
	$("#wpcf7-f261-o5").show();
	// $(".option-field-e select").addClass('wpcf7-validates-as-required');
	// $(".option-field-e select").attr('aria-required', 'true');
	break;
	case "F":
	$("#wpcf7-f262-o6").show();
	// $(".option-field-f select").addClass('wpcf7-validates-as-required');
	// $(".option-field-f select").attr('aria-required', 'true');
	break;
    default:
    hideForms();
		// $(".option-field select").removeClass('wpcf7-validates-as-required');
		// $(".option-field select").attr('aria-required', 'false');
    }
});


/*
 * Replace all SVG images with inline SVG
 */
jQuery('img.svg').each(function(){
    var $img = jQuery(this);
    var imgID = $img.attr('id');
    var imgClass = $img.attr('class');
    var imgURL = $img.attr('src');

    jQuery.get(imgURL, function(data) {
        // Get the SVG tag, ignore the rest
        var $svg = jQuery(data).find('svg');

        // Add replaced image's ID to the new SVG
        if(typeof imgID !== 'undefined') {
            $svg = $svg.attr('id', imgID);
        }
        // Add replaced image's classes to the new SVG
        if(typeof imgClass !== 'undefined') {
            $svg = $svg.attr('class', imgClass+' replaced-svg');
        }

        // Remove any invalid XML tags as per http://validator.w3.org
        $svg = $svg.removeAttr('xmlns:a');

        // Replace image with new SVG
        $img.replaceWith($svg);

    }, 'xml');

});



});
