<?php
/**
 * Fetch busstops for pricing & routes tab - single procuct.
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'wp_ajax_get_destination_details_callback', 'get_destination_details_callback' );
add_action( 'wp_ajax_nopriv_get_destination_details_callback', 'get_destination_details_callback' );
/**
 * Remove default woocommerce post types.
 */
function get_destination_details_callback() {

	check_ajax_referer( 'bus-bookingform', 'security' );

	$term_id = sanitize_text_field( $_POST['term_id'] );

	$meta_fields = get_term_meta( $term_id, 'mtrap_bus_stops_route_meta', true );
	echo '<select class="destination_to" name="destination_to">';
	echo '<option value="">' . __( 'Select To City', 'winger' ) . '</option>';
	if ( ! empty( $meta_fields ) ) {
		foreach ( $meta_fields as $related_stations_meta ) {
			$mtrap_term_name = get_term( $related_stations_meta )->name;
			if ( ! empty( $mtrap_term_name ) ) {
				echo '<option value=' . esc_html( $related_stations_meta ) . '>' . esc_html( ucfirst( $mtrap_term_name ) ) . '</option>';
			}
		}
	}
	echo '</select>';
	die;
}


add_action( 'wp_ajax_get_booking_details_fetch_to_cart', 'get_booking_details_fetch_to_cart' );
add_action( 'wp_ajax_nopriv_get_booking_details_fetch_to_cart', 'get_booking_details_fetch_to_cart' );
/**
 * Remove default woocommerce post types.
 */
