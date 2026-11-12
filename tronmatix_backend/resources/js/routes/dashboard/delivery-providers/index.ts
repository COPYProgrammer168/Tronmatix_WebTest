import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::index
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:15
 * @route '/dashboard/delivery-providers'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/dashboard/delivery-providers',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::index
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:15
 * @route '/dashboard/delivery-providers'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::index
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:15
 * @route '/dashboard/delivery-providers'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::index
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:15
 * @route '/dashboard/delivery-providers'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::index
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:15
 * @route '/dashboard/delivery-providers'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::index
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:15
 * @route '/dashboard/delivery-providers'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::index
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:15
 * @route '/dashboard/delivery-providers'
 */
        indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
/**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::create
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:21
 * @route '/dashboard/delivery-providers/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/dashboard/delivery-providers/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::create
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:21
 * @route '/dashboard/delivery-providers/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::create
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:21
 * @route '/dashboard/delivery-providers/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::create
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:21
 * @route '/dashboard/delivery-providers/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::create
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:21
 * @route '/dashboard/delivery-providers/create'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::create
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:21
 * @route '/dashboard/delivery-providers/create'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::create
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:21
 * @route '/dashboard/delivery-providers/create'
 */
        createForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    create.form = createForm
/**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::store
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:26
 * @route '/dashboard/delivery-providers'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/dashboard/delivery-providers',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::store
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:26
 * @route '/dashboard/delivery-providers'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::store
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:26
 * @route '/dashboard/delivery-providers'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::store
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:26
 * @route '/dashboard/delivery-providers'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::store
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:26
 * @route '/dashboard/delivery-providers'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::edit
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:47
 * @route '/dashboard/delivery-providers/{deliveryProvider}/edit'
 */
