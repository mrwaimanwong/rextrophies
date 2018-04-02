jQuery(document).ready(function($){
		
	function ViewModel() {
		var self = this;
		
		self.completingSetup = ko.observable(false);
		self.subscribing = ko.observable(false);
		
		self.emailAddress = ko.observable();
		
		self.subscribeAndCompleteSetup = function(){
			
			if(self.subscribing()){
				return;
			}
			
			self.subscribing(true);
			
			//POST subscirbe form to mailchimp servers and handle response by updating page
			$.ajax({
				type: "POST",
				url: "//philbaylog.us6.list-manage.com/subscribe/post-json?u=e551001469dd03b8e20452a24&id=37c10f22db&c=?",
				data: {
					EMAIL: self.emailAddress(),
					FNAME: qb_setup.email,
					WEBSITE: qb_setup.website
				},
				success: function(response){
					
					if(response.result == 'success'){
						
						self.completeSetup();
						
					}
					else{
						//bad email. TODO notify user somehow
						self.subscribing(false);
					}
					
				},
				dataType: 'json'
			});
		}
		
		self.completeSetup = function(){
			if(self.completingSetup()){
				return;
			}
			
			self.completingSetup(true);
			
			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: {
					action: 'qb_admin_ajax',
					endpoint: 'complete_setup',
					qb_admin_nonce: QB_GLOBALS.QB_ADMIN_NONCE,
					email: self.emailAddress(),
					subscribed: self.subscribing()//if user is also subscribing to mailchimp list, update this on the backend
				},
				success: function(response){
					self.completingSetup(false);
					
					//reload the page, since we've updated the setup completed user option, this will now load the bars page
					location.reload();
					
				},
				dataType: 'json'
			});
		}
		
		self.syncData = function(){
			//don't use loading placeholder since this data is local
			self.emailAddress(qb_setup.email);
		}
		
		self.init = function(){
			
		}
		
	}
	
	//initialize the view model
	viewModel = new ViewModel();
	ko.applyBindings(viewModel, $('#quickiebar-setup')[0]);
	
	viewModel.init();
	
	viewModel.syncData();
	
});