import {registerBlockType} from '@wordpress/blocks'
import {
	RichText,
	useBlockProps,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck
} from '@wordpress/block-editor'
import {
	PanelBody,
	PanelRow, SelectControl,
	TextControl,
	Button, ColorPalette, BaseControl
} from '@wordpress/components'

import classnames from "classnames"
import metadata from './block.json'
import {textColors} from "../../utils/colorPalette"
import {useEffect, useState} from "@wordpress/element";
import {useSelect} from "@wordpress/data";
import apiFetch from '@wordpress/api-fetch';

registerBlockType(metadata, {
	edit: ({attributes, setAttributes}) => {
		const {
			gameType, background_type, headline, textColor
		} = attributes


		const [isLoading, setIsLoading] = useState(true);

		const currentPostId = useSelect((select) => select('core/editor')?.getCurrentPostId(), []);

		useEffect(() => {
			if (!currentPostId) return;

			setIsLoading(true);

			apiFetch({
				path: `/wp/v2/charigame-campaign?meta_key=linked_landing_page&meta_value=${currentPostId}&_fields=game_type`,
			})
				.then(async (campaigns) => {
					if (!campaigns.length) {
						console.warn('Keine zugehörige Campaign gefunden');
						setIsLoading(false);
						return;
					}
					const campaign = campaigns[0];
					setAttributes({gameType: campaign.game_type});
					setIsLoading(false);
				})
				.catch((error) => {
					console.error('Fehler beim Laden der Campaign:', error);
					setIsLoading(false);
				});
		}, [currentPostId]);


		return (
			<>
				<InspectorControls>
					<PanelBody title="Background-Einstellungen"
							   initialOpen={true}>
						<PanelRow>
							<SelectControl
								label="Background auswählen"
								value={background_type}
								options={[
									{label: 'Kein Background', value: 'bg-white'},
									{label: 'Gradient 1', value: 'bg-gradient-to-t from-primary to-secondary relative'},
									{label: 'Gradient 2', value: 'bg-gradient-to-r from-primary to-secondary relative'},
									{label: 'Gradient 3', value: 'bg-gradient-to-b from-primary to-secondary relative'},
									{label: 'Gradient 4', value: 'bg-gradient-to-l from-primary to-secondary relative'},
									{label: 'Grid', value: 'grid-bg'},
									{label: 'Pastel Wave', value: 'pastel-wave'},
									{label: 'Radial', value: 'radial-bg'},
									{label: 'Gray', value: 'bg-gray-100'}
								]}
								onChange={(value) => setAttributes({background_type: value})}
							/>
						</PanelRow>
					</PanelBody>
					<PanelBody title="Textfarbe"
							   initialOpen={true}>
						<PanelRow>
							<BaseControl label="Farbe auswählen"
										 id="text-color">
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
				</InspectorControls>

				<div {...useBlockProps()}>
					<section id="game"
							 className={`${background_type || 'bg-white'} relative py-12`}>

						<RichText
							tagName="h2"
							className={classnames("font-main text-4xl pb-10 text-center", textColor)}
							placeholder="Der aktuelle Spendenstand im Überblick"
							value={headline}
							onChange={(nextValue) => {
								setAttributes({headline: nextValue})
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
						<div className={classnames("container relative py-3 sm:max-w-xl sm:mx-auto flex justify-center items-center min-h-[40vh]", textColor)}>
							{isLoading ? (
								"Loading game type..."
							) : (
								`Here appears the selected game type: ${
									gameType
										? gameType.charAt(0).toUpperCase() + gameType.slice(1)
										: "Unknown"
								}`
							)}
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
