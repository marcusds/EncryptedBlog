<?php
/*
Plugin Name: Encrypted Blog
Plugin URI: https://github.com/marcusds/EncryptedBlog
Description: Encrypts blog posts so that even with access to the WordPress database your posts will be private.
Version: 0.0.4
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
	 * Decrypts and returns content
	 * @param string $val Content coming from wordpress
	 * @return text Decrypted content
	 **/
	function decrypt_content( $val ) {
		if( isset( $_SESSION['encryption_key'] ) ) {	
			$val = encryptblog::encdec( $val, $_SESSION['encryption_key'] );
		}
		return $val;
	}

	/**
	 * Encrypts and returns content
	 * @param string $val Content coming from wordpress
	 * @return text Encrypted content
	 **/
	function encrypt_content( $val ) {
		if( isset( $_SESSION['encryption_key'] ) ) {	
			$val = encryptblog::encdec( $val, $_SESSION['encryption_key'] );
		}
		return $val;
	}

	/**
	 * Decrypts and encrypts content against string 
	 * @param string $str String to encrypt
	 * @param string $key Key to encrypt against
	 * @return string Decrypted or encrypted string
	 */
	function encdec( $str, $key = '' ) { // TODO Replace with a much more secure encryption system.
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

	/**
	 * Starts a session in WordPress
	 */
	function start_session() {
		if( ! session_id() ) {
			session_start();
		}
		if( isset( $_POST['encryption_key'] ) ) {
			$_SESSION['encryption_key'] = $_POST['encryption_key'];
		}
	}

	/**
	 * Ends a session in WordPress
	 */
	function end_session() {
		session_destroy();
	}	
 
	/**
	 * When no key is set show form asking for one.
	 * @param $template - Full path to the normal template file. 
	 */
	function key_get_template( $template ) {		
		if( is_user_logged_in() && ! isset( $_SESSION['encryption_key'] ) ) {
			return dirname( __FILE__ ) . '/encrypted_blog_form.php';
		} else {
			return $template;
		}
	} 
	
	/**
	 * Redirects users always to homepage, never to wp-admin. We need to do so we can force them to enter a key.
	 * @param string $redirect_to
	 * @param string $request
	 */
	function login_redirect( $redirect_to, $request ) {
		if( strpos($redirect_to, 'wp-admin') !== false ) {
			return home_url();
		} else {
			return $redirect_to;
		}
	}
	
	/**
	 * Redirect non-logged in users to the log in page.
	 */
	function must_be_logged_in() {
		if( ! is_user_logged_in() ) {
			if( is_front_page() ) {
				wp_safe_redirect( wp_login_url(), 302 ); // Using safe redirect so users don't get redirected to 3rd party site to steal encryption key.
			} else {
				wp_safe_redirect( wp_login_url( get_permalink() ), 302 );
			}
			exit;
		}
	}
	
	/**
	 * Redirects user to homepage, but with redirect url so we can go there after getting the key.
	 * @param unknown_type $user_login
	 * @param unknown_type $user
	 */
	function setup_redirect() {
		wp_safe_redirect( get_site_url().'?redirect_to='.urlencode($_POST['redirect_to']), 302 );
		exit;
	}
	
	/**
	 * Users who are not logged in won't able to see the blog's title.
	 * @return string 
	 */
	function hide_title() {
		if( ! is_user_logged_in() ) {
			return 'Log in required';
		}
	}
}

// Setup filters.
add_filter( 'the_content', array( 'encryptblog', 'decrypt_content' ), 1, 1 );
add_filter( 'content_edit_pre', array( 'encryptblog', 'decrypt_content' ), 1, 1 );
add_filter( 'content_save_pre', array( 'encryptblog', 'encrypt_content' ), 1, 1 );
add_filter( 'template_include', array( 'encryptblog', 'key_get_template' ), 1, 1 );
add_filter( 'init', array( 'encryptblog', 'start_session' ), 1 );
add_filter( 'login_redirect', array( 'encryptblog', 'login_redirect' ), 10, 3 );
add_action( 'template_redirect', array('encryptblog', 'must_be_logged_in' ) );
add_action( 'wp_logout', array( 'encryptblog', 'end_session' ) );
add_action( 'wp_login', array( 'encryptblog', 'end_session' ) );
add_action( 'wp_login', array( 'encryptblog', 'setup_redirect' ), 10 );
add_action( 'bloginfo', array( 'encryptblog', 'hide_title') );

// Remove feeds - they won't be decrypted, so there is no point in having them. They are just another potential hole. I may provide a way in the future to decrypt feeds, but it'll be far down the list because I think it's silly.
remove_action( 'do_feed_rdf', 'do_feed_rdf', 10, 1 );
remove_action( 'do_feed_rss', 'do_feed_rss', 10, 1 );
remove_action( 'do_feed_rss2', 'do_feed_rss2', 10, 1 );
remove_action( 'do_feed_atom', 'do_feed_atom', 10, 1 );
?>