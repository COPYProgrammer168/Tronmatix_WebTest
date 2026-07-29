import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Dashboard\DiscountController::store
 * @see app/Http/Controllers/Dashboard/DiscountController.php:38
 * @route '/dashboard/discounts'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/dashboard/discounts',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\DiscountController::store
 * @see app/Http/Controllers/Dashboard/DiscountController.php:38
 * @route '/dashboard/discounts'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\DiscountController::store
 * @see app/Http/Controllers/Dashboard/DiscountController.php:38
 * @route '/dashboard/discounts'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\DiscountController::store
 * @see app/Http/Controllers/Dashboard/DiscountController.php:38
 * @route '/dashboard/discounts'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\DiscountController::store
 * @see app/Http/Controllers/Dashboard/DiscountController.php:38
 * @route '/dashboard/discounts'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Dashboard\DiscountController::update
 * @see app/Http/Controllers/Dashboard/DiscountController.php:58
 * @route '/dashboard/discounts/{discount}'
 */
export const update = (args: { discount: number | { id: number } } | [discount: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/dashboard/discounts/{discount}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Dashboard\DiscountController::update
 * @see app/Http/Controllers/Dashboard/DiscountController.php:58
 * @route '/dashboard/discounts/{discount}'
 */
update.url = (args: { discount: number | { id: number } } | [discount: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { discount: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { discount: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    discount: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        discount: typeof args.discount === 'object'
                ? args.discount.id
                : args.discount,
                }

    return update.definition.url
            .replace('{discount}', parsedArgs.discount.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\DiscountController::update
 * @see app/Http/Controllers/Dashboard/DiscountController.php:58
 * @route '/dashboard/discounts/{discount}'
 */
update.put = (args: { discount: number | { id: number } } | [discount: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Dashboard\DiscountController::update
 * @see app/Http/Controllers/Dashboard/DiscountController.php:58
 * @route '/dashboard/discounts/{discount}'
 */
    const updateForm = (args: { discount: number | { id: number } } | [discount: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\DiscountController::update
 * @see app/Http/Controllers/Dashboard/DiscountController.php:58
 * @route '/dashboard/discounts/{discount}'
 */
        updateForm.put = (args: { discount: number | { id: number } } | [discount: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\Dashboard\DiscountController::destroy
 * @see app/Http/Controllers/Dashboard/DiscountController.php:78
 * @route '/dashboard/discounts/{discount}'
 */
export const destroy = (args: { discount: number | { id: number } } | [discount: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/dashboard/discounts/{discount}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Dashboard\DiscountController::destroy
 * @see app/Http/Controllers/Dashboard/DiscountController.php:78
 * @route '/dashboard/discounts/{discount}'
 */
destroy.url = (args: { discount: number | { id: number } } | [discount: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { discount: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { discount: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    discount: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        discount: typeof args.discount === 'object'
                ? args.discount.id
                : args.discount,
                }

    return destroy.definition.url
            .replace('{discount}', parsedArgs.discount.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\DiscountController::destroy
 * @see app/Http/Controllers/Dashboard/DiscountController.php:78
 * @route '/dashboard/discounts/{discount}'
 */
destroy.delete = (args: { discount: number | { id: number } } | [discount: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Dashboard\DiscountController::destroy
 * @see app/Http/Controllers/Dashboard/DiscountController.php:78
 * @route '/dashboard/discounts/{discount}'
 */
    const destroyForm = (args: { discount: number | { id: number } } | [discount: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\DiscountController::destroy
 * @see app/Http/Controllers/Dashboard/DiscountController.php:78
 * @route '/dashboard/discounts/{discount}'
 */
        destroyForm.delete = (args: { discount: number | { id: number } } | [discount: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
/**
* @see \App\Http\Controllers\Dashboard\DiscountController::saveBadge
 * @see app/Http/Controllers/Dashboard/DiscountController.php:90
 * @route '/dashboard/discounts/{discount}/badge'
 */
export const saveBadge = (args: { discount: number | { id: number } } | [discount: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: saveBadge.url(args, options),
    method: 'patch',
})

saveBadge.definition = {
    methods: ["patch"],
    url: '/dashboard/discounts/{discount}/badge',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Dashboard\DiscountController::saveBadge
 * @see app/Http/Controllers/Dashboard/DiscountController.php:90
 * @route '/dashboard/discounts/{discount}/badge'
 */
saveBadge.url = (args: { discount: number | { id: number } } | [discount: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { discount: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { discount: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    discount: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        discount: typeof args.discount === 'object'
                ? args.discount.id
                : args.discount,
                }

    return saveBadge.definition.url
            .replace('{discount}', parsedArgs.discount.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\DiscountController::saveBadge
 * @see app/Http/Controllers/Dashboard/DiscountController.php:90
 * @route '/dashboard/discounts/{discount}/badge'
 */
saveBadge.patch = (args: { discount: number | { id: number } } | [discount: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: saveBadge.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Dashboard\DiscountController::saveBadge
 * @see app/Http/Controllers/Dashboard/DiscountController.php:90
 * @route '/dashboard/discounts/{discount}/badge'
 */
    const saveBadgeForm = (args: { discount: number | { id: number } } | [discount: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: saveBadge.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\DiscountController::saveBadge
 * @see app/Http/Controllers/Dashboard/DiscountController.php:90
 * @route '/dashboard/discounts/{discount}/badge'
 */
        saveBadgeForm.patch = (args: { discount: number | { id: number } } | [discount: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: saveBadge.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    saveBadge.form = saveBadgeForm
const DiscountController = { store, update, destroy, saveBadge }

export default DiscountController