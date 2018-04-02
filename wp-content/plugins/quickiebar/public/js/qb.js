function QuickieBar() {

	//local reference to wp jQuery
	var $ = jQuery;

	var self = this;

	self.previewingOnAdminPage = false;
	self.preventHidingWithCloseButton = false;//prevents hiding the bar using the close button

	/*Default Options*/
	self.options = {

		bar_uuid: 0,													//the bar's id

		bar_height: 'thin',								// regular || thin || tall || skinny
		new_tab: 'enabled',										// enabled || disabled
		placement: 'top',											// top || bottom
		devices: 'all',												// all || desktoponly || mobileonly
		attribution: 'hidden',								// visible || hidden
		alignment: 'leftright',								// centered || leftright
		sticky: 'enabled',										// enabled || disabled
		animation: 'slidein',									// slidein || none
		button_style: 'rounded',							// rounded || pill || square || outline
		close_button_visibility: 'onhover',		// onhover || always || hidden

		color_bar_background: '#56C4C3',
		color_bar_text: '#FFFFFF',
		color_button_background: '#062332',
		color_button_text: '#FFFFFF',

		bar_text: 'Get the most powerful conversion dropdown for Wordpress {{arrow-right}}',
		button_text: 'FREE DOWNLOAD {{download}}',

		destination: 'https://quickiebar.com',

		fixed_compatibility: 'off',
		bar_zindex: '100'
	};

	self.init = function(options){

		if(options){

			//for each option, update self.options
			$.each(options, function(index, option){

				//unescape strings that might contain special characters
				if(index == 'bar_text' ||
					index == 'button_text' ||
					index == 'subscribe_text' ||
					index == 'bar_html'
				){
					option = unescape(option);
				}
				self.options[index] = option;
			});

		}
	}

	self.initAndCreateBar = function(options){
		self.init(options);

		self.createBar();

		self.bindEventsToBar();
	}

	self.initAndShowBar = function(options, isAdminPage){

		//we have to set up the bar slightly differently for admin page preview
		if(isAdminPage){
			self.previewingOnAdminPage = true;
			self.preventHidingWithCloseButton = true;
		}

		self.init(options);

		self.createBar();

		self.bindEventsToBar();

		self.show();
	}

	self.prepareBarOrButtonText = function(text, removeRightArrows){
		if(!text || text == ''){
			return '';
		}

		//only remove right arrows when page width < 900px
		if(removeRightArrows && $('body').width() < 900){
			text = text.replace(/ {{(.*?)(-right)(.*?)}}/g, '');
		}

		//remove {{}} if text includes "-right" so as to not show right arrows on mobile devices

		//find each inserted icon and replace with <i> html
		return text.replace(/{{(.*?)}}/g, '&nbsp;<i class="fa fa-$1"></i>&nbsp;');
	}

	self.getQuickieBarTopLevelClasses = function(){
		var qbClasses = '';

		//add the associated classes based on bar options to quickiebar div
		qbClasses += 'qb-bar_height-' + self.options.bar_height + ' ';
		qbClasses += 'qb-sticky-' + (self.options.fixed_compatibility == 'on' ? 'enabled' : self.options.sticky) + ' ';//overwrite default sticky option if fixed_compatibility mode is on
		qbClasses += 'qb-placement-' + self.options.placement + ' ';
		qbClasses += 'qb-attribution-' + self.options.attribution + ' ';
		qbClasses += 'qb-alignment-' + self.options.alignment + ' ';
		qbClasses += 'qb-button_style-' + self.options.button_style + ' ';
		qbClasses += 'qb-close_button_visibility-' + self.options.close_button_visibility + ' ';

		if(self.previewingOnAdminPage){
			qbClasses += 'qb-admin-preview ';
		}

		return qbClasses;
	}

	//create html block to be placed before #page div that contains all the html for the quickiebar
	self.craftHtml = function(){

		var barText = self.options.bar_text ? self.prepareBarOrButtonText(self.options.bar_text, true) : self.prepareBarOrButtonText('Bar Text goes here', true);
		var buttonText = self.options.button_text ? self.prepareBarOrButtonText(self.options.button_text) : '';

		$qbHtml = '';

		$qbHtml = '<div id="quickiebar-show-button" class="show-button-sticky-' + (self.options.fixed_compatibility == 'on' ? 'enabled' : self.options.sticky) + ' show-button-placement-' + self.options.placement + '" style="color:' + self.options.color_bar_text + ';background:' + self.options.color_bar_background + ';z-index:' + self.options.bar_zindex + ';"><div class="show-button" style="color:' + self.options.color_bar_text + ';background:' + self.options.color_bar_background + ';"><i class="fa fa-chevron-down"></i><i class="fa fa-chevron-up"></i></div></div>';

		$qbHtml += '<div id="quickiebar" class="qb-' + self.options.bar_uuid + ' qb-' + self.options.id + ' qb ' + self.getQuickieBarTopLevelClasses() + '" style="background:' + self.options.color_bar_background + ';z-index:' + self.options.bar_zindex + ';">';

			$qbHtml += '<div class="hover-background-overlay"></div>';

			if(self.options.attribution == 'visible'){
				$qbHtml += '<a href="https://quickiebar.com/" target="_blank"><div class="qb-attribution ' + (GetLuminance(self.options.color_bar_background) > 200 ? 'qb-attribution-dark' : '') + '"></div></a>';
			}

			$qbHtml += '<div class="wrap">';
				$qbHtml += '<div class="qb-wrap">';
					$qbHtml += '<span class="bar-text" style="color:' + self.options.color_bar_text + ';">' + barText + '</span>';
					$qbHtml += '<span class="bar-button" style="' + (buttonText == '' ? 'display:none;' : '') + 'color:' + self.options.color_button_text + ';background:' + self.options.color_button_background + ';border-color:' + self.options.color_button_text + ';">' + buttonText + '</span>';
				$qbHtml += '</div>';//end .qb-wrap
			$qbHtml += '</div>';//end .wrap

			$qbHtml += '<div class="qb-close-button ' + (GetLuminance(self.options.color_bar_background) > 200 ? 'qb-close-button-dark' : '') + '"><i class="fa fa-times-circle-o"></i></div>';

			$qbHtml += '<a href="' + self.options.destination + '" ' + (self.options.new_tab == 'enabled' && (self.options.destination.indexOf('#') != 0) ? 'target="_blank"' : '') + ' class="link-overlay" style="' + (self.options.destination == '' ? 'display:none;' : '') + '"></a>'

			$qbHtml += '<div class="qb-close-bar"><i class="fa fa-chevron-up"></i></div>';

		$qbHtml += '</div>';//end #quickiebar

		return $qbHtml;
	}

	self.createBar = function(){

		$page = self.getPage();

		//self.getPage().before(self.craftCSS());
		$page.before(self.craftHtml());

		//TODO implement this / put this in function
		//$qbReviveHtml = '<div id="quickiebar-revive"><i class="fa fa-bolt"></i></div>';
	}

	self.hideAndDestroyBar = function(){

		self.hide(0, function(){
			$('#quickiebar.qb').remove();
		});

	}

	self.bindEventsToBar = function(){

		//Bind hover functions to show close button & attribution (if applicable)
		$('#quickiebar.qb').on('mouseover', function(){
			$(".qb-attribution").stop().addClass('visible');
			$(".qb-close-button").stop().addClass('visible');
		}).on('mouseout', function(){
			$(".qb-attribution").stop().removeClass('visible');
			$(".qb-close-button").stop().removeClass('visible');
		});

		//Bind close functions
		$('.qb-close-bar,.qb-close-button').click(function(){
			if(self.preventHidingWithCloseButton || self.previewingOnAdminPage){
				return;
			}

			self.hide();
		});

		//Bind conversion tracking on bar click
		$("#quickiebar.qb .link-overlay").click(function(){
			self.trackConversion();
			//console.log("TRACK CONVERSION");
		});

		//Bind show function on show-button
		$("#quickiebar-show-button").click(function(){
			qb.show();

			//if we're showing the bar again, we should clear the dismissal tracking
			qb.resetCurrentBarDismissalTracking();
		});
	}

	self.getPage = function(){
		if(self.previewingOnAdminPage){
			return $('html > body');
		}
		else{
			return $('html > body');
		}
	}

	self.getFixedHeader = function(){

		if($('.qb-fixed-header') && $('.qb-fixed-header').length > 0){
			return $('.qb-fixed-header');
		}

		if($('.qbp-fixed-header') && $('.qbp-fixed-header').length > 0){
			return $('.qbp-fixed-header');
		}

		if($('header') && $('header').length > 0){
			return $('header');
		}

		if($('#header') && $('#header').length > 0){
		  return $('#header');
		}

		if($('#masthead') && $('#masthead').length > 0){
			return $('#masthead');
		}

		if($('.site-header') && $('.site-header').length > 0){
			return $('.site-header').first();
		}

		//if we can't find it with selectors, the loop through every element in <body> - stopping once we find a position:fixed element and return that.
		//TODO
		return false;
	}

	//Just show the toggle for the bar, not the bar itself (show bar in "minimized" display)
	self.showBarToggle = function(){

		if(self.options.placement == 'top'){
			$("#quickiebar-show-button").css('margin-top', -24);
		}
		else if(self.options.placement == 'bottom'){
			$("#quickiebar-show-button").css('margin-bottom', -24);
		}

		$('#quickiebar-show-button').show();
		$("#quickiebar-show-button").stop().animate({'margin-top': 0,'margin-bottom': 0}, 250);
	}

	self.show = function(){

		$page = self.getPage();

		$qbHeight = $('#quickiebar').height();

		$slideIn = self.options.animation === 'slidein';

		//if we need to make page adjustments
		if((self.options.fixed_compatibility == 'on' || self.options.sticky == 'enabled' || self.options.placement == 'bottom' || self.previewingOnAdminPage) && $('body').width() > 900){

			if(self.options.placement == 'top'){

				if($slideIn){
					$page.addClass('qb-disable-animation').animate({'padding-top': $qbHeight}, 300, 'swing', function(){
						$page.removeClass('qb-disable-animation');
					});
				}
				else{
					$page.addClass('qb-disable-animation');
					$page.css('padding-top', $qbHeight);

					//not sure why...but I have to do this after a slight delay
					setTimeout(function(){
						$page.removeClass('qb-disable-animation');
					}, 20);
				}

				//adjust fixed header if required
				if(self.options.fixed_compatibility == 'on'){
					$fixedHeader = self.getFixedHeader();

					if($fixedHeader){

						if($slideIn){
							$fixedHeader.animate({'margin-top': $qbHeight}, 300, 'swing');
						}
						else{
							$fixedHeader.css('margin-top', $qbHeight);
						}

					}
				}

				//adjust for WordPress admin top bar if required AND not previewing on Bars page (since we remove the admin bar in this situation)
				if($('#wpadminbar') && !self.previewingOnAdminPage){
					$page.css('padding-top', $qbHeight + $('#wpadminbar').height());

					$('#quickiebar').css('marginTop', $('#wpadminbar').height());
				}

			}
			else if(self.options.placement === 'bottom'){
				$page.css('margin-bottom', $qbHeight);
			}
		}

		//Animate the bar itself
		if($slideIn){

			$('#quickiebar').stop().slideDown(300);
		}
		else{
			$('#quickiebar').stop().show();
		}

		//Hide the show button if visible
		$('#quickiebar-show-button').hide();

	}

	self.hide = function(animationDuration, callback){

		//track the dismissal first before any other code, just in case errors are thrown
		//we want to make sure to not show this bar to the user again
		self.trackDismissal();

		if(typeof animationDuration == 'undefined'){
			animationDuration = 200;
		}

		$page = self.getPage();

		$page.addClass('qb-disable-animation').animate({'padding-top': 0, 'margin-bottom': 0}, animationDuration, 'swing', function(){
			$page.removeClass('qb-disable-animation');
		});

		$('#quickiebar').stop().slideUp(animationDuration, function(){
			//execute callback when bar hidden from page
			if(typeof callback == 'function'){
				callback();
			}
		});

		//adjust fixed header if required
		if(self.options.fixed_compatibility == 'on' && self.options.placement == 'top'){
			$fixedHeader = self.getFixedHeader();

			if($fixedHeader){

				if($slideIn){
					$fixedHeader.animate({'margin-top': 0}, 300, 'swing');
				}
				else{
					$fixedHeader.css('margin-top', 0);
				}

			}
		}

		//Show the show button
		self.showBarToggle();

		if(typeof callback === 'function'){
			callback();
		}

	}

	self.fetchBar = function(callback){
		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: {
				action: 'qb_public_ajax',
				endpoint: 'get_bar',
				qb_public_nonce: QB_PUBLIC_GLOBALS.QB_PUBLIC_NONCE
			},
			success: function(bar){
				callback(bar);
			},
			dataType: 'json'
		});
	}

	self.getUserUuid = function(){
		var user_uuid = QBGetCookie('qb_user_uuid');

		if(!user_uuid){
			//if no user_uuid created, create it now
			user_uuid = QBGenerateUuid();

			//persist uuid to local storage
			QBSetCookie('qb_user_uuid', user_uuid, 7);//TODO allow user to set the expiration days
		}

		return user_uuid;
	}

	self.getBarViews = function(){

		var bar_views_cookie = QBGetCookie('qb_bar_views');
		var bar_views;

		if(!bar_views_cookie){

			//initialize empty array as bar_views property
			bar_views = [];

			QBSetCookie('qb_bar_views', JSON.stringify(bar_views), 7);
		}
		else{

			//load bars_viewed from cookie
			bar_views = JSON.parse(QBGetCookie('qb_bar_views'));
		}

		return bar_views;

	}

	self.getBarConversions = function(){

		var bar_conversions_cookie = QBGetCookie('qb_bar_conversions');
		var bar_conversions;

		if(!bar_conversions_cookie){

			//initialize empty array as bar_conversions property
			bar_conversions = [];

			QBSetCookie('qb_bar_conversions', JSON.stringify(bar_conversions), 7);
		}
		else{

			//load bars_viewed from cookie
			bar_conversions = JSON.parse(QBGetCookie('qb_bar_conversions'));
		}

		return bar_conversions;

	}

	self.getBarDismissals = function(){

		var bar_dismissals_cookie = QBGetCookie('qb_bar_dismissals');
		var bar_dismissals;

		if(!bar_dismissals_cookie){

			//initialize empty array as bar_conversions property
			bar_dismissals = [];

			QBSetCookie('qb_bar_dismissals', JSON.stringify(bar_dismissals), 7);
		}
		else{

			//load bars_viewed from cookie
			bar_dismissals = JSON.parse(QBGetCookie('qb_bar_dismissals'));
		}

		return bar_dismissals;

	}

	self.resetAllTracking = function(){
		QBDeleteCookie('qb_user_uuid');
		QBDeleteCookie('qb_bar_views');
		QBDeleteCookie('qb_bar_conversions');
		QBDeleteCookie('qb_bar_dismissals');
	}

	self.resetCurrentBarDismissalTracking = function(){
		var bar_dismissals = self.getBarDismissals();

		for(var i = bar_dismissals.length; i >= 0; i--){
			if(bar_dismissals[i] == qb.options.bar_uuid){
				bar_dismissals.splice(i, 1);
			}
		}

		QBSetCookie('qb_bar_dismissals', JSON.stringify(bar_dismissals), 7);
	}

	self.trackView = function(){

		var bar_uuid = self.options.bar_uuid;
		var bar_views = self.getBarViews();

		//if bar_uuid == 0, don't track the view
		if(bar_uuid == 0){
			return;
		}


		if(bar_views.indexOf(bar_uuid) < 0){

			//if user hasn't viewed bar yet, track the view to the db then update locally
			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: {
					action: 'qb_public_ajax',
					endpoint: 'save_view',
					user_uuid: self.getUserUuid(),
					bar_uuid: bar_uuid,
					qb_public_nonce: QB_PUBLIC_GLOBALS.QB_PUBLIC_NONCE
				},
				success: function(){

					//push the current bar onto the list of viewed bars
					bar_views.push(bar_uuid);

					//persist viewed bars as cookie
					QBSetCookie('qb_bar_views', JSON.stringify(bar_views), 7);

				},
				dataType: 'json'
			});
		}

	}

	self.trackConversion = function(){

		var bar_uuid = self.options.bar_uuid;
		var bar_conversions = self.getBarConversions();

		//if bar_uuid == 0, don't track the view
		if(bar_uuid == 0){
			return;
		}


		if(bar_conversions.indexOf(bar_uuid) < 0){

			//if user hasn't converted on the bar yet, track the conversion to the db then update locally
			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: {
					action: 'qb_public_ajax',
					endpoint: 'save_conversion',
					user_uuid: self.getUserUuid(),
					bar_uuid: bar_uuid,
					qb_public_nonce: QB_PUBLIC_GLOBALS.QB_PUBLIC_NONCE
				},
				success: function(){

					//push the current bar onto the list of viewed bars
					bar_conversions.push(bar_uuid);

					//persist viewed bars as cookie
					QBSetCookie('qb_bar_conversions', JSON.stringify(bar_conversions), 7);

				},
				dataType: 'json'
			});
		}

	}

	self.trackDismissal = function(){
		var bar_uuid = self.options.bar_uuid;
		var bar_dismissals = self.getBarDismissals();

		//if bar_uuid == 0, don't track the view
		if(bar_uuid == 0){
			return;
		}

		//we don't persist this information to the server...
		//instead, just updated user's cookie so they are not shown the bar again

		//push the current bar onto the list of viewed bars
		bar_dismissals.push(bar_uuid);

		//persist viewed bars as cookie
		QBSetCookie('qb_bar_dismissals', JSON.stringify(bar_dismissals), 7);
	}

}

