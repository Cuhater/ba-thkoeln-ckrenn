<?php
namespace ChariGame;

require_once plugin_dir_path(__FILE__) . 'components/shadcn-loader.php';

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    ChariGame
 * @subpackage ChariGame/admin
 */
class ChariGame_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @var string
	 */
	private $version;

    /**
     * Shadcn components loader
     *
     * @var Admin\Components\ShadcnLoader
     */
    private $shadcn_loader;

	/**
	 * Constructor
	 *
	 * @param string $plugin_name Plugin name.
	 * @param string $version Plugin version.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->enqueue_scripts();
        $this->enqueue_styles();

		$this->load_custom_post_types();
		$this->charigame_data_table = new Admin\ChariGame_Admin_DataTable();

        $this->shadcn_loader = new Admin\Components\ShadcnLoader();
	}

	/**
	 * Load admin stylesheets.
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/plugin-name-admin.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Load admin scripts.
	 */
	public function enqueue_scripts() : void {
		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/backend.js',
			array( 'jquery' ),
			$this->version,
			false
		);
	}
}