function get_booking_details_fetch_to_cart() {

	// phpcs:disable WordPress.Security.NonceVerification.Missing
	if ( ! isset( $_POST['post_id'] ) ) {
		return;
	}

	check_ajax_referer( 'bus-bookingform', 'security' );

	global $wpdb;

	$count_passengers = $_POST['passenger_count'];

	$return              = false;
	$error               = '';
	$passenger_error     = array();
	$journey_return_date = ! empty( $_POST['journey_return_date'] ) ? sanitize_text_field( $_POST['journey_return_date'] ) : '';

	if ( ! empty( $journey_return_date ) ) {
		$journey_from = ! empty( $_POST['journey_from'] ) ? sanitize_text_field( $_POST['journey_from'] ) : '';
		$journey_to   = ! empty( $_POST['journey_to'] ) ? sanitize_text_field( $_POST['journey_to'] ) : '';
		$journey_date = ! empty( $_POST['journey_date'] ) ? sanitize_text_field( $_POST['journey_date'] ) : '';
		$return       = true;
	} else {
		$journey_from = ! empty( $_POST['journey_to'] ) ? sanitize_text_field( $_POST['journey_to'] ) : '';
		$journey_to   = ! empty( $_POST['journey_from'] ) ? sanitize_text_field( $_POST['journey_from'] ) : '';
		$journey_date = ! empty( $_POST['journey_date'] ) ? sanitize_text_field( $_POST['journey_date'] ) : '';
		$return       = false;
	}
	$post_id                  = ! empty( $_POST['post_id'] ) ? sanitize_text_field( $_POST['post_id'] ) : '';
	$seat_class               = ! empty( $_POST['seat_class'] ) ? sanitize_text_field( $_POST['seat_class'] ) : '';
	$passenger_data_unslashed = ! empty( $_POST['passenger_data'] ) ? wp_unslash( $_POST['passenger_data'] ) : '';
	$passenger_data_arr       = json_decode( $passenger_data_unslashed );
	$passenger_data           = ! empty( $passenger_data_arr ) ? maybe_serialize( $passenger_data_arr ) : '';

	if ( empty( $post_id ) ) {
		$data = array(
			'error'        => __( 'Selected bus is not available right now. Please try again later.', 'winger' ),
			'return'       => $return,
			'journey_to'   => $journey_to,
			'journey_date' => $journey_date,
			'journey_from' => $journey_from,
			'checkout'     => home_url() . '/cart/',
		);
		echo json_encode( $data );
		die;
	}

	if ( empty( $seat_class ) ) {
		$data = array(
			'error'        => __( 'Please enter the seat class for your journey.', 'winger' ),
			'return'       => $return,
			'journey_to'   => $journey_to,
			'journey_date' => $journey_date,
			'journey_from' => $journey_from,
			'checkout'     => home_url() . '/cart/',
		);
		echo json_encode( $data );
		die;
	}
	
	if ( $passenger_data ) {
		$passenger_data_unserialized = maybe_unserialize( $passenger_data );
		$required_fields             = array( 'passenger_name', 'passenger_email', 'passenger_phone', 'passenger_gender', 'passenger_type' );
		foreach ( $passenger_data_unserialized as $passenger ) {
			foreach ( $required_fields as $field ) {
				if ( empty( $passenger->$field ) ) {
					$data = array(
						'error'        => __( 'Please fill up all details for passengers.', 'winger' ),
						'return'       => $return,
						'journey_to'   => $journey_to,
						'journey_date' => $journey_date,
						'journey_from' => $journey_from,
						'checkout'     => home_url() . '/cart/',
					);
					echo json_encode( $data );
					die;
				}
			}
		}
	}

	if ( ! empty( maybe_unserialize( $passenger_data ) ) ) {
		foreach ( maybe_unserialize( $passenger_data ) as $passenger_dtl ) {
			if ( ! empty( $passenger_dtl->passenger_name ) && ! empty( $passenger_dtl->passenger_gender ) && ! empty( $passenger_dtl->passenger_type ) ) {
				$passenger_error[] = array(
					'passenger_name'   => $passenger_dtl->passenger_name,
					'passenger_email'  => $passenger_dtl->passenger_email,
					'passenger_phone'  => $passenger_dtl->passenger_phone,
					'passenger_gender' => $passenger_dtl->passenger_gender,
					'passenger_type'   => $passenger_dtl->passenger_type,
				);
			}
		}
	}

	if ( count( maybe_unserialize( $passenger_data ) ) !== count( $passenger_error ) ) {

		$data = array(
			'error'        => __( 'Please fill up all details for passengers.', 'winger' ),
			'return'       => $return,
			'journey_to'   => $journey_to,
			'journey_date' => $journey_date,
			'journey_from' => $journey_from,
			'checkout'     => home_url() . '/cart/',
		);
		echo json_encode( $data );
		die;
	}

	// Get all the station ids.
	$mtrap_get_bus_stop_pricing_details = $wpdb->get_results(
		"SELECT * FROM {$wpdb->prefix}mtrap_custom_bus_stops WHERE `post_id` = $post_id",
		ARRAY_A
	);

	$mtrap_boarding_station_id = get_post_meta( $post_id, 'mtrap_bus_stops', true );
	$mtrap_bus_tax             = get_post_meta( $post_id, 'mtrap_bus_tax', true );
	$mtrap_seat_class          = get_post_meta( $post_id, 'mtrap_seat_class', true );
	$mtrap_boarding_time       = get_post_meta( $post_id, 'mtrap_boarding_time', true );
	$mtrap_bus_coach_type      = get_post_meta( $post_id, 'mtrap_bus_coach_type', true );

	if ( ! empty( $mtrap_get_bus_stop_pricing_details ) ) {

		$mtrap_final_price = mtrap_price_calculations(
			$post_id,
			$mtrap_boarding_station_id,
			sanitize_text_field( $_POST['journey_from'] ),
			sanitize_text_field( $_POST['journey_to'] ),
			sanitize_text_field( $_POST['passenger_types'] ),
			$mtrap_bus_tax,
			$mtrap_seat_class[ sanitize_text_field( $_POST['seat_class'] ) ]
		);

		$product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', sanitize_text_field( $_POST['post_id'] ) );
		$product           = wc_get_product( $product_id );
		$quantity          = 1;
		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
		$product_status    = get_post_status( $product_id );
		$variation_id      = 0;
		$variation         = array();

		if ( $product && 'variation' === $product->get_type() ) {
			$variation_id = $product_id;
			$product_id   = $product->get_parent_id();
			$variation    = $product->get_variation_attributes();
		}

		if ( empty( $mtrap_final_price['final_price'] ) || $mtrap_final_price['final_price'] === 0 ) {

			$error = __( 'Sorry! This bus is not available for now. Please try again later.', 'winger' );

		} elseif ( $passed_validation && 'publish' === $product_status ) {

			$cart_item_key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation );

			if ( ! empty( $cart_item_key ) ) {

				do_action( 'woocommerce_ajax_added_to_cart', $product_id );

				// variables for passing in session.
				$station_details        = array();
				$time_details           = array();
				$journey_from_data      = ! empty( $_POST['journey_from'] ) ? sanitize_text_field( sanitize_text_field( $_POST['journey_from'] ) ) : '';
				$journey_to_data        = ! empty( $_POST['journey_to'] ) ? sanitize_text_field( sanitize_text_field( $_POST['journey_to'] ) ) : '';
				$journey_from_name_data = get_term_by( 'id', $journey_from_data, 'bus-stops' );
				$journey_to_name_data   = get_term_by( 'id', $journey_to_data, 'bus-stops' );
				$coach_type             = get_term_by( 'id', $mtrap_bus_coach_type, 'bus-types' );
				$amenities_details      = get_the_terms( $product_id, 'aminities' );
				$amenities_names        = join( ', ', wp_list_pluck( $amenities_details, 'name' ) );

				// custom table query to get station details.
				if ( ! empty( $mtrap_get_bus_stop_pricing_details ) ) {
					foreach ( array_filter( $mtrap_get_bus_stop_pricing_details ) as $stop_details ) {
						$station_details[]        = ! empty( $stop_details['station_stop'] ) ? $stop_details['station_stop'] : '';
						$time_details[]           = ! empty( $stop_details['station_time'] ) ? $stop_details['station_time'] : '';
						$time_departure[]         = ! empty( $stop_details['station_departure_time'] ) ? $stop_details['station_departure_time'] : '';
						$station_day_difference[] = ! empty( $stop_details['station_day_difference'] ) ? $stop_details['station_day_difference'] : 0;
					}
					// combine arrays.
					$mtrap_combined_station_time            = array_combine( $station_details, $time_details );
					$mtrap_combined_station_departure_time  = array_combine( $station_details, $time_departure );
					$mtrap_combined_station_time_difference = array_combine( $station_details, $station_day_difference );
				}
				// get departure time.
				if ( array_key_exists( $_POST['journey_from'], $mtrap_combined_station_departure_time ) ) {
					$departure_time = esc_html( gmdate( 'h:i A', strtotime( $mtrap_combined_station_departure_time[ sanitize_text_field( $_POST['journey_from'] ) ] ) ) );
				} else {
					$departure_time = esc_html( gmdate( 'h:i A', strtotime( $mtrap_boarding_time ) ) );
				}

				// get arrival time.
				if ( array_key_exists( $_POST['journey_to'], $mtrap_combined_station_time ) ) {
					$arrival_time = esc_html( gmdate( 'h:i A', strtotime( $mtrap_combined_station_time[ sanitize_text_field( $_POST['journey_to'] ) ] ) ) );
				}

				// calculate time duration.
				if ( ( $_POST['journey_from'] != $mtrap_boarding_station_id ) ) {
					$start_time = $mtrap_combined_station_time[ sanitize_text_field( $_POST['journey_from'] ) ];
				} else {
					$start_time = $mtrap_boarding_time;
				}
				$end_time = $mtrap_combined_station_time[ sanitize_text_field( $_POST['journey_to'] ) ];

				$start_timestamp  = strtotime( $start_time );
				$end_timestamp    = strtotime( $end_time );
				$mtrap_duration   = $end_timestamp - $start_timestamp;
				$duration_hours   = floor( $mtrap_duration / 3600 );
				$duration_minutes = ( $mtrap_duration % 3600 ) / 60;

				if ( ! empty( $mtrap_combined_station_time_difference && $stop_details['station_day'] != 'same-day' ) ) {
					$mtrap_remaining_days = $mtrap_combined_station_time_difference[ sanitize_text_field( $_POST['journey_to'] ) ];
				}
				$days_count    = ! empty( $mtrap_remaining_days ) ? $mtrap_remaining_days . 'D ' : '';
				$hours_count   = ! empty( $duration_hours ) ? $duration_hours . 'H ' : '';
				$minutes_count = ! empty( $duration_minutes ) ? $duration_minutes . 'M' : '';

				$journey_duration = esc_html( $days_count . $hours_count . $minutes_count );

				if ( ! empty( $journey_date ) ) {
					$partial_from_date = explode( '-', $journey_date );
					$journy_date_day   = gregoriantojd( $partial_from_date[1], $partial_from_date[0], $partial_from_date[2] );
				}

				$mtrap_sc_boarding_pickup_point = get_term_meta( sanitize_text_field( $_POST['journey_from'] ), 'mtrap_bus_stops_pickuppoint_meta', true );
				$mtrap_sc_drop_point            = get_term_meta( sanitize_text_field( $_POST['journey_to'] ), 'mtrap_bus_stops_pickuppoint_meta', true );

				$mtrap_get_seat_class_slug = get_term_by( 'slug', sanitize_text_field( $_POST['seat_class'] ), 'seat-class' );

				$mtrap_get_seat_class_name = ! empty( $mtrap_get_seat_class_slug->name ) ? $mtrap_get_seat_class_slug->name : '';

				$bus_data = WC()->session->get( 'bus_details_' . $cart_item_key );

				if ( ! WC()->session->has_session() ) {
					WC()->session->set_customer_session_cookie( true );
				}

				if ( isset( $bus_data ) ) {
					WC()->session->__unset( 'bus_details_' . $cart_item_key ); // Remove session variable.
				}

				$mtrap_session_content = array(
					'journey_from'           => isset( $journey_from_data ) ? $journey_from_name_data->name : '',
					'pickup_point'           => $mtrap_sc_boarding_pickup_point,
					'journey_to'             => isset( $journey_to_data ) ? $journey_to_name_data->name : '',
					'drop_point'             => $mtrap_sc_drop_point,
					'journey_date'           => sanitize_text_field( $_POST['journey_date'] ),
					'departure_time'         => $departure_time,
					'arrival_time'           => $arrival_time,
					'seat_class'             => $mtrap_get_seat_class_name,
					'coach_type'             => isset( $coach_type ) ? $coach_type->name : '',
					'passenger'              => $count_passengers,
					'passenger_details'      => $passenger_data,
					'_passenger_types'       => sanitize_text_field( $_POST['passenger_types'] ),
					'_product_id'            => $product_id,
					'_journey_from_id'       => $journey_from_data,
					'_journey_to_id'         => $journey_to_data,
					'_journy_day'            => jddayofweek( $journy_date_day, 1 ),
					'_amenities_names'       => $amenities_names,
					'_journey_duration'      => $journey_duration,
					'_bus_tax'               => $mtrap_bus_tax,
					'_seat_class_price'      => isset( $mtrap_seat_class[ $seat_class ] ) ? $mtrap_seat_class[ $seat_class ] : '',
					'_base_price'            => $mtrap_final_price['base_price'],
					'_journey_price'         => $mtrap_final_price['final_price'],
					'_passenger_data_ticket' => $passenger_data,
				);

				// Set the session data.
				WC()->session->set(
					'bus_details_' . $cart_item_key,
					$mtrap_session_content
				);

			} else {

				$error = __( 'Sorry! This bus is not available for now. Please try again later.', 'winger' );
			}
		} else {

			$error = __( 'Sorry! This bus is not available for now. Please try again later.', 'winger' );
		}
	}

	$data = array(
		'error'        => $error,
		'return'       => $return,
		'journey_to'   => $journey_to,
		'journey_date' => $journey_date,
		'journey_from' => $journey_from,
		'checkout'     => home_url() . '/cart/',

	);

	echo json_encode( $data );

	die;
}

