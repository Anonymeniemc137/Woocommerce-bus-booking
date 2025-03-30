<?php
/**
 * Theme functions to add numbers meta for the user add/edit screen.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


add_action( 'user_new_form', 'mtrap_user_profile_add_edit_phone_number_add' );

/**
 * Add phone number, gender, and user type fields to add/edit users screen.
 */
function mtrap_user_profile_add_edit_phone_number_add() {
	// Fetch the user phone number, country code, date of birth, gender, and user type from $_POST data
	$mtrap_user_phone_country_code = isset( $_POST['mtrap_user_phone_country_code'] ) ? sanitize_text_field( $_POST['mtrap_user_phone_country_code'] ) : '';
	$mtrap_user_phone_number       = isset( $_POST['mtrap_user_phone_number'] ) ? sanitize_text_field( $_POST['mtrap_user_phone_number'] ) : '';
	$mtrap_user_date_of_birth      = isset( $_POST['mtrap_user_birth_date'] ) ? sanitize_text_field( $_POST['mtrap_user_birth_date'] ) : '';
	$mtrap_user_gender             = isset( $_POST['mtrap_user_gender'] ) ? sanitize_text_field( $_POST['mtrap_user_gender'] ) : '';
	$mrtap_passenger_type          = isset( $_POST['mrtap_passenger_type'] ) ? sanitize_text_field( $_POST['mrtap_passenger_type'] ) : '';

	// Output the fields inside the table
	?>
	<h2><?php esc_html_e( 'Phone Number', 'winger' ); ?></h2>
	<table class="form-table" id="fieldset-billing">
		<tbody>
			<tr>
				<th>
					<label for="mtrap_user_phone_number"><?php esc_html_e( 'Phone Number', 'winger' ); ?></label>
				</th>
				<td>
					<input type="text" id="mtrap_user_phone_country_code" value="<?php echo esc_html( $mtrap_user_phone_country_code ); ?>" name="mtrap_user_phone_country_code" maxlength="6" size="10" placeholder="Country code">
					<input name="mtrap_user_phone_number" type="text"  value="<?php echo esc_html( $mtrap_user_phone_number ); ?>" id="mtrap_user_phone_number" class="regular-text" placeholder="Please add phone number">
				</td>
			</tr>
			<tr>
				<th>
					<label for="mtrap_user_birth_date"><?php esc_html_e( 'Date of birth', 'woocommerce' ); ?></label>
				</th>
				<td>
					<input type="date" placeholder="Enter your birthdate" class="regular-text" name="mtrap_user_birth_date" id="mtrap_user_birth_date" autocomplete="mtrap_user_birth_date" value="<?php echo esc_html( $mtrap_user_date_of_birth ); ?>">
				</td>
			</tr>
			<tr>
				<th>
					<label for="mtrap_user_gender"><?php esc_html_e( 'Gender', 'winger' ); ?></label>
				</th>
				<td>
					<select name="mtrap_user_gender" id="mtrap_user_gender">
						<option value="Male"<?php selected( $mtrap_user_gender, 'Male' ); ?>><?php esc_html_e( 'Male', 'winger' ); ?></option>
						<option value="Female"<?php selected( $mtrap_user_gender, 'Female' ); ?>><?php esc_html_e( 'Female', 'winger' ); ?></option>
						<option value="Other"<?php selected( $mtrap_user_gender, 'Other' ); ?>><?php esc_html_e( 'Other', 'winger' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th>
					<label for="mrtap_passenger_type"><?php esc_html_e( 'Passenger Type', 'winger' ); ?></label>
				</th>
				<td>
					<select name="mrtap_passenger_type" id="mrtap_passenger_type">
						<?php
						$mrtap_passenger_types = get_terms(
							array(
								'taxonomy'   => 'passenger-type',
								'hide_empty' => false,
							)
						);

						if ( ! empty( $mrtap_passenger_types ) && ! is_wp_error( $mrtap_passenger_types ) ) {
							foreach ( $mrtap_passenger_types as $passenger_type ) {
								?>
								<option value="<?php echo esc_attr( $passenger_type->slug ); ?>" <?php selected( $mrtap_passenger_type, $passenger_type->slug ); ?>><?php echo esc_html( $passenger_type->name ); ?></option>
								<?php
							}
						}
						?>
					</select>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
}


add_action( 'show_user_profile', 'mtrap_edit_user_profile_phone_number_add' );
add_action( 'edit_user_profile', 'mtrap_edit_user_profile_phone_number_add' );

/**
 * Add phone number, gender, and passenger type fields to user profile edit screen.
 *
 * @param object $user User object.
 */
function mtrap_edit_user_profile_phone_number_add( $user ) {
	// Fetch user meta data.
	$get_user_phone_meta    = get_user_meta( $user->ID, 'mtrap_user_phone_number', true );
	$get_country_code_meta  = get_user_meta( $user->ID, 'mtrap_user_phone_country_code', true );
	$get_user_date_of_birth = get_user_meta( $user->ID, 'mtrap_user_birth_date', true );

	// Set values for phone number, country code, and date of birth.
	$mtrap_user_phone_set     = ! empty( $_POST['mtrap_user_phone_number'] ) ? sanitize_text_field( $_POST['mtrap_user_phone_number'] ) : esc_html( $get_user_phone_meta );
	$mtrap_country_code       = ! empty( $_POST['mtrap_user_phone_country_code'] ) ? sanitize_text_field( $_POST['mtrap_user_phone_country_code'] ) : esc_html( $get_country_code_meta );
	$mtrap_user_date_of_birth = ! empty( $_POST['mtrap_user_birth_date'] ) ? sanitize_text_field( $_POST['mtrap_user_birth_date'] ) : esc_html( $get_user_date_of_birth );

	// Output the fields inside the table.
	?>
	<h2><?php esc_html_e( 'Phone Number', 'winger' ); ?></h2>
	<table class="form-table" id="fieldset-billing">
		<tbody>
			<tr>
				<th>
					<label for="mtrap_user_phone_number"><?php esc_html_e( 'Phone Number', 'winger' ); ?></label>
				</th>
				<td>
					<input type="text" value="<?php echo esc_html( $mtrap_country_code ); ?>" id="mtrap_user_phone_country_code" name="mtrap_user_phone_country_code" maxlength="6" size="10" placeholder="Country code">
					<input name="mtrap_user_phone_number" type="text" id="mtrap_user_phone_number" class="regular-text" value="<?php echo esc_html( $mtrap_user_phone_set ); ?>" placeholder="Please add phone number">
				</td>
			</tr>
			<tr>
				<th>
					<label for="mtrap_user_birth_date"><?php esc_html_e( 'Date of birth', 'woocommerce' ); ?></label>
				</th>
				<td>
					<input type="date" placeholder="Enter your birthdate" class="regular-text" name="mtrap_user_birth_date" id="mtrap_user_birth_date" autocomplete="mtrap_user_birth_date" value="<?php echo esc_html( $mtrap_user_date_of_birth ); ?>">
				</td>
			</tr>
			<tr>
				<th>
					<label for="mtrap_user_gender"><?php esc_html_e( 'Gender', 'winger' ); ?></label>
				</th>
				<td>
					<select name="mtrap_user_gender" id="mtrap_user_gender">
						<option value="Male"<?php selected( get_the_author_meta( 'mtrap_user_gender', $user->ID ), 'Male' ); ?>><?php esc_html_e( 'Male', 'winger' ); ?></option>
						<option value="Female"<?php selected( get_the_author_meta( 'mtrap_user_gender', $user->ID ), 'Female' ); ?>><?php esc_html_e( 'Female', 'winger' ); ?></option>
						<option value="Other"<?php selected( get_the_author_meta( 'mtrap_user_gender', $user->ID ), 'Other' ); ?>><?php esc_html_e( 'Other', 'winger' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th>
					<label for="mrtap_passenger_type"><?php esc_html_e( 'Passenger Type', 'winger' ); ?></label>
				</th>
				<td>
					<select name="mrtap_passenger_type" id="mrtap_passenger_type">
						<?php
						$mrtap_passenger_types = get_terms(
							array(
								'taxonomy'   => 'passenger-type',
								'hide_empty' => false,
							)
						);

						if ( ! empty( $mrtap_passenger_types ) && ! is_wp_error( $mrtap_passenger_types ) ) {
							foreach ( $mrtap_passenger_types as $passenger_type ) {
								?>
								<option value="<?php echo esc_attr( $passenger_type->slug ); ?>" <?php selected( get_the_author_meta( 'mrtap_passenger_type', $user->ID ), $passenger_type->slug ); ?>><?php echo esc_html( $passenger_type->name ); ?></option>
								<?php
							}
						}
						?>
					</select>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
}

add_action( 'user_register', 'mtrap_user_profile_update', 10, 3 );
add_action( 'personal_options_update', 'mtrap_user_profile_update', 10, 3 );
add_action( 'edit_user_profile_update', 'mtrap_user_profile_update', 10, 3 );

/**
 * Add phone number, gender, and passenger type fields to add/edit users screen.
 *
 * @param int $user_id User ID.
 */
function mtrap_user_profile_update( $user_id ) {
	if ( ! empty( $_POST['mtrap_user_phone_country_code'] ) ) {
		update_user_meta( $user_id, 'mtrap_user_phone_country_code', sanitize_text_field( $_POST['mtrap_user_phone_country_code'] ) );
	}
	if ( ! empty( $_POST['mtrap_user_phone_number'] ) ) {
		update_user_meta( $user_id, 'mtrap_user_phone_number', sanitize_text_field( $_POST['mtrap_user_phone_number'] ) );
	}
	if ( ! empty( $_POST['mtrap_user_birth_date'] ) ) {
		update_user_meta( $user_id, 'mtrap_user_birth_date', sanitize_text_field( gmdate( 'Y-m-d', strtotime( $_POST['mtrap_user_birth_date'] ) ) ) );
	}
	if ( ! empty( $_POST['mtrap_user_gender'] ) ) {
		update_user_meta( $user_id, 'mtrap_user_gender', sanitize_text_field( $_POST['mtrap_user_gender'] ) );
	}
	if ( ! empty( $_POST['mrtap_passenger_type'] ) ) {
		update_user_meta( $user_id, 'mrtap_passenger_type', sanitize_text_field( $_POST['mrtap_passenger_type'] ) );
	}
}

add_action( 'woocommerce_edit_account_form', 'mtrap_display_edit_account_fields' );

/**
 * Display the country code, phone number, birthdate, gender, and passenger type on my account - edit account page.
 */
function mtrap_display_edit_account_fields() {
	$user_id = get_current_user_id();

	// Get user meta data.
	$mtrap_country_code       = get_user_meta( $user_id, 'mtrap_user_phone_country_code', true );
	$mtrap_user_phone_set     = get_user_meta( $user_id, 'mtrap_user_phone_number', true );
	$mtrap_user_date_of_birth = get_user_meta( $user_id, 'mtrap_user_birth_date', true );
	$mtrap_user_gender        = get_user_meta( $user_id, 'mtrap_user_gender', true );
	$mrtap_passenger_type     = get_user_meta( $user_id, 'mrtap_passenger_type', true );
	?>

	<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
		<label for="mtrap_user_phone_country_code"><?php esc_html_e( 'Phone Country Code', 'your-text-domain' ); ?> <span class="required">*</span></label>
		<span class="woocommerce-Input woocommerce-Input--text input-text" style="width: 100% !important; display: inline-block; background-color: rgb(244,244,239); padding: 1.5em 2.6em; border: 1px solid #c59849; border-radius: 40px;"><?php echo esc_attr( $mtrap_country_code ); ?></span>
	</p>

	<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
		<label for="mtrap_user_phone_number"><?php esc_html_e( 'Phone Number', 'your-text-domain' ); ?> <span class="required">*</span></label>
		<span class="woocommerce-Input woocommerce-Input--text input-text" style="width: 100% !important; display: inline-block; background-color: rgb(244,244,239); padding: 1.5em 2.6em; border: 1px solid #c59849; border-radius: 40px;"><?php echo esc_attr( $mtrap_user_phone_set ); ?></span>
	</p>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
		<label for="mtrap_user_birth_date"><?php esc_html_e( 'Date of Birth', 'your-text-domain' ); ?> <span class="required">*</span></label>
		<input type="date" class="woocommerce-Input woocommerce-Input--text input-text" name="mtrap_user_birth_date" id="mtrap_user_birth_date" value="<?php echo esc_attr( $mtrap_user_date_of_birth ); ?>" />
	</p>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
		<label for="mtrap_user_gender"><?php esc_html_e( 'Gender', 'your-text-domain' ); ?> <span class="required">*</span></label>
		<select name="mtrap_user_gender" id="mtrap_user_gender">
			<option value="Male"<?php selected( $mtrap_user_gender, 'Male' ); ?>><?php esc_html_e( 'Male', 'your-text-domain' ); ?></option>
			<option value="Female"<?php selected( $mtrap_user_gender, 'Female' ); ?>><?php esc_html_e( 'Female', 'your-text-domain' ); ?></option>
			<option value="Other"<?php selected( $mtrap_user_gender, 'Other' ); ?>><?php esc_html_e( 'Other', 'your-text-domain' ); ?></option>
		</select>
	</p>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
		<label for="mrtap_passenger_type"><?php esc_html_e( 'Passenger Type', 'your-text-domain' ); ?> <span class="required">*</span></label>
		<select name="mrtap_passenger_type" id="mrtap_passenger_type">
			<?php
			$mrtap_passenger_types = get_terms(
				array(
					'taxonomy'   => 'passenger-type',
					'hide_empty' => false,
				)
			);

			if ( ! empty( $mrtap_passenger_types ) && ! is_wp_error( $mrtap_passenger_types ) ) {
				foreach ( $mrtap_passenger_types as $passenger_type ) {
					?>
					<option value="<?php echo esc_attr( $passenger_type->slug ); ?>" <?php selected( $mrtap_passenger_type, $passenger_type->slug ); ?>><?php echo esc_html( $passenger_type->name ); ?></option>
					<?php
				}
			}
			?>
		</select>
	</p>
	<?php
}

add_action( 'woocommerce_save_account_details', 'mtrap_save_account_fields' );

/**
 * Save the country code, phone number, birthdate, gender, and passenger type on my account - edit account page.
 *
 * @param int $user_id User ID.
 */
function mtrap_save_account_fields( $user_id ) {
	if ( isset( $_POST['mtrap_user_phone_country_code'] ) ) {
		update_user_meta( $user_id, 'mtrap_user_phone_country_code', sanitize_text_field( $_POST['mtrap_user_phone_country_code'] ) );
	}

	if ( isset( $_POST['mtrap_user_phone_number'] ) ) {
		update_user_meta( $user_id, 'mtrap_user_phone_number', sanitize_text_field( $_POST['mtrap_user_phone_number'] ) );
	}

	if ( isset( $_POST['mtrap_user_birth_date'] ) ) {
		update_user_meta( $user_id, 'mtrap_user_birth_date', sanitize_text_field( $_POST['mtrap_user_birth_date'] ) );
	}

	if ( isset( $_POST['mtrap_user_gender'] ) ) {
		update_user_meta( $user_id, 'mtrap_user_gender', sanitize_text_field( $_POST['mtrap_user_gender'] ) );
	}

	if ( isset( $_POST['mrtap_passenger_type'] ) ) {
		update_user_meta( $user_id, 'mrtap_passenger_type', sanitize_text_field( $_POST['mrtap_passenger_type'] ) );
	}
}
