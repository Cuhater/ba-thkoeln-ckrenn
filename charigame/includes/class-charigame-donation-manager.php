<?php
/**
 * ChariGame Donation Manager
 *
 * Manages donation amounts and statistics for ChariGame.
 * Implements efficient storage and calculation of donation amounts.
 *
 * @package ChariGame
 * @subpackage Includes
 * @since 1.0.0
 */

namespace ChariGame\Includes;
/**
 * Donation Manager Class
 *
 * Handles donation tracking, statistics, and related functionality.
 */
class ChariGame_Donation_Manager {

	/**
	 * Register hooks and actions.
	 *
	 * @return void
	 */
	public function register(): void {
		register_activation_hook( CHARIGAME_PLUGIN_FILE, array( $this, 'create_tables' ) );

		add_action( 'wp_ajax_charigame_save_score', array( $this, 'handle_save_score' ) );
		add_action( 'wp_ajax_nopriv_charigame_save_score', array( $this, 'handle_save_score' ) );

		add_action( 'wp_ajax_charigame_set_last_played', array( $this, 'handle_set_last_played' ) );
		add_action( 'wp_ajax_nopriv_charigame_set_last_played', array( $this, 'handle_set_last_played' ) );

		add_action( 'wp_ajax_charigame_get_donations', array( $this, 'handle_get_donations' ) );
		add_action( 'wp_ajax_nopriv_charigame_get_donations', array( $this, 'handle_get_donations' ) );

		add_action( 'wp_ajax_charigame_get_frontend_data', array( $this, 'handle_get_frontend_data' ) );
		add_action( 'wp_ajax_nopriv_charigame_get_frontend_data', array( $this, 'handle_get_frontend_data' ) );

		add_action( 'wp_ajax_update_donations', array( __CLASS__, 'update_donations' ) );
		add_action( 'wp_ajax_nopriv_update_donations', array( __CLASS__, 'update_donations' ) );
    }

	/**
	 * Create the required database tables.
	 *
	 * @return void
	 */
    public function create_tables(): void {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $game_data_table = $wpdb->prefix . 'charigame_game_data';

        $campaign_stats_table = $wpdb->prefix . 'charigame_campaign_stats';

        $sql_campaign_stats = "CREATE TABLE $campaign_stats_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            campaign_id bigint(20) NOT NULL,
            recipient_id bigint(20) NOT NULL,
            donation_amount decimal(10,2) NOT NULL DEFAULT 0.00,
            games_played int NOT NULL DEFAULT 0,
            last_updated datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY campaign_recipient (campaign_id, recipient_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_campaign_stats);
    }

	/**
	 * Save a game result and update statistics.
	 *
	 * @return void
	 */
    public function handle_save_score(): void {

        if (!check_ajax_referer('charigame_nonce', 'nonce', false)) {
            wp_send_json_error(['message' => 'Security check failed']);
            wp_die();
        }


        $game_code = sanitize_text_field($_POST['code'] ?? '');
        $highscore = intval($_POST['highscore'] ?? 0);
        $last_played = intval($_POST['last_played'] ?? 0);
        $recipient_1 = floatval($_POST['recipient_1'] ?? 0);
        $recipient_2 = floatval($_POST['recipient_2'] ?? 0);
        $recipient_3 = floatval($_POST['recipient_3'] ?? 0);
        $campaign_id = intval($_POST['campaign_id'] ?? 0);

        if (empty($game_code)) {
            wp_send_json_error(['message' => 'Game code is required']);
        }

        $this->save_game_result($game_code, $highscore, $last_played, $recipient_1, $recipient_2, $recipient_3);

        if ($campaign_id) {
            $this->update_campaign_statistics($campaign_id, $highscore, $recipient_1, $recipient_2, $recipient_3);
        }

        $stats = $this->get_campaign_statistics($campaign_id);
        wp_send_json_success(['message' => 'Score saved successfully', 'statistics' => $stats]);
    }

	/**
	 * Return donation statistics for a campaign.
	 *
	 * @return void
	 */
    public function handle_get_donations(): void {
        if (!check_ajax_referer('charigame_nonce', 'nonce', false)) {
            wp_send_json_error(['message' => 'Security check failed']);
            wp_die();
        }

        $campaign_id = intval($_POST['campaign_id'] ?? 0);

        if (!$campaign_id) {
            wp_send_json_error(['message' => 'Campaign ID is required']);
        }

        $stats = $this->get_campaign_statistics($campaign_id);
        wp_send_json_success($stats);
    }

