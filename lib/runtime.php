<?php 

if (!defined('Identity +')){
	error_log("File \"".__FILE__."\" was called directly. ");
	exit; // Exit if accessed directly
}

function idp_healthy($options){
	$idp_on = true;

    if(empty($options) || isset($options['cert-data']) || isset($options['cert-password'])){
        $idp_on = false;
        add_settings_error('identity_plus_settings', 'identity-plus-api-certificate-error', "API Certificate is missing!", "error");
    }

	if($idp_on){
		$cert_store = array();
		if(openssl_pkcs12_read (base64_decode($options['cert-data']) , $cert_store , $options['cert-password'])){
			$cert_details = openssl_x509_parse($cs['cert']);
			$now = time();
			$your_date = strtotime(wp_get_current_user()->user_registered);
			$days = floor(abs($cert_details['validTo_time_t'] - $now) / 86400);
			
			// disable Identity Plus if agent certificate expired
			// user must go through re-initialziation process
			$idp_on = $cert_details['validTo_time_t'] - $now > 0;
			$idp_on = $days > 500;
		}
		else{
            add_settings_error('identity_plus_settings', 'identity-plus-api-certificate-error', "Certificate password might be wrong!", "error");
            $idp_on = false;
        }
	}

	return $idp_on;
}

/**
 * Lazy create identity + API object
 * 
 * @param unknown $options
 */
function identity_plus_create_api($options){
    return new Identity_Plus_API(base64_decode($options['cert-data']), $options['cert-password']);
}