add_action( 'woocommerce_before_calculate_totals', 'woocommerce_custom_price_to_cart_item', 10, 1 );
/**
 * Maniplulate the final price before cart price calculations.
 */
function woocommerce_custom_price_to_cart_item( $cart_object ) {

	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;
	}

	if ( $cart_object ) {

		foreach ( $cart_object->get_cart() as $cart_item_key => $cart_item ) {

			$mtrap_bus_details_session_set = WC()->session->get( 'bus_details_' . $cart_item_key );
			$journey_from_id               = isset( $mtrap_bus_details_session_set['_journey_from_id'] ) ? $mtrap_bus_details_session_set['_journey_from_id'] : '';
			$journey_to_id                 = isset( $mtrap_bus_details_session_set['_journey_to_id'] ) ? $mtrap_bus_details_session_set['_journey_to_id'] : '';
			$passenger_types               = isset( $mtrap_bus_details_session_set['_passenger_types'] ) ? $mtrap_bus_details_session_set['_passenger_types'] : '';
			$bus_tax                       = isset( $mtrap_bus_details_session_set['_bus_tax'] ) ? $mtrap_bus_details_session_set['_bus_tax'] : '';
			$seat_class_price              = isset( $mtrap_bus_details_session_set['_seat_class_price'] ) ? $mtrap_bus_details_session_set['_seat_class_price'] : '';
			$mtrap_boarding_station_id     = get_post_meta( $cart_item['product_id'], 'mtrap_bus_stops', true );

			$ticket_modified       = isset( $mtrap_bus_details_session_set['_ticket_modified'] ) ? $mtrap_bus_details_session_set['_ticket_modified'] : 0;
			$ticket_modified_price = isset( $mtrap_bus_details_session_set['_ticket_modified_price'] ) ? $mtrap_bus_details_session_set['_ticket_modified_price'] : '';

			if ( $ticket_modified != 1 && empty( $ticket_modified_price ) ) {
				$final_price_arr = mtrap_price_calculations(
					$cart_item['product_id'],
					$mtrap_boarding_station_id,
					$journey_from_id,
					$journey_to_id,
					$passenger_types,
					$bus_tax,
					$seat_class_price
				);
    
				$base_price  = $final_price_arr['base_price'];
				$final_price = $final_price_arr['final_price'];

				if ( ! empty( $base_price ) && ! empty( $final_price ) ) {
					$cart_item['data']->set_price( $final_price );
					$cart_item['data']->get_price();
				}
			} else {
				$cart_item['data']->set_price( $ticket_modified_price );
				$cart_item['data']->get_price();
			}

			// If custom price is 0, remove the cart item.
			// if ( empty( $base_price ) || empty( $final_price ) ) {
			// $cart_object->remove_cart_item( $cart_item_key );
			// }
		}
	}
}


