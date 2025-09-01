<?php

namespace ChariGame\Admin;

require_once plugin_dir_path( __FILE__ ) . '/../includes/class-charigame-helper.php';
require_once plugin_dir_path( __FILE__ ) . '/class-charigame-data-table.php';
require_once plugin_dir_path( __FILE__ ) . '/components/shadcn-loader.php';

require_once plugin_dir_path( __FILE__ ) . '/components/ui/button.php';
require_once plugin_dir_path( __FILE__ ) . '/components/ui/card.php';
require_once plugin_dir_path( __FILE__ ) . '/components/ui/table.php';
require_once plugin_dir_path( __FILE__ ) . '/components/ui/progress.php';

use ChariGame\Includes\Helper;
use ChariGame\Includes\ChariGame_Donation_Manager;

function shadcn_function_caller( $function_name, $args = array() ) {
	if ( function_exists( $function_name ) ) {
		return call_user_func( $function_name, $args );
	}
	return '';
}

class ChariGame_Admin_Menu {

	public function register_menu(): void {
		$finished_campaigns = 1;
		$menu_title         = 'ChariGame';
		if ( $finished_campaigns > 0 ) {
			$menu_title .= ' <span class="update-plugins"><span class="update-count">' . $finished_campaigns . '</span></span>';
		}

		add_menu_page(
			'ChariGame',
			$menu_title,
			'manage_options',
			'charigame-types',
			'__return_null', // enables flyout-menu
			'dashicons-games',
			1000
		);

		add_submenu_page(
			'charigame-types',
			'Dashboard',
			'Dashboard',
			'manage_options',
			'charigame-dashboard',
			array( $this, 'render' )
		);

		add_submenu_page(
			'charigame-types',
			'ChariGame E-Mail Settings',
			'E-Mail Settings',
			'manage_options',
			'charigame-email-settings',
			array( $this, 'render_email_settings' )
		);
		add_filter( 'custom_menu_order', '__return_true' );
		add_filter( 'menu_order', array( $this, 'reorder_submenu' ) );
	}
	public function reorder_submenu( $menu_order ) {
		global $submenu;

		if ( isset( $submenu['charigame-types'] ) ) {
			$dashboard_item     = null;
			$email_item         = null;
			$campaign_item      = null;
			$landingpage_item   = null;
			$game_item          = null;
			$user_item          = null;
			$recipient_item     = null;
			$game_settings_item = null;
			$other_items        = array();

			foreach ( $submenu['charigame-types'] as $key => $item ) {
				switch ( $item[2] ) {
					case 'charigame-dashboard':
						$dashboard_item = $item;
						break;
					case 'edit.php?post_type=charigame-campaign':
						$campaign_item = $item;
						break;
					case 'edit.php?post_type=charigame-landing':
						$landingpage_item = $item;
						break;
					case 'edit.php?post_type=charigame-email-t':
						$email_templates = $item;
						break;
					case 'edit.php?post_type=charigame-game':
						$game_item = $item;
						break;
					case 'edit.php?post_type=charigame-user':
						$user_item = $item;
						break;
					case 'edit.php?post_type=charigame-recipients':
						$recipient_item = $item;
						break;
					case 'edit.php?post_type=charigame-game-set':
						$game_settings_item = $item;
						break;
					case 'charigame-email-settings':
						$email_item = $item;
						break;
					default:
						$other_items[] = $item;
						break;
				}
			}
			$submenu['charigame-types'] = array();
			if ( $dashboard_item ) {
				$submenu['charigame-types'][] = $dashboard_item;
			}
			if ( $campaign_item ) {
				$submenu['charigame-types'][] = $campaign_item;
			}
			if ( $landingpage_item ) {
				$submenu['charigame-types'][] = $landingpage_item;
			}
			if ( $email_templates ) {
				$submenu['charigame-types'][] = $email_templates;
			}
			if ( $game_item ) {
				$submenu['charigame-types'][] = $game_item;
			}
			if ( $user_item ) {
				$submenu['charigame-types'][] = $user_item;
			}
			if ( $recipient_item ) {
				$submenu['charigame-types'][] = $recipient_item;
			}
			if ( $game_settings_item ) {
				$submenu['charigame-types'][] = $game_settings_item;
			}
			if ( $email_item ) {
				$submenu['charigame-types'][] = $email_item;
			}
			foreach ( $other_items as $item ) {
				$submenu['charigame-types'][] = $item;
			}
		}

		return $menu_order;
	}


