<?php
/**
 * ChariGame Recipient Post Type
 *
 * Registers the custom post type for donation recipients and their custom fields.
 *
 * @package ChariGame
 * @subpackage PostTypes
 * @since 1.0.0
 */

namespace ChariGame\PostTypes;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * Recipient Class
 *
 * Registers and configures the recipient post type with Carbon Fields.
 */
class ChariGame_Recipient {

	/**
	 * Register hooks and actions.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'register_post_type' ), 5 );
		add_action( 'carbon_fields_register_fields', array( $this, 'register_custom_fields' ), 20 );
	}


	/**
	 * Register the recipient post type.
	 *
	 * @return void
	 */
	public function register_post_type(): void {
		register_post_type(
			'charigame-recipients',
			array(
				'labels'             => array(
					'name'                     => 'ChariGame Donation Recipients',
					'singular_name'            => 'ChariGame Donation Recipient',
					'menu_name'                => 'ChariGame Donation Recipients ',
					'all_items'                => 'Recipients',
					'edit_item'                => 'Edit ChariGame Donation Recipient',
					'view_item'                => 'View ChariGame Donation Recipient',
					'view_items'               => 'View ChariGame Donation Recipients ',
					'add_new_item'             => 'Add New ChariGame Donation Recipient',
					'add_new'                  => 'Add New ChariGame Donation Recipient',
					'new_item'                 => 'New ChariGame Donation Recipient',
					'parent_item_colon'        => 'Parent ChariGame Donation Recipient:',
					'search_items'             => 'Search ChariGame Donation Recipients ',
					'not_found'                => 'No ChariGame Donation Recipient found',
					'not_found_in_trash'       => 'No ChariGame Donation Recipient found in Trash',
					'archives'                 => 'Charigame Donation Recipient Archives',
					'attributes'               => 'Charigame Donation Recipient Attributes',
					'insert_into_item'         => 'Insert into charigame recipient',
					'uploaded_to_this_item'    => 'Uploaded to this charigame recipient',
					'filter_items_list'        => 'Filter Charigame Donation Recipients list',
					'filter_by_date'           => 'Filter Charigame Donation Recipients by date',
					'items_list_navigation'    => 'Charigame Donation Recipients	list navigation',
					'items_list'               => 'Charigame Donation Recipients	list',
					'item_published'           => 'Charigame Donation Recipient published.',
					'item_published_privately' => 'Charigame Donation Recipient published privately.',
					'item_reverted_to_draft'   => 'Charigame Donation Recipient reverted to draft.',
					'item_scheduled'           => 'Charigame Donation Recipient scheduled.',
					'item_updated'             => 'Charigame Donation Recipient updated.',
					'item_link'                => 'Charigame Donation Recipient Link',
					'item_link_description'    => 'A link to a charigame recipient.',
				),
				'public'             => true,
				'show_in_menu'       => 'charigame-types',
				'show_in_rest'       => true,
				'rest_base'          => 'charigame-recipients',
				'publicly_queryable' => false,
				'show_in_nav_menus'  => false,
				'menu_position'      => 1000,
				'menu_icon'          => 'dashicons-games',
				'supports'           => array(
					0 => 'title',
				),
				'rewrite'            => false,
				'delete_with_user'   => false,
			)
		);
	}

	/**
	 * Register custom fields for the recipient post type.
	 *
	 * @return void
	 */
	public function register_custom_fields(): void {
		Container::make( 'post_meta', __( 'Charigame Recipients Attributes' ) )
				->where( 'post_type', '=', 'charigame-recipients' ) // Using = instead of === as required by Carbon Fields.
				->add_fields(
					array(
						Field::make( 'image', 'recipient_logo', __( 'Logo' ) )
								->set_visible_in_rest_api( true )
								->set_value_type( 'id' )  // Store the image ID instead of URL.
								->set_type( array( 'image' ) )    // Restrict to image types.
								->set_width( 30 ),
						Field::make( 'text', 'recipient_name', __( 'Name' ) )
							->set_visible_in_rest_api( true ),
						Field::make( 'textarea', 'recipient_description', __( 'Description' ) )
							->set_visible_in_rest_api( true ),
					)
				);
	}
}
