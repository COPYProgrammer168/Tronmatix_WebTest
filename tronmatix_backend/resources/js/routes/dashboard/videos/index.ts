import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Dashboard\VideoController::store
 * @see app/Http/Controllers/Dashboard/VideoController.php:27
 * @route '/dashboard/videos'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/dashboard/videos',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\VideoController::store
 * @see app/Http/Controllers/Dashboard/VideoController.php:27
 * @route '/dashboard/videos'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\VideoController::store
 * @see app/Http/Controllers/Dashboard/VideoController.php:27
 * @route '/dashboard/videos'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\VideoController::store
 * @see app/Http/Controllers/Dashboard/VideoController.php:27
 * @route '/dashboard/videos'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\VideoController::store
 * @see app/Http/Controllers/Dashboard/VideoController.php:27
 * @route '/dashboard/videos'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Dashboard\VideoController::update
 * @see app/Http/Controllers/Dashboard/VideoController.php:61
 * @route '/dashboard/videos/{video}'
 */
export const update = (args: { video: number | { id: number } } | [video: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/dashboard/videos/{video}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Dashboard\VideoController::update
 * @see app/Http/Controllers/Dashboard/VideoController.php:61
 * @route '/dashboard/videos/{video}'
 */
update.url = (args: { video: number | { id: number } } | [video: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { video: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { video: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    video: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        video: typeof args.video === 'object'
                ? args.video.id
                : args.video,
                }

    return update.definition.url
            .replace('{video}', parsedArgs.video.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\VideoController::update
 * @see app/Http/Controllers/Dashboard/VideoController.php:61
 * @route '/dashboard/videos/{video}'
 */
update.put = (args: { video: number | { id: number } } | [video: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Dashboard\VideoController::update
 * @see app/Http/Controllers/Dashboard/VideoController.php:61
 * @route '/dashboard/videos/{video}'
 */
    const updateForm = (args: { video: number | { id: number } } | [video: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\VideoController::update
 * @see app/Http/Controllers/Dashboard/VideoController.php:61
 * @route '/dashboard/videos/{video}'
 */
        updateForm.put = (args: { video: number | { id: number } } | [video: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Dashboard\VideoController::toggle
 * @see app/Http/Controllers/Dashboard/VideoController.php:110
 * @route '/dashboard/videos/{video}/toggle'
 */
export const toggle = (args: { video: number | { id: number } } | [video: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggle.url(args, options),
    method: 'patch',
})

toggle.definition = {
    methods: ["patch"],
    url: '/dashboard/videos/{video}/toggle',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Dashboard\VideoController::toggle
 * @see app/Http/Controllers/Dashboard/VideoController.php:110
 * @route '/dashboard/videos/{video}/toggle'
 */
toggle.url = (args: { video: number | { id: number } } | [video: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { video: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { video: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    video: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        video: typeof args.video === 'object'
                ? args.video.id
                : args.video,
                }

    return toggle.definition.url
            .replace('{video}', parsedArgs.video.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\VideoController::toggle
 * @see app/Http/Controllers/Dashboard/VideoController.php:110
 * @route '/dashboard/videos/{video}/toggle'
 */
toggle.patch = (args: { video: number | { id: number } } | [video: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggle.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Dashboard\VideoController::toggle
 * @see app/Http/Controllers/Dashboard/VideoController.php:110
 * @route '/dashboard/videos/{video}/toggle'
 */
    const toggleForm = (args: { video: number | { id: number } } | [video: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: toggle.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\VideoController::toggle
 * @see app/Http/Controllers/Dashboard/VideoController.php:110
 * @route '/dashboard/videos/{video}/toggle'
 */
        toggleForm.patch = (args: { video: number | { id: number } } | [video: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Dashboard\VideoController::destroy
 * @see app/Http/Controllers/Dashboard/VideoController.php:118
 * @route '/dashboard/videos/{video}'
 */
export const destroy = (args: { video: number | { id: number } } | [video: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/dashboard/videos/{video}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Dashboard\VideoController::destroy
 * @see app/Http/Controllers/Dashboard/VideoController.php:118
 * @route '/dashboard/videos/{video}'
 */
destroy.url = (args: { video: number | { id: number } } | [video: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { video: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { video: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    video: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        video: typeof args.video === 'object'
                ? args.video.id
                : args.video,
                }

    return destroy.definition.url
            .replace('{video}', parsedArgs.video.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\VideoController::destroy
 * @see app/Http/Controllers/Dashboard/VideoController.php:118
 * @route '/dashboard/videos/{video}'
 */
destroy.delete = (args: { video: number | { id: number } } | [video: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Dashboard\VideoController::destroy
 * @see app/Http/Controllers/Dashboard/VideoController.php:118
 * @route '/dashboard/videos/{video}'
 */
    const destroyForm = (args: { video: number | { id: number } } | [video: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\VideoController::destroy
 * @see app/Http/Controllers/Dashboard/VideoController.php:118
 * @route '/dashboard/videos/{video}'
 */
        destroyForm.delete = (args: { video: number | { id: number } } | [video: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const videos = {
    store: Object.assign(store, store),
update: Object.assign(update, update),
toggle: Object.assign(toggle, toggle),
destroy: Object.assign(destroy, destroy),
}

export default videos