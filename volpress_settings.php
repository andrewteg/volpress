<?php
/*
Description: A simple class based on a tutorial at WP.Tuts that creates an page with metaboxes.
Author: Stephen Harris
Author URI: http://www.stephenharris.info
*/
/*  Copyright 2011 Stephen Harris (contact@stephenharris.info)
 
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
 
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.
 
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/
 
/**
 * If you use this class please make sure you rename it :).  
 *
 * The class takes the following arguments
 * * $hook - the hook of the 'parent' (menu top-level page).
 * * $title - the browser window title of the page
 * * $title - the page title as it appears in the menu
 * * $permissions - the capability a user requires to see the page
 * * $slug - a slug identifier for this page
 * * $body_content_cb -(optional) a callback that prints to the page, above the metaboxes. See the tutorial for more details.
 *
 * Example use
 * $my_admin page = new VolPress_Admin_Page('my_hook','My Admin Page','My Admin Page', 'manage_options','my-admin-page')
 *
 * Full example below the class (which adds example metaboxes too).
*/
 
class VolPress_Admin_Page {
	var $hook;
	var $title;
	var $menu;
	var $permissions;
	var $slug;
	var $page;
 
	/**
	 * Constructor class for the Simple Admin Metabox
	 *@param $hook - (string) parent page hook
	 *@param $title - (string) the browser window title of the page
	 *@param $menu - (string)  the page title as it appears in the menuk
	*@param $permissions - (string) the capability a user requires to see the page
	*@param $slug - (string) a slug identifier for this page
	*@param $body_content_cb - (callback)  (optional) a callback that prints to the page, above the metaboxes. See the tutorial for more details.
	*@param $body_footer_cb - (callback)  (optional) a callback that prints to the page, above the metaboxes. See the tutorial for more details.
	*/
	function __construct($hook, $title, $menu, $permissions, $slug, $body_content_cb='__return_true', $body_footer_cb='__return_true'){
		$this->hook = $hook;
		$this->title = $title;
		$this->menu = $menu;
		$this->permissions = $permissions;
		$this->slug = $slug;
		$this->body_content_cb = $body_content_cb;
		$this->body_footer_cb = $body_footer_cb;
 
		/* Add the page */
		add_action('admin_menu', array($this,'add_page'));
	}
 
 
	/**
	 * Adds the custom page.
	 * Adds callbacks to the load-* and admin_footer-* hooks
	*/
	function add_page(){
 
		/* Add the page */
		$this->page = add_submenu_page($this->hook,$this->title, $this->menu, $this->permissions,$this->slug,  array($this,'render_page'),10);
 
		/* Add callbacks for this screen only */
		add_action('load-'.$this->page,  array($this,'page_actions'),9);
		add_action('admin_footer-'.$this->page,array($this,'footer_scripts'));
	}
 
	/**
	 * Prints the jQuery script to initiliase the metaboxes
	 * Called on admin_footer-*
	*/
	function footer_scripts(){
		?>
		<script> postboxes.add_postbox_toggles(pagenow);</script>
		<?php
	} 
 
	/*
	* Actions to be taken prior to page loading. This is after headers have been set.
		* call on load-$hook
	* This calls the add_meta_boxes hooks, adds screen options and enqueues the postbox.js script.   
	*/
	function page_actions(){
		do_action('add_meta_boxes_'.$this->page, null);
		do_action('add_meta_boxes', $this->page, null);
 
		/* User can choose between 1 or 2 columns (default 2) */
		add_screen_option('layout_columns', array('max' => 2, 'default' => 2) );
 
		/* Enqueue WordPress' script for handling the metaboxes */
		wp_enqueue_script('postbox'); 
	}
 
 
	/**
	 * Renders the page
	*/
	function render_page(){
		?>
		<div class="wrap">
			<?php screen_icon(); ?> 
			<h2> <?php echo esc_html($this->title);?> </h2>
			<form name="my_form" method="post">  
				<?php wp_nonce_field( 'update_volpress_page', 'volpress_nonce' );
 
				/* Used to save closed metaboxes and their order */
				wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
				wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
 
				<div id="poststuff">
					 <div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>"> 
 
						  <div id="post-body-content">
							<?php call_user_func($this->body_content_cb); ?>
						  </div>    
 
						  <div id="postbox-container-1" class="postbox-container">
								<?php do_meta_boxes('','side',null); ?>
						  </div>    
 
						  <div id="postbox-container-2" class="postbox-container">
								<?php do_meta_boxes('','normal',null);  ?>
								<?php do_meta_boxes('','advanced',null); ?>
						  </div>

						  <?php call_user_func($this->body_footer_cb); ?>
 
					 </div> <!-- #post-body -->
				 </div> <!-- #poststuff -->
			</form>			
 
		 </div><!-- .wrap -->
		<?php
	}
 
}
 

