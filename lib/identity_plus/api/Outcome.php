<?php namespace identity_plus\api;

if (!defined('Identity +')){
	error_log("File \"".__FILE__."\" was called directly. ");
	exit; // Exit if accessed directly
}

require_once 'API_Action.php';
	
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
 * Possible API call outcomes. Thery are pretty descriptive
 */
class Outcome {
	private static $VALUES = array();

    public $code;
    public $action;
    
    private function __construct($code, $action) {
        $this->code = $code;
        $this->action = $action;
    }
    
    public static function value_of($n){
    	if(count(Outcome::$VALUES) == 0){
    		Outcome::$VALUES['OK_0000_Acknowledged'] = new Outcome("OK_0000", API_Action::Proceed);
    		Outcome::$VALUES['OK_0001_Subject_anonymous_certificate_valid'] = new Outcome("OK_0001", API_Action::Proceed);
    		Outcome::$VALUES['OK_0002_Subject_anonymous_certificate_valid_and_user_uid_associated'] = new Outcome("OK_0002", API_Action::Proceed);
    		
    		Outcome::$VALUES['OK_0100_Subject_user_successfully_associated_to_Identity_Plus_identity'] = new Outcome("OK_0100", API_Action::Proceed);
    		Outcome::$VALUES['OK_0101_Subject_user_disassociated'] = new Outcome("OK_0101", API_Action::Proceed);
    		Outcome::$VALUES['OK_0102_Subject_user_updated'] = new Outcome("OK_0102", API_Action::Proceed);
    		Outcome::$VALUES['OK_0103_Identity_Plus_certificate_found_via_legacy_method'] = new Outcome("OK_0103", API_Action::Proceed);
    		
    		Outcome::$VALUES['PB_0000_No_Identity_Plus_anonymous_certificate'] = new Outcome("PB_0000", API_Action::Ask);
    		Outcome::$VALUES['PB_0001_No_Identity_Plus_certificate_found_via_legacy_method'] = new Outcome("PB_0001", API_Action::Ask);
    		Outcome::$VALUES['PB_0002_Expired_Identity_Plus_anonymous_certificate'] = new Outcome("PB_0002", API_Action::Redirect);
    		Outcome::$VALUES['PB_0003_Identity_Plus_anonymous_certificate_needs_validation'] = new Outcome("PB_0003", API_Action::Redirect);
    		
    		Outcome::$VALUES['PB_0004_Revoked_Identity_Plus_anonymous_certificate'] = new Outcome("PB_0004", API_Action::Evasive_Maneuver);
    		Outcome::$VALUES['PB_0005_Intruder_Certificate'] = new Outcome("PB_0005", API_Action::Evasive_Maneuver);
    		Outcome::$VALUES['PB_0006_Unknown_Identity_Plus_anonymous_certificate'] = new Outcome("PB_0006", API_Action::Evasive_Maneuver);
    		Outcome::$VALUES['PB_0007_Crypto_Failure_package_was_tempered_with'] = new Outcome("PB_0007", API_Action::Evasive_Maneuver);
    		
    		Outcome::$VALUES['ER_0000_Undetermined_error'] = new Outcome("ER_0000", API_Action::None);
    		Outcome::$VALUES['ER_0001_Unknown_request_error'] = new Outcome("ER_0001", API_Action::None);
    		Outcome::$VALUES['ER_0002_No_such_operation_for_object'] = new Outcome("ER_0002", API_Action::None);
    		Outcome::$VALUES['ER_0003_Subject_user_name_is_already_associated'] = new Outcome("ER_0003", API_Action::None);
    		Outcome::$VALUES['ER_0004_Subject_user_name_is_already_associated_to_a_different_identity'] = new Outcome("ER_0004", API_Action::None);
    		Outcome::$VALUES['ER_0005_Subject_user_name_was_never_associated'] = new Outcome("ER_0005", API_Action::None);
    		Outcome::$VALUES['ER_0006_Return_URL_is_too_long'] = new Outcome("ER_0006", API_Action::None);
    		Outcome::$VALUES['ER_0007_An_intrusion_was_already_reported_on_this_certificate'] = new Outcome("ER_0007", API_Action::None);
    		
    		Outcome::$VALUES['ER_1100_No_Identity_Plus_API_certificate_presented'] = new Outcome("ER_1100", API_Action::Fix_API_Problem);
    		Outcome::$VALUES['ER_1101_Unknown_Identity_Plus_API_certificate'] = new Outcome("ER_1101", API_Action::Evasive_Maneuver);
    		Outcome::$VALUES['ER_1102_Expired_Identity_Plus_API_certificate'] = new Outcome("ER_1102", API_Action::Fix_API_Problem);
    		Outcome::$VALUES['ER_1103_Revoked_Identity_Plus_API_certificate'] = new Outcome("ER_1103", API_Action::Fix_API_Problem);
    		Outcome::$VALUES['ER_1104_Number_of_connectable_identities_exceeded'] = new Outcome("ER_1104", API_Action::Fix_API_Problem);
    		Outcome::$VALUES['ER_1105_Suspended_Identity_Plus_API_certificate'] = new Outcome("ER_1105", API_Action::Fix_API_Problem);
    		Outcome::$VALUES['ER_1106_General_Identity_Plus_API_Problem'] = new Outcome("ER_1106", API_Action::Fix_API_Problem);
  		}
    	
  		return Outcome::$VALUES[str_replace(' ', '_', $n)];
    }
}
