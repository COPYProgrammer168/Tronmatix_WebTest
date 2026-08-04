import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\DashboardController::show
 * @see app/Http/Controllers/DashboardController.php:400
 * @route '/dashboard/orders/{order_id}'
 */
export const show = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/dashboard/orders/{order_id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::show
 * @see app/Http/Controllers/DashboardController.php:400
 * @route '/dashboard/orders/{order_id}'
 */
show.url = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order_id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    order_id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order_id: args.order_id,
                }

    return show.definition.url
            .replace('{order_id}', parsedArgs.order_id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::show
 * @see app/Http/Controllers/DashboardController.php:400
 * @route '/dashboard/orders/{order_id}'
 */
show.get = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DashboardController::show
 * @see app/Http/Controllers/DashboardController.php:400
 * @route '/dashboard/orders/{order_id}'
 */
show.head = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DashboardController::show
 * @see app/Http/Controllers/DashboardController.php:400
 * @route '/dashboard/orders/{order_id}'
 */
    const showForm = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DashboardController::show
 * @see app/Http/Controllers/DashboardController.php:400
 * @route '/dashboard/orders/{order_id}'
 */
        showForm.get = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DashboardController::show
 * @see app/Http/Controllers/DashboardController.php:400
 * @route '/dashboard/orders/{order_id}'
 */
        showForm.head = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\DashboardController::status
 * @see app/Http/Controllers/DashboardController.php:408
 * @route '/dashboard/orders/{order_id}/status'
 */
export const status = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: status.url(args, options),
    method: 'put',
})

status.definition = {
    methods: ["put"],
    url: '/dashboard/orders/{order_id}/status',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\DashboardController::status
 * @see app/Http/Controllers/DashboardController.php:408
 * @route '/dashboard/orders/{order_id}/status'
 */
status.url = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order_id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    order_id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order_id: args.order_id,
                }

    return status.definition.url
            .replace('{order_id}', parsedArgs.order_id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::status
 * @see app/Http/Controllers/DashboardController.php:408
 * @route '/dashboard/orders/{order_id}/status'
 */
status.put = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: status.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\DashboardController::status
 * @see app/Http/Controllers/DashboardController.php:408
 * @route '/dashboard/orders/{order_id}/status'
 */
    const statusForm = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: status.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\DashboardController::status
 * @see app/Http/Controllers/DashboardController.php:408
 * @route '/dashboard/orders/{order_id}/status'
 */
        statusForm.put = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: status.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    status.form = statusForm
/**
* @see \App\Http\Controllers\DashboardController::confirmDelivery
 * @see app/Http/Controllers/DashboardController.php:462
 * @route '/dashboard/orders/{order_id}/confirm-delivery'
 */
export const confirmDelivery = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: confirmDelivery.url(args, options),
    method: 'post',
})

confirmDelivery.definition = {
    methods: ["post"],
    url: '/dashboard/orders/{order_id}/confirm-delivery',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\DashboardController::confirmDelivery
 * @see app/Http/Controllers/DashboardController.php:462
 * @route '/dashboard/orders/{order_id}/confirm-delivery'
 */
confirmDelivery.url = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order_id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    order_id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order_id: args.order_id,
                }

    return confirmDelivery.definition.url
            .replace('{order_id}', parsedArgs.order_id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::confirmDelivery
 * @see app/Http/Controllers/DashboardController.php:462
 * @route '/dashboard/orders/{order_id}/confirm-delivery'
 */
confirmDelivery.post = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: confirmDelivery.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\DashboardController::confirmDelivery
 * @see app/Http/Controllers/DashboardController.php:462
 * @route '/dashboard/orders/{order_id}/confirm-delivery'
 */
    const confirmDeliveryForm = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: confirmDelivery.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\DashboardController::confirmDelivery
 * @see app/Http/Controllers/DashboardController.php:462
 * @route '/dashboard/orders/{order_id}/confirm-delivery'
 */
        confirmDeliveryForm.post = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: confirmDelivery.url(args, options),
            method: 'post',
        })
    
    confirmDelivery.form = confirmDeliveryForm
/**
* @see \App\Http\Controllers\DashboardController::verifyPayment
 * @see app/Http/Controllers/DashboardController.php:501
 * @route '/dashboard/orders/{order_id}/verify-payment'
 */
export const verifyPayment = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verifyPayment.url(args, options),
    method: 'post',
})

verifyPayment.definition = {
    methods: ["post"],
    url: '/dashboard/orders/{order_id}/verify-payment',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\DashboardController::verifyPayment
 * @see app/Http/Controllers/DashboardController.php:501
 * @route '/dashboard/orders/{order_id}/verify-payment'
 */
verifyPayment.url = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order_id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    order_id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order_id: args.order_id,
                }

    return verifyPayment.definition.url
            .replace('{order_id}', parsedArgs.order_id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::verifyPayment
 * @see app/Http/Controllers/DashboardController.php:501
 * @route '/dashboard/orders/{order_id}/verify-payment'
 */
verifyPayment.post = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verifyPayment.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\DashboardController::verifyPayment
 * @see app/Http/Controllers/DashboardController.php:501
 * @route '/dashboard/orders/{order_id}/verify-payment'
 */
    const verifyPaymentForm = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: verifyPayment.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\DashboardController::verifyPayment
 * @see app/Http/Controllers/DashboardController.php:501
 * @route '/dashboard/orders/{order_id}/verify-payment'
 */
        verifyPaymentForm.post = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: verifyPayment.url(args, options),
            method: 'post',
        })
    
    verifyPayment.form = verifyPaymentForm
const orders = {
    show: Object.assign(show, show),
status: Object.assign(status, status),
confirmDelivery: Object.assign(confirmDelivery, confirmDelivery),
verifyPayment: Object.assign(verifyPayment, verifyPayment),
}

export default orders