<?php
/**
 * Theme functions to register custom meta box for the products
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


add_filter( 'wpseo_metabox_prio', 'mtrap_lower_yoast_metabox_priority' );
/**
 * Lowers the metabox priority to 'core' for Yoast SEO's metabox.
 *
 * @param string $priority The current priority.
 */
function mtrap_lower_yoast_metabox_priority( $priority ) {
	return 'low';
}


/**
 * Calcuates the modification charges if the date is within range.
 */
function get_modification_charges( $journeydate ) {
	// Fetch the order modification settings values.
	$order_modification_after_48_hours  = get_option( 'order_modification_more_than_48_hours', '' );
	$order_modification_within_48_hours = get_option( 'order_modification_within_48_hours', '' );
	$order_modification_within_24_hours = get_option( 'order_modification_within_24_hours', '' );

	// Get today's date.
	$today_date = gmdate( 'd-m-Y' );

	// Convert the dates to DateTime objects for comparison.
	$today_date_obj   = DateTime::createFromFormat( 'd-m-Y', $today_date );
	$journey_date_obj = DateTime::createFromFormat( 'd-m-Y', $journeydate );

	// Calculate the difference in hours.
	$interval = $today_date_obj->diff( $journey_date_obj );

	$hours_difference = ( $interval->days * 24 ) + $interval->h;

	// Determine the modification charges.
	if ( $hours_difference <= 24 ) {
		return $order_modification_within_24_hours;
	} elseif ( $hours_difference <= 48 ) {
		return $order_modification_within_48_hours;
	} else {
		return $order_modification_after_48_hours;
	}
}


