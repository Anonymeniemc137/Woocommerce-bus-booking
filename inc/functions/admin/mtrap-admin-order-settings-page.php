<?php
/**
 * Generate order settings page for the transport system.
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'admin_menu', 'mtrap_register_transport_subpages' );
/**
 * Add custom transport settings to admin page.
 */
function mtrap_register_transport_subpages() {
	// Add main menu page with the first submenu as the default page.
	add_menu_page(
		'Transport Settings',
		'Transport Settings',
		'manage_options',
		'order-modification-settings',
		'order_modification_settings_page',
		'dashicons-admin-generic',
		56
	);

	// Add submenu pages.
	add_submenu_page(
		'order-modification-settings',
		'Order Modification Settings',
		'Order Modification Settings',
		'manage_options',
		'order-modification-settings',
		'order_modification_settings_page'
	);

	add_submenu_page(
		'order-modification-settings',
		'Order Cancel Settings',
		'Order Cancel Settings',
		'manage_options',
		'order-cancel-settings',
		'order_cancel_settings_page'
	);

	add_submenu_page(
		'order-modification-settings',
		'Transportation Status Settings',
		'Transportation Status Settings',
		'manage_options',
		'transportation-status-settings',
		'transportation_status_settings_page'
	);
}

/**
 * Order Modification Settings page callback
 */
