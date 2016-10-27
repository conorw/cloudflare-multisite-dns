<?php

/*
Plugin Name:  CloudFlare(R) Multisite DNS
Description:  Add DNS Entry for new multisite domains
Version:      1.0
Author:       Conor Woods @ productiveprogrammer.io
Author URI:   https://productiveprogrammer.io
Contributors: @productiveprog

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
Neither the name of Alex Moss or pleer nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

define('CFDNS_VERSION', '1.2');

define('CFDNS_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('CFDNS_PLUGIN_PATH', plugin_dir_path(__FILE__) );
define('CFDNS_PLUGIN_BASENAME', plugin_basename(__FILE__));

require_once( CFDNS_PLUGIN_PATH . '/lib/cfdns.class.php');
require_once( CFDNS_PLUGIN_PATH . '/lib/cfdns_posttypes.php');


/*
	Admin styles & scripts
*/

function cfdns_admin_scripts_styles(){
	wp_register_script( 'cfdns-scripts', CFDNS_PLUGIN_URL . 'lib/cfdns.js' ) ;
	wp_register_style( 'cfdns-style', CFDNS_PLUGIN_URL . 'lib/cfdns.css' );

	wp_enqueue_script( 'cfdns-scripts' );
	wp_enqueue_style( 'cfdns-style' );
}
add_action('admin_init', 'cfdns_admin_scripts_styles');


/*
	Menu Page
*/

function cfdns_add_menu_page(){
	function cfdns_menu_page(){
		$options_page_url = CFDNS_PLUGIN_PATH . '/lib/cfdns_options.php';
		if(file_exists($options_page_url)){
			include_once($options_page_url);
		}
	};
	add_submenu_page( 'options-general.php', 'Cloudflare Multisite DNS', 'Cloudflare Multisite DNS', 'switch_themes', 'cfdns', 'cfdns_menu_page' );
};
add_action( 'admin_menu', 'cfdns_add_menu_page' );

function cfdns_plugin_settings_link($links) {
  $settings_link = '<a href="options-general.php?page=cfdns">Settings</a>';
  array_unshift($links, $settings_link);
  return $links;
}
add_filter("plugin_action_links_" . CFDNS_PLUGIN_BASENAME, 'cfdns_plugin_settings_link' );

/*
	Transaction Logging
*/

function cfdns_transaction_logging($message='empty', $status='success')
{
	global $wpdb;
	$cfdns = new CFDNS_API;

	if( isset($_REQUEST['message']) ){ $message = $_REQUEST['message']; }
	if( isset($_REQUEST['status']) ){ $status = $_REQUEST['status']; }
	if($status == 'print_debug'){
		print $message;
	}
	elseif( @!$cfdns->cfdns_options['cfdns_logging_disabled'] ){
		$total = wp_count_posts( 'cfdns_log_entries' );
		$log_entry = array(
		  'post_title' => (strtoupper($status) . ' : ' . strtolower(substr($message, 0, 150))),
		  'post_content' => $message,
		  'post_status' => 'publish',
		  'post_name' => ('cfdns-log-' . ($total->publish + 1)),
		  'post_type' => 'cfdns_log_entries',
		);
		wp_insert_post( $log_entry );
	}
}
add_action( 'wp_ajax_cfdns_transaction_logging', 'cfdns_transaction_logging' );

function cfdns_get_table_logging($verify=false){
	$limit = "30";
	$d_page = isset($_REQUEST['d_page']) ? $_REQUEST['d_page'] : 0;

	$args = array( 'post_type' => 'cfdns_log_entries', 'orderby' => 'ID', 'order' => 'DESC', 'paged' => $d_page, 'posts_per_page' => $limit );
	$log_entries = new WP_Query( $args );

	if( $verify && !$log_entries->have_posts() ){
		return false;
	}

	print "<h3>CloudFlare Multisite DNS Logging</h3>";
	print "<table>";
	print "<tr><th>ID</th><th>Time</th><th>Message</th></tr>";
	while ( $log_entries->have_posts() ) {
		global $post;
		$log_entries->the_post();
		print "<tr class='{$post->post_title}'><td>" . str_replace ( 'cfdns-log-' , '' , $post->post_name) . "</td><td>" . $post->post_date . "</td><td>" . $post->post_content . "</td></tr>";
	}
	print "</table>";
	print "<input id='cfdns-prev' onclick='cfdns.refreshLog(".($d_page - 1).");' type=button value='Previous {$limit}'/>";
	print "<input id='cfdns-next' onclick='cfdns.refreshLog(".($d_page + 1).");' type=button value='Next {$limit}'/>";
	die();
}
add_action( 'wp_ajax_cfdns_get_table_logging', 'cfdns_get_table_logging' );



/**
 * Example of wpmu_new_blog usage
 *
 * @param int    $blog_id Blog ID.
 * @param int    $user_id User ID.
 * @param string $domain  Site domain.
 * @param string $path    Site path.
 * @param int    $site_id Site ID. Only relevant on multi-network installs.
 * @param array  $meta    Meta data. Used to set initial site options.
 */
function wporg_wpmu_new_blog_example( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
	// TODO: support domain mapping
	$cfdns = new CFDNS_API;
	$cfdns->add_dns_after_blog_creation( $domain );
}
add_action( 'wpmu_new_blog', 'wporg_wpmu_new_blog_example', 10, 6 );




