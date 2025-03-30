<?php
/**
 * Fetch busstops for pricing & routes tab - single procuct.
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


add_filter( 'user_profile_update_errors', 'mtrap_admin_user_profile_add_edit_settings_errors' );
/**
 * Add phone number to add/edit users screen - validation.
 *
 * @param object $errors string.
 */
function mtrap_admin_user_profile_add_edit_settings_errors( $errors ) {

	global $wpdb;
	$mtrap_user_meta_db_table = $wpdb->prefix . 'usermeta';

	$mtrap_user_phone_country_code = sanitize_text_field( $_POST['mtrap_user_phone_country_code'] );
	$mtrap_user_phone_number       = sanitize_text_field( $_POST['mtrap_user_phone_number'] );
	$mtrap_user_birth_date         = sanitize_text_field( $_POST['mtrap_user_birth_date'] );

	$mtrap_user_id = isset( $_REQUEST['user_id'] ) ? (int) $_REQUEST['user_id'] : '';

	if ( ! empty( $mtrap_user_id ) ) {
		$mtrp_existing_user = $wpdb->get_row( $wpdb->prepare( "SELECT user_id FROM $mtrap_user_meta_db_table WHERE meta_key LIKE '%s' AND meta_value = '%s' AND user_id != '%d' LIMIT 1", 'mtrap_user_phone_number', $mtrap_user_phone_number, $mtrap_user_id ) );
	} else {
		$mtrp_existing_user = $wpdb->get_row( $wpdb->prepare( "SELECT user_id FROM $mtrap_user_meta_db_table WHERE meta_key LIKE '%s' AND meta_value = '%s' LIMIT 1", 'mtrap_user_phone_number', $mtrap_user_phone_number ) );
	}

	if ( empty( $mtrap_user_phone_country_code ) ) {
		$errors->add( 'mtrap-user-phone-country-code', __( 'Error: Please enter the country code!', 'winger' ) );
	}

	if ( empty( $mtrap_user_phone_number ) ) {
		$errors->add( 'mtrap-user-phone-number', __( 'Error: Please enter the phone number!', 'winger' ) );
	}

	if ( ! empty( $mtrp_existing_user ) && ! empty( $mtrp_existing_user->user_id ) ) {
		$errors->add( 'mtrap-duplicate-phone-number', __( 'Error: Phone number already used!', 'winger' ) );
	}

	if ( empty( $mtrap_user_birth_date ) ) {
		$errors->add( 'mtrap-user-date-of-birth', __( 'Error: Please enter the birthdate!', 'winger' ) );
	}

	return $errors;
}


add_filter( 'woocommerce_registration_errors', 'mtrap_front_user_registration_settings_errors', 10, 3 );
/**
 * Validate phone number on myaccount user registration.
 *
 * @param object $errors string.
 */
function mtrap_front_user_registration_settings_errors( $errors ) {
	global $wpdb;
	$mtrap_user_meta_db_table = $wpdb->prefix . 'usermeta';
	$mtrap_user_phone_number  = sanitize_text_field( $_POST['mtrap_user_phone_number'] );
	$mtrp_existing_user       = $wpdb->get_row( $wpdb->prepare( "SELECT user_id FROM $mtrap_user_meta_db_table WHERE meta_key LIKE '%s' AND meta_value = '%s' LIMIT 1", 'mtrap_user_phone_number', $mtrap_user_phone_number ) );

	if ( ! empty( $mtrp_existing_user ) && ! empty( $mtrp_existing_user->user_id ) ) {
		$errors->add( 'mtrap-registration-duplicate-phone-number', __( 'Phone number already used!', 'winger' ) );
	}
	return $errors;
}


add_filter( 'woocommerce_login_credentials', 'mtrap_woocommerce_login_credentials', 10, 1 );
/**
 * Allow login by phone number.
 *
 * @param array $creds details.
 */
