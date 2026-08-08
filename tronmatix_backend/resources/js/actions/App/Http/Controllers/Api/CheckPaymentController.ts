import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\CheckPaymentController::webhook
 * @see app/Http/Controllers/Api/CheckPaymentController.php:206
 * @route '/api/payment/webhook'
 */
export const webhook = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: webhook.url(options),
    method: 'post',
})

webhook.definition = {
    methods: ["post"],
    url: '/api/payment/webhook',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\CheckPaymentController::webhook
 * @see app/Http/Controllers/Api/CheckPaymentController.php:206
 * @route '/api/payment/webhook'
 */
webhook.url = (options?: RouteQueryOptions) => {
    return webhook.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\CheckPaymentController::webhook
 * @see app/Http/Controllers/Api/CheckPaymentController.php:206
 * @route '/api/payment/webhook'
 */
webhook.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: webhook.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\CheckPaymentController::webhook
 * @see app/Http/Controllers/Api/CheckPaymentController.php:206
 * @route '/api/payment/webhook'
 */
    const webhookForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: webhook.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\CheckPaymentController::webhook
 * @see app/Http/Controllers/Api/CheckPaymentController.php:206
 * @route '/api/payment/webhook'
 */
        webhookForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: webhook.url(options),
            method: 'post',
        })
    
    webhook.form = webhookForm
/**
* @see \App\Http\Controllers\Api\CheckPaymentController::verify
 * @see app/Http/Controllers/Api/CheckPaymentController.php:52
 * @route '/api/payment/verify'
 */
export const verify = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verify.url(options),
    method: 'post',
})

verify.definition = {
    methods: ["post"],
    url: '/api/payment/verify',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\CheckPaymentController::verify
 * @see app/Http/Controllers/Api/CheckPaymentController.php:52
 * @route '/api/payment/verify'
 */
verify.url = (options?: RouteQueryOptions) => {
    return verify.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\CheckPaymentController::verify
 * @see app/Http/Controllers/Api/CheckPaymentController.php:52
 * @route '/api/payment/verify'
 */
verify.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verify.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\CheckPaymentController::verify
 * @see app/Http/Controllers/Api/CheckPaymentController.php:52
 * @route '/api/payment/verify'
 */
    const verifyForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: verify.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\CheckPaymentController::verify
 * @see app/Http/Controllers/Api/CheckPaymentController.php:52
 * @route '/api/payment/verify'
 */
        verifyForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: verify.url(options),
            method: 'post',
        })
    
    verify.form = verifyForm
/**
* @see \App\Http\Controllers\Api\CheckPaymentController::confirmManual
 * @see app/Http/Controllers/Api/CheckPaymentController.php:165
 * @route '/api/payment/confirm-manual'
 */
export const confirmManual = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: confirmManual.url(options),
    method: 'post',
})

confirmManual.definition = {
    methods: ["post"],
    url: '/api/payment/confirm-manual',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\CheckPaymentController::confirmManual
 * @see app/Http/Controllers/Api/CheckPaymentController.php:165
 * @route '/api/payment/confirm-manual'
 */
confirmManual.url = (options?: RouteQueryOptions) => {
    return confirmManual.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\CheckPaymentController::confirmManual
 * @see app/Http/Controllers/Api/CheckPaymentController.php:165
 * @route '/api/payment/confirm-manual'
 */
confirmManual.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: confirmManual.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\CheckPaymentController::confirmManual
 * @see app/Http/Controllers/Api/CheckPaymentController.php:165
 * @route '/api/payment/confirm-manual'
 */
    const confirmManualForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: confirmManual.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\CheckPaymentController::confirmManual
 * @see app/Http/Controllers/Api/CheckPaymentController.php:165
 * @route '/api/payment/confirm-manual'
 */
        confirmManualForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: confirmManual.url(options),
            method: 'post',
        })
    
    confirmManual.form = confirmManualForm
const CheckPaymentController = { webhook, verify, confirmManual }

export default CheckPaymentController