// Register new order statuses.
add_action( 'init', 'mtrap_register_wc_modified_order_status' );
function mtrap_register_wc_modified_order_status() {
	register_post_status(
		'wc-modified',
		array(
			'label'                     => _x( 'Modified', 'Order status', 'woocommerce' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Modified (%s)', 'Modified (%s)', 'woocommerce' ),
		)
	);
	register_post_status(
		'wc-partial-cancel',
		array(
			'label'                     => _x( 'Partially Cancelled', 'Order status', 'woocommerce' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Partially Cancelled (%s)', 'Partially Cancelled (%s)', 'woocommerce' ),
		)
	);
}

// Add new order statuses to WooCommerce order statuses list.
add_filter( 'wc_order_statuses', 'mtrap_add_wc_modified_to_order_statuses' );
function mtrap_add_wc_modified_to_order_statuses( $order_statuses ) {
	$new_order_statuses = array();
	foreach ( $order_statuses as $key => $status ) {
		$new_order_statuses[ $key ] = $status;
		if ( 'wc-processing' === $key ) { // Add custom statuses after 'wc-processing'.
			$new_order_statuses['wc-modified']       = _x( 'Modified', 'Order status', 'woocommerce' );
			$new_order_statuses['wc-partial-cancel'] = _x( 'Partially Cancelled', 'Order status', 'woocommerce' );
		}
	}
	return $new_order_statuses;
}

// Add custom statuses to bulk actions dropdown.
add_filter( 'bulk_actions-edit-shop_order', 'mtrap_add_wc_modified_to_bulk_actions' );
function mtrap_add_wc_modified_to_bulk_actions( $bulk_actions ) {
	$bulk_actions['mark_modified']            = __( 'Change status to Modified', 'woocommerce' );
	$bulk_actions['mark_partially_cancelled'] = __( 'Change status to Partially Cancelled', 'woocommerce' );
	return $bulk_actions;
}

// Handle bulk actions for custom statuses.
add_filter( 'handle_bulk_actions-edit-shop_order', 'mtrap_handle_wc_modified_bulk_action', 10, 3 );
function mtrap_handle_wc_modified_bulk_action( $redirect_to, $action, $post_ids ) {
	if ( $action === 'mark_modified' ) {
		foreach ( $post_ids as $post_id ) {
			$order = wc_get_order( $post_id );
			if ( $order ) {
				$order->update_status( 'wc-modified', 'Order status changed to Modified' );
			}
		}
		$redirect_to = add_query_arg( 'marked_modified', count( $post_ids ), $redirect_to );
	}

	if ( $action === 'mark_partially_cancelled' ) {
		foreach ( $post_ids as $post_id ) {
			$order = wc_get_order( $post_id );
			if ( $order ) {
				$order->update_status( 'wc-partial-cancel', 'Order status changed to Partially Cancelled' );
			}
		}
		$redirect_to = add_query_arg( 'marked_partially_cancelled', count( $post_ids ), $redirect_to );
	}

	return $redirect_to;
}

// Show admin notices after bulk actions.
add_action( 'admin_notices', 'wc_modified_bulk_action_admin_notice' );
function wc_modified_bulk_action_admin_notice() {
	if ( ! empty( $_REQUEST['marked_modified'] ) ) {
		printf( '<div id="message" class="updated fade"><p>' . esc_html__( 'Changed status of %d orders to Modified.', 'woocommerce' ) . '</p></div>', esc_html( $_REQUEST['marked_modified'] ) );
	}
	if ( ! empty( $_REQUEST['marked_partially_cancelled'] ) ) {
		printf( '<div id="message" class="updated fade"><p>' . esc_html__( 'Changed status of %d orders to Partially Cancelled.', 'woocommerce' ) . '</p></div>', esc_html( $_REQUEST['marked_partially_cancelled'] ) );
	}
}

add_action( 'woocommerce_order_details_before_order_table', 'add_custom_notice_for_modified_trip', 10, 1 );
/**
 * If the trip is modified display message - my account order details.
 */
function add_custom_notice_for_modified_trip( $order_id ) {
	global $wpdb;
	$order = wc_get_order( $order_id );

	// Check if the order status is 'wc-modified'
	if ( $order && $order->get_status() === 'modified' ) {
		$item_names         = array();
		$item_ids           = array();
		$items              = $order->get_items();
		$final_id           = $order->get_id();
		$modified_order_ids = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT woi.order_id 
			FROM {$wpdb->prefix}woocommerce_order_itemmeta woi_meta 
			JOIN {$wpdb->prefix}woocommerce_order_items woi 
			ON woi_meta.order_item_id = woi.order_item_id 
			WHERE woi_meta.meta_key = '_previous_order_id' 
			AND woi_meta.meta_value = %d",
				$final_id
			),
			ARRAY_A
		);

		foreach ( $modified_order_ids as $item_id ) {
			$order_item_with_link = "<a href='" . esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . 'view-order/' . $item_id['order_id'] ) . "'>" . esc_html( $item_id['order_id'] ) . '</a>';
			$item_ids[]           = $order_item_with_link;
		}
		foreach ( $items as $item_id => $item ) {
			$trip_order_status = wc_get_order_item_meta( $item_id, '_trip_order_status', true );
			// Check if the _trip_order_status meta is 'modified'.
			if ( $trip_order_status === 'modified' ) {
				$item_names[] = $item->get_name();
			}
		}
		if ( ! empty( $item_names ) && ! empty( $item_ids ) ) {
			$trip_names          = implode( ', ', $item_names );
			$final_order_id_list = implode( ', ', $item_ids );
			echo '<div class="trip-notice"><span style="color: red;">Note</span>: <strong>' . esc_html( $trip_names ) . '</strong> journey was modified by order id(s) { ' . $final_order_id_list . ' }</div>';
		}
	}

	if ( $order && $order->get_status() === 'partial-cancel' ) {
		$cancellation_names    = array();
		$product_ids           = array();
		$journey_dates         = array();
		$cancellation_final_id = $order->get_id();
		$current_time          = current_time( 'timestamp' );
		$more_than_48_hours    = get_option( 'order_cancel_more_than_48_hours' );
		$within_24_48_hours    = get_option( 'order_cancel_within_24_to_48_hours' );
		$within_24_hours       = get_option( 'order_cancel_within_24_hours' );

		$cancel_order_ids = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}mtrap_customer_details
				WHERE order_id = %d
				AND passenger_order_status = %s",
				$cancellation_final_id,
				'partial-cancel'
			)
		);

		foreach ( $cancel_order_ids as $passenger ) {
			$hours_diff           = ( strtotime( $passenger->journey_date ) - $current_time ) / 3600;
			$cancellation_names[] = $passenger->passenger_name;
			$product_ids[]        = $passenger->product_id;

			// Calculate refund eligibility.
			if ( $hours_diff > 48 ) {
				$journey_dates[] = $more_than_48_hours;
			} elseif ( $hours_diff > 24 ) {
				$journey_dates[] = $within_24_48_hours;
			} else {
				$journey_dates[] = $within_24_hours;
			}
		}

		if ( ! empty( $cancellation_names ) ) {
			$customer_refunds = implode( ', ', $journey_dates );
			$customer_names   = implode( ', ', $cancellation_names );
			$product_name     = array();
			echo '<pre>';
			print_r( $customer_refunds );
			die;
			foreach ( $product_ids as $trip ) {
				$product = wc_get_product( $trip );
				if ( $product ) {
					$product_name[] = $product->get_title();
				}
			}

			$product_name_str = implode( ', ', $product_name );

			echo '<div class="trip-notice" ><span style="color: red;">Note</span>: in booking(s) <strong>' . esc_html( $product_name_str ) . '</strong> these customer(s) { <strong>' . esc_html( $customer_names ) . '</strong> } has cancelled the journey and is eligible for <strong>' . esc_html( $customer_refunds ) . '% </strong>refund.</div>';
		}
	}
}


