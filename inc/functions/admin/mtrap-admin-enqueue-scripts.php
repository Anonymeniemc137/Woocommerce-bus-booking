<?php
/**
 * Theme functions to enqueue scripts and styles
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'wp_enqueue_scripts', 'mtrap_theme_enqueue_styles_scripts' );
/**
 * Enqueue styles & scripts for theme.
 */
function mtrap_theme_enqueue_styles_scripts() {

	// Enqueue Styles.
	wp_enqueue_style( 'winger-parent-style', get_template_directory_uri() . '/style.css' );

	// front styles.
	wp_enqueue_style( 'mtrap-front-style', get_stylesheet_directory_uri() . '/assets/css/front/front-style.css' );

	// enqueue & localize ajax callback script.
		wp_enqueue_script( 'mtrap-ajax-script', get_stylesheet_directory_uri() . '/assets/js/admin/admin-script-ajax-callback-function.js', array( 'jquery' ) );
		wp_localize_script(
			'mtrap-ajax-script',
			'ajaxfilter',
			array(
				'ajax_url'           => admin_url( 'admin-ajax.php' ),
				'ajax_nonce'         => wp_create_nonce( 'stop-routing' ),
				'ajax_nonce_routing' => wp_create_nonce( 'remove-stop-routing' ),
			)
		);
}


add_action( 'admin_enqueue_scripts', 'mtrap_admin_enqueue_styles_scripts', 1, 1 );
/**
 * Enqueue styles & scripts admin side.
 */
function mtrap_admin_enqueue_styles_scripts() {

	$current_screen = get_current_screen();

	if ( ! empty( $current_screen->post_type ) && 'product' == $current_screen->post_type ) {
		// Enqueue Scripts.
		wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.6.3/css/all.min.css' );

		wp_enqueue_style( 'mtrap-daterangepicker-style', get_stylesheet_directory_uri() . '/assets/css/vendors/daterangepicker.css' );
		wp_enqueue_style( 'mtrap-select2-style', get_stylesheet_directory_uri() . '/assets/css/vendors/select2.min.css' );
		wp_enqueue_style( 'mtrap-select2-bootstrap-style', get_stylesheet_directory_uri() . '/assets/css/vendors/select2-bootstrap.min.css' );
		wp_enqueue_style( 'jquery-timepicker-css', get_stylesheet_directory_uri() . '/assets/css/vendors/jquery.timepicker.css' );

		// custom styles.
		wp_enqueue_style( 'mtrap-custom-admin-style', get_stylesheet_directory_uri() . '/assets/css/admin/admin-style.css' );

		// Enqueue Scripts.
		wp_enqueue_script( 'custom-timepicker-js', get_stylesheet_directory_uri() . '/assets/js/vendors/jquery.timepicker.js' );
		wp_enqueue_script( 'moment-script', get_stylesheet_directory_uri() . '/assets/js/vendors/moment.min.js' );
		wp_enqueue_script( 'daterangepicker-script', get_stylesheet_directory_uri() . '/assets/js/vendors/daterangepicker.min.js' );
		wp_enqueue_script( 'bootstrap-script', get_stylesheet_directory_uri() . '/assets/js/vendors/bootstrap.min.js' );
		wp_enqueue_script( 'select2-script', get_stylesheet_directory_uri() . '/assets/js/vendors/select2.min.js' );

		// custom scripts.
		wp_enqueue_script( 'admin-script', get_stylesheet_directory_uri() . '/assets/js/admin/admin-script.js' );

		$mtrap_woo_currency_symbol = ' (' . get_woocommerce_currency_symbol() . ')';

		wp_localize_script(
			'admin-script',
			'mtrapbusstops',
			array(
				'mtrap_label'                       => __( 'Add information about this station', 'winger' ),
				'mtrap_adult_price'                 => __( 'Adult price', 'winger' ),
				'mtrap_currency_sign'               => esc_html( $mtrap_woo_currency_symbol ),
				'mtrap_child_price'                 => __( 'Child price', 'winger' ),
				'mtrap_teen_price'                  => __( 'Teen price', 'winger' ),
				'mtrap_senior_price'                => __( 'Senior price', 'winger' ),
				'mtrap_day'                         => __( 'Station reaching day', 'winger' ),
				'mtrap_select_reach_day'            => __( 'Please select reach day', 'winger' ),
				'mtrap_select_reach_same_day'       => __( 'Same Day', 'winger' ),
				'mtrap_select_reach_differrent_day' => __( 'Different Day', 'winger' ),
				'mtrap_select_reach_day_difference' => __( 'Day difference', 'winger' ),
				'mtrap_stop_time'                   => __( 'Arrival time', 'winger' ),
				'mtrap_stop_time_departure'         => __( 'Departure time', 'winger' ),
				'mtrap_remove_bus_stop'             => __( 'Remove bus stop', 'winger' ),
			)
		);

		// enqueue & localize ajax callback script.
		wp_enqueue_script( 'mtrap-ajax-script', get_stylesheet_directory_uri() . '/assets/js/admin/admin-script-ajax-callback-function.js', array( 'jquery' ) );
		wp_localize_script(
			'mtrap-ajax-script',
			'ajaxfilter',
			array(
				'ajax_url'           => admin_url( 'admin-ajax.php' ),
				'ajax_nonce'         => wp_create_nonce( 'stop-routing' ),
				'ajax_nonce_routing' => wp_create_nonce( 'remove-stop-routing' ),
			)
		);

	}
}
