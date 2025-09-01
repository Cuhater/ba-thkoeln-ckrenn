import { registerBlockType } from '@wordpress/blocks';
import {
	RichText,
	useBlockProps,
	InspectorControls,
	useInnerBlocksProps,
	InnerBlocks
} from '@wordpress/block-editor';
import {
	PanelBody,
	PanelRow,
	SelectControl,
	BaseControl,
	ColorPalette,
	__experimentalRadio as Radio,
	__experimentalRadioGroup as RadioGroup,
} from '@wordpress/components';

import classnames from "classnames";
import metadata from './block.json';
import { textColors } from "../../utils/colorPalette";
import { gaps } from "../../utils/sizes";
import { hasChildBlocks } from "../../utils/childBlocks";


const ALLOWED_BLOCKS = ['charigame/how-to-play-item'];

const COLUMN_COUNTS = [
	{ label: '1', value: 'grid-cols-1' },
	{ label: '2', value: 'md:grid-cols-2' },
	{ label: '3', value: 'md:grid-cols-3' },
	{ label: '4', value: 'md:grid-cols-2 lg:grid-cols-4' }
];

registerBlockType(metadata, {
	edit: ({ attributes, setAttributes, clientId }) => {
		const {
			background_type,
			textColor,
			headline,
			columns,
			gap
		} = attributes;

		const classes = classnames(
			'w-full grid',
			gap,
			columns
		);

		const innerBlocksProps = useInnerBlocksProps(
			useBlockProps({
				className: classes,
			}),
			{
				allowedBlocks: ALLOWED_BLOCKS,
				orientation: 'horizontal',
				renderAppender: hasChildBlocks(clientId)
					? undefined
					: InnerBlocks.ButtonBlockAppender,
			}
		);

		return (
			<>
				<InspectorControls>
					<PanelBody title="Background-Einstellungen" initialOpen={true}>
						<PanelRow>
							<SelectControl
								label="Background auswählen"
								value={background_type}
								options={[
									{ label: 'Kein Background', value: 'bg-white' },
									{ label: 'Gradient 1', value: 'bg-gradient-to-t from-primary to-secondary relative' },
									{ label: 'Gradient 2', value: 'bg-gradient-to-r from-primary to-secondary relative' },
									{ label: 'Gradient 3', value: 'bg-gradient-to-b from-primary to-secondary relative' },
									{ label: 'Gradient 4', value: 'bg-gradient-to-l from-primary to-secondary relative' },
									{ label: 'Grid', value: 'grid-bg' },
									{ label: 'Pastel Wave', value: 'pastel-wave' },
									{ label: 'Radial', value: 'radial-bg' },
									{ label: 'Gray', value: 'bg-gray-100' }
								]}
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

					<PanelBody title="Columns" initialOpen={true}>
						<PanelRow>
							<RadioGroup label="Columns" onChange={(nextValue) => {
								setAttributes({ columns: nextValue })
							}} checked={columns}>
								{COLUMN_COUNTS.map((option) => (
									<Radio
										key={option.value}
										value={option.value}
									>
										{option.label}
									</Radio>
								))}
							</RadioGroup>
						</PanelRow>
					</PanelBody>

					<PanelBody title="Innenabstand" initialOpen={true}>
						<PanelRow>
							<RadioGroup label="Innenabstand" onChange={(nextValue) => {
								setAttributes({ gap: nextValue })
							}} checked={gap}>
								{gaps.map((option) => (
									<Radio
										key={option.label}
										value={option.value}
									>
										{option.label}
									</Radio>
								))}
							</RadioGroup>
						</PanelRow>
					</PanelBody>
				</InspectorControls>

				<div {...useBlockProps()}>
					<section className={`${background_type || 'bg-white'} relative`}>
						<div className="z-10 container relative py-12 sm:max-w-6xl sm:mx-auto">
							<RichText
								tagName="h2"
								className={classnames("font-main text-4xl pb-10 flex justify-center text-center mb-8 max-sm:px-2", textColor)}
								placeholder="So funktioniert das Spiel"
								value={headline}
								onChange={(nextValue) => setAttributes({ headline: nextValue })}
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
							<div {...innerBlocksProps} />
						</div>
					</section>
				</div>
			</>
		);
	},

	save: () => <InnerBlocks.Content />
});
