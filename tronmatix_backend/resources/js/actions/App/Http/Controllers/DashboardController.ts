import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\DashboardController::index
 * @see app/Http/Controllers/DashboardController.php:30
 * @route '/dashboard'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::index
 * @see app/Http/Controllers/DashboardController.php:30
 * @route '/dashboard'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::index
 * @see app/Http/Controllers/DashboardController.php:30
 * @route '/dashboard'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DashboardController::index
 * @see app/Http/Controllers/DashboardController.php:30
 * @route '/dashboard'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DashboardController::index
 * @see app/Http/Controllers/DashboardController.php:30
 * @route '/dashboard'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DashboardController::index
 * @see app/Http/Controllers/DashboardController.php:30
 * @route '/dashboard'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DashboardController::index
 * @see app/Http/Controllers/DashboardController.php:30
 * @route '/dashboard'
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
* @see \App\Http\Controllers\DashboardController::dashboardExport
 * @see app/Http/Controllers/DashboardController.php:677
 * @route '/dashboard/export'
 */
export const dashboardExport = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboardExport.url(options),
    method: 'get',
})

dashboardExport.definition = {
    methods: ["get","head"],
    url: '/dashboard/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::dashboardExport
 * @see app/Http/Controllers/DashboardController.php:677
 * @route '/dashboard/export'
 */
dashboardExport.url = (options?: RouteQueryOptions) => {
    return dashboardExport.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::dashboardExport
 * @see app/Http/Controllers/DashboardController.php:677
 * @route '/dashboard/export'
 */
dashboardExport.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboardExport.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DashboardController::dashboardExport
 * @see app/Http/Controllers/DashboardController.php:677
 * @route '/dashboard/export'
 */
dashboardExport.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboardExport.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DashboardController::dashboardExport
 * @see app/Http/Controllers/DashboardController.php:677
 * @route '/dashboard/export'
 */
    const dashboardExportForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dashboardExport.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DashboardController::dashboardExport
 * @see app/Http/Controllers/DashboardController.php:677
 * @route '/dashboard/export'
 */
        dashboardExportForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboardExport.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DashboardController::dashboardExport
 * @see app/Http/Controllers/DashboardController.php:677
 * @route '/dashboard/export'
 */
        dashboardExportForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboardExport.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    dashboardExport.form = dashboardExportForm
/**
* @see \App\Http\Controllers\DashboardController::orders
 * @see app/Http/Controllers/DashboardController.php:346
 * @route '/dashboard/orders'
 */
export const orders = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: orders.url(options),
    method: 'get',
})

orders.definition = {
    methods: ["get","head"],
    url: '/dashboard/orders',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::orders
 * @see app/Http/Controllers/DashboardController.php:346
 * @route '/dashboard/orders'
 */
orders.url = (options?: RouteQueryOptions) => {
    return orders.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::orders
 * @see app/Http/Controllers/DashboardController.php:346
 * @route '/dashboard/orders'
 */
orders.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: orders.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DashboardController::orders
 * @see app/Http/Controllers/DashboardController.php:346
 * @route '/dashboard/orders'
 */
orders.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: orders.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DashboardController::orders
 * @see app/Http/Controllers/DashboardController.php:346
 * @route '/dashboard/orders'
 */
    const ordersForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: orders.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DashboardController::orders
 * @see app/Http/Controllers/DashboardController.php:346
 * @route '/dashboard/orders'
 */
        ordersForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: orders.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DashboardController::orders
 * @see app/Http/Controllers/DashboardController.php:346
 * @route '/dashboard/orders'
 */
        ordersForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: orders.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    orders.form = ordersForm
/**
* @see \App\Http\Controllers\DashboardController::showOrder
 * @see app/Http/Controllers/DashboardController.php:373
 * @route '/dashboard/orders/{order}'
 */
export const showOrder = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showOrder.url(args, options),
    method: 'get',
})

