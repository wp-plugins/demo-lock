<?php
/*
Plugin Name: Demo Lock
Plugin URI: http://arcsec.ca/
Description: Provides a secure environment in the wp-admin interface enabling users to test plugins in live demos.
Author: Colin Hunt
Author URI: http://arcsec.ca/
Version: 1.0.0
License: GNU General Public License v2.0 or later
License URI: http://opensource.org/licenses/gpl-license.php
*/

/**
Copyright 2013 Colin Hunt (Colin@arcsec.ca)
Portions Copyright 2012  Thomas Griffin  (email : thomas@thomasgriffinmedia.com)

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

define( 'DEMO_PATH', plugin_dir_path(__FILE__) );
include_once( DEMO_PATH . 'config.php' );

/**
 * Demo class for the WordPress plugin.
 *
 * It is a final class so it cannot be extended.
 *
 * @since 1.0.0
 *
 * @package Demo_Lock
 */
final class Demo_Lock {

	/**
	 * Holds user roles information.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	private $role;
	
	/**
	 * Holds config variable information from config.php.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $config;

	/**
	 * Constructor. Loads the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		global $demovars;
		$this->config = $demovars;
		/** Load the class */
		$this->load();
	
	}
	
	/**
	 * Hooks all interactions into WordPress to kickstart the class.
	 *
	 * @since 1.0.0
	 */
	private function load() {
	
		/** Hook everything into plugins_loaded */
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	
	}
	
	/**
	 * In this method, we set any filters or actions and start modifying
	 * our user to have the correct permissions for demo usage.
	 *
	 * @since 1.0.0
	 */
	public function init() {
	
		/** Don't process anything unless the current user is a demo user */
		if ( $this->is_demo_user() ) {

			foreach ($this->config['role'] as $role) {
				/** Setup capabilities for user roles */
				$this->role = get_role( $role );

				/** Add capabilities to the user */
				foreach ( $this->config['allow_capabilites'] as $cap ) {
					if ( ! current_user_can( $cap ) ) {
						$this->role->add_cap( $cap );
					}
				}
			}

			/** Load hooks and filters */
			add_action( 'wp_loaded', array( $this, 'cheatin' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ), 11 );
			add_filter( 'login_redirect', array( $this, 'redirect' ) );
			add_filter( 'login_message', array( $this, 'login_message' ) );
			add_action( 'wp_dashboard_setup', array( $this, 'dashboard' ), 100 );
			add_action( 'admin_menu', array( $this, 'remove_menu_items' ) );
			add_action( 'wp_before_admin_bar_render', array( $this, 'admin_bar' ) );
			add_action( 'admin_footer', array( $this, 'jquery' ) );
			add_filter( 'admin_footer_text', array( $this, 'footer' ) );
			add_action( 'wp_footer', array( $this, 'jquery' ), 1000 );
		}

	}
	
	/**
	 * Make sure users don't try to access an admin page that they shouldn't.
	 *
	 * @since 1.0.0
	 *
	 * @global string $pagenow The current page slug
	 */
	public function cheatin() {
	
		global $pagenow;

		/** Paranoia security to make sure the demo user cannot access any page other than what we specify */

		/** If we find a user is trying to access a forbidden page, redirect them back to the dashboard */
		if (!in_array( $pagenow, $this->config['allow_pages']) || in_array($pagenow, $this->config['plugin_file']) && !($this->is_demo_area()) ) {
			wp_safe_redirect( get_admin_url() );
			exit;
		}	
	}
	
	/**
	 * Remove the ability for users to mess with the screen options panel.
	 *
	 * @since 1.0.0
	 */
	public function admin_init() {
			
		add_filter( 'screen_options_show_screen', '__return_false' );
	
	}
	
	/**
	 * Redirect the user to the Dashboard page upon logging in.
	 *
	 * @since 1.0.0
	 *
	 * @param string $redirect_to Default redirect URL (profile page)
	 * @return string $redirect_to Amended redirect URL (dashboard)
	 */
	public function redirect( $redirect_to ) {
	
		return get_admin_url();
	
	}
	
	/**
	 * Customize the login message with the demo username and password.
	 *
	 * @since 1.0.0
	 *
	 * @param string $message The default login message
	 * @return string $message Amended login message
	 */
	public function login_message( $message ) {

		$message = '<div style="font-size: 15px; margin-left: 8px; text-align: center;">';
		$message .= '<p>In order to gain access to the demo area, use the login credentials below:</p><br />';
		$message .= '<strong>Username: </strong> <span style="color: #cc0000;">' . $this->config['username'] . '</span><br />';
		$message .= '<strong>Password: </strong> <span style="color: #cc0000;">' . $this->config['password'] . '</span><br /><br />';
		$message .= '</div>';
		
		return $message;
	
	}
	
	/**
	 * If the user is not an admin, set the dashboard screen to one column 
	 * and remove the default dashbord widgets.
	 *
	 * @since 1.0.0
	 *
	 * @global string $pagenow The current page slug
	 */
	public function dashboard() {
	
		global $pagenow;
		$layout = get_user_option( 'screen_layout_dashboard', get_current_user_id() );
		wp_add_dashboard_widget('dashboard_widget', $this->config['plugin_title'], 'dashboard_widget_function');
		/** Set the screen layout to one column in the dashboard */
		if ( 'index.php' == $pagenow && 1 !== $layout )
			update_user_option( get_current_user_id(), 'screen_layout_dashboard', 1, true );
			
		/** Remove dashboard widgets from view */
		
		for ($i =0; $i < count($this->config['remove_meta_box']); $i++) {
			remove_meta_box($this->config['remove_meta_box'][$i][0], $this->config['remove_meta_box'][$i][1], $this->config['remove_meta_box'][$i][2]);
		}
	}
	
	/**
	 * Remove certain menu items from view so demo user cannot mess with them.
	 *
	 * @since 1.0.0
	 *
	 * @global array $menu Current array of menu items
	 */
	public function remove_menu_items() {
	
		global $menu;
		end( $menu );
		
		/** Remove the first menu separator */
		unset( $menu[4] );
		
		/** Now remove the menu items we don't want our user to see */
		$remove_menu_items = $this->config['remove_menu'];
	
		while ( prev( $menu ) ) {
			$item = explode( ' ', $menu[key( $menu )][0] );
			if ( in_array( $item[0] != null ? $item[0] : '', $remove_menu_items ) )
				unset( $menu[key( $menu )] );
		}
		foreach($this->config['remove_submenu'] as $key => $val) {
			if (is_array($val)) {
				foreach ($val as $subval) {
					remove_submenu_page($key, $subval);
				}
			} else {
				remove_submenu_page($key, $val);
			}
		}
	}
	
	/**
	 * Modify the admin bar to remove unnecessary links.
	 *
	 * @since 1.0.0
	 *
	 * @global object $wp_admin_bar The admin bar object
	 */
	public function admin_bar() {
	
		global $wp_admin_bar;

		/** Remove admin bar menu items that demo users don't need to see or access */
		foreach ($this->config['remove_node'] as $node) {
			$wp_admin_bar->remove_node($node);
		}
		/**Add custom nodes to admin bar*/
		for ($i=0; $i < count($this->config['add_node']); $i++) {
			$add = $this->config['add_node'][$i];
			$wp_admin_bar->add_node($add);
		}
	}
	
	/**
	 * We can't filter the Profile URL for the main account link in the admin bar, so we
	 * replace it using jQuery instead. We also remove the "+ New" item from the admin bar.
	 *
	 * This method also adds some extra text to spice up the currently empty dashboard area.
	 * Call it plugin marketing if you will. :-)
	 *
	 * @since 1.0.0
	 */
	public function jquery() {
	
		?>
		<script type="text/javascript">
			jQuery(document).ready(function($){
				/** Remove items from the admin bar first */
				$('#wp-admin-bar-my-account a:first').attr('href', '<?php echo get_admin_url(); ?>');
				$('#wp-admin-bar-view').remove();
				
				/** Customize the Dashboard area */
				$('.index-php #normal-sortables').fadeIn('fast', function(){
					/** Change width of the container */
					$(this).css({ 'height' : 'auto' });
				});
			});
		</script>
		<?php
		//hide "theme options" from appearance menu, can't seem to make it disappear any other way
		?>
		<style>.wp-first-item { display:none; }</style>
		<?php
	
	}
	
	/**
	 * Modify the footer text for the demo area.
	 *
	 * @since 1.0.0
	 *
	 * @param string $text The default footer text
	 * @return string $text Amended footer text
	 */
	public function footer( $text ) {
		return $this->config['footer'];
			
	}

	/**
	 * Helper function for determining whether the current user is a demo user or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether or not the user is a demo
	 */
	private function is_demo_user() {
	
		return (bool) ! current_user_can( 'manage_options' );
			
	}
	
	/**
	 * Helper function for determining whether the current page is in the demo area.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Demo area or not
	 */
	private function is_demo_area() {
		$req = $this->config['getvar'];
		foreach ($req as $key => $val) {
			if (!empty($_REQUEST[$key]) && $_REQUEST[$key] === $val) {
				return true;
			}
		}
		return false;
	}

}
// Function that outputs the contents of the dashboard widget
	function dashboard_widget_function() {
		global $demovars;
		echo $demovars['dashboard_text'];
	}
/** Instantiate the class */
$demolock = new Demo_Lock;