add_filter( 'woocommerce_get_item_data', 'mtrap_get_cart_item_data', 10, 2 );
/**
 * Display cart item meta data.
 */
function mtrap_get_cart_item_data( $item_data, $cart_item_data ) {

	$mtrap_bus_details_session_set = WC()->session->get( 'bus_details_' . $cart_item_data['key'] );

	if ( $mtrap_bus_details_session_set && $mtrap_bus_details_session_set['_product_id'] == $cart_item_data['product_id'] ) {
		unset( $mtrap_bus_details_session_set['journey_from'] );
		unset( $mtrap_bus_details_session_set['journey_to'] );
		unset( $mtrap_bus_details_session_set['_product_id'] );
		unset( $mtrap_bus_details_session_set['_journy_day'] );
		unset( $mtrap_bus_details_session_set['_amenities_names'] );
		unset( $mtrap_bus_details_session_set['_journey_duration'] );
		unset( $mtrap_bus_details_session_set['_bus_tax'] );
		unset( $mtrap_bus_details_session_set['_seat_class_price'] );
		unset( $mtrap_bus_details_session_set['_base_price'] );
		unset( $mtrap_bus_details_session_set['_journey_price'] );
		unset( $mtrap_bus_details_session_set['_journey_from_id'] );
		unset( $mtrap_bus_details_session_set['_journey_to_id'] );
		unset( $mtrap_bus_details_session_set['_passenger_types'] );
		unset( $mtrap_bus_details_session_set['_passenger_data_ticket'] );

		foreach ( $mtrap_bus_details_session_set as $key => $value ) {
			if ( ! empty( $key ) && ! empty( $value ) ) {
				$filtered_key = str_replace( '_', ' ', ucfirst( $key ) );
				if ( $key !== 'passenger_details' ) {
					$item_data[] = array(
						'key'   => __( $filtered_key, 'winger' ),
						'value' => wc_clean( $value ),
					);
				} else {
					$passenger_details = maybe_unserialize( $value );
					if ( $passenger_details ) {
						$passenger_info = '';
						if ( is_array( $passenger_details ) ) {
							foreach ( $passenger_details as $passenger ) {
								$passenger_info .= "{$passenger->passenger_name} ( {$passenger->passenger_email}, {$passenger->passenger_phone}, {$passenger->passenger_gender}, {$passenger->passenger_type} ), ";
							}
							$passenger_info = rtrim( $passenger_info, ', ' );
							$item_data[]    = array(
								'key'   => __( 'Passenger details', 'winger' ),
								'value' => wc_clean( $passenger_info ),
							);
						} else {
							$item_data[] = array(
								'key'   => __( 'Passenger details', 'winger' ),
								'value' => wc_clean( $value ),
							);
						}
					}
				}
			}
		}
	}
	return $item_data;
}


