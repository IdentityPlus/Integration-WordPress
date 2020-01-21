<?php namespace identity_plus\api\communication;

if (!defined('Identity +')){
	error_log("File \"".__FILE__."\" was called directly. ");
	exit; // Exit if accessed directly
}

require_once 'Outcome.php';

use identity_plus\api\Identity_Plus_Utils;
use identity_plus\api\Outcome;

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
 * A common ancestor class for all API Objects
 *
 * @author Stefan Harsan Farr
 */
class API_Object{
}

/**
 * A common ancestor class for all API requests
 *
 * @author Stefan Harsan Farr
 */
class API_Request extends API_Object{
	public function to_json(){
		$json = '';
		$props = get_object_vars ($this);
		foreach($props as $name => $value){
			if(strlen($json) != 0) $json .= ",";
			$json .= '"'.str_replace('_', '-', $name).'": '.json_encode($value);
		}
		return '{"'.$this->get_class_name().'":{'.$json."}}";
	}
	
	private function get_class_name(){
		$classname = get_class($this);
	    
		if($pos = strrpos($classname, '\\')) $classname = substr($classname, $pos + 1);
	    
		return str_replace('_', '-', $classname);
	}
}

/**
 * A common ancestor class for all API responses
 *
 * @author Stefan Harsan Farr
 */
class API_Response extends API_Object{
	/**
	 * Will contain the outcome of the request. All responses have this field so we are going to
	 * put it in the ancestor class
	 */
	public $outcome;

	public function __construct($data) {
		$this->outcome = Outcome::value_of($data->{'outcome'});
	}
}


/**
 * The Redirect_Request is part of the Legacy HTTP call assembly.
 * This request is sent URL encoded as part of the redirect when the the
 * identity + service is used to read the client certificate from the user browser.
 *
 * This Object is only needed by implementations that use the identity + service over
 * http or do not have access to reading the SSL Client certificate for other reasons
 *
 * This request object must always come encrypted with the API Client's private key
 *
 * @author Stefan Harsan Farr
 */
class Redirect_Request extends API_Request{

	/**
	 * The URL to return to when with a response after the certificate is
	 * extracted, or not. The response will come encrypted in the url query
	 */
	public $return_url;

	/**
	 * This is a random sequence of text which is not needed as part of the identity +
	 * processes and can be discarded.
	 *
	 * It's sole purpose is to force different encryption outputs for responses with the
	 * same value. We use 16 characters, but normally even one character would suffice
	 * to change the output of the encrypted bytes.
	 */
	public $salt;

	public function __construct($return_url){
		$this->return_url = $return_url;
		$this->salt = Identity_Plus_Utils::random_text(16);
	}
}

/**
 * The Agent Certificate Renewal request is used to issue certificates for Service Agents (Clients). 
 * This can happen in two ways,:
 * either the request is made for a valid previous certificate, in which a renewal procedure is executed,
 * or it needs to be performed with an initial secret and then an issuing is performed.
 * A service agent can rewnew its own certificate this way, but care must be taken as the previous certificate will be 
 * revoked uppon issuing the new one.
 * 
 * @author Stefan Harsan Farr
 */
class Service_Agent_Identity_Request extends API_Request{

    /**
     * Name of the agent to issue certificate for.
     * If the name exists, it will renew, otherwise it will create a new agent
     */
    public $agent_name;
    
    /**
     * Domain Name of the service where the agent should be issued.
     * 
     * By default (if the domain is not specified), the agent will be created/renewed within the service
     * to which the calling agent (the one whose certificate is used to make this call) belongs to.
     * 
     * If the target service is specified, then the calling service (the service to which the calling agent belongs to)
     * must have administrative rights in target service. Otherwise this operation will fail 
     */
    public $service_domain;
    
    public function __construct($service_domain, $agent_name){
            $this->service_domain = $service_domain;
            $this->agent_name = $agent_name;
    }
}

/**
 * The core response for most request containing a reference number
 * It comes in response to a call that requires a reference number such as an intrusion report
 * 
 * @author Stefan Harsan Farr
 */
