<?php
require_once plugin_dir_path( __FILE__ ) . '../../../includes/class-charigame-helper.php';
use ChariGame\Includes\Helper;

/** @var array $attributes */
/** @var string $content */

$header_image = $attributes['header_image'] ?? '';
$header_claim = $attributes['header_claim'] ?? 'Willkommen zu unserer E-Mail-Kampagne';
$header_claim_color = $attributes['header_claim_color'] ?? '#2673AA';
$headline = $attributes['headline'] ?? 'E-Mail-Headline';
$headline_color = $attributes['headline_color'] ?? '#000000';
$content_text = $attributes['content'] ?? 'Hier steht der E-Mail-Inhalt';
$cta_text = $attributes['cta_text'] ?? 'Zur Aktion';
$cta_color = $attributes['cta_color'] ?? '#2673AA';
$cta_url = $attributes['cta_url'] ?? '#';
$info = $attributes['info'] ?? 'Zusätzliche Informationen';
$signature = $attributes['signature'] ?? 'Mit freundlichen Grüßen';
$social_media = $attributes['social_media'] ?? [];
$imprint_title = $attributes['imprint_title'] ?? 'Impressum:';
$imprint_background_color = $attributes['imprint_background_color'] ?? '#28333E';
$imprint_text_color = $attributes['imprint_text_color'] ?? '#FFFFFF';
$imprint_content = $attributes['imprint_content'] ?? '';

$game_code_text = $attributes['game_code_text'] ?? 'Den Code können Sie unter der folgenden Adresse eingeben:';
$validity_text = $attributes['validity_text'] ?? 'Die Teilnahme ist exklusiv für Sie vom {valid_from} bis zum {valid_until} verfügbar.';
$closing_text = $attributes['closing_text'] ?? 'Wir freuen uns auf Ihre Teilnahme!';
$add_code_parameter = $attributes['add_code_parameter'] ?? true;

$data = [
    'game_code' => 'TEST123456',
    'valid_from' => date('d.m.Y'),
    'valid_until' => date('d.m.Y', strtotime('+4 weeks')),
    'campaign_url' => $cta_url,
];

$company_name = get_bloginfo('name');
$company_street = 'Musterstraße 123';
$company_city = '12345 Musterstadt';

$email_style = "max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; box-shadow: 0 2px 8px rgba(0,0,0,0.1);";
$header_style = "background-color: #ffffff;";
$content_style = "padding: 32px;";
$headline_style = "text-align: center;";
$cta_button_style = "background-color: {$cta_color}; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; display: inline-block;";
$footer_style = "background-color: {$imprint_background_color}; color: {$imprint_text_color}; padding: 20px; font-family: Arial, sans-serif;";
?>

