<?php
/**
 * Template Name: Order Cancellation
 */

get_header();

global $wpdb;

?>
<div class="mtrap-ticket-cancellation-header">
	<?php echo do_shortcode( '[trx_sc_layouts layout="9199"]' ); ?>
</div>
<?php

$booking_number       = ! empty( $_REQUEST['booking_number'] ) ? sanitize_text_field( $_REQUEST['booking_number'] ) : '';
$journeydate          = ! empty( $_REQUEST['journeydate'] ) ? sanitize_text_field( $_REQUEST['journeydate'] ) : '';
$email                = ! empty( $_REQUEST['journeyemail'] ) ? sanitize_text_field( $_REQUEST['journeyemail'] ) : '';
$source_city          = ! empty( $_REQUEST['destination_from'] ) ? sanitize_text_field( $_REQUEST['destination_from'] ) : '';
$destination_city     = ! empty( $_REQUEST['destination_to'] ) ? sanitize_text_field( $_REQUEST['destination_to'] ) : '';
$today_date_timestamp = strtotime( gmdate( 'd-m-Y' ) );



if ( ! empty( $booking_number ) && ! empty( $journeydate ) && ! empty( $email ) && ! empty( $source_city ) && ! empty( $destination_city ) ) {
	if ( strtotime( $journeydate ) > $today_date_timestamp ) {
		if ( wc_get_order_item_meta( $booking_number, '_trip_order_status', true ) != 'modified' && wc_get_order_item_meta( $booking_number, '_trip_order_status', true ) != 'partial-cancel' ) {
			global $wpdb;

			$order_id = $wpdb->get_var( $wpdb->prepare( "SELECT order_id FROM {$wpdb->prefix}woocommerce_order_items WHERE order_item_id = %d", $booking_number ) );

			if ( $order_id ) {
				$mtrap_order = wc_get_order( $order_id );
				$order_item  = $mtrap_order->get_item( $booking_number );

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
						$email_to_search = $email;
						$email_found     = false;
						foreach ( $passenger_data_ticket as $passenger ) {
							if ( isset( $passenger['passenger_email'] ) && $passenger['passenger_email'] === $email_to_search ) {
								$email_found = true;
								break;
							}
						}
					}
					// Check if searched details are correct.
					if ( ! empty( $mtrap_order_details ) && $mtrap_order_details['_journey_from_id'] == $source_city && $mtrap_order_details['_journey_to_id'] == $destination_city && $email_found == true && $journeydate == gmdate( 'd-m-Y', strtotime( $mtrap_order_details['journey_date'] ) ) ) {
					    
						?>
						<div class="elementor-container elementor-column-gap-extended order-cancellation-wrap">
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
											<div>
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
											<div class="passenger_outer_div">
												<label class="mtrap_sc_passenger filled fill_inited">
													<?php esc_html_e( 'Cancel the passengers', 'winger' ); ?>
												</label>
												<?php
												if ( ! empty( $mtrap_order_details['_passenger_data_ticket'] ) ) {
													$passenger_data = maybe_unserialize( $mtrap_order_details['_passenger_data_ticket'] );
													?>
													<div class="transportation-cancellation-form" data-order-id= "<?php echo $order_id; ?>" data-item-id= "<?php echo $booking_number; ?>">
														<?php
														foreach ( $passenger_data as $index => $passenger ) {
															if ( $passenger['passenger_order_status'] == 'processing' || $passenger['passenger_order_status'] == 'completed' ) {
																?>
																<div class="passenger_details_outer">
																	<label><?php esc_html_e( 'Passenger', 'winger' ); ?> - <?php echo $index + 1; ?></label>
																	<div class="passenger_details">
																		<div class="book_form_fx mtrap_fullname">
																			<input type="text" readonly name="Fullname[]" class="mtrap_passenger_fullname filled fill_inited" placeholder="<?php esc_attr_e( 'Full Name', 'winger' ); ?>" value="<?php echo esc_attr( $passenger['passenger_name'] ); ?>">
																		</div>
																		<div class="book_form_fx mtrap_email">
																			<input type="email" readonly name="Email[]" class="mtrap_passenger_email filled fill_inited" placeholder="<?php esc_attr_e( 'Email', 'winger' ); ?>" value="<?php echo esc_attr( $passenger['passenger_email'] ); ?>">
																		</div>
																		<div class="book_form_fx mtrap_phone">
																			<input type="text" readonly name="Phone[]" class="mtrap_passenger_phone filled fill_inited" placeholder="<?php esc_attr_e( 'Phone', 'winger' ); ?>" value="<?php echo esc_attr( $passenger['passenger_phone'] ); ?>">
																		</div>
																		<div class="book_form_fx gender-dropdown">
																			<div class="select_container mtrap_gender">
																				<select name="gender[]" class="mtrap_passenger_gender disabled readonly filled fill_inited">
																					<option value="<?php echo esc_attr( $passenger['passenger_gender'] ); ?>" selected disabled hidden><?php echo esc_attr( $passenger['passenger_gender'] ); ?></option>
																				</select>
																			</div>
																		</div> 
																		<div class="book_form_fx adult-child">
																			<div class="mtrap_pessagnertype">
																				<input type="text" readonly name="type[]" class="mtrap_passenger_type filled fill_inited" placeholder="<?php esc_attr_e( 'Type', 'winger' ); ?>" value="<?php echo esc_attr( $passenger['passenger_type'] ); ?>">
																			</div>
																		</div>
																		<div class="book_form_fx cancel-ticket">
																			<div class="mtrap_cancel_ticket">
																				<a class="mtrap_bus_cancel_btn trx_popup_button sc_button" href="javascript:void(0);"><?php esc_attr_e( 'Cancel', 'winger' ); ?></a>
																			</div>
																		</div>
																	</div>
																</div>
																<?php
															}
														}
														?>
													</div>
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
					<?php esc_html_e( 'Sorry, modified journey cannot be cancelled!', 'winger' ); ?>
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
} else {
	?>
	<div class="elementor-section elementor-top-section elementor-element elementor-section-full_width elementor-section-height-min-height elementor-section-content-middle elementor-section elementor-section-boxed">
		<div class="elementor-container elementor-column-gap-extended">
			<div class="elementor-row elementor-col-100 elementor-top-column elementor-element elementor-widget-wrap">
				<div class="search-list-alert">
			<?php esc_html_e( 'Please enter the cancellation details!', 'winger' ); ?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

get_footer();
?>

<script type="text/javascript">
	// Bus cancellation flow - On continue click ajax call.
	jQuery(document).on('click', '.mtrap_bus_cancel_btn', function() {
		currentContainer = jQuery(this).parents('.passenger_details_outer');
		let orderID = jQuery(currentContainer).parents('.transportation-cancellation-form').data('order-id');
		let itemID = jQuery(currentContainer).parents('.transportation-cancellation-form').data('item-id');
		let passengerName = jQuery(currentContainer).children('.passenger_details').find('.mtrap_fullname input').val();
		let passengerEmail = jQuery(currentContainer).children('.passenger_details').find('.mtrap_email input').val();
		let passengerPhone = jQuery(currentContainer).children('.passenger_details').find('.mtrap_phone input').val();
		let passengerGender = jQuery(currentContainer).children('.passenger_details').find('.mtrap_passenger_gender option:selected').val();
		let passengerType = jQuery(currentContainer).children('.passenger_details').find('.mtrap_pessagnertype input').val();
		
		jQuery.ajax({
			type: 'POST',
			url: ajaxObj.ajax_url,
			data: {
				action: "mtrap_delete_order_cancellation_data",
				security: ajaxObj.ajax_nonce_remove_passenger,
				order_id: orderID,
				item_id: itemID,
				passenger_name: passengerName,
				passenger_email: passengerEmail,
				passenger_phone: passengerPhone,
				passenger_gender: passengerGender,
				passenger_type: passengerType,
			},
			beforeSend: function (data) {
				currentContainer.addClass('section-loader');
				currentContainer.append('<div class="search-loader-main"><div class="search-loader"></div></div>');
			},
			success: function (data) {
				currentContainer.html(data);
				currentContainer.removeClass('section-loader');
			},
			error: function (data) {
				currentContainer.removeClass('section-loader');
				jQuery('.search-loader-main').remove();
				alert('Something went wrong! Please try again!');
				console.log(data); return false;
				//location.reload();
			},
		});
	});
</script>
