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
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

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
		<?php
		$order_tickets = get_post_meta( $order->get_id(), 'tickets', true );
		$tickets_array = maybe_unserialize( $order_tickets );
		// Check if the invoice PDF file exists.
		if ( ! empty( $tickets_array ) ) {
			?><h2><?php _e( 'Ticket' ); ?></h2><?php
			$ticket_count = 1;
			foreach ( $tickets_array as $tickets ) {
				echo '<p style="margin-top:10px;"><strong>' . __( 'Ticket' ) . ' ' . $ticket_count . ':</strong> <a style="margin: -10px 0px 0px 12px;" href="' . $tickets . '" download target="_blank">Download</a></p>';
				++$ticket_count;
			}
		} else {
			echo '<h2>' . __( 'Ticket', 'winger' ) . '</h2>';
			echo '<p style="margin-top:30px;">' . __( 'Ticket PDF not found.', 'winger' ) . '</p>';
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

	$order_tickets = get_post_meta( $order->get_id(), 'tickets', true );
	$tickets_array = maybe_unserialize( $order_tickets );
	// Check if the invoice PDF file exists.
	if ( ! empty( $tickets_array ) ) {
		echo '<h2>' . __( 'Tickets', 'winger' ) . '</h2>';
		$ticket_count = 1;
		foreach ( $tickets_array as $tickets ) {
			echo '<p style="margin-top:10px;"><strong>' . __( 'Ticket' ) . ' ' . $ticket_count . ':</strong> <a style="margin: -10px 0px 0px 12px;" href="' . $tickets . '" download target="_blank">Download</a></p>';
			++$ticket_count;
		}
	} else {
		echo '<h2>' . __( 'Ticket', 'winger' ) . '</h2>';
		echo '<p style="margin-top:30px;">' . __( 'Ticket PDF not found.', 'winger' ) . '</p>';
	}
}


/**
 * Generated PDF html.
 *
 * @param int    $order_id order id.
 *
 * @param string $path return url or base path.
 */
