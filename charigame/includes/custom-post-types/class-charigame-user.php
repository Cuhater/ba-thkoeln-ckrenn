<?php
/**
 * ChariGame User Post Type
 *
 * Registers the custom post type for users and their custom fields.
 *
 * @package ChariGame
 * @subpackage PostTypes
 * @since 1.0.0
 */

namespace ChariGame\PostTypes;

use ChariGame\Includes\Helper;


use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * User Class
 *
 * Registers and configures the user post type with Carbon Fields.
 */
class ChariGame_User {

	/**
	 * Register hooks and actions.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'register_post_type' ), 5 );
		add_action( 'carbon_fields_register_fields', array( $this, 'register_custom_fields' ), 20 );
		add_filter( 'manage_charigame-user_posts_columns', array( $this, 'set_custom_columns' ) );
		add_filter( 'manage_edit-charigame-user_sortable_columns', array( $this, 'set_sortable_columns' ) );
		add_action( 'manage_charigame-user_posts_custom_column', array( $this, 'custom_column_content' ), 10, 2 );
	}


	/**
	 * Register custom fields for the user post type.
	 *
	 * @return void
	 */
	public function register_custom_fields(): void {
		Container::make( 'post_meta', __( 'User Details' ) )
				->where( 'post_type', '=', 'charigame-user' ) // Using = instead of === as required by Carbon Fields.
				->add_fields(
					array(
						Field::make( 'text', 'first_name', __( 'First Name' ) )
								->set_width( 50 ),
						Field::make( 'text', 'last_name', __( 'Last Name' ) )
								->set_width( 50 ),
						Field::make( 'text', 'email', __( 'Email Address' ) )
								->set_width( 100 ),
						Field::make( 'date', 'birthday', __( 'Birthday' ) )
								->set_width( 50 ),
						Field::make( 'checkbox', 'imported', __( 'Imported' ) )
								->set_width( 25 ),
						Field::make( 'checkbox', 'email_sent', __( 'Email Sent' ) )
								->set_width( 25 ),
					)
				);
	}

	/**
	 * Register the user post type.
	 *
	 * @return void
	 */
	public function register_post_type(): void {
		register_post_type(
			'charigame-user',
			array(
				'labels'              => array(
					'name'                     => 'Charigame Users',
					'singular_name'            => 'Charigame User',
					'menu_name'                => 'Charigame Users',
					'all_items'                => 'Users',
					'edit_item'                => 'Edit Charigame User',
					'view_item'                => 'View Charigame User',
					'view_items'               => 'View Charigame User',
					'add_new_item'             => 'Add New Charigame User',
					'add_new'                  => 'Add New Charigame User',
					'new_item'                 => 'New Charigame User',
					'parent_item_colon'        => 'Parent Charigame User:',
					'search_items'             => 'Search Charigame User',
					'not_found'                => 'No Charigame User found',
					'not_found_in_trash'       => 'No Charigame User found in Trash',
					'archives'                 => 'Charigame User Archives',
					'attributes'               => 'Charigame User Attributes',
					'insert_into_item'         => 'Insert into charigame user',
					'uploaded_to_this_item'    => 'Uploaded to this Charigame user',
					'filter_items_list'        => 'Filter Charigame User list',
					'filter_by_date'           => 'Filter Charigame User by date',
					'items_list_navigation'    => 'Charigame User list navigation',
					'items_list'               => 'Charigame User list',
					'item_published'           => 'Charigame User published.',
					'item_published_privately' => 'Charigame User published privately.',
					'item_reverted_to_draft'   => 'Charigame User reverted to draft.',
					'item_scheduled'           => 'Charigame User scheduled.',
					'item_updated'             => 'Charigame User updated.',
					'item_link'                => 'Charigame User Link',
					'item_link_description'    => 'A link to a Charigame user.',
				),
				'public'              => true,
				'show_in_menu'        => 'charigame-types',
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
	/**
	 * Set custom columns for the user list view.
	 *
	 * @param array $columns The default columns.
	 * @return array Modified columns.
	 */
	public function set_custom_columns( array $columns ): array {
		return array(
			'cb'         => '<input type="checkbox" />',
			'title'      => __( 'Title' ),
			'first_name' => __( 'First Name' ),
			'last_name'  => __( 'Last Name' ),
			'email'      => __( 'Email' ),
			'birthday'   => __( 'Birthday' ),
			'imported'   => __( 'Imported' ),
			'email_sent' => __( 'Email Sent' ),
			'date'       => __( 'Date' ),
		);
	}
	/**
	 * Set sortable columns for the user list view.
	 *
	 * @param array $columns The default sortable columns.
	 * @return array Modified sortable columns.
	 */
	public function set_sortable_columns( array $columns ): array {
		$columns['title']      = 'title';
		$columns['first_name'] = 'first_name';
		$columns['last_name']  = 'last_name';
		$columns['email']      = 'email';
		$columns['birthday']   = 'birthday';
		$columns['date']       = 'date';
		return $columns;
	}
	/**
	 * Display content for custom columns in the user list view.
	 *
	 * @param string $column  The column name.
	 * @param int    $post_id The post ID.
	 * @return void
	 */
	public function custom_column_content( string $column, int $post_id ): void {
		switch ( $column ) {
			case 'first_name':
				echo esc_html( carbon_get_post_meta( $post_id, 'first_name' ) );
				break;
			case 'last_name':
				echo esc_html( carbon_get_post_meta( $post_id, 'last_name' ) );
				break;
			case 'email':
				echo esc_html( carbon_get_post_meta( $post_id, 'email' ) );
				break;
			case 'birthday':
				echo esc_html( carbon_get_post_meta( $post_id, 'birthday' ) );
				break;
			case 'imported':
				echo carbon_get_post_meta( $post_id, 'imported' ) ? '✓' : '×';
				break;
			case 'email_sent':
				echo carbon_get_post_meta( $post_id, 'email_sent' ) ? '✓' : '×';
				break;
		}
	}
}
