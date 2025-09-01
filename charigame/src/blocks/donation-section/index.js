import {registerBlockType} from '@wordpress/blocks';
import {
	RichText,
	useBlockProps,
	InspectorControls,
} from '@wordpress/block-editor';
import {
	PanelBody,
	PanelRow,
	SelectControl,
	BaseControl,
	ColorPalette,
} from '@wordpress/components';

// Importiere die BackgroundSelect-Komponente
import BackgroundSelect from '../../components/BackgroundSelect';
import {useState} from '@wordpress/element';

import classnames from "classnames";
import metadata from './block.json';
import {textColors} from "../../utils/colorPalette";
import {backgroundColors} from "../../utils/colorPalette";

// Import the campaign data utility
import { useCampaignData } from '../../utils/useCampaignData';
import BorderStyleSelect from "../../components/BorderStyleSelect";


registerBlockType(metadata, {
	edit: ({attributes, setAttributes}) => {
		const {
			headline,
			donationDescText,
			background_type,
			donationTitle,
			textColor,
			barColor,
			borderStyle,
		} = attributes;

		const { recipientsData, isLoading, campaignId } = useCampaignData({
			onSuccess: (recipients, campaignId) => {
				setAttributes({ recipients: recipients });
			},
			onError: (error) => {
				console.error('Error loading recipients:', error);
			}
		});

		return (
			<>
				<InspectorControls>
					<PanelBody title="Background-Einstellungen"
							   initialOpen={true}>
						<PanelRow>
							<BackgroundSelect
								value={background_type}
								onChange={(value) => setAttributes({background_type: value})}
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
					<PanelBody title="Balkenfarbe"
							   initialOpen={true}>
						<PanelRow>
							<BaseControl label="Farbe auswählen"
										 id="text-color">
								<ColorPalette
									value={barColor}
									colors={backgroundColors}
									disableCustomColors={true}
									onChange={(value, index) => {
										setAttributes({
											barColor:
												index !== undefined
													? backgroundColors[index].name
													: "text-inherit",
										})
									}}
								/>
							</BaseControl>
						</PanelRow>
					</PanelBody>
				</InspectorControls>

				<div {...useBlockProps()}>

					<section className={`${background_type || 'bg-gradient-to-r from-primary to-secondary relative'} relative`}>
						<div className={`${background_type || 'bg-gradient-to-r from-primary to-secondary relative'} ${borderStyle === 'border-torn' ? 'border-torn-top' : ''} ${borderStyle === 'border-wavy' ? 'border-wavy-bottom' : ''} ${borderStyle}`}></div>
						<div className="container relative py-12 sm:max-w-6xl sm:mx-auto">

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
							<RichText
								tagName="div"
								className={classnames("text-center", textColor)}
								placeholder="Aktueller Spendentopf insgesamt:"
								value={donationTitle}
								onChange={(nextValue) => {
									setAttributes({donationTitle: nextValue})
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
							<p
								className={classnames("text-center text-2xl", textColor)}>
								<span id="donation-display">0,00</span> €
							</p>

							<div className="z-10 container relative py-3 sm:max-w-xl sm:mx-auto">
								<RichText
									tagName="div"
									className={classnames("font-main text-2xl pb-8 text-center", textColor)}
									placeholder="Die Spendenverteilung im Überblick"
									value={donationDescText}
									onChange={(nextValue) => {
										setAttributes({donationDescText: nextValue})
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

								{isLoading ? (
									<div className="text-center py-8">
										<p>Lade Recipients...</p>
									</div>
								) : recipientsData.length > 0 ? (
									recipientsData.map((recipient, index) => (
										<div className="flex max-sm:mx-8 max-sm:mb-4">

											{recipient.recipient_logo_url && (
												<img
													src={recipient.recipient_logo_url}
													alt={recipient.title?.rendered || 'Recipient'}
													className="aspect-square !max-w-12 mb-4 object-contain rounded-lg bg-white max-sm:h-[50px] max-sm:w-[50px]"
												/>
											)}
											<div className="grow ml-4">
												<div className="flex justify-between mb-1">
											<span
												className={classnames("text-base font-medium", textColor)}>
													{recipient.title?.rendered || 'Charity Name'}
											</span>
													<span
														id="recipient-num-0"
														className={classnames("text-sm font-medium max-sm:min-w-[75px] max-sm:text-right", textColor)}
													>
                           				 	33 %
                        					</span>
												</div>
												<div
													className={classnames("w-full rounded-full h-2.5", textColor)}>
													<div
														id="recipient-bar-0"
														className={classnames(" h-2.5 rounded-full", barColor)}
														style={{width: '33%'}}
													/>
												</div>
											</div>
										</div>
									))
								) : (
									Array.from({ length: 3 }).map((_, index) => (
										<div key={index} className="recipient basis-1/3 overflow-hidden mx-8 lg:mx-0 bg-gradient-to-r from-primary via-teritary2 to-secondary p-0.5 rounded-t-3xl shadow-xl max-sm:mb-4">
											<div className="bg-white p-8 flex flex-col items-center lg:-mb-1 rounded-t-3xl shadow-sm border border-gray-100 h-full">
												<h3 className="font-main font-bold text-secondary text-2xl pb-10 flex justify-center text-center min-h-20">
													Recipient {index + 1}
												</h3>
												<div className="text-center text-gray-500">
													Kein Recipient ausgewählt
												</div>
											</div>
										</div>
									))
								)}




							</div>
						</div>
					</section>

				</div>
			</>
		);
	},

	save: () => null, // Server-Side Rendering
});
