<?php
/**
 * Shadcn Component Loader
 * Loads shadcn/ui components and styles
 */

namespace ChariGame\Admin\Components;

class ShadcnLoader {
    /**
     * Initialize the loader
     */
    public function __construct() {
        $this->include_components();
    }

    /**
     * Include shadcn component files
     */
    private function include_components() {
        $components_dir = plugin_dir_path(__FILE__) . 'ui/';
        $components = [
            'button.php',
            'card.php',
            'table.php',
            'progress.php',
        ];

        foreach ($components as $component) {
            $file_path = $components_dir . $component;
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }
    }
}
