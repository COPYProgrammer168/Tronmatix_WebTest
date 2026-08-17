import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\VideoController::index
 * @see app/Http/Controllers/Api/VideoController.php:16
 * @route '/api/videos'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/videos',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\VideoController::index
 * @see app/Http/Controllers/Api/VideoController.php:16
 * @route '/api/videos'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\VideoController::index
 * @see app/Http/Controllers/Api/VideoController.php:16
 * @route '/api/videos'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\VideoController::index
 * @see app/Http/Controllers/Api/VideoController.php:16
 * @route '/api/videos'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\VideoController::index
 * @see app/Http/Controllers/Api/VideoController.php:16
 * @route '/api/videos'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\VideoController::index
 * @see app/Http/Controllers/Api/VideoController.php:16
 * @route '/api/videos'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\VideoController::index
 * @see app/Http/Controllers/Api/VideoController.php:16
 * @route '/api/videos'
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
const VideoController = { index }

export default VideoController