	/**
	 * Provide all frontend data (recipients, donation distribution, colors, logo, nonce)
	 * for a campaign. Used when content is injected into landing pages via AJAX
	 * and normal enqueue/localize processes do not apply.
	 *
	 * @return void
	 */
    public function handle_get_frontend_data(): void {
        $campaign_id = intval($_POST['campaign_id'] ?? 0);
        if (!$campaign_id) {
            wp_send_json_error(['message' => 'Campaign ID is required']);
        }

        $donation_distribution = self::get_donation_distribution_by_campaign_id($campaign_id);
        $recipients = self::get_all_recipients_data_by_campaign_id($campaign_id);

        $game_settings_id = Helper::get_game_settings_id_by_campaign_id($campaign_id);
        $logo = $game_settings_id ? (wp_get_attachment_image_url(carbon_get_post_meta($game_settings_id, 'login_form_logo')) ?: '') : '';

        $memory_images = [];
        $pairs_count = 0;
        if ($game_settings_id) {
            $image_ids = carbon_get_post_meta($game_settings_id, 'memory_images');
            if (is_array($image_ids)) {
                foreach ($image_ids as $img_id) {
                    $url = wp_get_attachment_url($img_id);
                    if ($url) $memory_images[] = $url;
                }
            }
            $pairs_count = intval(carbon_get_post_meta($game_settings_id, 'pairs_count')) ?: 0;
        }

        $color_manager = ChariGame_Color_Manager::get_instance();
        $colors = $color_manager->get_campaign_colors($campaign_id);

        foreach ($recipients as $key => $recipient) {
            if (!empty($recipient['logo']) && empty($recipient['logo_url'])) {
                $recipients[$key]['logo_url'] = wp_get_attachment_image_url($recipient['logo'], 'thumbnail') ?: '';
            }
        }

        $payload = [
            'campaign_id'     => $campaign_id,
            'dist'            => $donation_distribution,
            'recipients'      => $recipients,
            'logo'            => $logo,
            'memory_images'   => $memory_images,
            'pairs_count'     => $pairs_count,
            'primary_color'   => $colors['primary'] ?? '#3B82F6',
            'secondary_color' => $colors['secondary'] ?? '#10B981',
            'teritary_color'  => $colors['tertiary'] ?? '#F59E0B',
            'nonce'           => wp_create_nonce('charigame_nonce'),
        ];

        wp_send_json_success($payload);
    }

	/**
	 * Save a game result in the database.
	 *
	 * @param string $game_code   The game code.
	 * @param int    $highscore   The player's highscore.
	 * @param int    $last_played The timestamp when the game was played.
	 * @param float  $recipient_1 Percentage for first recipient.
	 * @param float  $recipient_2 Percentage for second recipient.
	 * @param float  $recipient_3 Percentage for third recipient.
	 * @return bool True on success, false on failure.
	 */
    private function save_game_result(string $game_code, int $highscore, int $last_played, float $recipient_1, float $recipient_2, float $recipient_3): bool {
        global $wpdb;
        $table_name = $wpdb->prefix . 'charigame_game_data';

        // Convert timestamp to MySQL datetime with correct timezone
        $date = new \DateTime();
        $date->setTimestamp(intval($last_played / 1000));
        $date->setTimezone(wp_timezone()); // Use WordPress timezone setting
        $sqlTimestamp = $date->format('Y-m-d H:i:s');

        $result = $wpdb->update(
            $table_name,
            [
                'highscore' => $highscore,
                'last_played' => $sqlTimestamp,
                'recipient_1' => $recipient_1,
                'recipient_2' => $recipient_2,
                'recipient_3' => $recipient_3,
            ],
            [
                'game_code' => $game_code
            ]
        );

        return $result !== false;
    }