export const edit = (args: { deliveryProvider: number | { id: number } } | [deliveryProvider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/dashboard/delivery-providers/{deliveryProvider}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::edit
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:47
 * @route '/dashboard/delivery-providers/{deliveryProvider}/edit'
 */
edit.url = (args: { deliveryProvider: number | { id: number } } | [deliveryProvider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { deliveryProvider: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { deliveryProvider: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    deliveryProvider: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        deliveryProvider: typeof args.deliveryProvider === 'object'
                ? args.deliveryProvider.id
                : args.deliveryProvider,
                }

    return edit.definition.url
            .replace('{deliveryProvider}', parsedArgs.deliveryProvider.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::edit
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:47
 * @route '/dashboard/delivery-providers/{deliveryProvider}/edit'
 */
edit.get = (args: { deliveryProvider: number | { id: number } } | [deliveryProvider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::edit
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:47
 * @route '/dashboard/delivery-providers/{deliveryProvider}/edit'
 */
edit.head = (args: { deliveryProvider: number | { id: number } } | [deliveryProvider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::edit
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:47
 * @route '/dashboard/delivery-providers/{deliveryProvider}/edit'
 */
    const editForm = (args: { deliveryProvider: number | { id: number } } | [deliveryProvider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::edit
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:47
 * @route '/dashboard/delivery-providers/{deliveryProvider}/edit'
 */
        editForm.get = (args: { deliveryProvider: number | { id: number } } | [deliveryProvider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::edit
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:47
 * @route '/dashboard/delivery-providers/{deliveryProvider}/edit'
 */
        editForm.head = (args: { deliveryProvider: number | { id: number } } | [deliveryProvider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    edit.form = editForm
/**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::update
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:53
 * @route '/dashboard/delivery-providers/{deliveryProvider}'
 */
export const update = (args: { deliveryProvider: number | { id: number } } | [deliveryProvider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/dashboard/delivery-providers/{deliveryProvider}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::update
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:53
 * @route '/dashboard/delivery-providers/{deliveryProvider}'
 */
update.url = (args: { deliveryProvider: number | { id: number } } | [deliveryProvider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { deliveryProvider: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { deliveryProvider: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    deliveryProvider: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        deliveryProvider: typeof args.deliveryProvider === 'object'
                ? args.deliveryProvider.id
                : args.deliveryProvider,
                }

    return update.definition.url
            .replace('{deliveryProvider}', parsedArgs.deliveryProvider.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::update
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:53
 * @route '/dashboard/delivery-providers/{deliveryProvider}'
 */
update.put = (args: { deliveryProvider: number | { id: number } } | [deliveryProvider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::update
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:53
 * @route '/dashboard/delivery-providers/{deliveryProvider}'
 */
    const updateForm = (args: { deliveryProvider: number | { id: number } } | [deliveryProvider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::update
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:53
 * @route '/dashboard/delivery-providers/{deliveryProvider}'
 */
        updateForm.put = (args: { deliveryProvider: number | { id: number } } | [deliveryProvider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::toggle
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:104
 * @route '/dashboard/delivery-providers/{deliveryProvider}/toggle'
 */
export const toggle = (args: { deliveryProvider: number | { id: number } } | [deliveryProvider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggle.url(args, options),
    method: 'patch',
})

toggle.definition = {
    methods: ["patch"],
    url: '/dashboard/delivery-providers/{deliveryProvider}/toggle',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::toggle
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:104
 * @route '/dashboard/delivery-providers/{deliveryProvider}/toggle'
 */
toggle.url = (args: { deliveryProvider: number | { id: number } } | [deliveryProvider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { deliveryProvider: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { deliveryProvider: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    deliveryProvider: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        deliveryProvider: typeof args.deliveryProvider === 'object'
                ? args.deliveryProvider.id
                : args.deliveryProvider,
                }

    return toggle.definition.url
            .replace('{deliveryProvider}', parsedArgs.deliveryProvider.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::toggle
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:104
 * @route '/dashboard/delivery-providers/{deliveryProvider}/toggle'
 */
toggle.patch = (args: { deliveryProvider: number | { id: number } } | [deliveryProvider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggle.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::toggle
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:104
 * @route '/dashboard/delivery-providers/{deliveryProvider}/toggle'
 */
    const toggleForm = (args: { deliveryProvider: number | { id: number } } | [deliveryProvider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: toggle.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::toggle
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:104
 * @route '/dashboard/delivery-providers/{deliveryProvider}/toggle'
 */
        toggleForm.patch = (args: { deliveryProvider: number | { id: number } } | [deliveryProvider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::destroy
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:110
 * @route '/dashboard/delivery-providers/{deliveryProvider}'
 */
export const destroy = (args: { deliveryProvider: number | { id: number } } | [deliveryProvider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/dashboard/delivery-providers/{deliveryProvider}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::destroy
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:110
 * @route '/dashboard/delivery-providers/{deliveryProvider}'
 */
destroy.url = (args: { deliveryProvider: number | { id: number } } | [deliveryProvider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { deliveryProvider: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { deliveryProvider: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    deliveryProvider: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        deliveryProvider: typeof args.deliveryProvider === 'object'
                ? args.deliveryProvider.id
                : args.deliveryProvider,
                }

    return destroy.definition.url
            .replace('{deliveryProvider}', parsedArgs.deliveryProvider.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::destroy
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:110
 * @route '/dashboard/delivery-providers/{deliveryProvider}'
 */
destroy.delete = (args: { deliveryProvider: number | { id: number } } | [deliveryProvider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::destroy
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:110
 * @route '/dashboard/delivery-providers/{deliveryProvider}'
 */
    const destroyForm = (args: { deliveryProvider: number | { id: number } } | [deliveryProvider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\DeliveryProviderController::destroy
 * @see app/Http/Controllers/Dashboard/DeliveryProviderController.php:110
 * @route '/dashboard/delivery-providers/{deliveryProvider}'
 */
        destroyForm.delete = (args: { deliveryProvider: number | { id: number } } | [deliveryProvider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const deliveryProviders = {
    index: Object.assign(index, index),
create: Object.assign(create, create),
store: Object.assign(store, store),
edit: Object.assign(edit, edit),
update: Object.assign(update, update),
toggle: Object.assign(toggle, toggle),
destroy: Object.assign(destroy, destroy),
}

export default deliveryProviders