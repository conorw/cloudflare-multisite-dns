<?php

$cfdns = new CFDNS_API;

$cfdns_options_post = isset($_POST['cfdns_options']) ? $_POST['cfdns_options'] : false;

if($cfdns_options_post){
	update_option('cfdns_options', $cfdns_options_post);
	cfdns_transaction_logging('Updated CloudFlare Purge Settings');
	cfdns_transaction_logging('email=' . $cfdns_options_post['email'] . ' & token=' . substr($cfdns_options_post['token'], 0, 10) . '[...]' . ' & domain=' . ( isset($cfdns_options_post['account']) ? $cfdns_options_post['account'] : '') . ' & auto_purge=' . ( isset($cfdns_options_post['auto_purge']) ? $cfdns_options_post['auto_purge'] : '') );
}
$cfdns_options 					= get_option('cfdns_options');
$cfdns_email 						= isset($cfdns_options['email']) ? $cfdns_options['email'] : '';
$cfdns_token 						= isset($cfdns_options['token']) ? $cfdns_options['token'] : '';
$cfdns_account 					= $cfdns_options['account'] != "" ? $cfdns_options['account'] : $cfdns->get_wordpress_domain();;
$cfdns_console_details 			= isset($cfdns_options['console_details']) ? $cfdns_options['console_details'] : "0";
$cfdns_console_debugger 			= isset($cfdns_options['console_debugger']) ? $cfdns_options['console_debugger'] : "0";
$cfdns_console_calls 				= isset($cfdns_options['console_calls']) ? $cfdns_options['console_calls'] : "0";
$cfdns_logging_disabled 			= isset($cfdns_options['cfdns_logging_disabled']) ? $cfdns_options['cfdns_logging_disabled'] : "0";
$cfdns_auto_purge 				= isset($cfdns_options['auto_purge']) ? $cfdns_options['auto_purge'] : "0";
$show_debugging 			    	= isset($cfdns_options['show_debugging']) ? $cfdns_options['show_debugging'] : "0";
$cfdns_zone 						= isset($cfdns_options['cfdns_zone']) ? $cfdns_options['cfdns_zone'] : '';
$cfdns_ip 						= isset($cfdns_options['ip']) ? $cfdns_options['ip'] : '';

?>

<script>

function show_hide_debug(){
	var show;
	if( jQuery('input[name="cfdns_options[show_debugging]"]:checked').val() == '1' ){ show = true; }
	else{ show = false; }
	if( show ){ jQuery('.debugging-block').show(); }
	else{ jQuery('.debugging-block').hide(); }
}

jQuery(document).ready(function($){
	show_hide_debug();
	jQuery('input[name="cfdns_options[show_debugging]"]').change(function() {
		show_hide_debug();
	});

});

</script>

<div class="wrap">
	<div class="icon32" id="icon-options-general"><br></div><h2>CloudFlare&reg; Multisite DNS</h2>

	<p style="text-align: left;">
		CloudFlare Multisite DNS adds a new DNS record when a site is created<br />
	</p>

	<div id="cfdns-options-form">

	<?php if($cfdns_token == ''): ?>
		<div class="updated" id="message"><p><strong>Alert!</strong> You must get an Authentication Token from CloudFlare to start<br />If you don't already have a CloudFlare Cache account, you can <a target="_blank" href="https://www.cloudflare.com/sign-up">sign up for one here</a></p></div>
	<?php elseif($cfdns_account == ''): ?>
		<div class="updated" id="message"><p><strong>Alert!</strong> You must identify which CloudFlare Zone to target</p></div>
	<?php endif; ?>

	<form action="" id="cfdns-form" method="post">
		<table class="cfdns-table">
			<tbody>

				<tr>
					<th><label for="category_base">CloudFlare Email Address</label></th>
					<td class="col1"></td>
					<td class="col2">
						<input type="text" class="regular-text code" value="<?php echo $cfdns_email; ?>" id="cfdns-email" name="cfdns_options[email]">
					</td>
				</tr>
				<tr>
					<th><label for="tag_base">CloudFlare API Token</label></th>
					<td class="col1"></td>
					<td class="col2">
						<input type="text" class="regular-text code" value="<?php echo $cfdns_token; ?>" id="cfdns-token" name="cfdns_options[token]">
					</td>
				</tr>
				<?php if($cfdns_token): ?>
				<tr>
					<th><label for="tag_base">CloudFlare Zone</label></th>
					<td class="col1"></td>
					<td class="col2">
						<input type="text" class="regular-text code" value="<?php echo $cfdns_account; ?>" id="cfdns-account" name="cfdns_options[account]">
					</td>
				</tr>
				<tr>
					<th><label for="category_base">Your IP Address</label></th>
					<td class="col1"></td>
					<td class="col2">
						<input type="text" class="regular-text code" value="<?php echo $cfdns_ip; ?>" name="cfdns_options[ip]" id="cfdns-ip">
					</td>
				</tr>
				<tr>
					<th><label for="category_base">Debugging</label></th>
					<td class="col1"></td>
					<td class="col2">
						<input type="radio" name="cfdns_options[show_debugging]" value="1" <?php checked( $show_debugging, '1' ); ?>/> Show Debugging Sections
						<span style="width:40px;height:10px;display:inline-block"></span>
						<input type="radio" name="cfdns_options[show_debugging]" value="0" <?php checked( $show_debugging, '0' ); ?>/> Hide Debugging Sections
					</td>
				</tr>
				<tr class="debugging-block">
					<th><label for="category_base">Debugging Options</label></th>
					<td class="col1"></td>
					<td class="col2">
						<input type=checkbox name="cfdns_options[cfdns_logging_disabled]"  value="1" <?php checked( "1", $cfdns_logging_disabled); ?>> Turn Off Logging<br />
						<input type=checkbox name="cfdns_options[console_details]"  value="1" <?php checked( "1", $cfdns_console_details); ?>> Details to console (debug)<br />
						<!-- input type=checkbox name="cfdns_options[console_debugger]"  value="1" <?php checked( "1", $cfdns_console_debugger); ?>> errors to console (debug)<br / -->
						<input type=checkbox name="cfdns_options[console_calls]"  value="1" <?php checked( "1", $cfdns_console_calls); ?>> API calls to console (debug)<br />
					</td>
				</tr>
				<?php endif; ?>

				<tr>
					<th>&nbsp;</th>
					<td class="col1"></td>
					<td class="col2">
						<input type="submit" value="Update / Save" class="button-secondary"/>
					</td>
				</tr>

				<tr class="debugging-block">
					<th><hr /></th>
					<td colspan="2"><hr /></td>
				</tr>

			</tbody>
		</table>
	</form>

	<div id="spinner"></div>

	<div id="cfdns_table_logging_container" class="debugging-block">
		<div id="cfdns_table_logging"></div>
	</div>

	</div><!-- cfdns-form-wrapper -->

	<div style="clear:both;display:block;padding:40px 20px 0px;width:200px"><a href="/wp-admin/edit.php?post_type=cfdns_log_entries">Manage Cache Purge Log Entries</a></div>

</div>