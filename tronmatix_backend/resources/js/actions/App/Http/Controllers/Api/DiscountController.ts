import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\DiscountController::storefront
 * @see app/Http/Controllers/Api/DiscountController.php:110
 * @route '/api/discounts/public'
 */
export const storefront = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: storefront.url(options),
    method: 'get',
})

storefront.definition = {
    methods: ["get","head"],
    url: '/api/discounts/public',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\DiscountController::storefront
 * @see app/Http/Controllers/Api/DiscountController.php:110
 * @route '/api/discounts/public'
 */
storefront.url = (options?: RouteQueryOptions) => {
    return storefront.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\DiscountController::storefront
 * @see app/Http/Controllers/Api/DiscountController.php:110
 * @route '/api/discounts/public'
 */
storefront.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: storefront.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\DiscountController::storefront
 * @see app/Http/Controllers/Api/DiscountController.php:110
 * @route '/api/discounts/public'
 */
storefront.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: storefront.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\DiscountController::storefront
 * @see app/Http/Controllers/Api/DiscountController.php:110
 * @route '/api/discounts/public'
 */
    const storefrontForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: storefront.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\DiscountController::storefront
 * @see app/Http/Controllers/Api/DiscountController.php:110
 * @route '/api/discounts/public'
 */
        storefrontForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: storefront.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\DiscountController::storefront
 * @see app/Http/Controllers/Api/DiscountController.php:110
 * @route '/api/discounts/public'
 */
        storefrontForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: storefront.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    storefront.form = storefrontForm
/**
* @see \App\Http\Controllers\Api\DiscountController::apply
 * @see app/Http/Controllers/Api/DiscountController.php:133
 * @route '/api/apply-discount'
 */
export const apply = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: apply.url(options),
    method: 'post',
})

apply.definition = {
    methods: ["post"],
    url: '/api/apply-discount',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\DiscountController::apply
 * @see app/Http/Controllers/Api/DiscountController.php:133
 * @route '/api/apply-discount'
 */
apply.url = (options?: RouteQueryOptions) => {
    return apply.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\DiscountController::apply
 * @see app/Http/Controllers/Api/DiscountController.php:133
 * @route '/api/apply-discount'
 */
apply.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: apply.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\DiscountController::apply
 * @see app/Http/Controllers/Api/DiscountController.php:133
 * @route '/api/apply-discount'
 */
    const applyForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: apply.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\DiscountController::apply
 * @see app/Http/Controllers/Api/DiscountController.php:133
 * @route '/api/apply-discount'
 */
        applyForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: apply.url(options),
            method: 'post',
        })
    
    apply.form = applyForm
/**
* @see \App\Http\Controllers\Api\DiscountController::index
 * @see app/Http/Controllers/Api/DiscountController.php:13
 * @route '/api/discounts'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/discounts',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\DiscountController::index
 * @see app/Http/Controllers/Api/DiscountController.php:13
 * @route '/api/discounts'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\DiscountController::index
 * @see app/Http/Controllers/Api/DiscountController.php:13
 * @route '/api/discounts'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\DiscountController::index
 * @see app/Http/Controllers/Api/DiscountController.php:13
 * @route '/api/discounts'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\DiscountController::index
 * @see app/Http/Controllers/Api/DiscountController.php:13
 * @route '/api/discounts'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\DiscountController::index
 * @see app/Http/Controllers/Api/DiscountController.php:13
 * @route '/api/discounts'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\DiscountController::index
 * @see app/Http/Controllers/Api/DiscountController.php:13
 * @route '/api/discounts'
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
* @see \App\Http\Controllers\Api\DiscountController::store
 * @see app/Http/Controllers/Api/DiscountController.php:20
 * @route '/api/discounts'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/discounts',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\DiscountController::store
 * @see app/Http/Controllers/Api/DiscountController.php:20
 * @route '/api/discounts'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\DiscountController::store
 * @see app/Http/Controllers/Api/DiscountController.php:20
 * @route '/api/discounts'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\DiscountController::store
 * @see app/Http/Controllers/Api/DiscountController.php:20
 * @route '/api/discounts'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\DiscountController::store
 * @see app/Http/Controllers/Api/DiscountController.php:20
 * @route '/api/discounts'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Api\DiscountController::update
 * @see app/Http/Controllers/Api/DiscountController.php:49
 * @route '/api/discounts/{discount}'
 */
export const update = (args: { discount: number | { id: number } } | [discount: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/api/discounts/{discount}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Api\DiscountController::update
 * @see app/Http/Controllers/Api/DiscountController.php:49
 * @route '/api/discounts/{discount}'
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
* @see \App\Http\Controllers\Api\DiscountController::update
 * @see app/Http/Controllers/Api/DiscountController.php:49
 * @route '/api/discounts/{discount}'
 */
update.put = (args: { discount: number | { id: number } } | [discount: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Api\DiscountController::update
 * @see app/Http/Controllers/Api/DiscountController.php:49
 * @route '/api/discounts/{discount}'
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
* @see \App\Http\Controllers\Api\DiscountController::update
 * @see app/Http/Controllers/Api/DiscountController.php:49
 * @route '/api/discounts/{discount}'
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
* @see \App\Http\Controllers\Api\DiscountController::destroy
 * @see app/Http/Controllers/Api/DiscountController.php:78
 * @route '/api/discounts/{discount}'
 */
export const destroy = (args: { discount: number | { id: number } } | [discount: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/api/discounts/{discount}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\DiscountController::destroy
 * @see app/Http/Controllers/Api/DiscountController.php:78
 * @route '/api/discounts/{discount}'
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
* @see \App\Http\Controllers\Api\DiscountController::destroy
 * @see app/Http/Controllers/Api/DiscountController.php:78
 * @route '/api/discounts/{discount}'
 */
destroy.delete = (args: { discount: number | { id: number } } | [discount: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\DiscountController::destroy
 * @see app/Http/Controllers/Api/DiscountController.php:78
 * @route '/api/discounts/{discount}'
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
* @see \App\Http\Controllers\Api\DiscountController::destroy
 * @see app/Http/Controllers/Api/DiscountController.php:78
 * @route '/api/discounts/{discount}'
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
* @see \App\Http\Controllers\Api\DiscountController::saveBadge
 * @see app/Http/Controllers/Api/DiscountController.php:88
 * @route '/api/discounts/{discount}/badge'
 */
export const saveBadge = (args: { discount: number | { id: number } } | [discount: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: saveBadge.url(args, options),
    method: 'patch',
})

saveBadge.definition = {
    methods: ["patch"],
    url: '/api/discounts/{discount}/badge',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Api\DiscountController::saveBadge
 * @see app/Http/Controllers/Api/DiscountController.php:88
 * @route '/api/discounts/{discount}/badge'
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
* @see \App\Http\Controllers\Api\DiscountController::saveBadge
 * @see app/Http/Controllers/Api/DiscountController.php:88
 * @route '/api/discounts/{discount}/badge'
 */
saveBadge.patch = (args: { discount: number | { id: number } } | [discount: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: saveBadge.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Api\DiscountController::saveBadge
 * @see app/Http/Controllers/Api/DiscountController.php:88
 * @route '/api/discounts/{discount}/badge'
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
* @see \App\Http\Controllers\Api\DiscountController::saveBadge
 * @see app/Http/Controllers/Api/DiscountController.php:88
 * @route '/api/discounts/{discount}/badge'
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
const DiscountController = { storefront, apply, index, store, update, destroy, saveBadge }

export default DiscountController