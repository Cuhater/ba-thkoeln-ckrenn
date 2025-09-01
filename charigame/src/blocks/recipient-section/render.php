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
$background_type = !empty($attributes['background_type']) ? $attributes['background_type'] : 'bg-white';
$textColor = !empty($attributes['textColor']) ? $attributes['textColor'] : 'text-inherit';

$donation_manager = new ChariGame_Donation_Manager();

$campaign_id = 0;

if (function_exists('get_post_type') && get_post_type() === 'charigame-campaign') {
    $campaign_id = get_the_ID();
}

if (!$campaign_id) {
    if (!session_id()) { @session_start(); }
    if (!empty($_SESSION['charigame_campaign_id'])) {
        $campaign_id = intval($_SESSION['charigame_campaign_id']);
    }
}

if (!$campaign_id && isset($_GET['campaign_id'])) {
    $campaign_id = intval($_GET['campaign_id']);
}

$recipients_ids = $campaign_id ? $donation_manager->get_campaign_recipients($campaign_id) : [];
$all_recipients_data = [];
foreach ($recipients_ids as $recipient_id) {
    $all_recipients_data[] = ChariGame_Donation_Manager::get_recipient_data($recipient_id);
}
?>

<section id="recipients" class="<?php echo esc_attr($background_type); ?> relative">
	<div class="z-10 container relative py-12 sm:max-w-6xl sm:mx-auto">

		<h2 class="font-main <?php echo esc_attr($textColor); ?> text-4xl pb-10 flex justify-center text-center mb-8 max-sm:px-2">Wir spenden an diese Organisationen!</h2>

		<div class="flex lg:flex-row flex-col gap-4">

			<?php
			$index = 0;
			if ( ! empty( $all_recipients_data ) ) {
				foreach ( $all_recipients_data as $recipient ) { ?>
					<div id="<?php echo "recipient-" . $index ?>"
						 class="recipient basis-1/3 overflow-hidden mx-8 lg:mx-0 bg-gradient-to-r from-primary via-teritary2 to-secondary p-0.5 rounded-t-3xl shadow-xl max-sm:mb-4">
						<div class="bg-white p-8 flex flex-col items-center lg:-mb-1 rounded-t-3xl shadow-sm border border-gray-100 h-full">
							<img alt="Charity Logo"
								 class="aspect-square max-w-48 mb-4 object-contain"
								 src="<?php echo wp_get_attachment_image_url($recipient['logo'], 'full'); ?>">
							<h3 class="font-main font-bold text-2xl pb-10 flex justify-center text-center min-h-20 <?php echo esc_attr($textColor); ?>"><?php echo $recipient['title']; ?></h3>
							<div class="prose prose-lg max-w-none leading-normal"><?php echo wpautop( $recipient['description'] ); ?></div>
						</div>
					</div>
					<?php
					$index++;
				}
			} else {
				echo 'No recipients found.';
			}
			?>

		</div>
	</div>
</section>
