<?php ?>
	<div class="wrap">
		<h2>SMTP-Einstellungen</h2>
		<?php
		// Settings card using shadcn
		$settings_card_content = '';
		
		// Card header
		$settings_card_content .= call_user_func('shadcn_card_header', [
			'content' => 
				call_user_func('shadcn_card_title', ['text' => 'SMTP-Einstellungen']) . 
				call_user_func('shadcn_card_description', ['text' => 'Konfigurieren Sie die E-Mail-Versandeinstellungen für die Anwendung'])
		]);
		
		// Card content
		$form_content = '<form method="post" class="shadcn-p-6 shadcn-pt-0">';
		$form_content .= wp_nonce_field('charigame_save_smtp_action', '_wpnonce', true, false);
		
		// Create a grid for the form fields
		$form_content .= '<div class="shadcn-grid shadcn-grid-cols-1 md:shadcn-grid-cols-2 shadcn-gap-4 shadcn-mb-6">';
		
		// SMTP Host
		$form_content .= '<div class="shadcn-mb-4">';
		$form_content .= '<label class="shadcn-block shadcn-text-sm shadcn-font-medium shadcn-mb-2">SMTP Host</label>';
		$form_content .= '<input name="smtp_host" type="text" value="' . esc_attr($smtp['smtp_host']) . '" class="shadcn-w-full shadcn-p-2 shadcn-border shadcn-rounded-md" />';
		$form_content .= '</div>';
		
		// SMTP Port
		$form_content .= '<div class="shadcn-mb-4">';
		$form_content .= '<label class="shadcn-block shadcn-text-sm shadcn-font-medium shadcn-mb-2">SMTP Port</label>';
		$form_content .= '<input name="smtp_port" type="number" value="' . esc_attr($smtp['smtp_port']) . '" class="shadcn-w-full shadcn-p-2 shadcn-border shadcn-rounded-md" />';
		$form_content .= '</div>';
		
		// Benutzername
		$form_content .= '<div class="shadcn-mb-4">';
		$form_content .= '<label class="shadcn-block shadcn-text-sm shadcn-font-medium shadcn-mb-2">Benutzername</label>';
		$form_content .= '<input name="smtp_user" type="text" value="' . esc_attr($smtp['smtp_user']) . '" class="shadcn-w-full shadcn-p-2 shadcn-border shadcn-rounded-md" />';
		$form_content .= '</div>';
		
		// Passwort
		$form_content .= '<div class="shadcn-mb-4">';
		$form_content .= '<label class="shadcn-block shadcn-text-sm shadcn-font-medium shadcn-mb-2">Passwort</label>';
		if (defined('WPMS_SMTP_PASS')) {
			$form_content .= '<em class="shadcn-text-muted-foreground">Wird per Konstante gesetzt (WPMS_SMTP_PASS)</em>';
		} else {
			$form_content .= '<input name="smtp_pass" type="password" value="' . esc_attr($smtp['smtp_pass']) . '" class="shadcn-w-full shadcn-p-2 shadcn-border shadcn-rounded-md" />';
		}
		$form_content .= '</div>';
		
		// Verschlüsselung
		$form_content .= '<div class="shadcn-mb-4">';
		$form_content .= '<label class="shadcn-block shadcn-text-sm shadcn-font-medium shadcn-mb-2">Verschlüsselung</label>';
		$form_content .= '<select name="smtp_secure" class="shadcn-w-full shadcn-p-2 shadcn-border shadcn-rounded-md">';
		$form_content .= '<option value="tls" ' . selected($smtp['smtp_secure'], 'tls', false) . '>TLS</option>';
		$form_content .= '<option value="ssl" ' . selected($smtp['smtp_secure'], 'ssl', false) . '>SSL</option>';
		$form_content .= '<option value="" ' . selected($smtp['smtp_secure'], '', false) . '>Keine</option>';
		$form_content .= '</select>';
		$form_content .= '</div>';
		
		// Absender E-Mail
		$form_content .= '<div class="shadcn-mb-4">';
		$form_content .= '<label class="shadcn-block shadcn-text-sm shadcn-font-medium shadcn-mb-2">Absender E-Mail</label>';
		$form_content .= '<input name="smtp_from" type="email" value="' . esc_attr($smtp['smtp_from']) . '" class="shadcn-w-full shadcn-p-2 shadcn-border shadcn-rounded-md" />';
		$form_content .= '</div>';
		
		// Absender Name
		$form_content .= '<div class="shadcn-mb-4">';
		$form_content .= '<label class="shadcn-block shadcn-text-sm shadcn-font-medium shadcn-mb-2">Absender Name</label>';
		$form_content .= '<input name="smtp_fromname" type="text" value="' . esc_attr($smtp['smtp_fromname']) . '" class="shadcn-w-full shadcn-p-2 shadcn-border shadcn-rounded-md" />';
		$form_content .= '</div>';
		
		$form_content .= '</div>';
		
		// Submit button
		$form_content .= '<div class="shadcn-flex shadcn-justify-end">';
		$form_content .= call_user_func('shadcn_button', [
			'text' => 'SMTP Einstellungen speichern',
			'type' => 'submit',
			'name' => 'charigame_save_smtp_settings',
			'variant' => 'default',
		]);
		$form_content .= '</div>';
		$form_content .= '</form>';
		
		$settings_card_content .= call_user_func('shadcn_card_content', ['content' => $form_content]);
		
		// Output the settings card
		echo call_user_func('shadcn_card', ['content' => $settings_card_content, 'class' => 'shadcn-mb-6']);
		?>

		<?php
		// Test email card
		$test_email_card_content = '';
		
		// Card header
		$test_email_card_content .= call_user_func('shadcn_card_header', [
			'content' => 
				call_user_func('shadcn_card_title', ['text' => 'Test-E-Mail versenden']) . 
				call_user_func('shadcn_card_description', ['text' => 'Senden Sie eine Test-E-Mail, um Ihre SMTP-Einstellungen zu überprüfen'])
		]);
		
		// Card content
		$form_content = '<form method="post" class="shadcn-p-6 shadcn-pt-0">';
		$form_content .= wp_nonce_field('charigame_send_test_mail_action', '_wpnonce', true, false);
		
		// Create a grid for the form fields
		$form_content .= '<div class="shadcn-grid shadcn-grid-cols-1 md:shadcn-grid-cols-2 shadcn-gap-4 shadcn-mb-6">';
		
		// Absender E-Mail
		$form_content .= '<div class="shadcn-mb-4">';
		$form_content .= '<label class="shadcn-block shadcn-text-sm shadcn-font-medium shadcn-mb-2">Absender E-Mail</label>';
		$form_content .= '<input name="sender_email" type="email" value="' . esc_attr($smtp['smtp_from']) . '" class="shadcn-w-full shadcn-p-2 shadcn-border shadcn-rounded-md" />';
		$form_content .= '</div>';
		
		// Empfänger E-Mail
		$form_content .= '<div class="shadcn-mb-4">';
		$form_content .= '<label class="shadcn-block shadcn-text-sm shadcn-font-medium shadcn-mb-2">Empfänger E-Mail</label>';
		$form_content .= '<input name="recipient_email" type="email" value="" class="shadcn-w-full shadcn-p-2 shadcn-border shadcn-rounded-md" />';
		$form_content .= '</div>';
		
		$form_content .= '</div>';
		
		// Submit button
		$form_content .= '<div class="shadcn-flex shadcn-justify-end">';
		$form_content .= call_user_func('shadcn_button', [
			'text' => 'Testmail senden',
			'type' => 'submit',
			'name' => 'charigame_send_test_mail',
			'variant' => 'secondary',
		]);
		$form_content .= '</div>';
		$form_content .= '</form>';
		
		$test_email_card_content .= call_user_func('shadcn_card_content', ['content' => $form_content]);
		
		// Output the test email card
		echo call_user_func('shadcn_card', ['content' => $test_email_card_content]);
		?>
	</div>
