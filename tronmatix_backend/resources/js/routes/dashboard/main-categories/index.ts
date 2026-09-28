import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Dashboard\MainCategoryController::store
 * @see app/Http/Controllers/Dashboard/MainCategoryController.php:22
 * @route '/dashboard/main-categories'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/dashboard/main-categories',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\MainCategoryController::store
 * @see app/Http/Controllers/Dashboard/MainCategoryController.php:22
 * @route '/dashboard/main-categories'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\MainCategoryController::store
 * @see app/Http/Controllers/Dashboard/MainCategoryController.php:22
 * @route '/dashboard/main-categories'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\MainCategoryController::store
 * @see app/Http/Controllers/Dashboard/MainCategoryController.php:22
 * @route '/dashboard/main-categories'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\MainCategoryController::store
 * @see app/Http/Controllers/Dashboard/MainCategoryController.php:22
 * @route '/dashboard/main-categories'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Dashboard\MainCategoryController::update
 * @see app/Http/Controllers/Dashboard/MainCategoryController.php:34
 * @route '/dashboard/main-categories/{mainCategory}'
 */
export const update = (args: { mainCategory: number | { id: number } } | [mainCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/dashboard/main-categories/{mainCategory}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Dashboard\MainCategoryController::update
 * @see app/Http/Controllers/Dashboard/MainCategoryController.php:34
 * @route '/dashboard/main-categories/{mainCategory}'
 */
update.url = (args: { mainCategory: number | { id: number } } | [mainCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { mainCategory: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { mainCategory: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    mainCategory: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        mainCategory: typeof args.mainCategory === 'object'
                ? args.mainCategory.id
                : args.mainCategory,
                }

    return update.definition.url
            .replace('{mainCategory}', parsedArgs.mainCategory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\MainCategoryController::update
 * @see app/Http/Controllers/Dashboard/MainCategoryController.php:34
 * @route '/dashboard/main-categories/{mainCategory}'
 */
update.put = (args: { mainCategory: number | { id: number } } | [mainCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Dashboard\MainCategoryController::update
 * @see app/Http/Controllers/Dashboard/MainCategoryController.php:34
 * @route '/dashboard/main-categories/{mainCategory}'
 */
    const updateForm = (args: { mainCategory: number | { id: number } } | [mainCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\MainCategoryController::update
 * @see app/Http/Controllers/Dashboard/MainCategoryController.php:34
 * @route '/dashboard/main-categories/{mainCategory}'
 */
        updateForm.put = (args: { mainCategory: number | { id: number } } | [mainCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Dashboard\MainCategoryController::toggle
 * @see app/Http/Controllers/Dashboard/MainCategoryController.php:46
 * @route '/dashboard/main-categories/{mainCategory}/toggle'
 */
export const toggle = (args: { mainCategory: number | { id: number } } | [mainCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggle.url(args, options),
    method: 'patch',
})

toggle.definition = {
    methods: ["patch"],
    url: '/dashboard/main-categories/{mainCategory}/toggle',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Dashboard\MainCategoryController::toggle
 * @see app/Http/Controllers/Dashboard/MainCategoryController.php:46
 * @route '/dashboard/main-categories/{mainCategory}/toggle'
 */
toggle.url = (args: { mainCategory: number | { id: number } } | [mainCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { mainCategory: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { mainCategory: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    mainCategory: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        mainCategory: typeof args.mainCategory === 'object'
                ? args.mainCategory.id
                : args.mainCategory,
                }

    return toggle.definition.url
            .replace('{mainCategory}', parsedArgs.mainCategory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\MainCategoryController::toggle
 * @see app/Http/Controllers/Dashboard/MainCategoryController.php:46
 * @route '/dashboard/main-categories/{mainCategory}/toggle'
 */
toggle.patch = (args: { mainCategory: number | { id: number } } | [mainCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggle.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Dashboard\MainCategoryController::toggle
 * @see app/Http/Controllers/Dashboard/MainCategoryController.php:46
 * @route '/dashboard/main-categories/{mainCategory}/toggle'
 */
    const toggleForm = (args: { mainCategory: number | { id: number } } | [mainCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: toggle.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\MainCategoryController::toggle
 * @see app/Http/Controllers/Dashboard/MainCategoryController.php:46
 * @route '/dashboard/main-categories/{mainCategory}/toggle'
 */
        toggleForm.patch = (args: { mainCategory: number | { id: number } } | [mainCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Dashboard\MainCategoryController::destroy
 * @see app/Http/Controllers/Dashboard/MainCategoryController.php:53
 * @route '/dashboard/main-categories/{mainCategory}'
 */
export const destroy = (args: { mainCategory: number | { id: number } } | [mainCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/dashboard/main-categories/{mainCategory}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Dashboard\MainCategoryController::destroy
 * @see app/Http/Controllers/Dashboard/MainCategoryController.php:53
 * @route '/dashboard/main-categories/{mainCategory}'
 */
destroy.url = (args: { mainCategory: number | { id: number } } | [mainCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { mainCategory: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { mainCategory: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    mainCategory: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        mainCategory: typeof args.mainCategory === 'object'
                ? args.mainCategory.id
                : args.mainCategory,
                }

    return destroy.definition.url
            .replace('{mainCategory}', parsedArgs.mainCategory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\MainCategoryController::destroy
 * @see app/Http/Controllers/Dashboard/MainCategoryController.php:53
 * @route '/dashboard/main-categories/{mainCategory}'
 */
destroy.delete = (args: { mainCategory: number | { id: number } } | [mainCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Dashboard\MainCategoryController::destroy
 * @see app/Http/Controllers/Dashboard/MainCategoryController.php:53
 * @route '/dashboard/main-categories/{mainCategory}'
 */
    const destroyForm = (args: { mainCategory: number | { id: number } } | [mainCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\MainCategoryController::destroy
 * @see app/Http/Controllers/Dashboard/MainCategoryController.php:53
 * @route '/dashboard/main-categories/{mainCategory}'
 */
        destroyForm.delete = (args: { mainCategory: number | { id: number } } | [mainCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const mainCategories = {
    store: Object.assign(store, store),
update: Object.assign(update, update),
toggle: Object.assign(toggle, toggle),
destroy: Object.assign(destroy, destroy),
}

export default mainCategories