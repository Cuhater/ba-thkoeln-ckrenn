<?php
namespace ChariGame\Includes;

class Helper {

	/**
	 * Registriert AJAX-Hooks für die Highscore-Funktionalität
	 *
	 */
	public function register_ajax_hooks() : void {
		add_action( 'wp_ajax_set_user_highscore_db', array( 'ChariGame\Includes\Helper', 'set_user_highscore_db' ) );
		add_action( 'wp_ajax_nopriv_set_user_highscore_db', array( 'ChariGame\Includes\Helper', 'set_user_highscore_db' ) );
	}
	public static function get_all_campaigns() {

		$args = array(
			'post_type'      => 'charigame-campaign',
			'posts_per_page' => - 1,
		);
		return get_posts( $args );
	}
	public static function update_charigame_data_table( int $post_id ): void {
		$post          = get_post( $post_id );
		$campaign_name = $post->post_name;
		$user_posts    = new \WP_Query(
			array(
				'post_type'      => 'charigame-user',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			)
		);
		if ( $user_posts->have_posts() ) {
			while ( $user_posts->have_posts() ) {
				$user_posts->the_post();

				$email_address = carbon_get_post_meta( get_the_ID(), 'email' );
				$birthday      = carbon_get_post_meta( get_the_ID(), 'birthday' );

				if ( ! empty( $email_address ) && ! empty( $birthday ) ) {

					$game_type_slug = carbon_get_post_meta( $post->ID, 'game_type' );

					$valid_from_date  = carbon_get_post_meta( $post->ID, 'valid_from' );
					$valid_until_date = carbon_get_post_meta( $post->ID, 'valid_until' );

					$dispatch_date_option = carbon_get_post_meta( $post->ID, 'dispatch_date_option' );

					if ( $dispatch_date_option === 'dispatch' ) {
						$dispatch_date = carbon_get_post_meta( $post->ID, 'dispatch_date' );
						$valid_from    = $dispatch_date;
					} else {
						$valid_from = $birthday;
					}

					if ( ! empty( $valid_until_date ) ) {
						$valid_until = $valid_until_date;
					} else {

						$valid_from_weeks = carbon_get_post_meta( $post->ID, 'duration' ) ?: 4;
						$valid_until      = date( 'Y-m-d', strtotime( "+{$valid_from_weeks} weeks", strtotime( $valid_from ) ) );
					}

					$game_code = self::generate_unique_game_code( $email_address, $campaign_name );

					self::add_data_to_charigame_game_data_table(
						$campaign_name,
						$email_address,
						$game_type_slug,
						$game_code,
						$valid_from,
						$valid_until,
						null,
						null
					);
				}
			}
			wp_reset_postdata();
		}
	}
	public static function add_data_to_charigame_game_data_table( string $campaign_name, string $email_address, string $game_type, string $game_code, string $valid_from, string $valid_until, $last_played, $code_used ): void {

		global $wpdb;

		$table_name = $wpdb->prefix . 'charigame_game_data';
		if ( $code_used != null && $game_code != '' ) {
			$wpdb->update(
				$table_name,
				array(
					'code_used' => $code_used,
				),
				array(
					'game_code' => $game_code,
				)
			);

			return;
		}

		$valid_from  = gmdate( 'Y-m-d', strtotime( $valid_from ) );
		$valid_until = gmdate( 'Y-m-d', strtotime( $valid_until ) );

		$existing_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE email_address = %s AND game_type = %s AND campaign_name = %s", $email_address, $game_type, $campaign_name ) );

		if ( ! $existing_row ) {
			$data = array(
				'campaign_name' => $campaign_name,
				'email_address' => $email_address,
				'game_type'     => $game_type,
				'game_code'     => $game_code,
				'valid_from'    => $valid_from,
				'valid_until'   => $valid_until,
			);

			$wpdb->insert( $table_name, $data );
		} elseif ( $existing_row->valid_from !== $valid_from ) {
			$wpdb->update(
				$table_name,
				array(
					'valid_from'  => $valid_from,
					'valid_until' => $valid_until,
				),
				array(
					'email_address' => $email_address,
					'game_type'     => $game_type,
					'campaign_name' => $campaign_name,
				)
			);
		}
	}
	public static function delete_data_from_charigame_game_data_table(): void {
		global $wpdb;
		$table_name = $wpdb->prefix . 'charigame_game_data';
		$wpdb->query( "DELETE FROM $table_name" );
	}