<div class="email-template-preview" style="<?php echo esc_attr($email_style); ?>">
    <!-- Header -->
    <?php if (!empty($header_image)) : ?>
        <div style="<?php echo esc_attr($header_style); ?>">
            <img src="<?php echo esc_url($header_image); ?>" alt="Header" style="width: 100%; display: block;">
        </div>
    <?php endif; ?>

    <!-- Header Claim -->
    <?php if (!empty($header_claim)) : ?>
        <div style="text-align: center; padding: 10px;">
            <a href="<?php echo esc_url($cta_url); ?>" style="color: <?php echo esc_attr($header_claim_color); ?>; text-decoration: none; display: inline-block; width: 100%; text-align: center;">
                <?php
                $replaced_header_claim = str_replace(
                    ['{game_code}', '{valid_from}', '{valid_until}', '{name}', '{first_name}', '{last_name}'],
                    [$data['game_code'], $data['valid_from'], $data['valid_until'], 'Max Mustermann', 'Max', 'Mustermann'],
                    $header_claim
                );
                echo wp_kses_post($replaced_header_claim);
                ?>
            </a>
        </div>
    <?php endif; ?>

    <!-- Headline -->
    <h1 style="<?php echo esc_attr($headline_style); ?>; color: <?php echo esc_attr($headline_color); ?>">
        <?php
        // Variablen in der Headline ersetzen
        $replaced_headline = str_replace(
            ['{game_code}', '{valid_from}', '{valid_until}', '{name}', '{first_name}', '{last_name}'],
            [$data['game_code'], $data['valid_from'], $data['valid_until'], 'Max Mustermann', 'Max', 'Mustermann'],
            $headline
        );
        echo wp_kses_post($replaced_headline);
        ?>
    </h1>

    <!-- Content -->
    <div style="<?php echo esc_attr($content_style); ?>">
        <div style="text-align: center;">
            <?php
            $replaced_content = str_replace(
                ['{game_code}', '{valid_from}', '{valid_until}', '{name}', '{first_name}', '{last_name}'],
                [$data['game_code'], $data['valid_from'], $data['valid_until'], 'Max Mustermann', 'Max', 'Mustermann'],
                $content_text
            );
            echo wpautop(wp_kses_post($replaced_content));
            ?>
        </div>

        <!-- CTA Button -->
        <div style="text-align: center; padding: 20px;">
            <a href="<?php echo esc_url($cta_url); ?>" target="_blank" style="<?php echo esc_attr($cta_button_style); ?>">
                <?php echo esc_html($cta_text); ?>
            </a>
        </div>

        <!-- Info (optional) -->
        <?php if (!empty($info)) : ?>
            <p style="text-align: center;"><?php echo wp_kses_post($info); ?></p>
        <?php endif; ?>

        <!-- Game Code (wenn vorhanden) -->
        <?php if (!empty($data['game_code'])) : ?>
            <p style="text-align: center; padding:10px 0"><strong><?php echo esc_html($data['game_code']); ?></strong></p>
            <p style="text-align: center;"><?php echo esc_html(str_replace(
                ['{game_code}', '{valid_from}', '{valid_until}', '{name}', '{first_name}', '{last_name}'],
                [$data['game_code'], $data['valid_from'], $data['valid_until'], 'Max Mustermann', 'Max', 'Mustermann'],
                $game_code_text
            )); ?></p>
            <?php

            $display_url = $cta_url;
            if ($add_code_parameter && !empty($data['game_code'])) {
                $url_separator = (strpos($cta_url, '?') !== false) ? '&' : '?';
                $display_url = $cta_url . $url_separator . 'code=' . urlencode($data['game_code']);
            }
            ?>
            <a style="display: inline-block; color: <?php echo esc_attr($cta_color); ?>; text-align: center; width: 100%; text-decoration: none;"
               href="<?php echo esc_url($display_url); ?>"><?php echo esc_html($display_url); ?></a>
        <?php endif; ?>

        <!-- Gültigkeitsdaten (wenn vorhanden) -->
        <?php if (!empty($data['valid_from']) && !empty($data['valid_until'])) : ?>
            <p style="text-align: center;"><?php echo esc_html(str_replace(
                ['{valid_from}', '{valid_until}', '{name}', '{first_name}', '{last_name}', '{game_code}'],
                [$data['valid_from'], $data['valid_until'], 'Max Mustermann', 'Max', 'Mustermann', $data['game_code']],
                $validity_text
            )); ?></p>
        <?php endif; ?>

        <p style="text-align: center;"><?php echo esc_html(str_replace(
            ['{game_code}', '{valid_from}', '{valid_until}', '{name}', '{first_name}', '{last_name}'],
            [$data['game_code'], $data['valid_from'], $data['valid_until'], 'Max Mustermann', 'Max', 'Mustermann'],
            $closing_text
        )); ?></p>
    </div>

    <!-- Signature (optional) -->
    <?php if (!empty($signature)) : ?>
        <p style="text-align: center;"><?php echo wp_kses_post($signature); ?></p>
    <?php endif; ?>

    <!-- Social Media Icons (optional) -->
    <?php if (!empty($social_media)) : ?>
        <div style="text-align: center; padding: 20px;">
            <?php foreach ($social_media as $option) :
                $name = $option['social_media_name'] ?? '';
                $link = $option['social_media_link'] ?? '';
                $icon = $option['social_media_icon'] ?? '';
                if (!empty($link) && !empty($icon)) : ?>
                    <a href="<?php echo esc_url($link); ?>" style="margin-right: 10px;">
                        <img src="<?php echo esc_url($icon); ?>" alt="<?php echo esc_attr($name); ?>" style="width: 40px; height: 40px;">
                    </a>
                <?php endif;
            endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Footer with Imprint -->
    <div style="<?php echo esc_attr($footer_style); ?>">
        <?php if (!empty($imprint_title)) : ?>
            <p style="font-size: 16px; text-align: center; font-weight: bold; font-style: italic;"><?php echo esc_html($imprint_title); ?></p>
        <?php endif; ?>

        <?php if (!empty($imprint_content)) : ?>
            <div style="font-size: 14px; text-align: center;">
                <?php echo wpautop(wp_kses_post($imprint_content)); ?>
            </div>
        <?php else : ?>
            <p style="font-size: 14px; text-align: center;">
                <?php echo esc_html($company_name); ?><br>
                <?php echo esc_html($company_street); ?><br>
                <?php echo esc_html($company_city); ?>
            </p>
        <?php endif; ?>
    </div>
</div>
