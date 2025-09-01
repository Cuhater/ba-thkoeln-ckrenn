<?php

namespace ChariGame\Admin;

class ChariGame_Sortable_Table {
    /**
     * Campaign ID
     *
     * @var int
     */
    public $campaign_id;

    /**
     * Campaign name
     *
     * @var string
     */
    public $campaign_name;

    /**
     * Get all items for this campaign
     *
     * @return array
     */
    public function get_items() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'charigame_game_data';

        if (isset($this->campaign_id) && $this->campaign_id) {
            $post = get_post($this->campaign_id);
            $campaign_slug = $post ? $post->post_name : $this->campaign_name;
        } else {
            $campaign_slug = $this->campaign_name;
        }

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE campaign_name = %s",
                $campaign_slug
            ),
            ARRAY_A
        );

        return $results ?: [];
    }

    /**
     * Get column definitions for the table
     *
     * @return array
     */
    public function get_columns() {
        return [
            'email_address' => 'Email',
            'game_type' => 'Game Type',
            'game_code' => 'Game Code',
            'valid_from' => 'Valid From',
            'valid_until' => 'Valid Until',
            'code_used' => 'Code Used',
            'last_played' => 'Last Played',
            'highscore' => 'Highscore',
            'recipient_1' => 'Recipient 1 (%)',
            'recipient_2' => 'Recipient 2 (%)',
            'recipient_3' => 'Recipient 3 (%)',
            'email_sent' => 'Email Sent',
            'email_opened' => 'Email Opened',
            'game_completed' => 'Game Completed',
            'play_count' => 'Play Count',
            'play_duration' => 'Play Duration (sec)'
        ];
    }

    /**
     * Render the table using shadcn/ui components
     *
     * @return string
     */
    public function render() {
        $columns = $this->get_columns();
        $items = $this->get_items();

        $formatted_rows = [];
        foreach ($items as $item) {
            $row = [];
            foreach ($columns as $key => $label) {
                switch ($key) {
                    case 'code_used':
                        if (!empty($item[$key])) {
                            $date = new \DateTime($item[$key], wp_timezone());
                            $formatted_date = $date->format('d.m.Y H:i');
                            $row[$key] = '✅ <span class="timestamp-info" title="' . esc_attr($formatted_date) . '"></span>';
                        } else {
                            $row[$key] = '❌';
                        }
                        break;
                    case 'email_sent':
                    case 'email_opened':
                    case 'game_completed':
                        $row[$key] = !empty($item[$key]) ? '✅' : '❌';
                        break;

                    case 'last_played':
                        if (!empty($item[$key])) {
                            $date = new \DateTime($item[$key], wp_timezone());
                            $row[$key] = $date->format('d.m.Y H:i');
                        } else {
                            $row[$key] = '';
                        }
                        break;

                    case 'valid_from':
                    case 'valid_until':
                        $row[$key] = !empty($item[$key]) ? date('d.m.Y', strtotime($item[$key])) : '';
                        break;

                    case 'recipient_1':
                    case 'recipient_2':
                    case 'recipient_3':
                        $row[$key] = isset($item[$key]) ? $item[$key] . '%' : '0%';
                        break;

                    default:
                        $row[$key] = $item[$key] ?? '';
                }
            }
            $formatted_rows[] = $row;
        }

        if (function_exists('\ChariGame\Admin\shadcn_function_caller')) {
            return \ChariGame\Admin\shadcn_function_caller('shadcn_table', [
            'id' => 'charigame-data-table-' . $this->campaign_id,
            'headers' => $columns,
            'rows' => $formatted_rows,
            'sortable' => true,
            'default_sort' => 'email_address',
            'empty_message' => 'Keine Daten für diese Kampagne verfügbar'
        ]);
        } else {
            return call_user_func('shadcn_table', [
                'id' => 'charigame-data-table-' . $this->campaign_id,
                'headers' => $columns,
                'rows' => $formatted_rows,
                'sortable' => true,
                'default_sort' => 'email_address',
                'empty_message' => 'Keine Daten für diese Kampagne verfügbar'
            ]);
        }
    }
}
