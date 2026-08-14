import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\DeliveryController::provinces
 * @see app/Http/Controllers/Api/DeliveryController.php:14
 * @route '/api/provinces'
 */
export const provinces = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: provinces.url(options),
    method: 'get',
})

provinces.definition = {
    methods: ["get","head"],
    url: '/api/provinces',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\DeliveryController::provinces
 * @see app/Http/Controllers/Api/DeliveryController.php:14
 * @route '/api/provinces'
 */
provinces.url = (options?: RouteQueryOptions) => {
    return provinces.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\DeliveryController::provinces
 * @see app/Http/Controllers/Api/DeliveryController.php:14
 * @route '/api/provinces'
 */
provinces.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: provinces.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\DeliveryController::provinces
 * @see app/Http/Controllers/Api/DeliveryController.php:14
 * @route '/api/provinces'
 */
provinces.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: provinces.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\DeliveryController::provinces
 * @see app/Http/Controllers/Api/DeliveryController.php:14
 * @route '/api/provinces'
 */
    const provincesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: provinces.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\DeliveryController::provinces
 * @see app/Http/Controllers/Api/DeliveryController.php:14
 * @route '/api/provinces'
 */
        provincesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: provinces.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\DeliveryController::provinces
 * @see app/Http/Controllers/Api/DeliveryController.php:14
 * @route '/api/provinces'
 */
        provincesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: provinces.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    provinces.form = provincesForm
/**
* @see \App\Http\Controllers\Api\DeliveryController::deliveryProviders
 * @see app/Http/Controllers/Api/DeliveryController.php:24
 * @route '/api/delivery-providers'
 */
export const deliveryProviders = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: deliveryProviders.url(options),
    method: 'get',
})

deliveryProviders.definition = {
    methods: ["get","head"],
    url: '/api/delivery-providers',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\DeliveryController::deliveryProviders
 * @see app/Http/Controllers/Api/DeliveryController.php:24
 * @route '/api/delivery-providers'
 */
deliveryProviders.url = (options?: RouteQueryOptions) => {
    return deliveryProviders.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\DeliveryController::deliveryProviders
 * @see app/Http/Controllers/Api/DeliveryController.php:24
 * @route '/api/delivery-providers'
 */
deliveryProviders.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: deliveryProviders.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\DeliveryController::deliveryProviders
 * @see app/Http/Controllers/Api/DeliveryController.php:24
 * @route '/api/delivery-providers'
 */
deliveryProviders.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: deliveryProviders.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\DeliveryController::deliveryProviders
 * @see app/Http/Controllers/Api/DeliveryController.php:24
 * @route '/api/delivery-providers'
 */
    const deliveryProvidersForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: deliveryProviders.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\DeliveryController::deliveryProviders
 * @see app/Http/Controllers/Api/DeliveryController.php:24
 * @route '/api/delivery-providers'
 */
        deliveryProvidersForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: deliveryProviders.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\DeliveryController::deliveryProviders
 * @see app/Http/Controllers/Api/DeliveryController.php:24
 * @route '/api/delivery-providers'
 */
        deliveryProvidersForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: deliveryProviders.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    deliveryProviders.form = deliveryProvidersForm
/**
* @see \App\Http\Controllers\Api\DeliveryController::calculateFee
 * @see app/Http/Controllers/Api/DeliveryController.php:65
 * @route '/api/delivery/calculate-fee'
 */
export const calculateFee = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: calculateFee.url(options),
    method: 'post',
})

calculateFee.definition = {
    methods: ["post"],
    url: '/api/delivery/calculate-fee',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\DeliveryController::calculateFee
 * @see app/Http/Controllers/Api/DeliveryController.php:65
 * @route '/api/delivery/calculate-fee'
 */
calculateFee.url = (options?: RouteQueryOptions) => {
    return calculateFee.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\DeliveryController::calculateFee
 * @see app/Http/Controllers/Api/DeliveryController.php:65
 * @route '/api/delivery/calculate-fee'
 */
calculateFee.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: calculateFee.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\DeliveryController::calculateFee
 * @see app/Http/Controllers/Api/DeliveryController.php:65
 * @route '/api/delivery/calculate-fee'
 */
    const calculateFeeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: calculateFee.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\DeliveryController::calculateFee
 * @see app/Http/Controllers/Api/DeliveryController.php:65
 * @route '/api/delivery/calculate-fee'
 */
        calculateFeeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: calculateFee.url(options),
            method: 'post',
        })
    
    calculateFee.form = calculateFeeForm
const DeliveryController = { provinces, deliveryProviders, calculateFee }

export default DeliveryController