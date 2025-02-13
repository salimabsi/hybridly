
import type { HybridRequestOptions } from '@hybridly/core'
import { router } from '@hybridly/core'
import { computed } from 'vue'
import { toReactive } from '../utils'

type SortDirection = 'asc' | 'desc'

type AvailableHybridRequestOptions = Omit<HybridRequestOptions, 'url' | 'data'>

interface ToggleSortOptions extends AvailableHybridRequestOptions {
	direction?: SortDirection
}

declare global {
// #region interfaces
	interface FilterRefinement {
		/**
		 * Whether this filter is currently active.
		 */
		is_active: boolean
		/**
		 * The type of this filter.
		 */
		type: 'trashed' | 'callback' | 'exact' | 'similarity:loose' | 'similarity:begins_with_strict' | 'similarity:ends_with_strict' | string
		/**
		 * The label of the filter.
		 */
		label: string
		/**
		 * The metadata attributes of the filter.
		 */
		metadata: Record<string, any>
		/**
		 * The name of the fitler.
		 */
		name: string
		/**
		 * The current value of the filter.
		 */
		value: any
		/**
		 * Whether this filter is hidden.
		 */
		hidden: boolean
	}

	interface SortRefinement {
		/**
		 * Whether this sort is currently active.
		 */
		is_active: boolean
		/**
		 * The current direction of the sort.
		 */
		direction?: SortDirection
		/**
		 * The default direction of the sort.
		 */
		default?: SortDirection
		/**
		 * The label of the sort.
		 */
		label: string
		/**
		 * The metadata attributes of the sort.
		 */
		metadata: Record<string, any>
		/**
		 * The name of the sort.
		 */
		name: string
		/**
		 * The value corresponding to the descending sort.
		 */
		desc: string
		/**
		 * The value corresponding to the ascending sort.
		 */
		asc: string
		/**
		 * The value that will be applied on toggle.
		 */
		next: string
		/**
		 * Whether this sort is hidden.
		 */
		hidden: boolean
	}

	interface Refinements {
		/**
		 * The list of available filters.
		 */
		filters: Array<FilterRefinement>
		/**
		 * The list of available sorts.
		 */
		sorts: Array<SortRefinement>
		/**
		 * The URL scope for these refinements.
		 */
		scope?: string
		/**
		 * The scope keys for these refinements.
		 */
		keys: {
			/**
			 * The scope key for sorting.
			 */
			sorts: string
			/**
			 * The scope key for filtering.
			 */
			filters: string
		}
	}
// #endregion interfaces
}

export function useRefinements<
	Properties extends object,
	RefinementsKey extends {
		[K in keyof Properties]: Properties[K] extends Refinements ? K : never;
	}[keyof Properties],
>(properties: Properties, refinementsKeys: RefinementsKey, defaultOptions: AvailableHybridRequestOptions = {}) {
	const refinements = computed(() => properties[refinementsKeys] as Refinements)
	const sortsKey = computed(() => refinements.value.keys.sorts)
	const filtersKey = computed(() => refinements.value.keys.filters)

	async function reset(options: AvailableHybridRequestOptions = {}) {
		return await router.reload({
			...defaultOptions,
			...options,
			data: {
				[filtersKey.value]: undefined,
				[sortsKey.value]: undefined,
			},
		})
	}

	async function clearFilters(options: AvailableHybridRequestOptions = {}) {
		return await router.reload({
			...defaultOptions,
			...options,
			data: {
				[filtersKey.value]: undefined,
			},
		})
	}

	async function clearFilter(filter: string, options: AvailableHybridRequestOptions = {}) {
		return await router.reload({
			...defaultOptions,
			...options,
			data: {
				[filtersKey.value]: {
					[filter]: undefined,
				},
			},
		})
	}

	async function applyFilter(filter: string, value: any, options: AvailableHybridRequestOptions = {}) {
		if (!refinements.value.filters.find(({ name }) => name === filter)) {
			return
		}

		return await router.reload({
			...defaultOptions,
			...options,
			data: {
				[filtersKey.value]: {
					[filter]: value === '' ? undefined : value,
				},
			},
		})
	}

	async function clearSorts(options: AvailableHybridRequestOptions = {}) {
		return await router.reload({
			...defaultOptions,
			...options,
			data: {
				[sortsKey.value]: undefined,
			},
		})
	}

	function currentSorts(): Array<SortRefinement> {
		return refinements.value.sorts.filter(({ is_active }) => is_active)
	}

	function currentFilters(): Array<FilterRefinement> {
		return refinements.value.filters.filter(({ is_active }) => is_active)
	}

	function isSorting(): boolean {
		return currentSorts().length !== 0
	}

	function isFiltering(): boolean {
		return currentFilters().length !== 0
	}

	async function toggleSort(sortName: string, options?: ToggleSortOptions) {
		const sort = refinements.value.sorts.find(({ name }) => name === sortName)

		if (!sort) {
			console.warn(`[Refinement] Sort "${sortName} does not exist."`)
			return
		}

		const next = options?.direction
			? sort[options?.direction]
			: sort.next

		return await router.reload({
			...defaultOptions,
			...options,
			data: {
				[sortsKey.value]: next || undefined,
			},
		})
	}

	return {
		/**
		 * Available filters.
		 */
		filters: toReactive(refinements.value.filters),
		/**
		 * Available sorts.
		 */
		sorts: toReactive(refinements.value.sorts),
		/**
		 * Resets all filters and sorts.
		 */
		reset,
		/**
		 * Toggles the specified sort.
		 */
		toggleSort,
		/**
		 * Whether a sort is active.
		 */
		isSorting,
		/**
		 * Whether a filter is active.
		 */
		isFiltering,
		/**
		 * The current sorts.
		 */
		currentSorts,
		/**
		 * The current filters.
		 */
		currentFilters,
		/**
		 * Clears the given filter.
		 */
		clearFilter,
		/**
		 * Resets all sorts.
		 */
		clearSorts,
		/**
		 * Resets all filters.
		 */
		clearFilters,
		/**
		 * Applies the given filter.
		 */
		applyFilter,
	}
}
