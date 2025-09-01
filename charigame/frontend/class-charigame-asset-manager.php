<?php
/**
 * ChariGame Asset Manager
 *
 * Handles all frontend and admin assets for the ChariGame plugin.
 *
 * @package ChariGame
 * @subpackage Frontend
 * @since 1.0.0
 */

namespace ChariGame\Frontend;

use ChariGame\Includes\ChariGame_Color_Manager;
use ChariGame\Includes\ChariGame_Donation_Manager;
use ChariGame\Includes\Helper;

/**
 * Asset Manager Class
 *
 * Responsible for enqueueing and managing all CSS and JavaScript assets.
 */
class ChariGame_Asset_Manager {

	/**
	 * Register hooks and actions
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'deregister_assets' ), 999 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 999 );
		add_action( 'wp_head', array( $this, 'output_meta_viewport' ), 1 );
		// Use priority 999 to ensure the scripts and styles load after other plugins.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ), 999 );
	}

	/**
	 * Enqueue admin-specific assets
	 *
	 * @param string $hook The current admin page.
	 * @return void
	 */
	public function enqueue_admin_assets( $hook ): void {
		$screen            = get_current_screen();
		$charigame_types   = array(
			'charigame-campaign',
			'charigame-recipient',
			'charigame-user',
			'charigame-game',
			'charigame-landing',
		);
		$is_charigame_page = (
			false !== strpos( $hook, 'charigame' ) ||
			( $screen && in_array( $screen->post_type, $charigame_types, true ) ) ||
			( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) &&
				$screen &&
				in_array( $screen->post_type, $charigame_types, true )
			)
		);

		if ( $is_charigame_page ) {
			$this->enqueue_shadcn_css();
		} else {
			return;
		}

		$plugin_version = CHARIGAME_VERSION;
		$plugin_url     = plugin_dir_url( __DIR__ );
		$dependencies   = array( 'jquery' );

		if ( $screen && $screen->post_type === 'charigame-campaign' ) {
			if ( class_exists( 'Carbon_Fields\\Carbon_Fields' ) ) {
				$dependencies[] = 'carbon-fields-core';
			}
		}

		wp_enqueue_script(
			'charigame-backend-js',
			$plugin_url . 'js/backend.js',
			$dependencies,
			$plugin_version,
			true
		);

		$localize_data = array(
			'ajaxurl'                 => admin_url( 'admin-ajax.php' ),
			'nonce'                   => wp_create_nonce( 'charigame_admin_nonce' ),
			'plugin_url'              => $plugin_url,
			'current_screen'          => isset( $screen->id ) ? $screen->id : '',
			'post_type'               => isset( $screen->post_type ) ? $screen->post_type : '',
			'carbon_fields_available' => class_exists( 'Carbon_Fields\\Carbon_Fields' ),
		);

		if ( $screen && $screen->post_type === 'charigame-campaign' ) {
			global $post;
			$localize_data['post_id']          = isset( $post->ID ) ? $post->ID : 0;
			$localize_data['is_campaign_page'] = true;
		}

