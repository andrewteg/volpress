<?php

add_filter( 'single_template', 'get_custom_post_type_single_template' );
function get_custom_post_type_single_template($single_template) {
	 global $post;
	 if ($post->post_type == 'volpress') {
		//get_stylesheet_directory() gets child theme so best for plugin IMHO
		$theme_file = get_stylesheet_directory().'/single-volpress.php'; //echo $theme_file;
		if (file_exists($theme_file)) {
			//echo 'theme';
			$single_template = $theme_file;
		} else {
			//echo 'plugin';
			$single_template = VOLPRESS_PLUGIN_PATH . 'templates/single-volpress.php';
		}
	 }
	 return $single_template;
}


add_filter( 'archive_template', 'get_custom_post_type_archive_template' ) ;
function get_custom_post_type_archive_template( $archive_template ) {
     global $post;
     //custom post type archive
	 if (is_post_type_archive('volpress')) {
		//get_stylesheet_directory() gets child theme so best for plugin IMHO
		$theme_file = get_stylesheet_directory().'/archive-volpress.php'; //echo $theme_file;
		if (file_exists($theme_file)) {
			//echo 'theme';
			$archive_template = $theme_file;
		} else {
			//echo 'plugin';
			$archive_template = VOLPRESS_PLUGIN_PATH . 'templates/archive-volpress.php';
		}
	 }
	 //custom taxonomy (categories) archive
     if (is_tax('volpress-cats')) {
		//get_stylesheet_directory() gets child theme so best for plugin IMHO
		$theme_file = get_stylesheet_directory().'/taxonomy-volpress-cats.php'; //echo $theme_file;
		if (file_exists($theme_file)) {
			//echo 'theme';
			$archive_template = $theme_file;
		} else {
			//echo 'plugin';
			$archive_template = VOLPRESS_PLUGIN_PATH . 'templates/taxonomy-volpress-cats.php';
		}
     }
     //give it back updated for the custom stuff
     return $archive_template;
}
