import { registerBlockType } from '@wordpress/blocks'
import {
	RichText,
	useBlockProps,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck
} from '@wordpress/block-editor'
import {
	PanelBody,
	PanelRow,
	TextControl,
	Button, ColorPalette, BaseControl
} from '@wordpress/components'

import BackgroundSelect from '../../components/BackgroundSelect'
import BorderStyleSelect from '../../components/BorderStyleSelect'

import classnames from "classnames"
import metadata from './block.json'
import { textColors } from "../../utils/colorPalette"

registerBlockType(metadata, {
	edit: ({ attributes, setAttributes }) => {
		const {
			background_type,
			textColor,
			logo,
			campaign_title,
			campaign_desc_text,
			game_type_title,
			borderStyle,
		} = attributes

		return (
			<>
				<InspectorControls>
					<PanelBody title="Background-Einstellungen" initialOpen={true}>
						<PanelRow>
							<BackgroundSelect
								value={background_type}
								onChange={(value) => setAttributes({ background_type: value })}
							/>
						</PanelRow>
					</PanelBody>
					<PanelBody title="Rand-Stil" initialOpen={true}>
						<PanelRow>
							<BorderStyleSelect
								value={borderStyle}
								onChange={(value) => setAttributes({ borderStyle: value })}
								label="Randstil unten auswählen"
								help="Wähle den Stil für den unteren Rand des weißen Inhaltsbereichs"
							/>
						</PanelRow>
					</PanelBody>
					<PanelBody title="Textfarbe" initialOpen={true}>
						<PanelRow>
							<BaseControl label={"Farbe auswählen"} id={"text-color"}>
								<ColorPalette
									value={textColor}
									colors={textColors}
									disableCustomColors={true}
									onChange={(value, index) => {
										setAttributes({
											textColor:
												index !== undefined
													? textColors[index].name
													: "text-inherit",
										})
									}}
								/>
							</BaseControl>
						</PanelRow>
					</PanelBody>
					<PanelBody title="Logo" initialOpen={true}>
						<PanelRow>
							<MediaUploadCheck>
								<MediaUpload
									onSelect={(media) => {
										setAttributes({ logo: media.url })
									}}
									allowedTypes={['image']}
									value={logo}
									render={({ open }) => (
										<div>
											{logo && (
												<img
													src={logo}
													alt="Logo Preview"
													style={{ maxWidth: '100px', marginBottom: '10px' }}
												/>
											)}
											<Button onClick={open} variant="primary">
												{logo ? 'Logo ändern' : 'Logo auswählen'}
											</Button>
											{logo && (
												<Button
													onClick={() => setAttributes({ logo: '' })}
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

					<PanelBody title="Spiel-Details" initialOpen={true}>
						<PanelRow>
							<TextControl
								label="Spieltyp-Titel"
								value={game_type_title}
								onChange={(nextValue) => {
									setAttributes({ game_type_title: nextValue })
								}}
							/>
						</PanelRow>
					</PanelBody>
				</InspectorControls>

				<div {...useBlockProps()}>
					<section id="intro" className={`${background_type || 'bg-white'} relative pb-8 `}>
						<div className="template-background rounded-b-3xl h-full w-full absolute top-0 opacity-10 bg-cover"></div>

						<div className="container relative py-12 sm:max-w-2xl sm:mx-auto">
							<div className="absolute block w-max">
								{logo && (
									<img
										alt="Logo"
										className="mb-8 w-16 l-16 rounded-full"
										src={logo}
									/>
								)}
							</div>

							<div className="backplate mt-20 mb-10 z-[-1] absolute inset-0 bg-gradient-to-r from-secondary to-primary shadow-lg transform -skew-y-6 sm:skew-y-0 sm:-rotate-6 sm:rounded-3xl"></div>

							<div className={`bg-white mt-20 p-8 rounded-t-3xl ${borderStyle === 'border-torn' ? 'border-torn-top' : ''} ${borderStyle === 'border-wavy' ? 'border-wavy-bottom' : ''} ${borderStyle}`}>
								<div className="container sm:max-w-xl sm:mx-auto">
									<RichText
										tagName="h1"
										className={classnames("text-4xl pb-10 flex justify-center", textColor)}
										placeholder="Kampagnen-Titel eingeben..."
										value={campaign_title}
										onChange={(nextValue) => {
											setAttributes({ campaign_title: nextValue })
										}}
										allowedFormats={[
											'core/bold',
											'core/italic',
											'core/link',
											'core/strikethrough',
											'core/underline',
											'core/subscript',
											'core/superscript',
										]}
									/>

									<div className="flex flex-col">
										<RichText
											tagName="div"
											placeholder="Kampagnen-Beschreibung eingeben..."
											value={campaign_desc_text}
											onChange={(nextValue) => {
												setAttributes({ campaign_desc_text: nextValue })
											}}
											allowedFormats={[
												'core/bold',
												'core/italic',
												'core/link',
												'core/strikethrough',
												'core/underline',
												'core/subscript',
												'core/superscript',
											]}
										/>
									</div>

									<div className="flex justify-center mt-4">
										<p className="text-center">
											<strong>Wir spielen <RichText
												tagName="span"
												placeholder="Spieltyp"
												value={game_type_title}
												onChange={(nextValue) => {
													setAttributes({ game_type_title: nextValue })
												}}
												allowedFormats={[
													'core/bold',
													'core/italic',
													'core/link',
													'core/strikethrough',
													'core/underline',
													'core/subscript',
													'core/superscript',
												]}
											/>!</strong>
										</p>
									</div>
								</div>

								<div className="flex justify-center">
									<a href="#spiel">
										<button className="flex flex-row items-center justify-between mt-8 px-4 xs:px-8 py-3 xs:py-[1.125rem] w-max bg-secondary hover:bg-white rounded-lg text-white font-medium text-lg xs:text-xl hover:text-secondary hover:ring-2 hover:ring-secondary cursor-pointer">
											Zum Spiel
										</button>
									</a>
								</div>

									<p className="text-center mt-4 -mb-4 font-light">
										Die Teilnahme an dieser Aktion ist exklusiv für Sie bis zum DD.MM.YYYY möglich.
									</p>

							</div>
						</div>

					</section>
				</div>
			</>
		)
	},
	save: () => {
		return null // Da wir render.php verwenden
	}
})
