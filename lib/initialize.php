<?php 
if (!defined('Identity +')){
	error_log("File \"".__FILE__."\" was called directly. ");
	exit; // Exit if accessed directly
}

require_once 'identity_plus/Identity_Plus_API.php';
require_once 'identity_plus/api/API_Action.php';
require_once 'identity_plus/api/Identity_Plus_Utils.php';
require_once 'identity_plus/api/Communication.php';

use identity_plus\api\API_Action;
use identity_plus\api\Identity_Plus_Utils;
use identity_plus\api\Identity_Plus_API;
use identity_plus\api\communication\Anonymous_ID;
use identity_plus\api\communication\Intent_Type;
use identity_plus\api\communication\Intent_Reference;

add_action( 'wp_enqueue_scripts', 'identity_pluss_cf_frame_style' );
add_action( 'admin_enqueue_scripts', 'identity_pluss_cf_admin_frame_style' );

add_filter('manage_users_columns', 'idp_add_user_id_column');
add_action('manage_users_custom_column',  'idp_show_user_id_column_content', 10, 3);

function idp_problems($options){
	
    if(empty($options) || !isset($options['cert-data']) || !isset($options['cert-password'])){
        return "API Certificate is missing!";
    }

	$cert_store = array();

	if(openssl_pkcs12_read (base64_decode($options['cert-data']) , $cert_store , $options['cert-password'])){
		$cert_details = openssl_x509_parse($cert_store['cert']);
		$now = time();
		$days = floor(abs($cert_details['validTo_time_t'] - $now) / 86400);
		
		// disable Identity Plus if agent certificate expired
		// user must go through re-initialziation process
		if($cert_details['validTo_time_t'] - $now < 0) return "Certificate expired!" . strval($cert_details['validTo_time_t'] - $now);
		// $idp_on = $days > 500;
	}
	else return "Certificate password might be wrong!";
}

/**
 * Lazy create identity + API object
 * 
 * @param unknown $options
 */
function identity_plus_create_api($options){
    return new Identity_Plus_API(base64_decode($options['cert-data']), $options['cert-password']);
}

function idp_add_user_id_column($columns) {
    $columns['user_id'] = 'Id +';
    return $columns;
}
 
function idp_show_user_id_column_content($value, $column_name, $user_id) {
    $user = get_userdata( $user_id );
	if ( 'user_id' == $column_name ){
        $idp_bound = get_user_meta($user_id, 'identity-plus-bound', true);
        if($idp_bound) return $idp_bound;
        else return 'N/A';
    }
    return $value;
}



function identity_plus_initialize(){
		if(!function_exists("curl_init")){
			error_log("Curl extension is not installed on the server! Identity + needs php5-curl extension to work. <br>(for Ubuntu type: sudo apt-get install php5-curl)");
			return;
		}
	
		// make sure we have everything that is needed to
		// run Identity + (certificate, password)
		$options = get_option( 'identity_plus_settings' );

		// if we have Identity + then we can start using it
		if(!idp_problems($options)){
			// attempt to start session
			session_start();
			$identity_plus_api = null;

			// if returning from Identity + with information payload
			// extract the payload set the session variable
			error_log(">>>>>>>>>>>>>>>>>>>> ".$_GET['resp']);
			if($_GET['resp']){
					// the response gives us a reference  (a one time anonymous id corresponding to the user)
                    // will use that as anonymous id, the server does the rest
                    $_SESSION['identity-plus-anonymous-id'] = $_GET['resp'];
			}

			// if returning from Identity + with information payload
			// extract the payload set the session variable
			else if($_GET['idp-api-response']){
					// create the API in a lazy way, only if it is necessary
					if($identity_plus_api == null) $identity_plus_api = identity_plus_create_api($options);
					$identity_plus_api->legacy_http_extract_anonymous_id($_SESSION);
			}
	
			// get the Identity + profile of the current user if not already gotten and
			// if we have an anonymous identity + ID
			if(isset($_SESSION['identity-plus-anonymous-id']) && $_SESSION['identity-plus-anonymous-id'] != 'N/A'){
				// verify in real time if the certificate has been expired
				// and clear it from the session to force refresh
				if(get_option("identity-plus/".$_SESSION['identity-plus-anonymous-id'], "") == "expire"){
					unset($_SESSION['identity-plus-user-profile']);
					delete_option("identity-plus/".$_SESSION['identity-plus-anonymous-id']);
				}
					
				// Get Identity + User Profile if we have anonymous id
				if(!isset($_SESSION['identity-plus-user-profile'])){
                     $identity_plus_api = identity_plus_obtain_user_profile($options, $identity_plus_api);
                }
			}
	
	
			// If Identity + Profile Exists
			error_log("...........dx.............. " . $_SESSION['identity-plus-user-id']);
			error_log("......................... " . var_dump($_SESSION['identity-plus-user-profile']));
			if(isset($_SESSION['identity-plus-user-profile'])) $identity_plus_api = identity_plus_autologin($options, $identity_plus_api);

            // see if we triggered a bind event
			if(isset($_SESSION['identity-plus-user-profile']) && $_GET['bind'] && !get_user_meta($user_id, 'identity-plus-bound', true)){
                $user_id = get_current_user_id();
			    
                // the user was already bound, we specified that via the intent, but we need to this return value so that we can
                // remember this connection locally (it is optional, but useful), and to give feedback to the user
                add_user_meta($user_id, 'identity-plus-bound', $_SESSION['identity-plus-user-profile']->local_user_name);

                $error = "I: Your wordpress account and your identity plus account have been connected!";
                set_transient("identity_plus_acc_{$user_id}", $error, 45);      
            }	
	
			// verify if the resource matches the filter
			$is_resource_protected = false;
			$page = $_SERVER['REQUEST_URI'];
			$filter = isset($options['page-filter']) && strlen($options['page-filter']) > 0 ? $options['page-filter'] : "/wp-admin\n/wp-login.php";
	
			// iterate through the filter and see if the resource is protected
			foreach(explode("\n", $filter) as $f){
				$f = rtrim($f);
				if(strpos($page, $f) === 0){
					$is_resource_protected = true;
					break;
				}
			}
	
			// if current resource is not protected we skip this section
			if($is_resource_protected){
					// create the API in a lazy way, only if it is necessary
					if($identity_plus_api == null) $identity_plus_api = identity_plus_resource_protection_trigger($options, $identity_plus_api);
			}
	
	
			// redirect back to original url
			// if an Identity + action triggrered a redirect
			if(isset($_SESSION['identity-plus-return-query'])){
				wp_redirect($_SESSION['identity-plus-return-query']);
				unset($_SESSION['identity-plus-return-query']);
				exit();
			}
	
			if(is_user_logged_in() && strpos($_SERVER['REQUEST_URI'], '/wp-login.php') === 0 && $_REQUEST['action'] != 'logout'){
				wp_redirect('/wp-admin');
				exit();
			}
	
		} // end idp_on if
}


