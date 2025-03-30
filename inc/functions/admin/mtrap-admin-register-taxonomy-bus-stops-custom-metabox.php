<?php
/**
 * Theme functions to register custom meta box for the products
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'bus-stops_add_form_fields', 'mtrap_add_bus_rout_stops_add_terms_screen' );
/**
 * HTML of the meta field.
 * Used for add ADD term page.
 */
function mtrap_add_bus_rout_stops_add_terms_screen() {

	$mtrap_get_bus_stops_taxonomy_terms_add = get_terms(
		array(
			'taxonomy'   => 'bus-stops',
			'hide_empty' => false,
		)
	); ?>

	<div class="mtrap-pickuppoint-selection">
		<div class="form-field term-description-wrap">	
			<label for="tag-description"><?php esc_html_e( 'Add Pickup Point Name', 'winger' ); ?></label>
			<input type="text" name="mtrap-tax-pickuppoint-selection" class="term-meta-text-field"  />
			<p id="description-description"><?php esc_html_e( 'Add pickup point name for this city.', 'winger' ); ?></p>
		</div>
		<div class="form-field term-description-wrap">
			<label for="tag-description"><?php esc_html_e( 'Choose Other stops', 'winger' ); ?></label>
			<select data-placeholder="Choose other cities for creating the route" multiple="multiple" class="mtrap-tax-city-selection" name="mtrap-tax-city-selection[]">
				<?php
				if ( ! empty( $mtrap_get_bus_stops_taxonomy_terms_add ) ) {
					foreach ( $mtrap_get_bus_stops_taxonomy_terms_add as $bus_stop ) {
						echo '<option value=' . esc_html( $bus_stop->term_id ) . '>' . esc_html( $bus_stop->name ) . '</option>';
					}
				}
				?>
			</select>
			<p id="description-description"><?php esc_html_e( 'This will help choosing the routes for the buses. Choose cities connected from this city.', 'winger' ); ?></p>
		</div>
	</div>
	<?php
}

add_action( 'bus-stops_edit_form', 'mtrap_add_bus_route_stops_edit_terms_screen', 10, 2 );
/**
 * HTML of the meta field.
 * Used for add EDIT term page.
 *
 * @param object $term $term.
 * @param object $taxonomy $taxonomy.
 */
