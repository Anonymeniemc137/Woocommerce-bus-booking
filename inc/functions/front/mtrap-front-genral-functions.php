<?php
/**
 * General Theme functions to make the frontend user friendly and smooth.
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


add_filter( 'woocommerce_account_menu_items', 'mtrap_remove_my_account_endpoints' );
/**
 * Remove specific endpoints in the WooCommerce My Account screen.
 *
 * @param object $menu_links string.
 */
function mtrap_remove_my_account_endpoints( $menu_links ) {
	unset( $menu_links['downloads'] );
	unset( $menu_links['dashboard'] );
	return $menu_links;
}


add_action( 'save_post', 'mtrap_set_initial_product_price' );
/**
 * Update post meta & set regular price so product will be able to add to cart.
 *
 * @param object $post_id int.
 */
function mtrap_set_initial_product_price( $post_id ) {
	if ( 'product' === get_post_type() ) {
		update_post_meta( $post_id, '_regular_price', '1' );
		update_post_meta( $post_id, '_price', '1' );
		add_filter( 'woocommerce_is_purchasable', '__return_TRUE' );
	}
}


add_filter( 'woocommerce_return_to_shop_redirect', 'mtrap_return_to_shop_url' );
/**
 * Change return to shop url.
 **/
function mtrap_return_to_shop_url() {
	return home_url() . '/transportation-listing';
}


add_filter( 'woocommerce_add_cart_item_data', 'mtrap_split_product_individual_cart_items', 10, 2 );
/**
 * Remove quantity from woocommercre cart items.
 **/
function mtrap_split_product_individual_cart_items( $cart_item_data, $product_id ) {
	$unique_cart_item_key         = uniqid();
	$cart_item_data['unique_key'] = $unique_cart_item_key;
	return $cart_item_data;
}


/**
 * Products sold individually so user can add multiple products.
 */
add_filter( 'woocommerce_is_sold_individually', '__return_true' );


/**
 * Remove cart items restore notice ( Product removed, undo?).
 */
add_filter( 'woocommerce_cart_item_removed_notice_type', '__return_null' );


add_filter( 'woocommerce_my_account_get_addresses', 'mtrap_my_account_get_addresses', 10, 2 );
/**
 * Remove shipping address functionality from my account.
 **/
function mtrap_my_account_get_addresses( $adresses, $customer_id ) {
	if ( isset( $adresses['shipping'] ) ) {
		unset( $adresses['shipping'] );
	}
	return $adresses;
}


add_filter( 'woocommerce_billing_fields', 'remove_account_billing_phone_and_email_fields', 20, 1 );
/**
 * Remove some billing fields.
 **/
function remove_account_billing_phone_and_email_fields( $billing_fields ) {
	unset( $billing_fields['billing_company'] );
	unset( $billing_fields['billing_address_2'] );
	return $billing_fields;
}


add_filter( 'woocommerce_general_settings', 'general_settings_shop_phone' );
/**
 * Add custom store phone number setting in woocommerce settings.
 **/
function general_settings_shop_phone( $settings ) {
	$key = 0;

	foreach ( $settings as $values ) {
		$new_settings[ $key ] = $values;
		++$key;

		// Inserting array just after the post code in "Store Address" section
		if ( $values['id'] == 'woocommerce_store_postcode' ) {
			$new_settings[ $key ] = array(
				'title'    => __( 'Phone Number' ),
				'desc'     => __( 'Optional phone number of your business office' ),
				'id'       => 'woocommerce_store_phone', // <= The field ID (important)
				'default'  => '',
				'type'     => 'text',
				'desc_tip' => true, // or false
			);
			++$key;
		}
	}
	return $new_settings;
}


add_filter( 'woocommerce_display_item_meta', 'mtrap_modified_woocommerce_display_item_meta', 10, 3 );
/**
 * update meta keys format on checkout.
 */