jQuery(document).ready(function($){

	//if qbhide is toggled in URL, don't create & show the quickiebar
	//this is used on quickiebar.com for previewing the bar on third-party sites
	if(location.hash.indexOf('qbhide') > -1 || location.href.indexOf('wp-admin/admin.php') > -1 || location.href.indexOf('wp-login.php') > -1){
		return;
	}

	//globally instantiate a new QuickieBar object
	qb = new QuickieBar();

	qb.fetchBar(function(bar){

		//if on admin page


		if(!bar || !bar.bar_uuid){//need to check bar_uuid also just in case default qb options come back (depending on php version & debuggin settings, this might happen)
			//if no bar is live, nothing more to do
			return;
		}
		//immedialy show bar if debug_mode and hash
		else if(bar.debug_mode == 'on' && location.hash.toLowerCase().indexOf('qbshow') > -1){
			qb.initAndShowBar(bar);
			qb.trackView();
		}
		//if bar that is returned has already been dismissed, show toggle instead of bar
		else if(qb.getBarDismissals().indexOf(bar.bar_uuid) > -1 && (QB_PUBLIC_GLOBALS.USER_TYPE != 'admin')){
			qb.initAndCreateBar(bar);//create the bar but don't show it
			qb.showBarToggle();
		}
		//more logic to determine whether or not to show bar
		else{
			//if fixed header compatibility mode is on, don't show if on mobile devices (< 900 px)
			if(bar.fixed_compatibility == 'on' && $('body').width() < 900){
				return;
			}

			if(bar.device_visibility != 'all'){
				if(bar.device_visibility == 'desktoponly' && isMobileDevice()){
					return;
				}
				else if(bar.device_visibility == 'mobileonly' && !isMobileDevice()){
					return;
				}
			}

			qb.initAndShowBar(bar);
			qb.trackView();
		}

	});

});


