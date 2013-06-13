<?php
/*
Plugin Name: VolunteerPress
Plugin URI: http://www.andrewteg.com
Description: An online volunteer manager.
Version: 1.0
Author: Andrew Tegenkamp
Author URI: http://www.andrewteg.com/
License: GPL2
*/
/*
Copyright 2013  Andrew Tegenkamp  (email : andrewteg@gmail.com)

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

if(!class_exists('VolPress')) {
	class VolPress {
		/**
		 * Construct the plugin object
		 */
		public function __construct() {
			global $wpdb;
			//setup plugin vars
			define('VOLPRESS_PLUGIN_URL',  plugin_dir_url( __FILE__ ));
			define('VOLPRESS_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
			define('VOLPRESS_TABLE_TASKS', $wpdb->prefix."volpress_tasks");
			//if (is_admin()) echo VOLPRESS_PLUGIN_URL.'<br>'.VOLPRESS_PLUGIN_PATH; //show above on admin side only for debug

			// Register custom post types
			include_once(VOLPRESS_PLUGIN_PATH.'/volpress_cpt.php');
			$VolPress_CPT = new VolPress_CPT();

			//add admin pages as needed 
			if( is_admin() ) {
				include_once(VOLPRESS_PLUGIN_PATH.'/volpress_settings.php');
				//$VolPress_Settings = new VolPress_Settings();
			} else {
				include_once(VOLPRESS_PLUGIN_PATH.'/volpress_templates.php');
			}

		} // END public function __construct
		
		/**
		 * Activate the plugin
		 */
		public static function activate() {
			// Database Tables
			$sql = "CREATE TABLE ".VOLPRESS_TABLE_TASKS." (
				task_id int(11) unsigned NOT NULL AUTO_INCREMENT,
				task_event int(11) DEFAULT NULL,
				task_title varchar(255) DEFAULT NULL,
				task_qty_min int(11) DEFAULT NULL,
				task_qty_max int(11) DEFAULT NULL,
				task_time_start time DEFAULT NULL,
				task_time_end time DEFAULT NULL,
				task_sort int(11) DEFAULT NULL,
				task_mod timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`task_id`)
			);";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
			
			/* Add custom role and capability
			add_role('signup_sheet_manager', 'Sign-up Sheet Manager');
			$role = get_role('signup_sheet_manager');
			if (is_object($role)) {
				$role->add_cap('manage_signup_sheets');
				$role->add_cap('read');
			}
			$role = get_role('administrator');
			if (is_object($role)) {
				$role->add_cap('manage_signup_sheets');
			}
			*/
		} // END public static function activate
	
		/**
		 * Deactivate the plugin
		 */		
		public static function deactivate() {
			/* Remove custom role and capability
			$role = get_role('signup_sheet_manager');
			if (is_object($role)) {
				$role->remove_cap('manage_signup_sheets');
				$role->remove_cap('read');
				remove_role('signup_sheet_manager');
			}
			$role = get_role('administrator');
			if (is_object($role)) {
				$role->remove_cap('manage_signup_sheets');
			}
			*/
		} // END public static function deactivate
	} // END class VolPress
} // END if(!class_exists('VolPress'))

if(class_exists('VolPress')) {
	// Installation and uninstallation hooks
	register_activation_hook(__FILE__, array('VolPress', 'activate'));
	register_deactivation_hook(__FILE__, array('VolPress', 'deactivate'));

	// instantiate the plugin class
	$volPress = new VolPress();
	
	// Add a link to the settings page onto the plugin page
	if(isset($volPress)) {
		// Add the settings link to the plugins page
		function volpress_settings_link($links) { 
			$settings_link = '<a href="edit.php?post_type=volpress&page=settings">Settings</a>';
			array_unshift($links, $settings_link); 
			return $links; 
		}

		$plugin = plugin_basename(__FILE__); 
		add_filter("plugin_action_links_$plugin", 'volpress_settings_link');
	}
}

if (!function_exists("printPre")) {
	function printPre($var) { echo '<PRE>'; print_r($var); echo '</PRE>'; }
}