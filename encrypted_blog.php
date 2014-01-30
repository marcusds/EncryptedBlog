<?php
/*
Plugin Name: Encrypted Blog
Plugin URI: https://github.com/marcusds/EncryptedBlog
Description: Encrypts blog posts so that even with access to the WordPress database your posts will be private.
Version: 0.0.7
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

include 'GibberishAES.php';

class encryptblog {

	/**
	 * Decrypts and returns content
	 * @param string $val Content coming from wordpress
	 * @return text Decrypted content
	 **/
	function decrypt_content( $val ) {
		$val = encryptblog::decrypt( $val );
		return $val;
	}

	/**
	 * Encrypts our content
	 * @param string $content Content to encrypt
	 * @param string $key Key to encrypt against
	 * @return boolean|string
	 */
	function encrypt( $content, $key ) {
		GibberishAES::size(256);
		return GibberishAES::enc($content, $key);
	}

	/**
	 * Decrypts our content. Set data in wrapper, if does not decrypt in JS give button for OLD decrypt.
	 * @param string $content Content to decrypt
	 * @param string $key Key to decrypt against
	 * @parama boolean $falseonerror return false on error, true on success
	 * @return string Error on fail, content on success
	 */
	function decrypt( $content, $key = false, $falseonerror = false ) {
		global $post;
		$content = get_the_content();
		$nonce = wp_create_nonce('update_post_nonce');
		$link = admin_url('admin-ajax.php');

		return '<div class="encDataStore" data-enc="'.$content.'"><p class="encblo-error" style="display:none;">Incorrect encryption key or encrypted with old method.<br>
				<a class="decrypt-old-post" data-nonce="'.$nonce.'" data-post_id="'.$post->ID.'" href="#">Click here to decrypt the post </a>, you can re-encrypt it under the new method after.</p></div>';
	}
	
	/**
	 * Decrypts our content permantly when they are in the old format.
	 * @param string $content Content to decrypt
	 * @param string $key Key to decrypt against
	 * @parama boolean $falseonerror return false on error, true on success
	 * @return string Error on fail, content on success
	 */
	function decryptOldPosts( $content, $key, $falseonerror = false ) {
	
		$keyhash = hash( 'SHA256', $key, true );
		$iv = base64_decode( substr( $content, 0, 22 ) . '==' );
		if ( empty( $iv ) ) {
			if( $falseonerror == true ) {
				return false;
			}

			return $content;
		}
		$encrypted = substr( $content, 22 );
		
		$decrypted = @mcrypt_decrypt( MCRYPT_RIJNDAEL_128, $keyhash, base64_decode( $encrypted ), MCRYPT_MODE_CBC, $iv );
		
		$decrypted = rtrim( $decrypted, "\0\4" );
		$hash = substr( $decrypted, -32 );
		$decrypted = substr( $decrypted, 0, -32 );
		
		if ( md5($decrypted) != $hash ) {
			if( $falseonerror == true ) {
				return false;
			}
			return $content;
		}
		return $decrypted;
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
	 * Users who are not logged in won't able to see the blog's title.
	 * @return string 
	 */
	function hide_title( $array ) {
		if( ! is_user_logged_in() ) {
			return 'Log in required';
		} else {
			return $array;
		}
	}	
	
	/**
	 * Decrypt old post via AJAX.
	 */
	public function callback_decrypt() {
		global $wpdb; // this is how you get access to the database
		if ( current_user_can( 'manage_options' ) ) {
			if ( !wp_verify_nonce( $_REQUEST['nonce'], 'update_post_nonce')) {
				exit("Invalid nonce.");
			}
			
			$post_id = $_REQUEST['post_id'];
			$key = $_REQUEST['key'];
			
			$post = get_post($post_id);
			$content = $post->post_content;
			$content = encryptblog::decryptOldPosts($content, $key, true);

			if($content) {
				$post = array(
					'ID'           => $post_id,
					'post_content' => $content
				);
				$id = wp_update_post( $post );
				if(!$id) {
					die('0');
				} else {
					return 'true';
				}
			} else {
				die('0');
			}
		
			die(); // this is required to return a proper result
		}
		die ('0');
	}
	
	/**
	 * Loads Javascript for the plugin. The Javascript handles client side decryption.
	 */
	function setup_scripts() {
		wp_enqueue_script( 'gibberish-aes', plugins_url() . '/EncryptedBlog/gibberish-aes-1.0.0.min.js', array(), '1.0.0', false );
		wp_enqueue_script( 'sessionstorage', plugins_url() . '/EncryptedBlog/sessionstorage.min.js', array(), '1.4', false ); // sessionStorage for all browsers cause cookies are passable.
		wp_register_script( 'EB_dec', plugins_url() . '/EncryptedBlog/dec.js', array( 'jquery', 'sessionstorage' ), '0.0.7', false );
		wp_localize_script( 'EB_dec', 'EB_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
		wp_enqueue_script( 'EB_dec' );
	}
	/**
	 * Loads Javascript for the plugin in the admin panel.
	 */
	function setup_admin_scripts() {
		if(is_admin()) {
			wp_enqueue_script( 'gibberish-aes', plugins_url() . '/EncryptedBlog/gibberish-aes-1.0.0.min.js', array(), '1.0.0', false );
			wp_enqueue_script( 'jquery.cookie', plugins_url() . '/EncryptedBlog/jquery.cookie.js', array( 'jquery' ), '1.4.0', false );
			wp_enqueue_script( 'EB_admin', plugins_url() . '/EncryptedBlog/admin.js', array( 'jquery', 'jquery.cookie' ), '0.0.7', false );
		}
	}

	/**
	 * Disables auto save since that just causes havok with the encrypting.
	 */
	function disableautosave() {
		wp_deregister_script('autosave');
	}
}

// Setup filters & actions.
add_filter( 'the_content', array( 'encryptblog', 'decrypt_content' ), 1, 1 );
//1add_action( 'template_redirect', array('encryptblog', 'must_be_logged_in' ) );
add_action( 'bloginfo', array( 'encryptblog', 'hide_title') , 10, 1 );
add_action( 'wp_enqueue_scripts', array( 'encryptblog', 'setup_scripts') );
add_action( 'admin_enqueue_scripts', array( 'encryptblog', 'setup_admin_scripts') );
add_action( 'wp_ajax_eb_decrypt', array( 'encryptblog', 'callback_decrypt') );
add_action( 'wp_print_scripts', array( 'encryptblog', 'disableautosave') );

// Remove feeds - they won't be decrypted, so there is no point in having them. They are just another potential hole. I may provide a way in the future to decrypt feeds, but it'll be far down the list because I think it's silly.
remove_action( 'do_feed_rdf', 'do_feed_rdf', 10, 1 );
remove_action( 'do_feed_rss', 'do_feed_rss', 10, 1 );
remove_action( 'do_feed_rss2', 'do_feed_rss2', 10, 1 );
remove_action( 'do_feed_atom', 'do_feed_atom', 10, 1 );
?>