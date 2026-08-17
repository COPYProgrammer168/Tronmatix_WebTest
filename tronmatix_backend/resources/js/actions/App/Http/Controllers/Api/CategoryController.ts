import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\CategoryController::index
 * @see app/Http/Controllers/Api/CategoryController.php:108
 * @route '/api/categories'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/categories',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\CategoryController::index
 * @see app/Http/Controllers/Api/CategoryController.php:108
 * @route '/api/categories'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\CategoryController::index
 * @see app/Http/Controllers/Api/CategoryController.php:108
 * @route '/api/categories'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\CategoryController::index
 * @see app/Http/Controllers/Api/CategoryController.php:108
 * @route '/api/categories'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\CategoryController::index
 * @see app/Http/Controllers/Api/CategoryController.php:108
 * @route '/api/categories'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\CategoryController::index
 * @see app/Http/Controllers/Api/CategoryController.php:108
 * @route '/api/categories'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\CategoryController::index
 * @see app/Http/Controllers/Api/CategoryController.php:108
 * @route '/api/categories'
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
const CategoryController = { index }

export default CategoryController