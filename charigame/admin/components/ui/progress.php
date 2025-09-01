<?php
/**
 * Shadcn Progress Component
 * A PHP implementation of the shadcn/ui Progress component
 */

/**
 * Renders a progress bar
 *
 * @param array $args Arguments for configuring the progress bar
 * @return string The rendered progress bar HTML
 */
if (!function_exists('shadcn_progress')) {
    function shadcn_progress($args = []) {
    $defaults = [
        'id' => 'progress-' . uniqid(),
        'class' => '',
        'value' => 0,
        'max' => 100,
        'label' => '',
        'show_percentage' => false,
        'color' => '',
    ];

    $args = array_merge($defaults, $args);

    // Ensure value is between 0 and max
    $value = max(0, min($args['value'], $args['max']));
    $percentage = ($args['max'] > 0) ? ($value / $args['max']) * 100 : 0;

    $container_class = 'shadcn-progress';
    if (!empty($args['class'])) {
        $container_class .= ' ' . $args['class'];
    }

    $indicator_class = 'shadcn-progress-indicator';
    if (!empty($args['color'])) {
        $indicator_class .= ' ' . $args['color'];
    }

    $output = '';

    if (!empty($args['label'])) {
        $output .= '<label for="' . esc_attr($args['id']) . '" class="shadcn-text-sm shadcn-font-medium shadcn-mb-2 shadcn-block">' . esc_html($args['label']);

        if ($args['show_percentage']) {
            $output .= ' <span class="shadcn-text-muted-foreground">' . round($percentage) . '%</span>';
        }

        $output .= '</label>';
    }

    $output .= '<div id="' . esc_attr($args['id']) . '" role="progressbar" aria-valuemin="0" aria-valuemax="' . esc_attr($args['max']) . '" aria-valuenow="' . esc_attr($value) . '" class="' . esc_attr($container_class) . '">';
    $output .= '<div class="' . esc_attr($indicator_class) . '" style="transform: translateX(-' . (100 - $percentage) . '%);"></div>';
    $output .= '</div>';

    if (empty($args['label']) && $args['show_percentage']) {
        $output .= '<div class="shadcn-text-sm shadcn-text-muted-foreground shadcn-mt-1">' . round($percentage) . '%</div>';
    }

    return $output;
    }
}
