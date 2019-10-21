<?php namespace identity_plus\api;

if (!defined('Identity +')){
	error_log("File \"".__FILE__."\" was called directly. ");
	exit; // Exit if accessed directly
}

use identity_plus\api\communication\Redirect_Request;
use identity_plus\api\communication\Identity_Profile;
use identity_plus\api\communication\Anonymous_ID;
use identity_plus\api\communication\Simple_Response;
use identity_plus\api\communication\Identity_Inquiry;
use identity_plus\api\communication\Local_User_Information;
use identity_plus\api\communication\Trust;
use identity_plus\api\communication\Intrusion_Report;
use identity_plus\api\communication\Local_User_Reference;
use identity_plus\api\communication\Reference_Number;
use identity_plus\api\communication\Intrusion_Reference;
use identity_plus\api\communication\Intent_Type;
use identity_plus\api\communication\Intent;
use identity_plus\api\communication\Intent_Reference;
use identity_plus\api\communication\Service_Agent_Identity_Request;
use identity_plus\api\communication\Service_Agent_Identity;

/*
 * (C) Copyright 2016 Identity+ (https://identity.plus) and others.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * This code is part of the identity+ API Wrapper suite and it is meant to facilitate
 * access to the identity + ReST Service. While the ReST service is not dependent 
 * upon this code, this code shortens implementation time because it wraps regular
 * ReST calls into a more developer friendly package.
 * 
 * You are free to make changes to this code to better suite your particular
 * implementation and keep it closed source, however, if you consider the changes are relevant to the
 * the identity + community, please consider donating your changes back to the community.
 * 
 * You are permitted to use the identity.plus package names in your fork as long as the 
 * code can be used exclusively to connect to the Identity + ReST API services.
 * 
 * Please submit bugs or improvement requests at https://identity.plus/support/contact
 *
 * Contributors:
 *     Stefan Harsan Farr
 */

/**
 * The API_Action represents a set of actions that can be suggested for each outcome.
 * It is however ultimately up to the developers whether they want to take this action or 
 * do something else entirely
 *  
 * @author Stefan Harsan Farr
 */
class Identity_Plus_API {
	// const HOME = "identity.plus";
	const HOME = "local.stefanfarr.identityplus.app";
    const api_endpoint = "https://api." . self::HOME . "/v1";
	const validation_endpoint = "https://signon." . self::HOME;
	
    public $cert_details;
    private $private_key;
    private $cert;
    private $chain;
    
    /**
     * Parse the API certificate and key data and create the object
     * @param unknown $cert_data the certificate pkcs12 certificate + key store data
     * @param unknown $password the password for the keystore
     * @return Identity_Plus_API 
     */
    public function __construct($cert_data, $password){
    	$cert_store = array();
    	
    	if(openssl_pkcs12_read ($cert_data , $cert_store , $password)){
            $this->private_key = $cert_store['pkey'];
	    	$this->cert = $cert_store['cert'];
	    	$this->chain = $cert_store['extracerts'];
	    	$this->cert_details = openssl_x509_parse($this->cert);
    	}
    	else{
            error_log("Unable to load key material from pkcs12 store.");
            return NULL;
        } 
    }
    
    /**
     * It constructs the URL for a redirect based Anonymous ID retrival call. This is necessary when the local server
     * does not work over SSL and hence cannot see the SSL Client Certificate. In this case Identity + will extract it.
     * The call must contain a payload parameter which is encoded with the API Certifcate's private key, guaranteeing the 
     * legitimacy of the call. The answer will contain a parameter that is encrypted with the public key of the API
     * Key therefore only the issuing legitimate client service can open it.
     * 
     * @param unknown $return_url the URL to return to after the call is made. the default it will return to the landing URL
     * @return The url where to redirect
     */
    public function anonymous_id_retrival_endpoint($return_url = NULL){
    	if($return_url == NULL){
    		if (!isset($_SESSION)) session_start();
    		$_SESSION['identity-plus-return-query'] = Identity_Plus_Utils::here().Identity_Plus_Utils::query();
    		$return_url = Identity_Plus_Utils::here();
    	}

        $intent = $this->create_intent(Intent_Type::discover, NULL, NULL, NULL, NULL, $return_url);
        return self::validation_endpoint.'/' . $intent->value;
    }
    
