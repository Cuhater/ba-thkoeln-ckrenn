import { store as blockEditorStore } from '@wordpress/block-editor'
import { useSelect } from '@wordpress/data'

export function hasChildBlocks(clientId) {
	const { hasChildBlocks } = useSelect(
		(select) => {
			const { getBlockOrder, getBlockRootClientId } =
				select(blockEditorStore)

			const rootId = getBlockRootClientId(clientId)

			return {
				hasChildBlocks: getBlockOrder(clientId).length > 0,
				columnsIds: getBlockOrder(rootId),
			}
		},
		[clientId]
	)
	return hasChildBlocks
}
