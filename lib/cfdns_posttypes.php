<?php

function cfdns_custom_post_types(){
	register_post_type( 'cfdns_log_entries',
	    array(
      	'labels' => array(
          'name' => __( 'CCPurge Logging' ),
          'singular_name' => __( 'Log Entry' ),
          'add_new' => __( 'Add New Log Entry' ),
          'add_new_item' => __( 'Add New Log Entry' ),
          'edit_item' => __( 'Edit Log Entry' ),
          'new_item' => __( 'Add New Log Entry' ),
          'view_item' => __( 'View Log Entry' ),
          'search_items' => __( 'Search Log Entries' ),
          'not_found' => __( 'No log entries found' ),
          'not_found_in_trash' => __( 'No log entries found in trash' )
      ),
      'public' => false,
      'supports' => array(),
      'capability_type' => 'post',
      'hierarchical' => false,
      'rewrite' => array("slug" => "cfdns_log"),
      'show_in_menu' => false,
	    )
	);
};
add_action( 'init', 'cfdns_custom_post_types' );
