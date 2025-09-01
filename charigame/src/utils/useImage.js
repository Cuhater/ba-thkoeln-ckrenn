import apiFetch from '@wordpress/api-fetch'

/**
 * Fetches an image from the WordPress API and sets the image URL in the block attributes.
 * @param {number} imageID The image ID.
 * @param {function} setAttributes The setAttributes function from the block.
 * @param {string} attributeName The name of the attribute to set.
 */
export async function useImage (imageID, setAttributes, attributeName = 'imageUrl') {
	if (!imageID) {
		return
	}

	const image = await apiFetch({ path: `/wp/v2/media/${imageID}`, method: 'GET' })

	setAttributes({ [attributeName]: image.source_url })
}
