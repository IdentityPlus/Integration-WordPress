<?php

/*
   Plugin Name: Identity + 
   Plugin URI: https://wordpress.org/plugins/identity-plus
   Description: Connect your WordPress with Identity + and enable invisible 2 factor authentication, secured SSO, SSL Client Certificate based access on select pages and join the Identity + network of trust where devices and people are anonymousely rated based on how they behave. 
   Version: 1.6.4
   Author: Identity +
   Author URI: http://identity.plus
   License: GPL2
*/

/*
 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

 Copyright 2016 Identity +
 */
if (!defined('ABSPATH')){
	error_log("File \"".__FILE__."\" was called directly. ");
	exit; // Exit if accessed directly
}

define('Identity +',  "OK", false); // call identity + PHP files only from here on

include 'lib/settings_panel.php';
include 'lib/initialize.php';
include 'lib/comments.php';

add_action( 'plugins_loaded', 'identity_plus_initialize' );
add_action( 'wp_footer', 'identity_plus_add_footer', 100 );
add_action( 'admin_footer', 'identity_plus_add_admin_footer', 100 );
add_action('transition_comment_status', 'identity_plus_comment_callback', 10, 3);
add_action('wp_insert_comment', 'identity_plus_comment_inserted', 99, 2);
add_action('wp_logout', 'identity_plus_log_out');

add_filter('comment_form_defaults', 'identity_plus_comment_text');
add_filter( 'preprocess_comment', 'identity_plus_required_to_comment');

register_uninstall_hook(__FILE__, 'identity_plus_unistall');
