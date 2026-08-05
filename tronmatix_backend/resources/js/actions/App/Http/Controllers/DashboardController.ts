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
 * @see app/Http/Controllers/DashboardController.php:841
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
 * @see app/Http/Controllers/DashboardController.php:841
 * @route '/dashboard/export'
 */
dashboardExport.url = (options?: RouteQueryOptions) => {
    return dashboardExport.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::dashboardExport
 * @see app/Http/Controllers/DashboardController.php:841
 * @route '/dashboard/export'
 */
dashboardExport.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboardExport.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DashboardController::dashboardExport
 * @see app/Http/Controllers/DashboardController.php:841
 * @route '/dashboard/export'
 */
dashboardExport.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboardExport.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DashboardController::dashboardExport
 * @see app/Http/Controllers/DashboardController.php:841
 * @route '/dashboard/export'
 */
    const dashboardExportForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dashboardExport.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DashboardController::dashboardExport
 * @see app/Http/Controllers/DashboardController.php:841
 * @route '/dashboard/export'
 */
        dashboardExportForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboardExport.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DashboardController::dashboardExport
 * @see app/Http/Controllers/DashboardController.php:841
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
 * @see app/Http/Controllers/DashboardController.php:442
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
 * @see app/Http/Controllers/DashboardController.php:442
 * @route '/dashboard/orders'
 */
orders.url = (options?: RouteQueryOptions) => {
    return orders.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::orders
 * @see app/Http/Controllers/DashboardController.php:442
 * @route '/dashboard/orders'
 */
orders.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: orders.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DashboardController::orders
 * @see app/Http/Controllers/DashboardController.php:442
 * @route '/dashboard/orders'
 */
orders.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: orders.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DashboardController::orders
 * @see app/Http/Controllers/DashboardController.php:442
 * @route '/dashboard/orders'
 */
    const ordersForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: orders.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DashboardController::orders
 * @see app/Http/Controllers/DashboardController.php:442
 * @route '/dashboard/orders'
 */
        ordersForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: orders.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DashboardController::orders
 * @see app/Http/Controllers/DashboardController.php:442
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
 * @see app/Http/Controllers/DashboardController.php:492
 * @route '/dashboard/orders/{order_id}'
 */
export const showOrder = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showOrder.url(args, options),
    method: 'get',
})

showOrder.definition = {
    methods: ["get","head"],
    url: '/dashboard/orders/{order_id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::showOrder
 * @see app/Http/Controllers/DashboardController.php:492
 * @route '/dashboard/orders/{order_id}'
 */
showOrder.url = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return showOrder.definition.url
            .replace('{order_id}', parsedArgs.order_id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::showOrder
 * @see app/Http/Controllers/DashboardController.php:492
 * @route '/dashboard/orders/{order_id}'
 */
showOrder.get = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showOrder.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DashboardController::showOrder
 * @see app/Http/Controllers/DashboardController.php:492
 * @route '/dashboard/orders/{order_id}'
 */
showOrder.head = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: showOrder.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DashboardController::showOrder
 * @see app/Http/Controllers/DashboardController.php:492
 * @route '/dashboard/orders/{order_id}'
 */
    const showOrderForm = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: showOrder.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DashboardController::showOrder
 * @see app/Http/Controllers/DashboardController.php:492
 * @route '/dashboard/orders/{order_id}'
 */
        showOrderForm.get = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showOrder.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DashboardController::showOrder
 * @see app/Http/Controllers/DashboardController.php:492
 * @route '/dashboard/orders/{order_id}'
 */
        showOrderForm.head = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
 * @see app/Http/Controllers/DashboardController.php:500
 * @route '/dashboard/orders/{order_id}/status'
 */
export const updateOrderStatus = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateOrderStatus.url(args, options),
    method: 'put',
})

updateOrderStatus.definition = {
    methods: ["put"],
    url: '/dashboard/orders/{order_id}/status',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\DashboardController::updateOrderStatus
 * @see app/Http/Controllers/DashboardController.php:500
 * @route '/dashboard/orders/{order_id}/status'
 */
updateOrderStatus.url = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return updateOrderStatus.definition.url
            .replace('{order_id}', parsedArgs.order_id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::updateOrderStatus
 * @see app/Http/Controllers/DashboardController.php:500
 * @route '/dashboard/orders/{order_id}/status'
 */
updateOrderStatus.put = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateOrderStatus.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\DashboardController::updateOrderStatus
 * @see app/Http/Controllers/DashboardController.php:500
 * @route '/dashboard/orders/{order_id}/status'
 */
    const updateOrderStatusForm = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
 * @see app/Http/Controllers/DashboardController.php:500
 * @route '/dashboard/orders/{order_id}/status'
 */
        updateOrderStatusForm.put = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
 * @see app/Http/Controllers/DashboardController.php:554
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
 * @see app/Http/Controllers/DashboardController.php:554
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
 * @see app/Http/Controllers/DashboardController.php:554
 * @route '/dashboard/orders/{order_id}/confirm-delivery'
 */
confirmDelivery.post = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: confirmDelivery.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\DashboardController::confirmDelivery
 * @see app/Http/Controllers/DashboardController.php:554
 * @route '/dashboard/orders/{order_id}/confirm-delivery'
 */
    const confirmDeliveryForm = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: confirmDelivery.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\DashboardController::confirmDelivery
 * @see app/Http/Controllers/DashboardController.php:554
 * @route '/dashboard/orders/{order_id}/confirm-delivery'
 */
        confirmDeliveryForm.post = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: confirmDelivery.url(args, options),
            method: 'post',
        })
    
    confirmDelivery.form = confirmDeliveryForm