	/**
	 * Update campaign statistics for the specified campaign.
	 *
	 * @param int   $campaign_id The campaign ID.
	 * @param int   $highscore   The player's highscore.
	 * @param float $recipient_1 Percentage for first recipient.
	 * @param float $recipient_2 Percentage for second recipient.
	 * @param float $recipient_3 Percentage for third recipient.
	 * @return void
	 */
    private function update_campaign_statistics(int $campaign_id, int $highscore, float $recipient_1, float $recipient_2, float $recipient_3): void {
        global $wpdb;
        $stats_table = $wpdb->prefix . 'charigame_campaign_stats';
        $now = current_time('mysql');

        $donation_amount = $this->calculate_donation_amount($campaign_id, $highscore);

        $amounts = [
            $recipient_1 * $donation_amount / 100,
            $recipient_2 * $donation_amount / 100,
            $recipient_3 * $donation_amount / 100
        ];

        $recipients = $this->get_campaign_recipients($campaign_id);

        foreach ($recipients as $index => $recipient_id) {
            if (isset($amounts[$index]) && $recipient_id) {
                $wpdb->query($wpdb->prepare(
                    "INSERT INTO $stats_table
                    (campaign_id, recipient_id, donation_amount, games_played, last_updated)
                    VALUES (%d, %d, %f, 1, %s)
                    ON DUPLICATE KEY UPDATE
                    donation_amount = donation_amount + %f,
                    games_played = games_played + 1,
                    last_updated = %s",
                    $campaign_id, $recipient_id, $amounts[$index], $now, $amounts[$index], $now
                ));
                if ($wpdb->last_error) {
                    error_log('wpdb error: ' . $wpdb->last_error);
                }
            }
        }
    }

