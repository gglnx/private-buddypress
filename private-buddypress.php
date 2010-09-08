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
 
define('PRIVATE_BUDDYPRESS_VERSION', '1.0');
 
class PrivateBuddyPress {
	var $options;
	var $dbVersion;

	function PrivateBuddyPress() {
		// Load options
		$this->options = get_option('private_buddypress');
		$this->dbVersion = get_option('private_buddypress_version');
		
		// Load textdomain
		load_plugin_textdomain('private-buddypress', 'languages', dirname(plugin_basename(__FILE__)) . '/languages');
		
		// Add admin options
		add_action('admin_init', array($this, 'AdminInit'));
		
		// Add login redirect function
		add_action('template_redirect', array($this, 'LoginRedirect'), 1);
	}
		
	function AdminInit() {
		// Add settings section
		add_settings_section('private-buddypress', __('BuddyPress Protection', 'private-buddypress'), array($this, 'AdminOptions'), 'privacy');
		add_action('load-options.php', array($this, 'SaveAdminOptions'));
	}
		
	function Install() {
		// Check if a existing installation
		if ( PRIVATE_BUDDYPRESS_VERSION == get_option( 'private_buddypress_version' ) )
			return;
	
		// Default options
		$options = new stdClass();
		$options->exclude = new stdClass();
		$options->exclude->homepage = false;
		$options->exclude->registration = false;
			
		// Add or update options to database
		update_option('private_buddypress', $options);
		update_option('private_buddypress_version', PRIVATE_BUDDYPRESS_VERSION);
	}
		
	function LoginRedirect() {
		// Get current position
		$redirect_to = $_SERVER['REQUEST_URI'];
			
		// Check if user is logged in
		if ( false == is_user_logged_in() ):
			// Check if current page is a feed
			if ( is_feed() ):
				// Try to get saved login credentials
				$credentials = array(
					'user_login' => $_SERVER['PHP_AUTH_USER'],
					'user_password' => $_SERVER['PHP_AUTH_PW']
				);

				// Send headers for authentication
				if ( is_wp_error( wp_signon( $credentials ) ) ):
					header('WWW-Authenticate: Basic realm="' . get_option('blogtitle') . '"');
					header('HTTP/1.0 401 Unauthorized');
					die();
				endif;
			// Redirect to login page if for current page a is required
			elseif ( $this->LoginRequired() ):
				wp_redirect(get_option('siteurl') . '/wp-login.php?redirect_to=' . $redirect_to);
				exit;
			endif;
		endif;
	}
		
	function LoginRequired() {
		// No login required if homepage is excluded
		if ( true == $this->options->exclude->homepage && is_front_page() )
			return false;
				
		// No login required if registration is excluded
		if ( true == $this->options->exclude->registration && ( bp_is_register_page() || bp_is_activation_page() ) )
			return false;
			
		// Login required
		return true;
	}
		
	function SaveAdminOptions() {
		// Exclude homepage from protection
		if ( '1' == $_POST["bp_protection_exclude_home"] )
			$this->options->exclude->homepage = true;
				
		// Exclude registration from protection
		if ( '1' == $_POST["bp_protection_exclude_registration"] )
			$this->options->exclude->registration = true;
				
		// Save options
		update_option('private_buddypress', $this->options);
	}
		
	function AdminOptions() {
		// Add admin options
		echo '<table class="form-table">';
		echo '<tr valign="top">';
		echo '<th scope="row">' . __('Exclude from protection', 'private-buddypress') . '</th>';
		echo '<td>';
		echo '<label for="bp_protection_exclude_home"><input name="bp_protection_exclude_home" id="bp_protection_exclude_home" value="1" ' . checked(true, $this->options->exclude->homepage, false) . ' type="checkbox"> ' . __('Front page', 'private-buddypress') . '</label><br>';
		echo '<label for="bp_protection_exclude_registration"><input name="bp_protection_exclude_registration" id="bp_protection_exclude_registration" value="1" ' . checked(true, $this->options->exclude->registration, false) . ' type="checkbox"> ' . __('Registration', 'private-buddypress') . '</label>';
		echo '</td>';
		echo '</tr>';
		echo '</table>';
	}
}

// Add activation hook
register_activation_hook(__FILE__, array('PrivateBuddyPress', 'Install'));

// Init the plugin at WordPress startup
$t = new PrivateBuddyPress();