/**
* @see \App\Http\Controllers\DashboardController::verifyOrderPayment
 * @see app/Http/Controllers/DashboardController.php:593
 * @route '/dashboard/orders/{order_id}/verify-payment'
 */
export const verifyOrderPayment = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verifyOrderPayment.url(args, options),
    method: 'post',
})

verifyOrderPayment.definition = {
    methods: ["post"],
    url: '/dashboard/orders/{order_id}/verify-payment',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\DashboardController::verifyOrderPayment
 * @see app/Http/Controllers/DashboardController.php:593
 * @route '/dashboard/orders/{order_id}/verify-payment'
 */
verifyOrderPayment.url = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return verifyOrderPayment.definition.url
            .replace('{order_id}', parsedArgs.order_id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::verifyOrderPayment
 * @see app/Http/Controllers/DashboardController.php:593
 * @route '/dashboard/orders/{order_id}/verify-payment'
 */
verifyOrderPayment.post = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verifyOrderPayment.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\DashboardController::verifyOrderPayment
 * @see app/Http/Controllers/DashboardController.php:593
 * @route '/dashboard/orders/{order_id}/verify-payment'
 */
    const verifyOrderPaymentForm = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: verifyOrderPayment.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\DashboardController::verifyOrderPayment
 * @see app/Http/Controllers/DashboardController.php:593
 * @route '/dashboard/orders/{order_id}/verify-payment'
 */
        verifyOrderPaymentForm.post = (args: { order_id: string | number } | [order_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: verifyOrderPayment.url(args, options),
            method: 'post',
        })
    
    verifyOrderPayment.form = verifyOrderPaymentForm
/**
* @see \App\Http\Controllers\DashboardController::discounts
 * @see app/Http/Controllers/DashboardController.php:733
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
 * @see app/Http/Controllers/DashboardController.php:733
 * @route '/dashboard/discounts'
 */
discounts.url = (options?: RouteQueryOptions) => {
    return discounts.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::discounts
 * @see app/Http/Controllers/DashboardController.php:733
 * @route '/dashboard/discounts'
 */
discounts.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: discounts.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DashboardController::discounts
 * @see app/Http/Controllers/DashboardController.php:733
 * @route '/dashboard/discounts'
 */
discounts.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: discounts.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DashboardController::discounts
 * @see app/Http/Controllers/DashboardController.php:733
 * @route '/dashboard/discounts'
 */
    const discountsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: discounts.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DashboardController::discounts
 * @see app/Http/Controllers/DashboardController.php:733
 * @route '/dashboard/discounts'
 */
        discountsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: discounts.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DashboardController::discounts
 * @see app/Http/Controllers/DashboardController.php:733
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
 * @see app/Http/Controllers/DashboardController.php:61
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
 * @see app/Http/Controllers/DashboardController.php:61
 * @route '/dashboard/report'
 */
report.url = (options?: RouteQueryOptions) => {
    return report.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::report
 * @see app/Http/Controllers/DashboardController.php:61
 * @route '/dashboard/report'
 */
report.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: report.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DashboardController::report
 * @see app/Http/Controllers/DashboardController.php:61
 * @route '/dashboard/report'
 */
report.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: report.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DashboardController::report
 * @see app/Http/Controllers/DashboardController.php:61
 * @route '/dashboard/report'
 */
    const reportForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: report.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DashboardController::report
 * @see app/Http/Controllers/DashboardController.php:61
 * @route '/dashboard/report'
 */
        reportForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: report.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DashboardController::report
 * @see app/Http/Controllers/DashboardController.php:61
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
 * @see app/Http/Controllers/DashboardController.php:974
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
 * @see app/Http/Controllers/DashboardController.php:974
 * @route '/dashboard/stats'
 */
stats.url = (options?: RouteQueryOptions) => {
    return stats.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::stats
 * @see app/Http/Controllers/DashboardController.php:974
 * @route '/dashboard/stats'
 */
stats.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: stats.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DashboardController::stats
 * @see app/Http/Controllers/DashboardController.php:974
 * @route '/dashboard/stats'
 */
stats.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: stats.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DashboardController::stats
 * @see app/Http/Controllers/DashboardController.php:974
 * @route '/dashboard/stats'
 */
    const statsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: stats.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DashboardController::stats
 * @see app/Http/Controllers/DashboardController.php:974
 * @route '/dashboard/stats'
 */
        statsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: stats.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DashboardController::stats
 * @see app/Http/Controllers/DashboardController.php:974
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