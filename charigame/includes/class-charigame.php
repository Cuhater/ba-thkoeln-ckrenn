<?php
/**
 * Main ChariGame Plugin Class
 *
 * This is the main class that initializes and runs the ChariGame plugin.
 * It handles loading dependencies and initializing all components.
 *
 * @package ChariGame
 * @subpackage Includes
 * @since 1.0.0
 */

namespace ChariGame\Includes;

/**
 * ChariGame Main Class
 *
 * Core class that bootstraps the plugin functionality.
 */
class ChariGame {
	/**
	 * Run the plugin initialization.
	 *
	 * @return void
	 */
	public function run(): void {
		$this->load_dependencies();
		$this->init_components();
	}

	/**
	 * Load all required files and dependencies.
	 *
	 * @return void
	 */
	private function load_dependencies(): void {

		// Frontend components.
		require_once plugin_dir_path( __FILE__ ) . '../frontend/class-charigame-template-loader.php';
		require_once plugin_dir_path( __FILE__ ) . '../frontend/class-charigame-asset-manager.php';

		// Core components.
		require_once plugin_dir_path( __FILE__ ) . 'class-charigame-carbon-fields.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-charigame-blocks.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-charigame-shortcodes.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-charigame-login-handler.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-charigame-color-manager.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-charigame-helper.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-charigame-donation-manager.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-charigame-email-sender.php';
		require_once plugin_dir_path( __FILE__ ) . 'mappings.php';

		// Admin components.
		require_once plugin_dir_path( __FILE__ ) . '../admin/class-charigame-admin-menu.php';
		
		// Custom post types.
		require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/class-charigame-landingpage.php';
		require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/class-charigame-campaign.php';
		require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/class-charigame-recipient.php';
		require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/class-charigame-user.php';
		require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/class-charigame-game.php';
		require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/class-charigame-game-settings.php';
		require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/class-charigame-email-template.php';
	}

	/**
	 * Initialize all plugin components.
	 *
	 * Registers all needed components and loads post types after theme setup.
	 *
	 * @return void
	 */
	private function init_components(): void {
		// Initialize Carbon Fields.
		$carbon_fields = new ChariGame_Carbon_Fields();
		$carbon_fields->register();

		// Initialize Color Manager.
		$color_manager = ChariGame_Color_Manager::get_instance();
		$color_manager->register();

		// Initialize Blocks.
		$blocks = new ChariGame_Blocks();
		$blocks->register();

		// Initialize Shortcodes.
		$shortcodes = new ChariGame_Shortcodes();
		$shortcodes->register();

		// Initialize Login Handler.
		$login_handler = new ChariGame_Login_Handler();
		$login_handler->register();

		// Initialize Donation Manager.
		$donation_manager = new ChariGame_Donation_Manager();
		$donation_manager->register();

		// Initialize Email Sender.
		$email_sender = new ChariGame_Email_Sender();
		$email_sender->register();

		// Initialize AJAX Handler.
		$ajax = new Helper();
		$ajax->register_ajax_hooks();

		add_action(
			'after_setup_theme',
			function (): void {
				// Add theme support for editor styles.
				add_theme_support( 'editor-styles' );
				add_editor_style( plugin_dir_url( __FILE__ ) . '../dist/styles.css' );

				$admin_data_table = new \ChariGame\Admin\ChariGame_Admin_Menu();
				add_action( 'admin_menu', array( $admin_data_table, 'register_menu' ) );

				// Initialize post types.
				$campaign = new \ChariGame\PostTypes\ChariGame_Campaign();
				$campaign->register();

				$landingpage = new \ChariGame\PostTypes\ChariGame_Landing_Page();
				$landingpage->register();

				$game = new \ChariGame\PostTypes\ChariGame_Game();
				$game->register();

				$recipients = new \ChariGame\PostTypes\ChariGame_Recipient();
				$recipients->register();

				$users = new \ChariGame\PostTypes\ChariGame_User();
				$users->register();

				$game_set = new \ChariGame\PostTypes\ChariGame_Game_Settings();
				$game_set->register();

				$email_template = new \ChariGame\PostTypes\ChariGame_Email_Template();
				$email_template->register();

				// Initialize template loader.
				$template_loader = new \ChariGame\Frontend\ChariGame_Template_Loader();
				$template_loader->register();
			},
			20
		);
	}
}