add_action( 'woocommerce_add_order_item_meta', 'add_order_item_meta', 10, 3 );
/**
 * on checkout update order meta.
 */
function add_order_item_meta( $item_id, $cart_item, $cart_item_key ) {
	$mtrap_bus_details_session_set = WC()->session->get( 'bus_details_' . $cart_item_key );
	if ( isset( $mtrap_bus_details_session_set ) ) {
		foreach ( $mtrap_bus_details_session_set as $key => $value ) {
			if ( ! empty( $key ) && ! empty( $value ) ) {
				if ( $key !== 'passenger_details' && $key !== '_passenger_types' && $key !== '_passenger_data_ticket' ) {
					wc_add_order_item_meta( $item_id, __( $key, 'winger' ), $value );
				} elseif ( $key === 'passenger_details' ) {
					if ( ! empty( $value ) ) {
						$passenger_details = maybe_unserialize( $value );
						if ( is_array( $passenger_details ) ) {
							$passenger_info = '';
							foreach ( $passenger_details as $passenger ) {
								$passenger_info .= "{$passenger->passenger_name} ( {$passenger->passenger_email}, {$passenger->passenger_phone}, {$passenger->passenger_gender}, {$passenger->passenger_type} ), ";
							}
							$passenger_info = rtrim( $passenger_info, ', ' );
							wc_add_order_item_meta( $item_id, __( $key, 'winger' ), $passenger_info );
						} else {
							wc_add_order_item_meta( $item_id, __( $key, 'winger' ), $value );
						}
					}
				} elseif ( $key === '_passenger_types' ) {
					wc_add_order_item_meta( $item_id, __( $key, 'winger' ), wp_unslash( $value ) );
				} elseif ( $key === '_passenger_data_ticket' ) {
					$passenger_data_ticket = maybe_unserialize( $value );
					if ( ! empty( $passenger_data_ticket ) ) {
						$passenger_output = array();
						if( is_array( $passenger_data_ticket ) ){
    						foreach ( $passenger_data_ticket as $passenger ) {
    							$journey_pnr_number = gmdate( 'Ymd' ) . $item_id . wp_rand( 1, 100 );
    							if ( is_array ( $passenger ) ){
    							    $passenger_output[] = array(
        								'booking_no'       => $item_id,
        								'ticket_no'        => $journey_pnr_number,
        								'passenger_name'   => $passenger['passenger_name'],
        								'passenger_email'  => $passenger['passenger_email'],
        								'passenger_phone'  => $passenger['passenger_phone'],
        								'passenger_gender' => $passenger['passenger_gender'],
        								'passenger_type'   => $passenger['passenger_type'],
    							    );
    							} else{ 
    							    $passenger_output[] = array(
        								'booking_no'       => $item_id,
        								'ticket_no'        => $journey_pnr_number,
        								'passenger_name'   => $passenger->passenger_name,
        								'passenger_email'  => $passenger->passenger_email,
        								'passenger_phone'  => $passenger->passenger_phone,
        								'passenger_gender' => $passenger->passenger_gender,
        								'passenger_type'   => $passenger->passenger_type,
    							    );
    							}
    						
    						}
						}
						wc_add_order_item_meta( $item_id, __( $key, 'winger' ), maybe_serialize( $passenger_output ) );
					}
				}
			}
		}
	}
}

add_action( 'woocommerce_thankyou', 'mtrap_add_order_item_meta_thank_you_page', 10, 1 );
/**
 * Add order item meta "_trip_order_status" and WooCommerce order status to each order item on the thank you page.
 *
 * @param int $order_id Order ID.
 */
