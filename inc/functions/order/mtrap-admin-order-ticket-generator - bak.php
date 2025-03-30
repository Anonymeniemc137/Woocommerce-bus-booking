<?php
/**
 * Generate ticket for the order.
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// autoload dependency.
require_once get_stylesheet_directory() . '/dom-pdf/vendor/autoload.php';
use Dompdf\Dompdf;


add_filter( 'woocommerce_email_attachments', 'custom_attach_pdf_to_email', 10, 3 );
/**
 * Add PDF attachment to WooCommerce email.
 **/
function custom_attach_pdf_to_email( $attachments, $email_id, $order ) {
	$email_ids = array( 'new_order', 'customer_processing_order', 'customer_completed_order' );
	// Check if email is for order confirmation.
	if ( in_array( $email_id, $email_ids ) ) {
		// Get order details.
		$order_id     = $order->get_id();
		$invoice_path = generate_pdf_for_order( $order_id, 'base' );

		// Add PDF as attachment.
		if ( ! empty( $invoice_path ) ) {

			// Merge additional attachments with existing attachments.
			$attachments = array_merge( $attachments, $invoice_path );
		}
	}
	return $attachments;
}


add_action( 'woocommerce_admin_order_data_after_order_details', 'mtrap_display_order_invoice_pdf_in_admin' );
/**
 * Display PDF invoice on order details page.
 */
function mtrap_display_order_invoice_pdf_in_admin( $order ) {
	?>
	<div class="form-field form-field-wide">
		<h4><?php _e( 'Ticket' ); ?></h4>
		<?php
		// Construct the invoice PDF path.
		$invoice_path = WP_CONTENT_DIR . '/uploads/invoice/' . $order->get_id() . '.pdf';

		// Check if the invoice PDF file exists.
		if ( file_exists( $invoice_path ) ) {
			echo '<p><strong>' . __( 'Ticket PDF' ) . ':</strong> <a class="button" href="' . WP_CONTENT_URL . '/uploads/invoice/' . $order->get_id() . '.pdf" target="_blank">View Ticket</a></p>';
		} else {
			echo '<p><strong>' . __( 'Ticket PDF' ) . ':</strong> ' . __( 'Ticket PDF not found.' ) . '</p>';
		}
		?>
	</div>
	<?php
}


add_action( 'woocommerce_order_details_after_order_table', 'mtrap_display_order_invoice_pdf_in_my_account', 10, 1 );
/**
 * Display PDF invoice on order details page - my account.
 */
function mtrap_display_order_invoice_pdf_in_my_account( $order ) {
	// Construct the invoice PDF path.
	$invoice_path = WP_CONTENT_DIR . '/uploads/invoice/' . $order->get_id() . '.pdf';

	// Check if the invoice PDF file exists.
	if ( file_exists( $invoice_path ) ) {
		echo '<h2>' . __( 'Ticket', 'winger' ) . '</h2>';
		echo '<p style="margin-top:30px;"><strong>' . __( 'Order Ticket' ) . ':</strong> <a style="margin: -20px 0px 0px 12px;" href="' . WP_CONTENT_URL . '/uploads/invoice/' . $order->get_id() . '.pdf" download target="_blank">Download</a></p>';
	} else {
		echo '<h2>' . __( 'Ticket', 'winger' ) . '</h2>';
		echo '<p style="margin-top:30px;">' . __( 'Ticket PDF not found.', 'winger' ) . '</p>';
	}
}


/**
 * Generated PDF html.
 */
