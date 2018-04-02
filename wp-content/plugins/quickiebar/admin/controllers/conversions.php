<?php

/*
* qb_conversions
*
* @description: conroller for quickiebar conversions sub menu page
*
*/

class qb_conversions{

	var $action;

	function __construct(){
		add_action('admin_menu', array($this, 'admin_menu'));
	}

	function admin_menu(){
		$page = add_submenu_page('quickiebar', 'Conversions', 'Conversions', 'manage_options', 'quickiebar-conversions', array($this, 'html') );
	}

	static function save_view($user_uuid, $bar_uuid, $timestamp = false){
		global $wpdb;

		//0 is reserved for previewing bars. If an attempt to save data for a bar with uuid of 0 comes through, it is invalid
		if(!$bar_uuid || $bar_uuid == 0 || $bar_uuid == "0" || $bar_uuid == ""){
			return;
		}

		if(!$timestamp){
			$timestamp = current_time('mysql');
		}

		$result = $wpdb->insert(
			$wpdb->qb_views,
			array(
				'created_date' => $timestamp,
				'user_uuid' => $user_uuid,
				'bar_uuid' => $bar_uuid
			)
		);

		return;
	}

	static function save_conversion($user_uuid, $bar_uuid, $timestamp = false){
		global $wpdb;

		//0 is reserved for previewing bars. If an attempt to save data for a bar with uuid of 0 comes through, it is invalid
		if(!$bar_uuid || $bar_uuid == 0 || $bar_uuid == "0" || $bar_uuid == ""){
			return;
		}

		if(!$timestamp){
			$timestamp = current_time('mysql');
		}

		$result = $wpdb->insert(
			$wpdb->qb_conversions,
			array(
				'created_date' => $timestamp,
				'user_uuid' => $user_uuid,
				'bar_uuid' => $bar_uuid
			)
		);

		return;
	}

	static function populate_db_for_tests($bar_uuid){
		//POPULATE DB
		//randomly add records to DB for the last 30 days

		for($j = 0; $j < 10; $j++){
			for($i = 0; $i < 365; $i++){

				//random user information
				$user_uuid = substr(uniqid(rand(), true), 0, 15);

				$timestamp = date('Y-m-d H:i:s', strtotime("-" . rand(0,$i) . " days"));

				$action = rand(0,20);
				//convert
				if($action < 3){
					qb_conversions::save_conversion($user_uuid, $bar_uuid, $timestamp);
				}
				//view
				else{
					qb_conversions::save_view($user_uuid, $bar_uuid, $timestamp);
				}
			}
		}
	}

