<?php
/**
 * Shadcn Table Component
 * A PHP implementation of the shadcn/ui Table component
 */

/**
 * Renders a table with shadcn styling
 *
 * @param array $args Arguments for configuring the table
 * @return string The rendered table HTML
 */
if (!function_exists('shadcn_table')) {
    function shadcn_table($args = []) {
    // Default arguments
    $defaults = [
        'id' => 'shadcn-table-' . uniqid(),
        'class' => '',
        'headers' => [],
        'rows' => [],
        'sortable' => false,
        'default_sort' => null, // Column key for default sorting
        'default_sort_dir' => 'asc', // 'asc' or 'desc'
        'empty_message' => 'No data available',
        'responsive' => true,
    ];

    $args = array_merge($defaults, $args);

    $table_id = esc_attr($args['id']);

    $table_class = 'shadcn-table';
    if (!empty($args['class'])) {
        $table_class .= ' ' . $args['class'];
    }

    $output = '';
    if ($args['responsive']) {
        $output .= '<div class="shadcn-table-container">';
    }

    $output .= '<table id="' . $table_id . '" class="' . esc_attr($table_class) . '">';

    if (!empty($args['headers'])) {
        $output .= '<thead>';
        $output .= '<tr>';

        foreach ($args['headers'] as $key => $header) {
            $header_class = '';

            if ($args['sortable']) {
                $header_class = 'shadcn-table-sortable';
                $sort_dir = ($args['default_sort'] === $key && $args['default_sort_dir'] === 'asc') ? 'desc' : 'asc';

                $is_sorted = ($args['default_sort'] === $key);
                $sort_indicator = '';

                if ($is_sorted) {
                    $sort_indicator = $args['default_sort_dir'] === 'asc' ? ' ▲' : ' ▼';
                }

                $output .= '<th class="' . esc_attr($header_class) . '" data-sort-key="' . esc_attr($key) . '" data-sort-dir="' . esc_attr($sort_dir) . '">';
                $output .= esc_html($header) . $sort_indicator;
                $output .= '</th>';
            } else {
                $output .= '<th>' . esc_html($header) . '</th>';
            }
        }

        $output .= '</tr>';
        $output .= '</thead>';
    }

    // Table Body
    $output .= '<tbody>';

    if (empty($args['rows'])) {
        $output .= '<tr>';
        $output .= '<td colspan="' . count($args['headers']) . '">' . esc_html($args['empty_message']) . '</td>';
        $output .= '</tr>';
    } else {
        foreach ($args['rows'] as $row) {
            $output .= '<tr>';

            foreach ($args['headers'] as $key => $header) {
                $cell_value = isset($row[$key]) ? $row[$key] : '';
                $output .= '<td>' . $cell_value . '</td>';
            }

            $output .= '</tr>';
        }
    }

    $output .= '</tbody>';
    $output .= '</table>';

    if ($args['responsive']) {
        $output .= '</div>';
    }

    if ($args['sortable']) {
        $output .= '<script>
        document.addEventListener("DOMContentLoaded", function() {
            const table = document.getElementById("' . $table_id . '");
            if (!table) return;

            const headers = table.querySelectorAll("th[data-sort-key]");
            headers.forEach(header => {
                header.addEventListener("click", function() {
                    const sortKey = this.getAttribute("data-sort-key");
                    const sortDir = this.getAttribute("data-sort-dir");

                    // Update sort direction indicators
                    headers.forEach(h => {
                        h.textContent = h.textContent.replace(" ▲", "").replace(" ▼", "");
                        if (h === this) {
                            h.textContent += (sortDir === "asc") ? " ▲" : " ▼";
                        }
                    });

                    // Sort the table
                    const tbody = table.querySelector("tbody");
                    const rows = Array.from(tbody.querySelectorAll("tr"));

                    rows.sort((a, b) => {
                        const aValue = a.cells[Array.from(headers).indexOf(this)].textContent.trim();
                        const bValue = b.cells[Array.from(headers).indexOf(this)].textContent.trim();

                        // Check if values are numbers
                        const aNum = parseFloat(aValue);
                        const bNum = parseFloat(bValue);

                        if (!isNaN(aNum) && !isNaN(bNum)) {
                            return sortDir === "asc" ? aNum - bNum : bNum - aNum;
                        }

                        // String comparison
                        return sortDir === "asc"
                            ? aValue.localeCompare(bValue)
                            : bValue.localeCompare(aValue);
                    });

                    // Re-append sorted rows
                    rows.forEach(row => tbody.appendChild(row));

                    // Toggle sort direction for next click
                    this.setAttribute("data-sort-dir", sortDir === "asc" ? "desc" : "asc");
                });
            });

            // Perform default sort if specified
            if ("' . $args['default_sort'] . '") {
                const defaultHeader = table.querySelector(\'th[data-sort-key="' . $args['default_sort'] . '"]\');
                if (defaultHeader) {
                    defaultHeader.click();
                }
            }
        });
        </script>';
    }

    return $output;
    }
}

/**
 * Simple helper function to render a table caption
 */
if (!function_exists('shadcn_table_caption')) {
    function shadcn_table_caption($text, $class = '') {
    $base_class = 'shadcn-mt-4 shadcn-text-sm shadcn-text-muted-foreground';
    if (!empty($class)) {
        $base_class .= ' ' . $class;
    }

    return '<caption class="' . esc_attr($base_class) . '">' . esc_html($text) . '</caption>';
    }
}
