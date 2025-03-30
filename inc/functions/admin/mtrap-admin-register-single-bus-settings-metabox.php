<?php
/**
 * Theme functions to register custom meta box for the products
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'add_meta_boxes', 'mtrap_register_bus_options_meta_box', 1 );
/**
 * Register meta box bus options.
 */
function mtrap_register_bus_options_meta_box() {
		add_meta_box( 'bus-settings', __( 'Transport Settings', 'winger' ), 'mtrap_bus_options_meta_box_callback', 'product' );
}

/**
 * Meta box display callback.
 *
 * @param object $post $post.
 */
function mtrap_bus_options_meta_box_callback( $post ) {
	global $wpdb;
	
	// Queries for fetching the dynamic data.
	
	// Get Bus type taxonomy terms.
	$mtrap_get_bus_type_taxonomy_terms = get_terms(
		array(
			'taxonomy'   => 'bus-types',
			'hide_empty' => false,
		)
	);

	// Get Bus stops taxonomy terms.
	$mtrap_get_bus_stop_taxonomy_terms = get_terms(
		array(
			'taxonomy'   => 'bus-stops',
			'hide_empty' => false,
		)
	);

	// Get Bus seat-class taxonomy terms.
	$mtrap_get_seat_class_taxonomy_terms = get_terms(
		array(
			'taxonomy'   => 'seat-class',
			'hide_empty' => false,
		)
	);

	// Get Bus seat-class taxonomy terms.
	$mtrap_get_passenger_type_taxonomy_terms = get_terms(
		array(
			'taxonomy'   => 'passenger-type',
			'hide_empty' => false,
		)
	);
    
    // get time.
    $current_timestamp                              = time();
    
    // get options. 
    $mtrap_status_change_after_hours                = ! empty( get_option( 'bus_status_change_after_hours') ) ? get_option( 'bus_status_change_after_hours') : '';
    
	// meta variables.
	$mtrap_bus_status_meta_value                    = ! empty( get_post_meta( $post->ID, 'mtrap_bus_status', true ) ) ? get_post_meta( $post->ID, 'mtrap_bus_status', true ) : '';
	$mtrap_coach_type_meta_value                    = ! empty( get_post_meta( $post->ID, 'mtrap_bus_coach_type', true ) ) ? get_post_meta( $post->ID, 'mtrap_bus_coach_type', true ) : '';
	$mtrap_bus_advance_booking_meta_value           = ! empty( get_post_meta( $post->ID, 'mtrap_bus_advance_booking', true ) ) ? get_post_meta( $post->ID, 'mtrap_bus_advance_booking', true ) : 1;
	$mtrap_bus_off_dates_range_from_date_meta_value = ! empty( get_post_meta( $post->ID, 'mtrap_bus_off_dates_range_from_date', true ) ) ? get_post_meta( $post->ID, 'mtrap_bus_off_dates_range_from_date', true ) : '';
	$mtrap_bus_off_dates_range_to_date_meta_value   = ! empty( get_post_meta( $post->ID, 'mtrap_bus_off_dates_range_to_date', true ) ) ? get_post_meta( $post->ID, 'mtrap_bus_off_dates_range_to_date', true ) : '';
	$mtrap_bus_off_day_meta_value                   = ! empty( get_post_meta( $post->ID, 'mtrap_bus_off_day', true ) ) ? get_post_meta( $post->ID, 'mtrap_bus_off_day', true ) : '';
	$mtrap_bus_tax_meta_value                       = ! empty( get_post_meta( $post->ID, 'mtrap_bus_tax', true ) ) ? get_post_meta( $post->ID, 'mtrap_bus_tax', true ) : '';
	$transport_boarding_time                        = ! empty( get_post_meta( $post->ID, 'mtrap_boarding_time', true ) ) ? get_post_meta( $post->ID, 'mtrap_boarding_time', true ) : '';
    $updated_bus_booking_timestamp                  = ! empty( $transport_boarding_time ) && ! empty( $mtrap_status_change_after_hours ) ? strtotime($transport_boarding_time) - ($mtrap_status_change_after_hours * 3600) : 0;
    
    
	// concate two date range strings.
	$mtrap_date_range_values = ! empty( $mtrap_bus_off_dates_range_from_date_meta_value ) && ! empty( $mtrap_bus_off_dates_range_to_date_meta_value ) ? gmdate( 'd-m-Y', strtotime( $mtrap_bus_off_dates_range_from_date_meta_value ) ) . ' to ' . gmdate( 'd-m-Y', strtotime( $mtrap_bus_off_dates_range_to_date_meta_value ) ) : '';
	
	// get bus and station details.
	$get_bus_details = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mtrap_custom_bus_stops WHERE post_id=%d", $post->ID ), ARRAY_A );
	
	// get last bus station details.
	$get_last_bus_station_details = end($get_bus_details);
	?>

	<div class="mtrap-bus-options-meta-box-outer-wrap">
		<div id="woocommerce-product-data" class="mtrap-bus-options-product-meta-box">
			<div class="inside">
				<div class="panel-wrap mtrap-bus-options-sidebar">
					<ul class="mtrap-bus-options-sidebar-inner-list wc-tabs">
					    <li class="mtrap-bus-status-settings general_tab hide_if_grouped active">
							<a href="#bus_status_settings">
								<span class="fas fa-bus-alt"></span>
								<span>
									<?php esc_html_e( 'Transportation Status', 'winger' ); ?>
								</span>
							</a>
						</li>
						<li class="mtrap-bus-general-settings general_tab hide_if_grouped active">
							<a href="#bus_general_settings">
								<span class="fas fa-tools"></span>
								<span>
									<?php esc_html_e( 'Coach Settings', 'winger' ); ?>
								</span>
							</a>
						</li>
						<li class="mtrap-bus-inventory-options inventory_tab" style="">
							<a href="#bus_seat_settings">
								<span class="fas fa-chair"></span>
								<span>
									<?php esc_html_e( 'Seat Configure', 'winger' ); ?>
								</span>
							</a>
						</li>
						<li class="mtrap-bus-date-settings date_tab" style="">
							<a href="#bus_date_settings">
								<span class="fas fa-calendar-alt"></span>
								<span>
									<?php esc_html_e( 'Date Settings', 'winger' ); ?>
								</span>
							</a>
						</li>
						<li class="mtrap-bus-pricing-route-settings pricing_route_tab">
							<a href="#bus_pricing_route_settings">
								<span class="fas fa-map-marked-alt"></span>
								<span>
									<?php esc_html_e( 'Route & Seat Pricing', 'winger' ); ?>
								</span>
							</a>
						</li>
						<li class="mtrap-bus-pricing-route-settings pricing_route_tab">
							<a href="#bus_seat_class_price_settings">
								<span class="fas fa-hand-holding-usd"></span>
								<span>
									<?php esc_html_e( 'Seat Class & Pricing', 'winger' ); ?>
								</span>
							</a>
						</li>
						<li class="mtrap-bus-tax-settings pricing_route_tab">
							<a href="#tax_settings">
								<span class="fas fa-money-check-alt"></span>
								<span>
									<?php esc_html_e( 'Service fees settings', 'winger' ); ?>
								</span>
							</a>
						</li>
					</ul>
					<div id="bus_status_settings" class="panel woocommerce_options_panel" style="">
                        <div class="options_group pricing hidden" style="display: block;">
                            <div class="form-field">
                                <div class="mtrap-admin-meta-heading">
                                    <h2>
                                        <?php esc_html_e( 'Transportation Status', 'winger' ); ?>
                                    </h2>
                                    <small>
                                        <?php esc_html_e( 'Please select the status of the Transport.', 'winger' );
                                        ?>
                                    </small>
                                </div>
                                <div class="field-section">
                                    <div>
                                        <label for="mtrap_bus_status">
                                            <?php esc_html_e( 'Transportation Status', 'winger' ); ?>
                                        </label>
                                        <select name="mtrap_bus_status" id="mtrap_bus_status">
                                            <?php 
                                            $departureTimestamp = !empty($get_last_bus_station_details['station_departure_time']) ? strtotime($get_last_bus_station_details['station_departure_time']) : 0;
                                            
                                            // Check if current time falls within the allowed status change period
                                            if ($departureTimestamp && $current_timestamp >= $updated_bus_booking_timestamp && $current_timestamp <= $departureTimestamp) { ?>
                                                <option value="" disabled>
                                                    <?php printf( esc_html__( 'Transportation status can only be changed up to %d hours before departure or after the journey is complete.', 'winger' ), intval($mtrap_status_change_after_hours) ); ?>
                                                </option>
                                            <?php } else { ?>
                                                <option value="">
                                                    <?php esc_html_e( 'Please select today’s transportation status', 'winger' ); ?>
                                                </option>
                                                <option value="On Time" <?php selected($mtrap_bus_status_meta_value, 'On Time'); ?>>
                                                    <?php esc_html_e( 'On Time', 'winger' ); ?>
                                                </option>
                                                <option value="Cancelled" <?php selected($mtrap_bus_status_meta_value, 'Cancelled'); ?>>
                                                    <?php esc_html_e( 'Cancelled', 'winger' ); ?>
                                                </option>
                                                <option value="Delayed" <?php selected($mtrap_bus_status_meta_value, 'Delayed'); ?>>
                                                    <?php esc_html_e( 'Delayed', 'winger' ); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
					<div id="bus_general_settings" class="panel woocommerce_options_panel" style="">
						<div class="options_group pricing hidden" style="display: block;">
							<div class="form-field">
								<div class="mtrap-admin-meta-heading">
									<h2>
										<?php esc_html_e( 'Coach Settings', 'winger' ); ?>
									</h2>
									<small>
										<?php esc_html_e( 'Please select coach of your Transport.', 'winger' ); ?>
									</small>
								</div>

								<div class="field-section">
									<div>
										<label for="mtrap_bus_coach_type">
											<?php esc_html_e( 'Coach Type', 'winger' ); ?>
										</label>
										<select name="mtrap_bus_coach_type" id="mtrap_bus_coach_type">
											<option value="">
												<?php esc_html_e( 'Please select one coach', 'winger' ); ?>
											</option>
											<?php
											if ( ! empty( $mtrap_get_bus_type_taxonomy_terms ) ) {

												foreach ( $mtrap_get_bus_type_taxonomy_terms as $coach_terms ) {

													$mtrap_selected_bus_coach_type = $mtrap_coach_type_meta_value == $coach_terms->term_id ? 'selected' : '';

													echo '<option ' . esc_html( $mtrap_selected_bus_coach_type ) . ' value=' . esc_html( $coach_terms->term_id ) . '>' . esc_html( $coach_terms->name ) . '</option>';
												}
											}
											?>
										</select>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div id="bus_seat_settings" class="panel woocommerce_options_panel hidden" style="display: none;">
						<div class="options_group pricing hidden" style="display: block;">
							<div class="form-field">
								<div class="mtrap-admin-meta-heading">
									<h2>
										<?php esc_html_e( 'Seat Settings', 'winger' ); ?>
									</h2>
									<small>
										<?php esc_html_e( 'Configure the total number of seats for transport.', 'winger' ); ?>
									</small>
								</div>
								<div class="field-section">
									<?php
									if ( ! empty( $mtrap_get_seat_class_taxonomy_terms ) ) {
										foreach ( $mtrap_get_seat_class_taxonomy_terms as $seat_stock ) {
											$get_mtrap_seat_stock_meta = get_post_meta( $post->ID, 'mtrap_seat_stock', true );
											$mtrap_seat_stock_meta_val = ( ! empty( $get_mtrap_seat_stock_meta[ $seat_stock->slug ] ) ) ? esc_html( $get_mtrap_seat_stock_meta[ $seat_stock->slug ] ) : '';
											?>
											<div>
												<label for="seat_stock">
												<?php esc_html_e( $seat_stock->name ); ?>
													<span class="mtrap-seats">
													<?php esc_html_e( 'seats', 'winger' ); ?>
													</span>
												</label>
												<input type="number" term-id = "<?php echo esc_html( $seat_stock->term_id ); ?>" id="seat_stock" name="seat_stock[<?php echo esc_html( $seat_stock->slug ); ?>][]" min="0" max="1000" value="<?php echo esc_html( $mtrap_seat_stock_meta_val ); ?>"/>
											</div>
											<?php
										}
									}
									?>
								</div>
							</div>
						</div>
					</div>
					<div id="bus_date_settings" class="panel woocommerce_options_panel hidden" style="display: none;">
						<div class="options_group pricing hidden" style="display: block;">
							<div class="form-field">
								<div class="mtrap-admin-meta-heading">
									<h2>
										<?php esc_html_e( 'Transport Date Settings', 'winger' ); ?>
									</h2>
									<small>
										<?php esc_html_e( 'Please set the number of days for advance for bookings, designate the transport holidays and non-operational days.', 'winger' ); ?>
									</small>
								</div>

								<div class="field-section">
									<div>
										<label for="advance-booking">
											<?php esc_html_e( 'Maximum days advanced booking start', 'winger' ); ?>
										</label>
											<input type="number" id="advance-booking" name="advance-booking" min="1" max="1000" value="<?php echo esc_html( $mtrap_bus_advance_booking_meta_value ); ?>">
									</div>
									<div>
										<label for="off-dates-range">
											<?php esc_html_e( 'Transport off dates in range', 'winger' ); ?>
										</label>
											<input id="off-dates-range" readonly type="text" name="off-dates-range" placeholder="Please select date range" value="<?php echo esc_html( $mtrap_date_range_values ); ?>">
									</div>
									<div>
										<h2>
											<?php esc_html_e( 'Transport Off Day', 'winger' ); ?>
										</h2>
									</div>
									<div>
										<?php
										$mtrap_monday_checked    = ! empty( $mtrap_bus_off_day_meta_value ) && in_array( 'monday', $mtrap_bus_off_day_meta_value ) ? 'checked' : '';
										$mtrap_tuesday_checked   = ! empty( $mtrap_bus_off_day_meta_value ) && in_array( 'tuesday', $mtrap_bus_off_day_meta_value ) ? 'checked' : '';
										$mtrap_wednesday_checked = ! empty( $mtrap_bus_off_day_meta_value ) && in_array( 'wednesday', $mtrap_bus_off_day_meta_value ) ? 'checked' : '';
										$mtrap_thursday_checked  = ! empty( $mtrap_bus_off_day_meta_value ) && in_array( 'thursday', $mtrap_bus_off_day_meta_value ) ? 'checked' : '';
										$mtrap_friday_checked    = ! empty( $mtrap_bus_off_day_meta_value ) && in_array( 'friday', $mtrap_bus_off_day_meta_value ) ? 'checked' : '';
										$mtrap_saturday_checked  = ! empty( $mtrap_bus_off_day_meta_value ) && in_array( 'saturday', $mtrap_bus_off_day_meta_value ) ? 'checked' : '';
										$mtrap_sunday_checked    = ! empty( $mtrap_bus_off_day_meta_value ) && in_array( 'sunday', $mtrap_bus_off_day_meta_value ) ? 'checked' : '';
										?>
										<input type="checkbox" <?php echo esc_html( $mtrap_monday_checked ); ?> id="weekday-mon" name="bus_off_day[]" value="monday" >
										<label for="weekday-mon">
											<?php esc_html_e( 'Monday', 'winger' ); ?>
										</label>
									</div>
									<div>
										<input type="checkbox" <?php echo esc_html( $mtrap_tuesday_checked ); ?> id="weekday-tue" name="bus_off_day[]" value="tuesday" >
										<label for="weekday-tue">
											<?php esc_html_e( 'Tuesday', 'winger' ); ?>
										</label>
									</div>
									<div>
										<input type="checkbox" <?php echo esc_html( $mtrap_wednesday_checked ); ?> id="weekday-wed" name="bus_off_day[]" value="wednesday">
										<label for="weekday-wed">
											<?php esc_html_e( 'Wednesday', 'winger' ); ?>
										</label>
									</div>
									<div>
										<input type="checkbox" <?php echo esc_html( $mtrap_thursday_checked ); ?> id="weekday-thu" name="bus_off_day[]" value="thursday" >
										<label for="weekday-thu">
											<?php esc_html_e( 'Thursday', 'winger' ); ?>
										</label>
									</div>
									<div>
										<input type="checkbox" <?php echo esc_html( $mtrap_friday_checked ); ?> id="weekday-fri" name="bus_off_day[]" value="friday"  >
										<label for="weekday-fri">
											<?php esc_html_e( 'Friday', 'winger' ); ?>
										</label>
									</div>
									<div>
										<input type="checkbox" <?php echo esc_html( $mtrap_saturday_checked ); ?> id="weekday-sat" name="bus_off_day[]" value="saturday">
										<label for="weekday-sat">
											<?php esc_html_e( 'Saturday', 'winger' ); ?>
										</label>
									</div>
									<div>
										<input type="checkbox" <?php echo esc_html( $mtrap_sunday_checked ); ?> id="weekday-sun" name="bus_off_day[]" value="sunday" >
										<label for="weekday-sun">
											<?php esc_html_e( 'Sunday', 'winger' ); ?>
										</label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div id="bus_pricing_route_settings" class="panel woocommerce_options_panel hidden"
						style="display: none;">
						<div class="options_group pricing hidden" style="display: block;">
							<div class="form-field">
								<div class="mtrap-admin-meta-heading">
									<h2>
										<?php esc_html_e( 'Routing Settings', 'winger' ); ?>
									</h2>
									<small>
										<?php esc_html_e( 'Please configure your transport route, including the boarding points, stations, station arrival times, and pricing for both adults and children.', 'winger' ); ?>
									</small>
								</div>
								<div class="field-section">
									<div class="boarding-bus-stop">
										<label for="mtrap_bus_stops">
											<?php esc_html_e( 'Select boarding point', 'winger' ); ?>
										</label>
										<select name="mtrap_bus_stops" id="mtrap_bus_stops">
											<option value="">
												<?php esc_html_e( 'Please select boarding point', 'winger' ); ?>
											</option>
											<?php
											if ( ! empty( $mtrap_get_bus_stop_taxonomy_terms ) ) {
												$get_mtrap_bus_stops = get_post_meta( $post->ID, 'mtrap_bus_stops', true );
												foreach ( $mtrap_get_bus_stop_taxonomy_terms as $bus_stops ) {
													$boarding_selected = ( $bus_stops->term_id == $get_mtrap_bus_stops ) ? 'selected' : '';
													echo '<option ' . $boarding_selected . ' value=' . esc_html( $bus_stops->term_id ) . '>' . esc_html( $bus_stops->name ) . '</option>';
												}
											}
											?>
										</select>
										<img class="mtrap-boarding-loader" style="width:30px; display:none;" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/loader.gif">
									</div>
								</div>
								<div class="field-section bus-details">
									<?php
									$mtrap_bus_stops           = get_post_meta( $post->ID, 'mtrap_bus_stops', true );
									$boarding_time             = get_post_meta( $post->ID, 'mtrap_boarding_time', true );
									$mtrap_get_currency_symbol = get_woocommerce_currency_symbol();
									
									if ( ! empty( $mtrap_bus_stops ) && ! empty( $get_bus_details ) ) {
										$boarding_point_bus              = get_term_by( 'id', $mtrap_bus_stops, 'bus-stops' );
										$boadring_name                   = ! empty( $boarding_point_bus ) ? $boarding_point_bus->name : '';
										$boarding_station                = get_term_meta( $mtrap_bus_stops, 'mtrap_bus_stops_pickuppoint_meta', true );
										$boarding_station_address        = get_term_meta( $mtrap_bus_stops, 'mtrap_bus_stops_pickuppoint_address', true );
										$mtrap_get_related_stations_meta = get_term_meta( $mtrap_bus_stops, 'mtrap_bus_stops_route_meta', true );
										?>
										<div class="boarding-point">
											<label for="boarding-point-bus">
												<?php esc_html_e( 'Boarding point', 'winger' ); ?>
											</label>
											<input type="text" readonly class="boarding-point-bus" name="boarding-point-bus" value="<?php echo esc_html( $boadring_name ); ?>">
										</div>  
										<div class="boarding-station">
											<label for="boarding-station">
												<?php esc_html_e( 'Boarding station', 'winger' ); ?>
											</label>
											<input type="text" readonly class="boarding-station" name="boarding-station" value="<?php echo esc_html( $boarding_station ); ?>">
										</div>
										<div class="boarding-address">
											<label for="boarding-address">
												<?php esc_html_e( 'Boarding address', 'winger' ); ?>
											</label>
											<input type="text" readonly class="boarding-address" name="boarding-address" value="<?php echo esc_html( $boarding_station_address ); ?>">
										</div>
										<div>
											<label for="boarding-time">
												<?php esc_html_e( 'Departure Time', 'winger' ); ?>
											</label>
											<input type="text"  class="boarding-time" name="boarding-time" value="<?php echo $boarding_time; ?>">
										</div>
										<div class="mtrap-admin-meta-heading">
											<h4><?php esc_html_e( 'Configure Other Stations', 'winger' ); ?></h4>
											<small><?php esc_html_e( 'Please configure other stations for your transport.', 'winger' ); ?></small>
										</div>
										<?php
										$x = 0;
										if ( ! empty( $get_bus_details ) ) {
											foreach ( $get_bus_details as $get_bus_detail ) {

												$station_stop_address = get_term_meta( $get_bus_detail['station_stop'], 'mtrap_bus_stops_pickuppoint_address', true );
												$station_stop_point   = get_term_meta( $get_bus_detail['station_stop'], 'mtrap_bus_stops_pickuppoint_meta', true );

												?>
												<div class="field-section">
													<div class="mtrap-admin-meta-heading">
														<h4><?php esc_html_e( 'Add information about this station.', 'winger' ); ?></h4>
													</div>
													<div>
														<label for="mtrap_bus_stops_callback">
															<?php esc_html_e( 'Stop', 'winger' ); ?>
														</label>
														<select name="mtrap_bus_stops_callback[]" class="mtrap_bus_stops_callback">
															<option value=""><?php esc_html_e( 'Please select station', 'winger' ); ?></option>
															<?php
															if ( ! empty( $mtrap_get_related_stations_meta ) ) {
																foreach ( $mtrap_get_related_stations_meta as $related_stations_meta ) {
																	$stops_details  = get_term_by( 'id', $related_stations_meta, 'bus-stops' );
																	$stops_selected = ( $related_stations_meta == $get_bus_detail['station_stop'] ) ? 'selected' : '';
																	if ( ! empty( $stops_details->name ) ) {
																		echo '<option ' . $stops_selected . ' value=' . esc_html( $related_stations_meta ) . '>' . esc_html( ucfirst( $stops_details->name ) ) . '</option>';
																	}
																}
															}
															?>
														</select>
													</div>
													<div>
														<label for="mtrap_bus_stops_point_callback">
															<?php esc_html_e( 'Stop point', 'winger' ); ?>
														</label>
														<input type="text" readonly class="stop-point" name="stop-point" value="<?php echo esc_html( $station_stop_point ); ?>">
													</div>
													<div>
														<label for="mtrap_bus_stops_address_callback">
															<?php esc_html_e( 'Stop address', 'winger' ); ?>
														</label>
														<input type="text" readonly class="stop-address" name="stop-address" value="<?php echo esc_html( $station_stop_address ); ?>">
													</div>
													<div class="passenger-type">
														<?php
														if ( ! empty( $mtrap_get_passenger_type_taxonomy_terms ) ) {
															foreach ( $mtrap_get_passenger_type_taxonomy_terms as $passenger_type ) {
																$adults_unserialize_price = ! empty( $get_bus_detail['price'] ) ? maybe_unserialize( $get_bus_detail['price'] ) : '';
																$adults_price             = ! empty( $adults_unserialize_price[ $passenger_type->slug ] ) ? $adults_unserialize_price[ $passenger_type->slug ][0] : '';
																?>
																<div style="width:100%; display: inline-block; margin-bottom: 30px;">
																	<label for="passenger_type_pricing">
																		<?php esc_html_e( $passenger_type->name ); ?>
																		<span class="mtrap-percentage-symbol">
																			<?php echo 'Price ( ' . $mtrap_get_currency_symbol . ' )'; ?>
																		</span>
																	</label>
																	<input type="number" term-slug = "<?php echo esc_html( $passenger_type->slug ); ?>" id="passenger_type_pricing" class="passenger-type-price" name="passenger_type_pricing[<?php echo $get_bus_detail['station_stop']; ?>][<?php echo esc_html( $passenger_type->slug ); ?>][]" min="0" max="40000" value="<?php echo $adults_price; ?>"/>
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
															<option value="same-day" <?php selected( $get_bus_detail['station_day'], 'same-day' ); ?>><?php esc_html_e( 'Same Day', 'winger' ); ?></option>
															<option value="different-day" <?php selected( $get_bus_detail['station_day'], 'different-day' ); ?>><?php esc_html_e( 'Different Day', 'winger' ); ?></option>
														</select>
													</div>
													<div style="<?php echo $get_bus_detail['station_day'] == 'same-day' ? 'display:none' : 'display:block'; ?>">
														<label for="station_day_difference">
															<?php esc_html_e( 'Day difference', 'winger' ); ?>
														</label>
														<input type="number" class="station_day_difference" name="station_day_difference[]" min="0" max="10" value="<?php echo (int) $get_bus_detail['station_day_difference']; ?>">
													</div>
													<div>
														<label for="station_time">
															<?php esc_html_e( 'Arrival time', 'winger' ); ?>
														</label>
														<input type="text" class="station_time" name="station_time[]" value="<?php echo gmdate( 'H:i:s', strtotime( $get_bus_detail['station_time'] ) ); ?>">
													</div>
													<div>
														<label for="station_departure_time">
															<?php esc_html_e( 'Departure time', 'winger' ); ?>
														</label>
														<input type="text" class="station_departure_time" name="station_departure_time[]" value="<?php echo gmdate( 'H:i:s', strtotime( $get_bus_detail['station_departure_time'] ) ); ?>">
														<?php if ( $x !== 0 ) { ?>
														<button data-id="<?php echo $get_bus_detail['ID']; ?>" type="button" class="mtrap-remove-bus-stop button button-primary button-large"><?php esc_html_e( 'Remove bus stop', 'winger' ); ?></button>
														<?php } ?>
														<input type="hidden" name="station_id[]" value="<?php echo $get_bus_detail['ID']; ?>">
													</div>
												</div>
												<?php
												++$x; }
										}
										?>
										<div class="mtrap-more-bus-stops"></div>
										<div class="mtrap-add-bus-stops">
											<button type="button" class="mtrap-add-bus-stop button button-primary button-large"><?php esc_html_e( 'Add new stop', 'winger' ); ?></button>
										</div>
										<img class="mtrap-boarding-loader" style="width:30px; display:none;" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/loader.gif">
									<?php } ?>
								</div>
							</div>
						</div>
					</div>
					<div id="bus_seat_class_price_settings" class="panel woocommerce_options_panel hidden"
						style="display: none;">
						<div class="options_group pricing hidden" style="display: block;">
							<div class="form-field inited inited_media_selector">
								<div class="mtrap-admin-meta-heading">
									<h2><?php esc_html_e( 'Seat class & Pricing settings', 'winger' ); ?></h2>
									<small><?php esc_html_e( 'Select the seat class, and note that pricing for each class will increase on a percentage basis.', 'winger' ); ?></small>
								</div>
								<div class="field-section" style="margin-bottom:10px;">
									<div class="default-seat-class">
										<label for="mtrap_default_seat_class">
											<?php esc_html_e( 'Default seat class', 'winger' ); ?>
										</label>
										<select name="mtrap_default_seat_class" id="mtrap_default_seat_class">
											<option value="">
												<?php esc_html_e( 'Please select the default seat class', 'winger' ); ?>
											</option>
											<?php
											if ( ! empty( $mtrap_get_seat_class_taxonomy_terms ) ) {
												$get_mtrap_seat_class = get_post_meta( $post->ID, 'mtrap_default_seat_class', true );
												foreach ( $mtrap_get_seat_class_taxonomy_terms as $seat_class ) {
													$seat_class_selected = ( $seat_class->term_id == $get_mtrap_seat_class ) ? 'selected' : '';
													echo '<option ' . $seat_class_selected . ' value=' . esc_html( $seat_class->term_id ) . '>' . esc_html( $seat_class->name ) . '</option>';
												}
											}
											?>
										</select>
										<img class="mtrap-boarding-loader" style="width:30px; display:none;" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/loader.gif">
									</div>
								</div>
								<div class="field-section">
										<?php
										if ( ! empty( $mtrap_get_seat_class_taxonomy_terms ) ) {
											foreach ( $mtrap_get_seat_class_taxonomy_terms as $seat_class ) {
												$get_mtrap_seat_class_meta = get_post_meta( $post->ID, 'mtrap_seat_class', true );
												$mtrap_seat_class_meta_val = ( ! empty( $get_mtrap_seat_class_meta[ $seat_class->slug ] ) ) ? esc_html( $get_mtrap_seat_class_meta[ $seat_class->slug ] ) : '';
												?>
												<div>
													<label for="seat_class_pricing">
														<?php esc_html_e( $seat_class->name ); ?>
														<span class="mtrap-percentage-symbol">
															<?php esc_html_e( 'Price (%)', 'winger' ); ?>
														</span>
													</label>
													<input type="number" term-id = "<?php echo esc_html( $seat_class->term_id ); ?>" id="seat_class_pricing" name="seat_class_pricing[<?php echo esc_html( $seat_class->slug ); ?>][]" min="0" max="1000" value="<?php echo esc_html( $mtrap_seat_class_meta_val ); ?>"/>
												</div>
												<?php
											}
										}
										?>
								</div>
							</div>
						</div>
					</div>
					<div id="tax_settings" class="panel woocommerce_options_panel hidden"
						style="display: none;">
						<div class="options_group pricing hidden" style="display: block;">
							<div class="form-field inited inited_media_selector">
								<div class="mtrap-admin-meta-heading">
									<h2><?php esc_html_e( 'Service fees settings', 'winger' ); ?></h2>
									<small><?php esc_html_e( 'Apply the appropriate tax rate to the transport service.', 'winger' ); ?></small>
								</div>
								<div class="field-section">
									<div>
										<label for="bus_tax">
											<span class="mtrap-percentage-symbol">
												<?php esc_html_e( 'Service fees (%)', 'winger' ); ?>
											</span>
										</label>
										<input type="number" id="bus_tax" name="bus_tax" min="0" max="100" value="<?php echo esc_html( $mtrap_bus_tax_meta_value ); ?>"/>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="clear"></div>
				</div>
			</div>
		</div>
	</div>
	<?php
}


add_action( 'save_post', 'mtrap_bus_options_save_meta_box', 10, 2 );
/**
 * Save meta box content.
 *
 * @param  int    $post_id $post_id.
 * @param  object $post $post.
 */
function mtrap_bus_options_save_meta_box( $post_id, $post ) {

	global $wpdb;

	// Check the logged in user has permission to edit this post .
	if ( ! current_user_can( 'manage_options' ) ) {
		return $post_id;
	}

	// if this is just a revision, don't save changes.
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	// Only set for post_type = product!.
	if ( 'product' !== $post->post_type ) {
		return;
	}

	// Conditional meta updates.
	if ( isset( $_POST['mtrap_bus_status'] ) ) {
		update_post_meta( $post_id, 'mtrap_bus_status', sanitize_text_field( $_POST['mtrap_bus_status'] ) );
		
		// send email on bus status change to the customers.
		if ( $_POST['mtrap_bus_status'] === 'Cancelled' ) {
            mtrap_cancelled_bus_status_email( $post_id );
        } elseif ( $_POST['mtrap_bus_status'] === 'Delayed' ) {
            mtrap_delayed_bus_status_email( $post_id );
        }
	}
	
	if ( isset( $_POST['mtrap_bus_coach_type'] ) ) {
		update_post_meta( $post_id, 'mtrap_bus_coach_type', sanitize_text_field( $_POST['mtrap_bus_coach_type'] ) );
	}

	if ( isset( $_POST['mtrap_default_seat_class'] ) ) {
		update_post_meta( $post_id, 'mtrap_default_seat_class', sanitize_text_field( $_POST['mtrap_default_seat_class'] ) );
	}

	if ( isset( $_POST['advance-booking'] ) ) {
		update_post_meta( $post_id, 'mtrap_bus_advance_booking', sanitize_text_field( $_POST['advance-booking'] ) );
	}

	if ( isset( $_POST['off-dates-range'] ) && ! empty( $_POST['off-dates-range'] ) ) {
		$mtrap_bus_off           = explode( 'to', sanitize_text_field( $_POST['off-dates-range'] ) );
		$mtrap_bus_off_from_date = $mtrap_bus_off[0];
		$mtrap_bus_off_from_to   = $mtrap_bus_off[1];

		update_post_meta( $post_id, 'mtrap_bus_off_dates_range_from_date', sanitize_text_field( gmdate( 'Y-m-d', strtotime( $mtrap_bus_off_from_date ) ) ) );

		update_post_meta( $post_id, 'mtrap_bus_off_dates_range_to_date', sanitize_text_field( gmdate( 'Y-m-d', strtotime( $mtrap_bus_off_from_to ) ) ) );
	}
	
	if ( empty( $_POST['off-dates-range'] ) ) {
		update_post_meta( $post_id, 'mtrap_bus_off_dates_range_from_date', '' );
		update_post_meta( $post_id, 'mtrap_bus_off_dates_range_to_date', '' );
	}

	if ( isset( $_POST['bus_off_day'] ) ) {
		update_post_meta( $post_id, 'mtrap_bus_off_day', array_map( 'sanitize_text_field', $_POST['bus_off_day'] ) );
	}

	if ( isset( $_POST['mtrap_bus_stops'] ) ) {

		update_post_meta( $post_id, 'mtrap_bus_stops', sanitize_text_field( $_POST['mtrap_bus_stops'] ) );
	}

	if ( isset( $_POST['boarding-time'] ) ) {

		update_post_meta( $post_id, 'mtrap_boarding_time', sanitize_text_field( $_POST['boarding-time'] ) );
	}

	if ( isset( $_POST['seat_stock'] ) ) {
		$mtrap_seat_stock_key_arr  = array();
		$mtrap_seat_stock_key_val  = array();
		$mtrap_filtered_seat_stock = array_filter( $_POST['seat_stock'] );

		foreach ( $mtrap_filtered_seat_stock as $class => $seat_stock ) {
			$mtrap_seat_stock_key_arr[] = $class;
			$mtrap_seat_stock_key_val[] = $seat_stock[0];
		}
		update_post_meta( $post_id, 'mtrap_seat_stock', array_combine( $mtrap_seat_stock_key_arr, $mtrap_seat_stock_key_val ) );
	}

	if ( isset( $_POST['seat_class_pricing'] ) ) {
		$mtrap_seat_class_key_arr  = array();
		$mtrap_seat_class_key_val  = array();
		$mtrap_filtered_seat_class = array_filter( $_POST['seat_class_pricing'] );

		foreach ( $mtrap_filtered_seat_class as $class => $class_price ) {
			$mtrap_seat_class_key_arr[] = $class;
			$mtrap_seat_class_key_val[] = $class_price[0];
		}
		update_post_meta( $post_id, 'mtrap_seat_class', array_combine( $mtrap_seat_class_key_arr, $mtrap_seat_class_key_val ) );
	}

	if ( isset( $_POST['bus_tax'] ) ) {

		update_post_meta( $post_id, 'mtrap_bus_tax', sanitize_text_field( $_POST['bus_tax'] ) );
	}

	// Save onboarding details.
	$mtrap_bus_stops_callback            = ! empty( $_POST['mtrap_bus_stops_callback'] ) ? array_map( 'sanitize_text_field', $_POST['mtrap_bus_stops_callback'] ) : '';
	$mtrap_bus_stop_pricing              = isset( $_POST['passenger_type_pricing'] ) ? $_POST['passenger_type_pricing'] : '';
	$mtrap_bus_stop_reach_day            = isset( $_POST['station_day'] ) ? array_map( 'sanitize_text_field', $_POST['station_day'] ) : '';
	$mtrap_bus_stop_reach_day_difference = isset( $_POST['station_day_difference'] ) ? array_map( 'sanitize_text_field', $_POST['station_day_difference'] ) : '';
	$mtrap_bus_stop_reach_time           = isset( $_POST['station_time'] ) ? array_map( 'sanitize_text_field', $_POST['station_time'] ) : '';
	$mtrap_bus_stop_departure_time       = isset( $_POST['station_departure_time'] ) ? array_map( 'sanitize_text_field', $_POST['station_departure_time'] ) : '';
	$station_id                          = isset( $_POST['station_id'] ) ? array_map( 'sanitize_text_field', $_POST['station_id'] ) : '';

	if ( ! empty( $mtrap_bus_stops_callback ) ) {

		$mtrap_bus_stops_db_table = $wpdb->prefix . 'mtrap_custom_bus_stops';

		foreach ( array_filter( $mtrap_bus_stops_callback )  as $stopkey => $stops ) {

			if ( ! empty( $station_id[ $stopkey ] ) && ! empty( $stops ) ) {

				echo $wpdb->update(
					$mtrap_bus_stops_db_table,
					array(
						'station_stop'           => $stops,
						'price'                  => ! empty( $mtrap_bus_stop_pricing[ $stops ] ) ? maybe_serialize( $mtrap_bus_stop_pricing[ $stops ] ) : '',
						'station_day'            => ! empty( $mtrap_bus_stop_reach_day[ $stopkey ] ) ? $mtrap_bus_stop_reach_day[ $stopkey ] : '',
						'station_day_difference' => ! empty( $mtrap_bus_stop_reach_day_difference[ $stopkey ] ) ? $mtrap_bus_stop_reach_day_difference[ $stopkey ] : 0,
						'station_time'           => ! empty( $mtrap_bus_stop_reach_time[ $stopkey ] ) ? $mtrap_bus_stop_reach_time[ $stopkey ] : '',
						'station_departure_time' => ! empty( $mtrap_bus_stop_departure_time[ $stopkey ] ) ? $mtrap_bus_stop_departure_time[ $stopkey ] : '',
					),
					array(
						'ID' => $station_id[ $stopkey ],
					),
					array(
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
					),
					array(
						'%d',
					)
				);

			} elseif ( ! empty( $stops ) ) {

					$wpdb->insert(
						$mtrap_bus_stops_db_table,
						array(
							'post_id'                => $post_id,
							'station_stop'           => $stops,
							'price'                  => ! empty( $mtrap_bus_stop_pricing[ $stops ] ) ? maybe_serialize( $mtrap_bus_stop_pricing[ $stops ] ) : '',
							'station_day'            => ! empty( $mtrap_bus_stop_reach_day[ $stopkey ] ) ? $mtrap_bus_stop_reach_day[ $stopkey ] : '',
							'station_day_difference' => ! empty( $mtrap_bus_stop_reach_day_difference[ $stopkey ] ) ? $mtrap_bus_stop_reach_day_difference[ $stopkey ] : 0,
							'station_time'           => ! empty( $mtrap_bus_stop_reach_time[ $stopkey ] ) ? $mtrap_bus_stop_reach_time[ $stopkey ] : '',
							'station_departure_time' => ! empty( $mtrap_bus_stop_departure_time[ $stopkey ] ) ? $mtrap_bus_stop_departure_time[ $stopkey ] : '',
						),
						array(
							'%d',
							'%s',
							'%s',
							'%s',
							'%s',
							'%s',
							'%s',
						)
					);
			}
		}
	}
}

add_action( 'admin_init', 'mtrap_bus_create_custom_table_if_not_exists' );
/**
 * Create table on admin init.
 */
function mtrap_bus_create_custom_table_if_not_exists() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'mtrap_custom_bus_stops';

	// Check if the table exists.
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) !== $table_name ) {
		// Table doesn't exist, create its .
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
            ID int NOT NULL AUTO_INCREMENT,
            post_id int(20),
            station_stop int(20),
			price text,
			station_day varchar(100),
			station_day_difference int(10),
			station_time time(3),
			station_departure_time time(3),
            PRIMARY KEY (ID)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}