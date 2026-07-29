import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\DeliveryController::provinces
 * @see app/Http/Controllers/Api/DeliveryController.php:12
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
 * @see app/Http/Controllers/Api/DeliveryController.php:12
 * @route '/api/provinces'
 */
provinces.url = (options?: RouteQueryOptions) => {
    return provinces.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\DeliveryController::provinces
 * @see app/Http/Controllers/Api/DeliveryController.php:12
 * @route '/api/provinces'
 */
provinces.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: provinces.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\DeliveryController::provinces
 * @see app/Http/Controllers/Api/DeliveryController.php:12
 * @route '/api/provinces'
 */
provinces.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: provinces.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\DeliveryController::provinces
 * @see app/Http/Controllers/Api/DeliveryController.php:12
 * @route '/api/provinces'
 */
    const provincesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: provinces.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\DeliveryController::provinces
 * @see app/Http/Controllers/Api/DeliveryController.php:12
 * @route '/api/provinces'
 */
        provincesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: provinces.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\DeliveryController::provinces
 * @see app/Http/Controllers/Api/DeliveryController.php:12
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
 * @see app/Http/Controllers/Api/DeliveryController.php:22
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
 * @see app/Http/Controllers/Api/DeliveryController.php:22
 * @route '/api/delivery-providers'
 */
deliveryProviders.url = (options?: RouteQueryOptions) => {
    return deliveryProviders.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\DeliveryController::deliveryProviders
 * @see app/Http/Controllers/Api/DeliveryController.php:22
 * @route '/api/delivery-providers'
 */
deliveryProviders.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: deliveryProviders.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\DeliveryController::deliveryProviders
 * @see app/Http/Controllers/Api/DeliveryController.php:22
 * @route '/api/delivery-providers'
 */
deliveryProviders.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: deliveryProviders.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\DeliveryController::deliveryProviders
 * @see app/Http/Controllers/Api/DeliveryController.php:22
 * @route '/api/delivery-providers'
 */
    const deliveryProvidersForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: deliveryProviders.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\DeliveryController::deliveryProviders
 * @see app/Http/Controllers/Api/DeliveryController.php:22
 * @route '/api/delivery-providers'
 */
        deliveryProvidersForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: deliveryProviders.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\DeliveryController::deliveryProviders
 * @see app/Http/Controllers/Api/DeliveryController.php:22
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
const DeliveryController = { provinces, deliveryProviders }

export default DeliveryController