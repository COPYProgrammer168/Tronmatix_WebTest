import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Dashboard\StockController::index
 * @see app/Http/Controllers/Dashboard/StockController.php:30
 * @route '/dashboard/stock'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/dashboard/stock',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\StockController::index
 * @see app/Http/Controllers/Dashboard/StockController.php:30
 * @route '/dashboard/stock'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\StockController::index
 * @see app/Http/Controllers/Dashboard/StockController.php:30
 * @route '/dashboard/stock'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\StockController::index
 * @see app/Http/Controllers/Dashboard/StockController.php:30
 * @route '/dashboard/stock'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\StockController::index
 * @see app/Http/Controllers/Dashboard/StockController.php:30
 * @route '/dashboard/stock'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\StockController::index
 * @see app/Http/Controllers/Dashboard/StockController.php:30
 * @route '/dashboard/stock'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\StockController::index
 * @see app/Http/Controllers/Dashboard/StockController.php:30
 * @route '/dashboard/stock'
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
* @see \App\Http\Controllers\Dashboard\StockController::receive
 * @see app/Http/Controllers/Dashboard/StockController.php:54
 * @route '/dashboard/stock/receive'
 */
export const receive = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: receive.url(options),
    method: 'post',
})

receive.definition = {
    methods: ["post"],
    url: '/dashboard/stock/receive',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\StockController::receive
 * @see app/Http/Controllers/Dashboard/StockController.php:54
 * @route '/dashboard/stock/receive'
 */
receive.url = (options?: RouteQueryOptions) => {
    return receive.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\StockController::receive
 * @see app/Http/Controllers/Dashboard/StockController.php:54
 * @route '/dashboard/stock/receive'
 */
receive.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: receive.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\StockController::receive
 * @see app/Http/Controllers/Dashboard/StockController.php:54
 * @route '/dashboard/stock/receive'
 */
    const receiveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: receive.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\StockController::receive
 * @see app/Http/Controllers/Dashboard/StockController.php:54
 * @route '/dashboard/stock/receive'
 */
        receiveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: receive.url(options),
            method: 'post',
        })
    
    receive.form = receiveForm
/**
* @see \App\Http\Controllers\Dashboard\StockController::adjust
 * @see app/Http/Controllers/Dashboard/StockController.php:73
 * @route '/dashboard/stock/adjust'
 */
export const adjust = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: adjust.url(options),
    method: 'post',
})

adjust.definition = {
    methods: ["post"],
    url: '/dashboard/stock/adjust',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\StockController::adjust
 * @see app/Http/Controllers/Dashboard/StockController.php:73
 * @route '/dashboard/stock/adjust'
 */
adjust.url = (options?: RouteQueryOptions) => {
    return adjust.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\StockController::adjust
 * @see app/Http/Controllers/Dashboard/StockController.php:73
 * @route '/dashboard/stock/adjust'
 */
adjust.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: adjust.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\StockController::adjust
 * @see app/Http/Controllers/Dashboard/StockController.php:73
 * @route '/dashboard/stock/adjust'
 */
    const adjustForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: adjust.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\StockController::adjust
 * @see app/Http/Controllers/Dashboard/StockController.php:73
 * @route '/dashboard/stock/adjust'
 */
        adjustForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: adjust.url(options),
            method: 'post',
        })
    
    adjust.form = adjustForm
/**
* @see \App\Http\Controllers\Dashboard\StockController::damaged
 * @see app/Http/Controllers/Dashboard/StockController.php:124
 * @route '/dashboard/stock/damaged'
 */
export const damaged = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: damaged.url(options),
    method: 'post',
})

damaged.definition = {
    methods: ["post"],
    url: '/dashboard/stock/damaged',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\StockController::damaged
 * @see app/Http/Controllers/Dashboard/StockController.php:124
 * @route '/dashboard/stock/damaged'
 */
damaged.url = (options?: RouteQueryOptions) => {
    return damaged.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\StockController::damaged
 * @see app/Http/Controllers/Dashboard/StockController.php:124
 * @route '/dashboard/stock/damaged'
 */
damaged.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: damaged.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\StockController::damaged
 * @see app/Http/Controllers/Dashboard/StockController.php:124
 * @route '/dashboard/stock/damaged'
 */
    const damagedForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: damaged.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\StockController::damaged
 * @see app/Http/Controllers/Dashboard/StockController.php:124
 * @route '/dashboard/stock/damaged'
 */
        damagedForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: damaged.url(options),
            method: 'post',
        })
    
    damaged.form = damagedForm
/**
* @see \App\Http\Controllers\Dashboard\StockController::reset
 * @see app/Http/Controllers/Dashboard/StockController.php:97
 * @route '/dashboard/stock/reset'
 */
export const reset = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reset.url(options),
    method: 'post',
})

reset.definition = {
    methods: ["post"],
    url: '/dashboard/stock/reset',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\StockController::reset
 * @see app/Http/Controllers/Dashboard/StockController.php:97
 * @route '/dashboard/stock/reset'
 */
reset.url = (options?: RouteQueryOptions) => {
    return reset.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\StockController::reset
 * @see app/Http/Controllers/Dashboard/StockController.php:97
 * @route '/dashboard/stock/reset'
 */
reset.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reset.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\StockController::reset
 * @see app/Http/Controllers/Dashboard/StockController.php:97
 * @route '/dashboard/stock/reset'
 */
    const resetForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: reset.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\StockController::reset
 * @see app/Http/Controllers/Dashboard/StockController.php:97
 * @route '/dashboard/stock/reset'
 */
        resetForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: reset.url(options),
            method: 'post',
        })
    
    reset.form = resetForm
