<?php
/**
 * The Header: Logo and main menu
 *
 * @package WINGER
 * @since WINGER 1.0
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js
									<?php
										// Class scheme_xxx need in the <html> as context for the <body>!
										echo ' scheme_' . esc_attr( winger_get_theme_option( 'color_scheme' ) );
									?>
										">
<head>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

	<?php
	if ( function_exists( 'wp_body_open' ) ) {
		wp_body_open();
	} else {
		do_action( 'wp_body_open' );
	}
	do_action( 'winger_action_before_body' );
	?>

	<div class="body_wrap">

		<div class="page_wrap">
			
			<?php
			$winger_full_post_loading = ( is_singular( 'post' ) || is_singular( 'attachment' ) ) && winger_get_value_gp( 'action' ) == 'full_post_loading';
			$winger_prev_post_loading = ( is_singular( 'post' ) || is_singular( 'attachment' ) ) && winger_get_value_gp( 'action' ) == 'prev_post_loading';

			// Don't display the header elements while actions 'full_post_loading' and 'prev_post_loading'
			if ( ! $winger_full_post_loading && ! $winger_prev_post_loading ) {

				// Short links to fast access to the content, sidebar and footer from the keyboard
				?>
				<a class="winger_skip_link skip_to_content_link" href="#content_skip_link_anchor" tabindex="1"><?php esc_html_e( "Skip to content", 'winger' ); ?></a>
				<?php if ( winger_sidebar_present() ) { ?>
				<a class="winger_skip_link skip_to_sidebar_link" href="#sidebar_skip_link_anchor" tabindex="1"><?php esc_html_e( "Skip to sidebar", 'winger' ); ?></a>
				<?php } ?>
				<a class="winger_skip_link skip_to_footer_link" href="#footer_skip_link_anchor" tabindex="1"><?php esc_html_e( "Skip to footer", 'winger' ); ?></a>
				
				<?php
				do_action( 'winger_action_before_header' );

				// Header
				$winger_header_type = winger_get_theme_option( 'header_type' );
				if ( 'custom' == $winger_header_type && ! winger_is_layouts_available() ) {
					$winger_header_type = 'default';
				}
				get_template_part( apply_filters( 'winger_filter_get_template_part', "templates/header-{$winger_header_type}" ) );

				// Side menu
				if ( in_array( winger_get_theme_option( 'menu_side' ), array( 'left', 'right' ) ) ) {
					get_template_part( apply_filters( 'winger_filter_get_template_part', 'templates/header-navi-side' ) );
				}

				// Mobile menu
				get_template_part( apply_filters( 'winger_filter_get_template_part', 'templates/header-navi-mobile' ) );

				do_action( 'winger_action_after_header' );

			}
			?>

			<div class="page_content_wrap">
				<?php
				do_action( 'winger_action_page_content_wrap', $winger_full_post_loading || $winger_prev_post_loading );

				// Single posts banner
				if ( is_singular( 'post' ) || is_singular( 'attachment' ) ) {
					if ( $winger_prev_post_loading ) {
						if ( winger_get_theme_option( 'posts_navigation_scroll_which_block' ) != 'article' ) {
							do_action( 'winger_action_between_posts' );
						}
					}
					// Single post thumbnail and title
					$winger_path = apply_filters( 'winger_filter_get_template_part', 'templates/single-styles/' . winger_get_theme_option( 'single_style' ) );
					if ( winger_get_file_dir( $winger_path . '.php' ) != '' ) {
						get_template_part( $winger_path );
					}
				}

				// Widgets area above page content
				$winger_body_style   = winger_get_theme_option( 'body_style' );
				$winger_widgets_name = winger_get_theme_option( 'widgets_above_page' );
				$winger_show_widgets = ! winger_is_off( $winger_widgets_name ) && is_active_sidebar( $winger_widgets_name );
				if ( $winger_show_widgets ) {
					if ( 'fullscreen' != $winger_body_style ) {
						?>
						<div class="content_wrap">
							<?php
					}
					winger_create_widgets_area( 'widgets_above_page' );
					if ( 'fullscreen' != $winger_body_style ) {
						?>
						</div><!-- </.content_wrap> -->
						<?php
					}
				}

				// Content area
				?>
				<div class="content_wrap<?php echo 'fullscreen' == $winger_body_style ? '_fullscreen' : ''; ?>">

					<div class="content">
						<?php
						// Skip link anchor to fast access to the content from keyboard
						?>
						<a id="content_skip_link_anchor" class="winger_skip_link_anchor" href="#"></a>
						<?php
						// Single posts banner between prev/next posts
						if ( ( is_singular( 'post' ) || is_singular( 'attachment' ) )
							&& $winger_prev_post_loading 
							&& winger_get_theme_option( 'posts_navigation_scroll_which_block' ) == 'article'
						) {
							do_action( 'winger_action_between_posts' );
						}

						// Widgets area inside page content
						winger_create_widgets_area( 'widgets_above_content' );