/* Usage */
 
	//Create a page
	$VolPress_Settings = new VolPress_Admin_Page('edit.php?post_type=volpress',__('VolPress Settings','domain'),__('Settings','domain'), 'manage_options','settings','volpress_settings_body_content', 'volpress_settings_body_footer');
 
	//Define the body content for the page (if callback is specified above)
	function volpress_settings_body_content() {
		?><p>Here you can set specific VolPress settings. This allows you to change it to suit your needs and will hopefully grow!<p><?php
	}
	function volpress_settings_body_footer() {
		?><input type="submit" class="button-primary" id="volpress_settings_submit" name="Submit" value="Save Changes (All)" /><?php
	}
 
	//Add some metaboxes to the page
	add_action('add_meta_boxes','volpress_settings_metaboxes');
	function volpress_settings_metaboxes($post){ 
		//echo $post;
		add_meta_box('volpress_settings_1','Formatting','volpress_settings_metabox','volpress_page_settings','normal','high', array('section'=>1));
		add_meta_box('volpress_settings_2','Help','volpress_settings_metabox','volpress_page_settings','side','high', array('section'=>2));
	}
 
	//Define the insides of the metabox
	function volpress_settings_metabox($post, $callback){
		switch ($callback['args']['section']) {
			case 1:
				//update as needed
				if (sizeof($_POST) && wp_verify_nonce( $_POST['volpress_nonce'], 'update_volpress_page' )) {
					//printPre($_POST);
					if (isset($_POST['volpress_date_format'])) update_option( 'volpress_date_format', $_POST['volpress_date_format'] ); //date
					if (isset($_POST['volpress_time_format'])) update_option( 'volpress_time_format', $_POST['volpress_time_format'] ); //time
				}

				//show data
				$vp_date = get_option( 'volpress_date_format' );
				$vp_time = get_option( 'volpress_time_format' );
				$date_formats = array(
					'mm/dd/yy' => 'US Default - mm/dd/yy',
					'yy-mm-dd' => 'ISO 8601 - yy-mm-dd',
					'd M, y' => 'Short - d M, y',
					'd MM, y' => 'Medium - d MM, y',
					'DD, d MM, yy' => 'Full - DD, d MM, yy',
				);
				?>
				<script>//jQuery(".postbox").addClass("closed"); //start them all closed if ever needed</script>
				This format is used in the admin section and can optionally be used for the list of events.
				<table class="optiontable form-table">
					<tr>
						<td>Date Format</td>
						<td>
							<select name="volpress_date_format">
								<?php foreach ($date_formats AS $formatVal => $formatText) {
									$selected = ($formatVal == $vp_date) ? 'selected' : '';
									echo '<option value="'.$formatVal.'" '.$selected.'>'.$formatText.'</option>';
								} ?>
							</select>
						</td>
					</tr>
					<tr>
						<td>Time Format</td>
						<td><input type="text" name="volpress_time_format" value="<?php echo $vp_time; ?>" /></td>
					</tr>
				</table>
				<?php
				break;
			case 2:
				echo 'Some Help Contents';
				?>
				<p>
				<strong>Date Formats</strong>
				<blockquote>
				M = Jan-Dec<br />
				MM = January = December<br />
				mm = 01-12<br />
				DD = Sunday-Saturday<br />
				dd = 01-31<br />
				y = 13<br />
				yy = 2013<br />
				</blockquote>
				</p>
				<p><strong>Time Formats</strong>
					<blockquote>Time format follows the Time section of Date Formatting for <a href="http://us2.php.net/manual/en/function.date.php" target="_blank">PHP</a>.</blockquote>
				</p>

				<?php
				break;
		}
	} //volpress_settings_metabox