function mtrap_modified_woocommerce_display_item_meta( $html, $item, $args = array() ) {
	$html = '';
	foreach ( $item->get_formatted_meta_data() as $meta ) {
		$display_value     = wp_strip_all_tags( $meta->display_value );
		$display_clean_key = sanitize_title_with_dashes( $meta->display_key );
		$value             = $args['autop'] ? wp_kses_post( $display_value ) : wp_kses_post( make_clickable( trim( $display_value ) ) );
		$html             .= '<li class="' . wp_strip_all_tags( $display_clean_key ) . '">' .
			'<span class="wc-item-meta-label">' . str_replace( '_', ' ', ucfirst( $meta->display_key ) ) . ': </span>
			<strong>' . $value . '</strong></li>';
	}
	return $html;
}

/**
 * Get seat counts for the bus for specific dayz.
 */
function mtrap_stock_calculations($journey_date, $journey_from, $journey_to, $product_id) {
    global $wpdb;
    
    $counts = array();
    $final_bus_route_array = array();
    $final_occupied_seats_array = array();
    $seat_count_per_station = array();
    $final_seat_class = array();

    // Get the seat counts for the bus
    $total_bus_seats = get_post_meta($product_id, 'mtrap_seat_stock', true);

    // Return if no seats are available
    if (empty($total_bus_seats)) {
        return 0;
    }

    // Get bus stops and merge them into the final bus route array
    $mtrap_bus_starting_point = array('station_stop' => get_post_meta($product_id, 'mtrap_bus_stops', true));
    $mtrap_get_bus_stops_order = $wpdb->get_results("SELECT station_stop FROM {$wpdb->prefix}mtrap_custom_bus_stops WHERE post_id = $product_id", ARRAY_A);
    $mtrap_merged_stops = !empty($mtrap_bus_starting_point) && !empty($mtrap_get_bus_stops_order)
        ? array_merge(array($mtrap_bus_starting_point), $mtrap_get_bus_stops_order)
        : array();

    // Populate final bus route array
    foreach ($mtrap_merged_stops as $stops) {
        $final_bus_route_array[] = $stops['station_stop'];
    }
    
    // Initialize seat count per station
    if (!empty($total_bus_seats)) {
        foreach ($total_bus_seats as $seat_class => $count) {
            foreach ($final_bus_route_array as $index => $station) {
                $seat_count_per_station[$seat_class][$index] = $count;
            }
        }
    }
    
    // Get booking data for each seat class and populate final_occupied_seats_array
    foreach ($total_bus_seats as $key => $seats_arr) {
        $dateObject = DateTime::createFromFormat('d-m-Y', $journey_date);
        $filtered_date = $dateObject->format('Y-m-d');
        $filtered_key = ucwords(str_replace('-', ' ', $key));

        $occupied_seats = $wpdb->get_results(
            "SELECT seat_class, journey_from, journey_to, COUNT(*) as seat_count 
            FROM {$wpdb->prefix}mtrap_customer_details 
            WHERE journey_date = '$filtered_date' 
            AND product_id = '$product_id' 
            AND seat_class = '$filtered_key' 
            AND (passenger_order_status = 'processing' OR passenger_order_status = 'completed')
            GROUP BY journey_from, journey_to, seat_class",
            ARRAY_A
        );

        if (!empty($occupied_seats)) {
            foreach ($occupied_seats as $class) {
                $final_occupied_seats_array[] = array($key => $class);
            }
        }
    }   
    
    // Loop through occupied seats to adjust seat counts
    foreach ($final_occupied_seats_array as $item) {
        foreach ($item as $key => $value) {
            $startIndex = array_search($value['journey_from'], $final_bus_route_array);
            $endIndex = array_search($value['journey_to'], $final_bus_route_array);

            // Get the number of seats booked
            $seatCount = isset($value['seat_count']) ? $value['seat_count'] : 0;

            if ($startIndex !== false && $endIndex !== false) {
                // Reduce only for the booked range and by the correct seat count
                for ($i = $startIndex; $i <= $endIndex; $i++) {
                    if (!isset($counts[$key][$i])) {
                        $counts[$key][$i] = 0;
                    }
                    if($i != $endIndex){
                        // Add the booked seat count to the total for this segment
                        $counts[$key][$i] += $seatCount;
                    }elseif( $endIndex == count( $final_bus_route_array ) - 1 ){
                        // Add the booked seat count to the total for this segment
                        $counts[$key][$i] += $seatCount;
                    }
                }
				// if( $endIndex == count( $final_bus_route_array ) - 1  ){
				// 	$counts[$key][$endIndex] -= 1;
				// }
            }
        }
    }
    
    // Adjust available seats based on the current journey
    foreach ($total_bus_seats as $key => $count) {
        // Get start and end indices for the current journey
        $start = array_search($journey_from, $final_bus_route_array);
        $end = array_search($journey_to, $final_bus_route_array);

        // Only reduce seats for the specific journey range
        for ($i = $start; $i <= $end; $i++) {
            if (isset($counts[$key][$i])) {
               $seat_count_per_station[$key][$i] -= $counts[$key][$i];
            }
        }
    }
 
    // Loop through each class of seat counts
    foreach ($seat_count_per_station as $class => &$seats) {
        $minValueFound = null;
    
        // Loop through each seat count in the current class
        foreach ($seats as $index => &$seat) {
            // Check if the current seat count is less than the previous one or if it's the first iteration
            if ($minValueFound === null || $seat < $minValueFound) {
                $minValueFound = $seat;
    
                // Update only if the current value is less than the previous value
                if ($index > 0) {
                    for ($i = $index - 1; $i >= 0; $i--) {
                        if ($seats[$i] > $minValueFound) {
                            $seats[$i] = $minValueFound;
                        } else {
                            break; // Stop if values are already less than or equal
                        }
                    }
                }
            }
        }
    }

    // Adjust total bus seats based on all seat class updates
    foreach ($seat_count_per_station as $seat_class => $counts) {
        $startIndex = array_search($journey_from, $final_bus_route_array);
        $final_seat_class[$seat_class] = $counts[$startIndex];
    }
   
    return $final_seat_class;
}

