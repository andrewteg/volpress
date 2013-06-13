<?php

//register the post type with labels and options etc
function volpress_custom_init() {
		//add volunteer
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
						'menu_name' => 'Vol Events'
				),
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
				'supports' => array( 'title', 'editor', 'author', 'thumbnail' )
		); 
		register_post_type( 'volpress', $args );
}
add_action( 'init', 'volpress_custom_init' );

//add filter to ensure the text Volunteer, or volunteer, is displayed when user updates a volunteer 
function volpress_update_messages( $messages ) {
	global $post, $post_ID;
	$messages['volpress_vol'] = array(
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
add_filter( 'post_updated_messages', 'volpress_update_messages' );

function volpress_custom_help_tab() {
	global $post_ID;
	$screen = get_current_screen();

	if( isset($_GET['post_type']) ) $post_type = $_GET['post_type'];
	else $post_type = get_post_type( $post_ID );

	if( $post_type == 'book' ) :

		$screen->add_help_tab( array(
			'id' => 'volpress_vol_help', //unique id for the tab
			'title' => 'Custom  Help', //unique visible title for the tab
			'content' => '<h3>Help Title</h3><p>Help content coming here soon.</p>',  //actual help text
		));
	endif;
}
add_action('admin_head', 'volpress_custom_help_tab');


/***********************
	 META BOXES
***********************/
add_action( 'add_meta_boxes', 'volpress_cpt_add_box' );		/* Define the custom box */
add_action( 'save_post', 'volpress_save_postdata' );		/* Do something with the data entered */

/* Adds a box to the main column on the Post and Page edit screens */
function volpress_cpt_add_box() {
	add_meta_box('volpress_section_1', 'Tasks', 'volpress_inner_custom_box', 'volpress', 'normal', 'default', array('section'=>1));
	add_meta_box('volpress_section_2', 'Date', 'volpress_inner_custom_box', 'volpress', 'side', 'high', array('section'=>2));
	//add_meta_box('volpress_sectionid', 'My Post Section Title', 'volpress_inner_custom_box', 'volpress');
}

/* Prints the box content */
function volpress_inner_custom_box( $post, $callback ) {
	switch ($callback['args']['section']) {
	case 1:
		wp_nonce_field( plugin_basename( __FILE__ ), 'volpress_noncename' ); 	// Use nonce for verification
		echo 'howdy';
			break;

	case 2:
		echo 'Event Date: <input type="text" name="volpress_new_field[vp_date]" id="vp_date" style="" value="'.get_post_meta($post->ID, '_vp_date', vp_date).'" />';
		echo '<br /><br /><em>Times are set on each individual task so volunteers can better see when they can be expected to arrive and be finished.</em>';
		break;
	}


	// The actual fields for data entry
	/* Use get_post_meta to retrieve an existing value from the database and use the value for the form
	$value = get_post_meta( $post->ID, '_my_meta_value_key', true );
	echo '<label for="volpress_new_field">';
			 _e("Description for this field", 'volpress_textdomain' );
	echo '</label> ';
	echo '<input type="text" id="volpress_new_field" name="volpress_new_field" value="'.esc_attr($value).'" size="25" />';
	*/
}

/* When the post is saved, saves our custom data */
function volpress_save_postdata( $post_id ) {

	// First we need to check if the current user is authorised to do this action. 
	if ( 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) )
				return;
	} else {
		if ( ! current_user_can( 'edit_post', $post_id ) )
				return;
	}

	// Secondly we need to check if the user intended to change this value.
	if ( ! isset( $_POST['volpress_noncename'] ) || ! wp_verify_nonce( $_POST['volpress_noncename'], plugin_basename( __FILE__ ) ) )
			return;

	// Thirdly we can save the value to the database

	//if saving in a custom table, get post_ID
	$post_ID = $_POST['post_ID'];
	//sanitize user input
	$mydata = sanitize_text_field( $_POST['volpress_new_field'] );

	// Do something with $mydata 
	// either using 
	add_post_meta($post_ID, '_my_meta_value_key', $mydata, true) or
		update_post_meta($post_ID, '_my_meta_value_key', $mydata);
	// or a custom table (see Further Reading section below)
}