function mtrap_add_order_item_meta_thank_you_page( $order_id ) {
	if ( ! $order_id ) {
		return;
	}
	global $wpdb;
	$customer_details_table = $wpdb->prefix . 'mtrap_customer_details';
	$order                  = wc_get_order( $order_id );
	$order_status           = $order->get_status();
	// Loop through each order item and add the meta.
	foreach ( $order->get_items() as $item_id => $item ) {

		$booking_exists   = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $customer_details_table WHERE booking_no = %d", $item_id ) );
		$order_passengers = wc_get_order_item_meta( $item_id, '_passenger_data_ticket', true );
		$journey_date     = wc_get_order_item_meta( $item_id, 'journey_date', true );
		$journey_from     = wc_get_order_item_meta( $item_id, '_journey_from_id', true );
		$journey_to       = wc_get_order_item_meta( $item_id, '_journey_to_id', true );
		$seat_class       = wc_get_order_item_meta( $item_id, 'seat_class', true );

		$unserialized_passengers = maybe_unserialize( $order_passengers );
		$product_id              = $item->get_product_id();
		if ( $unserialized_passengers ) {
			foreach ( $unserialized_passengers as &$passenger ) {
				$passenger['product_id']             = $product_id;
				$passenger['order_id']               = $order_id;
				$passenger['passenger_order_status'] = $order_status;
				$passenger['journey_date']           = $journey_date;
				$passenger['updated_date']           = $journey_date;
				$passenger['journey_from']           = $journey_from;
				$passenger['journey_to']             = $journey_to;
				$passenger['seat_class']             = $seat_class;
			}
			unset( $passenger );
			wc_update_order_item_meta( $item_id, '_passenger_data_ticket', maybe_serialize( $unserialized_passengers ) );
		}
		if ( ! $booking_exists ) {
			mtrap_insert_data_customer_details_dbtable( $unserialized_passengers );
		}

		// If its modified ticket.
		if ( wc_get_order_item_meta( $item_id, '_ticket_modified', true ) == 1 ) {
			// Update the status for previous order id.
			$previous_order_id     = wc_get_order_item_meta( $item_id, '_previous_order_id', true );
			$previous_order_object = wc_get_order( $previous_order_id );

			if ( $previous_order_object ) {
				$previous_order_object->update_status( 'wc-modified' );

				$previous_order_line_item = wc_get_order_item_meta( $item_id, '_previous_order_booking_id', true );
				$order_note               = sprintf( __( 'Customer has modified this order. Previous order id is %d', 'winger' ), $previous_order_id );

				$order->update_status( 'completed', $order_note );
				wc_update_order_item_meta( $item_id, '_trip_order_status', 'completed' );
				wc_update_order_item_meta( $previous_order_line_item, '_trip_order_status', 'modified' );

				// Log debug message
				error_log( "Sending modified order email from order #{$previous_order_id} to order #{$order_id}" );

				$wpdb->query( $wpdb->prepare( "UPDATE $customer_details_table SET passenger_order_status='modified' WHERE booking_no=$previous_order_line_item" ) );

				// Call the email function
				mtrap_check_and_send_modified_order_email( $previous_order_id, $order_id );

			} else {
				error_log( "Previous order object not found for ID: {$previous_order_id}" );
			}
		} else {
			// Add the order status to the order item meta.
			wc_add_order_item_meta( $item_id, '_trip_order_status', $order_status );
		}
	}
}


/**
 * Disable woocommerce cart permalinks.
 */
add_filter( 'woocommerce_cart_item_permalink', '__return_null' );


/**
 * Total bus price calculations.
 */
function mtrap_price_calculations( $post_id, $mtrap_boarding_station_id, $journey_from, $journey_to, $passenger_types, $mtrap_bus_tax, $mtrap_seat_class_price ) {

	global $wpdb;
	$mtrap_final_price                  = 0;
	$mtrap_total_base_price             = 0;
	$mtrap_array_key_bus_from_added     = 0;
	$mtrap_initial_price                = array();
	$mtrap_array_passenger_types        = array();
	$mtrap_calculated_prices_arr        = array();
	$mtrap_get_bus_stop_pricing_details = $wpdb->get_results(
		"SELECT * FROM {$wpdb->prefix}mtrap_custom_bus_stops WHERE `post_id` = $post_id",
		ARRAY_A
	);

	if ( ! empty( $mtrap_get_bus_stop_pricing_details ) && ! empty( $journey_from ) && ! empty( $journey_to ) && ( ! empty( $passenger_types ) ) ) {

		$mtrap_array_key_bus_from = array_search( $journey_from, array_column( $mtrap_get_bus_stop_pricing_details, 'station_stop' ) );

		$mtrap_array_key_bus_from_added = in_array( $journey_from, array_column( $mtrap_get_bus_stop_pricing_details, 'station_stop' ) ) ? ( $mtrap_array_key_bus_from + 1 ) : 0;

		if ( $mtrap_boarding_station_id === $journey_from ) {
			$mtrap_array_key_bus_from_added = 0;
		}

		$mtrap_array_key_bus_to = array_search( $journey_to, array_column( $mtrap_get_bus_stop_pricing_details, 'station_stop' ) ) + 1;

		if ( $mtrap_array_key_bus_from_added == 1 ) {
			$mtrap_array_key_bus_to = $mtrap_array_key_bus_to - 1;
		}

		if ( $mtrap_array_key_bus_from_added === $mtrap_array_key_bus_to ) {
			$mtrap_get_all_stations = $mtrap_get_bus_stop_pricing_details[ $mtrap_array_key_bus_from_added ];
		} else {
			$mtrap_get_all_stations = array_slice( $mtrap_get_bus_stop_pricing_details, $mtrap_array_key_bus_from_added, $mtrap_array_key_bus_to );
		}

		if ( array_key_exists( '0', $mtrap_get_all_stations ) ) {
			foreach ( $mtrap_get_all_stations as $single_stations ) {
				$mtrap_initial_price[] = maybe_unserialize( $single_stations['price'] );
			}
		} else {
			$mtrap_initial_price[] = maybe_unserialize( $mtrap_get_all_stations['price'] );
		}

		$passenger_types_arr = json_decode( wp_unslash( $passenger_types ) );

		if ( ! empty( $passenger_types_arr ) ) {
			foreach ( $passenger_types_arr as $passenger_type ) {
				$mtrap_array_passenger_types[] = $passenger_type->selectedPassengerType;
			}
		}

		$mtrap_passenger_types_values = array_count_values( $mtrap_array_passenger_types );

		foreach ( $mtrap_passenger_types_values as $key => $multiplier ) {
			if ( isset( $mtrap_initial_price[0][ $key ] ) ) {
				foreach ( $mtrap_initial_price as $price ) {
					$mtrap_calculated_prices_arr[] = $price[ $key ][0] * $multiplier;
				}
			}
		}

		$mtrap_base_price_calculated = 0 !== $mtrap_calculated_prices_arr ? array_sum( $mtrap_calculated_prices_arr ) : 0;
		$mtrap_total_base_price      = $mtrap_base_price_calculated; // final base price after calculation.

		if ( empty( $mtrap_bus_tax ) && empty( $mtrap_seat_class_price ) ) {
			$mtrap_final_price = $mtrap_total_base_price; // final base price after calculation.
		} elseif ( ! empty( $mtrap_bus_tax ) && empty( $mtrap_seat_class_price ) ) {
			$mtrap_final_price  = $mtrap_total_base_price;
			$mtrap_final_price *= ( 1 + $mtrap_bus_tax / 100 ); // final price with tax & without seat class after calculation.
		} elseif ( empty( $mtrap_bus_tax ) && ! empty( $mtrap_seat_class_price ) ) {
			$mtrap_final_price  = $mtrap_total_base_price;
			$mtrap_final_price *= ( 1 + $mtrap_seat_class_price / 100 ); // final price with seat class & without tax after calculation.
		} else {
			$mtrap_total_price_w_tax  = $mtrap_total_base_price;
			$mtrap_total_price_w_tax *= ( 1 + $mtrap_bus_tax / 100 );
			$mtrap_final_price        = $mtrap_total_price_w_tax;
			$mtrap_final_price       *= ( 1 + $mtrap_seat_class_price / 100 ); // final price with tax & seat class after calculation.
		}
	}
	return array(
		'base_price'  => floor( $mtrap_total_base_price ),
		'final_price' => floor( $mtrap_final_price ),
	);
}


