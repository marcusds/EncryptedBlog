<?php
/*
Plugin Name: Encrypted Blog
Plugin URI: https://github.com/marcusds/EncryptedBlog
Description: A brief description of the Plugin.
Version: 0.0.3.1
Author: marcusds
Author URI: https://github.com/marcusds
License: GPL2
*/

/*  Copyright 2012  Marcus S.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class encryptblog {

	/**
	 * Filter to replace the [caption] shortcode text with HTML5 compliant code
	 *
	 * @return text HTML content describing embedded figure
	 **/
	function decrypt_content($val) {
		if( isset( $_SESSION['encryption_key'] ) ) {	
			$val = encryptblog::encdec( $val, $_SESSION['encryption_key'] );
		}
		return $val;
	}

	/**
	 * Filter to replace the [caption] shortcode text with HTML5 compliant code
	 *
	 * @return text HTML content describing embedded figure
	 **/
	function encrypt_content( $val ) {
		if( isset( $_SESSION['encryption_key'] ) ) {	
			$val = encryptblog::encdec( $val, $_SESSION['encryption_key'] );
		}
		return $val;
	}

	function encdec( $str, $key = '' ) {
		if ($key == '') {
			return $str;
		}
		$key = str_replace( chr( 32 ), '', $key );
		if( strlen($key) < 8 )
		{
			if( isset( $_SESSION['encryption_key'] ) ) {
				unset( $_SESSION['encryption_key'] );
			}
			return 'key error';
		}
		$kl = strlen( $key ) < 32 ? strlen( $key ) : 32;
		
		$k = array();
		for($i = 0; $i < $kl; $i++) {
			$k[$i] = ord( $key{$i} )&0x1F;
		}
		$j = 0;
		for($i = 0; $i < strlen($str); $i++) {
			$e = ord( $str{$i} );
			$str{$i} = $e&0xE0 ? chr( $e ^ $k[$j] ) : chr( $e );
			$j++;
			$j = $j == $kl ? 0 : $j;
		}
		return $str;
	}

	function start_session() {
		if( ! session_id() ) {
			session_start();
		}
		if( isset( $_POST['encryption_key'] ) ) {
			$_SESSION['encryption_key'] = $_POST['encryption_key'];
		}
	}

	function end_session() {
		session_destroy();
	}	
 
	// @param $template - Full path to the normal template file. 
	function key_get_template($template) { 
		if( is_user_logged_in() && ! isset( $_SESSION['encryption_key'] ) ) {
			return dirname( __FILE__ ) . '/encrypted_blog_form.php';
		} else {
			return $template;
		}
	} 
	
	function login_redirect($redirect_to, $request){
		global $current_user;
		get_currentuserinfo();
		//is there a user to check?
		if(is_array($current_user->roles))
		{
			return home_url(); // We need to always redirect somewhere where it'll check the template so that we can fire key_get_template() and get the user's key.
		}
	}
}

add_filter( 'the_content', array( 'encryptblog', 'decrypt_content' ), 1, 1 );
add_filter( 'content_edit_pre', array( 'encryptblog', 'decrypt_content' ), 1, 1 );
add_filter( 'content_save_pre', array( 'encryptblog', 'encrypt_content' ), 1, 1 );
add_filter( 'template_include', array( 'encryptblog', 'key_get_template' ), 1, 1 );
add_filter( 'init', array( 'encryptblog', 'start_session' ), 1 );
add_filter( 'login_redirect', array( 'encryptblog', 'login_redirect' ), 10, 3 );
add_action( 'wp_logout', array( 'encryptblog', 'end_session' ) );
add_action( 'wp_login', array( 'encryptblog', 'end_session' ) );
?>