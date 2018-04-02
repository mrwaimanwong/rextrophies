jQuery(document).ready(function($){
		
	function ViewModel() {
		var self = this;
		
		self.syncingDataWithServer = ko.observable(true);
		self.savingSettings = ko.observable(false);
		self.subscribing = ko.observable(false);
		self.deletingPlugin = ko.observable(false);
		
		self.pages = ko.observable();
		self.posts = ko.observable();
		self.categories = ko.observable();
		
		self.managingPageExceptions = ko.observable(false);
		self.managingPostExceptions = ko.observable(false);
		self.managingCategoryExceptions = ko.observable(false);
		
		self.settings = {
			attribution: ko.observable(),
			visibility: ko.observable(),
			page_visibility: ko.observable(),
			page_exceptions: ko.observableArray(),
			post_visibility: ko.observable(),
			post_exceptions: ko.observableArray(),
			category_visibility: ko.observable(),
			category_exceptions: ko.observableArray(),
			email: ko.observable(),
			subscribed: ko.observable(),
			fixed_compatibility: ko.observable(),
			debug_mode: ko.observable(),
			device_visibility: ko.observable(),
			archive_page_visibility: ko.observable(),
			bar_zindex: ko.observable(),
			custom_post_type_visibility: ko.observable()
		}
		self.settings.cache = {
			attribution: ko.observable(),
			visibility: ko.observable(),
			page_visibility: ko.observable(),
			page_exceptions: ko.observableArray(),
			post_visibility: ko.observable(),
			post_exceptions: ko.observableArray(),
			category_visibility: ko.observable(),
			category_exceptions: ko.observableArray(),
			custom_visibility: ko.observable(),
			email: ko.observable(),
			fixed_compatibility: ko.observable(),
			debug_mode: ko.observable(),
			device_visibility: ko.observable(),
			archive_page_visibility: ko.observable(),
			bar_zindex: ko.observable(),
			custom_post_type_visibility: ko.observable()
		}
		
		self.settings.page_visibility.subscribe(function(){
			//when value changes, remove exceptions list since user probably has no use for carrying over settings from "inclusions" to "exceptions"
			//self.pages.selected.removeAll();
			self.settings.page_exceptions.removeAll();
			self.managingPageExceptions(false);
		});
		self.settings.post_visibility.subscribe(function(){
			//when value changes, remove exceptions list since user probably has no use for carrying over settings from "inclusions" to "exceptions"
			//self.posts.selected.removeAll();
			self.settings.post_exceptions.removeAll();
			self.managingPostExceptions(false);
		});
		self.settings.category_visibility.subscribe(function(){
			//when value changes, remove exceptions list since user probably has no use for carrying over settings from "inclusions" to "exceptions"
			//self.categories.selected.removeAll();
			self.settings.category_exceptions.removeAll();
			self.managingCategoryExceptions(false);
		});
		
		self.pageIsExcepted = function(page){
			if(!self.settings.page_exceptions() || self.settings.page_exceptions().length == 0){
				return false;
			}
			
			return self.settings.page_exceptions().indexOf(page.ID) > -1;
		}
		self.postIsExcepted = function(post){
			if(!self.settings.post_exceptions() || self.settings.post_exceptions().length == 0){
				return false;
			}
			
			return self.settings.post_exceptions().indexOf(post.ID) > -1;
		}
		self.categoryIsExcepted = function(category){
			if(!self.settings.category_exceptions() || self.settings.category_exceptions().length == 0){
				return false;
			}
			
			return self.settings.category_exceptions().indexOf(category.ID) > -1;
		}
		
		self.togglePageException = function(page){
			if(self.pageIsExcepted(page)){
				self.settings.page_exceptions.remove(page.ID);
			}
			else{
				self.settings.page_exceptions.push(page.ID);
			}
		}
		self.togglePostException = function(post){
			if(self.postIsExcepted(post)){
				self.settings.post_exceptions.remove(post.ID);
			}
			else{
				self.settings.post_exceptions.push(post.ID);
			}
		}
		self.toggleCategoryException = function(category){
			if(self.categoryIsExcepted(category)){
				self.settings.category_exceptions.remove(category.ID);
			}
			else{
				self.settings.category_exceptions.push(category.ID);
			}
		}
		
		//count funtions for page & post selections
		self.pages.selectedCount = ko.computed(function(){
			if(!self.pages()){
				return 0;
			}
			
			if(self.settings.page_visibility() == 'show'){
				return self.pages().length - self.settings.page_exceptions().length;
			}
			else{
				return self.settings.page_exceptions().length;
			}
		});
		self.posts.selectedCount = ko.computed(function(){
			if(!self.posts()){
				return 0;
			}
			
			if(self.settings.post_visibility() == 'show'){
				return self.posts().length - self.settings.post_exceptions().length;
			}
			else{
				return self.settings.post_exceptions().length;
			}
		});
		
		self.categories.selectedCount = ko.computed(function(){
			if(!self.categories()){
				return 0;
			}
			
			if(self.settings.category_visibility() == 'show'){
				return self.categories().length - self.settings.category_exceptions().length;
			}
			else{
				return self.settings.category_exceptions().length;
			}
		});
		
		self.categoryFilteringEnabled = ko.computed(function(){
			return self.settings.post_visibility() == 'show' && self.settings.post_exceptions().length == 0;
		});
		self.postsFilteringEnabled = ko.computed(function(){
			return self.settings.category_visibility() == 'show' && self.settings.category_exceptions().length == 0;
		});
		
		self.settings.cacheCurrentSettings = function(){
			
			self.settings.cache.attribution(self.settings.attribution());
			self.settings.cache.visibility(self.settings.visibility());
			self.settings.cache.page_visibility(self.settings.page_visibility());
			self.settings.cache.page_exceptions(self.settings.page_exceptions().slice());//slice to create deep copy
			self.settings.cache.post_visibility(self.settings.post_visibility());
			self.settings.cache.post_exceptions(self.settings.post_exceptions().slice());//slice to create deep copy
			self.settings.cache.category_visibility(self.settings.category_visibility() ? self.settings.category_visibility() : 'show');
			self.settings.cache.category_exceptions(self.settings.category_exceptions().slice());//slice to create deep copy
			self.settings.cache.email(self.settings.email());
			self.settings.cache.fixed_compatibility(self.settings.fixed_compatibility());
			self.settings.cache.debug_mode(self.settings.debug_mode());
			self.settings.cache.device_visibility(self.settings.device_visibility());
			self.settings.cache.archive_page_visibility(self.settings.archive_page_visibility());
			self.settings.cache.bar_zindex(self.settings.bar_zindex());
			self.settings.cache.custom_post_type_visibility(self.settings.custom_post_type_visibility());
			
		}
		
		self.settings.dirty = ko.computed(function(){
			
			if(self.settings.attribution() != self.settings.cache.attribution() ||
				self.settings.visibility() != self.settings.cache.visibility() ||
				self.settings.page_visibility() != self.settings.cache.page_visibility() ||
				JSON.stringify(self.settings.page_exceptions()) != JSON.stringify(self.settings.cache.page_exceptions()) ||
				self.settings.post_visibility() != self.settings.cache.post_visibility() ||
				JSON.stringify(self.settings.post_exceptions()) != JSON.stringify(self.settings.cache.post_exceptions()) ||
				self.settings.category_visibility() != self.settings.cache.category_visibility() ||
				JSON.stringify(self.settings.category_exceptions()) != JSON.stringify(self.settings.cache.category_exceptions()) ||
				self.settings.email() != self.settings.cache.email() ||
				self.settings.fixed_compatibility() != self.settings.cache.fixed_compatibility() ||
				self.settings.debug_mode() != self.settings.cache.debug_mode() ||
				self.settings.device_visibility() != self.settings.cache.device_visibility() ||
				self.settings.archive_page_visibility() != self.settings.cache.archive_page_visibility() ||
				self.settings.bar_zindex() != self.settings.cache.bar_zindex() ||
				self.settings.custom_post_type_visibility() != self.settings.cache.custom_post_type_visibility()
			){
				return true;
			}
			else{
				return false;
			}
			
		});
		
		self.settings.dirty.subscribe(function(newValue){
			if(newValue){
				self.dismissNotification();
			}
		});
		
		
		self.notification = ko.observable(false);
		self.pushNotification = function(text, type){
			self.notification({
				text: text,
				status: type
			});
		}
		self.dismissNotification = function(){
			self.notification(false);
		}
		
		self.destroyPluginData = function(){
			
			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: {
					action: 'qb_admin_ajax',
					endpoint: 'destroy_plugin_data',
					qb_admin_nonce: QB_GLOBALS.QB_ADMIN_NONCE
				},
				success: function(response){
					
					//navigate up a directory - not sure exactly how well this works but we have to do something...
					document.location.href="../";
					
				},
				dataType: 'json'
			});
			
		}
		
		self.saveSettings = function(callback){
			if(self.savingSettings()){
				return;
			}
			
			self.savingSettings(true);
			
			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: {
					action: 'qb_admin_ajax',
					endpoint: 'update_quickiebar_settings',
					qb_admin_nonce: QB_GLOBALS.QB_ADMIN_NONCE,
					settings: {
						attribution: self.settings.attribution(),
						visibility: self.settings.visibility(),
						page_visibility: self.settings.page_visibility(),
						page_exceptions: self.settings.page_exceptions().length > 0 ? JSON.stringify(self.settings.page_exceptions()) : false,
						post_visibility: self.settings.post_visibility(),
						post_exceptions: self.settings.post_exceptions().length > 0 ? JSON.stringify(self.settings.post_exceptions()) : false,
						category_visibility: self.settings.category_visibility(),
						category_exceptions: self.settings.category_exceptions().length > 0 ? JSON.stringify(self.settings.category_exceptions()) : false,
						email: self.settings.email(),
						subscribed: self.settings.subscribed(),
						fixed_compatibility: self.settings.fixed_compatibility(),
						debug_mode: self.settings.debug_mode(),
						device_visibility: self.settings.device_visibility(),
						archive_page_visibility: self.settings.archive_page_visibility(),
						bar_zindex: self.settings.bar_zindex(),
						custom_post_type_visibility: self.settings.custom_post_type_visibility()
					}
				},
				success: function(response){
					
					self.pushNotification('Settings updated', 'success');
					self.settings.cacheCurrentSettings();
					
					self.savingSettings(false);
					
					if(typeof callback == 'function'){
						callback();
					}
				},
				dataType: 'json'
			});
		}
		
		self.subscribe = function(){
			//Get value current saved in email field
			$email_address = self.settings.email();
			
			if(self.subscribing()){
				return;
			}
			
			self.subscribing(true);
			
			//POST subscirbe form to mailchimp servers and handle response by updating page
			$.ajax({
				type: "POST",
				url: "//philbaylog.us6.list-manage.com/subscribe/post-json?u=e551001469dd03b8e20452a24&id=37c10f22db&c=?",
				data: {
					EMAIL: $email_address,
					FNAME: qb_settings.fname,
					WEBSITE: qb_settings.website
				},
				success: function(response){
					
					if(response.result == 'success'){
						
						//save subscribed status & current email address (if changed) to quickiebar
						self.settings.subscribed(true);
						
						self.saveSettings(function(){
							$('#subscribe-success-text').show();
							self.subscribing(false);
						});
						
					}
					else{
						self.pushNotification('Couldn\'t subscribe at this time', 'failure');
					}
					
				},
				dataType: 'json'
			});
		}
		
		self.syncData = function(){
			self.syncingDataWithServer(true);
			
			//get settings straight from php localization
			self.settings.attribution(qb_settings.attribution);
			self.settings.visibility(qb_settings.visibility);
			self.settings.page_visibility(qb_settings.page_visibility ? qb_settings.page_visibility : 'show');
			self.settings.page_exceptions((qb_settings.page_exceptions && qb_settings.page_exceptions != 'false') ? JSON.parse(qb_settings.page_exceptions) : []);
			self.settings.post_visibility(qb_settings.post_visibility ? qb_settings.post_visibility : 'show');
			self.settings.post_exceptions((qb_settings.post_exceptions && qb_settings.post_exceptions != 'false') ? JSON.parse(qb_settings.post_exceptions) : []);
			
			self.settings.category_visibility(qb_settings.category_visibility ? qb_settings.category_visibility : 'show');
			self.settings.category_exceptions((qb_settings.category_exceptions && qb_settings.category_exceptions != 'false') ? JSON.parse(qb_settings.category_exceptions) : []);
			
			self.settings.email(qb_settings.email);
			self.settings.subscribed(qb_settings.subscribed);
			self.settings.fixed_compatibility(qb_settings.fixed_compatibility);
			self.settings.debug_mode(qb_settings.debug_mode);
			self.settings.device_visibility(qb_settings.device_visibility);
			
			self.settings.archive_page_visibility(qb_settings.archive_page_visibility);
			self.settings.bar_zindex(qb_settings.bar_zindex);
			
			self.settings.custom_post_type_visibility(qb_settings.custom_post_type_visibility);
			
			//if page exceptions or post exceptions, toggle those menus visibility accordingly
			if(self.settings.page_exceptions().length > 0){
				self.managingPageExceptions(true);
			}
			if(self.settings.post_exceptions().length > 0){
				self.managingPostExceptions(true);
			}
			if(self.settings.category_exceptions().length > 0){
				self.managingCategoryExceptions(true);
			}
			
			//fetch all pages & posts for custom visibility setting
			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: {
					action: 'qb_admin_ajax',
					endpoint: 'get_pages_and_posts_and_categories',
					qb_admin_nonce: QB_GLOBALS.QB_ADMIN_NONCE
				},
				success: function(allPagesAndPostsAndCategories){
					
					self.pages(allPagesAndPostsAndCategories.pages);
					self.posts(allPagesAndPostsAndCategories.posts);
					self.categories(_.map(allPagesAndPostsAndCategories.categories, function(category){
						return {
							ID: parseInt(category.cat_ID),
							name: category.name
						}
					}));
					
					//if page or post exceptions contain post ids that have been deleted, remove these values
					//don't worry about removing from server. this will update automatically the next time the user modifies the exceptions, and it's not doing any harm
					$.each(self.settings.page_exceptions(), function(index, pageID){
						var matchedPage = _.find(self.pages(), function(pages){
							return pages.ID == pageID;
						});
						
						if(!matchedPage){
							self.settings.page_exceptions.remove(pageID);
						}
					});
					$.each(self.settings.post_exceptions(), function(index, postID){
						var matchedPost = _.find(self.posts(), function(posts){
							return posts.ID == postID;
						});
						
						if(!matchedPost){
							self.settings.post_exceptions.remove(postID);
						}
					});
					$.each(self.settings.category_exceptions(), function(index, categoryID){
						var matchedCategory = _.find(self.categories(), function(category){
							return parseInt(category.ID) == categoryID;
						});
						
						if(!matchedCategory){
							self.settings.category_exceptions.remove(categoryID);
						}
					});
					
					self.settings.cacheCurrentSettings();
					
					self.syncingDataWithServer(false);
				},
				dataType: 'json'
			});
			
		}
		
		self.init = function(){
			
		}
		
	}
	
	//initialize the view model
	viewModel = new ViewModel();
	ko.applyBindings(viewModel, $('#quickiebar-settings')[0]);
	
	viewModel.init();
	
	viewModel.syncData();
	
});