/**
 * Issue an API call to Identity + and obtain the Certificate status and the 
 * Identity + Profile associated with the certificate. 
 * 
 * @param unknown $options
 * @param unknown $identity_plus_api
 */
function identity_plus_obtain_user_profile($options, $identity_plus_api){
		// create the API in a lazy way, only if it is necessary
		if($identity_plus_api == null) $identity_plus_api = identity_plus_create_api($options);
			
		// Issue the a profile verification at Identity +
		$profile = $identity_plus_api->query_identity_plus_profile($_SESSION['identity-plus-anonymous-id']);

		// Verify the outcome of the request
		if($profile->outcome->action == API_Action::Redirect){
			// This will be triggered if the certificate needs any kind
			// of validation that needs to be done at Identity +
			$validation_url = $identity_plus_api->certificate_validation_endpoint();
			wp_redirect($validation_url);
			exit();
		}
		else if($profile->outcome->action == API_Action::Evasive_Maneuver){
			// Triggered if there are security issues with the certificate of the visitor
			// Expired, manually revoked, flagged for danger, etc.
			include 'danger.php';
			exit();
		}
		else $_SESSION['identity-plus-user-profile'] = $profile;
		
		return $identity_plus_api;
}


/**
 * Post process the Identity + profile to make sure everything is in order in the database
 * the connectsion with the local user, log in the user automcatically, etc.
 * 
 * @param unknown $options
 * @param unknown $identity_plus_api
 */
function identity_plus_autologin($options, $identity_plus_api){
		// ignore autologin if action is logout
		if($_REQUEST['action'] == 'logout') return;
	
		$profile = $_SESSION['identity-plus-user-profile'];
			
		// If user is logged in and the Identity + profile is not bound
		if(is_user_logged_in() && !isset($profile->local_user_name) && false){
			if($identity_plus_api == null) $identity_plus_api = identity_plus_create_api($options);
	
			$now = time();
			$your_date = strtotime(wp_get_current_user()->user_registered);
			$days = ceil(abs($now - $your_date) / 86400);
			$profile = $identity_plus_api->bind_local_user($_SESSION['identity-plus-anonymous-id'], wp_get_current_user()->ID, $days);
			$_SESSION['identity-plus-user-profile'] = $profile;
		}
			
		// if no user is logged in but we have
		// Identity + Profile with local user ID connected
		// will log in the user automatically
		if(!is_user_logged_in() && isset($profile->local_user_name)){
			$user = get_user_by('id', $profile->local_user_name);

			// Automatically log in the user who owns the certificate
			if(!is_wp_error($user)){
				wp_clear_auth_cookie();
				wp_set_current_user($user->ID);
				wp_set_auth_cookie($user->ID);
				do_action('wp_login', $user->user_login);
			}
		}
			
		// make sure we remember how this user is bound locally as well
		// this is not entirely necessary, Identity + will communicate this info back,
		// but just in case so that we know which other users are connected, when they are
		// not necessarily on-line
		if(is_user_logged_in() && isset($profile->local_user_name)){
            update_user_meta(wp_get_current_user()->ID, "identity-plus-bound", $profile->local_user_name);
        }
}


