import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Dashboard\SubCategoryController::store
 * @see app/Http/Controllers/Dashboard/SubCategoryController.php:22
 * @route '/dashboard/sub-categories'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/dashboard/sub-categories',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\SubCategoryController::store
 * @see app/Http/Controllers/Dashboard/SubCategoryController.php:22
 * @route '/dashboard/sub-categories'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\SubCategoryController::store
 * @see app/Http/Controllers/Dashboard/SubCategoryController.php:22
 * @route '/dashboard/sub-categories'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\SubCategoryController::store
 * @see app/Http/Controllers/Dashboard/SubCategoryController.php:22
 * @route '/dashboard/sub-categories'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\SubCategoryController::store
 * @see app/Http/Controllers/Dashboard/SubCategoryController.php:22
 * @route '/dashboard/sub-categories'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Dashboard\SubCategoryController::update
 * @see app/Http/Controllers/Dashboard/SubCategoryController.php:34
 * @route '/dashboard/sub-categories/{subCategory}'
 */
export const update = (args: { subCategory: number | { id: number } } | [subCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/dashboard/sub-categories/{subCategory}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Dashboard\SubCategoryController::update
 * @see app/Http/Controllers/Dashboard/SubCategoryController.php:34
 * @route '/dashboard/sub-categories/{subCategory}'
 */
update.url = (args: { subCategory: number | { id: number } } | [subCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { subCategory: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { subCategory: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    subCategory: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        subCategory: typeof args.subCategory === 'object'
                ? args.subCategory.id
                : args.subCategory,
                }

    return update.definition.url
            .replace('{subCategory}', parsedArgs.subCategory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\SubCategoryController::update
 * @see app/Http/Controllers/Dashboard/SubCategoryController.php:34
 * @route '/dashboard/sub-categories/{subCategory}'
 */
update.put = (args: { subCategory: number | { id: number } } | [subCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Dashboard\SubCategoryController::update
 * @see app/Http/Controllers/Dashboard/SubCategoryController.php:34
 * @route '/dashboard/sub-categories/{subCategory}'
 */
    const updateForm = (args: { subCategory: number | { id: number } } | [subCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\SubCategoryController::update
 * @see app/Http/Controllers/Dashboard/SubCategoryController.php:34
 * @route '/dashboard/sub-categories/{subCategory}'
 */
        updateForm.put = (args: { subCategory: number | { id: number } } | [subCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Dashboard\SubCategoryController::toggle
 * @see app/Http/Controllers/Dashboard/SubCategoryController.php:46
 * @route '/dashboard/sub-categories/{subCategory}/toggle'
 */
export const toggle = (args: { subCategory: number | { id: number } } | [subCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggle.url(args, options),
    method: 'patch',
})

toggle.definition = {
    methods: ["patch"],
    url: '/dashboard/sub-categories/{subCategory}/toggle',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Dashboard\SubCategoryController::toggle
 * @see app/Http/Controllers/Dashboard/SubCategoryController.php:46
 * @route '/dashboard/sub-categories/{subCategory}/toggle'
 */
toggle.url = (args: { subCategory: number | { id: number } } | [subCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { subCategory: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { subCategory: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    subCategory: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        subCategory: typeof args.subCategory === 'object'
                ? args.subCategory.id
                : args.subCategory,
                }

    return toggle.definition.url
            .replace('{subCategory}', parsedArgs.subCategory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\SubCategoryController::toggle
 * @see app/Http/Controllers/Dashboard/SubCategoryController.php:46
 * @route '/dashboard/sub-categories/{subCategory}/toggle'
 */
toggle.patch = (args: { subCategory: number | { id: number } } | [subCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggle.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Dashboard\SubCategoryController::toggle
 * @see app/Http/Controllers/Dashboard/SubCategoryController.php:46
 * @route '/dashboard/sub-categories/{subCategory}/toggle'
 */
    const toggleForm = (args: { subCategory: number | { id: number } } | [subCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: toggle.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\SubCategoryController::toggle
 * @see app/Http/Controllers/Dashboard/SubCategoryController.php:46
 * @route '/dashboard/sub-categories/{subCategory}/toggle'
 */
        toggleForm.patch = (args: { subCategory: number | { id: number } } | [subCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Dashboard\SubCategoryController::destroy
 * @see app/Http/Controllers/Dashboard/SubCategoryController.php:53
 * @route '/dashboard/sub-categories/{subCategory}'
 */
export const destroy = (args: { subCategory: number | { id: number } } | [subCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/dashboard/sub-categories/{subCategory}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Dashboard\SubCategoryController::destroy
 * @see app/Http/Controllers/Dashboard/SubCategoryController.php:53
 * @route '/dashboard/sub-categories/{subCategory}'
 */
destroy.url = (args: { subCategory: number | { id: number } } | [subCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { subCategory: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { subCategory: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    subCategory: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        subCategory: typeof args.subCategory === 'object'
                ? args.subCategory.id
                : args.subCategory,
                }

    return destroy.definition.url
            .replace('{subCategory}', parsedArgs.subCategory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\SubCategoryController::destroy
 * @see app/Http/Controllers/Dashboard/SubCategoryController.php:53
 * @route '/dashboard/sub-categories/{subCategory}'
 */
destroy.delete = (args: { subCategory: number | { id: number } } | [subCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Dashboard\SubCategoryController::destroy
 * @see app/Http/Controllers/Dashboard/SubCategoryController.php:53
 * @route '/dashboard/sub-categories/{subCategory}'
 */
    const destroyForm = (args: { subCategory: number | { id: number } } | [subCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\SubCategoryController::destroy
 * @see app/Http/Controllers/Dashboard/SubCategoryController.php:53
 * @route '/dashboard/sub-categories/{subCategory}'
 */
        destroyForm.delete = (args: { subCategory: number | { id: number } } | [subCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const subCategories = {
    store: Object.assign(store, store),
update: Object.assign(update, update),
toggle: Object.assign(toggle, toggle),
destroy: Object.assign(destroy, destroy),
}

export default subCategories