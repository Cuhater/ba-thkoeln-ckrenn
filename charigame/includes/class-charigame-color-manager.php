<?php
/**
 * ChariGame Color Manager
 *
 * Manages colors for campaigns and provides CSS variables.
 *
 * @package ChariGame
 * @subpackage Includes
 * @since 1.0.0
 */

namespace ChariGame\Includes;

/**
 * Color Manager Class
 *
 * Handles color management, CSS variables, and color-related functionality.
 */
class ChariGame_Color_Manager {
	/**
	 * Instance of this class.
	 *
	 * @var ChariGame_Color_Manager|null
	 */
	private static $instance = null;

	/**
	 * Current campaign ID.
	 *
	 * @var int|null
	 */
	private $current_campaign_id = null;

	/**
	 * Get the singleton instance of this class.
	 *
	 * @return ChariGame_Color_Manager The instance.
	 */
	public static function get_instance(): self {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register hooks and actions.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'wp_head', array( $this, 'output_css_variables' ), 5 );
		add_action( 'admin_head', array( $this, 'output_admin_css_variables' ), 5 );
		add_action( 'wp_enqueue_scripts', array( $this, 'localize_colors_for_js' ), 10 );

		add_action( 'carbon_fields_post_meta_container_saved', array( $this, 'on_campaign_saved' ), 10, 2 );

		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_colors' ) );
	}

	/**
	 * Set the current campaign ID.
	 *
	 * @param int $campaign_id The campaign post ID.
	 * @return void
	 */
	public function set_current_campaign( int $campaign_id ): void {
		$this->current_campaign_id = $campaign_id;
	}

	/**
	 * Automatically determine the campaign ID.
	 *
	 * @return int|null The campaign ID or null if not found.
	 */
	private function get_current_campaign_id(): ?int {
		if ( $this->current_campaign_id ) {
			return $this->current_campaign_id;
		}

		// For single campaign pages.
		if ( is_singular( 'charigame-campaign' ) ) {
			return get_the_ID();
		}

		// For landing pages - find linked campaign.
		if ( is_singular( 'charigame-landing' ) ) {
			$campaigns = get_posts( array(
				'post_type'      => 'charigame-campaign',
				'meta_query'     => array(
					array(
						'key'     => '_linked_landing_page',
						'value'   => '"' . get_the_ID() . '"',
						'compare' => 'LIKE',
					),
				),
				'posts_per_page' => 1,
			) );

			if ( ! empty( $campaigns ) ) {
				return $campaigns[0]->ID;
			}
		}

		if ( isset( $_GET['campaign_id'] ) ) {
			return intval( $_GET['campaign_id'] );
		}

		return null;
	}

	/**
	 * Get colors for a campaign.
	 *
	 * @param int|null $campaign_id Optional campaign ID.
	 * @return array Array of color values.
	 */
	public function get_campaign_colors( ?int $campaign_id = null ): array {
		if ( ! $campaign_id ) {
			$campaign_id = $this->get_current_campaign_id();
		}

		if ( ! $campaign_id ) {
			return $this->get_default_colors();
		}

		$colors = array(
			'primary'    => carbon_get_post_meta( $campaign_id, 'primary_color' ) ? carbon_get_post_meta( $campaign_id, 'primary_color' ) : '#3B82F6',
			'secondary'  => carbon_get_post_meta( $campaign_id, 'secondary_color' ) ? carbon_get_post_meta( $campaign_id, 'secondary_color' ) : '#10B981',
			'tertiary'   => carbon_get_post_meta( $campaign_id, 'tertiary_color' ) ? carbon_get_post_meta( $campaign_id, 'tertiary_color' ) : '#F59E0B',
			'success'    => carbon_get_post_meta( $campaign_id, 'success_color' ) ? carbon_get_post_meta( $campaign_id, 'success_color' ) : '#22C55E',
			'warning'    => carbon_get_post_meta( $campaign_id, 'warning_color' ) ? carbon_get_post_meta( $campaign_id, 'warning_color' ) : '#F59E0B',
			'error'      => carbon_get_post_meta( $campaign_id, 'error_color' ) ? carbon_get_post_meta( $campaign_id, 'error_color' ) : '#EF4444',
		);

		return $colors;
	}

	/**
	 * Get default colors.
	 *
	 * @return array Array of default color values.
	 */
	private function get_default_colors(): array {
		return array(
			'primary'    => '#3B82F6',
			'secondary'  => '#10B981',
			'tertiary'   => '#F59E0B',
			'success'    => '#22C55E',
			'warning'    => '#F59E0B',
			'error'      => '#EF4444',
		);
	}

	/**
	 * Output CSS variables in the frontend.
	 *
	 * @return void
	 */
	public function output_css_variables(): void {
		$colors = $this->get_campaign_colors();

		echo '<style id="charigame-colors">';
		echo ':root {';
		foreach ( $colors as $name => $value ) {
			echo esc_html( "--color-{$name}: {$value};" );
		}
		echo '}';
		echo '</style>';
	}

	/**
	 * Output CSS variables in the admin.
	 *
	 * @return void
	 */
	public function output_admin_css_variables(): void {
		global $post;

		if ( ! $post || $post->post_type !== 'charigame-campaign' ) {
			return;
		}

		$colors = $this->get_campaign_colors( $post->ID );

		echo '<style id="charigame-admin-colors">';
		echo ':root {';
		foreach ( $colors as $name => $value ) {
			echo esc_html( "--color-{$name}: {$value};" );
		}
		echo '}';
		echo '</style>';
	}

	/**
	 * Make colors available for JavaScript.
	 *
	 * @return void
	 */
	public function localize_colors_for_js(): void {
		$colors = $this->get_campaign_colors();

		wp_localize_script( 'wp-element', 'charigameColors', $colors );
	}

	/**
	 * Enqueue colors for Block Editor.
	 *
	 * @return void
	 */
	public function enqueue_editor_colors(): void {
		global $post;

		if ( ! $post ) {
			return;
		}

		$campaign_id = null;

		if ( $post->post_type === 'charigame-campaign' ) {
			$campaign_id = $post->ID;
		}

		if ( $post->post_type === 'charigame-landing' ) {
			// Direct search for linked campaign.
			$campaigns = get_posts( array(
				'post_type'      => 'charigame-campaign',
				'meta_query'     => array(
					array(
						'key'     => '_linked_landing_page',
						'value'   => 'post:charigame-landing:' . $post->ID,
						'compare' => '=',
					),
				),
				'posts_per_page' => 1,
			) );

			if ( ! empty( $campaigns ) ) {
				$campaign_id = $campaigns[0]->ID;
			}
		}

		// Fallback to default colors if no campaign found.
		if ( $campaign_id ) {
			$colors = $this->get_campaign_colors( $campaign_id );
		} else {
			$colors = $this->get_default_colors();
		}

		// Only CSS variables for Block Editor.
		wp_add_inline_style(
			'wp-edit-blocks',
			':root {' . implode(
				'',
				array_map(
					function( $name, $value ) {
						return esc_html( "--color-{$name}: {$value};" );
					},
					array_keys( $colors ),
					$colors
				)
			) . '}'
		);

		// JavaScript object for React components.
		wp_localize_script( 'wp-block-editor', 'charigameColors', $colors );
	}

	/**
	 * Called when a campaign is saved.
	 *
	 * @param int      $post_id   The post ID being saved.
	 * @param object   $container The Carbon Fields container being saved.
	 * @return void
	 */
	public function on_campaign_saved( int $post_id, $container ): void {
		if ( get_post_type( $post_id ) === 'charigame-campaign' ) {
			$this->clear_color_cache( $post_id );
		}
	}

	/**
	 * Clear color cache.
	 *
	 * @param int $campaign_id The campaign ID to clear cache for.
	 * @return void
	 */
	private function clear_color_cache( int $campaign_id ): void {
		wp_cache_delete( "charigame_colors_{$campaign_id}", 'charigame' );
	}

	/**
	 * Utility method for shortcodes/templates.
	 *
	 * @param string   $color_name  The name of the color to retrieve.
	 * @param int|null $campaign_id Optional campaign ID.
	 * @return string The color value.
	 */
	public static function get_color( string $color_name, ?int $campaign_id = null ): string {
		$instance = self::get_instance();
		$colors   = $instance->get_campaign_colors( $campaign_id );

		return $colors[$color_name] ?? $colors['primary'];
	}
}
