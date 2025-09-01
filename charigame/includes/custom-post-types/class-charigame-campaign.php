<?php
/**
 * ChariGame Campaign Post Type
 *
 * Handles campaign post type registration and meta fields.
 *
 * @package ChariGame
 * @subpackage PostTypes
 * @since 1.0.0
 */

namespace ChariGame\PostTypes;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * Campaign Class
 *
 * Registers and configures the campaign post type with Carbon Fields.
 */
class ChariGame_Campaign {

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
	 * Register the campaign post type
	 *
	 * @return void
	 */
	public function register_post_type(): void {
		$menu_title = $this->get_menu_title();

		register_post_type(
			'charigame-campaign',
			array(
				'labels'           => array(
					'name'                     => 'Charigame Campaign',
					'singular_name'            => 'Charigame Campaign',
					'menu_name'                => 'Charigame Campaign',
					'all_items'                => 'Campaigns',
					'edit_item'                => 'Edit Charigame Campaign',
					'view_item'                => 'View Charigame Campaign',
					'view_items'               => 'View Charigame Campaigns',
					'add_new_item'             => 'Add New Charigame Campaign',
					'add_new'                  => 'Add New Charigame Campaign',
					'new_item'                 => 'New Charigame Campaign',
					'parent_item_colon'        => 'Parent Charigame Campaign:',
					'search_items'             => 'Search Charigame Campaigns',
					'not_found'                => 'No charigame campaign found',
					'not_found_in_trash'       => 'No charigame campaign found in Trash',
					'archives'                 => 'Charigame Campaign Archives',
					'attributes'               => 'Charigame Campaign Attributes',
					'insert_into_item'         => 'Insert into charigame campaign',
					'uploaded_to_this_item'    => 'Uploaded to this Charigame campaign',
					'filter_items_list'        => 'Filter Charigame campaign list',
					'filter_by_date'           => 'Filter Charigame campaign by date',
					'items_list_navigation'    => 'Charigame Campaign list navigation',
					'items_list'               => 'Charigame Campaign list',
					'item_published'           => 'Charigame Campaign published.',
					'item_published_privately' => 'Charigame Campaign published privately.',
					'item_reverted_to_draft'   => 'Charigame Campaign reverted to draft.',
					'item_scheduled'           => 'Charigame Campaign scheduled.',
					'item_updated'             => 'Charigame Campaign updated.',
					'item_link'                => 'Charigame Campaign Link',
					'item_link_description'    => 'A link to a Charigame campaign.',
				),
				'public'           => true,
				'show_in_menu'     => 'charigame-types',
				'show_in_rest'     => true,
				'rest_base'        => 'charigame-campaign',
				'menu_position'    => 998,
				'menu_icon'        => 'dashicons-games',
				'supports'         => array(
					0 => 'title',
					'custom-fields',
				),
				'rewrite'          => array(
					'slug'       => 'spendenspiel',
					'with_front' => false,
					'pages'      => false,
				),
				'delete_with_user' => false,
			)
		);
	}

