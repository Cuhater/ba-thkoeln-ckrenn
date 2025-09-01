<?php
require_once plugin_dir_path( __FILE__ ) . '../../../includes/class-charigame-donation-manager.php';

use ChariGame\Includes\ChariGame_Donation_Manager;

/** @var array $attributes */
/** @var string $content */

$logo               = $attributes['logo'] ?? '';
$campaign_title     = $attributes['campaign_title'] ?? 'Willkommen zu unserer Kampagne';
$campaign_desc_text = $attributes['campaign_desc_text'] ?? 'Beschreibungstext der Kampagne';
$game_type_title    = $attributes['game_type_title'] ?? 'Spieltyp';
$game_type_excerpt  = $attributes['game_type_excerpt'] ?? 'Beschreibung des Spieltyps';
$valid_until        = $attributes['valid_until'] ?? '';

$background_type = 'bg-white';
if ( ! empty( $attributes['background_type'] ) ) {
	$background_type = $attributes['background_type'];
} elseif ( ! empty( $attributes['backgroundType'] ) ) {
	$background_type = $attributes['backgroundType'];
}
$text_color = ! empty( $attributes['textColor'] ) ? $attributes['textColor'] : 'text-inherit';

$stats         = ChariGame_Donation_Manager::get_campaign_statistics( get_the_ID() );
$total_overall = $stats['total_donation'] ?? 0;
$recipients    = $stats['recipients'] ?? array();
?>

	<section class="relative <?php echo esc_attr( $background_type ); ?>">

		<div class="container relative py-12 sm:max-w-6xl sm:mx-auto">
			<h2 class="font-main <?php echo esc_attr( $text_color ); ?> text-4xl pb-10 text-center">Der aktuelle Spendenstand im Überblick</h2>
			<div class="text-center <?php echo esc_attr( $text_color ); ?>">Aktueller Spendentopf insgesamt:</div>
			<p class="text-center <?php echo esc_attr( $text_color ); ?> text-2xl">
				<?php
				if ( $total_overall > 0 ) {
					echo '<span id="donation-display">' . number_format( round( $total_overall, 0 ), 2, ',', '.' ) . '</span> €';
				} else {
					echo 'Noch keine Spende vorhanden';
				}
				?>
			</p>
			<?php if ( $total_overall > 0 && ! empty( $recipients ) ) { ?>
				<div class="z-10 container relative py-3 sm:max-w-xl sm:mx-auto">
					<h3 class="font-main <?php echo esc_attr( $text_color ); ?> text-2xl pb-8 text-center">Die Spendenverteilung im Überblick</h3>
					<?php
					foreach ( $recipients as $recipient ) {
						?>
						<div class="flex max-sm:mx-8 max-sm:mb-4">
							<img alt="<?php echo esc_attr( $recipient['title'] ); ?> Logo"
								class="aspect-square max-w-12 mb-4 object-contain rounded-lg bg-white max-sm:h-[50px] max-sm:w-[50px]"
								src="<?php echo esc_url( $recipient['logo'] ); ?>">
							<div class="grow ml-4">
								<div class="flex justify-between mb-1">
									<span class="text-base font-medium <?php echo esc_attr( $text_color ); ?>"><?php echo esc_html( $recipient['title'] ); ?></span>
									<span class="text-sm font-medium <?php echo esc_attr( $text_color ); ?> max-sm:min-w-[75px] max-sm:text-right"><?php echo number_format( $recipient['percentage'], 0 ); ?> %</span>
								</div>
								<div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
									<div class="bg-primary h-2.5 rounded-full"
										style="width:<?php echo number_format( $recipient['percentage'], 0 ); ?>%"></div>
								</div>
								<div class="<?php echo esc_attr( $text_color ); ?> text-lg mt-2"><?php echo number_format( round( $recipient['donation_amount'], 0 ), 2, ',', '.' ); ?> €</div>
							</div>
						</div>
						<?php
					}
					?>
				</div>
			<?php } ?>


		</div>

	</section>