/**
 * Custom email template for modification email.
 */
function wc_send_custom_order_email( $recipient, $subject, $heading, $message, $order ) {
	// Load WooCommerce mailer.
	$mailer   = WC()->mailer();
	$order_id = $order->get_id();
	// Create email headers.
	$headers = array( 'Content-Type: text/html; charset=UTF-8' );

	// Use wp_upload_dir to get the correct file path.
	$attachment_path = generate_pdf_for_order( $order_id, 'base' );
	$attachments     = array();
	if ( $attachment_path ) {
		foreach ( $attachment_path as $attachment ) {
			if ( file_exists( $attachment ) ) {
				$attachments[] = $attachment;
			} else {
				error_log( 'Attachment file not found: ' . $attachment );
			}
		}
	}

	// Email template.
	ob_start();
	wc_get_template( 'emails/email-header.php', array( 'email_heading' => $heading ) );
	echo wpautop( $message );
	wc_get_template( 'emails/email-footer.php' );
	$body = ob_get_clean();

	// Send the email.
	$mailer->send( $recipient, $subject, $body, $headers, $attachments );
}

/**
 * Custom email template for cancellation email.
 */
function wc_send_custom_order_email_cancellation( $recipient, $subject, $heading, $message, $order ) {
	// Load WooCommerce mailer.
	$mailer   = WC()->mailer();
	$order_id = $order->get_id();
	// Create email headers.
	$headers = array( 'Content-Type: text/html; charset=UTF-8' );

	// Email template.
	ob_start();
	wc_get_template( 'emails/email-header.php', array( 'email_heading' => $heading ) );
	echo wpautop( $message );
	wc_get_template( 'emails/email-footer.php' );
	$body = ob_get_clean();

	// Send the email.
	$mailer->send( $recipient, $subject, $body, $headers, $attachments );
}


/**
 * Send custom email once the order has been modified.
 */
function mtrap_check_and_send_modified_order_email( $old_order_id, $new_order_id ) {
	// Get the order object.
	$order = wc_get_order( $new_order_id );

	// Check if the order meta '_ticket_modified' exists and is set to 1.
	if ( $order && '1' === $order->get_meta( '_ticket_modified' ) ) {
		// Set email content.
		$subject = __( 'Your Order Has Been Modified', 'woocommerce' );
		$heading = __( 'Order Modified', 'woocommerce' );
		$message = sprintf( __( 'Your order #%1$d has been modified and here is new order id #%2$d. Please visit the site and review the details.', 'woocommerce' ), $old_order_id, $new_order_id );

		// Send email to customer.
		$customer_email = $order->get_billing_email();
		wc_send_custom_order_email( $customer_email, $subject, $heading, $message, $order );

		// Send email to admin.
		$admin_email = get_option( 'admin_email' );
		wc_send_custom_order_email( $admin_email, $subject, $heading, $message, $order );

		// Optionally, update the order meta to indicate the email was sent.
		$order->update_meta_data( '_ticket_modified_email_sent', 1 );
		$order->save();
	} else {
		error_log( 'mtrap_check_and_send_modified_order_email: _ticket_modified not set or order not found' );
	}
}


