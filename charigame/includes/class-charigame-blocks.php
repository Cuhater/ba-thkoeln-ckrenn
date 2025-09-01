<?php
/**
 * ChariGame Blocks
 *
 * Registers and manages Gutenberg blocks for the ChariGame plugin.
 *
 * @package ChariGame
 * @subpackage Includes
 * @since 1.0.0
 */

namespace ChariGame\Includes;

/**
 * Blocks Class
 *
 * Handles block registration, categories, and editor assets.
 */
class ChariGame_Blocks {

	/**
	 * Register hooks and actions.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		add_filter( 'allowed_block_types_all', array( $this, 'restrict_blocks_for_charigame_cpt' ), 10, 2 );
		add_filter( 'block_categories_all', array( $this, 'add_charigame_block_category' ) );
	}

	/**
	 * Restrict available blocks for ChariGame custom post types.
	 *
	 * @param array|bool $allowed_blocks Array of allowed block types or boolean to enable/disable all.
	 * @param object     $editor_context The current editor context.
	 * @return array|bool Modified array of allowed block types.
	 */
	/**
	 * Restrict available blocks for ChariGame custom post types.
	 *
	 * @param array|bool $allowed_blocks Array of allowed block types or boolean to enable/disable all.
	 * @param object     $editor_context The current editor context.
	 * @return array|bool Modified array of allowed block types.
	 */
	public function restrict_blocks_for_charigame_cpt( $allowed_blocks, $editor_context ): array|bool {
		$charigame_post_types = array(
			'charigame-campaign',
			'charigame-landing'
		);
		$email_template_post_type = 'charigame-email-t';

		if ( isset( $editor_context->post ) ) {
			if ( in_array( $editor_context->post->post_type, $charigame_post_types ) ) {
				return array(
					'charigame/container',
					'charigame/columns',
					'charigame/column',
					'charigame/headline',
					'charigame/intro-section',
					'charigame/recipient-section',
					'charigame/donation-section',
					'charigame/how-to-play-section',
					'charigame/how-to-play-item',
					'charigame/game-section',
					// 'charigame/recipient-selector',
				);
			} elseif ( $editor_context->post->post_type === $email_template_post_type ) {
				return array(
					'charigame/email-template',
				);
			}
		}

		return $allowed_blocks;
	}

	/**
	 * Register all blocks from the src/blocks directory.
	 *
	 * @return void
	 */
	public function register_blocks(): void {
		$blocks_dir = plugin_dir_path( __DIR__ ) . 'src/blocks/';

		if ( is_dir( $blocks_dir ) ) {
			$block_folders = glob( $blocks_dir . '*', GLOB_ONLYDIR );

			foreach ( $block_folders as $block_folder ) {
				$block_json = $block_folder . '/block.json';
				if ( file_exists( $block_json ) ) {
					register_block_type( $block_folder );
				}
			}
		}
	}

	/**
	 * Add a custom block category for ChariGame blocks.
	 *
	 * @param array $categories Array of block categories.
	 * @return array Modified array of block categories.
	 */
	public function add_charigame_block_category( array $categories ): array {
		return array_merge(
			$categories,
			array(
				array(
					'slug'  => 'charigame-blocks',
					'title' => 'ChariGame Blocks',
					'icon'  => 'games',
				),
			)
		);
	}

	/**
	 * Enqueue block editor assets.
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets(): void {
		$asset_file = plugin_dir_path( __DIR__ ) . 'dist/blocks.asset.php';

		if ( file_exists( $asset_file ) ) {
			$asset = include $asset_file;

			wp_enqueue_script(
				'charigame-blocks',
				plugin_dir_url( __DIR__ ) . 'dist/blocks.js',
				$asset['dependencies'],
				$asset['version']
			);

			// Only enqueue CSS if it exists.
			if ( file_exists( plugin_dir_path( __DIR__ ) . 'dist/blocks.css' ) ) {
				wp_enqueue_style(
					'charigame-blocks-editor',
					plugin_dir_url( __DIR__ ) . 'dist/blocks.css',
					[],
					$asset['version']
				);
			}

			// Localized data for JavaScript.
			wp_localize_script( 'charigame-blocks', 'charigameBlocks', array(
				'pluginUrl' => plugin_dir_url( __DIR__ ),
				'postType' => get_current_screen() ? get_current_screen()->post_type : '',
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'charigame_blocks_nonce' ),
			) );
		}
	}
}
