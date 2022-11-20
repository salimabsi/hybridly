import type { RouterContext } from '@hybridly/core'
import { debug } from '@hybridly/utils'
import type { ComponentOptions } from 'vue'
import { ref, shallowRef, triggerRef, unref } from 'vue'
import type { Layout } from '../composables/layout'
import type { RouteCollection } from '../routes'
import type { MaybeRef } from '../utils'

// Todo multiple stores
export const state = {
	context: ref<RouterContext>(),
	view: shallowRef<ComponentOptions>(),
	viewLayout: shallowRef<Layout>(),
	viewLayoutProperties: ref<any>(),
	viewKey: ref<number>(),
	routes: ref<RouteCollection>(),

	setRoutes(routes?: MaybeRef<RouteCollection>) {
		debug.adapter('vue:state:routes', 'Setting routes:', routes)
		if (routes) {
			state.routes.value = unref(routes)
		}
	},

	setView(view: MaybeRef<ComponentOptions>) {
		debug.adapter('vue:state:view', 'Setting view:', view)
		state.view.value = view
	},

	setViewLayout(layout: Layout | Layout[]) {
		debug.adapter('vue:state:view', 'Setting layout', layout)
		state.viewLayout.value = layout
	},

	setViewLayoutProperties(properties: any) {
		debug.adapter('vue:state:view', 'Setting layout properties:', properties)
		state.viewLayoutProperties.value = properties
	},

	setContext(context: MaybeRef<RouterContext>, trigger?: boolean) {
		debug.adapter('vue:state:context', 'Setting context:', context)
		state.context.value = unref(context)

		if (trigger !== false) {
			triggerRef(state.context)
		}
	},

	setViewKey(key: MaybeRef<number>) {
		debug.adapter('vue:state:key', 'Setting view key:', key)
		state.viewKey.value = unref(key)
	},
}