function mtrap_woocommerce_login_credentials( $creds ) {

	$user_login = $creds['user_login'];

	if ( is_numeric( $user_login ) ) {

		$args = array(
			'meta_query' =>

			array(

				array(
					'key'     => 'mtrap_user_phone_number',
					'value'   => $user_login,
					'compare' => '=',
				),
			),
		);

		$user_data = get_users( $args );

		if ( ! empty( $user_data ) ) {
			$creds['user_login'] = $user_data[0]->data->user_login;
		}
	}

	return $creds;
}

add_filter( 'login_errors', 'mtrap_woocommerce_login_error_message', 10, 1 );
/**
 * Change validation error message.
 *
 * @param string $error_message message.
 */
function mtrap_woocommerce_login_error_message( $error_message ) {

	if ( 'index.php' !== $GLOBALS['pagenow'] ) {
		return $error_message;
	}

	$blank_field_validation    = strpos( $error_message, 'Username is required' );
	$not_register_validation   = strpos( $error_message, 'not registered on this site' );
	$incorrect_pass_validation = strpos( $error_message, 'The password you entered for the username' );
	$unknown_pass_validation   = strpos( $error_message, 'Check again or try' );

	if ( $blank_field_validation ) {

		$error_message = '<strong>' . __( 'Error:', 'woocommerce' ) . '</strong> ' . __( 'Please enter phone no. or email id.', 'winger' );
	}

	if ( $not_register_validation ) {

		$error_message = '<strong>' . __( 'Error:', 'woocommerce' ) . '</strong> ' . __( 'Your provided phone no. or email id not register on site.', 'winger' );
	}

	if ( $incorrect_pass_validation ) {

		$error_message = '<strong>' . __( 'Error:', 'woocommerce' ) . '</strong> ' . __( 'Your provided login details are incorrect.', 'winger' );
	}

	if ( $unknown_pass_validation ) {

		$error_message = '<strong>' . __( 'Error:', 'woocommerce' ) . '</strong> ' . __( 'Unknown email address.', 'winger' );
	}

	return $error_message;
}

add_filter( 'woocommerce_process_login_errors', 'mtrap_phone_no_validation', 10, 2 );
/**
 * Check validation for phone no. during login process.
 *
 * @param object $validation_error error object.
 * @param string $user_login user_login.
 */
function mtrap_phone_no_validation( $validation_error, $user_login ) {

	if ( ! is_email( $user_login ) && ! preg_match( '/^[0-9]+$/', $user_login ) ) {

		$error_message = __( 'Invalid phone/email id.', 'winger' );

		$validation_error->add( 'invalid-phone-email', $error_message );
	}

	return $validation_error;
}

add_filter( 'woocommerce_add_error', 'mtrap_woocommerce_lost_pass_error_message', 10, 1 );
/**
 * Change validation error message.
 *
 * @param string $error_message message.
 */
function mtrap_woocommerce_lost_pass_error_message( $error_message ) {

	if ( sanitize_title( $error_message ) == 'enter-a-username-or-email-address' ) {

		$error_message = __( 'Please enter email id.', 'winger' );
	}

	if ( sanitize_title( $error_message ) == 'invalid-username-or-email' ) {

		$error_message = __( 'Invalid an email id.', 'winger' );
	}

	return $error_message;
}

add_action( 'lostpassword_post', 'mtrap_email_id_validation', 10, 1 );
/**
 * Check validation for email during lost password.
 *
 * @param object $validation_error error object.
 */
function mtrap_email_id_validation( $validation_error ) {

	$user_login = isset( $_POST['user_login'] ) ? sanitize_user( wp_unslash( $_POST['user_login'] ) ) : '';

	if ( ! is_email( $user_login ) ) {

		$error_message = __( 'Please enter valid email id.', 'winger' );

		$validation_error->add( 'invalid-phone-email', $error_message );
	}

	return $validation_error;
}

add_filter( 'woocommerce_process_registration_errors', 'mtrap_registration_validation', 10, 1 );
/**
 * Check validation in registration form.
 *
 * @param object $validation_error error object.
 */