	//get conversion statistics for a bar with the specified bar_uuid, and return in the specified format
	static function get_conversions_for_bar($bar_uuid, $format = 'php'){

		global $wpdb;

		//qb_conversions::populate_db_for_tests('54d0fb28785ce');

		$views = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT *
				FROM $wpdb->qb_views
				WHERE bar_uuid = %s
				", $bar_uuid
			)
		);

		$conversions = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT *
				FROM $wpdb->qb_conversions
				WHERE bar_uuid = %s
				", $bar_uuid
			)
		);

		$allQueryResults = array();
		$allQueryResults['viewsByDate'] = array();
		$allQueryResults['viewsByHour'] = array();
		$allQueryResults['conversionsByDate'] = array();
		$allQueryResults['conversionsByHour'] = array();

		//this is the code with the DISTINCT field specified
		//TODO use some version of this
		/*$q = "SELECT DATE(created_date) as created_date, COUNT(DISTINCT user_uuid) views FROM $wpdb->qb_views WHERE user_uuid != '' GROUP BY DATE(created_date)";
		$viewsByDate = $wpdb->get_results($q);*/

		$currentWordpressTime = current_time('mysql');
		$currentWordpress = the_date('mysql');

		/*New Query*/
		$query = "SELECT DATE(created_date) as created_date, COUNT(id) views FROM $wpdb->qb_views WHERE bar_uuid = %s AND user_uuid != '' GROUP BY DATE(created_date)";
		$queryResults = $wpdb->get_results($wpdb->prepare($query, $bar_uuid));

		//reformat so created_date is the primary index and save
		foreach($queryResults as $view){
			$allQueryResults['viewsByDate'][$view->created_date] = $view->views;
		}

		/*New Query*/
		$query = "SELECT EXTRACT(hour from created_date) as created_hour, COUNT(id) views FROM $wpdb->qb_views WHERE bar_uuid = %s AND user_uuid != '' AND DATE(created_date) = CURDATE() GROUP BY EXTRACT(hour from created_date)";
		$queryResults = $wpdb->get_results($wpdb->prepare($query, $bar_uuid));

		//reformat so created_date is the primary index and save
		foreach($queryResults as $view){
			$allQueryResults['viewsByHour'][$view->created_hour] = $view->views;
		}

		/*New Query*/
		$query = "SELECT DATE(created_date) as created_date, COUNT(id) conversions FROM $wpdb->qb_conversions WHERE bar_uuid = %s AND user_uuid != '' GROUP BY DATE(created_date)";
		$queryResults = $wpdb->get_results($wpdb->prepare($query, $bar_uuid));

		//reformat so created_date is the primary index and save
		foreach($queryResults as $conversion){
			$allQueryResults['conversionsByDate'][$conversion->created_date] = $conversion->conversions;
		}

		/*New Query*/
		$query = "SELECT EXTRACT(hour from created_date) as created_hour, COUNT(id) conversions FROM $wpdb->qb_conversions WHERE bar_uuid = %s AND user_uuid != '' AND DATE_SUB(CURDATE(), INTERVAL 1 DAY) GROUP BY EXTRACT(hour from created_date)";
		$queryResults = $wpdb->get_results($wpdb->prepare($query, $bar_uuid));

		//reformat so created_date is the primary index and save
		foreach($queryResults as $conversion){
			$allQueryResults['conversionsByHour'][$conversion->created_hour] = $conversion->conversions;
		}

		/*All Time Total Views For Bar*/
		$query = "SELECT COUNT(*) FROM $wpdb->qb_views WHERE bar_uuid = %s AND user_uuid != ''";
		$allQueryResults['totalViews'] = $wpdb->get_var($wpdb->prepare($query, $bar_uuid));

		/*All Time Total Views For Bar*/
		$query = "SELECT COUNT(id) FROM $wpdb->qb_conversions WHERE bar_uuid = %s AND user_uuid != ''";
		$allQueryResults['totalConversions'] = $wpdb->get_var($wpdb->prepare($query, $bar_uuid));

		//****************END QUERIES

		$last24Hours = array();

		//for the last 30 days, set up our array
		$last30Days = array();

		//set initial day to today (wordpress time)
		//$day = current_time('Y-m-d');//Can't use because requires WordPress 3.9 or greater
		$day = date('Y-m-d', current_time('timestamp') );

		//while $day is greater than or equal to one month ago...
		while($day >= date('Y-m-d', strtotime("-30 days"))){

			array_push($last30Days, $day);
			//echo date('Y-m-d', strtotime("-1 day", $day));

			//subtract one day from current day
			$day = date('Y-m-d', strtotime("-1 day", strtotime($day)));
		}

		//reverse the array so the 30th day ago is first, and today is last
		$last30Days = array_reverse($last30Days);

		$last30Views = array();
		$last30Conversions = array();

		//$last30Rate = array();
		//$last30Ratio = array();

		//for each of the last 30 days, find the counts corresponding for each day and add to our $results object
		foreach($last30Days as $day){

			//extract view count
			if(array_key_exists($day, $allQueryResults['viewsByDate'])){
				array_push($last30Views, array(
					'date' => $day,
					'count' => intval($allQueryResults['viewsByDate'][$day])
				) );
			}
			else{
				array_push($last30Views, array(
					'date' => $day,
					'count' => 0
				) );
			}

			//extract conversion count
			if(array_key_exists($day, $allQueryResults['conversionsByDate'])){
				array_push($last30Conversions, array(
					'date' => $day,
					'count' => intval($allQueryResults['conversionsByDate'][$day])
				) );
			}
			else{
				array_push($last30Conversions, array(
					'date' => $day,
					'count' => 0
				) );
			}

		}

		$result = array(
			'last30Views' => $last30Views,
			'last30Conversions' => $last30Conversions,
			'totalViews' => $allQueryResults['totalViews'],
			'totalConversions' => $allQueryResults['totalConversions'],
		);

		if($format == 'json'){
			return json_encode($result);
		}
		else{
			return $result;
		}

	}

	//echo out the conversions view (html file) file when loading the bars admin page
	function html(){
		echo file_get_contents(QB_PLUGIN_PATH . 'admin/views/conversions.html');

		//enqueue scripts for this view
		$this->enqueue_scripts_for_view();

	}

	function enqueue_scripts_for_view(){

		wp_enqueue_script('chartjs', QB_PLUGIN_URL . 'admin/js/inc/chartjs-1.0.1.min.js', array(  ), '1.0.1', true);

		wp_enqueue_script('qb-conversions', QB_PLUGIN_URL . 'admin/js/conversions.js', array('jquery', 'knockout', 'underscore', 'chartjs'), microtime(), true);
		wp_localize_script('qb-conversions', 'QB_GLOBALS', array( 'QB_ADMIN_NONCE' => wp_create_nonce('qb_admin_nonce') ));

	}
}

new qb_conversions();

?>
