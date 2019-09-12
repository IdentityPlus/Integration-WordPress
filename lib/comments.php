<?php
if (!defined('Identity +')){
	error_log("File \"".__FILE__."\" was called directly. ");
	exit; // Exit if accessed directly
}

require_once 'identity_plus/api/Intrusion_Severity.php';

use identity_plus\api\Identity_Plus_API;
use identity_plus\api\Intrusion_Severity;
use identity_plus\api\Identity_Plus_Utils;

add_action('wp_head', 'identity_plus_hide_comments', 100);


/**
 * Reward or report user based when comments are approved or 
 * reported as spam
 * @param unknown $new_status
 * @param unknown $old_status
 * @param unknown $comment
 */
function identity_plus_comment_callback($new_status, $old_status, $comment) {
	if($old_status != $new_status) {
		if($new_status == 'approved'){
				$cert_id = get_comment_meta($comment->comment_ID, "identity-plus-anonymous-id", true);
				if(isset($cert_id) && $cert_id != null){
						$reference = get_comment_meta($comment->comment_ID, "identity-plus-intrusion-reference", true);
						identity_plus_send_trust($cert_id, 10, $reference);
						if($reference) delete_comment_meta($comment->comment_ID, "identity-plus-intrusion-reference");
				}
		}
		else if($new_status == 'spam'){
				$cert_id = get_comment_meta($comment->comment_ID, "identity-plus-anonymous-id", true);
				if(isset($cert_id) && $cert_id != null){
						$reference = identity_plus_send_intrusion($cert_id);
						if($reference){
								update_comment_meta($comment->comment_ID, "identity-plus-intrusion-reference", $reference->value);
						}
				}
		}
		else if($old_status == 'spam' && $new_status == 'unapproved'){
				$cert_id = get_comment_meta($comment->comment_ID, "identity-plus-anonymous-id", true);
				if(isset($cert_id) && $cert_id != null){
						$reference = get_comment_meta($comment->comment_ID, "identity-plus-intrusion-reference", true);
						if($reference){
								identity_plus_revoke_intrusion($reference);
								delete_comment_meta($comment->comment_ID, "identity-plus-intrusion-reference");
						}
				}
		}
		// error_log("->".$new_status);
	}
}


/**
 * Send trust tokens 
 * 
 * @param unknown $cert_id
 * @param unknown $amount
 * @param unknown $intrusion_reference reference of the intrusion to delete, if any
 */
function identity_plus_send_trust($cert_id, $amount, $intrusion_reference = NULL){
		$options = get_option( 'identity_plus_settings' );
		if(!empty($options) && isset($options['cert-data']) && isset($options['cert-password'])){
				$identity_plus_api = new Identity_Plus_API(base64_decode($options['cert-data']), $options['cert-password']);

				if(isset($identity_plus_api) && $identity_plus_api != NULL){
						// revoke previousely submitted intrusions, if any
						if($intrusion_reference != NULL) $identity_plus_api->revoke_intrusion($intrusion_reference);

						$identity_plus_api->put_trust("", $cert_id, $amount);
						
						// careful, the comment is not comming from the user that approves it
						add_option("identity-plus/".$cert_id, "expire");
				}
		}
}

/**
 * Revoke an intrusion
 *
 * @param unknown $reference reference of the intrusion to delete
 */
function identity_plus_revoke_intrusion($reference){
		$options = get_option( 'identity_plus_settings' );
		if(!empty($options) && isset($options['cert-data']) && isset($options['cert-password'])){
				$identity_plus_api = new Identity_Plus_API(base64_decode($options['cert-data']), $options['cert-password']);
				
				if(isset($identity_plus_api) && $identity_plus_api != NULL){
						$identity_plus_api->revoke_intrusion($reference);
						delete_comment_meta($comment->comment_ID, "identity-plus-intrusion-reference");
								
						// careful, the comment is not comming from the user that approves it
						add_option("identity-plus/".$cert_id, "expire");
				}
		}
}


/**
 * Send the intrustion
 * 
 * @param unknown $cert_id
 */
