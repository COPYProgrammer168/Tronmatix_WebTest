import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\MarqueeController::index
 * @see app/Http/Controllers/Api/MarqueeController.php:16
 * @route '/api/marquees'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/marquees',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\MarqueeController::index
 * @see app/Http/Controllers/Api/MarqueeController.php:16
 * @route '/api/marquees'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\MarqueeController::index
 * @see app/Http/Controllers/Api/MarqueeController.php:16
 * @route '/api/marquees'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\MarqueeController::index
 * @see app/Http/Controllers/Api/MarqueeController.php:16
 * @route '/api/marquees'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\MarqueeController::index
 * @see app/Http/Controllers/Api/MarqueeController.php:16
 * @route '/api/marquees'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\MarqueeController::index
 * @see app/Http/Controllers/Api/MarqueeController.php:16
 * @route '/api/marquees'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\MarqueeController::index
 * @see app/Http/Controllers/Api/MarqueeController.php:16
 * @route '/api/marquees'
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
const MarqueeController = { index }

export default MarqueeController