class Service_Agent_Identity extends API_Response{
   
    /**
     * p12 certificate format
     */
    public $p12;

    /**
     * Password for the p12 format
     */
    public $password;

    /**
     * PEM encoded certificate
     */
    public $certificate;
    
    /**
     * PEM encoded private key
     */
    public $private_key;
    
    
	public function __construct($data){
		parent::__construct($data);
		$this->p12 = $data->{'p12'};
        $this->password = $data->{'password'};
        $this->certificate = $data->{'certificate'};
        $this->private_key = $data->{'private-key'};
    }
}

/**
 * The serial_number is part of the Legacy HTTP call assembly.
 * This response comes URL encoded as part of the redirect when the the
 * identity + service is used to read the client certificate from the user browser.
 *
 * This Object is only needed by implementations that use the identity + service over
 * http or do not have access to reading the SSL Client certificate for other reasons
 *
 * This object will always come encrypted with the API Client's public key
 *
 * @author Stefan Harsan Farr
 */
class Anonymous_ID extends API_Response{
	/**
	 * The anonymous id as identified by the identity+ api
	 */
	public $serial_number;

	/**
	 * This is a random sequence of text which is not needed as part of the identity +
	 * processes and can be discarded.
	 *
	 * It's sole purpose is to force different encryption outputs for responses with the
	 * same value. We use 16 characters, but normally even one character would suffice
	 * to change the output of the encrypted bytes.
	 */
	public $salt;

	/**
	 * Empty initializer, it is necessary to initialize to null the public fields.
	 * The deserializer will override the final modifier and re-initialize the fields
	 * with the proper values
	 */
	public function __construct($data){
		parent::__construct($data);
		
		$this->serial_number = $data->{'serial-number'};
		$this->salt = $data->{'salt'};
	}
}

/**
 * It is a non-abstract representative of the API Response, the simplest case
 * of response in which we only have an outcome response.
 *
 * @author Stefan Harsan Farr
 */
class Simple_Response extends API_Response{
	/**
	 * Empty initializer, it is necessary to initialize to null the public fields.
	 * The deserializer will override the final modifier and re-initialize the fields
	 * with the proper values
	 */
	public function __construct($data){
		parent::__construct($data);
	}
}


/**
 * The core response for most request containing a reference number
 * It comes in response to a call that requires a reference number such as an intrusion report
 *
 * @author Stefan Harsan Farr
 */
class Reference_Number extends API_Response{
	/**
	 * The reference number value
	 */
	public $value;

	public function __construct($data){
		parent::__construct($data);
		$this->value = $data->{'value'};
	}
}

/**
 * The core response for most request containing all the profile information
 * necessary.
 *
 * @author Stefan Harsan Farr
 */
class Identity_Profile extends API_Response{
	/**
	 * The local user id that was bound with the identity+ account to which the validated
	 * anonymous id belongs to, if any.
	 *
	 * In case no local user id was bound to the identity + account by the requesting API Client
	 * this field will be empty.
	 */
	public $local_user_name;

	/**
	 * The local user secret that was bound with the identity+ account to which the validated
	 * anonymous id belongs to, if any.
	 *
	 * In case no secret was bound to the identity + account by the requesting API Client
	 * this field will be empty.
	 */
	public $user_secret;

	/**
	 * A list of web sites which the user chose as trust sponsors. An empty list can mean
	 * new user, user chose to not advertise presence on any sites, or a bot
	 */
	public $trust_sponsors;

	/**
	 * The number of sites this identity is bound. This is jsut a number it does not contain specifics
	 */
	public $sites_frequented;

	/**
	 * The average age of the accounts over the sites the identity has an account with
	 */
	public $average_identity_age;

	/**
	 * The maximum age of the accounts over the sites the identity has an account with
	 */
	public $max_identity_age;

	/**
	 * Trust score, as computed by identity +. This is a logarithmic value and cannot be larger than 5.
	 * see trust score analysis on the https://identity.plus/resources/api-best-practices for details
	 */
	public $trust_score;

