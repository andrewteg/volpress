<?php
if(!class_exists('VolPress_CPT')) {
	/**
	 * A PostTypeTemplate class
	 */
	class VolPress_CPT {
		const POST_TYPE	= "volpress";
		//private $_meta	= array('volpress_date');
		
		/**
		 * The Constructor
		 */
		public function __construct() {
			global $wpdb;
			// activate/deactivate/initialize
			add_action('init', array(&$this, 'init'));
			add_action('admin_head', array(&$this, 'plugin_header'));
		} // END public function __construct()

		/**
		 * hook into WP's init action hook
		 */
		public function init() {
			// Create Post Type
			$args = array(
					'labels' => array(
							'name' => 'Volunteer Events',
							'singular_name' => 'Volunteer Event',
							'add_new' => 'Add Volunteer Event',
							'add_new_item' => 'New Volunteer Event',
							'edit_item' => 'Edit Volunteer Event',
							'new_item' => 'New Volunteer Event',
							'all_items' => 'All Volunteer Events',
							'view_item' => 'View Volunteer Event',
							'search_items' => 'Search Volunteer Events',
							'not_found' =>  'No Volunteer Events found',
							'not_found_in_trash' => 'No Volunteer Events found in Trash', 
							'parent_item_colon' => '',
							'menu_name' => 'VolPress'
					),
					//'taxonomies' => array('volpress_cats'),
					'public' => true,
					'publicly_queryable' => true,
					'show_ui' => true, 
					'show_in_menu' => true, 
					'query_var' => true,
					'rewrite' => array( 'slug' => 'volunteer' ),
					'capability_type' => 'post',
					'has_archive' => true, 
					'hierarchical' => false,
					'menu_position' => null,
					'menu_icon' => VOLPRESS_PLUGIN_URL .'images/hand.png', // 16px16
					'supports' => array( 'title', 'editor', 'author', 'thumbnail' ),
					'register_meta_box_cb' => array(&$this, 'add_meta_boxes'),
			); 
			register_post_type(self::POST_TYPE, $args );

			// Add Taxonomy
			$volpress_cat_args = array(
				'hierarchical'      => true,
				'labels'            => array(
					'name'              => _x( 'Categories', 'taxonomy general name' ),
					'singular_name'     => _x( 'Category', 'taxonomy singular name' ),
					'search_items'      => __( 'Search Categories' ),
					'all_items'         => __( 'All Categories' ),
					'parent_item'       => __( 'Parent Category' ),
					'parent_item_colon' => __( 'Parent Category:' ),
					'edit_item'         => __( 'Edit Category' ),
					'update_item'       => __( 'Update Category' ),
					'add_new_item'      => __( 'Add New Category' ),
					'new_item_name'     => __( 'New Category Name' ),
					'menu_name'         => __( 'Categories' ),
				),
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'volunteers' ),
			);
			register_taxonomy( 'volpress_cats', array( 'x', self::POST_TYPE ), $volpress_cat_args );
			//add_rewrite_rule('^volunteer/categories/([^/]*)/([^/]*)/?','index.php?page_id=12&food=$matches[1]&variety=$matches[2]','top');
						//[volunteer/categories/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$] => index.php?taxonomy=volpress_cats&term=$matches[1]&feed=$matches[2]

			// Add Filters to Update Messages and Save Custom Fields, Customize Display, etc.
			add_filter('post_updated_messages', array($this, 'volpress_update_messages'));
			add_action('save_post', array(&$this, 'save_post'));
			
			 //admin table columns
			add_filter('manage_edit-volpress_columns', array(&$this, 'new_columns'));
			add_action('manage_volpress_posts_custom_column', array(&$this, 'manage_columns'), 10, 2);

		} // END public function init()


		function plugin_header() {
			global $post_type;
			if (($_GET['post_type'] == self::POST_TYPE) || ($post_type == self::POST_TYPE)) : ?>
			<style>#icon-edit { background:transparent url('<?php echo VOLPRESS_PLUGIN_URL .'images/hand.png';?>') no-repeat 0px 7px; width:15px; }</style>
			<?php endif;
		}
		/*
		function plugin_header() {
			global $post_type;
			?>
			<style type="text/css" media="screen">
				#menu-posts-POSTTYPE .wp-menu-image {
					background: url('<?php echo VOLPRESS_PLUGIN_URL .'images/hand.png';?>') no-repeat 6px -17px !important;
				}
				#menu-posts-POSTTYPE:hover .wp-menu-image, #menu-posts-POSTTYPE.wp-has-current-submenu .wp-menu-image {
					background-position:6px 7px!important;
				}
			</style>
			<?php
		}*/

		/**
		 * Create the post type
		 */
		public function volpress_update_messages( $messages ) {
			$messages['volpress'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __('Volunteer Event updated. <a href="%s">View volunteer Event</a>', 'your_text_domain'), esc_url( get_permalink($post_ID) ) ),
			2 => __('Custom field updated.', 'your_text_domain'),
			3 => __('Custom field deleted.', 'your_text_domain'),
			4 => __('Volunteer Event updated.', 'your_text_domain'),
			/* translators: %s: date and time of the revision */
			5 => isset($_GET['revision']) ? sprintf( __('Volunteer Event restored to revision from %s', 'your_text_domain'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __('Volunteer Event published. <a href="%s">View Volunteer Event</a>', 'your_text_domain'), esc_url( get_permalink($post_ID) ) ),
			7 => __('Volunteer Event saved.', 'your_text_domain'),
			8 => sprintf( __('Volunteer Event submitted. <a target="_blank" href="%s">Preview volunteer Event</a>', 'your_text_domain'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			9 => sprintf( __('Volunteer Event scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview volunteer Event</a>', 'your_text_domain'),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			10 => sprintf( __('Volunteer Event draft updated. <a target="_blank" href="%s">Preview volunteer</a>', 'your_text_domain'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			);

			return $messages;
		}
	
		/**
		 * Save the metaboxes for this custom post type
		 */
		public function save_post($post_id) {
			global $wpdb;

			//right post type?
			if($_POST['post_type'] == self::POST_TYPE) {
				// Check AutoSave, nonce, and permissions
				if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return; // verify if this is an auto save routine.
				if (!isset( $_POST['volpress_noncename']) || ! wp_verify_nonce($_POST['volpress_noncename'], plugin_basename(__FILE__))) return;  // Secondly we need to check if the user intended to change this value.
				if (!current_user_can('edit_post', $post_id)) return; //check for edit_post ability
				if (!current_user_can('edit_page', $post_id)) return; //check for edit_page ability

				//ok, do it if we're this far along

				//update meta for post
				foreach($_POST[self::POST_TYPE.'_new_field'] as $field_name => $field_val) {
					update_post_meta($post_id, $field_name, $field_val); // Update the post's meta field
				}

				//update custom table data http://codex.wordpress.org/Creating_Tables_with_Plugins
				foreach ($_POST['volpress_task'] AS $task_id => $task) {
					//echo $task_id; printPre($task);
					if ($task['title']) {
						$data_array = array(
								'task_event' => $post_id,
								'task_title' => $task['title'],
								'task_qty_min' => $task['qty_min'],
								'task_qty_max' => $task['qty_max'],
								//'task_time_start' => date('H:i:s', strtotime($task['time_start'])),
								//'task_time_end' => date('H:i:s', strtotime($task['time_end'])),
								'task_sort' => $task['sort'],
						);
						//handle times special to deal with nulls and WPDB issues RE: http://core.trac.wordpress.org/ticket/15158 
						if ($task['time_start']) {
							$data_array['task_time_start'] = date('H:i:s', strtotime($task['time_start']));
						} else {
							$wpdb->query( $wpdb->prepare( "UPDATE ".VOLPRESS_TABLE_TASKS." SET task_time_start = NULL WHERE task_id = %d", $task_id ));
						}
						if ($task['time_end']) {
							$data_array['task_time_end'] = date('H:i:s', strtotime($task['time_end']));
						} else {
							$wpdb->query( $wpdb->prepare( "UPDATE ".VOLPRESS_TABLE_TASKS." SET task_time_end = NULL WHERE task_id = %d", $task_id ));
						}
						// $wpdb->update( $table, $data, $where, $format = null, $where_format = null );						
						$wpdb->update(VOLPRESS_TABLE_TASKS,
							$data_array, 
							array( 'task_id' => $task_id ), 
							array( '%d', '%s', '%d', '%d', '%s', '%s', '%d' ), 
							array( '%d' )
						);
					} else { //delete the task
						$wpdb->query( $wpdb->prepare( "DELETE FROM ".VOLPRESS_TABLE_TASKS." WHERE task_id = %d", $task_id ));
					} //if (task['title'])
				}
				
				if (is_array($_POST['volpress_task_new'])) {
					foreach ($_POST['volpress_task_new'] AS $task_id => $task) {
						//echo $task_id; printPre($task);
						if ($task['title']) {
							$data_array = array(
									'task_event' => $post_id,
									'task_title' => $task['title'],
									'task_qty_min' => $task['qty_min'],
									'task_qty_max' => $task['qty_max'],
									'task_sort' => $task['sort'],
							);
							if ($task['time_start']) $data_array['task_time_start'] = date('H:i:s', strtotime($task['time_start']));
							if ($task['time_start']) $data_array['task_time_end'] = date('H:i:s', strtotime($task['time_end']));
							//printPre($data_array); exit();
							$wpdb->insert(VOLPRESS_TABLE_TASKS,
								$data_array,
								array( '%d', '%s', '%d', '%d', '%s', '%s', '%d' ) 
							);
						}
					}
				}
				//echo '<hr>saving...'.$post_id; printPre($_POST); exit('<hr>save_post<hr>');
			} else {
				return;
			} // if($_POST['post_type'])

		} // END public function save_post($post_id)

		/**
		 * hook into WP's add_meta_boxes action hook
		 */
		public function add_meta_boxes() {
			global $post;
			if( !wp_script_is('jquery-ui') ) { 
				wp_enqueue_script( 'jquery-ui' , 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js' );
				//wp_enqueue_script( 'jquery-ui' );
				wp_register_style( 'jquery-ui-smoothness', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/themes/smoothness/jquery-ui.min.css' );
				wp_enqueue_style( 'jquery-ui-smoothness' );

				//my stuff
				wp_register_style( 'volpress-style', VOLPRESS_PLUGIN_URL.'css/volpress.css' );
				wp_enqueue_style( 'volpress-style' );
				wp_enqueue_script( 'volpress_admin', VOLPRESS_PLUGIN_URL.'scripts/volpress.js' );
				$volpress_script_data = array(
						'ajaxurl' => admin_url( 'admin-ajax.php' ),
						'volpress_plugin_url' => VOLPRESS_PLUGIN_URL, 
						'volpress_plugin_path' => VOLPRESS_PLUGIN_PATH, 
						'post_id' => $post->ID
					);
				wp_localize_script('volpress_admin', 'php_data', $volpress_script_data);

				//timepicker
				wp_register_style( 'jquery-ui-timepicker', VOLPRESS_PLUGIN_URL.'scripts/jquery.ui.timepicker.css' );
				wp_enqueue_style( 'jquery-ui-timepicker' );
				wp_enqueue_script( 'volpress_timepicker', VOLPRESS_PLUGIN_URL.'scripts/jquery.ui.timepicker.js' );
			} 

			// Add this metabox to every selected post
			add_meta_box('volpress_section_1', 'Tasks', array( &$this, 'volpress_inner_custom_box' ), 'volpress', 'normal', 'default', array('section'=>1));
			add_meta_box('volpress_section_2', 'Date', array( &$this, 'volpress_inner_custom_box' ), 'volpress', 'side', 'high', array('section'=>2));
		} // END public function add_meta_boxes()

		public function volpress_inner_custom_box( $post, $callback ) {
			global $wpdb;
			$date_format = get_option( 'volpress_date_format' );
			$time_format = get_option( 'volpress_time_format' );
			switch ($callback['args']['section']) {
			case 1:
				//use nonce for verification
				wp_nonce_field( plugin_basename( __FILE__ ), 'volpress_noncename' );
				//get data
				$strSQL = $wpdb->prepare("SELECT * FROM ".VOLPRESS_TABLE_TASKS." WHERE task_event = %d ORDER BY task_sort, task_id", $post->ID); //echo $strSQL.'<hr>';
				$tasks = $wpdb->get_results($strSQL, ARRAY_A); //printPre($tasks);
				?>
				<table id="volpress_task_table" class="">
					<thead>
						<th>Task</th>
						<th>Min</th>
						<th>Max</th>
						<th>Start</th>
						<th>End</th>
						<th>Sort</th>
					</thead>
					<tbody id="volpress_task_table_body">
				<?php foreach ($tasks AS $task) { ?>
					<tr id="volpress_task_<?php echo $task['task_id']; ?>">
						<td><input type="text" name="volpress_task[<?php echo $task['task_id']; ?>][title]" value="<?php echo $task['task_title']; ?>" class="volpress_title" /></td>
						<td><input type="text" name="volpress_task[<?php echo $task['task_id']; ?>][qty_min]" value="<?php echo $task['task_qty_min']; ?>" class="volpress_qty" /></td>
						<td><input type="text" name="volpress_task[<?php echo $task['task_id']; ?>][qty_max]" value="<?php echo $task['task_qty_max']; ?>" class="volpress_qty" /></td>
						<td><input type="text" name="volpress_task[<?php echo $task['task_id']; ?>][time_start]" value="<?php echo $this->volpress_show_time($task['task_time_start']); ?>" class="volpress_time" /></td>
						<td><input type="text" name="volpress_task[<?php echo $task['task_id']; ?>][time_end]" value="<?php echo $this->volpress_show_time($task['task_time_end']); ?>" class="volpress_time" /></td>
						<td class="volpress_sort_cell"><img src="<?php echo VOLPRESS_PLUGIN_URL.'/images/icon-drag-y.png'; ?>" /><input type="hidden" name="volpress_task[<?php echo $task['task_id']; ?>][sort]" value="<?php echo $task['task_sort']; ?>" class="volpress_sort" /></td>
					</tr>
				<?php } //foreach tasks ?>
				</tbody></table>
				<a class="volpress_add_task">+ New Task</a>
				<p>Leave a Task Title Blank to Remove that Task! ToDo: Remove all signups from that task as well!</p>
				<?php
				break;
			case 2:
				//echo 'DF=>'. $date_format.'|<br>TF=>'.$time_format.'|';
				?>
				<style>
					div.ui-datepicker{ font-size:90%; }
				</style>
				<script>
					jQuery(function() {
						jQuery( "#volpress_date" ).datepicker({ dateFormat: '<?php echo $date_format; ?>' });
					});
				</script>			    
				<?php
				echo 'Event Date: <input type="text" name="volpress_new_field[volpress_date]" id="volpress_date" style="" value="'.get_post_meta($post->ID, 'volpress_date', true).'" />';
				echo '<br /><br /><em>Times are set on each individual task so volunteers can better see when they can be expected to arrive and be finished.</em>';
				break;
			}
		}

		public function volpress_show_time($the_time) {
			$time_format = get_option( 'volpress_time_format' );
			//echo '|'.$the_time.'|';
			if ($the_time) echo date($time_format, strtotime($the_time));
		} //volpress_show_time

		public function new_columns($cols) {
			//take the first two (checkbox + title) + your stuff + the rest
			return array_slice($cols, 0, 2) + array('volpress_date' => 'Event Date') + array_slice($cols, 2);
		}

		public function manage_columns($column_name, $id) {
			global $wpdb;
			switch ($column_name) {
				case 'volpress_date' :
					$volpress_date = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = 'volpress_date' AND post_id = %d;", $id));
					echo $volpress_date;
					break;
				default:
					echo $id;
					break;
			} // end switch

		}

	} // END class VolPress_CPT
} // END if(!class_exists('Post_Type_Template'))


/* Going AJAX crazy?
	Try http://www.garyc40.com/2010/03/5-tips-for-using-ajax-in-wordpress/
		http://wp.smashingmagazine.com/2011/10/18/how-to-use-ajax-in-wordpress/ 
		http://codex.wordpress.org/AJAX_in_Plugins
		searching "wp-load ajax"
*/
/* if both logged in and not logged in users can send this AJAX request,
// add both of these actions, otherwise add only the appropriate one
//add_action( 'wp_ajax_nopriv_myajax-submit', 'myajax_submit' );
add_action( 'wp_ajax_myajax-submit', 'myajax_submit' );
function myajax_submit() {
	global $post;
	//echo 'HERE I AM!!!!';
	// get the submitted parameters
	$postID = $_POST['postID'];
 
	// generate the response
	$response = json_encode( array( 
		'success' => true,
		'test' => 'abc',
	) );
 
	// response output
	header( "Content-Type: application/json" );
	echo $response;
 
	// IMPORTANT: don't forget to "exit"
	exit;
}
*/





/*
 * Replace Taxonomy slug with Post Type slug in url
 * Version: 1.1
 */
function taxonomy_slug_rewrite($wp_rewrite) {
	//printPre($wp_rewrite->rules);
	/*
	$rules = array();
	// get all custom taxonomies
	$taxonomies = get_taxonomies(array('_builtin' => false), 'objects');
	// get all custom post types
	$post_types = get_post_types(array('public' => true, '_builtin' => false), 'objects');
	foreach ($post_types as $post_type) {
		echo '<hr>'.$post_type->labels->name.'<hr>';
		foreach ($taxonomies as $taxonomy) {
			printPre($taxonomy);
			echo $taxonomy->rewrite['slug'];        	
			echo '<hr>';
		 
			// go through all post types which this taxonomy is assigned to
			foreach ($taxonomy->object_type as $object_type) {
				echo $object_type.'|vs|'.$post_type->rewrite['slug'].'<br>';
				 
				// check if taxonomy is registered for this custom type
				if ($object_type == 'volpress') { // $post_type->rewrite['slug']) {
					echo $object_type;
			 
					// get category objects
					$terms = get_categories(array('type' => $object_type, 'taxonomy' => $taxonomy->name, 'hide_empty' => 0));
					printPre($terms);
			 
					// make rules
					foreach ($terms as $term) {
						// $term.'<br>';
						$rules[$object_type . '/' . $term->slug . '/?$'] = 'index.php?' . $term->taxonomy . '=' . $term->slug;
					}
				}
			}
		}
	}
	// merge with global rules
	$wp_rewrite->rules = $rules + $wp_rewrite->rules;
	echo '<hr><hr>';
	printPre($wp_rewrite);
	*/
}
add_filter('generate_rewrite_rules', 'taxonomy_slug_rewrite');