	/**
	 * Get donation statistics for a campaign.
	 *
	 * @param int $campaign_id The campaign ID.
	 * @return array Array of statistics data.
	 */
    public static function get_campaign_statistics(int $campaign_id): array {
        global $wpdb;
        $stats_table = $wpdb->prefix . 'charigame_campaign_stats';

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT recipient_id, donation_amount, games_played
            FROM $stats_table
            WHERE campaign_id = %d",
            $campaign_id
        ), ARRAY_A);

        $stats = [
            'total_donation' => 0,
            'total_games' => 0,
            'recipients' => []
        ];

        foreach ($results as $row) {
            $donation_amount = floatval($row['donation_amount']);

            $stats['total_donation'] += $donation_amount;
            $stats['total_games'] += intval($row['games_played']);

            $recipient_id = intval($row['recipient_id']);
            $recipient_data = self::get_recipient_data($recipient_id);

            $stats['recipients'][$recipient_id] = [
                'id' => $recipient_id,
                'donation_amount' => $donation_amount,
                'title' => $recipient_data['title'] ?: get_the_title($recipient_id),
                'description' => $recipient_data['description'] ?? '',
                'logo' => wp_get_attachment_image_url($recipient_data['logo'], 'thumbnail') ?: '',
                'percentage' => 0
            ];
        }

        if ($stats['total_donation'] > 0) {
            foreach ($stats['recipients'] as &$recipient) {
                $recipient['percentage'] = round(($recipient['donation_amount'] / $stats['total_donation']) * 100, 0);
            }
        }

        return $stats;
    }

	/**
	 * Calculate the donation amount based on highscore and campaign settings.
	 *
	 * @param int $campaign_id The campaign ID.
	 * @param int $highscore   The player's highscore.
	 * @return float The calculated donation amount.
	 */
    private function calculate_donation_amount(int $campaign_id, int $highscore): float {
        $config = carbon_get_post_meta($campaign_id, 'donation_distribution_group');
        if (!$config) {
            return 0;
        }

        $win_categories = $config[0]['win_categories'] ?? [];
        $compare_greater = !($config[0]['highscore'] ?? false);

        foreach ($win_categories as $category) {
            $limit = isset($category['points_limit']) ? (float)$category['points_limit'] : 0;
            $amount = isset($category['donation_amount']) ? (float)$category['donation_amount'] : 0;

            $amount = round($amount, 0);

            if ($compare_greater && $highscore >= $limit) {
                return $amount;
            } elseif (!$compare_greater && $highscore <= $limit) {
                return $amount;
            }
        }

        return 0;
    }

	/**
	 * Get recipient IDs for a campaign.
	 *
	 * @param int $campaign_id The campaign ID.
	 * @return array Array of recipient IDs.
	 */
    public static function get_campaign_recipients(int $campaign_id): array {
        $recipients = [];
        $recipients_field = carbon_get_post_meta($campaign_id, 'recipients');
        if (is_array($recipients_field)) {
            foreach ($recipients_field as $row) {
                foreach ($row['recipient'] as $recipient) {
                    if (!empty($recipient['id'])) {
                        $recipients[] = $recipient['id'];
                    }
                }
            }
        }
        return $recipients;
    }

	/**
	 * Reset statistics for a campaign.
	 *
	 * @param int $campaign_id The campaign ID.
	 * @return bool True on success, false on failure.
	 */
    public function reset_campaign_statistics(int $campaign_id): bool {
        global $wpdb;
        $stats_table = $wpdb->prefix . 'charigame_campaign_stats';

        return $wpdb->delete($stats_table, ['campaign_id' => $campaign_id], ['%d']) !== false;
    }

	/**
	 * Get data for a recipient.
	 *
	 * @param int $recipient_id The recipient ID.
	 * @return array Recipient data.
	 */
    public static function get_recipient_data(int $recipient_id): array {
        return [
            'id' => $recipient_id,
            'title' => carbon_get_post_meta($recipient_id, 'recipient_name'),
            'description' => carbon_get_post_meta($recipient_id, 'recipient_description'),
            'logo' => carbon_get_post_meta($recipient_id, 'recipient_logo'),
        ];
    }

	/**
	 * Get data for all recipients by campaign ID.
	 *
	 * @param int $campaign_id The campaign ID.
	 * @return array Array of recipient data.
	 */
	public static function get_all_recipients_data_by_campaign_id( int $campaign_id ): array {
		$recipients_data = [];
		$recipients = carbon_get_post_meta( $campaign_id, 'recipients' );
		if (!is_array($recipients)) {
			return $recipients_data;
		}

		foreach ( $recipients as  $row ) {
			foreach ( $row['recipient'] as $i => $recipient ) {
				$logo_id = carbon_get_post_meta( $recipient['id'], 'recipient_logo' );
				$logo_url = wp_get_attachment_image_url($logo_id, 'thumbnail') ?: '';

				$recipients_data['recipient_' . ($i + 1)] = [
					'title'       => carbon_get_post_meta( $recipient['id'], 'recipient_name' ),
					'description' => carbon_get_post_meta( $recipient['id'], 'recipient_description' ),
					'logo'        => $logo_id,
					'logo_url'    => $logo_url,
				];
			}
		}
		return $recipients_data;
	}

	/**
     * Gibt die Spendenverteilung für eine Kampagne zurück
     *
     * @param int $campaign_id ID der Kampagne
     * @return array Array mit Spendenlimits und -beträgen
     */
	public static function get_donation_distribution_by_campaign_id( int $campaign_id ) : array {
		$donation_distribution = [];
		if ($campaign_id) {
			$game_settings_dist_group = carbon_get_post_meta($campaign_id, 'donation_distribution_group');
			if (isset($game_settings_dist_group[0]['win_categories'])) {
				foreach ($game_settings_dist_group[0]['win_categories'] as $cat) {
					$donation_distribution[] = [
						'limit' => isset($cat['points_limit']) ? (float)$cat['points_limit'] : 0,
						'spendenbetrag' => isset($cat['donation_amount']) ? round((float)$cat['donation_amount'], 0) : 0,
					];
				}
			}
		}
		return $donation_distribution;
	}

	/**
     * AJAX-Handler für das Aktualisieren der Spenden
     */
	public static function update_donations() {
		if ( ! isset( $_POST['campaign_id'] ) || ! is_numeric( $_POST['campaign_id'] ) ) {
			wp_send_json_error( [ 'message' => 'Invalid campaign ID' ] );
		}
		$campaign_id = intval( $_POST['campaign_id'] );
		$donations = self::get_campaign_statistics( $campaign_id );
		if ( $donations ) {
			wp_send_json_success( $donations );
		} else {
			wp_send_json_error( [ 'message' => 'No donations found' ] );
		}
	}

	/**
	 * AJAX Handler zum Setzen des last_played Timestamps wenn ein Spiel gestartet wird
	 *
	 * @return void
	 */
	public function handle_set_last_played(): void {
		if (!wp_verify_nonce($_POST['nonce'] ?? '', 'charigame_nonce')) {
			wp_send_json_error(['message' => 'Security check failed']);
		}

		$game_code = sanitize_text_field($_POST['code'] ?? '');
		$last_played = intval($_POST['last_played'] ?? 0);

		if (empty($game_code)) {
			wp_send_json_error(['message' => 'Game code is required']);
		}

		if ($last_played <= 0) {
			$last_played = time() * 1000;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'charigame_game_data';

		$date = new \DateTime();
		$date->setTimestamp(intval($last_played / 1000));
		$date->setTimezone(wp_timezone());
		$sqlTimestamp = $date->format('Y-m-d H:i:s');

		$result = $wpdb->update(
			$table_name,
			['last_played' => $sqlTimestamp],
			['game_code' => $game_code]
		);

		if ($result !== false) {
			wp_send_json_success(['message' => 'Last played timestamp updated']);
		} else {
			error_log("ChariGame: Failed to update last_played timestamp for game code: $game_code");
			wp_send_json_error(['message' => 'Failed to update timestamp']);
		}

		wp_die();
	}

}