showOrder.definition = {
    methods: ["get","head"],
    url: '/dashboard/orders/{order}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::showOrder
 * @see app/Http/Controllers/DashboardController.php:373
 * @route '/dashboard/orders/{order}'
 */
showOrder.url = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return showOrder.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::showOrder
 * @see app/Http/Controllers/DashboardController.php:373
 * @route '/dashboard/orders/{order}'
 */
showOrder.get = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showOrder.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DashboardController::showOrder
 * @see app/Http/Controllers/DashboardController.php:373
 * @route '/dashboard/orders/{order}'
 */
showOrder.head = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: showOrder.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DashboardController::showOrder
 * @see app/Http/Controllers/DashboardController.php:373
 * @route '/dashboard/orders/{order}'
 */
    const showOrderForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: showOrder.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DashboardController::showOrder
 * @see app/Http/Controllers/DashboardController.php:373
 * @route '/dashboard/orders/{order}'
 */
        showOrderForm.get = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showOrder.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DashboardController::showOrder
 * @see app/Http/Controllers/DashboardController.php:373
 * @route '/dashboard/orders/{order}'
 */
        showOrderForm.head = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showOrder.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    showOrder.form = showOrderForm
/**
* @see \App\Http\Controllers\DashboardController::updateOrderStatus
 * @see app/Http/Controllers/DashboardController.php:380
 * @route '/dashboard/orders/{order}/status'
 */
export const updateOrderStatus = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateOrderStatus.url(args, options),
    method: 'put',
})

updateOrderStatus.definition = {
    methods: ["put"],
    url: '/dashboard/orders/{order}/status',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\DashboardController::updateOrderStatus
 * @see app/Http/Controllers/DashboardController.php:380
 * @route '/dashboard/orders/{order}/status'
 */
updateOrderStatus.url = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return updateOrderStatus.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::updateOrderStatus
 * @see app/Http/Controllers/DashboardController.php:380
 * @route '/dashboard/orders/{order}/status'
 */
updateOrderStatus.put = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateOrderStatus.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\DashboardController::updateOrderStatus
 * @see app/Http/Controllers/DashboardController.php:380
 * @route '/dashboard/orders/{order}/status'
 */
    const updateOrderStatusForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateOrderStatus.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\DashboardController::updateOrderStatus
 * @see app/Http/Controllers/DashboardController.php:380
 * @route '/dashboard/orders/{order}/status'
 */
        updateOrderStatusForm.put = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateOrderStatus.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateOrderStatus.form = updateOrderStatusForm
/**
* @see \App\Http\Controllers\DashboardController::confirmDelivery
 * @see app/Http/Controllers/DashboardController.php:426
 * @route '/dashboard/orders/{order}/confirm-delivery'
 */
export const confirmDelivery = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: confirmDelivery.url(args, options),
    method: 'post',
})

confirmDelivery.definition = {
    methods: ["post"],
    url: '/dashboard/orders/{order}/confirm-delivery',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\DashboardController::confirmDelivery
 * @see app/Http/Controllers/DashboardController.php:426
 * @route '/dashboard/orders/{order}/confirm-delivery'
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
* @see \App\Http\Controllers\DashboardController::confirmDelivery
 * @see app/Http/Controllers/DashboardController.php:426
 * @route '/dashboard/orders/{order}/confirm-delivery'
 */
confirmDelivery.post = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: confirmDelivery.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\DashboardController::confirmDelivery
 * @see app/Http/Controllers/DashboardController.php:426
 * @route '/dashboard/orders/{order}/confirm-delivery'
 */
    const confirmDeliveryForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: confirmDelivery.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\DashboardController::confirmDelivery
 * @see app/Http/Controllers/DashboardController.php:426
 * @route '/dashboard/orders/{order}/confirm-delivery'
 */
        confirmDeliveryForm.post = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: confirmDelivery.url(args, options),
            method: 'post',
        })
    
    confirmDelivery.form = confirmDeliveryForm
/**
* @see \App\Http\Controllers\DashboardController::verifyOrderPayment
 * @see app/Http/Controllers/DashboardController.php:456
 * @route '/dashboard/orders/{order}/verify-payment'
 */
export const verifyOrderPayment = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verifyOrderPayment.url(args, options),
    method: 'post',
})

verifyOrderPayment.definition = {
    methods: ["post"],
    url: '/dashboard/orders/{order}/verify-payment',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\DashboardController::verifyOrderPayment
 * @see app/Http/Controllers/DashboardController.php:456
 * @route '/dashboard/orders/{order}/verify-payment'
 */
verifyOrderPayment.url = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return verifyOrderPayment.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::verifyOrderPayment
 * @see app/Http/Controllers/DashboardController.php:456
 * @route '/dashboard/orders/{order}/verify-payment'
 */
verifyOrderPayment.post = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verifyOrderPayment.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\DashboardController::verifyOrderPayment
 * @see app/Http/Controllers/DashboardController.php:456
 * @route '/dashboard/orders/{order}/verify-payment'
 */
    const verifyOrderPaymentForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: verifyOrderPayment.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\DashboardController::verifyOrderPayment
 * @see app/Http/Controllers/DashboardController.php:456
 * @route '/dashboard/orders/{order}/verify-payment'
 */
        verifyOrderPaymentForm.post = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: verifyOrderPayment.url(args, options),
            method: 'post',
        })
    
    verifyOrderPayment.form = verifyOrderPaymentForm
