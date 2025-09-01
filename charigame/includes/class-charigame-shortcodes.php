<?php

namespace ChariGame\Includes;

class ChariGame_Shortcodes {

	public function register(): void {
		add_shortcode('charigame_login_form', [$this, 'render_login_form']);
	}

	public function render_login_form($atts): string {
		if (!session_id()) {
			session_start();
		}

		$campaign_id = get_the_ID();

		if (isset($_SESSION['charigame_user_id']) && $_SESSION['charigame_campaign_id'] == $campaign_id) {
			return $this->render_logged_in_content($campaign_id);
		}

		$atts = shortcode_atts([
			'title' => 'Spendenkampagne',
			'button_text' => 'Login',
			'login_text' => 'Bitte geben Sie Ihren Zugangscode ein, den Sie per Mail erhalten haben.',
			'logo' => '',
			'placeholder' => 'Zugangscode hier eingeben',
			'campaign_id' => $campaign_id,
		], $atts);

		$logo = carbon_get_post_meta($campaign_id, 'login_form_logo');
		$login_text = carbon_get_post_meta($campaign_id, 'login_form_text');

		if ($logo) {
			$atts['logo'] = $logo;
		}
		if ($login_text) {
			$atts['login_text'] = $login_text;
		}

		ob_start();
		?>
		<div id="charigame-form-container">
			<section class="charigame-login-section">
				<div class="absolute top-0 w-full min-h-[90px] opacity-30"></div>
				<div class="absolute inset-0 opacity-10 template-background"></div>

				<div class="relative flex min-h-screen flex-col justify-between pt-12">
					<div class="container relative z-10 mx-auto max-w-xl py-3">
						<div class="absolute inset-0 mt-16 -z-10 transform -skew-y-6 rounded-3xl bg-gradient-to-r from-secondary to-primary shadow-lg sm:-rotate-6 sm:skew-y-0"></div>

						<a href="<?php echo home_url(); ?>" class="block w-max">
							<?php if ($atts['logo']): ?>
								<img alt="Logo" class="mb-8 h-16 w-16 rounded-full"
									 src="<?php echo wp_get_attachment_image_url($atts['logo']); ?>">
							<?php endif; ?>
						</a>

						<div class="w-full max-h-12 -mb-1 rotate-180"></div>

						<div class="bg-white p-8">
							<h1 class="pb-10 text-4xl font-bold"><?php echo esc_html($atts['title']); ?></h1>
							<p class="mb-6 text-xl text-gray-600">
								<?php echo esc_html($atts['login_text']); ?>
							</p>

							<form id="charigame-login-form" class="space-y-4">
								<?php wp_nonce_field('charigame_login_nonce', 'charigame_nonce'); ?>
								<input type="hidden" name="campaign_id" value="<?php echo esc_attr($campaign_id); ?>">

								<div>
									<input type="text" id="unlock_code" name="charigame_key"
										   placeholder="<?php echo esc_attr($atts['placeholder']); ?>"
										   class="w-full rounded border px-3 py-2 text-gray-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
										   required>
								</div>

								<div class="relative">
									<button type="submit"
											class="flex items-center gap-3 rounded-lg bg-secondary px-6 py-3 font-medium text-white transition-all hover:bg-white hover:text-secondary hover:ring-2 hover:ring-secondary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
										<svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
												  d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
										</svg>
										<?php echo esc_html($atts['button_text']); ?>
									</button>
								</div>

								<div class="error-message hidden rounded bg-red-50 p-3 text-red-600 text-sm"></div>
								<div class="loading-message hidden rounded bg-blue-50 p-3 text-blue-600 text-sm">
									Anmeldung läuft...
								</div>
							</form>
						</div>
					</div>
				</div>
			</section>
		</div>

		<script>
			document.getElementById('charigame-login-form').addEventListener('submit', function (e) {
				e.preventDefault();

				const formData = new FormData(this);
				formData.append('action', 'charigame_login');
				formData.append('nonce', document.querySelector('[name="charigame_nonce"]').value);

				// Loading state
				document.querySelector('.loading-message').classList.remove('hidden');
				document.querySelector('.error-message').classList.add('hidden');

				fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
					method: 'POST',
					body: formData
				})
					.then(response => response.json())
					.then(data => {
						document.querySelector('.loading-message').classList.add('hidden');

						if (data.success) {
							const statusMessage = document.createElement('div');
							statusMessage.className = 'success-message rounded bg-green-50 p-3 text-green-600 text-sm';
							statusMessage.innerHTML = data.data.message || 'Login erfolgreich. Weiterleitung...';
							document.querySelector('.error-message').before(statusMessage);

							console.log('Login successful. Redirecting to campaign page...');

							setTimeout(function() {

								if (data.data.redirect) {
									window.location.href = data.data.redirect;
								} else {

									window.location.href = window.location.pathname;
								}
							}, 1000);
						} else {
							document.querySelector('.error-message').textContent = data.data.message;
							document.querySelector('.error-message').classList.remove('hidden');
						}
					})
					.catch(error => {
						document.querySelector('.loading-message').classList.add('hidden');
						document.querySelector('.error-message').textContent = 'Ein Fehler ist aufgetreten.';
						document.querySelector('.error-message').classList.remove('hidden');
						console.error('Error:', error);
					});
			});
		</script>
		<?php
		return ob_get_clean();
	}

	/**
	 * Rendert den Content für bereits eingeloggte User
	 */
	private function render_logged_in_content($campaign_id): string {
		$login_handler = new ChariGame_Login_Handler();
		return $login_handler->get_landing_page_content($campaign_id);
	}
}