    /**
     * It constructs the URL for a certificate validation redirect. This is necessary when the certificate is not clean
     * so the conflict needs to be resolved at Identity +. This could be idle certificate, expired or any number of reason.
     * The call must contain a payload parameter which is encoded with the API Certifcate's private key, guaranteeing the 
     * legitimacy of the call. The answer will contain a parameter that is encrypted with the public key of the API
     * Key therefore only the issuing legitimate client service can open it.
     * 
     * @param unknown $return_url the URL to return to after certificate conflict is resolved. The default it will return to the landing URL
     * @return The url where to redirect
     */
    public function certificate_validation_endpoint($return_url = NULL){
        if($return_url == NULL){
    		session_start();
    		$_SESSION['identity-plus-return-query'] =  Identity_Plus_Utils::here().Identity_Plus_Utils::query();
    		$return_url = Identity_Plus_Utils::here();
    	}

        $intent = $this->create_intent(Intent_Type::discover, NULL, NULL, NULL, NULL, $return_url);
        return self::validation_endpoint.'/' . $intent->value;
    }

    /**
     * Renew Identity Plus Service Agent ID
     * 
     * @param unknown $service_domain the service to which the agent belongs. The service the current agent represents must be administrator 
     * over the target service. By default it is the service the agent belongs (self renew)
     * @param unknown $agent_name the name of the agent, if not provided it will be taken from the certificate (self renew)
     * @return if all goes well an updated Service_Agent_Identity otherwise a Simple_Response containing an error code
     */
    public function issue_service_agent_identity($service_domain, $agent_name){
    	$request = new Service_Agent_Identity_Request($service_domain, $agent_name);
    	return $this->issue_call($request, "PUT");
    }
    
