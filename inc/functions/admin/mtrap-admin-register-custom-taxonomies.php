<?php
/**
 * Theme functions and definitions of the child themes
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


add_action( 'init', 'mtrap_custom_taxonomies_registration' );
/**
 * Add custom taxonomies.
 */
function mtrap_custom_taxonomies_registration() {

	// Labels for Transport stop taxonomy.
	$mtrap_labels_busstops_tax = array(
		'name'                       => __( 'Transport Stops' ),
		'singular_name'              => __( 'Transport Stop' ),
		'menu_name'                  => __( 'Transport Stops' ),
		'all_items'                  => __( 'All Transport Stops' ),
		'parent_item'                => __( 'Parent Transport Stop' ),
		'parent_item_colon'          => __( 'Parent Transport Stop:' ),
		'new_item_name'              => __( 'New Transport Stop Name' ),
		'add_new_item'               => __( 'Add New Transport Stop' ),
		'edit_item'                  => __( 'Edit Transport Stop' ),
		'update_item'                => __( 'Update Transport Stop' ),
		'separate_items_with_commas' => __( 'Separate Transport Stop with commas' ),
		'search_items'               => __( 'Search Transport Stops' ),
		'add_or_remove_items'        => __( 'Add or remove Transport Stops' ),
		'choose_from_most_used'      => __( 'Choose from the most used Transport Stops' ),
	);

	// Labels for bus types taxonomy.
	$mtrap_labels_bus_types_tax = array(
		'name'                       => __( 'Transport Types' ),
		'singular_name'              => __( 'Transport Type' ),
		'menu_name'                  => __( 'Transport Types' ),
		'all_items'                  => __( 'All Transport Types' ),
		'parent_item'                => __( 'Parent Transport Type' ),
		'parent_item_colon'          => __( 'Parent Transport Type:' ),
		'new_item_name'              => __( 'New Transport Type Name' ),
		'add_new_item'               => __( 'Add New Transport Type' ),
		'edit_item'                  => __( 'Edit Transport Type' ),
		'update_item'                => __( 'Update Transport Type' ),
		'separate_items_with_commas' => __( 'Separate Transport Type with commas' ),
		'search_items'               => __( 'Search Transport Types' ),
		'add_or_remove_items'        => __( 'Add or remove Transport Type' ),
		'choose_from_most_used'      => __( 'Choose from the most used Transport Types' ),
	);

	// Labels for aminities taxonomy.
	$mtrap_labels_aminities_tax = array(
		'name'                       => __( 'Aminities' ),
		'singular_name'              => __( 'Aminity' ),
		'menu_name'                  => __( 'Aminities' ),
		'all_items'                  => __( 'All Aminities' ),
		'parent_item'                => __( 'Parent Aminity' ),
		'parent_item_colon'          => __( 'Parent Aminity:' ),
		'new_item_name'              => __( 'New Aminity Name' ),
		'add_new_item'               => __( 'Add New Aminity' ),
		'edit_item'                  => __( 'Edit Aminity' ),
		'update_item'                => __( 'Update Aminity' ),
		'separate_items_with_commas' => __( 'Separate Aminity with commas' ),
		'search_items'               => __( 'Search Aminities' ),
		'add_or_remove_items'        => __( 'Add or remove Aminity' ),
		'choose_from_most_used'      => __( 'Choose from the most used Aminity' ),
	);

	// Labels for seat class taxonomy.
	$mtrap_labels_seat_class_tax = array(
		'name'                       => __( 'Seat Class' ),
		'singular_name'              => __( 'Seat Class' ),
		'menu_name'                  => __( 'Seat Class' ),
		'all_items'                  => __( 'All Seat Classes' ),
		'parent_item'                => __( 'Parent Seat Class' ),
		'parent_item_colon'          => __( 'Parent Seat Class:' ),
		'new_item_name'              => __( 'New Seat Class Name' ),
		'add_new_item'               => __( 'Add New Seat Class' ),
		'edit_item'                  => __( 'Edit Seat Class' ),
		'update_item'                => __( 'Update Seat Class' ),
		'separate_items_with_commas' => __( 'Separate Seat Class with commas' ),
		'search_items'               => __( 'Search Seat Classes' ),
		'add_or_remove_items'        => __( 'Add or remove Seat Class' ),
		'choose_from_most_used'      => __( 'Choose from the most used Seat Class' ),
	);

	// Labels for State taxonomy.
	$mtrap_labels_state_tax = array(
		'name'                       => __( 'States' ),
		'singular_name'              => __( 'State' ),
		'menu_name'                  => __( 'States' ),
		'all_items'                  => __( 'All states' ),
		'parent_item'                => __( 'Parent state' ),
		'parent_item_colon'          => __( 'Parent state:' ),
		'new_item_name'              => __( 'New state Name' ),
		'add_new_item'               => __( 'Add New state' ),
		'edit_item'                  => __( 'Edit state' ),
		'update_item'                => __( 'Update state' ),
		'separate_items_with_commas' => __( 'Separate state with commas' ),
		'search_items'               => __( 'Search states' ),
		'add_or_remove_items'        => __( 'Add or remove states' ),
		'choose_from_most_used'      => __( 'Choose from the most used states' ),
	);

	// Labels for passenger types taxonomy.
	$mtrap_labels_passenger_type_tax = array(
		'name'                       => __( 'Passenger Types' ),
		'singular_name'              => __( 'Passenger Type' ),
		'menu_name'                  => __( 'Passenger Types' ),
		'all_items'                  => __( 'All Passenger Types' ),
		'parent_item'                => __( 'Parent passenger Type' ),
		'parent_item_colon'          => __( 'Parent passenger Type:' ),
		'new_item_name'              => __( 'New Passenger Type Name' ),
		'add_new_item'               => __( 'Add New Passenger Type' ),
		'edit_item'                  => __( 'Edit Passenger Type' ),
		'update_item'                => __( 'Update Passenger Type' ),
		'separate_items_with_commas' => __( 'Separate Passenger Type with commas' ),
		'search_items'               => __( 'Search Passenger Types' ),
		'add_or_remove_items'        => __( 'Add or remove Passenger Type' ),
		'choose_from_most_used'      => __( 'Choose from the most used Passenger Types' ),
	);

	// Arguments for bus stop taxonomy.
	$mtrap_labels_busstops_args = array(
		'labels'            => $mtrap_labels_busstops_tax,
		'hierarchical'      => false,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => true,
	);

	// Arguments for bus types taxonomy.
	$mtrap_labels_bus_types_args = array(
		'labels'            => $mtrap_labels_bus_types_tax,
		'hierarchical'      => false,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => true,
	);

	// Arguments for aminities taxonomy.
	$mtrap_labels_aminities_args = array(
		'labels'            => $mtrap_labels_aminities_tax,
		'hierarchical'      => false,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => true,
	);

	// Arguments for seat class taxonomy.
	$mtrap_labels_seat_class_args = array(
		'labels'            => $mtrap_labels_seat_class_tax,
		'hierarchical'      => false,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => true,
	);
	// Arguments for state class taxonomy.
	$mtrap_labels_state_class_args = array(
		'labels'            => $mtrap_labels_state_tax,
		'hierarchical'      => false,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => true,
	);

	// Arguments for passenger types taxonomy.
	$mtrap_labels_passenger_type_args = array(
		'labels'            => $mtrap_labels_passenger_type_tax,
		'hierarchical'      => false,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => true,
	);

	// Register taxonomies.
	register_taxonomy( 'bus-stops', 'product', $mtrap_labels_busstops_args );
	register_taxonomy( 'bus-types', 'product', $mtrap_labels_bus_types_args );
	register_taxonomy( 'aminities', 'product', $mtrap_labels_aminities_args );
	register_taxonomy( 'seat-class', 'product', $mtrap_labels_seat_class_args );
	register_taxonomy( 'passenger-type', 'product', $mtrap_labels_passenger_type_args );
	register_taxonomy( 'states', 'destinations', $mtrap_labels_state_class_args );

	// Object types of the taxonomy.
	register_taxonomy_for_object_type( 'bus-stops', 'product' );
	register_taxonomy_for_object_type( 'bus-types', 'product' );
	register_taxonomy_for_object_type( 'aminities', 'product' );
	register_taxonomy_for_object_type( 'seat-class', 'product' );
	register_taxonomy_for_object_type( 'passenger-type', 'product' );
}
