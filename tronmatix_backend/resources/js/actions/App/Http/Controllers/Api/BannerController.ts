import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\BannerController::index
 * @see app/Http/Controllers/Api/BannerController.php:17
 * @route '/api/banners'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/banners',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\BannerController::index
 * @see app/Http/Controllers/Api/BannerController.php:17
 * @route '/api/banners'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\BannerController::index
 * @see app/Http/Controllers/Api/BannerController.php:17
 * @route '/api/banners'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\BannerController::index
 * @see app/Http/Controllers/Api/BannerController.php:17
 * @route '/api/banners'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\BannerController::index
 * @see app/Http/Controllers/Api/BannerController.php:17
 * @route '/api/banners'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\BannerController::index
 * @see app/Http/Controllers/Api/BannerController.php:17
 * @route '/api/banners'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\BannerController::index
 * @see app/Http/Controllers/Api/BannerController.php:17
 * @route '/api/banners'
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
const BannerController = { index }

export default BannerController