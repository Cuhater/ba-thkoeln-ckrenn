<?php
/**
 * Template for displaying single Charigame Landing posts
 *
 * PHP version 8.1
 *
 * @package Charigame
 * @subpackage Templates
 * @since 1.0.0
 */

session_start();

if ( ! isset( $_SESSION['charigame_user_id'] ) ) {
	wp_safe_redirect( home_url( '/access-denied/' ) );
	exit;
}

get_header(); ?>

<div class="charigame-landing-container">
	<?php
	while ( have_posts() ) :
		the_post();
		?>

		<div class="landing-header">
			<h1 class="landing-title"><?php the_title(); ?></h1>
		</div>

		<div class="landing-content">
			<?php
			the_content();
			?>
		</div>

		<!-- Optional: Game Integration -->
		<div class="game-section">
			<?php
			$campaign_id = isset( $_SESSION['charigame_campaign_id'] ) ? absint( $_SESSION['charigame_campaign_id'] ) : 0;

			if ( $campaign_id ) {
				$game_type = get_post_meta( $campaign_id, 'game_type', true );
				$shortcode = sprintf(
					'[charigame_game type="%s" campaign_id="%d"]',
					esc_attr( $game_type ),
					$campaign_id
				);
				echo do_shortcode( $shortcode );
			}
			?>
		</div>

	<?php endwhile; ?>
</div>

<?php get_footer(); ?>