function generate_pdf_for_order( $order_id, $path ) {
	if ( ! $order_id ) {
		return;
	}

	// initiate and use the dompdf class.
	$dompdf = new Dompdf();
	$dompdf->set_option( 'isRemoteEnabled', true );
    $dompdf->set_option( 'isHtml5ParserEnabled', true );
    $dompdf->set_option( 'enable_html5_parser', true );

	// Get order.
	$order         = wc_get_order( $order_id );
	$items         = $order->get_items();
	$aminities_arr = array();
	$tickets       = array();
    $html_data     = '';
	// Loop through order line items.
	foreach ( $items  as $item ) {

		// Loop through order item custom meta data.
		$formatted_meta_data = $item->get_formatted_meta_data( '', true );
		// Loop through order item custom meta data.
		foreach ( $formatted_meta_data as $meta_id => $meta_data ) {
			// Targettting specific meta data (meta keys).
			if ( $meta_data->key == '_passenger_data_ticket' ) {

				$product_id                   = $item->get_product_id();
				$get_coach_type_terms         = get_post_meta( $product_id, 'mtrap_bus_coach_type', true );
				$coach_type_term_arr          = get_term_by( 'id', $get_coach_type_terms, 'bus-types' );
				$coach_type_term              = ! empty( $coach_type_term_arr->name ) ? $coach_type_term_arr->name : '';
				$aminities_terms              = wp_get_post_terms( $product_id, 'aminities' );
				$pickup_point                 = ! empty( $item['_journey_from_id'] ) ? $item['_journey_from_id'] : ' - ';
				$pickup_point_arr             = get_term_by( 'id', $pickup_point, 'bus-stops' );
				$pickup_point_name            = ! empty( $pickup_point_arr->name ) ? $pickup_point_arr->name : '';
				$drop_point                   = ! empty( $item['_journey_to_id'] ) ? $item['_journey_to_id'] : ' - ';
				$drop_point_arr               = get_term_by( 'id', $drop_point, 'bus-stops' );
				$drop_point_name              = $drop_point_arr->name;
				$journey_date                 = ! empty( $item['journey_date'] ) ? $item['journey_date'] : ' - ';
				$journey_seat_class           = ! empty( $item['seat_class'] ) ? $item['seat_class'] : '';
				$arrival_time                 = ! empty( $item['arrival_time'] ) ? $item['arrival_time'] : ' - ';
				$departure_time               = ! empty( $item['departure_time'] ) ? $item['departure_time'] : ' - ';
				$journey_pickup_point_address = ! empty( $pickup_point ) ? get_term_meta( $pickup_point, 'mtrap_bus_stops_pickuppoint_address', true ) : ' - ';
				$journey_drop_point_address   = ! empty( $drop_point ) ? get_term_meta( $drop_point, 'mtrap_bus_stops_pickuppoint_address', true ) : ' - ';
				if ( ! empty( $aminities_terms ) && ! is_wp_error( $aminities_terms ) ) {
					foreach ( $aminities_terms as $term ) {
						$aminities_arr[] = $term->name;
					}
				}
				$aminities_string = ! empty( $aminities_arr ) ? implode( ', ', $aminities_arr ) : '';
				// Set data in a formated multidimensional array.
				$data_output[ $meta_id ] = array(
					'coach_type_term'              => $coach_type_term,
					'journey_pickup_point_address' => $journey_pickup_point_address,
					'journey_drop_point_address'   => $journey_drop_point_address,
					'departure_time'               => $departure_time,
					'arrival_time'                 => $arrival_time,
					'journey_seat_class'           => $journey_seat_class,
					'journey_date'                 => $journey_date,
					'pickup_point_name'            => $pickup_point_name,
					'drop_point_name'              => $drop_point_name,
					'aminities'                    => $aminities_string,
					'passenger_details'            => $meta_data->value,
				);
			}
		}
	}

	if ( ! empty( $data_output ) ) {

		$store_name           = ! empty( get_option( 'blogname' ) ) ? get_option( 'blogname' ) : '';
		$store_address        = ! empty( get_option( 'woocommerce_store_address', '' ) ) ? get_option( 'woocommerce_store_address', '' ) . ',&nbsp;' : '';
		$store_address_2      = ! empty( get_option( 'woocommerce_store_address_2', '' ) ) ? get_option( 'woocommerce_store_address_2', '' ) . ',&nbsp;' : '';
		$store_city           = ! empty( get_option( 'woocommerce_store_city', '' ) ) ? get_option( 'woocommerce_store_city', '' ) . ',&nbsp;' : '';
		$store_postcode       = ! empty( get_option( 'woocommerce_store_postcode', '' ) ) ? get_option( 'woocommerce_store_postcode', '' ) : '';
		$final_address        = $store_address . $store_address_2 . $store_city . $store_postcode;
		$store_phone          = ! empty( get_option( 'woocommerce_store_phone', '' ) ) ? get_option( 'woocommerce_store_phone', '' ) : '';
		$store_baggage_notice = ! empty( get_option( 'woocommerce_email_footer_text', '' ) ) ? get_option( 'woocommerce_email_footer_text', '' ) : '';
		$store_admin_email    = ! empty( get_option( 'admin_email' ) ) ? get_option( 'admin_email' ) : '';

		// Get order details.
		$custom_logo_id     = get_theme_mod( 'custom_logo' );
		$logo_url           = wp_get_attachment_image_url( $custom_logo_id, 'full' );
		$email_header_image = ! empty( get_option( 'woocommerce_email_header_image' ) ) ? get_option( 'woocommerce_email_header_image' ) : $logo_url;
		$order_date         = $order->get_date_created();
		$journey_date       = $order_date->format( 'Ymd' );

		foreach ( $data_output as $travel_detail ) {

			$passenger_details = maybe_unserialize( $travel_detail['passenger_details'] );

			if ( ! empty( $passenger_details ) ) {

				foreach ( $passenger_details as $passenger_detail ) {

					// Output HTML content to buffer.
					$html_data .= '<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
						<style>
							body {
								font-family: Arial, Helvetica, sans-serif;
							}
					
							.header {
								display: flex;
								background-color: #11131a !important;
								color: #fff !important;
								print-color-adjust: exact;
							}
					
							.header-wrapper {
								display: flex;
								flex-direction: column;
								background-color: #11131a !important;
								color: #fff !important;
								print-color-adjust: exact;
							}
					
							
							.ticket-detail p {
								margin-top: 10px;
								margin-bottom: 10px;
							}
					
							.header-ticket {
								background-color: #d8b372 !important;
								color: #fff !important;
							}
					
							.date-time {
								border-bottom: 1px dashed gray;
							}
					
							.journey-details {
							  
								border-top: 0;
								border-bottom: 1px dashed gray;
							}
					
							.passenger-details {
						   
								border-top: 1px dashed gray;
								border-bottom:1px dashed gray;
							}
					
							.stations {
							
								border-top: 0;
						border-bottom: 0;
							}
					
							@media print {
								.header {
									background-color: #11131a !important;
									-webkit-print-color-adjust: exact;
									print-color-adjust: exact;
								}
					
								.header-wrapper {
									background-color: #11131a !important;
									-webkit-print-color-adjust: exact;
									print-color-adjust: exact;
								}
					
								.header-ticket {
									background-color: #d8b372 !important;
									color: #fff !important;
									-webkit-print-color-adjust: exact;
									print-color-adjust: exact;
								}
					
								.ticket-detail {
									border: 1px solid gray;
									-webkit-print-color-adjust: exact;
									print-color-adjust: exact;
								}
					
								.journey-details {
									border-top: 1px dashed gray;
									-webkit-print-color-adjust: exact;
									print-color-adjust: exact;
								}
					
								.passenger-details {
									border-top: 1px dashed gray;
									-webkit-print-color-adjust: exact;
									print-color-adjust: exact;
								}
					
								.stations {
									border-top: 1px dashed gray;
									-webkit-print-color-adjust: exact;
									print-color-adjust: exact;
								}
					
								.additional {
									border-top: 1px dashed gray;
									-webkit-print-color-adjust: exact;
									print-color-adjust: exact;
								}
							}
						</style></head><body>
						<div class="header-wrapper">
							<div class="header">
								<div style="width: 20%;">
									<img src="' . $email_header_image . '" alt="" style="max-width: 120px; padding: 25px;">
								</div>
								<div style="text-align: center; padding:5px; width: 60%; ">
									<h3 style="font-weight: 500; margin-bottom: 10px;">' . $store_name . '</h3>
									<p style="margin-top: 0; padding-bottom: 10px; text-align: center; max-width: 452px; margin: 0 auto;">' . $final_address . '</p>
									<p style="margin-bottom: 7px; margin-top: 7px; display: inline;">
										<span style="font-weight: 600;">' . __( 'Phone no:', 'winger' ) . '</span>&nbsp;' . $store_phone . '
									</p>
									<span style="padding: 6px; display: inline;">|</span>
									<p style="margin-bottom: 7px; margin-top: 7px; display: inline;">
										<span style="font-weight: 600;">' . __( 'E-mail:', 'winger' ) . '</span>&nbsp;' . $store_admin_email . '
									</p>
								</div>
								<div class="qrcode" style="width: 20%;">
									<img src="' . get_stylesheet_directory_uri() . '/assets/img/pdfinterface-calender.png" alt=""
										style="max-width: 100px; float: right; padding-top: 5px; padding-right: 5px;">
								</div>
							</div>
							<div
								style="display: flex; text-align: center; font-size: 14px; padding: 10px 0px; margin: 0 auto;  border-top: 1px dashed #fff; width: 100%; justify-content: center;">
								<p style="margin-bottom: 7px; margin-top: 7px; display: inline;">
									' . __( 'Ticket Number:', 'winger' ) . '&nbsp; ' . $passenger_detail['ticket_no'] . '
								</p>
							</div>
						</div>
						<div class="ticket-detail" style="border: 1px solid gray; border-radius: 5px; margin-top: 25px;">
							<div class="header-ticket" style="padding: 15px;">
								' . __( 'Ticket Details', 'winger' ) . '
							</div>
							<div class="date-time" style="padding-left: 30px; ">
								<p style="font-size: 14px; color: #747f8d;">' . __( 'Journey Date', 'winger' ) . '</p>
								<p style="font-size: 14px; font-weight: 600;"><img
										style="height: 25px; width: 25px; vertical-align: middle;" src="' . get_stylesheet_directory_uri() . '/assets/img/pdfinterface-calender.png" alt="">
									<span>' . $travel_detail['journey_date'] . '</span></p>
							</div>
							<div class="journey-details" style="padding-left: 30px; display:flex; justify-content: space-between ;">
								<div class="seat-type" style="width: 50%;">
									<p style="font-size: 14px; color: #747f8d;">  ' . __( ' Seat Type', 'winger' ) . '</p>
									<p style="font-size: 12px; font-weight: 600; color: #747f8d;"><img
											style="height: 25px; width: 25px; vertical-align: middle;" src="' . get_stylesheet_directory_uri() . '/assets/img/pdfinterface-bus.png" alt="">' . $travel_detail['coach_type_term'] . '</br><span>' . $travel_detail['journey_seat_class'] . '</span></p>
								</div>
								<div class="amenities" style="width: 50%;">
									<p style="font-size: 14px; color: #747f8d;">' . __( 'Amenities', 'winger' ) . '</p>
									<p style="font-size: 12px; font-weight: 600; color: #747f8d;"><img
											style="height: 25px; width: 25px; vertical-align: middle;" src="' . get_stylesheet_directory_uri() . '/assets/img/pdfinterface-wallet.png" alt=""> <span
											style="max-width: 200px;">  ' . $travel_detail['aminities'] . ' </span> </p>
								</div>
							</div>
							<div class="stations" style="display: flex; justify-content: space-between ; padding-left: 30px;">
								<div class="boarding-point" style="width: 50%; ">
									<p style="font-size: 14px; color: #747f8d;">' . __( 'Boarding Point', 'winger' ) . '</p>
									<p><img style="height: 25px; width: 25px; vertical-align: middle;" src="' . get_stylesheet_directory_uri() . '/assets/img/pdfinterface-location.png" alt=""><span
											style="font-weight: bold; font-size: 14px;">' . $travel_detail['pickup_point_name'] . '</span></p>
									<p style="margin-left: 25px; max-width: 300px;  color: #747f8d; font-size: 12px; margin-bottom: 0px;">
										' . $travel_detail['journey_pickup_point_address'] . '</p>
									<p style="font-weight: 600; margin-left: 25px; font-size: 12px; margin-top: 0px;"><span
											style="font-weight: normal; color:#747f8d; line-height: 2;">' . __( 'Pickup Time:', 'winger' ) . '</span>
											' . $travel_detail['departure_time'] . '</p>
								</div>
								<div class="drop-point" style="width: 50%;">
									<p style="font-size: 14px; color: #747f8d;">' . __( 'Dropping Point', 'winger' ) . '</p>
									<p><img style="height: 25px; width: 25px; vertical-align: middle;" src="' . get_stylesheet_directory_uri() . '/assets/img/pdfinterface-location.png" alt=""><span
											style="font-weight: bold; font-size: 14px;">' . $travel_detail['drop_point_name'] . '</span></p>
									<p style="margin-left: 25px; max-width: 300px;  color: #747f8d; font-size: 12px; margin-bottom: 0px;">
										' . $travel_detail['journey_drop_point_address'] . '</p>
									<p style="font-weight: 600; margin-left: 25px; font-size: 12px; margin-top: 0px;"><span
											style="font-weight: normal; color:#747f8d; line-height: 2;">' . __( 'Dropping Time:', 'winger' ) . '</span>
											' . $travel_detail['arrival_time'] . '</p>
								</div>
							</div>
							<div class="passenger-details">
								<div class="name" style="padding-left: 30px;">
									<p style="font-size: 14px; color: #747f8d;"> ' . __( 'Passenger Details', 'winger' ) . '</p>
									<p style="font-size: 14px; font-weight: 600; margin-bottom: 0px;"><img
											style="height: 25px; width: 25px; vertical-align: middle;" src="' . get_stylesheet_directory_uri() . '/assets/img/pdfinterface-passenger.png" alt=""> &nbsp;
											' . esc_html( $passenger_detail['passenger_name'] ) . '</p>
									<span style="margin-left: 36px; color: #747f8d; font-size: 12px ;"> ' . ucfirst( $passenger_detail['passenger_type'] ) . ', ' . ucfirst( $passenger_detail['passenger_gender'] ) . '</span>
									<p style="margin-left: 36px; font-size: 12px; color: #747f8d; margin-bottom: 0;"> ' . __( 'Phone no. :', 'winger' ) . ' <span
											style="color: #747f8d;">' . esc_html( $passenger_detail['passenger_phone'] ) . '</span></p>
									<p style="margin-left: 36px; font-size: 12px; color: #747f8d; margin-top: 5px;"> ' . __( 'Email :', 'winger' ) . '<span
											style="color: #747f8d;">' . esc_html( $passenger_detail['passenger_email'] ) . '</span></p>
								</div>
							</div>
							<div class="additional" style="padding-left: 30px;">
								<p style="font-size: 14px; color: #747f8d;">' . __( 'Additional Information', 'winger' ) . '</p>
								<p style="font-size: 14px; color:#747f8d;">' . $store_baggage_notice . '</p>
							</div>
						</div>
					</body></html>';

					$dompdf->loadHtml( $html_data );

					// (Optional) Setup the paper size and orientation.
					$dompdf->setPaper( 'A4', 'portraite' );

					// Render PDF.
					$dompdf->render();

					// Save PDF to a folder.
					$upload_dir     = wp_upload_dir();
					$base_file_path = $upload_dir['basedir'] . '/invoice/' . $order_id . '.pdf';
					$base_url       = $upload_dir['baseurl'] . '/invoice/' . $order_id . '.pdf';

					file_put_contents( $base_file_path, $dompdf->output() );

					if ( $path == 'base' ) {

						$tickets[] = $base_file_path;

					} else {

						$tickets[] = $base_url;
					}
				}
			}
		}
	}

	return $tickets;
}