    /**
     * The total number of trust awarded to this identity + user by the api client 
     */
    public $local_trust;
    
    /**
     * The total number of trust awarded to this identity + user by the api client 
     */
    public $local_intrusions;
	
    /**
     * In case this was an out of band authorization, the id of the certificate that made the authorization
     */
	public $authorizing_certificate;

	/**
	 * Empty initializer, it is necessary to initialize to null the public fields.
	 * The deserializer will override the final modifier and re-initialize the fields
	 * with the proper values
	 */
	public function __construct($data){
		parent::__construct($data);

		if(isset($data->{'local-user-id'})) $this->local_user_name = $data->{'local-user-name'};
		if(isset($data->{'user-secret'})) $this->user_secret = $data->{'user-secret'};
		$this->trust_sponsors = $data->{'trust-sponsors'};
		$this->sites_frequented = $data->{'sites-frequented'};
		$this->average_identity_age = $data->{'average-identity-age'};
		$this->max_identity_age = $data->{'max-identity-age'};
		$this->trust_score = $data->{'trust-score'};
		$this->local_trust = $data->{'local-trust'};
		$this->local_intrusions = $data->{'local-intrusions'};
		if(isset($data->{'authorizing-certificate'})) $this->authorizing_certificate = $data->{'authorizing-certificate'};
	}
}

/**
 * The response for an intent ia a reference token which comes encoded as a JSon object
 * so it can be easily differentiated from an error response
 *
 * @author Stefan Harsan Farr
 */
class Intent_Reference extends API_Response{
	/**
	 * The reference value
	 */
	public $value;
	public $challenge;

	public function __construct($data){
		parent::__construct($data);
		$this->value = $data->{'value'};
		$this->challenge = $data->{'challenge'};
	}
}

/**
 * The type of the intent
 * 
 * @author Stefan Harsan Farr
 */
class Intent_Type {
    /* check if the device has a certificate. This is usually necessary if the site cannot read the certificate itself. No action will be performed if certificate is not found */
    const discover = 'discover';

    /* request this device to be certified. Connect device or sign up for identity plus if necessary. The operation will be performed under the brand of the domain */
    const request = 'request';

    /* request this device to be certified and bind local user to it. Connect device or sign up for identity plus if necessary. The operation will be performed under the brand of the domain */
    const bind = 'bind'; 

	/* attempts to assume ownership of a online service. Identity plus will supply a challenge which it assumes will be available at the domain whose onwership is being assumed */
	const assume_ownership = 'assume-ownership'; 
}

class Intent extends API_Request{
	/**
	 * The type of the intent, can be any of {'discover', 'request', 'bind'}, see Inttent_Type
	 */
	public $type;

	/**
	 * Local user name for the identity plus account to be bound with.
     * If they type is 'bind', this field must be specified
	 */
	public $local_user_name;

	/**
	 * the URL to return to after the operation
	 */
	public $return_url;

	/**
	 * Optionally share personal information with IdentityPlus, to speed up sign up procedure
	 */
	public $name;
	public $email_address;
	public $phone_number;
	public $service_name;

	public function __construct($type, $local_user_name, $name, $email_address, $phone_number, $return_url, $service_name = '') {
	    $this->type = $type;
	    $this->local_user_name = $local_user_name;
	    $this->return_url = $return_url;
	    $this->email_address = $email_address;
	    $this->phone_number = $phone_number;
	    $this->name = $name;
		$this->service_name = $service_name;
	}
}

class Identity_Inquiry extends API_Request{
	/**
	 * The serial number extracted from the identity + certificate of the visitor
	 */
	public $serial_number;

	public function __construct($serial_number) {
		$this->serial_number = $serial_number;
	}
}

class Local_User_Reference extends API_Request{
	/**
	 * The local user name to to refer. This is only available within the context of the requesting API client
	 * which bound the user in the first place
	 */
	public $local_user_name;

