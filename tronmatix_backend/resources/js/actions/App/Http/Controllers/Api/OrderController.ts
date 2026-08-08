import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\OrderController::store
 * @see app/Http/Controllers/Api/OrderController.php:73
 * @route '/api/orders'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/orders',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\OrderController::store
 * @see app/Http/Controllers/Api/OrderController.php:73
 * @route '/api/orders'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\OrderController::store
 * @see app/Http/Controllers/Api/OrderController.php:73
 * @route '/api/orders'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\OrderController::store
 * @see app/Http/Controllers/Api/OrderController.php:73
 * @route '/api/orders'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\OrderController::store
 * @see app/Http/Controllers/Api/OrderController.php:73
 * @route '/api/orders'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Api\OrderController::cancel
 * @see app/Http/Controllers/Api/OrderController.php:371
 * @route '/api/orders/{order}/cancel'
 */
export const cancel = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cancel.url(args, options),
    method: 'post',
})

cancel.definition = {
    methods: ["post"],
    url: '/api/orders/{order}/cancel',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\OrderController::cancel
 * @see app/Http/Controllers/Api/OrderController.php:371
 * @route '/api/orders/{order}/cancel'
 */
cancel.url = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { order: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.id
                : args.order,
                }

    return cancel.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\OrderController::cancel
 * @see app/Http/Controllers/Api/OrderController.php:371
 * @route '/api/orders/{order}/cancel'
 */
cancel.post = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cancel.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\OrderController::cancel
 * @see app/Http/Controllers/Api/OrderController.php:371
 * @route '/api/orders/{order}/cancel'
 */
    const cancelForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: cancel.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\OrderController::cancel
 * @see app/Http/Controllers/Api/OrderController.php:371
 * @route '/api/orders/{order}/cancel'
 */
        cancelForm.post = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: cancel.url(args, options),
            method: 'post',
        })
    
    cancel.form = cancelForm
/**
* @see \App\Http\Controllers\Api\OrderController::destroy
 * @see app/Http/Controllers/Api/OrderController.php:428
 * @route '/api/orders/{order}'
 */
export const destroy = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/api/orders/{order}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\OrderController::destroy
 * @see app/Http/Controllers/Api/OrderController.php:428
 * @route '/api/orders/{order}'
 */
destroy.url = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { order: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.id
                : args.order,
                }

    return destroy.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\OrderController::destroy
 * @see app/Http/Controllers/Api/OrderController.php:428
 * @route '/api/orders/{order}'
 */
destroy.delete = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\OrderController::destroy
 * @see app/Http/Controllers/Api/OrderController.php:428
 * @route '/api/orders/{order}'
 */
    const destroyForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\OrderController::destroy
 * @see app/Http/Controllers/Api/OrderController.php:428
 * @route '/api/orders/{order}'
 */
        destroyForm.delete = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Api\OrderController::confirmDelivery
 * @see app/Http/Controllers/Api/OrderController.php:444
 * @route '/api/orders/{order}/confirm-delivery'
 */
export const confirmDelivery = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: confirmDelivery.url(args, options),
    method: 'post',
})

confirmDelivery.definition = {
    methods: ["post"],
    url: '/api/orders/{order}/confirm-delivery',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\OrderController::confirmDelivery
 * @see app/Http/Controllers/Api/OrderController.php:444
 * @route '/api/orders/{order}/confirm-delivery'
 */
confirmDelivery.url = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { order: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.id
                : args.order,
                }

    return confirmDelivery.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\OrderController::confirmDelivery
 * @see app/Http/Controllers/Api/OrderController.php:444
 * @route '/api/orders/{order}/confirm-delivery'
 */
confirmDelivery.post = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: confirmDelivery.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\OrderController::confirmDelivery
 * @see app/Http/Controllers/Api/OrderController.php:444
 * @route '/api/orders/{order}/confirm-delivery'
 */
    const confirmDeliveryForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: confirmDelivery.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\OrderController::confirmDelivery
 * @see app/Http/Controllers/Api/OrderController.php:444
 * @route '/api/orders/{order}/confirm-delivery'
 */
        confirmDeliveryForm.post = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: confirmDelivery.url(args, options),
            method: 'post',
        })
    
    confirmDelivery.form = confirmDeliveryForm
/**
* @see \App\Http\Controllers\Api\OrderController::index
 * @see app/Http/Controllers/Api/OrderController.php:31
 * @route '/api/orders'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/orders',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\OrderController::index
 * @see app/Http/Controllers/Api/OrderController.php:31
 * @route '/api/orders'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\OrderController::index
 * @see app/Http/Controllers/Api/OrderController.php:31
 * @route '/api/orders'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\OrderController::index
 * @see app/Http/Controllers/Api/OrderController.php:31
 * @route '/api/orders'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\OrderController::index
 * @see app/Http/Controllers/Api/OrderController.php:31
 * @route '/api/orders'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\OrderController::index
 * @see app/Http/Controllers/Api/OrderController.php:31
 * @route '/api/orders'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\OrderController::index
 * @see app/Http/Controllers/Api/OrderController.php:31
 * @route '/api/orders'
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
* @see \App\Http\Controllers\Api\OrderController::show
 * @see app/Http/Controllers/Api/OrderController.php:57
 * @route '/api/orders/{order}'
 */
