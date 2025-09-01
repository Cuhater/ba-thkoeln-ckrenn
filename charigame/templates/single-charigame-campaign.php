<?php
/**
 * Template for displaying single Charigame Campaign posts
 *
 * PHP version 8.1
 *
 * @package Charigame
 * @subpackage Templates
 * @since 1.0.0
 */

$color_manager = ChariGame\Includes\ChariGame_Color_Manager::get_instance();
$colors        = $color_manager->get_campaign_colors();

wp_head();
$linked_landing_page = carbon_get_post_meta( get_the_ID(), 'linked_landing_page' );
$landing_page_id     = $linked_landing_page[0]['id'];
if ( $landing_page_id && is_numeric( $landing_page_id ) ) {
	$content = apply_filters( 'the_content', get_post_field( 'post_content', $landing_page_id ) );
}
?>
<div class="min-h-screen w-full bg-white relative">
	<div>
		<div class="charigame-campaign-container">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<div class="campaign-content">
					<?php
					echo do_shortcode( '[charigame_login_form title="' . esc_attr( get_the_title() ) . '" logo="' . esc_attr( carbon_get_the_post_meta( 'login_form_logo' ) ) . '" ]' );
					?>
				</div>

			<?php endwhile; ?>
		</div>
	</div>
</div>
