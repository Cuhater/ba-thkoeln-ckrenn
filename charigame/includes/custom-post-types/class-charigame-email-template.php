<?php

namespace ChariGame\PostTypes;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * ChariGame Email Template Post Type
 *
 * Registers the custom post type for email templates
 * and defines the associated Carbon Fields.
 *
 * @package ChariGame
 * @subpackage PostTypes
 * @since 1.0.0
 */
class ChariGame_Email_Template {

	/**
	 * Register hooks and actions
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'register_post_type' ), 5 );
		add_action( 'carbon_fields_register_fields', array( $this, 'register_custom_fields' ) );
		add_filter( 'manage_charigame-email-template_posts_columns', array( $this, 'set_custom_columns' ) );
		add_action( 'manage_charigame-email-template_posts_custom_column', array( $this, 'render_custom_columns' ), 10, 2 );
		add_action( 'admin_notices', array( $this, 'display_variable_documentation' ) );
	}

	/**
	 * Register the custom post type for email templates
	 *
	 * @return void
	 */
	public function register_post_type(): void {
		register_post_type(
			'charigame-email-t',
			array(
				'labels'             => array(
					'name'                     => 'ChariGame Email Templates',
					'singular_name'            => 'ChariGame Email Template',
					'menu_name'                => 'Email Templates',
					'all_items'                => 'Email Templates',
					'edit_item'                => 'Edit Email Template',
					'view_item'                => 'View Email Template',
					'view_items'               => 'View Email Templates',
					'add_new_item'             => 'Add New Email Template',
					'add_new'                  => 'Add New',
					'new_item'                 => 'New Email Template',
					'parent_item_colon'        => 'Parent Email Template:',
					'search_items'             => 'Search Email Templates',
					'not_found'                => 'No email templates found',
					'not_found_in_trash'       => 'No email templates found in Trash',
					'archives'                 => 'Email Template Archives',
					'attributes'               => 'Email Template Attributes',
					'insert_into_item'         => 'Insert into email template',
					'uploaded_to_this_item'    => 'Uploaded to this email template',
					'filter_items_list'        => 'Filter email templates list',
					'filter_by_date'           => 'Filter email templates by date',
					'items_list_navigation'    => 'Email Templates list navigation',
					'items_list'               => 'Email Templates list',
					'item_published'           => 'Email Template published.',
					'item_published_privately' => 'Email Template published privately.',
					'item_reverted_to_draft'   => 'Email Template reverted to draft.',
					'item_scheduled'           => 'Email Template scheduled.',
					'item_updated'             => 'Email Template updated.',
					'item_link'                => 'Email Template Link',
					'item_link_description'    => 'A link to an email template.',
				),
				'public'             => true,
				'show_in_menu'       => 'charigame-types',
				'show_in_rest'       => true,
				'publicly_queryable' => false,
				'show_in_nav_menus'  => false,
				'show_in_admin_bar'  => true,
				'menu_position'      => 999,
				'menu_icon'          => 'dashicons-email-alt',
				'supports'           => array(
					'title',
					'thumbnail',
					'custom-fields',
					'revisions',
					'editor', // Add editor support to enable Gutenberg.
				),
				'rewrite'            => array(
					'slug'       => 'email-template',
					'with_front' => false,
					'pages'      => false,
				),
				'has_archive'        => false,
				'delete_with_user'   => false,
				'capability_type'    => 'post',
				'hierarchical'       => false,
				'template'           => array(
					array( 'charigame/email-template' ), // Set email template block as default template.
				),
				'template_lock'      => 'all', // Lock the template to allow only this block.
			)
		);
	}

	/**
	 * Register Carbon Fields for the email template post type
	 *
	 * @return void
	 */
	public function register_custom_fields(): void {
		// Only category containers are kept, all other fields are managed through the Gutenberg block.
		Container::make( 'post_meta', __( 'Email Template Categories' ) )
			->where( 'post_type', '=', 'charigame-email-t' )
			->add_fields(
				array(
					Field::make( 'text', 'email_subject', __( 'Email Subject' ) )
						->set_help_text( __( 'The subject line of the email' ) )
						->set_required( true )
						->set_width( 100 ),

					Field::make( 'checkbox', 'is_birthday_template', __( 'Is Birthday Email' ) )
						->set_option_value( 'yes' )
						->set_help_text( __( 'Check if this template is for birthday emails' ) ),

					Field::make( 'checkbox', 'is_campaign_template', __( 'Is Campaign Email' ) )
						->set_option_value( 'yes' )
						->set_help_text( __( 'Check if this template is for campaign emails' ) ),

					Field::make( 'checkbox', 'is_reminder_template', __( 'Is Reminder Email' ) )
						->set_option_value( 'yes' )
						->set_help_text( __( 'Check if this template is for reminder emails' ) ),
				)
			);
	}

