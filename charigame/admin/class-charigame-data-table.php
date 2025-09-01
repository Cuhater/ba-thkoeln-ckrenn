<?php

namespace ChariGame\Admin;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Charigame_Dashboard extends \WP_List_Table {
	public $campaign_id;
	public $campaign_name;

	function __construct() {
		parent::__construct( array(
			'singular' => 'Custom Item',
			'plural'   => 'Custom Items',
			'ajax'     => false
		) );
	}

	function get_columns() {
		return array(
			'email_address' => 'Email',
			'game_type'     => 'Game Type',
			'game_code'     => 'Game Code',
			'valid_from'    => 'Valid From',
			'valid_until'   => 'Valid Until',
			'code_used'     => 'Code Used',
			'last_played'   => 'Last Played',
			'highscore'     => 'Highscore',
			'recipient_1'   => 'Recipient 1 Distribution (%)',
			'recipient_2'   => 'Recipient 2 Distribution (%)',
			'recipient_3'   => 'Recipient 3 Distribution (%)',
			'email_sent'    => 'E-Mail Sent'
		);
	}

	function prepare_items() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'charigame_game_data';

		$columns = $this->get_columns();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, array(), $sortable);

		$per_page = 50;
		$current_page = $this->get_pagenum();

		if (isset($this->campaign_id) && $this->campaign_id) {
			$post = get_post($this->campaign_id);
			$campaign_slug = $post ? $post->post_name : $this->campaign_name;
		} else {
			$campaign_slug = $this->campaign_name;
		}

		$total_items = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE campaign_name = %s", $campaign_slug) );

		$offset = ( $current_page - 1 ) * $per_page;
		$sql = $wpdb->prepare("SELECT * FROM $table_name WHERE campaign_name = %s LIMIT %d OFFSET %d", $campaign_slug, $per_page, $offset);
		$results = $wpdb->get_results($sql, ARRAY_A);

		$this->items = $results;

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil($total_items / $per_page)
		));
	}

	function column_default($item, $column_name) {
		return $item[$column_name];
	}
}

