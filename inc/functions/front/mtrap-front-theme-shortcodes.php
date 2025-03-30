<?php
/**
 * Shortcode functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_shortcode( 'mtrap_destination_form_shortcode', 'mtrap_date_shortcode_function' );
/**
 * Bus search form.
 *
 * @param object $atts string.
 */
function mtrap_date_shortcode_function( $atts ) {
	ob_start();
	wp_enqueue_style( 'jquery-ui-css' );
	wp_enqueue_script( 'jquery-ui-js' );
	// Parse shortcode attributes.
	$atts = shortcode_atts(
		array(
			'show' => 'true', // Default value is true.
		),
		$atts,
		'return_date'
	);
	// Check if the 'show' attribute is set to true.
	$show_return_date              = filter_var( $atts['show'], FILTER_VALIDATE_BOOLEAN );
	$mtrap_bus_search_from_city    = ! empty( $_POST['destination_from'] ) ? sanitize_text_field( $_POST['destination_from'] ) : '';
	$mtrap_bus_search_to_city      = ! empty( $_POST['destination_to'] ) ? sanitize_text_field( $_POST['destination_to'] ) : '';
	$mtrap_bus_search_booking_date = ( ! empty( $_POST['booking-date'] ) ? sanitize_text_field( $_POST['booking-date'] ) : ''  );
	$mtrap_bus_search_return_date  = ( ! empty( $_POST['return-date'] ) ? sanitize_text_field( $_POST['return-date'] ) : '' );
	$disabled_class                = empty( $mtrap_bus_search_from_city ) ? 'disabled' : '';
	?>
	<div class="tab-content city-dropdown">
		<form action="<?php echo home_url( 'transportation-listing/' ); ?>" class="booking_form" method="post" id="booking_form">
			<input type="hidden" id="is-round-trip" name="is-round-trip" value="<?php echo $atts['show']; ?>">
			<div class="column-bus-search city-value-one">
				<select class="destination_from" class="js-city form-control" name="destination_from">
						<option value=""><?php echo __( 'Select From City', 'winger' ); ?></option>
					<?php
					$bus_stops = get_terms(
						array(
							'taxonomy'   => 'bus-stops',
							'hide_empty' => false,
						)
					);
					foreach ( $bus_stops as $bus_stop ) {
						echo '<option ' . selected( esc_html( $bus_stop->term_id ), $mtrap_bus_search_from_city ) . 'value="' . esc_html( $bus_stop->term_id ) . '" >' . esc_html( $bus_stop->name ) . '</option>';
					}
					?>
				</select>
				<div class="destination-from-err"></div>
			</div>
			<div class="mtrap-swap-cities"><a href="javascript:void(0);"><img src="/wp-content/uploads/2024/05/swap.png" height="40px" width="40px"></a></div>
			<div class="column-bus-search city-value-two">
				<select class="destination_to" name="destination_to" <?php echo $disabled_class; ?>>
					<option  value=""><?php echo __( 'Select To City', 'winger' ); ?></option>
					<?php
					if ( ! empty( $mtrap_bus_search_from_city ) ) {

						$to_meta_fields = get_term_meta( $mtrap_bus_search_from_city, 'mtrap_bus_stops_route_meta', true );
						if ( ! empty( $to_meta_fields ) ) {
							foreach ( $to_meta_fields as $related_stations_meta ) {
								$mtrap_term_name = get_term( $related_stations_meta )->name;
								if ( ! empty( $mtrap_term_name ) ) {
									echo '<option ' . selected( esc_html( $related_stations_meta ), $mtrap_bus_search_to_city ) . ' value=' . esc_html( $related_stations_meta ) . '>' . esc_html( ucfirst( $mtrap_term_name ) ) . '</option>';
								}
							}
						}
					}
					?>
				</select>
				<div class="destination-to-err"></div>
			</div>
			<div class="column-bus-search booking-date">
				<input type="text" readonly class="bookingdate" name="booking-date" placeholder="<?php echo __( 'Booking Date', 'winger' ); ?>" value="<?php echo esc_html( $mtrap_bus_search_booking_date ); ?>">
				<div class="booking-date-err"></div>
			</div>
			<?php
			// Output HTML for return date if 'show' attribute is true.
			if ( $show_return_date ) {
				?>
				<div class="column-bus-search return-date">
					<input type="text" readonly class="returndate" name="return-date" placeholder="<?php echo __( 'Return Date', 'winger' ); ?>" value="<?php echo esc_html( $mtrap_bus_search_return_date ); ?>">
					<div class="return-date-err"></div>
				</div>
				<?php
			}
			?>
			<div class="column-bus-search">
				<input type="submit" class="submit-btn" value="<?php echo __( 'Search', 'winger' ); ?>">
			</div>
		</form>
	</div>
	<?php
	return ob_get_clean();
}