/**
 * Remove duplicate array.
 */
function multidimensional_array_diff_by_column( $array1, $array2, $column ) {
	$columnValues1 = array_column( $array1, $column );
	$columnValues2 = array_column( $array2, $column );

	$diffValues = array_diff( $columnValues1, $columnValues2 );

	$diff = array();
	foreach ( $array1 as $item ) {
		if ( in_array( $item[ $column ], $diffValues ) ) {
			$diff[] = $item;
		}
	}

	return $diff;
}


add_action( 'woocommerce_after_checkout_validation', 'mtrap_custom_checkout_validation', 10, 2 );
/**
 * On checkout, check if stock is available.
 */
function mtrap_custom_checkout_validation( $data, $errors ) {
	$cart = WC()->cart;
	if ( ! empty( $cart ) ) {
		$product_name_array = array();
		$cart_url           = wc_get_cart_url(); 
		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			$product_id   = $cart_item['product_id'];
			$product_name = $cart_item['data']->get_name();
			$bus_data     = WC()->session->get( 'bus_details_' . $cart_item_key );

			if ( isset( $bus_data['passenger'], $bus_data['journey_date'], $bus_data['seat_class'] ) ) {
				$final_stock_count = mtrap_stock_calculations($bus_data['journey_date'], $bus_data['_journey_from_id'], $bus_data['_journey_to_id'], $product_id);
				if ( is_array( $final_stock_count ) ) {
					$seat_class_key = str_replace( ' ', '-', strtolower( $bus_data['seat_class'] ) );
				
					if (array_key_exists($seat_class_key, $final_stock_count)) {
                        if ( $bus_data['passenger'] > $final_stock_count[ $seat_class_key ] ) {
    						$product_name_array[] = $product_name;
    					}
                    }
				} elseif ( $bus_data['passenger'] > $final_stock_count ) {
						$product_name_array[] = $product_name;
				}
			} else {
				$errors->add( 'custom_error', sprintf( __( 'Your cart has some invalid items, please return to the ( <a href="%2$s"> Cart </a> ) remove them and then procceed to checkout.' ), $cart_url ) );
			}
		}
		
		if ( ! empty( $product_name_array ) ) {
			$errors->add( 'custom_error', sprintf( __( 'Sorry, the bus you have selected ( %1$s ) no longer has selected seats available please return to ( <a href="%2$s"> Cart </a> ) remove the item from cart & try again!', 'winger' ), implode( ', ', $product_name_array ), $cart_url ) );
		}
	}
}



