<?php
/** @var array $attributes */
/** @var string $content */

$icon = isset($attributes['icon']) ? sanitize_file_name($attributes['icon']) : '';
$headline = isset($attributes['headline']) ? $attributes['headline'] : '';
$body = isset($attributes['body']) ? $attributes['body'] : '';
$textColor = isset($attributes['textColor']) ? $attributes['textColor'] : 'text-inherit';
$backgroundColor = isset($attributes['backgroundColor']) ? $attributes['backgroundColor'] : 'bg-primary';
$iconColor = isset($attributes['iconColor']) ? $attributes['iconColor'] : 'text-secondary';
$iconBgColor = isset($attributes['iconBgColor']) ? $attributes['iconBgColor'] : 'bg-secondary';
?>
<div class="flex flex-col items-center p-4 h-full rounded-lg <?php echo esc_attr($backgroundColor); ?>">
    <div class="shrink-0 rounded-lg p-4 mb-4 <?php echo esc_attr($textColor); ?> <?php echo esc_attr($iconBgColor); ?>">
        <?php if ($icon): ?>
            <?php
            $iconPath = plugin_dir_path(dirname(dirname(__DIR__))) . 'assets/icons/outline/' . $icon . '.svg';
            if (file_exists($iconPath)) {
                $svg = file_get_contents($iconPath);

                $svg = preg_replace('/<svg(.*?)>/','<svg$1 class="w-6 h-6 ' . esc_attr($iconColor) . '" fill="currentColor">', $svg);
                echo $svg;
            } else {

                echo '<img class="w-6 h-6 ' . esc_attr($iconColor) . '" src="' . esc_url(plugin_dir_url(dirname(dirname(__DIR__))) . 'assets/icons/outline/' . $icon . '.svg') . '" alt="' . esc_attr($icon) . '">';
            }
            ?>
        <?php endif; ?>
    </div>
    <span class="text-lg font-bold text-center mb-2 <?php echo esc_attr($textColor); ?>">
        <?php echo wp_kses_post($headline); ?>
    </span>
    <span class="mt-1 text-sm text-gray-600 text-center <?php echo esc_attr($textColor); ?>">
        <?php echo wp_kses_post($body); ?>
    </span>
</div>