	public function render_email_settings(): void {
		if ( isset( $_POST['charigame_save_smtp_settings'] ) && check_admin_referer( 'charigame_save_smtp_action' ) ) {
			$options = array(
				'smtp_host'     => sanitize_text_field( $_POST['smtp_host'] ),
				'smtp_port'     => absint( $_POST['smtp_port'] ),
				'smtp_user'     => sanitize_text_field( $_POST['smtp_user'] ),
				'smtp_pass'     => sanitize_text_field( $_POST['smtp_pass'] ),
				'smtp_secure'   => sanitize_text_field( $_POST['smtp_secure'] ),
				'smtp_from'     => sanitize_email( $_POST['smtp_from'] ),
				'smtp_fromname' => sanitize_text_field( $_POST['smtp_fromname'] ),
			);
			update_option( 'charigame_smtp_settings', $options );
			echo '<div class="notice notice-success"><p>✅ SMTP-Einstellungen gespeichert.</p></div>';
		}

		if ( isset( $_POST['charigame_send_test_mail'] ) && check_admin_referer( 'charigame_send_test_mail_action' ) ) {
			$from = sanitize_email( $_POST['sender_email'] );
			$to   = sanitize_email( $_POST['recipient_email'] );
			$smtp = get_option( 'charigame_smtp_settings' );

			add_action(
				'phpmailer_init',
				function ( $phpmailer ) use ( $smtp, $from ) {
					$phpmailer->isSMTP();
					$phpmailer->Host       = $smtp['smtp_host'];
					$phpmailer->SMTPAuth   = true;
					$phpmailer->Port       = $smtp['smtp_port'];
					$phpmailer->Username   = $smtp['smtp_user'];
					$phpmailer->Password   = defined( 'WPMS_SMTP_PASS' ) ? WPMS_SMTP_PASS : $smtp['smtp_pass'];
					$phpmailer->SMTPSecure = $smtp['smtp_secure'];
					$phpmailer->setFrom( $from, 'ChariGame Plugin' );
				}
			);

			$subject = 'ChariGame Test-E-Mail';
			$message = 'Dies ist eine Testmail von deinem WordPress-System.';

			if ( wp_mail( $to, $subject, $message ) ) {
				echo '<div class="notice notice-success"><p>✅ Test-E-Mail erfolgreich gesendet.</p></div>';
			} else {
				echo '<div class="notice notice-error"><p>❌ Fehler beim Senden.</p></div>';
			}

			remove_all_actions( 'phpmailer_init' );
		}

		$smtp = get_option(
			'charigame_smtp_settings',
			array(
				'smtp_host'     => '',
				'smtp_port'     => 587,
				'smtp_user'     => '',
				'smtp_pass'     => '',
				'smtp_secure'   => 'tls',
				'smtp_from'     => '',
				'smtp_fromname' => '',
			)
		);

		include plugin_dir_path( __FILE__ ) . '/partials/email-settings-form.php';
	}