add_action( 'wp_ajax_mtrap_delete_order_cancellation_data', 'mtrap_delete_order_cancellation_data' );
add_action( 'wp_ajax_nopriv_mtrap_delete_order_cancellation_data', 'mtrap_delete_order_cancellation_data' );

/**
 * Cancel order for specific user.
 */
function mtrap_delete_order_cancellation_data() {
	global $wpdb;
	check_ajax_referer( 'remove-passenger', 'security' );

	$order_id         = ! empty( $_POST['order_id'] ) ? sanitize_text_field( $_POST['order_id'] ) : '';
	$item_id          = ! empty( $_POST['item_id'] ) ? sanitize_text_field( $_POST['item_id'] ) : '';
	$passenger_name   = ! empty( $_POST['passenger_name'] ) ? sanitize_text_field( $_POST['passenger_name'] ) : '';
	$passenger_email  = ! empty( $_POST['passenger_email'] ) ? sanitize_text_field( $_POST['passenger_email'] ) : '';
	$passenger_phone  = ! empty( $_POST['passenger_phone'] ) ? sanitize_text_field( $_POST['passenger_phone'] ) : '';
	$passenger_gender = ! empty( $_POST['passenger_gender'] ) ? sanitize_text_field( $_POST['passenger_gender'] ) : '';
	$passenger_type   = ! empty( $_POST['passenger_type'] ) ? sanitize_text_field( $_POST['passenger_type'] ) : '';

	if ( $order_id && $item_id && $passenger_name && $passenger_email && $passenger_phone && $passenger_gender && $passenger_type ) {

		$db_customer_details = $wpdb->prefix . 'mtrap_customer_details';

		// Check if the record exists.
		$record = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $db_customer_details WHERE order_id = %s AND passenger_name = %s AND passenger_email = %s AND passenger_phone = %s AND passenger_gender = %s AND passenger_type = %s",
				$order_id,
				$passenger_name,
				$passenger_email,
				$passenger_phone,
				$passenger_gender,
				$passenger_type
			)
		);

		if ( $record ) {
			// Update the order status in custom db table.
			$update_result = $wpdb->update(
				$db_customer_details,
				array( 'passenger_order_status' => 'partial-cancel' ),
				array(
					'order_id'         => $order_id,
					'passenger_name'   => $passenger_name,
					'passenger_email'  => $passenger_email,
					'passenger_phone'  => $passenger_phone,
					'passenger_gender' => $passenger_gender,
					'passenger_type'   => $passenger_type,
				)
			);

			if ( $update_result !== false ) {
				$updated_passengers = array();
				$order              = wc_get_order( $order_id );
				$more_than_48_hours = get_option( 'order_cancel_more_than_48_hours' );
				$within_24_48_hours = get_option( 'order_cancel_within_24_to_48_hours' );
				$within_24_hours    = get_option( 'order_cancel_within_24_hours' );
				$passengers_data    = wc_get_order_item_meta( $item_id, '_passenger_data_ticket', true );
				$journey_date       = wc_get_order_item_meta( $item_id, 'journey_date', true );
				$passenger_details  = maybe_unserialize( $passengers_data );
				$current_time       = current_time( 'timestamp' );
				$departure_time     = strtotime( $journey_date );
				$hours_diff         = ( $departure_time - $current_time ) / 3600;

				foreach ( $passenger_details as $passenger_detail ) {
					if (
						$passenger_detail['passenger_name'] === $passenger_name &&
						$passenger_detail['passenger_email'] === $passenger_email &&
						$passenger_detail['passenger_phone'] === $passenger_phone &&
						$passenger_detail['passenger_gender'] === $passenger_gender &&
						$passenger_detail['passenger_type'] === $passenger_type
					) {
						$updated_passengers[] = array_merge( $passenger_detail, array( 'passenger_order_status' => 'partial-cancel' ) );
					} else {
						$updated_passengers[] = $passenger_detail;
					}
				}

				// Update the order status in order meta value - _passenger_data_ticket.
				wc_update_order_item_meta( $item_id, '_passenger_data_ticket', maybe_serialize( $updated_passengers ) );

				// Update the order status in order meta value - _trip_order_status.
				wc_update_order_item_meta( $item_id, '_trip_order_status', 'partial-cancel' );

				$order_note = sprintf( __( 'Order status changed to Partially Cancelled', 'winger' ) );
				$order->update_status( 'partial-cancel', $order_note );

				// Calculate refund eligibility.
				if ( $hours_diff > 48 ) {
					$refund_amount = $more_than_48_hours;
				} elseif ( $hours_diff > 24 ) {
					$refund_amount = $within_24_48_hours;
				} else {
					$refund_amount = $within_24_hours;
				}

				?>
				<div class="elementor-section elementor-top-section elementor-element elementor-section-full_width elementor-section-height-min-height elementor-section-content-middle elementor-section elementor-section-boxed">
					<div class="elementor-container elementor-column-gap-extended">
						<div class="elementor-row elementor-col-100 elementor-top-column elementor-element elementor-widget-wrap">
							<div class="search-list-alert">
								<?php esc_html_e( 'Journey has been cancelled for this passenger, you are eligible for ' . $refund_amount . '% refund of your individual ticket. Our agency will contact you regarding the further process!', 'winger' ); ?>
								<?php esc_html_e( 'Services fees are non-refundable!', 'winger' ); ?>
							</div>
						</div>
					</div>
				</div>
				<?php
				if( $order_id ){
					// after printing message send email to the customer. 
					$cancel_order_ids = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT * FROM {$wpdb->prefix}mtrap_customer_details
							WHERE order_id = %d
							AND passenger_order_status = %s",
							$order_id,
							'partial-cancel'
						)
					);
					
					if ( ! empty( $cancel_order_ids ) ) {
						foreach ( $cancel_order_ids as $passenger ) {
							$cancellation_names[] = $passenger->passenger_name;
						}
						if ( ! empty( $cancellation_names ) ) {
							$customer_names   = implode( ', ', $cancellation_names );
							mtrap_check_and_send_cancellation_order_email( $order_id, $customer_names );
						}
					}
				}

			} else {
				?>
				<div class="elementor-section elementor-top-section elementor-element elementor-section-full_width elementor-section-height-min-height elementor-section-content-middle elementor-section elementor-section-boxed">
					<div class="elementor-container elementor-column-gap-extended">
						<div class="elementor-row elementor-col-100 elementor-top-column elementor-element elementor-widget-wrap">
							<div class="search-list-alert">
							<?php esc_html_e( 'Failed to update passenger order status, Please try again or contact us!', 'winger' ); ?>
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
					<?php esc_html_e( 'Journey record not found please try again or contact us!', 'winger' ); ?>
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
			<?php esc_html_e( 'Journey record not found please try again or contact us!', 'winger' ); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	die();
}


