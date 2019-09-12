<?php namespace identity_plus\api;

if (!defined('Identity +')){
	error_log("File \"".__FILE__."\" was called directly. ");
	exit; // Exit if accessed directly
}

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

class Identity_Plus_Utils{
	const RANDOM_TEXT_POOL = '0123456789ABCDEFGHJKLMNOPQRSTUVXYZabcdefghijkmnopqrstuvxyz~!@#$%^&*()_+=[]{}<>?';
	
	public static function random_text($length){
		$p = '';
		for($i = 0; $i < $length; ++$i) $p .= substr(self::RANDOM_TEXT_POOL, rand(0, strlen(self::RANDOM_TEXT_POOL) -1), 1);
		return $p;
	}
	
	public static function base64url_encode($plainText){
		$base64 = base64_encode($plainText);
		$base64url = strtr($base64, '+/', '-_');
		return ($base64url);
	}
	
	public static function base64url_decode($base64url){
		$base64 = strtr($base64url, '-_', '+/');
		return base64_decode($base64);
	}

	public static function here(){
		$s = $_SERVER;
		
		$ssl = (!empty($s['HTTPS'] ) && $s['HTTPS'] == 'on' );
		$sp = strtolower($s['SERVER_PROTOCOL'] );
		$protocol = substr($sp, 0, strpos( $sp, '/' )) . (( $ssl ) ? 's' : '' );
		$port = $s['SERVER_PORT'];
		$port = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
		$host = isset( $host ) ? $host : $s['SERVER_NAME'] . $port;
		
		$page = $_SERVER['REQUEST_URI'];
		$qpos = strpos($page, "?");
		if($qpos) $page = substr($page, 0, $qpos);
		
		return $protocol.'://'.$host.$page;
	}
	
	public static function query(){
		$query = $_SERVER['REQUEST_URI'];
		$qpos = strpos($query, "?");
		if($qpos) $query = substr($query, $qpos);
		else return "";
		
		return $query;
	}
}