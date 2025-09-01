<?php
require_once plugin_dir_path( __FILE__ ) . '../../../includes/class-charigame-helper.php';
use ChariGame\Includes\Helper;

/** @var array $attributes */
/** @var string $content */

$logo = $attributes['logo'] ?? '';
$campaign_title = $attributes['campaign_title'] ?? 'Willkommen zu unserer Kampagne';
$campaign_desc_text = $attributes['campaign_desc_text'] ?? 'Beschreibungstext der Kampagne';
$game_type_title = $attributes['game_type_title'] ?? 'Spieltyp';
$game_type_excerpt = $attributes['game_type_excerpt'] ?? 'Beschreibung des Spieltyps';
$text_color = $attributes['textColor'] ?? 'text-inherit';
$border_style = $attributes['borderStyle'] ?? 'border-none';

?>

<section id="intro" class="relative pb-8 <?php echo esc_attr( $attributes['background_type'] ) ?>">
	<div class="template-background rounded-b-3xl h-full w-full absolute top-0 opacity-10 bg-cover"></div>

	<div class="container relative py-12 sm:max-w-2xl sm:mx-auto">
		<a class="absolute block w-max" href="<?php echo esc_url(home_url()); ?>">
			<img alt="Logo"
				 class="mb-8 w-16 l-16 rounded-full"
				 src="<?php echo esc_url($logo); ?>">
		</a>
		<div class="backplate mt-20 mb-10 z-[-1] absolute inset-0 bg-gradient-to-r from-secondary to-primary shadow-lg transform -skew-y-6 sm:skew-y-0 sm:-rotate-6 sm:rounded-3xl"></div>
		<div class="bg-white mt-20 p-8 rounded-t-3xl <?php echo esc_attr($border_style); ?> <?php echo $border_style === 'border-torn' ? 'border-torn-top' : ''; ?> <?php echo $border_style === 'border-wavy' ? 'border-wavy-bottom' : ''; ?>">
			<div class="container sm:max-w-xl sm:mx-auto">

				<h1 class="text-4xl pb-10 flex justify-center <?php echo esc_attr( $text_color )?>"><?php echo esc_html($campaign_title); ?></h1>
				<div class="flex flex-col">
					<div class="block">
						<?php echo $campaign_desc_text; ?>
					</div>
				</div>
				<div class="flex justify-center mt-4">
					<p class="text-center"><strong>Wir spielen <?php echo esc_html($game_type_title); ?>!</strong></p>
				</div>

			</div>
			<div class="flex justify-center">
				<a href="#spiel">
					<button class="flex flex-row items-center justify-between mt-8 px-4 xs:px-8 py-3 xs:py-[1.125rem] w-max bg-secondary hover:bg-white rounded-lg text-white font-medium text-lg xs:text-xl hover:text-secondary hover:ring-2 hover:ring-secondary cursor-pointer">
						Zum Spiel
					</button>
				</a>
			</div>
				<p class="text-center mt-4 -mb-4 font-light">
					Die Teilnahme an dieser Aktion ist exklusiv für Sie bis zum <?php echo Helper::charigame_compute_valid_until(get_the_ID())['valid_until'] ?> möglich.
				</p>

		</div>
	</div>
</section>
