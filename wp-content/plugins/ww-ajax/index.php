<?php
/*
Plugin Name: WW Ajax
Description: Add an Ajax call
Author: Wai Man Wong
URL: https://www.waimanwong.com
*/


add_action("wp_ajax_ww_user_vote", "ww_user_vote");
add_action("wp_ajax_nopriv_ww_user_vote", "ww_must_login");
add_action( 'init', 'ww_script_enqueuer' );


function ww_user_vote() {

   if ( !wp_verify_nonce( $_REQUEST['nonce'], "ww_user_vote_nonce")) {
      exit("No naughty business please");
   }

   $vote_count = get_post_meta($_REQUEST["post_id"], "votes", true);
   $vote_count = ($vote_count == ’) ? 0 : $vote_count;
   $new_vote_count = $vote_count + 1;
   //This function creates the post’s meta data if it doesn’t yet exist, so we can use it to create, not just update.
   $vote = update_post_meta($_REQUEST["post_id"], "votes", $new_vote_count);

   if($vote === false) {
      $result['type'] = "error";
      $result['vote_count'] = $vote_count;
   }
   else {
      $result['type'] = "success";
      $result['vote_count'] = $new_vote_count;
   }

   if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
      $result = json_encode($result);
      echo $result;
   }
   else {
      header("Location: ".$_SERVER["HTTP_REFERER"]);
   }

   die();

}

function ww_must_login() {
   echo "You must log in to vote";
   die();
}

function ww_script_enqueuer() {
   wp_register_script( "ww_voter_script", WP_PLUGIN_URL.'/ww-ajax/ww_voter_script.js', array('jquery') );
   wp_localize_script( 'ww_voter_script', 'wwAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));

   wp_enqueue_script( 'jquery' );
   wp_enqueue_script( 'ww_voter_script' );
}
