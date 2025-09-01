<?php
/**
 * ChariGame Carbon Fields
 *
 * Handles Carbon Fields library initialization.
 *
 * @package ChariGame
 * @subpackage Includes
 * @since 1.0.0
 */

namespace ChariGame\Includes;

use Carbon_Fields\Carbon_Fields;

/**
 * Carbon Fields Class
 *
 * Initializes and bootstraps the Carbon Fields library.
 */
class ChariGame_Carbon_Fields {
	/**
	 * Register hooks and actions.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'after_setup_theme', array( $this, 'boot_carbon_fields' ) );
	}

	/**
	 * Boot Carbon Fields library.
	 *
	 * Loads the autoloader and initializes Carbon Fields.
	 *
	 * @return void
	 */
	public function boot_carbon_fields(): void {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';
		Carbon_Fields::boot();
	}
}