<?php
/**
 * Theme functions and definitions of the child themes
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


add_action( 'init', 'mtrap_custom_post_type_registration' );
/**
 * Add custom taxonomies.
 */
function mtrap_custom_post_type_registration() {

	$labels = array(
		'name'               => _x( 'Destinations', 'Post Type General Name', 'winger' ),
		'singular_name'      => _x( 'Destination', 'Post Type Singular Name', 'winger' ),
		'menu_name'          => __( 'Destination', 'winger' ),
		'parent_item_colon'  => __( 'Parent destination', 'winger' ),
		'all_items'          => __( 'All destinations', 'winger' ),
		'view_item'          => __( 'View destination', 'winger' ),
		'add_new_item'       => __( 'Add new destination', 'winger' ),
		'add_new'            => __( 'Add new', 'winger' ),
		'edit_item'          => __( 'Edit destination', 'winger' ),
		'update_item'        => __( 'Update destination', 'winger' ),
		'search_items'       => __( 'Search destination', 'winger' ),
		'not_found'          => __( 'Not Found', 'winger' ),
		'not_found_in_trash' => __( 'Not found in Trash', 'winger' ),
	);

	// Set other options for Custom Post Type.

	$args = array(
		'label'               => __( 'Destination', 'winger' ),
		'description'         => __( 'Destination news and reviews', 'winger' ),
		'labels'              => $labels,
		// Features this CPT supports in Post Editor.
		'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'revisions', 'custom-fields' ),

		/*
		A hierarchical CPT is like Pages and can have
		* Parent and child items. A non-hierarchical CPT
		* is like Posts.
		*/
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 5,
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => false,
		'publicly_queryable'  => false,
		'capability_type'     => 'post',
		'show_in_rest'        => true,
	);

	// Register post type.
	register_post_type( 'destinations', $args );
}