	/**
	 * Register custom meta fields using Carbon Fields
	 *
	 * @return void
	 */
	public function register_custom_fields(): void {
		Container::make( 'post_meta', __( 'Campaign Settings' ) )
				->where( 'post_type', '=', 'charigame-campaign' ) // Using = instead of === as required by Carbon Fields
				->add_fields(
					array(
						Field::make( 'select', 'game_type', __( 'Game Type' ) )
								->set_visible_in_rest_api( true )
								->add_options( $this->get_game_types() )
								->set_width( 33 ),

						Field::make( 'association', 'game_settings', __( 'Game Settings' ) )
							->set_visible_in_rest_api( true )
							->set_types(
								array(
									array(
										'type'      => 'post',
										'post_type' => 'charigame-game-set',
									),

								)
							)
							->set_help_text( 'Select the game settings for this campaign' )
							->set_width( 33 ),

						Field::make( 'association', 'linked_landing_page', __( 'Landing Page' ) )
						->set_visible_in_rest_api( true )
						->set_types(
							array(
								array(
									'type'      => 'post',
									'post_type' => 'charigame-landing',
								),
							)
						)
						->set_max( 1 )
						->set_help_text( 'Select the landing page to show after successful login' )
						->set_width( 33 ),

						Field::make( 'association', 'email_template', __( 'Email Template' ) )
							->set_types(
								array(
									array(
										'type'      => 'post',
										'post_type' => 'charigame-email-t',
									),
								)
							)
							->set_max( 1 )
							->set_help_text( 'Select the email template to use for this campaign' )
							->set_width( 100 ),

						Field::make( 'radio', 'dispatch_date_option', __( 'Dispatch Date Option' ) )
							->add_options(
								array(
									'birthday' => 'Birthday',
									'dispatch' => 'Dispatch Date',
								)
							)
							->set_default_value( 'birthday' )
							->set_help_text( 'Choose to send emails based on either birthdays or dispatch dates' )
							->set_required( true )
							->set_width( 100 ),

						Field::make( 'date', 'dispatch_date', __( 'Dispatch Date' ) )
							->set_storage_format( 'Y-m-d' )
							->set_conditional_logic(
								array(
									array(
										'field' => 'dispatch_date_option',
										'value' => 'dispatch',
									),
								)
							)
							->set_width( 50 ),

						Field::make( 'date', 'campaign_start', __( 'Campaign Start' ) )
							->set_storage_format( 'Y-m-d' )
							->set_conditional_logic(
								array(
									array(
										'field' => 'dispatch_date_option',
										'value' => 'birthday',
									),
								)
							)
							->set_required( true )
							->set_width( 50 ),

						Field::make( 'time', 'dispatch_time', __( 'Dispatch Time' ) )
							->set_storage_format( 'H:i' )
							->set_input_format( 'H:i', 'H:i' )
							->set_help_text( 'Time when the email is sent to users (24-hour format)' )
							->set_width( 50 ),

						Field::make( 'text', 'code_validity_duration', __( 'Code Validity Duration (in weeks)' ) )
							->set_attribute( 'type', 'range' )
							->set_attribute( 'min', '1' )
							->set_attribute( 'max', '8' )
							->set_attribute( 'step', '1' )
							->set_default_value( '4' )
							->set_width( 100 ),
						Field::make( 'html', 'code_validity_duration_output' )
								->set_html( '<div id="duration_wrapper">Duration: <span id="code_validity_duration-output">4</span> Weeks</div>' ),

						Field::make( 'html', 'campaign_end_info', __( 'Campaign End Information' ) )
							->set_html( '<p>This campaign will end on <span id="end-date">[date]</span> at <span id="end-time">[time]</span></p>' )
							->set_width( 100 ),

						Field::make( 'complex', 'donation_distribution_group', __( 'Donation Distribution' ) )
							->add_fields(
								array(
									Field::make( 'text', 'initial_donation_pool', __( 'Initial Donation Pool' ) )
										->set_attribute( 'type', 'number' )
										->set_attribute( 'min', '0' )
										->set_attribute( 'max', '10000' )
										->set_attribute( 'step', '0.01' )
										->set_default_value( 0 )
										->set_help_text( 'Amount in € (e.g. 1000 for 1,000€)' )
										->set_width( 100 ),

									Field::make( 'complex', 'win_categories', __( 'Win Categories' ) )
										->set_layout( 'tabbed-vertical' )
										->add_fields(
											array(
												Field::make( 'text', 'points_limit', __( 'Points/Time Limit' ) )
													->set_attribute( 'min', 0 )
													->set_width( 50 )
													->set_help_text( 'Minimum points/time required for this category' ),

												Field::make( 'text', 'donation_amount', __( 'Donation Amount (€)' ) )
													->set_attribute( 'min', 0 )
													->set_attribute( 'step', 0.01 )
													->set_width( 50 )
													->set_help_text( 'Donation amount for this category in Euro' ),
											)
										)
										->set_header_template( 'From <%- points_limit %> → <%- donation_amount %>€' )
										->set_help_text( 'Define different win categories with point limits and donation amounts' ),
								)
							)
							->set_max( 1 ),

						Field::make( 'complex', 'recipients', __( 'Recipients' ) )
						->set_visible_in_rest_api( true )
						->set_max( 3 )
						->add_fields(
							array(
								Field::make( 'association', 'recipient', __( 'Select Recipient' ) )
								->set_types(
									array(
										array(
											'type'      => 'post',
											'post_type' => 'charigame-recipients',
										),
									)
								),
							)
						),
					)
				);
		Container::make( 'post_meta', __( 'Login Settings' ) )
				->where( 'post_type', '=', 'charigame-campaign' ) // Using = instead of === as required by Carbon Fields
				->add_fields(
					array(
						Field::make( 'image', 'login_form_logo', __( 'Login Form Logo' ) )
								->set_default_value( '#3B82F6' )
								->set_help_text( 'Brand Logo' )
								->set_width( 50 ),
					)
				)
				->add_fields(
					array(
						Field::make( 'text', 'login_form_text', __( 'Login Form Text' ) )
								->set_default_value( '#10B981' )
								->set_help_text( 'Text which is displayed on the login form' )
								->set_width( 50 ),

					)
				)
				->add_fields(
					array(
						Field::make( 'color', 'primary_color', __( 'Primary Color' ) )
								->set_default_value( '#3B82F6' )
								->set_help_text( 'Main brand color for buttons, links, etc.' )
								->set_width( 50 ),

					)
				)
				->add_fields(
					array(
						Field::make( 'color', 'secondary_color', __( 'Secondary Color' ) )
								->set_default_value( '#10B981' )
								->set_help_text( 'Secondary brand color' )
								->set_width( 50 ),

					)
				)
				->add_fields(
					array(
						Field::make( 'color', 'tertiary_color', __( 'Tertiary Color' ) )
								->set_default_value( '#F59E0B' )
								->set_help_text( 'Accent color' )
								->set_width( 25 ),

					)
				)
				->add_fields(
					array(
						Field::make( 'color', 'success_color', __( 'Success Color' ) )
								->set_default_value( '#22C55E' )
								->set_help_text( 'Color for success messages' )
								->set_width( 25 ),
					)
				)
				->add_fields(
					array(
						Field::make( 'color', 'warning_color', __( 'Warning Color' ) )
								->set_default_value( '#F59E0B' )
								->set_help_text( 'Color for warning messages' )
								->set_width( 25 ),
					)
				)
				->add_fields(
					array(
						Field::make( 'color', 'error_color', __( 'Error Color' ) )
								->set_default_value( '#EF4444' )
								->set_help_text( 'Color for error messages' )
								->set_width( 25 ),
					)
				);
	}

