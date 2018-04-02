jQuery(document).ready(function($){
		
	function ViewModel() {
		var self = this;
		
		self.syncingDataWithServer = ko.observable(true);
		self.loadingConversionData = ko.observable(true);
		
		self.bars = ko.observableArray();
		
		self.chartCanvas;
		
		self.selectedBar = ko.observable(false);
		self.selectedBar.subscribe(function(){
			//every time we change selected bar, we need to refresh stats on the page
			self.loadConversionDataForSelectedBar();
		});
		
		self.conversionData = ko.observable();
		self.chartInterval = ko.observable("week");
		
		self.chartIntervalPadding = ko.computed(function(){
			if(self.chartInterval() == 'today' || self.chartInterval() == 'alltime'){
				return 50;
			}
			else if(self.chartInterval() == 'week'){
				return 15;
			}
			else if(self.chartInterval() == 'month'){
				return 4;
			}
		});
		
		self.getDayFromMysqlDate = function(date){
			var dateParts = date.split("-");
			
			var date = new Date(parseInt(dateParts[0]), parseInt(dateParts[1]) - 1, parseInt(dateParts[2]));
			
			var weekday = new Array(7);
			weekday[0]=  "SUN";
			weekday[1] = "MON";
			weekday[2] = "TUE";
			weekday[3] = "WED";
			weekday[4] = "THU";
			weekday[5] = "FRI";
			weekday[6] = "SAT";
			
			return weekday[date.getDay()];
		}
		
		self.conversionTotals = ko.computed(function(){
			
			if(!self.conversionData()){
				return false;
			}
			
			return {
				today: {
					views: _.reduce(_.last(self.conversionData().last30Views, 1), function(memo, data){ return memo + data.count; }, 0),
					conversions: _.reduce(_.last(self.conversionData().last30Conversions, 1), function(memo, data){ return memo + data.count; }, 0)
				},
				week: {
					views: _.reduce(_.last(self.conversionData().last30Views, 7), function(memo, data){ return memo + data.count; }, 0),
					conversions: _.reduce(_.last(self.conversionData().last30Conversions, 7), function(memo, data){ return memo + data.count; }, 0)
				},
				month: {
					views: _.reduce(_.last(self.conversionData().last30Views, 30), function(memo, data){ return memo + data.count; }, 0),
					conversions: _.reduce(_.last(self.conversionData().last30Conversions, 30), function(memo, data){ return memo + data.count; }, 0)
				},
				alltime: {
					views: self.conversionData().totalViews,
					conversions: self.conversionData().totalConversions
				}
			}
			
		});
		
		self.chartIntervalTotals = ko.computed(function(){
			
			var interval = self.chartInterval();
			
			var totals = self.conversionTotals();
			
			if(!interval || !totals){
				return {
					views: 0,
					conversions: 0,
					conversionRate: 0
				}
			}
			
			var totalsForInterval = totals[interval];
			
			return {
				views: totalsForInterval.views,
				conversions: totalsForInterval.conversions,
				conversionRate: totalsForInterval.conversions > 0 ? parseFloat((((totalsForInterval.conversions / totalsForInterval.views) * 100).toFixed(1))) : 0
			}
			
		});
		
		self.allTimeTotals = ko.computed(function(){
			
			if(!self.conversionData()){
				return {
					views: 0,
					conversions: 0,
					conversionRate: 0
				}
			}
			else{
				return {
					views: parseInt(self.conversionData().totalViews),
					conversions: parseInt(self.conversionData().totalConversions),
					conversionRate: self.conversionData().totalConversions > 0 ? parseFloat((((self.conversionData().totalConversions / self.conversionData().totalViews) * 100).toFixed(1))) : 0
				}
			}
			
		});
		
		//use the chart type, interval, and conversion data (raw from server) to compute data for display on the chart
		self.chartData = ko.computed(function(){
			if(!self.conversionData()){
				return false;
			}
			
			if(self.chartInterval() == 'week' || self.chartInterval() == 'month'){
				
				var chartIntervalDurationInDays = self.chartInterval() == 'week' ? 7 : 30;
				
				var chartLabels;
				
				if(self.chartInterval() == 'week'){
					chartLabels = _.last(_.map(self.conversionData()['last30Conversions'], function(data){ return self.getDayFromMysqlDate(data.date); }), chartIntervalDurationInDays);
				}
				else{
					chartLabels = _.last(_.map(self.conversionData()['last30Conversions'], function(data){ return parseInt(data.date.split('-')[2]); }), chartIntervalDurationInDays);
				}
				
				var chartData = _.last(_.map(self.conversionData()['last30Conversions'], function(data){ return data.count }), chartIntervalDurationInDays);
				
				//data for bar chart
				//http://www.chartjs.org/docs/#bar-chart-introduction
				return {
					labels: chartLabels,
					datasets: [
					{
						label: "My Only Dataset",
						fillColor: "rgba(136,205,229,.85)",
	          strokeColor: "rgba(136,205,229,.85)",
	          highlightFill: "rgba(136,205,229,1)",
	          highlightStroke: "rgba(136,205,229,1)",
						data: chartData
					}]
				}
				
			}
			else if(self.chartInterval() == 'alltime'){
				
				var totals = self.allTimeTotals();
				
				//data for doughnut/pie chart
				//http://www.chartjs.org/docs/#doughnut-pie-chart-introduction
				return [
				{
					value: totals.views - totals.conversions,
					color:'#CCCCCC',
					highlight: '#DDDDDD',
					label: 'No action'
				},
				{
					value: totals.conversions,
					color:'#84C334',
					highlight: '#84C334',
					label: 'Converted'
				}
				];
				
			}
			
			//return complete chart object for use by Chartjs
			
			
		});
		self.chartData.subscribe(function(newData){
			
			if(self.chartCanvas){
				self.chartCanvas.destroy();
			}
			var ctx = document.getElementById("qb-conversion-chart").getContext("2d");
			
			if(self.chartInterval() == 'week' || self.chartInterval() == 'month'){
				self.chartCanvas = new Chart(ctx).Bar(newData, {
					scaleBeginAtZero: true,
					scaleFontFamily: "'Montserrat', 'sans-serif'",
					//scaleShowLabels: false,
					maintainAspectRatio: false,
					scaleLineWidth: 0,

					//showScale:false,

					tooltipTitleFontFamily: "'Montserrat', 'sans-serif'",
					tooltipTitleFontSize: 12,
					tooltipYPadding: 2,
					tooltipXPadding: 8,
					tooltipCornerRadius: 2,
					tooltipTemplate: "<%=numberWithCommas(value)%>",

					scaleShowGridLines: false,
					barShowStroke: true,
					barValueSpacing: self.chartIntervalPadding(),
					responsive: true
				});
			}
			else if(self.chartInterval() == 'alltime'){
				self.chartCanvas = new Chart(ctx).Doughnut(newData, {
					percentageInnerCutout : 65,
					animateScale: true,
					animationEasing: 'easeInOutQuad',
					maintainAspectRation: true,

					tooltipTitleFontFamily: "'Montserrat', 'sans-serif'",
					tooltipTitleFontSize: 12,
					tooltipYPadding: 2,
					tooltipXPadding: 8,
					tooltipCornerRadius: 2,
					
					onAnimationComplete: function(){
						this.showTooltip(this.segments, true);
					},
					tooltipEvents: [],
					
					//tooltipTemplate: "<%=numberWithCommas(value)%>",
					
					responsive: true
				});
			}
			
		});
		
		self.getBarByUuid = function(bar_uuid){
			var matchedBar = _.find(self.bars(), function(bar){
				return bar.bar_uuid == bar_uuid;
			});
			
			return matchedBar ? matchedBar : false;
		}
		
		self.loadConversionDataForSelectedBar = function(){
			
			if(!self.selectedBar()){
				return;
			}
			
			self.loadingConversionData(true);
			
			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: {
					action: 'qb_admin_ajax',
					endpoint: 'get_conversions_for_bar',
					bar_uuid: self.selectedBar().bar_uuid,
					qb_admin_nonce: QB_GLOBALS.QB_ADMIN_NONCE
				},
				success: function(conversionData){
					self.conversionData(conversionData);
					
					self.loadingConversionData(false);
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
					
					//unselected any currently selected bar
					self.selectedBar(false);
					
					//remove any bars we are currently storing locally
					self.bars.removeAll();
					
					//add all bars from server
					self.bars(bars);
					
					self.syncingDataWithServer(false);
					
					if(self.bars().length > 0){
						
						var hash = location.hash.substring(1);
						//if we have specified a bar id in the hash load data for this bar
						if(hash){
							if(self.getBarByUuid(hash)){
								self.selectedBar(self.getBarByUuid(hash));
							}
							else{
								//couldn't find bar, load first bar
								self.selectedBar(self.bars()[0]);
							}
						}
						//no bar specified in hash, load first bar
						else{
							self.selectedBar(self.bars()[0]);
						}
					}
					else{
						//no bars created yet, don't try to load any conversion data
					}
					
				},
				dataType: 'json'
			});
			
		}
		
		self.init = function(){
			
		}
		
	}
	
	//initialize the view model
	viewModel = new ViewModel();
	ko.applyBindings(viewModel, $('#quickiebar-conversions')[0]);
	
	viewModel.init();
	viewModel.syncData();
	
});

//TODO add this to global quickiebar.js file
function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}