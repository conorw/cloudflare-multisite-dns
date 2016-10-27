(function($){

	window.cfdns = {};
	var cfdns = window.cfdns;

	cfdns.initialize = function() {
		cfdns.setElements();
		cfdns.allSites();
		cfdns.purgeUrl();
		jQuery(document).ajaxStart(function() {
			jQuery('#spinner').show();
		})
		jQuery(document).ajaxStop(function() {
			jQuery('#spinner').hide();
		})

	};

	cfdns.setElements = function() {
		cfdns.elems = {};
		cfdns.elems.form = {};
		cfdns.elems.form.form = jQuery('#cfdns-form');
		cfdns.elems.form.username = cfdns.elems.form.form.find('#cfdns-email');
		cfdns.elems.form.account = cfdns.elems.form.form.find('#cfdns-account');
		cfdns.elems.form.token = cfdns.elems.form.form.find('#cfdns-token');
		cfdns.elems.form.url = cfdns.elems.form.form.find('#cfdns-url');
		cfdns.elems.all_sites_btn = jQuery('#cfdns-all-sites');
		cfdns.elems.purge_url_btn = jQuery('#cfdns-purge-url');
		cfdns.elems.logging_container = jQuery('#cfdns_table_logging');

		cfdns.properties = {};
	};

	cfdns.handleJsonResponse = function(response, status) {
		if( status === undefined ){ status = 'success'; }
		if( status == 'success' ){
			alert('CloudFlare API Connect: Success\n\nSee log for details');
		}
		else{
			alert('CloudFlare API Connect: Error\n\n' + response);
		}
		cfdns.refreshLog(0);
	}

	cfdns.allSites = function() {
		cfdns.elems.all_sites_btn.bind('click', function(e) {
			e.preventDefault();
			if( confirm('It may take up to a while for this function to execute\nso this function should be used sparingly\n\nAre you sure you want to continue?') ) {
				jQuery.ajax({
					'type'  : 'post',
					'url'		: ajaxurl,
					'data'	: {
									'action'	: 'cfdns_all_sites'
								  },
					'success'	: function(response) { cfdns.handleJsonResponse(response, 'success'); },
					'error'	: function(response) { cfdns.handleJsonResponse(response, 'error'); }
				});
			}
		});
	}

	cfdns.purgeUrl = function() {
		cfdns.elems.purge_url_btn.bind('click', function(e) {
			e.preventDefault();
			jQuery.ajax({
				'type'  : 'post',
				'url'		: ajaxurl,
				'data'	: {
								'action'	: 'cfdns_purge_url',
								'url'			: cfdns.elems.form.url.val()
							  },
				'success'	: function(response) { cfdns.handleJsonResponse(response, 'success'); },
				'error'	: function(response) { cfdns.handleJsonResponse(response, 'error'); }
			})
		});
	}

	cfdns.cfdns_transaction_logging = function(message, status) {
		jQuery.ajax({
			'type'  : 'post',
			'url'		: ajaxurl,
			'data'	: {
							'action'	: 'cfdns_transaction_logging',
							'message'	: message,
							'status'  : status
						  },
			'success'	: function(response) { cfdns.refreshLog(0); },
			'error'		: function(response) { console.log(response); }
		});
	};

	cfdns.refreshLog = function(page) {
		jQuery.ajax({
			'type'  : 'post',
			'url'		: ajaxurl,
			'data'	: {
							'action'	: 'cfdns_get_table_logging',
							'd_page' : page
						  },
			'success'	: function(response) { cfdns.elems.logging_container.html(response); },
			'error'	: function(response) { console.log(response); }
		})
	}


	jQuery(document).ready(function() {
		cfdns.initialize();
		cfdns.refreshLog(0);
		console.log('cfdns loaded');
	});


})(jQuery);