	/**
	 * Define custom columns for the email template overview
	 *
	 * @param array $columns Default WordPress columns.
	 * @return array Modified columns.
	 */
	public function set_custom_columns( $columns ): array {
		$columns = array(
			'cb'            => $columns['cb'],
			'title'         => __( 'Title' ),
			'email_subject' => __( 'Email Subject' ),
			'template_type' => __( 'Template Type' ),
			'date'          => __( 'Date' ),
		);

		return $columns;
	}

	/**
	 * Render the content of custom columns
	 *
	 * @param string $column Column name being rendered.
	 * @param int    $post_id Post ID being displayed.
	 * @return void
	 */
	public function render_custom_columns( $column, $post_id ): void {
		switch ( $column ) {
			case 'email_subject':
				echo esc_html( carbon_get_post_meta( $post_id, 'email_subject' ) );
				break;

			case 'template_type':
				$types = array();

				if ( carbon_get_post_meta( $post_id, 'is_birthday_template' ) ) {
					$types[] = '<span class="email-type birthday-type">Birthday</span>';
				}

				if ( carbon_get_post_meta( $post_id, 'is_campaign_template' ) ) {
					$types[] = '<span class="email-type campaign-type">Campaign</span>';
				}

				if ( carbon_get_post_meta( $post_id, 'is_reminder_template' ) ) {
					$types[] = '<span class="email-type reminder-type">Reminder</span>';
				}

				if ( ! empty( $types ) ) {
					echo wp_kses( implode( ' ', $types ), array( 'span' => array( 'class' => array() ) ) );
				} else {
					echo wp_kses( '<span class="email-type">Uncategorized</span>', array( 'span' => array( 'class' => array() ) ) );
				}
				break;
		}
	}

	/**
	 * Display documentation for available variables in email templates
	 *
	 * @return void
	 */
	public function display_variable_documentation(): void {
		global $pagenow, $post;

		// Only show on email template edit screen.
		if ( ( $pagenow === 'post.php' || $pagenow === 'post-new.php' ) &&
			isset( $post ) && $post->post_type === 'charigame-email-t' ) {

			$variables = array(
				'{name}'         => 'Full name of recipient (e.g., "John Smith")',
				'{first_name}'   => 'First name of recipient (e.g., "John")',
				'{last_name}'    => 'Last name of recipient (e.g., "Smith")',
				'{game_code}'    => 'Unique game code for the recipient',
				'{valid_from}'   => 'Campaign start date (format: DD.MM.YYYY)',
				'{valid_until}'  => 'Campaign end date (format: DD.MM.YYYY)',
				'{campaign_url}' => 'URL to the campaign landing page',
			);

			echo '<div class="notice notice-info">
                <h3>Available Variables for Email Templates</h3>
                <p>The following variables can be used in the email template texts:</p>
                <table class="widefat" style="margin-bottom: 15px;">
                    <thead>
                        <tr>
                            <th>Variable</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>';

			foreach ( $variables as $variable => $description ) {
				echo '<tr>
                    <td><code>' . esc_html( $variable ) . '</code></td>
                    <td>' . esc_html( $description ) . '</td>
                </tr>';
			}

			echo '</tbody>
                </table>
                <p><strong>Note:</strong> These variables can be used in the following sections of the email template:</p>
            <ul style="margin-left: 20px;">
                <li>Header Claim</li>
                <li>Headline</li>
                <li>Email Content</li>
                <li>Game Code Text</li>
                <li>Validity Text</li>
                <li>Closing Text</li>
            </ul>
            <p>Additionally, colors for the header claim and headline can be customized individually.</p>
            </div>';
		}
	}
}