add_filter( 'woocommerce_order_item_name', 'mtrap_remove_product_link_from_order_item_name', 10, 2 );
/**
 * Remove product link from order details page - my account.
 */
function mtrap_remove_product_link_from_order_item_name( $item_name, $item ) {
	// Check if the item is a product
	if ( ! $item->is_type( 'line_item' ) ) {
		return $item_name;
	}

	// Return the item name without the link
	return $item->get_name();
}

add_filter( 'woocommerce_cart_item_remove_link', 'remove_icon_and_add_text', 10, 2 );
/**
 * Update remove product text - cart.
 */
function remove_icon_and_add_text( $string, $cart_item_key ) {
	$string = str_replace( 'class="remove"', '', $string );
	return str_replace( '&times;', 'Delete', $string );
}

remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
add_action( 'woocommerce_review_order_after_cart_contents', 'custom_content_after_shipping' );
/**
 * Add coupon form before checkout total.
 */
function custom_content_after_shipping() {
	?>
	<tr class="order-total">
		<td><?php woocommerce_checkout_coupon_form(); ?></td>
		<td></td>
	</tr>		
	<?php
}



add_action( 'woocommerce_before_cart', 'add_return_to_shop_link' );
/**
 * Add button after procceed to checkout button - cart.
 */
function add_return_to_shop_link() {
	?>
	<p class="return-to-shop" style="margin-bottom:40px;">
		<?php $tranportation_url = site_url() . '/transportation-listing'; ?>
		<a class="button wc-backward" href="<?php echo $tranportation_url; ?>"><?php _e( 'Return to booking!', 'winger' ); ?></a>
	</p>
	<?php
}


add_filter( 'woocommerce_save_account_details_required_fields', 'ts_hide_first_name' );
/**
 * Remove required field from my account - my account.
 */
function ts_hide_first_name( $required_fields ) {
	unset( $required_fields['account_display_name'] );
	return $required_fields;
}

/**
 * Remove order again functionality from order details page - my account.
 */
remove_action( 'woocommerce_order_details_after_order_table', 'woocommerce_order_again_button' );


add_filter( 'woocommerce_account_menu_items', 'custom_my_account_menu_items' );
/**
 * Remove order again functionality from order details page - my account.
 */
function custom_my_account_menu_items( $items ) {
	unset( $items['downloads'] );
	return $items;
}


add_action( 'woocommerce_my_account_my_orders_column_order_action', 'display_order_action_column_content' );
/**
 * Display content for custom column in WooCommerce My Account Orders listing
 *
 * @param array $order Order object.
 */
function display_order_action_column_content( $order ) {
	echo '<a class="button" href="' . home_url( 'transportation-order-modification/' ) . '">' . __( 'Modify', 'winger' ) . '</a>';
}


add_filter( 'woocommerce_my_account_my_orders_actions', 'add_modify_and_cancel_buttons_to_orders', 10, 2 );
/**
 * Add Modify and Cancel buttons conditionally to my accounts page.
 *
 * @param array    $actions The existing order actions.
 * @param WC_Order $order The current order object.
 * @return array Modified order actions.
 */
