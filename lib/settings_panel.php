<?php 

if (!defined('Identity +')){
	error_log("File \"".__FILE__."\" was called directly. ");
	exit; // Exit if accessed directly
}

use identity_plus\api\communication\Intent_Type;
use identity_plus\api\Identity_Plus_API;


add_action( 'admin_enqueue_scripts', 'identity_plus_admin_styles' );
add_action( 'admin_menu', 'identity_plus_add_admin_menu' );
add_action( 'admin_init', 'identity_plus_settings_init' );



function identity_plus_add_admin_menu(  ) {
		add_options_page( 'IdentityPlus Settings', 'Identity +', 'manage_options', 'identity_plus_network_of_trust', 'identity_plus_options_page' );
}



function identity_plus_settings_init(  ) {
        if(!function_exists("curl_init")) add_settings_error('identity_plus_settings', 'identity-plus-curl-error', "Curl extension is not installed on the server! Identity + needs php-curl extension to work. <br>(for Ubuntu type: sudo apt-get install php-curl)", "error");
        $problems = idp_problems(get_option( 'identity_plus_settings' ));
		if($problems) add_settings_error('identity_plus_settings', 'identity-plus-api-certificate-error', $problems, "error");
}



function identity_plus_cert_file_render( ) {
		?><input type="file" style="margin-top:5px;" name="identity-plus-api-cert-file" /><?php
}


function identity_plus_cert_password_render(  ) { 
		$options = get_option( 'identity_plus_settings' ); ?>
		<input type='text' name='identity_plus_settings[cert-password]' style="width:350px; margin-bottom:10px; margin-top:5px;" placeholder="Type/Paste Certificate Password" value='<?php echo isset($options['cert-password']) ? $options['cert-password'] : ""; ?>'><?php
}



function identity_plus_comments_render(  ) {
		$options = get_option( 'identity_plus_settings' );?>
		<input type='checkbox' id='identity_plus_settings[comments]' name='identity_plus_settings[comments]' <?php isset($options['comments']) ? checked( $options['comments'], 1 ) : ""; ?> value='1'><label for='identity_plus_settings[comments]'>Enforce Identity + SSL Client Certificate</label>
		<p class="identity-plus-hint" style="max-width:640px; font-size:90%; color:rgba(0, 0, 0, 0.6);">When Identity + SSL Client Certificate is enforced, comments will be blocked to devices with no certificates.
		Devices that have certificate and submit spam, will be blocked upon the first report of the smap preventing them from repeating the action.
		This makes the life of spammers extremely difficul.</p><?php
}



function identity_plus_enforce_render(  ) {
		$options = get_option( 'identity_plus_settings' );?>
		<input type='checkbox' id='identity_plus_settings[enforce]' name='identity_plus_settings[enforce]' <?php isset($options['enforce']) ? checked( $options['enforce'], 1 ) : ""; ?> value='1'><label for='identity_plus_settings[enforce]'>Enforce Device Identity</label>
		<p class="identity-plus-hint" style="max-width:640px; font-size:90%; color:rgba(0, 0, 0, 0.6); margin-bottom:10px; ">When Identity + certificate is enforced, resources starting with any of the enumerated filters will only 
		be accessible from devices (desktop / laptop /mobile ) bearing a valid Identity + SSL Client Certificate. </p><?php
}



function identity_plus_lock_down_render(  ) {
		$options = get_option( 'identity_plus_settings' );?>
		<input type='checkbox' id='identity_plus_settings[lock-down]' name='identity_plus_settings[lock-down]' <?php isset($options['lock-down']) ? checked( $options['lock-down'], 1 ) : ""; ?> value='1'><label for='identity_plus_settings[lock-down]'>Enabled</label>
		<p class="identity-plus-hint" style="max-width:640px; font-size:90%; color:rgba(0, 0, 0, 0.6);">When lock down is enabled the filtered resources will only be accessible to Identity + connected users.</p><?php
}



function identity_plus_page_filter_render(  ) { 
		$options = get_option( 'identity_plus_settings' );?>
		<textarea cols='40' rows='5' name='identity_plus_settings[page-filter]'><?php echo isset($options['page-filter']) && strlen($options['page-filter']) > 0 ? $options['page-filter'] : "/wp-admin\n/wp-login.php\n/?rest_route=/\n/wp-json/"; ?></textarea>
		<?php
}