/**
 * Send custom email once the order has been cancelled.
 */
function mtrap_check_and_send_cancellation_order_email( $order_id, $customer_names ) {
	// Get the order object.
	$order = wc_get_order( $order_id );
   
	// Set email content.
	$subject = __( 'Your Order Has Been Cancelled', 'woocommerce' );
	$subject_admin = __( 'A customer has cancelled the journey', 'woocommerce' );
	$heading = __( 'Order Cancelled', 'woocommerce' );
	$message = sprintf( __( 'Customer(s) %1$s has cancelled a journey from order #%2$d. Please visit the site and review the details.', 'woocommerce' ), $customer_names, $order_id );

	// Send email to customer.
	$customer_email = $order->get_billing_email();
	wc_send_custom_order_email_cancellation( $customer_email, $subject, $heading, $message, $order );

	// Send email to admin.
	$admin_email = get_option( 'admin_email' );
	wc_send_custom_order_email_cancellation( $admin_email, $subject_admin, $heading, $message, $order );
    
	// Optionally, update the order meta to indicate the email was sent.
	$order->update_meta_data( '_ticket_cancelled_email_sent', 1 );
	$order->save();

}


/**
 * Send custom email once the bus status is set to be cancelled.
 */
function mtrap_cancelled_bus_status_email( $post_id ) {
    // Get today's date in 'Y-m-d' format for accurate comparison
    $current_date = current_time( 'Y-m-d' );

    // Retrieve orders created today
    $orders = wc_get_orders( array(
        'limit'        => -1,
        'type'         => 'shop_order',
        'date_created' => $current_date
    ) );

    // Retrieve the email content for cancelled status from options
    $email_content = get_option( 'bus_status_email_cancelled', '' );
    $heading = __( 'Journey Cancelled Notification', 'winger' );

    foreach ( $orders as $order ) {
        // Iterate through each item in the order
        foreach ( $order->get_items() as $item ) {
            // Check if order item meta 'journey_date' matches today's date
            $journey_date   = date('Y-m-d', strtotime($item->get_meta( 'journey_date' )));
            $journey_from   = $item->get_meta( 'journey_from' );
            $journey_to     = $item->get_meta( 'journey_to' );
            $departure_time = $item->get_meta( 'departure_time' );

            // Format journey date for the subject line
            $formatted_journey_date = date_i18n( 'jS F Y', strtotime( $journey_date ) );
            $subject = sprintf( __( 'Notice: Your Journey on %s is Cancelled', 'winger' ), $formatted_journey_date );

            // Merge email content
            $email_content_merged = sprintf(
                __( 'Your journey from %s to %s on %s at %s has been cancelled. %s', 'winger' ),
                $journey_from,
                $journey_to,
                $formatted_journey_date,
                $departure_time,
                "\n\n" . $email_content  // Adds two line breaks before $email_content
            );

            if ( $journey_date === $current_date ) {
                // Check if the item is related to the given post_id
                if ( $item->get_product_id() == $post_id ) {
                    // Get customer email and send only to them
                    $customer_email = $order->get_billing_email();
                    if ( $customer_email ) {
                        wc_send_custom_order_email_cancellation( $customer_email, $subject, $heading, $email_content_merged, $order );
                    }
                }
            }
        }
    }
}

/**
 * Send custom email once the bus status is set to be delayed.
 */
