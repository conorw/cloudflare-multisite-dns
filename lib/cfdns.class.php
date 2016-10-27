<?php
/*

Description:  API Integration with CloudFlare to purge your cache
Author:       Bryan Shanaver @ fiftyandfifty.org
Author URI:   https://www.fiftyandfifty.org/
Contributors: shanaver

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
Neither the name of Alex Moss or pleer nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

*/

class CFDNS_API {

	var $cfdns_endpoint 		= "https://api.cloudflare.com/client/v4/";
	var $cfdns_options 		    = array();
	var $cfdns_suppress_debug 	= false;

	function __construct() {
		$this->cfdns_options = get_option('cfdns_options');
		if( empty($this->cfdns_options['account']) ){
			$this->cfdns_options['account'] = $this->get_wordpress_domain();
			update_option('cfdns_options', $this->cfdns_options);
		}
	}
	function return_json_success($data='') {
		print json_encode( array("success" => 'true', "data" => $data) );
	}
	function return_json_error($error='') {
		print json_encode( array("success" => 'false', 'error' => array("message" => $error)) );
	}

	function add_domain($extra_post_variables = null){

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
		// cfdns_transaction_logging('POST VARS:'.$extra_post_variables,'error');
		$results = wp_remote_post($this->cfdns_endpoint.'zones/'.$this->cfdns_options['account'].'/dns_records', $args);

		if( isset($this->cfdns_options['console_details']) && !$this->cfdns_suppress_debug ){
			print_r($results);
		}

		if( is_wp_error( $results ) ){
			cfdns_transaction_logging(print_r($results->get_error_message(), true), 'Wordpress Error');
		}

		if($results['response']['code'] != '200'){
			cfdns_transaction_logging(print_r($results, true), 'error');
		}
		$entry = json_decode($results['body'])['result'];
		$entry['proxied'] = true;
		$args = array( 'headers' => $headers, 'body' => $entry);
		$updated = wp_remote_post($this->cfdns_endpoint.'zones/'.$this->cfdns_options['account'].'/'.'dns_records/'.$entry['id'], $args);
		return json_decode($updated['body']);
	}

	function add_dns_after_blog_creation($domain){
		// $results = $this->add_domain(array('type'=>'A', 'name' => $domain,'content' => $this->cfdns_options['ip']));
		$results = $this->add_domain('{"type":"A","name":"'.$domain.'","content":"'.$this->cfdns_options['ip'].'","proxied":true}');
	}

	function get_wordpress_domain(){
		$domain = preg_replace('/http:\/\//', '', get_home_url() );
		$domain = preg_replace('/www./', '', $domain  );
		return $domain;
	}
}