	public function render(): void {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php

			$all_campaigns = Helper::get_all_campaigns();
			$tabs          = array();
			$campaign_map  = array();

			foreach ( $all_campaigns as $index => $single_campaign ) {
				$tab_key                  = 'tab' . ( $index + 1 );
				$tab_label                = ucfirst( $single_campaign->post_title );
				$tabs[ $tab_key ]         = $tab_label;
				$campaign_map[ $tab_key ] = $single_campaign->ID;

				add_settings_section(
					"charigame-dashboard_{$tab_key}_settings",
					'',
					function () use ( $single_campaign ) {
						$this->render_tab_settings_callback( $single_campaign );
					},
					"charigame-dashboard_{$tab_key}"
				);
			}

			$current_tab = isset( $_GET['tab'] ) && isset( $tabs[ $_GET['tab'] ] ) ? $_GET['tab'] : array_key_first( $tabs );
			$this->handle_post_actions( $tabs, $current_tab, $campaign_map );

			?>

			<form method="post"
				action="<?php echo esc_url( admin_url( 'admin.php?page=charigame-dashboard&tab=' . $current_tab ) ); ?>">
				<nav class="nav-tab-wrapper">
					<?php
					foreach ( $tabs as $tab => $name ) :
						$current = $tab === $current_tab ? ' nav-tab-active' : '';
						$url     = add_query_arg(
							array(
								'page' => 'charigame-dashboard',
								'tab'  => $tab,
							),
							''
						);
						?>
						<a class="nav-tab<?php echo esc_attr( $current ); ?>"
						href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $name ); ?></a>
					<?php endforeach; ?>
				</nav>

				<div class="shadcn-mt-6">
					<?php
					settings_fields( "charigame-dashboard_{$current_tab}_settings" );
					do_settings_sections( "charigame-dashboard_{$current_tab}" );
					?>
				</div>

				<div class="shadcn-flex shadcn-flex-wrap shadcn-gap-4 shadcn-mt-6">
					<?php
					echo call_user_func(
						'shadcn_button',
						array(
							'text'    => 'Benutzerdaten aktualisieren',
							'type'    => 'submit',
							'name'    => 'submit',
							'value'   => 'Benutzerdaten aktualisieren',
							'variant' => 'default',
						)
					);

					echo call_user_func(
						'shadcn_button',
						array(
							'text'    => 'Benutzerdaten löschen',
							'type'    => 'submit',
							'name'    => 'delete',
							'value'   => 'Benutzerdaten löschen',
							'variant' => 'destructive',
						)
					);

					echo call_user_func(
						'shadcn_button',
						array(
							'text'    => 'Kampagnendaten zurücksetzen',
							'type'    => 'submit',
							'name'    => 'reset_campaign',
							'value'   => 'Kampagnendaten zurücksetzen',
							'variant' => 'destructive',
							'class'   => 'shadcn-ml-auto',
						)
					);
					?>
				</div>
			</form>

