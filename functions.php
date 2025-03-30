<?php
/**
 * Theme functions and definitions of the child themes
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Include required files.
*/

// Admin functions.

require get_stylesheet_directory() . '/inc/functions/admin/mtrap-admin-enqueue-scripts.php';

require get_stylesheet_directory() . '/inc/functions/admin/mtrap-admin-register-custom-taxonomies.php';

require get_stylesheet_directory() . '/inc/functions/admin/mtrap-admin-register-custom-post-type.php';

require get_stylesheet_directory() . '/inc/functions/admin/mtrap-admin-remove-default-functioanlity.php';

require get_stylesheet_directory() . '/inc/functions/admin/mtrap-admin-register-single-bus-settings-metabox.php';

require get_stylesheet_directory() . '/inc/functions/admin/mtrap-admin-register-taxonomy-bus-stops-custom-metabox.php';

require get_stylesheet_directory() . '/inc/functions/admin/mtrap-admin-genral-functions.php';

require get_stylesheet_directory() . '/inc/functions/admin/mtrap-admin-single-bus-settings-bus-stop-ajax-callback.php';

require get_stylesheet_directory() . '/inc/functions/admin/mtrap-admin-register-meta-user.php';

require get_stylesheet_directory() . '/inc/functions/admin/mtrap-admin-order-settings-page.php';

require get_stylesheet_directory() . '/inc/functions/admin/mtrap-admin-theme-shortcodes.php';


// Front functions.

require get_stylesheet_directory() . '/inc/functions/front/mtrap-front-theme-enqueue-scripts-styles.php';

require get_stylesheet_directory() . '/inc/functions/front/mtrap-front-theme-shortcodes.php';

require get_stylesheet_directory() . '/inc/functions/front/mtrap-front-my-account-form-processing.php';

require get_stylesheet_directory() . '/inc/functions/front/mtrap-front-callback-function.php';

require get_stylesheet_directory() . '/inc/functions/front/mtrap-front-genral-functions.php';


// order functions.

require get_stylesheet_directory() . '/inc/functions/order/mtrap-admin-order-ticket-generator.php';