/**
* @see \App\Http\Controllers\Dashboard\StockController::history
 * @see app/Http/Controllers/Dashboard/StockController.php:146
 * @route '/dashboard/stock/{product}/history'
 */
export const history = (args: { product: number | { id: number } } | [product: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(args, options),
    method: 'get',
})

history.definition = {
    methods: ["get","head"],
    url: '/dashboard/stock/{product}/history',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\StockController::history
 * @see app/Http/Controllers/Dashboard/StockController.php:146
 * @route '/dashboard/stock/{product}/history'
 */
history.url = (args: { product: number | { id: number } } | [product: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { product: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { product: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    product: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        product: typeof args.product === 'object'
                ? args.product.id
                : args.product,
                }

    return history.definition.url
            .replace('{product}', parsedArgs.product.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\StockController::history
 * @see app/Http/Controllers/Dashboard/StockController.php:146
 * @route '/dashboard/stock/{product}/history'
 */
history.get = (args: { product: number | { id: number } } | [product: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\StockController::history
 * @see app/Http/Controllers/Dashboard/StockController.php:146
 * @route '/dashboard/stock/{product}/history'
 */
history.head = (args: { product: number | { id: number } } | [product: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: history.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\StockController::history
 * @see app/Http/Controllers/Dashboard/StockController.php:146
 * @route '/dashboard/stock/{product}/history'
 */
    const historyForm = (args: { product: number | { id: number } } | [product: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: history.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\StockController::history
 * @see app/Http/Controllers/Dashboard/StockController.php:146
 * @route '/dashboard/stock/{product}/history'
 */
        historyForm.get = (args: { product: number | { id: number } } | [product: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: history.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\StockController::history
 * @see app/Http/Controllers/Dashboard/StockController.php:146
 * @route '/dashboard/stock/{product}/history'
 */
        historyForm.head = (args: { product: number | { id: number } } | [product: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: history.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    history.form = historyForm
/**
* @see \App\Http\Controllers\Dashboard\StockController::report
 * @see app/Http/Controllers/Dashboard/StockController.php:161
 * @route '/dashboard/stock/report'
 */
export const report = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: report.url(options),
    method: 'get',
})

report.definition = {
    methods: ["get","head"],
    url: '/dashboard/stock/report',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\StockController::report
 * @see app/Http/Controllers/Dashboard/StockController.php:161
 * @route '/dashboard/stock/report'
 */
report.url = (options?: RouteQueryOptions) => {
    return report.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\StockController::report
 * @see app/Http/Controllers/Dashboard/StockController.php:161
 * @route '/dashboard/stock/report'
 */
report.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: report.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\StockController::report
 * @see app/Http/Controllers/Dashboard/StockController.php:161
 * @route '/dashboard/stock/report'
 */
report.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: report.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\StockController::report
 * @see app/Http/Controllers/Dashboard/StockController.php:161
 * @route '/dashboard/stock/report'
 */
    const reportForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: report.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\StockController::report
 * @see app/Http/Controllers/Dashboard/StockController.php:161
 * @route '/dashboard/stock/report'
 */
        reportForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: report.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\StockController::report
 * @see app/Http/Controllers/Dashboard/StockController.php:161
 * @route '/dashboard/stock/report'
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
* @see \App\Http\Controllers\Dashboard\StockController::exportMethod
 * @see app/Http/Controllers/Dashboard/StockController.php:195
 * @route '/dashboard/stock/export'
 */
export const exportMethod = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})

exportMethod.definition = {
    methods: ["get","head"],
    url: '/dashboard/stock/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\StockController::exportMethod
 * @see app/Http/Controllers/Dashboard/StockController.php:195
 * @route '/dashboard/stock/export'
 */
exportMethod.url = (options?: RouteQueryOptions) => {
    return exportMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\StockController::exportMethod
 * @see app/Http/Controllers/Dashboard/StockController.php:195
 * @route '/dashboard/stock/export'
 */
exportMethod.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\StockController::exportMethod
 * @see app/Http/Controllers/Dashboard/StockController.php:195
 * @route '/dashboard/stock/export'
 */
exportMethod.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportMethod.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\StockController::exportMethod
 * @see app/Http/Controllers/Dashboard/StockController.php:195
 * @route '/dashboard/stock/export'
 */
    const exportMethodForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: exportMethod.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\StockController::exportMethod
 * @see app/Http/Controllers/Dashboard/StockController.php:195
 * @route '/dashboard/stock/export'
 */
        exportMethodForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportMethod.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\StockController::exportMethod
 * @see app/Http/Controllers/Dashboard/StockController.php:195
 * @route '/dashboard/stock/export'
 */
        exportMethodForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportMethod.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    exportMethod.form = exportMethodForm
const stock = {
    index: Object.assign(index, index),
receive: Object.assign(receive, receive),
adjust: Object.assign(adjust, adjust),
damaged: Object.assign(damaged, damaged),
reset: Object.assign(reset, reset),
history: Object.assign(history, history),
report: Object.assign(report, report),
export: Object.assign(exportMethod, exportMethod),
}

export default stock