export const show = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/api/orders/{order}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\OrderController::show
 * @see app/Http/Controllers/Api/OrderController.php:57
 * @route '/api/orders/{order}'
 */
show.url = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { order: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.id
                : args.order,
                }

    return show.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\OrderController::show
 * @see app/Http/Controllers/Api/OrderController.php:57
 * @route '/api/orders/{order}'
 */
show.get = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\OrderController::show
 * @see app/Http/Controllers/Api/OrderController.php:57
 * @route '/api/orders/{order}'
 */
show.head = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\OrderController::show
 * @see app/Http/Controllers/Api/OrderController.php:57
 * @route '/api/orders/{order}'
 */
    const showForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\OrderController::show
 * @see app/Http/Controllers/Api/OrderController.php:57
 * @route '/api/orders/{order}'
 */
        showForm.get = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\OrderController::show
 * @see app/Http/Controllers/Api/OrderController.php:57
 * @route '/api/orders/{order}'
 */
        showForm.head = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
/**
* @see \App\Http\Controllers\Api\OrderController::updateStatus
 * @see app/Http/Controllers/Api/OrderController.php:477
 * @route '/api/orders/{order}/status'
 */
export const updateStatus = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateStatus.url(args, options),
    method: 'put',
})

updateStatus.definition = {
    methods: ["put"],
    url: '/api/orders/{order}/status',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Api\OrderController::updateStatus
 * @see app/Http/Controllers/Api/OrderController.php:477
 * @route '/api/orders/{order}/status'
 */
updateStatus.url = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { order: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.id
                : args.order,
                }

    return updateStatus.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\OrderController::updateStatus
 * @see app/Http/Controllers/Api/OrderController.php:477
 * @route '/api/orders/{order}/status'
 */
updateStatus.put = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateStatus.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Api\OrderController::updateStatus
 * @see app/Http/Controllers/Api/OrderController.php:477
 * @route '/api/orders/{order}/status'
 */
    const updateStatusForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateStatus.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\OrderController::updateStatus
 * @see app/Http/Controllers/Api/OrderController.php:477
 * @route '/api/orders/{order}/status'
 */
        updateStatusForm.put = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateStatus.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateStatus.form = updateStatusForm
/**
* @see \App\Http\Controllers\Api\OrderController::verifyPayment
 * @see app/Http/Controllers/Api/OrderController.php:513
 * @route '/api/orders/{order}/verify-payment'
 */
export const verifyPayment = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verifyPayment.url(args, options),
    method: 'post',
})

verifyPayment.definition = {
    methods: ["post"],
    url: '/api/orders/{order}/verify-payment',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\OrderController::verifyPayment
 * @see app/Http/Controllers/Api/OrderController.php:513
 * @route '/api/orders/{order}/verify-payment'
 */
verifyPayment.url = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { order: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.id
                : args.order,
                }

    return verifyPayment.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\OrderController::verifyPayment
 * @see app/Http/Controllers/Api/OrderController.php:513
 * @route '/api/orders/{order}/verify-payment'
 */
verifyPayment.post = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verifyPayment.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\OrderController::verifyPayment
 * @see app/Http/Controllers/Api/OrderController.php:513
 * @route '/api/orders/{order}/verify-payment'
 */
    const verifyPaymentForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: verifyPayment.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\OrderController::verifyPayment
 * @see app/Http/Controllers/Api/OrderController.php:513
 * @route '/api/orders/{order}/verify-payment'
 */
        verifyPaymentForm.post = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: verifyPayment.url(args, options),
            method: 'post',
        })
    
    verifyPayment.form = verifyPaymentForm
/**
* @see \App\Http\Controllers\Api\OrderController::staffConfirmDelivery
 * @see app/Http/Controllers/Api/OrderController.php:539
 * @route '/api/orders/{order}/staff-confirm-delivery'
 */
export const staffConfirmDelivery = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: staffConfirmDelivery.url(args, options),
    method: 'post',
})

staffConfirmDelivery.definition = {
    methods: ["post"],
    url: '/api/orders/{order}/staff-confirm-delivery',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\OrderController::staffConfirmDelivery
 * @see app/Http/Controllers/Api/OrderController.php:539
 * @route '/api/orders/{order}/staff-confirm-delivery'
 */
staffConfirmDelivery.url = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { order: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.id
                : args.order,
                }

    return staffConfirmDelivery.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\OrderController::staffConfirmDelivery
 * @see app/Http/Controllers/Api/OrderController.php:539
 * @route '/api/orders/{order}/staff-confirm-delivery'
 */
staffConfirmDelivery.post = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: staffConfirmDelivery.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\OrderController::staffConfirmDelivery
 * @see app/Http/Controllers/Api/OrderController.php:539
 * @route '/api/orders/{order}/staff-confirm-delivery'
 */
    const staffConfirmDeliveryForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: staffConfirmDelivery.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\OrderController::staffConfirmDelivery
 * @see app/Http/Controllers/Api/OrderController.php:539
 * @route '/api/orders/{order}/staff-confirm-delivery'
 */
        staffConfirmDeliveryForm.post = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: staffConfirmDelivery.url(args, options),
            method: 'post',
        })
    
    staffConfirmDelivery.form = staffConfirmDeliveryForm
const OrderController = { store, cancel, destroy, confirmDelivery, index, show, updateStatus, verifyPayment, staffConfirmDelivery }

export default OrderController