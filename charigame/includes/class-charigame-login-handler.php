<?php
/**
 * ChariGame Login Handler
 *
 * Handles user authentication via game codes and session management.
 *
 * @package ChariGame
 * @subpackage Includes
 * @since 1.0.0
 */

namespace ChariGame\Includes;

/**
 * Login Handler Class
 *
 * Manages login processes, session handling, and game code validation.
 */
class ChariGame_Login_Handler {

	/**
	 * Register hooks and actions.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'wp_ajax_charigame_login', array( $this, 'handle_login' ) );
		add_action( 'wp_ajax_nopriv_charigame_login', array( $this, 'handle_login' ) );
		add_action( 'plugins_loaded', array( $this, 'start_session' ), 1 );
		add_action( 'template_redirect', array( $this, 'handle_code_parameter' ) );
	}

	/**
	 * Start a PHP session if one doesn't exist already.
	 *
	 * @return void
	 */
	public function start_session(): void {
		if ( ! session_id() && ! headers_sent() ) {
			session_start();
		}
	}

	/**
	 * Handle AJAX login requests.
	 *
	 * Validates the game code and sets up session variables on successful login.
	 *
	 * @return void
	 */
	public function handle_login(): void {
		if ( ! session_id() && ! headers_sent() ) {
			session_start();
		}

		// Nonce verification for security.
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'charigame_login_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed', 'charigame' ) ) );
		}

		$key         = sanitize_text_field( $_POST['charigame_key'] ?? '' );
		$campaign_id = intval( $_POST['campaign_id'] ?? 0 );

		if ( empty( $key ) ) {
			wp_send_json_error( array( 'message' => __( 'Key required', 'charigame' ) ) );
		}

		if ( ! $campaign_id ) {
			wp_send_json_error( array( 'message' => __( 'Campaign ID required', 'charigame' ) ) );
		}

		$user = $this->validate_key( $key );

		if ( ! $user ) {
			wp_send_json_error( array( 'message' => __( 'Invalid key', 'charigame' ) ) );
		}

		// Set session variables.
		$_SESSION['charigame_key']         = $_POST['charigame_key'];
		$_SESSION['charigame_user_id']     = $user->ID;
		$_SESSION['charigame_campaign_id'] = $campaign_id;

		// For AJAX requests: Trigger redirection to the campaign page.
		// This solves the problem with missing scripts and assets.
		$campaign_permalink = get_permalink( $campaign_id );

		wp_send_json_success(
			array(
				'redirect' => $campaign_permalink,
				'message'  => __( 'Login successful. Redirecting...', 'charigame' ),
			)
		);
	}

	/**
	 * Validates a game code key and updates the code_used timestamp on first use.
	 *
	 * @param string $key The game code to validate.
	 * @return \WP_Post|null The user post if found, null otherwise.
	 */
	public function validate_key( string $key ): ?\WP_Post {
		global $wpdb;
		$table = $wpdb->prefix . 'charigame_game_data';

		// Check cache first.
		$cache_key = 'charigame_key_validate_' . md5( $key );
		$row       = wp_cache_get( $cache_key, 'charigame_keys' );

		if ( false === $row ) {
			// Not in cache, query the database.
			$row = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT email_address, code_used FROM $table WHERE game_code = %s LIMIT 1",
					$key
				)
			);

			// Cache the result for 1 hour.
			if ( $row ) {
				wp_cache_set( $cache_key, $row, 'charigame_keys', 3600 );
			}
		}

		if ( $row && ! empty( $row->email_address ) ) {
			// If the code has not been marked as used yet, set it now.
			if ( empty( $row->code_used ) ) {
				// Use correct timezone with WordPress settings.
				$now          = new \DateTime( 'now', wp_timezone() );
				$current_time = $now->format( 'Y-m-d H:i:s' );

				$result = $wpdb->update(
					$table,
					array(
						'code_used' => $current_time,
					),
					array(
						'game_code' => $key,
					)
				);

				// Log errors for debugging if update fails.
				if ( $result === false ) {
					error_log( "ChariGame: Failed to update code_used timestamp for game code: $key" );
				} else {
					// Invalidate cache after successful update.
					wp_cache_delete( $cache_key, 'charigame_keys' );
				}
			}

			// Cache key for user lookup.
			$user_cache_key = 'charigame_user_by_email_' . md5( $row->email_address );
			$users          = wp_cache_get( $user_cache_key, 'charigame_users' );

			if ( false === $users ) {
				// Search for the user CPT with matching email address.
				$users = get_posts(
					array(
						'post_type'      => 'charigame-user',
						'meta_query'     => array(
							array(
								'key'     => 'email',
								'value'   => $row->email_address,
								'compare' => '=',
							),
						),
						'posts_per_page' => 1,
					)
				);

				// Cache user for 1 hour.
				wp_cache_set( $user_cache_key, $users, 'charigame_users', 3600 );
			}

			return $users[0] ?? null;
		}

		return null;
	}

	/**
	 * Get the landing page content for a campaign.
	 *
	 * @param int $campaign_id The campaign post ID.
	 * @return string The HTML content for the landing page.
	 */
	public function get_landing_page_content( int $campaign_id ): string {
		// Use Carbon Fields correctly.
		$linked_landing_page = carbon_get_post_meta( $campaign_id, 'linked_landing_page' );

		if ( ! empty( $linked_landing_page ) && isset( $linked_landing_page[0] ) ) {
			$landing_page_id = $linked_landing_page[0]['id'];

			if ( $landing_page_id && is_numeric( $landing_page_id ) ) {
				// Check if the landing page exists.
				$landing_page = get_post( $landing_page_id );
				if ( $landing_page && $landing_page->post_status === 'publish' ) {
					// Output content with campaign context.
					$content = $this->render_landing_page_with_context( $landing_page_id, $campaign_id );

					return $content;
				}
			}
		}

		// Fallback: Standard message.
		return '<div class="charigame-landing-content">
                    <h2>' . esc_html__( 'Welcome!', 'charigame' ) . '</h2>
                    <p>' . esc_html__( 'You have successfully registered.', 'charigame' ) . '</p>
                </div>';
	}

	/**
	 * Render the landing page content with campaign context.
	 *
	 * @param int $landing_page_id The landing page post ID.
	 * @param int $campaign_id The campaign post ID.
	 * @return string The rendered HTML content.
	 */
	private function render_landing_page_with_context( int $landing_page_id, int $campaign_id ): string {
		// Load campaign-specific styles.
		$campaign_styles = $this->get_campaign_styles( $campaign_id );

		// Landing page content.
		$content = apply_filters( 'the_content', get_post_field( 'post_content', $landing_page_id ) );

		// Wrap with campaign context.
		$output  = '<div class="charigame-landing-content" data-campaign-id="' . $campaign_id . '">';
		$output .= '<div id="hidden-code" data-gamecode="' . $_SESSION['charigame_key'] . '">';
		$output .= $campaign_styles;
		$output .= $content;
		$output .= '</div>';

		return $output;
	}

	/**
	 * Get the campaign-specific CSS styles.
	 *
	 * @param int $campaign_id The campaign post ID.
	 * @return string The CSS styles as a style tag.
	 */
	private function get_campaign_styles( int $campaign_id ): string {
		$primary_color   = carbon_get_post_meta( $campaign_id, 'primary_color' );
		$secondary_color = carbon_get_post_meta( $campaign_id, 'secondary_color' );
		$tertiary_color  = carbon_get_post_meta( $campaign_id, 'tertiary_color' );
		$success_color   = carbon_get_post_meta( $campaign_id, 'success_color' );
		$warning_color   = carbon_get_post_meta( $campaign_id, 'warning_color' );
		$error_color     = carbon_get_post_meta( $campaign_id, 'error_color' );

		return '<style>
            .charigame-landing-content {
                --primary-color: ' . ( $primary_color ?: '#3B82F6' ) . ';
                --secondary-color: ' . ( $secondary_color ?: '#10B981' ) . ';
                --tertiary-color: ' . ( $tertiary_color ?: '#F59E0B' ) . ';
                --success-color: ' . ( $success_color ?: '#22C55E' ) . ';
                --warning-color: ' . ( $warning_color ?: '#F59E0B' ) . ';
                --error-color: ' . ( $error_color ?: '#EF4444' ) . ';
            }
        </style>';
	}

	/**
	 * Check if a user is already logged in.
	 *
	 * @param int|null $campaign_id Optional campaign ID to check against.
	 * @return bool True if the user is logged in, false otherwise.
	 */
	public function is_user_logged_in( ?int $campaign_id = null ): bool {
		if ( ! session_id() && ! headers_sent() ) {
			session_start();
		}

		return isset( $_SESSION['charigame_user_id'] ) &&
				( ! $campaign_id || $_SESSION['charigame_campaign_id'] === $campaign_id );
	}

	/**
	 * Handles auto-login via URL code parameter.
	 *
	 * Processes the 'code' parameter from the URL for automatic login.
	 *
	 * @return void
	 */
	public function handle_code_parameter(): void {
		global $post;

		// Only process on campaign pages.
		if ( ! is_singular( 'charigame-campaign' ) ) {
			return;
		}

		// Check if code parameter exists.
		if ( isset( $_GET['code'] ) && ! empty( $_GET['code'] ) ) {
			$code        = sanitize_text_field( $_GET['code'] );
			$campaign_id = get_the_ID();

			// Start session if not already started.
			if ( ! session_id() && ! headers_sent() ) {
				session_start();
			}

			// Check if already logged in to this campaign.
			if ( isset( $_SESSION['charigame_user_id'] ) && $_SESSION['charigame_campaign_id'] === $campaign_id ) {
				// Already logged in, do nothing.
				return;
			}

			// Create a cache key based on the game code.
			$cache_key = 'charigame_code_' . md5( $code );

			// Try to get the user from cache first.
			$user = wp_cache_get( $cache_key, 'charigame_codes' );

			if ( false === $user ) {
				// Not in cache, validate the key.
				$user = $this->validate_key( $code );

				if ( $user ) {
					// Cache the result for 1 hour (3600 seconds).
					wp_cache_set( $cache_key, $user, 'charigame_codes', 3600 );
				}
			}

			if ( $user ) {
				// Set session variables.
				$_SESSION['charigame_key']         = $code;
				$_SESSION['charigame_user_id']     = $user->ID;
				$_SESSION['charigame_campaign_id'] = $campaign_id;

				// Redirect to the same page without the code parameter to avoid issues with refreshing.
				wp_redirect( get_permalink( $campaign_id ) );
				exit;
			}
		}
	}
}
