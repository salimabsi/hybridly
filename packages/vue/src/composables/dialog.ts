import { router } from 'hybridly'
import { computed } from 'vue'
import { dialogStore } from '../stores/dialog'
import { state } from '../stores/state'

/**
 * Exposes utilities related to the dialogs.
 */
export function useDialog() {
	return {
		/** Closes the dialog. */
		close: () => router.unstack(),
		/** Closes the dialog. */
		unstack: router.unstack,
		/** Whether the dialog is shown. */
		show: computed(() => dialogStore.state.show.value),
		/** Properties of the dialog. */
		properties: computed(() => state.context.value?.dialog?.properties),
	}
}
