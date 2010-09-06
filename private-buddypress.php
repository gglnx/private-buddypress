<?php
/**
 * Plugin Name: Private BuddyPress
 * Description: Protect your BuddyPress Installation from strangers. Only registered users will be allowed to view the installation.
 * Author: Dennis Morhardt
 * Author URI: http://www.dennismorhardt.de/
 * Plugin URI: http://bp-tutorials.de/
 * Version: 1.0
 * Text Domain: private-buddypress
 * Domain Path: /languages
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301 USA
 */
 
class PrivateBuddyPress {
	function PrivateBuddyPress() {
		add_action('template_redirect', array($this, 'LoginRedirect'));
	}
	
	function LoginRedirect() {
		$redirect_to = $_SERVER['REQUEST_URI'];
		
		if ( false == is_user_logged_in() ):
			if ( is_feed() ):
				$credentials = array();
				$credentials['user_login'] = $_SERVER['PHP_AUTH_USER'];
				$credentials['user_password'] = $_SERVER['PHP_AUTH_PW'];
				$user = wp_signon($credentials);

				if ( is_wp_error( $user ) ):
					header('WWW-Authenticate: Basic realm="' . get_option('blogtitle') . '"');
					header('HTTP/1.0 401 Unauthorized');
					die();
				endif;
			else:
				wp_redirect(get_option('siteurl') . '/wp-login.php?redirect_to=' . $redirect_to);
			endif;
		endif;
	}
	
	function AdminOptions() {
		
	}
}

// Init the plugin at WordPress startup
function PrivateBuddyPress() { $this = new PrivateBuddyPress(); }
add_action('plugins_loaded', 'PrivateBuddyPress');
