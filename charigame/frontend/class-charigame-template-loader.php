<?php
/**
 * ChariGame Template Loader
 *
 * Handles template loading for campaign and landing pages.
 *
 * @package ChariGame
 * @subpackage Frontend
 * @since 1.0.0
 */

namespace ChariGame\Frontend;

/**
 * Template Loader Class
 *
 * Responsible for loading custom templates for ChariGame post types.
 */
class ChariGame_Template_Loader {

	/**
	 * Register hooks and filters
	 *
	 * @return void
	 */
	public function register(): void {
		add_filter( 'single_template', array( $this, 'load_campaign_template' ) );
		add_filter( 'single_template', array( $this, 'load_landing_template' ) );
	}


	/**
	 * Load custom template for campaign post type
	 *
	 * @param string $template The current template path.
	 * @return string Modified template path.
	 */
	public function load_campaign_template( $template ): string {
		global $post;

		if ( $post->post_type === 'charigame-campaign' ) {
			$plugin_template = plugin_dir_path( __DIR__ ) . 'templates/single-charigame-campaign.php';

			if ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}

		return $template;
	}

	/**
	 * Handle landing page templates with redirect
	 *
	 * @param string $template The current template path.
	 * @return string Modified template path.
	 */
	public function load_landing_template( $template ): string {
		global $post;

		if ( $post->post_type === 'charigame-landing' ) {
			wp_safe_redirect( home_url() );
			exit;
		}

		return $template;
	}


}
