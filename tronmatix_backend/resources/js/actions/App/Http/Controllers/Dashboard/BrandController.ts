import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Dashboard\BrandController::index
 * @see app/Http/Controllers/Dashboard/BrandController.php:14
 * @route '/dashboard/brands'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/dashboard/brands',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\BrandController::index
 * @see app/Http/Controllers/Dashboard/BrandController.php:14
 * @route '/dashboard/brands'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\BrandController::index
 * @see app/Http/Controllers/Dashboard/BrandController.php:14
 * @route '/dashboard/brands'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\BrandController::index
 * @see app/Http/Controllers/Dashboard/BrandController.php:14
 * @route '/dashboard/brands'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\BrandController::index
 * @see app/Http/Controllers/Dashboard/BrandController.php:14
 * @route '/dashboard/brands'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\BrandController::index
 * @see app/Http/Controllers/Dashboard/BrandController.php:14
 * @route '/dashboard/brands'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\BrandController::index
 * @see app/Http/Controllers/Dashboard/BrandController.php:14
 * @route '/dashboard/brands'
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
* @see \App\Http\Controllers\Dashboard\BrandController::create
 * @see app/Http/Controllers/Dashboard/BrandController.php:21
 * @route '/dashboard/brands/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/dashboard/brands/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\BrandController::create
 * @see app/Http/Controllers/Dashboard/BrandController.php:21
 * @route '/dashboard/brands/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\BrandController::create
 * @see app/Http/Controllers/Dashboard/BrandController.php:21
 * @route '/dashboard/brands/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\BrandController::create
 * @see app/Http/Controllers/Dashboard/BrandController.php:21
 * @route '/dashboard/brands/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\BrandController::create
 * @see app/Http/Controllers/Dashboard/BrandController.php:21
 * @route '/dashboard/brands/create'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\BrandController::create
 * @see app/Http/Controllers/Dashboard/BrandController.php:21
 * @route '/dashboard/brands/create'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\BrandController::create
 * @see app/Http/Controllers/Dashboard/BrandController.php:21
 * @route '/dashboard/brands/create'
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
* @see \App\Http\Controllers\Dashboard\BrandController::edit
 * @see app/Http/Controllers/Dashboard/BrandController.php:40
 * @route '/dashboard/brands/{brand}'
 */
export const edit = (args: { brand: number | { id: number } } | [brand: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/dashboard/brands/{brand}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\BrandController::edit
 * @see app/Http/Controllers/Dashboard/BrandController.php:40
 * @route '/dashboard/brands/{brand}'
 */
edit.url = (args: { brand: number | { id: number } } | [brand: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return edit.definition.url
            .replace('{brand}', parsedArgs.brand.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\BrandController::edit
 * @see app/Http/Controllers/Dashboard/BrandController.php:40
 * @route '/dashboard/brands/{brand}'
 */
edit.get = (args: { brand: number | { id: number } } | [brand: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\BrandController::edit
 * @see app/Http/Controllers/Dashboard/BrandController.php:40
 * @route '/dashboard/brands/{brand}'
 */
edit.head = (args: { brand: number | { id: number } } | [brand: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\BrandController::edit
 * @see app/Http/Controllers/Dashboard/BrandController.php:40
 * @route '/dashboard/brands/{brand}'
 */
    const editForm = (args: { brand: number | { id: number } } | [brand: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\BrandController::edit
 * @see app/Http/Controllers/Dashboard/BrandController.php:40
 * @route '/dashboard/brands/{brand}'
 */
        editForm.get = (args: { brand: number | { id: number } } | [brand: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\BrandController::edit
 * @see app/Http/Controllers/Dashboard/BrandController.php:40
 * @route '/dashboard/brands/{brand}'
 */
        editForm.head = (args: { brand: number | { id: number } } | [brand: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Dashboard\BrandController::update
 * @see app/Http/Controllers/Dashboard/BrandController.php:45
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
 * @see app/Http/Controllers/Dashboard/BrandController.php:45
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
 * @see app/Http/Controllers/Dashboard/BrandController.php:45
 * @route '/dashboard/brands/{brand}'
 */
update.put = (args: { brand: number | { id: number } } | [brand: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Dashboard\BrandController::update
 * @see app/Http/Controllers/Dashboard/BrandController.php:45
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
 * @see app/Http/Controllers/Dashboard/BrandController.php:45
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
 * @see app/Http/Controllers/Dashboard/BrandController.php:59
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
 * @see app/Http/Controllers/Dashboard/BrandController.php:59
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
 * @see app/Http/Controllers/Dashboard/BrandController.php:59
 * @route '/dashboard/brands/{brand}/toggle'
 */
toggle.patch = (args: { brand: number | { id: number } } | [brand: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggle.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Dashboard\BrandController::toggle
 * @see app/Http/Controllers/Dashboard/BrandController.php:59
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
 * @see app/Http/Controllers/Dashboard/BrandController.php:59
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
 * @see app/Http/Controllers/Dashboard/BrandController.php:67
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
 * @see app/Http/Controllers/Dashboard/BrandController.php:67
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
 * @see app/Http/Controllers/Dashboard/BrandController.php:67
 * @route '/dashboard/brands/{brand}'
 */
destroy.delete = (args: { brand: number | { id: number } } | [brand: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Dashboard\BrandController::destroy
 * @see app/Http/Controllers/Dashboard/BrandController.php:67
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
 * @see app/Http/Controllers/Dashboard/BrandController.php:67
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
const BrandController = { index, create, edit, update, toggle, destroy }

export default BrandController