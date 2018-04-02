<?php

/*
* qb_bars
*
* @description: conroller for quickiebar bars sub menu page
*
*/

class qb_bars{

	var $action;

	function __construct(){
		add_action('admin_menu', array($this, 'admin_menu'));
	}

	function admin_menu(){
		$page = add_submenu_page('quickiebar', 'Bars', 'Bars', 'manage_options', 'quickiebar', array($this, 'html') );
	}

	//create a new quickiebar with a given set of options specified by user in an ajax call
	static function create_bar($options, $format = 'php'){

		global $wpdb;

		$newUuid = uniqid();//not random, but unique enough for our purposes http://php.net/manual/en/function.uniqid.php

		$options['created_date'] = current_time('mysql');
		$options['bar_uuid'] = $newUuid;

		$result = $wpdb->insert(
			$wpdb->qb_bars,
			$options
		);

		//lastly, get the newly created bar and return in specified format
		return qb_bars::get_bar_by_uuid($newUuid, $format);

	}

	//update an existing bar
	static function update_bar($options, $format = 'php'){
		global $wpdb;

		$bar_uuid = $options['bar_uuid'];

		$result = $wpdb->update(
			$wpdb->qb_bars,
			$options,
			array('bar_uuid' => $bar_uuid)
		);

		//lastly, get the updated bar and return in specified format
		return qb_bars::get_bar_by_uuid($bar_uuid, $format);
	}

	//get a single bar for display to user (whichever bar is live)
	//do not populate with stats or any other non-public data
	static function get_bar($format = 'json'){
		global $wpdb;

		$results = $wpdb->get_results(
			"
			SELECT *
			FROM $wpdb->qb_bars
			WHERE status = 'live'
			"
		);

		//if no live bars, return empty array
		if(count($results) == 0){
			return ($format == 'json' ? json_encode(false) : false);
		}

		//otherwise, take first live bar
		$the_bar = $results[0];

		//append attribution option from admin settings to each bar
		//TODO figure out how much this affects performance...(I'm guessing not much since both these calls will likely be cached?)
		$qb_attribution = get_option('qb_attribution');
		$qb_fixed_compatibility = get_option('qb_fixed_compatibility');
		$qb_debug_mode = get_option('qb_debug_mode');
		$qb_device_visibility = get_option('qb_device_visibility');
		$qb_bar_zindex = get_option('qb_bar_zindex');

		$the_bar->attribution = $qb_attribution;
		$the_bar->fixed_compatibility = $qb_fixed_compatibility;
		$the_bar->debug_mode = $qb_debug_mode;
		$the_bar->device_visibility = $qb_device_visibility;
		$the_bar->bar_zindex = $qb_bar_zindex;

		if($format == 'json'){
			return json_encode($the_bar);
		}
		else{
			return $the_bar;
		}
	}

	//get a bar with an bar_uuid and all stats / options along with it
	static function get_bar_by_uuid($bar_uuid, $format = 'php'){
		global $wpdb;

		$bar = $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT *
				FROM $wpdb->qb_bars
				WHERE bar_uuid = %s AND status != 'deleted'
				"
			, $bar_uuid)
		);

		$bar->attribution = get_option('qb_attribution');
		$bar->fixed_compatibility = get_option('qb_fixed_compatibility');
		$bar->debug_mode = get_option('qb_debug_mode');
		$bar->device_visibility = get_option('qb_device_visibility');
		$bar->bar_zindex = get_option('qb_bar_zindex');

		//count views and save in "views" property
		$query = "SELECT COUNT(*) FROM $wpdb->qb_views WHERE bar_uuid = %s";
		$bar->views = $wpdb->get_var($wpdb->prepare($query, $bar_uuid));

		//count conversions and save in "conversions" property
		$query = "SELECT COUNT(*) FROM $wpdb->qb_conversions WHERE bar_uuid = %s";
		$bar->conversions = $wpdb->get_var($wpdb->prepare($query, $bar_uuid));

		if($format == 'json'){
			return json_encode($bar);
		}
		else{
			return $bar;
		}
	}

	//get a list of all bars in the specified format
	static function get_bars($format = 'php'){
		global $wpdb;

		$bars = array();
		$barsWithStats = array();

		$bars = $wpdb->get_results(
			"
			SELECT *
			FROM $wpdb->qb_bars
			WHERE status != 'deleted'
			"
		);

		//for each barid, get the full bar object using get_bar_by_uuid
		foreach($bars as $bar){
			array_push($barsWithStats, qb_bars::get_bar_by_uuid($bar->bar_uuid, 'php'));
		}

		if($format == 'json'){
			return json_encode($barsWithStats);
		}
		else{
			return $barsWithStats;
		}
	}

	//update a bars status to live with the given id
	static function resume_bar($bar_uuid, $format = 'php'){
		global $wpdb;

		//first, pause all bars which are currently live (should only be one)
		$result = $wpdb->update(
			$wpdb->qb_bars,
			array( 'status' => 'paused' ),
			array('status' => 'live')
		);

		//now update the bar with bar_uuid == $bar_uuid to be live
		$result = $wpdb->update(
			$wpdb->qb_bars,
			array( 'status' => 'live' ),
			array('bar_uuid' => $bar_uuid)
		);

		//lastly, get the updated bar and return in specified format
		return qb_bars::get_bar_by_uuid($bar_uuid, $format);
	}

	//update a bars status to paused with the given id
	static function pause_bar($bar_uuid, $format = 'php'){
		global $wpdb;

		//TODO sanitize this input
		$result = $wpdb->update(
			$wpdb->qb_bars,
			array( 'status' => 'paused' ),
			array('bar_uuid' => $bar_uuid)
		);

		//lastly, get the updated bar and return in specified format
		return qb_bars::get_bar_by_uuid($bar_uuid, $format);
	}

	//deletes a bar by updating its status to deleted
	static function delete_bar($bar_uuid, $format = 'php'){
		global $wpdb;

		//TODO sanitize this input
		$result = $wpdb->update(
			$wpdb->qb_bars,
			array( 'status' => 'deleted' ),
			array('bar_uuid' => $bar_uuid)
		);

		//return true after delete is successful
		if($format == 'json'){
			return json_encode(true);
		}
		else{
			return true;
		}
	}

	//echo out the bars view (html file) file when loading the bars admin page
	function html(){
		echo file_get_contents(QB_PLUGIN_PATH . 'admin/views/bars.html');

		//enqueue scripts for this view
		$this->enqueue_scripts_for_view();

		/*If we're loading the Bars page, we need to load the public quickiebar script to render the preview bar*/
		quickiebar()->load_quickiebar_script();
	}

	function enqueue_scripts_for_view(){

		wp_enqueue_script('qb-bars', QB_PLUGIN_URL . 'admin/js/bars.js', array('jquery', 'knockout', 'underscore'), microtime(), true);
		wp_localize_script('qb-bars', 'QB_GLOBALS', array( 'QB_ADMIN_NONCE' => wp_create_nonce('qb_admin_nonce') ));

		wp_enqueue_script('colpick', QB_PLUGIN_URL . 'admin/js/inc/colpick/js/colpick.js', array('jquery'), '2.0.2', true);
		wp_enqueue_script('tooltipster', QB_PLUGIN_URL . 'admin/js/inc/tooltipster/jquery.tooltipster.min.js', array('jquery'), '0.0.0', true);

	}
}

new qb_bars();

?>
