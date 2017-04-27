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
	function wp_remote_delete($url, $args) {
				$defaults = array('method' => 'DELETE');
				$r = wp_parse_args( $args, $defaults );
				return wp_remote_request($url, $r);
			}
	function bulk_add_all_sites(){
		// loop around all sites and add to Cloudflare
		$blog_list = get_blog_list( 0, 'all' );
		foreach ($blog_list AS $blog) {
			cfdns_transaction_logging('Attempting Blog'.$blog['domain'],'Wordpress' );
			$this->add_dns_after_blog_creation($blog['domain']);
		}
	}
	function add_dns_after_blog_creation($domain){

		$extra_post_variables = '{"type":"CNAME","name":"'.$domain.'","content":"'.$this->cfdns_options['ip'].'","proxied":true}';

		if( !$this->allow_connect()){
				return;
		}
		$args = array( 'headers' => $this->get_auth_headers(), 'body' => $extra_post_variables);

		$results = wp_remote_post($this->cfdns_endpoint.'zones/'.$this->cfdns_options['account'].'/dns_records', $args);

		$this->print_results($results);
	}

function get_domain_identifier($domain){
	if( !$this->allow_connect()){
				return;
			}
		$args = array( 'headers' => $this->get_auth_headers(), 'body' => $extra_post_variables);

		$results = wp_remote_get($this->cfdns_endpoint.'zones/'.$this->cfdns_options['account'].'/dns_records?type=CNAME&name='.$domain, $args);

		$this->print_results($results);

		if($results['result']){
			return $results['result'][0]['id'];
		}
		return null;
}
	function get_auth_headers(){
		return array(
			'content-type'=> 'application/json',
			'X-Auth-Email'=>$this->cfdns_options['email'],
			'X-Auth-Key'=>$this->cfdns_options['token']
			);
	}
	function allow_connect(){
		if( $this->cfdns_options['token'] == '' || $this->cfdns_options['email'] == '' || $this->cfdns_options['account'] == ''){
				cfdns_transaction_logging('Call failed due to missing config options: email=' . $this->cfdns_options['email'] . ' & token=' . substr($this->cfdns_options['token'], 0, 10) . '[...]' . ' & domain=' . ( isset($this->cfdns_options['account']) ? $this->cfdns_options['account'] : '')  );
				return false;
		}
		return true;
	}
	function delete_dns_after_blog_deletion($domain){
		cfdns_transaction_logging('Attempting To delete DNS For'.$domain,'Wordpress' );
		// get the DNS identifier for the domain
		$domainId = $this->get_domain_identifier($domain);
		cfdns_transaction_logging('Attempting To delete DNS For DNSID'.$domainId,'Wordpress' );

		if($domainId){
			if( !$this->allow_connect()){
				return;
			}

			$args = array( 'headers' => $this->get_auth_headers());
			$results = $this->wp_remote_delete($this->cfdns_endpoint.'zones/'.$this->cfdns_options['account'].'/'.'dns_records/'.$domainId, $args);
			$this->print_results($results);

		}

	}

	function print_results($results){
		if( isset($this->cfdns_options['console_details']) && !$this->cfdns_suppress_debug ){
			print_r($results);
		}
		if( is_wp_error( $results ) ){
			cfdns_transaction_logging(print_r($results->get_error_message(), true), 'Wordpress Error');
		}
	}

}