/*
Sample Init Code:

qb.init({
	bar_height: 'thin',								// regular || thin || tall || skinny
	new_tab: 'enabled',										// true || false
	placement: 'top',											// top || bottom
	devices: 'all',												// all || desktoponly || mobileonly
	attribution: 'visible',								// visible || hidden
	alignment: 'leftright',								// centered || leftright
	sticky: 'enabled',										// true || false
	animation: 'slidein',									// slidein || none
	button_style: 'rounded',							// rounded || pill || square || outline
	close_button_visibility: 'onhover',		// onhover || alwaysshow || alwayshide

	color_bar_background: '#56C4C3',
	color_bar_text: '#FFFFFF',
	color_button_background: '#062332',
	color_button_text: '#FFFFFF',

	bar_text: 'Get the most powerful conversion dropdown for Wordpress {{arrow-right}}',
	button_text: 'FREE DOWNLOAD {{download}}'
});*/

function isMobileDevice(){
	var check = false;
	  (function(a,b){if(/(android|bb\d+|meego).+mobile|android|ipad|playbook|silk|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))check = true})(navigator.userAgent||navigator.vendor||window.opera);
	return check;
}

function QBSetCookie(name, value, days) {
	var d = new Date();
  d.setTime(d.getTime() + (days*24*60*60*1000));
  var expires = "expires="+d.toUTCString();
  document.cookie = name + "=" + value + "; " + expires + '; path=/';
}