	public static function delete_charigame_users_batch( int $batch_size = 100 ): int {
		$posts = get_posts(
			array(
				'post_type'      => 'charigame-user',
				'posts_per_page' => $batch_size,
				'post_status'    => 'any',
			)
		);

		foreach ( $posts as $post ) {
			wp_delete_post( $post->ID, true );
		}

		return count( $posts );
	}
	public static function set_user_highscore_db() {

		if ( ! wp_verify_nonce( $_POST['nonce'], 'nonce' ) ) {
			global $wpdb;

			$table_name  = $wpdb->prefix . 'edg_game_data';
			$gamecode    = $_POST['code'];
			$highscore   = intval( $_POST['highscore'] );
			$last_played = intval( $_POST['last_played'] );
			$recipient_1 = floatval( $_POST['recipient_1'] );
			$recipient_2 = floatval( $_POST['recipient_2'] );
			$recipient_3 = floatval( $_POST['recipient_3'] );
			$gamecode    = substr( $gamecode, 4, - 4 );

			$last_played  = $last_played / 1000;
			$sqlTimestamp = date( 'Y-m-d H:i:s', $last_played );

			$result = $wpdb->update(
				$table_name,
				array(
					'highscore'   => $highscore,
					'last_played' => $sqlTimestamp,
					'recipient_1' => $recipient_1,
					'recipient_2' => $recipient_2,
					'recipient_3' => $recipient_3,
				),
				array(
					'game_code' => $gamecode,
				)
			);

			if ( $result !== false ) {
				$response = array(
					'message' => 'Highscore successfully ' . $gamecode . ' . ' . $result,
				);
				wp_send_json( $response );
				echo 'Highscore successfully updated for ' . $gamecode;
			} else {
				$response = array(
					'message' => 'Failed to update highscore' . $gamecode . ' . ' . $highscore . ' . ',
				);
				wp_send_json( $response );
				echo 'Failed to update highscore' . $gamecode . $gamecode . ' . ' . $highscore . ' . ';
			}
		}
		wp_die();
	}
	/**
	 * Generiert einen eindeutigen Game Code basierend auf E-Mail und Kampagne
	 *
	 * @param string $email_address
	 * @param string $campaign_name
	 * @return string
	 */
	private static function generate_unique_game_code( string $email_address, string $campaign_name ): string {

		$unique_string = $email_address . '|' . $campaign_name;

		$hash = md5( $unique_string );

		$game_code = strtoupper( substr( $hash, 0, 12 ) );

		return $game_code;
	}

	public static function charigame_compute_valid_until( int $post_id ): array {

		if ( ! function_exists( 'carbon_get_post_meta' ) ) {
			return array(
				'valid_until'      => '',
				'valid_until_time' => '',
			);
		}

		$dispatch_option = carbon_get_post_meta( $post_id, 'dispatch_date_option' );
		$dispatch_date   = carbon_get_post_meta( $post_id, 'dispatch_date' );
		$campaign_start  = carbon_get_post_meta( $post_id, 'campaign_start' );
		$dispatch_time   = carbon_get_post_meta( $post_id, 'dispatch_time' );
		$duration_weeks  = (int) carbon_get_post_meta( $post_id, 'code_validity_duration' );

		$end_date = null;

		if ( $dispatch_option === 'birthday' && ! empty( $campaign_start ) ) {

			try {
				$dt = new \DateTime( $campaign_start, wp_timezone() );
				$dt->modify( '+1 year' );
				$end_date = $dt;
			} catch ( \Exception $e ) {
			}
		} elseif ( $dispatch_option === 'dispatch' && ! empty( $dispatch_date ) && $duration_weeks > 0 ) {

			try {
				$dt   = new \DateTime( $dispatch_date, wp_timezone() );
				$days = $duration_weeks * 7;
				$dt->modify( '+' . $days . ' days' );
				$end_date = $dt;
			} catch ( \Exception $e ) {
			}
		}

		$valid_until      = '';
		$valid_until_time = '';

		if ( $end_date instanceof \DateTime ) {
			$valid_until = $end_date->format( 'd.m.Y' );
		}
		if ( ! empty( $dispatch_time ) ) {
			$valid_until_time = $dispatch_time;
		}

		return array(
			'valid_until'      => $valid_until,
			'valid_until_time' => $valid_until_time,
		);
	}


	/**
	 * Gibt die Game Settings ID für eine bestimmte Kampagne zurück.
	 *
	 * Diese Funktion ermittelt den zugehörigen Game-Set Eintrag für eine Kampagne
	 * und gibt dessen ID zurück, damit mit Carbon Fields die korrekten Metadaten
	 * wie z.B. das Logo abgerufen werden können.
	 *
	 * @param int $campaign_id Die ID der Kampagne.
	 * @return int Die ID des zugehörigen Game-Set Eintrags oder 0 wenn nicht gefunden
	 */
	public static function get_game_settings_id_by_campaign_id( int $campaign_id ): int {

		$campaign = get_post( $campaign_id );
		if ( ! $campaign || $campaign->post_type !== 'charigame-campaign' ) {
			return 0;
		}

		$game_type = carbon_get_post_meta( $campaign_id, 'game_type' );
		if ( empty( $game_type ) ) {
			return 0;
		}

		$game_sets = get_posts(
			array(
				'post_type'      => 'charigame-game-set',
				'posts_per_page' => 1,
				'meta_query'     => array(
					array(
						'key'     => '_game_type',
						'value'   => $game_type,
						'compare' => '=',
					),
				),
			)
		);

		if ( ! empty( $game_sets ) ) {
			return $game_sets[0]->ID;
		}

		return 0;
	}
}