function identity_plus_send_intrusion($cert_id){
		$options = get_option( 'identity_plus_settings' );
		if(!empty($options) && isset($options['cert-data']) && isset($options['cert-password'])){
				$identity_plus_api = new Identity_Plus_API(base64_decode($options['cert-data']), $options['cert-password']);
				
				if(isset($identity_plus_api) && $identity_plus_api != NULL){
						$reference = $identity_plus_api->report_intrustion(
								$cert_id, 
								identity_plus\api\Intrusion_Severity::intrusive, 
								"A SPAM was reported to originating from this device. This can be damaging to your reputation.",
								Identity_Plus_Utils::here().Identity_Plus_Utils::query());

						// careful, the comment is not comming from the user that approves it
						add_option("identity-plus/".$cert_id, "expire");
						return $reference;
				}
		}
		
		return false;
}


/**
 * Hide the comments if Identity + is required but not logged in. The submission is validated and will 
 * fail but we should prevent that action by hiding the form
 */
function identity_plus_hide_comments(){
		$options = get_option( 'identity_plus_settings' );
		if(!empty($options) && isset($options['cert-data']) && isset($options['cert-password']) && isset($options['comments']) && $options['comments']){
				if(!isset($_SESSION['identity-plus-anonymous-id']) || $_SESSION['identity-plus-anonymous-id'] == 'N/A'){
						?><style>#commentform{display:none;}</style><?php
				}
		}
}

/**
 * Add Idenitity + badge to the comments section
 * @param unknown $arg
 * @return string
 */
function identity_plus_comment_text($arg) {
	$options = get_option( 'identity_plus_settings' );
	
	if(isset($options['comments']) && $options['comments']){
				$identity_plus_api = new Identity_Plus_API(base64_decode($options['cert-data']), $options['cert-password']);
		
				$message = isset($_SESSION['identity-plus-anonymous-id']) && $_SESSION['identity-plus-anonymous-id'] != 'N/A' ?
									 'You will receive further credits for your contribution upon approval' :
									 'We\'d like you to use an Identity + Device ID when you comment!';
				
				$return_url = Identity_Plus_Utils::here().Identity_Plus_Utils::query();
				
				// compute the challenge for the badge
				// it needs to contain the anonymus id of the visitor as seen by this server
				$anonymous_id_challenge = isset($_SESSION['identity-plus-anonymous-id']) && $_SESSION['identity-plus-anonymous-id'] != 'N/A' ? $identity_plus_api->compute_challenge($_SESSION['identity-plus-anonymous-id']) : "none";

				$arg['title_reply'] = $arg['title_reply'].
				'<div><iframe src="'.Identity_Plus_API::validation_endpoint.'/widgets/identity-badge?origin='.$identity_plus_api->cert_details['serialNumber'].'&return='.urlencode($return_url).'&anonymous_id='.$anonymous_id_challenge.'" scrolling="no" allowtransparency="true" style="width:450px; max-width:100%; height:64px; display:block; border:0px;margin-top:5px;"></iframe>'.
				'<span style="font-size:14px; line-height:120%;  ">'.$message.'</span></div>';
	}
		
		return $arg;
}


/**
 * Validation in case somebody bypassess the hidden comment form and 
 * submits a comment anyways
 * 
 * @param unknown $commentdata
 * @return unknown
 */
function identity_plus_required_to_comment($commentdata){
		if(!isset($_SESSION['identity-plus-anonymous-id']) || $_SESSION['identity-plus-anonymous-id'] == 'N/A'){
				wp_die(__( 'You need an Identity + device id to comment. Sorry for the inconvenience.'));
		}
		
		return $commentdata;
}


/**
 * Add the identity + anonymoys id of the user when comment is 
 * inserted into the databse
 * 
 * @param unknown $comment_id
 * @param unknown $comment_object
 */
function identity_plus_comment_inserted($comment_id, $comment_object) {
		if(isset($_SESSION['identity-plus-anonymous-id']) && $_SESSION['identity-plus-anonymous-id'] != 'N/A'){
				add_comment_meta($comment_id, "identity-plus-anonymous-id", $_SESSION['identity-plus-anonymous-id'], true);
				if(wp_get_comment_status($comment_id) == 'approved') identity_plus_send_trust($_SESSION['identity-plus-anonymous-id'], 100);
		}
}
