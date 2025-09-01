<?php
/**
 * ChariGame Landing Page Post Type
 *
 * Registers the custom post type for landing pages.
 *
 * @package ChariGame
 * @subpackage PostTypes
 * @since 1.0.0
 */

namespace ChariGame\PostTypes;

/**
 * Landing Page Class
 *
 * Registers and configures the landing page post type.
 */
class ChariGame_Landing_Page {

	/**
	 * Register hooks and actions.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'register_post_type' ), 5 );
	}

	/**
	 * Register the landing page post type.
	 *
	 * @return void
	 */
	public function register_post_type(): void {
		register_post_type(
			'charigame-landing',
			array(
				'labels'             => array(
					'name'                     => 'ChariGame Landing Pages',
					'singular_name'            => 'ChariGame Landing Page',
					'menu_name'                => 'Landing Pages',
					'all_items'                => 'Landing Pages',
					'edit_item'                => 'Edit Landing Page',
					'view_item'                => 'View Landing Page',
					'view_items'               => 'View Landing Pages',
					'add_new_item'             => 'Add New Landing Page',
					'add_new'                  => 'Add New',
					'new_item'                 => 'New Landing Page',
					'parent_item_colon'        => 'Parent Landing Page:',
					'search_items'             => 'Search Landing Pages',
					'not_found'                => 'No landing pages found',
					'not_found_in_trash'       => 'No landing pages found in Trash',
					'archives'                 => 'Landing Page Archives',
					'attributes'               => 'Landing Page Attributes',
					'insert_into_item'         => 'Insert into landing page',
					'uploaded_to_this_item'    => 'Uploaded to this landing page',
					'filter_items_list'        => 'Filter landing pages list',
					'filter_by_date'           => 'Filter landing pages by date',
					'items_list_navigation'    => 'Landing Pages list navigation',
					'items_list'               => 'Landing Pages list',
					'item_published'           => 'Landing Page published.',
					'item_published_privately' => 'Landing Page published privately.',
					'item_reverted_to_draft'   => 'Landing Page reverted to draft.',
					'item_scheduled'           => 'Landing Page scheduled.',
					'item_updated'             => 'Landing Page updated.',
					'item_link'                => 'Landing Page Link',
					'item_link_description'    => 'A link to a landing page.',
				),
				'public'             => true,
				'show_in_menu'       => 'charigame-types',
				'show_in_rest'       => true,
				'publicly_queryable' => true,
				'show_in_nav_menus'  => true,
				'show_in_admin_bar'  => true,
				'menu_position'      => 999,
				'menu_icon'          => 'dashicons-admin-page',
				'supports'           => array(
					'title',
					'editor',
					'thumbnail',
					'excerpt',
					'custom-fields',
					'revisions',
					'page-attributes',
				),
				'rewrite'            => array(
					'slug'       => 'landing',
					'with_front' => false,
					'pages'      => true,
				),
				'has_archive'        => true,
				'delete_with_user'   => false,
				'capability_type'    => 'page',
				'hierarchical'       => true,
			)
		);
	}
}