function mtrap_registration_validation( $validation_error ) {

	$first_name            = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
	$last_name             = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
	$birth_date            = isset( $_POST['mtrap_user_birth_date'] ) ? sanitize_text_field( wp_unslash( $_POST['mtrap_user_birth_date'] ) ) : '';
	$country_code          = isset( $_POST['mtrap_user_phone_country_code'] ) ? sanitize_text_field( wp_unslash( $_POST['mtrap_user_phone_country_code'] ) ) : '';
	$phone_num             = isset( $_POST['mtrap_user_phone_number'] ) ? sanitize_text_field( wp_unslash( $_POST['mtrap_user_phone_number'] ) ) : '';
	$reg_email             = isset( $_POST['email'] ) ? sanitize_text_field( wp_unslash( $_POST['email'] ) ) : '';
	$user_password         = isset( $_POST['password'] ) ? $_POST['password'] : '';
	$user_confirm_password = isset( $_POST['reg_confirm_password'] ) ? $_POST['reg_confirm_password'] : '';

	if ( empty( $first_name ) ) {

		$validation_error->add( 'invalid-first-name', __( 'Please enter first name.', 'winger' ) );
	}

	if ( empty( $last_name ) ) {

		$validation_error->add( 'invalid-last-name', __( 'Please enter last name.', 'winger' ) );
	}

	if ( empty( $birth_date ) ) {

		$validation_error->add( 'invalid-birth-date', __( 'Please select birth date.', 'winger' ) );
	}

	if ( empty( $country_code ) ) {

		$validation_error->add( 'invalid-country-code', __( 'Please enter country code.', 'winger' ) );
	}

	if ( empty( $phone_num ) ) {

		$validation_error->add( 'invalid-phone-no', __( 'Please enter phone no.', 'winger' ) );
	}

	if ( ! is_email( $reg_email ) ) {

		$validation_error->add( 'invalid-email', __( 'Please enter valid email id.', 'winger' ) );
	}

	if ( empty( $user_password ) ) {

		$validation_error->add( 'invalid-pass', __( 'Please enter password.', 'winger' ) );
	}

	if ( empty( $user_confirm_password ) ) {

		$validation_error->add( 'invalid-confirm-pass', __( 'Please enter confirm password.', 'winger' ) );
	}

	if ( ! empty( $user_confirm_password ) && $user_password !== $user_confirm_password ) {

		$validation_error->add( 'invalid-match-pass', __( 'Your password does not match with confirm password.', 'winger' ) );
	}

	return $validation_error;
}

add_action( 'woocommerce_created_customer', 'mtrap_save_registration_fields' );
/**
 * Check validation for email during lost password.
 *
 * @param object $customer_id int.
 */
function mtrap_save_registration_fields( $customer_id ) {
	if ( isset( $_POST['first_name'] ) ) {
		update_user_meta( $customer_id, 'first_name', sanitize_text_field( $_POST['first_name'] ) );
	}
	if ( isset( $_POST['last_name'] ) ) {
		update_user_meta( $customer_id, 'last_name', sanitize_text_field( $_POST['last_name'] ) );
	}
}

// Login / logout url change.
/**
 * Filter to redirect a user to a specific page after logout.
 * @return [URL] logout URL with page slug on which it will be redirected after logout.
 */
add_filter( 'login_logout_menu_logout', 'mtrap_loginpress_login_menu_logout_redirect' );
function mtrap_loginpress_login_menu_logout_redirect() {	
	return wp_logout_url( home_url() . '/my-account/' );
}
/**
* Filter to redirect a user to a myaccount page.
*
* @return [URL] custom login page URL.
*/
add_filter( 'login_logout_menu_login', 'mtrap_loginpress_login_menu_login_redirect' );
function mtrap_loginpress_login_menu_login_redirect() {
  return wp_logout_url( home_url() . '/my-account/' );
}
