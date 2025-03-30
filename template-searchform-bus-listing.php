<?php
/**
 * Template Name: Searchform Transport Listing
 */

get_header();

wp_enqueue_script( 'mtrap-bus-listing-js' );
global $wpdb;

// Form fetched variables.
$mtrap_trip_status             = ! empty( $_POST['is-round-trip'] ) ? sanitize_text_field( $_POST['is-round-trip'] ) : '';
$mtrap_bus_search_from_city    = ! empty( $_POST['destination_from'] ) ? sanitize_text_field( $_POST['destination_from'] ) : '';
$mtrap_bus_search_to_city      = ! empty( $_POST['destination_to'] ) ? sanitize_text_field( $_POST['destination_to'] ) : '';
$mtrap_bus_search_booking_date = ( ! empty( $_POST['booking-date'] ) ? sanitize_text_field( $_POST['booking-date'] ) : '' );
$mtrap_bus_search_return_date  = ( ! empty( $_POST['return-date'] ) ? sanitize_text_field( $_POST['return-date'] ) :'' );

$mtrap_from_city_id = get_term_by( 'id', $mtrap_bus_search_from_city, 'bus-stops' );
$mtrap_to_city_id   = get_term_by( 'id', $mtrap_bus_search_to_city, 'bus-stops' );

// Get current language.
$mtrap_current_selected_language = defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : '';

// Get Bus passenger-type taxonomy terms.
$mtrap_get_passenger_type_taxonomy_terms = get_terms(
	array(
		'taxonomy'   => 'passenger-type',
		'hide_empty' => false,
	)
);
?>
<div class="mtrap-bus-listing-banner-section">
	<div class="search-form-section">
		<?php echo do_shortcode( '[trx_sc_layouts layout="8789"]' ); ?>
	</div>
