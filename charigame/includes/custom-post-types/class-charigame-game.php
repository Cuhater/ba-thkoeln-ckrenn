<?php
/**
 * ChariGame Game Post Type
 *
 * Registers the custom post type for games and their settings.
 *
 * @package ChariGame
 * @subpackage PostTypes
 * @since 1.0.0
 */

namespace ChariGame\PostTypes;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * Game Class
 *
 * Registers and configures the game post type with Carbon Fields.
 */
class ChariGame_Game {

	/**
	 * Register hooks and actions
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'register_post_type' ), 5 );
		add_action( 'carbon_fields_register_fields', array( $this, 'register_custom_fields' ), 20 );
	}


	/**
	 * Register the game post type
	 *
	 * @return void
	 */
	public function register_post_type(): void {
		add_action(
			'init',
			function (): void {
				register_post_type(
					'charigame-game',
					array(
						'labels'              => array(
							'name'                     => 'ChariGame Game',
							'singular_name'            => 'ChariGame Game',
							'menu_name'                => 'ChariGame Game',
							'all_items'                => 'Games',
							'edit_item'                => 'Edit ChariGame Game',
							'view_item'                => 'View ChariGame Game',
							'view_items'               => 'View ChariGame Game',
							'add_new_item'             => 'Add New ChariGame Game',
							'add_new'                  => 'Add New ChariGame Game',
							'new_item'                 => 'New ChariGame Game',
							'parent_item_colon'        => 'Parent ChariGame Game',
							'search_items'             => 'Search ChariGame Game',
							'not_found'                => 'No charigame game type found',
							'not_found_in_trash'       => 'No charigame game type found in Trash',
							'archives'                 => 'Charigame Game Archives',
							'attributes'               => 'Charigame Game Attributes',
							'insert_into_item'         => 'Insert into charigame game type',
							'uploaded_to_this_item'    => 'Uploaded to this charigame game type',
							'filter_items_list'        => 'Filter charigame game type list',
							'filter_by_date'           => 'Filter charigame game type by date',
							'items_list_navigation'    => 'Charigame Game list navigation',
							'items_list'               => 'Charigame Game list',
							'item_published'           => 'Charigame Game published.',
							'item_published_privately' => 'Charigame Game published privately.',
							'item_reverted_to_draft'   => 'Charigame Game reverted to draft.',
							'item_scheduled'           => 'Charigame Game scheduled.',
							'item_updated'             => 'Charigame Game updated.',
							'item_link'                => 'Charigame Game Link',
							'item_link_description'    => 'A link to a charigame game type.',
						),
						'public'              => true,
						'show_in_menu'        => false,
						'exclude_from_search' => true,
						'publicly_queryable'  => false,
						'show_in_nav_menus'   => false,
						'show_in_admin_bar'   => false,
						'show_in_rest'        => false,
						'menu_position'       => 1001,
						'menu_icon'           => 'dashicons-games',
						'supports'            => array(
							0 => 'title',
						),
						'rewrite'             => false,
						'delete_with_user'    => false,
					)
				);
			}
		);
	}
	/**
	 * Register custom fields for the game post type
	 *
	 * @return void
	 */
	public function register_custom_fields(): void {
		Container::make( 'post_meta', __( 'Charigame Game Attributes' ) )
				->where( 'post_type', '=', 'charigame-game' ) // Using = instead of === as required by Carbon Fields.
				->add_fields(
					array(
						Field::make( 'rich_text', 'desc', __( 'Description' ) )
								->set_width( 100 ),

						Field::make( 'complex', 'how_to_play_group', __( 'How To Play' ) )
								->add_fields(
									array(
										Field::make( 'text', 'how_to_play_headline', __( 'How To Play Headline' ) )
											->set_default_value( 'How to play:' ),

										Field::make( 'complex', 'how_to_play_steps', __( 'How To Play Steps' ) )
											->set_layout( 'tabbed-horizontal' )
											->add_fields(
												array(
													Field::make( 'select', 'step_icon', __( 'Step Icon' ) )
														->add_options(
															array(
																'dashicons-admin-home' => 'Home',
																'dashicons-games' => 'Games',
																'dashicons-admin-generic' => 'Generic',
																'dashicons-yes' => 'Check',
																'dashicons-arrow-right' => 'Arrow Right',
																'dashicons-arrow-left' => 'Arrow Left',
																'dashicons-star-filled' => 'Star',
															// Add more Dashicons here as needed.
															)
														)
														->set_width( 25 ),

													Field::make( 'textarea', 'step_headline', __( 'Step Headline' ) )
														->set_width( 25 ),

													Field::make( 'textarea', 'step_text', __( 'Step Text' ) )
														->set_width( 25 ),

													Field::make( 'select', 'step_color', __( 'Step Color' ) )
														->add_options(
															array(
																'primary'   => __( 'Primary' ),
																'secondary' => __( 'Secondary' ),
																'tertiary'  => __( 'Tertiary' ),
															)
														)
														->set_width( 25 ),
												)
											)
											->set_header_template( '<%- $_index + 1 %> - <%- step_headline %>' ),
									)
								),
					)
				);
	}
}