/**
* @see \App\Http\Controllers\DashboardController::discounts
 * @see app/Http/Controllers/DashboardController.php:588
 * @route '/dashboard/discounts'
 */
export const discounts = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: discounts.url(options),
    method: 'get',
})

discounts.definition = {
    methods: ["get","head"],
    url: '/dashboard/discounts',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::discounts
 * @see app/Http/Controllers/DashboardController.php:588
 * @route '/dashboard/discounts'
 */
discounts.url = (options?: RouteQueryOptions) => {
    return discounts.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::discounts
 * @see app/Http/Controllers/DashboardController.php:588
 * @route '/dashboard/discounts'
 */
discounts.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: discounts.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DashboardController::discounts
 * @see app/Http/Controllers/DashboardController.php:588
 * @route '/dashboard/discounts'
 */
discounts.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: discounts.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DashboardController::discounts
 * @see app/Http/Controllers/DashboardController.php:588
 * @route '/dashboard/discounts'
 */
    const discountsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: discounts.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DashboardController::discounts
 * @see app/Http/Controllers/DashboardController.php:588
 * @route '/dashboard/discounts'
 */
        discountsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: discounts.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DashboardController::discounts
 * @see app/Http/Controllers/DashboardController.php:588
 * @route '/dashboard/discounts'
 */
        discountsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: discounts.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    discounts.form = discountsForm
/**
* @see \App\Http\Controllers\DashboardController::report
 * @see app/Http/Controllers/DashboardController.php:57
 * @route '/dashboard/report'
 */
export const report = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: report.url(options),
    method: 'get',
})

report.definition = {
    methods: ["get","head"],
    url: '/dashboard/report',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::report
 * @see app/Http/Controllers/DashboardController.php:57
 * @route '/dashboard/report'
 */
report.url = (options?: RouteQueryOptions) => {
    return report.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::report
 * @see app/Http/Controllers/DashboardController.php:57
 * @route '/dashboard/report'
 */
report.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: report.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DashboardController::report
 * @see app/Http/Controllers/DashboardController.php:57
 * @route '/dashboard/report'
 */
report.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: report.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DashboardController::report
 * @see app/Http/Controllers/DashboardController.php:57
 * @route '/dashboard/report'
 */
    const reportForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: report.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DashboardController::report
 * @see app/Http/Controllers/DashboardController.php:57
 * @route '/dashboard/report'
 */
        reportForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: report.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DashboardController::report
 * @see app/Http/Controllers/DashboardController.php:57
 * @route '/dashboard/report'
 */
        reportForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: report.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    report.form = reportForm
/**
* @see \App\Http\Controllers\DashboardController::stats
 * @see app/Http/Controllers/DashboardController.php:809
 * @route '/dashboard/stats'
 */
export const stats = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: stats.url(options),
    method: 'get',
})

stats.definition = {
    methods: ["get","head"],
    url: '/dashboard/stats',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::stats
 * @see app/Http/Controllers/DashboardController.php:809
 * @route '/dashboard/stats'
 */
stats.url = (options?: RouteQueryOptions) => {
    return stats.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::stats
 * @see app/Http/Controllers/DashboardController.php:809
 * @route '/dashboard/stats'
 */
stats.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: stats.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DashboardController::stats
 * @see app/Http/Controllers/DashboardController.php:809
 * @route '/dashboard/stats'
 */
stats.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: stats.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DashboardController::stats
 * @see app/Http/Controllers/DashboardController.php:809
 * @route '/dashboard/stats'
 */
    const statsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: stats.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DashboardController::stats
 * @see app/Http/Controllers/DashboardController.php:809
 * @route '/dashboard/stats'
 */
        statsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: stats.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DashboardController::stats
 * @see app/Http/Controllers/DashboardController.php:809
 * @route '/dashboard/stats'
 */
        statsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: stats.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    stats.form = statsForm
const DashboardController = { index, dashboardExport, orders, showOrder, updateOrderStatus, confirmDelivery, verifyOrderPayment, discounts, report, stats }

export default DashboardController