/**
 * Run the protection filter for resources that fall into the category
 * 
 * @param unknown $options
 * @param unknown $identity_plus_api
 */
function identity_plus_resource_protection_trigger($options, $identity_plus_api){
		// Get the Identity + Anonymous Id
		// if not laready gotten
		if(!isset($_SESSION['identity-plus-anonymous-id'])){
				// lazy creation of the api
				if($identity_plus_api == null) $identity_plus_api = identity_plus_create_api($options);
				
				// send to Identity + to extract the Identity + anonymous id
				$retrive_url = $identity_plus_api->anonymous_id_retrival_endpoint();

				wp_redirect($retrive_url);
				exit();
		}
		 

		// We must see if the resource being accessed needs to be locked
		// This one is more restrictive
		$lock_resource = false;

        // unifying lock-down and enforce to avoid confusion so we are taking this sectionout
		// if(isset($options['lock-down']) && $options['lock-down'] && (!isset($_SESSION['identity-plus-user-profile']) || !isset($_SESSION['identity-plus-user-profile']->local_user_name))){
			// If lock down is on and
			// No Identity + Profile or there is no local user bound
		//	$lock_resource = true;
		//} else 

        if(isset($options['enforce']) && $options['enforce']  && !isset($_SESSION['identity-plus-user-profile'])){
			// Enforce is on and
			// No Identity + Profile
			$lock_resource = true;
		}
		
		if($lock_resource){
				include 'protected.php';
				exit();
		}
		
		return $identity_plus_api;
}


/**
 * Admin footer is the same
 */
function identity_plus_add_admin_footer() {
		identity_plus_add_footer(true);
}


/**
 * Add an Identity + cross validation widget to the bottom of the page
 * @param string $admin
 */
function identity_plus_add_footer($admin = false) {
		// only add cross validation widget if there is an identity + id present
		if(isset($_SESSION['identity-plus-anonymous-id']) && $_SESSION['identity-plus-anonymous-id'] != 'N/A'){
				$options = get_option( 'identity_plus_settings' );
				if(!empty($options) && isset($options['cert-data']) && isset($options['cert-password'])){
						$identity_plus_api = identity_plus_create_api($options);
				}
				
				// add the footer cross-validation widget
				if(false && isset($identity_plus_api) && $identity_plus_api != NULL){?>
						<iframe src="<?php echo Identity_Plus_API::validation_endpoint; ?>/widgets/cross-validation?origin=<?php echo $identity_plus_api->cert_details['serialNumber'] ?>&challenge=<?php echo  $identity_plus_api->compute_challenge()?>" scrolling="no" class="identity-plus-cf"></iframe>
						<?php
				}
		}
}


/**
 * Add style for identity + cross validation widget
 */
function identity_pluss_cf_frame_style(){
		?>
		<style>
				.identity-plus-cf{border:0px; width:100%; height:110px; overflow-x:hidden; overflow-y:hidden; border-top:1px solid #000000;}
				@media screen and (max-width: 700px){ .identity-plus-cf{height:210px; overflow-x:hidden; overflow-y:hidden;	}}
		</style>
		<?php 
}



/**
 * Add style for identity + cross validation widget on admin pages
 */
function identity_pluss_cf_admin_frame_style(){
		?>
		<style>
				.identity-plus-cf{border:0px; width:100%; height:110px; overflow-x:hidden; overflow-y:hidden; border-top:1px solid #000000;}
				@media screen and (max-width: 700px){ .identity-plus-cf{height:210px; overflow-x:hidden; overflow-y:hidden;	}}
				#wpfooter{bottom:120px;
		</style>
		<?php 
}


/**
 * Delete identity + session entries on log out. 
 */
function identity_plus_log_out(){
		unset($_SESSION['identity-plus-anonymous-id']);
		unset($_SESSION['identity-plus-user-profile']);

		wp_redirect('/');
		exit();
}


/**
 * Remove identity + database entries and disconnect the local user on identity + 
 */
function identity_plus_unistall(){
		// unbind users on Identity +
		$options = get_option( 'identity_plus_settings' );
		if(!empty($options) && isset($options['cert-data']) && isset($options['cert-password'])){
				$identity_plus_api = new Identity_Plus_API(base64_decode($options['cert-data']), $options['cert-password']);
	
				$users = get_users(array('meta_key' => 'identity-plus-bound'));
				foreach ($users as $u){
						$identity_plus_api->unbind_local_user($u->ID);
						delete_metadata('user', $u->ID, "identity-plus-bound");
				}
		}
		
		// delete comment metadata
		$comments = get_comments(array('meta_key' => "identity-plus-anonymous-id"));
		foreach ($comments as $c){
				delete_metadata('comment', $c->comment_ID, "identity-plus-anonymous-id");
		}
		
		// delete identity + options
		delete_option( 'identity_plus_settings' );
}
