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
		add_options_page( 'IdentityPlus Settings', 'Identity Plus', 'manage_options', 'identity_plus', 'identity_plus_options_page' );
}



function identity_plus_settings_init(  ) {
        if(!function_exists("curl_init")){
			add_settings_error('identity_plus_settings', 'identity-plus-curl-error', "Curl extension is not installed on the server! Identity + needs php-curl extension to work. <br>(for Ubuntu type: sudo apt-get install php-curl)", "error");
		}

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
		<p class="identity-plus-hint" style="margin-bottom:10px;">When Identity Plus device id is enforced, resources starting with any of the enumerated filters will only 
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
				.identity-plus-main-fm-header {margin:0; background:url('<?php echo plugins_url( 'img/idp.svg', __FILE__ ) ?>') no-repeat top left; background-size:42px; margin-bottom:30px;}
				.identity-plus-main-fm-header h1{padding-left:50px; padding-top:20px; margin-bottom:0; font-size:30px;font-weight:normal; }
				.identity-plus-main-fm-header h5{padding-left:50px; font-size:14px; font-weight:300; padding-bottom:0px; padding-top:0; margin:10px 0px 0px 0px;}
				h5.identity-plus-title {font-weight:300; font-size:16px; line-height:130%; margin:0; max-width:640px; color:#909090;}
				.identity-plus-main-fm p{margin:0;}
				.identity-plus-main-fm th{padding-bottom:15px; padding-top:15px; color:#136a92;}
				.identity-plus-main-fm td{padding-bottom:10px; padding-top:10px; }
				.identity-plus-main-fm h2, .identity-plus-main-fm h3{border:1px solid #72777c; border-bottom:0; background:#72777c; float:left; clear:left; padding:8px 20px; margin-bottom:0px; color:#FFFFFF; font-weight:normal; border-top-left-radius:1px; border-top-right-radius:1px; margin-left:0px; margin-top:50px;}
				.identity-plus-main-fm h4{border-bottom:1px solid #E0E0E0; color:#707070; padding-bottom:3px; padding-top:15px; margin-bottom:5px; font-weight:normal; font-size:16px;padding-top:0; margin-top:0; }
				.identity-plus-main-fm .cert {max-width:600px; border-radius:3px; float:left; clear:both;}
				.identity-plus-main-fm .cert p span{font-weight:bold;}
				.identity-plus-main-fm .cert p{margin:0px; float:left; clear:left;}
				.identity-plus-main-fm .cert {padding:10px; background:rgba(255, 255, 255, 0.6); border:1px solid rgba(0, 0, 0, 0.3);}
				.identity-plus-separator{border-top:1px solid #72777c; margin-top:0px; float:left; width:100%; clear:both; height:5px; margin-bottom:0px;}
				.identity-plus-hint{float:left; clear:both; max-width:600px; color:#909090; font-size:12px; margin-top:0px; margin-bottom:10px;}
                .identity-plus-brand span{color:#4292D3; margin-left:0.1em;}
                .identity-plus-main-fm input, .identity-plus-main-fm textarea{ float:left; clear:left;}
                .identity-plus-main-fm input[type="checkbox"]{ margin-top:0; margin-right:5px;}
                .identity-plus-main-fm label{ float:left; font-weight:400;}
                .identity-plus-main-fm div{float:left; clear:left; overflow:hidden; margin-bottom:10px;}
                .identity-plus-main-fm table{max-width:600px; float:left; clear:left;}
                .identity-plus-main-fm table th img{border-radius:60px; border:3px solid #D0D0D0;}
				.identity-plus-main-fm a.toggle-off {font-size:16px; color:#202020; padding:5px 0px 5px 0px; margin-right:30px; cursor:pointer;}
				.identity-plus-main-fm a.toggle-on {font-size:16px; color:#202020;  padding:5px 0px 5px 0px; margin-right:30px; cursor:pointer; border-bottom:1px solid #606060; display:inline-block;}
				.circular_progress {transform: rotate(90deg);display: inline;}
				div.holder-more {overflow: hidden;width: 128px;height: 128px;margin-top: 1px;margin-right: 30px;text-align: center; padding-left: 0; display: inline-block; float:left; clear:left; margin-right:30px;}
				div.holder-more p.overlay {position: relative;width: 100%;line-height: 120%;top: -93px;font-weight: 400; font-size:120%;}
				div.holder-more p.overlay span {font-weight: 300;font-size: 90%;color: #606060;}
				#wpfooter{position:static;}
				.nodisp{display:none;}
				.identity-plus-main-fm input[type=checkbox], .identity-plus-main-fm input[type=radio]{margin:0px 10px 5px 0px; float;left; clear:left; box-shadow:none;}
				.identity-plus-main-fm input[type=text]{padding:5px; box-shadow:none;}
				.identity-plus-main-fm textarea{margin:0px 10px 5px 0px; float:left; clear:left; margin-bottom:20px; border-radius:1px; border:1px solid #72777c; box-shadow:none; padding:5px 10px; background:rgba(0,0,0,0.05); font-family:monospace;}
				.identity-plus-main-fm textarea:focus{box-shadow:none;}
				.identity-plus-main-fm .submit{float:left; clear:left; margin-top:0px; padding:0px; height:32px;}
				.identity-plus-main-fm .submit input[type="submit"]{text-decoration:none; background:#4292D3; color:#FFFFFF; display:inline-block; border-radius:1px; border:1px solid rgba(0,0,0,0.1); cursor:pointer; box-shadow:none; text-shadow:none; font-size:14px; padding:3px 18px; height:auto;}
				.identity-plus-main-fm a.submit{text-decoration:none; background:#4292D3; color:#FFFFFF; display:inline-block; border-radius:1px; border:1px solid rgba(0,0,0,0.1); cursor:pointer; box-shadow:none; text-shadow:none; font-size:14px; padding:8px 18px 6px 18px; height:auto;}
				.wp-core-ui .notice.is-dismissible{margin-left:0;}
		</style>
		<?php 
}

function settings_header(){ ?>
	<table><tr>
			<td><h5 class="identity-plus-title">
				Authenticate with your device and prevent unknown and malicious devices from accessing your Wordpress account. Great security at great convenience.
			</h5></td>
	</tr></table>
<?php }

function identity_plus_api_section_callback(  ) {
	$problems = idp_problems(get_option( 'identity_plus_settings' ));

	settings_header();

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
				<input type="hidden" name="action" value="certify_ownership">
				<div>
					<p class="identity-plus-hint" style="font-size:13px; margin-bottom:5px;">Click the button below to add certify your ownership of this Wordpress instance.</p>
					<?php submit_button("Certify Ownership"); ?>
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
			<h1 class="identity-plus-brand">identity<span>plus</span></h1>
			<h5>authenticate everything</h5>
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

add_action( 'admin_post_certify_ownership', 'identity_plus_admin_certify_ownership');
function identity_plus_admin_certify_ownership(){
	$options = get_option( 'identity_plus_settings' );

	// request a registration intent and receive a reference and a challenge
	// identity plus will make an ouut of band call to the server with the intent to validate that challenge
	if($identity_plus_api == null) $identity_plus_api = identity_plus_create_api($options);
	$intent_ref = $identity_plus_api->issue_register_intent();

	error_log("intent----->".$intent_ref->value);

	// store the challenge in the database so we can serve it later
	$options['registeration-reference'] = $intent_ref->value;
	$options['challenge'] = $intent_ref->challenge;

	update_option( 'identity_plus_settings', $options);

	// redirect to authorization page
	wp_redirect( "https://register." . Identity_Plus_API::HOME . '/' . $intent_ref->value, 302, 'WordPress' );

	exit();
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
		wp_redirect("https://signon." . Identity_Plus_API::HOME . '/' . $intent->value);

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
                    'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCIKCSB3aWR0aD0iNjQwcHgiIGhlaWdodD0iNjQwcHgiIHZpZXdCb3g9IjAgMCA2NDAgNjQwIiBlbmFibGUtYmFja2dyb3VuZD0ibmV3IDAgMCA2NDAgNjQwIiB4bWw6c3BhY2U9InByZXNlcnZlIj4KPGc+PHBhdGggZmlsbD0iIzQ1OTZDRSIgZD0iTTI0NC44NTcsNTg4LjAyN0gxMDcuODMxTDM5NS41MTMsMy40MmgxMzYuNjU2TDI0NC44NTcsNTg4LjAyN3oiLz48L2c+Cjwvc3ZnPgo='
            );
        }
}

function connect_header(){ ?>
	<table><tr>
			<th><img width="64" height="64" src="<?php echo plugins_url( 'img/unknown.jpg', __FILE__ ) ?>"></th>
			<td><p class="identity-plus-hint">
				Your Wordpress uses <a target="_blank" title="My Identity Plus Application" href="https://my.identity.plus"class="identity-plus-brand">identity<span>plus</span></a> to protect your account and your credentials.
				You can now enjoy secure, passwordless authentication experience. Only devices owned and registered by you can access your Wordpress account.
			</p></td>
	</tr></table>
<?php }

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
                <?php if(get_user_meta($user_id, 'identity-plus-bound', true)){ 
					connect_header(); 

					?>

                    <h2>Disconnect</h2><p class="identity-plus-separator" style="padding-top:5px;"></p>
                    <?php if(isset($options['enforce']) && $options['enforce'] == 1 ){ ?>
                        <p class="identity-plus-hint" >Your <a href="<?php echo admin_url('options-general.php?page=identity_plus'); ?>">identityplus settings</a> only allow admin access from certified devices. Disconnect is disabled as you would lock yourself out from admin section.</p>
                    <?php } else { ?>
                        <p class="identity-plus-hint" >By disconnecting your identityplus account from the local account, you will lose the ability to sign in via device id. Are you sure?</p>
                        <input type="hidden" name="action" value="identity_plus_disconnect">
                        <div style="margin-top:10px;"><input type="checkbox" id="idp-i-am-sure" name="idp-i-am-sure" onchange="document.getElementById('identity_plus_disconnect').style.display = document.getElementById('idp-i-am-sure').checked ? 'block' : 'none';"><label for="idp-i-am-sure">Yes, I am sure I want to disconnect.</label></div>
                        <input type="submit" id="identity_plus_disconnect" style="display:none; background:#900000; color:#FFFFFF; padding:8px 18px 6px 18px; border-radius:3px; border:1px solid rgba(0,0,0,0.1);" value="Disconnect">
                    <?php } ?>

                <?php } else if(isset($_SESSION['identity-plus-user-profile'])){ 
					connect_header();

					?>
                    
                    <p class="identity-plus-hint" >Connect your identity<span class="identity-plus-brand">plus</span> account for secure, password-less login experience.</p>
                    <input type="hidden" name="action" value="identity_plus_connect">
                    <input type="submit" id="identity_plus_disconnect" style="background:#4292D3; color:#FFFFFF; padding:8px 18px 6px 18px; border-radius:3px; border:1px solid rgba(0,0,0,0.1); cursor:pointer; margin-top:10px;" value="Connect">
                <?php } else { 
					connect_header();

					?>
                    
                    <p class="identity-plus-hint" >Get your free identity<span class="identity-plus-brand">plus</span> account for secure, password-less login experience.</p>
                    <input type="hidden" name="action" value="identity_plus_connect">
                    <input type="submit" id="identity_plus_disconnect" style="background:#303030; color:#62B2F3; padding:7px 15px 5px 15px; border-radius:2px; border:1px solid #000000" value="Get Id+">
                <?php } ?>
        <?php
}


function identity_plus_authentication_page(  ) {
		?>
		<div class="identity-plus-main-fm-header">
			<h1 class="identity-plus-brand">identity<span>plus</span></h1>
			<h5>authenticate everything</h5>
		</div>
		<form class="identity-plus-main-fm" method="post" action="<?php echo admin_url( 'admin.php' ); ?>">
                <?php wp_nonce_field('my_delete_action'); ?>
				<?php identity_plus_idp_page(); ?>
		</form>
		<?php
}


add_filter('upload_mimes', 'identity_plus_enable_extra_extensions');
