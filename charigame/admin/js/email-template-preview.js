/**
 * Email Template Preview Script
 *
 * Handles the preview functionality for email templates
 */
jQuery(document).ready(function($) {
    $('#preview-email-template').on('click', function(e) {
        e.preventDefault();

        const emailSubject = $('input[name="carbon_fields_compact_input[_email_subject]"]').val() || '';
        const headerImage = $('input[name="carbon_fields_compact_input[_email_header_image]"]').val() || '';
        const headerClaim = $('input[name="carbon_fields_compact_input[_email_header_claim]"]').val() || '';
        const headline = $('input[name="carbon_fields_compact_input[_email_headline]"]').val() || '';
        let content = '';

        const contentTextarea = $('textarea[name="carbon_fields_compact_input[_email_content]"]');
        if (contentTextarea.length) {
            content = contentTextarea.val() || '';
        }

        if (typeof tinymce !== 'undefined') {
            const editorIds = [
                'carbon_fields_compact_input__email_content',
                '_email_content',
                'email_content'
            ];

            for (const id of editorIds) {
                const editor = tinymce.get(id);
                if (editor && !editor.isHidden()) {
                    content = editor.getContent();
                    break;
                }
            }
        }

        if (!content && typeof wp !== 'undefined' && wp.editor) {
            content = wp.editor.getContent('carbon_fields_compact_input__email_content') || content;
        }
        const ctaText = $('input[name="carbon_fields_compact_input[_email_cta_text]"]').val() || 'Zur Spendenaktion!';
        const ctaColor = $('input[name="carbon_fields_compact_input[_email_cta_color]"]').val() || '#2673AA';
        let info = '';

        const infoTextarea = $('textarea[name="carbon_fields_compact_input[_email_info]"]');
        if (infoTextarea.length) {
            info = infoTextarea.val() || '';
        }

        if (typeof tinymce !== 'undefined') {
            const infoEditorIds = [
                'carbon_fields_compact_input__email_info',
                '_email_info',
                'email_info'
            ];

            for (const id of infoEditorIds) {
                const editor = tinymce.get(id);
                if (editor && !editor.isHidden()) {
                    info = editor.getContent();
                    break;
                }
            }
        }

        if (!info && typeof wp !== 'undefined' && wp.editor) {
            info = wp.editor.getContent('carbon_fields_compact_input__email_info') || info;
        }
        let signature = '';

        const signatureTextarea = $('textarea[name="carbon_fields_compact_input[_email_signature]"]');
        if (signatureTextarea.length) {
            signature = signatureTextarea.val() || '';
        }

        if (typeof tinymce !== 'undefined') {
            const signatureEditorIds = [
                'carbon_fields_compact_input__email_signature',
                '_email_signature',
                'email_signature'
            ];

            for (const id of signatureEditorIds) {
                const editor = tinymce.get(id);
                if (editor && !editor.isHidden()) {
                    signature = editor.getContent();
                    break;
                }
            }
        }

        if (!signature && typeof wp !== 'undefined' && wp.editor) {
            signature = wp.editor.getContent('carbon_fields_compact_input__email_signature') || signature;
        }

        let imprintTitle = $('input[name="carbon_fields_compact_input[_imprint_title]"]').val() || 'Impressum:';
        let imprintBackgroundColor = $('input[name="carbon_fields_compact_input[_imprint_background_color]"]').val() || '#28333E';
        let imprintTextColor = $('input[name="carbon_fields_compact_input[_imprint_text_color]"]').val() || '#FFFFFF';

        let imprintContent = '';

        const imprintTextarea = $('textarea[name="carbon_fields_compact_input[_imprint_content]"]');
        if (imprintTextarea.length) {
            imprintContent = imprintTextarea.val() || '';
        }

        if (typeof tinymce !== 'undefined') {
            const imprintEditorIds = [
                'carbon_fields_compact_input__imprint_content',
                '_imprint_content',
                'imprint_content'
            ];

            for (const id of imprintEditorIds) {
                const editor = tinymce.get(id);
                if (editor && !editor.isHidden()) {
                    imprintContent = editor.getContent();
                    break;
                }
            }
        }

        if (!imprintContent && typeof wp !== 'undefined' && wp.editor) {
            imprintContent = wp.editor.getContent('carbon_fields_compact_input__imprint_content') || imprintContent;
        }

        const data = {
            action: 'charigame_preview_email_template',
            nonce: charigame_admin.nonce,
            email_data: {
                subject: emailSubject,
                header_image: headerImage,
                header_claim: headerClaim,
                headline: headline,
                content: content,
                cta_text: ctaText,
                cta_color: ctaColor,
                info: info,
                signature: signature,
                imprint_title: imprintTitle,
                imprint_background_color: imprintBackgroundColor,
                imprint_text_color: imprintTextColor,
                imprint_content: imprintContent
            }
        };

        Swal.fire({
            title: 'Generating Preview...',
            text: 'Please wait while we generate your email preview.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        $.post(ajaxurl, data, function(response) {
            if (response.success) {
                Swal.fire({
                    title: 'Email Template Preview',
                    html: response.data.html,
                    width: '800px',
                    confirmButtonText: 'Close Preview',
                    confirmButtonColor: '#2673AA',
                    showClass: {
                        popup: 'animate__animated animate__fadeIn'
                    }
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: response.data.message || 'Failed to generate preview.',
                    icon: 'error',
                    confirmButtonColor: '#2673AA'
                });
            }
        }).fail(function() {
            Swal.fire({
                title: 'Error',
                text: 'Failed to connect to the server.',
                icon: 'error',
                confirmButtonColor: '#2673AA'
            });
        });
    });
});
