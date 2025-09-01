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
import { useEffect, useState } from '@wordpress/element';

import classnames from "classnames";
import metadata from './block.json';
import {backgroundColors, textColors} from "../../utils/colorPalette";
import { heroIconList } from "../../utils/iconlist";


registerBlockType(metadata, {
	edit: ({ attributes, setAttributes }) => {
		const { icon, headline, body, headlineColor, textColor, iconColor, backgroundColor, iconBgColor } = attributes;
		const [svgContent, setSvgContent] = useState('');

		useEffect(() => {
			if (icon) {
				fetch(`/wp-content/plugins/charigame/assets/icons/outline/${icon}.svg`)
					.then(res => res.text())
					.then(svg => {

						const svgWithColor = svg.replace(
							/<svg(.*?)>/,
							`<svg$1 class='w-6 h-6 ${iconColor}' fill='currentColor'>`
						);
						setSvgContent(svgWithColor);
					});
			}
		}, [icon, iconColor]);

		return (
			<>
				<InspectorControls>
					<PanelBody title="Headline Color" initialOpen={true}>
						<PanelRow>
							<BaseControl label="Select Color" id="text-color">
								<ColorPalette
									value={headlineColor}
									colors={textColors}
									disableCustomColors={true}
									onChange={(value, index) => {
										setAttributes({
											headlineColor:
												index !== undefined
													? textColors[index].name
													: "text-inherit",
										})
									}}
								/>
							</BaseControl>
						</PanelRow>
					</PanelBody>

					<PanelBody title="Text Color" initialOpen={true}>
						<PanelRow>
							<BaseControl label="Select Color" id="text-color">
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


					<PanelBody title="Backgroundcolor" initialOpen={true}>
						<PanelRow>
							<BaseControl label="Select Color" id="background-color">
								<ColorPalette
									value={backgroundColor}
									colors={backgroundColors}
									disableCustomColors={true}
									onChange={(value, index) => {
										setAttributes({
											backgroundColor:
												index !== undefined
													? backgroundColors[index].name
													: "bg-primary",
										})
									}}
								/>
							</BaseControl>
						</PanelRow>
					</PanelBody>

					<PanelBody title="Icon">
						<div style={{ display: 'grid', gridTemplateColumns: 'repeat(6, 1fr)', gap: '4px' }}>
							{heroIconList.map((iconName) => (
								<button
									key={iconName}
									onClick={() => setAttributes({ icon: iconName })}
									style={{
										border: attributes.icon === iconName ? '2px solid blue' : '1px solid #ddd',
										padding: '4px',
										background: 'white'
									}}
								>
									<img src={`/wp-content/plugins/charigame/assets/icons/outline/${iconName}.svg`} alt={iconName} />
								</button>
							))}
						</div>
					</PanelBody>
					<PanelBody title="Icon Color" initialOpen={true}>
						<PanelRow>
							<BaseControl label="Select Color" id="text-color">
								<ColorPalette
									value={iconColor}
									colors={textColors}
									disableCustomColors={true}
									onChange={(value, index) => {
										setAttributes({
											iconColor:
												index !== undefined
													? textColors[index].name
													: "text-secondary",
										})
									}}
								/>
							</BaseControl>
						</PanelRow>
					</PanelBody>
					<PanelBody title="Icon Backgroundcolor" initialOpen={true}>
						<PanelRow>
							<BaseControl label="Select Color" id="background-color">
								<ColorPalette
									value={iconBgColor}
									colors={backgroundColors}
									disableCustomColors={true}
									onChange={(value, index) => {
										setAttributes({
											iconBgColor:
												index !== undefined
													? backgroundColors[index].name
													: "bg-secondary",
										})
									}}
								/>
							</BaseControl>
						</PanelRow>
					</PanelBody>

				</InspectorControls>

				<div {...useBlockProps({className: `flex flex-col items-center p-4 h-full rounded-lg  ${backgroundColor}`})}>
					<div className={classnames("shrink-0 rounded-lg p-4 mb-4", iconBgColor)}>
						{icon && svgContent && (
							<span dangerouslySetInnerHTML={{ __html: svgContent }} />
						)}
					</div>
					<RichText
						tagName="span"
						className={classnames("text-lg font-bold text-center mb-2", headlineColor)}
						placeholder="Überschrift"
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
					<RichText
						tagName="span"
						className={classnames("mt-1 text-sm text-center", textColor)}
						placeholder="Der aktuelle Spendenstand im Überblick"
						value={body}
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
				</div>
			</>
		);
	},

	save: () => null,
});
