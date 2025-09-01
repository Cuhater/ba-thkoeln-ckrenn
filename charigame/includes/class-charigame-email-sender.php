<?php

namespace ChariGame\Includes;

use DateTime;

/**
 * ChariGame Email Sender
 *
 * Responsible for sending emails based on email templates.
 *
 * @package ChariGame
 * @subpackage Includes
 * @since 1.0.0
 */

/**
 * Email Sender Class
 *
 * Handles email sending, templates, and scheduling.
 */
class ChariGame_Email_Sender {

	/**
	 * Register hooks and actions.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'phpmailer_init', array( $this, 'setup_smtp' ) );

		add_action( 'charigame_send_email_hook', array( $this, 'send_email_by_campaign_id' ), 10, 1 );

		add_action( 'wp_ajax_charigame_send_test_email', array( $this, 'handle_send_test_email' ) );
		add_action( 'wp_ajax_charigame_preview_email_template', array( $this, 'handle_preview_email_template' ) );

		add_action( 'save_post_charigame-campaign', array( $this, 'schedule_campaign_emails' ), 10, 3 );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Schedule campaign emails on campaign save.
	 *
	 * This is a placeholder to satisfy the hooked callback. Implement scheduling logic
	 * (e.g., using wp_schedule_single_event) as needed.
	 *
	 * @param int      $post_id The post ID.
	 * @param \WP_Post $post    The post object.
	 * @param bool     $update  Whether this is an existing post being updated.
	 *
	 * @return void
	 */
	public function schedule_campaign_emails( int $post_id, $post, bool $update ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( $post instanceof \WP_Post && $post->post_type !== 'charigame-campaign' ) {
			return;
		}

		return;
	}

	/**
	 * Configure SMTP settings for email sending.
	 *
	 * @param object $phpmailer The PHPMailer instance.
	 *
	 * @return void
	 */
	public function setup_smtp( $phpmailer ): void {
		$smtp_settings = get_option( 'charigame_smtp_settings', array() );

		if ( empty( $smtp_settings ) || empty( $smtp_settings['smtp_host'] ) ) {
			return;
		}

		$phpmailer->isSMTP();
		// phpcs:disable WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase
		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$phpmailer->Host     = $smtp_settings['smtp_host'] ?? '';
		$phpmailer->SMTPAuth = true;
		$phpmailer->Port     = intval( $smtp_settings['smtp_port'] ?? 587 );
		$phpmailer->Username = $smtp_settings['smtp_user'] ?? '';

		$phpmailer->Password = defined( 'CHARIGAME_SMTP_PASS' ) ? CHARIGAME_SMTP_PASS : ( $smtp_settings['smtp_pass'] ?? '' );

		$phpmailer->SMTPSecure = $smtp_settings['smtp_secure'] ?? 'tls';
		// phpcs:enable
		if ( ! empty( $smtp_settings['smtp_from'] ) ) {
			$phpmailer->setFrom(
				$smtp_settings['smtp_from'],
				$smtp_settings['smtp_fromname'] ?? get_bloginfo( 'name' )
			);
		}
	}

