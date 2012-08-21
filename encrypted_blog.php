<?php
/*
Plugin Name: Encrypted Blog
Plugin URI: https://github.com/marcusds/EncryptedBlog
Description: A brief description of the Plugin.
Version: 0.0.1
Author: marcusds
Author URI: https://github.com/marcusds
License: GPL2
*/

/*  Copyright 2012  Marcus Schwab

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

add_filter('the_content', 'decrypt_content',1,1);
add_filter('content_edit_pre', 'decrypt_content',1,1);

/**
 * Filter to replace the [caption] shortcode text with HTML5 compliant code
 *
 * @return text HTML content describing embedded figure
 **/
function decrypt_content($val)
{
    $key = "This is a very secret key";
	$val = encdec($val, $key);
	return $val;
}

add_filter('content_save_pre', 'encrypt_content',1,1);

/**
 * Filter to replace the [caption] shortcode text with HTML5 compliant code
 *
 * @return text HTML content describing embedded figure
 **/
function encrypt_content($val)
{
    $key = "This is a very secret key";
	$val = encdec($val, $key);
	return $val;
}

function encdec($str,$ky='')
{
	if($ky=='')return $str;
	
	$ky = str_replace(chr(32),'',$ky);
	if(strlen($ky)<8)
	{
		exit('key error');
	}
	$kl = strlen($ky )<32?strlen($ky):32;
	
	$k = array();
	for($i=0;$i<$kl;$i++)
	{
		$k[$i] = ord($ky{$i})&0x1F;
	}
	$j = 0;
	for($i=0;$i<strlen($str);$i++)
	{
		$e = ord($str{$i});
		$str{$i}=$e&0xE0?chr($e^$k[$j]):chr($e);
		$j++;
		$j=$j==$kl?0:$j;
	}
	return $str;
}
?>