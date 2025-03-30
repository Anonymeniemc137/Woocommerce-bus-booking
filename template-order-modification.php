<?php
/**
 * Template Name: Order Modification
 */

get_header();

?>
<div class="mtrap-ticket-modification-header">
	<?php echo do_shortcode( '[trx_sc_layouts layout="9179"]' ); ?>
</div>
<?php

$mtrap_om_booking_number = ! empty( $_REQUEST['booking_number'] ) ? sanitize_text_field( $_REQUEST['booking_number'] ) : '';
$mtrap_om_from_city      = ! empty( $_REQUEST['destination_from'] ) ? sanitize_text_field( $_REQUEST['destination_from'] ) : '';
$mtrap_om_to_city        = ! empty( $_REQUEST['destination_to'] ) ? sanitize_text_field( $_REQUEST['destination_to'] ) : '';
$mtrap_om_email          = ! empty( $_REQUEST['journeyemail'] ) ? sanitize_text_field( $_REQUEST['journeyemail'] ) : '';
$mtrap_om_journey_date   = ! empty( $_REQUEST['journeydate'] ) ? sanitize_text_field( $_REQUEST['journeydate'] ) : '';
$today_date_timestamp    = strtotime( gmdate( 'd-m-Y' ) );

// On order modification form submission, check submission data and add product to cart.
if ( isset( $_POST['mtrap_form_submitted'] ) && $_POST['mtrap_form_submitted'] == '1' ) {
	$result         = array();
	$meta_key       = array();
	$meta_value     = array();
	$fullnames      = isset( $_POST['Fullname'] ) ? array_map( 'sanitize_text_field', $_POST['Fullname'] ) : array();
	$emails         = isset( $_POST['Email'] ) ? array_map( 'sanitize_email', $_POST['Email'] ) : array();
	$phones         = isset( $_POST['Phone'] ) ? array_map( 'sanitize_text_field', $_POST['Phone'] ) : array();
	$genders        = isset( $_POST['gender'] ) ? array_map( 'sanitize_text_field', $_POST['gender'] ) : array();
	$passenger_type = isset( $_POST['type'] ) ? array_map( 'sanitize_text_field', $_POST['type'] ) : array();
	$journeydate    = isset( $_POST['changejourneydate'] ) ? sanitize_text_field( $_POST['changejourneydate'] ) : '';

	// Hidden items to verify order.
	$order_id      = isset( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : '';
	$order_item_id = isset( $_POST['number'] ) ? sanitize_text_field( $_POST['number'] ) : '';
	$journey_to    = isset( $_POST['from'] ) ? sanitize_text_field( $_POST['from'] ) : '';
	$journey_from  = isset( $_POST['to'] ) ? sanitize_text_field( $_POST['to'] ) : '';
	// Create passengers array.
	$passengers = array();
	for ( $i = 0; $i < count( $fullnames ); $i++ ) {
		$journey_pnr_number = gmdate( 'Ymd' ) . $order_item_id . wp_rand( 1, 100 );
		$passengers[]       = array(
			'booking_no'       => $order_item_id,
			'ticket_no'        => $journey_pnr_number,
			'passenger_name'   => $fullnames[ $i ],
			'passenger_email'  => $emails[ $i ],
			'passenger_phone'  => $phones[ $i ],
			'passenger_gender' => $genders[ $i ],
			'passenger_type'   => $passenger_type[ $i ],
		);
	}

	// Format passenger details.
	foreach ( $passengers as $passenger ) {
		$fullname = isset( $passenger['passenger_name'] ) ? $passenger['passenger_name'] : '';
		$email    = isset( $passenger['passenger_email'] ) ? $passenger['passenger_email'] : '';
		$phone    = isset( $passenger['passenger_phone'] ) ? $passenger['passenger_phone'] : '';
		$gender   = isset( $passenger['passenger_gender'] ) ? $passenger['passenger_gender'] : '';
		$type     = isset( $passenger['passenger_type'] ) ? $passenger['passenger_type'] : '';

		$formatted_string = $fullname . '( ' . $email . ', ' . $phone . ', ' . $gender . ', ' . $type . ' )';
		$result[]         = $formatted_string;
	}

	// Check if required data is present.
	if ( ! empty( $order_id ) && ! empty( $order_item_id ) && ! empty( $journey_to ) && ! empty( $journey_from ) ) {
		$order = wc_get_order( $order_id );
		if ( wc_get_order_item_meta( $order_item_id, '_trip_order_status', true ) != 'partial-cancel' ) {
			// Ensure the order is valid.
			if ( $order ) {
				$items = $order->get_items();
				$item  = isset( $items[ $order_item_id ] ) ? $items[ $order_item_id ] : false;

				if ( $item ) {
					$previous_order_base_price = wc_get_order_item_meta( $order_item_id, '_base_price', true );
					$modification_charge          = get_modification_charges( $journeydate );
					$meta_data                    = $item->get_meta_data();
					$updated_meta_data            = array();
					foreach ( $meta_data as $meta ) {
						$meta_array = $meta->get_data();
						if ( $meta_array['key'] == 'journey_date' ) {
							$meta_array['value'] = $journeydate;
						}
						if ( $meta_array['key'] == '_passenger_data_ticket' ) {
							$meta_array['value'] = maybe_serialize( $passengers );
						}
						if ( $meta_array['key'] == 'passenger_details' ) {
							$meta_array['value'] = implode( ', ', $result );
						}
						if ( $meta_array['key'] == '_journy_day' ) {
							$new_date            = DateTime::createFromFormat( 'd-m-Y', $journeydate );
							$day_of_week         = $new_date->format( 'l' );
							$meta_array['value'] = maybe_serialize( $day_of_week );
						}
						$updated_meta_data[] = $meta_array;
					}

					foreach ( $updated_meta_data as $meta ) {
						$meta_key[]   = $meta ['key'];
						$meta_value[] = $meta['value'];
					}
					$meta_array = array_combine( $meta_key, $meta_value );

					// Calculate new order total.
					if ( $modification_charge > 0 ) {
					    $product_id                     = $item->get_product_id();
					    $mtrap_bus_service_fees         = get_post_meta( $product_id, 'mtrap_bus_tax', true );
					    
						$order_total_new = $previous_order_base_price + ( $previous_order_base_price * ( $modification_charge / 100 ) );
						$final_price_before_service_fees = $order_total_new - $previous_order_base_price;
						$final_price = $final_price_before_service_fees + ($final_price_before_service_fees * $mtrap_bus_service_fees / 100);

						// Add updated item to cart.
						WC()->cart->empty_cart();
						
						$mtrap_bus_off_days             = get_post_meta( $product_id, 'mtrap_bus_off_day', true );
						$off_days                       = maybe_unserialize( $mtrap_bus_off_days );
						$mtrap_bus_off_dates_range_from = get_post_meta( $product_id, 'mtrap_bus_off_dates_range_from_date', true );
						$mtrap_bus_off_dates_range_to   = get_post_meta( $product_id, 'mtrap_bus_off_dates_range_to_date', true );
						$quantity                       = wc_get_order_item_meta( $order_item_id, 'passenger', true );
						$seat_class                     = strtolower( wc_get_order_item_meta( $order_item_id, 'seat_class', true ) );
						$bus_stock_on_new_journey_day   = mtrap_stock_calculations( $journeydate, $journey_from, $journey_to,  $product_id );
						
					
						if ( !empty( $off_days ) && in_array( strtolower( $new_date->format( 'l' ) ), $off_days ) ) {
							?>
							<div class="elementor-section elementor-top-section elementor-element elementor-section-full_width elementor-section-height-min-height elementor-section-content-middle elementor-section elementor-section-boxed">
								<div class="elementor-container elementor-column-gap-extended">
									<div class="elementor-row elementor-col-100 elementor-top-column elementor-element elementor-widget-wrap">
										<div class="search-list-alert">
											<?php
											echo esc_html( 'Sorry, transportation is not available on that day, please choose another date!', 'winger' ); 
											?>
										</div>
									</div>
								</div>
							</div>
							<?php
						} elseif ( strtotime( $journeydate ) >= strtotime( $mtrap_bus_off_dates_range_from ) && strtotime( $journeydate ) <= strtotime( $mtrap_bus_off_dates_range_to ) ) {
							// If booking date is between bus off range skip current bus.
							?>
							<div class="elementor-section elementor-top-section elementor-element elementor-section-full_width elementor-section-height-min-height elementor-section-content-middle elementor-section elementor-section-boxed">
								<div class="elementor-container elementor-column-gap-extended">
									<div class="elementor-row elementor-col-100 elementor-top-column elementor-element elementor-widget-wrap">
										<div class="search-list-alert">
											<?php
											echo esc_html( 'Sorry, transportation is not available on that day, please choose another date!', 'winger' ); 
											?>
										</div>
									</div>
								</div>
							</div>
							<?php
							//elseif (array_key_exists($seat_class, $bus_stock_on_new_journey_day) && $quantity > $bus_stock_on_new_journey_day[$seat_class])
						} elseif ( ! empty( $bus_stock_on_new_journey_day[$seat_class] ) && $quantity > $bus_stock_on_new_journey_day[$seat_class] && array_key_exists($seat_class, $bus_stock_on_new_journey_day) ) {
    							?>
    							<div class="elementor-section elementor-top-section elementor-element elementor-section-full_width elementor-section-height-min-height elementor-section-content-middle elementor-section elementor-section-boxed">
    								<div class="elementor-container elementor-column-gap-extended">
    									<div class="elementor-row elementor-col-100 elementor-top-column elementor-element elementor-widget-wrap">
    										<div class="search-list-alert">
    											<?php
    											echo esc_html( 'Sorry, Not enough seats available for this seat class, please choose another date!', 'winger' );
    											?>
    										</div>
    									</div>
    								</div>
    							</div>
    							<?php
						} else {
							$cart_item_key     = WC()->cart->add_to_cart( $product_id, $quantity, 0, array(), array( 'custom_price' => $final_price ) );
							$item_meta_updated = array(
								'journey_from'           => $meta_array['journey_from'],
								'pickup_point'           => $meta_array['pickup_point'],
								'journey_to'             => $meta_array['journey_to'],
								'drop_point'             => $meta_array['drop_point'],
								'journey_date'           => $meta_array['journey_date'],
								'departure_time'         => $meta_array['departure_time'],
								'arrival_time'           => $meta_array['arrival_time'],
								'seat_class'             => $meta_array['seat_class'],
								'coach_type'             => $meta_array['coach_type'],
								'passenger'              => $meta_array['passenger'],
								'passenger_details'      => $meta_array['passenger_details'],
								'_product_id'            => $product_id,
								'_passenger_types'       => $meta_array['_passenger_types'],
								'_journey_from_id'       => $meta_array['_journey_from_id'],
								'_journey_to_id'         => $meta_array['_journey_to_id'],
								'_journy_day'            => $meta_array['_journy_day'],
								'_amenities_names'       => $meta_array['_amenities_names'],
								'_journey_duration'      => $meta_array['_journey_duration'],
								'_bus_tax'               => $meta_array['_bus_tax'],
								'_seat_class_price'      => $meta_array['_seat_class_price'],
								'_base_price'            => $meta_array['_base_price'],
								'_journey_price'         => $meta_array['_journey_price'],
								'_passenger_data_ticket' => $meta_array['_passenger_data_ticket'],
								'_ticket_modified'       => 1,
								'_ticket_modified_price' => $final_price,
								'_previous_order_id'     => $order_id,
								'_previous_order_booking_id' => $order_item_id,
							);

							// Save meta data to session.
							WC()->session->set( 'bus_details_' . $cart_item_key, $item_meta_updated );

							wp_safe_redirect( wc_get_cart_url() );
							exit;
						}
					} 
				}
			}
		} else {
			?>
			<div class="elementor-section elementor-top-section elementor-element elementor-section-full_width elementor-section-height-min-height elementor-section-content-middle elementor-section elementor-section-boxed">
				<div class="elementor-container elementor-column-gap-extended">
					<div class="elementor-row elementor-col-100 elementor-top-column elementor-element elementor-widget-wrap">
						<div class="search-list-alert">
							<?php echo 'Sorry, once you have cancelled or modified the journey, you can not modify it again!'; ?>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
	}
}


if ( ! empty( $mtrap_om_booking_number ) && ! empty( $mtrap_om_from_city ) && ! empty( $mtrap_om_to_city ) && ! empty( $mtrap_om_email ) && ! empty( $mtrap_om_journey_date ) ) {
	if ( strtotime( $mtrap_om_journey_date ) >= $today_date_timestamp ) {
		if ( wc_get_order_item_meta( $mtrap_om_booking_number, '_trip_order_status', true ) != 'partial-cancel' && wc_get_order_item_meta( $mtrap_om_booking_number, '_trip_order_status', true ) != 'modified' ) {
			global $wpdb;

			$order_id = $wpdb->get_var( $wpdb->prepare( "SELECT order_id FROM {$wpdb->prefix}woocommerce_order_items WHERE order_item_id = %d", $mtrap_om_booking_number ) );

			if ( $order_id ) {
				$mtrap_order = wc_get_order( $order_id );
				$order_item  = $mtrap_order->get_item( $mtrap_om_booking_number );

				if ( $order_item ) {
					$meta_keys   = array();
					$meta_values = array();
					$item_meta   = $order_item->get_meta_data();

					foreach ( $item_meta as $meta ) {
						$meta_keys[]   = $meta->key;
						$meta_values[] = $meta->value;
					}
					$mtrap_order_details   = ! empty( $meta_keys ) && ! empty( $meta_values ) ? array_combine( $meta_keys, $meta_values ) : '';
					$passenger_data_ticket = unserialize( $mtrap_order_details['_passenger_data_ticket'] );

					// Check if searched email exists in the order.
					if ( is_array( $passenger_data_ticket ) ) {
						$email_to_search = $mtrap_om_email;
						$email_found     = false;
						foreach ( $passenger_data_ticket as $passenger ) {
							if ( isset( $passenger['passenger_email'] ) && $passenger['passenger_email'] === $email_to_search ) {
								$email_found = true;
								break;
							}
						}
					}

					// Check if searched details are correct.
					if ( ! empty( $mtrap_order_details ) && $mtrap_order_details['_journey_from_id'] == $mtrap_om_from_city && $mtrap_order_details['_journey_to_id'] == $mtrap_om_to_city && $email_found == true && $mtrap_om_journey_date == gmdate( 'd-m-Y', strtotime( $mtrap_order_details['journey_date'] ) ) ) {
					    
						?>
						<div class="elementor-container elementor-column-gap-extended order-modification-wrap">
							<div class="elementor-row elementor-col-100 elementor-top-column elementor-element elementor-widget-wrap">
								<div class="bus-list-main" style="margin-top:20px;">
									<div class="bus-list-header">
										<ul>
											<li class="route-details"><?php esc_html_e( 'Route Name', 'winger' ); ?></li>
											<li class="Departure"><?php esc_html_e( 'Departure', 'winger' ); ?></li>
											<li class="Duration"><?php esc_html_e( 'Duration', 'winger' ); ?></li>
											<li class="Arrival"><?php esc_html_e( 'Arrival', 'winger' ); ?></li>
											<li class="Fare"><?php esc_html_e( 'Fare', 'winger' ); ?></li>
										</ul>
									</div>
									<div class="bus-list-items">
										<div class="list-item">
											<div class="route-details">
												<div class="title">
													<?php
													if ( ! empty( $mtrap_order_details['journey_from'] ) ) {
														echo esc_html( $mtrap_order_details['journey_from'] ) . ' ' . __( 'to', 'winger' ) . ' ';
													}
													if ( ! empty( $mtrap_order_details['journey_to'] ) ) {
														echo esc_html( $mtrap_order_details['journey_to'] );
													}
													?>
												</div>
											</div>
											<?php if ( ! empty( $mtrap_order_details['departure_time'] ) ) { ?>
												<div class="Departure">
													<span><?php esc_html_e( 'Departure Time', 'winger' ); ?></span>
													<?php echo esc_html( $mtrap_order_details['departure_time'] ); ?>
												</div>
											<?php } ?>
											<?php if ( ! empty( $mtrap_order_details['_journey_duration'] ) ) { ?>
												<div class="Duration">
													<span><?php esc_html_e( 'Duration', 'winger' ); ?></span>
													<?php echo esc_html( $mtrap_order_details['_journey_duration'] ); ?>
												</div>
											<?php } ?>
											<?php if ( ! empty( $mtrap_order_details['arrival_time'] ) ) { ?>
												<div class="Arrival">
													<span><?php esc_html_e( 'Arrival Time', 'winger' ); ?></span>
													<?php echo esc_html( $mtrap_order_details['arrival_time'] ); ?>
												</div>
											<?php } ?>
											<?php if ( ! empty( $mtrap_order_details['_journey_price'] ) ) { ?>
												<div class="Fare">
													<span><?php esc_html_e( 'Fare', 'winger' ); ?></span>
													<?php echo esc_html( $mtrap_order_details['_journey_price'] ); ?>
												</div>
											<?php } ?>
											<?php if ( ! empty( $mtrap_order_details['_amenities_names'] ) ) { ?>
												<div class="mtrap-additional-travel-details">
													<div class="amenities-details">
														<span><?php esc_html_e( 'Amenities Details:', 'winger' ); ?></span>
														<?php echo esc_html( $mtrap_order_details['_amenities_names'] ); ?>
													</div>
												</div>
											<?php } ?>
										</div>
										<div class="book_now_fx">
											<div class="">
												<?php if ( ! empty( $mtrap_order_details['pickup_point'] ) ) { ?>
													<div class="book_form_fx">
														<label class="mtrap-pickup-point"><?php esc_html_e( 'Pickup Point : ', 'winger' ); ?></label>
														<label class="mtrap-pickup-point"><?php echo esc_html( $mtrap_order_details['pickup_point'] ); ?></label>
													</div>
												<?php } ?>
												<?php if ( ! empty( $mtrap_order_details['drop_point'] ) ) { ?>
													<div class="book_form_fx">
														<label class="mtrap-bus-drop-point"><?php esc_html_e( 'Drop Point : ', 'winger' ); ?></label>
														<label><?php echo esc_html( $mtrap_order_details['drop_point'] ); ?></label>
													</div>
												<?php } ?>
												<?php if ( ! empty( $mtrap_order_details['seat_class'] ) ) { ?>
													<div class="book_form_fx">
														<label for="mtrap_sc_seat_class_selection"><?php esc_html_e( 'Seat Class : ', 'winger' ); ?></label>
														<label class="mtrap_sc_passenger_seat_selection">
															<?php echo esc_html( $mtrap_order_details['seat_class'] ); ?>
														</label>
													</div>
												<?php } ?>
												<?php if ( ! empty( $mtrap_order_details['passenger'] ) ) { ?>
													<div class="book_form_fx">
														<div class="mtrap_sc_passenger_selection">
															<label for="mtrap_sc_passenger_selection"><?php esc_html_e( 'Passenger : ', 'winger' ); ?></label>
															<label class="mtrap_sc_passenger filled fill_inited"><?php echo esc_html( $mtrap_order_details['passenger'] ); ?></label>
														</div>
													</div>
												<?php } ?>
												<div class="passenger_outer_div">
													<label class="mtrap_sc_passenger filled fill_inited">
														<?php esc_html_e( 'Update your passenger information', 'winger' ); ?>
													</label>
													<?php
													if ( ! empty( $mtrap_order_details['_passenger_data_ticket'] ) ) {
														$passenger_data = maybe_unserialize( $mtrap_order_details['_passenger_data_ticket'] );
														?>
														<form method="post" class="transportation-modification-form" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>">
															<input type="hidden" name="mtrap_form_submitted" value="1">
															<input type="hidden" name="number" value="<?php echo $mtrap_om_booking_number; ?>">
															<input type="hidden" name="from" value="<?php echo $mtrap_om_from_city; ?>">
															<input type="hidden" name="to" value="<?php echo $mtrap_om_to_city; ?>">
															<input type="hidden" name="id" value="<?php echo $order_id; ?>">
															<?php
															foreach ( $passenger_data as $index => $passenger ) {
																?>
																<div class="passenger_details_outer">
																	<label><?php esc_html_e( 'Passenger', 'winger' ); ?> - <?php echo $index + 1; ?></label>
																	<div class="passenger_details">
																		<div class="book_form_fx mtrap_fullname">
																			<input type="text" name="Fullname[]" class="mtrap_passenger_fullname filled fill_inited" placeholder="<?php esc_attr_e( 'Full Name', 'winger' ); ?>" value="<?php echo esc_attr( $passenger['passenger_name'] ); ?>">
																		</div>
																		<div class="book_form_fx mtrap_email">
																			<input type="email" name="Email[]" class="mtrap_passenger_email filled fill_inited" placeholder="<?php esc_attr_e( 'Email', 'winger' ); ?>" value="<?php echo esc_attr( $passenger['passenger_email'] ); ?>">
																		</div>
																		<div class="book_form_fx mtrap_phone">
																			<input type="text" name="Phone[]" class="mtrap_passenger_phone filled fill_inited" placeholder="<?php esc_attr_e( 'Phone', 'winger' ); ?>" value="<?php echo esc_attr( $passenger['passenger_phone'] ); ?>">
																		</div>
																		<div class="book_form_fx gender-dropdown">
																			<div class="select_container mtrap_gender">
																				<select name="gender[]" class="mtrap_passenger_gender filled fill_inited">
																					<option value="" selected disabled hidden><?php esc_html_e( 'Gender', 'winger' ); ?></option>
																					<option <?php selected( $passenger['passenger_gender'], 'Male' ); ?> value="Male"><?php esc_html_e( 'Male', 'winger' ); ?></option>
																					<option <?php selected( $passenger['passenger_gender'], 'Female' ); ?> value="Female"><?php esc_html_e( 'Female', 'winger' ); ?></option>
																					<option <?php selected( $passenger['passenger_gender'], 'Other' ); ?> value="Other"><?php esc_html_e( 'Other', 'winger' ); ?></option>
																				</select>
																			</div>
																		</div>
																		<div class="book_form_fx adult-child">
																			<div class="mtrap_pessagnertype">
																				<input type="text" readonly name="type[]" class="mtrap_passenger_type filled fill_inited" placeholder="<?php esc_attr_e( 'Type', 'winger' ); ?>" value="<?php echo esc_attr( $passenger['passenger_type'] ); ?>">
																			</div>
																		</div>
																	</div>
																</div>
																<?php
															}
															?>
															<div class="column-bus-search journey-date">
															<div class="book_form_fx">
																<label for="mtrap_sc_seat_class_selection"><?php esc_html_e( 'Change your journey date', 'winger' ); ?></label>
																<div class="mtrap_sc_passenger_seat_selection">
																	<input type="text" readonly class="changejourneydate" name="changejourneydate" placeholder="<?php echo __( 'Journey Date', 'winger' ); ?>" value="<?php echo esc_html( $mtrap_om_journey_date ); ?>">
																</div>
															</div>
															<div class="mtrap_bus_modification_continue_btn buttn_continue"><input type="submit" value="Modify your ticket"></div>
														</form>
														<?php
													}
													?>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<?php
					} else {
						?>
						<div class="elementor-section elementor-top-section elementor-element elementor-section-full_width elementor-section-height-min-height elementor-section-content-middle elementor-section elementor-section-boxed">
							<div class="elementor-container elementor-column-gap-extended">
								<div class="elementor-row elementor-col-100 elementor-top-column elementor-element elementor-widget-wrap">
									<div class="search-list-alert">
								<?php esc_html_e( 'Booking not found please make sure that booking details are correct or contact us!', 'winger' ); ?>
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
							<?php esc_html_e( 'Booking not found please make sure that booking details are correct or contact us!', 'winger' ); ?>
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
						<?php esc_html_e( 'Booking not found please make sure that booking details are correct or contact us!', 'winger' ); ?>
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
					<?php esc_html_e( 'Sorry, This order is already cancelled once you can not modify it!', 'winger' ); ?>
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
				<?php esc_html_e( 'Journey is in the past you can not cancel this journey!', 'winger' ); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
} elseif ( ! isset( $_POST['mtrap_form_submitted'] ) ) {
	?>
		<div class="elementor-section elementor-top-section elementor-element elementor-section-full_width elementor-section-height-min-height elementor-section-content-middle elementor-section elementor-section-boxed">
			<div class="elementor-container elementor-column-gap-extended">
				<div class="elementor-row elementor-col-100 elementor-top-column elementor-element elementor-widget-wrap">
					<div class="search-list-alert">
					<?php esc_html_e( 'Please enter the ticket modification details!', 'winger' ); ?>
					</div>
				</div>
			</div>
		</div>
		<?php

}
get_footer();
?>