function mtrap_delayed_bus_status_email( $post_id ) {
    // Get today's date in 'Y-m-d' format for accurate comparison
    $current_date = current_time( 'Y-m-d' );

    // Retrieve orders created today
    $orders = wc_get_orders( array(
        'limit'        => -1,
        'type'         => 'shop_order',
        'date_created' => $current_date
    ) );

    // Retrieve the email content for delayed status from options
    $email_content = get_option( 'bus_status_email_delayed', '' );
    $heading = __( 'Journey Delayed Notification', 'winger' );

    foreach ( $orders as $order ) {
        // Iterate through each item in the order
        foreach ( $order->get_items() as $item ) {
            // Check if order item meta 'journey_date' matches today's date
            $journey_date   = date('Y-m-d', strtotime($item->get_meta( 'journey_date' )));
            $journey_from   = $item->get_meta( 'journey_from' );
            $journey_to     = $item->get_meta( 'journey_to' );
            $departure_time = $item->get_meta( 'departure_time' );

            // Format journey date for the subject line
            $formatted_journey_date = date_i18n( 'jS F Y', strtotime( $journey_date ) );
            $subject = sprintf( __( 'Notice: Your Journey on %s is Delayed', 'winger' ), $formatted_journey_date );

            // Merge email content
            $email_content_merged = sprintf(
                __( 'Your journey from %s to %s on %s at %s has been delayed. %s', 'winger' ),
                $journey_from,
                $journey_to,
                $formatted_journey_date,
                $departure_time,
                "\n\n" . $email_content  // Adds two line breaks before $email_content
            );

            if ( $journey_date === $current_date ) {
                // Check if the item is related to the given post_id
                if ( $item->get_product_id() == $post_id ) {
                    // Get customer email and send only to them
                    $customer_email = $order->get_billing_email();
                    if ( $customer_email ) {
                        wc_send_custom_order_email_cancellation( $customer_email, $subject, $heading, $email_content_merged, $order );
                    }
                }
            }
        }
    }
}




add_action( 'woocommerce_admin_order_item_headers', 'add_custom_notice_for_modified_trip_edit_order', 10, 1 );
/**
 * If the trip is modified display message - my account order details.
 */
function add_custom_notice_for_modified_trip_edit_order( $order_id ) {
	global $wpdb;
	$order = wc_get_order( $order_id );
	// Check if the order status is 'wc-modified'.
	if ( $order && $order->get_status() === 'modified' ) {
		$item_names         = array();
		$item_ids           = array();
		$items              = $order->get_items();
		$final_id           = $order->get_id();
		$modified_order_ids = $wpdb->get_results( "SELECT DISTINCT woi.order_id FROM {$wpdb->prefix}woocommerce_order_itemmeta woi_meta JOIN {$wpdb->prefix}woocommerce_order_items woi ON woi_meta.order_item_id = woi.order_item_id WHERE woi_meta.meta_key = '_previous_order_id' AND woi_meta.meta_value =$final_id;", ARRAY_A );

		foreach ( $modified_order_ids as $item_id ) {
			$order_item_with_link = "<a href='" . get_admin_url() . 'post.php?post=' . $item_id['order_id'] . "&action=edit'>" . $item_id['order_id'] . '</a>';
			$item_ids[]           = $order_item_with_link;
		}

		foreach ( $items as $item_id => $item ) {
			$trip_order_status = wc_get_order_item_meta( $item_id, '_trip_order_status', true );
			// Check if the _trip_order_status meta is 'modified'.
			if ( $trip_order_status === 'modified' ) {
				$item_names[] = $item['name'];
			}
		}
		if ( ! empty( $item_names ) && ! empty( $item_ids ) ) {
			$trip_names          = implode( ', ', $item_names );
			$final_order_id_list = implode( ', ', $item_ids );
			echo '<div class="trip-notice" style="margin:10px 10px 10px 10px;"><span style="color: red;">Note</span>: <strong>' . $trip_names . '</strong> journey was modified by order id(s) { ' . $final_order_id_list . ' }</div>';
		}
	}

	if ( $order && $order->get_status() === 'partial-cancel' ) {
		$cancellation_names    = array();
		$product_ids           = array();
		$journey_dates         = array();
		$cancellation_final_id = $order->get_id();
		$current_time          = current_time( 'timestamp' );
		$more_than_48_hours    = get_option( 'order_cancel_more_than_48_hours' );
		$within_24_48_hours    = get_option( 'order_cancel_within_24_to_48_hours' );
		$within_24_hours       = get_option( 'order_cancel_within_24_hours' );

		$cancel_order_ids = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}mtrap_customer_details
				WHERE order_id = %d
				AND passenger_order_status = %s",
				$cancellation_final_id,
				'partial-cancel'
			)
		);

		foreach ( $cancel_order_ids as $passenger ) {
			$hours_diff           = ( strtotime( $passenger->journey_date ) - $current_time ) / 3600;
			$cancellation_names[] = $passenger->passenger_name;
			$product_ids[]        = $passenger->product_id;

			// Calculate refund eligibility.
			if ( $hours_diff > 48 ) {
				$journey_dates[] = $more_than_48_hours;
			} elseif ( $hours_diff > 24 ) {
				$journey_dates[] = $within_24_48_hours;
			} else {
				$journey_dates[] = $within_24_hours;
			}
		}

		if ( ! empty( $cancellation_names ) ) {
			$customer_refunds = implode( ', ', $journey_dates );
			$customer_names   = implode( ', ', $cancellation_names );
			$product_name     = array();

			foreach ( $product_ids as $trip ) {
				$product = wc_get_product( $trip );
				if ( $product ) {
					$product_name[] = $product->get_title();
				}
			}

			$product_name_str = implode( ', ', $product_name );

			echo '<div class="trip-notice"><span style="color: red;">Note</span>: in booking(s) <strong>' . esc_html( $product_name_str ) . '</strong> these customer(s) { <strong>' . esc_html( $customer_names ) . '</strong> } have cancelled the journey and are eligible for a <strong>' . esc_html( $customer_refunds ) . '%</strong> refund.</div>';
		}
	}
}