add_shortcode( 'mtrap_order_modification_form_shortcode', 'mtrap_order_modification_shortcode_function' );
/**
 * Modify your journey order details.
 */
function mtrap_order_modification_shortcode_function() {
	ob_start();
	wp_enqueue_style( 'jquery-ui-css' );
	wp_enqueue_script( 'jquery-ui-js' );

	// Bus stops query.
	$bus_stops = get_terms(
		array(
			'taxonomy'   => 'bus-stops',
			'hide_empty' => false,
		)
	);

	// local variables.
	$mtrap_om_booking_number = ! empty( $_POST['booking_number'] ) ? sanitize_text_field( $_POST['booking_number'] ) : '';
	$mtrap_om_from_city      = ! empty( $_POST['destination_from'] ) ? sanitize_text_field( $_POST['destination_from'] ) : '';
	$mtrap_om_to_city        = ! empty( $_POST['destination_to'] ) ? sanitize_text_field( $_POST['destination_to'] ) : '';
	$mtrap_om_email          = ! empty( $_POST['journeyemail'] ) ? sanitize_text_field( $_POST['journeyemail'] ) : '';
	$mtrap_om_journey_date   = ! empty( $_POST['journeydate'] ) ? sanitize_text_field( $_POST['journeydate'] ) : '';
	$disabled_class          = empty( $mtrap_om_from_city ) ? 'disabled' : '';

	?>
	<div class="mtrap-ticket-modification-outer">
		<form action="<?php echo home_url( 'transportation-order-modification/' ); ?>" class="transportation_ticket_modification_form" method="post">
			<div class="column-bus-search booking-number">
				<input type="text" class="booking_number" name="booking_number" required placeholder="<?php echo __( 'Booking Number', 'winger' ); ?>" value="<?php echo esc_html( $mtrap_om_booking_number ); ?>">
				<div class="booking-number-err"></div>
			</div>
			<div class="column-bus-search city-value-one">
				<select class="destination_from" class="js-city form-control" name="destination_from">
					<option value=""><?php echo __( 'From City', 'winger' ); ?></option>
					<?php
					foreach ( $bus_stops as $bus_stop ) {
						echo '<option ' . selected( esc_html( $bus_stop->term_id ), $mtrap_om_from_city ) . 'value="' . esc_html( $bus_stop->term_id ) . '" >' . esc_html( $bus_stop->name ) . '</option>';
					}
					?>
				</select>
				<div class="destination-from-om-err"></div>
			</div>
			<div class="column-bus-search city-value-two">
				<select class="destination_to" name="destination_to" <?php echo esc_html( $disabled_class ); ?>>
					<option  value=""><?php echo __( 'To City', 'winger' ); ?></option>
					<?php
					if ( ! empty( $mtrap_om_from_city ) ) {
						$to_meta_fields = get_term_meta( $mtrap_om_from_city, 'mtrap_bus_stops_route_meta', true );
						if ( ! empty( $to_meta_fields ) ) {
							foreach ( $to_meta_fields as $related_stations_meta ) {
								$mtrap_term_name = get_term( $related_stations_meta )->name;
								if ( ! empty( $mtrap_term_name ) ) {
									echo '<option ' . selected( esc_html( $related_stations_meta ), $mtrap_om_to_city ) . ' value=' . esc_html( $related_stations_meta ) . '>' . esc_html( ucfirst( $mtrap_term_name ) ) . '</option>';
								}
							}
						}
					}
					?>
				</select>
				<div class="destination-to-om-err"></div>
			</div>
			<div class="column-bus-search journey-email">
				<input type="email" class="journeyemail" name="journeyemail" required value="<?php echo esc_html( $mtrap_om_email ); ?>" placeholder="<?php echo __( 'Email', 'winger' ); ?>">
				<div class="journey-email-err"></div>
			</div>
			<div class="column-bus-search journey-date">
				<input type="text" readonly class="journeydate" name="journeydate" placeholder="<?php echo __( 'Journey Date', 'winger' ); ?>" value="<?php echo esc_html( $mtrap_om_journey_date ); ?>">
				<div class="journey-date-err"></div>
			</div>
			<div class="column-modify-ticket">
				<input type="submit" class="submit-journey-modification-btn submit-btn" value="<?php echo __( 'Search', 'winger' ); ?>">
			</div>
		</form>
	</div>
	<?php
	return ob_get_clean();
}