add_action( 'wp_ajax_set_next_journey_dates', 'set_next_journey_dates' );
add_action( 'wp_ajax_nopriv_set_next_journey_dates', 'set_next_journey_dates' );
/**
 *  AJAX handler to store next valid journey dates in session
**/ 
function set_next_journey_dates() {
	$booking_date = ! empty ( $_POST['booking-date'] ) ? $_POST['booking-date'] : '';
	$return_date  = ! empty ( $_POST['return-date'] ) ? $_POST['return-date'] : '';

	// Unset the existing session.
	WC()->session->__unset( 'mtrap_next_journey_date_booking' );
	WC()->session->__unset( 'mtrap_next_journey_date_return' );

	if ( isset( $booking_date ) || isset( $return_date ) ) {
		// Set a new session.
		WC()->session->set( 'mtrap_next_journey_date_booking', sanitize_text_field( $booking_date ) );
		WC()->session->set( 'mtrap_next_journey_date_return', sanitize_text_field( $return_date ) );
		wp_send_json_success( 'Session dates set.' );
	} else {
		wp_send_json_error( 'Invalid data.' );
	}
}


add_action('template_redirect', 'mtrap_bus_submission_session_set');
/**
 *  AJAX handler to store next valid journey dates in session
**/ 
function mtrap_bus_submission_session_set(){
	if ( ! empty( $_POST['destination_from'] ) && ! empty( $_POST['destination_to'] ) ){
		if( $_POST['is-round-trip'] == false ) {
			WC()->session->__unset( 'mtrap_next_journey_date_booking' );
			WC()->session->__unset( 'mtrap_next_journey_date_return' );
		}
	}
}
