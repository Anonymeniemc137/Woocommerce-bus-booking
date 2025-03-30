<?php
/**
 * The Footer: widgets area, logo, footer menu and socials
 *
 * @package WINGER
 * @since WINGER 1.0
 */

							// Widgets area inside page content
							winger_create_widgets_area( 'widgets_below_content' );
						
							?>
						</div><!-- /.content -->
						<?php

						// Show main sidebar
						get_sidebar();
						?>
					</div><!-- /.content_wrap -->
					<?php

					// Widgets area below page content and related posts below page content
					$winger_body_style = winger_get_theme_option( 'body_style' );
					$winger_widgets_name = winger_get_theme_option( 'widgets_below_page' );
					$winger_show_widgets = ! winger_is_off( $winger_widgets_name ) && is_active_sidebar( $winger_widgets_name );
					$winger_show_related = is_single() && winger_get_theme_option( 'related_position' ) == 'below_page';
					if ( $winger_show_widgets || $winger_show_related ) {
						if ( 'fullscreen' != $winger_body_style ) {
							?>
							<div class="content_wrap">
							<?php
						}
						// Show related posts before footer
						if ( $winger_show_related ) {
							do_action( 'winger_action_related_posts' );
						}

						// Widgets area below page content
						if ( $winger_show_widgets ) {
							winger_create_widgets_area( 'widgets_below_page' );
						}
						if ( 'fullscreen' != $winger_body_style ) {
							?>
							</div><!-- /.content_wrap -->
							<?php
						}
					}
					?>
			</div><!-- /.page_content_wrap -->
			<?php

			// Don't display the footer elements while actions 'full_post_loading' and 'prev_post_loading'
			if ( ( ! is_singular( 'post' ) && ! is_singular( 'attachment' ) ) || ! in_array ( winger_get_value_gp( 'action' ), array( 'full_post_loading', 'prev_post_loading' ) ) ) {
				
				// Skip link anchor to fast access to the footer from keyboard
				?>
				<a id="footer_skip_link_anchor" class="winger_skip_link_anchor" href="#"></a>
				<?php

				do_action( 'winger_action_before_footer' );

				// Footer
				$winger_footer_type = winger_get_theme_option( 'footer_type' );
				if ( 'custom' == $winger_footer_type && ! winger_is_layouts_available() ) {
					$winger_footer_type = 'default';
				}
				get_template_part( apply_filters( 'winger_filter_get_template_part', "templates/footer-{$winger_footer_type}" ) );

				do_action( 'winger_action_after_footer' );

			}
			?>

		</div><!-- /.page_wrap -->

	</div><!-- /.body_wrap -->

	<?php wp_footer(); ?>

</body>
</html>