function generate_pdf_for_order( $order_id, $path ) {
	if ( ! $order_id ) {
		return;
	}

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
				$booking_id                   = $item->get_id();
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
					'booking_id'                   => $booking_id,
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

		foreach ( $data_output as $travel_detail ) {

			$passenger_details = maybe_unserialize( $travel_detail['passenger_details'] );

			if ( ! empty( $passenger_details ) ) {

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
				$email_header_image = ! empty( $logo_url ) ? $logo_url : '';
				$order_date         = $order->get_date_created();
				$journey_date       = $order_date->format( 'Ymd' );
				$journey_pnr_number = $journey_date . $order_id;

				foreach ( $passenger_details as $passenger_detail ) {
			
					// initiate and use the dompdf class.
					$dompdf = new Dompdf();

					$dompdf->set_option( 'isRemoteEnabled', true );
					// Output HTML content to buffer.

					$html_data = generate_ticket_pdf( $travel_detail, $passenger_detail, $store_name, $final_address, $store_phone, $store_baggage_notice, $store_admin_email, $email_header_image, $journey_pnr_number );

					$dompdf->loadHtml( $html_data );

					// (Optional) Setup the paper size and orientation.
					$dompdf->setPaper( 'A4', 'portraite' );

					// Render PDF.
					$dompdf->render();

					// Save PDF to a folder.
					$upload_dir = wp_upload_dir();

					$base_file_path = $upload_dir['basedir'] . '/invoice/' . $passenger_detail['ticket_no'] . '.pdf';
					$base_url       = $upload_dir['baseurl'] . '/invoice/' . $passenger_detail['ticket_no'] . '.pdf';

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

add_action( 'init', 'invoice_folder_create' );
/**
 * Generated invoice folder if not exist.
 */
function invoice_folder_create() {

	// Save PDF to a folder.
	$upload_dir = wp_upload_dir();

	// Full path to the new folder.
	$folder_path = $upload_dir['basedir'] . '/invoice';
	// Check if the folder exists.
	if ( ! is_dir( $folder_path ) ) {

		wp_mkdir_p( $folder_path );
	}
}

add_action( 'woocommerce_order_status_completed', 'mtrap_generate_invoice', 10, 1 );
add_action( 'woocommerce_order_status_processing', 'mtrap_generate_invoice', 10, 1 );
/**
 * Add pdf on order status completed or processing.
 *
 * @param int $order_id order id.
 */
function mtrap_generate_invoice( $order_id ) {

	$invoice_path = generate_pdf_for_order( $order_id, 'path' );

	update_post_meta( $order_id, 'tickets', maybe_serialize( $invoice_path ) );
}

/**
 * Generate pdf for ticket.
 *
 * @param array  $travel_detail travels details.
 *
 * @param array  $passenger_detail passenger details.
 *
 * @param string $store_name store_name.
 *
 * @param string $final_address address.
 *
 * @param string $store_phone phone no.
 *
 * @param string $store_baggage_notice notice.
 *
 * @param string $store_admin_email admin email.
 *
 * @param string $email_header_image logo url.
 */
function generate_ticket_pdf( $travel_detail, $passenger_detail, $store_name, $final_address, $store_phone, $store_baggage_notice, $store_admin_email, $email_header_image, $journey_pnr_number ) {
	// Generate the QR code.
	$qr_code = QrCode::create( __( 'Ticket Number:', 'winger' ) . ' ' . $passenger_detail['ticket_no'] . ' | ' . __( 'Booking Number:', 'winger' ) . ' ' . 'TT5K66761896' )->setSize( 150 );

	// Create a PNG writer.
	$qr_writer = new PngWriter();

	// Create the QR code image.
	$qr_result = $qr_writer->write( $qr_code );

	// Get the QR code image as a base64 string.
	$qr_code_base64 = base64_encode( $qr_result->getString() );

	ob_start();
	?>
	<html lang="en">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Document</title>
			<style>
				@page {
					margin: 0;
				}
				body {
					font-family: Arial, Helvetica, sans-serif;
					margin: 0;
					padding: 0;
				}
				.header, .header-wrapper, .header-ticket {
					display: block;
					width: 100%;
					background-color: #11131a;
					color: #fff;
					text-align: center;
					padding: 10px;
				}
				.header img, .header-ticket img {
					width: 100px;
				}
				.ticket-detail {
					border: 1px solid gray;
					border-radius: 5px;
					margin: 0;
					padding: 15px;
					display: block;
					width: 100%;
					overflow: hidden;
				}
				.date-time, .journey-details, .passenger-details, .additional {
					padding: 15px;
					border-bottom: 1px dashed gray;
					display: block;
					width: 100%;
				}
				.journey-details, .passenger-details {
					border-top: 1px dashed gray;
				}
				.stations {
					padding: 15px;
					border-top: 1px dashed gray;
					border-bottom: 1px dashed gray;
					display: block;
					width: 100%;
				}
				.stations td {
					width: 50%;
					vertical-align: top;
				}
				.stations td p {
					margin: 0;
				}
				.passenger-details div {
					padding-left: 15px;
				}
				.additional {
					border-top: 1px dashed gray;
				}
				.header {
					white-space: nowrap;
					text-align: center;
				}
				.header > div {
					display: inline-block;
					vertical-align: top;
					margin: 0 10px; 
				}
				.logo-header img,
				.qrcode img {
					max-width: 100px;
				}
				.infor-header {
					width: 500px;
				}
				p{
					margin-bottom: 0;
				}
			</style>
		</head>
		<body>
			<div class="header-wrapper" style="padding-bottom: 0; margin-bottom:10px">
				<div class="header" width="100%" style="padding: 0;">
					<div class="logo-header" style="float:left;">
						<img src="<?php echo $email_header_image; ?>" alt="Header Image" style="width:100%; max-width:150px; padding-top:40px">
					</div>
					<div class="infor-header" style="float:left;">
						<h3 style="font-weight: 500; margin-bottom: 10px;color: #fff; margin-right:25px;"><?php echo $store_name; ?></h3>
						<p style="margin-bottom: 0;  margin-right:25px;"><?php echo $final_address; ?></p>
						<p style="margin-bottom: 10px;"><?php echo __( 'Phone no:', 'winger' ); ?> <?php echo $store_phone; ?> | <?php echo __( 'E-mail:', 'winger' ); ?> <?php echo $store_admin_email; ?></p>
					</div>
					<div class="qrcode" style="float:right; padding-right:10px;">
						<img src="data:image/png;base64,<?php echo $qr_code_base64; ?>" alt="QR Code">
					</div>
					<div style="display: flex; text-align: center; font-size: 14px; padding: 10px 0px; margin: 0 auto;  border-top: 1px dashed #fff; width: 100%; justify-content: center; clear:both;">
						<p style="margin-bottom: 7px; margin-top: 7px; display: inline;">
							<?php echo __( 'Ticket Number:', 'winger' ); ?><?php echo $journey_pnr_number; ?>
							| <?php echo __( 'Booking Number:', 'winger' ); ?> <?php echo $travel_detail['booking_id']; ?>
						</p>
					</div>
				</div>
			</div>
			<table class="ticket-wrapper" width="100%" cellspacing="0" cellpadding="10px">
				<tr>
					<td cellspacing="0" cellpadding="0" style="padding:0 25px 25px;">
					<div class="ticket-detail" style="border: 1px solid gray;border-radius: 5px;padding:0px;display: table;" width="80%">
				<div class="header-ticket" style="background-color: #d8b372 !important;color: #fff !important;height:40px; display: block; padding-left:10px; padding-top:1px ; padding-bottom: 5px;">
					<p style="margin-bottom: 0;text-align: left;"><?php echo __( 'Ticket Details', 'winger' ); ?></p>
				</div>
				<div class="date-time" style="padding: 15px 30px;">
					<p style="font-size: 14px; color: #747f8d;margin-bottom: 0;"><?php echo __( 'Journey Date', 'winger' ); ?></p>
					<p style="font-size: 14px; font-weight: 600;margin-bottom: 0;display: table; font-family: Arial, Helvetica, sans-serif;">
						<img src="<?php echo get_stylesheet_directory_uri() . '/assets/img/pdfinterface-calender.png'; ?>" alt="" style="height: 25px; width: 25px; vertical-align: middle;margin-right: 14px;">
						<span style="display: table-cell;vertical-align: middle; font-family: Arial, Helvetica, sans-serif !important;"><?php echo $travel_detail['journey_date']; ?></span>
					</p>
				</div>
				<div class="journey-details" style="padding: 15px 30px;background-color: #fef8ef;" width="100%">
					<table width="100%">
						<tr>
							<td class="seat-type" style="width: 50%;padding:0;" valign="top">
								<p style="font-size: 14px; color: #747f8d;margin-bottom: 0;"><?php echo __( 'Seat Type', 'winger' ); ?></p>
								<p  style="font-size: 12px; font-weight: 600; color: #747f8d;margin-bottom: 0;display: table; font-family: Arial, Helvetica, sans-serif !important;">
									<img style="height: 25px; width: 25px; vertical-align: middle;margin-right: 10px;" src="<?php echo get_stylesheet_directory_uri() . '/assets/img/pdfinterface-bus.png'; ?>" alt="">
									<span style="display: table-cell;vertical-align: middle; font-family: Arial, Helvetica, sans-serif!important;"><?php echo $travel_detail['coach_type_term'] . ' ' . $travel_detail['journey_seat_class']; ?></span>
								</p>
							</td>
							<td class="amenities" style="width: 50%;padding:0;" valign="top">
								<p  style="font-size: 14px; color: #747f8d;margin-bottom: 0;"><?php echo __( 'Amenities', 'winger' ); ?></p>
								<P style="font-size: 12px; font-weight: 600; color: #747f8d;margin-bottom: 0;display: table; max-width:300px; font-family: Arial, Helvetica, sans-serif!important;">
									<img style="height: 25px; width: 25px; vertical-align: middle;margin-right: 10px;" src="<?php echo get_stylesheet_directory_uri() . '/assets/img/pdfinterface-wallet.png'; ?>" alt="">
									<span style="font-family: Arial, Helvetica, sans-serif!important; display: table-cell;vertical-align: middle; max-width:250px; table-layout: fixed; word-break: break-all; "><?php echo $travel_detail['aminities']; ?></span>
								</p>
							</td>
						</tr>
					</table>
				</div>
				<div class="stations" style="padding: 15px 30px;background-color: #fef8ef;" width="100%">
					<table width="100%">
						<tr>
							<td class="boarding-point"  style="width: 50%;padding:0;" valign="top">
								<p style="font-size: 14px;color: #747f8d;margin-top: 10px;margin-bottom: 10px;"><?php echo __( 'Boarding Point', 'winger' ); ?></p>
								<p style="vertical-align: middle;margin-bottom: 0;display: table;max-width: 300px;" >
									<img style="height: 25px; width: 25px; vertical-align: bottom;margin-right:7px;margin-top: -3px;" src="<?php echo get_stylesheet_directory_uri() . '/assets/img/pdfinterface-location.png'; ?>" alt="">
									<span style="display: table-cell;vertical-align: middle;">
										<?php echo $travel_detail['pickup_point_name']; ?>
									</span>
								</p>
								<p style="margin-left: 36px; max-width: 300px;  color: #747f8d; font-size: 12px; margin-bottom: 0px;"><?php echo $travel_detail['journey_pickup_point_address']; ?></p>
								<p style="font-weight: 600; margin-left: 36px; font-size: 12px; margin-top: 0px;"><span style="font-weight: normal; color:#747f8d; vertical-align: bottom;"><?php echo __( 'Pickup Time:', 'winger' ); ?><strong> <?php echo $travel_detail['departure_time']; ?></strong></span></p>
							</td>
							<td class="drop-point" style="width: 50%;padding:0;" valign="top">
								<p style="font-size: 14px;color: #747f8d;margin-top:10px;margin-bottom: 12px;"><?php echo __( 'Dropping Point', 'winger' ); ?></p>
								<p style="vertical-align: middle;margin-bottom: 0;max-width: 300px;"><img style="height: 25px;width: 25px;vertical-align: bottom;margin-bottom: 0;display: inline;margin-right: 10px;margin-top: -3px;" src="<?php echo get_stylesheet_directory_uri() . '/assets/img/pdfinterface-location.png'; ?>" alt=""><span style="font-weight: normal; margin-top:-5px; margin-bottom:2px;"><?php echo $travel_detail['drop_point_name']; ?></span></p>
								<p style="margin-left: 36px; max-width: 300px;  color: #747f8d; font-size: 12px; margin-bottom: 0px;"><?php echo $travel_detail['journey_drop_point_address']; ?></p>
								
								<p style="font-weight: 600; margin-left: 36px; font-size: 12px; margin-top: 0px;"><span style="font-weight: normal; color:#747f8d; vertical-align: bottom;"><?php echo __( 'Dropping Time:', 'winger' ); ?><strong> <?php echo $travel_detail['arrival_time']; ?></strong></span></p>
							</td>
						</tr>
					</table>
				</div>
				<div class="passenger-details" style="padding: 15px 30px;" width="100%">
					<div class="name" style="padding:0;">
						<p style="font-size: 14px;color: #747f8d;margin-top: 10px;margin-bottom: 10px;"><?php echo __( 'Passenger Details', 'winger' ); ?></p>
						<p style="font-size: 14px; font-weight: 600; margin-bottom: 0px; font-family: Arial, Helvetica, sans-serif!important;"><img style="height: 25px; width: 25px; vertical-align: middle; display:inline;" src="<?php echo get_stylesheet_directory_uri() . '/assets/img/pdfinterface-passenger.png'; ?>" alt=""> &nbsp;<span style="margin-left:2px; font-family: Arial, Helvetica, sans-serif !important; margin-left:2px;"><?php echo esc_html( $passenger_detail['passenger_name'] ); ?></span></p>
						<p style="margin-left: 36px; color: #747f8d; font-size: 12px ;margin-bottom: 0px;"><?php echo ucfirst( $passenger_detail['passenger_type'] ) . ', ' . ucfirst( $passenger_detail['passenger_gender'] ); ?></p>
						<p style="margin-left: 36px; font-size: 12px; color: #747f8d; margin-bottom: 0;"><?php echo __( 'Phone no.:', 'winger' ); ?> <?php echo esc_html( $passenger_detail['passenger_phone'] ); ?></p>
						<p style="margin-left: 36px; font-size: 12px; color: #747f8d;margin-bottom: 0px;"><?php echo __( 'Email:', 'winger' ); ?> <?php echo esc_html( $passenger_detail['passenger_email'] ); ?></p>
					</div>
				</div>
				<div class="additional" style="padding-left: 30px;border-bottom: 0;" width="100%">
					<p style="font-size: 14px; color: #747f8d;margin-bottom: 0;"><?php echo __( 'Additional Information', 'winger' ); ?></p>
					<p style="font-size: 14px; color:#747f8d;margin-bottom: 0; max-width:600px;"><?php echo $store_baggage_notice; ?></p>
				</div>
			</div></td>
				</tr>
			
			</table>
		</body>
	</html>
	<?php
	// Capture the output from the buffer and assign it to $html_content.
	$html_content = ob_get_clean();

	// Return the HTML content.
	return $html_content;
}