    public function register_service($register_intent, $agent_name){
    	$args = array(
            "authorization" => $register_intent, 
            "agent-name" => $agent_name
        );

    	$request = array(
            "operation" => "issue-service-agent-certificate", 
            "args" => $args
        );
        
        $call = curl_init(self::validation_endpoint . "/api/v1");
        
    	// curl_setopt($call, CURLOPT_VERBOSE, true);
    	curl_setopt($call, CURLOPT_URL, self::validation_endpoint . "/api/v1");
  		curl_setopt($call, CURLOPT_CUSTOMREQUEST, "POST");
    	curl_setopt($call, CURLOPT_POSTFIELDS, json_encode($request)); 
    	curl_setopt($call, CURLOPT_RETURNTRANSFER, true);
        
        curl_setopt($call, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($call, CURLOPT_SSL_VERIFYHOST, false);
    	
    	$result = curl_exec($call);
    	    	
    	curl_close ($call);
    	
        return self::decode(json_decode($result));
    }
   
    /**
     * Initiates an binding API Call which will bind the local user to the identity + account with the given anonymoys id.
     * This must be the user which is currently in the browser with the given recognized and valid certificate. This call
     * should only be made in the presence of an ongoing session.
     * 
     * @param unknown $serial_number: the anonymous id extracted from the SSL Client Certificate of the visitor currently holding the session
     * @param unknown $local_id: the local unique id to bind with this anonymous user, the id must point back to the currently logged in user
     * @param unknown $account_age_days: the number of days since this person has a local account
     * @param number $trust_so_far: the amount of trust placed in this account by the service
     * @return if all goes well an updated Identity_Profile (with the freshly bound local user) otherwise a Simple_Response containing an error code
     */
    public function bind_local_user($serial_number, $local_id, $account_age_days, $trust_so_far = 100){
    	$request = new Local_User_Information($serial_number, $local_id, $account_age_days, $trust_so_far);
    	return $this->issue_call($request, "PUT");
    }
    
    /**
     * Disconnects a local user from Identity +
     * 
     * @param unknown $local_id the id of the local user to unbind. This must be the same id with which the user has been bound with
     * @return if all goes well an updated Identity_Profile (with no bound user this time) otherwise a Simple_Response containing an error code
     */
    public function unbind_local_user($local_id){
    	$request = new Local_User_Reference($local_id);
    	return $this->issue_call($request, "DELETE");
    }
    
    /**
     * Deletes an intrusion from Identity +
     *
     * @param $reference the id of the intrusion to delete. Only intrusions submitted by this server can be deleted
     * @return a Simple_Response with an acknowledge or containing an error code
     */
    public function revoke_intrusion($reference){
    	$request = new Intrusion_Reference($reference);
    	return $this->issue_call($request, "DELETE");
    }
    
    /**
     * Adds trust to a certificate holder. Trust is logarithmic. You can add maximum 10000 tokens of trust for a logarithmic output of maximum 4
     * a default one is addedd in case the user has no intrusion record, as such 5 is equivalent with 100% trust. The trust of a stranger with no intrusion is 1 (20%),
     * if you have assigned him 10 tokens of trust it will become 2 (40%), if you assign 100 tokens of trust it becomes 3 (60%), at 1000 tokens of trust is 4 (80%).
     * Adjust your trust regime based on these values.
     * 
     * @param unknown $local_user_name: the local id of the user, if applicable, if trust is added for a strange visitor this can be left empty
     * @param unknown $serial_number: this is only needed if there is no local user, in that case the trust goes to the stranger bearing this anonymous id
     * @param unknown $trust_tokens: the amount of trust.
     * @return if all goes well an updated Identity_Profile (containting the extra trust) otherwise a Simple_Response containing an error code
     */
    public function put_trust($local_user_name, $serial_number, $trust_tokens){
    	$request = new Trust($local_user_name, $serial_number, $trust_tokens);
    	return $this->issue_call($request, "PUT");
    }

    /**
     * Prepares and submits an intrusion report.
     * Intrusions are really bad, please be mindful. If you just want to give a warning, submit an intrusion with 0 value. This does not harm the certificate holder's
     * reputation but it will send him or her a warning and will temporarily block the cerfiicate. Trust is ruined just as fast as it grows. 
     * A user with an intrusion report will no longer receive the default trust point therefore it will start with a handicap of 1 point (maximum being 4 in this 
     * case equivalent with maximum 80%). But based on the amount of severity, the trust value will quickly fall below the default 20% of a stranger, 
     * even if the individual starts with a high trust.
     * 
     * If it is legitimate adjust severity accordingly. 
     * 
     * @param unknown $serial_number: the certificate to report against.
     * @param unknown $severity: the severity of the intrusion see Intrusion_Severity class 
     * @param unknown $message: a message to send the owner
     * @param unknown $url: the url of the page where the user did the harm
     * @param string $additional_information: any other information you want to send identity +
     * @return A reference Number for the intrusion
     */
    public function report_intrustion($serial_number, $severity, $message, $url, $additional_information = ''){
    	$headers = array();
    	foreach ($_SERVER as $key => $value){
	    		if(strpos($key, 'HTTP_') === 0){
		    			array_push($headers, str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))))." = ".$value);
	    		}
    	}
    	
    	$request = new Intrusion_Report(
    			$serial_number, $severity, $message, 
    			$_SERVER['REMOTE_ADDR'],
    			$url, 
    			$headers, 
    			$additional_information);
    	
    	return $this->issue_call($request, "PUT");
    }
    
    /**
     * Queries for the Identity + profile of the visitor with the given anonymous id. This call should always take place in the 
     * presence of a certificate. Otherwise it may result in outdated response. Ex: the user could have revoked, renewed this
     * certificate since then.
     *   
     * @param unknown $serial_number
     * @return if all goes well an Identity_Profile, otherwise a Simple_Response containing an error code
     */
    public function query_identity_plus_profile($serial_number){
    	$request = new Identity_Inquiry($serial_number);
    	return $this->issue_call($request, "POST");
    }

    /**
     * Creates an activity intent that can later be references 
     *   
     * @param unknown $serial_number
     * @return if all goes well an Identity_Profile, otherwise a Simple_Response containing an error code
     */
    public function create_intent($type, $local_user_name, $name, $email_address, $phone_number, $return_url){
    	$request = new Intent($type, $local_user_name, $name, $email_address, $phone_number, $return_url);
    	return $this->issue_call($request, "PUT");
    }
    
    /**
     * Processes the HTTP call via curl. The call is authenticated with the API certificate
     * 
     * @param unknown $request, The request object that is going into the call in JSON encodedd form
     * @param unknown $method: the call method GET, POST OR DELETE
     * @param string $debug
	 *
     * @return The JSON object containing the repsonse
     */
   	private function issue_call($request, $method, $debug = FALSE){
    	$temp_cert = tmpfile();
    	$temp_pkey = tmpfile();
    	$temp_pass = Identity_Plus_Utils::random_text(20);
    	
        // export the key, we need to use this, because it will add a password (it is important for security reasons)
    	openssl_pkey_export_to_file($this->private_key, stream_get_meta_data($temp_pkey)['uri'], $temp_pass);
        
        // store the certificate into the file (it is simple text encoded pem)
        fwrite($temp_cert, $this->cert);
        // if we have a chain, store the chain to the file as well
        if($this->chain) foreach ($this->chain as $key => $value) fwrite($temp_cert, $value);

    	
    	$call = curl_init(self::api_endpoint);
    	
    	if($debug) curl_setopt($call, CURLOPT_VERBOSE, true);
    	curl_setopt($call, CURLOPT_URL, self::api_endpoint);
    	curl_setopt($call, CURLOPT_SSLKEY, stream_get_meta_data($temp_pkey)['uri']);
  		curl_setopt($call, CURLOPT_SSLKEYPASSWD, $temp_pass);
    	curl_setopt($call, CURLOPT_SSLCERT, stream_get_meta_data($temp_cert)['uri']);
  		curl_setopt($call, CURLOPT_CUSTOMREQUEST, $method);
    	curl_setopt($call, CURLOPT_POSTFIELDS, $request->to_json()); 
    	curl_setopt($call, CURLOPT_RETURNTRANSFER, true);
        
        curl_setopt($call, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($call, CURLOPT_SSL_VERIFYHOST, false);

    	$result = curl_exec($call);
    	
    	if($debug){
	    	var_dump(curl_getinfo($call));
	    	error_log(curl_error($call));
    	}
    	
    	curl_close ($call);
    	
    	// this removes the files
    	fclose($temp_cert);
    	fclose($temp_pkey);

    	return self::decode(json_decode($result));
    }
    
    /**
     * An encryption function wrapper using the API certificate's private key
     * 
     * @param unknown $raw_data: some data to be encrypted
     * @return data encrypted with the private key
     */
    public function encrypt($raw_data){
    	openssl_private_encrypt($raw_data, $encrypted, $this->private_key);
    	return $encrypted;
    }


    /**
     * Identifies the JSON object in the response and decodes it into the corresponding PHP Object 
     * @param unknown A response Object.
     */
	public static function decode($data){
        error_log('------------------\n'.json_encode($data).'------------------\n');
        
		if(property_exists($data, 'Identity-Profile')) return new Identity_Profile($data->{'Identity-Profile'});
		else if(property_exists($data, 'Reference-Number')) return new Reference_Number($data->{'Reference-Number'});
		else if(property_exists($data, 'Anonymous-ID')) return new Anonymous_ID($data->{'Anonymous-ID'});
		else if(property_exists($data, 'Intent-Reference')) return new Intent_Reference($data->{'Intent-Reference'});
		else if(property_exists($data, 'Service-Agent-Identity')) return new Service_Agent_Identity($data->{'Service-Agent-Identity'});
		else return new Simple_Response($data->{'Simple-Response'});
	}
	
	/**
	 * Computes an Identity + challenge based on some data.
	 * 
	 * @param unknown $object
	 * @return unknown
	 */
	public function compute_challenge($object = NULL){
		// By default this data will be the serial number of the key
		if($object == NULL) $object = $this->cert_details['serialNumber'];
		
		// add some salt, make it difficult to crack
		$challenge = Identity_Plus_Utils::random_text(32).$object;
		
		//encrypt and transform to url base 64 (these challenges are meant to be sent via URL)
		$challenge = Identity_Plus_Utils::base64url_encode($this->encrypt($challenge));
		
		return $challenge;
	}
	
}