function mtrap_add_bus_route_stops_edit_terms_screen( $term, $taxonomy ) {

	$mtrap_bus_stops_pickuppoint_value   = get_term_meta( $term->term_id, 'mtrap_bus_stops_pickuppoint_meta' );
	$mtrap_bus_stops_route_meta_value    = get_term_meta( $term->term_id, 'mtrap_bus_stops_route_meta' );
	$mtrap_bus_stops_pickuppoint_address = get_term_meta( $term->term_id, 'mtrap_bus_stops_pickuppoint_address' );

	// print_r($mtrap_bus_stops_route_meta_value); exit;
	// Get Bus type taxonomy terms.
	$mtrap_get_bus_stops_taxonomy_terms_edit = get_terms(
		array(
			'taxonomy'   => 'bus-stops',
			'hide_empty' => false,
			'exclude'    => $term->term_id,
		)
	);

	?>
	<table class="mtrap-tax-city-selection-outer-wrap form-table mtrap-pickuppoint-selection" role="presentation">
		<tbody>
			<tr class="mtrap-tax-city-selection-wrap form-field">
				<th scope="row"><label for="mtrap-tax-city-label"><?php esc_html_e( 'Add Pickup Point Name', 'winger' ); ?></label></th>
				<td>
					<?php
					if ( ! empty( $mtrap_bus_stops_pickuppoint_value ) ) {
						?>
						<input type="text" name="mtrap-tax-pickuppoint-selection" value="<?php echo esc_attr( $mtrap_bus_stops_pickuppoint_value[0] ); ?>" class="term-meta-text-field"  />
						<?php
					} else {
						?>
						<input type="text" name="mtrap-tax-pickuppoint-selection" class="term-meta-text-field"  />
					<?php } ?>
					<p class="description" id="parent-description"><?php esc_html_e( 'Add pickup point name for this city.', 'winger' ); ?></p>
				</td>
			</tr>
			<tr class="mtrap-tax-city-address-selection-wrap form-field">
				<th scope="row"><label for="mtrap-tax-city-label"><?php esc_html_e( 'Add Pickup Point Address', 'winger' ); ?></label></th>
				<td>
					<?php
					if ( ! empty( $mtrap_bus_stops_pickuppoint_address ) ) {
						?>
						<input type="text" name="mtrap-tax-pickuppoint-address-selection" value="<?php echo esc_attr( $mtrap_bus_stops_pickuppoint_address[0] ); ?>" class="term-meta-text-field"  />
						<?php
					} else {
						?>
						<input type="text" name="mtrap-tax-pickuppoint-address-selection" class="term-meta-text-field"  />
					<?php } ?>
					<p class="description" id="parent-description"><?php esc_html_e( 'Add pickup point address for this city.', 'winger' ); ?></p>
				</td>
			</tr>
			<tr class="mtrap-tax-city-selection-wrap form-field">
				<th scope="row"><label for="mtrap-tax-city-label"><?php esc_html_e( 'Choose Other stops', 'winger' ); ?></label></th>
				<td>
					<select data-placeholder="Choose other cities for creating the route" multiple="multiple" class="mtrap-tax-city-selection" name="mtrap-tax-city-selection[]">
						<?php
						if ( ! empty( $mtrap_get_bus_stops_taxonomy_terms_edit ) ) {
							foreach ( $mtrap_get_bus_stops_taxonomy_terms_edit as $bus_stop ) {
								if ( isset( $mtrap_bus_stops_route_meta_value ) && ! empty( $mtrap_bus_stops_route_meta_value[0] ) ) {
									$mtrap_selected_routes = in_array( $bus_stop->term_id, $mtrap_bus_stops_route_meta_value[0] ) ? 'selected' : '';
									echo '<option ' . esc_html( $mtrap_selected_routes ) . ' value=' . esc_html( $bus_stop->term_id ) . '>' . esc_html( $bus_stop->name ) . '</option>';
								} else {
									echo '<option value=' . esc_html( $bus_stop->term_id ) . '>' . esc_html( $bus_stop->name ) . '</option>';
								}
							}
						}
						?>
					</select>
					<p class="description" id="parent-description"><?php esc_html_e( 'This will help choosing the routes for the buses. Choose cities connected from this city.', 'winger' ); ?></p>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
}

add_action( 'create_term', 'mtrap_add_bus_route_stops_save_term' );
add_action( 'edit_term', 'mtrap_add_bus_route_stops_save_term' );
/**
 * Save meta data to the database.
 *
 * @param int $term_id $term_id.
 */
function mtrap_add_bus_route_stops_save_term( $term_id ) {

	if ( ! empty( $_POST['mtrap-tax-pickuppoint-selection'] ) ) {

		// Update term meta.
		update_term_meta( $term_id, 'mtrap_bus_stops_pickuppoint_meta', sanitize_text_field( $_POST['mtrap-tax-pickuppoint-selection'] ) );
	}
	if ( ! empty( $_POST['mtrap-tax-city-selection'] ) ) {

		update_term_meta( $term_id, 'mtrap_bus_stops_route_meta', array_map( 'sanitize_text_field', $_POST['mtrap-tax-city-selection'] ) );
	}
	if ( ! empty( $_POST['mtrap-tax-pickuppoint-address-selection'] ) ) {

		update_term_meta( $term_id, 'mtrap_bus_stops_pickuppoint_address', sanitize_text_field( $_POST['mtrap-tax-pickuppoint-address-selection'] ) );
	}
}
