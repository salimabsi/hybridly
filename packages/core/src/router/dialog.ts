import { getInternalRouterContext } from '../context'
import { performHybridNavigation } from './router'
import type { HybridRequestOptions } from './types'

export interface UnstackOptions extends HybridRequestOptions {}

/**
 * Closes the dialog.
 */
export async function unstack(options?: UnstackOptions) {
	const context = getInternalRouterContext()
	const url = context.dialog?.redirectUrl ?? context.dialog?.baseUrl

	await context.adapter.unstack?.(context)

	if (!url) {
		return
	}

	return await performHybridNavigation({
		url,
		preserveScroll: true,
		preserveState: true,
		...options,
	})
}