			<form method="post"
				id="charigame-user-delete-form"
				class="shadcn-mt-6"
				action="<?php echo esc_url( admin_url( 'admin.php?page=charigame-dashboard&tab=' . $current_tab ) ); ?>">
				<input type="hidden"
					name="delete_all_user"
					value="Alle Nutzer aus der Tabelle Charigame User löschen"/>
				<?php
				echo call_user_func(
					'shadcn_button',
					array(
						'text'    => 'Alle Nutzer aus der Tabelle Charigame User löschen',
						'type'    => 'submit',
						'name'    => 'delete_all_user',
						'variant' => 'destructive',
					)
				);
				?>
			</form>
		</div>
		<?php
		$this->handle_batch_user_delete();
	}

	private function render_tab_settings_callback( $campaign ): void {
		require_once plugin_dir_path( __FILE__ ) . 'class-charigame-sortable-table.php';

		$stats      = ChariGame_Donation_Manager::get_campaign_statistics( $campaign->ID );
		$recipients = ChariGame_Donation_Manager::get_all_recipients_data_by_campaign_id( $campaign->ID );

		$total_donation = $stats['total_donation'] ?? 0;
		$total_games    = $stats['total_games'] ?? 0;

		$donation_card_content = '';

		$donation_card_content .= call_user_func(
			'shadcn_card_header',
			array(
				'content' =>
					call_user_func( 'shadcn_card_title', array( 'text' => esc_html( ucfirst( $campaign->post_title ) ) . ' Spendenverteilung' ) ) .
					call_user_func( 'shadcn_card_description', array( 'text' => 'Übersicht der gesammelten Spenden und deren Verteilung' ) ),
			)
		);

		$card_content = '';

		if ( is_array( $recipients ) && ! empty( $recipients ) ) {

			$card_content .= '<div class="shadcn-grid shadcn-grid-cols-1 md:shadcn-grid-cols-' . count( $recipients ) . ' shadcn-gap-4">';

			foreach ( $recipients as $index => $recipient ) {
				$recipient_amount     = 0;
				$recipient_percentage = 0;


				if ( isset( $stats['recipients'] ) ) {
					foreach ( $stats['recipients'] as $r ) {
						if ( isset( $r['title'] ) && $r['title'] === $recipient['title'] ) {
							$recipient_amount     = $r['donation_amount'];
							$recipient_percentage = $r['percentage'];
							break;
						}
					}
				}

				$card_content .= '<div class="shadcn-p-2 shadcn-rounded-lg shadcn-border">';
				$card_content .= '<div class="shadcn-flex shadcn-flex-col shadcn-gap-2">';


				if ( ! empty( $recipient['logo_url'] ) ) {
					$card_content .= '<div class="shadcn-mb-2 shadcn-flex shadcn-justify-center">';
					$card_content .= '<img src="' . esc_url( $recipient['logo_url'] ) . '" alt="' . esc_attr( $recipient['title'] ) . '" class="shadcn-h-16 shadcn-w-auto" />';
					$card_content .= '</div>';
				}


				$card_content .= '<h4 class="shadcn-text-base shadcn-font-medium">' . esc_html( $recipient['title'] ) . '</h4>';


				$card_content .= '<div class="shadcn-flex shadcn-justify-between shadcn-items-center">';
				$card_content .= '<span>' . number_format( round( $recipient_amount, 0 ), 2, ',', '.' ) . ' €</span>';
				$card_content .= '<span>' . round( $recipient_percentage ) . '%</span>';
				$card_content .= '</div>';


				$card_content .= call_user_func(
					'shadcn_progress',
					array(
						'value' => $recipient_percentage,
						'max'   => 100,
						'color' => 'shadcn-bg-primary',
					)
				);

				$card_content .= '</div>';
				$card_content .= '</div>';
			}

			$card_content .= '</div>';
		} else {
			$card_content .= '<p class="shadcn-text-muted-foreground">Keine Empfänger definiert.</p>';
		}


		$card_content .= '<div class="shadcn-mt-4 shadcn-flex shadcn-justify-between shadcn-items-center shadcn-p-4 shadcn-border shadcn-rounded-lg">';
		$card_content .= '<div>';
		$card_content .= '<span class="shadcn-text-sm shadcn-text-muted-foreground">Spendentopf gesamt</span>';
		$card_content .= '<h3 class="shadcn-text-2xl shadcn-font-bold">' . number_format( round( $total_donation, 0 ), 2, ',', '.' ) . ' €</h3>';
		$card_content .= '</div>';
		$card_content .= '<div class="shadcn-text-right">';
		$card_content .= '<span class="shadcn-text-sm shadcn-text-muted-foreground">Anzahl Spiele</span>';
		$card_content .= '<h3 class="shadcn-text-2xl shadcn-font-bold">' . $total_games . '</h3>';
		$card_content .= '</div>';
		$card_content .= '</div>';


		$view_campaign_url = get_permalink( $campaign->ID );
		$card_content     .= '<div class="shadcn-mt-4 shadcn-flex shadcn-justify-center">';
		$card_content     .= call_user_func(
			'shadcn_button',
			array(
				'text'       => 'Kampagne anzeigen',
				'attributes' => array(
					'onclick' => 'window.open("' . esc_url( $view_campaign_url ) . '", "_blank")',
				),
				'variant'    => 'default',
			)
		);
		$card_content     .= '</div>';

		$donation_card_content .= call_user_func( 'shadcn_card_content', array( 'content' => $card_content ) );

		echo call_user_func(
			'shadcn_card',
			array(
				'content' => $donation_card_content,
				'class'   => 'shadcn-mb-6',
			)
		);

		$table_card_content = '';

		$table_card_content .= call_user_func(
			'shadcn_card_header',
			array(
				'content' =>
					call_user_func( 'shadcn_card_title', array( 'text' => 'Charigame Nutzerdaten' ) ) .
					call_user_func( 'shadcn_card_description', array( 'text' => 'Alle Spieler und deren Aktivitäten für diese Kampagne' ) ),
			)
		);

		$table                = new ChariGame_Sortable_Table();
		$table->campaign_id   = $campaign->ID;
		$table->campaign_name = $campaign->post_name;

		$table_card_content .= call_user_func( 'shadcn_card_content', array( 'content' => $table->render() ) );

		echo call_user_func( 'shadcn_card', array( 'content' => $table_card_content ) );
	}

	private function handle_post_actions( array $tabs, string $current_tab, array $campaign_map ): void {
		if ( isset( $_POST['submit'] ) && $_POST['submit'] === 'Benutzerdaten aktualisieren' ) {
			$campaign_id = $campaign_map[ $current_tab ] ?? null;

			if ( $campaign_id ) {
				Helper::update_charigame_data_table( $campaign_id );
				echo '<div class="notice notice-success"><p>Einstellungen für die Kampagne ' . esc_html( $tabs[ $current_tab ] ) . ' wurden erfolgreich aktualisiert!</p></div>';
			} else {
				echo '<div class="notice notice-error"><p>Kampagne nicht gefunden!</p></div>';
			}
		}

		if ( isset( $_POST['delete'] ) && $_POST['delete'] === 'Benutzerdaten löschen' ) {
			Helper::delete_data_from_charigame_game_data_table();
			echo '<div class="notice notice-warning"><p>Benutzerdaten wurden gelöscht!</p></div>';
		}

		if ( isset( $_POST['reset_campaign'] ) && $_POST['reset_campaign'] === 'Kampagnendaten zurücksetzen' ) {
			$campaign_id = $campaign_map[ $current_tab ] ?? null;

			if ( $campaign_id ) {
				$donation_manager = new ChariGame_Donation_Manager();
				$result           = $donation_manager->reset_campaign_statistics( $campaign_id );

				if ( $result ) {
					echo '<div class="notice notice-warning"><p>Kampagnendaten für ' . esc_html( $tabs[ $current_tab ] ) . ' wurden zurückgesetzt!</p></div>';
				} else {
					echo '<div class="notice notice-error"><p>Fehler beim Zurücksetzen der Kampagnendaten!</p></div>';
				}
			} else {
				echo '<div class="notice notice-error"><p>Kampagne nicht gefunden!</p></div>';
			}
		}
	}

	private function handle_batch_user_delete(): void {
		if ( isset( $_POST['delete_all_user'] ) && $_POST['delete_all_user'] === 'Alle Nutzer aus der Tabelle Charigame User löschen' ) {
			$count = Helper::delete_charigame_users_batch();

			if ( $count > 0 ) {
				echo '<div class="notice notice-warning"><p>' . esc_html( $count ) . ' Nutzer gelöscht… Weiter mit dem nächsten Batch.</p></div>';
				echo '<script>setTimeout(function(){ document.getElementById("charigame-user-delete-form").submit(); }, 1000);</script>';
			} else {
				echo '<div class="notice notice-success"><p>Alle Charigame Nutzer wurden erfolgreich gelöscht!</p></div>';
			}
		}
	}
}