	/**
	 * Get available game types from charigame-game posts
	 *
	 * @return array Array of game types as name => title pairs.
	 */
	private function get_game_types(): array {
		$game_types = array();
		$posts      = get_posts(
			array(
				'post_type'      => 'charigame-game',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		foreach ( $posts as $post ) {
			$game_types[ $post->post_name ] = $post->post_title;
		}

		return $game_types;
	}

	/**
	 * Get menu title with notification count
	 *
	 * @return string Menu title, potentially with notification badge.
	 */
	protected function get_menu_title(): string {
		$count = $this->get_expired_campaign_count();

		if ( $count > 0 ) {
			return sprintf(
				__( 'Campaigns <span class="update-plugins"><span class="update-count">%d</span></span>', 'charigame' ),
				$count
			);
		}

		return __( 'Campaigns', 'charigame' );
	}

	/**
	 * Get count of expired campaigns
	 *
	 * @return int Number of expired campaigns.
	 */
	protected function get_expired_campaign_count(): int {
		$args = array(
			'post_type'   => 'charigame-campaign',
			'post_status' => 'publish',
			'meta_query'  => array(
				array(
					'key'     => 'end_date',
					'value'   => current_time( 'Y-m-d' ),
					'compare' => '<',
					'type'    => 'DATE',
				),
			),
			'fields'      => 'ids',
		);

		$query = new \WP_Query( $args );

		return $query->found_posts;
	}
}