// Clear WooCommerce cart on user login
function mtrap_clear_cart_on_login() {
	WC()->cart->empty_cart();
}
add_action( 'wp_login', 'mtrap_clear_cart_on_login', 10, 2 );

// Clear WooCommerce cart on user logout
function mtrap_clear_cart_on_logout() {
	WC()->cart->empty_cart();
}
add_action( 'wp_logout', 'mtrap_clear_cart_on_logout' );


add_action( 'woocommerce_add_to_cart', 'mtrap_start_cart_timer_after_product_added_to_cart', 10, 6 );
/**
 * When product is added to cart create session.
 */
function mtrap_start_cart_timer_after_product_added_to_cart() {
	WC()->session->set( 'transportation_added_to_cart_timestamp', time() );
}


add_action( 'template_redirect', 'mtrap_clear_cart_destroy_session_if_threshold_meet' );
/**
 * on template redirect check if cart item is added more than 15 mins, if yes destroy the session and empty cart.
 */
function mtrap_clear_cart_destroy_session_if_threshold_meet() {
	$mtrap_current_time           = time();
	$cart_timestamp_session = WC()->session->get( 'transportation_added_to_cart_timestamp' );
	$session_threshold              = 15 * 60;

	if ( $cart_timestamp_session && ( $mtrap_current_time - $cart_timestamp_session ) > $session_threshold ) {
		WC()->cart->empty_cart();
		WC()->session->__unset( 'transportation_added_to_cart_timestamp' );
	}
}


add_filter('cron_schedules', 'mtrap_custom_daily_product_status_reset_cron_schedules');
/**
 * Schedule the cron to run daily.
 */
function mtrap_custom_daily_product_status_reset_cron_schedules($schedules) {
    $schedules['daily_1159pm'] = array(
        'interval' => 24 * 60 * 60, // 24 hours in seconds
        'display'  => esc_html__('Every 24 hours at 11:59 PM'),
    );
    return $schedules;
}


add_action('wp', 'mtrap_schedule_nightly_product_status_update');
/**
 * Cron is set for the night 11:59 PM.
 */
function mtrap_schedule_nightly_product_status_update() {
    // Check if the event is not already scheduled
    if (!wp_next_scheduled('nightly_update_product_status')) {
        // Schedule the event for 11:59 PM today
        $timestamp = strtotime('tomorrow 23:59:00');
        wp_schedule_event($timestamp, 'daily_1159pm', 'nightly_update_product_status');
    }
}


add_action('nightly_update_product_status', 'mtrap_update_cancelled_products_status');
/**
 * When cron runs, check every product and if any product status is cancelled, update it. 
 */
function mtrap_update_cancelled_products_status() {
    // Query all WooCommerce products
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1, // Retrieve all products
        'post_status'    => 'publish',
    );
    $products = get_posts($args);

    // Loop through each product
    foreach ($products as $product) {
        $product_id = $product->ID;

        // Get the meta value of mtrap_bus_status
        $bus_status = get_post_meta($product_id, 'mtrap_bus_status', true);

        // Check if the meta value exists and is "Cancelled"
        if ($bus_status === 'Cancelled') {
            // Update the status to "On Time"
            update_post_meta($product_id, 'mtrap_bus_status', 'On Time');
        }
    }
}
