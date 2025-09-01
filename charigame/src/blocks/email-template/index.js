import { registerBlockType } from '@wordpress/blocks'
import {
    RichText,
    useBlockProps,
    InspectorControls,
    MediaUpload,
    MediaUploadCheck,
} from '@wordpress/block-editor'
import {
    PanelBody,
    PanelRow,
    TextControl,
    Button,
    ColorPicker,
    ColorPalette,
    __experimentalInputControl as InputControl,
    BaseControl,
    Flex,
    FlexItem,
} from '@wordpress/components'

import metadata from './block.json'

registerBlockType(metadata, {
    edit: ({ attributes, setAttributes }) => {
        const {
            header_image,
            header_claim,
            header_claim_color,
            headline,
            headline_color,
            content,
            cta_text,
            cta_color,
            cta_url,
            info,
            signature,
            social_media,
            imprint_title,
            imprint_background_color,
            imprint_text_color,
            imprint_content,
            game_code_text,
            validity_text,
            closing_text,
            add_code_parameter,
        } = attributes

        // Function to handle social media changes
        const handleSocialMediaChange = (index, field, value) => {
            const newSocialMedia = [...social_media]
            if (!newSocialMedia[index]) {
                newSocialMedia[index] = {}
            }
            newSocialMedia[index][field] = value
            setAttributes({ social_media: newSocialMedia })
        }

        // Function to add a new social media item
        const addSocialMediaItem = () => {
            const newSocialMedia = [...social_media, { social_media_name: '', social_media_link: '', social_media_icon: '' }]
            setAttributes({ social_media: newSocialMedia })
        }

        // Function to remove a social media item
        const removeSocialMediaItem = (index) => {
            const newSocialMedia = [...social_media]
            newSocialMedia.splice(index, 1)
            setAttributes({ social_media: newSocialMedia })
        }


        const data = {
            game_code: '{game_code}',
            valid_from: new Date().toLocaleDateString('de-DE'),
            valid_until: new Date(new Date().setDate(new Date().getDate() + 28)).toLocaleDateString('de-DE'),
            campaign_url: cta_url || '#',
        }

        const emailStyle = {
            maxWidth: '600px',
            margin: '0 auto',
            border: '1px solid #e0e0e0',
            boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
        }

        const headerStyle = {
            backgroundColor: '#ffffff',
        }

        const contentStyle = {
            padding: '32px',
        }

        const headlineStyle = {
            textAlign: 'center',
        }

        const ctaButtonStyle = {
            backgroundColor: cta_color,
            color: 'white',
            textDecoration: 'none',
            padding: '10px 20px',
            borderRadius: '5px',
            display: 'inline-block',
        }

        const footerStyle = {
            backgroundColor: imprint_background_color,
            color: imprint_text_color,
            padding: '20px',
            fontFamily: 'Arial, sans-serif',
        }

        return (
            <>
                <InspectorControls>
                    <PanelBody title="Header-Einstellungen" initialOpen={true}>
                        <PanelRow>
                            <BaseControl label="Header-Claim Farbe" id="header-claim-color-picker">
                                <ColorPicker
                                    color={header_claim_color}
                                    onChange={(value) => setAttributes({ header_claim_color: value })}
                                    enableAlpha={false}
                                />
                            </BaseControl>
                        </PanelRow>
                        <PanelRow>
                            <BaseControl label="Headline Farbe" id="headline-color-picker">
                                <ColorPicker
                                    color={headline_color}
                                    onChange={(value) => setAttributes({ headline_color: value })}
                                    enableAlpha={false}
                                />
                            </BaseControl>
                        </PanelRow>
                        <PanelRow>
                            <MediaUploadCheck>
                                <MediaUpload
                                    onSelect={(media) => {
                                        setAttributes({ header_image: media.url })
                                    }}
                                    allowedTypes={['image']}
                                    value={header_image}
                                    render={({ open }) => (
                                        <div>
                                            {header_image && (
                                                <img
                                                    src={header_image}
                                                    alt="Header Image Preview"
                                                    style={{ maxWidth: '100%', marginBottom: '10px' }}
                                                />
                                            )}
                                            <Button onClick={open} variant="primary">
                                                {header_image ? 'Header-Bild ändern' : 'Header-Bild auswählen'}
                                            </Button>
                                            {header_image && (
                                                <Button
                                                    onClick={() => setAttributes({ header_image: '' })}
                                                    variant="secondary"
                                                    style={{ marginLeft: '10px' }}
                                                >
                                                    Entfernen
                                                </Button>
                                            )}
                                        </div>
                                    )}
                                />
                            </MediaUploadCheck>
                        </PanelRow>
                    </PanelBody>

                    <PanelBody title="CTA-Button" initialOpen={false}>
                        <PanelRow>
                            <TextControl
                                label="Button-Text"
                                value={cta_text}
                                onChange={(value) => setAttributes({ cta_text: value })}
                            />
                        </PanelRow>
                        <PanelRow>
                            <TextControl
                                label="Button-URL"
                                value={cta_url}
                                onChange={(value) => setAttributes({ cta_url: value })}
                            />
                        </PanelRow>
                        <PanelRow>
                            <BaseControl label="Button-Farbe" id="cta-color-picker">
                                <ColorPicker
                                    color={cta_color}
                                    onChange={(value) => setAttributes({ cta_color: value })}
                                    enableAlpha={false}
                                />
                            </BaseControl>
                        </PanelRow>
                    </PanelBody>

                    <PanelBody title="Social Media" initialOpen={false}>
                        {social_media.map((item, index) => (
                            <div key={index} style={{ marginBottom: '20px', padding: '10px', border: '1px solid #e0e0e0' }}>
                                <TextControl
                                    label="Name"
                                    value={item.social_media_name || ''}
                                    onChange={(value) => handleSocialMediaChange(index, 'social_media_name', value)}
                                />
                                <TextControl
                                    label="Link"
                                    value={item.social_media_link || ''}
                                    onChange={(value) => handleSocialMediaChange(index, 'social_media_link', value)}
                                />
                                <MediaUploadCheck>
                                    <MediaUpload
                                        onSelect={(media) => {
                                            handleSocialMediaChange(index, 'social_media_icon', media.url)
                                        }}
                                        allowedTypes={['image']}
                                        value={item.social_media_icon}
                                        render={({ open }) => (
                                            <div>
                                                {item.social_media_icon && (
                                                    <img
                                                        src={item.social_media_icon}
                                                        alt="Icon Preview"
                                                        style={{ maxWidth: '40px', marginBottom: '10px' }}
                                                    />
                                                )}
                                                <Button onClick={open} variant="primary">
                                                    {item.social_media_icon ? 'Icon ändern' : 'Icon auswählen'}
                                                </Button>
                                                {item.social_media_icon && (
                                                    <Button
                                                        onClick={() => handleSocialMediaChange(index, 'social_media_icon', '')}
                                                        variant="secondary"
                                                        style={{ marginLeft: '10px' }}
                                                    >
                                                        Entfernen
                                                    </Button>
                                                )}
                                            </div>
                                        )}
                                    />
                                </MediaUploadCheck>
                                <Button
                                    onClick={() => removeSocialMediaItem(index)}
                                    variant="secondary"
                                    style={{ marginTop: '10px' }}
                                    isDestructive
                                >
                                    Entfernen
                                </Button>
                            </div>
                        ))}
                        <Button
                            onClick={addSocialMediaItem}
                            variant="primary"
                        >
                            Social Media Icon hinzufügen
                        </Button>
                    </PanelBody>

                    <PanelBody title="Variable Texte" initialOpen={false}>
                        <PanelRow>
                            <TextControl
                                label="Game-Code Text"
                                value={game_code_text}
                                onChange={(value) => setAttributes({ game_code_text: value })}
                                help="Verfügbare Variablen: {game_code}, {valid_from}, {valid_until}, {name}, {first_name}, {last_name}"
                            />
                        </PanelRow>
                        <PanelRow>
                            <TextControl
                                label="Gültigkeits-Text"
                                value={validity_text}
                                onChange={(value) => setAttributes({ validity_text: value })}
                                help="Verfügbare Variablen: {valid_from}, {valid_until}"
                            />
                        </PanelRow>
                        <PanelRow>
                            <TextControl
                                label="Abschluss-Text"
                                value={closing_text}
                                onChange={(value) => setAttributes({ closing_text: value })}
                                help="Verfügbare Variablen: {name}, {first_name}, {last_name}, {game_code}"
                            />
                        </PanelRow>
                        <PanelRow>
                            <BaseControl id="add_code_parameter">
                                <div>
                                    <input
                                        type="checkbox"
                                        id="add_code_parameter"
                                        checked={add_code_parameter}
                                        onChange={() => setAttributes({ add_code_parameter: !add_code_parameter })}
                                    />
                                    <label htmlFor="add_code_parameter" style={{ marginLeft: '10px' }}>
                                        Game-Code als URL-Parameter hinzufügen (code=GAMECODE)
                                    </label>
                                </div>
                            </BaseControl>
                        </PanelRow>
                    </PanelBody>

                    <PanelBody title="Impressum-Einstellungen" initialOpen={false}>
                        <PanelRow>
                            <TextControl
                                label="Impressum-Titel"
                                value={imprint_title}
                                onChange={(value) => setAttributes({ imprint_title: value })}
                            />
                        </PanelRow>
                        <PanelRow>
                            <BaseControl label="Hintergrundfarbe" id="imprint-bg-color-picker">
                                <ColorPicker
                                    color={imprint_background_color}
                                    onChange={(value) => setAttributes({ imprint_background_color: value })}
                                    enableAlpha={false}
                                />
                            </BaseControl>
                        </PanelRow>
                        <PanelRow>
                            <BaseControl label="Textfarbe" id="imprint-text-color-picker">
                                <ColorPicker
                                    color={imprint_text_color}
                                    onChange={(value) => setAttributes({ imprint_text_color: value })}
                                    enableAlpha={false}
                                />
                            </BaseControl>
                        </PanelRow>
                    </PanelBody>
                </InspectorControls>

                <div {...useBlockProps()}>
                    {/* E-Mail-Template-Vorschau */}
                    <div className="email-template-preview" style={emailStyle}>
                        {/* Header */}
                        {header_image && (
                            <div style={headerStyle}>
                                <img src={header_image} alt="Header" style={{ width: '100%', display: 'block' }} />
                            </div>
                        )}

                        {/* Header Claim */}
                        <div style={{ textAlign: 'center', padding: '10px' }}>
                            <RichText
                                tagName="div"
                                style={{ color: header_claim_color, textDecoration: 'none', display: 'inline-block', width: '100%', textAlign: 'center' }}
                                value={header_claim}
                                onChange={(value) => setAttributes({ header_claim: value })}
                                placeholder="Header-Claim eingeben..."
                                allowedFormats={['core/bold', 'core/italic']}
                            />
                        </div>

                        {/* Headline */}
                        <RichText
                            tagName="h1"
                            style={{...headlineStyle, color: headline_color}}
                            value={headline}
                            onChange={(value) => setAttributes({ headline: value })}
                            placeholder="Headline eingeben..."
                            allowedFormats={['core/bold', 'core/italic']}
                        />

                        {/* Content */}
                        <div style={contentStyle}>
                            <div style={{ textAlign: 'center' }}>
                                <RichText
                                    tagName="div"
                                    value={content}
                                    onChange={(value) => setAttributes({ content: value })}
                                    placeholder="Inhalt eingeben..."
                                    allowedFormats={['core/bold', 'core/italic', 'core/link']}
                                />
                            </div>

                            {/* CTA Button */}
                            <div style={{ textAlign: 'center', padding: '20px' }}>
                                <div style={ctaButtonStyle}>
                                    {cta_text || 'Zur Aktion'}
                                </div>
                            </div>

                            {/* Info (optional) */}
                            <div style={{ textAlign: 'center' }}>
                                <RichText
                                    tagName="p"
                                    value={info}
                                    onChange={(value) => setAttributes({ info: value })}
                                    placeholder="Zusätzliche Informationen eingeben..."
                                    allowedFormats={['core/bold', 'core/italic', 'core/link']}
                                />
                            </div>

                            {/* Game Code (Preview) */}
                            <p style={{ textAlign: 'center', padding: '10px 0'}}><strong>{data.game_code}</strong></p>
                            <p style={{ textAlign: 'center' }}>{attributes.game_code_text || 'Den Code können Sie unter der folgenden Adresse eingeben:'}</p>
                            <div style={{ display: 'inline-block', color: cta_color, textAlign: 'center', width: '100%', textDecoration: 'none' }}>
                                {data.campaign_url}
                            </div>

                            {/* Gültigkeitsdaten (Preview) */}
                            <p style={{ textAlign: 'center', paddingTop: '10px' }}>
                                {(attributes.validity_text || 'Die Teilnahme ist exklusiv für Sie vom {valid_from} bis zum {valid_until} verfügbar.')
                                    .replace('{valid_from}', data.valid_from)
                                    .replace('{valid_until}', data.valid_until)}
                            </p>

                            <p style={{ textAlign: 'center', paddingTop: '20px'}}>{attributes.closing_text || 'Wir freuen uns auf Ihre Teilnahme!'}</p>
                        </div>

                        {/* Signature (optional) */}
                        <div style={{ textAlign: 'center' }}>
                            <RichText
                                tagName="p"
                                value={signature}
                                onChange={(value) => setAttributes({ signature: value })}
                                placeholder="Signatur eingeben..."
                                allowedFormats={['core/bold', 'core/italic']}
                            />
                        </div>

                        {/* Social Media Icons (Preview) */}
                        {social_media.length > 0 && (
                            <div style={{ textAlign: 'center', padding: '20px' }}>
                                {social_media.map((item, index) => (
                                    item.social_media_icon && (
                                        <img
                                            key={index}
                                            src={item.social_media_icon}
                                            alt={item.social_media_name || 'Social Media'}
                                            style={{ width: '40px', height: '40px', marginRight: '10px' }}
                                        />
                                    )
                                ))}
                            </div>
                        )}

                        {/* Footer with Imprint */}
                        <div style={footerStyle}>
                            <p style={{ fontSize: '16px', textAlign: 'center', fontWeight: 'bold', fontStyle: 'italic' }}>
                                {imprint_title}
                            </p>
                            <div style={{ textAlign: 'center' }}>
                                <RichText
                                    tagName="div"
                                    style={{ fontSize: '14px' }}
                                    value={imprint_content}
                                    onChange={(value) => setAttributes({ imprint_content: value })}
                                    placeholder="Impressum-Inhalt eingeben..."
                                    allowedFormats={['core/bold', 'core/italic', 'core/link']}
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </>
        )
    },
    save: () => {
        return null // Da wir render.php verwenden
    }
})