// add_action( 'woocommerce_order_status_completed', 'mtrap_generate_invoice', 10, 1 );
// add_action( 'woocommerce_order_status_processing', 'mtrap_generate_invoice', 10, 1 );
/**
 * Add pdf on order status completed or processing.
 **/
function mtrap_generate_invoice( $order_id ) {

	$invoice_path = generate_pdf_for_order( $order_id, 'path' );

	update_post_meta( $order_id, 'tickets', maybe_serialize( $invoice_path ) );
}

function generate_custom_pdf_content( $order_id ) {
	ob_start();
	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Document</title>
		<style>
			body {
				font-family: Arial, Helvetica, sans-serif;
			}

			.header {
				display: flex;
				background-color: #11131a !important;
				color: #fff !important;
				print-color-adjust: exact;
			}

			.header-wrapper {
				display: flex;
				flex-direction: column;
				background-color: #11131a !important;
				color: #fff !important;
				print-color-adjust: exact;
			}

			
			.ticket-detail p {
				margin-top: 10px;
				margin-bottom: 10px;
			}

			.header-ticket {
				background-color: #d8b372 !important;
				color: #fff !important;
			}

			.date-time {
				border-bottom: 1px dashed gray;
			}

			.journey-details {
			
				border-top: 0;
				border-bottom: 1px dashed gray;
			}

			.passenger-details {
		
				border-top: 1px dashed gray;
				border-bottom:1px dashed gray;
			}

			.stations {
			
				border-top: 0;
		border-bottom: 0;
			}

			.additional {
				
			}

			@media print {
				.header {
					background-color: #11131a !important;
					-webkit-print-color-adjust: exact;
					print-color-adjust: exact;
				}

				.header-wrapper {
					background-color: #11131a !important;
					-webkit-print-color-adjust: exact;
					print-color-adjust: exact;
				}

				.header-ticket {
					background-color: #d8b372 !important;
					color: #fff !important;
					-webkit-print-color-adjust: exact;
					print-color-adjust: exact;
				}

				.ticket-detail {
					border: 1px solid gray;
					-webkit-print-color-adjust: exact;
					print-color-adjust: exact;
				}

				.journey-details {
					border-top: 1px dashed gray;
					-webkit-print-color-adjust: exact;
					print-color-adjust: exact;
				}

				.passenger-details {
					border-top: 1px dashed gray;
					-webkit-print-color-adjust: exact;
					print-color-adjust: exact;
				}

				.stations {
					border-top: 1px dashed gray;
					-webkit-print-color-adjust: exact;
					print-color-adjust: exact;
				}

				.additional {
					border-top: 1px dashed gray;
					-webkit-print-color-adjust: exact;
					print-color-adjust: exact;
				}
			}
		</style>
	</head>
    <body>
    <div class="header-wrapper">
        <div class="header">
            <div style="width: 20%;">
                <img src="/wp-content/uploads/2019/07/aws-white-logo-final.svg" style="max-width: 120px; padding: 25px;"/>
            </div>
            <div style="text-align: center; padding:5px; width: 60%; ">
                <h3 style="font-weight: 500; margin-bottom: 10px;">Africa Web Solution</h3>
                <p style="margin-top: 0; padding-bottom: 10px; text-align: center; max-width: 452px; margin: 0 auto;">
                    Address here Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet consectetur adipisicing elit.
                    Inventore, obcaecati?
                </p>
                <p style="margin-bottom: 7px; margin-top: 7px; display: inline;">
                    <span style="font-weight: 600;">Phone no:</span>&nbsp;1234567890
                </p>
                <span style="padding: 6px; display: inline;">|</span>
                <p style="margin-bottom: 7px; margin-top: 7px; display: inline;">
                    <span style="font-weight: 600;">Email:</span>&nbsp;text@example.com
                </p>
            </div>
            <div class="qrcode" style="width: 20%;">
                <img src="http://192.168.1.104/busproject-stg/wp-content/uploads/2024/05/QR_code.png" style="max-width: 100px; float: right; padding-top: 5px; padding-right: 5px;"/>
            </div>
        </div>
        <div
            style="display: flex; text-align: center; font-size: 14px; padding: 10px 0px; margin: 0 auto;  border-top: 1px dashed #fff; width: 100%; justify-content: center;">
            <p style="margin-bottom: 7px; margin-top: 7px; display: inline;">
                Ticket Number: TT5K66761896
            </p>
        </div>
    </div>
    <div class="ticket-detail" style="border: 1px solid gray; border-radius: 5px; margin-top: 25px;">
        <div class="header-ticket" style="padding: 15px;">
            Ticket Details
        </div>
        <div class="date-time" style="padding-left: 30px; ">
            <p>Journey Date and Time</p>
            <div class="image-container">
                <img src="http://192.168.1.104/busproject-stg/wp-content/uploads/2024/04/bus-set-with-different-perspectives_23-2147830307-1.jpg"/>
                <span>20/04/2024, 10:00 PM</span>
            </div>
        </div>
        <div class="journey-details" style="padding-left: 30px; display:flex; justify-content: space-between ;">
            <div class="seat-type" style="width: 50%;">
                <p style="font-size: 14px; color: #747f8d;"> Seat Type</p>
                <p style="font-size: 12px; font-weight: 600; color: #747f8d;"><span>NON
                        A/C Sleeper (2+1)</span></p>
            </div>
            <div class="amenities" style="width: 50%;">
                <p style="font-size: 14px; color: #747f8d;">Amenities</p>
                <p style="font-size: 12px; font-weight: 600; color: #747f8d;"><span
                        style="max-width: 200px;"> Charger Plug, Emergency exit, Fire Extinguisher, Mobile
                        Ticket Supported </span> </p>
            </div>
        </div>
        <div class="stations" style="display: flex; justify-content: space-between ; padding-left: 30px;">
            <div class="boarding-point" style="width: 50%; ">
                <p style="font-size: 14px; color: #747f8d;">Boarding Point</p>
                <p><span
                        style="font-weight: bold; font-size: 14px;">Lillie</span></p>
                <p style="margin-left: 25px; max-width: 300px;  color: #747f8d; font-size: 12px; margin-bottom: 0px;">
                    Chandarana Travels
                    Chandarana Travels, Near Bus Station , Amreli</p>
                <p style="font-weight: 600; margin-left: 25px; font-size: 12px; margin-top: 0px;"><span
                        style="font-weight: normal; color:#747f8d; line-height: 2;">Pickup TIME:</span>
                    04:40 AM</p>
            </div>
            <div class="drop-point" style="width: 50%;">
                <p style="font-size: 14px; color: #747f8d;">Dropping Point</p>
                <p><span style="font-weight: bold; font-size: 14px;">Lillie</span></p>
                <p style="margin-left: 25px; max-width: 300px;  color: #747f8d; font-size: 12px; margin-bottom: 0px;">
                    Shivranjani, Jodhpur
                    Village, Ahmedabad</p>
                <p style="font-weight: 600; margin-left: 25px; font-size: 12px; margin-top: 0px;"><span
                        style="font-weight: normal; color:#747f8d; line-height: 2;">DROPPING TIME:</span>
                    04:40 AM</p>
            </div>
        </div>
        <div class="passenger-details">
            <div class="name" style="padding-left: 30px;">
                <p style="font-size: 14px; color: #747f8d;"> Passenger Details</p>
                <p style="font-size: 14px; font-weight: 600; margin-bottom: 0px;">John Doe</p>
                <span style="margin-left: 36px; color: #747f8d; font-size: 12px ;"> Adult, Male</span>
                <p style="margin-left: 36px; font-size: 12px; color: #747f8d; margin-bottom: 0;"> Phone no. <span
                        style="color: #747f8d;">978465321465</span></p>
                <p style="margin-left: 36px; font-size: 12px; color: #747f8d; margin-top: 5px;"> Email :<span
                        style="color: #747f8d;">johndoe@example.com</span></p>
            </div>
        </div>
        <div class="additional" style="padding-left: 30px;">
            <p style="font-size: 14px; color: #747f8d;">Additional Information</p>
            <p style="font-size: 14px; color:#747f8d;">Lorem ipsum dolor sit amet, consectetur adipisicing elit.
                Sapiente suscipit debitis eum voluptates quasi ratione ut, omnis velit tempore dolorum quaerat vitae
                dolorem expedita optio, aliquam quibusdam, similique quos accusamus ea dolor. Optio quidem, ut tempora,
                quam incidunt, nemo recusandae repellat aperiam assumenda eos tempore sapiente perferendis eius!
                Commodi, possimus! Lorem ipsum dolor sit amet consectetur adipisicing elit. Dignissimos quam fugiat
                recusandae voluptas. Delectus at facilis dolores, rem, molestias vitae minima error, consectetur in
                veniam aliquid commodi temporibus blanditiis! Nemo!</p>
        </div>
    </div>
	</body>
	</html>
	<?php
	// Capture the output from the buffer and assign it to $html_content
	$html_content = ob_get_clean();

	// Return the HTML content
	return $html_content;
}
