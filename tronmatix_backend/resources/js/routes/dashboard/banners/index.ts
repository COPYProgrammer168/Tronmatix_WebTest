import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Dashboard\BannerController::store
 * @see app/Http/Controllers/Dashboard/BannerController.php:26
 * @route '/dashboard/banners'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/dashboard/banners',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\BannerController::store
 * @see app/Http/Controllers/Dashboard/BannerController.php:26
 * @route '/dashboard/banners'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\BannerController::store
 * @see app/Http/Controllers/Dashboard/BannerController.php:26
 * @route '/dashboard/banners'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\BannerController::store
 * @see app/Http/Controllers/Dashboard/BannerController.php:26
 * @route '/dashboard/banners'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\BannerController::store
 * @see app/Http/Controllers/Dashboard/BannerController.php:26
 * @route '/dashboard/banners'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Dashboard\BannerController::update
 * @see app/Http/Controllers/Dashboard/BannerController.php:51
 * @route '/dashboard/banners/{banner}'
 */
export const update = (args: { banner: number | { id: number } } | [banner: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/dashboard/banners/{banner}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Dashboard\BannerController::update
 * @see app/Http/Controllers/Dashboard/BannerController.php:51
 * @route '/dashboard/banners/{banner}'
 */
update.url = (args: { banner: number | { id: number } } | [banner: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { banner: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { banner: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    banner: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        banner: typeof args.banner === 'object'
                ? args.banner.id
                : args.banner,
                }

    return update.definition.url
            .replace('{banner}', parsedArgs.banner.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\BannerController::update
 * @see app/Http/Controllers/Dashboard/BannerController.php:51
 * @route '/dashboard/banners/{banner}'
 */
update.put = (args: { banner: number | { id: number } } | [banner: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Dashboard\BannerController::update
 * @see app/Http/Controllers/Dashboard/BannerController.php:51
 * @route '/dashboard/banners/{banner}'
 */
    const updateForm = (args: { banner: number | { id: number } } | [banner: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\BannerController::update
 * @see app/Http/Controllers/Dashboard/BannerController.php:51
 * @route '/dashboard/banners/{banner}'
 */
        updateForm.put = (args: { banner: number | { id: number } } | [banner: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Dashboard\BannerController::toggle
 * @see app/Http/Controllers/Dashboard/BannerController.php:104
 * @route '/dashboard/banners/{banner}/toggle'
 */
export const toggle = (args: { banner: number | { id: number } } | [banner: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggle.url(args, options),
    method: 'patch',
})

toggle.definition = {
    methods: ["patch"],
    url: '/dashboard/banners/{banner}/toggle',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Dashboard\BannerController::toggle
 * @see app/Http/Controllers/Dashboard/BannerController.php:104
 * @route '/dashboard/banners/{banner}/toggle'
 */
toggle.url = (args: { banner: number | { id: number } } | [banner: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { banner: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { banner: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    banner: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        banner: typeof args.banner === 'object'
                ? args.banner.id
                : args.banner,
                }

    return toggle.definition.url
            .replace('{banner}', parsedArgs.banner.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\BannerController::toggle
 * @see app/Http/Controllers/Dashboard/BannerController.php:104
 * @route '/dashboard/banners/{banner}/toggle'
 */
toggle.patch = (args: { banner: number | { id: number } } | [banner: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggle.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Dashboard\BannerController::toggle
 * @see app/Http/Controllers/Dashboard/BannerController.php:104
 * @route '/dashboard/banners/{banner}/toggle'
 */
    const toggleForm = (args: { banner: number | { id: number } } | [banner: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: toggle.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\BannerController::toggle
 * @see app/Http/Controllers/Dashboard/BannerController.php:104
 * @route '/dashboard/banners/{banner}/toggle'
 */
        toggleForm.patch = (args: { banner: number | { id: number } } | [banner: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Dashboard\BannerController::destroy
 * @see app/Http/Controllers/Dashboard/BannerController.php:112
 * @route '/dashboard/banners/{banner}'
 */
export const destroy = (args: { banner: number | { id: number } } | [banner: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/dashboard/banners/{banner}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Dashboard\BannerController::destroy
 * @see app/Http/Controllers/Dashboard/BannerController.php:112
 * @route '/dashboard/banners/{banner}'
 */
destroy.url = (args: { banner: number | { id: number } } | [banner: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { banner: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { banner: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    banner: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        banner: typeof args.banner === 'object'
                ? args.banner.id
                : args.banner,
                }

    return destroy.definition.url
            .replace('{banner}', parsedArgs.banner.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\BannerController::destroy
 * @see app/Http/Controllers/Dashboard/BannerController.php:112
 * @route '/dashboard/banners/{banner}'
 */
destroy.delete = (args: { banner: number | { id: number } } | [banner: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Dashboard\BannerController::destroy
 * @see app/Http/Controllers/Dashboard/BannerController.php:112
 * @route '/dashboard/banners/{banner}'
 */
    const destroyForm = (args: { banner: number | { id: number } } | [banner: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\BannerController::destroy
 * @see app/Http/Controllers/Dashboard/BannerController.php:112
 * @route '/dashboard/banners/{banner}'
 */
        destroyForm.delete = (args: { banner: number | { id: number } } | [banner: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const banners = {
    store: Object.assign(store, store),
update: Object.assign(update, update),
toggle: Object.assign(toggle, toggle),
destroy: Object.assign(destroy, destroy),
}

export default banners