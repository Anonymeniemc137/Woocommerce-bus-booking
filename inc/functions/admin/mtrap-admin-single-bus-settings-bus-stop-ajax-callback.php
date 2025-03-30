<?php
/**
 * Fetch busstops for pricing & routes tab - single procuct.
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'wp_ajax_mtrap_callback_function_selected_stop', 'mtrap_callback_function_selected_stop' );
add_action( 'wp_ajax_nopriv_mtrap_callback_function_selected_stop', 'mtrap_callback_function_selected_stop' );
/**
 * Remove default woocommerce post types.
 */
function mtrap_callback_function_selected_stop() {

	check_ajax_referer( 'stop-routing', 'security' );
	global $wpdb;

	// Get Bus seat-class taxonomy terms.
	$mtrap_get_passenger_type_taxonomy_terms = get_terms(
		array(
			'taxonomy'   => 'passenger-type',
			'hide_empty' => false,
		)
	);

	// Local variables.
	$mtrap_selected_stop             = sanitize_text_field( $_POST['selected_stop'] );
	$mtrap_get_currency_symbol       = get_woocommerce_currency_symbol();
	$mtrap_get_related_stations_meta = ! empty( get_term_meta( $mtrap_selected_stop, 'mtrap_bus_stops_route_meta' ) ) ? get_term_meta( $mtrap_selected_stop, 'mtrap_bus_stops_route_meta' ) : '';
	$mtrap_get_boarding_point_meta   = ! empty( get_term_meta( $mtrap_selected_stop, 'mtrap_bus_stops_pickuppoint_meta' ) ) ? get_term_meta( $mtrap_selected_stop, 'mtrap_bus_stops_pickuppoint_meta' ) : '';
	$delete_station                  = isset( $_POST['delete_station'] ) ? array_map( 'sanitize_text_field', $_POST['delete_station'] ) : '';
	$boadring_stations               = ! empty( $mtrap_get_boarding_point_meta[0] ) ? $mtrap_get_boarding_point_meta[0] : '';

	// Delete existing station.
	if ( ! empty( $delete_station ) ) {

		foreach ( $delete_station as $deleted_id ) {

			$wpdb->delete( $wpdb->prefix . 'mtrap_custom_bus_stops', array( 'ID' => $deleted_id ), array( '%d' ) );
		}
	}

	?>
	<div class="field-section">
		<div class="boarding-point">
			<label for="boarding-point-bus">
				<?php esc_html_e( 'Boarding Point', 'winger' ); ?>
			</label>
			<input type="text" readonly class="boarding-point-bus" name="boarding-point-bus" value="<?php echo esc_html( get_term( $mtrap_selected_stop )->name ); ?>">
		</div>  
		<div class="boarding-station">
			<label for="boarding-station">
				<?php esc_html_e( 'Boarding Station', 'winger' ); ?>
			</label>
			<input type="text" readonly class="boarding-station" name="boarding-station" value="<?php echo esc_html( $boadring_stations ); ?>">
		</div>
		<div>
			<label for="boarding-time">
				<?php esc_html_e( 'Departure time', 'winger' ); ?>
			</label>
			<input type="text" class="boarding-time" name="boarding-time">
		</div>
	</div>  
	<div class="field-section">
		<div>
			<div class="mtrap-admin-meta-heading">
				<h4><?php esc_html_e( 'Select Other Stations', 'winger' ); ?></h4>
				<small><?php esc_html_e( 'Please select other stations for your bus', 'winger' ); ?></small>
			</div>
		</div>
		<div>
			<label for="mtrap_bus_stops_callback">
				<?php esc_html_e( 'Stop', 'winger' ); ?>
			</label>
			<select name="mtrap_bus_stops_callback[]" class="mtrap_bus_stops_callback">
				<option value=""><?php esc_html_e( 'Please select station', 'winger' ); ?></option>
				<?php
				if ( ! empty( $mtrap_get_related_stations_meta[0] ) ) {
					foreach ( $mtrap_get_related_stations_meta[0] as $related_stations_meta ) {
						$stops_details = get_term_by( 'id', $related_stations_meta, 'bus-stops' );
						if ( ! empty( $stops_details->name ) ) {
							echo '<option value=' . esc_html( $related_stations_meta ) . '>' . esc_html( ucfirst( $stops_details->name ) ) . '</option>';
						}
					}
				}
				?>
			</select>
		</div>
		<div class="passenger-type">
			<?php
			if ( ! empty( $mtrap_get_passenger_type_taxonomy_terms ) ) {
				foreach ( $mtrap_get_passenger_type_taxonomy_terms as $passenger_type ) {
					?>
					<div style="width:100%; display: inline-block; margin-bottom: 30px;">
						<label for="passenger_type_pricing">
							<?php esc_html_e( $passenger_type->name ); ?>
							<span class="mtrap-percentage-symbol">
								<?php echo 'Price ( ' . $mtrap_get_currency_symbol . ' )'; ?>
							</span>
						</label>
						<input type="number" class="passenger-type-price" term-slug = "<?php echo esc_html( $passenger_type->slug ); ?>" id="passenger_type_pricing" name="passenger_type_pricing[<?php echo esc_html( $passenger_type->slug ); ?>][]" min="0" max="10000" />
					</div>
					<?php
				}
			}
			?>
		</div>
		<div>
			<label for="station_day">
				<?php esc_html_e( 'Station reaching day', 'winger' ); ?>
			</label>
			<select name="station_day[]" class="mtrap_station_days_callback">
				<option value="same-day"><?php esc_html_e( 'Same Day', 'winger' ); ?></option>
				<option value="different-day"><?php esc_html_e( 'Different Day', 'winger' ); ?></option>
			</select>
		</div>
		<div style="display:none;">
			<label for="station_day_difference">
				<?php esc_html_e( 'Day difference', 'winger' ); ?>
			</label>
			<input type="number" class="station_day_difference" name="station_day_difference[]" min="0" max="10" value="">
		</div>
		<div>
			<label for="station_time">
				<?php esc_html_e( 'Arrival Time', 'winger' ); ?>
			</label>
			<input type="text" class="station_time" name="station_time[]" value="0:00:00">
		</div>
		<div>
			<label for="station_departure_time">
				<?php esc_html_e( 'Departure time', 'winger' ); ?>
			</label>
			<input type="text" class="station_departure_time" name="station_departure_time[]" value="0:00:00">
		</div>
		<div class="mtrap-more-bus-stops"></div>
		<div class="mtrap-add-bus-stops">
			<button type="button" class="mtrap-add-bus-stop button button-primary button-large"><?php esc_html_e( 'Add new bus stop', 'winger' ); ?></button>
		</div>
	</div>
	
	<?php
	die;
}


add_action( 'wp_ajax_mtrap_callback_remove_station', 'mtrap_callback_remove_station' );
add_action( 'wp_ajax_nopriv_mtrap_callback_remove_station', 'mtrap_callback_remove_station' );
/**
 * Remove default woocommerce post types.
 */
function mtrap_callback_remove_station() {

	global $wpdb;
	check_ajax_referer( 'remove-stop-routing', 'security' );

	$mtrap_bus_stops_db_table = $wpdb->prefix . 'mtrap_custom_bus_stops';

	$stop_id = sanitize_text_field( $_POST['stop_id'] );

	if ( ! empty( $stop_id ) ) {
		$wpdb->delete( $mtrap_bus_stops_db_table, array( 'id' => $stop_id ), array( '%d' ) );
	}
	die;
}
