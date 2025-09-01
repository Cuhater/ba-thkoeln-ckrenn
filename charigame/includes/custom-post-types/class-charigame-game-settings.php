<?php
/**
 * ChariGame Game Settings Post Type
 *
 * Registers the custom post type for game settings and their custom fields.
 *
 * @package ChariGame
 * @subpackage PostTypes
 * @since 1.0.0
 */

namespace ChariGame\PostTypes;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * Game Settings Class
 *
 * Registers and configures the game settings post type with Carbon Fields.
 */
class ChariGame_Game_Settings {

	/**
	 * Register hooks and actions
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'carbon_fields_register_fields', array( $this, 'register_custom_fields' ) );
	}

	/**
	 * Register the game settings post type
	 *
	 * @return void
	 */
	public function register_post_type(): void {
		register_post_type( 'charigame-game-set', array(
			'labels'             => array(
				'name'                     => 'ChariGame Game Set',
				'singular_name'            => 'ChariGame Game Set',
				'menu_name'                => 'ChariGame Game Set',
				'all_items'                => 'Game Settings',
				'edit_item'                => 'Edit ChariGame Game Set',
				'view_item'                => 'View ChariGame Game Set',
				'view_items'               => 'View ChariGame Game Sets',
				'add_new_item'             => 'Add New ChariGame Game Set',
				'add_new'                  => 'Add New ChariGame Game Set',
				'new_item'                 => 'New ChariGame Game Set',
				'parent_item_colon'        => 'Parent ChariGame Game Set:',
				'search_items'             => 'Search ChariGame Game Sets',
				'not_found'                => 'No ChariGame Game Set found',
				'not_found_in_trash'       => 'No ChariGame Game Set found in Trash',
				'archives'                 => 'Charigame Game Set Archives',
				'attributes'               => 'Charigame Game Set Attributes',
				'insert_into_item'         => 'Insert into charigame game set',
				'uploaded_to_this_item'    => 'Uploaded to this charigame game set',
				'filter_items_list'        => 'Filter Charigame Game Sets list',
				'filter_by_date'           => 'Filter Charigame Game Sets by date',
				'items_list_navigation'    => 'Charigame Game Sets list navigation',
				'items_list'               => 'Charigame Game Sets list',
				'item_published'           => 'Charigame Game Set published.',
				'item_published_privately' => 'Charigame Game Set published privately.',
				'item_reverted_to_draft'   => 'Charigame Game Set reverted to draft.',
				'item_scheduled'           => 'Charigame Game Set scheduled.',
				'item_updated'             => 'Charigame Game Set updated.',
				'item_link'                => 'Charigame Game Set Link',
				'item_link_description'    => 'A link to a charigame game set.',
			),
			'public'             => true,
			'show_in_menu'       => 'charigame-types',
			'show_in_rest'       => true,
			'rest_base'          => 'charigame-game-set',
			'publicly_queryable' => false,
			'show_in_nav_menus'  => false,
			'menu_position'      => 1000,
			'menu_icon'          => 'dashicons-admin-generic',
			'supports'           => array(
				'title',
			),
			'rewrite'            => false,
			'delete_with_user'   => false,
		));
	}

	/**
	 * Register custom fields for the game settings post type
	 *
	 * @return void
	 */
	public function register_custom_fields(): void {
		$fields = array(
			Field::make( 'select', 'game_type', __( 'Settings for Game Type:' ) )
				->add_options( $this->get_available_games() )
				->set_required( true )
				->set_width( 50 ),
		);

		$games_dir = plugin_dir_path( __FILE__ ) . '../../src/games/';
		foreach ( glob( $games_dir . '*/settings.php' ) as $settings_file ) {
			require_once $settings_file;
			$class_name = $this->get_settings_fields_class_name( $settings_file );
			if ( class_exists( $class_name ) && method_exists( $class_name, 'get_fields' ) ) {
				$fields = array_merge( $fields, $class_name::get_fields() );
			}
		}

		Container::make( 'post_meta', __( 'Game Settings' ) )
			->where( 'post_type', '=', 'charigame-game-set' ) // Using = instead of === as required by Carbon Fields
			->add_fields( $fields );
	}

	/**
	 * Get the class name for settings fields based on the file path
	 *
	 * @param string $settings_file Path to the settings file.
	 * @return string Class name for the settings fields.
	 */
	private function get_settings_fields_class_name( string $settings_file ): string {
		$parts = explode( DIRECTORY_SEPARATOR, $settings_file );
		$game_name = ucfirst( $parts[ count( $parts ) - 2 ] );
		return $game_name . 'SettingsFields';
	}

	/**
	 * Get a list of available games from the games directory
	 *
	 * @return array Array of game names as key => value pairs.
	 */
	private function get_available_games(): array {
		$games = array();
		$games_dir = plugin_dir_path( __FILE__ ) . '../../src/games/';
		foreach ( glob( $games_dir . '*', GLOB_ONLYDIR ) as $game_path ) {
			$game = strtolower( basename( $game_path ) );
			$games[ $game ] = ucfirst( $game );
		}
		return $games;
	}
}
