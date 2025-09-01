import { registerBlockType } from '@wordpress/blocks';
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

import BackgroundSelect from '../../components/BackgroundSelect';
import { useSelect } from '@wordpress/data';
import { useEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

import classnames from "classnames";
import metadata from './block.json';
import { textColors } from "../../utils/colorPalette";


registerBlockType(metadata, {
	edit: ({ attributes, setAttributes }) => {
		const {
			background_type,
			direction,
			textColor,
			headline,
		} = attributes;

		const [recipientsData, setRecipientsData] = useState([]);
		const [isLoading, setIsLoading] = useState(true);

		const currentPostId = useSelect((select) => select('core/editor')?.getCurrentPostId(), []);

		useEffect(() => {
			if (!currentPostId) return;

			setIsLoading(true);

			const currentPostType = wp.data.select('core/editor').getCurrentPostType();

			const fetchRecipients = async (campaignId) => {
				try {

					const campaign = await apiFetch({
						path: `/wp/v2/charigame-campaign/${campaignId}?_fields=id,recipients`,
					});


					const recipientIds = campaign.recipients?.[0]?.recipient?.map(r => r.id) || [];

					if (!recipientIds.length) {
						console.warn('Keine Recipients in Campaign gefunden');
						setIsLoading(false);
						return;
					}

					const recipientPosts = await Promise.all(
						recipientIds.map(id =>
							apiFetch({ path: `/wp/v2/charigame-recipients/${id}` }).catch(() => null)
						)
					);

					const validRecipients = recipientPosts.filter(Boolean);


					const imageIds = validRecipients
						.map(rec => rec.recipient_logo)
						.filter(Boolean);

					const images = await Promise.all(
						imageIds.map(id =>
							apiFetch({ path: `/wp/v2/media/${id}` }).catch(() => null)
						)
					);

					const imageMap = {};
					images.forEach(img => {
						if (img && img.id) imageMap[img.id] = img.source_url;
					});

					const recipientsWithImages = validRecipients.map(recipient => ({
						...recipient,
						recipient_logo_url: imageMap[recipient.recipient_logo] || null,
					}));

					setRecipientsData(recipientsWithImages);
					setAttributes({ recipients: recipientsWithImages });
					setIsLoading(false);
				} catch (err) {
					console.error('Fehler beim Laden der Recipient-Daten:', err);
					setIsLoading(false);
				}
			};


			if (currentPostType === 'charigame-campaign') {
				fetchRecipients(currentPostId);
				return;
			}


			apiFetch({
				path: `/wp/v2/charigame-campaign?linked_landing_page=${currentPostId}&_fields=id,linked_landing_page`,
			})
				.then((campaigns) => {

					const currentCampaign = campaigns.find(c => {
						if (!c.linked_landing_page || !Array.isArray(c.linked_landing_page)) {
							return false;
						}
						return c.linked_landing_page.some(lp => parseInt(lp.id, 10) === currentPostId);
					});

					if (!currentCampaign) {
						console.warn('Keine Campaign gefunden, die Landing Page referenziert', currentPostId);
						setIsLoading(false);
						return;
					}

					const campaignId = currentCampaign.id;
					fetchRecipients(campaignId);
				})
				.catch((error) => {
					console.error('Fehler beim Suchen der verknüpften Campaign:', error);
					setIsLoading(false);
				});
		}, [currentPostId]);

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
					<PanelBody title="Textfarbe" initialOpen={true}>
						<PanelRow>
							<BaseControl label="Farbe auswählen" id="text-color">
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
					<PanelBody title="Background-Einstellungen" initialOpen={true}>
						<PanelRow>
							<SelectControl
								label="Ansicht auswählen"
								value={direction}
								options={[
									{ label: 'horizontal', value: 'lg:flex-row' },
									{ label: 'vertikal', value: 'lg:flex-col' },
								]}
								onChange={(value) => setAttributes({ direction: value })}
							/>
						</PanelRow>
					</PanelBody>
				</InspectorControls>

				<div {...useBlockProps()}>
					<section id="recipients"  className={`${background_type || 'bg-white'} relative`}>
						<div className="z-10 container relative py-12 sm:max-w-6xl sm:mx-auto">
							<RichText
								tagName="h2"
								className={classnames("font-main text-4xl pb-10 flex justify-center text-center mb-8 max-sm:px-2", textColor)}
								placeholder="Wir spenden an diese Organisationen"
								value={headline}
								onChange={(nextValue) => setAttributes({ headline: nextValue })}
							/>

							<div className={`${direction || 'lg:flex-row'} flex flex-col gap-4`}>
								{isLoading ? (
									<div className="text-center py-8">
										<p>Lade Recipients...</p>
									</div>
								) : recipientsData.length > 0 ? (
									recipientsData.map((recipient, index) => (
										<div key={recipient.id || index} className="recipient basis-1/3 overflow-hidden mx-8 lg:mx-0 bg-gradient-to-r from-primary via-teritary2 to-secondary p-0.5 rounded-t-3xl shadow-xl max-sm:mb-4">
											<div className="bg-white p-8 flex flex-col items-center lg:-mb-1 rounded-t-3xl shadow-sm border border-gray-100 h-full">
												{recipient.recipient_logo_url && (
													<img
														src={recipient.recipient_logo_url}
														alt={recipient.title?.rendered || 'Recipient'}
														className="aspect-square !max-w-48 mb-4 object-contain"
													/>
												)}
												<h3 className="font-main font-bold text-secondary text-2xl pb-10 flex justify-center text-center min-h-20">
													{recipient.title?.rendered || 'Titel nicht verfügbar'}
												</h3>
												<div className="prose prose-lg max-w-none leading-normal">
													{recipient.recipient_description ? (
														<p>{recipient.recipient_description}</p>
													) : (
														'Beschreibung nicht verfügbar'
													)}
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
