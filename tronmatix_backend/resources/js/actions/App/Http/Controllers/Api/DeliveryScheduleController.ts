import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\DeliveryScheduleController::index
 * @see app/Http/Controllers/Api/DeliveryScheduleController.php:14
 * @route '/api/delivery-schedules'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/delivery-schedules',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\DeliveryScheduleController::index
 * @see app/Http/Controllers/Api/DeliveryScheduleController.php:14
 * @route '/api/delivery-schedules'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\DeliveryScheduleController::index
 * @see app/Http/Controllers/Api/DeliveryScheduleController.php:14
 * @route '/api/delivery-schedules'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\DeliveryScheduleController::index
 * @see app/Http/Controllers/Api/DeliveryScheduleController.php:14
 * @route '/api/delivery-schedules'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\DeliveryScheduleController::index
 * @see app/Http/Controllers/Api/DeliveryScheduleController.php:14
 * @route '/api/delivery-schedules'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\DeliveryScheduleController::index
 * @see app/Http/Controllers/Api/DeliveryScheduleController.php:14
 * @route '/api/delivery-schedules'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\DeliveryScheduleController::index
 * @see app/Http/Controllers/Api/DeliveryScheduleController.php:14
 * @route '/api/delivery-schedules'
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
const DeliveryScheduleController = { index }

export default DeliveryScheduleController