</div>
<div class="elementor-section elementor-top-section elementor-element elementor-section-full_width elementor-section-height-min-height elementor-section-content-middle elementor-section elementor-section-boxed">
	<div class="elementor-container elementor-column-gap-extended">
		<div class="elementor-row elementor-col-100 elementor-top-column elementor-element elementor-widget-wrap">
			<?php 
			if ( $mtrap_from_city_id && $mtrap_to_city_id && $mtrap_bus_search_booking_date ) {

				// Set session on bus form submission.
				WC()->session->set( 'mtrap_next_journey_date_booking', sanitize_text_field( $mtrap_bus_search_booking_date ) );
				WC()->session->set( 'mtrap_next_journey_date_return', sanitize_text_field( $mtrap_bus_search_return_date ) );

				// Fetch search results from database.
				$mtrap_bus_search_listing_query = $wpdb->get_results(
					"SELECT busstops.post_id FROM {$wpdb->prefix}postmeta AS postmeta 
						LEFT JOIN {$wpdb->prefix}mtrap_custom_bus_stops AS busstops  ON busstops.post_id = postmeta.post_id
						LEFT JOIN {$wpdb->prefix}icl_translations AS translationt ON postmeta.post_id = translationt.element_id 
						LEFT JOIN {$wpdb->prefix}posts AS busstatus ON postmeta.post_id = busstatus.ID
						WHERE ( (postmeta.meta_key = 'mtrap_bus_stops' AND postmeta.meta_value = $mtrap_bus_search_from_city ) AND busstops.station_stop = $mtrap_bus_search_to_city AND language_code='$mtrap_current_selected_language') AND busstatus.post_status = 'publish'
						OR 
						(busstops.station_stop = $mtrap_bus_search_from_city AND (SELECT busstops.post_id FROM {$wpdb->prefix}mtrap_custom_bus_stops AS station WHERE station.post_id = busstops.post_id AND station.station_stop = $mtrap_bus_search_to_city AND busstatus.post_status = 'publish' AND station.ID > busstops.ID AND language_code='$mtrap_current_selected_language')) 
						GROUP BY busstops.post_id ORDER BY busstops.post_id DESC",
					ARRAY_A
				);
				?>
				<?php if ( count( $mtrap_bus_search_listing_query ) != 0 ) { ?>
					<div class="search-list-alert">
						<div>
							<?php echo esc_html( $mtrap_from_city_id->name ); ?>
							<?php esc_html_e( ' To ', 'winger' ); ?>
							<?php echo esc_html( $mtrap_to_city_id->name ); ?> -- <?php esc_html_e( 'Journey Date: ', 'winger' ); ?> <?php echo esc_html( $mtrap_bus_search_booking_date ); ?>
						</div>
					</div>
				<?php } ?>
			<?php } ?>
			<?php if ( $mtrap_from_city_id && $mtrap_to_city_id && empty( $mtrap_bus_search_booking_date ) ) { ?>
				<div class="search-list-alert">
				<?php esc_html_e( 'Please select the date of the journey.', 'winger' ); ?>
				</div>
			<?php } ?>
		<div class="bus-list-main">
			<?php if ( $mtrap_from_city_id && $mtrap_to_city_id && $mtrap_bus_search_booking_date && count( $mtrap_bus_search_listing_query ) != 0 ) { ?>
				<div class="bus-list-header">
					<ul>
						<li class="route-details"><?php esc_html_e( 'Route Name', 'winger' ); ?></li>
						<li class="Departure"><?php esc_html_e( 'Departure', 'winger' ); ?></li>
						<li class="Duration"><?php esc_html_e( 'Duration', 'winger' ); ?></li>
						<li class="Arrival"><?php esc_html_e( 'Arrival', 'winger' ); ?></li>
						<li class="Fare"><?php esc_html_e( 'Fare', 'winger' ); ?></li>
						<li class="Vacant"><?php esc_html_e( 'Vacant', 'winger' ); ?></li>
						<li class="Select_btn"<?php esc_html_e( 'Select', 'winger' ); ?>></li>
						<li class="bus-status"><?php esc_html_e( 'Status', 'winger' ); ?></li>
					</ul>
				</div>
				<?php
			}

			$mtrap_off_dates_range = array();

			if ( ! empty( $mtrap_bus_search_from_city ) && ! empty( $mtrap_bus_search_to_city ) && ! empty( $mtrap_bus_search_booking_date ) ) {

				if ( ! empty( $mtrap_bus_search_listing_query ) ) {

					foreach ( array_filter( $mtrap_bus_search_listing_query ) as $product_id ) {

						$mtrap_bus_off_days             = get_post_meta( $product_id['post_id'], 'mtrap_bus_off_day', true );
						$mtrap_bus_off_dates_range_from = get_post_meta( $product_id['post_id'], 'mtrap_bus_off_dates_range_from_date', true );
						$mtrap_bus_off_dates_range_to   = get_post_meta( $product_id['post_id'], 'mtrap_bus_off_dates_range_to_date', true );
						$mtrap_bus_off_weekdays         = get_post_meta( $product_id['post_id'], 'mtrap_bus_off_day', true );

						// if booking date is beetween bus off range skip current bus.
						if ( ! empty( $mtrap_bus_off_dates_range_from ) && ! empty( $mtrap_bus_off_dates_range_to ) && ! empty( $mtrap_bus_search_booking_date ) ) {
							if ( strtotime( $mtrap_bus_search_booking_date ) >= strtotime( $mtrap_bus_off_dates_range_from ) && strtotime( $mtrap_bus_search_booking_date ) <= strtotime( $mtrap_bus_off_dates_range_to ) ) {
								$mtrap_off_dates_range[]['post_id'] = $product_id['post_id'];
							}
						}

						// if booking date is on weekoff day skip current bus.
						if ( ! empty( $mtrap_bus_off_weekdays ) ) {
							foreach ( $mtrap_bus_off_weekdays as $off_weekday ) {
								// for from date.
								if ( ! empty( $mtrap_bus_search_booking_date ) ) {
									$partial_from_date   = explode( '-', $mtrap_bus_search_booking_date );
									$from_date_gregorian = gregoriantojd( $partial_from_date[1], $partial_from_date[0], $partial_from_date[2] );
									if ( ( strcasecmp( jddayofweek( $from_date_gregorian, 1 ), $off_weekday ) == 0 ) ) {
										$mtrap_off_dates_range[]['post_id'] = $product_id['post_id'];
									}
								}
								// for to date.
								if ( ! empty( $mtrap_bus_search_return_date ) ) {
									$partial_to_date   = explode( '-', $mtrap_bus_search_return_date );
									$to_date_gregorian = gregoriantojd( $partial_to_date[1], $partial_to_date[0], $partial_to_date[2] );
									if ( ( strcasecmp( jddayofweek( $to_date_gregorian, 1 ), $off_weekday ) == 0 ) ) {
										$mtrap_off_dates_range[]['post_id'] = $product_id['post_id'];
									}
								}
							}
						}
					}
				}


				if ( ! empty( $mtrap_bus_search_listing_query ) ) {

					$filter_ids = multidimensional_array_diff_by_column( array_filter( $mtrap_bus_search_listing_query ), $mtrap_off_dates_range, 'post_id' );

					if ( ! empty( $filter_ids ) ) {

						foreach ( $filter_ids as $product_id ) {

							// Local variables.
							$mtrap_coach_type_meta_value    = get_post_meta( $product_id['post_id'], 'mtrap_bus_coach_type', true );
							$mtrap_boarding_time_meta_value = get_post_meta( $product_id['post_id'], 'mtrap_boarding_time', true );
							$mtrap_boarding_station_id      = get_post_meta( $product_id['post_id'], 'mtrap_bus_stops', true );
							$mtrap_bus_vacant_seats         = get_post_meta( $product_id['post_id'], 'mtrap_seat_stock', true );
							$mtrap_bus_off_days             = get_post_meta( $product_id['post_id'], 'mtrap_bus_off_day', true );
							$mtrap_bus_off_dates_range_from = get_post_meta( $product_id['post_id'], 'mtrap_bus_off_dates_range_from_date', true );
							$mtrap_bus_off_dates_range_to   = get_post_meta( $product_id['post_id'], 'mtrap_bus_off_dates_range_to_date', true );
							$mtrap_bus_off_weekdays         = get_post_meta( $product_id['post_id'], 'mtrap_bus_off_day', true );
							$mtrap_bus_advance_booking      = get_post_meta( $product_id['post_id'], 'mtrap_bus_advance_booking', true );
							$mtrap_bus_tax                  = get_post_meta( $product_id['post_id'], 'mtrap_bus_tax', true );
							$mtrap_seat_class               = get_post_meta( $product_id['post_id'], 'mtrap_seat_class', true );
							$get_mtrap_seat_class		    = get_post_meta( $product_id['post_id'], 'mtrap_default_seat_class', true );
							$mtrap_bus_status_meta_value    = get_post_meta( $product_id['post_id'], 'mtrap_bus_status', true );

							// if booking date is beetween bus off range skip current bus.
							if ( ! empty( $mtrap_bus_off_dates_range_from ) && ! empty( $mtrap_bus_off_dates_range_to ) && ! empty( $mtrap_bus_search_booking_date ) ) {
								if ( strtotime( $mtrap_bus_search_booking_date ) >= strtotime( $mtrap_bus_off_dates_range_from ) && strtotime( $mtrap_bus_search_booking_date ) <= strtotime( $mtrap_bus_off_dates_range_to ) ) {
									continue;
								}
							}

							// if booking date is on weekoff day skip current bus.
							if ( ! empty( $mtrap_bus_off_weekdays ) ) {
								foreach ( $mtrap_bus_off_weekdays as $off_weekday ) {
									// for from date.
									if ( ! empty( $mtrap_bus_search_booking_date ) ) {
										$partial_from_date   = explode( '-', $mtrap_bus_search_booking_date );
										$from_date_gregorian = gregoriantojd( $partial_from_date[1], $partial_from_date[0], $partial_from_date[2] );
										if ( ( strcasecmp( jddayofweek( $from_date_gregorian, 1 ), $off_weekday ) == 0 ) ) {
											continue 2;
										}
									}
									// for to date.
									if ( ! empty( $mtrap_bus_search_return_date ) ) {
										$partial_to_date   = explode( '-', $mtrap_bus_search_return_date );
										$to_date_gregorian = gregoriantojd( $partial_to_date[1], $partial_to_date[0], $partial_to_date[2] );
										if ( ( strcasecmp( jddayofweek( $to_date_gregorian, 1 ), $off_weekday ) == 0 ) ) {
											continue 2;
										}
									}
								}
							}

							$mtrap_get_bus_stop_details = $wpdb->get_results(
								$wpdb->prepare(
									'SELECT *  FROM `wp_mtrap_custom_bus_stops` WHERE `post_id` = %d ORDER BY id ASC',
									$product_id['post_id']
								),
								ARRAY_A
							);

							$amenities_details = get_the_terms( $product_id['post_id'], 'aminities' );
							?>
							<div class="bus-list-items">
									<div class="list-item" data-post-id="<?php echo esc_html( $product_id['post_id'] ); ?>">
										<div class="route-details">
											<div class="title">
												<?php
												echo esc_html( $mtrap_from_city_id->name );
												esc_html_e( ' To ', 'winger' );
												echo esc_html( $mtrap_to_city_id->name );
												$mtrap_boarding_station = get_term_by( 'id', $mtrap_boarding_station_id, 'bus-stops' );
												$route_details          = array();
												$station_details        = array();
												$time_details           = array();
												$departure_time_details = array();
												$station_day_difference = array();
												$station_from           = array();
												$station_to             = array();
												$price                  = array();

												if ( ! empty( $mtrap_get_bus_stop_details ) ) {
													foreach ( array_filter( $mtrap_get_bus_stop_details ) as $stop_details ) {
														$mtrap_stop_details       = get_term_by( 'id', $stop_details['station_stop'], 'bus-stops' );
														$route_details[]          = ! empty( $mtrap_stop_details->name ) ? $mtrap_stop_details->name : '';
														$station_details[]        = ! empty( $stop_details['station_stop'] ) ? $stop_details['station_stop'] : '';
														$price[]                  = ! empty( $stop_details['price'] ) ? $stop_details['price'] : '';
														$time_details[]           = ! empty( $stop_details['station_time'] ) ? $stop_details['station_time'] : '';
														$station_day_difference[] = ! empty( $stop_details['station_day_difference'] ) ? $stop_details['station_day_difference'] : 0;
														$departure_time_details[] = ! empty( $stop_details['station_departure_time'] ) ? $stop_details['station_departure_time'] : '';
													}
													$mtrap_combined_station_time            = array_combine( $station_details, $time_details );
													$mtrap_combined_station_departure_time  = array_combine( $station_details, $departure_time_details );
													$mtrap_combined_station_time_difference = array_combine( $station_details, $station_day_difference );
													$mtrap_combined_station_price           = array_combine( $station_details, $price );
												}
												?>
											</div>
										</div>
										<?php if ( ! empty( $mtrap_combined_station_time ) && ! empty( $mtrap_bus_search_from_city ) ) { ?>
											<div class="Departure">
												<span>
													<?php esc_html_e( 'Departure Time', 'winger' ); ?>
												</span>
												<?php
												if ( array_key_exists( $mtrap_bus_search_from_city, $mtrap_combined_station_departure_time ) ) {
													echo esc_html( gmdate( 'h:i A', strtotime( $mtrap_combined_station_departure_time[ $mtrap_bus_search_from_city ] ) ) );
												} else {
													echo esc_html( gmdate( 'h:i A', strtotime( $mtrap_boarding_time_meta_value ) ) );
												}
												?>
											</div>
										<?php } ?>
										<?php if ( ! empty( $mtrap_boarding_time_meta_value ) && ! empty( $mtrap_bus_search_from_city ) ) { ?>
											<div class="Duration">
												<span>
													<?php esc_html_e( 'Duration', 'winger' ); ?>
												</span>
												<?php
												if ( ( $mtrap_bus_search_from_city != $mtrap_boarding_station_id ) ) {
													$start_time = $mtrap_combined_station_departure_time[ $mtrap_bus_search_from_city ];
												} else {
													$start_time = $mtrap_boarding_time_meta_value;
												}
												$end_time = $mtrap_combined_station_time[ $mtrap_bus_search_to_city ];

												// Convert departure and arrival times to Unix timestamps.
												$departure_timestamp = strtotime( $start_time );
												$arrival_timestamp   = strtotime( $end_time );

												// Calculate the time difference in seconds
												$time_difference = $arrival_timestamp - $departure_timestamp;

												// Calculate days, hours, and minutes
												$mtrap_remaining_days = $mtrap_combined_station_time_difference[ $mtrap_bus_search_to_city ];
												$days_count           = ! empty( $mtrap_remaining_days ) ? $mtrap_remaining_days . 'D ' : '';
												$hours_count          = floor( ( $time_difference % ( 60 * 60 * 24 ) ) / ( 60 * 60 ) ) . 'H ';
												$minutes_count        = floor( ( $time_difference % ( 60 * 60 ) ) / 60 ) . 'M';

												// Output the result
												echo esc_html( $days_count . $hours_count . $minutes_count );
												?>
											</div>
										<?php } ?>
										<?php if ( ! empty( $mtrap_combined_station_time ) && ! empty( $mtrap_bus_search_to_city ) ) { ?>
											<div class="Arrival">
												<span>
													<?php esc_html_e( 'Arrival Time', 'winger' ); ?>
												</span>
												<?php
												if ( array_key_exists( $mtrap_bus_search_to_city, $mtrap_combined_station_time ) ) {
													echo esc_html( gmdate( 'h:i A', strtotime( $mtrap_combined_station_time[ $mtrap_bus_search_to_city ] ) ) );
												}
												?>
											</div>
										<?php } ?>
										<?php if ( ! empty( $mtrap_get_bus_stop_details ) ) { ?>
											<div class="Fare">
												<span>
													<?php esc_html_e( 'Fare', 'winger' ); ?>
												</span>
												<?php
												echo get_woocommerce_currency_symbol(); 
                                                ?>&nbsp;<?php
												$mtrap_array_key_bus_from = array_search( $mtrap_bus_search_from_city, array_column( $mtrap_get_bus_stop_details, 'station_stop' ) );

												$mtrap_array_key_bus_from_added = in_array( $mtrap_bus_search_from_city, array_column( $mtrap_get_bus_stop_details, 'station_stop' ) ) ? ( $mtrap_array_key_bus_from + 1 ) : 0;

												if ( $mtrap_boarding_station_id === $mtrap_bus_search_from_city ) {
													$mtrap_array_key_bus_from_added = 0;
												}

												$mtrap_array_key_bus_to = array_search( $mtrap_bus_search_to_city, array_column( $mtrap_get_bus_stop_details, 'station_stop' ) ) + 1;

												if ( $mtrap_array_key_bus_from_added == 1 ) {
													$mtrap_array_key_bus_to = $mtrap_array_key_bus_to - 1;
												}

												if ( $mtrap_array_key_bus_from_added === $mtrap_array_key_bus_to ) {
													$mtrap_get_all_stations = $mtrap_get_bus_stop_details[ $mtrap_array_key_bus_from_added ];
												} else {
													$mtrap_get_all_stations = array_slice( $mtrap_get_bus_stop_details, $mtrap_array_key_bus_from_added, $mtrap_array_key_bus_to );
												}

												// Initialize variables for prices.
												$initial_adult_price          = array();
												$mtrap_get_all_station_prices = array();
												if ( array_key_exists( 0, $mtrap_get_all_stations ) ) {
													foreach ( $mtrap_get_all_stations as $station ) {
														$unserialized_price             = maybe_unserialize( $station['price'] );
														$mtrap_get_all_station_prices[] = $unserialized_price;
														$initial_adult_price[]          = $unserialized_price['adult'][0];
													}
													$mtrap_adult_price = array_sum( $initial_adult_price );
												} else {
													$unserialized_price             = maybe_unserialize( $mtrap_get_all_stations['price'] );
													$mtrap_get_all_station_prices[] = $unserialized_price;
													$initial_adult_price[]          = $unserialized_price['adult'][0];
													$mtrap_adult_price              = $initial_adult_price[0];
												}
												echo esc_html( $mtrap_adult_price );
												?>
											</div>
										<?php } ?>
										<?php if ( ! empty( $mtrap_bus_search_listing_query ) ) { ?>
											<div class="Vacant">
												<span>
													<?php esc_html_e( 'Vacant', 'winger' ); ?>
												</span>
												<?php
												if ( ! empty( $mtrap_bus_vacant_seats ) ) {
													?>
													<div class = "vacant-seats-count">
														<?php
														$final_stock_count = mtrap_stock_calculations( $mtrap_bus_search_booking_date, $mtrap_bus_search_from_city, $mtrap_bus_search_to_city, $product_id['post_id'] );
														if ( is_array( $final_stock_count ) ) {
															if ( array_sum( $final_stock_count ) >= 0 ) {
																echo array_sum( $final_stock_count );
															} else {
																echo 0;
															}
														} elseif ( $final_stock_count >= 0 ) {
																echo $final_stock_count;
														} else {
															echo 0;
														}
														?>
													</div>
													<?php
												}
												?>
											</div>
										<?php } ?>
										<?php
										// dont display bus booking button if booking date is greater than advance_booking days.
										$mtrap_get_today_date           = date_create( gmdate( 'Y-m-d' ) );
										$mtrap_get_added_days_date      = date_add( $mtrap_get_today_date, date_interval_create_from_date_string( "$mtrap_bus_advance_booking days" ) );
										$mtrap_formated_added_days_date = date_format( $mtrap_get_added_days_date, 'Y-m-d' );
										$mtrap_user_booking_date        = gmdate( 'Y-m-d', strtotime( $mtrap_bus_search_booking_date ) );

										if ( $mtrap_user_booking_date <= $mtrap_formated_added_days_date ) {
											?>
											<div class="select_btn select_bus_btn">
												<a class="trx_popup_button sc_button"><?php esc_html_e( 'Select', 'winger' ); ?></a>
											</div>
											<?php
										} else {
											esc_html_e( 'Booking is not open yet!', 'winger' );
										}
										?>
										<div class="mtrap-current-bus-status">
									        <?php $bus_status_dynamic_class = $mtrap_bus_status_meta_value == 'Cancelled' ? 'today-status-cancelled' : ($mtrap_bus_status_meta_value == 'Delayed' ? 'today-status-delayed' : 'today-status-on-time'); ?>										    
										    <?php if ( gmdate( 'Y-m-d' ) == $mtrap_user_booking_date ) { 
										        ?>
										        <div class="<?php echo $bus_status_dynamic_class; ?>">
										            <?php esc_html_e( $mtrap_bus_status_meta_value , 'winger' ); ?>
										        </div>
										    <?php } else { ?>
										        <div class="today-status-on-time">
										             <?php esc_html_e( 'On Time', 'winger' ); ?>
										        </div>
										    <?php
										    }
										    ?>
										</div>
										<div class="mtrap-additional-travel-details">
										<?php
										if ( ! empty( $amenities_details ) ) {
											?>
												<div class="amenities-details">
													<span><?php esc_html_e( 'Amenities Details : &nbsp;', 'winger' ); ?></span>
												<?php echo join( ', ', wp_list_pluck( $amenities_details, 'name' ) ); ?>
												</div>
												<?php
										}
										if ( ! empty( $mtrap_coach_type_meta_value ) ) {
											$mtrap_coach_name = get_term_by( 'id', $mtrap_coach_type_meta_value, 'bus-types' );
											if ( ! empty( $mtrap_coach_name->name ) ) {
												?>
												<div class="coach-type">
													<span><?php esc_html_e( 'Coach Type : &nbsp;', 'winger' ); ?></span>
													<?php echo esc_html( $mtrap_coach_name->name ); ?>
												</div>
												<?php } ?>
											<?php } ?>
											<?php
											if ( ! empty( $mtrap_boarding_station->name ) ) {
												?>
												<div class="route-list-outer">
												<span><?php esc_html_e( 'Route : &nbsp;', 'winger' ); ?></span>
												<?php
												if ( ! empty( array_filter( $route_details ) ) ) {
													?>
														<div class="route-list"><?php echo esc_html( $mtrap_boarding_station->name ) . ', ' . implode( ', ', array_filter( $route_details ) ); ?></div>
													<?php } else { ?>
														<div class="route-list"><?php echo esc_html( $mtrap_boarding_station->name ); ?></div>
														<?php
													}
													?>
												</div>
												<?php
											}
											?>
										</div>
									</div>
								<?php
								$mtrap_sc_boarding_pickup_point = get_term_meta( $mtrap_bus_search_from_city, 'mtrap_bus_stops_pickuppoint_meta', true );
								$mtrap_sc_drop_point            = get_term_meta( $mtrap_bus_search_to_city, 'mtrap_bus_stops_pickuppoint_meta', true );
								$mtrap_maximum_passengers       = 20;
								$mtrap_initial_sum_passengers   = 0;
								?>
								<div class="book_now_fx" style="display: none;">
									<div class="book_tckt_form">
										<div class="book_form_fx select_container">
										<?php if ( ! empty( $mtrap_sc_boarding_pickup_point ) ) { ?>
												<td style="border-right-style:hidden; background-color: #FFF; border-bottom: 1px solid #ccc;">
												<select name="mtrap-bus-pickup-point" class="mtrap-bus-pickup-point orderby filled fill_inited">
													<option value="<?php echo esc_html( $mtrap_sc_boarding_pickup_point ); ?>" selected disabled><?php echo esc_html( $mtrap_sc_boarding_pickup_point ); ?></option>
												</select>
											<?php } ?>
										</div>
										<div class="book_form_fx select_container">
										<?php if ( ! empty( $mtrap_sc_drop_point ) ) { ?>
												<td style="border-right-style:hidden; background-color: #FFF; border-bottom: 1px solid #ccc;">
												<select name="mtrap-bus-drop-point" class="	 orderby filled fill_inited">
													<option value="<?php echo esc_html( $mtrap_sc_drop_point ); ?>" selected disabled><?php echo esc_html( $mtrap_sc_drop_point ); ?></option>
												</select>
											<?php } ?>
										</div>
										<div class="book_form_fx">
										<?php
										$mtrap_get_seat_class = wp_get_post_terms( $product_id['post_id'], 'seat-class', array( 'fields' => 'all' ) );
										?>
											<label for="mtrap_sc_seat_class_selection"><?php esc_html_e( 'Seat Class', 'winger' ); ?></label>
											<div class="mtrap_sc_passenger_seat_selection">
												<select name="mtrap_sc_seat_class_selection" class="mtrap_sc_seat_class_selection">
													<option value=""><?php esc_html_e( 'Please select seat class', 'winger' ); ?></option>
													<?php
													if( ! empty( $mtrap_get_seat_class ) ){
														foreach ( $mtrap_get_seat_class as $seat_class ) {
															if ( ! empty( $seat_class->name ) && $mtrap_bus_vacant_seats[ $seat_class->slug ] !== 0 && ! empty( $mtrap_bus_vacant_seats[ $seat_class->slug ] ) ) {
																$seat_class_selected = ( $seat_class->term_id == $get_mtrap_seat_class ) ? 'selected' : '';
																?>
																<option <?php echo $seat_class_selected ?> value="<?php echo esc_html( $seat_class->slug ); ?>"><?php echo esc_html( $seat_class->name ); ?></option>
																<?php
															}
														}
													}
													?>
												</select>
												<div class="mtrap-seat-class-vacant" style="display:none;">
													<?php
													if ( is_array( $final_stock_count ) && ! empty( $final_stock_count ) ) {
														foreach ( $final_stock_count as $key => $vacant_seats ) {
															?>
															<input type="hidden" class="<?php echo $key; ?>_seats seat-vacancy" name="<?php echo $key; ?>" value="<?php echo esc_html( $vacant_seats ); ?>">
														<?php } ?>
														<?php
													} elseif ( ! empty( $mtrap_bus_vacant_seats ) ) {
														foreach ( $mtrap_bus_vacant_seats as $key => $vacant_seats ) {
															?>
															<input type="hidden" class="<?php echo $key; ?>_seats seat-vacancy" name="<?php echo $key; ?>" value="<?php echo esc_html( $vacant_seats ); ?>">
														<?php } ?>
													<?php } ?>
												</div>
											</div>
										</div>
										<?php
										$cart_item_key = '';
										if ( WC()->session && ! empty( WC()->cart->cart_contents ) ) {
											foreach ( WC()->cart->cart_contents as $key => $cart_item ) {
												$cart_item_key = $key;
											}
											if ( ! empty( $cart_item_key ) ) {
												$mtrap_bus_details_session_set = WC()->session->get( 'bus_details_' . $cart_item_key );
												if ( ! empty( $mtrap_bus_details_session_set ) ) {
													$passenger_details         = ! empty( $mtrap_bus_details_session_set['_passenger_data_ticket'] ) ? maybe_unserialize( $mtrap_bus_details_session_set['_passenger_data_ticket'] ) : '';
													$selected_passenger_number = ! empty( $mtrap_bus_details_session_set['passenger'] ) ? $mtrap_bus_details_session_set['passenger'] : '';

													?>
													<div class="book_form_fx">
														<label for="mtrap_sc_passenger_selection"><?php esc_html_e( 'Passenger', 'winger' ); ?></label>
														<div class="mtrap_sc_passenger_selection">
															<select name="mtrap_sc_passenger_selection" class="mtrap_sc_passenger">
																<?php
																for ( $passenger_number = 1; $passenger_number <= $mtrap_maximum_passengers; $passenger_number++ ) {
																	$selected = ( $selected_passenger_number == $passenger_number ) ? 'selected' : '';
																	?>
																	<option value="<?php echo esc_html( $passenger_number ); ?>" <?php echo $selected; ?>><?php echo esc_html( $passenger_number ); ?></option>
																	<?php
																}
																?>
															</select>
														</div>
													</div>
													<?php
													if ( $passenger_details ) {
														$passenger_count = 1;
														?>
														<div class="passenger_outer_div">
															<?php
															foreach ( $passenger_details as $passenger ) {
																$passenger_name   = ! empty( $passenger->passenger_name ) ? $passenger->passenger_name : '';
																$passenger_email  = ! empty( $passenger->passenger_email ) ? $passenger->passenger_email : '';
																$passenger_phone  = ! empty( $passenger->passenger_phone ) ? $passenger->passenger_phone : '';
																$passenger_gender = ! empty( $passenger->passenger_gender ) ? $passenger->passenger_gender : '';
																$passenger_type   = ! empty( $passenger->passenger_type ) ? $passenger->passenger_type : '';

																?>
																<div class="passenger_details_outer">
																	<label><?php echo __( 'Passenger', 'winger' ) . ' - ' . $passenger_count; ?></label>
																	<div class="passenger_details">
																		<div class="book_form_fx mtrap_fullname">
																			<input type="text" name="Fullname[]" class="mtrap_passenger_fullname" placeholder="<?php esc_html_e( 'Full Name', 'winger' ); ?>" value="<?php echo $passenger_name; ?>">
																		</div>
																		<div class="book_form_fx mtrap_email">
																			<input type="email" name="Email[]" class="mtrap_passenger_email" placeholder="<?php esc_html_e( 'Email', 'winger' ); ?>" value="<?php echo $passenger_email; ?>">
																		</div>
																		<div class="book_form_fx mtrap_phone">
																			<input type="text" name="Phone[]" class="mtrap_passenger_phone" placeholder="<?php esc_html_e( 'Phone ', 'winger' ); ?>" value="<?php echo $passenger_phone; ?>">
																		</div>
																		<div class="book_form_fx gender-dropdown">
																			<div class="select_container mtrap_gender">
																			<select name="gender[]" class="mtrap_passenger_gender">
																				<option value="" <?php selected( $passenger_gender, '' ); ?> disabled hidden><?php esc_html_e( 'Gender', 'winger' ); ?></option>
																				<option value="Male" <?php selected( $passenger_gender, 'Male' ); ?>><?php esc_html_e( 'Male', 'winger' ); ?></option>
																				<option value="Female" <?php selected( $passenger_gender, 'Female' ); ?>><?php esc_html_e( 'Female', 'winger' ); ?></option>
																				<option value="Other" <?php selected( $passenger_gender, 'Other' ); ?>><?php esc_html_e( 'Other', 'winger' ); ?></option>
																			</select>
																			</div>
																		</div>
																		<div class="book_form_fx adult-child">
																			<div class="select_container mtrap_pessagnertype">
																				<select name="passenger[]" class="mtrap_passenger_type">
																					<?php
																					if ( ! empty( $mtrap_get_passenger_type_taxonomy_terms ) && ! empty( $mtrap_get_all_station_prices ) ) {
																						foreach ( $mtrap_get_passenger_type_taxonomy_terms as $passenger_type ) {
																							foreach ( $mtrap_get_all_station_prices as $price ) {
																								if ( empty( $price[ esc_html( $passenger_type->slug ) ][0] ) || $price[ esc_html( $passenger_type->slug ) ][0] == 0 ) {
																									continue 2;
																								}
																							}
																							$selected = ( $passenger->passenger_type == $passenger_type->slug ) ? 'selected' : '';
																							?>
																							<option value="<?php echo esc_html( $passenger_type->slug ); ?>" <?php echo $selected; ?>><?php echo esc_html( $passenger_type->name ); ?></option>
																							<?php
																						}
																					}
																					?>
																				</select>
																			</div>
																		</div>
																	</div>
																</div>
																<?php
																++$passenger_count;
															}
															?>
														</div>
														<?php
													} 
												} else { 
													?>
													<div class="passenger_outer_div">
														<div class="passenger_details_outer">
															<label><?php echo __( 'Passenger', 'winger' ) . ' - 1'; ?></label>
															<div class="passenger_details">
																<div class="book_form_fx mtrap_fullname">
																	<input type="text" name="Fullname[]" class="mtrap_passenger_fullname" placeholder="<?php esc_html_e( 'Full Name', 'winger' ); ?>">
																</div>
																<div class="book_form_fx mtrap_email">
																	<input type="email" name="Email[]" class="mtrap_passenger_email" placeholder="<?php esc_html_e( 'Email', 'winger' ); ?>">
																</div>
																<div class="book_form_fx mtrap_phone">
																	<input type="text" name="Phone[]" class="mtrap_passenger_phone" placeholder="<?php esc_html_e( 'Phone ', 'winger' ); ?>">
																</div>
																<div class="book_form_fx gender-dropdown">
																	<div class="select_container mtrap_gender">
																		<select name="gender[]" class="mtrap_passenger_gender">
																			<option value="" selected disabled hidden><?php esc_html_e( 'Gender', 'winger' ); ?></option>
																			<option value="Male"><?php esc_html_e( 'Male', 'winger' ); ?></option>
																			<option value="Female"><?php esc_html_e( 'Female', 'winger' ); ?></option>
																			<option value="Other"><?php esc_html_e( 'Other', 'winger' ); ?></option>
																		</select>
																	</div>
																</div>
																<div class="book_form_fx adult-child">
																	<div class="select_container mtrap_pessagnertype">
																		<select name="passenger[]" class="mtrap_passenger_type">
																			<?php
																			if ( ! empty( $mtrap_get_passenger_type_taxonomy_terms ) && ! empty( $mtrap_get_all_station_prices ) ) {
																				foreach ( $mtrap_get_passenger_type_taxonomy_terms as $passenger_type ) {
																					$valid_option = true;
																					foreach ( $mtrap_get_all_station_prices as $price ) {
																						if ( empty( $price[ esc_html( $passenger_type->slug ) ][0] ) || $price[ esc_html( $passenger_type->slug ) ][0] == 0 ) {
																							$valid_option = false;
																							break;
																						}
																					}
																					if ( $valid_option ) {
																						?>
																						<option value="<?php echo esc_html( $passenger_type->slug ); ?>"><?php echo esc_html( $passenger_type->name ); ?></option>
																						<?php
																					}
																				}
																			}
																			?>
																		</select>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<?php
												}
											}
										} else {
											?>
											<div class="book_form_fx">
												<label for="mtrap_sc_passenger_selection"><?php esc_html_e( 'Passenger', 'winger' ); ?></label>
												<div class="mtrap_sc_passenger_selection">
													<select name="mtrap_sc_passenger_selection" class="mtrap_sc_passenger">
														<?php
														for ( $passenger_number = 1; $passenger_number <= $mtrap_maximum_passengers; $passenger_number++ ) {
															$mtrap_initial_sum_passengers = $mtrap_initial_sum_passengers + $passenger_number;
															?>
															<option value="<?php echo esc_html( $passenger_number ); ?>"><?php echo esc_html( $passenger_number ); ?></option>
															<?php
														}
														?>
													</select>
												</div>
											</div>
											<?php
											if ( is_user_logged_in() ) {
												$mtrap_user_id    = get_current_user_id();
												$mtrap_user_data  = get_userdata( $mtrap_user_id );
												$mtrap_user_email = $mtrap_user_data->user_email;

												$first_name              = ! empty( get_user_meta( $mtrap_user_id, 'first_name', true ) ) ? get_user_meta( $mtrap_user_id, 'first_name', true ) : '';
												$last_name               = ! empty( get_user_meta( $mtrap_user_id, 'last_name', true ) ) ? get_user_meta( $mtrap_user_id, 'last_name', true ) : '';
												$mtrap_user_phone_number = ! empty( get_user_meta( $mtrap_user_id, 'mtrap_user_phone_number', true ) ) ? get_user_meta( $mtrap_user_id, 'mtrap_user_phone_number', true ) : '';
												$mtrap_user_gender       = ! empty( get_user_meta( $mtrap_user_id, 'mtrap_user_gender', true ) ) ? get_user_meta( $mtrap_user_id, 'mtrap_user_gender', true ) : '';
												$mrtap_passenger_type    = ! empty( get_user_meta( $mtrap_user_id, 'mrtap_user_type', true ) ) ? get_user_meta( $mtrap_user_id, 'mrtap_passenger_type', true ) : '';

												$user_full_name = $first_name . '&nbsp;' . $last_name;
												?>
												<div class="passenger_outer_div">
													<div class="passenger_details_outer">
														<label><?php echo __( 'Passenger', 'winger' ) . ' - 1'; ?></label>
														<div class="passenger_details">
															<div class="book_form_fx mtrap_fullname">
																<input type="text" name="Fullname[]" class="mtrap_passenger_fullname" placeholder="<?php esc_html_e( 'Full Name', 'winger' ); ?>" value=<?php echo $user_full_name; ?>>
															</div>
															<div class="book_form_fx mtrap_email">
																<input type="email" name="Email[]" class="mtrap_passenger_email" placeholder="<?php esc_html_e( 'Email', 'winger' ); ?>" value=<?php echo $mtrap_user_email; ?>>
															</div>
															<div class="book_form_fx mtrap_phone">
																<input type="text" name="Phone[]" class="mtrap_passenger_phone" placeholder="<?php esc_html_e( 'Phone ', 'winger' ); ?>" value=<?php echo $mtrap_user_phone_number; ?>>
															</div>
															<div class="book_form_fx gender-dropdown">
																<div class="select_container mtrap_gender">
																	<select name="gender[]" class="mtrap_passenger_gender">
																		<option value="" selected disabled hidden><?php esc_html_e( 'Gender', 'winger' ); ?></option>
																		<option <?php selected( $mtrap_user_gender, 'Male' ); ?> value="Male"><?php esc_html_e( 'Male', 'winger' ); ?></option>
																		<option <?php selected( $mtrap_user_gender, 'Female' ); ?> value="Female"><?php esc_html_e( 'Female', 'winger' ); ?></option>
																		<option <?php selected( $mtrap_user_gender, 'Other' ); ?> value="Other"><?php esc_html_e( 'Other', 'winger' ); ?></option>
																	</select>
																</div>
															</div>
															<div class="book_form_fx adult-child">
																<div class="select_container mtrap_pessagnertype">
																	<select name="passenger[]" class="mtrap_passenger_type">
																		<?php
																		if ( ! empty( $mtrap_get_passenger_type_taxonomy_terms ) && ! empty( $mtrap_get_all_station_prices ) ) {
																			foreach ( $mtrap_get_passenger_type_taxonomy_terms as $passenger_type ) {
																				$valid_option = true;
																				foreach ( $mtrap_get_all_station_prices as $price ) {
																					if ( empty( $price[ esc_html( $passenger_type->slug ) ][0] ) || $price[ esc_html( $passenger_type->slug ) ][0] == 0 ) {
																						$valid_option = false;
																						break;
																					}
																				}
																				if ( $valid_option ) {
																					?>
																					<option <?php selected( $mrtap_passenger_type, $passenger_type->slug ); ?> value="<?php echo esc_html( $passenger_type->slug ); ?>"><?php echo esc_html( $passenger_type->name ); ?></option>
																					<?php
																				}
																			}
																		}
																		?>
																	</select>
																</div>
															</div>
														</div>
													</div>
												</div>
												<?php
											} else {
												?>
												<div class="passenger_outer_div">
													<div class="passenger_details_outer">
														<label><?php echo __( 'Passenger', 'winger' ) . ' - 1'; ?></label>
														<div class="passenger_details">
															<div class="book_form_fx mtrap_fullname">
																<input type="text" name="Fullname[]" class="mtrap_passenger_fullname" placeholder="<?php esc_html_e( 'Full Name', 'winger' ); ?>">
															</div>
															<div class="book_form_fx mtrap_email">
																<input type="email" name="Email[]" class="mtrap_passenger_email" placeholder="<?php esc_html_e( 'Email', 'winger' ); ?>">
															</div>
															<div class="book_form_fx mtrap_phone">
																<input type="text" name="Phone[]" class="mtrap_passenger_phone" placeholder="<?php esc_html_e( 'Phone ', 'winger' ); ?>">
															</div>
															<div class="book_form_fx gender-dropdown">
																<div class="select_container mtrap_gender">
																	<select name="gender[]" class="mtrap_passenger_gender">
																		<option value="" selected disabled hidden><?php esc_html_e( 'Gender', 'winger' ); ?></option>
																		<option value="Male"><?php esc_html_e( 'Male', 'winger' ); ?></option>
																		<option value="Female"><?php esc_html_e( 'Female', 'winger' ); ?></option>
																		<option value="Other"><?php esc_html_e( 'Other', 'winger' ); ?></option>
																	</select>
																</div>
															</div>
															<div class="book_form_fx adult-child">
																<div class="select_container mtrap_pessagnertype">
																	<select name="passenger[]" class="mtrap_passenger_type">
																		<?php
																		if ( ! empty( $mtrap_get_passenger_type_taxonomy_terms ) && ! empty( $mtrap_get_all_station_prices ) ) {
																			foreach ( $mtrap_get_passenger_type_taxonomy_terms as $passenger_type ) {
																				$valid_option = true;
																				foreach ( $mtrap_get_all_station_prices as $price ) {
																					if ( empty( $price[ esc_html( $passenger_type->slug ) ][0] ) || $price[ esc_html( $passenger_type->slug ) ][0] == 0 ) {
																						$valid_option = false;
																						break;
																					}
																				}
																				if ( $valid_option ) {
																					?>
																					<option value="<?php echo esc_html( $passenger_type->slug ); ?>"><?php echo esc_html( $passenger_type->name ); ?></option>
																					<?php
																				}
																			}
																		}
																		?>
																	</select>
																</div>
															</div>
														</div>
													</div>
												</div>
												<?php
											}
										}
										?>
									</div>
									<div class="info_table">
										<table>
											<thead>
												<th style="border-right-style: hidden;" colspan="2"><?php esc_html_e( 'Journey Details', 'winger' ); ?></th>
											</thead>
											<tbody>
												<tr>
													<?php if ( $mtrap_from_city_id && $mtrap_to_city_id ) { ?>
														<td class="mtrap_journey_from_to" style="border-right-style: hidden;" colspan="2">
															<?php echo esc_html( $mtrap_from_city_id->name ); ?>
															<?php esc_html_e( ' To ', 'winger' ); ?>
															<?php echo esc_html( $mtrap_to_city_id->name ); ?></td>
													<?php } ?>
												</tr>
												<tr>
													<td><?php esc_html_e( 'Journey Date', 'winger' ); ?></td>
													<td class="mtrap_journey_date">
													<?php
													if ( ! empty( $mtrap_bus_search_booking_date ) ) {
														echo esc_html( $mtrap_bus_search_booking_date );
													}
													?>
													</td>
												</tr>
												<tr>
													<td><?php esc_html_e( 'Journey Day', 'winger' ); ?></td>
													<td class="mtrap_journey_day"> 
														<?php
														if ( ! empty( $mtrap_bus_search_booking_date ) ) {
															$partial_from_date   = explode( '-', $mtrap_bus_search_booking_date );
															$from_date_gregorian = gregoriantojd( $partial_from_date[1], $partial_from_date[0], $partial_from_date[2] );
															echo jddayofweek( $from_date_gregorian, 1 );
														}
														?>
													</td>
												</tr>
												<tr>
													<td><?php esc_html_e( 'Departure Time', 'winger' ); ?></td>
													<td class="mtrap_journey_departure_time"> 
														<?php
														if ( ! empty( $mtrap_combined_station_departure_time ) && ! empty( $mtrap_bus_search_from_city ) ) {
															if ( array_key_exists( $mtrap_bus_search_from_city, $mtrap_combined_station_departure_time ) ) {
																echo esc_html( gmdate( 'h:i A', strtotime( $mtrap_combined_station_departure_time[ $mtrap_bus_search_from_city ] ) ) );
															} else {
																echo esc_html( gmdate( 'h:i A', strtotime( $mtrap_boarding_time_meta_value ) ) );
															}
														}
														?>
													</td>
												</tr>
												<tr>
													<td><?php esc_html_e( 'Arrival Time', 'winger' ); ?></td>
													<td class="mtrap_journey_arrival_time">
														<?php
														if ( ! empty( $mtrap_combined_station_time ) && ! empty( $mtrap_bus_search_to_city ) ) {
															if ( array_key_exists( $mtrap_bus_search_to_city, $mtrap_combined_station_time ) ) {
																echo esc_html( gmdate( 'h:i A', strtotime( $mtrap_combined_station_time[ $mtrap_bus_search_to_city ] ) ) );
															}
														}
														?>
													</td>
												</tr>
												<tr style="border-right-style: hidden; border-left-style: hidden;">
													<td style="border-right-style:hidden; background-color: #FFF; border-bottom: 1px solid #ccc;">
														<span style="margin-bottom: 0;">
														<?php esc_html_e( 'Base Fare:', 'winger' ); ?>
														</span>
													</td>
													<td style=" background-color: #FFF; border-bottom: 1px solid #ccc;">
														<div class="base-ptrice-outer" style="padding-top: 40px; margin-bottom: 0; display: inline;">
														<?php
														if ( ! empty( $mtrap_get_bus_stop_details ) ) {
															echo get_woocommerce_currency_symbol();
															?>
																<div class="base-price" style="display: inline;">
																<?php
																	echo esc_html( $mtrap_adult_price );
																?>
																</div>
															<?php } ?>
															<div class="mtrap-all-prices" style="display:none;">
																<?php

																if ( ! empty( $mtrap_get_all_station_prices ) ) {
																	$passengers_types = array();
																	foreach ( $mtrap_get_all_station_prices as $prices_array ) {
																		foreach ( $prices_array as $key => $price ) {
																			if ( $key == $key ) {
																				$passengers_types[ $key ][] = $price;
																			} else {
																				$passengers_types[ $key ][] = $price;
																			}
																		}
																	}
																	foreach ( $passengers_types as $key => $passenger_type ) {
																		$passenger_total_price = array();
																		foreach ( $passenger_type as $passenger ) {
																			$passenger_total_price[] = $passenger[0];
																		}
																		?>
																		<input type="hidden" class="<?php echo $key; ?>_price price-settings" name="<?php echo $key; ?>" value="<?php echo esc_html( array_sum( $passenger_total_price ) ); ?>">
																		<?php
																	}
																}
																?>
																<?php if ( ! empty( $mtrap_bus_tax ) ) { ?>
																	<input type="hidden" class	="price_tax" name="price_tax" value="<?php echo esc_html( $mtrap_bus_tax ); ?>">
																<?php } ?>
															</div>
														</div>
													</td>
												</tr>
												<?php
												if ( ! empty( $mtrap_seat_class ) ) {
													?>
													<tr style="border-right-style: hidden; border-left-style: hidden; border-top-style: hidden;">
														<td style="border-right-style:hidden; background-color: #FFF; border-bottom: 1px solid #ccc;">
															<span><?php esc_html_e( 'Seat Class', 'winger' ); ?> : </span>
														</td>
														<td class="mtrap-seat-class" style="background-color: #FFF; border-bottom: 1px solid #ccc;">
															<span class="seat-title" style="display: inline;">
																<?php esc_html_e( ' - ', 'winger' ); ?>
															</span>
														</td>
													</tr>
													<div class="mtrap-seat-class-price" style="display:none;">
														<?php
														foreach ( $mtrap_seat_class as $seat_class => $value ) {
															if ( ! empty( $seat_class ) ) {
															    if( ! empty( $value ) ){
    																?>
    																<input type="hidden" class="class-price" name="class-price" data-seat-class="<?php echo esc_html( $seat_class ); ?>" value="<?php echo esc_html( $value ); ?>">
    																<?php
															    } else {
															        ?>
    																<input type="hidden" class="class-price" name="class-price" data-seat-class="<?php echo esc_html( $seat_class ); ?>" value="0">
    																<?php
															    }
															}
														}
														?>
													</div>
													<?php
												}
												?>
												<?php if ( ! empty( $mtrap_bus_tax ) ) { ?>
													<tr style="border-right-style: hidden; border-left-style: hidden; border-top-style: hidden;">
														<td style="border-right-style:hidden; background-color: #FFF; border-bottom: 1px solid #ccc;">
															<span><?php esc_html_e( 'Service fees', 'winger' ); ?> : </span>
														</td>
														<td class="mtrap_bus_tax" style=" background-color: #FFF; border-bottom: 1px solid #ccc;">
															<span>
																<?php echo esc_html( $mtrap_bus_tax ) . '%'; ?>
															</span>
														</td>
													</tr>
													<?php
												}
												?>
												<tr style="border-right-style: hidden; border-left-style: hidden; border-top-style: hidden; ">
													<td style="border-right-style:hidden; background-color: #FFF; border-bottom: 1px solid #ccc; ">
												<?php esc_html_e( 'Total', 'winger' ); ?>
													</td>
													<td class="mtrap-final-price-outer" style=" background-color: #FFF; display: flex; border: 0px !important;">
													    <?php echo get_woocommerce_currency_symbol(); ?>&nbsp;
    													<div class="mtrap-final-price">
        													<?php
        													if ( ! empty( $mtrap_bus_tax ) && ! empty( $mtrap_adult_price ) ) {
        														$mtrap_adult_price *= ( 1 + $mtrap_bus_tax / 100 );
        														echo esc_html( $mtrap_adult_price );
        													} else {
        														echo esc_html( $mtrap_adult_price );
        													}
        													?>
        												</div>
													</td>
												</tr>
											</tbody>
										</table>
										<?php if ( gmdate( 'Y-m-d' ) != $mtrap_user_booking_date || $mtrap_bus_status_meta_value != 'Cancelled' ) { ?>
                                            <div class="mtrap_bus_listing_continue_btn buttn_continue">
                                                <input type="submit" value="Continue">
                                            </div>
                                        <?php } else { ?>
                                            <div class="buttn_continue"><?php esc_html_e( 'Sorry, This journey has been cancelled!', 'winger' ); ?></div>
                                        <?php } ?>

									</div>
								</div>
							</div>
							<?php
						}
					} else {
						// Convert date range and booking/return dates to DateTime objects.
						$bookingDate = new DateTime( $mtrap_bus_search_booking_date );
						$returnDate  = new DateTime( $mtrap_bus_search_return_date );
						$startDate   = new DateTime( $mtrap_bus_off_dates_range_from );
						$endDate     = new DateTime( $mtrap_bus_off_dates_range_to );


						// Function to check if a date is within the specified range.
						function isDateInRange( $date, $startDate, $endDate ) {
							return ( $date >= $startDate && $date <= $endDate );
						}

						// Function to check if a date is a weekday to skip.
						function isWeekdayToSkip( $date, $mtrap_bus_off_weekdays ) {
							return in_array( strtolower( $date->format( 'l' ) ), $mtrap_bus_off_weekdays );
						}

						// Function to find the immediate date after the given date range and weekdays.
						function getNextValidDate( $date, $startDate, $endDate, $mtrap_bus_off_weekdays ) {
							$currentDate = clone $date;
							$currentDate->modify( '+1 day' ); // Start checking from the day after the given date.

							while ( true ) {
								if ( ! isWeekdayToSkip( $currentDate, $mtrap_bus_off_weekdays ) && ! isDateInRange( $currentDate, $startDate, $endDate ) ) {
									return $currentDate;
								}
								$currentDate->modify( '+1 day' );
							}
						}

						// Check if the booking date or return date is within the blocked range or on blocked weekdays.
						$bookingDateValid = ! isDateInRange( $bookingDate, $startDate, $endDate ) && ! isWeekdayToSkip( $bookingDate, $mtrap_bus_off_weekdays );
						$returnDateValid  = ! isDateInRange( $returnDate, $startDate, $endDate ) && ! isWeekdayToSkip( $returnDate, $mtrap_bus_off_weekdays );

						if ( ! $bookingDateValid ) {
							// Get the next valid booking date.
							$nextValidBookingDate = getNextValidDate( $bookingDate, $startDate, $endDate, $mtrap_bus_off_weekdays );
						}

						if ( ! $returnDateValid ) {
							// Get the next valid return date.
							$nextValidReturnDate = getNextValidDate( $returnDate, $startDate, $endDate, $mtrap_bus_off_weekdays );
						}

						
						// Compose the message.
						if ( ! $bookingDateValid || ! $returnDateValid ) {
							if ( ! $bookingDateValid && ! $returnDateValid ) {
								$message = sprintf(
									esc_html__( 'Sorry, our agency is not offering a journey on these dates, but we are back in the business on %1$s for the booking date and %2$s for the return date. Would you like to book the tickets on these dates?', 'winger' ),
									$nextValidBookingDate->format( 'Y-m-d (l)' ),
									$nextValidReturnDate->format( 'Y-m-d (l)' )
								);
							} elseif ( ! $bookingDateValid ) {
								$message = sprintf(
									esc_html__( 'Sorry, our agency is not offering a journey on the booking date, but we are back in the business on %s. Would you like to book the ticket on that day?', 'winger' ),
									$nextValidBookingDate->format( 'Y-m-d (l)' )
								);
							} else {
								$message = sprintf(
									esc_html__( 'Sorry, our agency is not offering a journey on the return date, but we are back in the business on %s. Would you like to book the ticket on that day?', 'winger' ),
									$nextValidReturnDate->format( 'Y-m-d (l)' )
								);
							}

						}
						?>
						<div class="elementor-section elementor-top-section elementor-element elementor-section-full_width elementor-section-height-min-height elementor-section-content-middle elementor-section elementor-section-boxed">
							<div class="elementor-container elementor-column-gap-extended">
								<div class="elementor-row elementor-col-100 elementor-top-column elementor-element elementor-widget-wrap">
									<div class="search-list-alert">
									<?php
										// Print the message
										echo esc_html($message);
										?>
											<a id="yesButton" href="javascript:void(0);" style="background-color: white !important;color: #000 !important;display: inline-block;vertical-align: top;font-size: 15px;font-weight: 500;padding: 0 30px;border-radius: 40px;margin-left: 10px;" ><?php esc_html_e( 'Yes!', 'winger' ); ?></a>
											<a id="noButton" href="<?php echo home_url(); ?>" style="background-color: white !important;color: #000 !important;display: inline-block;vertical-align: top;font-size: 15px;font-weight: 500;padding: 0 30px;border-radius: 40px;margin-left: 10px;" ><?php esc_html_e( 'No', 'winger' ); ?></a>
										<?php
									?>
									</div>
								</div>
							</div>
						</div>
						<script>
							jQuery(document).ready(function($) {
								$(document).on('click', '#yesButton', function() { 
									var activeTabContent = $('.one-round-tabs-section .elementor-tabs-wrapper .elementor-active').text().trim();
									var bookingDate = '';
									var returnDate = '';

									// Set dates based on the active tab content
									if (activeTabContent === 'Round trip' || activeTabContent === 'Aller-retour' ) {
										bookingDate = '<?php echo isset($nextValidBookingDate) ? $nextValidBookingDate->format('d-m-Y') : $bookingDate->format('d-m-Y'); ?>';
										returnDate = '<?php echo isset($nextValidReturnDate) ? $nextValidReturnDate->format('d-m-Y') : $returnDate->format('d-m-Y'); ?>';
									} else {
										bookingDate = '<?php echo isset($nextValidBookingDate) ? $nextValidBookingDate->format('d-m-Y') : ''; ?>';
									}

									//Send AJAX request
									$.ajax({
										url: '<?php echo admin_url('admin-ajax.php'); ?>',
										type: 'POST',
										data: {
											action: 'set_next_journey_dates',
											'booking-date': bookingDate,
											'return-date': returnDate
										},
										success: function(response) {
											if (response.success) {
												// Update the input values for booking and return dates
												$('.bookingdate').val(bookingDate);
												$('.returndate').val(returnDate);
												$('.returndate').attr('value', returnDate);
												document.getElementById("booking_form").submit();
											}
										},
										error: function() {
											console.log('Error setting session data.');
										}
									});
								});
							});
						</script>
						<?php
						}
				} else {
					?>
					<div class="elementor-section elementor-top-section elementor-element elementor-section-full_width elementor-section-height-min-height elementor-section-content-middle elementor-section elementor-section-boxed">
						<div class="elementor-container elementor-column-gap-extended">
							<div class="elementor-row elementor-col-100 elementor-top-column elementor-element elementor-widget-wrap">
								<div class="search-list-alert">
								<?php esc_html_e( 'Sorry, no buses found for this route!', 'winger' ); ?>
								</div>
							</div>
						</div>
					</div>
					<?php
				}
			} else {
				?>
				<div class="elementor-section elementor-top-section elementor-element elementor-section-full_width elementor-section-height-min-height elementor-section-content-middle elementor-section elementor-section-boxed">
					<div class="elementor-container elementor-column-gap-extended">
						<div class="elementor-row elementor-col-100 elementor-top-column elementor-element elementor-widget-wrap">
							<div class="search-list-alert">
							<?php esc_html_e( 'Please enter the booking details!', 'winger' ); ?>
							</div>
						</div>
					</div>
				</div>
				<?php
			}
			?>
		</div>
		</div>
	</div>
</div>
<?php
get_footer();
?>