		wp_localize_script( 'charigame-backend-js', 'charigameAdmin', $localize_data );
	}

	/**
	 * Deregister WordPress scripts and styles on game pages
	 *
	 * This prevents conflicts with the game scripts.
	 *
	 * @return void
	 */
	public function deregister_assets(): void {
		if ( 'charigame-campaign' !== get_post_type() ) {
			return;
		}

		global $wp_scripts, $wp_styles;

		$script_exceptions = array( 'borlabs-cookie', 'borlabs-cookie-prioritize' );
		$style_exceptions  = array( 'borlabs-cookie' );

		foreach ( $wp_scripts->registered as $script ) {
			if ( ! str_contains( $script->src, '/wp-admin/' ) && ! in_array( $script->handle, $script_exceptions, true ) ) {
				wp_deregister_script( $script->handle );
			}
		}

		foreach ( $wp_styles->registered as $style ) {
			if ( ! str_contains( $style->src, '/wp-admin/' ) && ! in_array( $style->handle, $style_exceptions, true ) ) {
				wp_deregister_style( $style->handle );
			}
		}
	}

	/**
	 * Enqueue frontend scripts and styles for the games
	 *
	 * @return void
	 */
	public function enqueue_assets(): void {
		if ( 'charigame-campaign' !== get_post_type() ) {
			return;
		}

		$plugin_version = CHARIGAME_VERSION;
		$plugin_url     = plugin_dir_url( dirname( __DIR__ ) ) . 'charigame/';

		wp_enqueue_style( 'elancer-donate-games-styles', $plugin_url . 'dist/styles.css', array(), $plugin_version );

		wp_enqueue_script( 'jquery', 'https://code.jquery.com/jquery-3.7.1.min.js', array(), '3.7.1' );

		$campaign_id           = get_the_ID();
		$donation_distribution = ChariGame_Donation_Manager::get_donation_distribution_by_campaign_id( $campaign_id );
		$recipients            = ChariGame_Donation_Manager::get_all_recipients_data_by_campaign_id( $campaign_id );

		$game_settings_id = Helper::get_game_settings_id_by_campaign_id( $campaign_id );

		$logo = wp_get_attachment_image_url( carbon_get_post_meta( $game_settings_id, 'login_form_logo' ) );

		$color_manager = ChariGame_Color_Manager::get_instance();
		$colors        = $color_manager->get_campaign_colors( $campaign_id );

		if ( file_exists( plugin_dir_path( dirname( __DIR__ ) ) . 'charigame/src/games/helper.js' ) ) {
			wp_enqueue_script( 'elancer-helper', $plugin_url . 'src/games/helper.js', array(), $plugin_version );

			foreach ( $recipients as $key => $recipient ) {
				if ( ! empty( $recipient['logo'] ) && empty( $recipient['logo_url'] ) ) {
					$logo_url = wp_get_attachment_image_url( $recipient['logo'], 'thumbnail' );
					$recipients[ $key ]['logo_url'] = $logo_url ? $logo_url : '';
				}
			}

			$memory_image_urls = array();
			$pairs_count       = 0;

			if ( $game_settings_id ) {
				$image_ids = carbon_get_post_meta( $game_settings_id, 'memory_images' );

				if ( is_array( $image_ids ) ) {
					foreach ( $image_ids as $img_id ) {
						$url = wp_get_attachment_url( $img_id );
						if ( $url ) {
							$memory_image_urls[] = $url;
						}
					}
				}

				$pairs_count_meta = carbon_get_post_meta( $game_settings_id, 'pairs_count' );
				$pairs_count = $pairs_count_meta ? intval( $pairs_count_meta ) : 0;
			}

			wp_localize_script(
				'elancer-helper',
				'helper_vars',
				array(
					'campaign_id'     => $campaign_id,
					'dist'            => $donation_distribution,
					'recipients'      => $recipients,
					'logo'            => $logo,
					'memory_images'   => $memory_image_urls,
					'pairs_count'     => $pairs_count,
					'primary_color'   => $colors['primary'],
					'secondary_color' => $colors['secondary'],
					'tertiary_color'  => $colors['tertiary'],
					'nonce'           => wp_create_nonce( 'charigame_nonce' ),
					'plugin_path'     => $plugin_url,
				)
			);
		}

		if ( file_exists( plugin_dir_path( dirname( __DIR__ ) ) . 'charigame/src/games/picker.js' ) ) {
			wp_enqueue_script( 'elancer-donate-picker', $plugin_url . 'src/games/picker.js', array(), $plugin_version );
		}

		if ( file_exists( plugin_dir_path( dirname( __DIR__ ) ) . 'charigame/src/games/confetti.min.js' ) ) {
			wp_enqueue_script( 'confetti-js', $plugin_url . 'src/games/confetti.min.js', array(), $plugin_version );
		}

		if ( file_exists( plugin_dir_path( dirname( __DIR__ ) ) . 'charigame/js/gsap/gsap_3.12.5.min.js' ) ) {
			wp_enqueue_script( 'gsap-js', $plugin_url . 'js/gsap/gsap_3.12.5.min.js' );

			if ( file_exists( plugin_dir_path( dirname( __DIR__ ) ) . 'charigame/js/animation.js' ) ) {
				wp_enqueue_script( 'animation-js', $plugin_url . 'js/animation.js', array( 'gsap-js' ) );
			}

			if ( file_exists( plugin_dir_path( dirname( __DIR__ ) ) . 'charigame/js/gsap/observer_3.12.5.min.js' ) ) {
				wp_enqueue_script( 'gsap-observer', $plugin_url . 'js/gsap/observer_3.12.5.min.js', array( 'gsap-js' ) );
			}

			if ( file_exists( plugin_dir_path( dirname( __DIR__ ) ) . 'charigame/js/gsap/scrolltrigger_3.12.5.min.js' ) ) {
				wp_enqueue_script( 'gsap-scrolltrigger', $plugin_url . 'js/gsap/scrolltrigger_3.12.5.min.js', array( 'gsap-js' ) );
			}

			if ( file_exists( plugin_dir_path( dirname( __DIR__ ) ) . 'charigame/js/gsap/motionpathplugin_3.12.5.min.js' ) ) {
				wp_enqueue_script( 'gsap-motionpath', $plugin_url . 'js/gsap/motionpathplugin_3.12.5.min.js', array( 'gsap-js' ) );
			}

			if ( file_exists( plugin_dir_path( dirname( __DIR__ ) ) . 'charigame/js/gsap/draggable_3.12.5.min.js' ) ) {
				wp_enqueue_script( 'gsap-draggable', $plugin_url . 'js/gsap/draggable_3.12.5.min.js', array( 'gsap-js' ) );
			}
		}

		$current_game_type = carbon_get_the_post_meta( 'game_type' );

		if ( $current_game_type === 'memory' && file_exists( plugin_dir_path( dirname( __DIR__ ) ) . 'charigame/src/games/memory/game.js' ) ) {
			wp_enqueue_script( 'elancer-memory-game', $plugin_url . 'src/games/memory/game.js', array(), $plugin_version );
			wp_localize_script(
				'elancer-memory-game',
				'myAjax',
				array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) )
			);

			$memory_images = carbon_get_post_meta( $game_settings_id, 'memory_images' );
			$memory_images = is_array( $memory_images ) ? $memory_images : array();

			$cardback_image = carbon_get_post_meta( $game_settings_id, 'login_form_logo' );
			$cardback_url = $cardback_image ? wp_get_attachment_image_url( $cardback_image ) : '';

			wp_localize_script(
				'elancer-memory-game',
				'my_plugin_vars',
				array(
					'cardback'    => $cardback_url,
					'plugin_path' => $plugin_url,
					'image_array' => $memory_images,
				)
			);
		}

		if ( $current_game_type === 'tower' ) {
			if ( file_exists( plugin_dir_path( dirname( __DIR__ ) ) . 'charigame/src/games/tower/three_r134.min.js' ) ) {
				wp_enqueue_script( 'three-js', $plugin_url . 'src/games/tower/three_r134.min.js' );
			}

			if ( file_exists( plugin_dir_path( dirname( __DIR__ ) ) . 'charigame/src/games/tower/game.js' ) ) {
				wp_enqueue_script( 'elancer-tower-game', $plugin_url . 'src/games/tower/game.js', array( 'gsap-js' ), $plugin_version );

				$tower_logo_id = carbon_get_post_meta( $game_settings_id, 'login_form_logo' );
				$tower_logo = $tower_logo_id ? wp_get_attachment_image_url( $tower_logo_id ) : '';

				wp_localize_script(
					'elancer-tower-game',
					'game_vars',
					array( 'logo' => $tower_logo )
				);
			}

			if ( file_exists( plugin_dir_path( dirname( __DIR__ ) ) . 'charigame/src/games/tower/tower-styles.css' ) ) {
				wp_enqueue_style( 'elancer-tower-game-styles', $plugin_url . 'src/games/tower/tower-styles.css', array(), $plugin_version );
			}
		}
	}


	/**
	 * Output the viewport meta tag for responsive design
	 *
	 * @return void
	 */
	public function output_meta_viewport(): void {
		if ( is_singular( 'charigame-campaign' ) ) {
			echo '<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n";
		}
	}

	/**
	 * Enqueue shadcn CSS for admin pages
	 *
	 * @return void
	 */
	public function enqueue_shadcn_css(): void {
		$css_min_path = plugin_dir_path( __DIR__ ) . 'admin/css/shadcn-ui.min.css';
		$css_path     = plugin_dir_path( __DIR__ ) . 'admin/css/shadcn-ui.css';

		if ( file_exists( $css_min_path ) ) {
			$css_url = plugin_dir_url( __DIR__ ) . 'admin/css/shadcn-ui.min.css';
		} else {
			$css_url = plugin_dir_url( __DIR__ ) . 'admin/css/shadcn-ui.css';
		}

		wp_enqueue_style(
			'shadcn-ui',
			$css_url,
			array(),
			CHARIGAME_VERSION
		);

		wp_enqueue_style(
			'shadcn-components',
			plugin_dir_url( __DIR__ ) . 'admin/css/shadcn-components.css',
			array( 'shadcn-ui' ),
			CHARIGAME_VERSION
		);

		$css_variables = '
			/* Define theme only for charigame scope, not globally. */
			body.toplevel_page_charigame-types,
			body[class*="charigame-"] {
				/* Standard WordPress blue with light gray background. */
				--background: 0 0% 97%;
				--foreground: 222.2 84% 4.9%;
				--card: 0 0% 100%;
				--card-foreground: 222.2 84% 4.9%;
				--popover: 0 0% 100%;
				--popover-foreground: 222.2 84% 4.9%;
				--primary: 210 100% 40%; /* WordPress blue #2271b1. */
				--primary-foreground: 0 0% 100%;
				--secondary: 205 30% 95%;
				--secondary-foreground: 210 30% 20%;
				--muted: 210 20% 96%;
				--muted-foreground: 215 15% 45%;
				--accent: 210 20% 96%;
				--accent-foreground: 222.2 47.4% 11.2%;
				--destructive: 0 84.2% 60.2%;
				--destructive-foreground: 0 0% 100%;
				--border: 214.3 31.8% 91.4%;
				--input: 214.3 31.8% 91.4%;
				--ring: 210 100% 40%; /* WordPress blue #2271b1. */
				--radius: 0.5rem;
			}
			}

			/* WordPress admin override styles. */
			.wrap .shadcn-table {
				width: 100% !important;
				border-collapse: collapse !important;
				font-size: 0.875rem !important;
				margin-bottom: 1rem !important;
			}

			.wrap .shadcn-table th,
			.wrap .shadcn-table td {
				padding: 0.75rem 1rem !important;
				vertical-align: middle !important;
			}

			.wrap .shadcn-card {
				background-color: white !important;
				padding: 0 !important;
			}

			/* Apply the theme only to ChariGame plugin areas. */
			body.toplevel_page_charigame-types #wpcontent,
			body[class*="charigame-"] #wpcontent {
				background-color: hsl(var(--background)) !important;
			}

			/* WordPress standard blue buttons for ChariGame pages. */
			body.toplevel_page_charigame-types .button-primary,
			body[class*="charigame-"] .button-primary,
			.shadcn-btn-primary {
				background-color: #2271b1 !important;
				border-color: #2271b1 !important;
			}

			body.toplevel_page_charigame-types .button-primary:hover,
			body[class*="charigame-"] .button-primary:hover,
			.shadcn-btn-primary:hover {
				background-color: #135e96 !important;
				border-color: #135e96 !important;
			}

			/* Override link colors only in ChariGame plugin pages. */
			body.toplevel_page_charigame-types #wpbody a,
			body[class*="charigame-"] #wpbody .edit a,
			body[class*="charigame-"] #wpbody .view a,
			body[class*="charigame-"] #wpbody a.row-title,
			.shadcn-card a {
				color: #2271b1 !important; /* WordPress standard blue. */
			}

			body[class*="charigame-"] #wpbody .row-actions span.trash a{
			    color: #b32d2e !important;
			}

			body.toplevel_page_charigame-types #wpbody a:hover,
			body[class*="charigame-"] #wpbody a:hover,
			.shadcn-card a:hover {
				color: #135e96 !important; /* WordPress darker blue. */
				text-decoration: underline;
			}
		';

		wp_add_inline_style( 'shadcn-ui', $css_variables );
	}
}