	public function __construct($local_user_name) {
		$this->local_user_name = $local_user_name;
	}
}

/**
 * The Intrusion_Reference request is needed for simple intrusion operations that
 * are done on intrusions, such as deletion
 *
 * @author Stefan Harsan Farr
 */
class Intrusion_Reference extends API_Request{
	/**
	 * The intrusion to to refer. This is only available within the context of the requesting API client
	 */
	public $value;

	public function __construct($value) {
		$this->value = $value;
	}
}


class Local_User_Information extends API_Request{
	/**
	 * The anonymous id extracted from the identity + certificate of the visitor.
	 * at this stage identity + is not yet aware of the association, therefore it cannot search for the user name
	 */
	public $serial_number;

	/**
	 * How long is this user a member of the service requesting the association.
	 * the age in days of the local account in other words.
	 */
	public $local_user_age;

	/**
	 * A unique identifier that binds the local user to the identity.
	 * it is recommended you don't use the user name, but rather a different random set of string such as user id
	 * in any case, this information is only be accessible to the service binding the user
	 */
	public $local_user_name;

	/**
	 * The amount of initial trust tokens to associate.
	 * A default of 10 trust points are automatically associated upon user binding.
	 * The maximum amount of trust points that one service can associate with an identity
	 * is 10000.
	 */
	public $tokens_of_trust;

	public function __construct($serial_number, $local_user_name, $local_user_age, $tokes_of_trust = 100) {
		$this->local_user_name = $local_user_name;
		$this->tokens_of_trust = $tokes_of_trust;
		$this->serial_number = $serial_number;
		$this->local_user_age = $local_user_age;
	}
}

class Trust extends API_Request{
	/**
	 * The local user name to to refer. This is only available within the context of the requesting API client
	 * which bound the user in the first place
	 */
	public $local_user_name;

	/**
	 * The anonymous id to refer. This is only available when the Identity + account is not bound to local user
	 */
	public $serial_number;
	
	/**
	 * The amount of trust tokens to associate.
	 * A default of 10 trust points are automatically associated upon user binding.
	 * The maximum amount of trust points that one service can associate with an identity
	 * is 10000.
	 */
	public $tokens_of_trust;

	public function __construct($local_user_name, $serial_number, $tokens_of_trust){
		$this->local_user_name = $local_user_name;
		$this->serial_number = $serial_number;
		$this->tokens_of_trust = $tokens_of_trust;
	}
}


class User_Secret extends API_Request{
	/**
	 * The local user name to to refer. This is only available within the context of the requesting API client
	 * which bound the user in the first place
	 */
	public $local_user_name;

	/**
	 * the secret to associate. This could be a password, an encryption key,etc.
	 */
	public $secret;

	public function __construct($local_user_name, $secret) {
		$this->local_user_name = $local_user_name;
		$this->secret = $secret == null ? "" : $secret;
	}
}


class Intrusion_Report extends API_Request{
	/**
	 * The anonymous id extracted from the identity + certificate of the visitor.
	 */
	public $intruding_certificate_uid;

	/**
	 * The severity of the intrusion, see severity for details
	 */
	public $severity;

	/**
	 * A message which will be delivered to the person who owns the identity associated with the
	 * offending certificate
	 */
	public $message;

	/**
	 * IP address where the request was made from
	 */
	public $intruder_ip_address;

	/**
	 * The url that was accessed during the intrusion
	 */
	public $intruded_url;

	/**
	 * HTTP headers from the intrusion requests
	 */
	public $intruding_request_headers;

	/**
	 * Additional information.
	 */
	public $additional_information;

	public function __construct($offending_certificate_uid, $severity, $message, $request_ip_address, $visited_uri, $request_headers, $additional_information) {
		$this->intruding_certificate_uid = $offending_certificate_uid;
		$this->severity = $severity;
		$this->message = $message;
		$this->intruder_ip_address = $request_ip_address;
		$this->intruded_url = $visited_uri;
		$this->intruding_request_headers = $request_headers;
		$this->additional_information = $additional_information;
	}
}
