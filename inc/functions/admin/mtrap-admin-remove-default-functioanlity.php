<?php
/**
 * Theme functions to remove unused functioanlities of the woocoomerce plugin
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


add_action( 'init', 'mtrap_unregister_default_taxonomies' );
/**
 * Remove default woocommerce post types.
 */
function mtrap_unregister_default_taxonomies() {
	unregister_taxonomy_for_object_type( 'product_cat', 'product' );
	unregister_taxonomy_for_object_type( 'product_tag', 'product' );
}


add_action( 'add_meta_boxes_product', 'mtrap_remove_metaboxes_edit_product_screen', 9999 );
/**
 * Remove metaboxes form edit product screen.
 */
function mtrap_remove_metaboxes_edit_product_screen() {
	remove_meta_box( 'postexcerpt', 'product', 'normal' );
	remove_meta_box( 'woocommerce-product-data', 'product', 'normal' );
	remove_meta_box( 'tagsdiv-product_tag', 'product', 'side' );
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
}


add_action( 'admin_menu', 'mtrap_change_admin_label_of_products_menu_item' );
/**
 * Remove metaboxes form edit product screen.
 */
function mtrap_change_admin_label_of_products_menu_item() {
	global $menu;
	foreach ( $menu as $key => $item ) {
		if ( $item[2] === 'edit.php?post_type=product' ) {
			$menu[ $key ][0] = 'Transport';
			$menu[ $key ][6] = 'dashicons-tickets-alt';
			break;
		}
	}
}


add_action( 'admin_menu', 'mtrap_hide_woocommerce_menus', 100 );
/**
 * Remove metaboxes form edit product screen.
 */
function mtrap_hide_woocommerce_menus() {
	// Hide attributes functionality.
	remove_submenu_page( 'edit.php?post_type=product', 'product_attributes' );

	// Hide Reviews functionality.
	remove_submenu_page( 'edit.php?post_type=product', 'product-reviews' );
}


add_filter( 'hidden_meta_boxes', 'mtrap_hide_unwanted_meta_boxes' );
/**
 * Hides extra metaboxes.
 *
 * @param string $hidden The hidden.
 */
function mtrap_hide_unwanted_meta_boxes( $hidden ) {
	$hidden[] = 'bus-typesdiv';
	$hidden[] = 'bus-stopsdiv';
	$hidden[] = 'slider_revolution_metabox';
	$hidden[] = 'eg-meta-box';
	$hidden[] = 'commentsdiv';
	return $hidden;
}


add_action( 'init', 'mtrap_deregister_unwanted_posttypes_taxonomies', 100 );
/**
 * Deregisters custom post types & taxonomies.
 */
function mtrap_deregister_unwanted_posttypes_taxonomies() {
	// Unregister Courses.
	unregister_post_type( 'cpt_courses' );
	unregister_taxonomy_for_object_type( 'cpt_courses_group', 'cpt_courses' );

	// Unregister Team.
	unregister_post_type( 'cpt_team' );
	unregister_taxonomy_for_object_type( 'cpt_team_group', 'cpt_team' );
}


add_action(
	'admin_init',
	function () {
		// Redirect any user trying to access comments page
		global $pagenow;

		if ( $pagenow === 'edit-comments.php' ) {
			wp_safe_redirect( admin_url() );
			exit;
		}

		// Disable support for comments and trackbacks in post types
		foreach ( get_post_types() as $post_type ) {
			if ( post_type_supports( $post_type, 'comments' ) ) {
				remove_post_type_support( $post_type, 'comments' );
				remove_post_type_support( $post_type, 'trackbacks' );
			}
		}
	}
);

// Close comments on the front-end
add_filter( 'comments_open', '__return_false', 20, 2 );
add_filter( 'pings_open', '__return_false', 20, 2 );

// Hide existing comments
add_filter( 'comments_array', '__return_empty_array', 10, 2 );

// Remove comments page in menu
add_action(
	'admin_menu',
	function () {
		remove_menu_page( 'edit-comments.php' );
	}
);

// Remove comments links from admin bar
add_action(
	'init',
	function () {
		if ( is_admin_bar_showing() ) {
			remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
		}
	}
);