function add_modify_and_cancel_buttons_to_orders( $actions, $order ) {
	global $wpdb;
	$order_id             = $order->get_id();
	$order_status         = $order->get_status();
	$order_item_quantity = $order->get_item_count();
	$today_date = gmdate( 'Y-m-d' );
	$journey_counter = 1;
	
	// Query to retrieve order details.
	$journey_details_statement = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mtrap_customer_details WHERE order_id = %d", $order_id );
	$get_journey_details = $wpdb->get_results( $journey_details_statement , ARRAY_A );
   
  
   if ( $order_status == 'processing' || $order_status == 'completed' ){
       foreach ( $get_journey_details as $journey_details ){
            if ( ! empty( $journey_details ) ){
               if ( $journey_details['journey_date'] > $today_date ) {
                    
                    // Get journey details. 
                    $booking_no = $journey_details['booking_no'];
                    $journey_from = $journey_details['journey_from'];
                    $journey_to = $journey_details['journey_to'];
                    $passenger_email = $journey_details['passenger_email'];
                    $journey_date = $journey_details['journey_date'];
                    
                    // Get name of the journey. 
                    $from_city = get_term_by( 'id', $journey_from, 'bus-stops' );
                    $to_city = get_term_by( 'id', $journey_to, 'bus-stops' );
                    $from_city_name = $from_city->name;
                    $to_city_name = $to_city->name;
                    
                    if( $order_item_quantity > 1 ){
                        foreach( array( $booking_no ) as $booking ){
                            // Add Modify button.
                			$actions['modify-' . $journey_counter] = array(
                                'url'    => wp_nonce_url(
                                    add_query_arg(
                                        array(
                                            'booking_number' => $booking_no,
                                            'destination_from' => $journey_from,
                                            'destination_to' => $journey_to,
                                            'journeyemail' => $passenger_email,
                                            'journeydate' => gmdate( 'd-m-Y', strtotime( $journey_date ) ),
                                        ),
                                        home_url('transportation-order-modification/')
                                    ),
                                    'woocommerce-order-modification'
                                ),
                                'name'   => __('Modify - ' . $from_city_name . ' to ' . $to_city_name, 'woocommerce'),
                                'action' => 'modify-' . $journey_counter,
                            );
                
                			// Add Cancel button.
                			$actions['cancel-' . $journey_counter ] = array(
                				'url'    => wp_nonce_url(
                                    add_query_arg(
                                        array(
                                            'booking_number' => $booking_no,
                                            'destination_from' => $journey_from,
                                            'destination_to' => $journey_to,
                                            'journeyemail' => $passenger_email,
                                            'journeydate' => gmdate( 'd-m-Y', strtotime( $journey_date ) ),
                                        ),
                                        home_url('cancellation/')
                                    ),
                                    'woocommerce-order-cancellation'
                                ),
                				'name'   => __( 'Cancel - ' . $from_city_name . ' to ' . $to_city_name  , 'woocommerce' ),
                				'action' => 'cancel-' . $journey_counter,
                			);
                			$journey_counter++;
                        }
                    } else {
                        // Add Modify button.
            			$actions['modify'] = array(
            			    'url'    => wp_nonce_url(
                                add_query_arg(
                                        array(
                                            'booking_number' => $booking_no,
                                            'destination_from' => $journey_from,
                                            'destination_to' => $journey_to,
                                            'journeyemail' => $passenger_email,
                                            'journeydate' => gmdate( 'd-m-Y', strtotime( $journey_date ) ),
                                        ),
                                        home_url('transportation-order-modification/')
                                    ),
                                    'woocommerce-order-modification'
                                ),
            				'name'   => __( 'Modify', 'woocommerce' ),
            				'action' => 'modify',
            			);
            
            			// Add Cancel button.
            			$actions['cancel'] = array(
            				'url'    => wp_nonce_url(
                                add_query_arg(
                                        array(
                                            'booking_number' => $booking_no,
                                            'destination_from' => $journey_from,
                                            'destination_to' => $journey_to,
                                            'journeyemail' => $passenger_email,
                                            'journeydate' => gmdate( 'd-m-Y', strtotime( $journey_date ) ),
                                        ),
                                        home_url('cancellation/')
                                    ),
                                    'woocommerce-order-cancellation'
                                ),
            				'name'   => __( 'Cancel', 'woocommerce' ),
            				'action' => 'cancel',
            			);
                    }
               }
            } 
       }
    }
	return $actions;
}


add_action( 'init', 'mtrap_create_customers_table' );
/**
 * Create table on admin init that stores custom data of the customers.
 */