function order_modification_settings_page() {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Order Modification Settings', 'winger' ); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'order-modification-settings-group' );
			do_settings_sections( 'order-modification-settings' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Order Cancel Settings page callback
 */
function order_cancel_settings_page() {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Order Cancel Settings', 'winger' ); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'order-cancel-settings-group' );
			do_settings_sections( 'order-cancel-settings' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Bus Status Settings page callback
 */
function transportation_status_settings_page() {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Transportation Status Settings', 'winger' ); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'tranportation-status-settings-group' );
			do_settings_sections( 'transportation-status-settings' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

add_action( 'admin_init', 'register_transport_settings' );
/**
 * Register settings
 */
function register_transport_settings() {
	// Register settings for the order modification page.
	register_setting( 'order-modification-settings-group', 'order_modification_more_than_48_hours' );
	register_setting( 'order-modification-settings-group', 'order_modification_within_48_hours' );
	register_setting( 'order-modification-settings-group', 'order_modification_within_24_hours' );

	add_settings_section(
		'order_modification_main_section',
		'', // Empty heading.
		'__return_false', // No callback for subheading.
		'order-modification-settings'
	);

	add_settings_field(
		'order_modification_more_than_48_hours',
		__( 'More than 48 hours', 'winger' ),
		'order_modification_more_than_48_hours_callback',
		'order-modification-settings',
		'order_modification_main_section'
	);

	add_settings_field(
		'order_modification_within_48_hours',
		__( 'Within 48 hours', 'winger' ),
		'order_modification_within_48_hours_callback',
		'order-modification-settings',
		'order_modification_main_section'
	);

	add_settings_field(
		'order_modification_within_24_hours',
		__( 'Within 24 hours', 'winger' ),
		'order_modification_within_24_hours_callback',
		'order-modification-settings',
		'order_modification_main_section'
	);

	// Register settings for the order cancel page.
	register_setting( 'order-cancel-settings-group', 'order_cancel_more_than_48_hours' );
	register_setting( 'order-cancel-settings-group', 'order_cancel_within_24_to_48_hours' );
	register_setting( 'order-cancel-settings-group', 'order_cancel_within_24_hours' );

	add_settings_section(
		'order_cancel_main_section',
		'', // Empty heading.
		'__return_false', // No callback for subheading.
		'order-cancel-settings'
	);

	add_settings_field(
		'order_cancel_more_than_48_hours',
		__( 'More than 48 hours', 'winger' ),
		'order_cancel_more_than_48_hours_callback',
		'order-cancel-settings',
		'order_cancel_main_section'
	);

	add_settings_field(
		'order_cancel_within_24_to_48_hours',
		__( 'Within 24 to 48 hours', 'winger' ),
		'order_cancel_within_24_to_48_hours_callback',
		'order-cancel-settings',
		'order_cancel_main_section'
	);

	add_settings_field(
		'order_cancel_within_24_hours',
		__( 'Within 24 hours', 'winger' ),
		'order_cancel_within_24_hours_callback',
		'order-cancel-settings',
		'order_cancel_main_section'
	);

	// Register settings for the bus status page.
	register_setting( 'tranportation-status-settings-group', 'bus_status_change_after_hours' );
	register_setting( 'tranportation-status-settings-group', 'bus_status_email_cancelled' );
	register_setting( 'tranportation-status-settings-group', 'bus_status_email_delayed' );

	add_settings_section(
		'transportation_status_main_section',
		'', // Empty heading.
		'__return_false', // No callback for subheading.
		'transportation-status-settings'
	);
	
	// Register the "Restrict transportation status changes after hours" field
	add_settings_field(
		'bus_status_change_after_hours',
		__( 'Restrict transportation status changes after hours', 'winger' ),
		'bus_status_change_after_hours_callback',
		'transportation-status-settings',
		'transportation_status_main_section'
	);
	
	// Register the "Email Content for Cancelled Journey" field
    add_settings_field(
        'bus_status_email_cancelled',
        __( 'Email Content for Cancelled Journey', 'winger' ),
        'bus_status_email_cancelled_callback',
        'transportation-status-settings',
        'transportation_status_main_section'
    );

    // Register the "Email Content for Delayed Journey" field
    add_settings_field(
        'bus_status_email_delayed',
        __( 'Email Content for Delayed Journey', 'winger' ),
        'bus_status_email_delayed_callback',
        'transportation-status-settings',
        'transportation_status_main_section'
    );
}

/**
 * Callback functions for settings fields
 */

function order_modification_more_than_48_hours_callback() {
	$value = get_option( 'order_modification_more_than_48_hours', '' );
	echo '<input type="number" name="order_modification_more_than_48_hours" value="' . esc_attr( $value ) . '" />';
	echo '<p class="description">' . __( 'If user modifies the order more than 48 hours before departure, add order modification charge in ( % ).', 'winger' ) . '</p>';
}

function order_modification_within_48_hours_callback() {
	$value = get_option( 'order_modification_within_48_hours', '' );
	echo '<input type="number" name="order_modification_within_48_hours" value="' . esc_attr( $value ) . '" />';
	echo '<p class="description">' . __( 'If user modifies the order within 48 hours, add order modification charge in ( % ).', 'winger' ) . '</p>';
}

function order_modification_within_24_hours_callback() {
	$value = get_option( 'order_modification_within_24_hours', '' );
	echo '<input type="number" name="order_modification_within_24_hours" value="' . esc_attr( $value ) . '" />';
	echo '<p class="description">' . __( 'If user modifies the order within 24 hours, add order modification charge in ( % ).', 'winger' ) . '</p>';
}

function order_cancel_more_than_48_hours_callback() {
	$value = get_option( 'order_cancel_more_than_48_hours', '' );
	echo '<input type="number" name="order_cancel_more_than_48_hours" value="' . esc_attr( $value ) . '" />';
	echo '<p class="description">' . __( 'If user cancels the order more than 48 hours before departure, set refund percentage.', 'winger' ) . '</p>';
}

function order_cancel_within_24_to_48_hours_callback() {
	$value = get_option( 'order_cancel_within_24_to_48_hours', '' );
	echo '<input type="number" name="order_cancel_within_24_to_48_hours" value="' . esc_attr( $value ) . '" />';
	echo '<p class="description">' . __( 'If user cancels the order within 24 to 48 hours before departure, set refund percentage.', 'winger' ) . '</p>';
}

function order_cancel_within_24_hours_callback() {
	$value = get_option( 'order_cancel_within_24_hours', '' );
	echo '<input type="number" name="order_cancel_within_24_hours" value="' . esc_attr( $value ) . '" />';
	echo '<p class="description">' . __( 'If user cancels the order within 24 hours before departure, set refund percentage.', 'winger' ) . '</p>';
}

function bus_status_change_after_hours_callback() {
	$value = get_option( 'bus_status_change_after_hours', '' );
	echo '<input type="number" name="bus_status_change_after_hours" value="' . esc_attr( $value ) . '" />';
	echo '<p class="description">' . __( 'Restrict any transportation status changes after this number of hours before departure.', 'winger' ) . '</p>';
}

function bus_status_email_cancelled_callback() {
    $value = get_option( 'bus_status_email_cancelled', '' );
    echo '<textarea name="bus_status_email_cancelled" rows="5" cols="50">' . esc_textarea( $value ) . '</textarea>';
    echo '<p class="description">' . __( 'Content for email notifications sent when a journey is cancelled.', 'winger' ) . '</p>';
}

function bus_status_email_delayed_callback() {
    $value = get_option( 'bus_status_email_delayed', '' );
    echo '<textarea name="bus_status_email_delayed" rows="5" cols="50">' . esc_textarea( $value ) . '</textarea>';
    echo '<p class="description">' . __( 'Content for email notifications sent when a journey is delayed.', 'winger' ) . '</p>';
}
?>
