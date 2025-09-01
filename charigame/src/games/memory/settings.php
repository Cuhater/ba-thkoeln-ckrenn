<?php
/**
 * Memory game settings fields.
 *
 * This file defines the Carbon Fields for the memory game settings.
 *
 * @package ChariGame
 * @subpackage Games
 * @since 1.0.0
 */

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * Memory game settings fields class.
 *
 * Provides field definitions for the memory game settings in the admin.
 */
class MemorySettingsFields {
	/**
	 * Get fields for the memory game settings.
	 *
	 * @return array Array of Carbon Field objects.
	 */
	public static function get_fields() {
		// Hook auf REST API, um die Memory-Bilder in der REST-API verfügbar zu machen.
		// Dies ist notwendig, damit die Bilder im Frontend geladen werden können und dies nicht im Carbon Field media_gallery verfügbar ist.
		add_action( 'rest_api_init', function() {
			register_rest_field(
				'charigame-game-set', // CPT-Slug.
				'memory_images_full', // Neues Feld in REST-API.
				array(
					'get_callback' => function( $post_object ) {
						$ids = carbon_get_post_meta( $post_object['id'], 'memory_images' );
						if ( ! is_array( $ids ) ) {
							return array();
						}
						return array_map( function( $id ) {
							return array(
								'id'  => $id,
								'url' => wp_get_attachment_url( $id ),
							);
						}, $ids );
					},
					'schema' => array(
						'type'  => 'array',
						'items' => array(
							'type'       => 'object',
							'properties' => array(
								'id'  => array( 'type' => 'integer' ),
								'url' => array( 'type' => 'string' ),
							),
						),
					),
				)
			);
		});
		return array(
			Field::make( 'media_gallery', 'memory_images', __( 'Memory Images' ) )
				->set_visible_in_rest_api( true )
				->set_type( array( 'image' ) ),
			Field::make( 'text', 'pairs_count', __( 'Anzahl Paare' ) )
				->set_attribute( 'type', 'number' )
				->set_attribute( 'min', '2' )
				->set_attribute( 'max', '50' )
				->set_default_value( '8' )
				->set_width( 50 )
				->set_help_text( 'Wie viele Paare sollen im Spiel sein?' ),
			Field::make( 'image', 'login_form_logo', __( 'Cardback Logo' ) )
				->set_help_text( 'The Icon or Logo displayed on the cardback' )
				->set_width( 50 ),
		);
	}
}


