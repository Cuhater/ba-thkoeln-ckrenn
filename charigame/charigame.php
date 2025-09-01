<?php
/**
 * The charigame main file
 *
 * WordPress reads this file to generate the plugin information in the plugin
 * admin area. This file also includes all the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://elancer-team.de/charigame/
 * @since             1.0.0
 * @package           ChariGame
 *
 * @wordpress-plugin
 * Plugin Name:       ChariGame | elancer-team GmbH
 * Plugin URI:        https://elancer-team.de/charigame/
 * Description:       ChariGame is a versatile fundraising plugin designed to streamline your donation campaigns with ease. With the ability to run multiple campaigns concurrently, this plugin empowers you to manage diverse fundraising initiatives effortlessly.
 * Version:           1.0.0
 * Author:            elancer-team GmbH
 * Author URI:        https://elancer-team.de/charigame/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       charigame
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'CHARIGAME_VERSION', '1.0.0' );

/**
 * Plugin constants
 */
define( 'CHARIGAME_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CHARIGAME_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'CHARIGAME_PLUGIN_FILE', __FILE__ );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_charigame_plugin(): void {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-charigame-activator.php';
	\ChariGame\ChariGame_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_plugin_name(): void {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin-name-deactivator.php';
	\ChariGame\Charigame_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_charigame_plugin' );

/**
 * Gutenberg Blocks Registration
 */
function charigame_register_blocks() {
	$build_path = plugin_dir_path( __FILE__ ) . 'build/';

	if ( file_exists( $build_path . 'index.js' ) ) {

		$asset_file = $build_path . 'index.asset.php';
		$asset      = file_exists( $asset_file ) ? include $asset_file : array(
			'dependencies' => array(),
			'version'      => CHARIGAME_VERSION,
		);

		wp_register_script(
			'charigame-blocks',
			plugin_dir_url( __FILE__ ) . 'build/index.js',
			$asset['dependencies'],
			$asset['version'],
			false
		);

		wp_register_style(
			'charigame-blocks-editor',
			plugin_dir_url( __FILE__ ) . 'build/index.css',
			array(),
			$asset['version']
		);
		add_filter( 'block_categories_all', 'charigame_block_categories' );
	}
}

add_action( 'init', 'charigame_register_blocks' );

/**
 * Add custom block category
 *
 * @param array $categories Array of existing block categories.
 *
 * @return array
 */
function charigame_block_categories( $categories ) {
	return array_merge(
		$categories,
		array(
			array(
				'slug'  => 'charigame-blocks',
				'title' => 'ChariGame Blocks',
				'icon'  => 'games',
			),
		)
	);
}

/**
 * Enqueue Block Editor Assets
 */
function charigame_enqueue_block_editor_assets() {
	$build_path = plugin_dir_path( __FILE__ ) . 'build/';

	if ( file_exists( $build_path . 'index.js' ) ) {
		wp_enqueue_script( 'charigame-blocks' );
		wp_enqueue_style( 'charigame-blocks-editor' );
	}
}

add_action( 'enqueue_block_editor_assets', 'charigame_enqueue_block_editor_assets' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-charigame.php';

/**
 * Frontend classes: Template Loader and Asset Manager
 */
require plugin_dir_path( __FILE__ ) . 'frontend/class-charigame-template-loader.php';
require plugin_dir_path( __FILE__ ) . 'frontend/class-charigame-asset-manager.php';

/**
 * Donation Manager für effiziente Spendenverwaltung
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-charigame-donation-manager.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_charigame(): void {
	$plugin = new \ChariGame\Includes\ChariGame();
	$plugin->run();

	$loader = new \ChariGame\Frontend\ChariGame_Template_Loader();
	$loader->register();

	$asset_manager = new \ChariGame\Frontend\ChariGame_Asset_Manager();
	$asset_manager->register();

	$donation_manager = new \ChariGame\Includes\ChariGame_Donation_Manager();
	$donation_manager->register();
}

run_charigame();