	/**
	 * AJAX handler for sending a test email.
	 *
	 * @return void
	 */
	public function handle_send_test_email(): void {
		check_admin_referer( 'charigame_email_test_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'No permission' ) );

			return;
		}

		$email       = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$template_id = isset( $_POST['template_id'] ) ? intval( $_POST['template_id'] ) : 0;

		if ( empty( $email ) ) {
			wp_send_json_error( array( 'message' => 'Please provide a valid email address' ) );

			return;
		}

		if ( empty( $template_id ) ) {
			wp_send_json_error( array( 'message' => 'Please select an email template' ) );

			return;
		}

		$result = $this->send_test_email( $email, $template_id );

		if ( $result ) {
			wp_send_json_success( array( 'message' => 'Test email sent successfully' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Error sending test email' ) );
		}
	}

	/**
	 * Send a test email with the specified template.
	 *
	 * @param string $email The recipient email address.
	 * @param int    $template_id The email template ID.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function send_test_email( string $email, int $template_id ): bool {
		$test_data = array(
			'name'          => 'Max Mustermann',
			'first_name'    => 'Max',
			'last_name'     => 'Mustermann',
			'game_code'     => 'TEST123456',
			'campaign_id'   => 0,
			'campaign_name' => 'testcampaign',
			'valid_from'    => gmdate( 'd.m.Y' ),
			'valid_until'   => gmdate( 'd.m.Y', strtotime( '+4 weeks' ) ),
			'campaign_url'  => home_url( '/spendenspiel/testcampaign' ),
		);

		return $this->send_email_by_template( $email, $template_id, $test_data );
	}

	/**
	 * Send an email based on a campaign ID.
	 *
	 * @param int $campaign_id The campaign ID.
	 *
	 * @return void
	 */
	public function send_email_by_campaign_id( int $campaign_id ): void {
		$campaign = get_post( $campaign_id );
		if ( ! $campaign || $campaign->post_type !== 'charigame-campaign' ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( message: "ChariGame Email Sender: Invalid campaign ID: $campaign_id" );

			return;
		}

		$template_id = carbon_get_post_meta( $campaign_id, 'email_template' );
		if ( empty( $template_id ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( message: "ChariGame Email Sender: No email template defined for campaign $campaign_id" );

			return;
		}

		$dispatch_option = carbon_get_post_meta( $campaign_id, 'dispatch_date_option' );
		$is_all_users    = ( $dispatch_option === 'dispatch' );

		$users = $this->get_email_recipients( $is_all_users );
		if ( empty( $users ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( message: "ChariGame Email Sender: No recipients found for campaign $campaign_id" );

			return;
		}

		$campaign_slug = $campaign->post_name;
		$success_count = 0;
		$error_count   = 0;

		foreach ( $users as $user ) {
			$game_code = $this->get_game_code_by_email( $user->email, $campaign_slug );
			if ( empty( $game_code ) ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( message: "ChariGame Email Sender: No game code found for {$user->email} in campaign $campaign_slug" );
				++$error_count;
				continue;
			}

			// Prepare email data.
			$game_data  = $this->get_game_data_by_code( $game_code );
			$email_data = array(
				'name'          => $user->first_name . ' ' . $user->last_name,
				'first_name'    => $user->first_name,
				'last_name'     => $user->last_name,
				'game_code'     => $game_code,
				'campaign_id'   => $campaign_id,
				'campaign_name' => $campaign_slug,
				'valid_from'    => $game_data ? gmdate( 'd.m.Y', strtotime( $game_data->valid_from ) ) : gmdate( 'd.m.Y' ),
				'valid_until'   => $game_data ? gmdate( 'd.m.Y', strtotime( $game_data->valid_until ) ) : gmdate( 'd.m.Y', strtotime( '+4 weeks' ) ),
				'campaign_url'  => home_url( "/spendenspiel/$campaign_slug" ),
			);

			// Send email.
			$result = $this->send_email_by_template( $user->email, $template_id, $email_data );
			if ( $result ) {
				// Mark email as sent.
				update_post_meta( $user->ID, 'email_sent', 1 );
				++$success_count;
			} else {
				++$error_count;
			}
		}
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( message: "ChariGame Email Sender: Campaign $campaign_id - $success_count emails sent successfully, $error_count errors" );
	}

	/**
	 * Send an email with a specific template and data.
	 *
	 * @param string $email The recipient email address.
	 * @param int    $template_id The email template ID.
	 * @param array  $data The data for the email.
	 *
	 * @return bool True on success, false on failure.
	 */
	private function send_email_by_template( string $email, int $template_id, array $data ): bool {

		$post_content = get_post_field( 'post_content', $template_id );
		$blocks       = parse_blocks( $post_content );
		$email_block  = reset( $blocks );
		if ( empty( $email_block ) || $email_block['blockName'] !== 'charigame/email-template' ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( message: "ChariGame Email Sender: No valid email template block found in template ID: $template_id" );

			return false;
		}

		$attributes = $email_block['attrs'];

		$subject = carbon_get_post_meta( $template_id, 'email_subject' );

		$header_image             = $attributes['header_image'] ?? '';
		$header_claim             = $attributes['header_claim'] ?? '';
		$header_claim_color       = $attributes['header_claim_color'] ?? '#2673AA';
		$headline                 = $attributes['headline'] ?? '';
		$headline_color           = $attributes['headline_color'] ?? '#000000';
		$content                  = $attributes['content'] ?? '';
		$cta_text                 = $attributes['cta_text'] ?? 'Zur Spendenaktion!';
		$cta_color                = $attributes['cta_color'] ?? '#2673AA';
		$cta_url                  = $attributes['cta_url'] ?? '';
		$info                     = $attributes['info'] ?? '';
		$signature                = $attributes['signature'] ?? '';
		$social_media             = $attributes['social_media'] ?? array();
		$imprint_title            = $attributes['imprint_title'] ?? 'Impressum:';
		$imprint_background_color = $attributes['imprint_background_color'] ?? '#28333E';
		$imprint_text_color       = $attributes['imprint_text_color'] ?? '#FFFFFF';
		$imprint_content          = $attributes['imprint_content'] ?? '';

		$game_code_text = $attributes['game_code_text'] ?? 'Den Code können Sie unter der folgenden Adresse eingeben:';
		$validity_text  = $attributes['validity_text'] ?? 'Die Teilnahme ist exklusiv für Sie vom {valid_from} bis zum {valid_until} verfügbar.';
		$closing_text   = $attributes['closing_text'] ?? 'Wir freuen uns auf Ihre Teilnahme!';

		$subject      = $this->replace_variables( $subject, $data );
		$header_claim = $this->replace_variables( $header_claim, $data );
		$headline     = $this->replace_variables( $headline, $data );
		$content      = $this->replace_variables( $content, $data );
		$info         = $this->replace_variables( $info, $data );
		$signature    = $this->replace_variables( $signature, $data );

		$company_settings = carbon_get_post_meta( 'carbon_fields_theme_options', 'company_settings' );
		$company_name     = $company_settings['company_name'] ?? get_bloginfo( 'name' );
		$company_street   = $company_settings['company_street'] ?? '';
		$company_city     = $company_settings['company_city'] ?? '';

		if ( empty( $cta_url ) ) {
			$cta_url = $data['campaign_url'];
		}

		$add_code_parameter = $attributes['add_code_parameter'] ?? true;
		if ( $add_code_parameter && ! empty( $data['game_code'] ) ) {
			$url_separator = ( strpos( $cta_url, '?' ) !== false ) ? '&' : '?';
			$cta_url      .= $url_separator . 'code=' . rawurlencode( $data['game_code'] );
		}

		$message = $this->generate_email_html(
			$header_image,
			$header_claim,
			$header_claim_color,
			$headline,
			$headline_color,
			$content,
			$cta_text,
			$cta_color,
			$cta_url,
			$info,
			$signature,
			$social_media,
			$company_name,
			$company_street,
			$company_city,
			$data,
			$imprint_background_color,
			$imprint_text_color,
			$imprint_title,
			$imprint_content,
			$game_code_text,
			$validity_text,
			$closing_text
		);

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		return wp_mail( $email, $subject, $message, $headers );
	}

	/**
	 * Generate the HTML for the email.
	 *
	 * @param bool $all_users Whether to get all users or only those with birthdays today.
	 *
	 * @return array Array of user objects.
	 */
	private function get_email_recipients( bool $all_users = false ): array {
		$args = array(
			'post_type'      => 'charigame-user',
			'posts_per_page' => - 1,
			'meta_query'     => array(),
		);

		if ( ! $all_users ) {
			$current_date         = gmdate( 'Ymd' );
			$args['meta_query'][] = array(
				'key'     => 'birthday',
				'value'   => $current_date,
				'compare' => '=',
			);
		}

		$args['meta_query'][] = array(
			'key'     => 'email_sent',
			'value'   => '1',
			'compare' => '!=',
		);

		$query = new \WP_Query( $args );
		$users = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$user             = new \stdClass();
				$user->ID         = get_the_ID();
				$first_name       = get_post_meta( $user->ID, 'first-name', true );
				$user->first_name = $first_name ? $first_name : 'User';
				$last_name        = get_post_meta( $user->ID, 'last-name', true );
				$user->last_name  = $last_name ? $last_name : '';
				$user->email      = get_post_meta( $user->ID, 'email', true );

				if ( ! empty( $user->email ) ) {
					$users[] = $user;
				}
			}
			wp_reset_postdata();
		}

		return $users;
	}

	/**
	 * AJAX-Handler für die Vorschau eines E-Mail-Templates
	 */
	public function handle_preview_email_template(): void {

		if ( ! check_ajax_referer( 'charigame_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => 'Sicherheitsüberprüfung fehlgeschlagen' ) );

			return;
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => 'Keine Berechtigung' ) );

			return;
		}

		$email_data = isset( $_POST['email_data'] )
			? array_map( 'sanitize_text_field', wp_unslash( $_POST['email_data'] ) )
			: array();

		if ( empty( $email_data ) ) {
			wp_send_json_error( array( 'message' => 'Keine E-Mail-Daten übermittelt' ) );

			return;
		}

		$test_data = array(
			'name'          => 'Max Mustermann',
			'first_name'    => 'Max',
			'last_name'     => 'Mustermann',
			'game_code'     => 'TEST123456',
			'campaign_id'   => 0,
			'campaign_name' => 'testcampaign',
			'valid_from'    => gmdate( 'd.m.Y' ),
			'valid_until'   => gmdate( 'd.m.Y', strtotime( '+4 weeks' ) ),
			'campaign_url'  => home_url( '/spendenspiel/testcampaign' ),
		);

		$subject      = $this->replace_variables( $email_data['subject'] ?? '', $test_data );
		$header_claim = $this->replace_variables( $email_data['header_claim'] ?? '', $test_data );
		$headline     = $this->replace_variables( $email_data['headline'] ?? '', $test_data );
		$content      = $this->replace_variables( $email_data['content'] ?? '', $test_data );
		$info         = $this->replace_variables( $email_data['info'] ?? '', $test_data );
		$signature    = $this->replace_variables( $email_data['signature'] ?? '', $test_data );

		$header_image = $email_data['header_image'] ?? '';
		$cta_text     = $email_data['cta_text'] ?? 'Zur Spendenaktion!';
		$cta_color    = $email_data['cta_color'] ?? '#2673AA';
		$cta_url      = $test_data['campaign_url'];

		$company_name   = get_bloginfo( 'name' );
		$company_street = 'Musterstraße 123';
		$company_city   = '12345 Musterstadt';

		$social_media = array();

		$imprint_title            = $email_data['imprint_title'] ?? 'Impressum:';
		$imprint_background_color = $email_data['imprint_background_color'] ?? '#28333E';
		$imprint_text_color       = $email_data['imprint_text_color'] ?? '#FFFFFF';
		$imprint_content          = $email_data['imprint_content'] ?? '';

		$game_code_text = $email_data['game_code_text'] ?? 'Den Code können Sie unter der folgenden Adresse eingeben:';
		$validity_text  = $email_data['validity_text'] ?? 'Die Teilnahme ist exklusiv für Sie vom {valid_from} bis zum {valid_until} verfügbar.';
		$closing_text   = $email_data['closing_text'] ?? 'Wir freuen uns auf Ihre Teilnahme!';

		// Generate email HTML.
		$html = $this->generate_email_html(
			$header_image,
			$header_claim,
			$email_data['header_claim_color'] ?? '#2673AA',
			$headline,
			$email_data['headline_color'] ?? '#000000',
			$content,
			$cta_text,
			$cta_color,
			$cta_url,
			$info,
			$signature,
			$social_media,
			$company_name,
			$company_street,
			$company_city,
			$test_data,
			$imprint_background_color,
			$imprint_text_color,
			$imprint_title,
			$imprint_content,
			$game_code_text,
			$validity_text,
			$closing_text
		);
		wp_send_json_success(
			array(
				'html'    => $html,
				'subject' => $subject,
			)
		);
	}

	/**
	 * Enqueues the admin scripts for the email template preview.
	 *
	 * @param string $hook The current admin page hook (e.g. 'post.php').
	 *
	 * @return void
	 */
	public function enqueue_admin_scripts( $hook ): void {
		global $post;

		// Only load on the Email Template edit screen.
		if ( $hook === 'post.php' && isset( $post ) && $post->post_type === 'charigame-email-t' ) {
			wp_enqueue_script( 'sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', array(), '11.0.0', true );
			wp_enqueue_style( 'sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css', array(), '11.0.0' );

			// Animate.css for animations.
			wp_enqueue_style( 'animate-css', 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css', array(), '4.1.1' );

			// Email Template Preview Script.
			wp_enqueue_script(
				'charigame-email-template-preview',
				plugin_dir_url( __DIR__ ) . 'admin/js/email-template-preview.js',
				array( 'jquery' ),
				'1.0.0',
				true
			);

			// Localize script für Nonce und AJAX URL.
			wp_localize_script(
				'charigame-email-template-preview',
				'charigame_admin',
				array(
					'nonce'    => wp_create_nonce( 'charigame_admin_nonce' ),
					'ajax_url' => admin_url( 'admin-ajax.php' ),
				)
			);
		}
	}
}
