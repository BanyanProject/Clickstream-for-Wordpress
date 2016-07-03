<?php
/**
 * Plugin Name: Clickstream for Wordpress
 * Plugin URI: http://openorganize.com
 * Description: Creates a table of clickstream data that site administrator can use to create custom web analytics.
 * Version: 1.0
 * Author: Tom Adkins
 * Author URI: http://openorganize.com
 * License: GPL3
 */
 
function bp_clickstream_tracking($query)
{	
	global $wpdb;	
	
	$table = $wpdb->prefix . 'clickstream';
		
	$data = array();	
	
	if (isset($_SERVER['REMOTE_ADDR']))		
		$data['ip_address'] = $_SERVER['REMOTE_ADDR'];
		
	if (isset($_SERVER['HTTP_USER_AGENT']))
		$data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

	$data['click_timestamp'] = time();
				
	// TODO: add member id, nationbuilder fields
	
	if (wp_get_session_token())
		$data['session_token'] = wp_get_session_token();
	
	if (is_user_logged_in()) {
		$data['user_id'] = get_current_user_id();		
	}

	// google analytics fields
	if (isset($_COOKIE['__utma']))
		$data['utma'] = $_COOKIE['__utma'];
		
	if (isset($_COOKIE['__utmb']))
		$data['utmb'] = $_COOKIE['__utmb'];
		
	if (isset($_COOKIE['__utmz']))
		$data['utmz'] = $_COOKIE['__utmz'];
		
	if (isset($_COOKIE['___utmv']))
		$data['utmv'] = $_COOKIE['___utmv'];
		
	if (isset($_COOKIE['___utmx']))
		$data['utmx'] = $_COOKIE['___utmx'];	
	
	$data['request'] = $query->request;
	$data['matched_rule'] = $query->matched_rule;
	$data['matched_query'] = $query->matched_query;
		
	$res = $wpdb->insert($table,$data);
}

add_action( 'parse_request','bp_clickstream_tracking');

register_activation_hook( __FILE__, 'clickstream_create_db' );

function clickstream_create_db() {
	
	global $wpdb;
	$version = get_option( 'clickstream_version', '1.0' );	
	
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix . 'clickstream';

	$sql = "CREATE TABLE $table_name (
 		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  		ip_address varchar(15) DEFAULT NULL,
  		user_agent varchar(255) DEFAULT NULL,
 		click_timestamp bigint(20) unsigned NOT NULL,
  		session_token char(64) DEFAULT NULL,
  		user_id bigint(20) unsigned DEFAULT NULL,
		utma varchar(255) DEFAULT NULL,
		utmb varchar(255) DEFAULT NULL,
		utmz varchar(255) DEFAULT NULL,
		utmv varchar(255) DEFAULT NULL,
		utmx varchar(255) DEFAULT NULL,
		request varchar(255) NOT NULL,
		matched_rule varchar(255),
		matched_query varchar(255),
  		PRIMARY KEY  (id),
  		KEY ip_address (ip_address),
 		KEY click_timestamp (click_timestamp),
   		KEY session_token (session_token),
 	 	KEY user_id (user_id),
 		KEY utma (utma),
  		KEY utmb (utmb),
  		KEY utmz (utmz),
  		KEY utmv (utmv),
 		KEY utmx (utmx)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

 