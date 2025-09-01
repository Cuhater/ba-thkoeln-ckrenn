<?php
/**
 * Shadcn Card Component
 * A PHP implementation of the shadcn/ui Card component
 */

/**
 * Renders a card container
 *
 * @param array $args Arguments for configuring the card
 * @return string The rendered card HTML
 */
if (!function_exists('shadcn_card')) {
    function shadcn_card($args = []) {
    $defaults = [
        'id' => '',
        'class' => '',
        'content' => '',
    ];

    $args = array_merge($defaults, $args);

    $id_attr = !empty($args['id']) ? ' id="' . esc_attr($args['id']) . '"' : '';

    $card_class = 'shadcn-card';
    if (!empty($args['class'])) {
        $card_class .= ' ' . $args['class'];
    }

    $output = '<div' . $id_attr . ' class="' . esc_attr($card_class) . '">';
    $output .= $args['content'];
    $output .= '</div>';

    return $output;
    }
}

/**
 * Renders a card header
 */
if (!function_exists('shadcn_card_header')) {
    function shadcn_card_header($args = []) {
    $defaults = [
        'class' => '',
        'content' => '',
    ];

    $args = array_merge($defaults, $args);

    $class = 'shadcn-card-header';
    if (!empty($args['class'])) {
        $class .= ' ' . $args['class'];
    }

    return '<div class="' . esc_attr($class) . '">' . $args['content'] . '</div>';
    }
}

/**
 * Renders a card title
 */
if (!function_exists('shadcn_card_title')) {
    function shadcn_card_title($args = []) {
    $defaults = [
        'class' => '',
        'text' => '',
    ];

    $args = array_merge($defaults, $args);

    $class = 'shadcn-text-2xl shadcn-font-semibold shadcn-leading-none shadcn-tracking-tight';
    if (!empty($args['class'])) {
        $class .= ' ' . $args['class'];
    }

    return '<h3 class="' . esc_attr($class) . '">' . esc_html($args['text']) . '</h3>';
    }
}

/**
 * Renders a card description
 */
if (!function_exists('shadcn_card_description')) {
    function shadcn_card_description($args = []) {
    $defaults = [
        'class' => '',
        'text' => '',
    ];

    $args = array_merge($defaults, $args);

    $class = 'shadcn-text-sm shadcn-text-muted-foreground';
    if (!empty($args['class'])) {
        $class .= ' ' . $args['class'];
    }

    return '<p class="' . esc_attr($class) . '">' . esc_html($args['text']) . '</p>';
    }
}

/**
 * Renders a card content
 */
if (!function_exists('shadcn_card_content')) {
    function shadcn_card_content($args = []) {
    $defaults = [
        'class' => '',
        'content' => '',
    ];

    $args = array_merge($defaults, $args);

    $class = 'shadcn-p-6 shadcn-pt-0';
    if (!empty($args['class'])) {
        $class .= ' ' . $args['class'];
    }

    return '<div class="' . esc_attr($class) . '">' . $args['content'] . '</div>';
    }
}

/**
 * Renders a card footer
 */
if (!function_exists('shadcn_card_footer')) {
    function shadcn_card_footer($args = []) {
    $defaults = [
        'class' => '',
        'content' => '',
    ];

    $args = array_merge($defaults, $args);

    $class = 'shadcn-flex shadcn-items-center shadcn-p-6 shadcn-pt-0';
    if (!empty($args['class'])) {
        $class .= ' ' . $args['class'];
    }

    return '<div class="' . esc_attr($class) . '">' . $args['content'] . '</div>';
    }
}
