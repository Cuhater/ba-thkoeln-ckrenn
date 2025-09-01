<?php
/**
 * ChariGame Activator
 *
 * Handles plugin activation tasks like registering post types and creating database tables.
 *
 * @package ChariGame
 * @subpackage Includes
 * @since 1.0.0
 */

namespace ChariGame;
class ChariGame_Activator {

	/**
	 * Activate the plugin.
	 *
	 * Loads required files, registers post types, creates database tables,
	 * and flushes rewrite rules.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function activate(): void {
		// Load CPTs & Admin Menu.
		require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/class-charigame-campaign.php';
		require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/class-charigame-user.php';
		require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/class-charigame-recipient.php';
		require_once plugin_dir_path( __FILE__ ) . '../admin/class-charigame-admin-menu.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-charigame-donation-manager.php';

		// Register Post Types.
		( new \ChariGame\PostTypes\ChariGame_Campaign() )->register();
		( new \ChariGame\PostTypes\ChariGame_User() )->register();
		( new \ChariGame\PostTypes\ChariGame_Recipient() )->register();
		( new \ChariGame\Admin\ChariGame_Admin_Menu() )->register_menu();

		// Automatically insert game types.
		self::insert_game_types();

		// Create game data table.
		self::create_game_data_table();

		// Create donation manager table.
		$donation_manager = new \ChariGame\Includes\ChariGame_Donation_Manager();
		$donation_manager->create_tables();

		// Update rewrites.
		flush_rewrite_rules();
	}
	/**
	 * Insert game types into the database.
	 *
	 * Scans the games directory and creates a post for each game type if it doesn't exist.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private static function insert_game_types(): void {
		$plugin_base_dir          = plugin_dir_path( __DIR__ );
		$game_directories_pattern = $plugin_base_dir . 'src/games/*';
		$game_directories         = glob( $game_directories_pattern, GLOB_ONLYDIR );

		foreach ( $game_directories as $single_game_type ) {
			$basename   = basename( $single_game_type );
			$post_slug  = sanitize_title( $basename );
			$post_title = ucfirst( $basename );

			$existing_post = get_page_by_path( $post_slug, OBJECT, 'charigame-game' );

			if ( ! $existing_post ) {
				wp_insert_post(
					array(
						'post_title'  => $post_title,
						'post_name'   => $post_slug,
						'post_status' => 'publish',
						'post_author' => 1,
						'post_type'   => 'charigame-game',
					)
				);
			}
		}
	}
	/**
	 * Create the game data table in the database.
	 *
	 * Sets up the table structure for storing game-related data.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private static function create_game_data_table(): void {
		global $wpdb;

		$table_name = $wpdb->prefix . 'charigame_game_data';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) !== $table_name ) {
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
				campaign_name VARCHAR(255) NOT NULL,
				email_address VARCHAR(255) NOT NULL,
				game_type VARCHAR(255) NOT NULL,
				game_code VARCHAR(50) NOT NULL,
				valid_from DATE NOT NULL,
				valid_until DATE NOT NULL,
				code_used TIMESTAMP NULL,
				last_played TIMESTAMP NULL,
				highscore INT DEFAULT 0,
				recipient_1 FLOAT DEFAULT 0,
				recipient_2 FLOAT DEFAULT 0,
				recipient_3 FLOAT DEFAULT 0,
				email_sent BOOLEAN DEFAULT 0,
				PRIMARY KEY (campaign_name, email_address, game_type),
				UNIQUE (campaign_name, email_address)
			) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}
	}
}
