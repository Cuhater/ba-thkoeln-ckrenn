<?php
/** @var array $attributes */
/** @var string $content */

$background_type = isset($attributes['background_type']) ? $attributes['background_type'] : 'bg-white';
?>

<section id="spiel"
         class="py-12 <?php echo esc_attr($background_type); ?> ">
    <div id="bottom-seperator" class="seperator-white">
<!--		--><?php //echo $torn_bottom; ?>
    </div>
    <h2 id="gamesection-headline"
        class="font-main text-white text-4xl text-center mb-10 max-sm:mx-4">Das Spiel</h2>
    <?php
    $gameType = isset($attributes['gameType']) ? $attributes['gameType'] : '';
    if ($gameType) {
        $game_template_path = plugin_dir_path(dirname(dirname(__DIR__))) . 'src/games/' . $gameType . '/game.php';
        if (file_exists($game_template_path)) {
            ?>
            <?php require $game_template_path; ?>
            <?php
        } else {
            echo '<div class="text-red-500">Kein Spiel-Template für "' . esc_html($gameType) . '" gefunden.</div>';
        }
    }
    ?>
</section>