function mtrap_create_customers_table() {
	global $wpdb;
	$table_name      = $wpdb->prefix . 'mtrap_customer_details';
	$charset_collate = $wpdb->get_charset_collate();

	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) !== $table_name ) {
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			booking_no int NOT NULL,
			ticket_no bigint NOT NULL,
			order_id int NOT NULL,
			product_id int NOT NULL,
			journey_from int NOT NULL,
			journey_to int NOT NULL,
			journey_date date NOT NULL,
			updated_date date NOT NULL,
			seat_class varchar(255) NOT NULL,
			passenger_name varchar(255) NOT NULL,
			passenger_email varchar(255) NOT NULL,
			passenger_phone varchar(20) NOT NULL,
			passenger_gender varchar(10) NOT NULL,
			passenger_type varchar(10) NOT NULL,
			passenger_order_status varchar(20) NOT NULL,
			PRIMARY KEY (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}


/**
 * Function to insert data into table.
 */
function mtrap_insert_data_customer_details_dbtable( $data ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mtrap_customer_details';

	foreach ( $data as $row ) {
		$wpdb->insert(
			$table_name,
			array(
				'booking_no'             => $row['booking_no'],
				'ticket_no'              => $row['ticket_no'],
				'order_id'               => $row['order_id'],
				'product_id'             => $row['product_id'],
				'journey_from'           => $row['journey_from'],
				'journey_to'             => $row['journey_to'],
				'journey_date'           => gmdate( 'Y-m-d', strtotime( $row['journey_date'] ) ),
				'updated_date'           => gmdate( 'Y-m-d', strtotime( $row['updated_date'] ) ),
				'passenger_order_status' => $row['passenger_order_status'],
				'seat_class'             => $row['seat_class'],
				'passenger_name'         => $row['passenger_name'],
				'passenger_email'        => $row['passenger_email'],
				'passenger_phone'        => $row['passenger_phone'],
				'passenger_gender'       => $row['passenger_gender'],
				'passenger_type'         => $row['passenger_type'],
			)
		);
	}
}



add_action( 'add_meta_boxes', 'add_custom_meta_box' );

/**
 * Add the custom meta box for creating passenger table.
 */
function add_custom_meta_box() {
	add_meta_box(
		'custom_meta_box',
		__( 'Passenger Details', 'woocommerce' ),
		'display_custom_meta_box',
		'shop_order',
		'normal',
		'high'
	);
}

/**
 * Callback function to display the meta box content.
 */
function display_custom_meta_box( $post ) {
	global $wpdb;

	// Get the order ID.
	$order_id = $post->ID;

	// Get the order items.
	$order       = wc_get_order( $order_id );
	$order_items = $order->get_items();

	// Prepare the table HTML.
	echo '<table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th>' . __( 'Passenger Name', 'woocommerce' ) . '</th>
                    <th>' . __( 'Seat Class', 'woocommerce' ) . '</th>
                    <th>' . __( 'Journey Date', 'woocommerce' ) . '</th>
                    <th>' . __( 'Passenger Phone', 'woocommerce' ) . '</th>
                    <th>' . __( 'Passenger Order Status', 'woocommerce' ) . '</th>
                </tr>
            </thead>
            <tbody>';

	// Loop through each order item
	foreach ( $order_items as $item_id => $item ) {
		// Fetch passenger details from wp_mtrap_customer_details.
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT passenger_name, seat_class, journey_date, passenger_phone, passenger_order_status 
             FROM {$wpdb->prefix}mtrap_customer_details 
             WHERE order_id = %d AND booking_no = %d",
				$order_id,
				$item_id
			)
		);

		// Display each passenger's details in a row.
		if ( $results ) {
			foreach ( $results as $row ) {
				echo '<tr>
                        <td>' . esc_html( $row->passenger_name ) . '</td>
                        <td>' . esc_html( $row->seat_class ) . '</td>
                        <td>' . esc_html( $row->journey_date ) . '</td>
                        <td>' . esc_html( $row->passenger_phone ) . '</td>
                        <td>' . esc_html( $row->passenger_order_status ) . '</td>
                      </tr>';
			}
		} else {
			echo '<tr><td colspan="5">' . __( 'No passenger details found.', 'woocommerce' ) . '</td></tr>';
		}
	}

	echo '</tbody></table>';
}


/**
 * Function to remove woocommerce_formatted_price hook
 */
add_filter( 'formatted_woocommerce_price', function ( $formatted_price, $price, $decimals, $decimal_separator ) {
	return $price;
}, 10, 4 );

