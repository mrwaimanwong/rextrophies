jQuery(document).ready(function($){
	
	$('.qb-tooltip').tooltipster({
		position: 'right',//top-right or top-left
		theme: 'tooltipster-qb',
		delay: 0,
		speed: 300
	});
		
	function ViewModel() {
		var self = this;
		
		self.syncingDataWithServer = ko.observable(true);
		
		self.bars = ko.observableArray();
		self.bars.ordered = ko.computed(function(){
			return _.sortBy(self.bars(), function(bar){
				return -1 * bar.created_date;
			});
		});
		
		self.upgradeModalIsVisible = ko.observable(false);
		self.replacedBarText = ko.observable('');
		self.currentlyRunningBar = ko.computed(function(){
			var currentlyRunningBar = _.find(self.bars(), function(bar){
				return bar.status == 'live';
			});
			
			return currentlyRunningBar ? currentlyRunningBar : false;
		});
		
		self.editingBar = ko.observable(false);
		self.editingBarUuid = ko.observable(0);
		self.currentlyEditingBarIsLive = function(){
			return self.getBarByUuid(self.editingBarUuid()).status == 'live';
		}
		
		self.deletingBar = ko.observable(false);
		
		self.publishingBar = ko.observable(false);
		
		//whenever we stop editing a bar, clear out the all of the current bar options and reset to default and reset the editingBarUuid value
		self.editingBar.subscribe(function(newValue){
			if(newValue){
				
				//set up QB preview
				self.setupPreviewBar();
				
				//remove padding-top from html for wpadminbar and hide bar
				$('html').css('padding-top', '0px');
				$("#wpadminbar").hide();
				
			}
			else{
				
				self.editingBarUuid(0);
				self.clearEditingBarInformation();
				
				//tear down QB preview
				self.closePreviewBar();
				
				//add padding-top back to html for wpadminbar and show bar
				$('html').css('padding-top', '32px');
				$("#wpadminbar").show();
				
			}
		});
		
		self.setupPreviewBar = function(){
			if(typeof qb == 'undefined'){
				qb = new QuickieBar();
			}
			
			//hide in case currently visible
			qb.hide(0);
			
			qb.initAndShowBar(ko.toJS(self.barOptions), true);
			
			//this helps animation not be buggy..but doesn't do the complete trick
			/*setTimeout(function(){
				qb.initAndShowBar(ko.toJS(self.barOptions), true);
			}, 300);*/
			
		}
		self.updatePreviewBarWithBarOptions = function(){
			
		}
		self.closePreviewBar = function(){
			if(typeof qb !== 'undefined'){
				qb.hideAndDestroyBar();
			}
		}
		
		self.updatePreviewOption = function(optionName, optionValue){
			if(typeof qb == 'undefined' || typeof qb.options == 'undefined' || typeof qb.options[optionName] == 'undefined'){
				return;
			}
			
			//update qb option - this won't change any of the view..but is important so methods like show() work as intented
			qb.options[optionName] = optionValue;
			
			//if we're not editing a bar, don't update styles of DOM elements
			if(!self.editingBar()){
				return;
			}

			//if option is a color
			if(optionName.indexOf('color') > -1){
				if(optionName == 'color_bar_background'){
					$('#quickiebar').css('background', optionValue);
				}
				else if(optionName == 'color_bar_text'){
					$('#quickiebar .bar-text').css('color', optionValue);
				}
				else if(optionName == 'color_button_text'){
					$('#quickiebar .bar-button').css('color', optionValue);
					$('#quickiebar .bar-button').css('border-color', optionValue);
				}
				else if(optionName == 'color_button_background'){
					$('#quickiebar .bar-button').css('background', optionValue);
				}

				//all done here
				return;
			}
			
			//if option is a text value
			if(optionName == 'bar_text'){
				$('#quickiebar .bar-text').html(self.getBarOrButtonTextAsHtml(optionValue));
				return;
			}
			else if(optionName == 'button_text'){
				
				if(optionValue && optionValue !== ''){
					$('#quickiebar .bar-button').css('display', 'inline-block');
					$('#quickiebar .bar-button').html(self.getBarOrButtonTextAsHtml(optionValue));
				}
				else{
					//hide if no text
					$('#quickiebar .bar-button').css('display', 'none');
				}
				
				return;
			}
			
			//if option is destination...don't do anything
			if(optionName == 'destination'){
				if(optionValue && optionValue !== ''){
					$("#quickiebar .link-overlay").css('display', 'block');
				}
				else{
					$("#quickiebar .link-overlay").css('display', 'none');
				}
				return;
			}

			//...if option is not a color...manipulate bar using classes

			//get array of current classes to try to find matching class
			var qbClassesArray = $('#quickiebar').attr("class").split(' ');
			var matchingClass = false;

			$.each(qbClassesArray, function(index, qbClass){
				if(qbClass.indexOf(optionName) > -1){
					//this is the class specifying how to display the given optionName
					matchingClass = qbClass;
				}
			});

			//remove matching class for the optionName & add the new one w/ the updated paramter
			if(matchingClass){
				$('#quickiebar').removeClass(matchingClass);
			}
			$('#quickiebar').addClass('qb-' + optionName + '-' + optionValue);

			//make any other adjustments as needed
			if(optionName == 'placement' || optionName == 'bar_height'){
				//reshow to recalculate the bar height offset
				if(optionName == 'placement'){
					qb.hide(0);
				}
				qb.show();
			}
		}
		
		self.alwaysShowAdditionalOptions = ko.observable(false);
		self.showingAdditionalOptions = ko.observable(true);
		
		self.DEFAULT_BAR_OPTIONS = {
			
			bar_height: 'thin',
			
			bar_text: '',
			button_text: '',
			destination: '',
			
			color_bar_background: '#368FCF',
			color_button_background: '#28557A',
			color_bar_text: '#FFFFFF',
			color_button_text: '#FFFFFF',
			
			new_tab: 'enabled',
			placement: 'top',
			devices: 'all',
			alignment: 'centered',
			sticky: 'enabled',
			animation: 'slidein',
			button_style: 'rounded',
			close_button_visibility: 'onhover'
		}
		
		self.barOptions = {
			bar_height: ko.observable(self.DEFAULT_BAR_OPTIONS.bar_height),
			
			bar_text: ko.observable(self.DEFAULT_BAR_OPTIONS.bar_text),
			button_text: ko.observable(self.DEFAULT_BAR_OPTIONS.button_text),
			destination: ko.observable(self.DEFAULT_BAR_OPTIONS.destination),
			
			color_bar_background: ko.observable(self.DEFAULT_BAR_OPTIONS.color_bar_background),
			color_button_background: ko.observable(self.DEFAULT_BAR_OPTIONS.color_button_background),
			color_bar_text: ko.observable(self.DEFAULT_BAR_OPTIONS.color_bar_text),
			color_button_text: ko.observable(self.DEFAULT_BAR_OPTIONS.color_button_text),
			
			new_tab: ko.observable(self.DEFAULT_BAR_OPTIONS.new_tab),
			placement: ko.observable(self.DEFAULT_BAR_OPTIONS.placement),
			devices: ko.observable(self.DEFAULT_BAR_OPTIONS.devices),
			alignment: ko.observable(self.DEFAULT_BAR_OPTIONS.alignment),
			sticky: ko.observable(self.DEFAULT_BAR_OPTIONS.sticky),
			animation: ko.observable(self.DEFAULT_BAR_OPTIONS.animation),
			button_style: ko.observable(self.DEFAULT_BAR_OPTIONS.button_style),
			close_button_visibility: ko.observable(self.DEFAULT_BAR_OPTIONS.close_button_visibility)
		}
		
		//add subscribe to each option that will update the preview quickiebar whenever updated
		_.each(self.barOptions, function(optionValue, optionName){
			optionValue.subscribe(function(newValue){
				self.updatePreviewOption(optionName, newValue);
			});
		});
		
		self.getBarOrButtonTextAsHtml = function(text){
			if(!text || text == ''){
				return '';
			}
			
			//find each inserted icon and replace with <i> html
			return text.replace(/{{(.*?)}}/g, '&nbsp;<i class="fa fa-$1"></i>&nbsp;');
		}
		
		self.destinationAsLink = ko.computed(function(){
			var textToReplace = self.barOptions.destination();
			
			//if no link specified, don't try to add http:// at beginning
			if(!textToReplace || textToReplace == ''){
				return '';
			}
			
			if(	textToReplace.indexOf('mailto:') == 0 ||
					textToReplace.indexOf('tel:')  == 0 ||
					textToReplace.indexOf('call:') == 0 ||
					textToReplace.indexOf('skype:') == 0 ||
					textToReplace.indexOf('sms:') == 0 ||
					textToReplace.indexOf('#') == 0 ||
					textToReplace.indexOf('//') == 0 ||
					textToReplace.indexOf('/') == 0
			){
				//do nothing - leave the link the way the user created it
				return textToReplace;
			}
			else{
				//make sure the link is an absolute link...
				//if the link is an absolute link, check for http:// or https:// - if we don't have either...add http:// at beginning of link
				if((textToReplace.indexOf('http://') > -1 || textToReplace.indexOf('https://') > -1 )){
					return textToReplace;
				}
				else{
					return 'http://' + textToReplace;
				}
			}
		});
		
		self.previewColor = ko.observable(false);
		
		self.barColors = ko.computed(function(){
			if(!self.previewColor()){
				return {
					barBackground: self.barOptions.color_bar_background(),
					barText: self.barOptions.color_bar_text(),
					buttonBackground: self.barOptions.color_button_background(),
					buttonText: self.barOptions.color_button_text()
				}
			}
			else{
				return {
					barBackground: self.previewColor().bar_background,
					barText: self.previewColor().bar_text,
					buttonBackground: self.previewColor().button_background,
					buttonText: self.previewColor().button_text,
				}
			}
		});
		
		self.barColorPresets = [
		{
			bar_background: '#062332',
			bar_text: '#FFFFFF',
			button_background: '#56C4C3',
			button_text: '#FFFFFF',
		},
		{
			bar_background: '#F15A29',
			bar_text: '#FFFFFF',
			button_background: '#062332',
			button_text: '#FFFFFF',
		},
		{
			bar_background: '#3C8DC7',
			bar_text: '#FFFFFF',
			button_background:'#FFFFFF',
			button_text: '#062332'
		},
		{
			bar_background: '#303030',
			bar_text: '#FFFFFF',
			button_background: '#84C334',
			button_text: '#FFFFFF',
		},
		{
			bar_background: '#368FCF',
			bar_text: '#FFFFFF',
			button_background: '#28557A',
			button_text: '#FFFFFF',
		},
		/*{
			bar_background: '#56C4C3',
			bar_text: '#FFFFFF',
			button_background: '#062332',
			button_text: '#FFFFFF',
		},*/
		/*{
			bar_background: '#9B7AB7',
			bar_text: '#FFFFFF',
			button_background: '#EF4651',
			button_text: '#FFFFFF',
		},*/
		/*{
			bar_background: '#CA4119',
			bar_text: '#FFFFFF',
			button_background: '#56C4C3',
			button_text: '#FFFFFF',
		},*/
		{
			bar_background: '#EDEDED',
			bar_text: '#CC10AA',
			button_background: '#CC10AA',
			button_text: '#FFFFFF',
		},
		{
			bar_background: '#DB4455',
			bar_text: '#FFFFFF',
			button_background: '#BF3B44',
			button_text: '#FFFFFF',
		},
		{
			bar_background: '#9B7AB7',
			bar_text: '#FFFFFF',
			button_background: '#FFFFFF',
			button_text: '#9B7AB7',
		},
		{
			bar_background: '#416782',
			bar_text: '#FFFFFF',
			button_background: '#8EC732',
			button_text: '#FFFFFF',
		},
		{
			bar_background: '#364047',
			bar_text: '#FFFFFF',
			button_background: '#CA4119',
			button_text: '#FFFFFF',
		}
		];
		
		self.selectColorPreset = function(preset){
			self.barOptions.color_bar_background(preset.bar_background);
			self.barOptions.color_bar_text(preset.bar_text);
			self.barOptions.color_button_background(preset.button_background);
			self.barOptions.color_button_text(preset.button_text);
		}
		
		self.barOptionsAreValid = ko.computed(function(){
			var options = self.barOptions;
			
			if(!options.bar_text() || options.bar_text() == ''){
				return false;
			}
			/*if(!options.button_text() || options.button_text() == ''){
				return false;
			}*/
			/*if(!options.destination() || options.destination() == '' || options.destination().length < 4){
				return false;
			}*/
			
			//all validation passed
			return true;
		});
		
		self.pickingIconForField = ko.observable(false);
		self.pickingIconForField.subscribe(function(newValue){
			if(newValue){
				setTimeout(function(){
					$('#qb-icon-picker input[type="text"]').focus();
				}, 50);
			}
		})
		self.faSearchText = ko.observable("");
		
		self.insertIcon = function(iconName){
			
			//insert icon into text input for either bar text or button text
			if(self.pickingIconForField() == 'bar'){
				
				//insert space before icon if didn't exist prior
				if(self.barOptions.bar_text().replace(/\s*$/,'').length == self.barOptions.bar_text().length){
					self.barOptions.bar_text(self.barOptions.bar_text() + ' ');
				}
				
				//insert icon by appending to end of barOptions.bar_text string
				self.barOptions.bar_text(self.barOptions.bar_text() + '{{' + iconName + '}}');
			}
			else if(self.pickingIconForField() == 'button'){
				
				//insert space before icon if didn't exist prior
				if(self.barOptions.button_text().replace(/\s*$/,'').length == self.barOptions.button_text().length){
					self.barOptions.button_text(self.barOptions.button_text() + ' ');
				}
				
				//insert icon by appending to end of barOptions.bar_text string
				self.barOptions.button_text(self.barOptions.button_text() + '{{' + iconName + '}}');
			}
			
			//close icon-picker
			self.pickingIconForField(false);
		}
		
		self.faIcons = [
		
		/*MOST POPULAR*/
		'envelope', 'envelope-o', 'cloud-download', 'download', 'book', 'bookmark', 'bookmark-o', 'mobile',
		'shopping-cart', 'credit-card', 'chevron-right', 'arrow-right', 'long-arrow-right',
		'file-text', 'file', 'file-o', 'file-text-o', 'file-archive-o', 'youtube-play', 'play',
		'gift', 'facebook', 'twitter', 'instagram', 'pinterest', 'paper-plane', 'paper-plane-o',
		'heart', 'bullhorn', 'check', 'check-square', 'check-circle-o', 'check-circle',
		
		'angle-double-down', 'angle-double-left', 'angle-double-right', 'angle-double-up', 
		'angle-down', 'angle-left', 'angle-right', 'angle-up', 'arrow-circle-down', 
		'arrow-circle-left', 'arrow-circle-o-down', 'arrow-circle-o-left', 'arrow-circle-o-right', 'arrow-circle-o-up', 
		'arrow-circle-right', 'arrow-circle-up', 'arrow-down', 'arrow-left', 'arrow-right', 
		'arrow-up', 'arrows-alt', 'caret-down', 'caret-left', 'caret-right', 
		'caret-up', 'chevron-circle-down', 'chevron-circle-left', 'chevron-circle-right', 'chevron-circle-up', 
		'chevron-down', 'chevron-left', 'chevron-right', 'chevron-up', 'hand-o-down', 
		'hand-o-left', 'hand-o-right', 'hand-o-up', 'long-arrow-down', 'long-arrow-left', 
		'long-arrow-right', 'long-arrow-up', 'backward', 'compress', 'eject', 
		'paper-plane', 'paper-plane-o', 'paw', 'pencil', 'pencil-square', 
		'pencil-square-o', 'phone', 'phone-square', 'photo', 'picture-o', 
		'plane', 'plus', 'plus-circle', 'plus-square', 'plus-square-o', 
		'power-off', 'print', 'puzzle-piece', 'qrcode', 'question', 
		'question-circle', 'quote-left', 'quote-right', 'random', 'recycle', 
		'refresh', 'remove', 'reorder', 'reply', 'reply-all',
		'expand', 'fast-backward', 'fast-forward', 'forward', 'pause', 
		'play', 'play-circle', 'play-circle-o', 'step-backward', 'step-forward',
		'angellist', 'area-chart', 'at', 'bell-slash', 'bell-slash-o', 'bicycle', 
		'binoculars', 'birthday-cake', 'bus', 'calculator', 'cc', 
		'cc-amex', 'cc-discover', 'cc-mastercard', 'cc-paypal', 'cc-stripe', 
		'cc-visa', 'copyright', 'eyedropper', 'futbol-o', 'google-wallet', 
		'ils', 'ioxhost', 'lastfm', 'lastfm-square', 'line-chart', 
		'meanpath', 'newspaper-o', 'paint-brush', 'paypal', 'pie-chart', 
		'plug', 'shekel', 'sheqel', 'slideshare', 'soccer-ball-o', 
		'toggle-off', 'toggle-on', 'trash', 'tty', 'twitch', 
		'wifi', 'yelp', 'adjust', 'anchor', 'archive', 'unlink', 
		'arrows', 'arrows-h', 'arrows-v', 'asterisk', 'automobile', 
		'ban', 'bank', 'bar-chart', 'bar-chart-o', 'barcode', 
		'bars', 'beer', 'bell', 'bell-o', 'bolt', 
		'bomb', 'briefcase', 
		'bug', 'building', 'building-o', 'bullhorn', 'bullseye', 
		'cab', 'calendar', 'calendar-o', 'camera', 'camera-retro', 
		'car', 'caret-square-o-down', 'caret-square-o-left', 'caret-square-o-right', 'caret-square-o-up', 
		'certificate', 'check-circle', 'check-circle-o', 'check-square', 
		'check-square-o', 'child', 'circle', 'circle-o', 'circle-o-notch', 
		'circle-thin', 'clock-o', 'close', 'cloud', 
		'cloud-upload', 'code', 'code-fork', 'coffee', 'cog', 
		'cogs', 'comment', 'comment-o', 'comments', 'comments-o', 
		'compass', 'credit-card', 'crop', 'crosshairs', 'cube', 
		'cubes', 'cutlery', 'dashboard', 'database', 'desktop', 
		'dot-circle-o', 'edit', 'ellipsis-h', 'ellipsis-v', 
		'envelope', 'envelope-o', 'envelope-square', 'eraser', 'exchange', 
		'exclamation', 'exclamation-circle', 'exclamation-triangle', 'external-link', 'external-link-square', 
		'eye', 'eye-slash', 'fax', 'female', 'fighter-jet', 
		'file-archive-o', 'file-audio-o', 'file-code-o', 'file-excel-o', 'file-image-o', 
		'file-movie-o', 'file-pdf-o', 'file-photo-o', 'file-picture-o', 'file-powerpoint-o', 
		'file-sound-o', 'file-video-o', 'file-word-o', 'file-zip-o', 'film', 
		'filter', 'fire', 'fire-extinguisher', 'flag', 'flag-checkered', 
		'flag-o', 'flash', 'flask', 'folder', 'folder-o', 
		'folder-open', 'folder-open-o', 'frown-o', 'gamepad', 'gavel', 
		'gear', 'gears', 'gift', 'glass', 'globe', 
		'graduation-cap', 'group', 'hdd-o', 'headphones', 'heart', 
		'heart-o', 'history', 'home', 'image', 'inbox', 
		'info', 'info-circle', 'institution', 'key', 'keyboard-o', 
		'language', 'laptop', 'leaf', 'legal', 'lemon-o', 
		'level-down', 'level-up', 'life-bouy', 'life-buoy', 'life-ring', 
		'life-saver', 'lightbulb-o', 'location-arrow', 'lock', 'magic', 
		'magnet', 'mail-forward', 'mail-reply', 'mail-reply-all', 'male', 
		'map-marker', 'meh-o', 'microphone', 'microphone-slash', 'minus', 
		'minus-circle', 'minus-square', 'minus-square-o', 'mobile', 'mobile-phone', 
		'money', 'moon-o', 'mortar-board', 'music', 'navicon', 
		'retweet', 'road', 'rocket', 'rss', 'rss-square', 
		'search', 'search-minus', 'search-plus', 'send', 'send-o', 
		'share', 'share-alt', 'share-alt-square', 'share-square', 'share-square-o', 
		'shield', 'sign-in', 'sign-out', 'signal', 
		'sitemap', 'sliders', 'smile-o', 'sort', 'sort-alpha-asc', 
		'sort-alpha-desc', 'sort-amount-asc', 'sort-amount-desc', 'sort-asc', 'sort-desc', 
		'sort-down', 'sort-numeric-asc', 'sort-numeric-desc', 'sort-up', 'space-shuttle', 
		'spinner', 'spoon', 'square', 'square-o', 'star', 
		'star-half', 'star-half-empty', 'star-half-full', 'star-half-o', 'star-o', 
		'suitcase', 'sun-o', 'support', 'tablet', 'tachometer', 
		'tag', 'tags', 'tasks', 'taxi', 'terminal', 
		'thumb-tack', 'thumbs-down', 'thumbs-o-down', 'thumbs-o-up', 'thumbs-up', 
		'ticket', 'times', 'times-circle', 'times-circle-o', 'tint', 
		'toggle-down', 'toggle-left', 'toggle-right', 'toggle-up', 'trash-o', 
		'tree', 'trophy', 'truck', 'umbrella', 'university', 
		'unlock', 'unlock-alt', 'unsorted', 'upload', 'user', 
		'users', 'video-camera', 'volume-down', 'volume-off', 'volume-up', 
		'warning', 'wheelchair', 'wrench', 'file', 'file-o', 
		'file-text', 'file-text-o', 'bitcoin', 'btc', 'cny', 
		'dollar', 'eur', 'euro', 'gbp', 'inr', 
		'jpy', 'krw', 'rmb', 'rouble', 'rub', 
		'ruble', 'rupee', 'try', 'turkish-lira', 'usd', 
		'won', 'yen', 'align-center', 'align-justify', 'align-left', 
		'align-right', 'bold', 'chain', 'chain-broken', 'clipboard', 
		'columns', 'copy', 'cut', 'dedent', 'files-o', 
		'floppy-o', 'font', 'header', 'indent', 'italic', 
		'link', 'list', 'list-alt', 'list-ol', 'list-ul', 
		'outdent', 'paperclip', 'paragraph', 'paste', 'repeat', 
		'rotate-left', 'rotate-right', 'save', 'scissors', 'strikethrough', 
		'subscript', 'superscript', 'table', 'text-height', 'text-width', 
		'th', 'th-large', 'th-list', 'underline', 'undo', 
		'stop', 'youtube-play', 'adn', 'android', 'apple', 
		'behance', 'behance-square', 'bitbucket', 'bitbucket-square', 'codepen', 
		'css3', 'delicious', 'deviantart', 'digg', 'dribbble', 
		'dropbox', 'drupal', 'empire', 'facebook', 'facebook-square', 
		'flickr', 'foursquare', 'ge', 'git', 'git-square', 
		'github', 'github-alt', 'github-square', 'gittip', 'google', 
		'google-plus', 'google-plus-square', 'hacker-news', 'html5', 'instagram', 
		'joomla', 'jsfiddle', 'linkedin', 'linkedin-square', 'linux', 
		'maxcdn', 'openid', 'pagelines', 'pied-piper', 'pied-piper-alt', 
		'pinterest', 'pinterest-square', 'qq', 'ra', 'rebel', 
		'reddit', 'reddit-square', 'renren', 'skype', 'slack', 
		'soundcloud', 'spotify', 'stack-exchange', 'stack-overflow', 'steam', 
		'steam-square', 'stumbleupon', 'stumbleupon-circle', 'tencent-weibo', 'trello', 
		'tumblr', 'tumblr-square', 'twitter', 'twitter-square', 'vimeo-square', 
		'vine', 'vk', 'wechat', 'weibo', 'weixin', 
		'windows', 'wordpress', 'xing', 'xing-square', 'yahoo', 
		'youtube', 'youtube-square', 'ambulance', 'h-square', 'hospital-o', 
		'medkit', 'stethoscope', 'user-md'
		];
		
		self.filteredIcons = ko.computed(function(){
			return _.filter(self.faIcons, function(icon){
				return icon.indexOf(self.faSearchText()) > -1;
			});
		});
		
		self.getFormattedConversionRate = function(conversions, viewers){
			var conversions = parseFloat(conversions);
			var viewers = parseFloat(viewers);
			
			//return 0.0 if viewers is zero to prevent division by zero errors
			if(viewers == 0){
				return "0.0";
			}
			
			var conversionRate = conversions / viewers;
			
			var conversionRateAsPercentage = conversionRate * 100;
			
			var formattedConversionRate = conversionRateAsPercentage.toFixed(1);
			
			return formattedConversionRate;
		}
		
		self.mapBarPropertiesToBarOptions = function(bar){
			
			self.barOptions.bar_height(bar.bar_height);
			
			self.barOptions.bar_text(unescape(bar.bar_text));
			self.barOptions.button_text(unescape(bar.button_text));
			self.barOptions.destination(bar.destination);
			
			self.barOptions.color_bar_background(bar.color_bar_background);
			self.barOptions.color_button_background(bar.color_button_background);
			self.barOptions.color_bar_text(bar.color_bar_text);
			self.barOptions.color_button_text(bar.color_button_text);
			
			self.barOptions.new_tab(bar.new_tab);
			self.barOptions.placement(bar.placement);
			self.barOptions.devices(bar.devices);
			self.barOptions.alignment(bar.alignment);
			self.barOptions.sticky(bar.sticky);
			self.barOptions.animation(bar.animation);
			self.barOptions.button_style(bar.button_style);
			self.barOptions.close_button_visibility(bar.close_button_visibility);
			
		}
		
		self.clearEditingBarInformation = function(){
			self.mapBarPropertiesToBarOptions(self.DEFAULT_BAR_OPTIONS);
		}
		
		self.beginEditingBar = function(bar){
			self.editingBarUuid(bar.bar_uuid);
			self.mapBarPropertiesToBarOptions(bar);
			
			self.editingBar(true);
		}
		
		self.getBarByUuid = function(bar_uuid){
			var matchedBar = _.find(self.bars(), function(bar){
				return bar.bar_uuid == bar_uuid;
			});
			
			return matchedBar ? matchedBar : false;
		}
		
		self.mapBarToJS = function(bar){
			var mappedBar = bar;
			
			mappedBar.bar_text = unescape(mappedBar.bar_text);
			mappedBar.button_text = unescape(mappedBar.button_text);
			
			return mappedBar;
		}
		
		self.escapeBarTextFields = function(barJS){
			
			barJS.bar_text = escape(barJS.bar_text);
			barJS.button_text = escape(barJS.button_text);
			
			return barJS;
		}
		
		self.createNewBarAndPublish = function(){
			self.publishingBar(true);
			
			self.createNewBar();
		}
		
		self.createNewBar = function(){
			
			if(!self.barOptionsAreValid()){
				return;
			}
			
			//convert entered destination to link form (basically, add 'http://' if missing at beginning)
			self.barOptions.destination(self.destinationAsLink());
			
			var barJS = ko.toJS(self.barOptions);
			
			//escape bar text fields
			barJS = self.escapeBarTextFields(barJS);
			
			if(self.publishingBar()){
				barJS.status = 'live';
			}
			
			self.syncingDataWithServer(true);
			
			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: {
					action: 'qb_admin_ajax',
					endpoint: 'create_bar',
					qb_admin_nonce: QB_GLOBALS.QB_ADMIN_NONCE,
					options: barJS
				},
				success: function(response){
					
					if(self.publishingBar()){
						
						self.resumeBar(response, function(){
							self.publishingBar(false);
							self.editingBar(false);
						});
						
					}
					else{
						//update statuses by resyncing all bar data
						self.syncData();
						self.editingBar(false);
					}
					
				},
				dataType: 'json'
			});
		}
		
		self.previewBarOptions = function(){
			//for each option, add as url parameter
			$previewURL = 'http://quickiebar.com/preview/#qbhide&';
			
			//add url to populate users blog in preview (will show on homepage)
			$previewURL += 'url=' + encodeURIComponent(document.location.origin + '#qbhide') + '&';
			
			_.each(self.barOptions, function(optionObservableProperty, propertyName){
				
				//skip status, & stats properties
				if(propertyName == 'status' || propertyName == 'views' || propertyName == 'conversions'){
					return;
				}
				
				$previewURL += propertyName + '=' + encodeURIComponent(optionObservableProperty()) + '&';
			});
			
			window.open($previewURL, '_blank');
		}
		
		self.saveBarChangesAndPublishBar = function(){
			self.publishingBar(true);
			
			self.saveBarChanges();
		}
		
		self.saveBarChanges = function(){
			
			if(!self.barOptionsAreValid() || self.editingBarUuid() == 0){
				return;
			}
			
			//convert entered destination to link form (basically, add 'http://' if missing at beginning)
			self.barOptions.destination(self.destinationAsLink());
			
			var barJS = ko.toJS(self.barOptions);
			
			barJS.bar_uuid = self.editingBarUuid();
			
			//remove readonly properties
			delete barJS.created_date;
			delete barJS.views;
			delete barJS.conversions;
			delete barJS.attribution;
			delete barJS.fixed_compatibility;
			
			//escape bar text fields
			barJS = self.escapeBarTextFields(barJS);
			
			self.syncingDataWithServer(true);

			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: {
					action: 'qb_admin_ajax',
					endpoint: 'update_bar',
					qb_admin_nonce: QB_GLOBALS.QB_ADMIN_NONCE,
					options: barJS
				},
				success: function(response){
					
					if(self.publishingBar()){
						
						self.resumeBar(response, function(){
							self.publishingBar(false);
							self.editingBar(false);
						});
						
					}
					else{
						//update statuses by resyncing all bar data
						self.syncData();
						self.editingBar(false);
					}
				},
				dataType: 'json'
			});
			
		}
		
		self.resumeBar = function(bar, callback){
			
			self.syncingDataWithServer(true);
			
			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: {
					action: 'qb_admin_ajax',
					endpoint: 'resume_bar',
					qb_admin_nonce: QB_GLOBALS.QB_ADMIN_NONCE,
					bar_uuid: bar.bar_uuid
				},
				success: function(response){
					
					//if we're replacing a currently running bar...
					//and we're not editing a single bar - so we're doing this from the "all bars" view
					if(!self.editingBar() && self.currentlyRunningBar()){
						self.upgradeModalIsVisible(true);
						self.replacedBarText(self.currentlyRunningBar().bar_text.substring(0, 30) + '...');
					}
					
					//update statuses by resyncing all bar data
					self.syncData();
					
					if(typeof callback == 'function'){
						callback();
					}
				},
				dataType: 'json'
			});
		}
		
		self.pauseBar = function(bar, callback){
			
			self.syncingDataWithServer(true);
			
			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: {
					action: 'qb_admin_ajax',
					endpoint: 'pause_bar',
					qb_admin_nonce: QB_GLOBALS.QB_ADMIN_NONCE,
					bar_uuid: bar.bar_uuid
				},
				success: function(response){
					
					//update statuses by resyncing all bar data
					self.syncData();
					
					if(typeof callback == 'function'){
						callback();
					}
				},
				dataType: 'json'
			});
		}
		
		self.deleteBar = function(){
			//TODO add logic checks here..."are you sure" stuff, etc.
			
			self.syncingDataWithServer(true);
			
			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: {
					action: 'qb_admin_ajax',
					endpoint: 'delete_bar',
					qb_admin_nonce: QB_GLOBALS.QB_ADMIN_NONCE,
					bar_uuid: self.editingBarUuid()
				},
				success: function(response){
					
					self.editingBar(false);
					self.deletingBar(false);
					
					//update statuses by resyncing all bar data
					self.syncData();
				},
				dataType: 'json'
			});
		}
		
		self.syncData = function(){
			self.syncingDataWithServer(true);
			
			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: {
					action: 'qb_admin_ajax',
					endpoint: 'get_bars',
					qb_admin_nonce: QB_GLOBALS.QB_ADMIN_NONCE
				},
				success: function(bars){
					
					//remove any bars we are currently storing locally
					self.bars.removeAll();
					
					//add all bars from server
					self.bars(_.map(bars, self.mapBarToJS));
					
					self.syncingDataWithServer(false);
				},
				dataType: 'json'
			});
			
		}
		
		self.init = function(){
			
			var colpickOptions = {
				showEvent: 'focus',
				layout: 'hex',
				submit: 0,
				livePreview: false,
				colorScheme: 'dark',
				onBeforeShow: function(){
					//hide all currently visible colorpickers
					$("#qb-color-options .option-value").colpickHide();
					
					//make sure we have the most up to date color
					$(this).colpickSetColor($(this).val(), true);
				},
				onChange: function(hsb,hex,rgb,el,bySetColor){
					if(!bySetColor){
						$(el).val('#' + hex.toUpperCase()).change();//need to fire .change() for knockout to register changes
					}
				}
			}
			
			//bind colpick (color picker) to input field for each color
			$("#qb-color-options .option-value").colpick(colpickOptions).keyup(function(){
				$(this).colpickSetColor(this.value);
			});
			
			//show color picker when sample color is picked
			$("#qb-color-options .sample").click(function(){
				$(this).parent().children('.option-value').focus();
			});
		}
		
	}
	
	//initialize the view model
	viewModel = new ViewModel();
	ko.applyBindings(viewModel, $('#quickiebar-bars')[0]);
	
	viewModel.init();
	viewModel.syncData();
	
});

//TODO add this to global quickiebar.js file
function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}