function QBGetCookie(name) {
    name += "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
    }
    return false;
}

function QBDeleteCookie(name) {
	if(QBGetCookie(name)){
		QBSetCookie(name, '', -1);
	}
}

//creates 13 hexidecimal random number as hash / practical "uuid"
function QBGenerateUuid(){
	//return Math.random().toString(36).slice(2);

	var id = [];
	var hexDigits = "0123456789abcdef";
	for(var i = 0;i<13;i++){
		id[i] = hexDigits.substr(Math.floor(Math.random() * 0x10), 1);
	}

	return id.join("");
}


function ColorLuminance(hex, lum) {

	// validate hex string
	hex = String(hex).replace(/[^0-9a-f]/gi, '');
	if (hex.length < 6) {
		hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
	}
	lum = lum || 0;

	// convert to decimal and change luminosity
	var rgb = "#", c, i;
	for (i = 0; i < 3; i++) {
		c = parseInt(hex.substr(i*2,2), 16);
		c = Math.round(Math.min(Math.max(0, c + (c * lum)), 255)).toString(16);
		rgb += ("00"+c).substr(c.length);
	}

	return rgb;
}

/*http://stackoverflow.com/questions/12043187/how-to-check-if-hex-color-is-too-black*/
function GetLuminance(hex){
	var c = hex.substring(1);      // strip #
	var rgb = parseInt(c, 16);   // convert rrggbb to decimal
	var r = (rgb >> 16) & 0xff;  // extract red
	var g = (rgb >>  8) & 0xff;  // extract green
	var b = (rgb >>  0) & 0xff;  // extract blue

	var luma = 0.2126 * r + 0.7152 * g + 0.0722 * b; // per ITU-R BT.709

	return luma
}
