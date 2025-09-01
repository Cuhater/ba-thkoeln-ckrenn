<?php
/** @var array $attributes */
/** @var string $content */

$background_type = isset($attributes['background_type']) ? $attributes['background_type'] : 'bg-white';
$textColor = isset($attributes['textColor']) ? $attributes['textColor'] : 'text-inherit';
$headline = isset($attributes['headline']) ? $attributes['headline'] : 'So funktioniert das Spiel';
$columns = isset($attributes['columns']) ? $attributes['columns'] : 'md:grid-cols-2 lg:grid-cols-4';
$gap = isset($attributes['gap']) ? $attributes['gap'] : 'gap-8';

$classes = join(" ", [
    "w-full md:grid flex flex-col",
    $gap,
    $columns,
]);
?>

<section id="how-to-play" class="<?php echo esc_attr($background_type); ?> relative">
    <div class="z-10 container relative py-12 sm:max-w-6xl sm:mx-auto">
        <h2 class="font-main text-4xl pb-10 flex justify-center text-center mb-8 max-sm:px-2 <?php echo esc_attr($textColor); ?>">
            <?php echo esc_html($headline); ?>
        </h2>
        <div class="<?php echo esc_attr($classes); ?>">
            <?php echo $content; ?>
        </div>
    </div>
</section>