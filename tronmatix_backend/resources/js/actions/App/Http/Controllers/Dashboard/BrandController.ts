import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Dashboard\BrandController::store
 * @see app/Http/Controllers/Dashboard/BrandController.php:24
 * @route '/dashboard/brands'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/dashboard/brands',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\BrandController::store
 * @see app/Http/Controllers/Dashboard/BrandController.php:24
 * @route '/dashboard/brands'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\BrandController::store
 * @see app/Http/Controllers/Dashboard/BrandController.php:24
 * @route '/dashboard/brands'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\BrandController::store
 * @see app/Http/Controllers/Dashboard/BrandController.php:24
 * @route '/dashboard/brands'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\BrandController::store
 * @see app/Http/Controllers/Dashboard/BrandController.php:24
 * @route '/dashboard/brands'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Dashboard\BrandController::update
 * @see app/Http/Controllers/Dashboard/BrandController.php:37
 * @route '/dashboard/brands/{brand}'
 */
export const update = (args: { brand: number | { id: number } } | [brand: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/dashboard/brands/{brand}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Dashboard\BrandController::update
 * @see app/Http/Controllers/Dashboard/BrandController.php:37
 * @route '/dashboard/brands/{brand}'
 */
update.url = (args: { brand: number | { id: number } } | [brand: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { brand: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { brand: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    brand: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        brand: typeof args.brand === 'object'
                ? args.brand.id
                : args.brand,
                }

    return update.definition.url
            .replace('{brand}', parsedArgs.brand.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\BrandController::update
 * @see app/Http/Controllers/Dashboard/BrandController.php:37
 * @route '/dashboard/brands/{brand}'
 */
update.put = (args: { brand: number | { id: number } } | [brand: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Dashboard\BrandController::update
 * @see app/Http/Controllers/Dashboard/BrandController.php:37
 * @route '/dashboard/brands/{brand}'
 */
    const updateForm = (args: { brand: number | { id: number } } | [brand: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\BrandController::update
 * @see app/Http/Controllers/Dashboard/BrandController.php:37
 * @route '/dashboard/brands/{brand}'
 */
        updateForm.put = (args: { brand: number | { id: number } } | [brand: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Dashboard\BrandController::toggle
 * @see app/Http/Controllers/Dashboard/BrandController.php:50
 * @route '/dashboard/brands/{brand}/toggle'
 */
export const toggle = (args: { brand: number | { id: number } } | [brand: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggle.url(args, options),
    method: 'patch',
})

toggle.definition = {
    methods: ["patch"],
    url: '/dashboard/brands/{brand}/toggle',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Dashboard\BrandController::toggle
 * @see app/Http/Controllers/Dashboard/BrandController.php:50
 * @route '/dashboard/brands/{brand}/toggle'
 */
toggle.url = (args: { brand: number | { id: number } } | [brand: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { brand: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { brand: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    brand: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        brand: typeof args.brand === 'object'
                ? args.brand.id
                : args.brand,
                }

    return toggle.definition.url
            .replace('{brand}', parsedArgs.brand.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\BrandController::toggle
 * @see app/Http/Controllers/Dashboard/BrandController.php:50
 * @route '/dashboard/brands/{brand}/toggle'
 */
toggle.patch = (args: { brand: number | { id: number } } | [brand: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggle.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Dashboard\BrandController::toggle
 * @see app/Http/Controllers/Dashboard/BrandController.php:50
 * @route '/dashboard/brands/{brand}/toggle'
 */
    const toggleForm = (args: { brand: number | { id: number } } | [brand: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: toggle.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\BrandController::toggle
 * @see app/Http/Controllers/Dashboard/BrandController.php:50
 * @route '/dashboard/brands/{brand}/toggle'
 */
        toggleForm.patch = (args: { brand: number | { id: number } } | [brand: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: toggle.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    toggle.form = toggleForm
/**
* @see \App\Http\Controllers\Dashboard\BrandController::destroy
 * @see app/Http/Controllers/Dashboard/BrandController.php:57
 * @route '/dashboard/brands/{brand}'
 */
export const destroy = (args: { brand: number | { id: number } } | [brand: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/dashboard/brands/{brand}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Dashboard\BrandController::destroy
 * @see app/Http/Controllers/Dashboard/BrandController.php:57
 * @route '/dashboard/brands/{brand}'
 */
destroy.url = (args: { brand: number | { id: number } } | [brand: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { brand: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { brand: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    brand: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        brand: typeof args.brand === 'object'
                ? args.brand.id
                : args.brand,
                }

    return destroy.definition.url
            .replace('{brand}', parsedArgs.brand.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\BrandController::destroy
 * @see app/Http/Controllers/Dashboard/BrandController.php:57
 * @route '/dashboard/brands/{brand}'
 */
destroy.delete = (args: { brand: number | { id: number } } | [brand: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Dashboard\BrandController::destroy
 * @see app/Http/Controllers/Dashboard/BrandController.php:57
 * @route '/dashboard/brands/{brand}'
 */
    const destroyForm = (args: { brand: number | { id: number } } | [brand: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\BrandController::destroy
 * @see app/Http/Controllers/Dashboard/BrandController.php:57
 * @route '/dashboard/brands/{brand}'
 */
        destroyForm.delete = (args: { brand: number | { id: number } } | [brand: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const BrandController = { store, update, toggle, destroy }

export default BrandController