function identity_plus_not_section_callback(  ) {
		$options = get_option( 'identity_plus_settings' );
		?><p class="identity-plus-separator" style="padding-top:5px;"></p><p class="identity-plus-hint">Authors of posts and approved comments that have Identity + profiles will be rewarded with tokens of trust.
		Similarly, when comments are marked as spam, the certificate of the originating dvice is reported, preventing it from repeating the action anywhere else</p>
		<?php 
}


function identity_plus_settings_section_callback(  ) { 
		?><p class="identity-plus-separator" style="padding-top:5px;"></p><p class="identity-plus-hint">You can restrict access to critical sections of your site to authorized devices only</p><?php 
}



function identity_plus_admin_styles(  ) {
		?>
		<style>
				.identity-plus-main-fm{ float:left; overflow:hidden; clear:left;}
				.identity-plus-main-fm-header {margin:0; background:url('<?php echo plugins_url( 'img/idp.svg', __FILE__ ) ?>') no-repeat top left; background-size:64px;}
				.identity-plus-main-fm-header h1{padding-left:80px; padding-top:10px; margin-bottom:0; font-size:36px;font-weight:normal; }
				.identity-plus-main-fm-header h5{padding-left:80px; font-size:20px; font-weight:300; padding-bottom:5px; padding-top:0; margin:15px 0px 0px 0px;}

				.identity-plus-main-fm p{margin:0;}
				.identity-plus-main-fm th{padding-bottom:15px; padding-top:15px; color:#136a92;}
				.identity-plus-main-fm td{padding-bottom:10px; padding-top:10px; }
				.identity-plus-main-fm h2, .identity-plus-main-fm h3{border:1px solid rgba(0,0,0,0.1); border-bottom:0; background:rgba(0,0,0,0.05); float:left; clear:left; padding:8px 20px; margin-bottom:0px; color:#404040; font-weight:normal; border-top-left-radius:5px; border-top-right-radius:5px; margin-left:10px; margin-top:50px;}
				.identity-plus-main-fm h4{border-bottom:1px solid #E0E0E0; color:#707070; padding-bottom:3px; padding-top:15px; margin-bottom:5px; font-weight:normal; font-size:16px;padding-top:0; margin-top:0; }
				.identity-plus-main-fm .cert {max-width:600px; border-radius:3px; float:left; clear:both;}
				.identity-plus-main-fm .cert p span{font-weight:bold;}
				.identity-plus-main-fm .cert p{margin:0px; float:left; clear:left;}
				.identity-plus-main-fm .cert {padding:10px; background:rgba(255, 255, 255, 0.6); border:1px solid rgba(0, 0, 0, 0.3);}
				.identity-plus-separator{border-top:1px solid rgba(0,0,0,0.1); margin-top:0px; float:left; width:100%; clear:both; height:5px; margin-bottom:0px;}
				.identity-plus-hint{float:left; clear:both; max-width:600px; color:#606060; font-size:14px; margin-top:0px; margin-bottom:10px;}
                .identity-plus-brand span{color:#4292D3;}
                .identity-plus-main-fm input, .identity-plus-main-fm textarea{ float:left; clear:left;}
                .identity-plus-main-fm input[type="checkbox"]{ margin-top:0; margin-right:5px;}
                .identity-plus-main-fm label{ float:left; font-weight:400;}
                .identity-plus-main-fm div{float:left; clear:left; overflow:hidden; margin-bottom:10px;}
                .identity-plus-main-fm table{max-width:600px; float:left; clear:left;}
                .identity-plus-main-fm table th img{border-radius:60px; border:3px solid #D0D0D0;}
				.identity-plus-main-fm a.toggle-off {font-size:16px; color:#202020; padding:5px 0px 5px 0px; margin-right:30px; cursor:pointer;}
				.identity-plus-main-fm a.toggle-on {font-size:16px; color:#202020;  padding:5px 0px 5px 0px; margin-right:30px; cursor:pointer; border-bottom:1px solid #606060; display:inline-block;}
				.circular_progress {transform: rotate(90deg);display: inline;}
				div.holder-more {overflow: hidden;width: 128px;height: 128px;margin-top: 1px;margin-right: 30px;text-align: center;padding-left: 0; display: inline-block; float:left; clear:left; margin-right:30px;}
				div.holder-more p.overlay {position: relative;width: 100%;line-height: 120%;top: -93px;font-weight: 400; font-size:120%;}
				div.holder-more p.overlay span {font-weight: 300;font-size: 90%;color: #606060;}
				#wpfooter{position:static;}
				.nodisp{display:none;}
				.identity-plus-main-fm input[type=checkbox], .identity-plus-main-fm input[type=radio]{margin:0px 10px 5px 0px; float;left; clear:left; box-shadow:none;}
				.identity-plus-main-fm input[type=text]{padding:5px; box-shadow:none;}
				.identity-plus-main-fm textarea{margin:0px 10px 5px 0px; float:left; clear:left; margin-bottom:20px; border-radius:0px; box-shadow:none; padding:5px 10px;}
				.identity-plus-main-fm .submit{float:left; clear:left; margin-top:0px; padding:0px; height:32px;}
				.identity-plus-main-fm .submit input[type="submit"]{text-decoration:none; background:#4292D3; color:#FFFFFF; display:inline-block; border-radius:3px; border:1px solid rgba(0,0,0,0.1); cursor:pointer; box-shadow:none; text-shadow:none; font-size:14px; padding:2px 18px; height:auto;}
				.identity-plus-main-fm a.submit{text-decoration:none; background:#4292D3; color:#FFFFFF; display:inline-block; border-radius:3px; border:1px solid rgba(0,0,0,0.1); cursor:pointer; box-shadow:none; text-shadow:none; font-size:14px; padding:6px 18px; height:auto;}
		</style>
		<?php 
}


function identity_plus_api_section_callback(  ) {
	$problems = idp_problems(get_option( 'identity_plus_settings' ));

	?>
	
	<div class="identity-plus-main-fm" >
		<h2>Service Identity</h2>
		<p class="identity-plus-separator" style="padding-top:5px;"></p>
		<p class="identity-plus-hint">Your Worpress uses PKI credentials to authenticate into Indentity Plus. This is necessary to make sure nobody impersonates your service.</p>
	</div>
	<div class="identity-plus-main-fm" >
	<table class=""><tr>

	<?php
	
	if(!$problems){
		// display dial for certificate lifetime
		// and expiry
		?>
		<td valign="top"><div class="holder-more"><?php
			$perimeter = 2*3.14*60;
			$options = get_option( 'identity_plus_settings' );
			$dash = 0;
			$days = 0;
			if(!empty($options) && isset($options['cert-data'])){
					$cs = array();
					if(openssl_pkcs12_read (base64_decode($options['cert-data']), $cs , isset($options['cert-password']) ? $options['cert-password'] : '')){
							$cert_details = openssl_x509_parse($cs['cert']);
							$now = time();
							$days = floor(abs($cert_details['validTo_time_t'] - $now) / 86400);
							$all_days = floor(abs($cert_details['validTo_time_t'] - $cert_details['validFrom_time_t']) / 86400);
							$dash = $perimeter*($days*1.0/$all_days*1.0);
					}
			}
			?><div class="holder-more">
			<svg width="124.0" height="124.0" viewBox="0 0 124.0 124.0" class="circular_progress">
				<circle cx="62.0" cy="62.0" r="60.0" fill="none" stroke="#E7E7E7" stroke-width="1.5"></circle>
				<circle cx="62.0" cy="62.0" r="60.0" fill="none" stroke="#007aD0" stroke-width="1.5" stroke-dasharray="<?php echo $perimeter; ?>" stroke-dashoffset="<?php echo ($perimeter - $dash);?>"></circle>
			</svg>
			<p class="overlay"><span><?php echo $days == 0 ? "" : "Expires"; ?><br><?php echo $days == 0 ? "N/A" : date("yy, M, d", $cert_details['validTo_time_t']) ?></span><br><?php echo $days == 0 ? "" : $days . "d"?><span></span></p>
		</div><?php
		?></div>
		</td>
	<?php } ?>

	<td valign="top"><div class="identity-plus-main-fm">
		<script>
			function toggle_renewal(mode){
				document.getElementById('renew-fm').className = mode == 0 ? 'identity-plus-hint' : 'nodisp'; 
				document.getElementById('upload-fm').className = mode == 0 ? 'nodisp' : 'identity-plus-hint';
				document.getElementById('integrated').className = mode == 0 ? 'toggle-on' : 'toggle-off'; 
				document.getElementById('manual').className = mode == 0 ? 'toggle-off' : 'toggle-on';
			}
		</script>
		<a id="integrated" class="toggle-on" onclick="toggle_renewal(0)">Automated</a>
		<a id="manual" class="toggle-off" onclick="toggle_renewal(1);">Manual</a>
	</div>

	<?php if(empty($options) || !isset($options['cert-data'])){ ?>
		<form id="renew-fm" class="identity-plus-main-fm" action="admin-post.php" method='post' enctype="multipart/form-data">
				<div>
					<p class="identity-plus-hint" style="font-size:13px; margin-bottom:5px;">Click the button below to add certify your ownership of this Wordpress instance.</p>
					<a class="submit" href="<?php echo("https://register." . Identity_Plus_API::HOME . "/?url=" . urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"));?>" target="_blank">Certify Ownership</a>
				</div>
		</form>
	<?php } else { ?>
		<form id="renew-fm" class="identity-plus-main-fm" action="admin-post.php" method='post' enctype="multipart/form-data">
				<input type="hidden" name="action" value="renew_certificate">
				<div>
					<p class="identity-plus-hint" style="font-size:13px; margin-bottom:5px;">To avoid outage, your service identity (certificate) will be renewed automatically in <?php echo floor($days - $all_days/3); ?> days.</p>
					<?php submit_button("Auto-Renew Now"); ?>
				</div>
		</form>
	<?php } ?>

	<form id="upload-fm" class="nodisp" action="admin-post.php" method='post' enctype="multipart/form-data">
			<input type="hidden" name="action" value="upload_certificate">
			<div>
				<p class="identity-plus-hint" style="font-size:13px;">Create the service in your <a href="https://my.identity.plus" target="_blank">identityp.plus dashboard</a>, issue the Service Agent Identity and upload it manually.</p>
				<?php identity_plus_cert_file_render(); ?>
				<?php identity_plus_cert_password_render(); ?>
				<?php submit_button("Upload Manually"); ?>
			</div>
	</form>
	</td></tr></table>

	<?php if(!$problems){ 
		// add the access restriction configuration section
		// and also the network of trust enrollment
		?>
		<div class="identity-plus-main-fm" >
			<h2>Access Restrictions</h2>
			<p class="identity-plus-separator" style="padding-top:5px;"></p><p class="identity-plus-hint">You can restrict access to critical sections of your site to authorized devices only. Add one resource pattern per line.</p>
		</div>
		<form id="upload-fm" class="identity-plus-main-fm" action="admin-post.php" method='post' enctype="multipart/form-data">
				<input type="hidden" name="action" value="save_access">
				<div>
					<?php identity_plus_page_filter_render(); ?>
					<?php identity_plus_enforce_render(); ?>
					<?php submit_button("Save"); ?>
				</div>
		</form>

		<div class="identity-plus-main-fm" >
			<h2>Network of Trust</h2>
			<p class="identity-plus-separator" style="padding-top:5px;"></p><p class="identity-plus-hint">Collaborate with the Identity Plus community to better identify legitimate users using anonymized hooks (no personal information is shared). This will help eliminate SPAM and fake accounts.</p>
		</div>
		<form id="upload-fm" class="identity-plus-main-fm" action="admin-post.php" method='post' enctype="multipart/form-data">
				<input type="hidden" name="action" value="not_enroll">
				<div>
					<?php submit_button(isset($options['not_enroll']) && $options['not_enroll'] == 1 ? "Disable" : "Enroll"); ?>
				</div>
		</form>
	<?php
	}
}

add_action( 'admin_post_not_enroll', 'identity_plus_admin_not_enroll');
function identity_plus_admin_not_enroll(){
	$options = get_option( 'identity_plus_settings');

	if(isset($options['not_enroll']) && $options['not_enroll'] == 1) $options['not_enroll'] = 0;
	else  $options['not_enroll'] = 1;

	update_option( 'identity_plus_settings', $options);

	wp_redirect( $_SERVER["HTTP_REFERER"], 302, 'WordPress' );
	exit;
	status_header(200);
	die("Certificate uploaded.");
}

add_action( 'admin_post_save_access', 'identity_plus_admin_save_access');
function identity_plus_admin_save_access(){
	$options = get_option( 'identity_plus_settings');

	$options['page-filter'] = $_POST["identity_plus_settings"]["page-filter"];
	$options['enforce'] = $_POST["identity_plus_settings"]["enforce"];

	update_option( 'identity_plus_settings', $options);

	wp_redirect( $_SERVER["HTTP_REFERER"], 302, 'WordPress' );
	exit;
	status_header(200);
	die("Certificate uploaded.");
}


function identity_plus_options_page(  ) { 
		?>
		<div class="identity-plus-main-fm-header">
			<h1 class="identity-plus-brand">Identity<span>plus</span></h1>
			<h5>man &amp; machine</h5>
		</div>
		
		<?php 
		
		identity_plus_api_section_callback();
}



function identity_plus_enable_extra_extensions($mime_types =array() ) {
		$mime_types['p12']  = 'application/x-pkcs12';
		$mime_types['svg']  = 'image/svg';
		return $mime_types;
}

add_action( 'admin_post_upload_certificate', 'identity_plus_admin_upload_certificate');
function identity_plus_admin_upload_certificate(){
	$options = get_option( 'identity_plus_settings');

	if(!empty($_FILES["identity-plus-api-cert-file"]["tmp_name"])){
		$options['cert-data'] = base64_encode(file_get_contents($_FILES["identity-plus-api-cert-file"]["tmp_name"]));
		$options['cert-password'] = $_POST["identity_plus_settings"]["cert-password"];
	}

	update_option( 'identity_plus_settings', $options);

	wp_redirect( $_SERVER["HTTP_REFERER"], 302, 'WordPress' );
	exit;
	status_header(200);
	die("Certificate uploaded.");
}

add_action( 'admin_post_renew_certificate', 'identity_plus_admin_renew_certificate');
function identity_plus_admin_renew_certificate(){
	idenity_plus_renew_service_agent_certificate();

	wp_redirect( $_SERVER["HTTP_REFERER"], 302, 'WordPress' );
	exit;
	status_header(200);
	die("Certificate renewed.");
}


# -------------------------- Id + Menu Page

add_action( 'admin_action_identity_plus_connect', 'identity_plus_connect');
function identity_plus_connect(){
        $user_id = get_current_user_id();
        $options = get_option( 'identity_plus_settings' );
        if($identity_plus_api == null) $identity_plus_api = identity_plus_create_api($options);

		$user_info = get_userdata($user_id);
		$intent = $identity_plus_api->create_intent(Intent_Type::bind, $user_id, $user_info->user_firstname . ' ' . $user_info->user_lastname, $user_info->user_email, '', $_SERVER['HTTP_REFERER'] . '&bind=true');
		unset($_SESSION['identity-plus-user-profile']);
		unset($_SESSION['identity-plus-anonymous-id']);
		wp_redirect(Identity_Plus_API::validation_endpoint.'/' . $intent->value);

        exit();
}

add_action( 'admin_action_identity_plus_disconnect', 'identity_plus_disconnect');
function identity_plus_disconnect(){
        $user_id = get_current_user_id();

        if(!$_REQUEST['idp-i-am-sure']){
            $error = "E: Please reinforce your desire to disconnect by checking the appropriate checkbox!";
            set_transient("identity_plus_acc_{$user_id}", $error, 45);      
        }
        else{
            $options = get_option('identity_plus_settings' );
            if($identity_plus_api == null) $identity_plus_api = identity_plus_create_api($options);
            $profile = $identity_plus_api->unbind_local_user($user_id);
			$_SESSION['identity-plus-user-profile'] = $profile;

			unset($_SESSION['identity-plus-user-profile']);
			unset($_SESSION['identity-plus-anonymous-id']);

            delete_user_meta($user_id, 'identity-plus-bound');
            $error = "I: Your wordpress account and your identity plus account have been disconnected!";
            set_transient("identity_plus_acc_{$user_id}", $error, 45);
        }

        wp_redirect( $_SERVER['HTTP_REFERER'] );
        exit();
}

add_action( 'admin_menu', 'identity_plus_add_idp_page' );

function identity_plus_add_idp_page(  ) {
        $options = get_option( 'identity_plus_settings' );
		if(!empty($options) && isset($options['cert-data'])){
            add_menu_page( 
                    'My IdentityPlus',
                    'Device Login', 
                    'exist', 
                    'identity_plus_authentication', 
                    'identity_plus_authentication_page',
                    'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+PHN2ZyAgIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLyIgICB4bWxuczpjYz0iaHR0cDovL2NyZWF0aXZlY29tbW9ucy5vcmcvbnMjIiAgIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyIgICB4bWxuczpzdmc9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiAgIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgICB4bWxuczpzb2RpcG9kaT0iaHR0cDovL3NvZGlwb2RpLnNvdXJjZWZvcmdlLm5ldC9EVEQvc29kaXBvZGktMC5kdGQiICAgeG1sbnM6aW5rc2NhcGU9Imh0dHA6Ly93d3cuaW5rc2NhcGUub3JnL25hbWVzcGFjZXMvaW5rc2NhcGUiICAgdmVyc2lvbj0iMS4xIiAgIGlkPSJMYXllcl8xIiAgIHg9IjBweCIgICB5PSIwcHgiICAgd2lkdGg9IjEyOHB4IiAgIGhlaWdodD0iMTI4cHgiICAgdmlld0JveD0iMCAwIDEyOCAxMjgiICAgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMTI4IDEyODsiICAgeG1sOnNwYWNlPSJwcmVzZXJ2ZSIgICBpbmtzY2FwZTp2ZXJzaW9uPSIwLjkxIHIxMzcyNSIgICBzb2RpcG9kaTpkb2NuYW1lPSJkYXJrLXBsdXMtc2ltcGxlLnN2ZyI+PG1ldGFkYXRhICAgICBpZD0ibWV0YWRhdGEzOSI+PHJkZjpSREY+PGNjOldvcmsgICAgICAgICByZGY6YWJvdXQ9IiI+PGRjOmZvcm1hdD5pbWFnZS9zdmcreG1sPC9kYzpmb3JtYXQ+PGRjOnR5cGUgICAgICAgICAgIHJkZjpyZXNvdXJjZT0iaHR0cDovL3B1cmwub3JnL2RjL2RjbWl0eXBlL1N0aWxsSW1hZ2UiIC8+PC9jYzpXb3JrPjwvcmRmOlJERj48L21ldGFkYXRhPjxkZWZzICAgICBpZD0iZGVmczM3IiAvPjxzb2RpcG9kaTpuYW1lZHZpZXcgICAgIHBhZ2Vjb2xvcj0iI2ZmZmZmZiIgICAgIGJvcmRlcmNvbG9yPSIjNjY2NjY2IiAgICAgYm9yZGVyb3BhY2l0eT0iMSIgICAgIG9iamVjdHRvbGVyYW5jZT0iMTAiICAgICBncmlkdG9sZXJhbmNlPSIxMCIgICAgIGd1aWRldG9sZXJhbmNlPSIxMCIgICAgIGlua3NjYXBlOnBhZ2VvcGFjaXR5PSIwIiAgICAgaW5rc2NhcGU6cGFnZXNoYWRvdz0iMiIgICAgIGlua3NjYXBlOndpbmRvdy13aWR0aD0iOTQ4IiAgICAgaW5rc2NhcGU6d2luZG93LWhlaWdodD0iNDgwIiAgICAgaWQ9Im5hbWVkdmlldzM1IiAgICAgc2hvd2dyaWQ9ImZhbHNlIiAgICAgaW5rc2NhcGU6em9vbT0iMS40ODk3NjUxIiAgICAgaW5rc2NhcGU6Y3g9IjEwNi45NzU5IiAgICAgaW5rc2NhcGU6Y3k9IjY0IiAgICAgaW5rc2NhcGU6d2luZG93LXg9IjEzMSIgICAgIGlua3NjYXBlOndpbmRvdy15PSI0NDAiICAgICBpbmtzY2FwZTp3aW5kb3ctbWF4aW1pemVkPSIwIiAgICAgaW5rc2NhcGU6Y3VycmVudC1sYXllcj0iZzMiIC8+PGcgICAgIGlkPSJnMyIgICAgIHN0eWxlPSJmaWxsOiNjY2NjY2MiPjxwYXRoICAgICAgIHN0eWxlPSJmaWxsOiNjY2NjY2MiICAgICAgIGQ9Im0gMCwwIDAsMTI4IDEyOCwwIDAsLTEyOCB6IG0gMTIuNjQwNjI1LDQwLjAxNzU3OCAxMS43MzYzMjgsMCAwLDkuNTcyMjY2IC0xMS43MzYzMjgsMCB6IG0gNDQuMDY0NDUzLC02IDExLjgwMjczNCwwIDAsNTcuMDE1NjI1IGMgLTguMDAxNzcsLTAuMzY2MTE5IC0xMi41Nzk4MTgsMC4zMjQzMTMgLTIzLjkwMDM5LDAuOTQ5MjE5IC00LjUyNDU0NSwwIC04LjI0MTM2MywtMS43OTIyOSAtMTEuMTQ4NDM4LC01LjM3Njk1MyAtMi45MDcwNzQsLTMuNjA2NTIyIC00LjM1OTM3NSwtOC4yMzkyNDYgLTQuMzU5Mzc1LC0xMy45MDAzOTEgMCwtNS42NjExNDUgMS40NTIzMDEsLTEwLjI4NDQ3OCA0LjM1OTM3NSwtMTMuODY5MTQgMi45MDcwNzUsLTMuNjA2NTIyIDYuNjIzODkzLC01LjQxMDE1NyAxMS4xNDg0MzgsLTUuNDEwMTU3IDE3LjE0MDM1NywxLjA2OTY5IDExLjA2MDU2NCw0LjM5NDExNCAxMi4wOTc2NTYsLTE5LjQwODIwMyB6IG0gMzUuNzM2MzI4LDE0LjkxNzk2OSA3LjgwNDY4NCwwIDAsMTcuMTc5Njg3IDE3LjExMzI5LDAgMCw3LjczNjMyOCAtMTcuMTEzMjksMCAwLDE3LjE4MTY0MSAtNy44MDQ2ODQsMCAwLC0xNy4xODE2NDEgLTE3LjExMzI4MSwwIDAsLTcuNzM2MzI4IDE3LjExMzI4MSwwIHogbSAtNzkuODAwNzgxLDUuMzc2OTUzIDExLjczNjMyOCwwIDAsMzYuNzIwNzAzIC0xMS43MzYzMjgsMCB6IG0gMzYuMzI2MTcyLDcuNjM4NjcyIGMgLTIuNDkxNzc4LDAgLTQuNDAzMDA4LDAuOTE3ODU5IC01LjczNjMyOCwyLjc1MzkwNiAtMS4zMTE0NjIsMS44MzYwNDcgLTEuOTY4NzUsNC41MDI3NjcgLTEuOTY4NzUsOCAwLDMuNDk3MjMzIDAuNjU3Mjg4LDYuMTYzOTUzIDEuOTY4NzUsOCAxLjMzMzMyLDEuODM2MDQ3IDMuMjQ0NTUsMi43NTM5MDYgNS43MzYzMjgsMi43NTM5MDYgMi41MTM2MzYsMCA0LjQyNjgxOSwtMC45MTc4NTkgNS43MzgyODEsLTIuNzUzOTA2IDEuMzMzMzIsLTEuODM2MDQ3IDIsLTQuNTAyNzY3IDIsLTggMCwtMy40OTcyMzMgLTAuNjY2NjgsLTYuMTYzOTUzIC0yLC04IC0xLjMxMTQ2MiwtMS44MzYwNDcgLTMuMjI0NjQ1LC0yLjc1MzkwNiAtNS43MzgyODEsLTIuNzUzOTA2IHoiICAgICAgIGlkPSJyZWN0NSIgICAgICAgaW5rc2NhcGU6Y29ubmVjdG9yLWN1cnZhdHVyZT0iMCIgICAgICAgc29kaXBvZGk6bm9kZXR5cGVzPSJjY2NjY2NjY2NjY2NjY2NzY2NjY2NjY2NjY2NjY2NjY2NjY2Njc2NzY3Njc2NzIiAvPjwvZz48ZyAgICAgaWQ9ImcyNSIgLz48ZyAgICAgaWQ9ImcyNyIgLz48ZyAgICAgaWQ9ImcyOSIgLz48ZyAgICAgaWQ9ImczMSIgLz48ZyAgICAgaWQ9ImczMyIgLz48L3N2Zz4='
            );
        }
}

function identity_plus_idp_page(  ) {
        $user_id = get_current_user_id();
        $msg = get_transient("identity_plus_acc_{$user_id}");
        if($msg){
            if(strpos($msg, 'E: ') === 0){ ?><div class="error is-dismissible"><p><?php echo substr($msg, 3); ?></p></div><?php }
            else{ ?><div class="notice notice-success is-dismissible"><p><?php echo substr($msg, 3); ?></p></div><?php }
            delete_transient("identity_plus_acc_{$user_id}");
        }

        $options = get_option( 'identity_plus_settings' );

        ?>
                <?php if(get_user_meta($user_id, 'identity-plus-bound', true)){ ?>
                    <table><tr>
	                        <th><img width="64" height="64" src="https://get.identity.plus/widgets/profile-picture"></th>
                   	    	<td><p class="identity-plus-hint">
                                Your Wordpress uses <a target="_blank" title="My Identity Plus Application" href="https://my.identity.plus"><span>identity</span></a> to protect your account and your credentials.
                                You can now enjoy secure password-less experience. Only devices owned and registered by you can access your Wordpress account.
                            </p></td>
                    </tr></table>

                    <h2>Disconnect</h2><p class="identity-plus-separator" style="padding-top:5px;"></p>
                    <?php if(isset($options['enforce']) && $options['enforce'] == 1 ){ ?>
                        <p class="identity-plus-hint" >Your <a href="<?php echo admin_url('options-general.php?page=identity_plus_network_of_trust'); ?>">identityplus settings</a> only allow admin access from certified devices. Disconnect is disabled as you would lock yourself out from admin section.</p>
                    <?php } else { ?>
                        <p class="identity-plus-hint" >By disconnecting your identityplus account from the local account, you will lose the ability to sign in via device id. Are you sure?</p>
                        <input type="hidden" name="action" value="identity_plus_disconnect">
                        <div style="margin-top:10px;"><input type="checkbox" id="idp-i-am-sure" name="idp-i-am-sure" onchange="document.getElementById('identity_plus_disconnect').style.display = document.getElementById('idp-i-am-sure').checked ? 'block' : 'none';"><label for="idp-i-am-sure">Yes, I am sure I want to disconnect.</label></div>
                        <input type="submit" id="identity_plus_disconnect" style="display:none; background:#900000; color:#FFFFFF; padding:8px 18px 6px 18px; border-radius:3px; border:1px solid rgba(0,0,0,0.1);" value="Disconnect">
                    <?php } ?>

                <?php } else if(isset($_SESSION['identity-plus-user-profile'])){ ?>
                    <table><tr>
                            <th><img width="64" height="64" src="https://get.identity.plus/widgets/profile-picture"></th>
                   	    	<td>
                                <p class="identity-plus-hint">
                                    Your Wordpress uses <a target="_blank" title="My Identity Plus Application" href="https://identity.plus"><span>identity</span></a> to protect your account and your credentials by
                                    only allowing devices owned and registered by you to access your Wordpress account.
                                </p>
                            </td>
                    </tr></table>
                    
                    <p class="identity-plus-hint" >Connect your identity<span class="identity-plus-brand">plus</span> account for secure, password-less login experience.</p>
                    <input type="hidden" name="action" value="identity_plus_connect">
                    <input type="submit" id="identity_plus_disconnect" style="background:#4292D3; color:#FFFFFF; padding:8px 18px 6px 18px; border-radius:3px; border:1px solid rgba(0,0,0,0.1); cursor:pointer; margin-top:10px;" value="Connect">
                <?php } else { ?>
                    <table><tr>
                   	    	<td>
                                <p class="identity-plus-hint">
                                    Your Wordpress uses <a target="_blank" title="My Identity Plus Application" href="https://identity.plus"><span>identity</span></a> to protect your account and your credentials by
                                    only allowing devices owned and registered by you to access your Wordpress account.
                                </p>
                            </td>
                    </tr></table>
                    
                    <p class="identity-plus-hint" >Get your free <span class="identity-plus-brand">plus</span> account for secure, password-less login experience.</p>
                    <input type="hidden" name="action" value="identity_plus_connect">
                    <input type="submit" id="identity_plus_disconnect" style="background:#303030; color:#62B2F3; padding:7px 15px 5px 15px; border-radius:2px; border:1px solid #000000" value="Get Id+">
                <?php } ?>
        <?php
}


function identity_plus_authentication_page(  ) {
		?>
		<div class="identity-plus-main-fm-header">
			<h1 class="identity-plus-brand">Identity<span>plus</span></h1>
			<h5>man &amp; machine</h5>
		</div>
		<form class="identity-plus-main-fm" method="post" action="<?php echo admin_url( 'admin.php' ); ?>">
                <?php wp_nonce_field('my_delete_action'); ?>
				<?php identity_plus_idp_page(); ?>
		</form>
		<?php
}


add_filter('upload_mimes', 'identity_plus_enable_extra_extensions');
