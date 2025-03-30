<?php
/**
 * Shortcode functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


add_shortcode( 'mtrap_cencallation_destination_form_shortcode', 'mtrap_cancellation_date_shortcode_function' );
/**
 * Theme functions to add custom login & registration functionality.
 *
 * @param object $atts string.
 */
function mtrap_cancellation_date_shortcode_function() {
	ob_start();

	$mtrap_oc_booking_number = ! empty( $_POST['booking_number'] ) ? sanitize_text_field( $_POST['booking_number'] ) : '';
	$mtrap_oc_from_city      = ! empty( $_POST['destination_from'] ) ? sanitize_text_field( $_POST['destination_from'] ) : '';
	$mtrap_oc_to_city        = ! empty( $_POST['destination_to'] ) ? sanitize_text_field( $_POST['destination_to'] ) : '';
	$mtrap_oc_email          = ! empty( $_POST['journeyemail'] ) ? sanitize_text_field( $_POST['journeyemail'] ) : '';
	$mtrap_oc_journey_date   = ! empty( $_POST['journeydate'] ) ? sanitize_text_field( $_POST['journeydate'] ) : '';
	$disabled_class          = empty( $mtrap_oc_from_city ) ? 'disabled' : '';

	// Bus stops query.
	$bus_stops = get_terms(
		array(
			'taxonomy'   => 'bus-stops',
			'hide_empty' => false,
		)
	);

	wp_enqueue_style( 'jquery-ui-css' );
	wp_enqueue_script( 'jquery-ui-js' );
	?>
	<div class="mtrap-ticket-cancellation-outer">
		<form action="<?php echo home_url( 'cancellation/' ); ?>" class="transportation_ticket_cancellation_form" method="post">
			<div class="column-bus-search booking-number">
				<input type="text" class="booking_number" name="booking_number" required placeholder="<?php echo __( 'Booking Number', 'winger' ); ?>" value="<?php echo esc_html( $mtrap_oc_booking_number ); ?>">
				<div class="booking-number-err"></div>
			</div>
			<div class="column-bus-search city-value-one">
				<select class="destination_from" class="js-city form-control" name="destination_from">
					<option value=""><?php echo __( 'From City', 'winger' ); ?></option>
					<?php
					foreach ( $bus_stops as $bus_stop ) {
						echo '<option ' . selected( esc_html( $bus_stop->term_id ), $mtrap_oc_from_city ) . 'value="' . esc_html( $bus_stop->term_id ) . '" >' . esc_html( $bus_stop->name ) . '</option>';
					}
					?>
				</select>
				<div class="destination-from-om-err"></div>
			</div>
			<div class="column-bus-search city-value-two">
				<select class="destination_to" name="destination_to" <?php echo esc_html( $disabled_class ); ?>>
					<option  value=""><?php echo __( 'To City', 'winger' ); ?></option>
					<?php
					if ( ! empty( $mtrap_oc_from_city ) ) {
						$to_meta_fields = get_term_meta( $mtrap_oc_from_city, 'mtrap_bus_stops_route_meta', true );
						if ( ! empty( $to_meta_fields ) ) {
							foreach ( $to_meta_fields as $related_stations_meta ) {
								$mtrap_term_name = get_term( $related_stations_meta )->name;
								if ( ! empty( $mtrap_term_name ) ) {
									echo '<option ' . selected( esc_html( $related_stations_meta ), $mtrap_oc_to_city ) . ' value=' . esc_html( $related_stations_meta ) . '>' . esc_html( ucfirst( $mtrap_term_name ) ) . '</option>';
								}
							}
						}
					}
					?>
				</select>
				<div class="destination-to-om-err"></div>
			</div>
			<div class="column-bus-search journey-email">
				<input type="email" class="journeyemail" name="journeyemail" required value="<?php echo esc_html( $mtrap_oc_email ); ?>" placeholder="<?php echo __( 'Email', 'winger' ); ?>">
				<div class="journey-email-err"></div>
			</div>
			<div class="column-bus-search journey-date">
				<input type="text" readonly class="journeydate" name="journeydate" placeholder="<?php echo __( 'Journey Date', 'winger' ); ?>" value="<?php echo esc_html( $mtrap_oc_journey_date ); ?>">
				<div class="journey-date-err"></div>
			</div>
			<div class="column-cancellation-ticket">
				<input type="submit" class="submit-journey-cancellation-btn submit-btn" value="<?php echo __( 'Search', 'winger' ); ?>">
			</div>
		</form>
	</div>
	<?php
	return ob_get_clean();
}



add_shortcode( 'get_theme_option', 'get_theme_option_shortcode' );
/**
 * Shortcode to get custom theme options.
 */
function get_theme_option_shortcode( $atts ) {
    $atts = shortcode_atts(
        array(
            'option_name' => '',
        ),
        $atts,
        'get_option'
    );

    $option_value = get_option( $atts['option_name'] );

    if ( ! empty( $option_value ) ) {
        return esc_html( $option_value );
    } 
}