/* eslint-disable vue/order-in-components */
import type { RouterContext } from '@hybridly/core'
import { debug } from '@hybridly/utils'
import type { PropType } from 'vue'
import { defineComponent, h } from 'vue'
import { dialogStore } from '../stores/dialog'
import { state } from '../stores/state'

export const wrapper = defineComponent({
	name: 'Hybridly',
	setup(props) {
		if (typeof window !== 'undefined') {
			state.setContext(props.context)

			if (!props.context) {
				throw new Error('Hybridly was not properly initialized. The context is missing.')
			}
		}

		function renderLayout() {
			debug.adapter('vue:render:layout', 'Rendering layout.')

			const view = renderView()

			if (typeof state.view.value?.layout === 'function') {
				return state.view.value.layout(h, view)
			}

			if (Array.isArray(state.view.value?.layout)) {
				return state.view
					.value!.layout.concat(view)
					.reverse()
					.reduce((view, layout) => {
						layout.inheritAttrs = !!layout.inheritAttrs

						return [
							h(layout, {
								...(state.view.value?.layoutProperties ?? {}),
								...state.context.value!.view.properties,
							}, () => view),
							renderDialog(),
						]
					})
			}

			return h(state.view.value?.layout, {
				...(state.view.value?.layoutProperties ?? {}),
				...state.context.value!.view.properties,
			}, () => view)
		}

		function renderView() {
			debug.adapter('vue:render:view', 'Rendering view.')
			state.view.value!.inheritAttrs = !!state.view.value!.inheritAttrs

			return h(state.view.value!, {
				...state.context.value!.view.properties,
				key: state.viewKey.value,
			})
		}

		function renderDialog() {
			if (dialogStore.state.component.value && dialogStore.state.properties.value) {
				debug.adapter('vue:render:dialog', 'Rendering dialog.')

				return h(dialogStore.state.component.value!, {
					...dialogStore.state.properties.value,
					key: dialogStore.state.key.value,
				})
			}
		}

		return () => {
			if (state.view.value) {
				if (state.viewLayout.value) {
					state.view.value.layout = state.viewLayout.value
					state.viewLayout.value = undefined
				}

				if (state.viewLayoutProperties.value) {
					state.view.value.layoutProperties = state.viewLayoutProperties.value
					state.viewLayoutProperties.value = undefined
				}

				if (state.view.value.layout) {
					return renderLayout()
				}

				return [renderView(), renderDialog()]
			}
		}
	},
	props: {
		context: {
			type: Object as PropType<RouterContext>,
			required: true,
		},
	},
})
