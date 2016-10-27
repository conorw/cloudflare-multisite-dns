<?php
/*

Description:  API Integration with CloudFlare to add a DNS entry when a new blog is created
Author:       Conor Woods @ productiveprogrammer.io
Author URI:   https://productiveprogrammer.io
Contributors: conorw

*/
class CFDNS_API {

	var $cfdns_endpoint 		= "https://api.cloudflare.com/client/v4/";
	var $cfdns_options 		    = array();
	var $cfdns_suppress_debug 	= true;

	function __construct() {
		$this->cfdns_options = get_option('cfdns_options');
	}

	function add_dns_after_blog_creation($domain){

		$extra_post_variables = '{"type":"A","name":"'.$domain.'","content":"'.$this->cfdns_options['ip'].'","proxied":true}';

		if( $this->cfdns_options['token'] == '' || $this->cfdns_options['email'] == '' || $this->cfdns_options['account'] == ''){
			cfdns_transaction_logging('Call failed due to missing config options: email=' . $this->cfdns_options['email'] . ' & token=' . substr($this->cfdns_options['token'], 0, 10) . '[...]' . ' & domain=' . ( isset($this->cfdns_options['account']) ? $this->cfdns_options['account'] : '')  );
			return;
		}
		$headers = array(
		'content-type'=> 'application/json',
	    'X-Auth-Email'=>$this->cfdns_options['email'],
	    'X-Auth-Key'=>$this->cfdns_options['token']
	    );
		$args = array( 'headers' => $headers, 'body' => $extra_post_variables);

		$results = wp_remote_post($this->cfdns_endpoint.'zones/'.$this->cfdns_options['account'].'/dns_records', $args);

		if( isset($this->cfdns_options['console_details']) && !$this->cfdns_suppress_debug ){
			print_r($results);
		}
		if( is_wp_error( $results ) ){
			cfdns_transaction_logging(print_r($results->get_error_message(), true), 'Wordpress Error');
		}
	}

}
