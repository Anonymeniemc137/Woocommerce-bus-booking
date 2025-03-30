<?php
/**
 * Theme functions to enqueue scripts and styles
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'wp_enqueue_scripts', 'mtrap_front_enqueue_styles_scripts', 1, 1 );
/**
 * Enqueue styles & scripts admin side.
 */
function mtrap_front_enqueue_styles_scripts() {
	// Front styles.
	wp_enqueue_style( 'winger-parent-style', get_template_directory_uri() . '/style.css' );
	wp_enqueue_style( 'mtrap-front-style', get_stylesheet_directory_uri() . '/assets/css/front/front-style.css' );
	wp_register_style( 'jquery-ui-css', get_stylesheet_directory_uri() . '/assets/css/vendors/jquery-ui.css' );

	// Front scripts.
	wp_register_script( 'mtrap-bus-listing-js', get_stylesheet_directory_uri() . '/assets/js/front/bus-search-listing-price-calc.js' );
	wp_register_script( 'jquery-ui-js', get_stylesheet_directory_uri() . '/assets/js/vendors/jquery-ui.js' );
	wp_enqueue_script( 'custom-script-js', get_stylesheet_directory_uri() . '/assets/js/front/custom-script.js' );
	wp_localize_script(
		'custom-script-js',
		'ajaxObj',
		array(
			'ajax_url'                    => admin_url( 'admin-ajax.php' ),
			'ajax_nonce'                  => wp_create_nonce( 'bus-bookingform' ),
			'ajax_nonce_remove_passenger' => wp_create_nonce( 'remove-passenger' ),
			'mtrap_calander_img'          => site_url() . '/wp-content/themes/winger-child/assets/img/calendar-interface-symbol-tool.png',
			'mtrap_full_name'             => 'Full Name',
			'mtrap_age'                   => 'Age',
			'mtrap_gender'                => 'Gender',
			'mtrap_passenger_count_alert' => 'Seats available for',
			'mtrap_total_seats'           => 'Total seats avaiable',
			'mtrap_male'                  => 'Male',
			'mtrap_female'                => 'Female',
			'mtrap_other'                 => 'Other',
			'mtrap_bus_full'              => 'Sorry, this bus is full!',
		)
	);
}
